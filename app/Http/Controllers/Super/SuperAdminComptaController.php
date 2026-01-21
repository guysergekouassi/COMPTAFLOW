<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ExerciceComptable;
use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminComptaController extends Controller
{
    /**
     * Affiche la liste des comptabilités (exercices)
     */
    public function index()
    {
        $exercices = ExerciceComptable::with(['company', 'user'])->get();
        return view('superadmin.accounting_list', compact('exercices'));
    }

    /**
     * Affiche le formulaire de création de comptabilité
     */
    public function create()
    {
        $companies = Company::where('is_active', 1)->get();
        $users = User::where('is_active', 1)->get();
        return view('superadmin.create_accounting', compact('companies', 'users'));
    }

    /**
     * Enregistre une nouvelle comptabilité
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'intitule' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        $exercice = ExerciceComptable::create([
            'company_id' => $validated['company_id'],
            'user_id' => $validated['user_id'],
            'intitule' => $validated['intitule'],
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'nombre_journaux_saisis' => 0,
            'cloturer' => false,
        ]);

        // Optionnel: Déclencher la synchronisation des journaux si nécessaire
        // $exercice->syncJournaux();

        return redirect()->route('superadmin.accounting.index')
            ->with('success', 'Exercice comptable créé avec succès !');
    }

    /**
     * Affiche le formulaire d'édition d'exercice
     */
    public function edit($id)
    {
        $exercice = ExerciceComptable::findOrFail($id);
        $companies = Company::where('is_active', 1)->get();
        $users = User::where('is_active', 1)->get();
        return view('superadmin.edit_accounting', compact('exercice', 'companies', 'users'));
    }

    /**
     * Met à jour un exercice
     */
    public function update(Request $request, $id)
    {
        $exercice = ExerciceComptable::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'intitule' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'cloturer' => 'required|boolean',
        ]);

        $exercice->update($validated);

        return redirect()->route('superadmin.accounting.index')
            ->with('success', 'Exercice comptable mis à jour avec succès !');
    }

    /**
     * Supprime un exercice
     */
    public function destroy($id)
    {
        $exercice = ExerciceComptable::findOrFail($id);
        $exercice->delete();

        return redirect()->route('superadmin.accounting.index')
            ->with('success', 'Exercice comptable supprimé avec succès !');
    }
}
