@php
    function renderNote3Row($code, $label, $data) {
        $prix = $data["N3_{$code}_PRIX"] ?? 0;
        $vnc = $data["N3_{$code}_VNC"] ?? 0;
        $diff = $prix - $vnc;
        $plus = $diff > 0 ? $diff : 0;
        $moins = $diff < 0 ? abs($diff) : 0;
        
        echo "<tr>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N3_{$code}_PRIX' value='{$prix}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N3_{$code}_VNC' value='{$vnc}'></td>";
        echo "<td class='col-val bg-light-green fw-bold text-success text-end px-3' id='plus_{$code}'>" . ($plus > 0 ? number_format($plus, 0, ',', ' ') : '-') . "</td>";
        echo "<td class='col-val bg-light-red fw-bold text-danger text-end px-3' id='moins_{$code}'>" . ($moins > 0 ? number_format($moins, 0, ',', ' ') : '-') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #334155; color: white; border: 1px solid #475569; padding: 8px; font-size: 0.7rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .bg-light-green { background: #f0fdf4; }
    .bg-light-red { background: #fef2f2; }
    .col-label { width: 40%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 3 : PLUS-VALUES ET MOINS-VALUES DE CESSION</h3>
            <div class="text-muted small mt-1">Détail des cessions d'immobilisations</div>
        </div>
        <button class="btn btn-warning btn-sm rounded-pill px-4 shadow-sm text-white" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th>NATURE DES IMMOBILISATIONS</th>
                    <th>PRIX DE CESSION (1)</th>
                    <th>VNC (2)</th>
                    <th>PLUS-VALUES (1-2) > 0</th>
                    <th>MOINS-VALUES (1-2) < 0</th>
                </tr>
            </thead>
            <tbody>
                @php
                    renderNote3Row('AE', 'Frais de développement et de prospection', $data);
                    renderNote3Row('AF', 'Brevets, licences, logiciels, etc.', $data);
                    renderNote3Row('AJ', 'Terrains', $data);
                    renderNote3Row('AK', 'Bâtiments', $data);
                    renderNote3Row('AL', 'Installations techniques, Matériel et Outillage', $data);
                    renderNote3Row('AM', 'Matériel de transport', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-warning">
        Note : Les plus-values et moins-values sont calculées automatiquement par différence entre le prix de cession et la valeur nette comptable (VNC).
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const prix = parseFloat(row.find('input[name*="_PRIX"]').val()) || 0;
        const vnc = parseFloat(row.find('input[name*="_VNC"]').val()) || 0;
        const diff = prix - vnc;
        
        row.find('[id^="plus_"]').text(diff > 0 ? new Intl.NumberFormat('fr-FR').format(diff) : '-');
        row.find('[id^="moins_"]').text(diff < 0 ? new Intl.NumberFormat('fr-FR').format(Math.abs(diff)) : '-');
    });
</script>
