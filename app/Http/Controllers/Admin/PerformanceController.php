<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EcritureComptable;
use App\Models\Company;
use App\Models\ExerciceComptable;
use App\Models\PlanTiers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentCompanyId = session('current_company_id', $user->company_id);
        $currentCompany = Company::find($currentCompanyId);

        // Si l'utilisateur n'est pas Admin, il ne devrait pas voir cette page (sécurité sup)
        // Mais middleware gère déjà.

        // Données Globales (Tous les utilisateurs de l'entreprise)
        $dashboardData = $this->getGlobalDashboardData($currentCompanyId);

        // Exercice en cours
         $exerciceEnCours = ExerciceComptable::where('company_id', $currentCompanyId)
            ->where('cloturer', 0)
            ->orderBy('date_debut', 'desc')
            ->first();

        return view('admin.performance.index', array_merge($dashboardData, [
            'currentCompany' => $currentCompany,
            'exerciceEnCours' => $exerciceEnCours
        ]));
    }

    private function getGlobalDashboardData($companyId)
    {
        try {
            $currentExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('cloturer', 0)
                ->first();
        } catch (\Exception $e) {
            $currentExercice = ExerciceComptable::where('company_id', $companyId)->first();
        }

        $exerciceId = $currentExercice ? $currentExercice->id : null;

        // KPI 1: Total Revenus (Global)
        $totalRevenue = EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) = ?', ['7']);
            })
            ->sum('credit');

        // KPI 2: Total Charges (Global)
        $totalExpenses = EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) = ?', ['6']);
            })
            ->sum('debit');

        $netResult = $totalRevenue - $totalExpenses;

        // KPI 4: Écritures du mois (Global)
        $monthlyEntries = EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->count();

        // KPI 5: Solde Trésorerie (Global)
        $cashBalance = EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->whereHas('planComptable', function($query) {
                $query->where('numero_de_compte', 'like', '5%');
            })
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->first()
            ->balance ?? 0;

        // KPI 6 & 7: Tiers (Global par définition)
        $clientCount = PlanTiers::where('company_id', $companyId)
            ->where(function($q) {
                $q->where('type_de_tiers', 'client')
                  ->orWhere('numero_de_tiers', 'like', '411%');
            })->count();

        $supplierCount = PlanTiers::where('company_id', $companyId)
            ->where(function($q) {
                $q->where('type_de_tiers', 'fournisseur')
                  ->orWhere('numero_de_tiers', 'like', '401%');
            })->count();

        // Progression Exercice
        $exerciceYear = $currentExercice ? $currentExercice->annee : date('Y');
        $exerciceProgress = 0;
        if ($currentExercice) {
            $start = Carbon::parse($currentExercice->date_debut);
            $now = Carbon::now();
            if ($now->greaterThan($start)) {
                $diffMonths = $start->diffInMonths($now);
                $exerciceProgress = min(100, round(($diffMonths / 12) * 100));
            }
        }

        // Charts Helpers (Global)
        $revenueChartData = $this->getGlobalRevenueChartData($companyId, $exerciceId);
        $expenseChartData = $this->getGlobalExpenseChartData($companyId, $exerciceId);

        // Dernières Écritures (Globales - toutes celles de l'entreprise)
        $recentEntries = EcritureComptable::with(['planComptable', 'planTiers', 'user']) // + User info
            ->where('company_id', $companyId)
            ->where('statut', 'approved')
            ->orderBy('date', 'desc')
            ->limit(10) // Plus d'historique pour l'admin
            ->get()
            ->map(function($entry) {
                $isIncome = $entry->credit > 0;
                return [
                    'description' => $entry->description_operation,
                    'date' => Carbon::parse($entry->date)->translatedFormat('d M Y'),
                    'journal' => $entry->codeJournal->libelle ?? 'Journal',
                    'amount' => $isIncome ? $entry->credit : $entry->debit,
                    'type' => $isIncome ? 'income' : 'expense',
                    'user_name' => $entry->user->name ?? 'Système' // Voir qui a fait l'écriture
                ];
            });

        return [
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netResult' => $netResult,
            'monthlyEntries' => $monthlyEntries,
            'cashBalance' => $cashBalance,
            'clientCount' => $clientCount,
            'supplierCount' => $supplierCount,
            'exerciceYear' => $exerciceYear,
            'exerciceProgress' => $exerciceProgress,
            'revenueChartData' => $revenueChartData,
            'expenseChartData' => $expenseChartData,
            'recentEntries' => $recentEntries,
        ];
    }

    private function getGlobalRevenueChartData($companyId, $exerciceId)
    {
        $revenues = EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) = ?', ['7']);
            })
            ->selectRaw('MONTH(date) as month, SUM(credit) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyData = array_fill(1, 12, 0);

        foreach ($revenues as $revenue) {
            $monthlyData[$revenue->month] = $revenue->total;
        }

        return [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
            'data' => array_values($monthlyData)
        ];
    }

    private function getGlobalExpenseChartData($companyId, $exerciceId)
    {
        $expenses = EcritureComptable::where('ecriture_comptables.company_id', $companyId)
            ->where('ecriture_comptables.statut', 'approved')
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) = ?', ['6']);
            })
            ->selectRaw('SUBSTRING(plan_comptables.numero_de_compte, 1, 2) as category, SUM(debit) as total')
            ->join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $categories = [
            '60' => 'Achats', '61' => 'Services ext.', '62' => 'Autres Svcs', 
            '63' => 'Impôts', '64' => 'Personnel', '65' => 'Autres', 
            '66' => 'Financier', '67' => 'Exceptionnel', '68' => 'Amort.', '69' => 'Impôt Bén.'
        ];

        $chartData = [];
        foreach ($expenses as $expense) {
            $category = $categories[$expense->category] ?? 'Autres';
            $chartData[] = [
                'category' => $category,
                'total' => $expense->total
            ];
        }

        return $chartData;
    }
}
