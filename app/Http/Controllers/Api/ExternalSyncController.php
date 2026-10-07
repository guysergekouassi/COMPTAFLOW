<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\AdminConfigController;
use App\Jobs\ImportCommitJob;
use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ImportStaging;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\User;
use App\Services\UniformisationImport;
use App\Services\NumerotationSaisie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $brut     = trim($numero);
        $uniforme = UniformisationImport::numeroDeCompte($brut, UniformisationImport::chiffresDeCompte($company));

        // Un numéro sans un seul chiffre n'est pas uniformisable ; on le range
        // tel quel plutôt que de perdre la ligne.
        if ($uniforme === '') {
            $uniforme = strtoupper($brut);
        }

        $existant = UniformisationImport::compte($company, $brut);

        // Le plan de Comptaflow fait foi : un compte déjà là n'est jamais
        // réécrit, ni son intitulé ni son type. C'est sa configuration
        // d'origine, et Selflow n'a pas à la corriger.
        if ($existant) {
            return $existant;
        }

        return PlanComptable::create(array_filter([
            'numero_de_compte' => $uniforme,
            // Le numéro tel que Selflow le nomme, rangé **sous** le nôtre.
            // C'est lui qui retrouvera le compte quand les écritures
            // arriveront, puisqu'elles désignent les comptes à la manière de
            // Selflow et non à la nôtre.
            'numero_original'  => $brut !== $uniforme ? $brut : null,
            'intitule'         => $libelle ?: 'Compte ' . $uniforme,
            'company_id'       => $company->id,
            'user_id'          => $company->user_id,
            'type_de_compte'   => self::typeDeCompte($uniforme),
            // La classe est le premier chiffre du numéro : la renseigner ici
            // évite qu'un compte venu de Selflow reste hors de tout état.
            'classe'           => is_numeric(substr($uniforme, 0, 1)) ? substr($uniforme, 0, 1) : null,
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

        // Cherché d'abord sur `numero_original` : Comptaflow régénère les
        // numéros de tiers à sa convention, et le numéro que Selflow porte
        // dans ses écritures n'est plus celui rangé dans `numero_de_tiers`.
        // Sans ce détour, aucune écriture ne retrouvait son tiers et toutes
        // retombaient sur le compte collectif — le défaut même que la
        // transmission du tiers avait corrigé.
        return UniformisationImport::tiers($company, $numeroTiers)?->id;
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
     * Le repli sur `selflow_company_id` a été retiré. C'était une **tolérance
     * de transition**, le temps que les deux applications soient déployées
     * ensemble, et elle rendait vaine la clé elle-même : le corps de la requête
     * redevenait la source, c'est-à-dire que l'appelant désignait lui-même les
     * livres dans lesquels il écrit. Elle est tombée avec ses trois jumelles.
     *
     * Rendre `null` ici n'est plus qu'un cas de défense en profondeur : les
     * deux routes de déversement passent par `cle.entreprise`, qui refuse déjà
     * en 401 (Unauthorized — non authentifié) l'appel sans en-tête.
     */
    private static function entrepriseDeLaRequete(Request $request): ?Company
    {
        $entreprise = $request->attributes->get('entreprise_liee');

        return $entreprise instanceof Company ? $entreprise : null;
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
            // Selflow déverse désormais une **opération entière** en un appel,
            // et non ligne par ligne. Quand il le dit, une seule ligne refusée
            // fait refuser le tout : une opération à moitié chez nous est un
            // débit sans son crédit, et une balance qui ne balance plus.
            'atomique'           => 'sometimes|boolean',
            'operation'          => 'sometimes|nullable|string|max:100',
        ]);

        $atomique = $request->boolean('atomique');

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

        // ── L'équilibre, vérifié à la réception ──
        //
        // Selflow le contrôle avant d'envoyer ; on le refait ici, parce
        // qu'une opération qui ne tombe pas, une fois rangée, ne se retrouve
        // qu'à la balance — et que nous n'avons pas la pièce d'origine pour
        // la comprendre. Seule une opération entière se juge ainsi : un envoi
        // ligne à ligne n'est pas censé tomber.
        if ($atomique) {
            $totalDebit  = round(collect($request->ecritures)->sum(fn ($ec) => (float) ($ec['debit'] ?? 0)), 2);
            $totalCredit = round(collect($request->ecritures)->sum(fn ($ec) => (float) ($ec['credit'] ?? 0)), 2);

            if (abs($totalDebit - $totalCredit) >= 0.01) {
                return response()->json([
                    'success' => false,
                    'count'   => 0,
                    'refus'   => [sprintf('opération déséquilibrée : débit %.2f, crédit %.2f', $totalDebit, $totalCredit)],
                    'message' => 'Opération refusée en entier : ses débits n\'égalent pas ses crédits. Rien n\'a été enregistré.',
                ], 422);
            }
        }

        $count = 0;
        $ignorees = 0;
        $refus = [];

        DB::beginTransaction();
        try {
            // ── Une opération, un numéro de saisie ──
            //
            // `n_saisie` recevait la `cle_selflow` de chaque ligne : unique
            // par ligne, il faisait de chaque ligne sa propre saisie. Une
            // facture de vente arrivait en quatre saisies d'une ligne chacune,
            // aucune équilibrée, et l'écran des saisies comme la copie — qui
            // regroupent par `n_saisie` — les traitaient comme quatre pièces.
            // Les lignes d'une même opération partagent désormais un numéro,
            // attribué ici, à notre convention : Selflow envoie, nous
            // numérotons. `cle_selflow` reste la clé d'idempotence de la ligne.
            $numeroDeSaisie = $request->filled('operation')
                ? self::numeroDeSaisieDeLOperation($company, $exercice, $request->ecritures)
                : null;

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
                //
                // Le code est cherché à la convention du dossier **puis** sur
                // le code d'origine : `VTE` de Selflow est rangé `VTE0` dans un
                // dossier à quatre caractères, et seul `numero_original` fait
                // le lien. Sans lui, tout un déversement se refusait sur
                // « journal inconnu » dès que les deux conventions différaient.
                $cjCode = $ec['code_journal'] ?? null;
                $codeJournal = $cjCode
                    ? UniformisationImport::journal($company, $cjCode)
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

                // Une ligne, un compte. Deux comptes sur une ligne : nous n'en
                // lirions qu'un, et l'autre se perdrait sans bruit.
                if (!empty($ec['compte_debit']) && !empty($ec['compte_credit'])) {
                    $refus[] = ($refPiece ?: '?') . ' : deux comptes sur une même ligne';
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
                    'n_saisie'                => $numeroDeSaisie ?: ($cleSelflow ?: ($refPiece ?: 'SELF_' . time() . '_' . $count)),
                    'cle_selflow'             => $cleSelflow,
                    'plan_comptable_id'       => $planComptable->id,
                    'plan_tiers_id'           => $planTiersId,
                    'debit'                   => $debitVal,
                    'credit'                  => $creditVal,
                    'statut'                  => 'approved',
                ]);

                $count++;
            }

            /*
             * L'opération passe entière, ou pas du tout.
             *
             * Selflow envoyait ses écritures une par une : si la ligne du
             * client passait et que celle de la vente était refusée — journal
             * inconnu, compte absent —, nous gardions un débit sans son
             * crédit. Rien ne recollait les morceaux, et la balance mentait.
             *
             * Depuis qu'il envoie l'opération d'un bloc, un refus partiel est
             * une raison suffisante de tout annuler : mieux vaut une opération
             * absente, que la reprise de Selflow repassera, qu'une opération
             * boiteuse que personne ne verra.
             */
            if ($atomique && $refus) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'count'   => 0,
                    'refus'   => $refus,
                    'message' => "Opération refusée en entier : " . count($refus)
                        . " ligne(s) sur " . count($request->ecritures)
                        . " n'ont pas pu être rangées. Rien n'a été enregistré.",
                ], 422);
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
     * Le numéro de saisie d'une opération déversée.
     *
     * - Une ligne de l'opération déjà rangée sous un numéro commun : on le
     *   reprend, pour qu'un renvoi complète la saisie au lieu d'en ouvrir une
     *   seconde.
     * - Sinon, un numéro neuf, du générateur ordinaire.
     *
     * Les lignes reçues **sous l'ancienne forme** — une saisie par ligne, le
     * numéro égal à leur `cle_selflow` — sont rangées au passage sous ce
     * numéro. C'est ce qui rend le rejeu sûr (chantier 7.4 du plan Selflow) :
     * renvoyer une opération déjà reçue n'ajoute rien, puisque chaque ligne
     * est reconnue par sa clé, mais la regroupe.
     */
    private static function numeroDeSaisieDeLOperation(Company $company, $exercice, array $lignes): string
    {
        $cles = collect($lignes)->pluck('cle_selflow')->filter()->values()->all();

        $dejaLa = $cles === [] ? collect() : EcritureComptable::where('company_id', $company->id)
            ->whereIn('cle_selflow', $cles)
            ->get(['id', 'cle_selflow', 'n_saisie']);

        $numero = $dejaLa->first(fn ($e) => $e->n_saisie && $e->n_saisie !== $e->cle_selflow)?->n_saisie
            ?? NumerotationSaisie::global($company->id, $exercice->id);

        $ancienneForme = $dejaLa->filter(fn ($e) => $e->n_saisie === $e->cle_selflow)->pluck('id')->all();

        if ($ancienneForme !== []) {
            EcritureComptable::whereIn('id', $ancienneForme)->update(['n_saisie' => $numero]);
        }

        return $numero;
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

        // L'import de Comptaflow travaille au nom d'un utilisateur du dossier :
        // c'est lui que l'historique des imports nommera.
        $admin = User::find($company->user_id)
            ?? User::where('company_id', $company->id)->where('role', 'admin')->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => "Le dossier Comptaflow n° {$company->id} n'a pas d'administrateur : "
                    . "l'import ne peut pas travailler en son nom.",
            ], 422);
        }

        $refus = [];

        // ── Un déversement est un import ──
        //
        // Le référentiel entrait par une voie à part, qui rangeait les numéros
        // tels que Selflow les nomme, puis par une copie de la normalisation
        // qui donnait `VTE0` là où l'import de Comptaflow donne `VTE1`. Il passe
        // maintenant par l'import lui-même : même préparation, mêmes codes
        // générés, même dédoublonnage, même rangement du numéro d'origine.
        //
        // L'ordre compte, comme à l'écran : un journal de trésorerie renvoie à
        // son compte, et un tiers à son compte collectif.
        try {
            $bilan = [
                'plan_comptable' => self::importer($company, $admin, 'initial',
                    ['numero_de_compte', 'intitule'],
                    array_map(fn ($l) => [
                        trim((string) ($l['numero_de_compte'] ?? '')),
                        trim((string) ($l['intitule'] ?? '')),
                    ], (array) $request->input('plan_comptable', [])),
                    $refus),

                // Le type est détecté par l'import, comme sur l'écran où il est
                // marqué « auto-généré par défaut ».
                'codes_journaux' => self::importer($company, $admin, 'journals',
                    ['code_journal', 'intitule', 'type', 'compte_de_tresorerie'],
                    array_map(fn ($l) => [
                        strtoupper(trim((string) ($l['code_journal'] ?? ''))),
                        trim((string) ($l['intitule'] ?? '')),
                        trim((string) ($l['type'] ?? '')),
                        trim((string) ($l['compte_numero'] ?? '')),
                    ], (array) $request->input('codes_journaux', [])),
                    $refus, ['type']),

                // Seuls les champs nécessaires passent par l'import : le numéro,
                // l'intitulé, le compte collectif. La catégorie se déduit du
                // préfixe, comme à l'écran.
                'tiers' => self::importer($company, $admin, 'tiers',
                    ['numero_de_tiers', 'intitule', 'compte_general'],
                    array_map(fn ($l) => [
                        strtoupper(trim((string) ($l['numero_de_tiers'] ?? ''))),
                        trim((string) ($l['intitule'] ?? '')),
                        trim((string) ($l['compte_general'] ?? '')),
                    ], (array) $request->input('tiers', [])),
                    $refus),
            ];

            // Le reste de la fiche d'un tiers ne demande aucune vérification.
            self::completerLesTiers($company, (array) $request->input('tiers', []));

            self::daterLaReception($company);
        } catch (\Throwable $e) {
            Log::error('ExternalSync deverserReferentiel error', [
                'company_id' => $company->id,
                'error'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déversement du référentiel : ' . $e->getMessage(),
            ], 500);
        }

        $total = fn (string $cle) => array_sum(array_column($bilan, $cle));

        // Le compte rendu dit ce qui est entré, ce qui était déjà là et ce qui
        // a été écarté. Une synchronisation qui annonce « succès » en ayant
        // laissé la moitié des lignes de côté est pire qu'un échec.
        return response()->json([
            'success'  => true,
            'comptes'  => $bilan['plan_comptable']['recues'],
            'journaux' => $bilan['codes_journaux']['recues'],
            'tiers'    => $bilan['tiers']['recues'],
            'detail'   => $bilan,
            'refus'    => $refus,
            'message'  => sprintf(
                '%d compte(s), %d journal(aux) et %d tiers reçus par l\'import : %d créé(s), %d déjà présent(s)%s.',
                $bilan['plan_comptable']['recues'],
                $bilan['codes_journaux']['recues'],
                $bilan['tiers']['recues'],
                $total('creees'),
                $total('deja_la'),
                $refus ? ', ' . count($refus) . ' ligne(s) écartée(s)' : ''
            ),
        ]);
    }

    /**
     * Faire passer des lignes par l'import de Comptaflow.
     *
     * Rien n'est réécrit ici : on dépose les lignes comme l'écran d'import les
     * dépose, avec la correspondance des colonnes que l'écran ferait choisir —
     * elle ne change jamais, puisque Selflow envoie toujours les mêmes champs.
     * Puis `ImportCommitJob` fait ce qu'il fait pour un fichier.
     *
     * La préparation est lue une fois de notre côté, pour **dire** ce que
     * l'import écarte et pourquoi. Le job ne le rapporte pas ligne à ligne, et
     * une ligne écartée sans motif est une ligne perdue.
     *
     * @param list<string> $colonnes les champs de l'import, dans l'ordre des colonnes
     * @param list<list<string>> $lignes
     * @param list<string> $auto les champs laissés à la détection de l'import
     * @return array{recues: int, creees: int, deja_la: int, ecartees: int}
     */
    private static function importer(Company $company, User $admin, string $type, array $colonnes,
        array $lignes, array &$refus, array $auto = []): array
    {
        $lignes = array_values(array_filter($lignes, fn ($l) => ($l[0] ?? '') !== '' || ($l[1] ?? '') !== ''));

        if ($lignes === []) {
            return ['recues' => 0, 'creees' => 0, 'deja_la' => 0, 'ecartees' => 0];
        }

        $mapping = ['_header_index' => 0];
        foreach ($colonnes as $index => $champ) {
            $mapping[$champ] = in_array($champ, $auto, true) ? 'AUTO' : $index;
        }

        $import = ImportStaging::create([
            'company_id' => $company->id,
            'user_id'    => $admin->id,
            'source'     => 'SELFLOW',
            'type'       => $type,
            'file_name'  => 'Déversement Selflow',
            'raw_data'   => array_merge([$colonnes], $lignes),
            'mapping'    => $mapping,
            'status'     => 'staging',
        ]);

        Auth::setUser($admin);
        session()->put('current_company_id', $company->id);

        $apercu  = app(AdminConfigController::class)->importStaging($import->id, true);
        $statuts = collect($apercu['rowsWithStatus'] ?? []);

        foreach ($statuts->where('status', 'error') as $ligne) {
            $source = $lignes[((int) $ligne['index']) - 1] ?? [];
            $refus[] = sprintf('%s « %s » : %s', self::NOMS_D_IMPORT[$type] ?? $type,
                trim(($source[0] ?? '') . ' ' . ($source[1] ?? '')),
                implode(' ', (array) $ligne['errors']));
        }

        ImportCommitJob::dispatchSync($import->id, $admin->id);

        $rapport = $import->fresh()->metadata['commit_report'] ?? [];

        if (($rapport['status'] ?? '') === 'error') {
            foreach ((array) ($rapport['errors'] ?? []) as $erreur) {
                $refus[] = (self::NOMS_D_IMPORT[$type] ?? $type) . ' : ' . $erreur;
            }
        }

        return [
            'recues'   => count($lignes),
            'creees'   => ($rapport['status'] ?? '') === 'success' ? (int) ($rapport['processed_g'] ?? 0) : 0,
            'deja_la'  => $statuts->where('status', 'duplicate')->count(),
            'ecartees' => $statuts->where('status', 'error')->count(),
        ];
    }

    private const NOMS_D_IMPORT = [
        'initial'  => 'Compte',
        'journals' => 'Journal',
        'tiers'    => 'Tiers',
    ];

    /**
     * Le reste de la fiche d'un tiers : téléphone, adresse, NCC, régime.
     *
     * Rien de comptable n'en dépend, et l'import ne le lit pas. On complète
     * ce qui est resté vide sur le tiers que l'import a rangé — retrouvé par
     * le numéro de Selflow, puisque l'import a régénéré le sien.
     */
    private static function completerLesTiers(Company $company, array $lignes): void
    {
        foreach ($lignes as $ligne) {
            $ligne = (array) $ligne;
            $numero = strtoupper(trim((string) ($ligne['numero_de_tiers'] ?? '')));
            $informations = self::informationsDuTiers((array) ($ligne['informations'] ?? []));

            if ($numero === '' || $informations === []) {
                continue;
            }

            $tiers = UniformisationImport::tiers($company, $numero);

            if ($tiers) {
                self::completer($tiers, $informations);
            }
        }
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


