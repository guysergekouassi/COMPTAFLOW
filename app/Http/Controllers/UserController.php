<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
// Assurez-vous que ces modèles existent pour les KPI


class UserController extends Controller
{


    private $allHabilitations = [
        'dashboard',
        'Analytics',
        'plan_comptable',
        'plan_tiers',
        'journaux',
        'grand_livre',
        'balance',
        'etats_financiers',
        'fichier_joindre',
        'tresorerie',
        'parametre',
        'modal_saisie_direct',
        'accounting_journals',
        'exercice_comptable',
        'Etat_de_rapprochement_bancaire',
        'Gestion_de_la_trésorerie',
        'gestion_analytique',
        'gestion_tiers',
        'user_management',
        'gestion_immobilisations',
        'gestion_reportings',
        'compagny_information',
        'gestion_stocks',
        'grand_livre_tiers',
        'poste',



    ];



    private function processHabilitations(string $role, array $input): array
    {
        // 1. Si admin, tout est activé.
        if ($role === 'admin') {
            return array_fill_keys($this->allHabilitations, true);
        }

        // 2. Pour les autres rôles (comptable), on initialise tout à false.
        $habilitations = array_fill_keys($this->allHabilitations, false);

        // Les entrées du formulaire sont un tableau simple (e.g., [0 => "perm1", 1 => "perm2"])
        $selectedHabilitations = array_values($input);

        // 3. Active les permissions sélectionnées si elles sont valides.
        foreach ($selectedHabilitations as $hab) {
            if (in_array($hab, $this->allHabilitations)){
                $habilitations[$hab] = true;
            }
        }
        return $habilitations;
    }

    // NOUVELLE MÉTHODE : Pour les statistiques du Tableau de Bord Administrateur
    public function dashboardStats()
    {
        $adminUser = Auth::user();
        $companyId = $adminUser->company_id;

        // --- KPI 1: Total comptes créés (Total Users dans la compagnie) ---
        $totalUsers = User::where('company_id', $companyId)->count();

        // --- KPI 2: Total comptes Connectés (dans sa compagnie) ---
        $connectedUsers = User::where('company_id', $companyId)
                              ->where('is_online', true)
                              ->count();

        // --- KPI 3: Plan Comptable créé par un Comptable (aujourd'hui) ---
        $plansToday = PlanComptable::where('company_id', $companyId)
                                     ->whereDate('created_at', today()) // Filtre les créations du jour
                                     ->count();

        // --- KPI 4: Exercice Comptable créé par un Comptable (aujourd'hui) ---
        $exercicesToday = ExerciceComptable::where('company_id', $companyId)
                                         ->whereDate('created_at', today()) // Filtre les créations du jour
                                         ->count();

        // Récupérer les habilitations de l'utilisateur connecté (pour le menu latéral)
        $habilitations = $adminUser->habilitations ?? [];

        // Retourner la vue du tableau de bord avec les KPI
        return view('admin.dashboard', compact(
            'totalUsers',
            'connectedUsers',
            'plansToday',
            'exercicesToday',
            'habilitations'
        ));
    }


