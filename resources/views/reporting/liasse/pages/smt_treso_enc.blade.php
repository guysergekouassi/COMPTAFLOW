{{-- SMT - 5. TRÉSORERIE : ENCAISSEMENTS (Recettes) --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.treso-card{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:20px;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);}
.treso-header{background:#f0fdf4;padding:14px 20px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#166534;display:flex;justify-content:space-between;align-items:center}
.treso-body{padding:0}
.treso-row{display:grid;grid-template-columns:50px 1fr 220px;border-bottom:1px solid #f1f5f9;align-items:center;transition: background 0.2s;}
.treso-row:hover { background: #fcfdfe; }
.treso-row:last-child{border-bottom:none}
.treso-cell{padding:12px 20px;font-weight:600;color:#334155}
.treso-code{color:#94a3b8;font-family:monospace;font-size:.75rem;text-align:center}
.liasse-input{border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px;text-align:right;font-weight:700;background:#fcfdfe;color:#1e293b;width:100%;transition:all 0.2s}
.liasse-input:focus{outline:none;border-color:#22c55e;background:#fff;box-shadow:0 0 0 4px rgba(34,197,94,0.1)}
.treso-total-row{background:#f0fdf4;font-weight:800;color:#166534;font-size:1rem}
.section-title { font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
.section-title i { color: #10b981; }
</style>

<div class="mb-4">
    <div class="section-title"><i class="fa-solid fa-arrow-trend-up"></i> TRÉSORERIE : ENCAISSEMENTS (FLUX DE TRÉSORERIE DISPONIBLE)</div>
    
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3" style="background:#f0fdf4; color:#166534">
        <i class="fa-solid fa-circle-info fs-4"></i>
        <div class="small">Saisissez les flux de trésorerie qui ne sont pas automatiquement extraits de votre comptabilité.</div>
    </div>

    <div class="treso-card">
        <div class="treso-header">
            <span>NATURE DES ENCAISSEMENTS</span>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">RECETTES</span>
        </div>
        <div class="treso-body">
            {{-- AA --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">AA</div>
                <div class="treso-cell">Ventes et prestations de services (Comptant)</div>
                <div class="treso-cell">
                    <input type="text" class="liasse-input" value="{{ $fmt($data['enc_ventes'] ?? 0) }}" readonly style="background:#f8fafc; color:#64748b; border-style: dashed;">
                </div>
            </div>
            {{-- AB --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">AB</div>
                <div class="treso-cell">Encaissements de créances (Clients &amp; tiers)</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTA_AB" value="{{ $data['MT_TFTA_AB'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- AC --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">AC</div>
                <div class="treso-cell">Produits divers et subventions</div>
                <div class="treso-cell">
                    <input type="text" class="liasse-input" value="{{ $fmt($data['enc_divers'] ?? 0) }}" readonly style="background:#f8fafc; color:#64748b; border-style: dashed;">
                </div>
            </div>
            {{-- AD --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">AD</div>
                <div class="treso-cell">Cessions d'immobilisations</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTA_AD" value="{{ $data['MT_TFTA_AD'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- AE --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">AE</div>
                <div class="treso-cell">Augmentation de capital / Apports des associés</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTA_AE" value="{{ $data['MT_TFTA_AE'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- AF --}}
            <div class="treso-row">
                <div class="treso-cell treso-code">AF</div>
                <div class="treso-cell">Emprunts et autres dettes financières</div>
                <div class="treso-cell">
                    <input type="number" class="liasse-input" name="MT_TFTA_AF" value="{{ $data['MT_TFTA_AF'] ?? 0 }}" placeholder="0">
                </div>
            </div>
            {{-- TOTAL --}}
            <div class="treso-row treso-total-row">
                <div class="treso-cell treso-code" style="color:inherit">AZ</div>
                <div class="treso-cell">TOTAL DES ENCAISSEMENTS (AA à AF)</div>
                <div class="treso-cell text-end px-4">
                    {{ number_format(
                        ($data['enc_ventes'] ?? 0) + 
                        ($data['enc_divers'] ?? 0) + 
                        floatval($data['MT_TFTA_AB'] ?? 0) + 
                        floatval($data['MT_TFTA_AD'] ?? 0) + 
                        floatval($data['MT_TFTA_AE'] ?? 0) + 
                        floatval($data['MT_TFTA_AF'] ?? 0),
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
            <th style="background-color: #f0fdf4; color: #166534; padding: 12px; border: 1px solid #e2e8f0; text-align: left; width: 50px;">CODE</th>
            <th style="background-color: #f0fdf4; color: #166534; padding: 12px; border: 1px solid #e2e8f0; text-align: left;">NATURE DES ENCAISSEMENTS</th>
            <th style="background-color: #f0fdf4; color: #166534; padding: 12px; border: 1px solid #e2e8f0; text-align: right; width: 200px;">MONTANT (N)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AA</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Ventes et prestations de services (Comptant)</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['enc_ventes'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AB</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Encaissements de créances (Clients & tiers)</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTA_AB'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AC</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Produits divers et subventions</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['enc_divers'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AD</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Cessions d'immobilisations</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTA_AD'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AE</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Augmentation de capital / Apports des associés</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTA_AE'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AF</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Emprunts et autres dettes financières</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['MT_TFTA_AF'] ?? 0) }}</td>
        </tr>
        <tr style="background-color: #f0fdf4; font-weight: bold;">
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">AZ</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">TOTAL DES ENCAISSEMENTS</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ number_format(
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
@endif
