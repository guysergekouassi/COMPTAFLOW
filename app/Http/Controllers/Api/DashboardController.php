<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanTiers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController regroupe les statistiques pour les 3 interfaces mobile :
 * 1. Super Admin (Vue globale du système)
 * 2. Admin (Vue globale de son entreprise/sous-entreprises)
 * 3. Comptable (Vue opérationnelle de ses activités)
 */
class DashboardController extends Controller
{
    /**
     * Retourne les données du tableau de bord selon le rôle de l'utilisateur.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        // Détermination de l'entreprise et de l'exercice contextuel
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');

        $data = [
            'role' => $role,
            'user' => [
                'name' => $user->name,
                'last_name' => $user->last_name,
            ],
            'stats' => []
        ];

        if ($user->isSuperAdmin()) {
            $data['stats'] = $this->getSuperAdminStats();
        } elseif ($user->isAdmin()) {
            $data['stats'] = $this->getAdminStats($companyId, $exerciceId);
        } else {
            $data['stats'] = $this->getComptableStats($user->id, $companyId, $exerciceId);
        }

        return response()->json($data);
    }

    /**
     * Statistiques pour le Super Administrateur.
     */
    private function getSuperAdminStats()
    {
        return [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('is_active', true)->count(),
            'total_users' => User::count(),
            'volume_traitement' => EcritureComptable::count(),
            'taux_completion' => $this->getGlobalCompletionRate(),
            'growth_chart' => $this->getCompanyGrowthData(),
        ];
    }

    /**
     * Statistiques pour l'Administrateur d'entreprise.
     */
    private function getAdminStats($companyId, $exerciceId = null)
    {
        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        $revenue = $this->getTotalRevenue($companyId, $exerciceId);
        $expenses = $this->getTotalExpenses($companyId, $exerciceId);

        return [
            'total_revenue' => $revenue,
            'total_expenses' => $expenses,
            'net_result' => $revenue - $expenses,
            'monthly_entries' => $this->getMonthlyEntriesCount($companyId),
            'cash_balance' => $this->getCashBalance($companyId),
            'clients_count' => PlanTiers::where('company_id', $companyId)->where('type_de_tiers', 'client')->count(),
            'suppliers_count' => PlanTiers::where('company_id', $companyId)->where('type_de_tiers', 'fournisseur')->count(),
            'revenue_chart' => $this->getRevenueChartData($companyId, $exerciceId),
        ];
    }

    /**
     * Statistiques pour le Comptable.
     */
    private function getComptableStats($userId, $companyId, $exerciceId = null)
    {
        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        return [
            'my_monthly_entries' => EcritureComptable::where('user_id', $userId)
                ->whereMonth('date', Carbon::now()->month)
                ->count(),
            'pending_approvals' => DB::table('approvals')->where('status', 'pending')->count(), // Exemple simplifié
            'recent_entries' => EcritureComptable::where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get(['id', 'description_operation', 'date', 'debit', 'credit']),
            'alerts' => $this->getComptableAlerts($userId, $companyId, $exerciceId),
        ];
    }

    // --- Méthodes Utilitaires ---

    private function getGlobalCompletionRate()
    {
        $total = ExerciceComptable::count();
        $closed = ExerciceComptable::where('cloturer', true)->count();
        return $total > 0 ? round(($closed / $total) * 100, 1) : 0;
    }

    private function getCompanyGrowthData()
    {
        return Company::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('count(*) as total')
        )
        ->where('created_at', '>=', now()->subYear())
        ->groupBy('month')
        ->orderBy('month')
        ->get();
    }

    private function getActiveExerciceId($companyId)
    {
        $exercice = ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();
        return $exercice ? $exercice->id : null;
    }

    private function getTotalRevenue($companyId, $exerciceId)
    {
        return EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->when($exerciceId, fn($q) => $q->where('exercices_comptables_id', $exerciceId))
            ->whereHas('planComptable', fn($q) => $q->where('numero_de_compte', 'like', '7%'))
            ->sum('credit');
    }

    private function getTotalExpenses($companyId, $exerciceId)
    {
        return EcritureComptable::where('company_id', $companyId)
            ->where('statut', 'approved')
            ->when($exerciceId, fn($q) => $q->where('exercices_comptables_id', $exerciceId))
            ->whereHas('planComptable', fn($q) => $q->where('numero_de_compte', 'like', '6%'))
            ->sum('debit');
    }

    private function getMonthlyEntriesCount($companyId)
    {
        return EcritureComptable::where('company_id', $companyId)
            ->whereMonth('date', Carbon::now()->month)
            ->count();
    }

    private function getCashBalance($companyId)
    {
        return EcritureComptable::where('company_id', $companyId)
            ->whereHas('planComptable', fn($q) => $q->where('numero_de_compte', 'like', '5%'))
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->first()
            ->balance ?? 0;
    }

    private function getRevenueChartData($companyId, $exerciceId)
    {
        return EcritureComptable::where('company_id', $companyId)
            ->when($exerciceId, fn($q) => $q->where('exercices_comptables_id', $exerciceId))
            ->whereHas('planComptable', fn($q) => $q->where('numero_de_compte', 'like', '7%'))
            ->selectRaw('MONTH(date) as month, SUM(credit) as total')
            ->groupBy('month')
            ->get();
    }

    private function getComptableAlerts($userId, $companyId, $exerciceId)
    {
        $alerts = [];
        $noDocCount = EcritureComptable::where('user_id', $userId)
            ->whereNull('piece_justificatif')
            ->count();

        if ($noDocCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "$noDocCount écritures sans pièce justificative",
            ];
        }

        return $alerts;
    }
}
