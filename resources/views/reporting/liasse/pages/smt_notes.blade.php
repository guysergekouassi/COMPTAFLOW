{{-- SMT - 7. NOTES ANNEXES CONSOLIDÉES (NOTE 1 à 13) --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp

@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.note-section{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:25px}
.note-header{background:#f8fafc;padding:12px 18px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#334155;display:flex;align-items:center;gap:10px}
.note-body{padding:20px}
.note-table{width:100%;border-collapse:collapse}
.note-table td{padding:10px;border-bottom:1px solid #f1f5f9;font-weight:600}
.note-input{width:100%;border:1.5px solid #e5e7eb;border-radius:8px;padding:8px 12px;font-weight:700;background:#fdfdfd}
</style>

<div class="mb-4 d-flex align-items-center gap-2">
    <i class="fa-solid fa-file-invoice" style="color:#d97706;font-size:1.3rem"></i>
    <h5 class="mb-0 fw-bold">NOTES ANNEXES <span class="smt-badge">SMT</span></h5>
</div>

{{-- NOTE 1 : IMMOBILISATIONS --}}
<div class="note-section shadow-sm">
    <div class="note-header"><span class="badge bg-secondary">NOTE 1</span> IMMOBILISATIONS &amp; AMORTISSEMENTS</div>
    <div class="note-body">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="small text-muted fw-bold mb-2">VALEUR BRUTE (DÉBUT EXER.)</label>
                <div class="p-3 border rounded-3 bg-light fw-bold text-end fs-5">{{ $fmt($data['immoBrut'] ?? 0) }}</div>
            </div>
            <div class="col-md-6">
                <label class="small text-muted fw-bold mb-2">AMORTISSEMENTS (DÉBUT EXER.)</label>
                <div class="p-3 border rounded-3 bg-light fw-bold text-end fs-5">{{ $fmt($data['immoAmort'] ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- NOTE 2 : MÉTHODES COMPTABLES --}}
<div class="note-section shadow-sm">
    <div class="note-header"><span class="badge bg-secondary">NOTE 2</span> MÉTHODES COMPTABLES</div>
    <div class="note-body">
        <label class="small text-muted fw-bold mb-2">DÉROGATIONS / RÈGLES PARTICULIÈRES</label>
        <textarea class="note-input w-100" rows="3" placeholder="Texte libre...">{{ $data['MT_NOTE2_1'] ?? 'Ces états ont été présentés selon le SYSCOHADA Révisé.' }}</textarea>
    </div>
</div>

{{-- NOTE 6 : EFFECTIFS --}}
<div class="note-section shadow-sm">
    <div class="note-header"><span class="badge bg-secondary">NOTE 6</span> EFFECTIFS &amp; MASSE SALARIALE</div>
    <div class="note-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex justify-content-between border-bottom pb-2">
                    <span class="text-muted">Masse Salariale calculée</span>
                    <span class="fw-bold">{{ $fmt($data['charges_pers'] ?? 0) }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3">
                    <label class="small text-muted fw-bold text-nowrap">NOMBRE D'EMPLOYÉS</label>
                    <input type="number" class="note-input text-center" value="{{ $data['MT_NOTE6_1'] ?? 0 }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- NOTE 13 : ENGAGEMENTS --}}
<div class="note-section shadow-sm">
    <div class="note-header"><span class="badge bg-secondary">NOTE 13</span> ENGAGEMENTS FINANCIERS</div>
    <div class="note-body">
        <div class="row">
            <div class="col-md-6 border-end">
                <label class="small text-danger fw-bold mb-2">ENGAGEMENTS DONNÉS</label>
                <input type="text" class="note-input mb-2" value="{{ $data['MT_NOTE13_A'] ?? '' }}" placeholder="Avals, Cautions donnés...">
            </div>
            <div class="col-md-6">
                <label class="small text-success fw-bold mb-2">ENGAGEMENTS REÇUS</label>
                <input type="text" class="note-input" value="{{ $data['MT_NOTE13_B'] ?? '' }}" placeholder="Avals, Cautions reçus...">
            </div>
        </div>
    </div>
</div>
@endif

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="2" style="background-color: #f8fafc; font-weight: bold; border: 1px solid #e5e7eb; padding: 10px;">NOTES ANNEXES SMT</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2" style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #e5e7eb;">NOTE 1 : IMMOBILISATIONS</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb;">Valeur Brute (Début)</td>
            <td style="border: 1px solid #e5e7eb; text-align: right;">{{ $fmt($data['immoBrut'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb;">Amortissements (Début)</td>
            <td style="border: 1px solid #e5e7eb; text-align: right;">{{ $fmt($data['immoAmort'] ?? 0) }}</td>
        </tr>
        <tr>
            <td colspan="2" style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #e5e7eb;">NOTE 2 : MÉTHODES COMPTABLES</td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #e5e7eb;">{{ $data['MT_NOTE2_1'] ?? 'Ces états ont été présentés selon le SYSCOHADA Révisé.' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #e5e7eb;">NOTE 6 : EFFECTIFS &amp; MASSE SALARIALE</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb;">Masse Salariale</td>
            <td style="border: 1px solid #e5e7eb; text-align: right;">{{ $fmt($data['charges_pers'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb;">Nombre d'employés</td>
            <td style="border: 1px solid #e5e7eb; text-align: right;">{{ $data['MT_NOTE6_1'] ?? 0 }}</td>
        </tr>
        <tr>
            <td colspan="2" style="background-color: #f1f5f9; font-weight: bold; border: 1px solid #e5e7eb;">NOTE 13 : ENGAGEMENTS FINANCIERS</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb;">Engagements Donnés</td>
            <td style="border: 1px solid #e5e7eb;">{{ $data['MT_NOTE13_A'] ?? 'Néant' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb;">Engagements Reçus</td>
            <td style="border: 1px solid #e5e7eb;">{{ $data['MT_NOTE13_B'] ?? 'Néant' }}</td>
        </tr>
    </tbody>
</table>
