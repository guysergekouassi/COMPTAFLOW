<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Auth;

class LiasseFiscaleController extends Controller
{
    /**
     * Pages du Système Normal (SN) — Liasse complète ~41 pages
     */
    private $pagesSn = [
        1  => ['code' => 'FICHE_R1',    'title' => 'FICHE R1 : IDENTIFICATION'],
        2  => ['code' => 'FICHE_R2',    'title' => 'FICHE R2 : ACTIVITES'],
        3  => ['code' => 'FICHE_R3',    'title' => 'FICHE R3 : DIRIGEANTS'],
        4  => ['code' => 'BALANCE',     'title' => 'BALANCE COMPTABLE'],
        5  => ['code' => 'GRAND_LIVRE', 'title' => 'GRAND LIVRE GEN. (EXTRAIT)'],
        6  => ['code' => 'BILAN_ACTIF', 'title' => 'BILAN ACTIF'],
        7  => ['code' => 'BILAN_PASSIF','title' => 'BILAN PASSIF'],
        8  => ['code' => 'RESULTAT',    'title' => 'COMPTE DE RÉSULTAT'],
        9  => ['code' => 'TFT',         'title' => 'TABLEAU DES FLUX DE TRÉSORERIE'],
        10 => ['code' => 'NOTE_1',      'title' => 'NOTE 1 : IMMOBILISATIONS BRUTES'],
        11 => ['code' => 'NOTE_2',      'title' => 'NOTE 2 : AMORTISSEMENTS'],
        12 => ['code' => 'NOTE_3',      'title' => 'NOTE 3 : PLUS-VALUES ET MOINS-VALUES'],
        13 => ['code' => 'NOTE_4',      'title' => 'NOTE 4 : PROVISIONS ET DEPRECIATIONS'],
        14 => ['code' => 'NOTE_5',      'title' => 'NOTE 5 : ACTIF IMMOBILISE - ENGAGEMENTS DE CREDIT-BAIL'],
        15 => ['code' => 'NOTE_6',      'title' => 'NOTE 6 : IMMOBILISATIONS FINANCIERES'],
        16 => ['code' => 'NOTE_7',      'title' => 'NOTE 7 : STOCKS'],
        17 => ['code' => 'NOTE_8',      'title' => 'NOTE 8 : CLIENTS ET AUTRES CREANCES'],
        18 => ['code' => 'NOTE_9',      'title' => 'NOTE 9 : TITRES DE PLACEMENT'],
        19 => ['code' => 'NOTE_10',     'title' => 'NOTE 10 : VALEURS A ENCAISSER'],
        20 => ['code' => 'NOTE_11',     'title' => 'NOTE 11 : DISPONIBILITES'],
        21 => ['code' => 'NOTE_12',     'title' => 'NOTE 12 : ECARTS DE CONVERSION'],
        22 => ['code' => 'NOTE_13',     'title' => 'NOTE 13 : CAPITAUX PROPRES'],
        23 => ['code' => 'NOTE_14',     'title' => 'NOTE 14 : SUBVENTIONS D\'INVESTISSEMENT'],
        24 => ['code' => 'NOTE_15',     'title' => 'NOTE 15 : PROVISIONS POUR RISQUES ET CHARGES'],
        25 => ['code' => 'NOTE_16',     'title' => 'NOTE 16 : DETTES FINANCIERES ET RESSOURCES ASSIMILEES'],
        26 => ['code' => 'NOTE_17',     'title' => 'NOTE 17 : FOURNISSEURS ET COMPTES RATTACHES'],
        27 => ['code' => 'NOTE_18',     'title' => 'NOTE 18 : FISCALITE'],
        28 => ['code' => 'NOTE_19',     'title' => 'NOTE 19 : PERSONNEL'],
        29 => ['code' => 'NOTE_20',     'title' => 'NOTE 20 : AUTRES DETTES ET COMPTES DE REGULARISATION'],
        30 => ['code' => 'NOTE_21',     'title' => 'NOTE 21 : CHIFFRE D\'AFFAIRES ET AUTRES PRODUITS'],
        31 => ['code' => 'NOTE_22',     'title' => 'NOTE 22 : ACHATS ET AUTRES CHARGES EXTERNES'],
        32 => ['code' => 'NOTE_23',     'title' => 'NOTE 23 : IMPOTS ET TAXES'],
        33 => ['code' => 'NOTE_24',     'title' => 'NOTE 24 : CHARGES DE PERSONNEL'],
        34 => ['code' => 'NOTE_25',     'title' => 'NOTE 25 : AUTRES CHARGES ET PRODUITS'],
        35 => ['code' => 'NOTE_26',     'title' => 'NOTE 26 : CHARGES ET PRODUITS FINANCIERS'],
        36 => ['code' => 'NOTE_27',     'title' => 'NOTE 27 : CHARGES ET PRODUITS H.A.O.'],
        37 => ['code' => 'NOTE_28',     'title' => 'NOTE 28 : EFFECTIFS, MASSE SALARIALE ET PERSO. EXTERIEUR'],
        38 => ['code' => 'NOTE_29',     'title' => 'NOTE 29 : PRODUCTION DE L\'EXERCICE'],
        39 => ['code' => 'NOTE_30',     'title' => 'NOTE 30 : ACHATS DESTINES A LA PRODUCTION'],
        40 => ['code' => 'NOTE_31',     'title' => 'NOTE 31 : CONSOMMATIONS DE L\'EXERCICE'],
        41 => ['code' => 'NOTE_32',     'title' => 'NOTE 32 : TABLEAU DU RESULTAT FISCAL'],
    ];

