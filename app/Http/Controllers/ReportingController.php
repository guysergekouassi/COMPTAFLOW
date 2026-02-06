<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountingReportingService;
use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Auth;

class ReportingController extends Controller
{
    protected $reportingService;

    public function __construct(AccountingReportingService $reportingService)
    {
        $this->middleware('auth');
        $this->reportingService = $reportingService;
    }

    public function bilan(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');
        $month = $request->input('month');
        $detail = $request->input('detail') == '1';

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getBilanData($exerciceId, $companyId, $month, $detail);

        return view('reporting.bilan', compact('data', 'exercice'));
    }

    public function resultat(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');
        $month = $request->input('month');
        $detail = $request->input('detail') == '1';

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getSIGData($exerciceId, $companyId, $month, $detail);

        return view('reporting.resultat', compact('data', 'exercice'));
    }

    public function tft(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');
        $month = $request->input('month');
        $detail = $request->input('detail') == '1';

        if (!$exerciceId) {
            $activeExercice = \App\Models\ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = \App\Models\ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getTFTMatrixData($exerciceId, $companyId, $detail);

        return view('reporting.tft', compact('data', 'exercice'));
    }

    public function exportBilan(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $month = $request->query('month');
        $detail = $request->query('detail') == '1';
        
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getBilanData($exerciceId, $companyId, $month, $detail);

        if ($format === 'pdf') {
            // Fix: Map $detail to $detailed as expected by the view
            $detailed = $detail;
            $pdf = \PDF::loadView('reporting.pdf.bilan', compact('data', 'exercice', 'month', 'detail', 'detailed'));
            return $pdf->download('bilan_' . $exercice->intitule . '.pdf');
        } elseif ($format === 'excel') {
            return \Excel::download(new \App\Exports\BilanExport($data, $exercice, $month, $detail), 'bilan_' . $exercice->intitule . '.xlsx');
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }

    public function exportResultat(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $month = $request->query('month');
        $detail = $request->query('detail') == '1';

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getSIGData($exerciceId, $companyId, $month, $detail);

        if ($format === 'pdf') {
            // Fix: Map $detail to $detailed as expected by the view
            $detailed = $detail;
            $pdf = \PDF::loadView('reporting.pdf.resultat', compact('data', 'exercice', 'month', 'detail', 'detailed'));
            return $pdf->download('resultat_' . $exercice->intitule . '.pdf');
        } elseif ($format === 'excel') {
            return \Excel::download(new \App\Exports\ResultatExport($data, $exercice, $month, $detail), 'resultat_' . $exercice->intitule . '.xlsx');
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }

    public function exportTFT(Request $request)
    {
        $month = $request->query('month');
        $detail = $request->query('detail') == '1';

        $user = \Illuminate\Support\Facades\Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $activeExercice = \App\Models\ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = \App\Models\ExerciceComptable::find($exerciceId);
        $format = $request->query('format', 'pdf');
        $data = $this->reportingService->getTFTMatrixData($exerciceId, $companyId, $detail);

        if ($format === 'pdf') {
            // Fix: Map $detail to $detailed as expected by the view
            $detailed = $detail;
            $pdf = \PDF::loadView('reporting.pdf.tft', compact('data', 'exercice', 'month', 'detail', 'detailed'));
            return $pdf->setPaper('a4', 'landscape')->download('TFT_' . $exercice->intitule . '.pdf');
        } elseif ($format === 'excel') {
            return \Excel::download(new \App\Exports\TFTMatrixExport($data, $exercice, $detail), 'TFT_' . $exercice->intitule . '.xlsx');
        }
        
        return back()->with('error', 'Format d\'exportation non supporté.');
    }
    public function monthlyResultat(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');
        $detail = $request->query('detail') == '1';

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getMonthlyResultatData($exerciceId, $companyId, $detail);

        return view('reporting.monthly_resultat', compact('data', 'exercice'));
    }

    public function exportMonthlyResultat(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $detail = $request->query('detail') == '1';

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            $exerciceId = $activeExercice ? $activeExercice->id : null;
        }

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $data = $this->reportingService->getMonthlyResultatData($exerciceId, $companyId, $detail);

        if ($format === 'pdf') {
            $detailed = $detail;
            $pdf = \PDF::loadView('reporting.pdf.monthly_resultat', compact('data', 'exercice', 'detail', 'detailed'));
            return $pdf->setPaper('a4', 'landscape')->download('resultat_mensuel_' . $exercice->intitule . '.pdf');
        } elseif ($format === 'excel') {
            return \Excel::download(new \App\Exports\MonthlyResultatExport($data, $exercice, $detail), 'resultat_mensuel_' . $exercice->intitule . '.xlsx');
        }

        return back()->with('error', 'Format d\'exportation non supporté.');
    }
}
