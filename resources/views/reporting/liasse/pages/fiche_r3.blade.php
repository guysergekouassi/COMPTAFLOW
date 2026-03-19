<div class="premium-card p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h5 class="fw-800 text-dark mb-1">FICHE R3 — DIRIGEANTS ET CONSEIL D'ADMINISTRATION</h5>
            <p class="text-muted small">Gouvernance de l'entité</p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-label-success">TABLEAU : FR3</span>
        </div>
    </div>

    <div class="mb-5">
        <h6 class="fw-700 text-primary mb-3"><i class="bx bx-user-voice me-2"></i>PRINCIPAUX DIRIGEANTS (FR3A)</h6>
        <table class="liasse-table">
            <thead>
                <tr>
                    <th>Nom et prénoms / Désignation sociale</th>
                    <th>N° NCC</th>
                    <th>Dexté (Poste)</th>
                    <th>Adresse Complète</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 1; $i <= 5; $i++)
                <tr>
                    <td><input type="text" name="FR3A_NOM_{{ $i }}" class="liasse-input text-start" value="{{ $data['FR3A_NOM_'.$i] ?? '' }}"></td>
                    <td><input type="text" name="FR3A_NCC_{{ $i }}" class="liasse-input" value="{{ $data['FR3A_NCC_'.$i] ?? '' }}"></td>
                    <td><input type="text" name="FR3A_POSTE_{{ $i }}" class="liasse-input" value="{{ $data['FR3A_POSTE_'.$i] ?? '' }}"></td>
                    <td><input type="text" name="FR3A_ADR_{{ $i }}" class="liasse-input" value="{{ $data['FR3A_ADR_'.$i] ?? '' }}"></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div>
        <h6 class="fw-700 text-primary mb-3"><i class="bx bx-group me-2"></i>MEMBRES DU CONSEIL D'ADMINISTRATION (FR3B)</h6>
        <table class="liasse-table">
            <thead>
                <tr>
                    <th>Nom et prénoms / Désignation sociale</th>
                    <th>N° NCC</th>
                    <th>Qualité</th>
                    <th>Adresse Complète</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 1; $i <= 5; $i++)
                <tr>
                    <td><input type="text" name="FR3B_NOM_{{ $i }}" class="liasse-input text-start" value="{{ $data['FR3B_NOM_'.$i] ?? '' }}"></td>
                    <td><input type="text" name="FR3B_NCC_{{ $i }}" class="liasse-input" value="{{ $data['FR3B_NCC_'.$i] ?? '' }}"></td>
                    <td><input type="text" name="FR3B_QUAL_{{ $i }}" class="liasse-input" value="{{ $data['FR3B_QUAL_'.$i] ?? '' }}"></td>
                    <td><input type="text" name="FR3B_ADR_{{ $i }}" class="liasse-input" value="{{ $data['FR3B_ADR_'.$i] ?? '' }}"></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex justify-content-end">
        <button class="btn btn-primary fw-700 shadow-sm" onclick="savePageData()">
            <i class="bx bx-save me-2"></i> Enregistrer Fiche R3
        </button>
    </div>
</div>
