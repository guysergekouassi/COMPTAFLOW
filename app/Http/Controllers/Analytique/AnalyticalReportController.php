<?php

namespace App\Http\Controllers\Analytique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AxeAnalytique;
use App\Models\SectionAnalytique;
use App\Models\ExerciceComptable;
use App\Models\RapportGrandLivreAnalytique;
use App\Models\RapportBalanceAnalytique;
use App\Services\Analytique\AnalyticalReportingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\Analytique\AnalyticalBalanceExport;
use App\Exports\Analytique\AnalyticalGrandLivreExport;
use App\Exports\Analytique\AnalyticalResultatExport;

class AnalyticalReportController extends Controller
{
    protected $reportingService;

    public function __construct(AnalyticalReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    // =========================================================================
    //  BALANCE ANALYTIQUE
    // =========================================================================

    /**
     * Page principale — liste des rapports Balance Analytique générés.
     */
    public function balance(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exerciceId  = session('current_exercice_id');
        $exerciceActif = $exerciceId
            ? ExerciceComptable::find($exerciceId)
            : ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first();
        $exerciceActif = $exerciceActif
            ?? ExerciceComptable::where('company_id', $companyId)->where('cloturer', 0)->orderByDesc('date_debut')->first();

        $axes = AxeAnalytique::where('company_id', $companyId)->get();

        $rapports = RapportBalanceAnalytique::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get();

        return view('analytique.reports.balance', [
            'axes'          => $axes,
            'rapports'      => $rapports,
            'exerciceActif' => $exerciceActif,
        ]);
    }

    /**
     * Génère et stocke un rapport Balance Analytique (PDF ou Excel).
     */
    public function generateBalance(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $user      = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $exerciceId = session('current_exercice_id')
                ?? ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first()?->id;

            $tousAxes = (bool) $request->input('tous_axes', false);
            $axeId  = $tousAxes ? null : $request->input('axe_id');
            $sectionId = $request->input('section_id');
            $toutesSections = (bool) $request->input('toutes_sections', false);
            $format = $request->input('format', 'pdf');
            $toutePeriode = (bool) $request->input('toute_periode', false);

            $dateDebut = $toutePeriode ? null : $request->input('date_debut');
            $dateFin   = $toutePeriode ? null : $request->input('date_fin');

            $axe     = $tousAxes ? null : AxeAnalytique::find($axeId);
            $results = $this->reportingService->getBalanceData(
                $companyId, $tousAxes ? null : $axeId, $exerciceId,
                [
                    'date_debut' => $dateDebut,
                    'date_fin'   => $dateFin,
                ]
            );

            // Filtrer par section spécifique si non-toutesSections
            if (!$toutesSections && $sectionId) {
                $results = $results->filter(fn($r) => $r->section_id == $sectionId);
            }

            // ── Stockage ────────────────────────────────────────────────────
            $dir = public_path('rapports_analytiques');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $filename = 'balance_analytique_' . now()->format('YmdHis') . '.' . ($format === 'excel' ? 'xlsx' : 'pdf');

            if ($format === 'excel') {
                Excel::store(new AnalyticalBalanceExport($results, $tousAxes ? 'Tous les axes' : ($axe?->libelle)), $filename, 'rapports_analytiques');
            } else {
                $exercice = ExerciceComptable::find($exerciceId);
                $company  = \App\Models\Company::find($companyId);
                $pdf = Pdf::loadView('analytique.reports.pdf.balance', compact('results', 'axe', 'exercice', 'company'))
                    ->setPaper('A4', 'landscape');
                $pdf->save($dir . '/' . $filename);
            }

            RapportBalanceAnalytique::create([
                'company_id'        => $companyId,
                'user_id'           => $user->id,
                'axe_analytique_id' => $tousAxes ? null : $axeId,
                'tous_axes'         => $tousAxes,
                'axe_libelle'       => $tousAxes ? 'Tous les axes' : ($axe?->libelle),
                'section_id'        => $toutesSections ? null : $sectionId,
                'section_libelle'   => $toutesSections ? 'Toutes les sections' : (SectionAnalytique::find($sectionId)?->code . ' - ' . SectionAnalytique::find($sectionId)?->libelle),
                'toutes_sections'   => $toutesSections,
                'date_debut'        => $dateDebut,
                'date_fin'          => $dateFin,
                'toute_periode'     => $toutePeriode,
                'format'            => $format,
                'fichier'           => $filename,
            ]);

            return redirect()->route('analytique.balance')
                ->with('success', 'Balance Analytique générée avec succès !');

        } catch (\Exception $e) {
            Log::error('Balance Analytique generate: ' . $e->getMessage());
            return redirect()->route('analytique.balance')
                ->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualise un rapport Balance Analytique.
     */
    public function previewBalance(Request $request)
    {
        try {
            $user      = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $exerciceId = session('current_exercice_id')
                ?? ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first()?->id;

            $tousAxes = (bool) $request->input('tous_axes', false);
            $axeId  = $tousAxes ? null : $request->input('axe_id');
            $sectionId = $request->input('section_id');
            $toutesSections = (bool) $request->input('toutes_sections', false);
            $toutePeriode = (bool) $request->input('toute_periode', false);

            $dateDebut = $toutePeriode ? null : $request->input('date_debut');
            $dateFin   = $toutePeriode ? null : $request->input('date_fin');

            $axe     = $tousAxes ? null : AxeAnalytique::find($axeId);
            $results = $this->reportingService->getBalanceData(
                $companyId, $tousAxes ? null : $axeId, $exerciceId,
                [
                    'date_debut' => $dateDebut,
                    'date_fin'   => $dateFin,
                ]
            );

            if (!$toutesSections && $sectionId) {
                $results = $results->filter(fn($r) => $r->section_id == $sectionId);
            }

            $exercice = ExerciceComptable::find($exerciceId);
            $company  = \App\Models\Company::find($companyId);
            $pdf = Pdf::loadView('analytique.reports.pdf.balance', compact('results', 'axe', 'exercice', 'company'))
                ->setPaper('A4', 'landscape');

            $fileName = 'preview_balance_' . time() . '.pdf';
            $filePath = public_path('previews/' . $fileName);
            if (!file_exists(public_path('previews'))) {
                mkdir(public_path('previews'), 0777, true);
            }
            $pdf->save($filePath);

            return response()->json(['success' => true, 'url' => asset('previews/' . $fileName)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime un rapport Balance Analytique.
     */
    public function destroyBalance($id)
    {
        try {
            $rapport = RapportBalanceAnalytique::findOrFail($id);
            $path    = public_path('rapports_analytiques/' . $rapport->fichier);
            if (File::exists($path)) {
                File::delete($path);
            }
            $rapport->delete();
            return redirect()->route('analytique.balance')->with('success', 'Rapport supprimé.');
        } catch (\Exception $e) {
            Log::error('Balance destroy: ' . $e->getMessage());
            return redirect()->route('analytique.balance')->with('error', 'Erreur lors de la suppression.');
        }
    }

    // =========================================================================
    //  GRAND LIVRE ANALYTIQUE
    // =========================================================================

    /**
     * Page principale — liste des rapports Grand Livre Analytique générés.
     */
    public function grandLivre(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exerciceId  = session('current_exercice_id');
        $exerciceActif = $exerciceId
            ? ExerciceComptable::find($exerciceId)
            : ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first();
        $exerciceActif = $exerciceActif
            ?? ExerciceComptable::where('company_id', $companyId)->where('cloturer', 0)->orderByDesc('date_debut')->first();

        $axes     = AxeAnalytique::where('company_id', $companyId)->get();
        $sections = SectionAnalytique::whereIn('axe_id', $axes->pluck('id'))->orderBy('code')->get();

        $rapports = RapportGrandLivreAnalytique::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get();

        return view('analytique.reports.grand_livre', [
            'axes'          => $axes,
            'sections'      => $sections,
            'rapports'      => $rapports,
            'exerciceActif' => $exerciceActif,
        ]);
    }

    /**
     * Génère et stocke un rapport Grand Livre Analytique (PDF ou Excel).
     */
    public function generateGrandLivre(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $user      = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $exerciceId = session('current_exercice_id')
                ?? ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first()?->id;

            $tousAxes = (bool) $request->input('tous_axes', false);
            $axeId  = $tousAxes ? null : $request->input('axe_id');
            $sectionDeId  = $request->input('section_de_id'); // null = depuis début
            $sectionAId   = $request->input('section_a_id');  // null = jusqu'à fin
            $toutesSections = (bool) $request->input('toutes_sections', false);
            $format       = $request->input('format', 'pdf');
            $toutePeriode = (bool) $request->input('toute_periode', false);

            $dateDebut = $toutePeriode ? null : $request->input('date_debut');
            $dateFin   = $toutePeriode ? null : $request->input('date_fin');

            $axe       = $tousAxes ? null : AxeAnalytique::find($axeId);
            $sectionDe = $toutesSections ? null : SectionAnalytique::find($sectionDeId);
            $sectionA  = $toutesSections ? null : SectionAnalytique::find($sectionAId);

            // Résolution des sections à inclure
            if ($toutesSections) {
                $selectedSectionId = 'all';
            } elseif ($sectionDeId && $sectionAId) {
                $selectedSectionId = 'all'; // on filtre plus bas via les données
            } else {
                $selectedSectionId = $sectionDeId ?? 'all';
            }

            $results = $this->reportingService->getGrandLivreData(
                $companyId,
                $selectedSectionId,
                $exerciceId,
                [
                    'axe_id'     => $tousAxes ? null : $axeId,
                    'date_debut' => $dateDebut,
                    'date_fin'   => $dateFin,
                ]
            );

            // Filtre plage De → A si non-null
            if (!$toutesSections && $sectionDeId && $sectionAId) {
                $codeDe = SectionAnalytique::find($sectionDeId)?->code;
                $codeA  = SectionAnalytique::find($sectionAId)?->code;
                if ($codeDe && $codeA) {
                    $results = $results->filter(function($r) use ($codeDe, $codeA) {
                        $c = $r->section_code ?? '';
                        return strcmp($c, $codeDe) >= 0 && strcmp($c, $codeA) <= 0;
                    });
                }
            }

            $nbMouvements = $results->count();

            // ── Stockage ────────────────────────────────────────────────────
            $dir = public_path('rapports_analytiques');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $filename = 'gl_analytique_' . now()->format('YmdHis') . '.' . ($format === 'excel' ? 'xlsx' : 'pdf');

            if ($format === 'excel') {
                $label = $toutesSections ? 'Toutes sections' : (($sectionDe?->libelle ?? '') . ' → ' . ($sectionA?->libelle ?? ''));
                Excel::store(new AnalyticalGrandLivreExport($results, $label), $filename, 'rapports_analytiques');
            } else {
                $exercice = ExerciceComptable::find($exerciceId);
                $company  = \App\Models\Company::find($companyId);
                $section  = $toutesSections ? null : $sectionDe;
                $pdf = Pdf::loadView('analytique.reports.pdf.grand_livre', compact('results', 'section', 'exercice', 'company'))
                    ->setPaper('A4', 'landscape');
                $pdf->save($dir . '/' . $filename);
            }

            RapportGrandLivreAnalytique::create([
                'company_id'          => $companyId,
                'user_id'             => $user->id,
                'axe_analytique_id'   => $tousAxes ? null : $axeId,
                'tous_axes'           => $tousAxes,
                'section_de_id'       => $toutesSections ? null : $sectionDeId,
                'section_a_id'        => $toutesSections ? null : $sectionAId,
                'axe_libelle'         => $tousAxes ? 'Tous les axes' : ($axe?->libelle),
                'section_de_libelle'  => $toutesSections ? null : ($sectionDe?->code . ' - ' . $sectionDe?->libelle),
                'section_a_libelle'   => $toutesSections ? null : ($sectionA?->code . ' - ' . $sectionA?->libelle),
                'toutes_sections'     => $toutesSections,
                'date_debut'          => $dateDebut,
                'date_fin'            => $dateFin,
                'toute_periode'       => $toutePeriode,
                'format'              => $format,
                'fichier'             => $filename,
                'nb_mouvements'       => $nbMouvements,
            ]);

            return redirect()->route('analytique.grand_livre')
                ->with('success', "Grand Livre Analytique généré ({$nbMouvements} mouvements).");

        } catch (\Exception $e) {
            Log::error('GL Analytique generate: ' . $e->getMessage());
            return redirect()->route('analytique.grand_livre')
                ->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualise un rapport Grand Livre Analytique.
     */
    public function previewGrandLivre(Request $request)
    {
        try {
            $user      = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $exerciceId = session('current_exercice_id')
                ?? ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first()?->id;

            $tousAxes = (bool) $request->input('tous_axes', false);
            $axeId  = $tousAxes ? null : $request->input('axe_id');
            $sectionDeId  = $request->input('section_de_id'); // null = depuis début
            $sectionAId   = $request->input('section_a_id');  // null = jusqu'à fin
            $toutesSections = (bool) $request->input('toutes_sections', false);
            $toutePeriode = (bool) $request->input('toute_periode', false);

            $dateDebut = $toutePeriode ? null : $request->input('date_debut');
            $dateFin   = $toutePeriode ? null : $request->input('date_fin');

            if ($toutesSections) {
                $selectedSectionId = 'all';
            } elseif ($sectionDeId && $sectionAId) {
                $selectedSectionId = 'all';
            } else {
                $selectedSectionId = $sectionDeId ?? 'all';
            }

            $results = $this->reportingService->getGrandLivreData(
                $companyId,
                $selectedSectionId,
                $exerciceId,
                [
                    'axe_id'     => $tousAxes ? null : $axeId,
                    'date_debut' => $dateDebut,
                    'date_fin'   => $dateFin,
                ]
            );

            if (!$toutesSections && $sectionDeId && $sectionAId) {
                $codeDe = SectionAnalytique::find($sectionDeId)?->code;
                $codeA  = SectionAnalytique::find($sectionAId)?->code;
                if ($codeDe && $codeA) {
                    $results = $results->filter(function($r) use ($codeDe, $codeA) {
                        $c = $r->section_code ?? '';
                        return strcmp($c, $codeDe) >= 0 && strcmp($c, $codeA) <= 0;
                    });
                }
            }

            $exercice = ExerciceComptable::find($exerciceId);
            $company  = \App\Models\Company::find($companyId);
            $section  = $toutesSections ? null : SectionAnalytique::find($sectionDeId);

            $pdf = Pdf::loadView('analytique.reports.pdf.grand_livre', compact('results', 'section', 'exercice', 'company'))
                ->setPaper('A4', 'landscape');

            $fileName = 'preview_gl_' . time() . '.pdf';
            $filePath = public_path('previews/' . $fileName);
            if (!file_exists(public_path('previews'))) {
                mkdir(public_path('previews'), 0777, true);
            }
            $pdf->save($filePath);

            return response()->json(['success' => true, 'url' => asset('previews/' . $fileName)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime un rapport Grand Livre Analytique.
     */
    public function destroyGrandLivre($id)
    {
        try {
            $rapport = RapportGrandLivreAnalytique::findOrFail($id);
            $path    = public_path('rapports_analytiques/' . $rapport->fichier);
            if (File::exists($path)) {
                File::delete($path);
            }
            $rapport->delete();
            return redirect()->route('analytique.grand_livre')->with('success', 'Rapport supprimé.');
        } catch (\Exception $e) {
            Log::error('GL destroy: ' . $e->getMessage());
            return redirect()->route('analytique.grand_livre')->with('error', 'Erreur lors de la suppression.');
        }
    }

    // =========================================================================
    //  RÉSULTAT ANALYTIQUE
    // =========================================================================

    /**
     * Analytical Result Report (Charges vs Products).
     */
    public function resultat(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exerciceId  = session('current_exercice_id');
        $exerciceActif = $exerciceId
            ? ExerciceComptable::find($exerciceId)
            : ExerciceComptable::where('company_id', $companyId)->where('is_active', 1)->first();
        $exerciceId = $exerciceActif?->id;

        $axes          = AxeAnalytique::where('company_id', $companyId)->get();
        $selectedAxeId = $request->get('axe_id', $axes->first()?->id);

        $results = collect([]);
        if ($selectedAxeId) {
            $results = $this->reportingService->getResultData($companyId, $selectedAxeId, $exerciceId, $request->all());
        }

        return view('analytique.reports.resultat', [
            'axes'          => $axes,
            'selectedAxeId' => $selectedAxeId,
            'results'       => $results,
            'exerciceActif' => $exerciceActif,
            'data'          => $request->all(),
        ]);
    }

    public function exportResultatExcel(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = $request->get('exercice_id') ?? session('current_exercice_id');
        $axes       = AxeAnalytique::where('company_id', $companyId)->get();
        $selectedAxeId = $request->get('axe_id', $axes->first()?->id);

        $results = $this->reportingService->getResultData($companyId, $selectedAxeId, $exerciceId, $request->all());
        $axe     = AxeAnalytique::find($selectedAxeId);

        return Excel::download(new AnalyticalResultatExport($results, $axe?->libelle), 'resultat_analytique_' . date('Ymd') . '.xlsx');
    }

    public function exportResultatPdf(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = $request->get('exercice_id') ?? session('current_exercice_id');
        $axes       = AxeAnalytique::where('company_id', $companyId)->get();
        $selectedAxeId = $request->get('axe_id', $axes->first()?->id);

        $results = $this->reportingService->getResultData($companyId, $selectedAxeId, $exerciceId, $request->all());
        $axe     = AxeAnalytique::find($selectedAxeId);

        $pdf = Pdf::loadView('analytique.reports.pdf.resultat', [
            'results'  => $results,
            'axe'      => $axe,
            'exercice' => ExerciceComptable::find($exerciceId),
            'company'  => \App\Models\Company::find($companyId),
        ]);

        return $pdf->download('resultat_analytique_' . date('Ymd') . '.pdf');
    }
}
