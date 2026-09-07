<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountingReportingService;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportingController extends Controller
{
    protected $reportingService;

    public function __construct(AccountingReportingService $reportingService)
    {
        $this->middleware('auth');
        $this->reportingService = $reportingService;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  CONTEXTE COMMUN (exercice / période / comparatif N-1 / détail)
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Résout le contexte d'un état financier à partir de la requête.
     *
     * Priorité pour l'exercice : paramètre `exercice_id` du formulaire de
     * téléchargement > exercice du contexte de session > exercice actif.
     * L'exercice demandé est systématiquement re-vérifié contre l'entreprise
     * active, pour qu'aucun paramètre d'URL ne puisse sortir du tenant.
     *
     * @return array{
     *   companyId:int, exercice:?ExerciceComptable, exercices:\Illuminate\Support\Collection,
     *   exerciceN1:?ExerciceComptable, months:array, month:?string, monthFrom:int, monthTo:int,
     *   detail:bool, comparatif:bool, format:string
     * }
     */
    private function reportContext(Request $request): array
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exercices = $this->reportingService->getExercicesSelectionnables($companyId);

        // ── Exercice demandé ──────────────────────────────────────────────
        $exercice   = null;
        $requestedId = $request->input('exercice_id');

        if ($requestedId) {
            $exercice = $exercices->firstWhere('id', (int) $requestedId);
        }
        if (!$exercice && session('current_exercice_id')) {
            $exercice = $exercices->firstWhere('id', (int) session('current_exercice_id'));
        }
        if (!$exercice) {
            $exercice = $exercices->firstWhere('is_active', 1)
                ?? $exercices->firstWhere('cloturer', 0)
                ?? $exercices->first();
        }

        // ── Mois / plage de mois ──────────────────────────────────────────
        $months = $exercice ? $this->reportingService->getMonthsForExercice($exercice->id) : [];
        $nbMois = count($months);

        $month = $request->input('month');
        if ($month === '' ) {
            $month = null;
        }

        // Plage de mois (états mensuels) : index 1..N dans la liste des mois de l'exercice.
        $monthFrom = (int) $request->input('month_from', 1);
        $monthTo   = (int) $request->input('month_to', $nbMois ?: 12);
        $monthFrom = max(1, min($monthFrom, $nbMois ?: 12));
        $monthTo   = max($monthFrom, min($monthTo, $nbMois ?: 12));

        // ── Comparatif N-1 ────────────────────────────────────────────────
        $comparatif = in_array($request->input('comparatif'), ['1', 1, true, 'true'], true);
        $exerciceN1 = $comparatif ? $this->reportingService->getExercicePrecedent($exercice) : null;

        return [
            'companyId'  => $companyId,
            'exercice'   => $exercice,
            'exercices'  => $exercices,
            'exerciceN1' => $exerciceN1,
            'months'     => $months,
            'month'      => $month,
            'monthFrom'  => $monthFrom,
            'monthTo'    => $monthTo,
            'detail'     => $request->input('detail') == '1',
            'comparatif' => $comparatif,
            'format'     => $request->input('format', 'pdf'),
        ];
    }

    /** Redirection commune quand aucun exercice n'est exploitable. */
    private function noExercice()
    {
        return redirect()->route('exercice_comptable')
            ->with('error', 'Veuillez sélectionner un exercice actif.');
    }

    /** En-têtes anti-cache communs aux téléchargements. */
    private function noCache($response)
    {
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /** Suffixe de nom de fichier reflétant la période et le comparatif. */
    private function fileSuffix(array $ctx): string
    {
        $suffix = Str::slug($ctx['exercice']->intitule);

        if (!empty($ctx['month']) && $ctx['month'] !== 'all' && is_numeric($ctx['month'])) {
            $suffix .= '_' . str_pad((int) $ctx['month'], 2, '0', STR_PAD_LEFT);
        }
        if ($ctx['comparatif'] && $ctx['exerciceN1']) {
            $suffix .= '_vs_' . Str::slug($ctx['exerciceN1']->intitule);
        }

        return $suffix;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  BILAN
    // ═════════════════════════════════════════════════════════════════════

    public function bilan(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $data     = $this->reportingService->getBilanData($exercice->id, $ctx['companyId'], $ctx['month'], $ctx['detail']);

        $exercices = $ctx['exercices'];
        $months    = $ctx['months'];

        return view('reporting.bilan', compact('data', 'exercice', 'exercices', 'months'));
    }

    public function exportBilan(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $month    = $ctx['month'];
        $detail   = $ctx['detail'];
        $detailed = $detail;

        $data   = $this->reportingService->getBilanData($exercice->id, $ctx['companyId'], $month, $detail);
        $dataN1 = $ctx['exerciceN1']
            ? $this->reportingService->getBilanData($ctx['exerciceN1']->id, $ctx['companyId'], $month, $detail)
            : null;
        $exerciceN1 = $ctx['exerciceN1'];

        if ($ctx['format'] === 'pdf') {
            $pdf = \PDF::loadView('reporting.pdf.bilan', compact('data', 'dataN1', 'exercice', 'exerciceN1', 'month', 'detail', 'detailed'));
            return $this->noCache($pdf->download('bilan_' . $this->fileSuffix($ctx) . '.pdf'));
        }

        if ($ctx['format'] === 'excel') {
            $export = new \App\Exports\BilanExport($data, $exercice, $month, $detail, $dataN1, $exerciceN1);
            return $this->noCache(\Excel::download($export, 'bilan_' . $this->fileSuffix($ctx) . '.xlsx'));
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }

    // ═════════════════════════════════════════════════════════════════════
    //  COMPTE DE RÉSULTAT (SIG)
    // ═════════════════════════════════════════════════════════════════════

    public function resultat(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $data     = $this->reportingService->getSIGData($exercice->id, $ctx['companyId'], $ctx['month'], $ctx['detail']);

        $exercices = $ctx['exercices'];
        $months    = $ctx['months'];

        return view('reporting.resultat', compact('data', 'exercice', 'exercices', 'months'));
    }

    public function exportResultat(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $month    = $ctx['month'];
        $detail   = $ctx['detail'];
        $detailed = $detail;

        $data   = $this->reportingService->getSIGData($exercice->id, $ctx['companyId'], $month, $detail);
        $dataN1 = $ctx['exerciceN1']
            ? $this->reportingService->getSIGData($ctx['exerciceN1']->id, $ctx['companyId'], $month, $detail)
            : null;
        $exerciceN1 = $ctx['exerciceN1'];

        if ($ctx['format'] === 'pdf') {
            $pdf = \PDF::loadView('reporting.pdf.resultat', compact('data', 'dataN1', 'exercice', 'exerciceN1', 'month', 'detail', 'detailed'));
            return $this->noCache($pdf->download('resultat_' . $this->fileSuffix($ctx) . '.pdf'));
        }

        if ($ctx['format'] === 'excel') {
            $export = new \App\Exports\ResultatExport($data, $exercice, $month, $detail, $dataN1, $exerciceN1);
            return $this->noCache(\Excel::download($export, 'resultat_' . $this->fileSuffix($ctx) . '.xlsx'));
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }

    // ═════════════════════════════════════════════════════════════════════
    //  TFT (matrice annuelle)
    // ═════════════════════════════════════════════════════════════════════

    public function tft(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $data     = $this->reportingService->getTFTMatrixData($exercice->id, $ctx['companyId'], $ctx['detail']);

        $exercices = $ctx['exercices'];
        $months    = $ctx['months'];

        return view('reporting.tft', compact('data', 'exercice', 'exercices', 'months'));
    }

    public function exportTFT(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice  = $ctx['exercice'];
        $month     = $ctx['month'];
        $detail    = $ctx['detail'];
        $detailed  = $detail;
        $monthFrom = $ctx['monthFrom'];
        $monthTo   = $ctx['monthTo'];

        $data   = $this->reportingService->getTFTMatrixData($exercice->id, $ctx['companyId'], $detail);
        $dataN1 = $ctx['exerciceN1']
            ? $this->reportingService->getTFTMatrixData($ctx['exerciceN1']->id, $ctx['companyId'], $detail)
            : null;
        $exerciceN1 = $ctx['exerciceN1'];

        if ($ctx['format'] === 'pdf') {
            $pdf = \PDF::loadView('reporting.pdf.tft', compact('data', 'dataN1', 'exercice', 'exerciceN1', 'month', 'detail', 'detailed', 'monthFrom', 'monthTo'));
            return $this->noCache($pdf->setPaper('a4', 'landscape')->download('TFT_' . $this->fileSuffix($ctx) . '.pdf'));
        }

        if ($ctx['format'] === 'excel') {
            $export = new \App\Exports\TFTMatrixExport($data, $exercice, $detail, $dataN1, $exerciceN1, $monthFrom, $monthTo);
            return $this->noCache(\Excel::download($export, 'TFT_' . $this->fileSuffix($ctx) . '.xlsx'));
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }

    // ═════════════════════════════════════════════════════════════════════
    //  COMPTE D'EXPLOITATION MENSUEL
    // ═════════════════════════════════════════════════════════════════════

    public function monthlyResultat(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $data     = $this->reportingService->getMonthlyResultatData($exercice->id, $ctx['companyId'], $ctx['detail']);

        $exercices = $ctx['exercices'];
        $months    = $ctx['months'];

        return view('reporting.monthly_resultat', compact('data', 'exercice', 'exercices', 'months'));
    }

    public function exportMonthlyResultat(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice  = $ctx['exercice'];
        $detail    = $ctx['detail'];
        $detailed  = $detail;
        $monthFrom = $ctx['monthFrom'];
        $monthTo   = $ctx['monthTo'];

        $data   = $this->reportingService->getMonthlyResultatData($exercice->id, $ctx['companyId'], $detail);
        $dataN1 = $ctx['exerciceN1']
            ? $this->reportingService->getMonthlyResultatData($ctx['exerciceN1']->id, $ctx['companyId'], $detail)
            : null;
        $exerciceN1 = $ctx['exerciceN1'];

        if ($ctx['format'] === 'pdf') {
            $pdf = \PDF::loadView('reporting.pdf.monthly_resultat', compact('data', 'dataN1', 'exercice', 'exerciceN1', 'detail', 'detailed', 'monthFrom', 'monthTo'));
            return $this->noCache($pdf->setPaper('a4', 'landscape')->download('resultat_mensuel_' . $this->fileSuffix($ctx) . '.pdf'));
        }

        if ($ctx['format'] === 'excel') {
            $export = new \App\Exports\MonthlyResultatExport($data, $exercice, $detail, $dataN1, $exerciceN1, $monthFrom, $monthTo);
            return $this->noCache(\Excel::download($export, 'resultat_mensuel_' . $this->fileSuffix($ctx) . '.xlsx'));
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }

    // ═════════════════════════════════════════════════════════════════════
    //  TFT PERSONNALISÉ (mensuel)
    // ═════════════════════════════════════════════════════════════════════

    public function tftPersonalized(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice = $ctx['exercice'];
        $data     = $this->reportingService->getPersonalizedTFTData($exercice->id, $ctx['companyId'], $ctx['detail']);

        $exercices = $ctx['exercices'];
        $months    = $ctx['months'];

        return view('reporting.tft_personalized', compact('data', 'exercice', 'exercices', 'months'));
    }

    public function exportPersonalizedTFT(Request $request)
    {
        $ctx = $this->reportContext($request);
        if (!$ctx['exercice']) return $this->noExercice();

        $exercice  = $ctx['exercice'];
        $detail    = $ctx['detail'];
        $detailed  = $detail;
        $monthFrom = $ctx['monthFrom'];
        $monthTo   = $ctx['monthTo'];

        $data   = $this->reportingService->getPersonalizedTFTData($exercice->id, $ctx['companyId'], $detail);
        $dataN1 = $ctx['exerciceN1']
            ? $this->reportingService->getPersonalizedTFTData($ctx['exerciceN1']->id, $ctx['companyId'], $detail)
            : null;
        $exerciceN1 = $ctx['exerciceN1'];

        if ($ctx['format'] === 'pdf') {
            $pdf = \PDF::loadView('reporting.pdf.tft_personalized', compact('data', 'dataN1', 'exercice', 'exerciceN1', 'detail', 'detailed', 'monthFrom', 'monthTo'));
            return $this->noCache($pdf->setPaper('a4', 'landscape')->download('TFT_Personnalise_' . $this->fileSuffix($ctx) . '.pdf'));
        }

        if ($ctx['format'] === 'excel') {
            return back()->with('error', 'Export Excel non disponible pour ce format personnalisé actuellement.');
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }
}
