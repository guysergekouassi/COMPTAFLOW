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
use App\Models\EcritureComptable;
use App\Services\AccountingReportingService;
use App\Services\ImmobilisationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\InternalNotification;
use App\Models\PlanComptable;

class ExerciceComptableController extends Controller
{
    protected $reportingService;
    protected $immobilisationService;

    public function __construct(AccountingReportingService $reportingService, ImmobilisationService $immobilisationService)
    {
        $this->middleware('auth');
        $this->reportingService = $reportingService;
        $this->immobilisationService = $immobilisationService;
    }
public function index()
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    // Récupérer l'ID de la société switchée
    $companyId = session('current_company_id', $user->company_id);

    if (!$companyId) {
        return redirect()->route('login')->with('error', 'Aucune entreprise associée.');
    }

    // Vérifier les échéances
    $this->checkUpcomingDeadlines($companyId);

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
            // VÉRIFICATION DES CHEVAUCHEMENTS DE DATES
            // Logique : (DebutNEW <= FinEXISTING) ET (FinNEW >= DebutEXISTING)
            $overlap = ExerciceComptable::where('company_id', $companyId)
                ->where(function ($query) use ($request) {
                    $query->where('date_debut', '<=', $request->date_fin)
                          ->where('date_fin', '>=', $request->date_debut);
                })
                ->first();

