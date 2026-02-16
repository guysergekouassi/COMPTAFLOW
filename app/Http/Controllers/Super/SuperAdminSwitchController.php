<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SuperAdminSwitchController extends Controller
{
    /**
     * Affiche la page de switch
     */
    public function index()
    {
        $companies = Company::with('users')->get();
        $currentSwitchedCompany = Session::get('switched_company_id');
        $currentSwitchedUser = Session::get('switched_user_id');
        
        return view('superadmin.switch', compact('companies', 'currentSwitchedCompany', 'currentSwitchedUser'));
    }

    /**
     * Bascule vers une entreprise spécifique
     */
    public function switchToCompany($companyId)
    {
        $company = Company::findOrFail($companyId);
        
        // Stocker l'ID du super admin original
        if (!Session::has('original_super_admin_id')) {
            Session::put('original_super_admin_id', Auth::id());
        }
        
        // Stocker l'entreprise vers laquelle on bascule
        Session::put('switched_company_id', $companyId);
        Session::put('current_company_id', $companyId);
        
        // Trouver un admin de cette entreprise pour se connecter en tant que
        $admin = User::where('company_id', $companyId)
            ->where('role', 'admin')
            ->first();
        
        if ($admin) {
            // Stocker les infos de switch
            Session::put('switched_user_id', $admin->id);
            Session::put('switched_company_id', $companyId);
            Session::put('current_company_id', $companyId);
            
            // Connexion réelle en tant qu'administrateur de l'entreprise
            Auth::loginUsingId($admin->id);
            
            return redirect()->route('admin.dashboard')
                ->with('success', "Vous êtes maintenant connecté à l'entreprise : {$company->company_name}");
        }
        
        return redirect()->route('superadmin.switch')
            ->with('error', "Aucun administrateur trouvé pour cette entreprise");
    }

    /**
     * Se connecte en tant qu'utilisateur spécifique
     */
    public function switchToUser($userId)
    {
        $user = User::findOrFail($userId);
        
        // Stocker l'ID du super admin original
        if (!Session::has('original_super_admin_id')) {
            Session::put('original_super_admin_id', Auth::id());
        }
        
        // Stocker l'utilisateur vers lequel on bascule
        Session::put('switched_user_id', $userId);
        Session::put('switched_company_id', $user->company_id);
        Session::put('current_company_id', $user->company_id);
        
        // Connexion réelle en tant qu'utilisateur cible
        Auth::loginUsingId($userId);
        
        // Rediriger selon le rôle
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')
                ->with('success', "Vous êtes maintenant connecté en tant que : {$user->name}");
        } elseif ($user->role === 'comptable') {
            return redirect()->route('compta.dashboard')
                ->with('success', "Vous êtes maintenant connecté en tant que : {$user->name}");
        } else {
            return redirect()->route('app.dashboard')
                ->with('success', "Vous êtes maintenant connecté en tant que : {$user->name}");
        }
    }

    /**
     * Retourne à l'interface super admin
     */
    public function returnToSuperAdmin()
    {
        $originalAdminId = Session::get('original_super_admin_id');
        
        if ($originalAdminId) {
            // Nettoyer les sessions de switch
            Session::forget('switched_company_id');
            Session::forget('switched_user_id');
            Session::forget('original_super_admin_id');
            Session::forget('current_company_id');
            
            // Reconnecter le Super Admin
            Auth::loginUsingId($originalAdminId);
            
            return redirect()->route('superadmin.switch')
                ->with('success', 'Vous êtes de retour dans l\'interface Super Admin');
        }
        
        return redirect()->route('app.dashboard');
    }
}
