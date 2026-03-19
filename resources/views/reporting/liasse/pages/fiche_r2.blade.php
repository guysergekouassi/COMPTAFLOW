<div class="premium-card p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h5 class="fw-800 text-dark mb-1">FICHE R2 — ACTIVITÉS DE L'ENTITÉ</h5>
            <p class="text-muted small">Détail des activités et nomenclature</p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-label-info">TABLEAU : FR2</span>
        </div>
    </div>

    <div class="mb-5">
        <h6 class="fw-700 text-primary mb-3"><i class="bx bx-list-check me-2"></i>DÉTAIL DE L'ACTIVITÉ (TABLEAU VARIABLE FR2B)</h6>
        <table class="liasse-table">
            <thead>
                <tr>
                    <th style="width: 40%">Activités exercées par l'entité</th>
                    <th class="col-val">Chiffre d'affaires (N)</th>
                    <th class="col-val">Chiffre d'affaires (N-1)</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 1; $i <= 5; $i++)
                <tr>
                    <td><input type="text" name="FR2B_ACT_{{ $i }}" class="liasse-input text-start" value="{{ $data['FR2B_ACT_'.$i] ?? '' }}" placeholder="Activité {{ $i }}..."></td>
                    <td><input type="number" name="FR2B_VAL_N_{{ $i }}" class="liasse-input" value="{{ $data['FR2B_VAL_N_'.$i] ?? '' }}"></td>
                    <td><input type="number" name="FR2B_VAL_N1_{{ $i }}" class="liasse-input" value="{{ $data['FR2B_VAL_N1_'.$i] ?? '' }}"></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-700 text-primary mb-3"><i class="bx bx-category me-2"></i>NOMENCLATURE (FR2C)</h6>
            <table class="liasse-table">
                <tbody>
                    <tr>
                        <td class="col-code">ZC1</td>
                        <td>Secteur d'activité principal</td>
                        <td><input type="text" name="ZC1" class="liasse-input" value="{{ $data['ZC1'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td class="col-code">ZC2</td>
                        <td>Code d'activité (NAF/CITI)</td>
                        <td><input type="text" name="ZC2" class="liasse-input" value="{{ $data['ZC2'] ?? '' }}"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="fw-700 text-primary mb-3"><i class="bx bx-money me-2"></i>TOTAL ACTIVITÉ (FR2D)</h6>
            <table class="liasse-table">
                <tbody>
                    <tr>
                        <td class="col-code">ZD1</td>
                        <td>Total Chiffre d'Affaires N</td>
                        <td><input type="number" name="ZD1" class="liasse-input" value="{{ $data['ZD1'] ?? '' }}"></td>
                    </tr>
                    <tr>
                        <td class="col-code">ZD2</td>
                        <td>Total Chiffre d'Affaires N-1</td>
                        <td><input type="number" name="ZD2" class="liasse-input" value="{{ $data['ZD2'] ?? '' }}"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-end">
        <button class="btn btn-primary fw-700 shadow-sm" onclick="savePageData()">
            <i class="bx bx-save me-2"></i> Enregistrer Fiche R2
        </button>
    </div>
</div>
