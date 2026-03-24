{{-- SMT - 5. TRÉSORERIE : ENCAISSEMENTS (Recettes) --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.treso-table{width:100%;border-collapse:separate;border-spacing:0;border-radius:12px;overflow:hidden;border:1.5px solid #e5e7eb}
.treso-table th{background:#f9fafb;padding:15px;font-size:.70rem;text-transform:uppercase;color:#4b5563;font-weight:700;border-bottom:1.5px solid #e5e7eb}
.treso-table td{padding:12px 15px;border-bottom:1px solid #f3f4f6;background:#fff;font-weight:600}
.treso-input{width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 12px;text-align:right;font-weight:700;background:#fdfdfd;color:#111827}
.treso-total{background:#fef3c7!important;color:#92400e;font-size:1.05rem}
</style>
@endif

@if(!isset($isExcel))
<div class="mb-4 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-trend-up" style="color:#d97706;font-size:1.3rem"></i>
        <h5 class="mb-0 fw-bold">TRÉSORERIE : ENCAISSEMENTS <span class="smt-badge">SMT</span></h5>
    </div>
    <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">RECETTES</div>
</div>

<div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
    <i class="fa-solid fa-circle-info fs-4"></i>
    <div class="small">Ce tableau retrace tous les flux de trésorerie entrant dans l'entreprise (Ventes, Apports, Emprunts, etc.) durant l'exercice.</div>
</div>
@endif

<table class="treso-table mb-4 shadow-sm">
    <thead>
        <tr>
            <th style="width:50px">CODE</th>
            <th>NATURE DES ENCAISSEMENTS</th>
            <th class="text-end" style="width:250px">MONTANT DE L'EXERCICE (N)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center text-muted small">AA</td>
            <td>Ventes et prestations de services (Comptant)</td>
            <td>
                @if(isset($isExcel))
                    {{ $fmt($data['enc_ventes'] ?? 0) }}
                @else
                    <input type="text" class="treso-input" value="{{ $fmt($data['enc_ventes'] ?? 0) }}" readonly style="background:#f8fafc">
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">AB</td>
            <td>Encaissements de créances (Clients &amp; tiers)</td>
            <td>
                @if(isset($isExcel))
                    {{ $fmt($data['MT_TFTA_AB'] ?? 0) }}
                @else
                    <input type="text" class="treso-input" value="{{ $data['MT_TFTA_AB'] ?? '' }}" placeholder="0">
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">AC</td>
            <td>Produits divers et subventions</td>
            <td>
                @if(isset($isExcel))
                    {{ $fmt($data['enc_divers'] ?? 0) }}
                @else
                    <input type="text" class="treso-input" value="{{ $fmt($data['enc_divers'] ?? 0) }}">
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">AD</td>
            <td>Cessions d'immobilisations</td>
            <td>
                @if(isset($isExcel))
                    {{ $fmt($data['MT_TFTA_AD'] ?? 0) }}
                @else
                    <input type="text" class="treso-input" value="{{ $data['MT_TFTA_AB'] ?? '' }}" placeholder="0">
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">AE</td>
            <td>Augmentation de capital / Apports des associés</td>
            <td>
                @if(isset($isExcel))
                    {{ $fmt($data['MT_TFTA_AE'] ?? 0) }}
                @else
                    <input type="text" class="treso-input" value="{{ $data['MT_TFTA_AE'] ?? '' }}" placeholder="0">
                @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">AF</td>
            <td>Emprunts et autres dettes financières</td>
            <td>
                @if(isset($isExcel))
                    {{ $fmt($data['MT_TFTA_AF'] ?? 0) }}
                @else
                    <input type="text" class="treso-input" value="{{ $data['MT_TFTA_AF'] ?? '' }}" placeholder="0">
                @endif
            </td>
        </tr>
        <tr class="treso-total">
            <td class="text-center fw-bold">AZ</td>
            <td class="fw-bold">TOTAL DES ENCAISSEMENTS (AA à AF)</td>
            <td class="text-end fw-bold px-4 fs-5">{{ number_format(
                ($data['enc_ventes'] ?? 0) + 
                ($data['enc_divers'] ?? 0) + 
                floatval($data['MT_TFTA_AB'] ?? 0) + 
                floatval($data['MT_TFTA_AD'] ?? 0) + 
                floatval($data['MT_TFTA_AE'] ?? 0) + 
                floatval($data['MT_TFTA_AF'] ?? 0),
                0, ',', ' '
            ) }}</td>
        </tr>
    </tbody>
</table>
