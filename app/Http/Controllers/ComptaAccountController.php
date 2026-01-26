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


    public function create()
    {
        // Retourne la vue de création d'un EXERCICE COMPTABLE (et non d'une société)
        $user = Auth::user();

        // Récupérer les entreprises gérées par l'admin
        // L'admin peut créer une comptabilité pour sa propre société ou ses sous-sociétés.
        $companies = Company::where('id', $user->company_id)
            ->orWhere('parent_company_id', $user->company_id)
            ->get();

        return view('compte_compta.create', compact('companies'));
    }

    /**
     * Enregistre un nouvel EXERCICE COMPTABLE (Comptabilité)
     */
    public function storeExercice(Request $request)
    {
        $user = Auth::user();

        // Validation pour l'exercice
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'intitule' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        // Vérification des droits : l'utilisateur doit avoir accès à la compagnie cible
        // On vérifie que company_id est dans la liste des compagnies gérées
        $managedCompanies = Company::where('id', $user->company_id)
            ->orWhere('parent_company_id', $user->company_id)
            ->pluck('id')
            ->toArray();

        if (!in_array($validated['company_id'], $managedCompanies)) {
            return back()->with('error', 'Vous n\'avez pas les droits pour créer une comptabilité sur cette entreprise.')->withInput();
        }

        DB::beginTransaction();
        try {
            $exercice = \App\Models\ExerciceComptable::create([
                'company_id' => $validated['company_id'],
                'user_id' => $user->id, // L'admin créateur devient le propriétaire/référent
                'intitule' => $validated['intitule'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'nombre_journaux_saisis' => 0,
                'cloturer' => false,
            ]);

            // Synchronisation optionnelle des journaux (si code existant)
            if (method_exists($exercice, 'syncJournaux')) {
                 $exercice->syncJournaux();
            }

            DB::commit();

            return redirect()->route('admin.dashboard')->with('success', 'Comptabilité (Exercice) créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création de la comptabilité: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Enregistre une nouvelle ENTITÉ (Société)
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

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
            'email_adresse' => 'required|email|max:255|unique:companies,email_adresse',
            'identification_TVA' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $validatedData['user_id'] = $userId;
        // Par défaut, on peut aussi lier au parent_company_id de l'admin si besoin, 
        // mais ici on semble créer des entités racines pour l'utilisateur.

        try {
            Company::create($validatedData);
            return redirect()->route('compta_accounts.index')->with('success', 'L\'entité a été créée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la création de l\'entité : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ancienne méthode store pour créer une société (Renommée pour potentielle réutilisation future ou référence)
     */
    public function storeCompany(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // ... (Logique existante conservée si besoin, sinon on aurait pu supprimer)
        // Pour l'instant, je vide pour éviter la confusion, ou je laisse tel quel mais non routé.
        // Je vais juste garder le code commenté ou déplacé.
        
        $validatedData = $request->validate([
             'company_name' => 'required|string|max:255|unique:companies,company_name',
             'is_active' => 'required|boolean',
             // ... autres validations
        ]);
        // ... Logique de création de société
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

        return redirect()->route('admin.dashboard')->with('success', 'Le compte comptabilité a été mis à jour.');
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

        return redirect()->route('admin.dashboard')->with('success', "Le compte comptabilité '{$companyName}' a été supprimé.");
    }
}
