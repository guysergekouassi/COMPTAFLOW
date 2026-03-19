@php
    function renderResultatRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
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
        echo ($isTotal ? "<strong>" . mb_strtoupper($label) . "</strong>" : $label) . "</td>";
        
        $noteVal = $data['note_'.$fieldBase] ?? $noteCode;
        if ($isExport) {
            echo "<td class='text-center'>{$noteVal}</td>";
            echo "<td class='col-val'>" . ($valN != 0 ? number_format($valN, 0, ',', ' ') : '-') . "</td>";
        } else {
            echo "<td class='text-center'><input type='text' class='liasse-input text-center p-1' name='note_{$fieldBase}' value='{$noteVal}'></td>";
            if ($isTotal) {
                echo "<td class='col-val text-success fw-800'>" . ($valN != 0 ? number_format($valN, 0, ',', ' ') : '-') . "</td>";
            } else {
                echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input text-success fw-800' name='{$fieldBase}' value='{$valN}'></td>";
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
                    <td class="col-code"></td>
                    <td colspan="4">ACTIVITES ORDINAIRES</td>
                </tr>
                @php
                    renderResultatRow('XA', 'Ventes de marchandises', '21', 'XA', $data);
                    renderResultatRow('XB', 'Achats de marchandises', '22', 'XB', $data);
                    renderResultatRow('XC', 'MARGE COMMERCIALE (A - B)', '', 'XC', $data, true);
                    renderResultatRow('XD', 'Production valorisée', '21', 'XD', $data);
                    renderResultatRow('XE', 'Consommation de l\'exercice', '22', 'XE', $data);
                    renderResultatRow('XF', 'VALEUR AJOUTÉE (C + D - E)', '', 'XF', $data, true);
                    renderResultatRow('XG', 'Subventions d\'exploitation', '21', 'XG', $data);
                    renderResultatRow('XH', 'Charges de personnel', '24', 'XH', $data);
                    renderResultatRow('XI', 'EXCÉDENT BRUT D\'EXPLOITATION (F + G - H)', '', 'XI', $data, true);
                    renderResultatRow('XJ', 'Reprise d\'amortissements et provisions', '4', 'XJ', $data);
                    renderResultatRow('XK', 'Dotations aux amortissements et provisions', '2', 'XK', $data);
                    renderResultatRow('XL', 'RÉSULTAT D\'EXPLOITATION (I + J - K)', '', 'XL', $data, true);
                    renderResultatRow('XM', 'Revenus financiers et assimilés', '26', 'XM', $data);
                    renderResultatRow('XN', 'Frais financiers et charges assimilées', '26', 'XN', $data);
                    renderResultatRow('XO', 'RÉSULTAT FINANCIER (M - N)', '', 'XO', $data, true);
                    renderResultatRow('XP', 'RÉSULTAT DES ACTIVITÉS ORDINAIRES (L + O)', '', 'XP', $data, true);
                    renderResultatRow('XQ', 'Produits Hors Activités Ordinaires (HAO)', '27', 'XQ', $data);
                    renderResultatRow('XR', 'Charges Hors Activités Ordinaires (HAO)', '27', 'XR', $data);
                    renderResultatRow('XS', 'RÉSULTAT H.A.O. (Q - R)', '', 'XS', $data, true);
                    renderResultatRow('XT', 'Participation des travailleurs', '30', 'XT', $data);
                    renderResultatRow('XU', 'Impôts sur le résultat', '28', 'XU', $data);
                    renderResultatRow('XV', 'RÉSULTAT NET (P + S - T - U)', '', 'XV', $data, true);
                @endphp
            </tbody>
        </table>
    </div>
</div>
