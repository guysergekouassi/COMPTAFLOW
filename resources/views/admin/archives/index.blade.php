<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<style>
    .archive-table thead th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        border-top: none;
    }
    .archive-row:hover { background-color: rgba(239, 68, 68, 0.02) !important; }
    .archive-badge {
        padding: 0.35rem 0.7rem;
        border-radius: 8px;
        font-size: 0.68rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .bg-module { background: #f1f5f9; color: #475569; }
    .bg-lot    { background: #fef3c7; color: #b45309; }
    .bg-jours  { background: #ecfdf5; color: #059669; }
    .bg-urgent { background: #fef2f2; color: #dc2626; }
    .stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.25rem 1.5rem;
    }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Archive des <span class="text-danger">suppressions</span>'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        {{-- En-tête --}}
                        <div class="bg-white p-6 rounded-[24px] shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-3 border border-slate-100 mb-6">
                            <div>
                                <h4 class="font-black mb-1 text-slate-800">Archive des suppressions</h4>
                                <p class="text-slate-500 mb-0">
                                    Tout ce qui est supprimé dans
                                    <strong>{{ $company->company_name ?? 'cette comptabilité' }}</strong>
                                    reste consultable ici pendant {{ $stats['retention'] }} jours, suppressions en lot comprises.
                                </p>
                            </div>
                            <button class="btn btn-outline-secondary px-4 py-2 rounded-xl font-bold" data-bs-toggle="collapse" data-bs-target="#filterArchives">
                                <i class="fa-solid fa-filter me-2"></i>Filtrer
                            </button>
                        </div>

                        {{-- Compteurs --}}
                        <div class="row g-4 mb-6">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="text-slate-400 text-uppercase fw-bold" style="font-size:0.68rem;">Éléments conservés</div>
                                    <div class="font-black text-slate-800" style="font-size:1.6rem;">{{ number_format($stats['total'], 0, ',', ' ') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="text-slate-400 text-uppercase fw-bold" style="font-size:0.68rem;">Suppressions en lot</div>
                                    <div class="font-black text-slate-800" style="font-size:1.6rem;">{{ number_format($stats['lots'], 0, ',', ' ') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="text-slate-400 text-uppercase fw-bold" style="font-size:0.68rem;">Purgés sous 7 jours</div>
                                    <div class="font-black text-danger" style="font-size:1.6rem;">{{ number_format($stats['expire_7j'], 0, ',', ' ') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Filtres --}}
                        <div class="collapse mb-6" id="filterArchives">
                            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm">
                                <form action="{{ route('admin.archives') }}" method="GET" class="row g-4">
                                    <div class="col-md-3">
                                        <label class="form-label font-bold text-xs text-slate-500 uppercase">Supprimé par</label>
                                        <select name="user_id" class="form-select border-slate-200 rounded-xl py-2.5">
                                            <option value="">Tout le monde</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-bold text-xs text-slate-500 uppercase">Module</label>
                                        <select name="module" class="form-select border-slate-200 rounded-xl py-2.5">
                                            <option value="">Tous les modules</option>
                                            @foreach($modules as $module)
                                                <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                                                    {{ \App\Models\AuditLog::MODULES[$module] ?? $module }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-bold text-xs text-slate-500 uppercase">Recherche</label>
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Libellé, n° de pièce…"
                                               class="form-control border-slate-200 rounded-xl py-2.5">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-bold text-xs text-slate-500 uppercase">Lignes par page</label>
                                        <select name="per_page" class="form-select border-slate-200 rounded-xl py-2.5">
                                            @foreach([25, 50, 100] as $taille)
                                                <option value="{{ $taille }}" {{ $perPage === $taille ? 'selected' : '' }}>{{ $taille }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-xl font-bold">Appliquer les filtres</button>
                                        <a href="{{ route('admin.archives') }}" class="btn btn-outline-secondary px-4 py-2.5 rounded-xl">Réinitialiser</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Tableau --}}
                        <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table archive-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-6">Supprimé le</th>
                                            <th>Par</th>
                                            <th>Module</th>
                                            <th>Élément supprimé</th>
                                            <th>Conservation</th>
                                            <th class="text-end pe-6">Contenu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($archives as $archive)
                                            <tr class="archive-row">
                                                <td class="ps-6 py-4">
                                                    <div class="font-bold text-slate-800">{{ optional($archive->deleted_at)->format('d/m/Y') }}</div>
                                                    <div class="text-xs text-slate-400">{{ optional($archive->deleted_at)->format('H:i:s') }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-bold text-sm">{{ $archive->user->name ?? 'Système' }} {{ $archive->user->last_name ?? '' }}</div>
                                                    <div class="text-xs text-slate-400">{{ $archive->ip_address }}</div>
                                                </td>
                                                <td>
                                                    <span class="archive-badge bg-module">{{ $archive->libelleModule() }}</span>
                                                </td>
                                                <td>
                                                    <div class="text-sm text-slate-700" style="max-width:420px;">{{ $archive->label }}</div>
                                                    @if($archive->batch_id)
                                                        <span class="archive-badge bg-lot mt-1">
                                                            <i class="fa-solid fa-layer-group"></i> Suppression en lot
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php $jours = $archive->joursRestants(); @endphp
                                                    <span class="archive-badge {{ $jours <= 7 ? 'bg-urgent' : 'bg-jours' }}">
                                                        {{ $jours }} jour{{ $jours > 1 ? 's' : '' }}
                                                    </span>
                                                    <div class="text-xs text-slate-400 mt-1">purge le {{ optional($archive->expires_at)->format('d/m/Y') }}</div>
                                                </td>
                                                <td class="text-end pe-6">
                                                    <button class="btn btn-sm btn-outline-secondary rounded-pill"
                                                            onclick="voirArchive({{ $archive->id }})">
                                                        <i class="fa-solid fa-eye me-1"></i>Voir
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i class="fa-solid fa-box-open fa-2x text-slate-300 mb-3 d-block"></i>
                                                    <span class="text-slate-400">Aucune suppression archivée pour cette comptabilité.</span>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-6 border-top border-slate-100 d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="text-slate-500 text-sm">
                                    @if($archives->total())
                                        {{ $archives->firstItem() }}&ndash;{{ $archives->lastItem() }} sur <strong>{{ number_format($archives->total(), 0, ',', ' ') }}</strong> élément(s)
                                    @else
                                        Aucun élément
                                    @endif
                                </div>
                                <div>{{ $archives->links() }}</div>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    {{-- Contenu détaillé d'un élément supprimé --}}
    <div class="modal fade" id="modalArchive" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border:0; border-radius:18px; overflow:hidden;">
                <div class="modal-header border-0" style="background:linear-gradient(135deg,#334155,#64748b); color:#fff;">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-box-archive me-2"></i>Élément supprimé</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="archiveEntete" class="mb-3"></div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:0.78rem;">
                            <thead>
                                <tr>
                                    <th class="text-slate-400 text-uppercase">Champ</th>
                                    <th class="text-slate-400 text-uppercase">Valeur au moment de la suppression</th>
                                </tr>
                            </thead>
                            <tbody id="archiveChamps"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function voirArchive(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalArchive'));
            const entete = document.getElementById('archiveEntete');
            const corps = document.getElementById('archiveChamps');

            entete.innerHTML = '<span class="text-muted">Chargement…</span>';
            corps.innerHTML = '';
            modal.show();

            fetch('{{ url('admin/archives') }}/' + id, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    entete.innerHTML =
                        '<div class="fw-bold text-dark mb-2">' + data.label + '</div>' +
                        '<div class="text-muted" style="font-size:0.8rem;">' +
                        'Module : <strong>' + data.module + '</strong> · ' +
                        'Supprimé par <strong>' + data.auteur + '</strong> le ' + data.supprime_le + '<br>' +
                        'Conservé jusqu\'au <strong>' + data.expire_le + '</strong> (' + data.jours_restants + ' jour(s) restant(s))' +
                        (data.lot ? ' · <span class="badge bg-warning text-dark">Suppression en lot</span>' : '') +
                        '</div>';

                    corps.innerHTML = data.champs.map(function (c) {
                        return '<tr><td class="fw-bold text-slate-600">' + c.champ + '</td><td>' + c.valeur + '</td></tr>';
                    }).join('');
                })
                .catch(function () {
                    entete.innerHTML = '<span class="text-danger">Impossible de charger le contenu de cette archive.</span>';
                });
        }
    </script>
</body>
</html>