            if ($overlap) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de créer cet exercice : Les dates chevauchent l'exercice existant \"{$overlap->intitule}\" ({$overlap->date_debut->format('d/m/Y')} au {$overlap->date_fin->format('d/m/Y')})."
                ], 422);
            }

            DB::beginTransaction();

            // Création de l'exercice
            $exercice = new ExerciceComptable();
            $exercice->date_debut = $request->date_debut;
            $exercice->date_fin = $request->date_fin;
            
            // Génération automatique de l'intitulé
            $year = Carbon::parse($request->date_debut)->year;
            $exercice->intitule = "EXERCICE $year";
            $exercice->user_id = $user->id;
            $exercice->company_id = $companyId;
            $exercice->nombre_journaux_saisis = 0;
            $exercice->cloturer = 0;
            $exercice->is_active = 0; // Pas actif par défaut
            $exercice->save();

            if (method_exists($exercice, 'syncJournaux')) {
                $exercice->syncJournaux();
            }

            // Rattrapage des amortissements qui attendaient cet exercice
            if ($this->immobilisationService) {
                $this->immobilisationService->syncOrphanAmortissements($exercice);
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

            return back()->with('success', "L'exercice \"{$exercice->intitule}\" est désormais l'exercice actif PAR DÉFAUT de l'entreprise.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'activation.');
        }
    }

    /**
     * Basculer sur un exercice spécifique (Contextuel par Session)
     * Si $id = 0 ou null, quitte le contexte et revient à l'exercice actif par défaut
     */
    public function switch($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // Si l'utilisateur veut quitter le contexte d'exercice
        if ($id == 0 || $id == 'default') {
            session()->forget('current_exercice_id');
            
            // Récupérer l'exercice actif par défaut
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', 1)
                ->first();
            
            $message = $exerciceActif 
                ? "Vous êtes revenu à l'exercice par défaut : {$exerciceActif->intitule}"
                : "Contexte d'exercice réinitialisé.";
            
            // Support AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'exercice' => $exerciceActif
                ]);
            }
            
            return back()->with('success', $message);
        }

        // Sinon, basculer vers l'exercice sélectionné
        $exercice = ExerciceComptable::where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$exercice) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exercice introuvable ou non autorisé.'
                ], 404);
            }
            return back()->with('error', 'Exercice introuvable ou non autorisé.');
        }

        // Mise à jour de la session contextuelle
        session(['current_exercice_id' => $exercice->id]);

        $message = "Vous travaillez maintenant sur l'exercice : {$exercice->intitule}";
        
        // Support AJAX
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'exercice' => $exercice
            ]);
        }

        return back()->with('success', $message);
    }

    public function cloturer($id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Action non autorisée. Seul un administrateur peut clôturer un exercice.');
        }

        $exercice = ExerciceComptable::findOrFail($id);
        $companyId = $exercice->company_id;

        if ($exercice->cloturer) {
            return back()->with('error', 'L\'exercice est déjà clôturé.');
        }

        // --- SÉCURITÉS AVANCÉES ---
        
        // 1. Vérification de la date : On ne peut pas clôturer avant la fin réelle
        if (now()->lt(Carbon::parse($exercice->date_fin))) {
            return back()->with('error', 'Sécurité : Vous ne pouvez pas clôturer un exercice avant sa date de fin effective (' . Carbon::parse($exercice->date_fin)->format('d/m/Y') . ').');
        }

        // 2. Vérification des écritures en attente
        $pendingEntries = EcritureComptable::where('exercices_comptables_id', $exercice->id)
            ->where('company_id', $companyId)
            ->where('statut', 'pending')
            ->count();
            
        if ($pendingEntries > 0) {
            return back()->with('error', "Sécurité : Il reste $pendingEntries écritures en attente de validation. Veuillez les traiter avant la clôture.");
        }

        // 3. Chercher ou CRÉER l'exercice suivant
        $nextExercice = ExerciceComptable::where('company_id', $companyId)
            ->where('date_debut', '>', $exercice->date_fin)
            ->orderBy('date_debut', 'asc')
            ->first();

        if (!$nextExercice) {
            // Création automatique de l'exercice suivant
            $debutNext = Carbon::parse($exercice->date_fin)->addDay();
            $finNext = (clone $debutNext)->addYear()->subDay();
            
            $nextExercice = ExerciceComptable::create([
                'company_id' => $companyId,
                'intitule' => 'EXERCICE ' . $debutNext->year,
                'date_debut' => $debutNext,
                'date_fin' => $finNext,
                'is_active' => 0,
                'cloturer' => 0,
                'user_id' => Auth::id()
            ]);
        }

        try {
            DB::beginTransaction();

            // 4. Calculer le résultat via le service
            $resultatData = $this->reportingService->getSIGData($exercice->id, $companyId);
            $montantResultat = $resultatData['resultat_net'];

            // 5. Identifier ou créer le journal RAN
            $journalRan = CodeJournal::where('company_id', $companyId)
                ->where(function($q) {
                    $q->where('code_journal', 'RAN')->orWhere('code_journal', 'REP');
                })->first();

            if (!$journalRan) {
                $journalRan = CodeJournal::create([
                    'company_id' => $companyId,
                    'code_journal' => 'RAN',
                    'intitule' => 'REPORT À NOUVEAU',
                    'type' => 'Opérations Diverses',
                    'traitement_analytique' => 0,
                    'user_id' => Auth::id()
                ]);
            }

            // 6. Récupérer tous les soldes des comptes de bilan (Classes 1 à 5)
            $ecritures = EcritureComptable::where('exercices_comptables_id', $exercice->id)
                ->where('company_id', $companyId)
                ->with('planComptable')
                ->get();

            $soldes = [];
            foreach ($ecritures as $ec) {
                if (!$ec->planComptable) continue;
                $num = $ec->planComptable->numero_de_compte;
                if (in_array($num[0], ['1', '2', '3', '4', '5'])) {
                    $soldes[$ec->plan_comptable_id] = ($soldes[$ec->plan_comptable_id] ?? 0) + ($ec->debit - $ec->credit);
                }
            }

            // Ajouter le résultat dans le RAN (Compte 131 ou 139)
            $compteResultatNum = $montantResultat >= 0 ? '131' : '139';
            $compteResultat = PlanComptable::where('company_id', $companyId)
                ->where('numero_de_compte', 'like', $compteResultatNum . '%')
                ->first();

            if ($compteResultat) {
                $soldes[$compteResultat->id] = ($soldes[$compteResultat->id] ?? 0) + $montantResultat;
            }

            // 7. Générer les écritures de RAN dans le nouvel exercice
            $nSaisie = 'RAN-' . $exercice->date_fin->format('Y');
            foreach ($soldes as $planId => $solde) {
                if (abs($solde) < 0.01) continue;

                EcritureComptable::create([
                    'date' => $nextExercice->date_debut,
                    'n_saisie' => $nSaisie,
                    'description_operation' => 'REPORT À NOUVEAU ' . $exercice->intitule,
                    'reference_piece' => 'RAN-' . $exercice->id,
                    'plan_comptable_id' => $planId,
                    'code_journal_id' => $journalRan->id,
                    'exercices_comptables_id' => $nextExercice->id,
                    'debit' => $solde > 0 ? $solde : 0,
                    'credit' => $solde < 0 ? abs($solde) : 0,
                    'company_id' => $companyId,
                    'user_id' => Auth::id(),
                    'is_ran' => true,
                    'statut' => 'approved' // Directement validé pour le RAN
                ]);
            }

            // 8. Marquer comme clôturé
            $exercice->update(['cloturer' => 1]);

            DB::commit();
            return back()->with('success', 'Exercice clôturé avec succès. L\'exercice ' . $nextExercice->intitule . ' a été créé/utilisé pour les reports à nouveau.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur clôture exercice : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la clôture : ' . $e->getMessage());
        }
    }

    public function reouvrir($id)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $exercice = ExerciceComptable::findOrFail($id);
        $companyId = $exercice->company_id;

        if (!$exercice->cloturer) {
            return back()->with('error', 'L\'exercice est déjà ouvert.');
        }

        try {
            DB::beginTransaction();

            // 1. Supprimer les écritures de RAN générées dans l'exercice suivant
            $referencePiece = 'RAN-' . $exercice->id;
            EcritureComptable::where('company_id', $companyId)
                ->where('reference_piece', $referencePiece)
                ->where('is_ran', true)
                ->delete();

            // 2. Marquer l'exercice comme ouvert
            $exercice->update(['cloturer' => 0]);

            DB::commit();
            
            // Notification de réouverture
            InternalNotification::create([
                'title' => 'Réouverture d\'exercice',
                'message' => "L'exercice " . $exercice->intitule . " a été réouvert par " . Auth::user()->name,
                'type' => 'warning',
                'receiver_id' => Auth::id(), // À adapter selon qui doit recevoir
                'company_id' => $companyId
            ]);

            return back()->with('success', 'Exercice réouvert avec succès. Les reports à nouveau correspondants ont été supprimés.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur réouverture exercice : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la réouverture : ' . $e->getMessage());
        }
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

        $data = $request->all();
        $year = Carbon::parse($request->date_debut)->year;
        $data['intitule'] = "EXERCICE $year";

        $exercice->update($data);

        return redirect()->route('exercice_comptable')
            ->with('success', 'Exercice mis à jour avec succès.');
    }

    private function checkUpcomingDeadlines($companyId)
    {
        $activeExercice = ExerciceComptable::where('company_id', $companyId)
            ->where('cloturer', 0)
            ->where('is_active', 1)
            ->first();

        if ($activeExercice) {
            $dateFin = Carbon::parse($activeExercice->date_fin);
            $daysRemaining = (int) now()->diffInDays($dateFin, false);

            if ($daysRemaining <= 30 && $daysRemaining >= -1) {
                $exists = InternalNotification::where('company_id', $companyId)
                    ->where('receiver_id', Auth::id())
                    ->where('title', 'Échéance d\'exercice')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (!$exists) {
                    $type = 'warning';
                    $msg = "L'exercice '{$activeExercice->intitule}' se termine bientôt (J-{$daysRemaining}).";
                    if ($daysRemaining <= 7) $type = 'danger';
                    if ($daysRemaining < 0) $msg = "L'exercice '{$activeExercice->intitule}' est arrivé à échéance le " . $dateFin->format('d/m/Y') . ".";

                    InternalNotification::create([
                        'title' => 'Échéance d\'exercice',
                        'message' => $msg,
                        'type' => $type,
                        'receiver_id' => Auth::id(),
                        'company_id' => $companyId
                    ]);
                }
            }
        }
    }
}