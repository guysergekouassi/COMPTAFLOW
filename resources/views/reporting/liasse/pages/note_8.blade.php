@php
    function renderNote8Row($ref, $label, $fieldBase, $data) {
        $brut = $data["N8_{$fieldBase}_BRUT"] ?? 0;
        $dep = $data["N8_{$fieldBase}_DEP"] ?? 0;
        $net = $brut - $dep;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>{$label}</td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N8_{$fieldBase}_BRUT' value='{$brut}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='form-control form-control-sm text-end border-0 bg-transparent liasse-input' name='N8_{$fieldBase}_DEP' value='{$dep}'></td>";
        echo "<td class='col-val bg-light-blue fw-bold px-3 text-end' id='total_n8_{$fieldBase}'>" . number_format($net, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .liasse-table th { background: #0369a1; color: white; border: 1px solid #075985; padding: 8px; font-size: 0.7rem; text-transform: uppercase; text-align: center; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #f0f9ff; font-weight: 800; color: #0369a1; }
    .bg-light-blue { background: #e0f2fe; color: #0369a1; }
    .col-label { width: 45%; }
    .col-val { width: 15%; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">NOTE 8 : CLIENTS ET AUTRES CREANCES</h3>
            <div class="text-muted small mt-1">Détail des créances de l'actif circulant</div>
        </div>
        <button class="btn btn-info btn-sm rounded-pill px-4 shadow-sm text-white" onclick="savePageData()">
            <i class="bx bxs-save me-1"></i> Sauvegarder
        </button>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th style="width: 50px;">REF</th>
                    <th>NATURE DES CREANCES</th>
                    <th>MONTANT BRUT</th>
                    <th>DEPRECIATIONS</th>
                    <th>MONTANT NET</th>
                </tr>
            </thead>
            <tbody>
                @php
                    renderNote8Row('BA', 'Clients', 'CLIENTS', $data);
                    renderNote8Row('BB', 'Clients, effets à recevoir', 'CLIENTS_EFFETS', $data);
                    renderNote8Row('BC', 'Personnel', 'PERSONNEL', $data);
                    renderNote8Row('BD', 'État et collectivités publiques', 'ETAT', $data);
                    renderNote8Row('BE', 'Associés et Groupe', 'ASSOCIES', $data);
                    renderNote8Row('BF', 'Débiteurs divers', 'DEBITEURS_DIVERS', $data);
                    renderNote8Row('BG', 'Créances sur hauts fonds (H.A.O.)', 'CREANCES_HAO', $data);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-info">
        Note : Cette note doit concorder avec les soldes des comptes de tiers à la clôture.
    </div>
</div>

<script>
    $('.liasse-input').on('input', function() {
        const row = $(this).closest('tr');
        const brut = parseFloat(row.find('input[name*="_BRUT"]').val()) || 0;
        const dep = parseFloat(row.find('input[name*="_DEP"]').val()) || 0;
        
        const total = brut - dep;
        row.find('[id^="total_n8_"]').text(new Intl.NumberFormat('fr-FR').format(total));
    });
</script>
