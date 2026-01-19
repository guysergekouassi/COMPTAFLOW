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
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'sector' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $company = Company::create([
            'company_name' => $validated['company_name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'is_active' => $validated['status'] === 'active' ? 1 : 0,
        ]);

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
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'sector' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $company->update([
            'company_name' => $validated['company_name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'is_active' => $validated['status'] === 'active' ? 1 : 0,
        ]);

        return redirect()->route('superadmin.entities')
            ->with('success', 'Entreprise mise à jour avec succès !');
    }
}
