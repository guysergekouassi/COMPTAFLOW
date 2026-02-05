<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TreasuryCategory;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryCategoryController extends Controller
{
    /**
     * Afficher la page de gestion des catégories de trésorerie
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $mainCompany = Company::findOrFail($companyId);
        
        $categories = TreasuryCategory::where('company_id', $companyId)
            ->withCount('postes')
            ->orderBy('name')
            ->get();

        return view('admin.config.treasury_categories', compact('categories', 'mainCompany'));
    }

    /**
     * Créer une nouvelle catégorie
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // Vérifier si la catégorie existe déjà
        $exists = TreasuryCategory::where('company_id', $companyId)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Cette catégorie existe déjà.');
        }

        TreasuryCategory::create([
            'name' => $request->name,
            'company_id' => $companyId,
        ]);

        return redirect()->route('admin.config.treasury_categories')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $category = TreasuryCategory::where('company_id', $companyId)
            ->findOrFail($id);

        // Vérifier si le nouveau nom existe déjà (sauf pour cette catégorie)
        $exists = TreasuryCategory::where('company_id', $companyId)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Cette catégorie existe déjà.');
        }

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.config.treasury_categories')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $category = TreasuryCategory::where('company_id', $companyId)
            ->findOrFail($id);

        // Vérifier si des postes utilisent cette catégorie
        if ($category->postes()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette catégorie car elle est utilisée par des postes de trésorerie.');
        }

        $category->delete();

        return redirect()->route('admin.config.treasury_categories')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Charger les catégories standards
     */
    public function loadStandardCategories()
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            
            $templates = [
                'Banques',
                'Caisses',
                'Comptes Courants',
                'Comptes d\'Épargne',
                'Comptes de Dépôt',
                'Autres',
            ];

            DB::beginTransaction();
            $count = 0;
            foreach ($templates as $name) {
                $exists = TreasuryCategory::where('company_id', $companyId)
                    ->where('name', $name)
                    ->exists();

                if (!$exists) {
                    TreasuryCategory::create([
                        'name' => $name,
                        'company_id' => $companyId,
                    ]);
                    $count++;
                }
            }
            DB::commit();

            return redirect()->route('admin.config.treasury_categories')
                ->with('success', "$count catégories standards chargées avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
        }
    }
}
