@php
    function renderActifRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $brut = $data[$fieldBase . '_brut'] ?? 0;
        $amort = $data[$fieldBase . '_amort'] ?? 0;
        $net = $data[$fieldBase . '_net'] ?? 0;
        $netN1 = $data[$fieldBase . '_net_N1'] ?? 0;
        $details = $data[$fieldBase . '_details'] ?? [];
        $isExport = isset($GLOBALS['isExport']) ? $GLOBALS['isExport'] : (isset($viewData['isExport']) ? $viewData['isExport'] : false);
        
        $rowClass = $isTotal ? 'row-total' : '';
        $hasDetails = count($details) > 0 && !$isTotal;
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='col-code'>{$ref}</td>";
        
        echo "<td>";
        if ($hasDetails && !$isExport) {
            echo "<button class='btn btn-sm btn-light py-0 px-2 text-primary border-0 me-2 shadow-none toggle-btn' type='button' data-bs-toggle='collapse' data-bs-target='#detail_{$fieldBase}'>";
            echo "<i class='bx bx-chevron-right'></i></button>";
        }
        echo ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        
        $noteVal = $data['note_'.$fieldBase] ?? $noteCode;
        if ($isExport) {
            echo "<td class='text-center'>{$noteVal}</td>";
            echo "<td class='col-val'>" . ($brut != 0 ? number_format($brut, 0, ',', ' ') : '-') . "</td>";
            echo "<td class='col-val'>" . ($amort != 0 ? number_format($amort, 0, ',', ' ') : '-') . "</td>";
        } else {
            echo "<td class='text-center'><input type='text' class='liasse-input text-center p-1' name='note_{$fieldBase}' value='{$noteVal}'></td>";
            if ($isTotal) {
                echo "<td class='col-val'></td><td class='col-val'></td>";
            } else {
                echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input' name='{$fieldBase}_brut' value='{$brut}'></td>";
                echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input' name='{$fieldBase}_amort' value='{$amort}'></td>";
            }
        }
        
        echo "<td class='col-val text-primary fw-800'>" . ($net != 0 ? number_format($net, 0, ',', ' ') : '-') . "</td>";
        echo "<td class='col-val text-secondary'>" . ($netN1 != 0 ? number_format($netN1, 0, ',', ' ') : '-') . "</td>";
        echo "</tr>";

        if ($hasDetails && !$isExport) {
            echo "<tr class='collapse' id='detail_{$fieldBase}'><td colspan='7' class='p-0'>";
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
    $('.toggle-btn').on('click', function() {
        $(this).find('i').toggleClass('bx-chevron-right bx-chevron-down');
    });
</script>

<div class="premium-card">
    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-code">REF</th>
                    <th rowspan="2">ACTIF (1)</th>
                    <th rowspan="2" style="width: 70px;" class="text-center">NOTE</th>
                    <th colspan="3" class="text-center border-bottom-0">EXERCICE AU 31/12/ N</th>
                    <th class="text-center">EXERCICE AU 31/12/ N-1</th>
                </tr>
                <tr>
                    <th class="text-center">BRUT</th>
                    <th class="text-center">AMORT / DEPREC.</th>
                    <th class="text-center">NET</th>
                    <th class="text-center">NET</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-section">
                    <td class="col-code">AD</td>
                    <td colspan="6">IMMOBILISATIONS INCORPORELLES</td>
                </tr>
                @php
                    renderActifRow('AE', 'Frais de développement et de prospection', '', 'AE', $data);
                    renderActifRow('AF', 'Brevets, licences, logiciels, et droits similaires', '', 'AF', $data);
                    renderActifRow('AG', 'Fonds commercial et droit au bail', '', 'AG', $data);
                    renderActifRow('AH', 'Autres immobilisations incorporelles', '', 'AH', $data);
                @endphp

                <tr class="row-section">
                    <td class="col-code">AI</td>
                    <td colspan="6">IMMOBILISATIONS CORPORELLES</td>
                </tr>
                @php
                    renderActifRow('AJ', 'Terrains', '3', 'AJ', $data);
                    renderActifRow('AK', 'Bâtiments', '3', 'AK', $data);
                    renderActifRow('AL', 'Aménagements, agencements et installations', '', 'AL', $data);
                    renderActifRow('AM', 'Matériel, mobilier et actifs biologiques', '', 'AM', $data);
                    renderActifRow('AN', 'Matériel de transport', '', 'AN', $data);
                    renderActifRow('AP', 'Avances et acomptes versés sur immobilisations', '', 'AP', $data);
                @endphp

                <tr class="row-section">
                    <td class="col-code">AQ</td>
                    <td colspan="6">IMMOBILISATIONS FINANCIERES</td>
                </tr>
                @php
                    renderActifRow('AR', 'Titres de participation', '4', 'AR', $data);
                    renderActifRow('AS', 'Autres immobilisations financières', '', 'AS', $data);
                    renderActifRow('AZ', 'TOTAL ACTIF IMMOBILISE', '', 'AZ', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">BA</td>
                    <td colspan="6">ACTIF CIRCULANT HAO</td>
                </tr>
                @php
                    renderActifRow('BB', 'STOCKS ET ENCOURS', '6', 'BB', $data);
                    renderActifRow('BG', 'CREANCES ET EMPLOIS ASSIMILES', '', 'BG', $data);
                    renderActifRow('BK', 'TOTAL ACTIF CIRCULANT', '', 'BK', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">BT</td>
                    <td colspan="6">TRESORERIE-ACTIF</td>
                </tr>
                @php
                    renderActifRow('BS', 'Banques, chèques postaux, caisse et assimilés', '11', 'BS', $data);
                    renderActifRow('BT', 'TOTAL TRESORERIE-ACTIF', '', 'BT', $data, true);
                    renderActifRow('BZ', 'TOTAL GENERAL (ACTIF)', '', 'BZ', $data, true);
                @endphp
            </tbody>
        </table>
    </div>
</div>
