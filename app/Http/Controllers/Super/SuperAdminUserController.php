<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminUserController extends Controller
{
    /**
     * Affiche la liste de tous les utilisateurs
     */
    public function index()
    {
        $users = User::with('company')->paginate(20);
        $companies = Company::all();
        
        return view('superadmin.users', compact('users', 'companies'));
    }

    /**
     * Affiche le formulaire de création d'utilisateur
     */
    public function create()
    {
        $companies = Company::where('is_active', 1)->get();
        $packs = \App\Models\pack::all();
        $permissions = config('accounting_permissions.permissions');
        return view('superadmin.create_user', compact('companies', 'packs', 'permissions'));
    }

    /**
     * Affiche le formulaire de création d'un administrateur (Dédié)
     */
    public function createAdmin()
    {
        $companies = Company::where('is_active', 1)->get();
        return view('superadmin.create_admin', compact('companies'));
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse',
            'password' => 'required|string|min:5',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,comptable,user',
            'is_active' => 'required|boolean',
            'pack_id' => 'nullable|exists:pack,id',
            'habilitations' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email_adresse' => $validated['email_adresse'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
            'pack_id' => $validated['pack_id'],
            'habilitations' => $validated['habilitations'] ?? [],
        ]);

        return redirect()->route('superadmin.users')
            ->with('success', 'Utilisateur créé avec succès !');
    }

    /**
     * Enregistre un nouvel administrateur avec toutes les habilitations par défaut
     */
    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse',
            'password' => 'required|string|min:5',
            'company_id' => 'required|exists:companies,id',
        ]);

        // Un administrateur a toutes les habilitations par défaut
        $allPermissions = config('accounting_permissions.permissions');
        $habilitations = [];
        foreach ($allPermissions as $key => $label) {
            $habilitations[$key] = "1";
        }

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email_adresse' => $validated['email_adresse'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
            'role' => 'admin',
            'is_active' => true,
            'habilitations' => $habilitations,
        ]);

        return redirect()->route('superadmin.users')
            ->with('success', 'Administrateur créé avec succès avec toutes les habilitations !');
    }

    /**
     * Affiche le formulaire d'édition d'utilisateur
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $companies = Company::where('is_active', 1)->get();
        $packs = \App\Models\pack::all();
        $permissions = config('accounting_permissions.permissions');
        return view('superadmin.edit_user', compact('user', 'companies', 'packs', 'permissions'));
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse,' . $id,
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,comptable,user',
            'is_active' => 'required|boolean',
            'pack_id' => 'nullable|exists:pack,id',
            'habilitations' => 'nullable|array',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('superadmin.users')
            ->with('success', 'Utilisateur mis à jour avec succès !');
    }

    /**
     * Supprime un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression du super admin
        if ($user->role === 'super_admin') {
            return redirect()->route('superadmin.users')
                ->with('error', 'Impossible de supprimer un super administrateur !');
        }

        $user->delete();

        return redirect()->route('superadmin.users')
            ->with('success', 'Utilisateur supprimé avec succès !');
    }
}
