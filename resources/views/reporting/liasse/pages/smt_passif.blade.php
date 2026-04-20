{{-- SMT BILAN PASSIF — Page séparée — Codes DGI : MT_PASSIF_HA/HB/HD/HZ --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.smt-section-header{background:linear-gradient(90deg,#fffbeb,#fef3c7);color:#92400e;font-weight:800;font-size:.75rem;text-transform:uppercase;letter-spacing:.06em}
.smt-sous-total{background:#f8fafc;font-weight:800;color:#334155}
.smt-grand-total{background:linear-gradient(90deg,#eff6ff,#dbeafe);color:#1e40af;font-weight:900}
.ref-code{font-size:.68rem;color:#94a3b8;font-weight:700;width:80px;text-align:center;font-family:monospace}
.num-right{text-align:right;font-variant-numeric:tabular-nums;font-weight:700}
</style>
@endif

@if(!isset($isExcel))
<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-scale-balanced" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Bilan PASSIF <span class="smt-badge">MT</span></h5>
        <small class="text-muted">MT_PASSIF — HA · HB · HD · HZ</small>
    </div>
</div>
@endif

<table class="liasse-table">
    <thead>
        <tr>
            <th class="ref-code">Code DGI</th>
            <th>PASSIF</th>
            <th class="num-right" style="width:180px;">MONTANT N (1)</th>
            <th class="num-right" style="width:160px;">MONTANT N-1 (2)</th>
        </tr>
    </thead>
    <tbody>
        {{-- HA — Capitaux propres --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">CAPITAUX PROPRES (HA)</td></tr>
        <tr>
            <td class="ref-code">HA→Capital</td>
            <td>Capital (Comptes 10X)</td>
            <td class="num-right">{{ $fmt($data['capital'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">HA→Rés.</td>
            <td>Réserves (Comptes 11X)</td>
            <td class="num-right">{{ $fmt($data['reserves'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">HA→RAN</td>
            <td>Report à nouveau (Comptes 12X)</td>
            <td class="num-right">{{ $fmt($data['report'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr class="smt-sous-total">
            <td class="ref-code text-amber">MT_PASSIF_HA</td>
            <td>SOUS-TOTAL CAPITAUX PROPRES (hors résultat)</td>
            <td class="num-right">{{ $fmt(($data['capital'] ?? 0) + ($data['reserves'] ?? 0) + ($data['report'] ?? 0)) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>

        {{-- HB — Résultat net --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">RÉSULTAT DE L'EXERCICE (HB)</td></tr>
        @php $res = floatval($data['resultat'] ?? 0); @endphp
        <tr>
            <td class="ref-code">MT_PASSIF_HB_1</td>
            <td>Résultat net de l'exercice (Bénéfice ou Perte)</td>
            <td class="num-right {{ $res >= 0 ? 'text-success' : 'text-danger' }} fs-6">
                {{ $res >= 0 ? '' : '(' }}{{ $fmt(abs($res)) }}{{ $res >= 0 ? '' : ')' }}
            </td>
            <td class="num-right text-muted">—</td>
        </tr>

        {{-- HD — Dettes --}}
        <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">DETTES (HD)</td></tr>
        <tr>
            <td class="ref-code">HD→Fin.</td>
            <td>Emprunts et dettes financières (Comptes 16X)</td>
            <td class="num-right">{{ $fmt($data['dettes_fin'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">HD→Exp.</td>
            <td>Fournisseurs et dettes d'exploitation (Comptes 40X)</td>
            <td class="num-right">{{ $fmt($data['dettes_exp'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">HD→Fisc.</td>
            <td>Dettes fiscales et sociales (Comptes 42-44)</td>
            <td class="num-right">{{ $fmt($data['dettes_fisc'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">HD→Trés.</td>
            <td>Trésorerie Passif (Comptes 52, 56)</td>
            <td class="num-right">{{ $fmt($data['treso_passif'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr class="smt-sous-total">
            <td class="ref-code">MT_PASSIF_HD</td>
            <td>SOUS-TOTAL DETTES</td>
            <td class="num-right">{{ $fmt(($data['dettes_fin'] ?? 0) + ($data['dettes_exp'] ?? 0) + ($data['dettes_fisc'] ?? 0) + ($data['treso_passif'] ?? 0)) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>

        {{-- HZ — Total --}}
        <tr class="smt-grand-total">
            <td class="ref-code" style="color:#1e40af">MT_PASSIF_HZ_1</td>
            <td class="fw-900">TOTAL GÉNÉRAL PASSIF</td>
            <td class="num-right fs-6 text-primary">{{ $fmt($data['totalPassif'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['totalPassifN1'] ?? 0) }}</td>
        </tr>
    </tbody>
</table>

@if(!isset($isExcel))
{{-- Équilibre Actif = Passif --}}
@php
    $actif  = floatval($data['totalActif'] ?? 0);
    $passif = floatval($data['totalPassif'] ?? 0);
    $diff   = abs($actif - $passif);
    $ok     = $diff < 1;
@endphp
<div class="mt-3 p-3 rounded-3 d-flex align-items-center gap-3 {{ $ok ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : 'bg-danger bg-opacity-10 border border-danger border-opacity-25' }}">
    <i class="fa-solid {{ $ok ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger' }} fs-3"></i>
    <div>
        <strong class="{{ $ok ? 'text-success' : 'text-danger' }} fs-6">
            {{ $ok ? '✔ Bilan équilibré — GZ = HZ' : '✘ Déséquilibre : ' . number_format($diff, 0, ',', ' ') . ' FCFA' }}
        </strong>
        <div class="small text-muted mt-1">
            GZ (Actif Net) = {{ number_format($actif, 0, ',', ' ') }} FCFA &nbsp;|&nbsp;
            HZ (Passif) = {{ number_format($passif, 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>
@endif
