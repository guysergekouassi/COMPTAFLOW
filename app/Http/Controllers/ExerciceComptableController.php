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

        // Utilisation de la session pour le switch de contexte
        $companyId = session('current_company_id', $user->company_id);

        if (!$companyId) {
            return redirect()->route('login')->with('error', 'Aucune entreprise associée à votre compte.');
        }

        // Récupération des exercices filtrés par la société active
        $exercices = ExerciceComptable::select(DB::raw('MAX(id) as id'), 'intitule', 'date_debut', 'date_fin')
            ->where('company_id', $companyId)
            ->groupBy('intitule', 'date_debut', 'date_fin')
            ->orderBy('date_debut', 'desc')
            ->get()
            ->map(function ($exercice) {
                $dateDebut = Carbon::parse($exercice->date_debut);
                $dateFin   = Carbon::parse($exercice->date_fin);
                $exercice->nb_mois = (int) $dateDebut->diffInMonths($dateFin) + 1;
                return $exercice;
            });

        $code_journaux = CodeJournal::get();

        return view('exercice_comptable', compact('exercices', 'code_journaux'));
    }

    public function getData()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Utilisation de la session ici aussi pour que le tableau se mette à jour au switch
        $companyId = session('current_company_id', $user->company_id);

        if (!$companyId) {
            return response()->json(['data' => []]);
        }

        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('date_debut', 'desc')
            ->get()
            ->map(function ($exercice) {
                $dateDebut = Carbon::parse($exercice->date_debut);
                $dateFin = Carbon::parse($exercice->date_fin);

                return [
                    'id' => $exercice->id,
                    'date_debut' => $exercice->date_debut,
                    'date_fin' => $exercice->date_fin,
                    'intitule' => $exercice->intitule,
                    'nb_mois' => (int) $dateDebut->diffInMonths($dateFin) + 1,
                    'nombre_journaux_saisis' => $exercice->nombre_journaux_saisis ?? 0,
                    'cloturer' => (bool) $exercice->cloturer
                ];
            });

        return response()->json(['data' => $exercices]);
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Récupération du contexte via 'current_company_id'
            $companyId = session('current_company_id', $user->company_id);

            $request->validate(ExerciceComptable::$rules);

            DB::beginTransaction();

            Log::info('Tentative de création exercice', [
                'intitule' => $request->intitule,
                'company_id' => $companyId
            ]);

            // Création sans la colonne parent_company_id qui n'existe pas
            $exercice = new ExerciceComptable();
            $exercice->date_debut = $request->date_debut;
            $exercice->date_fin = $request->date_fin;
            $exercice->intitule = $request->intitule;
            $exercice->user_id = $user->id;
            $exercice->company_id = $companyId;
            $exercice->nombre_journaux_saisis = 0;
            $exercice->cloturer = 0;
            $exercice->save();

            if (method_exists($exercice, 'syncJournaux')) {
                $exercice->syncJournaux();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exercice créé avec succès',
                'exercice' => $exercice
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur insertion exercice : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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