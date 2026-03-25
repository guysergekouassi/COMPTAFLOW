<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class LandingController extends Controller
{
    /**
     * Affiche la page vitrine
     */
    public function index()
    {
        return view('landing.index');
    }

    /**
     * Affiche la page de choix du pack (pricing)
     */
    public function pricing()
    {
        return view('landing.pricing');
    }

    /**
     * Affiche le formulaire d'inscription en fonction du type
     */
    public function registerForm($type)
    {
        // Validation du type
        if (!in_array($type, ['entreprise', 'comptable'])) {
            return redirect()->route('landing.pricing')->with('error', 'Type de pack invalide.');
        }

        return view('landing.register', compact('type'));
    }

    /**
     * Traite la soumission du formulaire d'inscription
     */
    public function registerSubmit(Request $request)
    {
        $request->validate([
            'type' => 'required|in:entreprise,comptable',
            // Infos entreprise
            'company_name' => 'required|string|max:255',
            'juridique_form' => 'required|string|max:100',
            'activity' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone_number' => 'required|string|max:50',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'company_email' => 'required|email|max:255',
            // Infos Admin
            'admin_name' => 'required|string|max:255',
            'admin_last_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email_adresse|max:255',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            // 1. Déterminer le parent_company_id (0 si c'est la racine, ou via une logique spécifique)
            // Dans ce système, une entreprise principale a parent_company_id = null ou 0.
            
            // 2. Créer l'entreprise
            $company = Company::create([
                'company_name' => $request->company_name,
                'juridique_form' => $request->juridique_form,
                'activity' => $request->activity,
                'social_capital' => $request->social_capital ?? 0,
                'status' => 'Actif',
                'is_active' => 1,
                'city' => $request->city,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'country' => $request->country,
                'phone_number' => $request->phone_number,
                'email_adresse' => $request->company_email,
                'identification_TVA' => $request->identification_TVA,
                'parent_company_id' => null, // Racine
            ]);

            // 3. Déterminer les habilitations standards
            // Un comptable a peut-être plus d'habilitations ou le même admin par défaut, 
            // on copie le format standard du système actuel
            $habilitations = [
                "creation_compte" => "1",
                "creation_journal" => "1",
                "creation_tiers" => "1",
                "saisie_journal" => "1",
                "consultation_grand_livre" => "1",
                "consultation_balance" => "1",
                "gestion_utilisateurs" => "1",
                "configuration_systeme" => "1",
                "consultation_tft" => "1"
                // Vous pouvez ajuster en fonction du besoin spécifique "entreprise" vs "comptable"
            ];

            // 4. Déterminer le rôle
            // Un administrateur métier
            $role = 'admin';

            // 5. Créer l'utilisateur Administrateur
            $user = User::create([
                'name' => $request->admin_name,
                'last_name' => $request->admin_last_name,
                'email_adresse' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'role' => $role,
                'company_id' => $company->id, // Lier à l'entreprise
                'phone_number' => $request->admin_phone ?? $request->phone_number,
                'habilitations' => $habilitations,
                'is_active' => 1
            ]);

            // Assigner l'administrateur à l'entreprise
            $company->admin_id = $user->id;
            $company->save();

            DB::commit();

            // 6. Envoi de l'e-mail de bienvenue
            try {
                Mail::to($user->email_adresse)->send(new WelcomeEmail($user, $request->type, $company));
            } catch (\Exception $e) {
                // Log l'erreur d'envoi mais on ne bloque pas l'inscription
                \Log::error('Erreur envoi email bienvenue : ' . $e->getMessage());
            }

            return redirect()->route('login')->with('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de votre compte : ' . $e->getMessage());
        }
    }
}
