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
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user();

        // Tous les journaux de la compagnie du user
        $PlanComptable = PlanComptable::where('company_id', $user->company_id)
            ->orderByRaw('LEFT(numero_de_compte, 1) ASC') // trie par la classe comptable (1 à 8)
            ->orderBy('numero_de_compte') // trie ensuite par ordre croissant dans chaque classe
            ->get();

        $Balance = Balance::where('company_id', $user->company_id)
            ->orderByDesc('created_at')
            ->get();


        return view('accounting_balance', compact('PlanComptable', 'Balance'));
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
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'format' => 'nullable|in:pdf,excel,csv' // ✅ on accepte le format
            ]);

            // dd($request->format_fichier);

            $user = Auth::user();
            $companyName = Auth::user()->company->company_name ?? 'Entreprise inconnue';


            $compte1 = PlanComptable::findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::findOrFail($request->plan_comptable_id_2);

            $min = min($compte1->numero_de_compte, $compte2->numero_de_compte);
            $max = max($compte1->numero_de_compte, $compte2->numero_de_compte);

            $comptesIds = PlanComptable::where('company_id', $user->company_id)
                ->whereBetween('numero_de_compte', [$min, $max])
                ->pluck('id');

            $ecritures = EcritureComptable::with([
                'planComptable',
                'planTiers',
                'codeJournal',
                'JournauxSaisis',
                'ExerciceComptable',
                'user',
                'company'
            ])
                ->where('company_id', $user->company_id)
                ->whereIn('plan_comptable_id', $comptesIds)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->get();

            $count = $ecritures->count();
            if ($count === 0) {
                return back()->with('error', 'Aucune écriture trouvée pour cette période.');
            }

            // Nouveau : choix du format
            $format_fichier = $request->format_fichier ?? 'pdf'; // PDF par défaut
            $BalancesPath = public_path('balances/'); // même dossier que ton PDF

            if ($format_fichier === 'excel') {
                $filename = 'balance_excel_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.xlsx';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new BalanceExport($ecritures), $filename, 'balances');

                // Enregistrement en BD
                Balance::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_comptable_id_1' => $request->plan_comptable_id_1,
                    'plan_comptable_id_2' => $request->plan_comptable_id_2,
                    'format' => $format_fichier,
                    'balance' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "Excel Balance généré avec succès ! ($count écritures)");
            }

            if ($format_fichier === 'csv') {
                $filename = 'balance_csv_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.csv';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new BalanceExport($ecritures), $filename, 'balances');

                // Enregistrement en BD
                Balance::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_comptable_id_1' => $request->plan_comptable_id_1,
                    'plan_comptable_id_2' => $request->plan_comptable_id_2,
                    'format' => $format_fichier,
                    'balance' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "CSV Balance généré avec succès ! ($count écritures)");
            }

            // Ton code PDF reste inchangé
            $filename = 'balance_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.pdf';
            $titre = "Balances des comptes";

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('balance', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_compte,
                'compte_2' => $compte2->numero_de_compte,
                'user' => $user,
                'titre' => $titre,
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
                'company_id' => $user->company_id,
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
                'type' => 'nullable|in:4,6,8'
            ]);

            $user = Auth::user();

            // --- Détermination des bornes de comptes ---
            $compte1 = PlanComptable::findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::findOrFail($request->plan_comptable_id_2);

            $min = min($compte1->numero_de_compte, $compte2->numero_de_compte);
            $max = max($compte1->numero_de_compte, $compte2->numero_de_compte);

            // --- Récupération des comptes concernés ---
            $comptesIds = PlanComptable::where('company_id', $user->company_id)
                ->whereBetween('numero_de_compte', [$min, $max])
                ->pluck('id');

            // --- Récupération des écritures ---
            $ecritures = EcritureComptable::with([
                'planComptable',
                'planTiers',
                'codeJournal',
                'JournauxSaisis',
                'ExerciceComptable',
                'user',
                'company'
            ])
                ->where('company_id', $user->company_id)
                ->whereIn('plan_comptable_id', $comptesIds)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->get();

            if ($ecritures->isEmpty()) {
                return back()->with('error', 'Aucune écriture trouvée pour cette période.');
            }

            // --- Définition du titre ---
            $titre = "Prévisualisation Balance des comptes";

            // --- Sélection dynamique de la vue PDF selon le type ---
            $view = match ($request->type) {
                '4' => 'balance',
                '6' => 'balance_6',
                '8' => 'balance_8',
                default => 'balance',
            };

            // --- Chargement du PDF avec la bonne vue ---
            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView($view, [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_compte,
                'compte_2' => $compte2->numero_de_compte,
                'user' => $user,
                'titre' => $titre,
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

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
