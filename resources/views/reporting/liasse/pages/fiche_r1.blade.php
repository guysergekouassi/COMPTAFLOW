@php
    $companyId = session('current_company_id') ?: auth()->user()->company_id;
    $company = \App\Models\Company::find($companyId);
@endphp

<div class="premium-card p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h5 class="fw-800 text-dark mb-1">FICHE R1 — IDENTIFICATION DE L'ENTITÉ</h5>
            <p class="text-muted small">Informations générales et coordonnées fiscales</p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-label-secondary">TABLEAU : FR1</span>
        </div>
    </div>

    <div class="alert bg-label-primary border-0 rounded-4 p-3 mb-4 d-flex align-items-center">
        <i class="bx bx-info-circle fs-3 me-3"></i>
        <div class="small">
            Ces informations sont extraites de la <strong>Configuration de l'Entreprise</strong>. 
            Les modifications effectuées ici seront enregistrées pour cette liasse spécifique.
        </div>
    </div>

    <table class="liasse-table">
        <thead>
            <tr>
                <th class="col-code">CODE</th>
                <th>DÉSIGNATION</th>
                <th class="col-val">VALEUR / RÉPONSE</th>
            </tr>
        </thead>
        <tbody>
            <tr class="row-section">
                <td class="col-code">R1-A</td>
                <td colspan="2">IDENTIFICATION DE L'EXERCICE</td>
            </tr>
            <tr>
                <td class="col-code">ZA1</td>
                <td>Date de début de l'exercice</td>
                <td><input type="text" name="ZA1" class="liasse-input" value="{{ $data['ZA1'] ?? '' }}" placeholder="dd/mm/yyyy"></td>
            </tr>
            <tr>
                <td class="col-code">ZA2</td>
                <td>Date de fin de l'exercice</td>
                <td><input type="text" name="ZA2" class="liasse-input" value="{{ $data['ZA2'] ?? '' }}" placeholder="dd/mm/yyyy"></td>
            </tr>
            <tr>
                <td class="col-code">ZA3</td>
                <td>Durée de l'exercice (en mois)</td>
                <td><input type="number" name="ZA3" class="liasse-input" value="{{ $data['ZA3'] ?? '12' }}"></td>
            </tr>

            <tr class="row-section">
                <td class="col-code">R1-B</td>
                <td colspan="2">IDENTIFICATION DE L'ENTITÉ</td>
            </tr>
            <tr>
                <td class="col-code">ZB1</td>
                <td>Désignation sociale / Nom et Prénoms</td>
                <td><input type="text" name="ZB1" class="liasse-input" value="{{ $data['ZB1'] ?? $company->company_name }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZB2</td>
                <td>N° Compte Contribuable (NCC)</td>
                <td><input type="text" name="ZB2" class="liasse-input" value="{{ $data['ZB2'] ?? $company->ncc }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZB3</td>
                <td>N° RCCM</td>
                <td><input type="text" name="ZB3" class="liasse-input" value="{{ $data['ZB3'] ?? $company->rccm }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZB4</td>
                <td>N° CNPS</td>
                <td><input type="text" name="ZB4" class="liasse-input" value="{{ $data['ZB4'] ?? $company->cnps }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZB5</td>
                <td>Forme Juridique</td>
                <td><input type="text" name="ZB5" class="liasse-input" value="{{ $data['ZB5'] ?? $company->juridique_form }}"></td>
            </tr>

            <tr class="row-section">
                <td class="col-code">R1-C</td>
                <td colspan="2">COORDONNÉES</td>
            </tr>
            <tr>
                <td class="col-code">ZC1</td>
                <td>Siège Social</td>
                <td><input type="text" name="ZC1" class="liasse-input" value="{{ $data['ZC1'] ?? $company->siege_social }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZC2</td>
                <td>Adresse Géographique</td>
                <td><input type="text" name="ZC2" class="liasse-input" value="{{ $data['ZC2'] ?? $company->adresse }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZC3</td>
                <td>Ville</td>
                <td><input type="text" name="ZC3" class="liasse-input" value="{{ $data['ZC3'] ?? $company->city }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZC4</td>
                <td>Numéro de Téléphone</td>
                <td><input type="text" name="ZC4" class="liasse-input" value="{{ $data['ZC4'] ?? $company->phone_number }}"></td>
            </tr>
            <tr>
                <td class="col-code">ZC5</td>
                <td>Adresse Email</td>
                <td><input type="email" name="ZC5" class="liasse-input" value="{{ $data['ZC5'] ?? $company->email_adresse }}"></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4 d-flex justify-content-end">
        <button class="btn btn-primary fw-700 shadow-sm" onclick="savePageData()">
            <i class="bx bx-save me-2"></i> Enregistrer les informations
        </button>
    </div>
</div>
