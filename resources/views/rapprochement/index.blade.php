<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
.glass-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 10px 25px -5px rgba(0,0,0,.05); }
.btn-action  { transition:all .2s cubic-bezier(.4,0,.2,1); }
.btn-action:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(30,64,175,.2); }
.table-row:hover  { background-color:#f1f5f9; }
.badge-statut-en_cours  { background:#eff6ff; color:#1d4ed8; }
.badge-statut-valide    { background:#f0fdf4; color:#16a34a; }
.badge-statut-cloture   { background:#f1f5f9; color:#64748b; }
.input-field-premium    { transition:all .2s ease; border:2px solid #f1f5f9!important; background:#f8fafc!important;
    border-radius:12px!important; padding:.75rem 1rem!important; font-size:.8rem!important;
    font-weight:600!important; color:#0f172a!important; width:100%; box-sizing:border-box; }
.input-field-premium:focus { border-color:#1e40af!important; background:#fff!important;
    box-shadow:0 0 0 4px rgba(30,64,175,.05)!important; outline:none!important; }
.input-label-premium { font-size:.7rem!important; font-weight:800!important; color:#64748b!important;
    text-transform:uppercase!important; letter-spacing:.05em!important; margin-bottom:.35rem!important; display:block!important; }
.btn-save-premium { padding:.75rem 1rem!important; border-radius:12px!important; background:#1e40af!important;
    color:#fff!important; font-weight:800!important; font-size:.8rem!important; border:none!important; width:100%; }
.btn-save-premium:hover { background:#1e3a8a!important; transform:translateY(-1px)!important; }
.premium-modal-content { background:rgba(255,255,255,.98); border-radius:20px;
    box-shadow:0 20px 30px -10px rgba(0,0,0,.1); max-width:560px; margin:auto; padding:1.5rem!important; }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">
    @include('components.sidebar')

    <div class="layout-page">
      @include('components.header', ['page_title' => 'Rapprochement <span class="text-gradient">Bancaire</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-emerald-700 uppercase bg-emerald-50 rounded-full ml-3">Trésorerie</span>'])

      <div class="content-wrapper">
        <div class="container-fluid flex-grow-1 container-p-y">

          {{-- Alertes --}}
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-5 rounded-2xl border-0 bg-green-50 text-green-800">
              <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          {{-- Barre d'actions --}}
          <div class="flex justify-between items-center mb-6 gap-4">
            <div>
              <p class="text-slate-500 text-sm">Comparez vos écritures du compte banque avec vos relevés bancaires pour détecter les écarts.</p>
            </div>
            <button type="button" data-bs-toggle="modal" data-bs-target="#modalNouveauRapprochement"
              class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
              <i class="fas fa-plus"></i> Nouveau Rapprochement
            </button>
          </div>

          {{-- Tableau des sessions --}}
          <div class="glass-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center">
                <i class="fas fa-balance-scale text-white"></i>
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-800 mb-0">Sessions de Rapprochement</h3>
                <p class="text-xs text-slate-500 mb-0">Historique de tous vos rapprochements bancaires</p>
              </div>
            </div>

            <div class="table-responsive">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Compte Banque</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Période</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Fichier Relevé</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Solde Banque</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Créé le</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  @forelse($rapprochements as $r)
                    <tr class="table-row">
                      <td class="px-6 py-4">
                        <div class="font-semibold text-slate-800 text-sm">{{ $r->compteTresorerie->name ?? '—' }}</div>
                        <div class="text-xs text-slate-400 font-mono">{{ $r->codeJournal->code_journal ?? '' }}</div>
                      </td>
                      <td class="px-6 py-4 text-sm text-slate-600">
                        {{ $r->date_debut->format('d/m/Y') }} → {{ $r->date_fin->format('d/m/Y') }}
                      </td>
                      <td class="px-6 py-4 text-sm text-slate-500">
                        {{ $r->nom_fichier_releve ?? '<span class="italic text-slate-300">Aucun fichier</span>' }}
                      </td>
                      <td class="px-6 py-4 text-sm font-mono font-bold text-slate-700">
                        {{ number_format($r->solde_final_banque, 0, ',', ' ') }} FCFA
                      </td>
                      <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold badge-statut-{{ $r->statut }}">
                          @if($r->statut === 'valide') ✅ Validé
                          @elseif($r->statut === 'cloture') 🔒 Clôturé
                          @else 🔄 En cours
                          @endif
                        </span>
                      </td>
                      <td class="px-6 py-4 text-xs text-slate-500">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                      <td class="px-6 py-4 text-right">
                        <a href="{{ route('rapprochement.show', $r->id) }}"
                          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold hover:bg-blue-100 transition">
                          <i class="fas fa-arrow-right"></i> Ouvrir
                        </a>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="px-8 py-16 text-center text-slate-400">
                        <div class="flex flex-col items-center gap-3">
                          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center">
                            <i class="fas fa-balance-scale text-slate-300 text-3xl"></i>
                          </div>
                          <span class="font-medium">Aucun rapprochement effectué pour le moment.</span>
                          <button type="button" data-bs-toggle="modal" data-bs-target="#modalNouveauRapprochement"
                            class="mt-2 px-6 py-2 bg-blue-700 text-white rounded-xl text-sm font-semibold">
                            Créer le premier rapprochement
                          </button>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($rapprochements->hasPages())
              <div class="px-6 py-4 border-t border-slate-100">
                {{ $rapprochements->links() }}
              </div>
            @endif
          </div>

        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
</div>

@include('components.footer')

{{-- ═══════════════════════ MODAL : NOUVEAU RAPPROCHEMENT ═══════════════════════ --}}
<div class="modal fade" id="modalNouveauRapprochement" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <form method="POST" action="{{ route('rapprochement.store') }}" id="formNouveauRapprochement">
      @csrf
      <div class="modal-content premium-modal-content" style="max-width:640px;">

        {{-- Entête --}}
        <div class="text-center mb-5 position-relative">
          <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal"></button>
          <div class="d-inline-flex align-items-center justify-content-center mb-3"
            style="width:60px;height:60px;background:linear-gradient(135deg,#1e40af,#3b82f6);border-radius:16px;">
            <i class="fas fa-balance-scale" style="font-size:28px;color:white;"></i>
          </div>
          <h2 class="font-extrabold text-slate-900 mb-1" style="font-size:1.4rem;">Nouveau Rapprochement</h2>
          <p class="text-muted text-sm">Sélectionnez le compte banque et la période à rapprocher</p>
        </div>

        {{-- Corps --}}
        <div class="row g-3">

          {{-- Compte banque --}}
          <div class="col-12">
            <label class="input-label-premium">🏦 Compte Banque</label>
            <select name="compte_tresorerie_id" id="compteSelect" class="input-field-premium" required>
              <option value="">— Sélectionnez un compte bancaire —</option>
              @foreach($comptesBancaires as $c)
                <option value="{{ $c->id }}"
                  data-compte="{{ $c->compteComptable->numero_de_compte ?? '' }}">
                  {{ $c->name }}
                  @if($c->compteComptable) — {{ $c->compteComptable->numero_de_compte }} @endif
                </option>
              @endforeach
            </select>
          </div>

          {{-- Code Journal --}}
          <div class="col-12">
            <label class="input-label-premium">📒 Code Journal (Banque)</label>
            <select name="code_journal_id" id="codeJournalSelect" class="input-field-premium">
              <option value="">— Sélectionnez le journal banque —</option>
              @foreach($codeJournaux as $j)
                <option value="{{ $j->id }}">{{ $j->code_journal }} — {{ $j->intitule }}</option>
              @endforeach
            </select>
            <span class="text-xs text-slate-400">Journal utilisé pour générer les écritures correctives</span>
          </div>

          {{-- Exercice --}}
          <div class="col-12">
            <label class="input-label-premium">📅 Exercice comptable</label>
            <select name="exercice_id" class="input-field-premium" required>
              <option value="">— Sélectionnez un exercice —</option>
              @foreach($exercices as $ex)
                <option value="{{ $ex->id }}">
                  {{ \Carbon\Carbon::parse($ex->date_debut)->format('d/m/Y') }} →
                  {{ \Carbon\Carbon::parse($ex->date_fin)->format('d/m/Y') }}
                  @if($ex->is_active) (Actif) @endif
                </option>
              @endforeach
            </select>
          </div>

          {{-- Période --}}
          <div class="col-6">
            <label class="input-label-premium">📅 Date de début</label>
            <input type="date" name="date_debut" class="input-field-premium" required>
          </div>
          <div class="col-6">
            <label class="input-label-premium">📅 Date de fin</label>
            <input type="date" name="date_fin" class="input-field-premium" required>
          </div>

          {{-- Soldes --}}
          <div class="col-12"><hr class="my-1"><p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Soldes de référence</p></div>
          <div class="col-4">
            <label class="input-label-premium">Solde initial banque</label>
            <input type="number" step="0.01" name="solde_initial_banque" class="input-field-premium" placeholder="0" required>
          </div>
          <div class="col-4">
            <label class="input-label-premium">Solde final banque (relevé)</label>
            <input type="number" step="0.01" name="solde_final_banque" class="input-field-premium" placeholder="0" required>
          </div>
          <div class="col-4">
            <label class="input-label-premium">Solde actuel compta</label>
            <input type="number" step="0.01" name="solde_initial_compta" id="soldeComptaInput" class="input-field-premium" placeholder="0" required>
            <span class="text-xs text-slate-400">Solde du compte 512 à cette date</span>
          </div>
        </div>

        {{-- Footer --}}
        <div class="d-flex gap-3 mt-4 pt-3" style="border-top:1px solid #e2e8f0;">
          <button type="button" class="btn flex-fill py-2 rounded-xl text-slate-500" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-save-premium flex-fill py-2">
            <i class="fas fa-arrow-right me-2"></i> Démarrer le rapprochement
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

</body>
</html>
