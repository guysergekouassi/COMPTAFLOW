<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAccessController extends Controller
{
    /**
     * Affiche la page de gestion des accès
     */
    public function index()
    {
        $companies = Company::withCount('users')->get();
        $users = User::with('company')->where('role', '!=', 'super_admin')->get();
        
        return view('superadmin.access_control', compact('companies', 'users'));
    }

    /**
     * Bloque une entreprise ET tous ses utilisateurs (blocage en cascade)
     */
    public function blockCompany(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        // Bloquer l'entreprise
        $company->update([
            'is_blocked' => true,
            'block_reason' => $validated['reason'],
            'blocked_at' => now(),
            'blocked_by' => Auth::id(),
        ]);
        
        // BLOCAGE EN CASCADE : Bloquer tous les utilisateurs de cette entreprise
        $blockedUsersCount = User::where('company_id', $id)
            ->where('role', '!=', 'super_admin') // Ne jamais bloquer un SA
            ->update([
                'is_blocked' => true,
                'block_reason' => "Entreprise bloquée : {$validated['reason']}",
                'blocked_at' => now(),
                'blocked_by' => Auth::id(),
            ]);
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'entreprise {$company->company_name} et {$blockedUsersCount} utilisateur(s) ont été bloqués");
    }

    /**
     * Débloque une entreprise
     */
    public function unblockCompany($id)
    {
        $company = Company::findOrFail($id);
        
        $company->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
        ]);
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'entreprise {$company->company_name} a été débloquée");
    }

    /**
     * Bloque un utilisateur
     */
    public function blockUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher le blocage d'un super admin
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.access')
                ->with('error', 'Impossible de bloquer un super administrateur');
        }
        
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        $user->update([
            'is_blocked' => true,
            'block_reason' => $validated['reason'],
            'blocked_at' => now(),
            'blocked_by' => Auth::id(),
        ]);
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'utilisateur {$user->name} a été bloqué");
    }

    /**
     * Débloque un utilisateur
     */
    public function unblockUser($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
        ]);
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'utilisateur {$user->name} a été débloqué");
    }

    /**
     * Supprime une entreprise
     */
    public function destroyCompany($id)
    {
        $company = Company::findOrFail($id);
        
        // Supprimer l'entreprise
        $company->delete();
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'entreprise {$company->company_name} a été supprimée définitivement.");
    }

    /**
     * Supprime un utilisateur
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de tout super admin
        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.access')
                ->with('error', 'Impossible de supprimer un super administrateur !');
        }
        
        // PROTECTION ADDITIONNELLE : Empêcher la suppression du SA primaire
        if ($user->isPrimarySuperAdmin()) {
            return redirect()->route('superadmin.access')
                ->with('error', 'Impossible de supprimer le Super Admin Principal ! Cette action est strictement interdite.');
        }
        
        $user->delete();
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'utilisateur {$user->name} a été supprimé définitivement.");
    }
}
