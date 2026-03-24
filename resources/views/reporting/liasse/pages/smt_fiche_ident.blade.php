{{-- SMT - 1. FICHE D'IDENTIFICATION & ACTIVITÉS --}}
@if(!isset($isExcel))
<style>
.smt-badge{background:#d97706;color:#fff;font-size:.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle}
.ident-card{border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fff}
.ident-header{background:#fef3c7;padding:12px;border-bottom:1.5px solid #e5e7eb;font-weight:700;color:#92400e;display:flex;justify-content:space-between}
.ident-body{padding:20px}
.ident-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:15px}
.ident-field{display:flex;flex-direction:column;gap:5px}
.ident-label{font-size:.75rem;font-weight:600;color:#4b5563;text-transform:uppercase}
.ident-input{border:1.5px solid #e5e7eb;border-radius:8px;padding:10px;font-weight:600;background:#f9fafb;color:#111827}
</style>

<div class="ident-card mb-4">
    <div class="ident-header"><span>IDENTIFICATION DE L'ENTREPRISE</span> <span class="text-muted">A.</span></div>
    <div class="ident-body">
        <div class="ident-row">
            <div class="ident-field">
                <label class="ident-label">Raison Sociale</label>
                <div class="ident-input">{{ $company->name }}</div>
            </div>
            <div class="ident-field">
                <label class="ident-label">N° Compte Contribuable (NCC)</label>
                <div class="ident-input">{{ $company->ncc ?? 'Non renseigné' }}</div>
            </div>
        </div>
    </div>
</div>
@endif

<table>
    <thead>
        <tr>
            <th colspan="2" style="background-color: #fef3c7; font-weight: bold;">IDENTIFICATION DE L'ENTREPRISE</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><b>Raison Sociale</b></td>
            <td>{{ $company->name }}</td>
        </tr>
        <tr>
            <td><b>N° Compte Contribuable (NCC)</b></td>
            <td>{{ $company->ncc ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><b>Adresse</b></td>
            <td>{{ $company->address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><b>Téléphone</b></td>
            <td>{{ $company->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <th colspan="2" style="background-color: #fef3c7; font-weight: bold;">PÉRIODE DE L'EXERCICE</th>
        </tr>
        <tr>
            <td><b>Date de début</b></td>
            <td>{{ $data['ZA1'] ?? $exercice->date_debut->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><b>Date de fin</b></td>
            <td>{{ $data['ZA2'] ?? $exercice->date_fin->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <th colspan="2" style="background-color: #fef3c7; font-weight: bold;">PRINCIPALES ACTIVITÉS</th>
        </tr>
        <tr>
            <td><b>Activité Principale</b></td>
            <td>{{ $data['MT_R2_BA'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><b>Chiffre d'Affaires Global (HT)</b></td>
            <td>{{ number_format($data['CA'] ?? 0, 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td><b>Effectif Moyen</b></td>
            <td>{{ $data['MT_R2_BF'] ?? 0 }}</td>
        </tr>
    </tbody>
</table>
