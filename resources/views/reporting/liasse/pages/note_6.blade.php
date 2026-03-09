@php
    function renderNote6Row($ref, $label, $fieldBase, $data) {
        $deb = $data["N6_{$fieldBase}_DEB"] ?? 0;
        $aug = $data["N6_{$fieldBase}_AUG"] ?? 0;
        $dim = $data["N6_{$fieldBase}_DIM"] ?? 0;
        $fin = $deb + $aug - $dim;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N6_{$fieldBase}_DEB' value='{$deb}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N6_{$fieldBase}_AUG' value='{$aug}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N6_{$fieldBase}_DIM' value='{$dim}'></td>";
        echo "<td class='col-val bg-light-blue fw-bold px-3 text-end' id='total_n6_{$fieldBase}'>" . number_format($fin, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #1e1b4b; color: white; border: 1px solid #312e81; padding: 8px; font-size: 0.7rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #eef2ff; font-weight: 800; color: #1e1b4b; }
    .bg-light-blue { background: #e0e7ff; color: #1e1b4b; }
    .col-label { width: 45%; }
    .col-val { width: 15%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 6 : IMMOBILISATIONS FINANCIERES</h3>
            <div class="text-muted small mt-1">Tableau des mouvements des immobilisations financières</div>
        </div>
        <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th style="width: 50px;">REF</th>
                    <th>NATURE DES TITRES ET PRÊTS</th>
                    <th>VALEUR BRUTE<br>DEBUT EXERCICE</th>
                    <th>AUGMENTATIONS<br>(Acquisitions)</th>
                    <th>DIMINUTIONS<br>(Cessions/Remb.)</th>
                    <th>VALEUR BRUTE<br>FIN EXERCICE</th>
                </tr>
            </thead>
            <tbody>
                @php
                    renderNote6Row('AP', 'Titres de participation', 'TITRES_PART', $data);
                    renderNote6Row('AQ', 'Titres immobilisés', 'TITRES_IMMO', $data);
                    renderNote6Row('AR', 'Prêts et autres créances financières', 'PRETS', $data);
                    renderNote6Row('AS', 'Dépôts et cautionnements versés', 'DEPOTS', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-indigo">
        Note : Cette note détaille les actifs financiers à long terme de l'entreprise.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const deb = parseFloat(row.find('input[name*="_DEB"]').val()) || 0;
        const aug = parseFloat(row.find('input[name*="_AUG"]').val()) || 0;
        const dim = parseFloat(row.find('input[name*="_DIM"]').val()) || 0;
        
        const total = deb + aug - dim;
        row.find('[id^="total_n6_"]').text(new Intl.NumberFormat('fr-FR').format(total));
    });
</script>
