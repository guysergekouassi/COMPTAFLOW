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
use App\Services\ComptesDeResultat;
use App\Services\ImmobilisationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\InternalNotification;
use App\Models\PlanComptable;
use RuntimeException;

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
            $exercice->nb_mois = $this->calculerNbMois($exercice->date_debut, $exercice->date_fin);
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
                    // Chaînes "Y-m-d" pures : pas d'horodatage UTC, donc pas de décalage
                    // d'un jour à l'affichage selon le fuseau du navigateur.
                    'date_debut' => $dateDebut->format('Y-m-d'),
                    'date_fin' => $dateFin->format('Y-m-d'),
                    'date_debut_fr' => $dateDebut->format('d/m/Y'),
                    'date_fin_fr' => $dateFin->format('d/m/Y'),
                    // Repli pour les anciens exercices enregistrés sans intitulé
                    'intitule' => $exercice->intitule ?: $this->genererIntitule($dateDebut, $dateFin),
                    'nb_mois' => $this->calculerNbMois($exercice->date_debut, $exercice->date_fin),
                    'is_active' => (bool) $exercice->is_active,
                    'nombre_journaux_saisis' => $exercice->nombre_journaux_saisis ?? 0,
                    'cloturer' => (bool) $exercice->cloturer,
                    'resultat_passe' => $this->resultatEstPasse($exercice),
                    'resultat_recalculable' => false
                ];
            });

        return response()->json(['data' => $exercices]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('compta.create')) {
            return response()->json(['success' => false, 'message' => 'Action non autorisée : habilitation « Ouvrir un exercice comptable » requise.'], 403);
        }
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after:date_debut',
            ], [
                'date_debut.required' => 'La date de début est obligatoire.',
                'date_fin.required' => 'La date de fin est obligatoire.',
                'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
            ]);

            // Dates normalisées : on ne garde que la partie calendaire (aucune heure,
            // donc aucun risque de glissement d'un jour au stockage ou à l'affichage).
            $dateDebut = Carbon::parse($request->date_debut)->startOfDay();
            $dateFin = Carbon::parse($request->date_fin)->startOfDay();

            // LOGIQUE DE RESTRICTION : On ne peut pas créer un exercice de plus que celui dans lequel nous sommes (+1 an max)
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
                ->where(function ($query) use ($dateDebut, $dateFin) {
                    $query->where('date_debut', '<=', $dateFin->format('Y-m-d'))
                          ->where('date_fin', '>=', $dateDebut->format('Y-m-d'));
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
            $exercice->date_debut = $dateDebut->format('Y-m-d');
            $exercice->date_fin = $dateFin->format('Y-m-d');

            // Génération automatique de l'intitulé (l'intitulé posté par le formulaire
            // est ignoré : seule l'année de la date de début fait foi)
            $exercice->intitule = $this->genererIntitule($dateDebut, $dateFin);
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
                    'message' => 'Exercice « ' . $exercice->intitule . ' » créé du '
                        . $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y') . '.',
                    'exercice' => [
                        'id' => $exercice->id,
                        'intitule' => $exercice->intitule,
                        'date_debut' => $dateDebut->format('Y-m-d'),
                        'date_fin' => $dateFin->format('Y-m-d'),
                    ]
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
        if (!Auth::user()->hasPermission('exercice_comptable')) {
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
        if (!Auth::user()->hasPermission('exercice_comptable')) {
            return back()->with('error', 'Action non autorisée : habilitation « Exercice comptable » requise.');
        }

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exercice = ExerciceComptable::where('company_id', $companyId)->findOrFail($id);
        if ($exercice->cloturer) return back()->with('error', 'L’exercice est déjà clôturé.');

        // Le prochain exercice doit exister AVANT le calcul du SIG et avant toute écriture.
        $dateFinSource = Carbon::parse($exercice->date_fin)->startOfDay();
        $dateDebutSuivant = $dateFinSource->copy()->addDay();
        $dateFinSuivantAttendue = $dateFinSource->copy()->addYear();
        $next = ExerciceComptable::where('company_id', $companyId)
            ->whereDate('date_debut', $dateDebutSuivant->toDateString())->first();

        if (!$next) {
            $annees = $dateDebutSuivant->year === $dateFinSuivantAttendue->year
                ? (string) $dateDebutSuivant->year
                : $dateDebutSuivant->year . '-' . $dateFinSuivantAttendue->year;
            $debutSource = Carbon::parse($exercice->date_debut);
            $intituleSource = $debutSource->year === $dateFinSource->year
                ? (string) $dateFinSource->year
                : $debutSource->year . '-' . $dateFinSource->year;
            $message = "L'exercice {$annees} (du " . $dateDebutSuivant->format('d/m/Y')
                . ' au ' . $dateFinSuivantAttendue->format('d/m/Y')
                . ") n'existe pas encore. Veuillez le créer avant de clôturer l'exercice {$intituleSource}.";
            return back()->with('error', $message)->with('creation_url', route('exercice_comptable'));
        }

        if (Carbon::parse($exercice->date_debut)->gt(now()->startOfDay())) {
            return back()->with('error', 'Impossible de clôturer avant la date de début.');
        }
        if ($dateFinSource->gt(now()->startOfDay())) {
            return back()->with('error', 'Clôture impossible : la date de fin réelle de l’exercice n’est pas encore atteinte. La date de l’exercice ne sera pas raccourcie automatiquement.');
        }
        $pending = EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $id)->where('statut', 'pending')->count();
        if ($pending) {
            return back()->with('error', "Clôture bloquée : {$pending} écriture(s) en attente de validation.");
        }

        try {
            DB::transaction(function () use ($exercice, $companyId, $user, $dateDebutSuivant) {
                $source = ExerciceComptable::where('company_id', $companyId)
                    ->whereKey($exercice->id)->lockForUpdate()->firstOrFail();
                if ($source->cloturer) throw new RuntimeException('L’exercice est déjà clôturé.');
                if (Carbon::parse($source->date_fin)->startOfDay()->gt(now()->startOfDay())) {
                    throw new RuntimeException('Clôture bloquée : la date de fin réelle de l’exercice n’est pas atteinte.');
                }
                if (EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $source->id)->where('statut', 'pending')->exists()) {
                    throw new RuntimeException('Clôture bloquée : des écritures sont encore en attente de validation.');
                }
                if ($this->hasResultatManuel($source)) {
                    throw new RuntimeException('Une écriture manuelle sur 1301/1309 est détectée dans l’exercice source. Clôture automatique bloquée.');
                }

                // Verrouiller et revérifier l'exercice cible avant toute écriture.
                $next = ExerciceComptable::where('company_id', $companyId)
                    ->whereDate('date_debut', $dateDebutSuivant->toDateString())
                    ->lockForUpdate()->first();
                if (!$next) throw new RuntimeException('Clôture bloquée : l’exercice suivant immédiat n’existe plus. Aucun mouvement n’a été créé.');
                if ($this->exerciceEstValideOuCloture($next)) {
                    throw new RuntimeException('Clôture bloquée : l’exercice suivant est déjà clôturé ou validé.');
                }

                $sig = $this->reportingService->getSIGData($source->id, $companyId, null, false, true);
                $resultat = round((float) ($sig['resultat_net'] ?? 0), 2);

                // Nettoyage transactionnel des anciennes pièces RES système créées
                // par la logique antérieure. Les écritures manuelles ne sont jamais supprimées.
                $legacyReference = 'RES-' . $source->id;
                $legacyRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $source->id)
                    ->where('reference_piece', $legacyReference)->get();
                if ($legacyRows->contains(fn ($line) => !(bool) $line->is_resultat_systeme)) {
                    throw new RuntimeException('Une ancienne pièce RES contient des lignes non système. Clôture bloquée ; aucune écriture manuelle ne sera supprimée.');
                }
                if ($legacyRows->isNotEmpty()) {
                    EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $source->id)
                        ->where('reference_piece', $legacyReference)
                        ->where('is_resultat_systeme', true)->delete();
                }

                $compteResultat = null;
                if (abs($resultat) >= 0.01) {
                    $compteResultat = ComptesDeResultat::pour($companyId, $resultat);
                    if (!$compteResultat) {
                        throw new RuntimeException('Le compte ' . ($resultat > 0 ? '1301' : '1309')
                            . ' est absent du plan comptable. Créez-le avant la clôture ; aucune création automatique ne sera effectuée.');
                    }
                }

                $referenceRan = 'RAN-' . $source->id;
                $ranReferenceAilleurs = EcritureComptable::where('company_id', $companyId)
                    ->where('reference_piece', $referenceRan)
                    ->where('exercices_comptables_id', '!=', $next->id)->exists();
                if ($ranReferenceAilleurs) {
                    throw new RuntimeException('La référence de report ' . $referenceRan . ' existe déjà dans un autre exercice. Vérification manuelle requise ; aucun doublon ne sera créé.');
                }
                $ranRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $next->id)
                    ->where('reference_piece', $referenceRan)
                    ->with('planComptable')->get();
                if ($ranRows->contains(fn ($line) => !(bool) $line->is_ran)) {
                    throw new RuntimeException('La référence RAN contient des lignes non identifiées comme report à nouveau. Clôture annulée.');
                }
                foreach ($ranRows as $line) {
                    $classe = substr((string) ($line->planComptable?->numero_de_compte ?? ''), 0, 1);
                    if (in_array($classe, ['6', '7', '8'], true)) {
                        throw new RuntimeException('Le RAN existant contient un compte de classe 6, 7 ou 8. Clôture annulée.');
                    }
                }

                // Le RAN est un journal système paramétré dans l'entreprise. On le
                // retrouve par son TYPE, jamais par un code écrit en dur (RAN, RAN1…).
                $journal = $this->journalReportANouveau($companyId);

                // Premier passage : création du report détaillé des seuls comptes de bilan 1 à 5.
                if ($ranRows->isEmpty()) {
                    $sourceRows = EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $source->id)
                        ->where(function ($q) {
                            $q->whereNull('statut')->orWhereIn('statut', ['approved', 'valid']);
                        })->with('planComptable')->get();
                    $balances = [];
                    foreach ($sourceRows as $line) {
                        $numero = (string) ($line->planComptable?->numero_de_compte ?? '');
                        if (!in_array(substr($numero, 0, 1), ['1', '2', '3', '4', '5'], true)) continue;
                        $key = implode('|', [
                            (string) $line->plan_comptable_id, (string) $line->plan_tiers_id,
                            (string) $line->compte_tresorerie_id, (string) $line->poste_tresorerie_id,
                            (string) $line->type_flux
                        ]);
                        $balances[$key] ??= [
                            'plan_comptable_id' => $line->plan_comptable_id,
                            'plan_tiers_id' => $line->plan_tiers_id,
                            'compte_tresorerie_id' => $line->compte_tresorerie_id,
                            'poste_tresorerie_id' => $line->poste_tresorerie_id,
                            'type_flux' => $line->type_flux, 'balance' => 0.0
                        ];
                        $balances[$key]['balance'] += (float) $line->debit - (float) $line->credit;
                    }
                    foreach ($balances as $balance) {
                        $amount = round($balance['balance'], 2);
                        if (abs($amount) < 0.01) continue;
                        EcritureComptable::create([
                            'date' => $dateDebutSuivant->toDateString(),
                            'n_saisie' => 'RAN-' . Carbon::parse($source->date_fin)->format('Y'),
                            'n_saisie_user' => 'RAN-' . Carbon::parse($source->date_fin)->format('Y'),
                            'description_operation' => $this->libelleReportAN($source),
                            'reference_piece' => $referenceRan,
                            'plan_comptable_id' => $balance['plan_comptable_id'],
                            'plan_tiers_id' => $balance['plan_tiers_id'],
                            'compte_tresorerie_id' => $balance['compte_tresorerie_id'],
                            'poste_tresorerie_id' => $balance['poste_tresorerie_id'],
                            'type_flux' => $balance['type_flux'],
                            'code_journal_id' => $journal->id,
                            'exercices_comptables_id' => $next->id,
                            'debit' => $amount > 0 ? $amount : 0,
                            'credit' => $amount < 0 ? abs($amount) : 0,
                            'company_id' => $companyId, 'user_id' => $user->id,
                            'is_ran' => true, 'is_resultat_systeme' => false,
                            'plan_analytique' => 0, 'statut' => 'approved'
                        ]);
                    }
                }

                // Deuxième passage : le résultat est le déséquilibre du report à nouveau.
                // Une seule ligne 1301/1309, dans le journal OD, datée du 1er jour de l'exercice suivant.
                $referenceRes = 'RES-' . $source->id;
                $ranRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $next->id)
                    ->where('reference_piece', $referenceRan)
                    ->with('planComptable')->get();
                $resRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $next->id)
                    ->where('reference_piece', $referenceRes)
                    ->with('planComptable')->get();
                if ($resRows->contains(fn ($line) => !(bool) $line->is_resultat_systeme)) {
                    throw new RuntimeException('La référence ' . $referenceRes . ' contient des lignes non système. Vérification manuelle requise ; aucune ligne ne sera supprimée.');
                }
                if ($resRows->count() > 1) {
                    throw new RuntimeException('Plusieurs lignes de résultat système existent dans l\'exercice suivant. Vérification manuelle requise ; aucun doublon ne sera ajouté.');
                }
                if ($resRows->isNotEmpty()) {
                    if (abs($resultat) < 0.01) {
                        throw new RuntimeException('Une ligne de résultat existe alors que le résultat est nul. Vérification manuelle requise.');
                    }
                    $row = $resRows->first();
                    $sens = ComptesDeResultat::sens($resultat);
                    $prefix = $resultat > 0 ? '1301' : '1309';
                    if (!str_starts_with((string) $row->planComptable->numero_de_compte, $prefix)
                        || abs(round((float) $row->debit - $sens['debit'], 2)) >= 0.01
                        || abs(round((float) $row->credit - $sens['credit'], 2)) >= 0.01) {
                        throw new RuntimeException('La ligne de résultat déjà présente ne correspond pas au résultat actuel. Aucun remplacement automatique.');
                    }
                } elseif (abs($resultat) >= 0.01) {
                    $journalOd = $this->journalOperationsDiverses($companyId);
                    $sens = ComptesDeResultat::sens($resultat);
                    EcritureComptable::create([
                        'date' => $dateDebutSuivant->toDateString(),
                        'n_saisie' => 'RES-' . Carbon::parse($source->date_fin)->format('Y'),
                        'n_saisie_user' => 'RES-' . Carbon::parse($source->date_fin)->format('Y'),
                        'description_operation' => $this->libelleResultat($source),
                        'reference_piece' => $referenceRes,
                        'plan_comptable_id' => $compteResultat->id,
                        'code_journal_id' => $journalOd->id,
                        'exercices_comptables_id' => $next->id,
                        'debit' => $sens['debit'], 'credit' => $sens['credit'],
                        'company_id' => $companyId, 'user_id' => $user->id,
                        'is_ran' => false, 'is_resultat_systeme' => true,
                        'plan_analytique' => 0, 'statut' => 'approved'
                    ]);
                }

                $ranFinal = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $next->id)
                    ->where('reference_piece', $referenceRan)
                    ->with('planComptable')->get();
                $resFinal = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $next->id)
                    ->where('reference_piece', $referenceRes)
                    ->with('planComptable')->get();
                $finalRows = $ranFinal->concat($resFinal);
                $debitTotal = round((float) $finalRows->sum('debit'), 2);
                $creditTotal = round((float) $finalRows->sum('credit'), 2);
                if (($ranFinal->isEmpty() && abs($resultat) >= 0.01)
                    || $ranFinal->contains(fn ($line) => !(bool) $line->is_ran)
                    || $resFinal->contains(fn ($line) => (bool) $line->is_ran)
                    || abs($debitTotal - $creditTotal) >= 0.01) {
                    throw new RuntimeException(sprintf(
                        'Report à nouveau et résultat non équilibrés (débit %.2f / crédit %.2f). Transaction annulée ; aucune ligne artificielle ne sera ajoutée.',
                        $debitTotal, $creditTotal
                    ));
                }
                foreach ($finalRows as $line) {
                    $classe = substr((string) ($line->planComptable?->numero_de_compte ?? ''), 0, 1);
                    if (in_array($classe, ['6', '7', '8'], true)) {
                        throw new RuntimeException('Le report à nouveau contient un compte de classe 6, 7 ou 8. Transaction annulée.');
                    }
                    if (Carbon::parse($line->date)->toDateString() !== $dateDebutSuivant->toDateString()) {
                        throw new RuntimeException('Une ligne du report ne porte pas la date d’ouverture de l’exercice suivant. Transaction annulée.');
                    }
                }
                if (abs($resultat) < 0.01 ? $resFinal->isNotEmpty() : $resFinal->count() !== 1) {
                    throw new RuntimeException('Il doit y avoir exactement une ligne de résultat (1301 ou 1309), ou aucune si le résultat est nul.');
                }

                $source->update(['cloturer' => true, 'resultat_passe' => true, 'resultat_passe_le' => now()]);
            }, 5);

            return back()->with('success', 'Clôture terminée : le report à nouveau (journal RAN) et le résultat (journal OD, 1301 bénéfice / 1309 perte) ont été créés dans l’exercice suivant. L’ensemble a été contrôlé équilibré.');
        } catch (\Throwable $e) {
            Log::error('Erreur clôture exercice', ['exercice_id' => $id, 'company_id' => $companyId, 'message' => $e->getMessage()]);
            return back()->with('error', 'Clôture annulée : ' . $e->getMessage());
        }
    }

    public function reouvrir($id)
    {
        if (!Auth::user()->hasPermission('exercice_comptable')) {
            return back()->with('error', 'Action non autorisée.');
        }
        $companyId = session('current_company_id', Auth::user()->company_id);
        $exercice = ExerciceComptable::where('company_id', $companyId)->findOrFail($id);
        if (!$exercice->cloturer) return back()->with('error', 'L’exercice est déjà ouvert.');

        try {
            DB::transaction(function () use ($exercice, $companyId) {
                $source = ExerciceComptable::where('company_id', $companyId)
                    ->whereKey($exercice->id)->lockForUpdate()->firstOrFail();
                if (!$source->cloturer) throw new RuntimeException('L’exercice est déjà ouvert.');

                $referenceRan = 'RAN-' . $source->id;
                $dateNext = Carbon::parse($source->date_fin)->addDay()->toDateString();
                $next = ExerciceComptable::where('company_id', $companyId)
                    ->whereDate('date_debut', $dateNext)->first();

                if ($next) {
                    $next = ExerciceComptable::where('company_id', $companyId)
                        ->whereKey($next->id)->lockForUpdate()->firstOrFail();
                    $ranRows = EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $next->id)
                        ->where('reference_piece', $referenceRan)->get();

                    if ($ranRows->contains(fn ($line) => !(bool) $line->is_ran)) {
                        throw new RuntimeException('Réouverture bloquée : la référence RAN contient des lignes non identifiées comme report à nouveau.');
                    }
                    $referenceRes = 'RES-' . $source->id;
                    $otherMovements = EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $next->id)
                        ->where(function ($q) use ($referenceRan) {
                            $q->whereNull('is_ran')->orWhere('is_ran', false)
                                ->orWhereNull('reference_piece')->orWhere('reference_piece', '!=', $referenceRan);
                        })
                        ->where(function ($q) use ($referenceRes) {
                            $q->whereNull('is_resultat_systeme')->orWhere('is_resultat_systeme', false)
                                ->orWhereNull('reference_piece')->orWhere('reference_piece', '!=', $referenceRes);
                        })->exists();
                    if ($otherMovements) {
                        throw new RuntimeException('Réouverture bloquée : l’exercice suivant contient des mouvements autres que le RAN de cette clôture. Aucun mouvement n’a été supprimé.');
                    }
                    if ($this->exerciceEstValideOuCloture($next)) {
                        throw new RuntimeException('Réouverture bloquée : l’exercice suivant est déjà clôturé ou validé.');
                    }
                    EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $next->id)
                        ->where('reference_piece', $referenceRan)
                        ->where('is_ran', true)->delete();
                    EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $next->id)
                        ->where('reference_piece', $referenceRes)
                        ->where('is_resultat_systeme', true)->delete();
                } elseif (EcritureComptable::where('company_id', $companyId)
                    ->where('reference_piece', $referenceRan)->exists()) {
                    throw new RuntimeException('Réouverture bloquée : des lignes RAN existent mais l’exercice cible immédiat est introuvable. Aucune suppression n’a été effectuée.');
                }

                // Compatibilité avec les anciennes pièces RES erronées : suppression
                // uniquement si toutes leurs lignes sont explicitement système.
                $referenceResultat = 'RES-' . $source->id;
                $legacyRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $source->id)
                    ->where('reference_piece', $referenceResultat)->get();
                if ($legacyRows->contains(fn ($line) => !(bool) $line->is_resultat_systeme)) {
                    throw new RuntimeException('Réouverture bloquée : une ancienne pièce RES contient des lignes manuelles. Elles sont conservées.');
                }
                if ($legacyRows->isNotEmpty()) {
                    EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $source->id)
                        ->where('reference_piece', $referenceResultat)
                        ->where('is_resultat_systeme', true)->delete();
                }

                $source->update([
                    'cloturer' => false,
                    'resultat_passe' => false,
                    'resultat_passe_le' => null
                ]);
            }, 5);

            return back()->with('success', 'Exercice rouvert. Le RAN système a été supprimé après vérification ; les mouvements ordinaires sont conservés. Les anciennes pièces RES système éventuelles ont été nettoyées.');
        } catch (\Throwable $e) {
            Log::warning('Réouverture exercice refusée', [
                'exercice_id' => $exercice->id,
                'company_id' => $companyId,
                'erreur' => $e->getMessage()
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasPermission('exercice_comptable')) {
            return back()->with('error', 'Action non autorisée : habilitation « Exercice comptable » requise.');
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
        if (!Auth::user()->hasPermission('exercice_comptable')) {
            return back()->with('error', 'Action non autorisée : habilitation « Exercice comptable » requise.');
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
        ], [
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
        ]);

        $dateDebut = Carbon::parse($request->date_debut)->startOfDay();
        $dateFin = Carbon::parse($request->date_fin)->startOfDay();

        // On ne met à jour que les colonnes maîtrisées ici : l'intitulé reste dérivé
        // de l'année de début, comme à la création.
        $exercice->update([
            'date_debut' => $dateDebut->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
            'intitule' => $this->genererIntitule($dateDebut, $dateFin),
        ]);

        return redirect()->route('exercice_comptable')
            ->with('success', 'Exercice mis à jour avec succès.');
    }

    /**
     * Durée de l'exercice en mois, bornes incluses.
     * Ex : 01/01/2026 -> 31/12/2026 = 12 mois (et non 11).
     */
    private function calculerNbMois($dateDebut, $dateFin)
    {
        $debut = Carbon::parse($dateDebut)->startOfDay();
        $fin = Carbon::parse($dateFin)->startOfDay()->addDay();

        $mois = (int) $debut->diffInMonths($fin);

        return $mois > 0 ? $mois : 1; // Minimum 1 mois
    }

    /**
     * Intitulé normalisé d'un exercice, toujours basé sur l'année de la date de début.
     * Le calcul est fait ici (côté serveur) et non dans le navigateur, afin qu'aucun
     * fuseau horaire client ne puisse décaler l'année.
     */
    private function genererIntitule($dateDebut, $dateFin = null)
    {
        $debut = Carbon::parse($dateDebut);
        $fin = $dateFin ? Carbon::parse($dateFin) : $debut->copy();
        $annees = $debut->year === $fin->year ? (string) $debut->year : $debut->year . '-' . $fin->year;

        return 'EXERCICE ' . $annees;
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

                    // sender_id est obligatoire en base : une notification d'échéance
                    // sans expéditeur faisait planter l'affichage de la page.
                    // Un incident sur la notification ne doit jamais empêcher
                    // la consultation des exercices.
                    try {
                        InternalNotification::create([
                            'title' => 'Échéance d\'exercice',
                            'message' => $msg,
                            'type' => $type,
                            'sender_id' => Auth::id(),
                            'receiver_id' => Auth::id(),
                            'company_id' => $companyId
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Notification échéance exercice non créée : ' . $e->getMessage());
                    }
                }
            }
        }
    }
    /**
     * Journal de report à nouveau de l'entreprise, retrouvé par son type système.
     * Son code dépend du paramétrage (RAN, RAN1…). Il n'est jamais créé ici.
     */
    private function journalReportANouveau(int $companyId): CodeJournal
    {
        $journal = CodeJournal::where('company_id', $companyId)
            ->whereIn('type', ['REPORT A NOUVEAU', 'REPORT À NOUVEAU'])
            ->orderBy('id')->first();
        if (!$journal) {
            throw new RuntimeException('Journal de report à nouveau non paramétré dans les paramètres de l\'entreprise.');
        }
        return $journal;
    }

    /**
     * Journal des opérations diverses de l'entreprise, retrouvé par son type système.
     * Il porte la ligne de résultat (1301/1309), distincte du journal de report à nouveau.
     */
    private function journalOperationsDiverses(int $companyId): CodeJournal
    {
        $journal = CodeJournal::where('company_id', $companyId)
            ->whereIn('type', ['Opérations Diverses', 'OPERATIONS DIVERSES', 'Operations Diverses'])
            ->orderBy('id')->first();
        if (!$journal) {
            throw new RuntimeException('Journal des opérations diverses non paramétré dans les paramètres de l\'entreprise.');
        }
        return $journal;
    }

    /** Libellé de la ligne de résultat, calculé à partir des dates réelles de l'exercice clôturé. */
    private function libelleResultat(ExerciceComptable $exercice): string
    {
        $debut = Carbon::parse($exercice->date_debut);
        $fin = Carbon::parse($exercice->date_fin);
        $periode = $debut->year === $fin->year ? (string) $debut->year : $debut->year.'-'.$fin->year;
        return 'Résultat de l\'exercice '.$periode;
    }

    /** Libellé RAN calculé exclusivement à partir des dates réelles de l'exercice clôturé. */
    private function libelleReportAN(ExerciceComptable $exercice): string
    {
        $debut = Carbon::parse($exercice->date_debut);
        $fin = Carbon::parse($exercice->date_fin);
        $periode = $debut->year === $fin->year ? (string)$debut->year : $debut->year.'-'.$fin->year;
        return 'Report à nouveau exercice '.$periode;
    }

    /** Un exercice cible est bloqué s'il porte un indicateur de clôture/validation disponible dans le schéma. */
    private function exerciceEstValideOuCloture(ExerciceComptable $exercice): bool
    {
        if ((bool)$exercice->cloturer) return true;
        $attributes = $exercice->getAttributes();
        foreach (['valide','validated','is_validated','validation','validation_status','statut','status'] as $field) {
            if (!array_key_exists($field, $attributes)) continue;
            $value = $attributes[$field];
            if (in_array(strtolower((string)$value), ['1','true','validé','validée','valide','validated','approved','closed','cloture','clôturé'], true)) return true;
        }
        return false;
    }

    /** Toute écriture 1301/1309 non explicitement marquée système constitue une intervention manuelle. */
    private function hasResultatManuel(ExerciceComptable $exercice): bool
    {
        return EcritureComptable::where('company_id', $exercice->company_id)
            ->where('exercices_comptables_id', $exercice->id)
            ->where(function ($q) {
                $q->whereNull('statut')->orWhereNotIn('statut', ['rejected', 'refused']);
            })
            ->where(function ($q) {
                $q->whereNull('is_ran')->orWhere('is_ran', false);
            })
            ->where(function ($q) {
                $q->whereNull('is_resultat_systeme')->orWhere('is_resultat_systeme', false);
            })
            ->whereHas('planComptable', function ($q) {
                $q->where('numero_de_compte', 'like', '1301%')
                    ->orWhere('numero_de_compte', 'like', '1309%');
            })->exists();
    }

    /** Vérifie le marqueur système, l’équilibre de la pièce et le solde inverse de toutes les classes 6, 7 et 8. */
    


    /** Le résultat n'est matérialisé qu'à la clôture, dans le RAN. */
    private function resultatEstPasse(ExerciceComptable $exercice): bool
    {
        return (bool) $exercice->cloturer;
    }
}
