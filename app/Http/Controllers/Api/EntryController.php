<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\JournalSaisi;
use App\Models\Approval;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * EntryController gère les écritures comptables et le scan IA pour le mobile.
 */
class EntryController extends Controller
{
    /**
     * Liste des écritures filtrées par exercice, entreprise et utilisateur.
     */
    public function index(Request $request)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        $exerciceId = $request->header('X-Exercice-Id');

        $query = EcritureComptable::where('company_id', $companyId)->with(['planComptable', 'planTiers', 'codeJournal']);


        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        } else {
            // Par défaut, seulement les validées
            $query->where('statut', 'approved');
        }

        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('admin.approvals')) {
            $query->where('user_id', $user->id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description_operation', 'like', '%' . $search . '%')
                  ->orWhere('n_saisie', 'like', '%' . $search . '%')
                  ->orWhere('reference_piece', 'like', '%' . $search . '%');
            });
        }

        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Enregistre une seule ligne d'écriture.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);

        $request->validate([
            'date' => 'required|date',
            'n_saisie' => 'required|string',
            'code_journal_id' => 'required|exists:code_journaux,id',
            'description_operation' => 'required|string',
            'plan_comptable_id' => 'required|exists:plan_comptables,id',
            'plan_tiers_id' => 'nullable|exists:plan_tiers,id',
            'compte_tresorerie_id' => 'nullable|exists:compte_tresoreries,id',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
            'piece_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $exercice = $this->getActiveExercice($companyId);
        if (!$exercice) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $pieceFilename = null;
        if ($request->hasFile('piece_justificatif')) {
            $file = $request->file('piece_justificatif');
            $pieceFilename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('justificatifs'), $pieceFilename);
        }

        $status = ($user->isAdmin() || $user->hasPermission('admin.approvals')) ? 'approved' : 'pending';
        
        // Numérotation
        $nSaisie = ($status === 'approved') ? $this->generateGlobalSaisieNumber($companyId) : $request->n_saisie;

        $ecriture = EcritureComptable::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'n_saisie' => $nSaisie,
            'n_saisie_user' => $request->n_saisie,
            'code_journal_id' => $request->code_journal_id,
            'exercices_comptables_id' => $exercice->id,
            'date' => $request->date,
            'description_operation' => $request->description_operation,
            'reference_piece' => $request->reference_piece,
            'plan_comptable_id' => $request->plan_comptable_id,
            'plan_tiers_id' => $request->plan_tiers_id,
            'compte_tresorerie_id' => $request->compte_tresorerie_id,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'piece_justificatif' => $pieceFilename,
            'statut' => $status,
        ]);

        if ($status === 'pending') {
            Approval::create([
                'approvable_type' => EcritureComptable::class,
                'approvable_id' => $ecriture->id,
                'type' => 'accounting_entry',
                'status' => 'pending',
                'requested_by' => $user->id,
                'data' => ['n_saisie' => $nSaisie]
            ]);
        }

        return response()->json($ecriture, 201);
    }

    /**
     * Scan une pièce via l'IA (Logique portée de IaController).
     */
    public function scan(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);

        $request->validate(['facture' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240']);

        // On appelle le IaController interne ou on duplique la logique pour l'API
        // Pour garder l'indépendance de l'API, on utilise une version simplifiée ou on instancie le controller
        $iaController = new \App\Http\Controllers\IaController();
        return $iaController->traiterFacture($request);
    }

    /**
     * Charge les lignes d'une saisie.
     */
    public function loadBySaisie(Request $request, $n_saisie)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        $ecritures = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('company_id', $companyId)
            ->where('n_saisie', $n_saisie)
            ->get();

        return response()->json($ecritures);
    }

    /**
     * Supprime une saisie (toutes les lignes).
     */
    public function destroy(Request $request, $n_saisie)
    {
        $companyId = $request->header('X-Company-Id', $request->user()->company_id);
        $count = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', $n_saisie)
            ->delete();

        return response()->json(['message' => "$count lignes supprimées."]);
    }

    // --- Helpers ---

    private function getActiveExercice($companyId)
    {
        return ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', 1)
            ->first() ?? ExerciceComptable::where('company_id', $companyId)
            ->where('cloturer', 0)
            ->orderBy('date_debut', 'desc')
            ->first();
    }

    private function generateGlobalSaisieNumber($companyId)
    {
        $last = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'ECR_%')
            ->latest('id')
            ->first();

        $nextNum = $last ? ((int)str_replace('ECR_', '', $last->n_saisie) + 1) : 1;
        return 'ECR_' . str_pad($nextNum, 12, '0', STR_PAD_LEFT);
    }
}
