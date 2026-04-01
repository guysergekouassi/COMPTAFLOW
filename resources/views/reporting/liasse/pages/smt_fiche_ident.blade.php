{{-- SMT - 1. FICHE D'IDENTIFICATION & ACTIVITÉS --}}
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.ident-card{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:20px;box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);}
.ident-header{background:#f8fafc;padding:12px 18px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#334155;display:flex;justify-content:space-between;align-items:center}
.ident-body{padding:24px}
.ident-grid{display:grid;grid-template-columns:repeat(2, 1fr);gap:24px}
.ident-field{display:flex;flex-direction:column;gap:8px}
.ident-label{font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.025em}
.liasse-input{border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 14px;font-weight:600;background:#fcfdfe;color:#1e293b;width:100%;transition:all 0.2s}
.liasse-input:focus{outline:none;border-color:#2563eb;background:#fff;box-shadow: 0 0 0 4px rgba(37,99,235,0.1)}
.section-title { font-size: 0.9rem; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.section-title i { color: #f59e0b; }
</style>

<div class="mb-4">
    <div class="section-title"><i class="fa-solid fa-building"></i> IDENTIFICATION DE L'ENTREPRISE</div>
    <div class="ident-card">
        <div class="ident-body">
            <div class="ident-grid">
                <div class="ident-field">
                    <label class="ident-label">Raison Sociale</label>
                    <input type="text" class="liasse-input" name="MT_R1_A" value="{{ $data['MT_R1_A'] ?? $company->name }}" placeholder="Nom de l'entreprise">
                </div>
                <div class="ident-field">
                    <label class="ident-label">N° Compte Contribuable (NCC)</label>
                    <input type="text" class="liasse-input" name="MT_R1_B" value="{{ $data['MT_R1_B'] ?? $company->ncc }}" placeholder="Numéro NCC">
                </div>
                <div class="ident-field">
                    <label class="ident-label">Adresse</label>
                    <input type="text" class="liasse-input" name="MT_R1_C" value="{{ $data['MT_R1_C'] ?? $company->address }}" placeholder="Adresse complète">
                </div>
                <div class="ident-field">
                    <label class="ident-label">Téléphone</label>
                    <input type="text" class="liasse-input" name="MT_R1_D" value="{{ $data['MT_R1_D'] ?? $company->phone }}" placeholder="Contact téléphonique">
                </div>
            </div>
        </div>
    </div>

    <div class="section-title mt-4"><i class="fa-solid fa-calendar-days"></i> PÉRIODE DE L'EXERCICE</div>
    <div class="ident-card">
        <div class="ident-body">
            <div class="ident-grid">
                <div class="ident-field">
                    <label class="ident-label">Date de début</label>
                    <input type="text" class="liasse-input" name="ZA1" value="{{ $data['ZA1'] ?? $exercice->date_debut->format('d/m/Y') }}" readonly style="background: #f1f5f9;">
                </div>
                <div class="ident-field">
                    <label class="ident-label">Date de fin</label>
                    <input type="text" class="liasse-input" name="ZA2" value="{{ $data['ZA2'] ?? $exercice->date_fin->format('d/m/Y') }}" readonly style="background: #f1f5f9;">
                </div>
            </div>
        </div>
    </div>

    <div class="section-title mt-4"><i class="fa-solid fa-briefcase"></i> PRINCIPALES ACTIVITÉS</div>
    <div class="ident-card">
        <div class="ident-body">
            <div class="ident-grid">
                <div class="ident-field" style="grid-column: span 2;">
                    <label class="ident-label">Activité Principale</label>
                    <textarea class="liasse-input" name="MT_R2_BA" rows="2" placeholder="Décrire l'activité principale de l'entreprise...">{{ $data['MT_R2_BA'] ?? '' }}</textarea>
                </div>
                <div class="ident-field">
                    <label class="ident-label">Chiffre d'Affaires Global (HT)</label>
                    <div class="liasse-input" style="background: #f1f5f9; font-weight: 800; color: #0f172a;">{{ number_format($data['CA'] ?? 0, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="ident-field">
                    <label class="ident-label">Effectif Moyen</label>
                    <input type="number" class="liasse-input" name="MT_R2_BF" value="{{ $data['MT_R2_BF'] ?? 0 }}">
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Tableau simplifié pour l'export Excel/PDF --}}
@if(isset($isExcel) || isset($isPdf))
<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <thead>
        <tr>
            <th colspan="2" style="background-color: #f8fafc; font-weight: bold; border: 1px solid #e2e8f0; padding: 12px; text-align: left;">IDENTIFICATION DE L'ENTREPRISE</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px; width: 30%;"><b>Raison Sociale</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['MT_R1_A'] ?? $company->name }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>N° Compte Contribuable (NCC)</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['MT_R1_B'] ?? $company->ncc ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Adresse</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['MT_R1_C'] ?? $company->address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Téléphone</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['MT_R1_D'] ?? $company->phone ?? 'N/A' }}</td>
        </tr>
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="font-weight: bold; border: 1px solid #e2e8f0; padding: 12px; text-align: left;">PÉRIODE DE L'EXERCICE</th>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Date de début</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['ZA1'] ?? $exercice->date_debut->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Date de fin</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['ZA2'] ?? $exercice->date_fin->format('d/m/Y') }}</td>
        </tr>
        <tr style="background-color: #f8fafc;">
            <th colspan="2" style="font-weight: bold; border: 1px solid #e2e8f0; padding: 12px; text-align: left;">PRINCIPALES ACTIVITÉS</th>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Activité Principale</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['MT_R2_BA'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Chiffre d'Affaires Global (HT)</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ number_format($data['CA'] ?? 0, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0; padding: 8px;"><b>Effectif Moyen</b></td>
            <td style="border: 1px solid #e2e8f0; padding: 8px;">{{ $data['MT_R2_BF'] ?? 0 }}</td>
        </tr>
    </tbody>
</table>
@endif
