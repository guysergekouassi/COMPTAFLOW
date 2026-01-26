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
        
        // Récupérer les utilisateurs de la compagnie active (sauf super admin)
        $users = User::where('role', '!=', 'super_admin')
                     ->where('company_id', $companyId)
                     ->get();

        $modules = Config::get('accounting_permissions.permissions');

        return view('admin.habilitations.index', compact('users', 'modules'));
    }

    public function update(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);
        $currentUser = Auth::user();
        $currentCompanyId = session('current_company_id', $currentUser->company_id);
        
        // Sécurité : vérifier que l'utilisateur appartient à la même compagnie (ou context)
        if ($targetUser->company_id != $currentCompanyId && !$currentUser->isSuperAdmin()) {
            abort(403, 'Action non autorisée sur cet utilisateur.');
        }

        $data = $request->validate([
            'habilitations' => 'nullable|array',
        ]);

        // On sauvegarde les permissions (tableau de chaînes de caractères)
        $targetUser->habilitations = $data['habilitations'] ?? [];
        $targetUser->save();

        // Réponse JSON pour AJAX (si utilisé) ou Redirect
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Habilitations mises à jour.']);
        }

        return back()->with('success', 'Habilitations mises à jour avec succès pour ' . $targetUser->name);
    }
}
