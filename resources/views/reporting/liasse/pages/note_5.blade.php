@php
    function renderNote5Row($ref, $label, $fieldBase, $data) {
        $val_origine = $data["N5_{$fieldBase}_VAL"] ?? 0;
        $duree = $data["N5_{$fieldBase}_DUREE"] ?? 0;
        $red_deb = $data["N5_{$fieldBase}_RED_DEB"] ?? 0;
        $red_ex = $data["N5_{$fieldBase}_RED_EX"] ?? 0;
        $red_reste = $data["N5_{$fieldBase}_RED_RESTE"] ?? 0;
        $prix_achat = $data["N5_{$fieldBase}_PRIX_ACHAT"] ?? 0;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N5_{$fieldBase}_VAL' value='{$val_origine}'></td>";
        echo "<td class='col-val text-center'><input type='number' class='form-control form-control-sm text-center border-0 bg-transparent liasse-input' name='N5_{$fieldBase}_DUREE' value='{$duree}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N5_{$fieldBase}_RED_DEB' value='{$red_deb}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N5_{$fieldBase}_RED_EX' value='{$red_ex}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N5_{$fieldBase}_RED_RESTE' value='{$red_reste}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N5_{$fieldBase}_PRIX_ACHAT' value='{$prix_achat}'></td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; table-layout: fixed; }
    .liasse-table th { background: #4b5563; color: white; border: 1px solid #374151; padding: 8px; font-size: 0.6rem; text-transform: uppercase; text-align: center; font-weight: 800; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.75rem; }
    .row-section { background: #f9fafb; font-weight: 800; color: #111827; }
    .col-label { width: 25%; }
    .col-val { width: 10%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0 uppercase">NOTE 5 : ACTIF IMMOBILISE - ENGAGEMENTS DE CREDIT-BAIL</h3>
            <div class="text-muted small mt-1">Détail des contrats de crédit-bail et contrats assimilés</div>
        </div>
        <button class="btn btn-secondary btn-sm rounded-pill px-4 shadow-sm" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th style="width: 50px;">REF</th>
                    <th>NATURE DES BIENS</th>
                    <th>VALEUR D'ORIGINE</th>
                    <th>DUREE<br>(Mois)</th>
                    <th>REDEVANCES<br>CUMUL DEBUT</th>
                    <th>REDEVANCES<br>EXERCICE</th>
                    <th>REDEVANCES<br>RESTE A COURIR</th>
                    <th>PRIX ACHAT<br>RESIDUEL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    renderNote5Row('AE', 'Terrains', 'TERRAINS', $data);
                    renderNote5Row('AK', 'Bâtiments', 'BATIMENTS', $data);
                    renderNote5Row('AM', 'Matériel de transport', 'TRANSPORT', $data);
                    renderNote5Row('AN', 'Autres immobilisations', 'AUTRES', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-secondary">
        Note : Cette note concerne les contrats de location-acquisition retraités ou non selon les normes SYSCOHADA.
    </div>
</div>
