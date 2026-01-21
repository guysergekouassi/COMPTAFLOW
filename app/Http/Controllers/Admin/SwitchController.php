<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\User;

class SwitchController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les entreprises gérées par l'admin (Principale + Sous-comptes)
        $managedCompanies = Company::where('id', $user->company_id)
            ->orWhere('parent_company_id', $user->company_id)
            ->get();

        // Récupérer les utilisateurs associés à ces entreprises (comptables, etc.)
        // On exclut l'admin lui-même pour ne pas switcher sur soi-même inutilement
        $managedUsers = User::whereIn('company_id', $managedCompanies->pluck('id'))
            ->where('id', '!=', $user->id) 
            ->get();

        return view('admin.switch.index', compact('managedCompanies', 'managedUsers'));
    }
}
