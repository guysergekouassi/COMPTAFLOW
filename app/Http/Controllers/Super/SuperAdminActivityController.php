<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\EcritureComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminActivityController extends Controller
{
    /**
     * Affiche le tableau de bord des activités
     */
    public function index()
    {
        // Statistiques d'activité
        $stats = [
            'total_users' => User::count(),
            'active_users_today' => User::whereDate('created_at', today())->count(),
            'total_companies' => Company::count(),
            'total_entries_today' => EcritureComptable::whereDate('created_at', today())->count(),
        ];

        // Activités récentes (dernières écritures comptables)
        $recentActivities = EcritureComptable::with(['user', 'company'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Activités par entreprise (30 derniers jours)
        $companies = Company::all();
        $activitiesByCompany = $companies->map(function($company) {
            $company->ecritures_comptables_count = EcritureComptable::where('company_id', $company->id)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count();
            return $company;
        });

        return view('superadmin.activities', compact('stats', 'recentActivities', 'activitiesByCompany'));
    }
}
