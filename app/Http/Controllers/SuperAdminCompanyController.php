<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Http\Controllers\UserController; // Assuming this is needed for getAllHabilitations()
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminCompanyController extends Controller
{

    public function store(Request $request)
    {


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
        'adresse' => 'required|string|max:255',
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
}
