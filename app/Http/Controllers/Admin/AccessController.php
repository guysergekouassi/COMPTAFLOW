<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessController extends Controller
{
    /**
     * Entreprise sur laquelle on travaille réellement (contexte de session,
     * pas l'entreprise du profil : les deux diffèrent en mode switch).
     */
    private function currentCompany(): ?Company
    {
        $user = Auth::user();

        return Company::with(['users', 'associatedUsers', 'admin'])
            ->find(session('current_company_id', $user->company_id));
    }

    public function index()
    {
        $company = $this->currentCompany();

        // Cloisonnement : une entreprise ne voit que ses propres membres.
        $users = $company
            ? $company->membres()->where('role', '!=', 'super_admin')->values()
            : collect();

        return view('admin.access.index', compact('users'));
    }

    public function toggleUser($id)
    {
        $company = $this->currentCompany();

        // On ne peut bloquer/débloquer qu'un membre de son entreprise.
        $user = $company ? $company->membres()->firstWhere('id', (int) $id) : null;

        if (!$user || $user->role === 'super_admin') {
            abort(403, "Cet utilisateur n'est pas rattaché à votre entreprise.");
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'bloqué' : 'débloqué';
        return back()->with('success', "L'utilisateur a été $status avec succès.");
    }
}
