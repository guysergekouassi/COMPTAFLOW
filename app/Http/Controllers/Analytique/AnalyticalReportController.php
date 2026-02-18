<?php

namespace App\Http\Controllers\Analytique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AxeAnalytique;
use App\Models\SectionAnalytique;
use App\Models\ExerciceComptable;
use App\Services\Analytique\AnalyticalReportingService;
use Illuminate\Support\Facades\Auth;

class AnalyticalReportController extends Controller
{
    protected $reportingService;

    public function __construct(AnalyticalReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Analytical Balance Report.
     */
    public function balance(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $exerciceId = $request->get('exercice_id') ?? session('current_exercice_id');
        if (!$exerciceId) {
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first();
            $exerciceId = $exerciceActif?->id;
        }

        $axes = AxeAnalytique::where('company_id', $companyId)->get();
        $selectedAxeId = $request->get('axe_id', $axes->first()?->id);

        $results = collect([]);
        if ($selectedAxeId) {
            $results = $this->reportingService->getBalanceData($companyId, $selectedAxeId, $exerciceId, $request->all());
        }

        return view('analytique.reports.balance', [
            'axes' => $axes,
            'selectedAxeId' => $selectedAxeId,
            'results' => $results,
            'exerciceActif' => ExerciceComptable::find($exerciceId),
            'exercices' => ExerciceComptable::where('company_id', $companyId)->orderBy('date_debut', 'desc')->get(),
            'data' => $request->all()
        ]);
    }

    /**
     * Analytical Grand Livre Report.
     */
    public function grandLivre(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $exerciceId = $request->get('exercice_id') ?? session('current_exercice_id');
        if (!$exerciceId) {
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first();
            $exerciceId = $exerciceActif?->id;
        }

        $axes = AxeAnalytique::where('company_id', $companyId)->get();
        $selectedAxeId = $request->get('axe_id', $axes->first()?->id);
        
        $sections = [];
        if ($selectedAxeId) {
            $sections = SectionAnalytique::where('axe_id', $selectedAxeId)->get();
        }
        
        $selectedSectionId = $request->get('section_id');

        $results = collect([]);
        if ($selectedSectionId) {
            $results = $this->reportingService->getGrandLivreData($companyId, $selectedSectionId, $exerciceId, $request->all());
        }

        return view('analytique.reports.grand_livre', [
            'axes' => $axes,
            'selectedAxeId' => $selectedAxeId,
            'sections' => $sections,
            'selectedSectionId' => $selectedSectionId,
            'results' => $results,
            'exerciceActif' => ExerciceComptable::find($exerciceId),
            'exercices' => ExerciceComptable::where('company_id', $companyId)->orderBy('date_debut', 'desc')->get(),
            'data' => $request->all()
        ]);
    }

    /**
     * Analytical Result Report (Charges vs Products).
     */
    public function resultat(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $exerciceId = $request->get('exercice_id') ?? session('current_exercice_id');
        if (!$exerciceId) {
            $exerciceActif = ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first();
            $exerciceId = $exerciceActif?->id;
        }

        $axes = AxeAnalytique::where('company_id', $companyId)->get();
        $selectedAxeId = $request->get('axe_id', $axes->first()?->id);

        $results = collect([]);
        if ($selectedAxeId) {
            $results = $this->reportingService->getResultData($companyId, $selectedAxeId, $exerciceId, $request->all());
        }

        return view('analytique.reports.resultat', [
            'axes' => $axes,
            'selectedAxeId' => $selectedAxeId,
            'results' => $results,
            'exerciceActif' => ExerciceComptable::find($exerciceId),
            'exercices' => ExerciceComptable::where('company_id', $companyId)->orderBy('date_debut', 'desc')->get(),
            'data' => $request->all()
        ]);
    }
}