    /**
     * Pages du Système Minimal de Trésorerie (SMT) — 14 pages conformes DGI-liasse-MT
     * CA ≤ 50 M FCFA — type XML : MT
     * Structure basée sur les vrais fichiers MT exportés de e-impôts.ci
     */
    private $pagesSmt = [
        1 => ['code' => 'BALANCE',          'title' => 'BALANCE'],
        2 => ['code' => 'GRAND_LIVRE',      'title' => 'GRAND LIVRE'],
        3 => ['code' => 'SMT_FICHE_IDENT',  'title' => '1. Fiche d\'Identification'],
        4 => ['code' => 'SMT_ACTIF',        'title' => '2. Bilan - Actif'],
        5 => ['code' => 'SMT_PASSIF',       'title' => '3. Bilan - Passif'],
        6 => ['code' => 'SMT_RESULTAT',     'title' => '4. Compte de Résultat'],
        7 => ['code' => 'SMT_TRESO_ENC',    'title' => '5. Trésorerie - Encaissements'],
        8 => ['code' => 'SMT_TRESO_DEC',    'title' => '6. Trésorerie - Décaissements'],
        9 => ['code' => 'SMT_NOTES',        'title' => '7. Notes Annexes'],
        10 => ['code' => 'SMT_FISCAL',      'title' => '8. Passage au Résultat Fiscal'],
    ];

    /**
     * Retourne le tableau de pages selon le régime
     */
    private function getPages(string $regime): array
    {
        return $regime === 'smt' ? $this->pagesSmt : $this->pagesSn;
    }

    public function index(Request $request, string $regime = 'sn')
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id') ?? session('exercice_actif_id');

        if (!$exerciceId) {
            return redirect()->route('exercice_comptable')->with('error', 'Veuillez sélectionner un exercice actif.');
        }

        $exercice = ExerciceComptable::find($exerciceId);
        $pages    = $this->getPages($regime);

        return view('reporting.liasse.index', compact('pages', 'exercice', 'regime'));
    }

    public function getPage(Request $request, string $regime, int $page, \App\Services\LiasseFiscaleService $service)
    {
        $pages    = $this->getPages($regime);
        $pageInfo = $pages[$page] ?? null;

        if (!$pageInfo) return response()->json(['error' => 'Page non trouvée'], 404);

        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Non authentifié'], 401);

        $companyId  = session('current_company_id', $user->company_id);
        $exerciceId = session('exercice_actif_id', session('current_exercice_id'));

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', true)->first()
                ?? ExerciceComptable::where('company_id', $companyId)->orderBy('date_debut', 'desc')->first();
            $exerciceId = $activeExercice?->id;
        }

        if (!$exerciceId) return response()->json(['error' => 'Aucun exercice trouvé'], 404);

        $exercice = ExerciceComptable::find($exerciceId);
        $company  = \App\Models\Company::find($companyId);

        // Universal data fetching through getPageData (which delegates to SMT if needed)
        $data = $service->getPageData($exerciceId, $pageInfo['code']);

        // Build view name
        $viewSlug = strtolower($pageInfo['code']);
        $viewName = 'reporting.liasse.pages.' . $viewSlug;

        if (!\Illuminate\Support\Facades\View::exists($viewName)) {
            $html = view('reporting.liasse.pages.en_developpement', [
                'title' => $pageInfo['title'],
                'code'  => $pageInfo['code'],
            ])->render();
        } else {
            // Pass all necessary objects to the view
            $html = view($viewName, compact('data', 'company', 'exercice'))->render();
        }

        $summary = $service->getSummaryData($exerciceId, $companyId);

        return response()->json([
            'html'    => $html,
            'title'   => $pageInfo['title'],
            'code'    => $pageInfo['code'],
            'summary' => $summary,
        ]);
    }

    public function storeManualData(Request $request, \App\Services\LiasseFiscaleService $service)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Non authentifié'], 401);

        $companyId  = session('current_company_id', $user->company_id);
        $exerciceId = session('exercice_actif_id', session('current_exercice_id'));

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)->where('is_active', true)->first();
            $exerciceId = $activeExercice?->id;
        }

        $service->saveManualData($exerciceId, $companyId, $request->input('page_code'), $request->input('data'));
        return response()->json(['success' => true]);
    }

    public function export(Request $request, string $regime, string $format, \App\Services\LiasseFiscaleService $service)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Non authentifié'], 401);

        $companyId  = session('current_company_id', $user->company_id);
        $exerciceId = session('exercice_actif_id', session('current_exercice_id'));

        if (!$exerciceId) {
            $activeExercice = ExerciceComptable::where('company_id', $companyId)->where('is_active', true)->first();
            $exerciceId = $activeExercice?->id;
        }

        $pageParam = $request->query('page');
        $pages     = $this->getPages($regime);
        $pageCode  = is_numeric($pageParam) && isset($pages[$pageParam]) ? $pages[$pageParam]['code'] : $pageParam;

        if ($format === 'xml') {
            $xmlContent = $service->generateXml($exerciceId, $companyId, $regime);
            $type = $regime === 'smt' ? 'RNI' : 'NO';
            return response($xmlContent, 200, [
                'Content-Type'        => 'application/xml',
                'Content-Disposition' => 'attachment; filename="liasse_' . $type . '_' . date('Ymd') . '.xml"',
            ]);
        }

        if ($format === 'pdf') {
            $pdfContent = $service->generatePdf($exerciceId, $companyId, $pageCode, $pages);
            $filename   = $pageCode ? 'liasse_' . strtolower($pageCode) . '_' . date('Ymd') . '.pdf'
                                    : 'liasse_' . $regime . '_complete_' . date('Ymd') . '.pdf';
            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($format === 'excel') {
            return $service->generateExcel($exerciceId, $companyId, $pageCode, $pages);
        }

        return response()->json(['message' => 'Format ' . $format . ' non supporté.']);
    }
}
