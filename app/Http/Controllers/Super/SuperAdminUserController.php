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
        return view('superadmin.create_user', compact('companies'));
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,comptable,user',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('superadmin.users')
            ->with('success', 'Utilisateur créé avec succès !');
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,comptable,user',
        ]);

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
