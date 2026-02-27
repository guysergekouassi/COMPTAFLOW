<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountingReportingService;
use App\Models\PlanComptable;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReportController gère les états financiers par API.
 */
class ReportController extends Controller
{
    protected $reportingService;

    public function __construct(AccountingReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Rapport : Bilan (Balance Sheet).
     */
    public function bilan(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $month = $request->query('month');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getBilanData($exerciceId, $companyId, $month, $detail);
        return response()->json($data);
    }

    /**
     * Rapport : Résultat (SIG / Income Statement).
     */
    public function resultat(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $month = $request->query('month');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getSIGData($exerciceId, $companyId, $month, $detail);
        return response()->json($data);
    }

    /**
     * Rapport : TFT (Tableau des Flux de Trésorerie).
     */
    public function tft(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getTFTMatrixData($exerciceId, $companyId, $detail);
        return response()->json($data);
    }

    /**
    /**
     * Rapport : Résultat Mensuel (SIG détaillée mois par mois).
     */
    public function monthlyResultat(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getMonthlyResultatData($exerciceId, $companyId, $detail);
        return response()->json($data);
    }

    /**
     * Rapport : TFT Mensuel (Flux de trésorerie mois par mois).
     */
    public function monthlyTft(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getTFTMatrixData($exerciceId, $companyId, $detail);
        return response()->json($data);
    }

    /**
     * Rapport : TFT Personnalisé.
     */
    public function personalizedTft(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getPersonalizedTFTData($exerciceId, $companyId, $detail);
        return response()->json($data);
    }

    /**

     * Rapport : Balance des comptes.
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');

        $query = EcritureComptable::select(
            'plan_comptable_id',
            DB::raw('SUM(debit) as total_debit'),
            DB::raw('SUM(credit) as total_credit'),
            DB::raw('SUM(debit - credit) as solde')
        )
        ->where('company_id', $companyId)
        ->groupBy('plan_comptable_id')
        ->with('planComptable:id,numero_de_compte,intitule');

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }
        if ($dateDebut) {
            $query->where('date', '>=', $dateDebut);
        }
        if ($dateFin) {
            $query->where('date', '<=', $dateFin);
        }

        return response()->json($query->get());
    }

    /**
     * Rapport : Grand Livre (Détail par compte).
     */
    public function grandLivre(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user->company_id);
        $exerciceId = $request->header('X-Exercice-Id');
        $planComptableId = $request->query('plan_comptable_id');
        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');

        $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
            ->where('company_id', $companyId);

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }
        if ($planComptableId) {
            $query->where('plan_comptable_id', $planComptableId);
        }
        if ($dateDebut) {
            $query->where('date', '>=', $dateDebut);
        }
        if ($dateFin) {
            $query->where('date', '<=', $dateFin);
        }

        return response()->json($query->orderBy('date')->get());
    }

    private function getActiveExerciceId($companyId)
    {
        $ex = ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', 1)
            ->first();
        return $ex ? $ex->id : null;
    }

    /**
     * Rapport : Balance Analytique.
     */
    public function balanceAnalytique(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user ? $user->company_id : null);
        $exerciceId = $request->header('X-Exercice-Id');
        $axeId = $request->query('axe_id');

        if (!$axeId) return response()->json(['message' => 'L\'ID de l\'axe est requis.'], 422);

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        $data = $this->reportingService->getBalanceAnalytiqueData($exerciceId, $companyId, $axeId);
        return response()->json($data);
    }

    /**
     * Rapport : Grand Livre Analytique.
     */
    public function grandLivreAnalytique(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user ? $user->company_id : null);
        $exerciceId = $request->header('X-Exercice-Id');
        $axeId = $request->query('axe_id');
        $sectionId = $request->query('section_id');

        if (!$axeId) return response()->json(['message' => 'L\'ID de l\'axe est requis.'], 422);

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        $data = $this->reportingService->getGrandLivreAnalytiqueData($exerciceId, $companyId, $axeId, $sectionId);
        return response()->json($data);
    }

    /**
     * Rapport : Résultat Analytique.
     */
    public function resultatAnalytique(Request $request)
    {
        $user = $request->user();
        $companyId = $request->header('X-Company-Id', $user ? $user->company_id : null);
        $exerciceId = $request->header('X-Exercice-Id');
        $axeId = $request->query('axe_id');

        if (!$axeId) return response()->json(['message' => 'L\'ID de l\'axe est requis.'], 422);

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        $data = $this->reportingService->getResultatAnalytiqueData($exerciceId, $companyId, $axeId);
        return response()->json($data);
    }
}
