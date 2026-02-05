<?php

namespace App\Http\Controllers;

use App\Models\Immobilisation;
use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
use App\Services\ImmobilisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImmobilisationController extends Controller
{
    protected $immobilisationService;

    public function __construct(ImmobilisationService $immobilisationService)
    {
        $this->immobilisationService = $immobilisationService;
    }

    /**
     * Liste des immobilisations
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // 1. Immobilisations enregistrées
        $query = Immobilisation::where('company_id', $companyId)
            ->with(['compteImmobilisation', 'compteAmortissement', 'compteDotation']);

        // 2. Écritures de classe 2 (Potentielles immobilisations) non encore liées
        // On cherche les écritures dont le compte commence par '2' mais pas '28' (Amortissements) ni '29' (Dépréciations) si on veut être strict,
        // mais généralemnt on veut juste tout ce qui est en 2.
        // Assurons-nous d'exclure celles qui ont déjà un ID d'immobilisation via la relation inverse ou un champ.
        // Comme on a ajouté ecriture_id sur Immobilisation, on doit chercher les écritures qui ne sont PAS dans la liste des ecriture_id des immobilisations.
        
        $queryImmo = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->whereHas('planComptable', function ($q) {
                $q->where('numero_de_compte', 'like', '2%')
                  ->where('numero_de_compte', 'not like', '28%') // Exclure amortissements
                  ->where('numero_de_compte', 'not like', '29%'); // Exclure dépréciations
            })
            ->whereDoesntHave('immobilisation');

        if (session()->has('current_exercice_id')) {
            $queryImmo->where('exercices_comptables_id', session('current_exercice_id'));
        }

        $ecrituresImmobilisables = $queryImmo->orderBy('date', 'desc')->get();

        // Filtres
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('exercice_id')) {
            $query->where('exercice_id', $request->exercice_id);
        }

        $immobilisations = $query->orderBy('created_at', 'desc')->get();

        // KPIs
        $totalImmobilisations = $immobilisations->count();
        $vncTotale = $immobilisations->sum(function ($immo) {
            return $immo->getValeurNetteComptable();
        });
        
        $exerciceActif = null;
        if (session()->has('current_exercice_id')) {
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)
                ->where('id', session('current_exercice_id'))
                ->first();
        }

        if (!$exerciceActif) {
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
        }
        
        $dotationsAnnee = 0;
        if ($exerciceActif) {
            $annee = \Carbon\Carbon::parse($exerciceActif->date_debut)->year;
            $dotationsAnnee = \App\Models\Amortissement::whereHas('immobilisation', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('annee', $annee)
            ->where('statut', 'comptabilise')
            ->sum('dotation_annuelle');
        }

        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('date_debut', 'desc')
            ->get();

        return view('immobilisations.dashboard', compact(
            'immobilisations',
            'totalImmobilisations',
            'vncTotale',
            'dotationsAnnee',
            'exercices',
            'ecrituresImmobilisables'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // Pré-remplissage depuis une écriture
        $ecriture = null;
        if ($request->has('ecriture_id')) {
            $ecriture = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->findOrFail($request->ecriture_id);
        }

        // Comptes de classe 2 (Immobilisations)
        $comptesImmobilisation = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', '2%')
            ->whereRaw('LENGTH(numero_de_compte) >= 4')
            ->orderBy('numero_de_compte')
            ->get();

        // Comptes de classe 28 (Amortissements)
        $comptesAmortissement = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', '28%')
            ->orderBy('numero_de_compte')
            ->get();

        // Comptes de classe 68 (Dotations)
        $comptesDotation = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', '681%')
            ->orderBy('numero_de_compte')
            ->get();

        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderBy('date_debut', 'desc')
            ->get();

        return view('immobilisations.create', compact(
            'comptesImmobilisation',
            'comptesAmortissement',
            'comptesDotation',
            'exercices',
            'ecriture'
        ));
    }

    /**
     * Enregistrer une nouvelle immobilisation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:immobilisations,code',
            'libelle' => 'required|string|max:255',
            'categorie' => 'required|in:incorporelle,corporelle,financiere',
            'description' => 'nullable|string',
            'compte_immobilisation_id' => 'required|exists:plan_comptables,id',
            'compte_amortissement_id' => 'required|exists:plan_comptables,id',
            'compte_dotation_id' => 'required|exists:plan_comptables,id',
            'date_acquisition' => 'required|date',
            'valeur_acquisition' => 'required|numeric|min:0',
            'fournisseur' => 'nullable|string|max:255',
            'numero_facture' => 'nullable|string|max:255',
            'date_mise_en_service' => 'required|date',
            'duree_amortissement' => 'required|integer|min:1|max:50',
            'methode_amortissement' => 'required|in:lineaire,degressif',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'ecriture_id' => 'nullable|exists:ecriture_comptables,id',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        $validated['company_id'] = $companyId;
        $validated['exercice_id'] = $exerciceId;
        $validated['valeur_residuelle'] = $validated['valeur_residuelle'] ?? 0;

        try {
            $immobilisation = $this->immobilisationService->creerImmobilisation($validated);
            
            return redirect()->route('immobilisations.show', $immobilisation->id)
                ->with('success', 'Immobilisation créée avec succès. Le tableau d\'amortissement a été généré.');
        } catch (\Exception $e) {
            Log::error('Erreur création immobilisation: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Erreur lors de la création de l\'immobilisation: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une immobilisation
     */
    public function show($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $immobilisation = Immobilisation::where('company_id', $companyId)
            ->with([
                'compteImmobilisation',
                'compteAmortissement',
                'compteDotation',
                'amortissements' => function ($query) {
                    $query->orderBy('annee');
                }
            ])
            ->findOrFail($id);

        return view('immobilisations.show', compact('immobilisation'));
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $immobilisation = Immobilisation::where('company_id', $companyId)->findOrFail($id);

        // Mêmes comptes que pour la création
        $comptesImmobilisation = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', '2%')
            ->whereRaw('LENGTH(numero_de_compte) >= 4')
            ->orderBy('numero_de_compte')
            ->get();

        $comptesAmortissement = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', '28%')
            ->orderBy('numero_de_compte')
            ->get();

        $comptesDotation = PlanComptable::where('company_id', $companyId)
            ->where('numero_de_compte', 'like', '681%')
            ->orderBy('numero_de_compte')
            ->get();

        return view('immobilisations.edit', compact(
            'immobilisation',
            'comptesImmobilisation',
            'comptesAmortissement',
            'comptesDotation'
        ));
    }

    /**
     * Mettre à jour une immobilisation
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $immobilisation = Immobilisation::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fournisseur' => 'nullable|string|max:255',
            'numero_facture' => 'nullable|string|max:255',
        ]);

        try {
            $immobilisation->update($validated);
            
            return redirect()->route('immobilisations.show', $immobilisation->id)
                ->with('success', 'Immobilisation modifiée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur modification immobilisation: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une immobilisation
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $immobilisation = Immobilisation::where('company_id', $companyId)->findOrFail($id);

        try {
            $immobilisation->delete();
            
            return redirect()->route('immobilisations.index')
                ->with('success', 'Immobilisation supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression immobilisation: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Générer les dotations annuelles
     */
    public function genererDotations(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            return back()->with('error', 'Aucun exercice actif sélectionné.');
        }

        try {
            $compteur = $this->immobilisationService->genererDotationsAnnuelles($exerciceId, $companyId);
            
            return back()->with('success', "$compteur dotations aux amortissements ont été générées et comptabilisées.");
        } catch (\Exception $e) {
            Log::error('Erreur génération dotations: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la génération des dotations: ' . $e->getMessage());
        }
    }

    /**
     * Exporter le tableau d'amortissement
     */
    public function exportTableau($id, Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $immobilisation = Immobilisation::where('company_id', $companyId)
            ->with('amortissements')
            ->findOrFail($id);

        $format = $request->query('format', 'pdf');

        if ($format === 'excel') {
            return \Excel::download(
                new \App\Exports\TableauAmortissementExport($immobilisation),
                'tableau_amortissement_' . $immobilisation->code . '.xlsx'
            );
        }

        // PDF
        $pdf = \PDF::loadView('immobilisations.pdf.tableau', compact('immobilisation'));
        return $pdf->download('tableau_amortissement_' . $immobilisation->code . '.pdf');
    }

    /**
     * Céder une immobilisation
     */
    public function ceder(Request $request, $id)
    {
        $validated = $request->validate([
            'date_cession' => 'required|date',
            'montant_cession' => 'required|numeric|min:0',
            'compte_cession_id' => 'required|exists:plan_comptables,id',
            'motif_cession' => 'nullable|string',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $immobilisation = Immobilisation::where('company_id', $companyId)->findOrFail($id);

        try {
            $this->immobilisationService->cederImmobilisation($immobilisation, $validated);
            
            return redirect()->route('immobilisations.show', $immobilisation->id)
                ->with('success', 'Immobilisation cédée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur cession immobilisation: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la cession: ' . $e->getMessage());
        }
    }
}
