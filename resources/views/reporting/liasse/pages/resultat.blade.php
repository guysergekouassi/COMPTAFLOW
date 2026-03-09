@php
    function renderResultatRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $valN = $data[$fieldBase] ?? 0;
        $valN1 = $data[$fieldBase . '_N1'] ?? 0;
        
        $rowClass = $isTotal ? 'row-total' : '';
        $inputClass = "form-control form-control-sm text-end border-0 bg-transparent fw-bold";
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>" . ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        echo "<td class='text-center'><input type='text' class='form-control form-control-sm text-center border-0 bg-transparent' name='note_{$fieldBase}' value='{$data['note_'.$fieldBase] ?? $noteCode}'></td>";
        echo "<td class='col-val bg-light-green'><input type='number' step='0.01' class='{$inputClass} liasse-input' name='{$fieldBase}' value='{$valN}'></td>";
        echo "<td class='col-val px-3'>" . number_format($valN1, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; table-layout: fixed; }
    .liasse-table th { background: #1e293b; color: white; border: 1px solid #334155; padding: 12px 8px; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #f1f5f9; font-weight: 800; color: #1e293b; }
    .row-total { background: #fdf2f8; font-weight: 700; }
    .bg-light-green { background: #f0fdf4; }
    .col-label { width: 45%; }
    .col-val { width: 15%; text-align: right; }
    .liasse-input:focus { background: white !important; border: 1px solid #10b981 !important; outline: none; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">COMPTE DE RESULTAT</h3>
            <div class="text-muted small mt-1">Désignation de l'entreprise : <span class="fw-bold text-success">{{ Auth::user()->company->name }}</span></div>
        </div>
        <div class="badge bg-label-success p-2">RÉSULTAT SINTAX</div>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th style="width: 50px;">REF</th>
                    <th>LIBELLES</th>
                    <th style="width: 60px;">NOTE</th>
                    <th class="text-center">EXERCICE AU 31/12/ N</th>
                    <th class="text-center">EXERCICE AU 31/12/ N-1</th>
                </tr>
            </thead>
            <tbody>
                {{-- ACTIVITES ORDINAIRES --}}
                <tr class="row-section">
                    <td class="text-center"></td>
                    <td colspan="4">ACTIVITES ORDINAIRES</td>
                </tr>
                @php
                    renderResultatRow('XA', 'Ventes de marchandises', '21', 'XA', $data);
                    renderResultatRow('XB', 'Achats de marchandises', '22', 'XB', $data);
                    renderResultatRow('XC', 'MARGE COMMERCIALE (A - B)', '', 'XC', $data, true);
                    
                    renderResultatRow('XH', 'CHIFFRE D\'AFFAIRES (XA + XB + XC + XD)', '', 'XF', $data, true);
                    
                    renderResultatRow('XK', 'VALEUR AJOUTÉE (CA - ACHATS)', '', 'XK', $data, true);
                    renderResultatRow('XO', 'EXCÉDENT BRUT D\'EXPLOITATION (EBE)', '', 'XO', $data, true);
                    renderResultatRow('XR', 'RÉSULTAT D\'EXPLOITATION', '', 'XR', $data, true);
                    
                    renderResultatRow('XW', 'RÉSULTAT FINANCIER', '', 'XW', $data, true);
                    renderResultatRow('XA', 'RÉSULTAT DES ACTIVITÉS ORDINAIRES', '', 'XA_TOTAL', $data, true);
                    
                    renderResultatRow('XG', 'RÉSULTAT NET', '', 'XG_TOTAL', $data, true);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-success">
        Note : Ce tableau est une synthèse des Soldes Intermédiaires de Gestion (SIG).
    </div>
</div>
