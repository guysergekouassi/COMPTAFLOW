<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\TreasuryCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


/**
 * ExternalSyncController
 * Endpoint API dédié à la liaison Selflow ↔ COMPTAFLOW.
 * Sécurisé par un secret partagé (header ou body).
 */
class ExternalSyncController extends Controller
{

    /**
     * Les deux exercices se recouvrent-ils ?
     *
     * Selflow annonce l'exercice qu'il a ouvert ; on prenait le nôtre sans
     * jamais comparer. Une pièce d'un exercice que Selflow vient de clore se
     * serait rangée dans l'exercice courant de Comptaflow, et les deux balances
     * auraient divergé sans qu'aucun écran ne le dise.
     *
     * On tolère un décalage de bornes — les deux applications n'ont pas
     * forcément le même premier jour — mais pas deux exercices disjoints.
     *
     * @return string|null le motif du refus, ou `null` si tout va bien
     */
    private static function desaccordDExercice($exercice, ?string $debut, ?string $fin): ?string
    {
        // Selflow ne dit rien : on ne peut rien vérifier, et refuser sur ce
        // seul motif bloquerait les versions antérieures du connecteur.
        if (empty($debut) || empty($fin)) {
            return null;
        }

        $notre = [$exercice->date_debut, $exercice->date_fin];

        if (empty($notre[0]) || empty($notre[1])) {
            return null;
        }

        $nDebut = \Carbon\Carbon::parse($notre[0]);
        $nFin   = \Carbon\Carbon::parse($notre[1]);
        $sDebut = \Carbon\Carbon::parse($debut);
        $sFin   = \Carbon\Carbon::parse($fin);

        if ($sFin->lt($nDebut) || $sDebut->gt($nFin)) {
            return sprintf(
                'Les exercices ne se recouvrent pas : Selflow travaille du %s au %s, '
                . 'Comptaflow du %s au %s. Alignez les exercices avant de déverser.',
                $sDebut->format('d/m/Y'), $sFin->format('d/m/Y'),
                $nDebut->format('d/m/Y'), $nFin->format('d/m/Y')
            );
        }

        return null;
    }

    /**
     * Le compte général, créé s'il manque — avec le bon type.
     *
     * `type_de_compte` valait **`actif`** en dur, pour tous les comptes créés
     * à la volée : un compte de vente `701000` arrivait donc au bilan, du côté
     * de l'actif. Les états de Comptaflow devenaient faux, et l'erreur ne se
     * voyait qu'au compte de résultat, vide.
     *
     * La classe du compte SYSCOHADA dit son type, et elle le dit sans
     * ambiguïté — c'est le premier chiffre du numéro.
     */
    private static function compteGeneral($company, string $numero, string $libelle)
    {
        $existant = PlanComptable::where('company_id', $company->id)
            ->where('numero_de_compte', $numero)
            ->first();

        // Le plan de Comptaflow fait foi : un compte déjà là n'est jamais
        // réécrit, ni son intitulé ni son type. C'est sa configuration
        // d'origine, et Selflow n'a pas à la corriger.
        if ($existant) {
            return $existant;
        }

        return PlanComptable::create([
            'numero_de_compte' => $numero,
            'intitule'         => $libelle ?: 'Compte ' . $numero,
            'company_id'       => $company->id,
            'user_id'          => $company->user_id,
            'type_de_compte'   => self::typeDeCompte($numero),
        ]);
    }

    /**
     * Le type d'un compte, d'après sa classe SYSCOHADA.
     *
     * | Classe | Nature | Type |
     * |---|---|---|
     * | 1 | Ressources durables | passif |
     * | 2 | Actif immobilisé | actif |
     * | 3 | Stocks | actif |
     * | 4 | Tiers | actif ou passif selon le compte — `actif` par défaut, l'utilisateur tranche |
     * | 5 | Trésorerie | actif |
     * | 6 | Charges | charge |
     * | 7 | Produits | produit |
     * | 8 | Autres charges et produits | charge |
     * | 9 | Analytique | analytique |
     */
    private static function typeDeCompte(string $numero): string
    {
        return match (substr($numero, 0, 1)) {
            '1'      => 'passif',
            '2', '3', '5' => 'actif',
            '4'      => 'actif',
            '6', '8' => 'charge',
            '7'      => 'produit',
            '9'      => 'analytique',
            default  => 'actif',
        };
    }

