<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ExerciceComptable;
use Illuminate\Support\Facades\Auth;

class LiasseFiscaleController extends Controller
{
    private $pages = [
        1 => ['code' => 'BILAN_ACTIF', 'title' => 'BILAN ACTIF'],
        2 => ['code' => 'BILAN_PASSIF', 'title' => 'BILAN PASSIF'],
        3 => ['code' => 'RESULTAT', 'title' => 'COMPTE DE RÉSULTAT'],
        4 => ['code' => 'TFT', 'title' => 'TABLEAU DES FLUX DE TRÉSORERIE'],
        5 => ['code' => 'NOTE_1', 'title' => 'NOTE 1 : IMMOBILISATIONS BRUTES'],
        6 => ['code' => 'NOTE_2', 'title' => 'NOTE 2 : AMORTISSEMENTS'],
        7 => ['code' => 'NOTE_3', 'title' => 'NOTE 3 : PLUS-VALUES ET MOINS-VALUES'],
        8 => ['code' => 'NOTE_4', 'title' => 'NOTE 4 : PROVISIONS ET DEPRECIATIONS'],
        9 => ['code' => 'NOTE_5', 'title' => 'NOTE 5 : ACTIF IMMOBILISE - ENGAGEMENTS DE CREDIT-BAIL'],
        10 => ['code' => 'NOTE_6', 'title' => 'NOTE 6 : IMMOBILISATIONS FINANCIERES'],
        11 => ['code' => 'NOTE_7', 'title' => 'NOTE 7 : STOCKS'],
        12 => ['code' => 'NOTE_8', 'title' => 'NOTE 8 : CLIENTS ET AUTRES CREANCES'],
        13 => ['code' => 'NOTE_9', 'title' => 'NOTE 9 : TITRES DE PLACEMENT'],
        14 => ['code' => 'NOTE_10', 'title' => 'NOTE 10 : VALEURS A ENCAISSER'],
        15 => ['code' => 'NOTE_11', 'title' => 'NOTE 11 : DISPONIBILITES'],
        16 => ['code' => 'NOTE_12', 'title' => 'NOTE 12 : ECARTS DE CONVERSION'],
        17 => ['code' => 'NOTE_13', 'title' => 'NOTE 13 : CAPITAUX PROPRES'],
        18 => ['code' => 'NOTE_14', 'title' => 'NOTE 14 : SUBVENTIONS D\'INVESTISSEMENT'],
        19 => ['code' => 'NOTE_15', 'title' => 'NOTE 15 : PROVISIONS POUR RISQUES ET CHARGES'],
        20 => ['code' => 'NOTE_16', 'title' => 'NOTE 16 : DETTES FINANCIERES ET RESSOURCES ASSIMILEES'],
        21 => ['code' => 'NOTE_17', 'title' => 'NOTE 17 : FOURNISSEURS ET COMPTES RATTACHES'],
        22 => ['code' => 'NOTE_18', 'title' => 'NOTE 18 : FISCALITE'],
        23 => ['code' => 'NOTE_19', 'title' => 'NOTE 19 : PERSONNEL'],
        24 => ['code' => 'NOTE_20', 'title' => 'NOTE 20 : AUTRES DETTES ET COMPTES DE REGULARISATION'],
        25 => ['code' => 'NOTE_21', 'title' => 'NOTE 21 : CHIFFRE D\'AFFAIRES ET AUTRES PRODUITS'],
        26 => ['code' => 'NOTE_22', 'title' => 'NOTE 22 : ACHATS ET AUTRES CHARGES EXTERNES'],
        27 => ['code' => 'NOTE_23', 'title' => 'NOTE 23 : IMPOTS ET TAXES'],
        28 => ['code' => 'NOTE_24', 'title' => 'NOTE 24 : CHARGES DE PERSONNEL'],
        29 => ['code' => 'NOTE_25', 'title' => 'NOTE 25 : AUTRES CHARGES ET PRODUITS'],
        30 => ['code' => 'NOTE_26', 'title' => 'NOTE 26 : CHARGES ET PRODUITS FINANCIERS'],
        31 => ['code' => 'NOTE_27', 'title' => 'NOTE 27 : CHARGES ET PRODUITS H.A.O.'],
        32 => ['code' => 'NOTE_28', 'title' => 'NOTE 28 : EFFECTIFS, MASSE SALARIALE ET PERSO. EXTERIEUR'],
        33 => ['code' => 'NOTE_29', 'title' => 'NOTE 29 : PRODUCTION DE L\'EXERCICE'],
        34 => ['code' => 'NOTE_30', 'title' => 'NOTE 30 : ACHATS DESTINES A LA PRODUCTION'],
        35 => ['code' => 'NOTE_31', 'title' => 'NOTE 31 : CONSOMMATIONS DE L\'EXERCICE'],
        36 => ['code' => 'NOTE_32', 'title' => 'NOTE 32 : TABLEAU DU RESULTAT FISCAL'],
    ];

    public function index(Request $request)
    {
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
        $pages = $this->pages;

        return view('reporting.liasse.index', compact('pages', 'exercice'));
    }

    public function getPage(Request $request, $page, \App\Services\LiasseFiscaleService $service)
    {
        $pageInfo = $this->pages[$page] ?? null;
        if (!$pageInfo) return response()->json(['error' => 'Page non trouvée'], 404);

        $exerciceId = session('current_exercice_id');
        $data = $service->getPageData($exerciceId, $pageInfo['code']);

        $viewName = 'reporting.liasse.pages.' . strtolower($pageInfo['code']);

        // Fallback si la vue n'existe pas encore
        if (!\Illuminate\Support\Facades\View::exists($viewName)) {
            $html = view('reporting.liasse.pages.en_developpement', [
                'title' => $pageInfo['title'],
                'code' => $pageInfo['code'],
            ])->render();
        } else {
            $html = view($viewName, compact('data'))->render();
        }

        return response()->json([
            'html' => $html,
            'title' => $pageInfo['title'],
            'code' => $pageInfo['code']
        ]);
    }

    public function storeManualData(Request $request, \App\Services\LiasseFiscaleService $service)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');
        $pageCode = $request->input('page_code');
        $data = $request->input('data');

        $service->saveManualData($exerciceId, $companyId, $pageCode, $data);

        return response()->json(['success' => true]);
    }

    public function export($format, \App\Services\LiasseFiscaleService $service)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $exerciceId = session('current_exercice_id');

        if ($format === 'xml') {
            $xmlContent = $service->generateXml($exerciceId, $companyId);
            return response($xmlContent, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="liasse_esintax.xml"',
            ]);
        }

        return response()->json(['message' => 'Format ' . $format . ' non supporté pour le moment.']);
    }
}
