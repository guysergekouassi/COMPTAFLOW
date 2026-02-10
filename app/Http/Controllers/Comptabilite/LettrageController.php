<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lettrage;
use App\Models\EcritureComptable;
use App\Models\PlanTiers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LettrageController extends Controller
{
    /**
     * Affiche l'interface de lettrage.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        // Récupérer les Tiers (Clients 41xxx et Fournisseurs 40xxx)
        $tiers = PlanTiers::where('company_id', $companyId)
            ->where(function($q) {
                $q->where('numero_de_tiers', 'like', '40%')
                  ->orWhere('numero_de_tiers', 'like', '41%');
            })
            ->orderBy('numero_de_tiers')
            ->get();

        $selectedTier = null;
        $ecritures = collect([]);

        if ($request->has('tier_id')) {
            $selectedTier = PlanTiers::where('company_id', $companyId)->find($request->tier_id);
            
            if ($selectedTier) {
                // Récupérer les écritures NON Lettrées de ce tiers
                // On peut aussi ajouter un filtre pour voir les lettrées
                $showLettrees = $request->boolean('show_lettrees');

                $query = EcritureComptable::where('company_id', $companyId)
                    ->where('plan_tiers_id', $selectedTier->id)
                    ->orderBy('date');
                
                if (!$showLettrees) {
                    $query->whereNull('lettrage_id');
                } else {
                    $query->with('lettrage');
                }

                $ecritures = $query->get();
            }
        }

        return view('comptabilite.lettrage.index', compact('tiers', 'selectedTier', 'ecritures'));
    }

    /**
     * Enregistre un nouveau lettrage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ecriture_ids' => 'required|array|min:2', // Au moins 2 écritures pour lettrer
            'ecriture_ids.*' => 'exists:ecriture_comptables,id',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        try {
            DB::beginTransaction();

            // 1. Vérifier que toutes les écritures appartiennent à la société et ne sont pas déjà lettrées
            $ecritures = EcritureComptable::whereIn('id', $request->ecriture_ids)
                ->where('company_id', $companyId)
                ->whereNull('lettrage_id') // Sécurité
                ->get();

            if ($ecritures->count() !== count($request->ecriture_ids)) {
                throw new \Exception("Certaines écritures sont introuvables ou déjà lettrées.");
            }

            // 2. Vérifier l'équilibre Débit = Crédit
            $sumDebit = $ecritures->sum('debit');
            $sumCredit = $ecritures->sum('credit');

            // On utilise une petite marge d'erreur pour les flottants (epsilon)
            if (abs($sumDebit - $sumCredit) > 0.01) {
                throw new \Exception("Le lettrage n'est pas équilibré. Débit: $sumDebit | Crédit: $sumCredit (Écart: " . ($sumDebit - $sumCredit) . ")");
            }

            // 3. Générer un Code de Lettrage Unique
            // Format : L-YYYY-XXXX (ex: L-2023-AAB) ou simple lettre incrémentale
            // Pour faire simple et robuste : Code Alphanumérique unique par Tiers
            $premierTiersId = $ecritures->first()->plan_tiers_id;
            
            // Trouver le dernier code pour ce tiers
            // C'est complexe de trouver le "dernier", on va utiliser une séquence globale ou un UUID court
            // Option simple : Date + Random ou Séquence
            $code = strtoupper(Str::random(5)); // Simple pour commencer
            
            // 4. Créer le Lettrage
            $lettrage = Lettrage::create([
                'code' => $code,
                'date_lettrage' => now(),
                'user_id' => $user->id,
                'company_id' => $companyId,
            ]);

            // 5. Mettre à jour les écritures
            EcritureComptable::whereIn('id', $request->ecriture_ids)->update([
                'lettrage_id' => $lettrage->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Lettrage effectué avec succès.', 
                'code' => $code,
                'lettrage_id' => $lettrage->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Supprimer un lettrage (Délettrage).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        try {
            $lettrage = Lettrage::where('company_id', $companyId)->findOrFail($id);
            
            // Les écritures seront automatiquement mises à NULL grâce la FK on delete set null 
            // Mais on peut le faire explicitement pour être sûr
            $lettrage->ecritures()->update(['lettrage_id' => null]);
            
            $lettrage->delete();

            return back()->with('success', 'Lettrage supprimé avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', "Erreur lors du délettrage : " . $e->getMessage());
        }
    }
}
