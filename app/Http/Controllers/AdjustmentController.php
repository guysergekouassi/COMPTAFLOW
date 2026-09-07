<?php

namespace App\Http\Controllers;

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdjustmentController extends Controller
{
    /**
     * Détection de doublons (Écritures presque similaires)
     */
    public function duplicates(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $exerciceActif = ExerciceComptable::where('company_id', $activeCompanyId)->where('is_active', 1)->first();
            $exerciceId = $exerciceActif ? $exerciceActif->id : null;
        }

        if (!$exerciceId) {
            return redirect()->back()->with('error', 'Veuillez sélectionner un exercice comptable pour la détection de doublons.');
        }

        // Algorithme : Grouper par date, journal, débit, crédit
        // On cherche les groupes ayant plus d'une ligne
        $duplicates = EcritureComptable::where('company_id', $activeCompanyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->where('statut', 'approved')
            ->select('date', 'code_journal_id', 'debit', 'credit', DB::raw('count(*) as count'))
            ->groupBy('date', 'code_journal_id', 'debit', 'credit')
            ->having('count', '>', 1)
            ->get();

        $groupedEntries = [];
        foreach ($duplicates as $dup) {
            $entries = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
                ->where('company_id', $activeCompanyId)
                ->where('exercices_comptables_id', $exerciceId)
                ->where('date', $dup->date)
                ->where('code_journal_id', $dup->code_journal_id)
                ->where('debit', $dup->debit)
                ->where('credit', $dup->credit)
                ->orderBy('created_at')
                ->get();
            
            $groupedEntries[] = [
                'criteria' => $dup,
                'entries' => $entries
            ];
        }

        return view('adjustment.duplicates', compact('groupedEntries'));
    }

    /**
     * Page de modification par lot
     */
    public function bulkEdit(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('company_id', $activeCompanyId);

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        // Filtres
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('description_operation', 'like', "%$s%")
                  ->orWhere('reference_piece', 'like', "%$s%");
            });
        }

        if ($request->filled('journal_id')) {
            $query->where('code_journal_id', $request->journal_id);
        }

        if ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }

        $entries = $query->orderBy('date', 'desc')->paginate(100);
        $journals = CodeJournal::where('company_id', $activeCompanyId)->get();

        return view('adjustment.bulk_edit', compact('entries', 'journals'));
    }

    /**
     * Traitement de la modification par lot
     */
    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $field = $request->input('field');
        $newValue = $request->input('value');

        if (empty($ids) || !$field) {
            return response()->json(['success' => false, 'message' => 'Données invalides.']);
        }

        try {
            DB::beginTransaction();

            $updateData = [];
            if ($field === 'plan_comptable_id') {
                $updateData['plan_comptable_id'] = $newValue;
            } elseif ($field === 'plan_tiers_id') {
                $updateData['plan_tiers_id'] = $newValue === 'null' ? null : $newValue;
            } elseif ($field === 'description_operation') {
                $updateData['description_operation'] = $newValue;
            } elseif ($field === 'reference_piece') {
                $updateData['reference_piece'] = $newValue;
            }

            if (!empty($updateData)) {
                EcritureComptable::whereIn('id', $ids)->update($updateData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => count($ids) . ' écritures mises à jour.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    /**
     * Page de réimputation (changement de numéro de compte)
     */
    public function reimputation(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('company_id', $activeCompanyId);

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        // Filtres
        if ($request->filled('compte_id')) {
            $query->where('plan_comptable_id', $request->compte_id);
        }

        if ($request->filled('journal_id')) {
            $query->where('code_journal_id', $request->journal_id);
        }

        if ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->where('date', '<=', $request->date_end);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('description_operation', 'like', "%$s%")
                  ->orWhere('reference_piece', 'like', "%$s%");
            });
        }

        $entries = $query->orderBy('date', 'desc')->paginate(50);
        $journals = CodeJournal::where('company_id', $activeCompanyId)->get();
        $comptes = PlanComptable::where('company_id', $activeCompanyId)->orderBy('numero_de_compte')->get();

        // Compte sélectionné pour l'affichage du filtre
        $selectedCompte = $request->filled('compte_id')
            ? PlanComptable::find($request->compte_id)
            : null;

        return view('adjustment.reimputation', compact(
            'entries', 'journals', 'comptes', 'selectedCompte'
        ));
    }

    /**
     * Appliquer la réimputation (changer le numéro de compte)
     *
     * Règles :
     *  - les écritures d'un exercice clôturé ne peuvent pas être réimputées ;
     *  - chaque lot est tracé dans le journal d'audit avec l'ancien compte de
     *    chaque ligne, ce qui permet de revenir en arrière si besoin.
     */
    public function applyReimputation(Request $request)
    {
        $request->validate([
            'ids'             => 'required|array|min:1',
            'ids.*'           => 'integer|exists:ecriture_comptables,id',
            'new_compte_id'   => 'required|exists:plan_comptables,id',
        ]);

        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);

        $newCompte = PlanComptable::where('company_id', $activeCompanyId)
            ->find($request->new_compte_id);

        if (!$newCompte) {
            return response()->json([
                'success' => false,
                'message' => "Le compte de destination n'appartient pas à l'entreprise courante.",
            ], 422);
        }

        // Lignes réellement concernées (cloisonnement par entreprise)
        $ecritures = EcritureComptable::with('planComptable:id,numero_de_compte,intitule')
            ->whereIn('id', $request->ids)
            ->where('company_id', $activeCompanyId)
            ->get();

        if ($ecritures->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Aucune écriture correspondante n'a été trouvée pour cette entreprise.",
            ], 422);
        }

        // Blocage des exercices clôturés
        $exercicesClotures = ExerciceComptable::whereIn('id', $ecritures->pluck('exercices_comptables_id')->filter()->unique())
            ->where('cloturer', 1)
            ->pluck('intitule', 'id');

        if ($exercicesClotures->isNotEmpty()) {
            $bloquees = $ecritures->whereIn('exercices_comptables_id', $exercicesClotures->keys())->count();
            $libelles = implode(', ', array_filter($exercicesClotures->all()));

            return response()->json([
                'success' => false,
                'message' => "Réimputation impossible : {$bloquees} écriture(s) appartiennent à un exercice clôturé"
                    . ($libelles ? " ({$libelles})" : '')
                    . ". Rouvrez l'exercice avant de corriger ces écritures.",
            ], 422);
        }

        // Lignes déjà imputées sur le compte cible : rien à faire
        $aModifier = $ecritures->where('plan_comptable_id', '!=', $newCompte->id);

        if ($aModifier->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "Les écritures sélectionnées sont déjà imputées sur le compte {$newCompte->numero_de_compte}.",
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updated = EcritureComptable::whereIn('id', $aModifier->pluck('id'))
                ->where('company_id', $activeCompanyId)
                ->update(['plan_comptable_id' => $newCompte->id]);

            // Traçabilité : l'update de masse ne déclenche pas les events Eloquent,
            // le journal d'audit est donc alimenté explicitement.
            $this->logReimputation($request, $activeCompanyId, $aModifier, $newCompte, $updated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updated} écriture(s) réimputée(s) vers le compte {$newCompte->numero_de_compte} - {$newCompte->intitule}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Réimputation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Enregistre le lot de réimputation dans le journal d'audit.
     * Le payload conserve l'ancien compte de chaque ligne pour permettre un retour arrière.
     */
    private function logReimputation(Request $request, $companyId, $ecritures, PlanComptable $newCompte, $updated)
    {
        $lignes = $ecritures->map(function ($e) {
            return [
                'ecriture_id'      => $e->id,
                'n_saisie'         => $e->n_saisie,
                'date'             => optional($e->date)->format('Y-m-d') ?? (string) $e->date,
                'ancien_compte_id' => $e->plan_comptable_id,
                'ancien_compte'    => $e->planComptable->numero_de_compte ?? null,
                'debit'            => $e->debit,
                'credit'           => $e->credit,
            ];
        })->values()->all();

        $anciensComptes = collect($lignes)->pluck('ancien_compte')->filter()->unique()->sort()->values();

        \App\Models\AuditLog::create([
            'user_id'     => Auth::id(),
            'company_id'  => $companyId,
            'action'      => 'REIMPUTATION',
            'model_type'  => EcritureComptable::class,
            'model_id'    => null,
            'description' => "Réimputation de {$updated} écriture(s) : "
                . ($anciensComptes->isNotEmpty() ? $anciensComptes->implode(', ') : 'compte inconnu')
                . " → {$newCompte->numero_de_compte} - {$newCompte->intitule}",
            'payload'     => [
                'nouveau_compte'  => [
                    'id'       => $newCompte->id,
                    'numero'   => $newCompte->numero_de_compte,
                    'intitule' => $newCompte->intitule,
                ],
                'anciens_comptes' => $anciensComptes->all(),
                'nb_ecritures'    => $updated,
                'lignes'          => $lignes,
            ],
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);
    }

    /**
     * Recherche AJAX pour autocomplétion (comptes et tiers)
     */
    public function searchReferences(Request $request)
    {
        $type = $request->input('type'); // 'account' or 'tier'
        $q = $request->input('q', '');
        $companyId = session('current_company_id');

        if ($type === 'account') {
            $results = PlanComptable::where('company_id', $companyId)
                ->where(function($query) use ($q) {
                    $query->where('numero_de_compte', 'like', "$q%")
                          ->orWhere('intitule', 'like', "%$q%");
                })
                ->orderBy('numero_de_compte')
                ->limit(20)
                ->get()
                ->map(fn($item) => ['id' => $item->id, 'text' => $item->numero_de_compte . ' - ' . $item->intitule]);
        } else {
            $results = PlanTiers::where('company_id', $companyId)
                ->where(function($query) use ($q) {
                    $query->where('numero_de_tiers', 'like', "$q%")
                          ->orWhere('intitule', 'like', "%$q%");
                })
                ->orderBy('numero_de_tiers')
                ->limit(20)
                ->get()
                ->map(fn($item) => ['id' => $item->id, 'text' => $item->numero_de_tiers . ' - ' . $item->intitule]);
        }

        return response()->json($results);
    }
}
