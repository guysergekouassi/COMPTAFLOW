{{-- SMT - 7. NOTES ANNEXES CONSOLIDÉES (NOTE 1 à 13) --}}
@php $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' '); @endphp
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.note-card{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:24px;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);}
.note-header{background:#f8fafc;padding:14px 20px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#334155;display:flex;align-items:center;gap:12px}
.note-body{padding:24px}
.note-label{font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:8px;display:block;letter-spacing:0.025em}
.liasse-input{border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 14px;font-weight:700;background:#fcfdfe;color:#1e293b;width:100%;transition:all 0.2s}
.liasse-input:focus{outline:none;border-color:#2563eb;background:#fff;box-shadow:0 0 0 4px rgba(37,99,235,0.1)}
.section-title { font-size: 1rem; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.section-title i { color: #d97706; }
.note-badge-num { background: #64748b; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; }
</style>

<div class="mb-4">
    <div class="section-title"><i class="fa-solid fa-file-lines"></i> NOTES ANNEXES CONSOLIDÉES</div>

    {{-- NOTE 1 : IMMOBILISATIONS --}}
    <div class="note-card">
        <div class="note-header"><span class="note-badge-num">NOTE 1</span> IMMOBILISATIONS & AMORTISSEMENTS</div>
        <div class="note-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="note-label">VALEUR BRUTE (DÉBUT EXER.)</label>
                    <div class="p-3 border rounded-3 bg-light fw-bold text-end fs-5" style="color: #475569;">{{ $fmt($data['immoBrut'] ?? 0) }}</div>
                </div>
                <div class="col-md-6">
                    <label class="note-label">AMORTISSEMENTS (DÉBUT EXER.)</label>
                    <div class="p-3 border rounded-3 bg-light fw-bold text-end fs-5" style="color: #475569;">{{ $fmt($data['immoAmort'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTE 2 : MÉTHODES COMPTABLES --}}
    <div class="note-card">
        <div class="note-header"><span class="note-badge-num">NOTE 2</span> MÉTHODES COMPTABLES</div>
        <div class="note-body">
            <label class="note-label">DÉROGATIONS / RÈGLES PARTICULIÈRES</label>
            <textarea class="liasse-input" name="MT_NOTE2_1" rows="3" placeholder="Saisissez les éventuelles dérogations...">{{ $data['MT_NOTE2_1'] ?? 'Ces états ont été présentés selon le SYSCOHADA Révisé.' }}</textarea>
        </div>
    </div>

    {{-- NOTE 6 : EFFECTIFS --}}
    <div class="note-card">
        <div class="note-header"><span class="note-badge-num">NOTE 6</span> EFFECTIFS & MASSE SALARIALE</div>
        <div class="note-body">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border">
                        <span class="fw-bold text-muted small uppercase">Masse Salariale (Calculée)</span>
                        <span class="fw-800 text-dark fs-5">{{ $fmt($data['charges_pers'] ?? 0) }}</span>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="note-label">NOMBRE D'EMPLOYÉS</label>
                    <input type="number" class="liasse-input text-center" name="MT_NOTE6_1" value="{{ $data['MT_NOTE6_1'] ?? 0 }}">
                </div>
            </div>
        </div>
    </div>

    {{-- NOTE 13 : ENGAGEMENTS --}}
    <div class="note-card">
        <div class="note-header"><span class="note-badge-num">NOTE 13</span> ENGAGEMENTS FINANCIERS</div>
        <div class="note-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="note-label" style="color:#dc2626">ENGAGEMENTS DONNÉS</label>
                    <input type="text" class="liasse-input" name="MT_NOTE13_A" value="{{ $data['MT_NOTE13_A'] ?? '' }}" placeholder="Avals, Cautions donnés...">
                </div>
                <div class="col-md-6">
                    <label class="note-label" style="color:#16a34a">ENGAGEMENTS REÇUS</label>
                    <input type="text" class="liasse-input" name="MT_NOTE13_B" value="{{ $data['MT_NOTE13_B'] ?? '' }}" placeholder="Avals, Cautions reçus...">
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
            <th colspan="2" style="background-color: #f8fafc; font-weight: bold; border: 1px solid #e5e7eb; padding: 12px; text-align: left;">NOTES ANNEXES SMT</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background-color: #f1f5f9;"><td colspan="2" style="padding: 8px; border: 1px solid #e5e7eb;"><b>NOTE 1 : IMMOBILISATIONS</b></td></tr>
        <tr>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">Valeur Brute (Début)</td>
            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">{{ $fmt($data['immoBrut'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">Amortissements (Début)</td>
            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">{{ $fmt($data['immoAmort'] ?? 0) }}</td>
        </tr>
        <tr style="background-color: #f1f5f9;"><td colspan="2" style="padding: 8px; border: 1px solid #e5e7eb;"><b>NOTE 2 : MÉTHODES COMPTABLES</b></td></tr>
        <tr>
            <td colspan="2" style="border: 1px solid #e5e7eb; padding: 8px;">{{ $data['MT_NOTE2_1'] ?? 'Ces états ont été présentés selon le SYSCOHADA Révisé.' }}</td>
        </tr>
        <tr style="background-color: #f1f5f9;"><td colspan="2" style="padding: 8px; border: 1px solid #e5e7eb;"><b>NOTE 6 : EFFECTIFS & MASSE SALARIALE</b></td></tr>
        <tr>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">Masse Salariale</td>
            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">{{ $fmt($data['charges_pers'] ?? 0) }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">Nombre d'employés</td>
            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right;">{{ $data['MT_NOTE6_1'] ?? 0 }}</td>
        </tr>
        <tr style="background-color: #f1f5f9;"><td colspan="2" style="padding: 8px; border: 1px solid #e5e7eb;"><b>NOTE 13 : ENGAGEMENTS FINANCIERS</b></td></tr>
        <tr>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">Engagements Donnés</td>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">{{ $data['MT_NOTE13_A'] ?? 'Néant' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">Engagements Reçus</td>
            <td style="border: 1px solid #e5e7eb; padding: 8px;">{{ $data['MT_NOTE13_B'] ?? 'Néant' }}</td>
        </tr>
    </tbody>
</table>
@endif
