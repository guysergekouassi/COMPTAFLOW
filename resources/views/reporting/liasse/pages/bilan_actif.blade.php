@php
    function renderActifRow($ref, $label, $noteCode, $fieldBase, $data, $isTotal = false) {
        $brut = $data[$fieldBase . '_brut'] ?? 0;
        $amort = $data[$fieldBase . '_amort'] ?? 0;
        $net = $data[$fieldBase . '_net'] ?? 0;
        $netN1 = $data[$fieldBase . '_net_N1'] ?? 0;
        
        $rowClass = $isTotal ? 'row-total' : '';
        $inputClass = "form-control form-control-sm text-end border-0 bg-transparent fw-bold";
        
        echo "<tr class='{$rowClass}'>";
        echo "<td class='text-center fw-bold text-secondary'>{$ref}</td>";
        echo "<td class='col-label'>" . ($isTotal ? "<strong>{$label}</strong>" : $label) . "</td>";
        echo "<td class='text-center'><input type='text' class='form-control form-control-sm text-center border-0 bg-transparent' name='note_{$fieldBase}' value='{$data['note_'.$fieldBase] ?? $noteCode}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='{$inputClass} liasse-input' name='{$fieldBase}_brut' value='{$brut}'></td>";
        echo "<td class='col-val'><input type='number' step='0.01' class='{$inputClass} liasse-input' name='{$fieldBase}_amort' value='{$amort}'></td>";
        echo "<td class='col-val col-val-net px-3'>" . number_format($net, 0, ',', ' ') . "</td>";
        echo "<td class='col-val px-3'>" . number_format($netN1, 0, ',', ' ') . "</td>";
        echo "</tr>";
    }
@endphp

<style>
    .liasse-table-wrapper { background: white; padding: 2rem; border-radius: 8px; }
    .liasse-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; table-layout: fixed; }
    .liasse-table th { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; padding: 12px 8px; font-size: 0.7rem; text-transform: uppercase; font-weight: 800; vertical-align: middle; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 4px 8px; font-size: 0.8rem; vertical-align: middle; }
    .row-section { background: #f1f5f9; font-weight: 800; color: #1e293b; text-transform: uppercase; }
    .row-total { background: #fdf2f8; font-weight: 700; }
    .col-label { width: 35%; }
    .col-val { width: 12%; text-align: right; }
    .col-val-net { background: #f0f9ff; font-weight: 800; color: #0369a1; text-align: right; }
    .liasse-input:focus { background: #fff !important; border: 1px solid #3b82f6 !important; outline: none; }
    .liasse-input::-webkit-inner-spin-button { -webkit-appearance: none; }
</style>

<div class="liasse-table-wrapper shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-900 text-dark mb-0">BILAN ACTIF</h3>
            <div class="text-muted small mt-1">Désignation de l'entreprise : <span class="fw-bold text-primary">{{ Auth::user()->company->name }}</span></div>
        </div>
        <div class="text-end">
            <div class="badge bg-label-primary p-2">SINTAX READY</div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="liasse-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50px;">REF</th>
                    <th rowspan="2">ACTIF (1)</th>
                    <th rowspan="2" style="width: 60px;">NOTE</th>
                    <th colspan="3" class="text-center">EXERCICE au 31/12/ N</th>
                    <th class="text-center">EXERCICE AU<br>31/12/ N-1</th>
                </tr>
                <tr>
                    <th class="text-center">BRUT</th>
                    <th class="text-center">AMORT et DEPREC.</th>
                    <th class="text-center">NET</th>
                    <th class="text-center">NET</th>
                </tr>
            </thead>
            <tbody>
                {{-- IMMOBILISATIONS INCORPORELLES --}}
                <tr class="row-section">
                    <td class="text-center">AD</td>
                    <td colspan="6">IMMOBILISATIONS INCORPORELLES</td>
                </tr>
                @php
                    renderActifRow('AE', 'Frais de développement et de prospection', '', 'AE', $data);
                    renderActifRow('AF', 'Brevets, licences, logiciels, et droits similaires', '', 'AF', $data);
                    renderActifRow('AG', 'Fonds commercial et droit au bail', '', 'AG', $data);
                    renderActifRow('AH', 'Autres immobilisations incorporelles', '', 'AH', $data);
                @endphp

                {{-- IMMOBILISATIONS CORPORELLES --}}
                <tr class="row-section">
                    <td class="text-center">AI</td>
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
                    <td class="text-center">AQ</td>
                    <td colspan="6">IMMOBILISATIONS FINANCIERES</td>
                </tr>
                @php
                    renderActifRow('AR', 'Titres de participation', '4', 'AR', $data);
                    renderActifRow('AS', 'Autres immobilisations financières', '', 'AS', $data);
                    renderActifRow('AZ', 'TOTAL ACTIF IMMOBILISE', '', 'AZ', $data, true);
                @endphp

                {{-- ACTIF CIRCULANT --}}
                <tr class="row-section">
                    <td class="text-center">BA</td>
                    <td colspan="6">ACTIF CIRCULANT HAO</td>
                </tr>
                @php
                    renderActifRow('BB', 'STOCKS ET ENCOURS', '6', 'BB', $data);
                    renderActifRow('BG', 'CREANCES ET EMPLOIS ASSIMILES', '', 'BG', $data);
                    renderActifRow('BK', 'TOTAL ACTIF CIRCULANT', '', 'BK', $data, true);
                @endphp

                {{-- TRESORERIE --}}
                <tr class="row-section">
                    <td class="text-center">BT</td>
                    <td colspan="6">TOTAL TRESORERIE-ACTIF</td>
                </tr>
                @php
                    renderActifRow('BS', 'Banques, chèques postaux, caisse et assimilés', '11', 'BS', $data);
                    renderActifRow('BT', 'TOTAL TRESORERIE-ACTIF', '', 'BT', $data, true);
                    renderActifRow('BZ', 'TOTAL GENERAL', '', 'BZ', $data, true);
                @endphp
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-light rounded italic small text-muted border-start border-4 border-primary">
        (1) Cette feuille ne peut pas être modifiée, elle sera remplie automatiquement avec les valeurs saisies dans l'onglet BILAN.
    </div>
</div>
