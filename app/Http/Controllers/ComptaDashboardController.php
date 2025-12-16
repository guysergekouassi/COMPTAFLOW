<?php




namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company; // Assurez-vous d'importer le modèle Company si vous en avez besoin dans ce contrôleur
use App\Models\PlanTiers;
class ComptaDashboardController extends Controller
{
    public function index()
    {

        $currentCompanyId = session('current_company_id');
        $currentCompany = Company::find($currentCompanyId);

        $user = auth()->user();


        if (!$user) {

            abort(403, 'Unauthorized access.');
        }

        $habilitations = $this->getUserHabilitations($user, $currentCompanyId);


        return view('comptable.comptdashboard',[
            'currentCompany'=> $currentCompany,
            'habilitations'=> $habilitations
        ]);
    }



    protected function getUserHabilitations($user, $companyId)
    {
        // Si l'utilisateur est SuperAdmin, il voit tout
        if ($user->isSuperAdmin()) {
            // Clé spéciale pour afficher le menu Admin/SuperAdmin si nécessaire
            // mais ne doit pas impacter les menus Comptables.
            // On peut retourner l'ensemble des clés pour s'assurer qu'il voit tout.
            return ['dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie',
                    'modal_saisie_direct', 'exercice_comptable', 'accounting_entry_real',
                    'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
                    'accounting_balance', 'accounting_balance_tiers', 'compte_exploitation',
                    'flux_tresorerie', 'tableau_amortissements', 'etat_tiers', 'compte_resultat',
                    'bilan', 'etats_analytiques', 'etats_previsionnels', 'user_management', 'compagny_information'];
        }

        // Si l'utilisateur est l'Admin de la compagnie, il a tous les accès comptables.
        // J'ai inclus une liste complète pour éviter la clé 'all_compta_menus_keys' qui peut ne pas être reconnue.
        if ($user->isAdmin()) {
             return ['dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie',
                    'modal_saisie_direct', 'exercice_comptable', 'accounting_entry_real',
                    'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
                    'accounting_balance', 'accounting_balance_tiers', 'compte_exploitation',
                    'flux_tresorerie', 'tableau_amortissements', 'etat_tiers', 'compte_resultat',
                    'bilan', 'etats_analytiques', 'etats_previsionnels', 'user_management', 'compagny_information'];
        }

        // Pour les utilisateurs créés (Comptable, standard, etc.):
        // 1. Vérifiez si le champ 'habilitations_keys' existe et est bien rempli.
        //    Je suppose que le champ contenant les clés d'habilitations est 'habilitations_keys' sur le modèle User.
        $habilitations = $user->habilitations_keys;

        // 2. Si le champ est une chaîne JSON, il faut le décoder.
        if (is_string($habilitations)) {
            $habilitations = json_decode($habilitations, true);
        }

        // 3. Assurez-vous que c'est un tableau valide, sinon retournez un tableau vide.
        return is_array($habilitations) ? $habilitations : [];
    }
}
