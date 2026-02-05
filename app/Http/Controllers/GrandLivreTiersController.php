<?php

namespace App\Http\Controllers;

use App\Models\PlanTiers;
use Illuminate\Support\Facades\Auth;
use App\Models\EcritureComptable;
use App\Models\GrandLivreTiers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Exports\GrandLivreTiersExport;

use App\Models\ExerciceComptable;
use PDF;

class GrandLivreTiersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $PlanTiers = PlanTiers::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderByRaw('LEFT(numero_de_tiers, 1) ASC')
            ->orderBy('numero_de_tiers')
            ->get();

        $grandLivre = GrandLivreTiers::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->get();

        // Récupérer l'exercice en cours (Priorité au CONTEXTE, puis ACTIF)
        $contextExerciceId = session('current_exercice_id');
        $exerciceEnCours = null;

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

        return view('accounting_ledger_tiers', compact('PlanTiers', 'grandLivre', 'exerciceEnCours'));
    }

    


    public function generateGrandLivre(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
                'format_fichier' => 'nullable|in:pdf,excel,csv',
                'display_mode' => 'nullable|in:origine,comptaflow,both'
            ]);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id); // Fix: use session/auth logic consistently
            $display_mode = $request->display_mode ?? 'comptaflow';

            // Lookup avec withoutGlobalScopes pour garantir l'accès
            $compte1 = PlanTiers::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_tiers_id_1);
            $compte2 = PlanTiers::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_tiers_id_2);

            // Comparaison de chaînes pour la plage de Tiers (important pour le tri lexicographique)
            $v1 = (string)$compte1->numero_de_tiers;
            $v2 = (string)$compte2->numero_de_tiers;

            // Correction BUG: PHP compare les chaînes numériques comme des entiers
            $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
            $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

            $comptesIds = PlanTiers::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_tiers', '>=', $min)
                ->where('numero_de_tiers', '<=', $max)
                ->pluck('id');

            $query = EcritureComptable::join('plan_tiers', 'ecriture_comptables.plan_tiers_id', '=', 'plan_tiers.id')
                ->select('ecriture_comptables.*')
                ->with([
                    'planTiers',
                    'planComptable',
                    'codeJournal',
                    'JournauxSaisis',
                    'ExerciceComptable',
                    'user',
                    'company'
                ])
                ->where('ecriture_comptables.company_id', $companyId)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->orderBy('plan_tiers.numero_de_tiers', 'asc')
                ->orderBy('date', 'asc')
                ->orderBy('n_saisie', 'asc');

            // Filtrage strict par exercice si le contexte est défini
            if (session()->has('current_exercice_id')) {
                $query->where('exercices_comptables_id', session('current_exercice_id'));
            }

            // Récupéraion globale
            $ecritures = $query->get();

            // Filtrage en mémoire sur les comptes Tiers pour être sûr d'avoir exactement la plage demandée
            $ecritures = $ecritures->whereIn('plan_tiers_id', $comptesIds);

            $count = $ecritures->count();
            // On ne bloque plus si vide
            // if ($count === 0) { ... }

            $format_fichier = $request->format_fichier ?? 'pdf'; // 📌 PDF par défaut
            $grandLivresPath = public_path('grand_livres_tiers/');

            // 🔹 Excel
            if ($format_fichier === 'excel') {
                $filename = 'grand_livre_tiers_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.xlsx';

                Excel::store(new GrandLivreTiersExport($ecritures, $soldesInitiaux), $filename, 'grand_livres_tiers');

                GrandLivreTiers::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_tiers_id_1' => $request->plan_tiers_id_1,
                    'plan_tiers_id_2' => $request->plan_tiers_id_2,
                    'format' => $format_fichier,
                    'grand_livre_tiers' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "Excel Grand Livre des Tiers généré avec succès ! ($count écritures)");
            }

            // Calcul des soldes initiaux par Tiers
            $soldesInitiaux = [];
            foreach ($comptesIds as $idTiers) {
                $prev = EcritureComptable::where('company_id', $companyId)
                    ->where('plan_tiers_id', $idTiers)
                    ->where('date', '<', $request->date_debut);
                
                if (session()->has('current_exercice_id')) {
                    $prev->where('exercices_comptables_id', session('current_exercice_id'));
                }

                $si_debit = (float)$prev->sum('debit');
                $si_credit = (float)$prev->sum('credit');
                
                if ($si_debit != 0 || $si_credit != 0) {
                    $soldesInitiaux[$idTiers] = [
                        'debit' => $si_debit,
                        'credit' => $si_credit,
                        'solde' => $si_debit - $si_credit
                    ];
                }
            }

            // 🔹 CSV
            if ($format_fichier === 'csv') {
                $filename = 'grand_livre_tiers_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.csv';

                Excel::store(new GrandLivreTiersExport($ecritures, $soldesInitiaux), $filename, 'grand_livres_tiers');

                GrandLivreTiers::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_tiers_id_1' => $request->plan_tiers_id_1,
                    'plan_tiers_id_2' => $request->plan_tiers_id_2,
                    'format' => $format_fichier,
                    'grand_livre_tiers' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "CSV Grand Livre des Tiers généré avec succès ! ($count écritures)");
            }



            // 🔹 PDF (par défaut)
            $filename = 'grand_livre_tiers_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.pdf';

            $titre = "Grand-livre des Tiers";

            // UTILISATION DU SERVICE DE PAGINATION
            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $display_mode);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_tiers,
                'compte_2' => $compte2->numero_de_tiers,
                'user' => $user,
                'titre' => $titre,
                'display_mode' => $display_mode 
            ]);

            $pdf->save($grandLivresPath . $filename);

            GrandLivreTiers::create([
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'plan_tiers_id_1' => $request->plan_tiers_id_1,
                'plan_tiers_id_2' => $request->plan_tiers_id_2,
                'format' => $format_fichier,
                'grand_livre_tiers' => $filename,
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return back()->with('success', "PDF Grand Livre des Tiers généré avec succès ! ($count écritures)");

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du grand livre des Tiers : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }


    public function previewGrandLivreTiers(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
                'display_mode' => 'nullable|in:origine,comptaflow,both'
            ]);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $display_mode = $request->display_mode ?? 'comptaflow';

            $compte1 = PlanTiers::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_tiers_id_1);
            $compte2 = PlanTiers::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_tiers_id_2);

            // Comparaison de chaînes pour la plage de Tiers (important pour le tri lexicographique)
            $v1 = (string)$compte1->numero_de_tiers;
            $v2 = (string)$compte2->numero_de_tiers;

            // Correction BUG: PHP compare les chaînes numériques comme des entiers
            $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
            $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

            $comptesIds = PlanTiers::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_tiers', '>=', $min)
                ->where('numero_de_tiers', '<=', $max)
                ->pluck('id');

            $query = EcritureComptable::join('plan_tiers', 'ecriture_comptables.plan_tiers_id', '=', 'plan_tiers.id')
                ->select('ecriture_comptables.*')
                ->with([
                    'planTiers',
                    'codeJournal',
                    'JournauxSaisis',
                    'ExerciceComptable',
                    'user',
                    'company'
                ])
                ->where('ecriture_comptables.company_id', $companyId)
                ->whereBetween('ecriture_comptables.date', [$request->date_debut, $request->date_fin])
                ->orderBy('plan_tiers.numero_de_tiers', 'asc')
                ->orderBy('date', 'asc')
                ->orderBy('n_saisie', 'asc');

            if (session()->has('current_exercice_id')) {
                $query->where('exercices_comptables_id', session('current_exercice_id'));
            }

            // Récupéraion globale
            $ecritures = $query->get();

            // Log debug
            Log::info('--- PREVIEW GRAND LIVRE TIERS DEBUG ---');
            Log::info('Company ID: ' . $companyId);
            Log::info('Range Tiers: ' . $min . ' - ' . $max);
            Log::info('Computed Ids Count: ' . $comptesIds->count());
            Log::info('Query Result (Pre-Filter): ' . $ecritures->count());

            // Filtrage en mémoire sur les comptes Tiers
            $ecritures = $ecritures->whereIn('plan_tiers_id', $comptesIds);

            Log::info('Final Result Count: ' . $ecritures->count());

            $count = $ecritures->count();
            // On ne bloque plus si vide
            // if ($count === 0) { ... }


            $titre = "Prévisualisation Grand-livre des Tiers";

            // Calcul des soldes initiaux par Tiers
            $soldesInitiaux = [];
            foreach ($comptesIds as $idTiers) {
                $prev = EcritureComptable::where('company_id', $companyId)
                    ->where('plan_tiers_id', $idTiers)
                    ->where('date', '<', $request->date_debut);
                
                if (session()->has('current_exercice_id')) {
                    $prev->where('exercices_comptables_id', session('current_exercice_id'));
                }

                $si_debit = (float)$prev->sum('debit');
                $si_credit = (float)$prev->sum('credit');
                
                if ($si_debit != 0 || $si_credit != 0) {
                    $soldesInitiaux[$idTiers] = [
                        'debit' => $si_debit,
                        'credit' => $si_credit,
                        'solde' => $si_debit - $si_credit
                    ];
                }
            }

            // UTILISATION DU SERVICE DE PAGINATION
            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $display_mode);

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_tiers,
                'compte_2' => $compte2->numero_de_tiers,
                'user' => $user,
                'titre' => $titre,
                'display_mode' => $display_mode 
            ]);


            // 🔹 Générer un fichier temporaire
            $fileName = 'preview_grand_livre_tiers' . time() . '.pdf';
            $filePath = public_path('previews/' . $fileName);

            // Crée le dossier s’il n’existe pas
            if (!file_exists(public_path('previews'))) {
                mkdir(public_path('previews'), 0777, true);
            }

            file_put_contents($filePath, $pdf->output());

            // Retourner une URL publique
            $url = asset('previews/' . $fileName);

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $livre = GrandLivreTiers::findOrFail($id);

            $filePath = public_path('grand_livres_tiers/' . $livre->grand_livre_tiers);

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $livre->delete();

            return redirect()->back()->with('success', 'Grand livre des Tiers supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du grand livre des Tiers : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}
