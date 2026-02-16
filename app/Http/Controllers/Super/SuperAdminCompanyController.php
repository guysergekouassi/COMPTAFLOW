<?php

namespace App\Http\Controllers\Super; // CORRECT NAMESPACE

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Http\Controllers\UserController; // Import Controller from Root
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TreasuryCategory;

class SuperAdminCompanyController extends Controller
{
    // METHODE MANQUANTE: create() - Le contrôleur original dans Super avait une méthode create().
    // Le contrôleur Root (utilisateur) N'EN AVAIT PAS, il avait 'store'.
    // Nous devons ajouter la méthode create() sinon la route Route::get(... 'create') va échouer.
    // Je copie celle du contrôleur Super original.
    public function create()
    {
        return view('superadmin.create_company');
    }

    // Le reste est le code de l'utilisateur (Root)

    public function store(Request $request)
    {
        Log::info('Données reçues :', $request->all());

        $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'juridique_form' => 'required|string|max:255',
            'activity' => 'required|string|max:255',
            'social_capital' => 'required|numeric|min:0',
            'adresse' => 'string|max:255',
            'code_postal' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email_adresse' => 'nullable|email|max:255',
            'identification_TVA' => 'nullable|string|max:50',
            'parent_company_id' => 'nullable|exists:companies,id',
        ]);
        
        DB::beginTransaction();
        try {
            $company = Company::create([
                'company_name' => $request->company_name,
                'is_active' => $request->input('is_active', true),
                'juridique_form' => $request->juridique_form,
                'activity' => $request->activity,
                'user_id' => 0, // Sera assigné plus tard lors de la création de l'admin
                'social_capital' => $request->social_capital,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'city' => $request->city,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'email_adresse' => $request->email_adresse,
                'identification_TVA' => $request->identification_TVA,
                'parent_company_id' => $request->parent_company_id,
             ]);
             
            // Création automatique des trois catégories de flux indispensables pour le TFT
            $tftCategories = [
                'I. Flux de trésorerie des activités opérationnelles',
                'II. Flux de trésorerie des activités d\'investissement',
                'III. Flux de trésorerie des activités de financement',
            ];

            foreach ($tftCategories as $catName) {
                TreasuryCategory::create([
                    'name' => $catName,
                    'company_id' => $company->id,
                ]);
            }
             
            DB::commit();

            return redirect()->route('superadmin.entities')->with('success', 'Entreprise créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage())->withInput();
        }
    }


    public function toggleStatus(Company $company)
    {
        $company->is_active = !$company->is_active;
        $company->save();

        // 1. Cascade aux utilisateurs de la compagnie elle-même
        User::where('company_id', $company->id)->update([
            'is_active' => $company->is_active
        ]);

        // 2. Cascade aux sous-compagnies (si c'est une mère)
        if (!$company->parent_company_id) {
            $childrenIds = Company::where('parent_company_id', $company->id)->pluck('id');
            
            // Bloquer toutes les sous-compagnies
            Company::whereIn('id', $childrenIds)->update(['is_active' => $company->is_active]);

            // Bloquer tous les utilisateurs de toutes les sous-compagnies
            User::whereIn('company_id', $childrenIds)->update(['is_active' => $company->is_active]);
        }

        $status = $company->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "La compagnie **{$company->company_name}** et toutes ses dépendances ont été {$status} avec succès.");
    }


    public function destroy(Company $company)
    {
        DB::beginTransaction();
        try {
            // 1. Si c'est une mère, supprimer toutes les filles
            if (!$company->parent_company_id) {
                $children = Company::where('parent_company_id', $company->id)->get();
                foreach($children as $child) {
                    // Supprimer les utilisateurs de la fille
                    User::where('company_id', $child->id)->delete();
                    $child->delete();
                }
            }

            // 2. Supprimer les utilisateurs de la compagnie elle-même
            User::where('company_id', $company->id)->delete();

            // 3. Supprimer la compagnie
            $company->delete();

            DB::commit();
            return back()->with('success', "La compagnie {$company->company_name} et toutes ses dépendances ont été supprimées définitivement.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression en cascade : ' . $e->getMessage());
        }
    }
    
    // METHODE MANQUANTE: edit() - Nécessaire pour resource controller ou route existante
     public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('superadmin.edit_company', compact('company'));
    }

public function update(Request $request, Company $company)
{
    // 1. Validation des données
    $request->validate([
        'company_name' => 'required|string|max:255|unique:companies,company_name,' . $company->id,
        'admin_name' => 'required|string|max:255',
        'admin_last_name' => 'required|string|max:255',
        'admin_email_adresse' => 'required|email|unique:users,email_adresse,' . $company->user_id,
        'admin_password' => 'nullable|string|min:8|confirmed',
        'juridique_form' => 'required|string|max:255',
        'activity' => 'required|string|max:255',
        'social_capital' => 'required|numeric|min:0',
        'adresse' => 'required|string|max:255',
        'code_postal' => 'required|string|max:20',
        'city' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'phone_number' => 'nullable|string|max:20',
        'identification_TVA' => 'nullable|string|max:50',
    ]);

    DB::beginTransaction();
    try {
        // 2. Mise à jour de la compagnie
        $company->update([
            'company_name' => $request->company_name,
            'juridique_form' => $request->juridique_form,
            'activity' => $request->activity,
            'social_capital' => $request->social_capital,
            'adresse' => $request->adresse,
            'code_postal' => $request->code_postal,
            'city' => $request->city,
            'country' => $request->country,
            'phone_number' => $request->phone_number,
            'identification_TVA' => $request->identification_TVA,
        ]);

        // 3. Mise à jour de l'admin associé
        $adminUser = User::find($company->user_id);

        if ($adminUser) {
            $userData = [
                'name' => $request->admin_name,
                'last_name' => $request->admin_last_name,
                'email_adresse' => $request->admin_email_adresse,
            ];

            // Mise à jour du mot de passe si fourni
            if ($request->filled('admin_password')) {
                $userData['password'] = Hash::make($request->admin_password);
            }

            // Traitement des habilitations : si c'est un admin, on force tout.
            if ($adminUser->role === 'admin') {
                $userController = new UserController();
                $userData['habilitations'] = array_fill_keys($userController->getAllHabilitations(), true);
            } else {
                $requestedHabilitations = $request->input('habilitations', []);
                $formattedHabilitations = [];
                foreach ($requestedHabilitations as $key => $value) {
                    $formattedHabilitations[$key] = true;
                }
                $userData['habilitations'] = $formattedHabilitations;
            }

            $adminUser->update($userData);
        }

        DB::commit();
        return redirect()->route('superadmin.dashboard')->with('success', 'La compagnie et son administrateur ont été mis à jour avec succès.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
    }
}
}
