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

use PDF;

class GrandLivreTiersController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $PlanTiers = PlanTiers::where('company_id', $user->company_id)
            ->orderByRaw('LEFT(numero_de_tiers, 1) ASC')
            ->orderBy('numero_de_tiers')
            ->get();

        $grandLivre = GrandLivreTiers::where('company_id', $user->company_id)
            ->orderByDesc('created_at')
            ->get();

        return view('accounting_ledger_tiers', compact('PlanTiers', 'grandLivre'));
    }

    


    public function generateGrandLivre(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_tiers_id_1' => 'required|exists:plan_tiers,id',
                'plan_tiers_id_2' => 'required|exists:plan_tiers,id',
                'format' => 'nullable|in:pdf,excel,csv' // ✅ on accepte plusieurs formats
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

            $format_fichier = $request->format_fichier ?? 'pdf'; // 📌 PDF par défaut
            $grandLivresPath = public_path('grand_livres_tiers/');

            // 🔹 Excel
            if ($format_fichier === 'excel') {
                $filename = 'grand_livre_tiers_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.xlsx';

                Excel::store(new GrandLivreTiersExport($ecritures), $filename, 'grand_livres_tiers');

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

            // 🔹 CSV
            if ($format_fichier === 'csv') {
                $filename = 'grand_livre_tiers_' . $compte1->numero_de_tiers . '_' . $compte2->numero_de_tiers . '_' . now()->format('YmdHis') . '.csv';

                Excel::store(new GrandLivreTiersExport($ecritures), $filename, 'grand_livres_tiers');

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

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_tiers,
                'compte_2' => $compte2->numero_de_tiers,
                'user' => $user,
                'titre' => $titre,
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
                'PlanTiers',
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


            $titre = "Prévisualisation Grand-livre des Tiers";

            $pdf = app('dompdf.wrapper');
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_tiers,
                'compte_2' => $compte2->numero_de_tiers,
                'user' => $user,
                'titre' => $titre,
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
