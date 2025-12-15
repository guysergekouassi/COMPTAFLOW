<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company; // Assurez-vous que le modèle Company est correct
use Illuminate\Support\Facades\Auth; // Importation nécessaire pour accéder à l'utilisateur connecté
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ComptaAccountController extends Controller
{

    public function index()
    {
        // Récupérer l'ID de l'utilisateur actuellement connecté
        $userId = Auth::id();

        // --- Requête principale : Filtrer UNIQUEMENT les comptes créés par cet utilisateur ---
        // Les comptes avec user_id = 0 (Super Admin/Système) sont exclus.
        $allComptaAccounts = Company::where('user_id', $userId)
                                     ->get();
        // -----------------------------------------------------------------------------------

        // Calcul des statistiques basées UNIQUEMENT sur les comptes filtrés
        $totalAccounts = $allComptaAccounts->count();
        // Utiliser filter() pour le comptage sur la collection si elle est déjà chargée
        $activeAccounts = $allComptaAccounts->filter(fn ($account) => $account->is_active == 1)->count();
        $inactiveAccounts = $totalAccounts - $activeAccounts;

        return view('compte_compta.creerCompteCompta', [
            'comptaAccounts' => $allComptaAccounts, // La collection filtrée
            'totalAccounts' => $totalAccounts,
            'activeAccounts' => $activeAccounts,
            'inactiveAccounts' => $inactiveAccounts
        ]);
    }


    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Validation des données
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'activity' => 'nullable|string|max:255',
            'juridique_form' => 'nullable|string|max:255',
            'social_capital' => 'nullable|numeric',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            // La validation `unique` doit fonctionner car l'email est unique pour toutes les compagnies
            'email_adresse' => 'required|email|unique:companies,email_adresse|max:255',
            'identification_TVA' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        // 2. AJOUTER l'ID de l'utilisateur connecté aux données validées
        $validatedData['user_id'] = $user->id;

        // 3. LOGIQUE CLÉ : Déterminer si la compagnie est une sous-compagnie.
        // Si l'utilisateur est un Admin et qu'il est déjà rattaché à une compagnie principale,
        // la nouvelle compagnie est considérée comme une sous-compagnie.
        if ($user->role === 'admin' && $user->company_id) {
            // Rattacher la nouvelle compagnie à la compagnie principale de l'Admin
            $validatedData['parent_company_id'] = $user->company_id;
        }

        // 4. Création de l'entité Company
        DB::beginTransaction();
        try {
            Company::create($validatedData);
            DB::commit();

            return redirect()->route('compta_accounts.index')->with('success', 'Le compte comptabilité a été créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Ajouter un log d'erreur si nécessaire pour le debug
            return back()->with('error', 'Erreur lors de la création du compte comptabilité: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour le compte de comptabilité spécifié.
     */
    public function update(Request $request, $id)
    {
        // Trouver la compagnie ET s'assurer qu'elle appartient à l'utilisateur actuel.
        // Les comptes user_id=0 sont exclus des modifications.
        $company = Company::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        // 1. Validation des données (ajustée pour ignorer l'email unique du compte actuel)
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'activity' => 'nullable|string|max:255',
            'juridique_form' => 'nullable|string|max:255',
            'social_capital' => 'nullable|numeric',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'email_adresse' => 'required|email|max:255|unique:companies,email_adresse,' . $company->id,
            'identification_TVA' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        // Le parent_company_id ne peut pas être modifié via ce formulaire (logique métier)
        // et n'est pas censé l'être une fois la compagnie créée.

        // 2. Mise à jour
        $company->update($validatedData);

        return redirect()->route('compta_accounts.index')->with('success', 'Le compte comptabilité a été mis à jour.');
    }

    /**
     * Supprime le compte de comptabilité spécifié.
     */
    public function destroy($id)
    {
        // Trouver la compagnie ET s'assurer qu'elle appartient à l'utilisateur actuel.
        // Les comptes user_id=0 sont exclus de la suppression.
        $company = Company::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->firstOrFail();

        // TODO: AJOUTER UNE VÉRIFICATION POUR S'ASSURER QU'IL N'Y A PAS DE SOUS-COMPAGNIES RATTACHÉES
        // Si d'autres compagnies ont cette $company->id comme parent_company_id, la suppression doit être bloquée
        // ou ces compagnies doivent être dé-rattachées (parent_company_id = NULL)

        $companyName = $company->company_name;

        // 1. Suppression
        $company->delete();

        return redirect()->route('compta_accounts.index')->with('success', "Le compte comptabilité '{$companyName}' a été supprimé.");
    }
}
