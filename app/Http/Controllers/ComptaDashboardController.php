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

        // Statistiques utilisateur
        $userStats = $this->getUserStats($user);
        
        // Statistiques comptables
        $comptaStats = $this->getComptaStats($currentCompanyId);

        return view('comptable.comptdashboard',[
            'currentCompany'=> $currentCompany,
            'habilitations'=> $habilitations,
            'user' => $user,
            'userStats' => $userStats,
            'comptaStats' => $comptaStats,
        ]);
    }





    protected function getUserStats($user)
    {
        return [
            'total_logins' => $user->total_logins ?? 0,
            'last_login' => $user->last_login_at ?? now(),
            'account_age_days' => $user->created_at ? $user->created_at->diffInDays(now()) : 0,
            'ecritures_mois' => \App\Models\EcritureComptable::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    protected function getComptaStats($companyId)
    {
        if (!$companyId) {
            return $this->getEmptyStats();
        }

        $revenusMois = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('credit');

        $depensesMois = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('debit');

        $revenusAnnee = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->whereYear('date', now()->year)
            ->sum('credit');

        $depensesAnnee = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->whereYear('date', now()->year)
            ->sum('debit');

        return [
            'solde_tresorerie' => \App\Models\CompteTresorerie::sum('solde_actuel') ?? 0,
            'tresorerie_variation' => '+14.8%',
            'revenus_mois' => $revenusMois,
            'revenus_variation' => '+8.2%',
            'depenses_mois' => $depensesMois,
            'depenses_variation' => '-5.1%',
            'pieces_saisies_mois' => \App\Models\EcritureComptable::where('company_id', $companyId)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
            'total_tiers' => \App\Models\PlanTiers::where('company_id', $companyId)->count(),
            'exercice_actuel' => \App\Models\ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first(),
            'jours_restants' => $this->getJoursRestants($companyId),
            'revenus_annee' => $revenusAnnee,
            'depenses_annee' => $depensesAnnee,
            'revenus_mensuels' => $this->getRevenusMensuels($companyId),
        ];
    }

    protected function getEmptyStats()
    {
        return [
            'solde_tresorerie' => 0,
            'tresorerie_variation' => '0%',
            'revenus_mois' => 0,
            'revenus_variation' => '0%',
            'depenses_mois' => 0,
            'depenses_variation' => '0%',
            'pieces_saisies_mois' => 0,
            'total_tiers' => 0,
            'exercice_actuel' => null,
            'jours_restants' => 0,
            'revenus_annee' => 0,
            'depenses_annee' => 0,
            'revenus_mensuels' => array_fill(0, 12, 0),
        ];
    }

    protected function getJoursRestants($companyId)
    {
        $exercice = \App\Models\ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if ($exercice && $exercice->date_fin) {
            return now()->diffInDays($exercice->date_fin, false);
        }

        return 0;
    }

    protected function getRevenusMensuels($companyId)
    {
        $revenus = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenus[] = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->whereMonth('date', $i)
                ->whereYear('date', now()->year)
                ->sum('credit');
        }
        return $revenus;
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
