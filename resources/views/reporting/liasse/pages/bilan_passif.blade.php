@php
    function renderPassifRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $valN = $data[$fieldBase] ?? 0;
        $valN1 = $data[$fieldBase . '_N1'] ?? 0;
        $details = $data[$fieldBase . '_details'] ?? [];
        $isExport = isset($GLOBALS['isExport']) ? $GLOBALS['isExport'] : (isset($viewData['isExport']) ? $viewData['isExport'] : false);
        
        $rowClass = $isTotal ? 'row-total' : '';
        $hasDetails = count($details) > 0 && !$isTotal;
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='col-code'>{$ref}</td>";
        
        echo "<td>";
        if ($hasDetails && !$isExport) {
            echo "<button class='btn btn-sm btn-light py-0 px-2 text-primary border-0 me-2 shadow-none toggle-btn' type='button' data-target-row='detail_{$fieldBase}'>";
            echo "<i class='bx bx-chevron-right'></i></button>";
        }
        echo ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        
        $noteVal = $data['note_'.$fieldBase] ?? $noteCode;
        if ($isExport) {
            echo "<td class='text-center'>{$noteVal}</td>";
            echo "<td class='col-val'>" . ($valN != 0 ? number_format($valN, 0, ',', ' ') : '-') . "</td>";
        } else {
            echo "<td class='text-center'><input type='text' class='liasse-input text-center p-1' name='note_{$fieldBase}' value='{$noteVal}'></td>";
            if ($isTotal) {
                echo "<td class='col-val text-primary fw-800'>" . ($valN != 0 ? number_format($valN, 0, ',', ' ') : '-') . "</td>";
            } else {
                echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input text-primary fw-800' name='{$fieldBase}' value='{$valN}'></td>";
            }
        }
        
        echo "<td class='col-val text-secondary'>" . ($valN1 != 0 ? number_format($valN1, 0, ',', ' ') : '-') . "</td>";
        echo "</tr>";

        if ($hasDetails && !$isExport) {
            echo "<tr id='detail_{$fieldBase}' style='display: none;'><td colspan='7' class='p-0 border-0'>";
            echo "<div class='bg-slate-50 border-bottom p-3 shadow-inner' style='box-shadow: inset 0 2px 4px rgba(0,0,0,0.02)'>";
            echo "<table class='table table-sm table-borderless mb-0' style='font-size: 0.8rem; background: transparent;'>";
            foreach($details as $d) {
                echo "<tr><td style='width:60px'></td>";
                echo "<td class='text-slate-600' style='width:40%'><strong>{$d['numero']}</strong> - {$d['intitule']}</td>";
                echo "<td class='text-end font-mono text-slate-800 fw-bold'>" . number_format($d['solde'], 0, ',', ' ') . "</td>";
                echo "<td colspan='4'></td></tr>";
            }
            echo "</table></div></td></tr>";
        }
    }
@endphp

<script>
    // Ajout d'une petite animation de rotation pour les icônes chevron
    $('.toggle-btn').off('click').on('click', function() {
        $(this).find('i').toggleClass('bx-chevron-right bx-chevron-down');
        var target = $(this).attr('data-target-row');
        $('#' + target).toggle();
    });
</script>

<div class="premium-card">
    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-code">REF</th>
                    <th rowspan="2">PASSIF</th>
                    <th rowspan="2" style="width: 70px;" class="text-center">NOTE</th>
                    <th class="text-center">EXERCICE AU 31/12/ N</th>
                    <th class="text-center">EXERCICE AU 31/12/ N-1</th>
                </tr>
                <tr>
                    <th class="text-center">NET</th>
                    <th class="text-center">NET</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-section">
                    <td class="col-code">C</td>
                    <td colspan="4">CAPITAUX PROPRES ET RESSOURCES ASSIMILEES</td>
                </tr>
                @php
                    renderPassifRow('CA', 'Capital', '', 'CA', $data);
                    renderPassifRow('CF', 'Réserves', '', 'CF', $data);
                    renderPassifRow('CG', 'Report à nouveau (+ ou -)', '', 'CG', $data);
                    renderPassifRow('CJ', 'Résultat net de l\'exercice (Bénéfice + ou Perte -)', '', 'CJ', $data);
                    renderPassifRow('CP', 'TOTAL CAPITAUX PROPRES', '', 'CP', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">D</td>
                    <td colspan="4">DETTES FINANCIERES ET RESSOURCES ASSIMILEES</td>
                </tr>
                @php
                    renderPassifRow('DA', 'Emprunts et dettes financières diverses', '16', 'DA', $data);
                    renderPassifRow('DP', 'TOTAL DETTES FINANCIERES', '', 'DP', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">F</td>
                    <td colspan="4">PASSIF CIRCULANT</td>
                </tr>
                @php
                    renderPassifRow('FB', 'Fournisseurs d\'exploitation', '17', 'FB', $data);
                    renderPassifRow('FG', 'TOTAL PASSIF CIRCULANT', '', 'FG', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">H</td>
                    <td colspan="4">TRESORERIE-PASSIF</td>
                </tr>
                @php
                    renderPassifRow('HA', 'Banques, crédits d\'escompte et de trésorerie', '16', 'HA', $data);
                    renderPassifRow('HP', 'TOTAL TRESORERIE-PASSIF', '', 'HP', $data, true);
                    renderPassifRow('GZ', 'TOTAL GENERAL PASSIF', '', 'GZ', $data, true);
                @endphp
            </tbody>
        </table>
    </div>
</div>
