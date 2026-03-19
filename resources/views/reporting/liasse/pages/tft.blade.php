@php
    function renderTFTRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $valN = $data[$fieldBase] ?? 0;
        $valN1 = $data[$fieldBase . '_N1'] ?? 0;
        $details = $data[$fieldBase . '_details'] ?? [];
        $isExport = isset($GLOBALS['isExport']) ? $GLOBALS['isExport'] : (isset($viewData['isExport']) ? $viewData['isExport'] : false);
        
        $rowClass = $isTotal ? 'row-total' : '';
        $hasDetails = count($details) > 0 && !$isTotal;
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='col-code'>{$ref}</td>";
        
        echo "<td class='text-start ps-3'>";
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
                echo "<td class='col-val text-indigo fw-800'>" . ($valN != 0 ? number_format($valN, 0, ',', ' ') : '-') . "</td>";
            } else {
                echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input text-indigo fw-800' name='{$fieldBase}' value='{$valN}'></td>";
            }
        }
        
        echo "<td class='col-val text-secondary'>" . ($valN1 != 0 ? number_format($valN1, 0, ',', ' ') : '-') . "</td>";
        echo "</tr>";

        if ($hasDetails && !$isExport) {
            echo "<tr id='detail_{$fieldBase}' style='display: none;'><td colspan='5' class='p-0 border-0'>";
            echo "<div class='bg-slate-50 border-bottom p-3 shadow-inner' style='box-shadow: inset 0 2px 4px rgba(0,0,0,0.02)'>";
            echo "<table class='table table-sm table-borderless mb-0' style='font-size: 0.8rem; background: transparent;'>";
            foreach($details as $d) {
                echo "<tr><td style='width:60px'></td>";
                echo "<td class='text-slate-600' style='width:40%'><strong>{$d['numero']}</strong> - {$d['intitule']}</td>";
                echo "<td class='text-end font-mono text-slate-800 fw-bold'>" . number_format($d['solde'], 0, ',', ' ') . "</td>";
                echo "<td colspan='2'></td></tr>";
            }
            echo "</table></div></td></tr>";
        }
    }
@endphp

<script>
    $('.toggle-btn').off('click').on('click', function() {
        $(this).find('i').toggleClass('bx-chevron-right bx-chevron-down');
        var target = $(this).attr('data-target-row');
        $('#' + target).toggle();
    });
</script>

<div class="premium-card">
    <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-code">REF</th>
                    <th rowspan="2">LIBELLES</th>
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
                    <td class="col-code">A</td>
                    <td colspan="4">FLUX DE TRESORERIE PROVENANT DES ACTIVITES OPERATIONNELLES</td>
                </tr>
                @php
                    renderTFTRow('ZA', 'Capacité d\'Autofinancement Globale (C.A.F.G)', '', 'ZA', $data);
                    renderTFTRow('ZB', 'Variation du besoin en fonds de roulement lié aux activités', '', 'ZB', $data);
                    renderTFTRow('ZC', 'FLUX DE TRESORERIE NET PROVENANT DES ACTIVITES OPERATIONNELLES (I)', '', 'ZC', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">B</td>
                    <td colspan="4">FLUX DE TRESORERIE PROVENANT DES ACTIVITES D\'INVESTISSEMENT</td>
                </tr>
                @php
                    renderTFTRow('ZD', 'Décaissements liés aux acquisitions d\'immobilisations corporelles', '', 'ZD', $data);
                    renderTFTRow('ZE', 'Encaissements liés aux cessions d\'immobilisations corporelles', '', 'ZE', $data);
                    renderTFTRow('ZF', 'FLUX DE TRESORERIE NET PROVENANT DES ACTIVITES D\'INVESTISSEMENT (II)', '', 'ZF', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code">C</td>
                    <td colspan="4">FLUX DE TRESORERIE PROVENANT DES ACTIVITES DE FINANCEMENT</td>
                </tr>
                @php
                    renderTFTRow('ZG', 'Augmentations de capital', '', 'ZG', $data);
                    renderTFTRow('ZH', 'Emprunts et autres dettes financières', '', 'ZH', $data);
                    renderTFTRow('ZI', 'Dividendes versés', '', 'ZI', $data);
                    renderTFTRow('ZJ', 'FLUX DE TRESORERIE NET PROVENANT DES ACTIVITES DE FINANCEMENT (III)', '', 'ZJ', $data, true);
                @endphp

                <tr class="row-section">
                    <td class="col-code"></td>
                    <td colspan="4">SYNTHESE DE LA TRESORERIE</td>
                </tr>
                @php
                    renderTFTRow('ZK', 'VARIATION NETTE DE LA TRESORERIE (I + II + III)', '', 'ZK', $data, true);
                    renderTFTRow('ZL', 'TRESORERIE AU 1er JANVIER', '', 'ZL', $data);
                    renderTFTRow('ZM', 'TRESORERIE AU 31 DECEMBRE', '', 'ZM', $data, true);
                @endphp
            </tbody>
        </table>
    </div>
</div>
