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

        $companyId = $user->company_id;

        if (!$companyId) {
            return redirect()->route('login')->with('error', 'Aucune entreprise associée à votre compte.');
        }

    // La requête est automatiquement filtrée par TenantScope (Session active)
    // Récupération des exercices uniques par intitulé en gardant le plus récent
    $exercices = ExerciceComptable::select(DB::raw('MAX(id) as id'), 'intitule', 'date_debut', 'date_fin')
        ->where('company_id', $companyId)
        ->groupBy('intitule', 'date_debut', 'date_fin')
        ->orderBy('date_debut', 'desc')
        ->get()
        ->map(function ($exercice) {
            $dateDebut = Carbon::parse($exercice->date_debut);
            $dateFin   = Carbon::parse($exercice->date_fin);

            // Différence en mois complets
            $nbMois = (int) $dateDebut->diffInMonths($dateFin) + 1;

            $exercice->nb_mois = $nbMois;
            return $exercice;
        });

        $code_journaux = CodeJournal::get();
// dd('Company ID:', $companyId, 'Nombre d\'exercices trouvés:', $exercices->count(), $exercices);
    return view('exercice_comptable', compact('exercices','code_journaux'));
}

   public function getData()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Utilisation d'une récupération plus directe du company_id
    $companyId = $user->company_id;

    if (!$companyId) {
        return response()->json(['data' => []]);
    }

    $exercices = ExerciceComptable::where('company_id', $companyId)
        ->orderBy('date_debut', 'desc')
        ->get()
        ->map(function ($exercice) {
            $dateDebut = \Carbon\Carbon::parse($exercice->date_debut);
            $dateFin = \Carbon\Carbon::parse($exercice->date_fin);

            return [
                'id' => $exercice->id,
                'date_debut' => $exercice->date_debut,
                'date_fin' => $exercice->date_fin,
                'intitule' => $exercice->intitule,
                'nb_mois' => (int) $dateDebut->diffInMonths($dateFin) + 1,
                'nombre_journaux_saisis' => $exercice->nombre_journaux_saisis ?? 0,
                // Correction ici : accès direct à l'attribut
                'cloturer' => (bool) $exercice->cloturer
            ];
        });

    return response()->json(['data' => $exercices]);
}

    // recreer
    public function store(Request $request)
    {
        logger('Début de la méthode store', ['request' => $request->all()]);

        try {
            $user = Auth::user();
            
            // Récupérer la société de l'utilisateur
            $company = $user->company;
            
            // Utiliser l'ID de la société actuelle (32 pour COMPTABILITE-CAAPA)
            $companyId = 32; // À remplacer par $company->id en production
            
            Log::info('ID de société utilisé', ['company_id' => $companyId]);

            // Validation des données de base
            $validated = $request->validate(ExerciceComptable::$rules, [
                'date_debut.required' => 'La date de début est obligatoire',
                'date_fin.required' => 'La date de fin est obligatoire',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
                'intitule.required' => 'L\'intitulé est obligatoire',
                'intitule.max' => 'L\'intitulé ne doit pas dépasser 255 caractères',
                'intitule.unique' => 'Un exercice avec cet intitulé existe déjà pour cette période'
            ]);

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
                Log::warning('Chevauchement détecté pour la période', [
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'company_id' => $companyId
                ]);
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
                Log::warning('Exercice avec le même intitulé existe déjà', [
                    'intitule' => $request->intitule,
                    'company_id' => $companyId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Un exercice avec le même intitulé existe déjà.'
                ], 422);
            }

            // Création de l'exercice dans une transaction
            DB::beginTransaction();

            try {
                Log::info('Création de l\'exercice avec les données', [
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'intitule' => $request->intitule,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'parent_company_id' => $user->company_id // Ajout de l'ID de la société parente
                ]);

                $exercice = new ExerciceComptable([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'intitule' => $request->intitule,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                    'parent_company_id' => $user->company_id, // Enregistrement de la société parente
                    'nombre_journaux_saisis' => 0,
                    'cloturer' => 0,
                ]);
                
                $exercice->save(); // Utilisation de save() pour déclencher les événements du modèle

                // Génération des journaux si la méthode existe
                if (method_exists($exercice, 'syncJournaux')) {
                    $exercice->syncJournaux();
                }

                DB::commit();
                Log::info('Exercice créé avec succès', ['exercice_id' => $exercice->id]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la création de l\'exercice: ' . $e->getMessage(), [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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
            Log::error('Erreur création exercice: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'exercice: ' . $e->getMessage()
            ], 500);
        }
    }





    public function cloturer($id)
    {
        $exercice = ExerciceComptable::findOrFail($id);

        if ($exercice->getAttribute('cloturer')) {
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