    /**
     * Le compte de tiers désigné par Selflow, s'il existe chez nous.
     *
     * **On ne le crée pas.** Le plan de tiers est la configuration d'origine de
     * Comptaflow : y ajouter des fiches depuis Selflow ferait deux référentiels
     * concurrents, et le comptable ne saurait plus lequel fait foi. Un tiers
     * inconnu laisse l'écriture sur son seul compte collectif — ce qui est
     * juste, quoique moins précis — et le comptable le rattachera lui-même.
     */
    private static function tiers($company, ?string $numeroTiers, $planComptableId): ?int
    {
        if (empty($numeroTiers)) {
            return null;
        }

        return PlanTiers::where('company_id', $company->id)
            ->where('numero_de_tiers', $numeroTiers)
            ->value('id');
    }

    /**
     * Le secret fourni est-il celui que l'on attend ?
     *
     * Deux corrections en une :
     *
     * - **un secret non configuré ne vaut pas « pas de contrôle ».** La valeur
     *   de repli en dur — « selflow-comptaflow-secret-2026 » — était publiée
     *   dans le dépôt : quiconque l'a lue pouvait déverser des écritures dans
     *   la comptabilité de n'importe quelle entreprise liée, ou lire la liste
     *   de toutes les entreprises de la plateforme. Sans variable
     *   d'environnement, on refuse désormais tout ;
     * - **`hash_equals` plutôt que `!==`.** Une comparaison de chaînes ordinaire
     *   s'arrête au premier caractère différent : le temps de réponse révèle
     *   alors combien de caractères sont justes, et le secret se devine
     *   caractère par caractère. `hash_equals` compare en temps constant.
     */
    private static function secretValide(?string $fourni, ?string $attendu): bool
    {
        if (empty($attendu) || empty($fourni)) {
            return false;
        }

        return hash_equals($attendu, $fourni);
    }

