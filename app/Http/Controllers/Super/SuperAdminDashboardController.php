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

        $companies = Company::with('children', 'admin', 'users')
                            ->whereNull('parent_company_id')
                            ->get();

        // 2. Récupération des données pour les KPIs
        // Ici, on peut laisser le comptage global si le Super Admin doit voir le total de TOUTES les entités.
        $totalCompanies = Company::count(); // Total de toutes les compagnies (mères + filles)
        $activeCompanies = Company::where('is_active', true)->count();

        // Comptage des utilisateurs par rôle
        $totalAdmins = User::where('role', 'admin')->count();
        $totalComptables = User::where('role', 'comptable')->count();
        $superAdminsCount = User::where('role', 'super_admin')->count();

        $totalUsers = $totalAdmins + $totalComptables + $superAdminsCount;


        $adminUsers = User::where('role', 'admin')->get();


        return view('superadmin.dashboard', compact(
            'totalCompanies',
            'activeCompanies',
            'totalAdmins',
            'totalComptables',
            'totalUsers',
            'superAdminsCount',
            'companies',
            'adminUsers'
        ));
    }


}
