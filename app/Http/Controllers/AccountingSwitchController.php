<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use App\Models\Company; // Utiliser le modèle Company au lieu de ComptaAccount

class AccountingSwitchController extends Controller
{

    public function switchToAccount($comptaAccountId)
    {
        $user = Auth::user();

        // 1. Vérification de l'existence du compte de comptabilité et que l'utilisateur y a accès
        $comptaAccount = Company::where('id', $comptaAccountId)
            ->where('user_id', $user->id)
            ->first();

        if (!$comptaAccount) {
            return back()->with('error', 'Le compte de comptabilité sélectionné est introuvable ou vous n\'y avez pas accès.');
        }


        Session::put('current_company_id', $comptaAccount->id);

        // 3. Stocker l'ID du compte comptable dans la session
        Session::put('current_compta_account_id', $comptaAccountId);

        // 4. Définir la variable clé qui contrôle l'affichage du menu
        // Le menu s'affiche si cette variable est à 'true' (plan comptable est actif)
        Session::put('plan_comptable', true);

        // 5. Redirection vers le tableau de bord de la comptabilité
        return redirect()->route('compta.dashboard')->with('success', 'Bascule vers le compte de comptabilité réussie.');
    }

    /**
     * Revenir à l'état de compagnie sans compte comptable actif (déconnexion du compte comptable).
     *
     * @return \Illuminate\Http\Response
     */
    public function clearAccount()
    {
        // Supprimer les variables de session spécifiques à la comptabilité
        Session::forget('current_compta_account_id');
        Session::forget('plan_comptable');

        // Réinitialiser la compagnie courante à la compagnie par défaut de l'utilisateur
        Session::put('current_company_id', Auth::user()->company_id);


        return redirect()->route('admin.switch')->with('info', 'Vous avez quitté le compte de comptabilité.');
    }
}
