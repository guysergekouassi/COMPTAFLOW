<?php

namespace App\Http\Controllers;
use App\Exports\BalanceExport;
use Illuminate\Support\Facades\Auth;
use App\Models\PlanComptable;
use App\Models\EcritureComptable;
use App\Models\GrandLivre;
use App\Models\Balance;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

// use PDF;
use PDF;
use App\Models\ExerciceComptable;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // Récupère TOUS les comptes sans exception, triés simplement par numéro
        $PlanComptable = PlanComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->orderBy('numero_de_compte', 'asc')
            ->get();

        $Balance = Balance::where('company_id', $companyId)
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

        // Si toujours aucun exercice, on prend le dernier même clôturé pour éviter le null
        if (!$exerciceEnCours) {
            $exerciceEnCours = ExerciceComptable::where('company_id', $companyId)
                ->orderBy('date_debut', 'desc')
                ->first();
        }

        return view('accounting_balance', compact('PlanComptable', 'Balance', 'exerciceEnCours'));
    }



    // public function generateBalance(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'date_debut' => 'required|date',
    //             'date_fin' => 'required|date|after_or_equal:date_debut',
    //             'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
    //             'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
    //         ]);

    //         $user = Auth::user();

    //         $compte1 = PlanComptable::findOrFail($request->plan_comptable_id_1);
    //         $compte2 = PlanComptable::findOrFail($request->plan_comptable_id_2);

    //         $min = min($compte1->numero_de_compte, $compte2->numero_de_compte);
    //         $max = max($compte1->numero_de_compte, $compte2->numero_de_compte);

    //         $comptesIds = PlanComptable::where('company_id', $user->company_id)
    //             ->whereBetween('numero_de_compte', [$min, $max])
    //             ->pluck('id');

    //         $ecritures = EcritureComptable::with([
    //             'planComptable',
    //             'planTiers',
    //             'codeJournal',
    //             'JournauxSaisis',
    //             'ExerciceComptable',
    //             'user',
    //             'company'
    //         ])
    //             ->where('company_id', $user->company_id)
    //             ->whereIn('plan_comptable_id', $comptesIds)
    //             ->whereBetween('date', [$request->date_debut, $request->date_fin])
    //             ->get();

    //         if ($ecritures->isEmpty()) {
    //             return back()->with('error', 'Aucune écriture trouvée pour cette période.');
    //         }

    //         $titre = "Balances des comptes";

    //         $filename = 'balance_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.pdf';
    //         $pdf = app('dompdf.wrapper');
    //         $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    //         $pdf->loadView('balance', [
    //             'company_name' => $user->company->company_name ?? 'Non défini',
    //             'ecritures' => $ecritures,
    //             'date_debut' => $request->date_debut,
    //             'date_fin' => $request->date_fin,
    //             'compte' => $compte1->numero_de_compte,
    //             'compte_2' => $compte2->numero_de_compte,
    //             'user' => $user,
    //             'titre' => $titre,
    //         ]);

    //         $pdf->save(public_path('balances/' . $filename));



    //         Balance::create([
    //             'date_debut' => $request->date_debut,
    //             'date_fin' => $request->date_fin,
    //             'plan_comptable_id_1' => $request->plan_comptable_id_1,
    //             'plan_comptable_id_2' => $request->plan_comptable_id_2,
    //             'balance' => $filename,
    //             'titre' => $titre,
    //             'user_id' => $user->id,
    //             'company_id' => $user->company_id,
    //         ]);

    //         return back()->with('success', "PDF balance généré avec succès ! ({$ecritures->count()} écritures)");
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
    //     }
    // }


    public function generateBalance(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
            'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
            'format_fichier' => 'nullable|in:pdf,excel,csv',
            'display_mode' => 'nullable|in:origine,comptaflow,both' // Mode d'affichage des numéros de compte
        ], [
            'date_debut.required' => 'La date de début est obligatoire.',
            'date_fin.required' => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'plan_comptable_id_1.required' => 'Le compte de début est requis.',
            'plan_comptable_id_2.required' => 'Le compte de fin est requis.',
        ]);

        try {

            // dd($request->format_fichier);

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

            // IMPORTANT: On affiche TOUS les comptes de l'exercice
            // sans filtrer par plage, car les utilisateurs ne sélectionnent pas toujours
            // la bonne plage et cela cause des résultats vides.
            $query = EcritureComptable::with([
                'planComptable',
                'planTiers',
                'codeJournal',
                'JournauxSaisis',
                'ExerciceComptable',
                'user',
                'company'
            ])
                ->where('company_id', $companyId)
                // Pas de filtre par plage de comptes - on affiche tout
                ->whereBetween('date', [$request->date_debut, $request->date_fin]);

            // Filtrage strict par exercice si le contexte est défini
            if (session()->has('current_exercice_id')) {
                $query->where('exercices_comptables_id', session('current_exercice_id'));
            }

            $ecritures = $query->get();
            
            // On autorise la génération même vide (utile pour certifier le néant)
            $count = $ecritures->count();
            // Si vide, on ne bloque pas, on génère une balance vide

            // Nouveau : choix du format
            $format_fichier = $request->format_fichier ?? 'pdf'; // PDF par défaut
            $BalancesPath = public_path('balances/'); // même dossier que ton PDF

            if ($format_fichier === 'excel') {
                $filename = 'balance_excel_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.xlsx';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new BalanceExport($ecritures, $request->display_mode ?? 'comptaflow'), $filename, 'balances');

                // Enregistrement en BD
                Balance::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_comptable_id_1' => $request->plan_comptable_id_1,
                    'plan_comptable_id_2' => $request->plan_comptable_id_2,
                    'format' => $format_fichier,
                    'balance' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                ]);

                return back()->with('success', "Excel Balance généré avec succès ! ($count écritures)");
            }

            if ($format_fichier === 'csv') {
                $filename = 'balance_csv_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.csv';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new BalanceExport($ecritures, $request->display_mode ?? 'comptaflow'), $filename, 'balances');

                // Enregistrement en BD
                Balance::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_comptable_id_1' => $request->plan_comptable_id_1,
                    'plan_comptable_id_2' => $request->plan_comptable_id_2,
                    'format' => $format_fichier,
                    'balance' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $companyId,
                ]);

                return back()->with('success', "CSV Balance généré avec succès ! ($count écritures)");
            }

            // Ton code PDF reste inchangé
            $filename = 'balance_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.pdf';
            $titre = "Balances des comptes";

            // --- Calcul de la plage réelle de comptes utilisés ---
            $comptesUtilises = $ecritures->pluck('planComptable.numero_de_compte')->filter()->sort();
            $premierCompte = $comptesUtilises->first() ?? $compte1->numero_de_compte;
            $dernierCompte = $comptesUtilises->last() ?? $compte2->numero_de_compte;

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('balance', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $premierCompte,
                'compte_2' => $dernierCompte,
                'user' => $user,
                'titre' => $titre,
                'display_mode' => $request->display_mode ?? 'comptaflow',
            ]);

            $pdf->save($BalancesPath . $filename);

            Balance::create([
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'plan_comptable_id_1' => $request->plan_comptable_id_1,
                'plan_comptable_id_2' => $request->plan_comptable_id_2,
                'format' => $format_fichier,
                'balance' => $filename,
                'user_id' => $user->id,
                'company_id' => $companyId,
            ]);

            return back()->with('success', "PDF Balance généré avec succès ! ($count écritures)");

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de la balance : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération de la balance.' . $e->getMessage());
        }
    }

    // public function previewBalance(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'date_debut' => 'required|date',
    //             'date_fin' => 'required|date|after_or_equal:date_debut',
    //             'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
    //             'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
    //             'type' => 'nullable|in:4,6,8'
    //         ]);

    //         $user = Auth::user();

    //         $compte1 = PlanComptable::findOrFail($request->plan_comptable_id_1);
    //         $compte2 = PlanComptable::findOrFail($request->plan_comptable_id_2);

    //         $min = min($compte1->numero_de_compte, $compte2->numero_de_compte);
    //         $max = max($compte1->numero_de_compte, $compte2->numero_de_compte);

    //         $comptesIds = PlanComptable::where('company_id', $user->company_id)
    //             ->whereBetween('numero_de_compte', [$min, $max])
    //             ->pluck('id');

    //         $ecritures = EcritureComptable::with([
    //             'planComptable',
    //             'planTiers',
    //             'codeJournal',
    //             'JournauxSaisis',
    //             'ExerciceComptable',
    //             'user',
    //             'company'
    //         ])
    //             ->where('company_id', $user->company_id)
    //             ->whereIn('plan_comptable_id', $comptesIds)
    //             ->whereBetween('date', [$request->date_debut, $request->date_fin])
    //             ->get();

    //         if ($ecritures->isEmpty()) {
    //             return back()->with('error', 'Aucune écriture trouvée pour cette période.');
    //         }

    //         $titre = "Prévisualisation Balances des comptes";

    //         $pdf = app('dompdf.wrapper');
    //         $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    //         $pdf->loadView('balance', [
    //             'company_name' => $user->company->company_name ?? 'Non défini',
    //             'ecritures' => $ecritures,
    //             'date_debut' => $request->date_debut,
    //             'date_fin' => $request->date_fin,
    //             'compte' => $compte1->numero_de_compte,
    //             'compte_2' => $compte2->numero_de_compte,
    //             'user' => $user,
    //             'titre' => $titre,
    //         ]);

    //         // Générer un fichier temporaire
    //         $fileName = 'preview_balance' . time() . '.pdf';
    //         $filePath = public_path('previews/' . $fileName);

    //         // Crée le dossier s’il n’existe pas
    //         if (!file_exists(public_path('previews'))) {
    //             mkdir(public_path('previews'), 0777, true);
    //         }

    //         file_put_contents($filePath, $pdf->output());

    //         // Retourner une URL publique
    //         $url = asset('previews/' . $fileName);

    //         return response()->json([
    //             'success' => true,
    //             'url' => $url
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    public function previewBalance(Request $request)
    {
        try {
            // --- Validation des entrées ---
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'type' => 'nullable|in:4,6,8',
                'display_mode' => 'nullable|in:origine,comptaflow,both'
            ], [
                'date_debut.required' => 'La date de début est obligatoire.',
                'date_fin.required' => 'La date de fin est obligatoire.',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
                'plan_comptable_id_1.required' => 'Le compte de début est requis.',
                'plan_comptable_id_2.required' => 'Le compte de fin est requis.',
            ]);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            // --- Détermination des bornes de comptes ---
            $compte1 = PlanComptable::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($request->plan_comptable_id_2);

            $v1 = (string)$compte1->numero_de_compte;
            $v2 = (string)$compte2->numero_de_compte;

            // Correction BUG: PHP compare les chaînes numériques comme des entiers
            $min = strcmp($v1, $v2) < 0 ? $v1 : $v2;
            $max = strcmp($v1, $v2) < 0 ? $v2 : $v1;

            // --- Récupération des comptes concernés ---
            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('numero_de_compte', '>=', $min)
                ->where('numero_de_compte', '<=', $max)
                ->pluck('id');

            // --- Récupération des écritures ---
            // IMPORTANT: On affiche TOUS les comptes de l'exercice
            // sans filtrer par plage, car les utilisateurs ne sélectionnent pas toujours
            // la bonne plage et cela cause des résultats vides.
            $query = EcritureComptable::with([
                'planComptable',
                'planTiers',
                'codeJournal',
                'JournauxSaisis',
                'ExerciceComptable',
                'user',
                'company'
            ])
                ->where('company_id', $companyId)
                // Pas de filtre par plage de comptes - on affiche tout
                ->whereBetween('date', [$request->date_debut, $request->date_fin]);

             // LOG DEBUG
            \Illuminate\Support\Facades\Log::info('--- PREVIEW BALANCE DEBUG ---');
            \Illuminate\Support\Facades\Log::info('Session ID: ' . session()->getId());
            \Illuminate\Support\Facades\Log::info('Current Exercice ID (Session): ' . session('current_exercice_id'));
            \Illuminate\Support\Facades\Log::info('Company ID: ' . $companyId);
            \Illuminate\Support\Facades\Log::info('Dates: ' . $request->date_debut . ' to ' . $request->date_fin);
            \Illuminate\Support\Facades\Log::info('Comptes IDs Count: ' . $comptesIds->count());

            // Filtrage strict par exercice si le contexte est défini
            if (session()->has('current_exercice_id')) {
                $query->where('exercices_comptables_id', session('current_exercice_id'));
                \Illuminate\Support\Facades\Log::info('Filtering by Exercice ID: ' . session('current_exercice_id'));
            } else {
                \Illuminate\Support\Facades\Log::info('NO Exercice Context Filter Applied');
            }

            $ecritures = $query->get();
            \Illuminate\Support\Facades\Log::info('Result Count: ' . $ecritures->count());

            // On autorise la prévisualisation vide
            // if ($ecritures->isEmpty()) { ... }

            // --- Définition du titre ---
            $titre = "Prévisualisation Balance des comptes";

            // --- Sélection dynamique de la vue PDF selon le type ---
            $view = match ($request->type) {
                '4' => 'balance',
                '6' => 'balance_6',
                '8' => 'balance_8',
                default => 'balance',
            };

            // --- Calcul de la plage réelle de comptes utilisés ---
            $comptesUtilises = $ecritures->pluck('planComptable.numero_de_compte')->filter()->sort();
            $premierCompte = $comptesUtilises->first() ?? $compte1->numero_de_compte;
            $dernierCompte = $comptesUtilises->last() ?? $compte2->numero_de_compte;

            // --- Chargement du PDF avec la bonne vue ---
            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView($view, [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $premierCompte,
                'compte_2' => $dernierCompte,
                'user' => $user,
                'titre' => $titre,
                'display_mode' => $request->display_mode ?? 'comptaflow',
            ]);

            // --- Sauvegarde du PDF temporaire ---
            $fileName = 'preview_balance_' . $request->type . '_' . time() . '.pdf';
            $directory = public_path('previews');

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            $filePath = $directory . '/' . $fileName;
            file_put_contents($filePath, $pdf->output());

            // --- URL publique pour la prévisualisation ---
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
            Log::error('Balance Preview Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $balance = Balance::findOrFail($id);
            $filePath = public_path('balances/' . $balance->balance);

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $balance->delete();

            return redirect()->back()->with('success', 'Balance supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }


}
