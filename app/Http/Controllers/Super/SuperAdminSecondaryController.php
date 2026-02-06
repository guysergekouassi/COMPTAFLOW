<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SuperAdminSecondaryController extends Controller
{
    /**
     * Affiche la liste des Super Admins Secondaires
     */
    public function index()
    {
        if (!Auth::user()->hasPermission('superadmin.secondary.index')) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Accès refusé : vous n\'avez pas les droits nécessaires.');
        }

        $admins = User::where('role', 'super_admin')
            ->where('super_admin_type', 'secondary')
            ->with('company')
            ->paginate(15);

        return view('superadmin.secondary_admins', compact('admins'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('superadmin.secondary.index')) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Accès refusé.');
        }

        $companies = Company::where('is_active', true)->get();
        $modules = Config::get('accounting_permissions.permissions', []);
        return view('superadmin.create_secondary_superadmin', compact('companies', 'modules'));
    }

    /**
     * Enregistre un nouveau Super Admin Secondaire
     */
    public function store(Request $request)
    {
        // Vérification de sécurité
        if (!Auth::user()->hasPermission('superadmin.secondary.index')) {
            return redirect()->back()
                ->with('error', 'Accès refusé.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse',
            'password' => 'required|string|min:5',
            'supervised_companies' => 'required|array|min:1',
            'supervised_companies.*' => 'exists:companies,id',
            'habilitations' => 'nullable|array',
        ]);
        
        User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email_adresse' => $validated['email_adresse'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
            'super_admin_type' => 'secondary',
            'supervised_companies' => $validated['supervised_companies'],
            'habilitations' => $request->habilitations ?? [],
            'is_active' => true,
            'created_by_id' => Auth::id(),
        ]);
        
        return redirect()->route('superadmin.secondary.index')
            ->with('success', 'Super Admin Secondaire créé avec succès !');
    }

    /**
     * Met à jour un Super Admin Secondaire
     */
    public function update(Request $request, $id)
    {
        // Vérification de sécurité
        if (!Auth::user()->hasPermission('superadmin.secondary.index')) {
            return redirect()->back()
                ->with('error', 'Accès refusé.');
        }
        
        $user = User::findOrFail($id);
        
        // Vérifier qu'on modifie bien un SA secondaire
        if (!$user->isSecondarySuperAdmin()) {
            return redirect()->back()
                ->with('error', 'Cet utilisateur n\'est pas un Super Admin Secondaire.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse,' . $id,
            'supervised_companies' => 'required|array|min:1',
            'supervised_companies.*' => 'exists:companies,id',
            'is_active' => 'required|boolean',
        ]);
        
        $updateData = [
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email_adresse' => $validated['email_adresse'],
            'supervised_companies' => $validated['supervised_companies'],
            'is_active' => $validated['is_active'],
        ];
        
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }
        
        $user->update($updateData);
        
        return redirect()->route('superadmin.secondary.index')
            ->with('success', 'Super Admin Secondaire mis à jour avec succès !');
    }

    /**
     * Supprime un Super Admin Secondaire
     */
    public function destroy($id)
    {
        // Vérification de sécurité
        if (!Auth::user()->hasPermission('superadmin.secondary.index')) {
            return redirect()->back()
                ->with('error', 'Accès refusé.');
        }
        
        $user = User::findOrFail($id);
        
        // Vérifier qu'on supprime bien un SA secondaire
        if (!$user->isSecondarySuperAdmin()) {
            return redirect()->back()
                ->with('error', 'Cet utilisateur n\'est pas un Super Admin Secondaire.');
        }
        
        $user->delete();
        
        return redirect()->route('superadmin.secondary.index')
            ->with('success', 'Super Admin Secondaire supprimé avec succès !');
    }
}
