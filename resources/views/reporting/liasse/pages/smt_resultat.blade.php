{{-- SMT COMPTE DE RÉSULTAT --}}
@php
    $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' ');
    $pct = function($val, $ref) {
        return $ref != 0 ? number_format(abs($val / $ref * 100), 1) . ' %' : '—';
    };
@endphp
<style>
    .smt-badge { background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle; }
    .smt-section-header { background:linear-gradient(90deg,#fffbeb,#fef3c7);color:#92400e;font-weight:800;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em; }
    .smt-sous-total { background:#f8fafc;font-weight:700;color:#334155; }
    .smt-grand-total { background:linear-gradient(90deg,#eff6ff,#dbeafe);color:#1e40af;font-weight:900; }
    .ref-code { font-size:0.68rem;color:#94a3b8;font-weight:700;width:50px;text-align:center; }
    .num-right { text-align:right;font-variant-numeric:tabular-nums;font-weight:700; }
</style>

<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-file-invoice-dollar" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Compte de Résultat <span class="smt-badge">SMT</span></h5>
        <small class="text-muted">Système Minimal de Trésorerie — recettes et dépenses simplifiées</small>
    </div>
</div>

<table class="liasse-table">
    <thead>
        <tr>
            <th class="ref-code">REF</th>
            <th>LIBELLÉ</th>
            <th class="num-right" style="width:160px;">EXERCICE N</th>
            <th class="num-right" style="width:140px;">EXERCICE N-1</th>
        </tr>
    </thead>
    <tbody>
        {{-- PRODUITS --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">PRODUITS D'EXPLOITATION (Recettes)</td></tr>
        <tr>
            <td class="ref-code">TA</td>
            <td>Ventes de marchandises, prestations de services</td>
            <td class="num-right">{{ $fmt($data['produits_ventes'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['total_produits_N1'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="ref-code">TC</td>
            <td>Autres produits d'exploitation</td>
            <td class="num-right">{{ $fmt($data['produits_autres'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr class="smt-sous-total">
            <td class="ref-code text-primary">XB</td>
            <td>TOTAL PRODUITS (Chiffre d'affaires)</td>
            <td class="num-right text-primary">{{ $fmt($data['total_produits'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['total_produits_N1'] ?? 0) }}</td>
        </tr>

        {{-- CHARGES --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">CHARGES D'EXPLOITATION (Dépenses)</td></tr>
        <tr>
            <td class="ref-code">RA</td>
            <td>Achats de marchandises et matières premières</td>
            <td class="num-right text-danger">{{ $fmt($data['achats_marchand'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">RE</td>
            <td>Services extérieurs et autres achats</td>
            <td class="num-right text-danger">{{ $fmt($data['achats_services'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">RK</td>
            <td>Charges de personnel (salaires, cotisations)</td>
            <td class="num-right text-danger">{{ $fmt($data['charges_personnel'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">RI</td>
            <td>Impôts et taxes (hors IS)</td>
            <td class="num-right text-danger">{{ $fmt($data['impots_taxes'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">RN</td>
            <td>Dotations aux amortissements et provisions</td>
            <td class="num-right text-danger">{{ $fmt($data['dotations'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">RS</td>
            <td>Autres charges d'exploitation</td>
            <td class="num-right text-danger">{{ $fmt($data['autres_charges'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr class="smt-sous-total">
            <td class="ref-code text-danger">XD</td>
            <td>TOTAL CHARGES</td>
            <td class="num-right text-danger">{{ $fmt($data['total_charges'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['total_charges_N1'] ?? 0) }}</td>
        </tr>

        {{-- RÉSULTAT --}}
        @php
            $res   = floatval($data['resultat_exercice'] ?? 0);
            $resN1 = floatval($data['resultat_exercice_N1'] ?? 0);
        @endphp
        <tr class="smt-grand-total">
            <td class="ref-code" style="color:#1e40af;">XS</td>
            <td>RÉSULTAT NET DE L'EXERCICE</td>
            <td class="num-right {{ $res >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $res >= 0 ? '' : '(' }}{{ $fmt(abs($res)) }}{{ $res >= 0 ? '' : ')' }}
            </td>
            <td class="num-right {{ $resN1 >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $resN1 >= 0 ? '' : '(' }}{{ $fmt(abs($resN1)) }}{{ $resN1 >= 0 ? '' : ')' }}
            </td>
        </tr>
    </tbody>
</table>

{{-- KPIs --}}
@php
    $ca     = floatval($data['total_produits'] ?? 0);
    $charge = floatval($data['total_charges'] ?? 0);
    $res    = $ca - $charge;
    $margin = $ca > 0 ? round($res / $ca * 100, 1) : 0;
@endphp
<div class="row g-3 mt-3">
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#f0fdf4;">
            <div class="small text-muted fw-600">CA Total</div>
            <div class="fw-800 text-success fs-5">{{ $fmt($ca) }}</div>
            <div class="small text-muted">FCFA</div>
        </div>
    </div>
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#fef2f2;">
            <div class="small text-muted fw-600">Total Charges</div>
            <div class="fw-800 text-danger fs-5">{{ $fmt($charge) }}</div>
            <div class="small text-muted">FCFA</div>
        </div>
    </div>
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background: {{ $margin >= 0 ? '#f0fdf4' : '#fef2f2' }};">
            <div class="small text-muted fw-600">Marge Nette</div>
            <div class="fw-800 {{ $margin >= 0 ? 'text-success' : 'text-danger' }} fs-5">{{ $margin }} %</div>
            <div class="small text-muted">du CA</div>
        </div>
    </div>
</div>
