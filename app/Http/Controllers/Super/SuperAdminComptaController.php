<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ComptaAccount;
use Illuminate\Http\Request;

class SuperAdminComptaController extends Controller
{
    /**
     * Affiche la liste des comptabilités
     */
    public function index()
    {
        $comptaAccounts = ComptaAccount::with('company')->get();
        return view('superadmin.accounting_list', compact('comptaAccounts'));
    }

    /**
     * Affiche le formulaire de création de comptabilité
     */
    public function create()
    {
        $companies = Company::where('is_active', 1)->get();
        return view('superadmin.create_accounting', compact('companies'));
    }

    /**
     * Enregistre une nouvelle comptabilité
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|string|max:100',
            'fiscal_year_start' => 'required|date',
            'fiscal_year_end' => 'required|date|after:fiscal_year_start',
        ]);

        $comptaAccount = ComptaAccount::create([
            'company_id' => $validated['company_id'],
            'account_name' => $validated['account_name'],
            'account_type' => $validated['account_type'],
            'fiscal_year_start' => $validated['fiscal_year_start'],
            'fiscal_year_end' => $validated['fiscal_year_end'],
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.accounting.index')
            ->with('success', 'Comptabilité créée avec succès !');
    }
}
