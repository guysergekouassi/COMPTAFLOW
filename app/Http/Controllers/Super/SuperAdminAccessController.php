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
     * Bloque une entreprise
     */
    public function blockCompany(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        $company->update([
            'is_blocked' => true,
            'block_reason' => $validated['reason'],
            'blocked_at' => now(),
            'blocked_by' => Auth::id(),
        ]);
        
        return redirect()->route('superadmin.access')
            ->with('success', "L'entreprise {$company->company_name} a été bloquée");
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
        if ($user->role === 'super_admin') {
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
}
