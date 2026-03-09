@php
    function renderTFTRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $valN = $data[$fieldBase] ?? 0;
        $valN1 = $data[$fieldBase . '_N1'] ?? 0;
        
        $rowClass = $isTotal ? 'row-total' : '';
        $inputClass = "form-control form-control-sm text-end border-0 bg-transparent fw-bold";
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>" . ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        $noteVal = $data['note_'.$fieldBase] ?? $noteCode;
        echo "<td class='text-center'><input type='text' class='form-control form-control-sm text-center border-0 bg-transparent' name='note_{$fieldBase}' value='{$noteVal}'></td>";
        echo "<td class='col-val bg-light-purple'><input type='number' step='0.01' class='{$inputClass} liasse-input' name='{$fieldBase}' value='{$valN}'></td>";
        echo "<td class='col-val px-3'>" . number_format($valN1, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; table-layout: fixed; }
    .liasse-table th { background: #1e293b; color: white; border: 1px solid #334155; padding: 12px 8px; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; }
    .row-section { background: #f8fafc; font-weight: 800; color: #4338ca; border-left: 4px solid #4338ca !important; }
    .row-total { background: #eef2ff; font-weight: 700; color: #4338ca; }
    .bg-light-purple { background: #f5f3ff; }
    .col-label { width: 45%; }
    .col-val { width: 15%; text-align: right; }
    .liasse-input:focus { background: white !important; border: 1px solid #6366f1 !important; outline: none; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">TABLEAU DES FLUX DE TRESORERIE (TFT)</h3>
            <div class="text-muted small mt-1">Désignation de l'entreprise : <span class="fw-bold text-indigo">{{ Auth::user()->company->name }}</span></div>
        </div>
        <div class="badge bg-label-primary p-2">TFT SYSCOHADA</div>
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
                {{-- SECTION A --}}
                <tr class="row-section">
                    <td class="text-center">A</td>
                    <td colspan="4">FLUX DE TRESORERIE PROVENANT DES ACTIVITES OPERATIONNELLES</td>
                </tr>
                @php
                    renderTFTRow('ZA', 'Capacité d\'Autofinancement Globale (C.A.F.G)', '', 'ZA', $data);
                    renderTFTRow('ZB', 'Variation du besoin en fonds de roulement lié aux activités', '', 'ZB', $data);
                    renderTFTRow('ZC', 'FLUX DE TRESORERIE NET PROVENANT DES ACTIVITES OPERATIONNELLES (I)', '', 'ZC', $data, true);
                @endphp

                {{-- SECTION B --}}
                <tr class="row-section">
                    <td class="text-center">B</td>
                    <td colspan="4">FLUX DE TRESORERIE PROVENANT DES ACTIVITES D\'INVESTISSEMENT</td>
                </tr>
                @php
                    renderTFTRow('ZD', 'Décaissements liés aux acquisitions d\'immobilisations corporelles', '', 'ZD', $data);
                    renderTFTRow('ZE', 'Encaissements liés aux cessions d\'immobilisations corporelles', '', 'ZE', $data);
                    renderTFTRow('ZF', 'FLUX DE TRESORERIE NET PROVENANT DES ACTIVITES D\'INVESTISSEMENT (II)', '', 'ZF', $data, true);
                @endphp

                {{-- SECTION C --}}
                <tr class="row-section">
                    <td class="text-center">C</td>
                    <td colspan="4">FLUX DE TRESORERIE PROVENANT DES ACTIVITES DE FINANCEMENT</td>
                </tr>
                @php
                    renderTFTRow('ZG', 'Augmentations de capital', '', 'ZG', $data);
                    renderTFTRow('ZH', 'Emprunts et autres dettes financières', '', 'ZH', $data);
                    renderTFTRow('ZI', 'Dividendes versés', '', 'ZI', $data);
                    renderTFTRow('ZJ', 'FLUX DE TRESORERIE NET PROVENANT DES ACTIVITES DE FINANCEMENT (III)', '', 'ZJ', $data, true);
                @endphp

                {{-- RECAP --}}
                <tr class="row-section">
                    <td class="text-center"></td>
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

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-primary">
        Note : Le TFT permet d'expliquer le passage de la trésorerie d'ouverture à la trésorerie de clôture.
    </div>
</div>
