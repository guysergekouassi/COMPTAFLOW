{{-- SMT - 6. TRÉSORERIE : DÉCAISSEMENTS (Dépenses) --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.treso-card{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:20px;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);}
.treso-header{background:#fef2f2;padding:14px 20px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#991b1b;display:flex;justify-content:space-between;align-items:center}
.treso-body{padding:0}
.treso-row{display:grid;grid-template-columns:50px 1fr 220px;border-bottom:1px solid #f1f5f9;align-items:center;transition: background 0.2s;}
.treso-row:hover { background: #fffafb; }
.treso-row:last-child{border-bottom:none}
.treso-cell{padding:12px 20px;font-weight:600;color:#334155}
.treso-code{color:#94a3b8;font-family:monospace;font-size:.75rem;text-align:center}
.liasse-input{border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px;text-align:right;font-weight:700;background:#fcfdfe;color:#1e293b;width:100%;transition:all 0.2s}
.liasse-input:focus{outline:none;border-color:#ef4444;background:#fff;box-shadow:0 0 0 4px rgba(239,68,68,0.1)}
.treso-total-row{background:#fef2f2;font-weight:800;color:#991b1b;font-size:1rem}
.section-title { font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
.section-title i { color: #dc2626; }
</style>

<div class="mb-4">
    <div class="section-title"><i class="fa-solid fa-arrow-trend-down"></i> TRÉSORERIE : DÉCAISSEMENTS (DÉPENSES & INVESTISSEMENTS)</div>
    
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3" style="background:#fef2f2; color:#991b1b">
        <i class="fa-solid fa-circle-exclamation fs-4"></i>
        <div class="small">Veuillez renseigner les décaissements manuels tels que les investissements ou les remboursements de dettes.</div>
    </div>

    <div class="treso-card">
        <div class="treso-header">
            <span>NATURE DES DÉCAISSEMENTS</span>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">DÉPENSES</span>
        </div>
        <div class="treso-body">
            {{-- BA --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BA</div>
                <div class="treso-cell">Achats de marchandises et matières (Comptant)</div>
                <div class="treso-cell">
                    <input type="text" class="liasse-input" value="{{ $fmt($data['dec_achats'] ?? 0) }}" readonly style="background:#f8fafc; color:#64748b; border-style: dashed;">
                </div>
            </div>
            {{-- BC --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BC</div>
                <div class="treso-cell">Services extérieurs et transports</div>
                <div class="treso-cell">
                    <input type="text" class="liasse-input" value="{{ $fmt($data['dec_services'] ?? 0) }}" readonly style="background:#f8fafc; color:#64748b; border-style: dashed;">
                </div>
            </div>
            {{-- BK --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BK</div>
                <div class="treso-cell">Charges de personnel et frais sociaux</div>
                <div class="treso-cell">
                    <input type="text" class="liasse-input" value="{{ $fmt($data['dec_pers'] ?? 0) }}" readonly style="background:#f8fafc; color:#64748b; border-style: dashed;">
                </div>
            </div>
            {{-- BI --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BI</div>
                <div class="treso-cell">Impôts et taxes</div>
                <div class="treso-cell">
                    <input type="text" class="liasse-input" value="{{ $fmt($data['dec_impots'] ?? 0) }}" readonly style="background:#f8fafc; color:#64748b; border-style: dashed;">
                </div>
            </div>
            {{-- BM --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BM</div>
                <div class="treso-cell">Acquisitions d'immobilisations</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTB_BM" value="{{ $data['MT_TFTB_BM'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- BN --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BN</div>
                <div class="treso-cell">Remboursements d'emprunts et frais financiers</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTB_BN" value="{{ $data['MT_TFTB_BN'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- BO --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">BO</div>
                <div class="treso-cell">Retraits des exploitants et dividendes</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTB_BO" value="{{ $data['MT_TFTB_BO'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- TOTAL --}}
            <div class="treso-row treso-total-row">
                <div class="treso-cell treso-code" style="color:inherit">BZ</div>
                <div class="treso-cell">TOTAL DES DÉCAISSEMENTS (BA à BO)</div>
                <div class="treso-cell text-end px-4">
                    {{ number_format(
                        ($data['dec_achats'] ?? 0) + 
                        ($data['dec_services'] ?? 0) + 
                        ($data['dec_pers'] ?? 0) + 
                        ($data['dec_impots'] ?? 0) + 
                        floatval($data['MT_TFTB_BM'] ?? 0) + 
                        floatval($data['MT_TFTB_BN'] ?? 0) + 
                        floatval($data['MT_TFTB_BO'] ?? 0),
                        0, ',', ' '
                    ) }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($isExcel) || isset($isPdf))
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: #fef2f2; color: #991b1b; padding: 12px; border: 1px solid #e2e8f0; text-align: left; width: 50px;">CODE</th>
            <th style="background-color: #fef2f2; color: #991b1b; padding: 12px; border: 1px solid #e2e8f0; text-align: left;">NATURE DES DÉCAISSEMENTS</th>
            <th style="background-color: #fef2f2; color: #991b1b; padding: 12px; border: 1px solid #e2e8f0; text-align: right; width: 200px;">MONTANT (N)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BA</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Achats (Comptant)</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['dec_achats'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BC</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Services extérieurs</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['dec_services'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BK</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Charges de personnel</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['dec_pers'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BI</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Impôts et taxes</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['dec_impots'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BM</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Acquisitions d'immobilisations</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTB_BM'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BN</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Remboursements d'emprunts</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTB_BN'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BO</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Retraits exploitants</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTB_BO'] ?? 0) }}</td>
        </tr>
        <tr style="background-color: #fef2f2; font-weight: bold;">
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">BZ</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">TOTAL DES DÉCAISSEMENTS</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ number_format(
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
@endif
