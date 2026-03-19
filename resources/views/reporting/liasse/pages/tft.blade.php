@php
    function renderTFTRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $valN = $data[$fieldBase] ?? 0;
        $valN1 = $data[$fieldBase . '_N1'] ?? 0;
        $isExport = $GLOBALS['isExport'] ?? ($viewData['isExport'] ?? false);
        
        $rowClass = $isTotal ? 'row-total' : '';
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='col-code'>{$ref}</td>";
        echo "<td>" . ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        
        $noteVal = $data['note_'.$fieldBase] ?? $noteCode;
        if ($isExport) {
            echo "<td class='text-center'>{$noteVal}</td>";
            echo "<td class='col-val'>" . ($valN != 0 ? number_format($valN, 0, ',', ' ') : '-') . "</td>";
        } else {
            echo "<td class='text-center'><input type='text' class='liasse-input text-center p-1' name='note_{$fieldBase}' value='{$noteVal}'></td>";
            echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input text-indigo fw-800' name='{$fieldBase}' value='{$valN}'></td>";
        }
        
        echo "<td class='col-val text-secondary'>" . number_format($valN1, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<div class="premium-card">
    <div class="table-responsive">
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
