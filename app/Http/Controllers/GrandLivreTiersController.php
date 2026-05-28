<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use Illuminate\Support\Facades\Auth;
use App\Models\EcritureComptable;
use App\Models\GrandLivreTiers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Exports\GrandLivreTiersExport;
use App\Models\ExerciceComptable;

class GrandLivreTiersController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $PlanTiers = PlanTiers::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderByRaw('LEFT(numero_de_tiers, 1) ASC')
            ->orderBy('numero_de_tiers')
            ->get();

        // Récupérer l'exercice en cours
        $contextExerciceId = session('current_exercice_id');
        $exerciceEnCours   = null;
        if ($contextExerciceId) {
            $exerciceEnCours = ExerciceComptable::where('id', $contextExerciceId)
                ->where('company_id', $companyId)->first();
        }
        if (!$exerciceEnCours) {
            $exerciceEnCours = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', 1)->first();
        }
        if (!$exerciceEnCours) {
            $exerciceEnCours = ExerciceComptable::where('company_id', $companyId)
                ->where('cloturer', 0)->orderBy('date_debut', 'desc')->first();
        }

        $grandLivre = $exerciceEnCours
            ? GrandLivreTiers::where('company_id', $companyId)
                ->where('date_debut', '>=', $exerciceEnCours->date_debut)
                ->where('date_fin', '<=', $exerciceEnCours->date_fin)
                ->orderByDesc('created_at')->get()
            : GrandLivreTiers::where('company_id', $companyId)
                ->orderByDesc('created_at')->get();

        return view('accounting_ledger_tiers', compact('PlanTiers', 'grandLivre', 'exerciceEnCours'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Génération Grand Livre Tiers (PDF / Excel / CSV)
    // ─────────────────────────────────────────────────────────────────────────
    public function generateGrandLivre(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'date_debut'      => 'required|date',
                'date_fin'        => 'required|date|after_or_equal:date_debut',
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
                'format_fichier'  => 'nullable|in:pdf,excel,csv',
                'display_mode'    => 'nullable|in:origine,comptaflow,both',
            ]);

            $user        = Auth::user();
            $companyId   = session('current_company_id', $user->company_id);
            $format      = $request->format_fichier ?? 'pdf';
            $displayMode = $request->display_mode   ?? 'comptaflow';
            $exerciceId  = session('current_exercice_id');

            [$compte1, $compte2, $min, $max] = $this->resolveTiersRange($companyId, $request);

            $ecritures      = $this->fetchEcrituresFlat($companyId, $min, $max, $request->date_debut, $request->date_fin, $exerciceId);
            $count          = $ecritures->count();
            $soldesInitiaux = $this->fetchSoldesInitiaux($companyId, $min, $max, $request->date_debut, $exerciceId);

            // ── CSV ──────────────────────────────────────────────────────────
            if ($format === 'csv') {
                $filename = "grand_livre_tiers_csv_{$compte1->numero_de_tiers}_{$compte2->numero_de_tiers}_" . now()->format('YmdHis') . '.csv';
                Excel::store(new GrandLivreTiersExport($ecritures, $soldesInitiaux), $filename, 'grand_livres_tiers');
                GrandLivreTiers::create($this->livreData($request, $user, $format, $filename));
                return back()->with('success', "CSV Grand Livre des Tiers généré avec succès ! ({$count} écritures)");
            }

            // ── PDF ──────────────────────────────────────────────────────────
            $filename  = "grand_livre_tiers_{$compte1->numero_de_tiers}_{$compte2->numero_de_tiers}_" . now()->format('YmdHis') . '.pdf';
            $titre     = 'Grand-livre des Tiers';
            $grandLivresPath = public_path('grand_livres_tiers/');
            if (!file_exists($grandLivresPath)) {
                mkdir($grandLivresPath, 0777, true);
            }

            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $displayMode);

            $pdf = $this->buildDompdf();
            $pdf->loadView('grand_livre', [
                'company_name'  => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                'date_debut'    => $request->date_debut,
                'date_fin'      => $request->date_fin,
                'compte'        => $compte1->numero_de_tiers,
                'compte_2'      => $compte2->numero_de_tiers,
                'user'          => $user,
                'titre'         => $titre,
                'display_mode'  => $displayMode,
            ]);

            $pdf->save($grandLivresPath . $filename);
            GrandLivreTiers::create($this->livreData($request, $user, $format, $filename));

            return back()->with('success', "PDF Grand Livre des Tiers généré avec succès ! ({$count} écritures)");

        } catch (\Exception $e) {
            Log::error('Erreur grand livre Tiers : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Prévisualisation Tiers
    // ─────────────────────────────────────────────────────────────────────────
    public function previewGrandLivreTiers(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'date_debut'      => 'required|date',
                'date_fin'        => 'required|date|after_or_equal:date_debut',
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
                'display_mode'    => 'nullable|in:origine,comptaflow,both',
            ]);

            $user        = Auth::user();
            $companyId   = session('current_company_id', $user->company_id);
            $displayMode = $request->display_mode ?? 'comptaflow';
            $exerciceId  = session('current_exercice_id');

            [$compte1, $compte2, $min, $max] = $this->resolveTiersRange($companyId, $request);

            $ecritures      = $this->fetchEcrituresFlat($companyId, $min, $max, $request->date_debut, $request->date_fin, $exerciceId);
            $soldesInitiaux = $this->fetchSoldesInitiaux($companyId, $min, $max, $request->date_debut, $exerciceId);

            $titre = 'Prévisualisation Grand-livre des Tiers';
            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $displayMode);

            $pdf = $this->buildDompdf();
            $pdf->loadView('grand_livre', [
                'company_name'  => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                'date_debut'    => $request->date_debut,
                'date_fin'      => $request->date_fin,
                'compte'        => $compte1->numero_de_tiers,
                'compte_2'      => $compte2->numero_de_tiers,
                'user'          => $user,
                'titre'         => $titre,
                'display_mode'  => $displayMode,
            ]);

            $fileName = 'preview_grand_livre_tiers_' . time() . '.pdf';
            $filePath = public_path('previews/' . $fileName);
            if (!file_exists(public_path('previews'))) {
                mkdir(public_path('previews'), 0777, true);
            }
            file_put_contents($filePath, $pdf->output());

            return response()->json(['success' => true, 'url' => asset('previews/' . $fileName)]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Suppression
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $livre    = GrandLivreTiers::findOrFail($id);
            $filePath = public_path('grand_livres_tiers/' . $livre->grand_livre_tiers);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $livre->delete();
            return redirect()->back()->with('success', 'Grand livre des Tiers supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression grand livre Tiers : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * UNE SEULE requête SQL (flat JOIN) — aucun eager loading Eloquent.
     */
    private function fetchEcrituresFlat(int $companyId, string $min, string $max, string $dateDebut, string $dateFin, ?int $exerciceId)
    {
        $query = DB::table('ecriture_comptables as e')
            ->join('plan_tiers as pt', function ($join) use ($companyId, $min, $max) {
                $join->on('e.plan_tiers_id', '=', 'pt.id')
                     ->where('pt.company_id', '=', $companyId)
                     ->where('pt.numero_de_tiers', '>=', $min)
                     ->where('pt.numero_de_tiers', '<=', $max);
            })
            ->leftJoin('plan_comptables as pc', 'e.plan_comptable_id', '=', 'pc.id')
            ->leftJoin('code_journals as cj', 'e.code_journal_id', '=', 'cj.id')
            ->leftJoin('lettrages as ltr', 'e.lettrage_id', '=', 'ltr.id')
            ->where('e.company_id', $companyId)
            ->whereBetween('e.date', [$dateDebut, $dateFin])
            ->select([
                'e.id',
                'e.date',
                'e.n_saisie',
                'e.n_saisie_user',
                'e.description_operation',
                'e.reference_piece',
                'e.plan_comptable_id',
                'e.plan_tiers_id',
                'e.code_journal_id',
                'e.debit',
                'e.credit',
                DB::raw('ltr.code             as lettrage_code'),
                DB::raw('pt.numero_de_tiers   as pt_numero'),
                DB::raw('pt.numero_original   as pt_numero_original'),
                DB::raw('pt.intitule          as pt_intitule'),
                DB::raw('pc.numero_de_compte  as pc_numero'),
                DB::raw('pc.numero_original   as pc_numero_original'),
                DB::raw('pc.intitule          as pc_intitule'),
                DB::raw('cj.code_journal      as cj_code'),
                DB::raw('cj.numero_original   as cj_numero_original'),
            ])
            ->orderBy('pt.numero_de_tiers', 'asc')
            ->orderBy('e.date', 'asc')
            ->orderBy('e.n_saisie', 'asc');

        if ($exerciceId) {
            $query->where('e.exercices_comptables_id', $exerciceId);
        }

        return $query->get()->map(function ($row) {
            $row->planTiers = (object)[
                'numero_de_tiers' => $row->pt_numero,
                'numero_original' => $row->pt_numero_original,
                'intitule'        => $row->pt_intitule,
            ];
            $row->planComptable = $row->pc_numero ? (object)[
                'numero_de_compte' => $row->pc_numero,
                'numero_original'  => $row->pc_numero_original,
                'intitule'         => $row->pc_intitule,
            ] : null;
            $row->codeJournal = $row->cj_code ? (object)[
                'code_journal'    => $row->cj_code,
                'numero_original' => $row->cj_numero_original,
            ] : null;
            $row->lettrage = $row->lettrage_code ?? '';
            return $row;
        });
    }

    /**
     * Soldes initiaux Tiers (1 requête GROUP BY).
     */
    private function fetchSoldesInitiaux(int $companyId, string $min, string $max, string $dateDebut, ?int $exerciceId): array
    {
        $query = DB::table('ecriture_comptables as e')
            ->join('plan_tiers as pt', function ($join) use ($companyId, $min, $max) {
                $join->on('e.plan_tiers_id', '=', 'pt.id')
                     ->where('pt.company_id', '=', $companyId)
                     ->where('pt.numero_de_tiers', '>=', $min)
                     ->where('pt.numero_de_tiers', '<=', $max);
            })
            ->where('e.company_id', $companyId)
            ->where('e.date', '<', $dateDebut)
            ->selectRaw('e.plan_tiers_id, SUM(e.debit) as si_debit, SUM(e.credit) as si_credit')
            ->groupBy('e.plan_tiers_id');

        if ($exerciceId) {
            $query->where('e.exercices_comptables_id', $exerciceId);
        }

        $result = [];
        foreach ($query->get() as $row) {
            $d = (float) $row->si_debit;
            $c = (float) $row->si_credit;
            $result[$row->plan_tiers_id] = ['debit' => $d, 'credit' => $c, 'solde' => $d - $c];
        }
        return $result;
    }

    /**
     * Résout la plage min/max des numéros de tiers.
     */
    private function resolveTiersRange(int $companyId, Request $request): array
    {
        $compte1 = PlanTiers::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->findOrFail($request->plan_tiers_id_1);
        $compte2 = PlanTiers::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->findOrFail($request->plan_tiers_id_2);

        $v1  = (string) $compte1->numero_de_tiers;
        $v2  = (string) $compte2->numero_de_tiers;
        $min = strcmp($v1, $v2) <= 0 ? $v1 : $v2;
        $max = strcmp($v1, $v2) <= 0 ? $v2 : $v1;

        return [$compte1, $compte2, $min, $max];
    }

    /**
     * Construit DOMPDF avec les options de performance optimales.
     */
    private function buildDompdf()
    {
        $pdf    = app('dompdf.wrapper');
        $domPdf = $pdf->getDomPDF();
        $domPdf->set_option('isPhpEnabled', true);
        $domPdf->set_option('enable_font_subsetting', false);
        $domPdf->set_option('isHtml5ParserEnabled', false);
        $domPdf->set_option('isRemoteEnabled', false);
        $domPdf->set_option('defaultFont', 'helvetica');
        return $pdf;
    }

    /**
     * Données communes pour enregistrement GrandLivreTiers en BD.
     */
    private function livreData(Request $request, $user, string $format, string $filename): array
    {
        return [
            'date_debut'      => $request->date_debut,
            'date_fin'        => $request->date_fin,
            'plan_tiers_id_1' => $request->plan_tiers_id_1,
            'plan_tiers_id_2' => $request->plan_tiers_id_2,
            'format'          => $format,
            'grand_livre_tiers' => $filename,
            'user_id'         => $user->id,
            'company_id'      => $user->company_id,
        ];
    }
}
