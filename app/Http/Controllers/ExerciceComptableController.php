<?php

namespace App\Http\Controllers;

use App\Models\ExerciceComptable;
use App\Models\JournalSaisi;

use App\Models\CodeJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Company;

class ExerciceComptableController extends Controller
{


public function index()
{
    $user = Auth::user();

    // La requête est automatiquement filtrée par TenantScope (Session active)
    $exercices = ExerciceComptable::orderBy('date_debut', 'desc')
        ->get()
        ->map(function ($exercice) {
            $dateDebut = Carbon::parse($exercice->date_debut);
            $dateFin   = Carbon::parse($exercice->date_fin);

            // Différence en mois complets
            $nbMois = (int) $dateDebut->diffInMonths($dateFin) + 1;

            // +1 car on veut compter le mois de début inclus

            $exercice->nb_mois = $nbMois;
            return $exercice;
        });

        $code_journaux = CodeJournal::get();
// dd('Company ID:', $companyId, 'Nombre d\'exercices trouvés:', $exercices->count(), $exercices);
    return view('exercice_comptable', compact('exercices','code_journaux'));
}



    // recreer
    public function store(Request $request)
    {
        try {
            // 1️⃣ Validation
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'intitule' => 'nullable|string|max:255',
            ]);

            // 2️⃣ Vérification du chevauchement (filtré auto par scope)
            $overlap = ExerciceComptable::where(function ($query) use ($request) {
                    $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                        ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin])
                        ->orWhere(function ($query) use ($request) {
                            $query->where('date_debut', '<=', $request->date_debut)
                                ->where('date_fin', '>=', $request->date_fin);
                        });
                })
                ->exists();

            if ($overlap) {
                return redirect()->back()->with('error', 'Les dates de l\'exercice se chevauchent avec un autre exercice.');
            }

            // 3️⃣ Création de l'exercice avec user_id et company_id
            $exercice = ExerciceComptable::create([
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'intitule' => $request->intitule,
                'user_id' => Auth::id(),
                'company_id' => Auth::user()->company_id,
            ]);

            // 4️⃣ Génération automatique des journaux pour cet exercice
            $exercice->syncJournaux(); // méthode ajoutée dans ExerciceComptable

            return redirect()->back()->with('success', 'Exercice comptable et journaux créés avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }





    public function cloturer($id)
    {
        $exercice = ExerciceComptable::findOrFail($id);

        if ($exercice->cloturer) {
            return back()->with('error', 'L\'exercice est déjà clôturé.');
        }

        $exercice->update(['cloturer' => 1]);

        return back()->with('success', 'Exercice clôturé avec succès.');
    }





    public function destroy($id)
    {
        $exercice = ExerciceComptable::findOrFail($id);

        $exercice->delete();

        return redirect()->back()->with('success', 'L\'exercice comptable a été supprimé avec succès.');
    }


}
