<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\PlanComptable;
use App\Models\CodeJournal;
use App\Models\PlanTiers;

class FusionController extends Controller
{
    /**
     * Dashboard de Fusion : Affiche ce qui peut être récupéré de la maison mère
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $company = Company::findOrFail($companyId);

        // Vérification : Seulement pour les sous-compagnies
        if (!$company->parent_company_id) {
            return redirect()->route('admin.dashboard')->with('error', 'Cette fonctionnalité est réservée aux filiales.');
        }

        $parentId = $company->parent_company_id;
        $parentCompany = Company::findOrFail($parentId);

        // Statistiques pour comparer (Parent vs Enfant)
        $stats = [
            'accounts' => [
                'parent' => PlanComptable::withoutGlobalScopes()->where('company_id', $parentId)->count(),
                'current' => PlanComptable::where('company_id', $companyId)->count(),
            ],
            'journals' => [
                'parent' => CodeJournal::withoutGlobalScopes()->where('company_id', $parentId)->count(),
                'current' => CodeJournal::where('company_id', $companyId)->count(),
            ],
            'tiers' => [
                'parent' => PlanTiers::withoutGlobalScopes()->where('company_id', $parentId)->count(),
                'current' => PlanTiers::where('company_id', $companyId)->count(),
            ]
        ];

        return view('admin.fusion.index', compact('company', 'parentCompany', 'stats'));
    }

    /**
     * Exécute la fusion (Injection des données)
     */
    public function run(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $company = Company::findOrFail($companyId);
        $parentId = $company->parent_company_id;

        if (!$parentId) {
            return back()->with('error', 'Action non autorisée.');
        }

        $scope = $request->input('scope', []); // accounts, journals, tiers
        $mode = $request->input('mode', 'append'); // append only for safety

        DB::beginTransaction();
        try {
            $log = [];

            // 1. PLAN COMPTABLE
            if (in_array('accounts', $scope)) {
                $parentAccounts = PlanComptable::withoutGlobalScopes()->where('company_id', $parentId)->get();
                $count = 0;
                foreach ($parentAccounts as $pAccount) {
                    $exists = PlanComptable::where('company_id', $companyId)
                        ->where('numero_de_compte', $pAccount->numero_de_compte)
                        ->exists();
                    
                    if (!$exists) {
                        $newAccount = $pAccount->replicate(['id', 'created_at', 'updated_at']);
                        $newAccount->company_id = $companyId;
                        $newAccount->user_id = $user->id; 
                        $newAccount->save();
                        $count++;
                    }
                }
                $log[] = "$count comptes comptables importés.";
            }

            // 2. CODIFICATIONS JOURNAUX
            if (in_array('journals', $scope)) {
                $parentJournals = CodeJournal::withoutGlobalScopes()->where('company_id', $parentId)->get();
                $count = 0;
                foreach ($parentJournals as $pJournal) {
                    $exists = CodeJournal::where('company_id', $companyId)
                        ->where('code_journal', $pJournal->code_journal)
                        ->exists();
                    
                    if (!$exists) {
                        $newJournal = $pJournal->replicate(['id', 'created_at', 'updated_at']);
                        $newJournal->company_id = $companyId;
                        $newJournal->save();
                        $count++;
                    }
                }
                $log[] = "$count codes journaux importés.";
            }

            // 3. PLAN TIERS
            if (in_array('tiers', $scope)) {
                $parentTiers = PlanTiers::withoutGlobalScopes()->where('company_id', $parentId)->get();
                $count = 0;
                foreach ($parentTiers as $pTier) {
                    $exists = PlanTiers::where('company_id', $companyId)
                        ->where('numero_de_tiers', $pTier->numero_de_tiers)
                        ->exists();
                    
                    if (!$exists) {
                        $newTier = $pTier->replicate(['id', 'created_at', 'updated_at']);
                        $newTier->company_id = $companyId;
                        $newTier->save();
                        $count++;
                    }
                }
                $log[] = "$count tiers importés.";
            }

            DB::commit();
            return back()->with('success', 'Fusion terminée : ' . implode(' ', $log));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la fusion : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialise (Supprime) les données fusionnées
     * ATTENTION : Seulement si aucune écriture n'existe
     */
    public function reset(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        // Sécurité : Vérifier si des écritures existent
        $hasEcritures = \App\Models\EcritureComptable::where('company_id', $companyId)->exists();
        
        if ($hasEcritures) {
            return back()->with('error', 'Impossible d\'annuler la fusion : Des écritures comptables ont déjà été passées. Veuillez d\'abord supprimer les écritures.');
        }

        DB::beginTransaction();
        try {
            // Suppression des données liées à l'entreprise courante
            $deletedAccounts = PlanComptable::where('company_id', $companyId)->delete();
            $deletedJournals = CodeJournal::where('company_id', $companyId)->delete();
            $deletedTiers = PlanTiers::where('company_id', $companyId)->delete();
            
            DB::commit();
            return back()->with('success', "Réinitialisation effectuée. Données supprimées : $deletedAccounts comptes, $deletedJournals journaux, $deletedTiers tiers.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }
}
