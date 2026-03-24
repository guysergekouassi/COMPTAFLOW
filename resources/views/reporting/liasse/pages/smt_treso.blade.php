{{-- SMT ÉTAT DE TRÉSORERIE --}}
@php
    $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' ');
@endphp
<style>
    .smt-badge { background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle; }
    .smt-section-header { background:linear-gradient(90deg,#fffbeb,#fef3c7);color:#92400e;font-weight:800;font-size:0.75rem; text-transform:uppercase;letter-spacing:0.06em; }
    .smt-total { background:#f8fafc;font-weight:800; }
    .smt-grand-total { background:linear-gradient(90deg,#eff6ff,#dbeafe);color:#1e40af;font-weight:900; }
    .ref-code { font-size:0.68rem;color:#94a3b8;font-weight:700;width:50px;text-align:center; }
    .num-right { text-align:right;font-variant-numeric:tabular-nums;font-weight:700; }
</style>

<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-money-bill-wave" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">État de Trésorerie <span class="smt-badge">SMT</span></h5>
        <small class="text-muted">Encaissements et décaissements de l'exercice</small>
    </div>
</div>

<table class="liasse-table">
    <thead>
        <tr>
            <th class="ref-code">REF</th>
            <th>LIBELLÉ</th>
            <th class="num-right" style="width:180px;">MONTANT (FCFA)</th>
        </tr>
    </thead>
    <tbody>
        {{-- ENCAISSEMENTS --}}
        <tr class="smt-section-header"><td colspan="3" class="px-3 py-2">ENCAISSEMENTS</td></tr>
        <tr>
            <td class="ref-code">ZA</td>
            <td>Trésorerie nette au 1er janvier (ouverture)</td>
            <td class="num-right text-muted">{{ $fmt($data['tresorerie_debut'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="ref-code">FA</td>
            <td>Encaissements reçus des clients (produits)</td>
            <td class="num-right text-success">{{ $fmt($data['encaissements_clients'] ?? 0) }}</td>
        </tr>
        <tr class="smt-total">
            <td class="ref-code text-success">ZB</td>
            <td>TOTAL ENCAISSEMENTS</td>
            <td class="num-right text-success">{{ $fmt($data['encaissements_clients'] ?? 0) }}</td>
        </tr>

        {{-- DÉCAISSEMENTS --}}
        <tr class="smt-section-header"><td colspan="3" class="px-3 py-2">DÉCAISSEMENTS</td></tr>
        <tr>
            <td class="ref-code">FB</td>
            <td>Paiements fournisseurs et prestataires</td>
            <td class="num-right text-danger">{{ $fmt($data['decaissements_fourn'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="ref-code">FC</td>
            <td>Paiements de charges de personnel</td>
            <td class="num-right text-danger">{{ $fmt($data['decaissements_pers'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="ref-code">FD</td>
            <td>Paiements d'impôts et taxes</td>
            <td class="num-right text-danger">{{ $fmt($data['decaissements_impots'] ?? 0) }}</td>
        </tr>
        <tr>
            <td class="ref-code">FE</td>
            <td>Autres décaissements d'exploitation</td>
            <td class="num-right text-danger">{{ $fmt($data['decaissements_autres'] ?? 0) }}</td>
        </tr>
        <tr class="smt-total">
            <td class="ref-code text-danger">ZC</td>
            <td>TOTAL DÉCAISSEMENTS</td>
            <td class="num-right text-danger">{{ $fmt($data['total_decaissements'] ?? 0) }}</td>
        </tr>

        {{-- VARIATION --}}
        <tr class="smt-section-header"><td colspan="3" class="px-3 py-2">VARIATION ET SOLDE</td></tr>
        @php
            $variation = floatval($data['variation_treso'] ?? 0);
            $fin_treso = floatval($data['tresorerie_fin'] ?? 0);
        @endphp
        <tr>
            <td class="ref-code">ZD</td>
            <td>Variation nette de trésorerie (Encaiss. — Décaiss.)</td>
            <td class="num-right {{ $variation >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $variation >= 0 ? '+' : '' }}{{ $fmt($variation) }}
            </td>
        </tr>
        <tr class="smt-grand-total">
            <td class="ref-code" style="color:#1e40af;">ZH</td>
            <td>TRÉSORERIE NETTE À LA CLÔTURE</td>
            <td class="num-right {{ $fin_treso >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $fmt($fin_treso) }}
            </td>
        </tr>
    </tbody>
</table>

{{-- Visual bar --}}
@php
    $enc = max(1, floatval($data['encaissements_clients'] ?? 1));
    $dec = floatval($data['total_decaissements'] ?? 0);
    $pct = min(100, round($dec / $enc * 100));
@endphp
<div class="mt-4 p-3 rounded-3 border" style="background:#f8fafc;">
    <div class="d-flex justify-content-between mb-1">
        <span class="small fw-700 text-muted">Ratio Décaissements / Encaissements</span>
        <span class="small fw-800 {{ $pct > 90 ? 'text-danger' : ($pct > 70 ? 'text-warning' : 'text-success') }}">{{ $pct }} %</span>
    </div>
    <div class="progress" style="height:10px;border-radius:10px;">
        <div class="progress-bar {{ $pct > 90 ? 'bg-danger' : ($pct > 70 ? 'bg-warning' : 'bg-success') }}"
             role="progressbar" style="width:{{ $pct }}%"></div>
    </div>
    <div class="small text-muted mt-1">
        Encaissements : {{ $fmt($enc) }} FCFA &nbsp;|&nbsp; Décaissements : {{ $fmt($dec) }} FCFA
    </div>
</div>
