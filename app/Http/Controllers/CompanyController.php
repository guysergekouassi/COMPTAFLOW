<?php

namespace App\Http\Controllers;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UserController; // Utilisé pour l'initialisation des habilitations
use App\Models\TreasuryCategory;

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

        if (!$company) {
            return redirect()->route('app.dashboard')
                ->with('error', "Aucune entreprise n'est sélectionnée : impossible d'afficher la fiche.");
        }

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
            'social_capital' => 'nullable|numeric|min:0',
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

            return redirect()->route('superadmin.dashboard')
                     ->with('success', 'La compagnie "' . $company->company_name . '" et son administrateur ont été créés avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



    /**
     * Met à jour la fiche de l'entreprise depuis l'interface de comptabilité.
     *
     * Accessible à l'administrateur de l'entreprise et à tout utilisateur
     * disposant de l'habilitation "compagny_information", uniquement sur une
     * entreprise qu'il gère.
     */
    public function update(Request $request, Company $company)
    {
        $user = Auth::user();

        if (!$user->hasPermission('compagny_information') && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, "Vous n'avez pas l'autorisation de modifier la fiche de l'entreprise.");
        }

        // On ne peut modifier que l'entreprise sur laquelle on travaille,
        // ou l'une des entreprises rattachées que l'on gère.
        $currentCompanyId = session('current_company_id', $user->company_id);
        $autorise = $user->isSuperAdmin()
            || (int) $company->id === (int) $currentCompanyId
            || $this->getManagedCompanies()->contains('id', $company->id);

        if (!$autorise) {
            abort(403, "Cette entreprise ne fait pas partie de celles que vous gérez.");
        }

        $validated = $request->validate([
            // Identité
            'company_name'         => 'required|string|max:255',
            'juridique_form'       => 'nullable|string|max:255',
            'activity'             => 'nullable|string|max:255',
            'social_capital'       => 'nullable|numeric|min:0',
            'phone_number'         => 'required|string|max:50',

            // Localisation
            'adresse'              => 'required|string|max:255',
            'siege_social'         => 'nullable|string|max:255',
            'commune'              => 'nullable|string|max:120',
            'quartier'             => 'nullable|string|max:120',
            'city'                 => 'required|string|max:50',
            'code_postal'          => 'required|string|max:20',
            'country'              => 'required|string|max:100',

            // Fiscal & DGI
            'idu'                  => 'nullable|string|max:100',
            'ncc'                  => 'nullable|string|max:255',
            'identification_TVA'   => 'nullable|string|max:255',
            'rccm'                 => 'nullable|string|max:255',
            'cnps'                 => 'nullable|string|max:255',
            'compte_contribuable'  => 'nullable|string|max:100',
            'regime'               => 'nullable|string|max:80',
            'rattachement_dgi'     => 'nullable|string|max:255',

            // Local & expert-comptable
            'proprietaire_local'   => 'nullable|string|max:255',
            'reference_cadastrale' => 'nullable|string|max:100',
            'expert_comptable_nom' => 'nullable|string|max:255',
            'expert_comptable_ncc' => 'nullable|string|max:255',

            // Logo & alertes
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'sticker_solde_alerte' => 'nullable|integer|min:0|max:9999',
        ], [
            'logo.image' => 'Le logo doit être une image (JPG, PNG, SVG ou WEBP).',
            'logo.max'   => 'Le logo ne doit pas dépasser 2 Mo.',
        ]);

        // L'adresse e-mail reste gérée à la création : elle n'est pas modifiable ici.
        unset($validated['email_adresse'], $validated['logo']);

        // Remplacement du logo
        if ($request->hasFile('logo')) {
            $ancienLogo = $company->logo_path;
            $validated['logo_path'] = $request->file('logo')->store('logos/companies', 'public');

            if ($ancienLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($ancienLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ancienLogo);
            }
        }

        // Le trait LogsActivity du modèle Company trace automatiquement
        // l'ancienne et la nouvelle valeur de chaque champ modifié.
        $company->update($validated);

        return back()->with('success', "Informations de l'entreprise mises à jour avec succès.");
    }

    /*
     * La création d'une sous-entreprise (entreprise dans une entreprise) a été
     * retirée : une comptabilité se crée désormais depuis Mon Espace, où chaque
     * personne retrouve toutes celles qu'elle gère.
     * Anciennes méthodes supprimées : adminCreateCompany() / adminStoreCompany().
     */

    private function getManagedCompanies()
    {
        $user = Auth::user();

        $managedCompanies = Company::where('user_id', $user->id)
                                ->orWhere('parent_company_id', $user->company_id)
                                ->orWhere('id', $user->company_id)
                                ->get()
                                ->unique('id');

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
