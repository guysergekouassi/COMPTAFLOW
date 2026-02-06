<?php

namespace App\Http\Controllers\Super;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        // === VUE GLOBALE & GOUVERNANCE ===

        // 1. KPIs CARTES DE SCORE
        $totalCompanies = Company::count();
        $activeCompanies = Company::where('is_active', true)->count();
        
        // Volume de Traitement (Total des écritures comptables système)
        $volumeTraitement = \App\Models\EcritureComptable::count();

        // Taux de Complétion (Exercices Clôturés / Total Exercices)
        $totalExercices = \App\Models\ExerciceComptable::count();
        $closedExercices = \App\Models\ExerciceComptable::where('cloturer', true)->count();
        $tauxCompletion = $totalExercices > 0 ? round(($closedExercices / $totalExercices) * 100, 1) : 0;

        // Alertes de Sécurité (Simulé pour l'instant)
        // Idéalement, relier à une table 'audit_logs' ou 'failed_jobs'
        $securityAlerts = 0; 

        // 2. DONNÉES POUR GRAPHIQUES

        // Répartition par Secteur (Top 5)
        $sectorsData = Company::select('activity', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('activity')
            ->groupBy('activity')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        
        $sectorLabels = $sectorsData->pluck('activity')->toArray();
        $sectorCounts = $sectorsData->pluck('total')->toArray();

        // Croissance des Données (Création de compagnies par mois sur les 12 derniers mois)
        $growthData = Company::select(
            \Illuminate\Support\Facades\DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            \Illuminate\Support\Facades\DB::raw('count(*) as total')
        )
        ->where('created_at', '>=', now()->subYear())
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $growthLabels = $growthData->pluck('month')->map(function($m) {
            return \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y');
        })->toArray();
        $growthCounts = $growthData->pluck('total')->toArray();


        return view('superadmin.dashboard', compact(
            'totalCompanies', 
            'activeCompanies', 
            'volumeTraitement', 
            'tauxCompletion', 
            'securityAlerts',
            'sectorLabels',
            'sectorCounts',
            'growthLabels',
            'growthCounts'
        ));
    }

    public function entities()
    {
        // Cette méthode sert l'ANCIEN Dashboard (Gestion des Entités)
        $user = Auth::user();
        
        // Récupérer les compagnies mères avec leurs sous-compagnies
        $rootCompanies = Company::with(['admin', 'users', 'children.admin', 'children.users'])
            ->whereNull('parent_company_id')
            ->get();
        
        // KPIs pour le haut de page
        $totalCompanies = Company::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalUsers = User::where('role', 'user')->count();
        $activeCompanies = Company::where('is_active', true)->count();
        $companies = Company::all();

        return view('superadmin.entities', compact('rootCompanies', 'totalCompanies', 'totalAdmins', 'totalUsers', 'activeCompanies', 'companies'));
    }
}
