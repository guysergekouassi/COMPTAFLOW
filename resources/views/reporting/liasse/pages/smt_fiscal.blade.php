{{-- SMT PASSAGE RÉSULTAT FISCAL --}}
@php
    $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' ');
@endphp
@if(!isset($isExcel))
<style>
    .smt-badge { background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle; }
    .fiscal-section { background:linear-gradient(90deg,#fffbeb,#fef3c7);color:#92400e;font-weight:800;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em; }
    .fiscal-total { background:#f1f5f9;font-weight:800; }
    .fiscal-grand-total { background:linear-gradient(90deg,#eff6ff,#dbeafe);color:#1e40af;font-weight:900; }
    .ref-code { font-size:0.68rem;color:#94a3b8;font-weight:700;width:50px;text-align:center; }
    .num-right { text-align:right;font-variant-numeric:tabular-nums;font-weight:700; }
    .liasse-input { width:100%;border:1.5px solid #e2e8f0;padding:6px 10px;text-align:right;border-radius:8px;transition:all 0.2s;font-weight:700;background:#fafafa; }
    .liasse-input:focus { background:white;border-color:#2563eb;outline:none;box-shadow:0 0 0 4px rgba(37,99,235,0.1); }
</style>
@endif

@if(!isset($isExcel))
<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-calculator" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Passage Résultat Fiscal <span class="smt-badge">SMT</span></h5>
        <small class="text-muted">Détermination du résultat fiscal à partir du résultat comptable</small>
    </div>
</div>

<div class="alert alert-warning border-0 rounded-3 mb-3" style="background:#fffbeb;border-left:4px solid #f59e0b !important;">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Note :</strong> Le résultat comptable est calculé automatiquement depuis la balance. Saisissez les réintégrations et déductions fiscales manuellement.
</div>
@endif

<table class="liasse-table">
    <thead>
        <tr>
            <th class="ref-code">REF</th>
            <th>LIBELLÉ</th>
            <th class="num-right" style="width:220px;">MONTANT (FCFA)</th>
        </tr>
    </thead>
    <tbody>
        {{-- RÉSULTAT COMPTABLE --}}
        <tr class="fiscal-section"><td colspan="3" class="px-3 py-2">RÉSULTAT COMPTABLE</td></tr>
        <tr>
            <td class="ref-code">XS</td>
            <td>Résultat net comptable de l'exercice</td>
            <td class="num-right {{ ($data['resultat_comptable'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $fmt($data['resultat_comptable'] ?? 0) }}
            </td>
        </tr>

        {{-- RÉINTÉGRATIONS --}}
        <tr class="fiscal-section"><td colspan="3" class="px-3 py-2">RÉINTÉGRATIONS FISCALES (charges non déductibles)</td></tr>
        <tr>
            <td class="ref-code">RI1</td>
            <td>Amendes, pénalités, majorations fiscales</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['ri_amendes'] ?? 0) }} @else <input type="number" class="liasse-input" name="ri_amendes" value="{{ $data['ri_amendes'] ?? 0 }}" placeholder="0" onchange="updateFiscal()"> @endif
            </td>
        </tr>
        <tr>
            <td class="ref-code">RI2</td>
            <td>Charges personnelles des dirigeants non admises</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['ri_dirigeants'] ?? 0) }} @else <input type="number" class="liasse-input" name="ri_dirigeants" value="{{ $data['ri_dirigeants'] ?? 0 }}" placeholder="0" onchange="updateFiscal()"> @endif
            </td>
        </tr>
        <tr>
            <td class="ref-code">RI3</td>
            <td>Autres charges non déductibles</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['ri_autres'] ?? 0) }} @else <input type="number" class="liasse-input" name="ri_autres" value="{{ $data['ri_autres'] ?? 0 }}" placeholder="0" onchange="updateFiscal()"> @endif
            </td>
        </tr>
        <tr class="fiscal-total">
            <td class="ref-code">XI1</td>
            <td>TOTAL RÉINTÉGRATIONS</td>
            <td class="num-right text-warning" id="total-reintegrations">{{ $fmt($data['reintegrations'] ?? 0) }}</td>
        </tr>

        {{-- DÉDUCTIONS --}}
        <tr class="fiscal-section"><td colspan="3" class="px-3 py-2">DÉDUCTIONS FISCALES (produits non imposables)</td></tr>
        <tr>
            <td class="ref-code">DE1</td>
            <td>Produits exonérés d'IS</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['de_exoneres'] ?? 0) }} @else <input type="number" class="liasse-input" name="de_exoneres" value="{{ $data['de_exoneres'] ?? 0 }}" placeholder="0" onchange="updateFiscal()"> @endif
            </td>
        </tr>
        <tr>
            <td class="ref-code">DE2</td>
            <td>Autres déductions fiscales</td>
            <td>
                @if(isset($isExcel)) {{ $fmt($data['de_autres'] ?? 0) }} @else <input type="number" class="liasse-input" name="de_autres" value="{{ $data['de_autres'] ?? 0 }}" placeholder="0" onchange="updateFiscal()"> @endif
            </td>
        </tr>
        <tr class="fiscal-total">
            <td class="ref-code">XI2</td>
            <td>TOTAL DÉDUCTIONS</td>
            <td class="num-right text-info" id="total-deductions">{{ $fmt($data['deductions'] ?? 0) }}</td>
        </tr>

        {{-- RÉSULTAT FISCAL --}}
        <tr class="fiscal-grand-total">
            <td class="ref-code" style="color:#1e40af;">XW</td>
            <td>RÉSULTAT FISCAL IMPOSABLE<br><small style="font-weight:400;color:#64748b;">= Résultat comptable + Réintégrations − Déductions</small></td>
            <td class="num-right fs-5" id="resultat-fiscal">{{ $fmt($data['resultat_fiscal'] ?? 0) }}</td>
        </tr>
    </tbody>
</table>

@if(!isset($isExcel))
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary rounded-pill px-4" onclick="savePageData()">
        <i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer les ajustements fiscaux
    </button>
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
