<?php

namespace App\Http\Controllers;

use App\Models\RapprochementBancaire;
use App\Models\LigneReleveBancaire;
use App\Models\PointageRapprochement;
use App\Models\CompteTresorerie;
use App\Models\ExerciceComptable;
use App\Models\EcritureComptable;
use App\Services\ReleveImportService;
use App\Services\RapprochementMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RapprochementBancaireController extends Controller
{
    public function __construct(
        private ReleveImportService          $importService,
        private RapprochementMatchingService $matchingService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  INDEX — Liste des sessions
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $companyId      = Auth::user()->company_id;
        $rapprochements = RapprochementBancaire::with(['compteTresorerie', 'codeJournal', 'exercice'])
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->paginate(20);

        $comptesBancaires = CompteTresorerie::where('company_id', $companyId)
            ->with('compteComptable')
            ->get();

        $exercices = ExerciceComptable::where('company_id', $companyId)
            ->orderByDesc('date_debut')
            ->get();

        // Journaux de type Banque (BQ, BNI, SGBCI, etc.)
        $codeJournaux = \App\Models\CodeJournal::where('company_id', $companyId)
            ->orderBy('code_journal')
            ->get();

        return view('rapprochement.index', compact(
            'rapprochements', 'comptesBancaires', 'exercices', 'codeJournaux'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  STORE — Créer une nouvelle session
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'compte_tresorerie_id' => 'required|exists:compte_tresoreries,id',
            'exercice_id'          => 'required|exists:exercices_comptables,id',
            'date_debut'           => 'required|date',
            'date_fin'             => 'required|date|after_or_equal:date_debut',
            'solde_initial_banque' => 'required|numeric',
            'solde_final_banque'   => 'required|numeric',
            'solde_initial_compta' => 'required|numeric',
            'code_journal_id'      => 'nullable|exists:code_journals,id',
        ]);

        $companyId = Auth::user()->company_id;

        $rapprochement = RapprochementBancaire::create([
            ...$validated,
            'company_id'  => $companyId,
            'statut'      => 'en_cours',
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('rapprochement.show', $rapprochement->id)
            ->with('success', 'Session de rapprochement créée.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SHOW — Page principale de la session
    // ─────────────────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $companyId     = Auth::user()->company_id;
        $rapprochement = RapprochementBancaire::with([
            'compteTresorerie.compteComptable',
            'codeJournal',
            'exercice',
            'lignesReleve',
            'pointages.ecritureComptable.codeJournal',
            'pointages.ligneReleve',
        ])->where('company_id', $companyId)->findOrFail($id);

        // Écritures comptables pour ce compte et cette période
        $ecritures = EcritureComptable::with(['codeJournal', 'planComptable', 'planTiers'])
            ->where('company_id', $companyId)
            ->where('compte_tresorerie_id', $rapprochement->compte_tresorerie_id)
            ->whereBetween('date', [
                $rapprochement->date_debut->format('Y-m-d'),
                $rapprochement->date_fin->format('Y-m-d'),
            ])
            ->orderBy('date')
            ->get();

        // IDs des écritures déjà pointées
        $ecrituresPointeesIds = $rapprochement->pointages->pluck('ecriture_comptable_id')->toArray();

        // Statistiques
        $stats = $rapprochement->lignesReleve->count() > 0
            ? $this->matchingService->getStats($id)
            : null;

        // Plan comptable pour le modal « Générer écriture corrective »
        // On propose les comptes de charges (6xx), produits (7xx) et frais (627xxx = frais banque)
        $planComptables = \App\Models\PlanComptable::where('company_id', $companyId)
            ->orderBy('numero_de_compte')
            ->get(['id', 'numero_de_compte', 'intitule']);

        return view('rapprochement.show', compact(
            'rapprochement', 'ecritures', 'ecrituresPointeesIds', 'stats', 'planComptables'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  IMPORT DU RELEVÉ BANCAIRE
    // ─────────────────────────────────────────────────────────────────────────

    public function importReleve(Request $request, int $id)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:csv,xlsx,xls,ods|max:10240',
        ]);

        $rapprochement = RapprochementBancaire::findOrFail($id);

        // Supprimer les anciennes lignes si on réimporte
        $rapprochement->lignesReleve()->delete();
        $rapprochement->pointages()->delete();

        // Parser le fichier
        $result = $this->importService->parse($request->file('fichier'));

        if (!empty($result['erreurs']) && empty($result['lignes'])) {
            return response()->json([
                'success' => false,
                'message' => implode(' | ', $result['erreurs']),
            ], 422);
        }

        // Insérer les lignes
        $lignes = [];
        foreach ($result['lignes'] as $ligne) {
            $lignes[] = array_merge($ligne, ['rapprochement_id' => $rapprochement->id]);
        }

        LigneReleveBancaire::insert($lignes);

        // Mettre à jour le nom du fichier
        $rapprochement->update([
            'nom_fichier_releve' => $request->file('fichier')->getClientOriginalName(),
        ]);

        // Recharger les lignes pour la réponse
        $lignesInserted = $rapprochement->lignesReleve()->orderBy('ordre')->get();

        return response()->json([
            'success'  => true,
            'nb_lignes'=> count($result['lignes']),
            'erreurs'  => $result['erreurs'],
            'lignes'   => $lignesInserted,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ANALYSER — Croiser les deux tableaux
    // ─────────────────────────────────────────────────────────────────────────

    public function analyser(int $id)
    {
        $stats = $this->matchingService->getStats($id);
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  RAPPROCHEMENT AUTO
    // ─────────────────────────────────────────────────────────────────────────

    public function autoRapprocher(int $id)
    {
        $result = $this->matchingService->autoMatch($id);
        $stats  = $this->matchingService->getStats($id);

        return response()->json([
            'success'     => true,
            'auto_result' => $result,
            'stats'       => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SAUVEGARDER UN POINTAGE (manuel)
    // ─────────────────────────────────────────────────────────────────────────

    public function savePointage(Request $request, int $id)
    {
        $validated = $request->validate([
            'ligne_releve_id'      => 'required|exists:lignes_releve_bancaire,id',
            'ecriture_comptable_id'=> 'required|exists:ecriture_comptables,id',
            'note'                 => 'nullable|string|max:500',
        ]);

        $pointage = $this->matchingService->savePointage(
            $id,
            $validated['ligne_releve_id'],
            $validated['ecriture_comptable_id'],
            'manuel',
            $validated['note'] ?? null
        );

        $stats = $this->matchingService->getStats($id);

        return response()->json([
            'success'  => true,
            'pointage' => $pointage->load(['ligneReleve', 'ecritureComptable']),
            'stats'    => $stats,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SUPPRIMER UN POINTAGE
    // ─────────────────────────────────────────────────────────────────────────

    public function deletePointage(int $id, int $pointageId)
    {
        $this->matchingService->deletePointage($pointageId);
        $stats = $this->matchingService->getStats($id);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ENREGISTRER — Finaliser la session
    // ─────────────────────────────────────────────────────────────────────────

    public function enregistrer(Request $request, int $id)
    {
        $rapprochement = RapprochementBancaire::findOrFail($id);
        $stats = $this->matchingService->getStats($id);

        $statut = $stats['equilibre'] ? 'valide' : 'en_cours';

        $rapprochement->update([
            'statut' => $statut,
            'note'   => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'statut'  => $statut,
            'message' => $statut === 'valide'
                ? '✅ Rapprochement validé — soldes équilibrés !'
                : '💾 Rapprochement sauvegardé (en cours — écart résiduel : ' . $stats['ecart_residuel'] . ' FCFA)',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  GÉNÉRER UNE ÉCRITURE CORRECTIVE
    // ─────────────────────────────────────────────────────────────────────────

    public function genererEcriture(Request $request, int $id)
    {
        $validated = $request->validate([
            'ligne_releve_id'   => 'required|exists:lignes_releve_bancaire,id',
            'plan_comptable_id' => 'required|exists:plan_comptables,id',
            'description'       => 'required|string|max:255',
            'sens'              => 'nullable|in:debit,credit',
        ]);

        $rapprochement = RapprochementBancaire::with(['codeJournal', 'compteTresorerie', 'exercice'])->findOrFail($id);
        $ligne         = LigneReleveBancaire::findOrFail($validated['ligne_releve_id']);
        $companyId     = Auth::user()->company_id;

        // ── Générer un n_saisie de type RAPPRO_XXXXX ──────────────────────────
        $lastNum = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'RAPPRO_%')
            ->max(DB::raw('CAST(SUBSTRING(n_saisie, 8) AS UNSIGNED)')) ?? 0;
        $nSaisie = 'RAPPRO_' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);

        // ── Sens de l'écriture (logique banque à l'envers) ────────────────────
        // Banque CRÉDIT (entrée argent en banque) = DÉBIT en comptabilité (débit 512)
        // Banque DÉBIT  (sortie argent en banque) = CRÉDIT en comptabilité (crédit 512)
        $montantBanqueDebit  = $ligne->debit  > 0 ? (float) $ligne->debit  : 0;
        $montantBanqueCredit = $ligne->credit > 0 ? (float) $ligne->credit : 0;

        // L'écriture double :
        // Ligne 1 — compte contrepartie (compte de charge/produit saisi par user)
        // Ligne 2 — compte banque (512) lié au CompteTresorerie → géré via compte_tresorerie_id

        // Débit / Crédit du compte de contrepartie :
        //   Si la banque est débitée (sortie) → la charge est débitée
        //   Si la banque est créditée (entrée) → le produit est crédité
        $debitContrepartie  = $montantBanqueDebit  > 0 ? $montantBanqueDebit  : 0;
        $creditContrepartie = $montantBanqueCredit > 0 ? $montantBanqueCredit : 0;

        // Sens forcé par l'utilisateur ?
        if ($validated['sens'] === 'debit') {
            $debitContrepartie  = max($montantBanqueDebit, $montantBanqueCredit);
            $creditContrepartie = 0;
        } elseif ($validated['sens'] === 'credit') {
            $debitContrepartie  = 0;
            $creditContrepartie = max($montantBanqueDebit, $montantBanqueCredit);
        }

        // ── Créer l'écriture ─────────────────────────────────────────────────
        $ecriture = EcritureComptable::create([
            'company_id'              => $companyId,
            'n_saisie'                => $nSaisie,
            'n_saisie_user'           => $nSaisie,
            'date'                    => $ligne->date_operation->format('Y-m-d'),
            'description_operation'   => $validated['description'],
            'reference_piece'         => 'RAPPRO-' . str_pad($id, 5, '0', STR_PAD_LEFT),
            'plan_comptable_id'       => $validated['plan_comptable_id'],
            'code_journal_id'         => $rapprochement->code_journal_id,
            'exercices_comptables_id' => $rapprochement->exercice_id,
            'compte_tresorerie_id'    => $rapprochement->compte_tresorerie_id,
            'debit'                   => $debitContrepartie,
            'credit'                  => $creditContrepartie,
            'user_id'                 => Auth::id(),
            'statut'                  => 'valide',
        ]);

        // ── Pointer automatiquement ──────────────────────────────────────────
        $this->matchingService->savePointage(
            $id, $ligne->id, $ecriture->id, 'auto',
            'Écriture corrective générée automatiquement (' . $nSaisie . ')'
        );

        $stats = $this->matchingService->getStats($id);

        return response()->json([
            'success'   => true,
            'ecriture'  => $ecriture,
            'n_saisie'  => $nSaisie,
            'stats'     => $stats,
            'message'   => '✅ Écriture ' . $nSaisie . ' créée et pointée automatiquement.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  GET ÉCRITURES NON POINTÉES (pour le rapprochement manuel AJAX)
    // ─────────────────────────────────────────────────────────────────────────

    public function getEcrituresDisponibles(Request $request, int $id)
    {
        $rapprochement    = RapprochementBancaire::findOrFail($id);
        $pointeesIds      = $rapprochement->pointages()->pluck('ecriture_comptable_id')->toArray();

        $ecritures = EcritureComptable::with(['codeJournal'])
            ->where('company_id', Auth::user()->company_id)
            ->where('compte_tresorerie_id', $rapprochement->compte_tresorerie_id)
            ->whereBetween('date', [
                $rapprochement->date_debut->format('Y-m-d'),
                $rapprochement->date_fin->format('Y-m-d'),
            ])
            ->whereNotIn('id', $pointeesIds)
            ->when($request->montant, fn($q, $m) =>
                $q->where(fn($q2) =>
                    $q2->where('debit', $m)->orWhere('credit', $m)
                )
            )
            ->orderBy('date')
            ->get();

        return response()->json(['ecritures' => $ecritures]);
    }
}
