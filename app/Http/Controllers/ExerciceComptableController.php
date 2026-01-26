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
use Illuminate\Validation\ValidationException;

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

    // Récupérer l'ID de la société switchée (ici ce sera 33 d'après vos logs)
    $companyId = session('current_company_id', $user->company_id);

    if (!$companyId) {
        return redirect()->route('login')->with('error', 'Aucune entreprise associée.');
    }

    $exercices = ExerciceComptable::where('company_id', $companyId)
        ->orderBy('date_debut', 'desc')
        ->get()
        ->map(function ($exercice) {
            $dateDebut = \Carbon\Carbon::parse($exercice->date_debut);
            $dateFin   = \Carbon\Carbon::parse($exercice->date_fin);
            // Calcul plus précis : si c'est exactement un an (01/01 au 01/01 ou au 31/12), on veut 12 mois.
            $exercice->nb_mois = (int) round($dateDebut->diffInMonths($dateFin));
            if ($exercice->nb_mois == 0) $exercice->nb_mois = 1; // Minimum 1 mois
            return $exercice;
        });

    // RÉCUPÉRATION DES JOURNAUX
    $code_journaux = CodeJournal::withoutGlobalScopes()
        ->where('company_id', $companyId)
        ->get();
    
    // Définir l'exercice par défaut pour le modal
    $exerciceActif = $exercices->first();

    // IMPORTANT: On passe bien les 3 variables à la vue
    return view('exercice_comptable', compact('exercices', 'code_journaux', 'exerciceActif'));
}
    public function getData(Request $request)
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

        $query = ExerciceComptable::where('company_id', $companyId);

        if ($request->filled('date_debut')) {
            try {
                $dateDebut = Carbon::parse($request->input('date_debut'))->startOfDay();
                $query->whereDate('date_debut', '>=', $dateDebut);
            } catch (\Exception $e) {
                // Ignorer un format invalide
            }
        }

        if ($request->filled('date_fin')) {
            try {
                $dateFin = Carbon::parse($request->input('date_fin'))->endOfDay();
                $query->whereDate('date_fin', '<=', $dateFin);
            } catch (\Exception $e) {
                // Ignorer un format invalide
            }
        }

        $exercices = $query->orderBy('date_debut', 'desc')
            ->get()
            ->map(function ($exercice) {
                $dateDebut = Carbon::parse($exercice->date_debut);
                $dateFin = Carbon::parse($exercice->date_fin);

                return [
                    'id' => $exercice->id,
                    'date_debut' => $exercice->date_debut,
                    'date_fin' => $exercice->date_fin,
                    'intitule' => $exercice->intitule,
                    'nb_mois' => (int) round($dateDebut->diffInMonths($dateFin)),
                    'is_active' => (bool) $exercice->is_active,
                    'nombre_journaux_saisis' => $exercice->nombre_journaux_saisis ?? 0,
                    'cloturer' => (bool) $exercice->cloturer
                ];
            });

        return response()->json(['data' => $exercices]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée. Seul un administrateur peut créer un exercice.'], 403);
        }
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            $request->validate(ExerciceComptable::$rules);

            // LOGIQUE DE RESTRICTION : On ne peut pas créer un exercice de plus que celui dans lequel nous sommes (+1 an max)
            $dateDebut = Carbon::parse($request->date_debut);
            $limitYear = Carbon::now()->year + 1;
            
            if ($dateDebut->year > $limitYear) {
                return response()->json([
                    'success' => false, 
                    'message' => "La logique comptable interdit de créer un exercice au-delà de l'année $limitYear."
                ], 422);
            }

            // VÉRIFICATION DES DOUBLONS
            $existing = ExerciceComptable::where('company_id', $companyId)
                ->where('date_debut', $request->date_debut)
                ->where('date_fin', $request->date_fin)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => "Un exercice avec ces dates existe déjà pour cette entreprise."
                ], 422);
            }

            DB::beginTransaction();

            // Création de l'exercice
            $exercice = new ExerciceComptable();
            $exercice->date_debut = $request->date_debut;
            $exercice->date_fin = $request->date_fin;
            $exercice->intitule = $request->intitule;
            $exercice->user_id = $user->id;
            $exercice->company_id = $companyId;
            $exercice->nombre_journaux_saisis = 0;
            $exercice->cloturer = 0;
            $exercice->is_active = 0; // Pas actif par défaut
            $exercice->save();

            if (method_exists($exercice, 'syncJournaux')) {
                $exercice->syncJournaux();
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exercice créé avec succès',
                    'exercice' => $exercice
                ]);
            }

            return redirect()->route('exercice_comptable')->with('success', 'Exercice créé avec succès');

        } catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                    'message' => 'Veuillez corriger les erreurs dans le formulaire.'
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur insertion exercice : ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function activate($id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        try {
            DB::beginTransaction();

            // Désactiver tous les autres exercices de la société
            ExerciceComptable::where('company_id', $companyId)->update(['is_active' => false]);

            // Activer l'exercice sélectionné
            $exercice = ExerciceComptable::where('company_id', $companyId)->findOrFail($id);
            $exercice->is_active = true;
            $exercice->save();

            DB::commit();

            return back()->with('success', "L'exercice \"{$exercice->intitule}\" est désormais l'exercice actif de l'entreprise.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'activation.');
        }
    }

    public function cloturer($id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Action non autorisée. Seul un administrateur peut clôturer un exercice.');
        }
        $exercice = ExerciceComptable::findOrFail($id);

        if ($exercice->cloturer) {
            return back()->with('error', 'L\'exercice est déjà clôturé.');
        }

        $exercice->update(['cloturer' => 1]);

        return back()->with('success', 'Exercice clôturé avec succès.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Action non autorisée. Seul un administrateur peut supprimer un exercice.');
        }
        $exercice = ExerciceComptable::findOrFail($id);
        $exercice->delete();

        return redirect()->back()->with('success', 'L\'exercice comptable a été supprimé avec succès.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exercice = ExerciceComptable::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Récupérer les journaux saisis pour cet exercice
        $journauxSaisis = JournalSaisi::where('exercices_comptables_id', $id)
            ->where('company_id', $companyId)
            ->with(['codeJournal'])
            ->get();

        return view('exercice_comptable_show', compact('exercice', 'journauxSaisis'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exercice = ExerciceComptable::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Empêcher l'édition si l'exercice est clôturé
        if ($exercice->cloturer) {
            return back()->with('error', 'Impossible de modifier un exercice clôturé.');
        }

        return view('exercice_comptable_edit', compact('exercice'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Action non autorisée. Seul un administrateur peut modifier un exercice.');
        }
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exercice = ExerciceComptable::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Empêcher la mise à jour si l'exercice est clôturé
        if ($exercice->cloturer) {
            return back()->with('error', 'Impossible de modifier un exercice clôturé.');
        }

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'intitule' => 'required|string|max:255'
        ]);

        $exercice->update($request->all());

        return redirect()->route('exercice_comptable')
            ->with('success', 'Exercice mis à jour avec succès.');
    }
}