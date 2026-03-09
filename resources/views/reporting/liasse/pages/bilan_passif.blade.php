@php
    function renderPassifRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $valN = $data[$fieldBase] ?? 0;
        $valN1 = $data[$fieldBase . '_N1'] ?? 0;
        
        $rowClass = $isTotal ? 'row-total' : '';
        $inputClass = "form-control form-control-sm text-end border-0 bg-transparent fw-bold";
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>" . ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        $noteVal = $data['note_'.$fieldBase] ?? $noteCode;
        echo "<td class='text-center'><input type='text' class='form-control form-control-sm text-center border-0 bg-transparent' name='note_{$fieldBase}' value='{$noteVal}'></td>";
        echo "<td class='col-val bg-light-blue'><input type='number' step='0.01' class='{$inputClass} liasse-input' name='{$fieldBase}' value='{$valN}'></td>";
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
    .bg-light-blue { background: #f0f9ff; }
    .col-label { width: 45%; }
    .col-val { width: 15%; text-align: right; }
    .liasse-input:focus { background: white !important; border: 1px solid #3b82f6 !important; outline: none; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">BILAN PASSIF</h3>
            <div class="text-muted small mt-1">Désignation de l'entreprise : <span class="fw-bold text-primary">{{ Auth::user()->company->name }}</span></div>
        </div>
        <div class="badge bg-label-info p-2">PASSIF SINTAX</div>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th style="width: 50px;">REF</th>
                    <th>PASSIF</th>
                    <th style="width: 60px;">NOTE</th>
                    <th class="text-center">EXERCICE AU 31/12/ N</th>
                    <th class="text-center">EXERCICE AU 31/12/ N-1</th>
                </tr>
            </thead>
            <tbody>
                {{-- CAPITAUX PROPRES --}}
                <tr class="row-section">
                    <td class="text-center">C</td>
                    <td colspan="4">CAPITAUX PROPRES ET RESSOURCES ASSIMILEES</td>
                </tr>
                @php
                    renderPassifRow('CA', 'Capital', '', 'CA', $data);
                    renderPassifRow('CF', 'Réserves', '', 'CF', $data);
                    renderPassifRow('CG', 'Report à nouveau (+ ou -)', '', 'CG', $data);
                    renderPassifRow('CJ', 'Résultat net de l\'exercice (Bénéfice + ou Perte -)', '', 'CJ', $data);
                    renderPassifRow('CP', 'TOTAL CAPITAUX PROPRES', '', 'CP', $data, true);
                @endphp

                {{-- DETTES FINANCIERES --}}
                <tr class="row-section">
                    <td class="text-center">D</td>
                    <td colspan="4">DETTES FINANCIERES ET RESSOURCES ASSIMILEES</td>
                </tr>
                @php
                    renderPassifRow('DA', 'Emprunts et dettes financières diverses', '16', 'DA', $data);
                    renderPassifRow('DP', 'TOTAL DETTES FINANCIERES', '', 'DP', $data, true);
                @endphp

                {{-- PASSIF CIRCULANT --}}
                <tr class="row-section">
                    <td class="text-center">F</td>
                    <td colspan="4">PASSIF CIRCULANT</td>
                </tr>
                @php
                    renderPassifRow('FB', 'Fournisseurs d\'exploitation', '17', 'FB', $data);
                    renderPassifRow('FG', 'TOTAL PASSIF CIRCULANT', '', 'FG', $data, true);
                @endphp

                {{-- TRESORERIE --}}
                <tr class="row-section">
                    <td class="text-center">H</td>
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

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-info">
        Note : Le résultat net est automatiquement calculé à partir du compte de résultat.
    </div>
</div>
