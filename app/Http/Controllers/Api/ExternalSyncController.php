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
     * Crée une entreprise + un administrateur depuis une requête externe (ex : Selflow).
     * POST /api/external/register-enterprise
     */
    public function registerEnterprise(Request $request)
    {
        // ── Vérification du secret partagé ──
        $expectedSecret = config('external_sync.external_sync_secret', 'selflow-comptaflow-secret-2026');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');

        if ($providedSecret !== $expectedSecret) {
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
        if ($providedSecret !== $expectedSecret) {
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
        $expectedSecret = config('external_sync.external_sync_secret', 'selflow-comptaflow-secret-2026');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');
        if ($providedSecret !== $expectedSecret) {
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
                ->select('id', 'numero_de_tiers', 'intitule', 'type_de_tiers', 'numero_original')
                ->get();

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
        $expectedSecret = config('external_sync.external_sync_secret', 'selflow-comptaflow-secret-2026');
        $providedSecret = $request->input('secret') ?? $request->header('X-Sync-Secret');
        if ($providedSecret !== $expectedSecret) {
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

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($request->ecritures as $ec) {
                $refPiece = $ec['reference_document'] ?? '';
                $libelle = $ec['libelle'] ?? '';
                $debitVal = $ec['debit'] ?? 0;
                $creditVal = $ec['credit'] ?? 0;

                // Trouver le journal
                $cjCode = $ec['code_journal'] ?? 'VTE';
                $codeJournal = CodeJournal::where('company_id', $company->id)
                    ->where('code_journal', $cjCode)
                    ->first() ?? CodeJournal::where('company_id', $company->id)->first();

                if (!$codeJournal) {
                    continue;
                }

                // Déterminer le compte à utiliser
                $accountCode = !empty($ec['compte_debit']) ? $ec['compte_debit'] : $ec['compte_credit'];
                if (empty($accountCode)) continue;

                $planComptableId = null;
                $planTiersId = null;

                // 1. Chercher dans PlanTiers
                $planTiers = PlanTiers::where('company_id', $company->id)
                    ->where('numero_de_tiers', $accountCode)
                    ->first();

                if ($planTiers) {
                    $planTiersId = $planTiers->id;
                    $planComptableId = $planTiers->compte_general;
                } else {
                    // 2. Chercher dans PlanComptable
                    $planComptable = PlanComptable::where('company_id', $company->id)
                        ->where('numero_de_compte', $accountCode)
                        ->first();

                    if (!$planComptable) {
                        $planComptable = PlanComptable::create([
                            'numero_de_compte' => $accountCode,
                            'intitule'         => $libelle ?: 'Compte ' . $accountCode,
                            'company_id'       => $company->id,
                            'user_id'          => $company->user_id,
                            'type_de_compte'   => 'actif',
                        ]);
                    }
                    $planComptableId = $planComptable->id;
                }

                // Créer l'écriture dans COMPTAFLOW
                EcritureComptable::create([
                    'company_id'              => $company->id,
                    'user_id'                 => $company->user_id,
                    'exercices_comptables_id' => $exercice->id,
                    'code_journal_id'         => $codeJournal->id,
                    'date'                    => $ec['date_ecriture'],
                    'description_operation'   => $libelle,
                    'reference_piece'         => $refPiece,
                    'n_saisie'                => $refPiece ?: 'SELF_' . time() . '_' . $count,
                    'plan_comptable_id'       => $planComptableId,
                    'plan_tiers_id'           => $planTiersId,
                    'debit'                   => $debitVal,
                    'credit'                  => $creditVal,
                    'statut'                  => 'approved',
                ]);

                $count++;
            }

            DB::commit();
            return response()->json(['success' => true, 'count' => $count, 'message' => "$count écritures déversées avec succès."]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ExternalSync deverserEcritures error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du déversement : ' . $e->getMessage()], 500);
        }
    }
}


