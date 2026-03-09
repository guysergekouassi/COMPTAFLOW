@php
    function renderNote9Row($code, $label, $data) {
        $acq = $data["N9_{$code}_ACQ"] ?? 0;
        $inv = $data["N9_{$code}_INV"] ?? 0;
        $dep = $data["N9_{$code}_DEP"] ?? 0;
        $net = $acq - $dep;
        
        echo "<tr>";
        echo "<td class='fw-bold text-dark'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N9_{$code}_ACQ' value='{$acq}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N9_{$code}_INV' value='{$inv}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N9_{$code}_DEP' value='{$dep}'></td>";
        echo "<td class='col-val bg-light-warning fw-bold text-dark text-end px-3' id='net_{$code}'>" . number_format($net, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #854d0e; color: white; border: 1px solid #a16207; padding: 8px; font-size: 0.7rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .bg-light-warning { background: #fefce8; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 9 : TITRES DE PLACEMENT</h3>
            <div class="text-muted small mt-1">Détail des titres de placement à court terme</div>
        </div>
        <button class="btn btn-warning btn-sm rounded-pill px-4 shadow-sm text-white" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th>NATURE DES TITRES</th>
                    <th>VALEUR D'ACQUISITION</th>
                    <th>VALEUR BOURSIERE / INVENTAIRE</th>
                    <th>DEPRECIATIONS</th>
                    <th>VALEUR NETTE</th>
                </tr>
            </thead>
            <tbody>
                @php
                    renderNote9Row('ACTIONS', 'Actions', $data);
                    renderNote9Row('OBLIGATIONS', 'Obligations', $data);
                    renderNote9Row('BONS_TRESOR', 'Bons du Trésor', $data);
                    renderNote9Row('AUTRES', 'Autres titres de placement', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-warning">
        Note : Les dépréciations sont constatées si la valeur d'inventaire est inférieure à la valeur d'acquisition.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const acq = parseFloat(row.find('input[name*="_ACQ"]').val()) || 0;
        const dep = parseFloat(row.find('input[name*="_DEP"]').val()) || 0;
        const net = acq - dep;
        
        row.find('[id^="net_"]').text(new Intl.NumberFormat('fr-FR').format(net));
    });
</script>
