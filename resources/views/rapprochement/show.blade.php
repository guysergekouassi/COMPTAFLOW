<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
/* ═══════ LAYOUT ═══════ */
.glass-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.04); }
.split-panel { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
@media(max-width:1024px){ .split-panel{ grid-template-columns:1fr; } }

/* ═══════ DRAG & DROP ═══════ */
#dropZone {
    border:2.5px dashed #cbd5e1; border-radius:16px; background:#f8fafc;
    min-height:180px; display:flex; flex-direction:column; align-items:center;
    justify-content:center; gap:12px; cursor:pointer; transition:all .3s;
}
#dropZone.drag-over { border-color:#1e40af; background:#eff6ff; }
#dropZone.has-file  { border-color:#16a34a; background:#f0fdf4; border-style:solid; }

/* ═══════ TABLEAUX ═══════ */
.tbl thead th { background:#f8fafc; padding:.6rem .75rem; font-size:.7rem;
    font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; }
.tbl tbody td { padding:.55rem .75rem; font-size:.78rem; vertical-align:middle; }
.tbl tbody tr:hover { background:#f8fafc; }
.tbl tbody tr.selected-row { background:#eff6ff; outline:2px solid #3b82f6; }

/* ═══════ BADGES STATUT ═══════ */
.badge-pointe    { background:#f0fdf4; color:#16a34a; }
.badge-non-pointe{ background:#fef3c7; color:#b45309; }
.badge-ecart     { background:#fef2f2; color:#dc2626; }

/* ═══════ STATS CARDS ═══════ */
.stat-card { border-radius:12px; padding:1rem 1.25rem; }
.stat-card.blue  { background:#eff6ff; border:1px solid #bfdbfe; }
.stat-card.green { background:#f0fdf4; border:1px solid #bbf7d0; }
.stat-card.red   { background:#fef2f2; border:1px solid #fecaca; }
.stat-card.gray  { background:#f8fafc; border:1px solid #e2e8f0; }

/* ═══════ BOUTONS ═══════ */
.btn-primary-custom { background:#1e40af; color:#fff; border:none; border-radius:10px;
    padding:.55rem 1.1rem; font-weight:700; font-size:.8rem; transition:all .2s; }
.btn-primary-custom:hover { background:#1e3a8a; transform:translateY(-1px); color:#fff; }
.btn-success-custom { background:#15803d; color:#fff; border:none; border-radius:10px;
    padding:.55rem 1.1rem; font-weight:700; font-size:.8rem; transition:all .2s; }
.btn-success-custom:hover { background:#166534; transform:translateY(-1px); color:#fff; }
.btn-orange-custom  { background:#c2410c; color:#fff; border:none; border-radius:10px;
    padding:.55rem 1.1rem; font-weight:700; font-size:.8rem; transition:all .2s; }
.btn-orange-custom:hover  { background:#9a3412; transform:translateY(-1px); color:#fff; }

/* ═══════ POINTAGE MANUEL ═══════ */
.pointer-mode .tbl tbody tr.pointable { cursor:pointer; }
.pointer-mode .tbl tbody tr.pointable:hover { background:#fef9c3; }

/* ═══════ SCROLL ═══════ */
.scroll-table { max-height:350px; overflow-y:auto; }

/* ═══════ HEADER STICKY ═══════ */
.rapprochement-header {
    position:sticky; top:0; z-index:100;
    background:#fff; border-bottom:1px solid #e2e8f0;
    padding:.75rem 1.5rem; display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
}
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">
    @include('components.sidebar')

    <div class="layout-page">
      @include('components.header', ['page_title' => 'Rapprochement <span class="text-gradient">Bancaire</span>'])

      <div class="content-wrapper">
        <div class="container-fluid flex-grow-1 container-p-y pt-0">

          {{-- ═══════════════════ BARRE SUPÉRIEURE ═══════════════════ --}}
          <div class="rapprochement-header mb-4">
            <div class="flex items-center gap-3">
              <a href="{{ route('rapprochement.index') }}" class="text-slate-400 hover:text-slate-700 transition">
                <i class="fas fa-arrow-left"></i>
              </a>
              <div>
                <h2 class="font-extrabold text-slate-900 mb-0 text-base">
                  {{ $rapprochement->compteTresorerie->name ?? '—' }}
                  @if($rapprochement->codeJournal)
                    <span class="text-slate-400 font-mono text-sm ml-1">({{ $rapprochement->codeJournal->code_journal }})</span>
                  @endif
                </h2>
                <p class="text-xs text-slate-500 mb-0">
                  {{ $rapprochement->date_debut->format('d/m/Y') }} → {{ $rapprochement->date_fin->format('d/m/Y') }}
                  &nbsp;|&nbsp;
                  @if($rapprochement->statut === 'valide')
                    <span class="text-green-600 font-bold">✅ Validé</span>
                  @elseif($rapprochement->statut === 'cloture')
                    <span class="text-slate-500 font-bold">🔒 Clôturé</span>
                  @else
                    <span class="text-amber-600 font-bold">🔄 En cours</span>
                  @endif
                </p>
              </div>
            </div>

            {{-- Bouton ENREGISTRER --}}
            <button id="btnEnregistrer" class="btn-success-custom flex items-center gap-2 px-6 py-2.5">
              <i class="fas fa-save"></i> Enregistrer
            </button>
          </div>

          {{-- ═══════════════════ STATS RAPIDES ═══════════════════ --}}
          @if($stats)
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5" id="statsBar">
            <div class="stat-card blue">
              <div class="text-xs font-bold text-blue-500 uppercase mb-1">Lignes relevé</div>
              <div class="text-2xl font-extrabold text-blue-700" id="stat-nb-lignes">{{ $stats['nb_lignes_releve'] }}</div>
            </div>
            <div class="stat-card green">
              <div class="text-xs font-bold text-green-600 uppercase mb-1">✅ Pointées</div>
              <div class="text-2xl font-extrabold text-green-700" id="stat-pointees">{{ $stats['nb_pointees'] }}</div>
            </div>
            <div class="stat-card red">
              <div class="text-xs font-bold text-red-500 uppercase mb-1">⚠️ Non pointées</div>
              <div class="text-2xl font-extrabold text-red-700" id="stat-non-pointees">{{ $stats['nb_non_pointees'] }}</div>
            </div>
            <div class="stat-card {{ $stats['equilibre'] ? 'green' : 'red' }}" id="stat-equilibre-card">
              <div class="text-xs font-bold uppercase mb-1 {{ $stats['equilibre'] ? 'text-green-600' : 'text-red-500' }}">Écart résiduel</div>
              <div class="text-2xl font-extrabold {{ $stats['equilibre'] ? 'text-green-700' : 'text-red-700' }}" id="stat-ecart">
                {{ number_format($stats['ecart_residuel'], 0, ',', ' ') }} FCFA
              </div>
            </div>
          </div>
          @endif

          {{-- ═══════════════════ PANNEAU PRINCIPAL (2 colonnes) ═══════════════════ --}}
          <div class="split-panel mb-5">

            {{-- ── GAUCHE : Écritures comptables ── --}}
            <div class="glass-card overflow-hidden">
              <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-white text-xs"></i>
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-0">Écritures Comptables</h4>
                    <p class="text-xs text-slate-400 mb-0">Compte {{ $rapprochement->compteTresorerie->name ?? '' }}</p>
                  </div>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $ecritures->count() }} ligne(s)</span>
              </div>

              <div class="scroll-table">
                <table class="w-full tbl" id="tableEcritures">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Jnl</th>
                      <th>Libellé</th>
                      <th class="text-right">Débit</th>
                      <th class="text-right">Crédit</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($ecritures as $e)
                      @php $pointe = in_array($e->id, $ecrituresPointeesIds); @endphp
                      <tr class="ecriture-row {{ $pointe ? 'opacity-40' : 'pointable' }}"
                        data-id="{{ $e->id }}"
                        data-montant="{{ max($e->debit, $e->credit) }}"
                        data-libelle="{{ $e->description_operation }}"
                        data-date="{{ $e->date }}"
                        data-pointe="{{ $pointe ? '1' : '0' }}">
                        <td class="font-mono text-slate-600">{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                        <td class="font-mono text-slate-500 text-xs">{{ $e->codeJournal?->code_journal ?? '—' }}</td>
                        <td class="text-slate-700 max-w-xs truncate" title="{{ $e->description_operation }}">{{ Str::limit($e->description_operation, 30) }}</td>
                        <td class="text-right font-mono {{ $e->debit > 0 ? 'text-slate-800 font-bold' : 'text-slate-300' }}">{{ $e->debit > 0 ? number_format($e->debit, 0, ',', ' ') : '' }}</td>
                        <td class="text-right font-mono {{ $e->credit > 0 ? 'text-slate-800 font-bold' : 'text-slate-300' }}">{{ $e->credit > 0 ? number_format($e->credit, 0, ',', ' ') : '' }}</td>
                        <td>
                          @if($pointe)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold badge-pointe">✓ Pointé</span>
                          @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold badge-non-pointe">En attente</span>
                          @endif
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="6" class="text-center text-slate-400 py-8">Aucune écriture pour cette période.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              {{-- Solde compta --}}
              <div class="px-5 py-3 border-t border-slate-100 flex justify-between text-sm">
                <span class="text-slate-500 font-medium">Solde initial compta :</span>
                <span class="font-extrabold text-slate-800 font-mono">{{ number_format($rapprochement->solde_initial_compta, 0, ',', ' ') }} FCFA</span>
              </div>
            </div>

            {{-- ── DROITE : Relevé bancaire ── --}}
            <div class="glass-card overflow-hidden">
              <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-university text-white text-xs"></i>
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-800 text-sm mb-0">Relevé Bancaire</h4>
                    <p class="text-xs text-slate-400 mb-0" id="nomFichierReleve">
                      {{ $rapprochement->nom_fichier_releve ?? 'Aucun fichier importé' }}
                    </p>
                  </div>
                </div>
                @if($rapprochement->lignesReleve->count() > 0)
                  <span class="text-xs font-bold text-slate-500">{{ $rapprochement->lignesReleve->count() }} ligne(s)</span>
                @endif
              </div>

              {{-- Zone drag & drop --}}
              @if($rapprochement->lignesReleve->count() === 0)
              <div class="p-4">
                <div id="dropZone" onclick="document.getElementById('inputFichier').click()">
                  <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-cloud-upload-alt text-slate-400 text-xl"></i>
                  </div>
                  <div class="text-center">
                    <p class="font-bold text-slate-700 text-sm mb-1">Glissez votre relevé ici</p>
                    <p class="text-xs text-slate-400">ou cliquez pour parcourir · CSV (;) · Excel (.xlsx)</p>
                  </div>
                  <div id="uploadProgress" class="hidden w-full px-6">
                    <div class="h-1.5 bg-slate-200 rounded-full overflow-hidden">
                      <div class="h-full bg-blue-500 rounded-full animate-pulse" style="width:60%"></div>
                    </div>
                    <p class="text-xs text-center text-blue-600 font-bold mt-1">Import en cours…</p>
                  </div>
                  <input type="file" id="inputFichier" accept=".csv,.xlsx,.xls,.ods" class="hidden">
                </div>
                <div id="importErreur" class="mt-3 text-xs text-red-600 font-medium hidden"></div>
              </div>
              @else
              {{-- Zone d'upload (réimport) --}}
              <div class="px-5 py-2 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                <span class="text-xs text-emerald-700 font-bold">✅ Fichier chargé</span>
                <button onclick="document.getElementById('inputFichierUpdate').click()" class="text-xs text-slate-500 hover:text-blue-600 transition font-medium">
                  <i class="fas fa-sync me-1"></i> Réimporter
                </button>
                <input type="file" id="inputFichierUpdate" accept=".csv,.xlsx,.xls" class="hidden">
              </div>
              @endif

              {{-- Tableau relevé --}}
              <div class="scroll-table" id="tableReleveWrapper" {{ $rapprochement->lignesReleve->count() === 0 ? 'style=display:none' : '' }}>
                <table class="w-full tbl" id="tableReleve">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Libellé</th>
                      <th class="text-right">Débit</th>
                      <th class="text-right">Crédit</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody id="tbodyReleve">
                    @foreach($rapprochement->lignesReleve as $l)
                      <tr class="releve-row {{ $l->statut === 'pointe' ? 'opacity-40' : 'pointable' }}"
                        data-id="{{ $l->id }}"
                        data-montant="{{ max($l->debit, $l->credit) }}"
                        data-libelle="{{ $l->libelle }}"
                        data-date="{{ $l->date_operation }}"
                        data-statut="{{ $l->statut }}">
                        <td class="font-mono text-slate-600 text-xs">{{ \Carbon\Carbon::parse($l->date_operation)->format('d/m/Y') }}</td>
                        <td class="text-slate-700 max-w-xs truncate text-xs" title="{{ $l->libelle }}">{{ Str::limit($l->libelle, 30) }}</td>
                        <td class="text-right font-mono text-xs {{ $l->debit > 0 ? 'text-red-600 font-bold' : 'text-slate-300' }}">{{ $l->debit > 0 ? number_format($l->debit, 0, ',', ' ') : '' }}</td>
                        <td class="text-right font-mono text-xs {{ $l->credit > 0 ? 'text-green-600 font-bold' : 'text-slate-300' }}">{{ $l->credit > 0 ? number_format($l->credit, 0, ',', ' ') : '' }}</td>
                        <td>
                          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold badge-{{ $l->statut === 'pointe' ? 'pointe' : 'non-pointe' }}">
                            {{ $l->statut === 'pointe' ? '✓' : '○' }}
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              {{-- Solde banque --}}
              <div class="px-5 py-3 border-t border-slate-100 flex justify-between text-sm">
                <span class="text-slate-500 font-medium">Solde final banque :</span>
                <span class="font-extrabold text-slate-800 font-mono">{{ number_format($rapprochement->solde_final_banque, 0, ',', ' ') }} FCFA</span>
              </div>
            </div>
          </div>{{-- /split-panel --}}

          {{-- ═══════════════════ BOUTONS D'ACTION ═══════════════════ --}}
          <div class="flex flex-wrap gap-3 mb-6 items-center justify-between">
            <div class="flex gap-3">
              <button id="btnAnalyser" class="btn-primary-custom flex items-center gap-2" {{ $rapprochement->lignesReleve->count() === 0 ? 'disabled' : '' }}>
                <i class="fas fa-search"></i> Analyser
              </button>
              <button id="btnAutoRapprochement" class="btn-primary-custom flex items-center gap-2 bg-indigo-600" style="background:#4f46e5!important" {{ $rapprochement->lignesReleve->count() === 0 ? 'disabled' : '' }}>
                <i class="fas fa-magic"></i> Rapprochement Auto
              </button>
              <button id="btnRapprochementManuel" class="btn-orange-custom flex items-center gap-2" {{ $rapprochement->lignesReleve->count() === 0 ? 'disabled' : '' }}>
                <i class="fas fa-hand-pointer"></i> Rapprochement Manuel
              </button>
            </div>
            <div id="modeManuelInfo" class="hidden text-xs text-amber-700 font-bold bg-amber-50 px-4 py-2 rounded-xl border border-amber-200">
              🖱️ Mode manuel actif — Cliquez une ligne relevé, puis une écriture
            </div>
          </div>

          {{-- ═══════════════════ RÉSULTATS ANALYSE ═══════════════════ --}}
          <div id="resultatsSection" class="{{ $rapprochement->lignesReleve->count() === 0 ? 'hidden' : '' }}">

            {{-- Soldes théoriques --}}
            <div class="glass-card p-5 mb-5" id="soldesTheoriques">
              <h5 class="font-extrabold text-slate-800 mb-4 text-sm flex items-center gap-2">
                <i class="fas fa-calculator text-blue-600"></i> Soldes Théoriques
              </h5>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="stat-card blue">
                  <div class="text-xs text-blue-500 font-bold uppercase mb-1">Solde banque actuel</div>
                  <div class="font-extrabold text-blue-700 text-lg font-mono" id="th-solde-banque">
                    {{ number_format($rapprochement->solde_final_banque, 0, ',', ' ') }}
                  </div>
                </div>
                <div class="stat-card gray">
                  <div class="text-xs text-slate-500 font-bold uppercase mb-1">Solde compta actuel</div>
                  <div class="font-extrabold text-slate-700 text-lg font-mono" id="th-solde-compta">
                    {{ number_format($rapprochement->solde_initial_compta, 0, ',', ' ') }}
                  </div>
                </div>
                <div class="stat-card green">
                  <div class="text-xs text-green-600 font-bold uppercase mb-1">Solde bancaire réel</div>
                  <div class="font-extrabold text-green-700 text-lg font-mono" id="th-solde-bancaire-reel">
                    @if($stats) {{ number_format($stats['solde_bancaire_reel'], 0, ',', ' ') }} @else — @endif
                  </div>
                </div>
                <div class="stat-card {{ ($stats && $stats['equilibre']) ? 'green' : 'red' }}" id="th-equilibre-card">
                  <div class="text-xs font-bold uppercase mb-1 {{ ($stats && $stats['equilibre']) ? 'text-green-600' : 'text-red-500' }}">
                    Solde compta réel
                  </div>
                  <div class="font-extrabold text-lg font-mono {{ ($stats && $stats['equilibre']) ? 'text-green-700' : 'text-red-700' }}" id="th-solde-compta-reel">
                    @if($stats) {{ number_format($stats['solde_compta_reel'], 0, ',', ' ') }} @else — @endif
                  </div>
                  <div class="text-xs mt-1 font-bold" id="th-equilibre-label">
                    @if($stats)
                      @if($stats['equilibre']) ✅ ÉQUILIBRÉ @else ⚠️ Écart : {{ number_format($stats['ecart_residuel'], 0, ',', ' ') }} FCFA @endif
                    @endif
                  </div>
                </div>
              </div>
            </div>

            {{-- ── Opérations rapprochées ── --}}
            <div class="glass-card overflow-hidden mb-4">
              <div class="px-5 py-3 border-b border-slate-100 flex items-center gap-2">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><i class="fas fa-check text-white text-xs"></i></div>
                <h5 class="font-bold text-slate-800 text-sm mb-0">✅ Opérations Rapprochées</h5>
                <span class="text-xs text-slate-400 ml-auto" id="nb-rapproches">{{ $rapprochement->pointages->count() }} correspondance(s)</span>
              </div>
              <div class="scroll-table">
                <table class="w-full tbl" id="tableRapproches">
                  <thead>
                    <tr>
                      <th>Date Relevé</th>
                      <th>Libellé Banque</th>
                      <th class="text-right">Montant</th>
                      <th>Date Compta</th>
                      <th>Libellé Compta</th>
                      <th>Type</th>
                      <th>Écart</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody id="tbodyRapproches">
                    @foreach($rapprochement->pointages as $p)
                      <tr id="pointage-row-{{ $p->id }}">
                        <td class="font-mono text-xs">{{ $p->ligneReleve ? $p->ligneReleve->date_operation->format('d/m/Y') : '—' }}</td>
                        <td class="text-xs truncate max-w-xs" title="{{ $p->ligneReleve?->libelle }}">{{ Str::limit($p->ligneReleve?->libelle, 28) }}</td>
                        <td class="text-right font-mono font-bold text-xs">
                          {{ $p->ligneReleve ? number_format(max($p->ligneReleve->debit, $p->ligneReleve->credit), 0, ',', ' ') : '—' }}
                        </td>
                        <td class="font-mono text-xs">{{ $p->ecritureComptable ? \Carbon\Carbon::parse($p->ecritureComptable->date)->format('d/m/Y') : '—' }}</td>
                        <td class="text-xs truncate max-w-xs">{{ Str::limit($p->ecritureComptable?->description_operation, 28) }}</td>
                        <td>
                          <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $p->type_pointage === 'auto' ? 'bg-purple-100 text-purple-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $p->type_pointage === 'auto' ? '🤖 Auto' : '✋ Manuel' }}
                          </span>
                        </td>
                        <td class="text-xs font-mono {{ $p->ecart > 0 ? 'text-red-600 font-bold' : 'text-slate-400' }}">
                          {{ $p->ecart > 0 ? number_format($p->ecart, 0, ',', ' ') : '—' }}
                        </td>
                        <td>
                          <button onclick="annulerPointage({{ $p->id }})"
                            class="text-xs text-red-400 hover:text-red-600 transition" title="Annuler ce pointage">
                            <i class="fas fa-times"></i>
                          </button>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>

            {{-- ── Écarts (non rapprochés) ── --}}
            <div class="glass-card overflow-hidden mb-4">
              <div class="px-5 py-3 border-b border-slate-100">
                <h5 class="font-bold text-slate-800 text-sm mb-0 flex items-center gap-2">
                  <i class="fas fa-exclamation-triangle text-amber-500"></i> Écarts Détectés
                </h5>
              </div>

              {{-- Sous-tableau 1 : En compta, pas en banque --}}
              <div class="border-b border-slate-100">
                <div class="px-5 py-2 bg-amber-50 flex items-center justify-between">
                  <span class="text-xs font-bold text-amber-700">📒 En comptabilité mais PAS dans le relevé banque</span>
                  <span class="text-xs text-amber-600" id="nb-compta-non-banque">
                    @if($stats) {{ $stats['nb_ecritures_non_pointees'] }} écriture(s) @endif
                  </span>
                </div>
                <div class="scroll-table" style="max-height:200px">
                  <table class="w-full tbl" id="tableComptaNonBanque">
                    <thead>
                      <tr>
                        <th>Date</th><th>Jnl</th><th>Libellé</th>
                        <th class="text-right">Débit</th><th class="text-right">Crédit</th><th>Note</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyComptaNonBanque">
                      @if($stats)
                        @foreach($stats['ecritures_non_pointees'] as $e)
                          <tr>
                            <td class="font-mono text-xs">{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>
                            <td class="text-xs text-slate-400">{{ $e->codeJournal?->code_journal ?? '—' }}</td>
                            <td class="text-xs truncate max-w-xs">{{ Str::limit($e->description_operation, 30) }}</td>
                            <td class="text-right font-mono text-xs text-slate-700">{{ $e->debit > 0 ? number_format($e->debit, 0, ',', ' ') : '' }}</td>
                            <td class="text-right font-mono text-xs text-slate-700">{{ $e->credit > 0 ? number_format($e->credit, 0, ',', ' ') : '' }}</td>
                            <td class="text-xs text-slate-400 italic">Chèque émis non encaissé ?</td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>

              {{-- Sous-tableau 2 : En banque, pas en compta --}}
              <div>
                <div class="px-5 py-2 bg-red-50 flex items-center justify-between">
                  <span class="text-xs font-bold text-red-700">🏦 Dans le relevé banque mais PAS en comptabilité</span>
                  <span class="text-xs text-red-600" id="nb-banque-non-compta">
                    @if($stats) {{ $stats['nb_non_pointees'] }} ligne(s) @endif
                  </span>
                </div>
                <div class="scroll-table" style="max-height:200px">
                  <table class="w-full tbl" id="tableBanqueNonCompta">
                    <thead>
                      <tr>
                        <th>Date</th><th>Libellé</th>
                        <th class="text-right">Débit</th><th class="text-right">Crédit</th><th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="tbodyBanqueNonCompta">
                      @if($stats)
                        @foreach($stats['lignes_releve_non_pointees'] as $l)
                          <tr>
                            <td class="font-mono text-xs">{{ \Carbon\Carbon::parse($l->date_operation)->format('d/m/Y') }}</td>
                            <td class="text-xs truncate max-w-xs">{{ Str::limit($l->libelle, 30) }}</td>
                            <td class="text-right font-mono text-xs text-red-600">{{ $l->debit > 0 ? number_format($l->debit, 0, ',', ' ') : '' }}</td>
                            <td class="text-right font-mono text-xs text-green-600">{{ $l->credit > 0 ? number_format($l->credit, 0, ',', ' ') : '' }}</td>
                            <td>
                              <button onclick="ouvrirGenererEcriture({{ $l->id }}, '{{ addslashes($l->libelle) }}', {{ max($l->debit, $l->credit) }}, '{{ $l->date_operation }}', {{ $l->debit }}, {{ $l->credit }})"
                                class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition font-bold">
                                + Écriture
                              </button>
                            </td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>{{-- /resultatsSection --}}

        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
</div>

@include('components.footer')

{{-- ═══════════════════════ MODAL : GÉNÉRER ÉCRITURE ═══════════════════════ --}}
<div class="modal fade" id="modalGenererEcriture" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content" style="border-radius:20px; padding:1.75rem; max-height:90vh; overflow-y:auto;">

      {{-- En-tête --}}
      <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-pen-to-square text-white"></i>
          </div>
          <div>
            <h5 class="font-extrabold text-slate-900 mb-0">Générer une écriture corrective</h5>
            <p class="text-xs text-slate-400 mb-0">Journal : <strong>{{ $rapprochement->codeJournal?->code_journal ?? 'Non défini' }}</strong></p>
          </div>
        </div>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- Info banque --}}
      <div class="bg-slate-50 rounded-2xl p-4 mb-4 border border-slate-200">
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <div class="text-xs text-slate-400 font-bold uppercase mb-1">Date</div>
            <div class="font-bold text-slate-700 text-sm" id="genDate">—</div>
          </div>
          <div>
            <div class="text-xs text-slate-400 font-bold uppercase mb-1">Libellé banque</div>
            <div class="font-bold text-slate-700 text-sm truncate" id="genLibelle">—</div>
          </div>
          <div>
            <div class="text-xs text-slate-400 font-bold uppercase mb-1">Montant</div>
            <div class="font-extrabold text-blue-700 text-base font-mono" id="genMontant">—</div>
          </div>
        </div>
      </div>

      <input type="hidden" id="genLigneId">
      <input type="hidden" id="genDebit">
      <input type="hidden" id="genCredit">

      <div class="row g-3">

        {{-- Description --}}
        <div class="col-12">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">
            📝 Libellé de l'écriture
          </label>
          <input type="text" id="genDescription"
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            placeholder="Ex : Frais bancaires janvier 2025">
        </div>

        {{-- Sens (Débit / Crédit) --}}
        <div class="col-12">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">
            🔄 Sens de l'écriture (compte de contrepartie)
          </label>
          <div class="flex gap-3">
            <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-blue-400 transition flex-1"
              id="labelSensDebit">
              <input type="radio" name="sens_ecriture" id="sensDebit" value="debit" class="text-blue-600">
              <div>
                <div class="font-bold text-slate-800 text-sm">Débit 📤</div>
                <div class="text-xs text-slate-400">Charge / Sortie (frais bancaires…)</div>
              </div>
            </label>
            <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-green-400 transition flex-1"
              id="labelSensCredit">
              <input type="radio" name="sens_ecriture" id="sensCredit" value="credit" class="text-green-600">
              <div>
                <div class="font-bold text-slate-800 text-sm">Crédit 📥</div>
                <div class="text-xs text-slate-400">Produit / Entrée (intérêts créditeurs…)</div>
              </div>
            </label>
          </div>
        </div>

        {{-- Compte de contrepartie --}}
        <div class="col-12">
          <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">
            🏷️ Compte de contrepartie <span class="text-red-500">*</span>
          </label>

          {{-- Recherche rapide --}}
          <div class="relative mb-2">
            <input type="text" id="rechercheCompte"
              class="w-full border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="Rechercher un compte (ex: 627 frais)…">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          </div>

          <select id="genPlanComptableId"
            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none"
            size="6" style="overflow-y:auto;">
            @foreach($planComptables as $p)
              <option value="{{ $p->id }}" data-num="{{ $p->numero_de_compte }}" data-lib="{{ strtolower($p->intitule ?? '') }}">
                {{ $p->numero_de_compte }} — {{ $p->intitule }}
              </option>
            @endforeach
          </select>
          <div class="text-xs text-slate-400 mt-1">
            💡 Comptes fréquents : <button type="button" class="text-blue-600 underline font-medium" onclick="filtreRapide('627')">627 Frais bancaires</button>
            · <button type="button" class="text-blue-600 underline font-medium" onclick="filtreRapide('775')">775 Intérêts</button>
            · <button type="button" class="text-blue-600 underline font-medium" onclick="filtreRapide('512')">512 Banque</button>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="flex gap-3 mt-5 pt-4" style="border-top:1px solid #e2e8f0;">
        <button type="button" class="flex-1 py-2.5 rounded-xl text-slate-500 border border-slate-200 text-sm font-semibold hover:bg-slate-50 transition"
          data-bs-dismiss="modal">Annuler</button>
        <button type="button" onclick="confirmerGenererEcriture()"
          class="flex-1 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-bold hover:bg-blue-800 transition flex items-center justify-center gap-2"
          id="btnConfirmerEcriture">
          <i class="fas fa-save"></i> Créer l'écriture et pointer
        </button>
      </div>
    </div>
  </div>
</div>

{{-- TOAST de notification --}}
<div id="toastNotif" class="hidden" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;max-width:360px;">
  <div class="glass-card px-5 py-3 flex items-center gap-3 shadow-xl">
    <span id="toastIcon" class="text-xl">✅</span>
    <span id="toastMsg" class="text-sm font-semibold text-slate-800"></span>
  </div>
</div>

{{-- ═══════════════════════ JAVASCRIPT ═══════════════════════ --}}
<script>
const RAPPROCHEMENT_ID = {{ $rapprochement->id }};
const CSRF_TOKEN = '{{ csrf_token() }}';

// ── Helpers ──────────────────────────────────────────────────────────
function toast(msg, type='success') {
    const el = document.getElementById('toastNotif');
    document.getElementById('toastIcon').textContent = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    document.getElementById('toastMsg').textContent = msg;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}

async function apiPost(url, data={}) {
    const r = await fetch(url, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json'},
        body: JSON.stringify(data)
    });
    return r.json();
}

async function apiDelete(url) {
    const r = await fetch(url, {
        method:'DELETE',
        headers:{'X-CSRF-TOKEN':CSRF_TOKEN,'Accept':'application/json'}
    });
    return r.json();
}

// ── Mise à jour des stats UI ──────────────────────────────────────────
function updateStats(stats) {
    if(!stats) return;
    document.getElementById('stat-nb-lignes')?.textContent !== undefined && (document.getElementById('stat-nb-lignes').textContent = stats.nb_lignes_releve);
    document.getElementById('stat-pointees').textContent  = stats.nb_pointees;
    document.getElementById('stat-non-pointees').textContent = stats.nb_non_pointees;
    document.getElementById('stat-ecart').textContent = formatNum(stats.ecart_residuel) + ' FCFA';
    document.getElementById('th-solde-bancaire-reel').textContent = formatNum(stats.solde_bancaire_reel);
    document.getElementById('th-solde-compta-reel').textContent   = formatNum(stats.solde_compta_reel);
    document.getElementById('th-equilibre-label').textContent = stats.equilibre
        ? '✅ ÉQUILIBRÉ'
        : '⚠️ Écart : ' + formatNum(stats.ecart_residuel) + ' FCFA';
    document.getElementById('nb-compta-non-banque').textContent = stats.nb_ecritures_non_pointees + ' écriture(s)';
    document.getElementById('nb-banque-non-compta').textContent  = stats.nb_non_pointees + ' ligne(s)';
}

function formatNum(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n));
}

// ── DRAG & DROP ───────────────────────────────────────────────────────
['dragenter','dragover'].forEach(ev => {
    document.getElementById('dropZone')?.addEventListener(ev, e => {
        e.preventDefault();
        document.getElementById('dropZone').classList.add('drag-over');
    });
});
['dragleave','drop'].forEach(ev => {
    document.getElementById('dropZone')?.addEventListener(ev, e => {
        e.preventDefault();
        document.getElementById('dropZone').classList.remove('drag-over');
        if(ev === 'drop' && e.dataTransfer.files.length) importerFichier(e.dataTransfer.files[0]);
    });
});
document.getElementById('inputFichier')?.addEventListener('change', e => {
    if(e.target.files.length) importerFichier(e.target.files[0]);
});
document.getElementById('inputFichierUpdate')?.addEventListener('change', e => {
    if(e.target.files.length) importerFichier(e.target.files[0]);
});

async function importerFichier(file) {
    const zone = document.getElementById('dropZone');
    const prog = document.getElementById('uploadProgress');
    const errDiv = document.getElementById('importErreur');
    if(prog) prog.classList.remove('hidden');
    if(errDiv) errDiv.classList.add('hidden');

    const fd = new FormData();
    fd.append('fichier', file);
    fd.append('_token', CSRF_TOKEN);

    try {
        const r = await fetch(`/rapprochement/${RAPPROCHEMENT_ID}/import`, {method:'POST', body:fd});
        const data = await r.json();
        if(data.success) {
            toast(`✅ ${data.nb_lignes} ligne(s) importée(s)`, 'success');
            if(zone) zone.classList.add('has-file');
            document.getElementById('nomFichierReleve').textContent = file.name;
            renderTableReleve(data.lignes);
            document.getElementById('resultatsSection')?.classList.remove('hidden');
            document.getElementById('tableReleveWrapper')?.removeAttribute('style');
        } else {
            if(errDiv) { errDiv.textContent = data.message; errDiv.classList.remove('hidden'); }
            toast(data.message, 'error');
        }
    } catch(e) {
        toast('Erreur réseau lors de l\'import', 'error');
    } finally {
        if(prog) prog.classList.add('hidden');
    }
}

function renderTableReleve(lignes) {
    const tbody = document.getElementById('tbodyReleve');
    if(!tbody) return;
    tbody.innerHTML = '';
    lignes.forEach(l => {
        const debit  = l.debit  > 0 ? `<span class="text-red-600 font-bold">${formatNum(l.debit)}</span>` : '';
        const credit = l.credit > 0 ? `<span class="text-green-600 font-bold">${formatNum(l.credit)}</span>` : '';
        tbody.insertAdjacentHTML('beforeend', `
          <tr class="releve-row pointable"
            data-id="${l.id}" data-montant="${Math.max(l.debit,l.credit)}"
            data-libelle="${(l.libelle||'').replace(/"/g,'&quot;')}"
            data-date="${l.date_operation}" data-statut="non_pointe">
            <td class="font-mono text-xs px-3 py-2">${formatDate(l.date_operation)}</td>
            <td class="text-xs truncate max-w-xs px-3 py-2" title="${l.libelle||''}">${(l.libelle||'').substring(0,30)}</td>
            <td class="text-right text-xs px-3 py-2">${debit}</td>
            <td class="text-right text-xs px-3 py-2">${credit}</td>
            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs font-bold badge-non-pointe">○</span></td>
          </tr>`);
    });
    document.getElementById('tableReleveWrapper')?.removeAttribute('style');
}

function formatDate(d) {
    if(!d) return '—';
    const p = d.split('-');
    return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : d;
}

// ── ANALYSER ─────────────────────────────────────────────────────────
document.getElementById('btnAnalyser')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnAnalyser');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Analyse…';
    try {
        const data = await apiPost(`/rapprochement/${RAPPROCHEMENT_ID}/analyser`);
        if(data.success) { updateStats(data.stats); toast('Analyse terminée', 'success'); }
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-search"></i> Analyser';
    }
});

// ── RAPPROCHEMENT AUTO ────────────────────────────────────────────────
document.getElementById('btnAutoRapprochement')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnAutoRapprochement');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Traitement…';
    try {
        const data = await apiPost(`/rapprochement/${RAPPROCHEMENT_ID}/auto`);
        if(data.success) {
            toast(`✅ ${data.auto_result.pointes} pointage(s) automatique(s) — ${data.auto_result.ambigus} ambigu(s)`, 'success');
            updateStats(data.stats);
            setTimeout(() => location.reload(), 1500);
        }
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic"></i> Rapprochement Auto';
    }
});

// ── RAPPROCHEMENT MANUEL ──────────────────────────────────────────────
let selectedLigne   = null;
let selectedEcriture = null;
let modeManuel = false;

document.getElementById('btnRapprochementManuel')?.addEventListener('click', () => {
    modeManuel = !modeManuel;
    const btn = document.getElementById('btnRapprochementManuel');
    const info = document.getElementById('modeManuelInfo');
    if(modeManuel) {
        btn.classList.add('ring-2','ring-orange-400');
        info.classList.remove('hidden');
        document.body.classList.add('pointer-mode');
        toast('Mode manuel activé — Cliquez une ligne du relevé, puis une écriture', 'info');
    } else {
        btn.classList.remove('ring-2','ring-orange-400');
        info.classList.add('hidden');
        document.body.classList.remove('pointer-mode');
        clearSelection();
    }
});

document.addEventListener('click', e => {
    if(!modeManuel) return;
    const releveRow   = e.target.closest('.releve-row.pointable');
    const ecritureRow = e.target.closest('.ecriture-row.pointable');
    if(releveRow) { selectRow(releveRow, 'releve'); }
    if(ecritureRow) { selectRow(ecritureRow, 'ecriture'); }
    if(selectedLigne && selectedEcriture) creerPointageManuel();
});

function selectRow(row, type) {
    document.querySelectorAll('.selected-row').forEach(r => r.classList.remove('selected-row'));
    row.classList.add('selected-row');
    if(type === 'releve')   selectedLigne    = row.dataset.id;
    if(type === 'ecriture') selectedEcriture = row.dataset.id;
}

function clearSelection() {
    selectedLigne = null; selectedEcriture = null;
    document.querySelectorAll('.selected-row').forEach(r => r.classList.remove('selected-row'));
}

async function creerPointageManuel() {
    try {
        const data = await apiPost(`/rapprochement/${RAPPROCHEMENT_ID}/pointage`, {
            ligne_releve_id: selectedLigne,
            ecriture_comptable_id: selectedEcriture
        });
        if(data.success) {
            toast('✅ Pointage manuel créé', 'success');
            updateStats(data.stats);
            setTimeout(() => location.reload(), 1200);
        } else {
            toast(data.message || 'Erreur lors du pointage', 'error');
        }
    } finally { clearSelection(); }
}

// ── ANNULER POINTAGE ─────────────────────────────────────────────────
async function annulerPointage(pointageId) {
    if(!confirm('Annuler ce pointage ?')) return;
    const data = await apiDelete(`/rapprochement/${RAPPROCHEMENT_ID}/pointage/${pointageId}`);
    if(data.success) {
        toast('Pointage annulé', 'info');
        updateStats(data.stats);
        document.getElementById(`pointage-row-${pointageId}`)?.remove();
    }
}

// ── ENREGISTRER ──────────────────────────────────────────────────────
document.getElementById('btnEnregistrer')?.addEventListener('click', async () => {
    const btn = document.getElementById('btnEnregistrer');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sauvegarde…';
    try {
        const data = await apiPost(`/rapprochement/${RAPPROCHEMENT_ID}/enregistrer`);
        if(data.success) toast(data.message, data.statut === 'valide' ? 'success' : 'info');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i> Enregistrer';
    }
});

// ── GÉNÉRER ÉCRITURE CORRECTIVE ───────────────────────────────────────
function ouvrirGenererEcriture(ligneId, libelle, montant, date, debit, credit) {
    document.getElementById('genLigneId').value         = ligneId;
    document.getElementById('genLibelle').textContent   = libelle;
    document.getElementById('genMontant').textContent   = formatNum(montant) + ' FCFA';
    document.getElementById('genDate').textContent      = date ? formatDate(date) : '—';
    document.getElementById('genDebit').value           = debit  || 0;
    document.getElementById('genCredit').value          = credit || 0;
    document.getElementById('genDescription').value     = libelle;

    // Auto-sélection du sens selon le mouvement bancaire
    // Banque DÉBIT  (sortie d'argent) → on débite la charge (frais)
    // Banque CRÉDIT (entrée d'argent) → on crédite le produit (intérêts)
    const d = parseFloat(debit)  || 0;
    const c = parseFloat(credit) || 0;
    if (d > 0) {
        document.getElementById('sensDebit').checked  = true;
        document.getElementById('sensCredit').checked = false;
        document.getElementById('labelSensDebit').classList.add('border-blue-500','bg-blue-50');
        document.getElementById('labelSensCredit').classList.remove('border-green-500','bg-green-50');
        filtreRapide('627'); // suggestion : frais bancaires
    } else if (c > 0) {
        document.getElementById('sensCredit').checked = true;
        document.getElementById('sensDebit').checked  = false;
        document.getElementById('labelSensCredit').classList.add('border-green-500','bg-green-50');
        document.getElementById('labelSensDebit').classList.remove('border-blue-500','bg-blue-50');
        filtreRapide('775'); // suggestion : intérêts créditeurs
    }

    new bootstrap.Modal(document.getElementById('modalGenererEcriture')).show();
}

// Filtre rapide (boutons suggestion)
function filtreRapide(terme) {
    document.getElementById('rechercheCompte').value = terme;
    filtrerComptes(terme);
}

// Filtre live du select plan comptable
function filtrerComptes(terme) {
    const select  = document.getElementById('genPlanComptableId');
    const options = select.querySelectorAll('option');
    const q       = (terme || '').toLowerCase();
    let firstVisible = null;
    options.forEach(opt => {
        const num = (opt.dataset.num || '').toLowerCase();
        const lib = (opt.dataset.lib || '').toLowerCase();
        const visible = !q || num.startsWith(q) || lib.includes(q);
        opt.style.display = visible ? '' : 'none';
        if (visible && !firstVisible) firstVisible = opt;
    });
    if (firstVisible) {
        select.querySelectorAll('option').forEach(o => o.selected = false);
        firstVisible.selected = true;
    }
}

// Live search
document.getElementById('rechercheCompte')?.addEventListener('input', e => filtrerComptes(e.target.value));

// Highlight radio sens
document.querySelectorAll('input[name="sens_ecriture"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const isDebit  = document.getElementById('sensDebit').checked;
        const isCredit = document.getElementById('sensCredit').checked;
        document.getElementById('labelSensDebit').classList.toggle('border-blue-500', isDebit);
        document.getElementById('labelSensDebit').classList.toggle('bg-blue-50', isDebit);
        document.getElementById('labelSensCredit').classList.toggle('border-green-500', isCredit);
        document.getElementById('labelSensCredit').classList.toggle('bg-green-50', isCredit);
    });
});

async function confirmerGenererEcriture() {
    const ligneId = document.getElementById('genLigneId').value;
    const planId  = document.getElementById('genPlanComptableId').value;
    const desc    = document.getElementById('genDescription').value.trim();
    const sensEl  = document.querySelector('input[name="sens_ecriture"]:checked');
    const sens    = sensEl ? sensEl.value : null;

    if (!planId) { toast('Sélectionnez un compte de contrepartie', 'error'); return; }
    if (!desc)   { toast('Saisissez un libellé pour l\'écriture',   'error'); return; }

    const btn = document.getElementById('btnConfirmerEcriture');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Création…';

    try {
        const data = await apiPost(`/rapprochement/${RAPPROCHEMENT_ID}/ecriture`, {
            ligne_releve_id:   ligneId,
            plan_comptable_id: planId,
            description:       desc,
            sens:              sens,
        });
        if (data.success) {
            toast(data.message || '✅ Écriture créée', 'success');
            if (data.stats) updateStats(data.stats);
            bootstrap.Modal.getInstance(document.getElementById('modalGenererEcriture')).hide();
            setTimeout(() => location.reload(), 1800);
        } else {
            toast(data.message || 'Erreur lors de la création', 'error');
        }
    } catch(e) {
        toast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Créer l\'écriture et pointer';
    }
}
</script>

</body>
</html>
