@php
    function renderNote2Row($ref, $label, $fieldBase, $data) {
        $deb = $data["N2_{$fieldBase}_DEB"] ?? 0;
        $dot = $data["N2_{$fieldBase}_DOT"] ?? 0;
        $aug = $data["N2_{$fieldBase}_AUG"] ?? 0;
        $ces = $data["N2_{$fieldBase}_CES"] ?? 0;
        $dim = $data["N2_{$fieldBase}_DIM"] ?? 0;
        $fin = $deb + $dot + $aug - $ces - $dim;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N2_{$fieldBase}_DEB' value='{$deb}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N2_{$fieldBase}_DOT' value='{$dot}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N2_{$fieldBase}_AUG' value='{$aug}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N2_{$fieldBase}_CES' value='{$ces}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N2_{$fieldBase}_DIM' value='{$dim}'></td>";
        echo "<td class='col-val bg-light-orange fw-bold px-3 text-end' id='total_n2_{$fieldBase}'>" . number_format($fin, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #475569; color: white; border: 1px solid #64748b; padding: 8px; font-size: 0.65rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #f8fafc; font-weight: 800; color: #475569; }
    .bg-light-orange { background: #fff7ed; color: #c2410c; }
    .col-label { width: 30%; }
    .col-val { width: 10%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 2 : AMORTISSEMENTS</h3>
            <div class="text-muted small mt-1">Évolution des amortissements de l'exercice</div>
        </div>
        <button class="btn btn-info btn-sm rounded-pill px-4 shadow-sm text-white" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder la Note
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50px;">REF</th>
                    <th rowspan="2">NATURE DES IMMOBILISATIONS</th>
                    <th rowspan="2">AMORTISSEMENTS<br>DEBUT EXERCICE</th>
                    <th colspan="2">AUGMENTATIONS</th>
                    <th colspan="2">DIMINUTIONS</th>
                    <th rowspan="2">AMORTISSEMENTS<br>FIN EXERCICE</th>
                </tr>
                <tr>
                    <th>Dotations</th>
                    <th>Autres</th>
                    <th>Cessions</th>
                    <th>Autres</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-section">
                    <td class="text-center">-</td>
                    <td colspan="7">IMMOBILISATIONS INCORPORELLES</td>
                </tr>
                @php
                    renderNote2Row('AE', 'Frais de développement et de prospection', 'AE', $data);
                    renderNote2Row('AF', 'Brevets, licences, logiciels, etc.', 'AF', $data);
                @endphp

                <tr class="row-section">
                    <td class="text-center">-</td>
                    <td colspan="7">IMMOBILISATIONS CORPORELLES</td>
                </tr>
                @php
                    renderNote2Row('AJ', 'Terrains', 'AJ', $data);
                    renderNote2Row('AK', 'Bâtiments', 'AK', $data);
                    renderNote2Row('AL', 'Installations techniques, Matériel et Outillage', 'AL', $data);
                    renderNote2Row('AM', 'Matériel de transport', 'AM', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-info">
        <strong>⚠️ Note :</strong> Les dotations de l'exercice doivent correspondre aux charges d'amortissement enregistrées au compte de résultat.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const deb = parseFloat(row.find('input[name*="_DEB"]').val()) || 0;
        const dot = parseFloat(row.find('input[name*="_DOT"]').val()) || 0;
        const aug = parseFloat(row.find('input[name*="_AUG"]').val()) || 0;
        const ces = parseFloat(row.find('input[name*="_CES"]').val()) || 0;
        const dim = parseFloat(row.find('input[name*="_DIM"]').val()) || 0;
        
        const total = deb + dot + aug - ces - dim;
        row.find('[id^="total_n2_"]').text(new Intl.NumberFormat('fr-FR').format(total));
    });
</script>
