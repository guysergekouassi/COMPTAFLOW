<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\User;

class SwitchController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Entreprises reellement gerees : la sienne, celles qu'il a creees et
        // celles auxquelles il est affecte.
        // ATTENTION : un orWhere('parent_company_id', $user->company_id) avec un
        // company_id nul se traduisait par "parent_company_id IS NULL", ce qui
        // remontait TOUTES les entreprises siege de la plateforme.
        $companyIds = collect();

        if ($user->company_id) {
            $companyIds->push($user->company_id);
            $companyIds = $companyIds->merge(
                Company::where('parent_company_id', $user->company_id)->pluck('id')
            );
        }

        $companyIds = $companyIds
            ->merge(Company::where('user_id', $user->id)->pluck('id'))
            ->merge(DB::table('company_user')->where('user_id', $user->id)->pluck('company_id'))
            ->filter()
            ->unique()
            ->values();

        $managedCompanies = Company::whereIn('id', $companyIds)
            ->orderBy('company_name')
            ->get();

        // Récupérer les utilisateurs associés à ces entreprises (comptables, etc.)
        // On exclut l'admin lui-même pour ne pas switcher sur soi-même inutilement
        $managedUsers = User::whereIn('company_id', $managedCompanies->pluck('id'))
            ->where('id', '!=', $user->id) 
            ->get();

        return view('admin.switch.index', compact('managedCompanies', 'managedUsers'));
    }
}
