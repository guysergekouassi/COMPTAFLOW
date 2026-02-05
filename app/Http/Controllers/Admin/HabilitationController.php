<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class HabilitationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Support du contexte pour SuperAdmin (switch company)
        $companyId = session('current_company_id', $user->company_id);
        $company = \App\Models\Company::find($companyId);
        
        // Est-ce une sous-entreprise ? (Pour la logique Fusion)
        $isSubCompany = $company && $company->parent_company_id ? true : false;
        
        // Récupérer les utilisateurs de la compagnie active (sauf super admin)
        $users = User::where('role', '!=', 'super_admin')
                     ->where('company_id', $companyId)
                     ->with('creator')
                     ->get();

        $modules = Config::get('accounting_permissions.permissions');

        return view('admin.habilitations.index', compact('users', 'modules', 'isSubCompany'));
    }

    public function update(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        $currentUser = Auth::user();
        $currentCompanyId = session('current_company_id', $currentUser->company_id);
        
        // Sécurité : Un utilisateur ne peut pas modifier ses propres habilitations
        if ($targetUser->id === $currentUser->id) {
            return back()->with('error', 'Vous ne pouvez pas modifier vos propres habilitations.');
        }

        // Sécurité : Seul le créateur peut modifier un admin secondaire ou un comptable
        if (!$targetUser->isPrincipalAdmin() && $targetUser->created_by_id !== $currentUser->id && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Seul le créateur de ce compte peut modifier ses habilitations.');
        }

        $data = $request->validate([
            'habilitations' => 'nullable|array',
        ]);

        $newHabilitations = $data['habilitations'] ?? [];
        $finalHabilitations = [];

        $allModules = Config::get('accounting_permissions.permissions', []);
        
        // Est-ce une sous-entreprise ?
        $company = \App\Models\Company::find($currentCompanyId);
        $isSubCompany = $company && $company->parent_company_id;

        // On boucle sur toutes les permissions pour appliquer la logique de filtrage
        foreach ($allModules as $section => $perms) {
            $isSuperAdminSection = str_contains($section, 'Super Admin');
            $isFusionSection = str_contains($section, 'Fusion & Démarrage');

            foreach ($perms as $key => $label) {
                // Règle 1 : Jamais de Super Admin pour les non-SuperAdmins
                if ($isSuperAdminSection) {
                    $finalHabilitations[$key] = "0";
                    continue;
                }

                // Règle 2 : Fusion uniquement pour sous-entreprises
                if ($isFusionSection && !$isSubCompany) {
                    $finalHabilitations[$key] = "0";
                    continue;
                }

                // Règle 3 : Admin Principal a tout (sauf SA/Fusion hors contexte)
                if ($targetUser->isPrincipalAdmin()) {
                    $finalHabilitations[$key] = "1";
                } else {
                    // Pour les autres, on prend ce qui est envoyé
                    $finalHabilitations[$key] = isset($newHabilitations[$key]) ? "1" : "0";
                }
            }
        }

        $targetUser->habilitations = $finalHabilitations;
        $targetUser->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Habilitations mises à jour pour ' . $targetUser->name]);
        }

        return back()->with('success', 'Habilitations mises à jour avec succès pour ' . $targetUser->name);
    }
}
