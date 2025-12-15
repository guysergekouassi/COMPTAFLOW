<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UserController; // Utilisé pour l'initialisation des habilitations

class CompanyController extends Controller

{
    public function index()
    {
        // Cette méthode index est utilisée pour la page d'information de compagnie
        $userController = new UserController();
        $allHabilitations = $userController->getAllHabilitations();

        $companies = Company::all();
        $adminUsers = User::where('role', 'admin')->get();
        $user = Auth::user();

        // S'assurer d'utiliser la compagnie du contexte actuel
        $currentCompanyId = session('current_company_id', $user->company_id);
        $company = Company::find($currentCompanyId);

        return view('compagny_information', compact('company','adminUsers'));
    }


    /**
     * Crée une nouvelle compagnie et son administrateur (action SuperAdmin).
     */
    public function store(Request $request)
    {
        // 1. Validation complète
        $request->validate([
            // Champs de la COMPAGNIE
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'juridique_form' => 'required|string|max:255',
            'activity' => 'required|string|max:255',
            'social_capital' => 'required|numeric|min:0',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:20',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:255',
            // 'email_adresse' => 'nullable|email|max:191|unique:companies,email_adresse',
            'phone_number' => 'nullable|string|min:10',
            'identification_TVA' => 'nullable|string|max:50',

            // Champs de l'ADMINISTRATEUR
            'admin_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email_adresse' => 'required|email|unique:users,email_adresse',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $userController = new UserController();

        DB::beginTransaction();
        try {
            // Création de l'Utilisateur Admin
            $adminUser = User::create([
                'name' => $request->admin_name,
                'last_name' => $request->admin_last_name,
                'email_adresse' => $request->admin_email_adresse,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin',
                'company_id' => null, // Initialisation à null avant la création de la compagnie
                'habilitations' => array_fill_keys($userController->getAllHabilitations(), true),
                'is_online' => false,
                'is_active' => true,
            ]);

            // Création de la Compagnie (Compagnie B)
            $company = Company::create([
                'company_name' => $request->company_name,
                'is_active' => true,
                'juridique_form' => $request->juridique_form,
                'activity' => $request->activity,
                'social_capital' => $request->social_capital,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'city' => $request->city,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'email_adresse' => $request->email_adresse,
                'identification_TVA' => $request->identification_TVA,
                'user_id' => $adminUser->id, // L'ID de l'Admin créateur
                // 'parent_company_id' reste NULL pour les compagnies de niveau supérieur (Compagnie B)
            ]);

            // Mettre à jour l'utilisateur Admin avec l'ID de sa compagnie principale
            $adminUser->company_id = $company->id;
            $adminUser->save();


            DB::commit();

            return redirect()->route('superadmin.dashboard')
                     ->with('success', 'La compagnie "' . $company->company_name . '" et son administrateur ont été créés avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'juridique_form' => 'nullable|string',
            'activity' => 'nullable|string',
            'social_capital' => 'nullable|numeric',
            'adresse' => 'nullable|string',
            'code_postal' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'email_adresse' => 'nullable|email',
            'identification_TVA' => 'nullable|string',
        ]);

        $company->update($validated);

        return back()->with('success', 'Informations de l\'entreprise mises à jour avec succès.');
    }


    /**
     * Permet à un Admin (non SuperAdmin) de créer une sous-compagnie/comptabilité (B', B'', etc.).
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminStoreCompany(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Assurer que l'utilisateur est un 'admin'
        if ($user->role !== 'admin') {
            abort(403, 'Accès non autorisé.');
        }

        // 2. Validation
        $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'juridique_form' => 'required|string|max:255',
            'activity' => 'required|string|max:255',
            'social_capital' => 'required|numeric|min:0',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:20',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:255',
            'phone_number' => 'nullable|string|min:10',
            'email_adresse' => 'nullable|email|max:191|unique:companies,email_adresse',
            'identification_TVA' => 'nullable|string|max:50',

        ]);

        DB::beginTransaction();
        try {
            // Création de la sous-compagnie (B', B'', etc.)
            $company = Company::create([
                'company_name' => $request->company_name,
                'is_active' => true,
                'juridique_form' => $request->juridique_form,
                'activity' => $request->activity,
                'social_capital' => $request->social_capital,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'city' => $request->city,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'email_adresse' => $request->email_adresse,
                'identification_TVA' => $request->identification_TVA,
                // Le lien clé ! La compagnie B' est rattachée à la compagnie B de l'Admin Manager
                'parent_company_id' => $user->company_id,
                // L'utilisateur 'user_id' de la sous-compagnie n'est pas nécessaire, car elle est gérée par l'Admin parent
            ]);

            DB::commit();

            // Basculer automatiquement vers la nouvelle compagnie (optionnel mais pratique)
            session(['current_company_id' => $company->id]);


            return redirect()->back()
                ->with('success', 'La sous-compagnie/comptabilité "' . $company->company_name . '" a été créée et sélectionnée avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log de l'erreur pour le debug
            // Log::error('Erreur lors de la création de la sous-compagnie : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création de la sous-compagnie.');
        }
    }




    private function getManagedCompanies()
    {
        $user = Auth::user();

        $mainCompanies = Company::where('user_id', $user->id)
                                ->whereNull('parent_company_id') // On s'assure que c'est bien une principale
                                ->get();

        $subCompanies = Company::where('parent_company_id', $user->company_id)->get();


        $managedCompanies = $mainCompanies->merge($subCompanies)->unique('id');

        // Ajouter la compagnie principale de l'utilisateur (celle qui définit son contexte)
        // Cela couvre le cas où l'utilisateur n'est pas le créateur (user_id), mais est assigné à une compagnie (company_id).
        $contextCompany = Company::find($user->company_id);
        if ($contextCompany && !$managedCompanies->contains('id', $contextCompany->id)) {
            $managedCompanies->push($contextCompany);
        }

        // Trier par nom pour un affichage plus propre dans le sélecteur
        return $managedCompanies->sortBy('company_name');
    }



    public function showCompanySelectorView()
    {
        $managedCompanies = $this->getManagedCompanies();
        $user = Auth::user();

        // Récupérer l'ID de la compagnie actuellement sélectionnée dans la session
        $currentCompanyId = session('current_company_id', $user->company_id);

        // Assurez-vous que cette vue existe et contient le HTML du `<select>`
        return view('user_management', compact('managedCompanies', 'currentCompanyId'));
    }


}
