<?php

namespace App\Http\Controllers\Super;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\TreasuryCategory;

class SuperAdminSetupController extends Controller
{
    /**
     * @var array Liste complète des habilitations (copiée de UserController).
     */
    private $allHabilitations = [
        'dashboard', 'Analytics', 'plan_comptable', 'plan_tiers', 'journaux', 'grand_livre',
        'balance', 'etats_financiers', 'fichier_joindre', 'tresorerie', 'parametre',
        'accounting_journals', 'exercice_comptable', 'Etat de rapprochement bancaire',
        'Gestion de la trésorerie', 'gestion_analytique', 'gestion_tiers', 'user_management',
        'gestion_immobilisations', 'gestion_reportings', 'compagny_information',
        'gestion_stocks',
    ];

    /**
     * Affiche le formulaire de configuration initiale.
     * Cette route devrait être protégée pour n'être accessible que si aucun Super-Admin n'existe.
     */
    public function showSetupForm()
    {
        // Vérification de sécurité (optionnel mais recommandé) :
        // Si un utilisateur avec un rôle "superadmin" ou une compagnie existe déjà, rediriger.
        if (User::where('role', 'superadmin')->exists() || Company::exists()) {
             return redirect()->route('app.dashboard')->with('error', 'Le système est déjà configuré.');
        }

        return view('auth.super_admin_setup'); // Créez cette vue (Super-Admin + infos Compagnie)
    }


    /**
     * Crée la compagnie, le Super-Admin (avec toutes les permissions) et le premier Admin de la compagnie.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setupSuperAdmin(Request $request)
    {
        // 1. Validation de toutes les données (Super-Admin, Admin, Compagnie)
        $request->validate([
            // Données du Super-Admin
            'super_name' => 'required|string|max:255',
            'super_last_name' => 'required|string|max:255',
            'super_email' => 'required|email|unique:users,email_adresse',
            'super_password' => 'required|string|min:8|confirmed',

            // Données de l'Admin (Premier utilisateur de la compagnie)
            'admin_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email_adresse',
            'admin_password' => 'required|string|min:8|confirmed',

            // Données de la Compagnie
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            // ... autres champs de la compagnie que vous souhaitez valider ...
        ]);


        // Assurer l'atomicité de la transaction
        DB::beginTransaction();

        try {
            // 2. Création de la Compagnie
            $company = Company::create([
                'company_name' => $request->company_name,
                'juridique_form' => $request->juridique_form ?? 'N/A',
                // ... autres champs de la compagnie
            ]);


            // 3. Création du Super-Admin (Rôle 'superadmin' - Hors de la structure des compagnies)
            User::create([
                'name' => $request->super_name,
                'last_name' => $request->super_last_name,
                'email_adresse' => $request->super_email,
                'password' => Hash::make($request->super_password),
                'role' => 'superadmin', // Nouveau rôle "superadmin"
                'company_id' => null, // Le Super-Admin n'est rattaché à aucune compagnie spécifique.
                'habilitations' => array_fill_keys($this->allHabilitations, true), // TOUTES les permissions
                'is_online' => false,
            ]);


            // 4. Création du Premier Admin de la Compagnie (Rôle 'admin' - Rattaché à la nouvelle compagnie)
            $adminHabilitations = array_fill_keys($this->allHabilitations, true); // Admin a toutes les permissions

            User::create([
                'name' => $request->admin_name,
                'last_name' => $request->admin_last_name,
                'email_adresse' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => 'admin', // Rôle "admin" (celui qui gère les comptables)
                'company_id' => $company->id, // Rattaché à la compagnie
                'habilitations' => $adminHabilitations,
                'is_online' => false,
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

            // Optionnel : Connecter automatiquement le Super-Admin ou rediriger vers la page de connexion
            return redirect('/login')->with('success', 'Configuration initiale réussie. Connectez-vous en tant que Super-Admin.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Loggez l'erreur pour le débogage si nécessaire
            // Log::error("Erreur lors de la configuration du Super-Admin: " . $e->getMessage());
            throw ValidationException::withMessages([
                'setup_error' => 'Erreur lors de la configuration initiale. Veuillez réessayer.',
            ]);
        }
    }

    // Vous n'avez pas besoin d'une méthode processHabilitations ici puisque les deux
    // utilisateurs (Super-Admin et Admin) reçoivent TOUTES les habilitations.
}