   // app/Http/Controllers/UserController.php

public function stat_online()
    {
        $allHabilitations = $this->allHabilitations;
        $user = Auth::user();
        $userCompanyId = $user->company_id;

        // 1. Déterminer la liste des IDs de TOUTES les compagnies gérées (mère + enfants)
        // Ceci inclut la compagnie principale de l'Admin ET toutes les sous-compagnies qu'il a créées.
        $managedCompanyIds = Company::where('id', $userCompanyId)
                           ->orWhere('parent_company_id', $userCompanyId)
                           ->orWhere('user_id', $user->id) // NOUVELLE RÈGLE D'AFFICHAGE
                           ->pluck('id')
                           ->toArray();

        // 2. Récupérer les objets Company pour le sélecteur dans la vue (la liste des comptabilités)
        $managedCompanies = Company::whereIn('id', $managedCompanyIds)->get();


         $users = User::with('company')
                    ->whereIn('company_id', $managedCompanyIds)
                    ->orderBy('created_at', 'desc')
                    ->get();

        // 3. Récupérer TOUS les utilisateurs de CES compagnies gérées pour les tableaux
        // C'est ici que l'utilisateur nouvellement créé est inclus.
        $allUsers = User::with('company')
            ->whereIn('company_id', $managedCompanyIds)
            ->get();

        // 4. Filtrer les collections pour les Admins et les Comptables
        $adminUsers =$allUsers->where('role', 'admin');
        $comptableUsers = $allUsers->where('role', 'comptable');

        // 5. Récupérer les habilitations de l'utilisateur connecté
        $habilitations = $user->habilitations ?? [];

        // 6. Statistiques :
        $totalUsers = $allUsers->count();
        $connectedUsers = $allUsers->where('is_online', true)->count();
        $offlineUsers = $totalUsers - $connectedUsers;
        // La variable $companies = Company::all(); est retirée car $managedCompanies est la source correcte.

        //Nouveau filtre par compagnie: comptables des compagnies sélectionnées


        // 7. Retourner la vue en passant toutes les variables nécessaires.
        return view('user_management', [
            // Variables principales de la vue :
            'users' => $allUsers,
            'totalUsers' => $totalUsers,
            'user' => $users,
            'connectedUsers' => $connectedUsers,
            'offlineUsers' => $offlineUsers,
            'habilitations' => $habilitations,

            // Variables pour les listes séparées :
            'admins' => $adminUsers,
            'comptables' => $comptableUsers,
            'managedCompanies' => $managedCompanies,
            'userCompanyId' => $userCompanyId,
            'allHabilitations' => $allHabilitations,
        ]);
    }

public function store(Request $request)
{
    $admin = Auth::user();

    // 1. Déterminer les compagnies que l'Admin a le droit de modifier


    $allowedCompanyIds = Company::where('id', $admin->company_id)
                            ->orWhere('parent_company_id', $admin->company_id)
                            // AJOUT DE LA NOUVELLE CONDITION
                            ->orWhere('user_id', $admin->id)
                            ->pluck('id')
                            ->toArray();

    // Ajoutez 'new' pour permettre la création d'une nouvelle compagnie
    $allowedCompanyIds[] = 'new';



    // Vérification d'autorisation
    if ($admin->role !== 'admin') {
        // Rediriger avec une erreur si l'utilisateur n'est pas un admin
        return back()->with('error', 'Seul un administrateur peut créer des comptes utilisateurs.');
    }
    // dd($allowedCompanyIds);
    // 2. Validation des données de la requête
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email_adresse' => 'required|email|unique:users,email_adresse',
        'password' => 'required|string|min:8', // min:8 est plus sûr pour un mot de passe
        'role' => 'required|in:admin,comptable',
        'is_online' => 'nullable|boolean',
        'company_id' => 'required',
        'habilitations' => 'nullable|array',

        'habilitations.*' => 'in:' . implode(',', $this->allHabilitations),

        'company_id' => 'required|in:'. implode(',', $allowedCompanyIds),
        // 'new_company_name' => 'nullable|string|max:255',
        'new_company_name' => 'required_if:company_id,new|nullable|string|max:255',

    ]);

    // Si la validation réussit, le script continue ici.
    $targetCompanyId = null;

    // 3. Logique de création de la nouvelle compagnie ou d'affectation
    if ($validated['company_id'] === 'new') {

        if (empty($validated['new_company_name'])) {
            return back()->with('error', 'Le nom de la nouvelle compagnie est requis.')->withInput();
        }

        // Création de la nouvelle compagnie
        $newCompany = Company::create([
            'company_name' => $validated['new_company_name'],
            'parent_company_id' => $admin->company_id,
            'user_id' => $admin->id,
        ]);
        $targetCompanyId = $newCompany->id;

    } else {
        // L'utilisateur est associé à une compagnie existante
        $targetCompanyId = $validated['company_id'];
    }

    // 4. Préparation des données pour l'enregistrement
    $validated['company_id'] = $targetCompanyId;
    $validated['password'] = Hash::make($validated['password']);

    // Traitement des habilitations (assurez-vous que processHabilitations existe)
    $validated['habilitations'] = $this->processHabilitations(
        $validated['role'],
        $request->input('habilitations', []) // Utiliser $request->input car $validated peut ne pas contenir 'habilitations' si c'est null
    );

    //  dd($validated);
    // 5. Création de l'utilisateur
    User::create($validated);

    return redirect()->route('user_management')->with('success', 'Utilisateur créé avec succès.');
}


 private function getManagedCompanyIds(User $admin): array
    {
        // L'Admin peut gérer :
        // 1. Sa compagnie principale ($admin->company_id)
        // 2. Toutes les compagnies qui ont sa compagnie principale comme parent_company_id

        $allowedCompanyIds = Company::where('id', $admin->company_id)
                                    ->orWhere('parent_company_id', $admin->company_id)
                                    ->pluck('id')
                                    ->toArray();

        // Cas d'urgence : Si la compagnie principale de l'Admin n'est pas dans la liste (par exemple, si parent_company_id est set à autre chose),
        // on s'assure qu'elle y est pour qu'il puisse au moins se gérer.
        if (!in_array($admin->company_id, $allowedCompanyIds)) {
            $allowedCompanyIds[] = $admin->company_id;
        }

        // Ajoutez 'new' pour permettre la création d'une nouvelle sous-compagnie via le même formulaire
        $allowedCompanyIds[] = 'new';

        return array_unique($allowedCompanyIds);
    }



    public function show(string $id)
    {
        //
    }

        public function edit(string $id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email_adresse' => 'required|email|unique:users,email_adresse,' . $id,
            'role' => 'required|in:admin,comptable',
            'habilitations' => 'nullable|array',
            'habilitations.*' => 'in:' . implode(',', $this->allHabilitations), // Validation contre la liste complète
        ]);

        $user = User::findOrFail($id);

        // Traitement des habilitations (Utilise le même helper que pour store)
        $validated['habilitations'] = $this->processHabilitations(
            $validated['role'],
            $request->input('habilitations', [])
        );

        $user->update($validated);

        return redirect()->back()->with('success', 'Utilisateur mis à jour avec succès.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Utilisateur supprimé avec succès.');
    }
    public function getAllHabilitations(): array
{
    return $this->allHabilitations;
}

