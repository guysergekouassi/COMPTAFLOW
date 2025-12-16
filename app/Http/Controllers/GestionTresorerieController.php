<?php

namespace App\Http\Controllers;
use App\Models\FluxType;
use App\Models\PlanComptable;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestionTresorerieController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = Auth::user()->company_id;

        $flux_types = FluxType::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get();

        $PlanComptable = PlanComptable::where('company_id', $user->company_id)
            ->orderByRaw('LEFT(numero_de_compte, 1) ASC')
            ->orderBy('numero_de_compte')
            ->get();



        return view('gestion_tresorerie', compact('flux_types', 'PlanComptable'));
    }


    public function store(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'categorie' => 'nullable|string|max:100',
                'nature' => 'nullable|string|max:191',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
            ]);

            $userId = Auth::id();
            $companyId = Auth::user()->company_id;

            // Création de l'exercice
            $exercice = FluxType::create([
                'categorie' => $request->categorie,
                'nature' => $request->nature,
                'plan_comptable_id_1' => $request->plan_comptable_id_1,
                'plan_comptable_id_2' => $request->plan_comptable_id_2,
                'user_id' => $userId,
                'company_id' => $companyId,
            ]);



            return redirect()->back()->with('success', 'Flux crée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }


    public function update(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'id' => 'required|integer|exists:flux_types,id',
                'categorie' => 'nullable|string|max:100',
                'nature' => 'nullable|string|max:191',
                'plan_comptable_id_1' => 'exists:plan_comptables,id',
                'plan_comptable_id_2' => 'exists:plan_comptables,id',

            ]);

            $flux = FluxType::findOrFail($request->id);

            // Vérifie que l'utilisateur appartient à la même entreprise
            if ($flux->company_id !== Auth::user()->company_id) {
                return redirect()->back()->with('error', 'Action non autorisée.');
            }

            // Mise à jour
            $flux->update([
                'categorie' => $request->categorie,
                'nature' => $request->nature,
                'plan_comptable_id_1' => $request->plan_comptable_id_1,
                'plan_comptable_id_2' => $request->plan_comptable_id_2,
            ]);

            return redirect()->back()->with('success', 'Flux mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }


    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:flux_types,id',
            ]);

            $flux = FluxType::findOrFail($request->id);

            // Vérifie que l'utilisateur appartient à la même entreprise
            if ($flux->company_id !== Auth::user()->company_id) {
                return redirect()->back()->with('error', 'Action non autorisée.');
            }

            $flux->delete();

            return redirect()->back()->with('success', 'Flux supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }



}
