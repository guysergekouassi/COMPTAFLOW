{{-- SMT PASSAGE RÉSULTAT FISCAL --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle;}
.fiscal-card{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:20px;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);}
.fiscal-header{background:#f8fafc;padding:14px 20px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#334155;display:flex;justify-content:space-between;align-items:center}
.fiscal-body{padding:0}
.fiscal-row{display:grid;grid-template-columns:80px 1fr 220px;border-bottom:1px solid #f1f5f9;align-items:center;transition: background 0.2s;}
.fiscal-row:hover { background: #fcfdfe; }
.fiscal-row:last-child{border-bottom:none}
.fiscal-cell{padding:12px 20px;font-weight:600;color:#334155}
.fiscal-code{color:#94a3b8;font-family:monospace;font-size:.75rem;text-align:center}
.liasse-input{border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px;text-align:right;font-weight:700;background:#fcfdfe;color:#1e293b;width:100%;transition:all 0.2s}
.liasse-input:focus{outline:none;border-color:#2563eb;background:#fff;box-shadow:0 0 0 4px rgba(37,99,235,0.1)}
.section-title { font-size: 0.95rem; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
.section-title i { color: #f59e0b; }
.fiscal-section-sep { background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; padding: 6px 20px; letter-spacing: 0.05em; }
.fiscal-total-row { background: #f1f5f9; font-weight: 800; }
.fiscal-grand-total { background: #eff6ff; color: #1e40af; font-size: 1rem; border-top: 2px solid #2563eb; }
</style>

<div class="mb-4">
    <div class="section-title"><i class="fa-solid fa-calculator"></i> DÉTERMINATION DU RÉSULTAT FISCAL</div>
    
    <div class="alert alert-warning border-0 rounded-4 mb-4 d-flex align-items-center gap-3" style="background:#fffbeb; border-left:4px solid #f59e0b !important;">
        <i class="fa-solid fa-circle-info fs-4" style="color:#f59e0b"></i>
        <div class="small">Saisissez les réintégrations (charges non déductibles) et les déductions (produits exonérés) pour obtenir votre résultat fiscal imposable.</div>
    </div>

    <div class="fiscal-card">
        <div class="fiscal-header">
            <span>TABLEAU DE PASSAGE AU RÉSULTAT FISCAL</span>
            <span class="smt-badge">SMT</span>
        </div>
        <div class="fiscal-body">
            <div class="fiscal-section-sep">RÉSULTAT COMPTABLE</div>
            <div class="fiscal-row">
                <div class="fiscal-cell fiscal-code">XS</div>
                <div class="fiscal-cell">Résultat net comptable de l'exercice</div>
                <div class="fiscal-cell text-end {{ ($data['resultat_comptable'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $fmt($data['resultat_comptable'] ?? 0) }}
                </div>
            </div>

            <div class="fiscal-section-sep">RÉINTÉGRATIONS FISCALES</div>
            <div class="fiscal-row">
                <div class="fiscal-cell fiscal-code">RI1</div>
                <div class="fiscal-cell">Amendes, pénalités, majorations fiscales</div>
                <div class="fiscal-cell">
                    <input type="number" class="liasse-input" name="ri_amendes" value="{{ $data['ri_amendes'] ?? 0 }}" onchange="updateFiscal()">
                </div>
            </div>
            <div class="fiscal-row">
                <div class="fiscal-cell fiscal-code">RI2</div>
                <div class="fiscal-cell">Charges personnelles des dirigeants non admises</div>
                <div class="fiscal-cell">
                    <input type="number" class="liasse-input" name="ri_dirigeants" value="{{ $data['ri_dirigeants'] ?? 0 }}" onchange="updateFiscal()">
                </div>
            </div>
            <div class="fiscal-row">
                <div class="fiscal-cell fiscal-code">RI3</div>
                <div class="fiscal-cell">Autres charges non déductibles</div>
                <div class="fiscal-cell">
                    <input type="number" class="liasse-input" name="ri_autres" value="{{ $data['ri_autres'] ?? 0 }}" onchange="updateFiscal()">
                </div>
            </div>
            <div class="fiscal-row fiscal-total-row">
                <div class="fiscal-cell fiscal-code text-warning">XI1</div>
                <div class="fiscal-cell text-warning">TOTAL RÉINTÉGRATIONS</div>
                <div class="fiscal-cell text-end text-warning" id="total-reintegrations">{{ $fmt($data['reintegrations'] ?? 0) }}</div>
            </div>

            <div class="fiscal-section-sep">DÉDUCTIONS FISCALES</div>
            <div class="fiscal-row">
                <div class="fiscal-cell fiscal-code">DE1</div>
                <div class="fiscal-cell">Produits exonérés d'IS</div>
                <div class="fiscal-cell">
                    <input type="number" class="liasse-input" name="de_exoneres" value="{{ $data['de_exoneres'] ?? 0 }}" onchange="updateFiscal()">
                </div>
            </div>
            <div class="fiscal-row">
                <div class="fiscal-cell fiscal-code">DE2</div>
                <div class="fiscal-cell">Autres déductions fiscales</div>
                <div class="fiscal-cell">
                    <input type="number" class="liasse-input" name="de_autres" value="{{ $data['de_autres'] ?? 0 }}" onchange="updateFiscal()">
                </div>
            </div>
            <div class="fiscal-row fiscal-total-row">
                <div class="fiscal-cell fiscal-code text-info">XI2</div>
                <div class="fiscal-cell text-info">TOTAL DÉDUCTIONS</div>
                <div class="fiscal-cell text-end text-info" id="total-deductions">{{ $fmt($data['deductions'] ?? 0) }}</div>
            </div>

            <div class="fiscal-row fiscal-grand-total">
                <div class="fiscal-cell fiscal-code" style="color:inherit">XW</div>
                <div class="fiscal-cell">
                    RÉSULTAT FISCAL IMPOSABLE
                    <div style="font-size: 0.7rem; font-weight: 400; opacity: 0.8;">= Résultat comptable + Réintégrations − Déductions</div>
                </div>
                <div class="fiscal-cell text-end fs-5" id="resultat-fiscal">
                    {{ $fmt($data['resultat_fiscal'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateFiscal() {
    var comptable = {{ floatval($data['resultat_comptable'] ?? 0) }};
    var ri = (parseFloat(document.querySelector('[name=ri_amendes]')?.value)||0)
           + (parseFloat(document.querySelector('[name=ri_dirigeants]')?.value)||0)
           + (parseFloat(document.querySelector('[name=ri_autres]')?.value)||0);
    var de = (parseFloat(document.querySelector('[name=de_exoneres]')?.value)||0)
           + (parseFloat(document.querySelector('[name=de_autres]')?.value)||0);
    var fiscal = comptable + ri - de;
    var fmtNum = n => new Intl.NumberFormat('fr-FR').format(Math.round(n));
    document.getElementById('total-reintegrations').textContent = fmtNum(ri);
    document.getElementById('total-deductions').textContent = fmtNum(de);
    document.getElementById('resultat-fiscal').textContent = fmtNum(fiscal);
}
</script>
@endif

@if(isset($isExcel) || isset($isPdf))
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: left;">CODE</th>
            <th style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: left;">LIBELLÉ</th>
            <th style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: right;">MONTANT (N)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">XS</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Résultat net comptable</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['resultat_comptable'] ?? 0) }}</td>
        </tr>
        <tr><td colspan="3" style="background-color: #fef3c7; padding: 6px; font-weight: bold;">RÉINTÉGRATIONS</td></tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">RI1</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Amendes et pénalités</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['ri_amendes'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">RI2</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Charges dirigeants</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['ri_dirigeants'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">RI3</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Autres charges non déductibles</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['ri_autres'] ?? 0) }}</td>
        </tr>
        <tr><td colspan="3" style="background-color: #fef3c7; padding: 6px; font-weight: bold;">DÉDUCTIONS</td></tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">DE1</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Produits exonérés</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['de_exoneres'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">DE2</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">Autres déductions</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['de_autres'] ?? 0) }}</td>
        </tr>
        <tr style="background-color: #eff6ff; font-weight: 800;">
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: center;">XW</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">RÉSULTAT FISCAL IMPOSABLE</td>
            <td style="border: 1px solid #e2e8f0; padding: 8px; text-align: right;">{{ $fmt($data['resultat_fiscal'] ?? 0) }}</td>
        </tr>
    </tbody>
</table>
@endif
