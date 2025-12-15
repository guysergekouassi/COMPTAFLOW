<?php

namespace App\Http\Controllers\Compte;
use App\Http\Controllers\Controller;
use App\Models\tresoreries\Tresoreries;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
// Assurez-vous que ces modèles existent pour les KPI
use App\Models\CompteTresorerie;
use App\Models\MouvementTresorerie;

class PosteTresorController extends Controller
{
// Fichier : PosteTresorController.php (Méthode index() mise à jour)

   public function index(){

        // 1. Démarrez la requête de base pour les comptes de classe 5
        // (SANS AUCUN FILTRE DE COMPANY_ID COMME DEMANDÉ)
        $query = PlanComptable::whereRaw("CAST(numero_de_compte AS CHAR) LIKE '5%'")
            ->orderBy('numero_de_compte');

        // 2. Exécuter la requête pour obtenir TOUS les comptes de classe 5
       $comptes5 = $query
            // Grouper par numéro et intitulé (pour être sûr)
            ->groupBy('numero_de_compte', 'intitule')
            // Sélectionner le MIN(id) pour obtenir une ID valide pour le formulaire,
            // ainsi que les colonnes de regroupement.
            ->selectRaw('MIN(id) as id, numero_de_compte, intitule')
            // Ordonner le résultat
            ->orderBy('numero_de_compte')
            ->get();
        // dd( $comptes5);
        // 3. Récupération des postes de trésorerie existants
        $comptes = CompteTresorerie::all();
        $postesTresorerie = $comptes;

        // 4. Passer toutes les listes à la vue
        return view('Poste.posteTresor' , compact('comptes', 'postesTresorerie', 'comptes5'));
    }









   public function create()
    {
        $user = Auth::user();
        $companyId = ($user->role === 'super_admin') ? session('active_company_id') : $user->company_id;

        // Logique corrigée
        $query = PlanComptable::where('numero_de_compte', 'like', '5%')
            ->orderBy('numero_de_compte');

        if (!is_null($companyId)) {
            $query->where('company_id', $companyId);
        }

        $comptesComptablesClasse5 = $query->get(['id', 'intitule', 'numero_de_compte']);

        // Cette route n'est plus utilisée pour afficher le formulaire si tout est géré par la modal index.
        return view('Poste.createPoste', compact('comptesComptablesClasse5'));
    }




   public function show(CompteTresorerie $compte)
    {
        $comptes = CompteTresorerie::all();
        $compte = null;
        $mouvements = collect();

        $mouvements = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $postesTresorerie = $postesTresorerie = $comptes;

        return view('Poste.posteTresor', compact('comptes','compte', 'mouvements', 'postesTresorerie'));
    }










    public function storeMouvement(Request $request)
    {
        // 1. Validation des données
        $request->validate([
            'compte_id' => 'required|exists:compte_tresoreries,id',
            'date_mouvement' => 'required|date',
            'libelle' => 'required|string|max:255',
            'type_mouvement' => 'required|in:encaissement,decaissement',
            'montant' => 'required|numeric|min:0.01',
            'reference_piece' => 'nullable|string|max:100',
        ]);

        // 2. Préparation des montants Débit/Crédit
        $montant = $request->input('montant');
        $isEncaissement = $request->input('type_mouvement') === 'encaissement';

        $debit = $isEncaissement ? null : $montant;
        $credit = $isEncaissement ? $montant : null;

        // 3. Création du Mouvement de Trésorerie
        $mouvement = MouvementTresorerie::create([
            'compte_tresorerie_id' => $request->input('compte_id'),
            'date_mouvement' => $request->input('date_mouvement'),
            'libelle' => $request->input('libelle'),
            'reference_piece' => $request->input('reference_piece'),
            'montant_debit' => $debit,
            'montant_credit' => $credit,
        ]);

        // 4. Mise à jour du Solde Actuel du Compte
        $compte = CompteTresorerie::find($request->input('compte_id'));

        if ($isEncaissement) {
            $compte->solde_actuel += $montant;
        } else {
            $compte->solde_actuel -= $montant;
        }

        $compte->save();

        return redirect()->route('postetresorerie.index')
                         ->with('success', 'Mouvement de trésorerie enregistré avec succès.');
    }










    // Méthode pour l'enregistrement d'un NOUVEAU POSTE de trésorerie (Route: postetresorerie.store_poste)
    public function storeCompteTresorerie(Request $request)
    {
        // 1. Validation des données pour la création du POSTE
        $validated = $request->validate([

            'name' => 'required|string|max:255|unique:compte_tresoreries,name',
           'type' => 'required|in:Flux Des Activités Operationnelles,Flux Des Activités Investissement,Flux Des Activités de Financement',

        ]);

        // 2. Création du CompteTresorerie
        $compte = CompteTresorerie::create([
             'company_id' => Auth::user()->company_id,
            'name' => $validated['name'],
            'type' => $validated['type'],

        ]);

        // Assurez-vous que la relation compteComptable existe sur votre modèle CompteTresorerie
        $numeroCompte = $compte->planComptable->numero_de_compte ?? 'N/A';

        return redirect()->route('postetresorerie.index')
                         ->with('success', 'Poste de trésorerie créé et lié au Plan Comptable ' . $numeroCompte . ' avec succès.');
    }

    public function update(Request $request, CompteTresorerie $compte)
{
    // 1. Validation des données
    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
            // Règle unique: ignore l'ID du compte actuel
            \Illuminate\Validation\Rule::unique('compte_tresoreries', 'name')->ignore($compte->id),
        ],
        'type' => 'required|in:Flux Des Activités Operationnelles,Flux Des Activités Investissement,Flux Des Activités de Financement',
    ]);

    // 2. Mise à jour du CompteTresorerie
    $compte->update([
        'name' => $validated['name'],
        'type' => $validated['type'],
    ]);

    // 3. Redirection avec message de succès
    return redirect()->route('postetresorerie.index')
                     ->with('success', 'Poste de trésorerie "' . $compte->name . '" mis à jour avec succès.');
}
}
