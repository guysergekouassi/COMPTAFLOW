@php
    function renderNote1Row($ref, $label, $fieldBase, $data) {
        $deb = $data["N1_{$fieldBase}_DEB"] ?? 0;
        $acq = $data["N1_{$fieldBase}_ACQ"] ?? 0;
        $aug = $data["N1_{$fieldBase}_AUG"] ?? 0;
        $ces = $data["N1_{$fieldBase}_CES"] ?? 0;
        $dim = $data["N1_{$fieldBase}_DIM"] ?? 0;
        $fin = $deb + $acq + $aug - $ces - $dim;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N1_{$fieldBase}_DEB' value='{$deb}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N1_{$fieldBase}_ACQ' value='{$acq}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N1_{$fieldBase}_AUG' value='{$aug}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N1_{$fieldBase}_CES' value='{$ces}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N1_{$fieldBase}_DIM' value='{$dim}'></td>";
        echo "<td class='col-val bg-light-blue fw-bold px-3 text-end' id='total_{$fieldBase}'>" . number_format($fin, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #334155; color: white; border: 1px solid #475569; padding: 8px; font-size: 0.65rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #f8fafc; font-weight: 800; color: #334155; }
    .bg-light-blue { background: #f0f9ff; color: #0369a1; }
    .col-label { width: 30%; }
    .col-val { width: 10%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 1 : IMMOBILISATIONS BRUTES</h3>
            <div class="text-muted small mt-1">Tableau des mouvements de l'exercice</div>
        </div>
        <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder la Note
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50px;">REF</th>
                    <th rowspan="2">DESIGNATION DES IMMOBILISATIONS</th>
                    <th rowspan="2">MONTANT BRUT<br>DEBUT EXERCICE</th>
                    <th colspan="2">AUGMENTATIONS</th>
                    <th colspan="2">DIMINUTIONS</th>
                    <th rowspan="2">MONTANT BRUT<br>FIN EXERCICE</th>
                </tr>
                <tr>
                    <th>Acquisitions</th>
                    <th>Autres</th>
                    <th>Cessions</th>
                    <th>Autres</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-section">
                    <td class="text-center">A</td>
                    <td colspan="7">IMMOBILISATIONS INCORPORELLES</td>
                </tr>
                @php
                    renderNote1Row('AE', 'Frais de développement et de prospection', 'AE', $data);
                    renderNote1Row('AF', 'Brevets, licences, logiciels, etc.', 'AF', $data);
                    renderNote1Row('AG', 'Fonds commercial et droit au bail', 'AG', $data);
                @endphp

                <tr class="row-section">
                    <td class="text-center">B</td>
                    <td colspan="7">IMMOBILISATIONS CORPORELLES</td>
                </tr>
                @php
                    renderNote1Row('AJ', 'Terrains', 'AJ', $data);
                    renderNote1Row('AK', 'Bâtiments', 'AK', $data);
                    renderNote1Row('AL', 'Installations techniques, Matériel et Outillage', 'AL', $data);
                    renderNote1Row('AM', 'Matériel de transport', 'AM', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-dark">
        <strong>⚠️ Note :</strong> Les colonnes "Montant Brut Début" et "Augmentations" doivent être saisies manuellement si elles ne sont pas reprises automatiquement de l'année précédente.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const deb = parseFloat(row.find('input[name*="_DEB"]').val()) || 0;
        const acq = parseFloat(row.find('input[name*="_ACQ"]').val()) || 0;
        const aug = parseFloat(row.find('input[name*="_AUG"]').val()) || 0;
        const ces = parseFloat(row.find('input[name*="_CES"]').val()) || 0;
        const dim = parseFloat(row.find('input[name*="_DIM"]').val()) || 0;
        
        const total = deb + acq + aug - ces - dim;
        row.find('[id^="total_"]').text(new Intl.NumberFormat('fr-FR').format(total));
    });
</script>
