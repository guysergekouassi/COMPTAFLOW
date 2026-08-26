<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * ExternalSyncController
 * Points d'entrée de la liaison Selflow ↔ COMPTAFLOW.
 *
 * Le secret partagé `EXTERNAL_SYNC_SECRET` dit que l'appel vient de Selflow ; il
 * ne dit **pas quelle entreprise appelle**. Les deux déversements sont donc
 * désormais filtrés par `cle.entreprise`, qui résout le dossier depuis l'en-tête
 * `X-Company-Key` et refuse une clé désignant un autre dossier que celui annoncé
 * dans le corps. Le cycle de vie de cette clé — provision, revoke, verify — vit
 * dans `ExternalCompanyController`.
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
    private static function compteGeneral($company, string $numero, string $libelle, ?string $strategie = null)
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

        return PlanComptable::create(array_filter([
            'numero_de_compte' => $numero,
            'intitule'         => $libelle ?: 'Compte ' . $numero,
            'company_id'       => $company->id,
            'user_id'          => $company->user_id,
            'type_de_compte'   => self::typeDeCompte($numero),
            // La classe est le premier chiffre du numéro : la renseigner ici
            // évite qu'un compte venu de Selflow reste hors de tout état.
            'classe'           => is_numeric(substr($numero, 0, 1)) ? substr($numero, 0, 1) : null,
            'adding_strategy'  => $strategie,
        ], fn ($v) => $v !== null));
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
     * Le dossier visé par la requête — d'après la clé, pas d'après le corps.
     *
     * `entreprise_liee` est posé par le filtre `cle.entreprise` à partir de
     * l'en-tête `X-Company-Key`, et c'est la seule source digne de foi : le
     * secret partagé dit que l'appel vient de Selflow, il ne dit pas de quelle
     * entreprise. Chercher le dossier dans le corps revenait à laisser
     * l'appelant désigner lui-même les livres dans lesquels il écrit.
     *
     * Le `??` est la **TOLÉRANCE DE TRANSITION** : tant que les deux
     * applications ne sont pas déployées ensemble, un Selflow d'avant ce lot
     * appelle encore sans en-tête.
     *
     * IL Y EN A **TROIS**, ET ELLES SE RETIRENT ENSEMBLE — en retirer une ou
     * deux laisse la porte ouverte du côté qu'on n'a pas fermé :
     *   1. celle-ci ;
     *   2. le bloc marqué dans `VerifieCleEntreprise::handle()` ;
     *   3. celle de Selflow, dans son `ExternalSyncControleur::entrepriseDeLaCle()`.
     *
     * Tant qu'elles sont là, le secret partagé suffit toujours à écrire dans
     * n'importe quel dossier.
     */
    private static function entrepriseDeLaRequete(Request $request): ?Company
    {
        $entreprise = $request->attributes->get('entreprise_liee');

        if ($entreprise instanceof Company) {
            return $entreprise;
        }

        return Company::where('selflow_company_id', $request->input('selflow_company_id'))->first();
    }

    /**
     * Date la **réception** d'un déversement accepté.
     *
     * Selflow affiche cette date à l'entreprise, et il l'écrivait jusqu'ici au
     * moment de l'*envoi* : elle datait une réception qui n'avait pas eu lieu —
     * un déversement refusé, ou perdu en route, laissait quand même « dernière
     * synchronisation : aujourd'hui » à l'écran.
     */
    private static function daterLaReception(Company $company): void
    {
        $company->forceFill(['selflow_last_deposit_at' => now()])->save();
    }

    /**
     * `registerEnterprise` a été retirée avec sa route.
     *
     * `POST /api/external/companies/provision` la remplace
     * (`ExternalCompanyController`). Trois raisons, dans cet ordre :
     *
     * - elle exigeait `admin_password` : le superadministrateur Selflow
     *   choisissait le mot de passe du compte d'un client, et cette valeur
     *   traversait la passerelle **en clair** dans le corps de la requête ;
     * - elle acceptait `selflow_sync_key` depuis le corps — c'est-à-dire que
     *   l'appelant choisissait lui-même le laissez-passer que Comptaflow allait
     *   ensuite reconnaître. La clé est désormais générée ici ;
     * - plus rien ne l'appelait depuis Selflow dans ce sens.
     *
     * ⚠️ La route **homonyme de Selflow** reste en place : Comptaflow l'appelle
     * toujours pour créer une entreprise *chez Selflow*
     * (`SuperAdminCompanyController`, `SuperAdminLiaisonController`). Les deux
     * portent le même chemin de chaque côté de la passerelle.
     */

    /**
     * Crée une entreprise dans SELFLOW depuis COMPTAFLOW.
     * (Utilisé dans l'autre sens — endpoint miroir appelé par COMPTAFLOW.)
     * POST /api/external/status
     */
    public function syncStatus(Request $request)
    {
        // `app.external_sync_secret` n'existe pas dans config/app.php : c'était
        // donc toujours la valeur de repli — « selflow-local-secret », en clair
        // dans le dépôt — qui faisait foi ici, quoi qu'on mette dans le .env.
        // Ce point d'entrée restait ouvert sur une clé publique pendant que les
        // cinq autres étaient refermés. Même source que partout ailleurs.
        $expectedSecret = config('external_sync.external_sync_secret');
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

        // La clé n'est plus stockée en clair : la recherche porte sur son
        // SHA-256, indexé et unique. Les liaisons établies avant ce lot ont été
        // basculées par la migration, elles continuent donc de répondre ici.
        $company = Company::where('selflow_sync_key_hash', hash('sha256', $request->selflow_sync_key))->first();

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Clé de synchronisation COMPTAFLOW invalide.'], 404);
        }

        if ($company->selflow_sync_key_revoked_at) {
            return response()->json([
                'success' => false,
                'message' => 'Clé de synchronisation révoquée le '
                    . $company->selflow_sync_key_revoked_at->format('d/m/Y') . '.',
            ], 401);
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

        $company = self::entrepriseDeLaRequete($request);

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

            self::daterLaReception($company);

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
     * Reçoit le référentiel d'une entreprise Selflow : plan comptable, codes
     * journaux, plan de tiers.
     * POST /api/external/referentiel/deverser
     *
     * **Selflow déverse ; Comptaflow reçoit.** Le code faisait exactement
     * l'inverse : Selflow appelait `link-company`, recopiait chez lui le plan
     * comptable de Comptaflow, puis supprimait ce qui n'y figurait pas — une
     * entreprise dont le comptable n'avait pas encore rempli son plan chez
     * Comptaflow se retrouvait dépouillée du sien. Cette méthode-là a été
     * retirée de Selflow ; celle-ci la remplace, dans l'autre sens.
     *
     * Deux règles gouvernent tout ce qui suit :
     *
     * - **amorcer quand Comptaflow est vide.** Une entreprise qui arrive avec
     *   ses 38 comptes, ses 10 journaux et ses tiers n'a pas à les ressaisir ;
     * - **ne rien écraser quand il ne l'est pas.** Ce que le comptable a saisi
     *   ou corrigé chez Comptaflow fait foi : une ligne déjà en place n'est
     *   jamais réécrite, seuls ses champs restés vides sont complétés. Et
     *   **rien n'est supprimé** — c'était la faute de l'ancien sens, à ne pas
     *   reproduire en miroir : un compte absent du déversement peut avoir été
     *   créé par le comptable, ou porter des écritures.
     *
     * L'ordre compte : le plan comptable, puis les journaux, puis les tiers —
     * un tiers renvoie à son compte général par une clé étrangère, il doit
     * exister avant lui.
     */
    public function deverserReferentiel(Request $request)
    {
        $expectedSecret = config('external_sync.external_sync_secret');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');

        if (!self::secretValide($providedSecret, $expectedSecret)) {
            Log::warning('ExternalSync: secret invalide', [
                'ip'    => $request->ip(),
                'route' => 'api/external/referentiel/deverser',
            ]);
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $request->validate([
            'selflow_company_id'    => 'required|integer',
            'comptaflow_company_id' => 'required|integer',
            'plan_comptable'        => 'nullable|array',
            'codes_journaux'        => 'nullable|array',
            'tiers'                 => 'nullable|array',
        ]);

        // ── La liaison doit exister ──
        //
        // On ne reçoit que d'une entreprise déjà liée, et on n'en crée pas une
        // au passage : un `comptaflow_company_id` erroné déverserait le
        // référentiel d'une entreprise dans la comptabilité d'une autre.
        //
        // Le dossier vient d'abord de la clé d'en-tête, que `cle.entreprise` a
        // déjà confrontée aux deux identifiants du corps. Le repli croise les
        // deux identifiants entre eux — c'est tout ce qu'on peut faire sans
        // clé, et cela ne vaut que le temps de la transition : les deux valeurs
        // viennent du même corps de requête, donc du même appelant.
        $company = self::entrepriseDeLaRequete($request);

        if ($company
            && ((int) $company->id !== (int) $request->comptaflow_company_id
                || (int) $company->selflow_company_id !== (int) $request->selflow_company_id)) {
            $company = null;
        }

        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => "Aucune liaison entre l'entreprise Selflow n° {$request->selflow_company_id} "
                    . "et l'entreprise Comptaflow n° {$request->comptaflow_company_id}. "
                    . 'Établissez la liaison avant de déverser le référentiel.',
            ], 404);
        }

        $comptes  = ['crees' => 0, 'completes' => 0, 'inchanges' => 0];
        $journaux = ['crees' => 0, 'completes' => 0, 'inchanges' => 0];
        $tiers    = ['crees' => 0, 'completes' => 0, 'inchanges' => 0];
        $refus    = [];

        DB::beginTransaction();
        try {
            foreach ((array) $request->input('plan_comptable', []) as $ligne) {
                self::recevoirUnCompte($company, (array) $ligne, $comptes, $refus);
            }

            foreach ((array) $request->input('codes_journaux', []) as $ligne) {
                self::recevoirUnJournal($company, (array) $ligne, $journaux, $refus);
            }

            foreach ((array) $request->input('tiers', []) as $ligne) {
                self::recevoirUnTiers($company, (array) $ligne, $tiers, $refus);
            }

            self::daterLaReception($company);

            DB::commit();

            // Le compte rendu dit ce qui a été créé, ce qui a été complété et
            // ce qui a été écarté. Une synchronisation qui annonce « succès »
            // en ayant laissé la moitié des lignes de côté est pire qu'un
            // échec : elle installe une confiance fausse.
            return response()->json([
                'success'  => true,
                'comptes'  => $comptes['crees'] + $comptes['completes'] + $comptes['inchanges'],
                'journaux' => $journaux['crees'] + $journaux['completes'] + $journaux['inchanges'],
                'tiers'    => $tiers['crees'] + $tiers['completes'] + $tiers['inchanges'],
                'detail'   => [
                    'plan_comptable' => $comptes,
                    'codes_journaux' => $journaux,
                    'tiers'          => $tiers,
                ],
                'refus'    => $refus,
                'message'  => sprintf(
                    '%d compte(s), %d journal(aux) et %d tiers reçus : %d créé(s), %d complété(s), %d déjà conforme(s)%s.',
                    $comptes['crees'] + $comptes['completes'] + $comptes['inchanges'],
                    $journaux['crees'] + $journaux['completes'] + $journaux['inchanges'],
                    $tiers['crees'] + $tiers['completes'] + $tiers['inchanges'],
                    $comptes['crees'] + $journaux['crees'] + $tiers['crees'],
                    $comptes['completes'] + $journaux['completes'] + $tiers['completes'],
                    $comptes['inchanges'] + $journaux['inchanges'] + $tiers['inchanges'],
                    $refus ? ', ' . count($refus) . ' ligne(s) écartée(s)' : ''
                ),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ExternalSync deverserReferentiel error', [
                'company_id' => $company->id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déversement du référentiel : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Un compte du plan de Selflow.
     *
     * Les champs reprennent les colonnes du modèle d'import
     * (`modele_plan_comptable.xlsx` : « N° compte » ; « Intitulé du compte »),
     * délibérément : le déversement passe par la même logique que l'import,
     * pas par une seconde voie à maintenir en parallèle.
     */
    private static function recevoirUnCompte($company, array $ligne, array &$compteur, array &$refus): void
    {
        $numero   = trim((string) ($ligne['numero_de_compte'] ?? ''));
        $intitule = trim((string) ($ligne['intitule'] ?? ''));

        if ($numero === '') {
            $refus[] = 'Compte sans numéro : ' . ($intitule ?: 'ligne vide');
            return;
        }

        $existant = PlanComptable::where('company_id', $company->id)
            ->where('numero_de_compte', $numero)
            ->first();

        if (!$existant) {
            $cree = self::compteGeneral($company, $numero, $intitule, 'imported');
            self::completer($cree, ['numero_original' => $ligne['numero_original'] ?? null]);
            $compteur['crees']++;
            return;
        }

        // Le compte existe : son intitulé et son type appartiennent au
        // comptable. On ne remplit que ce qui est resté vide.
        $modifie = self::completer($existant, [
            'numero_original' => $ligne['numero_original'] ?? null,
            'type_de_compte'  => self::typeDeCompte($numero),
            'classe'          => is_numeric(substr($numero, 0, 1)) ? substr($numero, 0, 1) : null,
        ]);

        $modifie ? $compteur['completes']++ : $compteur['inchanges']++;
    }

    /**
     * Un code journal de Selflow.
     *
     * Colonnes du modèle `modele_codes_journaux.xlsx` : « Code » ; « Intitulé » ;
     * « Type ». `compte_numero` porte le compte de trésorerie des journaux de
     * banque et de caisse.
     */
    private static function recevoirUnJournal($company, array $ligne, array &$compteur, array &$refus): void
    {
        $code     = strtoupper(trim((string) ($ligne['code_journal'] ?? '')));
        $intitule = trim((string) ($ligne['intitule'] ?? ''));
        $type     = trim((string) ($ligne['type'] ?? ''));

        if ($code === '') {
            $refus[] = 'Journal sans code : ' . ($intitule ?: 'ligne vide');
            return;
        }

        // Le compte de trésorerie, s'il est annoncé. Le plan comptable vient
        // d'être déversé : il y est déjà, sauf numérotation exotique.
        $compteTresorerie = null;
        $compteNumero = trim((string) ($ligne['compte_numero'] ?? ''));
        if ($compteNumero !== '') {
            $compteTresorerie = self::compteGeneral($company, $compteNumero, $intitule, 'imported')->id;
        }

        $existant = CodeJournal::where('company_id', $company->id)
            ->where('code_journal', $code)
            ->first();

        if (!$existant) {
            CodeJournal::create([
                'code_journal'          => $code,
                'numero_original'      => $ligne['numero_original'] ?? null,
                'intitule'             => $intitule ?: $code,
                'type'                 => $type ?: 'Opérations Diverses',
                'compte_de_tresorerie' => $compteTresorerie,
                'traitement_analytique' => false,
                'user_id'              => $company->user_id,
                'company_id'           => $company->id,
            ]);
            $compteur['crees']++;
            return;
        }

        // Le journal de Comptaflow fait foi — c'est sa configuration d'origine.
        $modifie = self::completer($existant, [
            'numero_original'      => $ligne['numero_original'] ?? null,
            'type'                 => $type ?: null,
            'compte_de_tresorerie' => $compteTresorerie,
        ]);

        $modifie ? $compteur['completes']++ : $compteur['inchanges']++;
    }

    /**
     * Un tiers de Selflow.
     *
     * Colonnes du modèle `modele_plan_tiers.xlsx` : « N° tiers » ; « Intitulé
     * du tiers » ; « Type ». Selflow transmet en plus `compte_general` — le
     * numéro du compte collectif — et `informations`, tout ce qui n'est pas
     * comptable.
     */
    private static function recevoirUnTiers($company, array $ligne, array &$compteur, array &$refus): void
    {
        $numero   = strtoupper(trim((string) ($ligne['numero_de_tiers'] ?? '')));
        $intitule = trim((string) ($ligne['intitule'] ?? ''));
        $type     = trim((string) ($ligne['type_de_tiers'] ?? ''));

        if ($numero === '' || $intitule === '') {
            $refus[] = 'Tiers incomplet : ' . ($numero ?: '(sans numéro)') . ' ' . ($intitule ?: '(sans intitulé)');
            return;
        }

        $compteGeneralId = self::compteGeneralDuTiers($company, $ligne['compte_general'] ?? null, $numero, $type);

        // `informations` — téléphone, adresse, courriel, NCC, RCCM, régime —
        // ne passe aucun contrôle : rien de comptable n'en dépend. Un champ
        // vide n'est pas transmis, il écraserait ce que Comptaflow détient
        // peut-être déjà.
        $informations = self::informationsDuTiers((array) ($ligne['informations'] ?? []));

        $existant = PlanTiers::where('company_id', $company->id)
            ->where('numero_de_tiers', $numero)
            ->first();

        if (!$existant) {
            PlanTiers::create(array_merge([
                'numero_de_tiers' => $numero,
                'numero_original' => $ligne['numero_original'] ?? null,
                'intitule'        => mb_strtoupper($intitule),
                'type_de_tiers'   => $type ?: 'Autre',
                'compte_general'  => $compteGeneralId,
                'user_id'         => $company->user_id,
                'company_id'      => $company->id,
            ], $informations));
            $compteur['crees']++;
            return;
        }

        // La fiche du comptable fait foi : on complète, on ne réécrit pas.
        $modifie = self::completer($existant, array_merge([
            'numero_original' => $ligne['numero_original'] ?? null,
            'compte_general'  => $compteGeneralId,
            'type_de_tiers'   => $type ?: null,
        ], $informations));

        $modifie ? $compteur['completes']++ : $compteur['inchanges']++;
    }

    /**
     * Le compte collectif d'un tiers.
     *
     * Selflow le transmet — c'est précisément ce qui manquait à
     * `MasterTiersImport`, dont l'import échouait sur la contrainte
     * d'intégrité. À défaut, on le déduit du préfixe du numéro de tiers, puis
     * de son type, comme le fait déjà `linkCompany`.
     */
    private static function compteGeneralDuTiers($company, ?string $numeroCompte, string $numeroTiers, ?string $type): ?int
    {
        $numeroCompte = trim((string) $numeroCompte);

        if ($numeroCompte !== '') {
            return self::compteGeneral($company, $numeroCompte, '', 'imported')->id;
        }

        $prefixe = self::prefixeCollectif($numeroTiers, $type);

        if (!$prefixe) {
            return null;
        }

        return PlanComptable::where('company_id', $company->id)
            ->where('numero_de_compte', 'like', $prefixe . '%')
            ->orderBy('numero_de_compte')
            ->value('id');
    }

    /**
     * Le compte collectif déduit d'un numéro de tiers, ou de son type :
     * `401…` fournisseurs, `410…` / `411…` clients.
     */
    private static function prefixeCollectif(string $numeroTiers, ?string $type): ?string
    {
        if (str_starts_with($numeroTiers, '401')) {
            return '401';
        }

        if (str_starts_with($numeroTiers, '410') || str_starts_with($numeroTiers, '411')) {
            return '411';
        }

        $type = strtolower(trim((string) $type));

        if (str_contains($type, 'fourn')) {
            return '401';
        }

        if (str_contains($type, 'client')) {
            return '411';
        }

        return null;
    }

    /**
     * Les informations non comptables d'un tiers, ramenées aux colonnes de
     * `plan_tiers`. Ce que l'on ne sait pas ranger est ignoré : `informations`
     * est un dépôt libre côté Selflow, et rien de comptable n'en dépend.
     */
    private static function informationsDuTiers(array $informations): array
    {
        $alias = [
            'telephone'           => 'telephone',
            'tel'                 => 'telephone',
            'phone'               => 'telephone',
            'email'               => 'email',
            'courriel'            => 'email',
            'mail'                => 'email',
            'adresse'             => 'adresse',
            'address'             => 'adresse',
            'ncc'                 => 'ncc',
            'rccm'                => 'rccm',
            'compte_contribuable' => 'compte_contribuable',
            'regime'              => 'regime',
            'regime_imposition'   => 'regime',
        ];

        $colonnes = [];

        foreach ($informations as $cle => $valeur) {
            if (!is_scalar($valeur)) {
                continue;
            }

            $valeur = trim((string) $valeur);

            if ($valeur === '') {
                continue;
            }

            $normalisee = strtolower(trim((string) $cle));

            if (isset($alias[$normalisee])) {
                $colonnes[$alias[$normalisee]] = $valeur;
            }
        }

        return $colonnes;
    }

    /**
     * Remplit les champs restés vides d'une ligne existante, et **ceux-là
     * seuls**.
     *
     * C'est la règle du déversement : ce que le comptable a saisi chez
     * Comptaflow lui appartient, ce qui manque peut venir de Selflow.
     *
     * @return bool vrai si quelque chose a été écrit
     */
    private static function completer($modele, array $valeurs): bool
    {
        $aRemplir = [];

        foreach ($valeurs as $colonne => $valeur) {
            if ($valeur === null || $valeur === '') {
                continue;
            }

            $actuel = $modele->{$colonne};

            if ($actuel === null || $actuel === '') {
                $aRemplir[$colonne] = $valeur;
            }
        }

        if (!$aRemplir) {
            return false;
        }

        $modele->fill($aRemplir)->save();

        return true;
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


