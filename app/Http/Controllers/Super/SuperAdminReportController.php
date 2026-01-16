<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\EcritureComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminReportController extends Controller
{
    /**
     * Affiche les rapports de performance
     */
    public function index()
    {
        // KPIs globaux
        $kpis = [
            'total_companies' => Company::count(),
            'active_companies' => Company::where('is_active', 1)->count(),
            'total_users' => User::where('role', '!=', 'super_admin')->count(),
            'total_entries' => EcritureComptable::count(),
        ];

        // Croissance mensuelle des entreprises
        $monthlyGrowth = Company::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();

        // Utilisation par entreprise (Top 10)
        $companies = Company::all();
        $companyUsage = $companies->map(function($company) {
            $company->ecritures_comptables_count = EcritureComptable::where('company_id', $company->id)->count();
            return $company;
        })->sortByDesc('ecritures_comptables_count')->take(10);

        // Répartition des utilisateurs par rôle
        $usersByRole = User::select('role', DB::raw('COUNT(*) as count'))
            ->where('role', '!=', 'super_admin')
            ->groupBy('role')
            ->get();

        return view('superadmin.reports', compact('kpis', 'monthlyGrowth', 'companyUsage', 'usersByRole'));
    }
}
