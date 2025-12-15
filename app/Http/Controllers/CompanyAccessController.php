<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyAccessController extends Controller
{
   
    public function accessCompany($companyId)
    {

        $company = Company::where('id', $companyId)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        $selectedCompany = Company::find($companyId);





        // 2. Stocker l'ID et le nom de l'entreprise sélectionnée en session.
        // La sidebar utilisera ces informations pour afficher le menu comptable.
        session([
            'active_company_id' => $company->id,
            'active_company_name' => $company->company_name,
        ]);

        session(['current_compta_account_id' => $companyId]); // Clé plus spécifique
        session(['current_company_id' => $companyId]);
        // 3. Rediriger vers le tableau de bord de l'application comptable.
        // Changez 'dashboard' par la route réelle de votre tableau de bord comptable.
        return redirect()->route('compta.dashboard')->with('success', "Vous êtes maintenant connecté au compte : {$company->company_name}.");
    }


    public function leaveCompany()
    {
        // Supprimer les variables de session liées au compte actif
        session()->forget([
        'active_company_id',
        'active_company_name',
        // --- Clés AJOUTÉES ici pour correction ---
        'current_compta_account_id',
        'current_company_id'
        // ----------------------------------------
    ]);

        // Rediriger vers la page de gestion des comptes (la liste)
        // Changez 'compta_accounts.index' par la route réelle de votre liste de comptes.
        return redirect()->route('compta_accounts.index')->with('info', 'Vous avez quitté le compte actif.');
    }
}
