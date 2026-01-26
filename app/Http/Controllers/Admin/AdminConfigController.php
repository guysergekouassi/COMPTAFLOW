<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ImportStaging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminConfigController extends Controller
{
    /**
     * Dashboard de configuration pour l'Administrateur
     */
    public function hub()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $mainCompany = Company::where('id', $companyId)->first();
        
        // Statistiques du modèle de référence
        $stats = [
            'accounts' => PlanComptable::where('company_id', $companyId)->count(),
            'tiers' => PlanTiers::where('company_id', $companyId)->count(),
            'journals' => CodeJournal::where('company_id', $companyId)->count(),
            'imported' => EcritureComptable::where('company_id', $companyId)
                ->where('statut', 'imported')
                ->count(),
        ];

        // Récupération de l'exercice actif
        $exerciceActif = \App\Models\ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', 1)
            ->first();

        return view('admin.config.hub', compact('mainCompany', 'stats', 'exerciceActif'));
    }

    /**
     * Mise à jour des paramètres globaux de l'entreprise
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'accounting_system' => 'required|string|in:SYSCOHADA,PCG,CUSTOM',
            'account_digits' => 'required|integer|min:4|max:12',
            'journal_code_digits' => 'nullable|integer|min:1|max:10',
            'journal_code_type' => 'nullable|string|in:alphabetical,alphanumeric,numeric',
            'tier_digits' => 'nullable|integer|min:4|max:15',
            'tier_id_type' => 'nullable|string|in:numeric,alphanumeric',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        
        $company->update([
            'accounting_system' => $request->accounting_system,
            'account_digits' => $request->account_digits,
            'journal_code_digits' => $request->journal_code_digits ?? $company->journal_code_digits,
            'journal_code_type' => $request->journal_code_type ?? $company->journal_code_type,
            'tier_digits' => $request->tier_digits ?? $company->tier_digits,
            'tier_id_type' => $request->tier_id_type ?? $company->tier_id_type,
        ]);

        return redirect()->route('admin.config.external_import')->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Gestion du Modèle de Plan Comptable
     */
    public function planComptable()
    {
        $user = Auth::user();
        $mainCompany = Company::findOrFail($user->company_id);
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->orderBy('numero_de_compte')
            ->get();
        
        $hasAccounts = $plansComptables->count() > 0;

        return view('admin.config.plan_comptable', compact('plansComptables', 'hasAccounts', 'mainCompany'));
    }

    /**
     * Gestion du Modèle de Plan Tiers
     */
    public function planTiers()
    {
        $user = Auth::user();
        $planTiers = PlanTiers::where('company_id', $user->company_id)
            ->orderBy('numero_de_tiers')
            ->get();
        
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->orderBy('numero_de_compte')
            ->get();

        $mainCompany = Company::findOrFail($user->company_id);
        
        return view('admin.config.plan_tiers', compact('planTiers', 'plansComptables', 'mainCompany'));
    }

    /**
     * Gestion de la Structure des Journaux
     */
    public function journals()
    {
        $user = Auth::user();
        $mainCompany = Company::findOrFail($user->company_id);
        $journals = CodeJournal::with('account')
            ->where('company_id', $user->company_id)
            ->orderBy('code_journal')
            ->get();
        
        // On récupère aussi le plan comptable pour les comptes de trésorerie
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->orderBy('numero_de_compte')
            ->get();

        return view('admin.config.journals', compact('journals', 'mainCompany', 'plansComptables'));
    }

    /**
     * Importation externe (Sage, etc.)
     */
    public function externalImport()
    {
        return view('admin.config.external_import');
    }

    /**
     * Charger les écritures importées (Passage de 'imported' à 'approved')
     */
    public function chargeImports()
    {
        $user = Auth::user();
        
        $importedCount = EcritureComptable::where('company_id', $user->company_id)
            ->where('statut', 'imported')
            ->count();

        if ($importedCount === 0) {
            return redirect()->route('admin.config.external_import')
                ->with('info', 'Aucune écriture importée en attente. Veuillez d\'abord importer un fichier.');
        }

        // Passage au statut approved pour toutes les écritures importées de la compagnie
        EcritureComptable::where('company_id', $user->company_id)
            ->where('statut', 'imported')
            ->update(['statut' => 'approved']);

        return redirect()->route('accounting_entry_list')
            ->with('success', "$importedCount écritures ont été chargées avec succès dans la liste principale.");
    }

    /**
     * Charger le Plan Comptable SYSCOHADA (4 chiffres)
     */
    /**
     * Charger le Plan Comptable SYSCOHADA (2-4 chiffres)
     */
    public function loadSyscohada4()
    {
        return $this->loadSyscohadaPlan('syscohada_24');
    }

    /**
     * Charger le Plan Comptable SAGE (6 chiffres)
     */
    public function loadSyscohada6()
    {
        return $this->loadSyscohadaPlan('sage_6');
    }

    /**
     * Charger le Plan Comptable DC-KNOWING (8 chiffres)
     */
    public function loadSyscohada8()
    {
        return $this->loadSyscohadaPlan('dc_knowing_8');
    }

    /**
     * Charger le Plan Comptable SYSCOHADA Standard (Interne)
     */
    private function loadSyscohadaPlan($mode = 'syscohada_24')
    {
        try {
            $user = Auth::user();
            $digits = 8; // Default for other logic if needed

            // CHARGEMENT DU PLAN COMPTABLE SYSCOHADA COMPLET
            // Source: config/syscohada_complet.php (870 comptes extraits de syscohada.txt)
            $syscohadaComplet = require config_path('syscohada_complet.php');
            
            if (empty($syscohadaComplet)) {
                return redirect()->back()->with('error', 'Erreur: Le fichier SYSCOHADA complet est introuvable ou vide.');
            }
            
            // Utiliser directement le référentiel complet
            $templates = $syscohadaComplet;

            // Tri par ordre croissant des clés (numéros de comptes)
            ksort($templates);

            DB::beginTransaction();
            $count = 0;
            foreach ($templates as $prefix => $intitule) {
                $numero = $prefix;

                if ($mode === 'sage_6') {
                    $numero = str_pad($prefix, 6, '0', STR_PAD_RIGHT);
                } elseif ($mode === 'dc_knowing_8') {
                    // Règle DC-KNOWING : [Préfixe SYSCOHADA] + '1' + '0' (padding à 8)
                    $numero = str_pad($prefix . '1', 8, '0', STR_PAD_RIGHT);
                } else {
                    // Mode SYSCOHADA (2-4) : on garde le numéro tel quel (longueur 2, 3 ou 4)
                    $numero = $prefix;
                }

                // Détection intelligente de la classe et du type
                $classe = (int)substr($prefix, 0, 1);
                $type = in_array($classe, [1, 2, 3, 4, 5, 9]) ? 'Bilan' : 'Compte de résultat';

                $exists = PlanComptable::where('company_id', $user->company_id)
                    ->where('numero_de_compte', $numero)
                    ->exists();

                if (!$exists) {
                    PlanComptable::create([
                        'numero_de_compte' => $numero,
                        'intitule' => mb_strtoupper($intitule),
                        'type_de_compte' => $type,
                        'classe' => $classe,
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'adding_strategy' => 'auto'
                    ]);
                    $count++;
                }
            }
            DB::commit();

            $modeNames = [
                'syscohada_24' => 'SYSCOHADA (2-4)',
                'sage_6' => 'COMPTES SAGE (6)',
                'dc_knowing_8' => 'COMPTES DC-KNOWING (8)'
            ];
            $modeName = $modeNames[$mode] ?? 'SYSCOHADA';

            return redirect()->route('admin.config.external_import')->with('success', "$count comptes chargés avec succès dans le format $modeName.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
        }
    }

    /**
     * Génération de Plan Comptable Personnalisé
     */
    public function generateCustomPlan(Request $request)
    {
        $request->validate([
            'digits' => 'required|integer|min:4|max:12',
            'system' => 'required|string|in:SYSCOHADA,PCG,CUSTOM',
            'sequential' => 'required|string|in:oui,non',
            'seq_prefix' => 'nullable|string',
            'seq_start' => 'nullable|integer|min:0|max:9',
            'seq_end' => 'nullable|integer|min:0|max:9',
        ]);

        try {
            $user = Auth::user();
            $digits = $request->digits;
            
            // On utilise les libellés SYSCOHADA par défaut (même pour CUSTOM comme demandé)
            $templates = [
                '1' => 'Comptes de capitaux propres',
                '2' => 'Comptes d\'immobilisations',
                '3' => 'Comptes de stocks',
                '4' => 'Comptes de tiers',
                '5' => 'Comptes de trésorerie',
                '6' => 'Comptes de charges des activités ordinaires',
                '7' => 'Comptes de produits des activités ordinaires',
                '8' => 'Comptes des autres charges et produits',
                '9' => 'Comptes des engagements hors bilan'
            ];

            DB::beginTransaction();
            $count = 0;
            
            foreach ($templates as $classPrefix => $intitule) {
                // Génération de base pour la classe
                $numeroBase = str_pad($classPrefix, $digits, '0', STR_PAD_RIGHT);
                
                if (!PlanComptable::where('company_id', $user->company_id)->where('numero_de_compte', $numeroBase)->exists()) {
                    PlanComptable::create([
                        'numero_de_compte' => $numeroBase,
                        'intitule' => "CLASSE $classPrefix - $intitule",
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'adding_strategy' => 'auto'
                    ]);
                    $count++;
                }

                // Si séquentiel, on génère une série
                if ($request->sequential === 'oui') {
                    $prefix = $classPrefix . ($request->seq_prefix ?? '');
                    for ($i = $request->seq_start; $i <= $request->seq_end; $i++) {
                        $numero = str_pad($prefix . $i, $digits, '0', STR_PAD_RIGHT);
                        
                        if (strlen($numero) <= $digits && !PlanComptable::where('company_id', $user->company_id)->where('numero_de_compte', $numero)->exists()) {
                            PlanComptable::create([
                                'numero_de_compte' => $numero,
                                'intitule' => "Compte $numero (Généré)",
                                'user_id' => $user->id,
                                'company_id' => $user->company_id,
                                'adding_strategy' => 'auto'
                            ]);
                            $count++;
                        }
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('admin.config.external_import')->with('success', "$count comptes générés avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur de génération : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser / Annuler le Plan Comptable
     */
    public function resetPlanComptable()
    {
        try {
            $user = Auth::user();
            PlanComptable::where('company_id', $user->company_id)->delete();
            return redirect()->route('admin.config.external_import')->with('success', 'Plan comptable réinitialisé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser / Annuler le Plan Tiers
     */
    public function resetPlanTiers()
    {
        try {
            $user = Auth::user();
            PlanTiers::where('company_id', $user->company_id)->delete();
            return redirect()->route('admin.config.external_import')->with('success', 'Plan tiers réinitialisé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser / Annuler les Journaux
     */
    public function resetJournals()
    {
        try {
            $user = Auth::user();
            CodeJournal::where('company_id', $user->company_id)->delete();
            return redirect()->route('admin.config.external_import')->with('success', 'Modèle de journaux réinitialisé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un nouveau compte Master
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'numero_de_compte' => 'required|string',
            'intitule' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        $digits = $company->account_digits ?? 8;

        // Validation stricte de la longueur
        if (strlen($request->numero_de_compte) != $digits) {
            return redirect()->back()->with('error', "Le numéro de compte doit comporter exactement $digits chiffres.");
        }

        $numero = $request->numero_de_compte;

        $exists = PlanComptable::where('company_id', $user->company_id)
            ->where('numero_de_compte', $numero)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le compte $numero existe déjà dans votre modèle.");
        }

        PlanComptable::create([
            'numero_de_compte' => $numero,
            'intitule' => $request->intitule,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'is_active' => true
        ]);

        return redirect()->route('admin.config.external_import')->with('success', 'Compte ajouté au modèle avec succès.');
    }

    /**
     * Importation des comptes via Excel/CSV
     */
    public function importAccounts(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            Excel::import(new \App\Imports\MasterPlanImport, $request->file('file'));
            return redirect()->route('admin.config.external_import')->with('success', 'Importation terminée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Charger les Journaux Standards
     */
    public function loadStandardJournals()
    {
        try {
            $user = Auth::user();
            
            $templates = [
                ['code' => 'ACH', 'intitule' => 'JOURNAL DES ACHATS', 'type' => 'Achats'],
                ['code' => 'VEN', 'intitule' => 'JOURNAL DES VENTES', 'type' => 'Ventes'],
                ['code' => 'BQ',  'intitule' => 'JOURNAL DE BANQUE', 'type' => 'Banque'],
                ['code' => 'CSH', 'intitule' => 'JOURNAL DE CAISSE', 'type' => 'Caisse'],
                ['code' => 'OD',  'intitule' => 'OPERATIONS DIVERSES', 'type' => 'Opérations Diverses'],
            ];

            DB::beginTransaction();
            $count = 0;
            foreach ($templates as $tpl) {
                $exists = CodeJournal::where('company_id', $user->company_id)
                    ->where('code_journal', $tpl['code'])
                    ->exists();

                if (!$exists) {
                    CodeJournal::create([
                        'code_journal' => $tpl['code'],
                        'intitule' => $tpl['intitule'],
                        'type' => $tpl['type'],
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                    ]);
                    $count++;
                }
            }
            DB::commit();

            return redirect()->route('admin.config.external_import')->with('success', "$count journaux standards chargés avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
        }
    }
    /**
     * Obtenir le prochain numéro de tiers disponible
     */
    public function getNextTierNumber(Request $request)
{
    try {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        $digits = (int)($company->tier_digits ?? 8);
        $type = $company->tier_id_type ?? 'numeric';
        
        $planComptableId = $request->input('plan_comptable_id');
        $intitule = $request->input('intitule', '');
        
        $planAccount = PlanComptable::find($planComptableId);
        
        if (!$planAccount) {
            return response()->json(['success' => false, 'message' => 'Compte collectif non trouvé.'], 400);
        }

        $accountNum = $planAccount->numero_de_compte;
        $nextId = "";

        if ($type === 'alphanumeric' && !empty($intitule)) {
            /**
             * LOGIQUE COMPTABLE PROFESSIONNELLE
             * Racine (3) + Abrégé (3) + Séquence
             */
            $root = substr($accountNum, 0, 3);
            
            // Nettoyage moderne avec Str::ascii
            $cleanIntitule = preg_replace('/[^A-Z0-9]/', '', Str::upper(Str::ascii($intitule)));
            $alphaPart = substr($cleanIntitule, 0, 3);
            $prefix = $root . $alphaPart;
            
            $seqLength = max(1, $digits - strlen($prefix));

            // Récupérer TOUS les tiers avec ce préfixe pour trouver le max numérique réel
            $existingTiers = PlanTiers::where('company_id', $user->company_id)
                ->where('numero_de_tiers', 'like', $prefix . '%')
                ->get();

            $maxSeq = 0;
            foreach ($existingTiers as $tier) {
                $suffix = substr($tier->numero_de_tiers, strlen($prefix));
                if (is_numeric($suffix)) {
                    $maxSeq = max($maxSeq, (int)$suffix);
                }
            }
            
            $seq = $maxSeq + 1;
            $nextId = $prefix . str_pad($seq, $seqLength, '0', STR_PAD_LEFT);
            
            // Sécurité : Si le résultat dépasse la longueur totale à cause d'une séquence trop grande
            if (strlen($nextId) > $digits && $seqLength > 0) {
                 $nextId = substr($nextId, 0, $digits);
            }

        } else {
            /**
             * LOGIQUE NUMÉRIQUE SÉQUENTIELLE
             */
            $base = $accountNum;
            $seqLength = max(1, $digits - strlen($base));

            $existingTiers = PlanTiers::where('company_id', $user->company_id)
                ->where('compte_general', $planComptableId)
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
            $nextId = $base . str_pad($seq, $seqLength, '0', STR_PAD_LEFT);
            
            if (strlen($nextId) > $digits) {
                $nextId = substr($nextId, 0, $digits);
            }
        }

        return response()->json([
            'success' => true,
            'next_id' => $nextId,
            'debug' => [
                'type' => $type,
                'digits' => $digits,
                'prefix_base' => ($type === 'alphanumeric' && isset($prefix)) ? $prefix : ($base ?? $accountNum ?? 'N/A')
            ]
        ]);

    } catch (\Exception $e) {
        Log::error("Erreur getNextTierNumber: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
    /**
     * Enregistrer un nouveau Tiers Master
     */
    public function storeTier(Request $request)
    {
        $request->validate([
            'numero_de_tiers' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'type_de_tiers' => 'required|string|in:Client,Fournisseur,Autre',
            'compte_general' => 'nullable|exists:plan_comptables,id',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        $digits = $company->tier_digits ?? 8;

        // On peut forcer la génération si le frontend a envoyé un numéro mais qu'on veut être sûr
        // ou si le numéro envoyé est vide.
        $num = strtoupper($request->numero_de_tiers);
        
        if (empty($num) || strlen($num) != $digits) {
             // Re-générer côté serveur par sécurité pour l'unicité
             $resp = $this->getNextTierNumber(new Request([
                'plan_comptable_id' => $request->compte_general,
                'intitule' => $request->intitule
             ]));
             $data = $resp->getData();
             if ($data->success) {
                 $num = $data->next_id;
             }
        }

        $exists = PlanTiers::where('company_id', $user->company_id)
            ->where('numero_de_tiers', $num)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le numéro de tiers {$num} existe déjà.");
        }

        PlanTiers::create([
            'numero_de_tiers' => $num,
            'intitule' => strtoupper($request->intitule),
            'type_de_tiers' => $request->type_de_tiers,
            'compte_general' => $request->compte_general,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);

        return redirect()->route('admin.config.external_import')->with('success', 'Tiers ajouté au modèle avec succès.');
    }

    /**
     * Enregistrer un nouveau Journal Master
     */
    public function storeJournal(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        $digits = $company->journal_code_digits ?? 3;
        $codeType = $company->journal_code_type ?? 'alphabetical';

        $request->validate([
            'code_journal' => 'required|string',
            'intitule' => 'required|string|max:255',
            'type' => 'required|string',
            'compte_de_tresorerie' => 'nullable|string',
            'poste_tresorerie' => 'nullable|string',
            'traitement_analytique' => 'nullable|string|in:oui,non',
            'rapprochement_sur' => 'nullable|string|in:Manuel,Automatique',
        ]);

        $code = strtoupper($request->code_journal);

        // Validation de la longueur
        if (strlen($code) != $digits) {
            return redirect()->back()->with('error', "Le code journal doit comporter exactement $digits caractères.");
        }

        // Validation du type de code
        if ($codeType == 'numeric' && !ctype_digit($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement numérique.");
        } elseif ($codeType == 'alphabetical' && !ctype_alpha($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement alphabétique.");
        }

        $exists = CodeJournal::where('company_id', $user->company_id)
            ->where('code_journal', $code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le code journal {$code} existe déjà.");
        }

        $compteId = null;
        if ($request->compte_de_tresorerie) {
            $compteId = PlanComptable::where('company_id', $user->company_id)
                ->where('numero_de_compte', $request->compte_de_tresorerie)
                ->value('id');
        }

        CodeJournal::create([
            'code_journal' => $code,
            'intitule' => strtoupper($request->intitule),
            'type' => $request->type,
            'compte_de_tresorerie' => $compteId,
            'compte_de_contrepartie' => $request->compte_de_contrepartie,
            'poste_tresorerie' => $request->poste_tresorerie,
            'traitement_analytique' => ($request->traitement_analytique === 'oui'),
            'rapprochement_sur' => $request->rapprochement_sur,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);

        return redirect()->route('admin.config.external_import')->with('success', 'Journal ajouté au modèle avec succès.');
    }

    /**
     * Importation des journaux via Excel/CSV
     */
    public function importJournals(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            Excel::import(new \App\Imports\MasterJournalImport, $request->file('file'));
            return redirect()->route('admin.config.external_import')->with('success', 'Importation des journaux terminée.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Importation des tiers via Excel/CSV
     */
    public function importTiers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            Excel::import(new \App\Imports\MasterTiersImport, $request->file('file'));
            return redirect()->route('admin.config.external_import')->with('success', 'Importation des tiers terminée.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Synchronisation du Plan Comptable (Charger depuis l'Admin)
     */
    public function syncPlanComptable(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id');
            
            if (!$currentCompanyId) {
                return response()->json(['success' => false, 'message' => 'Aucune entité active sélectionnée.'], 400);
            }

            $currentCompany = Company::find($currentCompanyId);
            $parentCompanyId = $currentCompany->parent_company_id ?? $user->company_id;

            if ($currentCompanyId == $parentCompanyId) {
                return response()->json(['success' => false, 'message' => 'Vous êtes déjà sur l\'entité parente.'], 400);
            }

            // Récupérer le plan de l'admin
            $adminPlans = PlanComptable::where('company_id', $parentCompanyId)->get();
            
            DB::beginTransaction();
            $count = 0;
            foreach ($adminPlans as $plan) {
                $exists = PlanComptable::where('company_id', $currentCompanyId)
                    ->where('numero_de_compte', $plan->numero_de_compte)
                    ->exists();
                
                if (!$exists) {
                    $newPlan = $plan->replicate();
                    $newPlan->company_id = $currentCompanyId;
                    $newPlan->user_id = $user->id;
                    $newPlan->save();
                    $count++;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "$count comptes ont été synchronisés avec succès."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Synchronisation des Tiers
     */
    public function syncPlanTiers(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id');
            
            $currentCompany = Company::find($currentCompanyId);
            $parentCompanyId = $currentCompany->parent_company_id ?? $user->company_id;

            $adminTiers = PlanTiers::where('company_id', $parentCompanyId)->get();
            
            DB::beginTransaction();
            $count = 0;
            foreach ($adminTiers as $tier) {
                $exists = PlanTiers::where('company_id', $currentCompanyId)
                    ->where('numero_de_tiers', $tier->numero_de_tiers)
                    ->exists();
                
                if (!$exists) {
                    $newTier = $tier->replicate();
                    $newTier->company_id = $currentCompanyId;
                    $newTier->save();
                    $count++;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "$count fiches tiers synchronisées."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Synchronisation des Journaux
     */
    public function syncJournals(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id');
            
            $currentCompany = Company::find($currentCompanyId);
            $parentCompanyId = $currentCompany->parent_company_id ?? $user->company_id;

            $adminJournals = CodeJournal::where('company_id', $parentCompanyId)->get();
            
            DB::beginTransaction();
            $count = 0;
            foreach ($adminJournals as $journal) {
                $exists = CodeJournal::where('company_id', $currentCompanyId)
                    ->where('code_journal', $journal->code_journal)
                    ->exists();
                
                if (!$exists) {
                    $newJournal = $journal->replicate();
                    $newJournal->company_id = $currentCompanyId;
                    $newJournal->save();
                    $count++;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "$count journaux synchronisés."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Suppression d'un compte du modèle
     */
    public function deleteAccount($id)
    {
        try {
            $user = Auth::user();
            $account = PlanComptable::where('company_id', $user->company_id)->findOrFail($id);
            $account->delete();
            return redirect()->route('admin.config.external_import')->with('success', 'Compte supprimé du modèle avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Suppression d'un tiers du modèle
     */
    public function deleteTier($id)
    {
        try {
            $user = Auth::user();
            $tier = PlanTiers::where('company_id', $user->company_id)->findOrFail($id);
            $tier->delete();
            return redirect()->route('admin.config.external_import')->with('success', 'Tiers supprimé du modèle avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Suppression d'un journal du modèle
     */
    public function deleteJournal($id)
    {
        try {
            $user = Auth::user();
            $journal = CodeJournal::where('company_id', $user->company_id)->findOrFail($id);
            $journal->delete();
            return redirect()->route('admin.config.external_import')->with('success', 'Journal supprimé du modèle avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour d'un compte du modèle
     */
    public function updateAccount(Request $request, $id)
    {
        $request->validate([
            'numero_de_compte' => 'required|string',
            'intitule' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $company = Company::findOrFail($user->company_id);
            $digits = $company->account_digits ?? 8;

            if (strlen($request->numero_de_compte) != $digits) {
                return redirect()->back()->with('error', "Le numéro de compte doit comporter exactement $digits chiffres.");
            }

            $account = PlanComptable::where('company_id', $user->company_id)->findOrFail($id);
            $account->update([
                'numero_de_compte' => $request->numero_de_compte,
                'intitule' => mb_strtoupper($request->intitule),
            ]);
            return redirect()->route('admin.config.external_import')->with('success', 'Compte mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour d'un tiers du modèle
     */
    public function updateTier(Request $request, $id)
    {
        $request->validate([
            'numero_de_tiers' => 'required|string',
            'intitule' => 'required|string',
            'type_de_tiers' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $tier = PlanTiers::where('company_id', $user->company_id)->findOrFail($id);
            $tier->update([
                'numero_de_tiers' => $request->numero_de_tiers,
                'intitule' => mb_strtoupper($request->intitule),
                'type_de_tiers' => $request->type_de_tiers,
                'compte_general' => $request->compte_general,
            ]);
            return redirect()->route('admin.config.external_import')->with('success', 'Tiers mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour d'un journal du modèle
     */
    public function updateJournal(Request $request, $id)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        $digits = $company->journal_code_digits ?? 3;
        $codeType = $company->journal_code_type ?? 'alphabetical';

        $request->validate([
            'code_journal' => 'required|string',
            'intitule' => 'required|string',
            'type' => 'required|string',
            'compte_de_tresorerie' => 'nullable|string',
            'compte_de_contrepartie' => 'nullable|string',
            'poste_tresorerie' => 'nullable|string',
            'traitement_analytique' => 'nullable|string|in:oui,non',
            'rapprochement_sur' => 'nullable|string|in:Manuel,Automatique',
        ]);

        $code = strtoupper($request->code_journal);

        // Validation de la longueur
        if (strlen($code) != $digits) {
            return redirect()->back()->with('error', "Le code journal doit comporter exactement $digits caractères.");
        }

        // Validation du type de code
        if ($codeType == 'numeric' && !ctype_digit($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement numérique.");
        } elseif ($codeType == 'alphabetical' && !ctype_alpha($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement alphabétique.");
        }

        try {
            $journal = CodeJournal::where('company_id', $user->company_id)->findOrFail($id);
            $compteId = null;
            if ($request->compte_de_tresorerie) {
                $compteId = PlanComptable::where('company_id', $user->company_id)
                    ->where('numero_de_compte', $request->compte_de_tresorerie)
                    ->value('id');
            }

            $journal->update([
                'code_journal' => $code,
                'intitule' => strtoupper($request->intitule),
                'type' => $request->type,
                'compte_de_tresorerie' => $compteId,
                'compte_de_contrepartie' => $request->compte_de_contrepartie,
                'poste_tresorerie' => $request->poste_tresorerie,
                'traitement_analytique' => ($request->traitement_analytique === 'oui'),
                'rapprochement_sur' => $request->rapprochement_sur,
            ]);
            return redirect()->route('admin.config.external_import')->with('success', 'Journal mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Tunnel d'Importation - Hub
     */
    public function importHub()
    {
        $user = Auth::user();
        $imports = ImportStaging::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.config.import_hub', compact('imports'));
    }

    /**
     * Tunnel d'Importation - Upload & Analyse Initiale
     */
    public function importUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt,xml',
            'source' => 'required|string',
            'type' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            ImportStaging::where('user_id', $user->id)
                ->where('company_id', $user->company_id)
                ->where('type', $request->type)
                ->whereIn('status', ['upload', 'staging'])
                ->delete();

            $sheetData = [];

            if ($extension === 'xml') {
                // --- PARSEUR XML INTELLIGENT & RECURSIF ---
                $xmlString = @file_get_contents($file->getRealPath());
                $xml = @simplexml_load_string($xmlString);
                if (!$xml) return redirect()->back()->with('error', "Format XML illisible.");

                // Fonction de détection du bloc de données répétitif
                $findDataNode = function($node) use (&$findDataNode) {
                    $counts = [];
                    foreach($node->children() as $child) {
                        $n = $child->getName();
                        $counts[$n] = ($counts[$n] ?? 0) + 1;
                    }
                    foreach($counts as $n => $c) if ($c >= 2) return $node->{$n};
                    foreach($node->children() as $child) { $res = $findDataNode($child); if ($res) return $res; }
                    return null;
                };

                $records = $findDataNode($xml);
                if ($records) {
                    $headers = [];
                    $rows = [];
                    foreach($records as $rec) {
                        $rowMap = [];
                        foreach($rec->children() as $f) {
                            $h = strtoupper($f->getName());
                            if (!in_array($h, $headers)) $headers[] = $h;
                            $rowMap[$h] = (string)$f;
                        }
                        $rows[] = $rowMap;
                    }
                    $sheetData[] = $headers;
                    foreach($rows as $rm) {
                        $line = [];
                        foreach($headers as $h) $line[] = $rm[$h] ?? '';
                        $sheetData[] = $line;
                    }
                }
            } elseif ($extension === 'txt' || $extension === 'csv') {
                // --- ANALYSEUR TXT ULTRA-ROBUSTE (ENCODING + FIXED/DELIM) ---
                $rawContent = file_get_contents($file->getRealPath());
                
                // 1. DÉTECTION & CONVERSION ENCODING (Sage export souvent ANSI/ISO)
                $enc = mb_detect_encoding($rawContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII']);
                if ($enc && $enc !== 'UTF-8') {
                    $rawContent = mb_convert_encoding($rawContent, 'UTF-8', $enc);
                }

                $content = explode("\n", str_replace("\r", "", $rawContent));
                $content = array_filter($content, fn($l) => trim($l) !== '');
                $sample = array_slice($content, 0, 50); // Scan plus large
                
                // 2. ESSAI DÉLIMITÉ
                $delimiters = [';', ',', "\t", '|'];
                $bestDelim = null;
                $maxCols = 0;
                foreach($delimiters as $d) {
                    $colsCount = array_map(fn($l) => count(explode($d, $l)), $sample);
                    $avgCols = count($colsCount) > 0 ? array_sum($colsCount) / count($colsCount) : 0;
                    if ($avgCols > $maxCols && $avgCols > 1.5) { 
                        $maxCols = $avgCols; 
                        $bestDelim = $d; 
                    }
                }

                if ($bestDelim) {
                    foreach($content as $l) $sheetData[] = str_getcsv($l, $bestDelim);
                } else {
                    // 3. ESSAI LARGEUR FIXE (STYLE SAGE / MAINFRAME)
                    // On cherche les "caniveaux" (colonnes de vides verticaux)
                    $maxL = max(array_map('mb_strlen', $sample) ?: [0]);
                    $density = array_fill(0, $maxL, 0);
                    foreach($sample as $l) {
                        for($i=0; $i<mb_strlen($l); $i++) if (mb_substr($l, $i, 1) !== ' ') $density[$i]++;
                    }
                    
                    $offsets = [0];
                    $inSilence = false;
                    for($i=1; $i<$maxL - 1; $i++) {
                        // Un silence est une zone où la densité est nulle sur TOUT l'échantillon
                        $isVoid = ($density[$i] == 0);
                        if ($isVoid && !$inSilence) { 
                            $inSilence = true; 
                        } elseif (!$isVoid && $inSilence) { 
                            // Fin du silence = nouveau début de colonne
                            $offsets[] = $i; 
                            $inSilence = false; 
                        }
                    }
                    
                    foreach($content as $l) {
                        $row = [];
                        for($i=0; $i<count($offsets); $i++) {
                            $start = $offsets[$i];
                            $len = isset($offsets[$i+1]) ? ($offsets[$i+1] - $start) : null;
                            $val = $len ? mb_substr($l, $start, $len) : mb_substr($l, $start);
                            $row[] = trim($val);
                        }
                        // Garder seulement les lignes qui ont au moins une donnée utile
                        if (count(array_filter($row)) > 0) $sheetData[] = $row;
                    }
                }
            } else {
                // --- EXCEL STANDARD ---
                $data = Excel::toArray([], $file);
                foreach($data as $s) if (!empty($s)) { $sheetData = $s; break; }
            }

            if (empty($sheetData) || count($sheetData) < 1) {
                return redirect()->back()->with('error', "Aucune donnée n'a pu être extraite.");
            }

            // AUTO-ADAPTATION : On autorise TOUT, c'est le Mapping qui fera le tri.
            // On ne bloque plus par validation rigide.
            $import = ImportStaging::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'source' => $request->source,
                'type' => $request->type,
                'file_name' => $file->getClientOriginalName(),
                'raw_data' => $sheetData,
                'status' => 'upload'
            ]);

            return redirect()->route('admin.import.mapping', $import->id);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur critique : ' . $e->getMessage());
        }
    }

    /**
     * Tunnel d'Importation - Interface de Mapping
     */
    public function importMapping($id)
    {
        $import = ImportStaging::findOrFail($id);
        // Définition exhaustive des champs et de leurs synonymes pour le mappage intelligent
        $fieldsDictionary = [
            'initial' => [
                'numero_de_compte' => [
                    'label' => 'Numéro de compte', 
                    'required' => true, 
                    'icon' => 'fa-hashtag', 
                    'match' => ['compte', 'num', 'code', 'acc', 'no', 'numero', 'ncompte', 'noaccount', 'comptenumber', 'comptegeneral', 'cptgen', 'cpt', 'compte_numero', 'compte_num'],
                    'pattern' => '/^\d{3,12}$/'
                ],
                'intitule' => [
                    'label' => 'Intitulé du compte', 
                    'required' => true, 
                    'icon' => 'fa-font', 
                    'match' => ['intitule', 'libelle', 'nom', 'desc', 'label', 'designation', 'intitule_compte', 'label_compte', 'accountname', 'libelledelasaisie', 'description', 'nomcompte', 'compte_intitule'],
                    'pattern' => '/^[a-zA-Z]/i'
                ]
            ],
            'tiers' => [
                'numero_de_tiers' => [
                    'label' => 'Numéro de Tiers', 
                    'required' => false, 
                    'icon' => 'fa-hashtag', 
                    'auto_generate' => true, 
                    'match' => ['identifiant', 'id', 'numtiers', 'notiers', 'numerotiers', 'codetiers', 'idtiers', 'comptetiers', 'auxiliaire', 'tiers', 'code_tiers', 'num_tiers', 'tier_num', 'tiers_numero', 'ctiers_numero'],
                    'pattern' => '/^[A-Z0-9]{2,15}$/i'
                ],
                'intitule' => [
                    'label' => 'Nom / Intitulé', 
                    'required' => true, 
                    'icon' => 'fa-font', 
                    'match' => ['intituledutiers', 'nomdutiers', 'raisondutiers', 'societe', 'intitule', 'nomtiers', 'client', 'fournisseur', 'nom', 'raison', 'raisonsociale', 'denomination', 'libelle', 'name', 'tiers_nom', 'nom_client', 'nom_fournisseur', 'tiers_intitule', 'ctiers_intitule'],
                    'pattern' => '/^[a-zA-Z]/i'
                ],
                'type_de_tiers' => [
                    'label' => 'Catégorie / Type', 
                    'required' => true, 
                    'icon' => 'fa-tags', 
                    'match' => ['type', 'categorie', 'cat', 'nature', 'naturetiers', 'typetiers', 'qualite', 'classification', 'cat_tiers', 'role', 'statut'],
                    'pattern' => '/^(cl|fo|cli|fou|0|1|2|3)$/i'
                ],
                'compte_general' => [
                    'label' => 'Compte général', 
                    'required' => true, 
                    'icon' => 'fa-link', 
                    'match' => ['rattachement', 'collectif', 'comptegeneral', 'cptgeneral', 'nocptcollectif', 'compte_collectif', 'compte_general', 'general', 'compte', 'comptecollectif', 'cptcollectif', 'numerocompte', 'numcompte', 'comptetiers', 'collectif_num', 'cpt_collectif', 'compte_numero'],
                    'pattern' => '/^(401|411|4)\d*/'
                ]
            ],
            'journals' => [
                'code_journal' => [
                    'label' => 'Code Journal', 
                    'required' => true, 
                    'icon' => 'fa-tag', 
                    'match' => ['code', 'journal', 'id', 'jnl', 'abr', 'codejournal', 'journalcode', 'jrn', 'abreviation', 'code_jnl', 'journal_code'],
                    'pattern' => '/^[A-Z0-9]{2,5}$/i'
                ],
                'intitule' => [
                    'label' => 'Intitulé du Journal', 
                    'required' => true, 
                    'icon' => 'fa-font', 
                    'match' => ['intitule', 'libelle', 'nom', 'label', 'designation', 'intitulejnl', 'nomdujournal', 'jnlname', 'libelle_journal', 'nom_journal', 'journal_intitule'],
                    'pattern' => '/^[a-zA-Z]/i'
                ],
                'type' => [
                    'label' => 'Type (Achats, Ventes, etc.)', 
                    'required' => true, 
                    'icon' => 'fa-layer-group', 
                    'match' => ['type', 'nature', 'cat', 'flux', 'categorie', 'journaltype', 'type_journal', 'classement'],
                    'pattern' => '/^(ach|ven|bq|ca|od|0|1|2|3)$/i'
                ],
                'compte_de_tresorerie' => [
                    'label' => 'Compte Trésorerie', 
                    'required' => false, 
                    'icon' => 'fa-university', 
                    'match' => ['compte_tresorerie', 'treso', 'banque', 'caisse', 'comptetresorerie', 'ncomptetresorerie', 'ctres', 'cpte_treso', 'nocountetresorerie', 'compte_banque', 'num_compte_treso', 'compte_caisse', 'compte_numero'],
                    'pattern' => '/^5\d+/'
                ],
                'traitement_analytique' => ['label' => 'Traitement Analytique', 'required' => false, 'icon' => 'fa-chart-pie', 'match' => ['analytique', 'traitementanalytique', 'sectionana', 'analyt', 'ana', 'analytique_actif', 'trtana', 'gestion_analytique']],
                'rapprochement_sur' => ['label' => 'État Rapprochement', 'required' => false, 'icon' => 'fa-check-double', 'match' => ['rapprochement', 'etatrap', 'rap', 'rappro', 'rapprochement_bancaire', 'rapprochement_manuel', 'bnqrap', 'gestion_rapprochement']]
            ],
            'courant' => [
                'jour' => [
                    'label' => 'Date / Jour', 
                    'required' => true, 
                    'icon' => 'fa-calendar', 
                    'match' => ['date', 'jour', 'period', 'quantieme', 'mvt', 'dateecrit', 'ecrituredate', 'date_operation', 'date_ecriture', 'quantieme', 'ecriture_date', 'dateecr'],
                    'pattern' => '/^\d{1,4}/'
                ],
                'journal' => [
                    'label' => 'Code Journal', 
                    'required' => true, 
                    'icon' => 'fa-book', 
                    'match' => ['journal', 'code', 'jnl', 'jrn', 'codejnl', 'idjnl', 'code_journal', 'jnl_code', 'identifiant_journal', 'ecriture_journal'],
                    'pattern' => '/^[A-Z0-9]{2,5}$/i'
                ],
                'reference' => ['label' => 'Référence Pièce', 'required' => false, 'icon' => 'fa-receipt', 'match' => ['ref', 'piece', 'facture', 'num', 'doc', 'numpiece', 'refpiece', 'ndoc', 'n_piece', 'reference_piece', 'n_facture', 'ecriture_piece', 'ecriture_reference']],
                'compte' => [
                    'label' => 'Numéro Compte', 
                    'required' => true, 
                    'icon' => 'fa-hashtag', 
                    'match' => ['compte', 'num', 'general', 'acc', 'ncompte', 'numaccount', 'compte_general', 'cpt_gen', 'numero_compte', 'ecriture_compte', 'compte_numero'],
                    'pattern' => '/^\d{3,12}$/'
                ],
                'libelle' => ['label' => 'Libellé Opération', 'required' => true, 'icon' => 'fa-font', 'match' => ['libelle', 'desc', 'nom', 'intitule', 'comm', 'objet', 'libelleecrit', 'description', 'intitule_operation', 'commentaire', 'designation_operation', 'ecriture_libelle']],
                'debit' => [
                    'label' => 'Montant Débit', 
                    'required' => true, 
                    'icon' => 'fa-plus-circle', 
                    'match' => ['debit', 'montant', 'flux_d', 'entree', 'amount_d', 'midebit', 'debits', 'montant_debit', 'somme_debit', 'ecriture_debit', 'montant_d', 'ecrc_montant'],
                    'is_numeric' => true
                ],
                'credit' => [
                    'label' => 'Montant Crédit', 
                    'required' => true, 
                    'icon' => 'fa-minus-circle', 
                    'match' => ['credit', 'montant_c', 'flux_c', 'sortie', 'amount_c', 'micredit', 'credits', 'montant_credit', 'somme_credit', 'ecriture_credit', 'montant_c', 'ecrc_montant'],
                    'is_numeric' => true
                ],
                'tiers' => ['label' => 'Compte Tiers', 'required' => false, 'icon' => 'fa-user', 'match' => ['tier', 'auxiliaire', 'client', 'fourn', 'compte_t', 'aux', 'tiersaux', 'compte_auxiliaire', 'auxiliaire_num', 'tiers_id', 'ecriture_tiers']]
            ]
        ];

        $typeKey = $import->type == 'initial' ? 'initial' : ($import->type == 'journals' ? 'journals' : ($import->type == 'tiers' ? 'tiers' : 'courant'));
        $fields = $fieldsDictionary[$typeKey];

        // LOGIQUE D'INTELLIGENCE : Détection de la meilleure ligne d'en-tête
        $headers = [];
        $headerIndex = -1; // -1 signifie PAS d'en-tête (on commence à la ligne 0)
        $maxMatches = 0;
        $scanLimit = min(count($import->raw_data), 20);

        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $import->raw_data[$i];
            if (empty($row)) continue;
            
            $currentMatches = 0;
            $matchedFields = [];

            foreach ($row as $cell) {
                if (empty($cell) || is_numeric($cell)) continue;
                
                $cleanCell = strtolower(preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $cell)));
                if (empty($cleanCell) || strlen($cleanCell) < 3) continue;

                foreach ($fields as $fieldKey => $field) {
                    if (in_array($fieldKey, $matchedFields)) continue;
                    
                    foreach ($field['match'] as $m) {
                        $cleanM = strtolower(preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $m)));
                        if ($cleanCell === $cleanM || str_contains($cleanCell, $cleanM)) {
                            $currentMatches++;
                            $matchedFields[] = $fieldKey;
                            break 2;
                        }
                    }
                }
            }

            // Seuil de détection : on a besoin d'au moins 1 match solide ou 2 partiels
            if ($currentMatches >= 1 && $currentMatches > $maxMatches) {
                $maxMatches = $currentMatches;
                $headerIndex = $i;
            }
        }

        // Si on a trouvé un en-tête, on le définit
        if ($headerIndex !== -1) {
            $headers = $import->raw_data[$headerIndex];
        } else {
            // Sinon, on génère des en-têtes fictifs "Colonne X"
            $maxCols = 0;
            foreach(array_slice($import->raw_data, 0, 10) as $r) $maxCols = max($maxCols, count($r));
            for($i=0; $i<$maxCols; $i++) $headers[] = "Colonne " . chr(65 + ($i % 26)) . ($i > 25 ? floor($i/26) : '');
        }

        // MOTEUR DE CORRESPONDANCE "INFAILLIBLE" : Multicritère
        // 1. Dictionnaire étendu, 2. Regex Pattern, 3. Analyse statistique
        $dataSamples = array_slice($import->raw_data, $headerIndex + 1, 20);
        
        foreach ($fields as $fieldKey => &$field) {
            $field['suggested_col'] = null;
            $bestScore = 0;

            foreach ($headers as $colIdx => $header) {
                $score = 0;
                
                // --- CRITÈRE 1 : CORRESPONDANCE SÉMANTIQUE (NOM DE COLONNE) ---
                if ($header) {
                    $cleanHeader = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $header)));
                    foreach ($field['match'] as $m) {
                        $cleanM = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $m)));
                        if ($cleanHeader === $cleanM) { $score += 100; break; }
                        elseif (str_contains($cleanHeader, $cleanM)) { $score += 40; }
                    }
                }

                // --- CRITÈRE 2 : ANALYSE DU CONTENU (DATA-FIRST) ---
                $formatMatches = 0;
                $totalPopulated = 0;
                $sampleValues = [];
                
                foreach ($dataSamples as $sampleRow) {
                    $cell = trim((string)($sampleRow[$colIdx] ?? ''));
                    if ($cell === '') continue;
                    $totalPopulated++;
                    $sampleValues[] = $cell;

                    // Test Regex Pattern
                    if (isset($field['pattern']) && preg_match($field['pattern'], $cell)) {
                        $formatMatches += 2; // Poids fort pour le pattern
                    }
                    // Test Numérique Spécifique
                    if (isset($field['is_numeric']) && is_numeric(str_replace([' ', ',', '€'], ['', '.', ''], $cell))) {
                        $formatMatches++;
                    }
                }

                if ($totalPopulated > 0) {
                    $dataScore = ($formatMatches / ($totalPopulated * 2)) * 80;
                    $score += $dataScore;
                }

                // --- CRITÈRE 3 : RÈGLES MÉTIERS ---
                if (count($sampleValues) > 0) {
                    if ($fieldKey == 'compte_general' && collect($sampleValues)->every(fn($v) => str_starts_with($v, '4'))) $score += 40;
                    if ($fieldKey == 'compte_de_tresorerie' && collect($sampleValues)->every(fn($v) => str_starts_with($v, '5'))) $score += 40;
                }

                if ($score > $bestScore && $score > 25) {
                    $bestScore = $score;
                    $field['suggested_col'] = $colIdx;
                }
            }
        }

        // Enregistrer l'index de l'en-tête SANS écraser le mapping existant
        $currentMapping = $import->mapping ?? [];
        $currentMapping['_header_index'] = $headerIndex;
        $import->update([
            'mapping' => $currentMapping
        ]);
        
        $typeLabels = [
            'initial' => 'Plan Comptable Master',
            'tiers' => 'Modèle de Tiers',
            'journals' => 'Modèle des Journaux',
            'courant' => 'Écritures Comptables'
        ];
        $importTitle = $typeLabels[$import->type] ?? 'Importation';
        
        $viewName = 'admin.config.import_mapper';
        if ($import->type == 'initial') $viewName = 'admin.config.import_mapper_plan';
        elseif ($import->type == 'tiers') $viewName = 'admin.config.import_mapper_tiers';
        elseif ($import->type == 'journals') $viewName = 'admin.config.import_mapper_journals';

        return view($viewName, compact('import', 'headers', 'fields', 'importTitle'));
    }

    /**
     * Tunnel d'Importation - Traitement du Mapping & Staging
     */
    public function processMapping(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $mapping = $request->input('mapping'); // Array: ['date' => 0, 'compte' => 2, ...]
        $fixedValues = $request->input('fixed_value', []);

        // Nettoyer le mapping des valeurs FIXED pour stocker les vraies colonnes ou les valeurs fixes
        $cleanMapping = [];
        foreach($mapping as $field => $col) {
            if ($col === 'FIXED') {
                $cleanMapping[$field] = 'FIXED:' . ($fixedValues[$field] ?? '');
            } else {
                $cleanMapping[$field] = $col;
            }
        }

        // Préserver le _header_index s'il existe
        $existingMapping = $import->mapping ?? [];
        $headerIndex = $existingMapping['_header_index'] ?? 0;
        $cleanMapping['_header_index'] = $headerIndex;

        $import->update([
            'mapping' => $cleanMapping,
            'status' => 'staging'
        ]);

        return redirect()->route('admin.import.staging', $import->id);
    }

    /**
     * Tunnel d'Importation - Revue & Correction (Staging)
     */
    public function importStaging($id)
    {
        $import = ImportStaging::findOrFail($id);
        $user = Auth::user();
        $accountDigits = $user->company->account_digits ?? 8;
        
        $mapping = $import->mapping;
        
        $headerIndex = $mapping['_header_index'] ?? 0;
        
        // Nettoyage des données : Ignorer les lignes avant les titres et les lignes totalement vides basées sur les colonnes mappées
        $data = array_filter(array_slice($import->raw_data, $headerIndex + 1), function($row) use ($mapping) {
            $hasData = false;
            foreach ($mapping as $field => $index) {
                if ($field !== '_header_index' && $index !== null && $index !== "" && !empty(trim($row[$index] ?? ''))) {
                    $hasData = true;
                    break;
                }
            }
            return $hasData;
        });

        $existingAccounts = PlanComptable::where('company_id', $user->company_id)
            ->pluck('numero_de_compte')
            ->toArray();
        
        $existingJournals = CodeJournal::where('company_id', $user->company_id)
            ->pluck('code_journal')
            ->toArray();

        // Déterminer l'indice maximum mappé pour éviter les erreurs d'Index Hors Limites (Padding)
        $maxMappingIndex = 0;
        foreach ($mapping as $mIdx) {
            if (is_numeric($mIdx)) $maxMappingIndex = max($maxMappingIndex, (int)$mIdx);
        }

        $rowsWithStatus = [];
        $errorCount = 0;
        $validCount = 0;

        foreach($data as $index => $rowRaw) {
            // PADDING : Forcer la ligne à avoir au moins assez de colonnes pour couvrir le mapping
            $row = array_pad($rowRaw, $maxMappingIndex + 1, null);
            
            // INITIALISATION AVEC TOUTES LES CLÉS POSSIBLES (Évite le null / Sans nom)
            $processedRow = [
                'intitule' => null, 
                'libelle' => null,
                'numero_de_compte' => null,
                'compte' => null,
                'numero_de_tiers' => null,
                'compte_general' => null,
                'type_de_tiers' => null,
                'code_journal' => null,
                'journal' => null,
                'type' => null,
                'jour' => null,
                'reference' => null,
                'debit' => 0,
                'credit' => 0,
            ];

            foreach($mapping as $fKey => $cId) {
                if ($fKey === '_header_index' || $cId === null || $cId === "" || $cId === "AUTO") continue;
                
                $val = null;
                if (is_string($cId) && str_starts_with($cId, 'FIXED:')) {
                    $val = substr($cId, 6);
                } else {
                    $idx = (int)$cId;
                    $val = $row[$idx] ?? null;
                }
                
                if ($val !== null) {
                    $processedRow[$fKey] = is_string($val) ? trim($val) : $val;
                }
            }
            
            // SYNCHRONISATION DES NOMS : 'intitule' est le standard pour Plan & Tiers, 'libelle' pour Écritures
            if (empty($processedRow['intitule']) && !empty($processedRow['libelle'])) {
                $processedRow['intitule'] = $processedRow['libelle'];
            } elseif (empty($processedRow['libelle']) && !empty($processedRow['intitule'])) {
                $processedRow['libelle'] = $processedRow['intitule'];
            }
            
            // On bascule sur le tableau nommé
            $row = $processedRow;

            $errors = [];
            
            if ($import->type == 'initial') {
                // Validation pour Plan Comptable
                $rowCompte = trim($row['numero_de_compte'] ?? '');
                if (empty($rowCompte)) {
                    $errors[] = "Numéro de compte manquant";
                } elseif (strlen($rowCompte) != $accountDigits) {
                    $errors[] = "Longueur incorrecte : attendu $accountDigits chiffres (reçu " . strlen($rowCompte) . ")";
                } elseif (in_array($rowCompte, $existingAccounts)) {
                    $errors[] = "Compte déjà présent dans le plan : $rowCompte";
                }
            } elseif ($import->type == 'journals') {
                // Validation pour Journaux
                $rowCode = trim($row['code_journal'] ?? '');
                
                // Détection intelligente du type si non spécifié ou si colonne absente
                if (empty($row['type'])) {
                    $searchStr = strtoupper($rowCode . ' ' . ($row['intitule'] ?? ''));
                    $detectedType = 'Opérations Diverses';
                    if (Str::contains($searchStr, ['ACH', 'FOURN', 'FRN'])) $detectedType = 'Achats';
                    elseif (Str::contains($searchStr, ['VEN', 'CLT', 'CLI'])) $detectedType = 'Ventes';
                    elseif (Str::contains($searchStr, ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'TRES', 'TRZ', 'BANKING'])) $detectedType = 'Banque';
                    elseif (Str::contains($searchStr, ['CAI', 'CASH', 'CAS'])) $detectedType = 'Caisse';
                    elseif (Str::contains($searchStr, ['OD', 'DIV', 'VAR'])) $detectedType = 'Opérations Diverses';
                    
                    $row['type'] = $detectedType;
                }

                if (empty($rowCode)) {
                    $errors[] = "Code journal manquant";
                } elseif (in_array(strtoupper($rowCode), array_map('strtoupper', $existingJournals))) {
                    $errors[] = "Journal déjà existant : $rowCode";
                }
            } elseif ($import->type == 'tiers') {
                // Validation pour Tiers
                // S'assurer que les clés sont bien lues
                $rowNum = trim($row['numero_de_tiers'] ?? '');
                $rowCompte = trim($row['compte_general'] ?? '');
                $rowType = trim($row['type_de_tiers'] ?? '');

                // TENTATIVE DE DÉDUCTION DU COMPTE SI MANQUANT
                if (empty($rowCompte) && !empty($rowType)) {
                    $typeLower = strtolower($rowType);
                    if (in_array($typeLower, ['client', 'cli', 'clt'])) {
                        $rowCompte = '411000'; // Défaut Client
                        $row['compte_general'] = $rowCompte;
                    } elseif (in_array($typeLower, ['fournisseur', 'four', 'frs', 'fourn'])) {
                        $rowCompte = '401000'; // Défaut Fournisseur
                        $row['compte_general'] = $rowCompte;
                    }
                }
                
                // GÉNÉRATION VIRTUELLE POUR L'AFFICHAGE DU STAGING
                $shouldAutoGenerate = ($mapping['numero_de_tiers'] ?? '') === 'AUTO' 
                                    || ($mapping['numero_de_tiers'] ?? '') === '' 
                                    || ($mapping['numero_de_tiers'] ?? '') === null
                                    || empty($rowNum);
                
                if ($shouldAutoGenerate) {
                    if (!empty($rowCompte)) {
                        $planAcc = PlanComptable::where('company_id', $user->company_id)
                            ->where('numero_de_compte', $rowCompte)
                            ->first();
                        
                        // Si le compte spécifique n'existe pas, on cherche un compte racine (ex: 411)
                        if (!$planAcc) {
                            $prefix = str_starts_with($rowCompte, '411') ? '411' : (str_starts_with($rowCompte, '401') ? '401' : null);
                            if ($prefix) {
                                $planAcc = PlanComptable::where('company_id', $user->company_id)
                                    ->where('numero_de_compte', 'LIKE', $prefix . '%')
                                    ->first();
                            }
                        }

                        if ($planAcc) {
                            $resp = $this->getNextTierNumber(new Request([
                                'plan_comptable_id' => $planAcc->id,
                                'intitule' => $row['intitule'] ?? ''
                            ]));
                            $genData = $resp->getData();
                            if ($genData->success) {
                                $rowNum = $genData->next_id;
                                $row['auto_num'] = $rowNum;
                                $row['numero_de_tiers'] = $rowNum;
                            } else {
                                $errors[] = "Erreur de génération : " . ($genData->message ?? 'Inconnue');
                            }
                        } else {
                            // GÉNÉRATION VIRTUELLE (Prédit le numéro même si le compte manque)
                            $accountDigits = $user->company->account_digits ?? 8;
                            $suffixLen = $accountDigits - strlen($rowCompte);
                            $rowNum = $rowCompte . str_pad('1', ($suffixLen > 0 ? $suffixLen : 1), '0', STR_PAD_LEFT);
                            $row['auto_num'] = $rowNum;
                            $row['numero_de_tiers'] = $rowNum;
                            $row['is_virtual'] = true;
                            $errors[] = "Le compte collectif $rowCompte n'existe pas. Créez-le pour valider.";
                            
                            // Détection si c'est probablement un numéro de tiers mal mappé
                            if (strlen($rowCompte) > 8 || !str_starts_with($rowCompte, '4')) {
                                $errors[] = "Attention : Ce compte ne ressemble pas à un compte collectif standard (ex: 411, 401). Vérifiez si vous n'avez pas mappé le numéro de tiers à la place du compte général.";
                            }
                        }
                    } else {
                        $errors[] = "Compte collectif absent. Impossible de générer le numéro.";
                    }
                }

                if (empty($rowNum) && empty($row['auto_num'] ?? '')) {
                    $errors[] = "Numéro de tiers manquant";
                }
                
                // Normalisation du type de tiers (si non vide)
                if (!empty($rowType)) {
                    $typeLower = strtolower($rowType);
                    if (in_array($typeLower, ['client', 'cli', 'clt'])) {
                        $row['type_de_tiers'] = 'Client';
                    } elseif (in_array($typeLower, ['fournisseur', 'four', 'frs', 'fourn'])) {
                        $row['type_de_tiers'] = 'Fournisseur';
                    } else {
                        $row['type_de_tiers'] = 'Autre';
                    }
                }
            } else {
                // Validation pour Écritures
                $rowCompte = trim($row['compte'] ?? '');
                $rowJournal = trim($row['journal'] ?? '');
                $rowDebit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $row['debit'] ?? 0));
                $rowCredit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $row['credit'] ?? 0));

                if (empty($rowCompte)) {
                    $errors[] = "Compte manquant";
                } elseif (!in_array($rowCompte, $existingAccounts)) {
                    $errors[] = "Compte inconnu : $rowCompte";
                }

                if (empty($rowJournal)) {
                    $errors[] = "Journal manquant";
                } elseif (!in_array(strtoupper($rowJournal), array_map('strtoupper', $existingJournals))) {
                    $errors[] = "Journal inconnu : $rowJournal";
                }
                
                $row['debit_val'] = $rowDebit;
                $row['credit_val'] = $rowCredit;
            }

            $status = (count($errors) > 0) ? 'error' : 'valid';
            if ($status == 'error') $errorCount++; else $validCount++;

            $rowsWithStatus[] = [
                'index' => $index,
                'data' => $row,
                'status' => $status,
                'errors' => $errors,
                'debit' => $row['debit_val'] ?? 0,
                'credit' => $row['credit_val'] ?? 0
            ];
        }

        $typeLabels = [
            'initial' => 'Plan Comptable Master',
            'tiers' => 'Modèle de Tiers',
            'journals' => 'Modèle des Journaux',
            'courant' => 'Écritures Comptables'
        ];
        $importTitle = $typeLabels[$import->type] ?? 'Importation';

        $viewName = 'admin.config.import_staging';
        if ($import->type == 'initial') $viewName = 'admin.config.import_staging_plan';
        elseif ($import->type == 'tiers') $viewName = 'admin.config.import_staging_tiers';
        elseif ($import->type == 'journals') $viewName = 'admin.config.import_staging_journals';

        return view($viewName, compact('import', 'rowsWithStatus', 'errorCount', 'validCount', 'importTitle'));
    }

    /**
     * Tunnel d'Importation - Injection Finale
     */
    public function commitImport(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $user = Auth::user();
        
        $mapping = $import->mapping;
        
        $headerIndex = $mapping['_header_index'] ?? 0;

        // Nettoyage des données : Ignorer les lignes avant les titres et les lignes totalement vides basées sur les colonnes mappées
        $data = array_filter(array_slice($import->raw_data, $headerIndex + 1), function($row) use ($mapping) {
            $hasData = false;
            foreach ($mapping as $field => $index) {
                if ($field !== '_header_index' && $index !== null && $index !== "" && !empty(trim($row[$index] ?? ''))) {
                    $hasData = true;
                    break;
                }
            }
            return $hasData;
        });

        $accountDigits = $user->company->account_digits ?? 8;

        $importedCount = 0;
        $duplicateCount = 0;

        DB::beginTransaction();
        try {
            $planComptableIds = PlanComptable::where('company_id', $user->company_id)->pluck('id', 'numero_de_compte')->toArray();
            $existingJournals = CodeJournal::where('company_id', $user->company_id)->pluck('code_journal')->toArray();

            foreach ($data as $index => $rowOrig) {
                // ANALYSE DATA SELON MAPPING (support FIXED values)
                $rowMapped = [];
                foreach($mapping as $field => $colIndex) {
                    if ($field === '_header_index') continue;
                    if (is_string($colIndex) && str_starts_with($colIndex, 'FIXED:')) {
                        $rowMapped[$field] = substr($colIndex, 6);
                    } else {
                        $rowMapped[$field] = $rowOrig[$colIndex] ?? null;
                    }
                }

                if ($import->type == 'initial') {
                    // --- IMPORT PLAN COMPTABLE ---
                    $msg_type = "comptes";
                    $rowCompte = trim($rowMapped['numero_de_compte'] ?? '');
                    if (empty($rowCompte)) continue;

                    if (isset($planComptableIds[$rowCompte])) {
                        // UPDATE existant
                        PlanComptable::where('id', $planComptableIds[$rowCompte])->update([
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'COMPTE SANS NOM'),
                            'user_id' => $user->id
                        ]);
                        $duplicateCount++;
                    } else {
                        // CREATE nouveau
                        $newAcc = PlanComptable::create([
                            'numero_de_compte' => $rowCompte,
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'COMPTE SANS NOM'),
                            'type_de_compte' => 'Bilan',
                            'classe' => substr($rowCompte, 0, 1),
                            'user_id' => $user->id,
                            'company_id' => $user->company_id
                        ]);
                        $planComptableIds[$rowCompte] = $newAcc->id;
                        $importedCount++;
                    }

                } elseif ($import->type == 'tiers') {
                    // --- IMPORT TIERS ---
                    $msg_type = "tiers";
                    $rowCompteNum = trim($rowMapped['compte_general'] ?? '');
                    $rowType = trim($rowMapped['type_de_tiers'] ?? '');
                    $rowNum = trim($rowMapped['numero_de_tiers'] ?? '');

                    // DÉDUCTION INTELLIGENTE SI COMPTE MANQUANT
                    if (empty($rowCompteNum) && !empty($rowType)) {
                        $typeLower = strtolower($rowType);
                        if (in_array($typeLower, ['client', 'cli', 'clt'])) {
                            $rowCompteNum = '411000';
                        } elseif (in_array($typeLower, ['fournisseur', 'four', 'frs', 'fourn'])) {
                            $rowCompteNum = '401000';
                        }
                    }

                    // Récupérer l'ID du compte collectif
                    $compteCollectifId = $planComptableIds[$rowCompteNum] ?? null;

                    // Si pas trouvé par numéro exact, chercher par racine (ex: 411)
                    if (!$compteCollectifId && !empty($rowCompteNum)) {
                         $prefix = str_starts_with($rowCompteNum, '411') ? '411' : (str_starts_with($rowCompteNum, '401') ? '401' : null);
                         if ($prefix) {
                             $compteCollectifId = PlanComptable::where('company_id', $user->company_id)
                                ->where('numero_de_compte', 'LIKE', $prefix . '%')
                                ->value('id');
                         }
                    }
                    
                    if ((empty($rowNum) || $rowNum === 'AUTO' || str_starts_with($rowNum, '-')) && $compteCollectifId) {
                        $resp = $this->getNextTierNumber(new Request([
                            'plan_comptable_id' => $compteCollectifId,
                            'intitule' => $rowMapped['intitule'] ?? ''
                        ]));
                        $genData = $resp->getData();
                        if ($genData->success) {
                            $rowNum = $genData->next_id;
                        }
                    }

                    // Fallback ultime si toujours vide ou AUTO (ne devrait pas arriver avec les corrections)
                    if (empty($rowNum) || $rowNum === 'AUTO') {
                        continue;
                    }

                    $existingTier = PlanTiers::where('company_id', $user->company_id)
                        ->where('numero_de_tiers', $rowNum)
                        ->first();

                    if ($existingTier) {
                        // UPDATE existant
                        $existingTier->update([
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'TIERS SANS NOM'),
                            'type_de_tiers' => ucfirst(strtolower($rowMapped['type_de_tiers'] ?? 'Autre')),
                            'compte_general' => $compteCollectifId,
                            'user_id' => $user->id
                        ]);
                        $duplicateCount++;
                    } else {
                        // CREATE nouveau
                        PlanTiers::create([
                            'numero_de_tiers' => strtoupper($rowNum),
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'TIERS SANS NOM'),
                            'type_de_tiers' => ucfirst(strtolower($rowMapped['type_de_tiers'] ?? 'Autre')),
                            'compte_general' => $compteCollectifId,
                            'user_id' => $user->id,
                            'company_id' => $user->company_id
                        ]);
                        $importedCount++;
                    }

                } elseif ($import->type == 'journals') {
                    // --- IMPORT JOURNAUX ---
                    $msg_type = "journaux";
                    $rowCode = trim($rowMapped['code_journal'] ?? '');
                    if (empty($rowCode)) continue;

                    $type = $rowMapped['type'] ?? null;
                    if (empty($type)) {
                        $searchStr = strtoupper($rowCode . ' ' . ($rowMapped['intitule'] ?? ''));
                        $type = 'Opérations Diverses';
                        if (Str::contains($searchStr, ['ACH', 'FOURN', 'FRN'])) $type = 'Achats';
                        elseif (Str::contains($searchStr, ['VEN', 'CLT', 'CLI'])) $type = 'Ventes';
                        elseif (Str::contains($searchStr, ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'TRES', 'TRZ', 'BANKING'])) $type = 'Banque';
                        elseif (Str::contains($searchStr, ['CAI', 'CASH', 'CAS'])) $type = 'Caisse';
                    }

                    $compteNum = trim($rowMapped['compte_de_tresorerie'] ?? '');
                    $compteId = $planComptableIds[$compteNum] ?? null;

                    $existingJournal = CodeJournal::where('company_id', $user->company_id)
                        ->where('code_journal', strtoupper($rowCode))
                        ->first();

                    if ($existingJournal) {
                        // UPDATE existant
                        $existingJournal->update([
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'JOURNAL SANS NOM'),
                            'type' => $type,
                            'compte_de_tresorerie' => $compteId,
                            'traitement_analytique' => (strtolower($rowMapped['traitement_analytique'] ?? '') === 'oui'),
                            'rapprochement_sur' => $rowMapped['rapprochement_sur'] ?? null,
                            'user_id' => $user->id
                        ]);
                        $duplicateCount++;
                    } else {
                        // CREATE nouveau
                        CodeJournal::create([
                            'code_journal' => strtoupper($rowCode),
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'JOURNAL SANS NOM'),
                            'type' => $type,
                            'compte_de_tresorerie' => $compteId,
                            'traitement_analytique' => (strtolower($rowMapped['traitement_analytique'] ?? '') === 'oui'),
                            'rapprochement_sur' => $rowMapped['rapprochement_sur'] ?? null,
                            'user_id' => $user->id,
                            'company_id' => $user->company_id
                        ]);
                        $existingJournals[] = strtoupper($rowCode); // Add to existingJournals for subsequent checks in the same import
                        $importedCount++;
                    }

                } elseif ($import->type == 'courant') {
                    // --- IMPORT ÉCRITURES ---
                    $msg_type = "écritures";
                    $rowCompte = trim($rowMapped['compte'] ?? '');
                    $rowJournal = trim($rowMapped['journal'] ?? '');

                    if (empty($rowCompte) || empty($rowJournal)) continue;

                    $compteId = $planComptableIds[$rowCompte] ?? null;
                    $journalId = CodeJournal::where('company_id', $user->company_id)
                        ->where('code_journal', strtoupper($rowJournal))
                        ->value('id');

                    if (!$compteId || !$journalId) continue;

                    $debit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $rowMapped['debit'] ?? 0));
                    $credit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $rowMapped['credit'] ?? 0));

                    $dateStr = $rowMapped['jour'] ?? now()->toDateString();
                    if (strlen($dateStr) <= 2) {
                        $ex = ExerciceComptable::find($import->exercice_id);
                        $year = $ex ? $ex->debut->year : now()->year;
                        $month = $ex ? $ex->debut->month : now()->month;
                        $date = Carbon::create($year, $month, (int)$dateStr);
                    } else {
                        try { $date = Carbon::parse($dateStr); } catch(\Exception $e) { $date = now(); }
                    }

                    EcritureComptable::create([
                        'jour' => $date,
                        'reference' => strtoupper($rowMapped['reference'] ?? 'IMPORT'),
                        'compte_id' => $compteId,
                        'code_journal_id' => $journalId,
                        'libelle' => strtoupper($rowMapped['libelle'] ?? 'IMPORTATION EXTERNE'),
                        'debit' => $debit,
                        'credit' => $credit,
                        'exercice_id' => $import->exercice_id,
                        'company_id' => $user->company_id,
                        'user_id' => $user->id
                    ]);
                    $importedCount++;
                }
            }

            $import->update([
                'status' => 'committed',
                'error_log' => "Importation réussie : $importedCount $msg_type créés."
            ]);

            DB::commit();

            $msg = "Migration terminée : $importedCount $msg_type importés.";
            if ($duplicateCount > 0) $msg .= " ($duplicateCount doublons ignorés)";

            // Toujours ramener sur la page Configuration / Importation comme demandé par l'utilisateur
            return redirect()->route('admin.config.external_import')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors de l\'injection : ' .  $e->getMessage());
        }
    }

    /**
     * Tunnel d'Importation - Création rapide de compte à la volée
     */
    public function quickAccountCreate(Request $request)
    {
        $request->validate([
            'numero_compte' => 'required|string',
            'intitule' => 'required|string',
            'type_de_compte' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();
            
            $exists = PlanComptable::where('company_id', $user->company_id)
                ->where('numero_de_compte', $request->numero_compte)
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ce compte existe déjà.']);
            }

            PlanComptable::create([
                'numero_de_compte' => $request->numero_compte,
                'intitule' => strtoupper($request->intitule),
                'type_de_compte' => $request->type_de_compte ?? 'Bilan',
                'classe' => substr($request->numero_compte, 0, 1),
                'user_id' => $user->id,
                'company_id' => $user->company_id
            ]);

            return response()->json(['success' => true, 'message' => 'Compte créé avec succès.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Tunnel d'Importation - Annulation de l'import (Suppression du Staging)
     */
    public function cancelImport($id)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $import->delete();
            return redirect()->route('admin.import.hub')->with('success', 'Importation annulée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    /**
     * Modification d'une ligne spécifique du staging
     */
    public function updateRow(Request $request, $id, $index)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $data = $import->raw_data;
            
            if (!isset($data[$index])) {
                return response()->json(['success' => false, 'message' => 'Ligne non trouvée.'], 404);
            }

            // Récupérer les nouvelles valeurs envoyées
            $newValues = $request->input('values');
            foreach ($newValues as $colIndex => $value) {
                $data[$index][$colIndex] = $value;
            }

            $import->update(['raw_data' => $data]);

            return response()->json(['success' => true, 'message' => 'Ligne mise à jour avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Suppression d'une ligne spécifique du staging
     */
    public function deleteRow($id, $index)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $data = $import->raw_data;
            
            if (!isset($data[$index])) {
                return response()->json(['success' => false, 'message' => 'Ligne non trouvée.'], 404);
            }

            // Supprimer la ligne et réindexer
            array_splice($data, $index, 1);
            
            $import->update(['raw_data' => $data]);

            return response()->json(['success' => true, 'message' => 'Ligne supprimée de l\'import.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Module d'Exportation - Hub principal
     */
    public function exportHub()
    {
        return view('admin.config.export');
    }

    /**
     * Module d'Exportation - Traitement de la génération
     */
    public function exportProcess(Request $request)
    {
        $type = $request->input('type'); // plan_comptable, plan_tiers, journals, ecritures
        $format = $request->input('format'); // excel, csv, sage, fec
        $user = Auth::user();

        // Récupération des données selon le type
        $data = [];
        $filename = "export_" . $type . "_" . date('Ymd_His');

        switch ($type) {
            case 'plan_comptable':
                $data = PlanComptable::where('company_id', $user->company_id)->orderBy('numero_de_compte')->get();
                $headers = ['Compte', 'Intitule', 'Type', 'Classe'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->numero_de_compte, $row->intitule, $row->type_de_compte, $row->classe], ';');
                    }
                };
                break;

            case 'plan_tiers':
                $data = PlanTiers::with('compte')->where('company_id', $user->company_id)->orderBy('numero_de_tiers')->get();
                $headers = ['Numero Tiers', 'Intitule', 'Type', 'Compte Collectif'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->numero_de_tiers, $row->intitule, $row->type_de_tiers, $row->compte->numero_de_compte ?? ''], ';');
                    }
                };
                break;

            case 'journals':
                $data = CodeJournal::where('company_id', $user->company_id)->orderBy('code_journal')->get();
                $headers = ['Code', 'Intitule', 'Type'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->code_journal, $row->intitule, $row->type], ';');
                    }
                };
                break;

            case 'ecritures':
                $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
                    ->where('company_id', $user->company_id);
                
                if ($request->input('status') == 'validated') {
                    $query->where('statut', 'approved');
                }
                
                if ($request->input('journal') && $request->input('journal') != 'all') {
                    $query->where('code_journal_id', $request->input('journal'));
                }

                $data = $query->orderBy('date')->orderBy('created_at')->get();

                if ($format == 'fec') {
                    $headers = ['JournalCode', 'JournalLib', 'EcritureNum', 'EcritureDate', 'CompteNum', 'CompteLib', 'CompAuxNum', 'CompAuxLib', 'PieceRef', 'PieceDate', 'EcritureLib', 'Debit', 'Credit', 'EcritureLet', 'DateLet', 'ValidDate', 'Montantdevise', 'Idevise'];
                    $callback = function($file) use ($data) {
                        foreach ($data as $row) {
                            fputcsv($file, [
                                $row->codeJournal->code_journal ?? '',
                                $row->codeJournal->intitule ?? '',
                                $row->id,
                                Carbon::parse($row->date)->format('Ymd'),
                                $row->planComptable->numero_de_compte ?? '',
                                $row->planComptable->intitule ?? '',
                                $row->planTiers->numero_de_tiers ?? '',
                                $row->planTiers->intitule ?? '',
                                $row->reference_piece ?? '',
                                Carbon::parse($row->date)->format('Ymd'),
                                $row->description_operation,
                                number_format($row->debit, 2, ',', ''),
                                number_format($row->credit, 2, ',', ''),
                                '', '',
                                $row->updated_at->format('Ymd'),
                                '', ''
                            ], "\t");
                        }
                    };
                    $filename .= ".txt";
                    $contentType = 'text/plain';
                } elseif ($format == 'sage') {
                    $headers = []; 
                    $callback = function($file) use ($data) {
                        foreach ($data as $row) {
                            $line = implode("\t", [
                                Carbon::parse($row->date)->format('dmy'),
                                $row->codeJournal->code_journal ?? '',
                                $row->planComptable->numero_de_compte ?? '',
                                '',
                                $row->reference_piece ?? '',
                                $row->description_operation,
                                $row->debit > 0 ? 'D' : 'C',
                                number_format(max($row->debit, $row->credit), 2, '.', '')
                            ]);
                            fwrite($file, $line . "\r\n");
                        }
                    };
                    $filename .= ".txt";
                    $contentType = 'text/plain';
                } else {
                    $headers = ['Journal', 'Date', 'Compte', 'Tier', 'Piece', 'Libelle', 'Debit', 'Credit'];
                    $callback = function($file) use ($data) {
                        foreach ($data as $row) {
                            fputcsv($file, [
                                $row->codeJournal->code_journal ?? '',
                                $row->date,
                                $row->planComptable->numero_de_compte ?? '',
                                $row->planTiers->numero_de_tiers ?? '',
                                $row->reference_piece ?? '',
                                $row->description_operation,
                                $row->debit,
                                $row->credit
                            ], ';');
                        }
                    };
                }
                break;
        }

        $extension = ($format == 'excel' || $format == 'csv') ? '.csv' : '';
        if (!empty($extension)) {
            $filename .= $extension;
        }

        $headers_res = [
            "Content-type"        => $contentType ?? 'text/csv',
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return new StreamedResponse(function() use ($headers, $callback, $format) {
            $file = fopen('php://output', 'w');
            
            if ($format != 'sage' && $format != 'fec') {
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            }

            if (!empty($headers)) {
                $separator = ($format == 'fec') ? "\t" : ';';
                fputcsv($file, $headers, $separator);
            }

            $callback($file);
            fclose($file);
        }, 200, $headers_res);
    }
}


