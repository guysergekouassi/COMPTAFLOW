<?php

namespace App\Http\Controllers;

use App\Models\PlanComptable;
use Illuminate\Support\Facades\Auth;
use App\Models\EcritureComptable;
use App\Models\GrandLivre;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GrandLivreExport;
use App\Models\ExerciceComptable;

class GrandLivreController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $companyId = session('current_company_id', $user->company_id);
        
        $PlanComptable = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderBy('numero_de_compte', 'asc')
            ->get(); // Récupère TOUS les résultats sans limite Laravel

        $grandLivre = GrandLivre::where('company_id', $companyId)
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

        return view('accounting_ledger', compact('PlanComptable', 'grandLivre', 'companyId', 'exerciceEnCours'));
    }

    

    public function generateGrandLivre(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'format_fichier' => 'nullable|in:pdf,excel,csv',
                'display_mode' => 'nullable|in:origine,comptaflow,both'
            ]);

            // dd($request->format_fichier);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $companyName = $user->company->company_name ?? 'Entreprise inconnue';

            $compte1 = PlanComptable::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_comptable_id_2);

            // Comparaison de chaînes pour la plage de comptes (important pour SYSCOHADA)
            $v1 = (string)$compte1->numero_de_compte;
            $v2 = (string)$compte2->numero_de_compte;
            
            // Correction BUG: PHP compare les chaînes numériques comme des entiers
            // On utilise strcmp pour forcer l'ordre alphabétique (comme SQL)
            $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
            $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_compte', '>=', $min)
                ->where('numero_de_compte', '<=', $max)
                ->pluck('id');

            $query = EcritureComptable::join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
                ->select('ecriture_comptables.*')
                ->with([
                    'planComptable',
                    'planTiers',
                    'codeJournal',
                    'JournauxSaisis',
                    'ExerciceComptable',
                    'user',
                    'company'
                ])
                ->where('ecriture_comptables.company_id', $companyId)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->orderBy('plan_comptables.numero_de_compte', 'asc')
                ->orderBy('date', 'asc')
                ->orderBy('n_saisie', 'asc');

            // Filtrage strict par exercice si le contexte est défini
            if (session()->has('current_exercice_id')) {
                $query->where('exercices_comptables_id', session('current_exercice_id'));
            }

            $ecritures = $query->get();

            // Filtrage en mémoire sur les comptes (car on a récupéré par date globale pour être sûr)
            $ecritures = $ecritures->whereIn('plan_comptable_id', $comptesIds);
            
            $count = $ecritures->count();
            // On continue même si vide pour générer un état "NÉANT"

            // Nouveau : choix du format
            $format_fichier = $request->format_fichier ?? 'pdf'; // PDF par défaut
            $grandLivresPath = public_path('grand_livres/'); // même dossier que ton PDF

            // Calcul des soldes initiaux par compte
            $soldesInitiaux = [];
            foreach ($comptesIds as $idCompte) {
                $prev = EcritureComptable::where('company_id', $companyId)
                    ->where('plan_comptable_id', $idCompte)
                    ->where('date', '<', $request->date_debut);
                
                if (session()->has('current_exercice_id')) {
                    $prev->where('exercices_comptables_id', session('current_exercice_id'));
                }

                $si_debit = (float)$prev->sum('debit');
                $si_credit = (float)$prev->sum('credit');
                
                if ($si_debit != 0 || $si_credit != 0) {
                    $soldesInitiaux[$idCompte] = [
                        'debit' => $si_debit,
                        'credit' => $si_credit,
                        'solde' => $si_debit - $si_credit
                    ];
                }
            }

            if ($format_fichier === 'excel') {
                $filename = 'grand_livre_excel_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.xlsx';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new GrandLivreExport($ecritures, $soldesInitiaux), $filename, 'grand_livres');

                // Enregistrement en BD
                GrandLivre::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_comptable_id_1' => $request->plan_comptable_id_1,
                    'plan_comptable_id_2' => $request->plan_comptable_id_2,
                    'format' => $format_fichier,
                    'grand_livre' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "Excel Grand Livre généré avec succès ! ($count écritures)");
            }

            if ($format_fichier === 'csv') {
                $filename = 'grand_livre_csv_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.csv';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new GrandLivreExport($ecritures, $soldesInitiaux), $filename, 'grand_livres');

                // Enregistrement en BD
                GrandLivre::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_comptable_id_1' => $request->plan_comptable_id_1,
                    'plan_comptable_id_2' => $request->plan_comptable_id_2,
                    'format' => $format_fichier,
                    'grand_livre' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "CSV Grand Livre généré avec succès ! ($count écritures)");
            }



            // PDF (par défaut)
            $filename = 'grand_livre_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.pdf';
            $titre = "Grand-livre des comptes";

            // UTILISATION DU SERVICE DE PAGINATION
            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $request->display_mode);

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData, // Nouvelle variable principale
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_compte,
                'compte_2' => $compte2->numero_de_compte,
                'user' => $user,
                'titre' => $titre,
                'display_mode' => $request->display_mode ?? 'comptaflow',
            ]);

            $pdf->save($grandLivresPath . $filename);

            GrandLivre::create([
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'plan_comptable_id_1' => $request->plan_comptable_id_1,
                'plan_comptable_id_2' => $request->plan_comptable_id_2,
                'format' => $format_fichier,
                'grand_livre' => $filename,
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return back()->with('success', "PDF Grand Livre généré avec succès ! ($count écritures)");

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du grand livre : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération du grand livre.' . $e->getMessage());
        }
    }






    public function previewGrandLivre(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'display_mode' => 'nullable|in:origine,comptaflow,both'
            ]);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            $compte1 = PlanComptable::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_comptable_id_2);

            // Comparaison de chaînes pour la plage de comptes (important pour SYSCOHADA)
            $v1 = (string)$compte1->numero_de_compte;
            $v2 = (string)$compte2->numero_de_compte;
            
            // Correction BUG: PHP compare les chaînes numériques comme des entiers
            $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
            $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_compte', '>=', $min)
                ->where('numero_de_compte', '<=', $max)
                ->pluck('id');

            $query = EcritureComptable::join('plan_comptables', 'ecriture_comptables.plan_comptable_id', '=', 'plan_comptables.id')
                ->select('ecriture_comptables.*')
                ->with([
                    'planComptable',
                    'planTiers',
                    'codeJournal',
                    'JournauxSaisis',
                    'ExerciceComptable',
                    'user',
                    'company'
                ])
                ->where('ecriture_comptables.company_id', $companyId)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->orderBy('plan_comptables.numero_de_compte', 'asc')
                ->orderBy('date', 'asc')
                ->orderBy('n_saisie', 'asc');

            if (session()->has('current_exercice_id')) {
                $query->where('exercices_comptables_id', session('current_exercice_id'));
            }

            $ecritures = $query->get();
            
            // Log debug
            Log::info('--- PREVIEW GRAND LIVRE DEBUG ---');
            Log::info('Company ID: ' . $companyId);
            Log::info('Range: ' . $min . ' - ' . $max);
            Log::info('Computed Ids Count: ' . $comptesIds->count());
            Log::info('Query Result (Pre-Filter): ' . $ecritures->count());

            // Filtrage en mémoire par comptes
            $ecritures = $ecritures->whereIn('plan_comptable_id', $comptesIds);
            
            Log::info('Final Result Count: ' . $ecritures->count());

            // On autorise la prévisualisation vide

            $titre = "Prévisualisation Grand-livre des comptes";

            // Calcul des soldes initiaux par compte
            $soldesInitiaux = [];
            foreach ($comptesIds as $idCompte) {
                $prev = EcritureComptable::where('company_id', $companyId)
                    ->where('plan_comptable_id', $idCompte)
                    ->where('date', '<', $request->date_debut);
                
                if (session()->has('current_exercice_id')) {
                    $prev->where('exercices_comptables_id', session('current_exercice_id'));
                }

                $si_debit = (float)$prev->sum('debit');
                $si_credit = (float)$prev->sum('credit');
                
                if ($si_debit != 0 || $si_credit != 0) {
                    $soldesInitiaux[$idCompte] = [
                        'debit' => $si_debit,
                        'credit' => $si_credit,
                        'solde' => $si_debit - $si_credit
                    ];
                }
            }

            // UTILISATION DU SERVICE DE PAGINATION
            $paginationService = new \App\Services\GrandLivrePaginationService();
            $paginatedData = $paginationService->paginate($ecritures, $soldesInitiaux, $titre, $request->display_mode);

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'paginatedData' => $paginatedData,
                // 'ecritures' => $ecritures,
                // 'soldesInitiaux' => $soldesInitiaux,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_compte,
                'compte_2' => $compte2->numero_de_compte,
                'user' => $user,
                'titre' => $titre,
                'display_mode' => $request->display_mode ?? 'comptaflow',
            ]);

            // 🔹 Générer un fichier temporaire
            $fileName = 'preview_grand_livre_' . time() . '.pdf';
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Données invalides : ' . implode(', ', collect($e->errors())->flatten()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('GrandLivre Preview Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }



    public function destroy($id)
    {
        try {
            $livre = GrandLivre::findOrFail($id);

            $filePath = public_path('grand_livres/' . $livre->grand_livre);

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $livre->delete();

            return redirect()->back()->with('success', 'Grand livre supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du grand livre : ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}
