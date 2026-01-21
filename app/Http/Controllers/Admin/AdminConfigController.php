<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminConfigController extends Controller
{
    /**
     * Dashboard de configuration pour l'Administrateur
     */
    public function hub()
    {
        $user = Auth::user();
        $mainCompany = Company::where('id', $user->company_id)->first();
        
        // Statistiques du modèle de référence
        $stats = [
            'accounts' => PlanComptable::where('company_id', $user->company_id)->count(),
            'tiers' => PlanTiers::where('company_id', $user->company_id)->count(),
            'journals' => CodeJournal::where('company_id', $user->company_id)->count(),
        ];

        return view('admin.config.hub', compact('mainCompany', 'stats'));
    }

    /**
     * Mise à jour des paramètres globaux de l'entreprise
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'accounting_system' => 'required|string|in:SYSCOHADA,PCG,CUSTOM',
            'account_digits' => 'required|integer|min:4|max:12',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
        
        $company->update([
            'accounting_system' => $request->accounting_system,
            'account_digits' => $request->account_digits,
        ]);

        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Gestion du Modèle de Plan Comptable
     */
    public function planComptable()
    {
        $user = Auth::user();
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->orderBy('numero_de_compte')
            ->get();

        return view('admin.config.plan_comptable', compact('plansComptables'));
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

        return view('admin.config.plan_tiers', compact('planTiers'));
    }

    /**
     * Gestion de la Structure des Journaux
     */
    public function journals()
    {
        $user = Auth::user();
        $journals = CodeJournal::where('company_id', $user->company_id)
            ->orderBy('code_journal')
            ->get();

        return view('admin.config.journals', compact('journals'));
    }

    /**
     * Charger le Plan Comptable SYSCOHADA Standard
     */
    public function loadSyscohadaPlan()
    {
        try {
            $user = Auth::user();
            $company = Company::findOrFail($user->company_id);
            $digits = $company->account_digits ?? 8;

            // Définition simplifiée du plan SYSCOHADA (Extraits du plan OHADA révisé)
            $templates = [
                '101' => 'Capital social',
                '106' => 'Réserves',
                '12' => 'Résultat de l\'exercice',
                '13' => 'Subventions d\'investissement',
                '16' => 'Emprunts et dettes assimilées',
                '21' => 'Immobilisations incorporelles',
                '22' => 'Immobilisations corporelles',
                '24' => 'Immobilisations financières',
                '28' => 'Amortissements',
                '31' => 'Stocks de marchandises',
                '401' => 'Fournisseurs',
                '411' => 'Clients',
                '421' => 'Personnel - Rémunérations dues',
                '431' => 'Sécurité sociale',
                '441' => 'État - Impôts sur les bénéfices',
                '445' => 'État - TVA',
                '521' => 'Banques',
                '541' => 'Caisse',
                '601' => 'Achats de marchandises',
                '611' => 'Sous-traitance générale',
                '613' => 'Locations',
                '622' => 'Honoraires',
                '631' => 'Impôts et taxes',
                '641' => 'Charges de personnel',
                '651' => 'Autres charges de gestion',
                '661' => 'Charges d\'intérêts',
                '701' => 'Ventes de marchandises',
                '706' => 'Services facturés',
                '751' => 'Autres produits de gestion',
                '771' => 'Produits financiers',
                '81' => 'Valeur ajoutée',
                '87' => 'Résultat net de l\'exercice'
            ];

            DB::beginTransaction();
            $count = 0;
            foreach ($templates as $prefix => $intitule) {
                $numero = str_pad($prefix, $digits, '0', STR_PAD_RIGHT);
                
                $exists = PlanComptable::where('company_id', $user->company_id)
                    ->where('numero_de_compte', $numero)
                    ->exists();

                if (!$exists) {
                    PlanComptable::create([
                        'numero_de_compte' => $numero,
                        'intitule' => $intitule,
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'is_active' => true
                    ]);
                    $count++;
                }
            }
            DB::commit();

            return redirect()->back()->with('success', "$count comptes SYSCOHADA chargés avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
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

        // Normaliser le numéro de compte (Padding)
        $numero = str_pad($request->numero_de_compte, $digits, '0', STR_PAD_RIGHT);

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

        return redirect()->back()->with('success', 'Compte ajouté au modèle avec succès.');
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
            return redirect()->back()->with('success', 'Importation terminée avec succès.');
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

            return redirect()->back()->with('success', "$count journaux standards chargés avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un nouveau Journal Master
     */
    public function storeJournal(Request $request)
    {
        $request->validate([
            'code_journal' => 'required|string|max:10',
            'intitule' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        $user = Auth::user();

        $exists = CodeJournal::where('company_id', $user->company_id)
            ->where('code_journal', $request->code_journal)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le code journal {$request->code_journal} existe déjà.");
        }

        CodeJournal::create([
            'code_journal' => strtoupper($request->code_journal),
            'intitule' => strtoupper($request->intitule),
            'type' => $request->type,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
        ]);

        return redirect()->back()->with('success', 'Journal ajouté au modèle avec succès.');
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
            return redirect()->back()->with('success', 'Importation des journaux terminée.');
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
            return redirect()->back()->with('success', 'Importation des tiers terminée.');
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
}