public function switchCompany(Request $request, $companyId)
{

    $user = Auth::user();

    // 1. Sécurité: Trouver l'ID de la compagnie mère de l'Admin
    $admin_primary_company_id = $user->company_id;

    // 2. Trouver la compagnie cible
    $company = Company::find($companyId);

    // 3. Vérification des permissions
    $is_super_admin = ($user->role === 'super_admin');
    $is_admin_manager = (
        $company->id == $admin_primary_company_id || // La compagnie principale de l'Admin
        $company->parent_company_id == $admin_primary_company_id ||// Une sous-compagnie créée
        $company->user_id == $user->id
    );

    if (!$company || (!$is_super_admin && !$is_admin_manager)) {
        return redirect()->back()->with('error', 'Accès non autorisé à cette comptabilité.');
    }

    // 4. Basculer le contexte dans la session
    session(['current_company_id' => $companyId]);

    // 5. Redirection vers le tableau de bord
    // Assurez-vous que cette route existe
    return redirect()->route('admin.dashboard')->with('success', 'Context changé: ' . $company->company_name);
}


public function impersonate(User $user)
    {
        // Optionnel : Vérifiez que seul un Admin peut le faire
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Accès non autorisé.');
        }

        // 1. Stocker l'ID de l'administrateur original dans la session
        session()->put('original_admin_id', Auth::id());

        // 2. Déconnecter l'utilisateur actuel (l'administrateur) et reconnecter en tant que cible
        Auth::login($user);

        // 3. Rediriger l'administrateur vers le tableau de bord du comptable
        return redirect()->route('comptable.comptdashboard')->with('impersonating', true)->with('success',
            "Vous êtes maintenant connecté en tant que {$user->name} {$user->last_name}. Cliquez sur le bouton 'Quitter' pour revenir à votre compte Admin.");
    }

    /**
     * Permet à l'administrateur de revenir à son compte original.
     */
    public function leaveImpersonation()
    {
        // 1. Récupérer et supprimer l'ID de l'administrateur original de la session
        $originalAdminId = session()->pull('original_admin_id');

        if (!$originalAdminId) {
            // Si pas d'ID original, l'impersonation n'était pas active
            return redirect('/')->with('error', "Session d'impersonation non active.");
        }

        $originalAdmin = User::find($originalAdminId);

        if (!$originalAdmin) {
            // Si l'admin original n'existe plus, on déconnecte la session actuelle.
            Auth::logout();
            return redirect('/login')->with('error', "Compte administrateur original introuvable ou supprimé.");
        }

        // 2. Déconnecter l'utilisateur actuel et reconnecter l'administrateur
        Auth::logout();
        Auth::login($originalAdmin);

        // 3. Rediriger l'administrateur vers la page de gestion des utilisateurs
        return redirect()->route('user_management')->with('success',
            "Vous êtes revenu à votre compte d'administrateur.");
    }
}

