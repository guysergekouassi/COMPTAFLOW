@php
    function renderResultatRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
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
            echo "<td class='col-val'><input type='number' step='0.01' class='liasse-input text-success fw-800' name='{$fieldBase}' value='{$valN}'></td>";
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
