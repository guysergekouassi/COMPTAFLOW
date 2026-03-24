{{-- SMT BILAN SIMPLIFIÉ --}}
@php
    $fmt = fn($v) => number_format(floatval($v ?? 0), 0, ',', ' ');
@endphp
<style>
    .smt-badge { background:#d97706;color:#fff;font-size:0.6rem;border-radius:6px;padding:2px 7px;font-weight:700;vertical-align:middle; }
    .smt-section-header { background:linear-gradient(90deg,#fffbeb,#fef3c7); color:#92400e; font-weight:800; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; }
    .ref-code { font-size:0.68rem; color:#94a3b8; font-weight:700; width:50px; text-align:center; }
    .smt-total { background:#f1f5f9; font-weight:800; }
    .smt-grand-total { background:linear-gradient(90deg,#eff6ff,#dbeafe); color:#1e40af; font-weight:900; }
    .num-right { text-align:right; font-variant-numeric:tabular-nums; font-weight:700; }
</style>

<div class="mb-3 d-flex align-items-center gap-2">
    <i class="fa-solid fa-file-lines" style="color:#d97706;font-size:1.3rem;"></i>
    <div>
        <h5 class="fw-800 mb-0">Bilan <span class="smt-badge">SMT</span></h5>
        <small class="text-muted">Système Minimal de Trésorerie — Format simplifié</small>
    </div>
</div>

<div class="row g-4">
    {{-- ===== ACTIF ===== --}}
    <div class="col-12 col-xl-6">
        <table class="liasse-table w-100">
            <thead>
                <tr>
                    <th class="ref-code">REF</th>
                    <th>ACTIF</th>
                    <th class="num-right" style="width:130px;">BRUT</th>
                    <th class="num-right" style="width:110px;">AMORT/DÉPR.</th>
                    <th class="num-right" style="width:120px;">NET N</th>
                    <th class="num-right" style="width:110px;">NET N-1</th>
                </tr>
            </thead>
            <tbody>
                <tr class="smt-section-header"><td colspan="6" class="px-3 py-2">ACTIF IMMOBILISÉ</td></tr>
                <tr>
                    <td class="ref-code">AI</td>
                    <td>Immobilisations corporelles et incorporelles</td>
                    <td class="num-right">{{ $fmt($data['immoBrut'] ?? 0) }}</td>
                    <td class="num-right text-danger">{{ $fmt($data['immoAmort'] ?? 0) }}</td>
                    <td class="num-right">{{ $fmt($data['immoNet'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>

                <tr class="smt-section-header"><td colspan="6" class="px-3 py-2">ACTIF CIRCULANT</td></tr>
                <tr>
                    <td class="ref-code">BB</td>
                    <td>Stocks et en-cours</td>
                    <td class="num-right">{{ $fmt($data['stocks'] ?? 0) }}</td>
                    <td class="num-right">—</td>
                    <td class="num-right">{{ $fmt($data['stocks'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr>
                    <td class="ref-code">BG</td>
                    <td>Créances et emplois assimilés</td>
                    <td class="num-right">{{ $fmt($data['creances'] ?? 0) }}</td>
                    <td class="num-right">—</td>
                    <td class="num-right">{{ $fmt($data['creances'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>

                <tr class="smt-section-header"><td colspan="6" class="px-3 py-2">TRÉSORERIE-ACTIF</td></tr>
                <tr>
                    <td class="ref-code">BW</td>
                    <td>Disponibilités (Banques, Caisses)</td>
                    <td class="num-right">{{ $fmt($data['treso_actif'] ?? 0) }}</td>
                    <td class="num-right">—</td>
                    <td class="num-right">{{ $fmt($data['treso_actif'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>

                <tr class="smt-grand-total">
                    <td class="ref-code" style="color:#1e40af;">BX</td>
                    <td>TOTAL GÉNÉRAL ACTIF</td>
                    <td class="num-right">{{ $fmt($data['totalActif'] ?? 0) }}</td>
                    <td class="num-right">{{ $fmt($data['immoAmort'] ?? 0) }}</td>
                    <td class="num-right">{{ $fmt($data['totalActif'] ?? 0) }}</td>
                    <td class="num-right">{{ $fmt($data['totalActifN1'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ===== PASSIF ===== --}}
    <div class="col-12 col-xl-6">
        <table class="liasse-table w-100">
            <thead>
                <tr>
                    <th class="ref-code">REF</th>
                    <th>PASSIF</th>
                    <th class="num-right" style="width:150px;">MONTANT N</th>
                    <th class="num-right" style="width:130px;">MONTANT N-1</th>
                </tr>
            </thead>
            <tbody>
                <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">CAPITAUX PROPRES</td></tr>
                <tr>
                    <td class="ref-code">CA</td><td>Capital</td>
                    <td class="num-right">{{ $fmt($data['capital'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr>
                    <td class="ref-code">CF</td><td>Réserves</td>
                    <td class="num-right">{{ $fmt($data['reserves'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr>
                    <td class="ref-code">CH</td><td>Report à nouveau</td>
                    <td class="num-right">{{ $fmt($data['report'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr>
                    <td class="ref-code">CJ</td><td>Résultat net de l'exercice</td>
                    <td class="num-right {{ ($data['resultat'] ?? 0) < 0 ? 'text-danger' : 'text-success' }}">{{ $fmt($data['resultat'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr class="smt-total">
                    <td class="ref-code">CP</td><td>TOTAL CAPITAUX PROPRES</td>
                    <td class="num-right">{{ $fmt($data['capitauxPropres'] ?? 0) }}</td>
                    <td class="num-right">{{ $fmt(($data['totalPassifN1'] ?? 0) * 0.8) }}</td>
                </tr>

                <tr class="smt-section-header"><td colspan="4" class="px-3 py-2">DETTES</td></tr>
                <tr>
                    <td class="ref-code">DA</td><td>Emprunts et dettes financières</td>
                    <td class="num-right">{{ $fmt($data['dettes_fin'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr>
                    <td class="ref-code">FB</td><td>Fournisseurs d'exploitation</td>
                    <td class="num-right">{{ $fmt($data['dettes_exp'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>
                <tr>
                    <td class="ref-code">DK</td><td>Dettes fiscales et sociales</td>
                    <td class="num-right">{{ $fmt($data['dettes_fisc'] ?? 0) }}</td>
                    <td class="num-right text-muted">—</td>
                </tr>

                <tr class="smt-grand-total">
                    <td class="ref-code" style="color:#1e40af;">DZ</td>
                    <td>TOTAL GÉNÉRAL PASSIF</td>
                    <td class="num-right">{{ $fmt($data['totalPassif'] ?? 0) }}</td>
                    <td class="num-right">{{ $fmt($data['totalPassifN1'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Équilibre check --}}
        @php
            $actif  = floatval($data['totalActif'] ?? 0);
            $passif = floatval($data['totalPassif'] ?? 0);
            $diff   = abs($actif - $passif);
            $ok     = $diff < 1;
        @endphp
        <div class="mt-3 p-3 rounded-3 d-flex align-items-center gap-2 {{ $ok ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : 'bg-danger bg-opacity-10 border border-danger border-opacity-25' }}">
            <i class="fa-solid {{ $ok ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger' }} fs-5"></i>
            <div>
                <strong class="{{ $ok ? 'text-success' : 'text-danger' }}">
                    {{ $ok ? 'Bilan équilibré ✔' : 'Déséquilibre : ' . number_format($diff, 0, ',', ' ') . ' FCFA' }}
                </strong>
                <div class="small text-muted">Actif = {{ number_format($actif,0,',',' ') }} | Passif = {{ number_format($passif,0,',',' ') }}</div>
            </div>
        </div>
    </div>
</div>
