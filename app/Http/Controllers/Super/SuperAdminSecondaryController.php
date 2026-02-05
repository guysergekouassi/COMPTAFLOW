<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SuperAdminSecondaryController extends Controller
{
    /**
     * Affiche la liste des Super Admins Secondaires
     */
    public function index()
    {
        // Seul le SA primaire peut accéder à cette page
        if (!Auth::user()->isPrimarySuperAdmin()) {
            return redirect()->route('superadmin.dashboard')
                ->with('error', 'Accès refusé : seul le Super Admin Principal peut gérer les Super Admins Secondaires.');
        }
        
        $secondaryAdmins = User::where('role', 'super_admin')
            ->where('super_admin_type', 'secondary')
            ->with('creator')
            ->get();
        
        $companies = Company::all();
        
        return view('superadmin.secondary_admins', compact('secondaryAdmins', 'companies'));
    }

    /**
     * Enregistre un nouveau Super Admin Secondaire
     */
    public function store(Request $request)
    {
        // Vérification de sécurité
        if (!Auth::user()->isPrimarySuperAdmin()) {
            return redirect()->back()
                ->with('error', 'Accès refusé : seul le Super Admin Principal peut créer des Super Admins Secondaires.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|max:191|unique:users,email_adresse',
            'password' => 'required|string|min:5',
            'supervised_companies' => 'required|array|min:1',
            'supervised_companies.*' => 'exists:companies,id',
        ]);
        
        User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email_adresse' => $validated['email_adresse'],
            'password' => Hash::make($validated['password']),
            'role' => 'super_admin',
            'super_admin_type' => 'secondary',
            'supervised_companies' => $validated['supervised_companies'],
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
        if (!Auth::user()->isPrimarySuperAdmin()) {
            return redirect()->back()
                ->with('error', 'Accès refusé : seul le Super Admin Principal peut modifier les Super Admins Secondaires.');
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
        if (!Auth::user()->isPrimarySuperAdmin()) {
            return redirect()->back()
                ->with('error', 'Accès refusé : seul le Super Admin Principal peut supprimer des Super Admins Secondaires.');
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
