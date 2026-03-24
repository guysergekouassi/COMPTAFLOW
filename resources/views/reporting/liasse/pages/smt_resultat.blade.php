{{-- SMT COMPTE DE RÉSULTAT — Codes DGI réels : KA, KX, JA, JB, JC, JD, JF, JX, KZ, KZC --}}
@php
    $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' ');
    $val = fn($k) => floatval($data[$k] ?? 0);
@endphp
<style>
    .smt-badge { background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle; }
    .smt-section-header { background:linear-gradient(90deg,#fffbeb,#fef3c7);color:#92400e;font-weight:800;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em; }
    .smt-sous-total { background:#f8fafc;font-weight:700;color:#334155; }
    .smt-grand-total { background:linear-gradient(90deg,#eff6ff,#dbeafe);color:#1e40af;font-weight:900; }
    .ref-code { font-size:0.68rem;color:#94a3b8;font-weight:700;width:80px;text-align:center;font-family:monospace; }
    .num-right { text-align:right;font-variant-numeric:tabular-nums;font-weight:700; }
</style>

<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-file-invoice-dollar" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Compte de Résultat <span class="smt-badge">MT</span></h5>
        <small class="text-muted">Système Minimal de Trésorerie — Codes DGI e-SINTAX</small>
    </div>
</div>

<table class="liasse-table">
    <thead>
        <tr>
            <th class="ref-code">Code DGI</th>
            <th>LIBELLÉ</th>
            <th class="num-right" style="width:180px;">EXERCICE N</th>
            <th class="num-right" style="width:160px;">EXERCICE N-1</th>
        </tr>
    </thead>
    <tbody>
        {{-- PRODUITS --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">PRODUITS D'EXPLOITATION</td></tr>
        <tr>
            <td class="ref-code">MT_RESULTAT_KA</td>
            <td>Chiffre d'affaires (ventes de marchandises / prestations)</td>
            <td class="num-right">{{ $fmt($data['CA'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['total_produits_N1'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="ref-code">—</td>
            <td>Autres produits d'exploitation</td>
            <td class="num-right">{{ $fmt($data['autres_produits'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr class="smt-sous-total">
            <td class="ref-code text-primary">MT_RESULTAT_KX</td>
            <td>TOTAL PRODUITS</td>
            <td class="num-right text-primary">{{ $fmt($data['total_produits'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['total_produits_N1'] ?? 0) }}</td>
        </tr>

        {{-- CHARGES --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">CHARGES D'EXPLOITATION</td></tr>
        <tr>
            <td class="ref-code">MT_RESULTAT_JA</td>
            <td>Achats de marchandises et matières premières</td>
            <td class="num-right text-danger">{{ $fmt($data['achats'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        @if(floatval($data['variation_stocks'] ?? 0) != 0)
        <tr>
            <td class="ref-code">MT_RESULTAT_VA</td>
            <td>Variation de stocks (achetés)</td>
            <td class="num-right {{ ($data['variation_stocks'] ?? 0) < 0 ? 'text-success' : 'text-danger' }}">{{ $fmt($data['variation_stocks'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        @endif
        <tr>
            <td class="ref-code">MT_RESULTAT_JB</td>
            <td>Services extérieurs et autres achats (loyers, transports…)</td>
            <td class="num-right text-danger">{{ $fmt($data['services_ext'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">MT_RESULTAT_JC</td>
            <td>Charges de personnel (salaires, cotisations)</td>
            <td class="num-right text-danger">{{ $fmt($data['charges_pers'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">MT_RESULTAT_JD</td>
            <td>Impôts et taxes (hors IS)</td>
            <td class="num-right text-danger">{{ $fmt($data['impots_taxes'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">MT_RESULTAT_JF</td>
            <td>Autres charges d'exploitation</td>
            <td class="num-right text-danger">{{ $fmt($data['autres_charges'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr class="smt-sous-total">
            <td class="ref-code text-danger">MT_RESULTAT_JX</td>
            <td>TOTAL CHARGES</td>
            <td class="num-right text-danger">{{ $fmt($data['total_charges'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['total_charges_N1'] ?? 0) }}</td>
        </tr>

        {{-- RÉSULTAT --}}
        @php
            $res   = floatval($data['resultat_net'] ?? 0);
            $resN1 = floatval($data['resultat_exercice_N1'] ?? 0);
            $absRes   = abs($res);
            $absResN1 = abs($resN1);
        @endphp
        <tr class="smt-grand-total">
            <td class="ref-code" style="color:#1e40af;">MT_RESULTAT_KZ</td>
            <td>RÉSULTAT NET DE L'EXERCICE (avant IS)</td>
            <td class="num-right {{ $res >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $res >= 0 ? '' : '(' }}{{ $fmt($absRes) }}{{ $res >= 0 ? '' : ')' }}
            </td>
            <td class="num-right {{ $resN1 >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $resN1 >= 0 ? '' : '(' }}{{ $fmt($absResN1) }}{{ $resN1 >= 0 ? '' : ')' }}
            </td>
        </tr>
        <tr style="background:#f8fafc;font-weight:700;">
            <td class="ref-code">MT_RESULTAT_KZC</td>
            <td>Résultat comptable après impôts (KZC)</td>
            <td class="num-right {{ floatval($data['resultat_fiscal'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $fmt($data['resultat_fiscal'] ?? 0) }}
            </td>
            <td class="num-right text-muted">—</td>
        </tr>
    </tbody>
</table>

{{-- KPIs --}}
@php
    $ca     = floatval($data['total_produits'] ?? 0);
    $charge = floatval($data['total_charges'] ?? 0);
    $res    = $ca - $charge;
    $margin = $ca > 0 ? round($res / $ca * 100, 1) : 0;
    $absMargin = abs($margin);
@endphp
<div class="row g-3 mt-3">
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#f0fdf4;">
            <div class="small text-muted fw-600">CA Total (KA)</div>
            <div class="fw-800 text-success fs-5">{{ $fmt($data['CA'] ?? 0) }}</div>
            <div class="small text-muted">FCFA</div>
        </div>
    </div>
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#fef2f2;">
            <div class="small text-muted fw-600">Total Charges (JX)</div>
            <div class="fw-800 text-danger fs-5">{{ $fmt($charge) }}</div>
            <div class="small text-muted">FCFA</div>
        </div>
    </div>
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background: {{ $margin >= 0 ? '#f0fdf4' : '#fef2f2' }};">
            <div class="small text-muted fw-600">Marge Nette (KZ)</div>
            <div class="fw-800 {{ $margin >= 0 ? 'text-success' : 'text-danger' }} fs-5">{{ ($margin >= 0 ? '' : '-') . $absMargin }} %</div>
            <div class="small text-muted">du CA</div>
        </div>
    </div>
</div>
