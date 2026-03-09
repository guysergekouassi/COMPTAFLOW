@php
    function renderNote4Row($ref, $label, $fieldBase, $data) {
        $deb = $data["N4_{$fieldBase}_DEB"] ?? 0;
        $dot_expl = $data["N4_{$fieldBase}_DOT_EXPL"] ?? 0;
        $dot_hao = $data["N4_{$fieldBase}_DOT_HAO"] ?? 0;
        $rep_expl = $data["N4_{$fieldBase}_REP_EXPL"] ?? 0;
        $rep_hao = $data["N4_{$fieldBase}_REP_HAO"] ?? 0;
        $fin = $deb + $dot_expl + $dot_hao - $rep_expl - $rep_hao;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N4_{$fieldBase}_DEB' value='{$deb}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N4_{$fieldBase}_DOT_EXPL' value='{$dot_expl}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N4_{$fieldBase}_DOT_HAO' value='{$dot_hao}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N4_{$fieldBase}_REP_EXPL' value='{$rep_expl}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N4_{$fieldBase}_REP_HAO' value='{$rep_hao}'></td>";
        echo "<td class='col-val bg-light-red fw-bold px-3 text-end' id='total_n4_{$fieldBase}'>" . number_format($fin, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #b91c1c; color: white; border: 1px solid #991b1b; padding: 8px; font-size: 0.65rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #fef2f2; font-weight: 800; color: #b91c1c; }
    .bg-light-red { background: #fee2e2; color: #b91c1c; }
    .col-label { width: 25%; }
    .col-val { width: 10%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 4 : PROVISIONS ET DEPRECIATIONS</h3>
            <div class="text-muted small mt-1">Tableau des provisions et dépréciations de l'exercice</div>
        </div>
        <button class="btn btn-danger btn-sm rounded-pill px-4 shadow-sm" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50px;">REF</th>
                    <th rowspan="2">NATURE DES PROVISIONS / DEPRECIATIONS</th>
                    <th rowspan="2">MONTANT AU DEBUT<br>DE L'EXERCICE</th>
                    <th colspan="2">DOTATIONS</th>
                    <th colspan="2">REPRISES</th>
                    <th rowspan="2">MONTANT EN FIN<br>DE L'EXERCICE</th>
                </tr>
                <tr>
                    <th>Exploitation</th>
                    <th>H.A.O.</th>
                    <th>Exploitation</th>
                    <th>H.A.O.</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-section">
                    <td class="text-center">-</td>
                    <td colspan="7">PROVISIONS</td>
                </tr>
                @php
                    renderNote4Row('AL', 'Provisions pour risques et charges', 'PROV_RISQUE', $data);
                @endphp

                <tr class="row-section">
                    <td class="text-center">-</td>
                    <td colspan="7">DEPRECIATIONS</td>
                </tr>
                @php
                    renderNote4Row('AE', 'Dépréciations des immobilisations', 'DEP_IMMO', $data);
                    renderNote4Row('AF', 'Dépréciations des stocks', 'DEP_STOCK', $data);
                    renderNote4Row('AG', 'Dépréciations des comptes tiers', 'DEP_TIERS', $data);
                    renderNote4Row('AH', 'Dépréciations des comptes de trésorerie', 'DEP_TRESO', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-danger">
        Note : Les dotations et reprises doivent être conformes aux écritures de fin d'exercice.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const deb = parseFloat(row.find('input[name*="_DEB"]').val()) || 0;
        const dot_expl = parseFloat(row.find('input[name*="_DOT_EXPL"]').val()) || 0;
        const dot_hao = parseFloat(row.find('input[name*="_DOT_HAO"]').val()) || 0;
        const rep_expl = parseFloat(row.find('input[name*="_REP_EXPL"]').val()) || 0;
        const rep_hao = parseFloat(row.find('input[name*="_REP_HAO"]').val()) || 0;
        
        const total = deb + dot_expl + dot_hao - rep_expl - rep_hao;
        row.find('[id^="total_n4_"]').text(new Intl.NumberFormat('fr-FR').format(total));
    });
</script>
