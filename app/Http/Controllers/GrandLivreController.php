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

        return view('accounting_ledger', compact('PlanComptable', 'grandLivre', 'companyId'));
    }

    

    public function generateGrandLivre(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'plan_comptable_id_1' => 'required|exists:plan_comptables,id',
                'plan_comptable_id_2' => 'required|exists:plan_comptables,id',
                'format_fichier' => 'nullable|in:pdf,excel,csv' 
            ]);

            // dd($request->format_fichier);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $companyName = $user->company->company_name ?? 'Entreprise inconnue';

            $compte1 = PlanComptable::withoutGlobalScopes()->findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::withoutGlobalScopes()->findOrFail($request->plan_comptable_id_2);

            // On compare en tant que chaînes (SYSCOHADA : longueur fixe 8 conseillée)
            $min = $compte1->numero_de_compte < $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;
            $max = $compte1->numero_de_compte > $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;

            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
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
                ->where('company_id', $companyId)
                ->whereIn('plan_comptable_id', $comptesIds)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->get();

            $count = $ecritures->count();
            if ($count === 0) {
                return back()->with('error', 'Aucune écriture trouvée pour cette période.');
            }

            // Nouveau : choix du format
            $format_fichier = $request->format_fichier ?? 'pdf'; // PDF par défaut
            $grandLivresPath = public_path('grand_livres/'); // même dossier que ton PDF

            if ($format_fichier === 'excel') {
                $filename = 'grand_livre_excel_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.xlsx';

                // Sauvegarde dans public/grand_livres/
                Excel::store(new GrandLivreExport($ecritures), $filename, 'grand_livres');

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
                Excel::store(new GrandLivreExport($ecritures), $filename, 'grand_livres');

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

            // Ton code PDF reste inchangé
            $filename = 'grand_livre_' . $compte1->numero_de_compte . '_' . $compte2->numero_de_compte . '_' . now()->format('YmdHis') . '.pdf';
            $titre = "Grand-livre des comptes";

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_compte,
                'compte_2' => $compte2->numero_de_compte,
                'user' => $user,
                'titre' => $titre,
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
            ]);

            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            $compte1 = PlanComptable::withoutGlobalScopes()->findOrFail($request->plan_comptable_id_1);
            $compte2 = PlanComptable::withoutGlobalScopes()->findOrFail($request->plan_comptable_id_2);

            $min = $compte1->numero_de_compte < $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;
            $max = $compte1->numero_de_compte > $compte2->numero_de_compte ? $compte1->numero_de_compte : $compte2->numero_de_compte;

            $comptesIds = PlanComptable::withoutGlobalScopes()
                ->where('company_id', $companyId)
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
                ->where('company_id', $companyId)
                ->whereIn('plan_comptable_id', $comptesIds)
                ->whereBetween('date', [$request->date_debut, $request->date_fin])
                ->get();

            if ($ecritures->count() === 0) {
                return response()->json(['success' => false, 'error' => 'Aucune écriture trouvée pour cette période.']);
            }

            $titre = "Prévisualisation Grand-livre des comptes";

            $pdf = app('dompdf.wrapper');
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->loadView('grand_livre', [
                'company_name' => $user->company->company_name ?? 'Non défini',
                'ecritures' => $ecritures,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'compte' => $compte1->numero_de_compte,
                'compte_2' => $compte2->numero_de_compte,
                'user' => $user,
                'titre' => $titre,
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
