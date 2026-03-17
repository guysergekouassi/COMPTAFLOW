<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccountingReportingService;
use App\Services\GrandLivrePaginationService;
use App\Models\PlanComptable;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

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

        \Log::info('Report Bilan Request', [
            'companyId' => $companyId,
            'exerciceId' => $exerciceId,
            'month' => $month,
            'format' => $request->query('format'),
            'headers' => $request->headers->all()
        ]);

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getBilanData($exerciceId, $companyId, $month, $detail);

        $format = $request->query('format');
        if ($format === 'pdf') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour la génération du PDF.'], 404);
            }
            $detailed = $detail;
            $pdf = Pdf::loadView('reporting.pdf.bilan', compact('data', 'exercice', 'month', 'detail', 'detailed'));
            return $pdf->download('bilan_' . ($exercice->intitule ?? 'export') . '.pdf');
        } elseif ($format === 'excel' || $format === 'xlsx') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour l\'export Excel.'], 404);
            }
            return Excel::download(new \App\Exports\BilanExport($data, $exercice, $month, $detail), 'bilan_' . ($exercice->intitule ?? 'export') . '.xlsx');
        }

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

        \Log::info('Report Resultat Request', [
            'companyId' => $companyId,
            'exerciceId' => $exerciceId,
            'month' => $month,
            'format' => $request->query('format')
        ]);

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getSIGData($exerciceId, $companyId, $month, $detail);

        $format = $request->query('format');
        if ($format === 'pdf') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour la génération du PDF.'], 404);
            }
            $detailed = $detail;
            $pdf = Pdf::loadView('reporting.pdf.resultat', compact('data', 'exercice', 'month', 'detail', 'detailed'));
            return $pdf->download('resultat_' . ($exercice->intitule ?? 'export') . '.pdf');
        } elseif ($format === 'excel' || $format === 'xlsx') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour l\'export Excel.'], 404);
            }
            return Excel::download(new \App\Exports\ResultatExport($data, $exercice, $month, $detail), 'resultat_' . ($exercice->intitule ?? 'export') . '.xlsx');
        }

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

        \Log::info('Report TFT Request', [
            'companyId' => $companyId,
            'exerciceId' => $exerciceId,
            'format' => $request->query('format')
        ]);

        if (!$exerciceId) {
            return response()->json(['message' => 'Aucun exercice actif trouvé.'], 422);
        }

        $data = $this->reportingService->getTFTMatrixData($exerciceId, $companyId, $detail);

        $format = $request->query('format');
        if ($format === 'pdf') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour la génération du PDF.'], 404);
            }
            $detailed = $detail;
            $pdf = Pdf::loadView('reporting.pdf.tft', compact('data', 'exercice', 'detail', 'detailed'));
            return $pdf->setPaper('a4', 'landscape')->download('TFT_' . ($exercice->intitule ?? 'export') . '.pdf');
        } elseif ($format === 'excel' || $format === 'xlsx') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour l\'export Excel.'], 404);
            }
            return Excel::download(new \App\Exports\TFTMatrixExport($data, $exercice, $detail), 'TFT_' . ($exercice->intitule ?? 'export') . '.xlsx');
        }

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

        $format = $request->query('format');
        if ($format === 'pdf') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour la génération du PDF.'], 404);
            }
            $detailed = $detail;
            $pdf = Pdf::loadView('reporting.pdf.monthly_resultat', compact('data', 'exercice', 'detail', 'detailed'));
            return $pdf->setPaper('a4', 'landscape')->download('resultat_mensuel_' . ($exercice->intitule ?? 'export') . '.pdf');
        } elseif ($format === 'excel' || $format === 'xlsx') {
            $exercice = ExerciceComptable::find($exerciceId);
            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour l\'export Excel.'], 404);
            }
            return Excel::download(new \App\Exports\MonthlyResultatExport($data, $exercice, $detail), 'resultat_mensuel_' . ($exercice->intitule ?? 'export') . '.xlsx');
        }

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

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        \Log::info('Report Balance Request', [
            'companyId' => $companyId,
            'exerciceId' => $exerciceId,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'format' => $request->query('format')
        ]);

        $results = $query->get();

        $format = $request->query('format');
        if ($format === 'pdf' || $format === 'excel' || $format === 'xlsx') {
            $exercice = ExerciceComptable::find($exerciceId);
            $company = \App\Models\Company::find($companyId);

            if (!$exercice && ($format === 'pdf' || $format === 'excel' || $format === 'xlsx')) {
                return response()->json(['message' => 'Exercice non trouvé pour l\'export.'], 404);
            }
            if (!$company && $format === 'pdf') {
                return response()->json(['message' => 'Entreprise non trouvée pour l\'export PDF.'], 404);
            }
            
            // For export, we might need a more detailed query or just reuse the results
            // BalanceExport expects a collection of ecritures with planComptable
            $ecritures = EcritureComptable::where('company_id', $companyId)
                ->where('exercices_comptables_id', $exerciceId)
                ->with('planComptable')
                ->get();

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('balance', [
                    'company_name' => $company->company_name ?? $company?->name ?? 'Entreprise',
                    'ecritures' => $ecritures,
                    'date_debut' => $dateDebut ?? ($exercice ? $exercice->date_debut : null),
                    'date_fin' => $dateFin ?? ($exercice ? $exercice->date_fin : null),
                    'user' => $user,
                    'titre' => 'Balance des comptes',
                    'display_mode' => $request->query('display_mode') ?? 'comptaflow'
                ]);
                return $pdf->download('balance.pdf');
            } else {
                return Excel::download(new \App\Exports\BalanceExport($ecritures, 'comptaflow'), 'balance.xlsx');
            }
        }

        return response()->json($results);
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

        if (!$exerciceId) {
            $exerciceId = $this->getActiveExerciceId($companyId);
        }

        \Log::info('Report GrandLivre Request', [
            'companyId' => $companyId,
            'exerciceId' => $exerciceId,
            'planComptableId' => $planComptableId,
            'format' => $request->query('format')
        ]);

        $results = $query->orderBy('date')->get();

        $format = $request->query('format');
        if ($format === 'pdf' || $format === 'excel' || $format === 'xlsx') {
            $exercice = ExerciceComptable::find($exerciceId);
            $company = \App\Models\Company::find($companyId);

            if (!$exercice) {
                return response()->json(['message' => 'Exercice non trouvé pour l\'export.'], 404);
            }

            $titre = "Grand-livre des comptes";

            if ($format === 'pdf') {
                // UTILISATION DU SERVICE DE PAGINATION pour le PDF
                $paginationService = new GrandLivrePaginationService();
                $displayMode = $request->query('display_mode') ?? 'comptaflow';
                $paginatedData = $paginationService->paginate($results, [], $titre, $displayMode);

                $pdf = Pdf::loadView('grand_livre', [
                    'company_name' => $company->company_name ?? 'Entreprise',
                    'paginatedData' => $paginatedData,
                    'date_debut' => $dateDebut ?? ($exercice ? $exercice->date_debut : null),
                    'date_fin' => $dateFin ?? ($exercice ? $exercice->date_fin : null),
                    'user' => $user,
                    'titre' => $titre,
                    'display_mode' => $displayMode
                ]);
                return $pdf->download('grand_livre.pdf');
            } else {
                return Excel::download(new \App\Exports\GrandLivreExport($results, []), 'grand_livre.xlsx');
            }
        }

        return response()->json($results);
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
