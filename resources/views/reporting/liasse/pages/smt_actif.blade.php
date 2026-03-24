{{-- SMT BILAN ACTIF — Page séparée — Codes DGI : MT_ACTIF_GB/GD/GF/GZ --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.smt-section-header{background:linear-gradient(90deg,#fffbeb,#fef3c7);color:#92400e;font-weight:800;font-size:.75rem;text-transform:uppercase;letter-spacing:.06em}
.smt-grand-total{background:linear-gradient(90deg,#eff6ff,#dbeafe);color:#1e40af;font-weight:900}
.ref-code{font-size:.68rem;color:#94a3b8;font-weight:700;width:80px;text-align:center;font-family:monospace}
.num-right{text-align:right;font-variant-numeric:tabular-nums;font-weight:700}
</style>
@endif

@if(!isset($isExcel))
<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-building-columns" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Bilan ACTIF <span class="smt-badge">MT</span></h5>
        <small class="text-muted">MT_ACTIF — GB · GD · GF · GZ</small>
    </div>
</div>
@endif

<table class="liasse-table">
    <thead>
        <tr>
            <th class="ref-code">Code DGI</th>
            <th>ACTIF</th>
            <th class="num-right" style="width:150px;">BRUT N (1)</th>
            <th class="num-right" style="width:130px;">AMORT/DÉPRÉC.</th>
            <th class="num-right" style="width:150px;">NET N (1)</th>
            <th class="num-right" style="width:130px;">NET N-1 (2)</th>
        </tr>
    </thead>
    <tbody>
        <tr class="smt-section-header"><td colspan="6" class="px-3 py-2">ACTIF IMMOBILISÉ</td></tr>
        <tr>
            <td class="ref-code">MT_ACTIF_GB_1</td>
            <td>Immobilisations incorporelles, corporelles et financières</td>
            <td class="num-right">{{ $fmt($data['immoBrut'] ?? 0) }}</td>
            <td class="num-right text-danger">{{ $fmt($data['immoAmort'] ?? 0) }}</td>
            <td class="num-right fw-700">{{ $fmt($data['immoNet'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['totalActifN1'] ?? 0) }}</td>
        </tr>

        <tr class="smt-section-header"><td colspan="6" class="px-3 py-2">ACTIF CIRCULANT</td></tr>
        <tr>
            <td class="ref-code">MT_ACTIF_GD_1</td>
            <td>Stocks et en-cours (Comptes 3X)</td>
            <td class="num-right">{{ $fmt($data['stocks'] ?? 0) }}</td>
            <td class="num-right">—</td>
            <td class="num-right">{{ $fmt($data['stocks'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>
        <tr>
            <td class="ref-code">MT_ACTIF_GF_1</td>
            <td>Créances et emplois assimilés (Comptes 4X)</td>
            <td class="num-right">{{ $fmt($data['creances'] ?? 0) }}</td>
            <td class="num-right">—</td>
            <td class="num-right">{{ $fmt($data['creances'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>

        <tr class="smt-section-header"><td colspan="6" class="px-3 py-2">TRÉSORERIE — ACTIF</td></tr>
        <tr>
            <td class="ref-code">—</td>
            <td>Disponibilités — Banques, Caisses, CCP (Comptes 52-57)</td>
            <td class="num-right">{{ $fmt($data['treso_actif'] ?? 0) }}</td>
            <td class="num-right">—</td>
            <td class="num-right">{{ $fmt($data['treso_actif'] ?? 0) }}</td>
            <td class="num-right text-muted">—</td>
        </tr>

        <tr class="smt-grand-total">
            <td class="ref-code" style="color:#1e40af">MT_ACTIF_GZ_1</td>
            <td class="fw-900">TOTAL GÉNÉRAL ACTIF</td>
            <td class="num-right">{{ $fmt(($data['immoBrut'] ?? 0) + ($data['stocks'] ?? 0) + ($data['creances'] ?? 0) + ($data['treso_actif'] ?? 0)) }}</td>
            <td class="num-right">{{ $fmt($data['immoAmort'] ?? 0) }}</td>
            <td class="num-right fs-6 text-primary">{{ $fmt($data['totalActif'] ?? 0) }}</td>
            <td class="num-right text-muted">{{ $fmt($data['totalActifN1'] ?? 0) }}</td>
        </tr>
    </tbody>
</table>

@if(!isset($isExcel))
{{-- Indicateurs --}}
<div class="row g-3 mt-3">
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#eff6ff">
            <div class="small text-muted fw-600">Actif Immobilisé Net</div>
            <div class="fw-800 text-primary fs-6">{{ $fmt($data['immoNet'] ?? 0) }} FCFA</div>
        </div>
    </div>
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#f0fdf4">
            <div class="small text-muted fw-600">Actif Circulant</div>
            <div class="fw-800 text-success fs-6">{{ $fmt(($data['stocks'] ?? 0) + ($data['creances'] ?? 0)) }} FCFA</div>
        </div>
    </div>
    <div class="col-4">
        <div class="p-3 rounded-3 border text-center" style="background:#fffbeb">
            <div class="small text-muted fw-600">Trésorerie Active</div>
            <div class="fw-800 text-warning fs-6">{{ $fmt($data['treso_actif'] ?? 0) }} FCFA</div>
        </div>
    </div>
</div>
@endif