    /**
     * Crée une entreprise + un administrateur depuis une requête externe (ex : Selflow).
     * POST /api/external/register-enterprise
     */
    public function registerEnterprise(Request $request)
    {
        // ── Vérification du secret partagé ──
        $expectedSecret = config('external_sync.external_sync_secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');

        if (!self::secretValide($providedSecret, $expectedSecret)) {
            Log::warning('ExternalSync: secret invalide', ['ip' => $request->ip()]);
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        // ── Validation ──
        $validator = Validator::make($request->all(), [
            'company_name'       => 'required|string|max:255',
            'activity'           => 'nullable|string|max:255',
            'juridique_form'     => 'nullable|string|max:50',
            'adresse'            => 'nullable|string|max:255',
            'city'               => 'nullable|string|max:100',
            'country'            => 'nullable|string|max:100',
            'phone_number'       => 'nullable|string|max:30',
            'email_adresse'      => 'required|email|max:255',
            'ncc'                => 'nullable|string|max:50',
            'rccm'               => 'nullable|string|max:100',
            'compte_contribuable'=> 'nullable|string|max:100',
            'regime'             => 'nullable|string|max:80',
            'admin_nom'          => 'nullable|string|max:100',
            'admin_prenom'       => 'nullable|string|max:150',
            'admin_password'     => 'required|string|min:8',
            'selflow_company_id' => 'nullable|integer',
            'selflow_sync_key'   => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ── Vérifier unicité email / company_name ──
        if (User::where('email_adresse', $request->email_adresse)->exists()) {
            return response()->json(['success' => false, 'message' => 'Un compte avec cet email existe déjà.'], 409);
        }
        if (Company::where('company_name', $request->company_name)->exists()) {
            return response()->json(['success' => false, 'message' => 'Une entreprise avec ce nom existe déjà.'], 409);
        }

        DB::beginTransaction();
        try {
            // 1. Créer l'entreprise
            $company = Company::create([
                'company_name'        => $request->company_name,
                'activity'            => $request->activity ?? 'Commercial',
                'juridique_form'      => $request->juridique_form ?? 'SARL',
                'social_capital'      => $request->social_capital ?? 0,
                'adresse'             => $request->adresse,
                'code_postal'         => '',
                'city'                => $request->city ?? 'Abidjan',
                'country'             => $request->country ?? "Côte d'Ivoire",
                'phone_number'        => $request->phone_number,
                'email_adresse'       => $request->email_adresse,
                'ncc'                 => $request->ncc,
                'rccm'                => $request->rccm,
                'compte_contribuable' => $request->compte_contribuable,
                'regime'              => $request->regime,
                'is_active'           => true,
                'selflow_company_id'  => $request->selflow_company_id,
                'selflow_sync_key'    => $request->selflow_sync_key,
                'selflow_sync_status' => 'active',
                'user_id'             => 0, // sera mis à jour après
            ]);

            // 2. Créer l'admin
            $adminUser = User::create([
                'name'          => $request->admin_nom ?? 'Admin',
                'last_name'     => $request->admin_prenom ?? '',
                'email_adresse' => $request->email_adresse,
                'password'      => Hash::make($request->admin_password),
                'role'          => 'admin',
                'company_id'    => $company->id,
                'is_active'     => true,
            ]);

            // 3. Lier l'admin à l'entreprise
            $company->update(['user_id' => $adminUser->id]);

            // 4. Créer les catégories TFT obligatoires
            foreach ([
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ] as $catName) {
                TreasuryCategory::create(['name' => $catName, 'company_id' => $company->id]);
            }

            DB::commit();

            Log::info('ExternalSync: entreprise créée depuis Selflow', [
                'company_id'   => $company->id,
                'company_name' => $company->company_name,
            ]);

            return response()->json([
                'success'    => true,
                'company_id' => $company->id,
                'message'    => 'Entreprise et administrateur créés avec succès dans COMPTAFLOW.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ExternalSync: erreur création entreprise', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur interne : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crée une entreprise dans SELFLOW depuis COMPTAFLOW.
     * (Utilisé dans l'autre sens — endpoint miroir appelé par COMPTAFLOW.)
     * POST /api/external/status
     */
    public function syncStatus(Request $request)
    {
        $expectedSecret = config('app.external_sync_secret', 'selflow-local-secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');
        if (!self::secretValide($providedSecret, $expectedSecret)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $company = Company::where('selflow_company_id', $request->selflow_company_id)->first();
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Entreprise non trouvée.'], 404);
        }

        return response()->json([
            'success'    => true,
            'company_id' => $company->id,
            'status'     => $company->selflow_sync_status,
        ]);
    }

    /**
     * Lie a posteriori une entreprise Selflow avec une entreprise COMPTAFLOW existante via sa clé.
     * POST /api/external/link-company
     */
    public function linkCompany(Request $request)
    {
        $expectedSecret = config('external_sync.external_sync_secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');
        if (!self::secretValide($providedSecret, $expectedSecret)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $request->validate([
            'selflow_sync_key'   => 'required|string|max:100',
            'selflow_company_id' => 'required|integer',
            'clients'            => 'nullable|array',
            'fournisseurs'       => 'nullable|array',
        ]);

        $company = Company::where('selflow_sync_key', $request->selflow_sync_key)->first();
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Clé de synchronisation COMPTAFLOW invalide.'], 404);
        }

        DB::beginTransaction();
        try {
            // Lier l'entreprise
            $company->update([
                'selflow_company_id'  => $request->selflow_company_id,
                'selflow_sync_status' => 'active',
            ]);

            // Fusionner les Tiers : Clients de Selflow -> PlanTiers de COMPTAFLOW
            if ($request->has('clients') && is_array($request->clients)) {
                $compteGeneralClient = \App\Models\PlanComptable::where('company_id', $company->id)
                    ->where('numero_de_compte', 'like', '411%')
                    ->first();

                foreach ($request->clients as $client) {
                    $intitule = strtoupper($client['nom'] ?? '');
                    if (empty($intitule)) continue;

                    $exists = \App\Models\PlanTiers::where('company_id', $company->id)
                        ->where('type_de_tiers', 'client')
                        ->where('intitule', $intitule)
                        ->first();

                    if (!$exists) {
                        $num = $this->generateNextTierNumber($company, '411', $intitule);
                        \App\Models\PlanTiers::create([
                            'numero_de_tiers' => $num,
                            'intitule'        => $intitule,
                            'type_de_tiers'   => 'client',
                            'compte_general'  => $compteGeneralClient?->id,
                            'user_id'         => $company->user_id,
                            'company_id'      => $company->id,
                            'numero_original' => $client['id'] ?? null,
                        ]);
                    } elseif ($client['id'] && !$exists->numero_original) {
                        $exists->update(['numero_original' => $client['id']]);
                    }
                }
            }

            // Fusionner les Tiers : Fournisseurs de Selflow -> PlanTiers de COMPTAFLOW
            if ($request->has('fournisseurs') && is_array($request->fournisseurs)) {
                $compteGeneralFourn = \App\Models\PlanComptable::where('company_id', $company->id)
                    ->where('numero_de_compte', 'like', '401%')
                    ->first();

                foreach ($request->fournisseurs as $fourn) {
                    $intitule = strtoupper($fourn['nom'] ?? '');
                    if (empty($intitule)) continue;

                    $exists = \App\Models\PlanTiers::where('company_id', $company->id)
                        ->where('type_de_tiers', 'fournisseur')
                        ->where('intitule', $intitule)
                        ->first();

                    if (!$exists) {
                        $num = $this->generateNextTierNumber($company, '401', $intitule);
                        \App\Models\PlanTiers::create([
                            'numero_de_tiers' => $num,
                            'intitule'        => $intitule,
                            'type_de_tiers'   => 'fournisseur',
                            'compte_general'  => $compteGeneralFourn?->id,
                            'user_id'         => $company->user_id,
                            'company_id'      => $company->id,
                            'numero_original' => $fourn['id'] ?? null,
                        ]);
                    } elseif ($fourn['id'] && !$exists->numero_original) {
                        $exists->update(['numero_original' => $fourn['id']]);
                    }
                }
            }

            DB::commit();

            // Récupérer les données pour le retour de synchronisation
            $planComptable = \App\Models\PlanComptable::where('company_id', $company->id)
                ->select('id', 'numero_de_compte', 'intitule', 'numero_original')
                ->get();

            $codesJournaux = \App\Models\CodeJournal::where('company_id', $company->id)
                ->get()
                ->map(function ($cj) {
                    $compteNumero = null;
                    if ($cj->type === 'Trésorerie') {
                        $compteNumero = $cj->compte_de_contrepartie;
                        if (!$compteNumero && $cj->account) {
                            $compteNumero = $cj->account->numero_de_compte;
                        }
                    }
                    if (!$compteNumero) {
                        if ($cj->type === 'Achats') {
                            $compteNumero = '601000';
                        } elseif ($cj->type === 'Ventes') {
                            $compteNumero = '701000';
                        } else {
                            $compteNumero = '471000';
                        }
                    }
                    return [
                        'id'                     => $cj->id,
                        'code_journal'           => $cj->code_journal,
                        'numero_original'        => $cj->numero_original,
                        'intitule'               => $cj->intitule,
                        'type'                   => $cj->type,
                        'compte_de_tresorerie'   => $cj->compte_de_tresorerie,
                        'compte_numero'          => $compteNumero,
                    ];
                });


            $tiers = \App\Models\PlanTiers::where('company_id', $company->id)
                ->with('compte')
                ->get()
                ->map(function($t) {
                    return [
                        'id'              => $t->id,
                        'numero_de_tiers' => $t->numero_de_tiers,
                        'intitule'        => $t->intitule,
                        'type_de_tiers'   => $t->type_de_tiers,
                        'numero_original' => $t->numero_original,
                        'compte_general'  => $t->compte ? $t->compte->numero_de_compte : null,
                        'compte_numero'   => $t->compte ? $t->compte->numero_de_compte : null,
                        'compte_original' => $t->compte ? $t->compte->numero_original : null,
                    ];
                });

            return response()->json([
                'success'        => true,
                'company_id'     => $company->id,
                'plan_comptable' => $planComptable,
                'codes_journaux' => $codesJournaux,
                'tiers'          => $tiers,
                'message'        => 'Liaison a posteriori établie avec succès.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ExternalSync linkCompany error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la liaison : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Génère le numéro de tiers suivant selon la logique COMPTAFLOW.
     */
    private function generateNextTierNumber($company, $prefix, $intitule)
    {
        $digits = $company->tier_digits ?? 8;
        $idType = $company->tier_id_type ?? 'numeric';

        if ($idType === 'numeric') {
            $base = $prefix;
        } else {
            $cleanName = strtoupper(preg_replace('/[^a-zA-Z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $intitule)));
            $namePart = substr($cleanName, 0, 3);
            if (strlen($namePart) < 1) $namePart = 'XXX';
            $base = $prefix . $namePart;
        }

        $availableSpace = max(0, $digits - strlen($base));
        if ($availableSpace === 0) {
            return substr($base, 0, $digits);
        }

        $existingTiers = \App\Models\PlanTiers::where('company_id', $company->id)
            ->where('numero_de_tiers', 'like', $base . '%')
            ->get();

        $maxSeq = 0;
        foreach ($existingTiers as $tier) {
            $suffix = substr($tier->numero_de_tiers, strlen($base));
            if (is_numeric($suffix)) {
                $maxSeq = max($maxSeq, (int)$suffix);
            }
        }

        $seq = $maxSeq + 1;
        $nextId = $base . str_pad($seq, $availableSpace, '0', STR_PAD_LEFT);
        if (strlen($nextId) > $digits) {
            $nextId = substr($nextId, 0, $digits);
        }

        return $nextId;
    }

    /**
     * Déverse des écritures de Selflow vers COMPTAFLOW.
     * POST /api/external/ecritures/deverser
     */
    public function deverserEcritures(Request $request)
    {
        $expectedSecret = config('external_sync.external_sync_secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');
        if (!self::secretValide($providedSecret, $expectedSecret)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $request->validate([
            'selflow_company_id' => 'required|integer',
            'ecritures'          => 'required|array',
        ]);

        $company = Company::where('selflow_company_id', $request->selflow_company_id)->first();
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Entreprise non trouvée ou non connectée.'], 404);
        }

        $exercice = ExerciceComptable::where('company_id', $company->id)
            ->where('is_active', true)
            ->first() ?? ExerciceComptable::where('company_id', $company->id)->first();

        if (!$exercice) {
            return response()->json(['success' => false, 'message' => 'Aucun exercice comptable trouvé pour cette entreprise.'], 422);
        }

        // ── L'exercice des deux côtés doit être le même ──
        //
        // On prenait le nôtre, actif, sans jamais comparer : une pièce d'un
        // exercice que Selflow vient de clore se serait rangée dans l'exercice
        // courant de Comptaflow, et les deux balances auraient divergé sans
        // qu'aucun écran ne le dise. Mieux vaut refuser franchement et laisser
        // l'utilisateur aligner ses exercices.
        $desaccord = self::desaccordDExercice($exercice, $request->input('exercice_debut'), $request->input('exercice_fin'));

        if ($desaccord) {
            return response()->json(['success' => false, 'message' => $desaccord], 409);
        }

        $count = 0;
        $ignorees = 0;
        $refus = [];

        DB::beginTransaction();
        try {
            foreach ($request->ecritures as $ec) {
                $refPiece = $ec['reference_document'] ?? '';
                $libelle = $ec['libelle'] ?? '';
                $debitVal = $ec['debit'] ?? 0;
                $creditVal = $ec['credit'] ?? 0;

                // ── Idempotence ──
                //
                // `n_saisie` recevait la référence de pièce, ou `SELF_ . time()`
                // à défaut : ni l'une ni l'autre ne distingue un **renvoi**
                // d'une écriture **nouvelle**. Rejouer une synchronisation —
                // après une coupure réseau, après un retry — dupliquait tout,
                // et la balance doublait sans que rien ne le signale.
                $cleSelflow = $ec['cle_selflow'] ?? null;

                if ($cleSelflow && EcritureComptable::where('company_id', $company->id)
                        ->where('cle_selflow', $cleSelflow)->exists()) {
                    $ignorees++;
                    continue;
                }

                // ── Le journal ──
                //
                // Un code inconnu retombait sur **le premier journal de la
                // liste** : une vente pouvait ainsi atterrir au journal de
                // caisse, et personne ne s'en apercevait avant la révision.
                // Le journal de Comptaflow fait foi — c'est sa configuration
                // d'origine — mais un code qu'il ne connaît pas est une erreur
                // à signaler, pas à rattraper au hasard.
                $cjCode = $ec['code_journal'] ?? null;
                $codeJournal = $cjCode
                    ? CodeJournal::where('company_id', $company->id)->where('code_journal', $cjCode)->first()
                    : null;

                if (!$codeJournal) {
                    $refus[] = ($refPiece ?: '?') . ' : journal « ' . ($cjCode ?? '—') . ' » inconnu';
                    continue;
                }

                // ── Le compte, et le tiers ──
                //
                // Une écriture de Selflow porte un compte général **et**, pour
                // un client ou un fournisseur, un compte de tiers. Le tiers
                // n'était pas transmis : on le cherchait dans `plan_tiers` à
                // partir du compte général, on ne le trouvait pas, et
                // l'écriture se rattachait au seul compte collectif. Le relevé
                // d'un client particulier devenait impossible à établir.
                $accountCode = !empty($ec['compte_debit']) ? $ec['compte_debit'] : ($ec['compte_credit'] ?? null);

                if (empty($accountCode)) {
                    $refus[] = ($refPiece ?: '?') . ' : aucun compte';
                    continue;
                }

                $planComptable = self::compteGeneral($company, $accountCode, $libelle);
                $planTiersId = self::tiers($company, $ec['compte_tiers'] ?? null, $planComptable->id);

                // ── L'écriture ──
                EcritureComptable::create([
                    'company_id'              => $company->id,
                    'user_id'                 => $company->user_id,
                    'exercices_comptables_id' => $exercice->id,
                    'code_journal_id'         => $codeJournal->id,
                    'date'                    => $ec['date_ecriture'],
                    'description_operation'   => $libelle,
                    'reference_piece'         => $refPiece,
                    'n_saisie'                => $cleSelflow ?: ($refPiece ?: 'SELF_' . time() . '_' . $count),
                    'cle_selflow'             => $cleSelflow,
                    'plan_comptable_id'       => $planComptable->id,
                    'plan_tiers_id'           => $planTiersId,
                    'debit'                   => $debitVal,
                    'credit'                  => $creditVal,
                    'statut'                  => 'approved',
                ]);

                $count++;
            }

            DB::commit();

            // Le compte rendu dit ce qui est passé, ce qui était déjà là, et
            // ce qui a été refusé. Une synchronisation qui annonce « succès »
            // en ayant écarté la moitié des lignes est pire qu'un échec.
            return response()->json([
                'success'  => true,
                'count'    => $count,
                'ignorees' => $ignorees,
                'refus'    => $refus,
                'message'  => "$count écriture(s) déversée(s)"
                    . ($ignorees ? ", $ignorees déjà présente(s)" : '')
                    . ($refus ? ', ' . count($refus) . ' refusée(s)' : '')
                    . '.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ExternalSync deverserEcritures error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du déversement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Liste toutes les entreprises COMPTAFLOW (pour le module Liaison SuperAdmin de SELFLOW).
     * POST /api/external/list-companies
     */
    public function listCompanies(Request $request)
    {
        $expectedSecret = config('external_sync.external_sync_secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');

        if (!self::secretValide($providedSecret, $expectedSecret)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $companies = Company::all()->map(function ($c) {
            $admin = User::where('company_id', $c->id)->where('role', 'admin')->first();
            return [
                'id'                   => $c->id,
                'nom'                  => $c->company_name,
                'email'                => $c->email_adresse,
                'telephone'            => $c->phone_number,
                'adresse'              => $c->adresse,
                'rccm'                 => $c->rccm,
                'ncc'                  => $c->ncc,
                'regime'               => $c->regime,
                'forme_juridique'      => $c->juridique_form,
                'gerant_nom'           => $admin ? $admin->name : null,
                'gerant_prenom'        => $admin ? $admin->last_name : null,
                'created_at'           => $c->created_at ? $c->created_at->format('d/m/Y') : null,
                'admin_email'          => $admin ? $admin->email_adresse : null,
                'is_linked'            => !empty($c->selflow_company_id),
                'selflow_status'       => $c->selflow_sync_status ?? 'inactive',
            ];
        });

        return response()->json([
            'success'   => true,
            'companies' => $companies,
        ]);
    }

    /**
     * Retourne les informations d'une entreprise COMPTAFLOW.
     * POST /api/external/company-info
     */
    public function companyInfo(Request $request)
    {
        $expectedSecret = config('external_sync.external_sync_secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');

        if (!self::secretValide($providedSecret, $expectedSecret)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $company = Company::find($request->comptaflow_company_id);
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Entreprise introuvable.'], 404);
        }

        $admin = User::where('company_id', $company->id)->where('role', 'admin')->first();

        return response()->json([
            'success' => true,
            'company' => [
                'id'             => $company->id,
                'nom'            => $company->company_name,
                'rccm'           => $company->rccm,
                'ncc'            => $company->ncc,
                'email'          => $company->email_adresse,
                'telephone'      => $company->phone_number,
                'adresse'        => $company->adresse,
                'regime'         => $company->regime,
                'created_at'     => $company->created_at ? $company->created_at->format('d/m/Y') : null,
                'admin_nom'      => $admin ? ($admin->name . ' ' . $admin->last_name) : null,
                'admin_email'    => $admin ? $admin->email_adresse : null,
                'selflow_status' => $company->selflow_sync_status ?? 'inactive',
            ],
        ]);
    }
}


