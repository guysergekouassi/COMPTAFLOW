<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminCompanyController extends Controller
{
    /**
     * Affiche le formulaire de création d'entreprise
     */
    public function create()
    {
        return view('superadmin.create_company');
    }

    /**
     * Enregistre une nouvelle entreprise
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'activity' => 'required|string|max:255',
            'juridique_form' => 'required|string|max:255',
            'social_capital' => 'required|numeric|min:0',
            'adresse' => 'required|string|max:500',
            'code_postal' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'email_adresse' => 'required|email|max:191|unique:companies,email_adresse',
            'identification_TVA' => 'nullable|string|max:255',
            'parent_company_id' => 'nullable|exists:companies,id',
            'is_active' => 'required|boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $company = Company::create($validated);

        return redirect()->route('superadmin.entities')
            ->with('success', 'Entreprise créée avec succès !');
    }

    /**
     * Affiche le formulaire d'édition d'entreprise
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('superadmin.edit_company', compact('company'));
    }

    /**
     * Met à jour une entreprise
     */
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'activity' => 'required|string|max:255',
            'juridique_form' => 'required|string|max:255',
            'social_capital' => 'required|numeric|min:0',
            'adresse' => 'required|string|max:500',
            'code_postal' => 'required|string|max:20',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'email_adresse' => 'required|email|max:191|unique:companies,email_adresse,' . $id,
            'identification_TVA' => 'nullable|string|max:255',
            'parent_company_id' => 'nullable|exists:companies,id',
            'is_active' => 'required|boolean',
        ]);

        $company->update($validated);

        return redirect()->route('superadmin.entities')
            ->with('success', 'Entreprise mise à jour avec succès !');
    }
}
