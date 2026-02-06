<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class SuperAdminHabilitationController extends Controller
{
    /**
     * Affiche la liste de tous les utilisateurs et leurs habilitations
     */
    public function index()
    {
        $user = Auth::user();
        
        // Règles NB1: SA Principal vs secondaire
        if ($user->isSecondarySuperAdmin()) {
            // Un SA secondaire ne peut pas listé les Super Admins
            $users = User::where('role', '!=', 'super_admin')
                        ->with(['company', 'creator'])
                        ->paginate(50);
        } else {
            // SA Principal voit tout
            $users = User::with(['company', 'creator'])->paginate(50);
        }

        $modules = Config::get('accounting_permissions.permissions');
        $companies = Company::all();

        return view('superadmin.habilitations', compact('users', 'modules', 'companies'));
    }

    /**
     * Met à jour les habilitations d'un utilisateur spécifique
     */
    public function update(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        $currentUser = Auth::user();

        // Sécurité Rules NB1:
        if ($targetUser->isSuperAdmin() && !$currentUser->isPrimarySuperAdmin()) {
            return back()->with('error', 'Seul le Super Admin Principal peut modifier les droits d\'un autre Super Admin.');
        }

        // Sécurité Rules NB2: Un SA secondaire ne peut pas modifier un SA
        if ($currentUser->isSecondarySuperAdmin() && $targetUser->isSuperAdmin()) {
            return back()->with('error', 'Accès refusé.');
        }

        $data = $request->validate([
            'habilitations' => 'nullable|array',
        ]);

        $newHabilitations = $data['habilitations'] ?? [];
        $finalHabilitations = [];

        $allModules = Config::get('accounting_permissions.permissions', []);
        
        // On boucle sur toutes les permissions pour appliquer la logique de filtrage
        foreach ($allModules as $section => $perms) {
            foreach ($perms as $key => $label) {
                // Pour les Super Admins, on peut tout donner (si autorisé par les règles ci-dessus)
                // Pour les Admins/Comptables, on prend ce qui est envoyé
                $finalHabilitations[$key] = isset($newHabilitations[$key]) ? "1" : "0";
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
