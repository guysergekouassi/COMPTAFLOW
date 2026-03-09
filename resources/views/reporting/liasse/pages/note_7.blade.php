@php
    function renderNote7Row($ref, $label, $fieldBase, $data) {
        $deb = $data["N7_{$fieldBase}_DEB"] ?? 0;
        $ent = $data["N7_{$fieldBase}_ENT"] ?? 0;
        $sor = $data["N7_{$fieldBase}_SOR"] ?? 0;
        $dep = $data["N7_{$fieldBase}_DEP"] ?? 0;
        
        $brutFin = $deb + $ent - $sor;
        $netFin = $brutFin - $dep;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N7_{$fieldBase}_DEB' value='{$deb}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N7_{$fieldBase}_ENT' value='{$ent}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N7_{$fieldBase}_SOR' value='{$sor}'></td>";
        echo "<td class='col-val bg-light-green text-end px-3' id='brut_{$fieldBase}'>" . number_format($brutFin, 0, ',', ' ') . "</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N7_{$fieldBase}_DEP' value='{$dep}'></td>";
        echo "<td class='col-val bg-light-blue fw-bold px-3 text-end' id='net_{$fieldBase}'>" . number_format($netFin, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #166534; color: white; border: 1px solid #14532d; padding: 6px; font-size: 0.6rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.75rem; }
    .row-section { background: #f0fdf4; font-weight: 800; color: #166534; }
    .bg-light-green { background: #f0fdf4; }
    .bg-light-blue { background: #eff6ff; color: #1e40af; }
    .col-label { width: 20%; }
    .col-val { width: 11%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 7 : STOCKS</h3>
            <div class="text-muted small mt-1">Tableau des mouvements de stocks de l'exercice</div>
        </div>
        <button class="btn btn-success btn-sm rounded-pill px-4 shadow-sm" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50px;">REF</th>
                    <th rowspan="2">NATURE DES STOCKS</th>
                    <th rowspan="2">VALEUR BRUTE<br>DEBUT EXERCICE</th>
                    <th rowspan="2">ENTREES /<br>AUGMENTATIONS</th>
                    <th rowspan="2">SORTIES /<br>DIMINUTIONS</th>
                    <th rowspan="2">VALEUR BRUTE<br>FIN EXERCICE</th>
                    <th rowspan="2">DEPRECIATIONS</th>
                    <th rowspan="2">VALEUR NETTE<br>FIN EXERCICE</th>
                </tr>
            </thead>
            <tbody>
                @php
                    renderNote7Row('BA', 'Marchandises', 'MARCHANDISES', $data);
                    renderNote7Row('BB', 'Matières premières et fournitures liées', 'MATIERES', $data);
                    renderNote7Row('BC', 'Autres approvisionnements', 'AUTRES_APPRO', $data);
                    renderNote7Row('BD', 'En-cours : produits', 'EN_COURS_PROD', $data);
                    renderNote7Row('BE', 'En-cours : services', 'EN_COURS_SERV', $data);
                    renderNote7Row('BF', 'Produits finis', 'PROD_FINIS', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-success">
        Note : La valeur nette fin d'exercice doit correspondre au montant figurant au bilan actif.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const deb = parseFloat(row.find('input[name*="_DEB"]').val()) || 0;
        const ent = parseFloat(row.find('input[name*="_ENT"]').val()) || 0;
        const sor = parseFloat(row.find('input[name*="_SOR"]').val()) || 0;
        const dep = parseFloat(row.find('input[name*="_DEP"]').val()) || 0;
        
        const brutFin = deb + ent - sor;
        const netFin = brutFin - dep;
        
        row.find('[id^="brut_"]').text(new Intl.NumberFormat('fr-FR').format(brutFin));
        row.find('[id^="net_"]').text(new Intl.NumberFormat('fr-FR').format(netFin));
    });
</script>
