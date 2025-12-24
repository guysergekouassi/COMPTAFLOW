<?php

namespace App\Http\Controllers;

use App\Models\ExerciceComptable;
use App\Models\JournalSaisi;

use App\Models\CodeJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class ExerciceComptableController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

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
        logger('Début de la méthode store', ['request' => $request->all()]);
        
        try {
            // Validation des données de base
            $validated = $request->validate(ExerciceComptable::$rules, [
                'date_debut.required' => 'La date de début est obligatoire',
                'date_fin.required' => 'La date de fin est obligatoire',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
                'intitule.required' => 'L\'intitulé est obligatoire',
                'intitule.max' => 'L\'intitulé ne doit pas dépasser 255 caractères',
                'intitule.unique' => 'Un exercice avec cet intitulé existe déjà pour cette période'
            ]);
            
            $user = Auth::user();
            $companyId = $user->company_id;
            
            // Vérification de l'unicité de l'intitulé pour cette entreprise
            $existingExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('intitule', $request->intitule)
                ->first();
                
            if ($existingExercice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un exercice avec le même intitulé existe déjà.'
                ], 422);
            }

            // Vérification des chevauchements de dates
            $overlap = ExerciceComptable::where('company_id', $companyId)
                ->where(function($query) use ($request) {
                    $query->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                        ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin])
                        ->orWhere(function($q) use ($request) {
                            $q->where('date_debut', '<=', $request->date_debut)
                              ->where('date_fin', '>=', $request->date_fin);
                        });
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un exercice existe déjà sur cette période.'
                ], 422);
            }

            // Vérification de l'existence d'un exercice avec le même intitulé
            $existingExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('intitule', $request->intitule)
                ->first();
                
            if ($existingExercice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un exercice avec le même intitulé existe déjà.'
                ], 422);
            }

            // Création de l'exercice dans une transaction
            DB::beginTransaction();
            
            try {
                logger('Création de l\'exercice avec les données', [
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'intitule' => $request->intitule,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                ]);
                
                $exercice = ExerciceComptable::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'intitule' => $request->intitule,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'nombre_journaux_saisis' => 0,
                    'cloturer' => 0,
                ]);

                // Génération des journaux si la méthode existe
                if (method_exists($exercice, 'syncJournaux')) {
                    $exercice->syncJournaux();
                }
                
                DB::commit();
                logger('Exercice créé avec succès', ['exercice_id' => $exercice->id]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Erreur lors de la création de l\'exercice: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'exercice: ' . $e->getMessage()
                ], 500);
            }

            // Calcul du nombre de mois
            $dateDebut = Carbon::parse($exercice->date_debut);
            $dateFin = Carbon::parse($exercice->date_fin);
            $nbMois = $dateDebut->diffInMonths($dateFin) + 1;
            
            // Mettre à jour l'exercice avec le nombre de mois calculé
            $exercice->update(['nb_mois' => $nbMois]);
            
            // Préparer la réponse
            $response = [
                'success' => true,
                'message' => 'Exercice comptable créé avec succès',
                'exercice' => [
                    'id' => $exercice->id,
                    'date_debut' => $exercice->date_debut,
                    'date_fin' => $exercice->date_fin,
                    'intitule' => $exercice->intitule,
                    'nb_mois' => $nbMois,
                    'nombre_journaux_saisis' => 0,
                    'cloturer' => 0
                ]
            ];
            
            // Retourner la vue avec les données mises à jour pour les requêtes non-AJAX
            if (!$request->ajax()) {
                $exercices = ExerciceComptable::where('company_id', $companyId)
                    ->orderBy('date_debut', 'desc')
                    ->get();
                    
                return view('exercice_comptable', [
                    'exercices' => $exercices,
                    'code_journaux' => CodeJournal::all(),
                    'success' => $response['message']
                ]);
            }
            
            // Pour les requêtes AJAX, retourner la réponse JSON
            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur création exercice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'exercice: ' . $e->getMessage()
            ], 500);
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
