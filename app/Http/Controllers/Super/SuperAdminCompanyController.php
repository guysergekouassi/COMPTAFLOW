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
    //   dd($request->all());
        Log::info('Données reçues :', $request->all());
        $userController = new UserController();

        $request->validate([

        'company_name' => 'required|string|max:255|unique:companies,company_name',
        'admin_name' => 'required|string|max:255',
        'admin_last_name' => 'required|string|max:255',
        'admin_email_adresse' => 'required|email|unique:users,email_adresse',
        'admin_password' => [
        'required',
        'string',
        'min:8',
        'confirmed',

            ],
        'juridique_form' => 'required|string|max:255',
        'activity' => 'required|string|max:255',
        'social_capital' => 'required|numeric|min:0',
        'adresse' => 'string|max:255',
        'code_postal' => 'required|string|max:20',
        'city' => 'required|string|max:255',
        'country' => 'required|string|max:255',

        'phone_number' => 'nullable|string|max:20',
        'identification_TVA' => 'nullable|string|max:50',
        ]);
        
        DB::beginTransaction();
        try {
            
            $company = Company::create([
                'company_name' => $request->company_name,
                'is_active' => true,
                'juridique_form' => $request->juridique_form,
                'activity' => $request->activity,
                 'user_id' => 0,
                'social_capital' => $request->social_capital,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'city' => $request->city,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'identification_TVA' => $request->identification_TVA,
             ]);
             
            
            $adminUser = User::create([
                'name' => $request->admin_name,
                'last_name' => $request->admin_last_name,
                'email_adresse' => $request->admin_email_adresse,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'company_id' => $company->id,
            'is_active' => true,
            'created_by_id' => Auth::id(),
            'habilitations' => array_fill_keys($userController->getAllHabilitations(), true),
                'is_online' => false,
            ]);
           
            $company->user_id = $adminUser->id;
            $company->save();
            DB::commit();

            return redirect()->route('superadmin.dashboard')->with('success', 'Compagnie et Admin créés avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }


    public function toggleStatus(Company $company)
    {
        $company->is_active = !$company->is_active;
        $company->save();

        User::where('company_id', $company->id)->update([
            'is_active' => $company->is_active
        ]);

        $status = $company->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "La compagnie **{$company->company_name}** a été {$status} avec succès.");
    }


    public function destroy(Company $company)
    {

        try {
            $company->delete();
            return back()->with('success', "La compagnie {$company->company_name} a été supprimée.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression de la compagnie.');

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
