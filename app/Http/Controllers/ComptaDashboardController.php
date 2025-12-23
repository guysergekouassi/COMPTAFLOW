<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use App\Models\PlanTiers;
use App\Models\EcritureComptable;
use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
use App\Models\JournalSaisi;
use Carbon\Carbon;

class ComptaDashboardController extends Controller
{
    public function index()
    {
        $currentCompanyId = session('current_company_id');
        $currentCompany = Company::find($currentCompanyId);
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $habilitations = $this->getUserHabilitations($user, $currentCompanyId);

        // Récupérer les données comptables réelles
        $dashboardData = $this->getDashboardData($currentCompanyId);

        return view('comptable.comptdashboard', array_merge($dashboardData, [
            'currentCompany' => $currentCompany,
            'habilitations' => $habilitations
        ]));
    }

    private function getDashboardData($companyId)
    {
        $currentExercice = ExerciceComptable::where('cloturer', 0)
            ->first();

        $exerciceId = $currentExercice ? $currentExercice->id : null;

        // KPI 1: Total des revenus (classes 7)
        $totalRevenue = EcritureComptable::when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) = ?', ['7']);
            })
            ->sum('credit');

        // KPI 2: Total des charges (classes 6)
        $totalExpenses = EcritureComptable::where('company_id', $companyId)
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) = ?', ['6']);
            })
            ->sum('debit');

        // KPI 3: Résultat net
        $netResult = $totalRevenue - $totalExpenses;

        // KPI 4: Écritures du mois (Nombre d'écritures pour le mois en cours)
        $monthlyEntries = EcritureComptable::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->count();

        // KPI 5: Solde Trésorerie (Somme des comptes classe 5)
        $cashBalance = EcritureComptable::whereHas('planComptable', function($query) {
                $query->where('numero_de_compte', 'like', '5%');
            })
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->first()
            ->balance ?? 0;

        // KPI 6: Tiers Actifs (Clients et Fournisseurs)
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

        // KPI 7: Exercice en cours
        $exerciceYear = $currentExercice ? $currentExercice->annee : date('Y');
        
        // Calcul de la progression de l'exercice (approximation basée sur les mois écoulés)
        $exerciceProgress = 0;
        if ($currentExercice) {
            $start = Carbon::parse($currentExercice->date_debut);
            $now = Carbon::now();
            if ($now->greaterThan($start)) {
                $diffMonths = $start->diffInMonths($now);
                $exerciceProgress = min(100, round(($diffMonths / 12) * 100));
            }
        }

        // Données pour les graphiques
        $revenueChartData = $this->getRevenueChartData($companyId, $exerciceId);
        $expenseChartData = $this->getExpenseChartData($companyId, $exerciceId);

        // Dernières écritures
        $recentEntries = EcritureComptable::with(['planComptable', 'planTiers'])
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get()
            ->map(function($entry) {
                $isIncome = $entry->credit > 0;
                return [
                    'description' => $entry->description_operation,
                    'date' => Carbon::parse($entry->date)->translatedFormat('d M Y'),
                    'journal' => $entry->codeJournal->libelle ?? 'Journal',
                    'amount' => $isIncome ? $entry->credit : $entry->debit,
                    'type' => $isIncome ? 'income' : 'expense'
                ];
            });

        // Dernières opérations de trésorerie
        $recentTreasuryEntries = EcritureComptable::with(['planComptable', 'codeJournal'])
            ->where('company_id', $companyId)
            ->whereHas('planComptable', function($query) {
                $query->where('numero_de_compte', 'like', '5%');
            })
            ->orderBy('date', 'desc')
            ->limit(3)
            ->get()
            ->map(function($entry) {
                $isCredit = $entry->credit > 0;
                return [
                    'title' => $entry->planComptable->intitule ?? 'Opération Trésorerie',
                    'poste' => $entry->description_operation,
                    'date' => Carbon::parse($entry->date)->translatedFormat('d M Y'),
                    'amount' => $isCredit ? -$entry->credit : $entry->debit,
                    'icon' => str_contains(strtolower($entry->planComptable->intitule ?? ''), 'banque') ? 'university' : 'money-bill-wave'
                ];
            });

        // Alertes comptables
        $alerts = $this->getAccountingAlerts($companyId, $exerciceId);

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
            'recentTreasuryEntries' => $recentTreasuryEntries,
            'alerts' => $alerts
        ];
    }

    private function getRevenueChartData($companyId, $exerciceId)
    {
        $revenues = EcritureComptable::where('company_id', $companyId)
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

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = 0;
        }

        foreach ($revenues as $revenue) {
            $monthlyData[$revenue->month] = $revenue->total;
        }

        return [
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
            'data' => array_values($monthlyData)
        ];
    }

    private function getExpenseChartData($companyId, $exerciceId)
    {
        $expenses = EcritureComptable::where('ecriture_comptables.company_id', $companyId)
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
            '60' => 'Achats',
            '61' => 'Services extérieurs',
            '62' => 'Autres services extérieurs',
            '63' => 'Impôts et taxes',
            '64' => 'Charges de personnel',
            '65' => 'Autres charges',
            '66' => 'Charges financières',
            '67' => 'Charges exceptionnelles',
            '68' => 'Dotations aux amortissements',
            '69' => 'Impôt sur les bénéfices'
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

    private function getAccountingAlerts($companyId, $exerciceId)
    {
        $alerts = [];

        // Alerte 1: Écritures sans pièce justificative
        $entriesWithoutDocs = EcritureComptable::where('company_id', $companyId)
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereNull('piece_justificatif')
            ->count();

        if ($entriesWithoutDocs > 0) {
            $alerts[] = [
                'title' => 'Pièces justificatives manquantes',
                'description' => "$entriesWithoutDocs écritures sans pièce justificative",
                'priority' => $entriesWithoutDocs > 10 ? 'high' : 'medium',
                'icon' => 'file-exclamation'
            ];
        }

        // Alerte 2: Exercice proche de la clôture
        if ($currentExercice = ExerciceComptable::find($exerciceId)) {
            $endDate = Carbon::parse($currentExercice->date_fin);
            $daysUntilEnd = $endDate->diffInDays(Carbon::now());

            if ($daysUntilEnd <= 30) {
                $alerts[] = [
                    'title' => 'Clôture de l\'exercice',
                    'description' => "L'exercice se termine dans $daysUntilEnd jours",
                    'priority' => $daysUntilEnd <= 7 ? 'high' : 'medium',
                    'icon' => 'calendar-check'
                ];
            }
        }

        // Alerte 3: Solde des comptes de trésorerie négatif
        $negativeBalances = EcritureComptable::where('company_id', $companyId)
            ->when($exerciceId, function($query) use ($exerciceId) {
                return $query->where('exercices_comptables_id', $exerciceId);
            })
            ->whereHas('planComptable', function($query) {
                $query->whereRaw('SUBSTRING(numero_de_compte, 1, 1) IN (?, ?)', ['5', '6']);
            })
            ->selectRaw('plan_comptable_id, SUM(credit) - SUM(debit) as balance')
            ->groupBy('plan_comptable_id')
            ->having('balance', '<', 0)
            ->count();

        if ($negativeBalances > 0) {
            $alerts[] = [
                'title' => 'Soldes négatifs',
                'description' => "$negativeBalances comptes présentent un solde négatif",
                'priority' => 'high',
                'icon' => 'exclamation-triangle'
            ];
        }

        return $alerts;
    }

    protected function getUserHabilitations($user, $companyId)
    {
        // Si l'utilisateur est SuperAdmin, il voit tout
        if ($user->isSuperAdmin()) {
            return ['dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie',
                    'modal_saisie_direct', 'exercice_comptable', 'accounting_entry_real',
                    'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
                    'accounting_balance', 'accounting_balance_tiers', 'compte_exploitation',
                    'flux_tresorerie', 'tableau_amortissements', 'etat_tiers', 'compte_resultat',
                    'bilan', 'etats_analytiques', 'etats_previsionnels', 'user_management', 'compagny_information'];
        }

        // Si l'utilisateur est l'Admin de la compagnie, il a tous les accès comptables.
        if ($user->isAdmin()) {
             return ['dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie',
                    'modal_saisie_direct', 'exercice_comptable', 'accounting_entry_real',
                    'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
                    'accounting_balance', 'accounting_balance_tiers', 'compte_exploitation',
                    'flux_tresorerie', 'tableau_amortissements', 'etat_tiers', 'compte_resultat',
                    'bilan', 'etats_analytiques', 'etats_previsionnels', 'user_management', 'compagny_information'];
        }

        // Pour les utilisateurs créés (Comptable, standard, etc.):
        $habilitations = $user->habilitations;

        // Si le champ est une chaîne JSON, il faut le décoder.
        if (is_string($habilitations)) {
            $habilitations = json_decode($habilitations, true);
        }

        // Assurer que c'est un tableau valide
        if (!is_array($habilitations)) {
            $habilitations = [];
        }

        // DEBUG: Log pour voir ce qui se passe
        Log::info('Habilitations avant ajout: ' . json_encode($habilitations));
        Log::info('modal_saisie_direct present: ' . (in_array('modal_saisie_direct', $habilitations) ? 'yes' : 'no'));
        Log::info('accounting_entry_real present: ' . (in_array('accounting_entry_real', $habilitations) ? 'yes' : 'no'));

        // Ajouter automatiquement accounting_entry_real si l'utilisateur a modal_saisie_direct
        if (in_array('modal_saisie_direct', $habilitations) && !in_array('accounting_entry_real', $habilitations)) {
            $habilitations[] = 'accounting_entry_real';
            Log::info('accounting_entry_real ajouté!');
        }

        // DEBUG: Forcer l'ajout de accounting_entry_real pour le test
        if (!in_array('accounting_entry_real', $habilitations)) {
            $habilitations[] = 'accounting_entry_real';
            Log::info('accounting_entry_real forcé!');
        }

        Log::info('Habilitations finales: ' . json_encode($habilitations));

        // Assurez-vous que c'est un tableau valide, sinon retournez un tableau vide.
        return $habilitations;
    }
}
