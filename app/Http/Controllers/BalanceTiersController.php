<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Exports\BalanceTiersExport;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\EcritureComptable;
use App\Models\GrandLivre;
use App\Models\BalanceTiers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;


// use PDF;
use PDF;
use Illuminate\Http\Request;

class BalanceTiersController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user();

        // Tous les journaux de la compagnie du user
        $PlanTiers = PlanTiers::where('company_id', $user->company_id)
            ->orderByRaw('LEFT(numero_de_tiers, 1) ASC')
            ->orderBy('numero_de_tiers')
            ->get();

        $Balance = BalanceTiers::where('company_id', $user->company_id)
            ->orderByDesc('created_at')
            ->get();


        return view('accounting_balance_tiers', compact('PlanTiers', 'Balance'));
    }



    // public function generateBalance(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'date_debut' => 'required|date',
    //             'date_fin' => 'required|date|after_or_equal:date_debut',
    //             'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
    //             'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
    //         ]);

    //         $user = Auth::user();

    //         $compte1 = PlanTiers::findOrFail($request->plan_tiers_id_1);
    //         $compte2 = PlanTiers::findOrFail($request->plan_tiers_id_2);

    //         $min = min($compte1->numero_de_tiers, $compte2->numero_de_tiers);
    //         $max = max($compte1->numero_de_tiers, $compte2->numero_de_tiers);

    //         $comptesIds = PlanTiers::where('company_id', $user->company_id)
    //             ->whereBetween('numero_de_tiers', [$min, $max])
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
    //             ->whereIn('plan_tiers_id', $comptesIds)
    //             ->whereBetween('date', [$request->date_debut, $request->date_fin])
    //             ->get();

    //         if ($ecritures->isEmpty()) {
    //             return back()->with('error', 'Aucune écriture trouvée pour cette période.');
    //         }

    //         $titre = "Balances des Tiers";

    //         $filename = 'balance_tiers_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.pdf';
    //         $pdf = app('dompdf.wrapper');
    //         $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    //         $pdf->loadView('balance_tiers', [
    //             'company_name' => $user->company->company_name ?? 'Non défini',
    //             'ecritures' => $ecritures,
    //             'date_debut' => $request->date_debut,
    //             'date_fin' => $request->date_fin,
    //             'compte' => $compte1->numero_de_tiers,
    //             'compte_2' => $compte2->numero_de_tiers,
    //             'user' => $user,
    //             'titre' => $titre,
    //         ]);

    //         $pdf->save(public_path('balances_tiers/' . $filename));



    //         BalanceTiers::create([
    //             'date_debut' => $request->date_debut,
    //             'date_fin' => $request->date_fin,
    //             'plan_tiers_id_1' => $request->plan_tiers_id_1,
    //             'plan_tiers_id_2' => $request->plan_tiers_id_2,
    //             'balance_tiers' => $filename,
    //             'user_id' => $user->id,
    //             'company_id' => $user->company_id,
    //         ]);

    //         return back()->with('success', "PDF balance des Tiers généré avec succès ! ({$ecritures->count()} écritures)");
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
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
                'format' => 'nullable|in:pdf,excel,csv' // ✅ ajout du format
            ]);

            $user = Auth::user();

            $compte1 = PlanTiers::findOrFail($request->plan_tiers_id_1);
            $compte2 = PlanTiers::findOrFail($request->plan_tiers_id_2);

            $min = min($compte1->numero_de_tiers, $compte2->numero_de_tiers);
            $max = max($compte1->numero_de_tiers, $compte2->numero_de_tiers);

            $comptesIds = PlanTiers::where('company_id', $user->company_id)
                ->whereBetween('numero_de_tiers', [$min, $max])
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
                ->whereIn('plan_tiers_id', $comptesIds)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->get();

            $count = $ecritures->count();
            if ($count === 0) {
                return back()->with('error', 'Aucune écriture trouvée pour cette période.');
            }

            // Choix du format
            $format_fichier = $request->format_fichier ?? 'pdf'; // par défaut PDF
            $BalancesPath = public_path('balances_tiers/');

            // === EXCEL ===
            if ($format_fichier === 'excel') {
                $filename = 'balance_tiers_excel_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.xlsx';

                Excel::store(new BalanceTiersExport($ecritures), $filename, 'balances_tiers');

                BalanceTiers::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_tiers_id_1' => $request->plan_tiers_id_1,
                    'plan_tiers_id_2' => $request->plan_tiers_id_2,
                    'format' => $format_fichier,
                    'balance_tiers' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "Excel Balance des Tiers généré avec succès ! ($count écritures)");
            }

            // === CSV ===
            if ($format_fichier === 'csv') {
                $filename = 'balance_tiers_csv_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.csv';

                Excel::store(new BalanceTiersExport($ecritures), $filename, 'balances_tiers');

                BalanceTiers::create([
                    'date_debut' => $request->date_debut,
                    'date_fin' => $request->date_fin,
                    'plan_tiers_id_1' => $request->plan_tiers_id_1,
                    'plan_tiers_id_2' => $request->plan_tiers_id_2,
                    'format' => $format_fichier,
                    'balance_tiers' => $filename,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                ]);

                return back()->with('success', "CSV Balance des Tiers généré avec succès ! ($count écritures)");
            }

            // === PDF ===
            $filename = 'balance_tiers_pdf_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.pdf';
            $titre = "Balances des Tiers";

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('balance_tiers', [
                'company_name' => $user->company->company_name ?? 'Non défini', // retiré comme demandé
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_tiers,
                'compte_2' => $compte2->numero_de_tiers,
                'user' => $user,
                'titre' => $titre,
            ]);

            $pdf->save($BalancesPath . $filename);

            BalanceTiers::create([
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'plan_tiers_id_1' => $request->plan_tiers_id_1,
                'plan_tiers_id_2' => $request->plan_tiers_id_2,
                'format' => $format_fichier,
                'balance_tiers' => $filename,
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                
            ]);

            return back()->with('success', "PDF Balance des Tiers généré avec succès ! ($count écritures)");

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de la balance des tiers : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération de la balance des tiers. ' . $e->getMessage());
        }
    }



    public function previewBalanceTiers(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
            ]);

            $user = Auth::user();

            $compte1 = PlanTiers::findOrFail($request->plan_tiers_id_1);
            $compte2 = PlanTiers::findOrFail($request->plan_tiers_id_2);

            $min = min($compte1->numero_de_tiers, $compte2->numero_de_tiers);
            $max = max($compte1->numero_de_tiers, $compte2->numero_de_tiers);

            $comptesIds = PlanTiers::where('company_id', $user->company_id)
                ->whereBetween('numero_de_tiers', [$min, $max])
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
                ->whereIn('plan_tiers_id', $comptesIds)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->get();

            if ($ecritures->isEmpty()) {
                return back()->with('error', 'Aucune écriture trouvée pour cette période.');
            }

            $titre = "Prévisualisation Balances des Tiers";

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('balance_tiers', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_tiers,
                'compte_2' => $compte2->numero_de_tiers,
                'user' => $user,
                'titre' => $titre,
            ]);
            // Générer un fichier temporaire
            $fileName = 'preview_balance_tiers' . time() . '.pdf';
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
            $balance = BalanceTiers::findOrFail($id);
            $filePath = public_path('balances_tiers/' . $balance->balance_tiers);

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $balance->delete();

            return redirect()->back()->with('success', 'Balance des Tiers supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }


}
