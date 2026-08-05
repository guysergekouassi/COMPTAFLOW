<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #f8fafc; color: #1e293b; }
    .glass-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); }
    .stat-badge { padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 11px; }
    .badge-active { background: #dcfce7; color: #166534; }
    .badge-inactive { background: #f1f5f9; color: #64748b; }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Liaisons COMPTAFLOW ↔ SELFLOW'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">

                    {{-- Alertes --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-6 rounded-xl" role="alert">
                            <i class="bx bx-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-6 rounded-xl" role="alert">
                            <i class="bx bx-error-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Header Banner --}}
                    <div class="d-flex justify-content-between align-items-center mb-6 flex-wrap gap-3">
                        <div>
                            <h3 class="fw-bold mb-1"><i class="fa-solid fa-link text-primary me-2"></i> Tableau croisé des liaisons</h3>
                            <p class="text-muted mb-0">Supervision des connexions et création d'entreprises bidirectionnelle.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary rounded-xl px-4 py-2 font-semibold" data-bs-toggle="modal" data-bs-target="#modalLierManuellement">
                                <i class="fa-solid fa-plug me-2"></i> Lier manuellement
                            </button>
                            <button type="button" class="btn btn-primary rounded-xl px-4 py-2 font-semibold" data-bs-toggle="modal" data-bs-target="#modalCreerSurSelflow">
                                <i class="fa-solid fa-paper-plane me-2"></i> Créer sur SELFLOW
                            </button>
                            <button type="button" class="btn btn-success rounded-xl px-4 py-2 font-semibold text-white" data-bs-toggle="modal" data-bs-target="#modalCreerDepuisSelflow">
                                <i class="fa-solid fa-plus-circle me-2"></i> Créer depuis SELFLOW
                            </button>
                        </div>
                    </div>

                    {{-- Table Card --}}
                    <div class="glass-card overflow-hidden mb-8">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="ps-6">Entreprise COMPTAFLOW</th>
                                        <th>ID COMPTAFLOW</th>
                                        <th>ID Selflow</th>
                                        <th>Statut Liaison</th>
                                        <th>Dernière Sync</th>
                                        <th class="pe-6 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($companies as $comp)
                                        @php $isLinked = !empty($comp->selflow_company_id); @endphp
                                        <tr>
                                            <td class="ps-6">
                                                <div class="fw-bold text-slate-800">{{ $comp->company_name }}</div>
                                                <div class="text-xs text-muted">{{ $comp->juridique_form ?? 'SARL' }} · {{ $comp->email_adresse }}</div>
                                                @if($comp->rccm) <div class="text-[10px] text-slate-400 font-mono">RCCM: {{ $comp->rccm }}</div> @endif
                                            </td>
                                            <td><span class="badge bg-blue-100 text-blue-700 font-mono font-bold">#{{ $comp->id }}</span></td>
                                            <td>
                                                @if($isLinked)
                                                    <span class="badge bg-indigo-100 text-indigo-700 font-mono font-bold">#{{ $comp->selflow_company_id }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($isLinked && $comp->selflow_sync_status === 'active')
                                                    <span class="stat-badge badge-active"><i class="fa-solid fa-circle-check me-1"></i> Active (selflow ↔ comptaflow)</span>
                                                @else
                                                    <span class="stat-badge badge-inactive"><i class="fa-solid fa-unlink me-1"></i> Non liée</span>
                                                @endif
                                            </td>
                                            <td class="text-xs text-muted">
                                                {{ $comp->selflow_last_sync_at ? \Carbon\Carbon::parse($comp->selflow_last_sync_at)->format('d/m/Y H:i') : '—' }}
                                            </td>
                                            <td class="pe-6 text-end">
                                                @if($isLinked)
                                                    <form method="POST" action="{{ route('superadmin.liaisons.destroy', $comp->id) }}" class="d-inline" onsubmit="return confirm('Délier cette entreprise ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-lg" title="Supprimer la liaison">
                                                            <i class="fa-solid fa-unlink"></i> Délier
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-primary btn-sm rounded-lg" onclick="ouvrirCreerSurSelflow({{ $comp->id }})">
                                                        <i class="fa-solid fa-paper-plane me-1"></i> Créer sur Selflow
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-6 text-muted">Aucune entreprise trouvée dans COMPTAFLOW.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 1 : Créer l'entreprise sur SELFLOW depuis une entreprise COMPTAFLOW --}}
    <div class="modal fade" id="modalCreerSurSelflow" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-2xl border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-100 px-6 py-4">
                    <h5 class="modal-title fw-bold text-slate-800"><i class="fa-solid fa-paper-plane text-primary me-2"></i> Créer sur SELFLOW (depuis COMPTAFLOW)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('superadmin.liaisons.creerSurSelflow') }}">
                    @csrf
                    <div class="modal-body p-6">
                        <div class="alert alert-primary rounded-xl text-xs mb-4">
                            <i class="fa-solid fa-info-circle me-1"></i> Sélectionner une entreprise COMPTAFLOW. Ses données (Nom, RCCM, NCC, Admin, Email) seront automatiquement transférées à SELFLOW pour y créer le compte entreprise + admin.
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-xs text-uppercase text-slate-500">Entreprise COMPTAFLOW non liée <span class="text-danger">*</span></label>
                            <select name="comptaflow_company_id" id="select-cptf-sur-sf" class="form-select rounded-xl" required onchange="afficherInfosCptf(this)">
                                <option value="">— Sélectionner une entreprise COMPTAFLOW —</option>
                                @foreach($companies->whereNull('selflow_company_id') as $cptf)
                                    @php $adm = \App\Models\User::where('company_id', $cptf->id)->where('role', 'admin')->first(); @endphp
                                    <option value="{{ $cptf->id }}"
                                        data-nom="{{ $cptf->company_name }}"
                                        data-email="{{ $cptf->email_adresse }}"
                                        data-rccm="{{ $cptf->rccm ?? '—' }}"
                                        data-ncc="{{ $cptf->ncc ?? '—' }}"
                                        data-admin="{{ $adm ? ($adm->name . ' ' . $adm->last_name) : 'Admin' }}">
                                        #{{ $cptf->id }} — {{ $cptf->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="box-infos-cptf" style="display:none;" class="bg-slate-50 p-4 rounded-xl mb-4 border border-slate-200 text-xs">
                            <div class="grid grid-cols-2 gap-3">
                                <div><span class="text-slate-400 font-bold uppercase block">Nom entreprise</span><strong id="cptf-nom">—</strong></div>
                                <div><span class="text-slate-400 font-bold uppercase block">Admin</span><strong id="cptf-admin">—</strong></div>
                                <div><span class="text-slate-400 font-bold uppercase block">Email admin</span><strong id="cptf-email">—</strong></div>
                                <div><span class="text-slate-400 font-bold uppercase block">RCCM / NCC</span><span id="cptf-rccm">—</span> / <span id="cptf-ncc">—</span></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-slate-500">Mot de passe pour l'administrateur SELFLOW <span class="text-danger">*</span></label>
                            <input type="password" name="admin_password" class="form-control rounded-xl" required minlength="8" placeholder="Définir le mot de passe admin Selflow (min 8 car.)">
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-100 px-6 py-3">
                        <button type="button" class="btn btn-light rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-4"><i class="fa-solid fa-rocket me-2"></i> Transférer et Créer sur SELFLOW</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 2 : Créer compte COMPTAFLOW depuis une entreprise SELFLOW --}}
    <div class="modal fade" id="modalCreerDepuisSelflow" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-2xl border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-100 px-6 py-4">
                    <h5 class="modal-title fw-bold text-slate-800"><i class="fa-solid fa-plus-circle text-success me-2"></i> Créer sur COMPTAFLOW (depuis SELFLOW)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('superadmin.liaisons.store') }}">
                    @csrf
                    <div class="modal-body p-6">
                        <div class="alert alert-success rounded-xl text-xs mb-4">
                            <i class="fa-solid fa-info-circle me-1"></i> Sélectionner une entreprise Selflow non encore liée. Ses coordonnées seront automatiquement pré-remplies.
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-xs text-uppercase text-slate-500">Entreprise Selflow <span class="text-danger">*</span></label>
                            <select name="selflow_company_id" class="form-select rounded-xl" required onchange="remplirChampsSelflow(this)">
                                <option value="">— Sélectionner une entreprise Selflow —</option>
                                @foreach($selflowCompanies as $sfComp)
                                    <option value="{{ $sfComp['id'] }}"
                                        data-nom="{{ $sfComp['nom'] }}"
                                        data-email="{{ $sfComp['email'] ?? $sfComp['admin_email'] ?? '' }}"
                                        data-tel="{{ $sfComp['telephone'] ?? '' }}"
                                        data-adresse="{{ $sfComp['adresse'] ?? '' }}"
                                        data-rccm="{{ $sfComp['rccm'] ?? '' }}"
                                        data-ncc="{{ $sfComp['ncc'] ?? '' }}"
                                        data-regime="{{ $sfComp['regime_imposition'] ?? '' }}"
                                        data-gerant-nom="{{ $sfComp['gerant_nom'] ?? '' }}"
                                        data-gerant-prenom="{{ $sfComp['gerant_prenom'] ?? '' }}"
                                        {{ !empty($sfComp['is_linked']) ? 'disabled' : '' }}>
                                        #{{ $sfComp['id'] }} — {{ $sfComp['nom'] }} {{ !empty($sfComp['is_linked']) ? '(Déjà liée)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-slate-500">Nom de l'entreprise COMPTAFLOW</label>
                                <input type="text" name="company_name" id="sf_nom" class="form-control rounded-xl" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-slate-500">Email administrateur</label>
                                <input type="email" name="email_adresse" id="sf_email" class="form-control rounded-xl" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-slate-500">RCCM</label>
                                <input type="text" name="rccm" id="sf_rccm" class="form-control rounded-xl">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-slate-500">NCC</label>
                                <input type="text" name="ncc" id="sf_ncc" class="form-control rounded-xl">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-slate-500">Nom Admin</label>
                                <input type="text" name="admin_nom" id="sf_gerant_nom" class="form-control rounded-xl">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs fw-bold text-slate-500">Prénom Admin</label>
                                <input type="text" name="admin_prenom" id="sf_gerant_prenom" class="form-control rounded-xl">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-xs fw-bold text-slate-500">Mot de passe COMPTAFLOW <span class="text-danger">*</span></label>
                                <input type="password" name="admin_password" class="form-control rounded-xl" required minlength="8" placeholder="Mot de passe du nouveau compte (min 8 car.)">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-100 px-6 py-3">
                        <button type="button" class="btn btn-light rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success text-white rounded-xl px-4"><i class="fa-solid fa-rocket me-2"></i> Créer et Lier sur COMPTAFLOW</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 3 : Lier manuellement --}}
    <div class="modal fade" id="modalLierManuellement" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-2xl border-0 shadow-2xl">
                <div class="modal-header border-b border-slate-100 px-6 py-4">
                    <h5 class="modal-title fw-bold text-slate-800"><i class="fa-solid fa-plug text-primary me-2"></i> Lier manuellement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('superadmin.liaisons.lier') }}">
                    @csrf
                    <div class="modal-body p-6">
                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-slate-500">Entreprise COMPTAFLOW</label>
                            <select name="comptaflow_company_id" class="form-select rounded-xl" required>
                                <option value="">— Sélectionner —</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}">#{{ $c->id }} — {{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-slate-500">ID Entreprise SELFLOW</label>
                            <input type="number" name="selflow_company_id" class="form-control rounded-xl" required placeholder="Ex: 12">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs fw-bold text-slate-500">Clé de synchronisation</label>
                            <input type="text" name="selflow_sync_key" class="form-control rounded-xl" required placeholder="Ex: sf_abc123xyz">
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-100 px-6 py-3">
                        <button type="button" class="btn btn-light rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-xl px-4">Enregistrer la liaison</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function ouvrirCreerSurSelflow(cptfId) {
        const sel = document.getElementById('select-cptf-sur-sf');
        sel.value = cptfId;
        afficherInfosCptf(sel);
        const modal = new bootstrap.Modal(document.getElementById('modalCreerSurSelflow'));
        modal.show();
    }

    function afficherInfosCptf(sel) {
        const opt = sel.selectedOptions[0];
        const box = document.getElementById('box-infos-cptf');
        if (!opt || !opt.value) { box.style.display = 'none'; return; }

        document.getElementById('cptf-nom').textContent   = opt.dataset.nom   || '—';
        document.getElementById('cptf-admin').textContent = opt.dataset.admin || '—';
        document.getElementById('cptf-email').textContent = opt.dataset.email || '—';
        document.getElementById('cptf-rccm').textContent  = opt.dataset.rccm  || '—';
        document.getElementById('cptf-ncc').textContent   = opt.dataset.ncc   || '—';
        box.style.display = 'block';
    }

    function remplirChampsSelflow(selectElem) {
        const opt = selectElem.selectedOptions[0];
        if (!opt || !opt.value) return;

        document.getElementById('sf_nom').value           = opt.dataset.nom || '';
        document.getElementById('sf_email').value         = opt.dataset.email || '';
        document.getElementById('sf_rccm').value          = opt.dataset.rccm || '';
        document.getElementById('sf_ncc').value           = opt.dataset.ncc || '';
        document.getElementById('sf_gerant_nom').value    = opt.dataset.gerantNom || '';
        document.getElementById('sf_gerant_prenom').value = opt.dataset.gerantPrenom || '';
    }
    </script>
</body>
</html>
