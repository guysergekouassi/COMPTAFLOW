<?php

namespace App\Http\Controllers;

use App\Models\PlanComptable;
use Illuminate\Support\Facades\Auth;
use App\Models\EcritureComptable;
use App\Models\GrandLivre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrandLivreExport;
use App\Models\ExerciceComptable;

class GrandLivreController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $PlanComptable = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderBy('numero_de_compte', 'asc')
            ->get();

        // Récupérer l'exercice en cours (priorité : contexte session > actif > non clôturé)
        $contextExerciceId = session('current_exercice_id');
        $exerciceEnCours   = null;

        if ($contextExerciceId) {
            $exerciceEnCours = ExerciceComptable::where('id', $contextExerciceId)
                ->where('company_id', $companyId)
                ->first();
        }
        if (!$exerciceEnCours) {
            $exerciceEnCours = ExerciceComptable::where('company_id', $companyId)
                ->where('is_active', 1)
                ->first();
        }
        if (!$exerciceEnCours) {
            $exerciceEnCours = ExerciceComptable::where('company_id', $companyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        $grandLivre = $exerciceEnCours
            ? GrandLivre::where('company_id', $companyId)
                ->where('date_debut', '>=', $exerciceEnCours->date_debut)
                ->where('date_fin', '<=', $exerciceEnCours->date_fin)
                ->orderByDesc('created_at')
                ->get()
            : GrandLivre::where('company_id', $companyId)
                ->orderByDesc('created_at')
                ->get();

        return view('accounting_ledger', compact('PlanComptable', 'grandLivre', 'companyId', 'exerciceEnCours'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Génération Grand Livre (PDF / Excel / CSV)
    // ─────────────────────────────────────────────────────────────────────────
    public function generateGrandLivre(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'date_debut'          => 'required|date',
                'date_fin'            => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'format_fichier'      => 'nullable|in:pdf,excel,csv',
                'display_mode'        => 'nullable|in:origine,comptaflow,both',
            ]);

            $user          = Auth::user();
            $companyId     = session('current_company_id', $user->company_id);
            $format        = $request->format_fichier ?? 'pdf';
            $displayMode   = $request->display_mode   ?? 'comptaflow';
            $exerciceId    = session('current_exercice_id');

            [$compte1, $compte2, $min, $max] = $this->resolveAccountRange($companyId, $request);

            // ── 1. Récupérer les IDs de comptes dans la plage ──────────────
            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_compte', '>=', $min)
                ->where('numero_de_compte', '<=', $max)
                ->pluck('id')
                ->toArray();

            // ── 2. UNE SEULE requête SQL avec 3 JOINs ─────────────────────
            //    (pas d'Eloquent eager-loading = pas de N requêtes supplémentaires)
            $ecritures = $this->fetchEcrituresFlat(
                $companyId, $comptesIds, $request->date_debut, $request->date_fin, $exerciceId
            );

            $count = $ecritures->count();

            // ── 3. Soldes initiaux (1 seule requête GROUP BY) ──────────────
            $soldesInitiaux = $this->fetchSoldesInitiaux(
                $companyId, $comptesIds, $request->date_debut, $exerciceId, 'plan_comptable_id'
            );

            // ── Excel / CSV ────────────────────────────────────────────────
            if ($format === 'excel' || $format === 'csv') {
                $ext      = $format === 'excel' ? 'xlsx' : 'csv';
                $disk     = 'grand_livres';
                $filename = "grand_livre_{$format}_{$compte1->numero_de_compte}_{$compte2->numero_de_compte}_" . now()->format('YmdHis') . ".{$ext}";

                Excel::store(new GrandLivreExport($ecritures, $soldesInitiaux), $filename, $disk);
                GrandLivre::create($this->livreData($request, $user, $format, $filename));

                return back()->with('success', ucfirst($format) . " Grand Livre généré avec succès ! ({$count} écritures)");
            }

            // ── PDF ────────────────────────────────────────────────────────
            $filename  = "grand_livre_{$compte1->numero_de_compte}_{$compte2->numero_de_compte}_" . now()->format('YmdHis') . '.pdf';
            $titre     = 'Grand-livre des comptes';
            $grandLivresPath = public_path('grand_livres/');

            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $displayMode);

            $pdf = $this->buildDompdf();
            $pdf->loadView('grand_livre', [
                'company_name'  => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                'date_debut'    => $request->date_debut,
                'date_fin'      => $request->date_fin,
                'compte'        => $compte1->numero_de_compte,
                'compte_2'      => $compte2->numero_de_compte,
                'user'          => $user,
                'titre'         => $titre,
                'display_mode'  => $displayMode,
            ]);

            $pdf->save($grandLivresPath . $filename);

            GrandLivre::create($this->livreData($request, $user, $format, $filename));

            return back()->with('success', "PDF Grand Livre généré avec succès ! ({$count} écritures)");

        } catch (\Exception $e) {
            Log::error('Erreur grand livre : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Prévisualisation (retourne une URL JSON)
    // ─────────────────────────────────────────────────────────────────────────
    public function previewGrandLivre(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            $request->validate([
                'date_debut'          => 'required|date',
                'date_fin'            => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'display_mode'        => 'nullable|in:origine,comptaflow,both',
            ]);

            $user        = Auth::user();
            $companyId   = session('current_company_id', $user->company_id);
            $displayMode = $request->display_mode ?? 'comptaflow';
            $exerciceId  = session('current_exercice_id');

            [$compte1, $compte2, $min, $max] = $this->resolveAccountRange($companyId, $request);

            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_compte', '>=', $min)
                ->where('numero_de_compte', '<=', $max)
                ->pluck('id')
                ->toArray();

            $ecritures      = $this->fetchEcrituresFlat($companyId, $comptesIds, $request->date_debut, $request->date_fin, $exerciceId);
            $soldesInitiaux = $this->fetchSoldesInitiaux($companyId, $comptesIds, $request->date_debut, $exerciceId, 'plan_comptable_id');

            $titre = 'Prévisualisation Grand-livre des comptes';
            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $displayMode);

            $pdf = $this->buildDompdf();
            $pdf->loadView('grand_livre', [
                'company_name'  => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                'date_debut'    => $request->date_debut,
                'date_fin'      => $request->date_fin,
                'compte'        => $compte1->numero_de_compte,
                'compte_2'      => $compte2->numero_de_compte,
                'user'          => $user,
                'titre'         => $titre,
                'display_mode'  => $displayMode,
            ]);

            $fileName = 'preview_grand_livre_' . time() . '.pdf';
            $filePath = public_path('previews/' . $fileName);
            if (!file_exists(public_path('previews'))) {
                mkdir(public_path('previews'), 0777, true);
            }
            file_put_contents($filePath, $pdf->output());

            return response()->json(['success' => true, 'url' => asset('previews/' . $fileName)]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Données invalides : ' . implode(', ', collect($e->errors())->flatten()->all()),
            ], 422);
        } catch (\Exception $e) {
            Log::error('GrandLivre Preview Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Suppression
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        try {
            $livre    = GrandLivre::findOrFail($id);
            $filePath = public_path('grand_livres/' . $livre->grand_livre);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $livre->delete();
            return redirect()->back()->with('success', 'Grand livre supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression grand livre : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * UNE SEULE requête SQL (flat JOIN) — aucun eager loading Eloquent.
     * Retourne une Collection d'objets stdClass enrichis de propriétés
     * compatibles avec ce qu'attend GrandLivrePaginationService.
     */
    private function fetchEcrituresFlat(int $companyId, array $comptesIds, string $dateDebut, string $dateFin, ?int $exerciceId)
    {
        if (empty($comptesIds)) {
            return collect();
        }

        $query = DB::table('ecriture_comptables as e')
            ->join('plan_comptables as pc', 'e.plan_comptable_id', '=', 'pc.id')
            ->leftJoin('plan_tiers as pt', 'e.plan_tiers_id', '=', 'pt.id')
            ->leftJoin('code_journals as cj', 'e.code_journal_id', '=', 'cj.id')
            ->where('e.company_id', $companyId)
            ->whereIn('e.plan_comptable_id', $comptesIds)
            ->whereBetween('e.date', [$dateDebut, $dateFin])
            ->select([
                // Écriture principale
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
                'e.lettrage',
                // Plan comptable
                DB::raw('pc.numero_de_compte  as pc_numero'),
                DB::raw('pc.numero_original   as pc_numero_original'),
                DB::raw('pc.intitule          as pc_intitule'),
                // Plan tiers
                DB::raw('pt.numero_de_tiers   as pt_numero'),
                DB::raw('pt.numero_original   as pt_numero_original'),
                DB::raw('pt.intitule          as pt_intitule'),
                // Code journal
                DB::raw('cj.code_journal      as cj_code'),
                DB::raw('cj.numero_original   as cj_numero_original'),
            ])
            ->orderBy('pc.numero_de_compte', 'asc')
            ->orderBy('e.date', 'asc')
            ->orderBy('e.n_saisie', 'asc');

        if ($exerciceId) {
            $query->where('e.exercices_comptables_id', $exerciceId);
        }

        // Récupérer en stdClass, puis adapter pour le service de pagination
        $rows = $query->get();

        // Transformer en objets compatibles avec GrandLivrePaginationService
        return $rows->map(function ($row) {
            // Créer des sous-objets stdClass qui imitent les relations Eloquent
            $row->planComptable = (object)[
                'numero_de_compte' => $row->pc_numero,
                'numero_original'  => $row->pc_numero_original,
                'intitule'         => $row->pc_intitule,
            ];
            $row->planTiers = $row->pt_numero ? (object)[
                'numero_de_tiers' => $row->pt_numero,
                'numero_original' => $row->pt_numero_original,
                'intitule'        => $row->pt_intitule,
            ] : null;
            $row->codeJournal = $row->cj_code ? (object)[
                'code_journal'    => $row->cj_code,
                'numero_original' => $row->cj_numero_original,
            ] : null;

            return $row;
        });
    }

    /**
     * Calcule les soldes initiaux (avant date_debut) en une seule requête GROUP BY.
     */
    private function fetchSoldesInitiaux(int $companyId, array $comptesIds, string $dateDebut, ?int $exerciceId, string $groupByColumn): array
    {
        if (empty($comptesIds)) {
            return [];
        }

        $query = DB::table('ecriture_comptables')
            ->where('company_id', $companyId)
            ->whereIn($groupByColumn, $comptesIds)
            ->where('date', '<', $dateDebut)
            ->selectRaw("{$groupByColumn}, SUM(debit) as si_debit, SUM(credit) as si_credit")
            ->groupBy($groupByColumn);

        if ($exerciceId) {
            $query->where('exercices_comptables_id', $exerciceId);
        }

        $result = [];
        foreach ($query->get() as $row) {
            $d = (float) $row->si_debit;
            $c = (float) $row->si_credit;
            $result[$row->$groupByColumn] = ['debit' => $d, 'credit' => $c, 'solde' => $d - $c];
        }
        return $result;
    }

    /**
     * Résout la plage min/max des numéros de comptes.
     */
    private function resolveAccountRange(int $companyId, Request $request): array
    {
        $compte1 = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->findOrFail($request->plan_comptable_id_1);
        $compte2 = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->findOrFail($request->plan_comptable_id_2);

        $v1  = (string) $compte1->numero_de_compte;
        $v2  = (string) $compte2->numero_de_compte;
        $min = strcmp($v1, $v2) <= 0 ? $v1 : $v2;
        $max = strcmp($v1, $v2) <= 0 ? $v2 : $v1;

        return [$compte1, $compte2, $min, $max];
    }

    /**
     * Construit et configure l'instance DOMPDF avec les options de performance optimales.
     */
    private function buildDompdf()
    {
        $pdf = app('dompdf.wrapper');
        $domPdf = $pdf->getDomPDF();
        $domPdf->set_option('isPhpEnabled', true);
        $domPdf->set_option('enable_font_subsetting', false);  // 10x plus rapide
        $domPdf->set_option('isHtml5ParserEnabled', false);    // Parser standard = plus léger
        $domPdf->set_option('isRemoteEnabled', false);          // Pas de ressources distantes
        $domPdf->set_option('defaultFont', 'helvetica');
        return $pdf;
    }

    /**
     * Données communes pour enregistrement GrandLivre en BD.
     */
    private function livreData(Request $request, $user, string $format, string $filename): array
    {
        return [
            'date_debut'          => $request->date_debut,
            'date_fin'            => $request->date_fin,
            'plan_comptable_id_1' => $request->plan_comptable_id_1,
            'plan_comptable_id_2' => $request->plan_comptable_id_2,
            'format'              => $format,
            'grand_livre'         => $filename,
            'user_id'             => $user->id,
            'company_id'          => $user->company_id,
        ];
    }
}
