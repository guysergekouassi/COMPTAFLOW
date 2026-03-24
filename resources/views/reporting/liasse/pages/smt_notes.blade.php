{{-- SMT NOTES ANNEXES SIMPLIFIÉES --}}
@php
    $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' ');
@endphp
<style>
    .smt-badge { background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle; }
    .note-card { border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;margin-bottom:1rem;background:#fafafa; }
    .note-card-header { font-weight:800;color:#1e293b;font-size:0.85rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:8px; }
    .note-tag { background:#e0f2fe;color:#0369a1;font-size:0.65rem;font-weight:700;padding:2px 8px;border-radius:6px; }
    .liasse-input { width:100%;border:1.5px solid #e2e8f0;padding:6px 10px;text-align:right;border-radius:8px;transition:all 0.2s;font-weight:700;background:#fafafa; }
    .liasse-input:focus { background:white;border-color:#d97706;outline:none;box-shadow:0 0 0 4px rgba(217,119,6,0.12); }
    .liasse-input-text { text-align:left; }
    .num-right { text-align:right;font-variant-numeric:tabular-nums;font-weight:700; }
    .label-col { color:#64748b;font-size:0.85rem;font-weight:600; }
</style>

<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-clipboard-list" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Notes Annexes Simplifiées <span class="smt-badge">SMT</span></h5>
        <small class="text-muted">Informations obligatoires — Système Minimal de Trésorerie</small>
    </div>
</div>

{{-- NOTE A : Immobilisations --}}
<div class="note-card">
    <div class="note-card-header">
        <i class="fa-solid fa-building" style="color:#d97706;"></i>
        NOTE A — IMMOBILISATIONS
        <span class="note-tag">Auto-calculé</span>
    </div>
    <table class="liasse-table">
        <thead>
            <tr><th>Poste</th><th class="num-right" style="width:180px;">Montant (FCFA)</th></tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col">Valeur brute des immobilisations</td>
                <td class="num-right">{{ $fmt($data['immobilisations_brut'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label-col">Amortissements cumulés</td>
                <td class="num-right text-danger">{{ $fmt($data['amortissements'] ?? 0) }}</td>
            </tr>
            <tr style="font-weight:800;background:#f8fafc;">
                <td>Valeur nette comptable</td>
                <td class="num-right text-primary">{{ $fmt(($data['immobilisations_brut'] ?? 0) - ($data['amortissements'] ?? 0)) }}</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- NOTE B : Engagements --}}
<div class="note-card">
    <div class="note-card-header">
        <i class="fa-solid fa-handshake" style="color:#d97706;"></i>
        NOTE B — ENGAGEMENTS FINANCIERS
        <span class="note-tag ms-1" style="background:#fef3c7;color:#92400e;">Saisie manuelle</span>
    </div>
    <table class="liasse-table">
        <thead>
            <tr><th>Type d'engagement</th><th class="num-right" style="width:180px;">Montant (FCFA)</th></tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-col">Total dettes financières et commerciales</td>
                <td class="num-right">{{ $fmt($data['dettes_total'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label-col">Cautions et garanties données</td>
                <td><input type="number" class="liasse-input" name="cautions_donnees" value="{{ $data['cautions_donnees'] ?? 0 }}" placeholder="0"></td>
            </tr>
            <tr>
                <td class="label-col">Crédits-baux et locations financières (valeur résiduelle)</td>
                <td><input type="number" class="liasse-input" name="credits_baux" value="{{ $data['credits_baux'] ?? 0 }}" placeholder="0"></td>
            </tr>
        </tbody>
    </table>
</div>

{{-- NOTE C : Personnel --}}
<div class="note-card">
    <div class="note-card-header">
        <i class="fa-solid fa-users" style="color:#d97706;"></i>
        NOTE C — EFFECTIFS ET PERSONNEL
        <span class="note-tag ms-1" style="background:#fef3c7;color:#92400e;">Saisie manuelle</span>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="label-col d-block mb-1">Effectif total au 31/12</label>
            <input type="number" class="liasse-input" name="effectif_total" value="{{ $data['effectif'] ?? 0 }}" placeholder="ex: 5" style="text-align:left;">
        </div>
        <div class="col-md-6">
            <label class="label-col d-block mb-1">Dont permanents</label>
            <input type="number" class="liasse-input" name="effectif_permanents" value="{{ $data['effectif_permanents'] ?? 0 }}" placeholder="ex: 3" style="text-align:left;">
        </div>
        <div class="col-md-6">
            <label class="label-col d-block mb-1">Masse salariale brute annuelle (FCFA)</label>
            <input type="number" class="liasse-input" name="masse_salariale" value="{{ $data['masse_salariale'] ?? 0 }}" placeholder="0">
        </div>
        <div class="col-md-6">
            <label class="label-col d-block mb-1">Cotisations patronales CNPS (FCFA)</label>
            <input type="number" class="liasse-input" name="cotisations_cnps" value="{{ $data['cotisations_cnps'] ?? 0 }}" placeholder="0">
        </div>
    </div>
</div>

{{-- NOTE D : Informations libres --}}
<div class="note-card">
    <div class="note-card-header">
        <i class="fa-solid fa-circle-info" style="color:#d97706;"></i>
        NOTE D — INFORMATIONS COMPLÉMENTAIRES
        <span class="note-tag ms-1" style="background:#fef3c7;color:#92400e;">Saisie manuelle</span>
    </div>
    <div class="mb-3">
        <label class="label-col d-block mb-1">Événements significatifs de l'exercice</label>
        <textarea class="form-control" name="evenements" rows="3" placeholder="Décrivez ici tout événement significatif survenu durant l'exercice...">{{ $data['evenements'] ?? '' }}</textarea>
    </div>
    <div>
        <label class="label-col d-block mb-1">Méthodes et règles comptables appliquées</label>
        <textarea class="form-control" name="methodes" rows="2" placeholder="Ex: Amortissement linéaire, évaluation des stocks au coût moyen pondéré...">{{ $data['methodes'] ?? '' }}</textarea>
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary rounded-pill px-4" onclick="savePageData()">
        <i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer les notes
    </button>
</div>
