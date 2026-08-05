<?php

namespace App\Http\Controllers;

use App\Models\EcritureModele;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EcritureModeleController extends Controller
{
    /**
     * Liste des modèles de saisie de la company courante (JSON).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);

        $modeles = EcritureModele::where('company_id', $activeCompanyId)
            ->orderBy('nom')
            ->get();

        return response()->json(['success' => true, 'modeles' => $modeles]);
    }

    /**
     * Crée un nouveau modèle de saisie à partir des lignes actuellement dans la grille.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('current_company_id', $user->company_id);

            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'code_journal_id' => 'nullable|integer|exists:code_journals,id',
                'lignes' => 'required|array|min:1',
                'lignes.*.plan_comptable_id' => 'required|integer|exists:plan_comptables,id',
                'lignes.*.plan_tiers_id' => 'nullable|integer|exists:plan_tiers,id',
                'lignes.*.debit' => 'nullable|numeric|min:0',
                'lignes.*.credit' => 'nullable|numeric|min:0',
                'lignes.*.poste_tresorerie_id' => 'nullable|integer',
            ]);

            $modele = EcritureModele::create([
                'nom' => $validated['nom'],
                'code_journal_id' => $validated['code_journal_id'] ?? null,
                'lignes' => $validated['lignes'],
                'company_id' => $activeCompanyId,
                'user_id' => $user->id,
            ]);

            return response()->json(['success' => true, 'modele' => $modele]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création du modèle.'], 500);
        }
    }

    /**
     * Supprime un modèle de saisie, scopé à la company courante.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $activeCompanyId = session('current_company_id', $user->company_id);

        $modele = EcritureModele::where('company_id', $activeCompanyId)->find($id);
        if (!$modele) {
            return response()->json(['success' => false, 'message' => 'Modèle introuvable ou n\'appartenant pas à votre société.'], 404);
        }

        $modele->delete();

        return response()->json(['success' => true, 'message' => 'Modèle supprimé.']);
    }
}
