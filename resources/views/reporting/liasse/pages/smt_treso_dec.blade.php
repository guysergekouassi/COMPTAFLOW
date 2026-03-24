{{-- SMT - 6. TRÉSORERIE : DÉCAISSEMENTS (Dépenses) --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.treso-table{width:100%;border-collapse:separate;border-spacing:0;border-radius:12px;overflow:hidden;border:1.5px solid #e5e7eb}
.treso-table th{background:#f9fafb;padding:15px;font-size:.70rem;text-transform:uppercase;color:#dc2626;font-weight:700;border-bottom:1.5px solid #e5e7eb}
.treso-table td{padding:12px 15px;border-bottom:1px solid #f3f4f6;background:#fff;font-weight:600}
.treso-input{width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 12px;text-align:right;font-weight:700;background:#fdfdfd;color:#111827}
.treso-total{background:#fee2e2!important;color:#991b1b;font-size:1.05rem}
</style>
@endif

@if(!isset($isExcel))
<div class="mb-4 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-trend-down" style="color:#dc2626;font-size:1.3rem"></i>
        <h5 class="mb-0 fw-bold">TRÉSORERIE : DÉCAISSEMENTS <span class="smt-badge">SMT</span></h5>
    </div>
    <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">DÉPENSES</div>
</div>
@endif

<table class="treso-table mb-4 shadow-sm">
    <thead>
        <tr>
            <th style="width:50px">CODE</th>
            <th>NATURE DES DÉCAISSEMENTS</th>
            <th class="text-end" style="width:250px">MONTANT DE L'EXERCICE (N)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center text-muted small">BA</td>
            <td>Achats de marchandises et matières (Comptant)</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['dec_achats'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $fmt($data['dec_achats'] ?? 0) }}" readonly style="background:#f8fafc"> @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">BC</td>
            <td>Services extérieurs et transports</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['dec_services'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $fmt($data['dec_services'] ?? 0) }}" readonly style="background:#f8fafc"> @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">BK</td>
            <td>Charges de personnel et frais sociaux</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['dec_pers'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $fmt($data['dec_pers'] ?? 0) }}" readonly style="background:#f8fafc"> @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">BI</td>
            <td>Impôts et taxes</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['dec_impots'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $fmt($data['dec_impots'] ?? 0) }}" readonly style="background:#f8fafc"> @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">BM</td>
            <td>Acquisitions d'immobilisations</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['MT_TFTB_BM'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $data['MT_TFTB_BM'] ?? '' }}" placeholder="0"> @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">BN</td>
            <td>Remboursements d'emprunts et frais financiers</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['MT_TFTB_BN'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $data['MT_TFTB_BN'] ?? '' }}" placeholder="0"> @endif
            </td>
        </tr>
        <tr>
            <td class="text-center text-muted small">BO</td>
            <td>Retraits des exploitants et dividendes</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['MT_TFTB_BO'] ?? 0) }} @else <input type="text" class="treso-input" value="{{ $data['MT_TFTB_BO'] ?? '' }}" placeholder="0"> @endif
            </td>
        </tr>
        <tr class="treso-total">
            <td class="text-center fw-bold">BZ</td>
            <td class="fw-bold">TOTAL DES DÉCAISSEMENTS (BA à BO)</td>
            <td class="text-end fw-bold px-4 fs-5">{{ number_format(
                ($data['dec_achats'] ?? 0) + 
                ($data['dec_services'] ?? 0) + 
                ($data['dec_pers'] ?? 0) + 
                ($data['dec_impots'] ?? 0) + 
                floatval($data['MT_TFTB_BM'] ?? 0) + 
                floatval($data['MT_TFTB_BN'] ?? 0) + 
                floatval($data['MT_TFTB_BO'] ?? 0),
                0, ',', ' '
            ) }}</td>
        </tr>
    </tbody>
</table>
