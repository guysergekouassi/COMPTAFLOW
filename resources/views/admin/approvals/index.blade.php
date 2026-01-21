<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => "Centre d'Approbation"])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Centre d'Approbation</h5>
                                <p class="text-muted small mb-0">Validez ou rejetez les demandes en attente de traitement</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="nav-align-top mb-4">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pending" aria-controls="navs-pending" aria-selected="true">
                                                En attente ({{ $pendingApprovals->count() }})
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-history" aria-controls="navs-history" aria-selected="false">
                                                Historique
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content border-0 bg-transparent p-0 pt-4">
                                        <div class="tab-pane fade show active" id="navs-pending" role="tabpanel">
                                            @if($pendingApprovals->isNotEmpty())
                                                <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-label-warning rounded-3 border border-warning">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md bg-warning text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                            <i class="fa-solid fa-bell fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark">Actions requises</h6>
                                                            <p class="mb-0 small text-muted">Vous avez {{ $pendingApprovals->count() }} demande(s) en attente de traitement.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="row g-4">
                                                @forelse($pendingApprovals as $approval)
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="glass-card h-100 d-flex flex-column">
                                                            <div class="p-4 border-bottom">
                                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                                    <span class="badge-premium badge-premium-info">
                                                                        {{ strtoupper(str_replace('_', ' ', $approval->type)) }}
                                                                    </span>
                                                                    <small class="text-muted bg-light px-2 py-1 rounded">
                                                                        <i class="fa-regular fa-clock me-1"></i> {{ $approval->created_at->diffForHumans() }}
                                                                    </small>
                                                                </div>
                                                                <div class="d-flex align-items-center mb-0">
                                                                    <div class="user-card-initials me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                                                        {{ substr($approval->requester->name ?? 'S', 0, 1) }}
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-bold text-dark">{{ $approval->requester->name ?? 'Système' }}</div>
                                                                        <small class="text-muted">Demandeur</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="p-4 flex-grow-1 bg-light bg-opacity-50">
                                                                @if($approval->data)
                                                                    <ul class="list-unstyled mb-0 small">
                                                                        @foreach(array_slice($approval->data, 0, 4) as $key => $value)
                                                                            <li class="mb-2 d-flex justify-content-between border-bottom pb-1 border-light">
                                                                                <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                                                                <span class="fw-medium text-end text-truncate ms-2" style="max-width: 150px;">
                                                                                    {{ is_array($value) ? 'Données complexes' : $value }}
                                                                                </span>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                    @if(count($approval->data) > 4)
                                                                        <div class="text-center mt-2">
                                                                            <small class="text-primary cursor-pointer" data-bs-toggle="collapse" data-bs-target="#more-{{$approval->id}}">
                                                                                Voir plus <i class="fa-solid fa-chevron-down ms-1"></i>
                                                                            </small>
                                                                            <div class="collapse mt-2 text-start" id="more-{{$approval->id}}">
                                                                                 <ul class="list-unstyled mb-0 small">
                                                                                    @foreach(array_slice($approval->data, 4) as $key => $value)
                                                                                        <li class="mb-2 d-flex justify-content-between border-bottom pb-1 border-light">
                                                                                            <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                                                                            <span class="fw-medium text-end text-truncate ms-2" style="max-width: 150px;">
                                                                                                {{ is_array($value) ? 'Detail' : $value }}
                                                                                            </span>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <p class="text-muted small mb-0 fst-italic text-center">Aucun détail supplémentaire disponible.</p>
                                                                @endif
                                                            </div>

                                                            <div class="p-3 border-top d-flex gap-2 bg-white rounded-bottom">
                                                                <form action="{{ route('admin.approvals.approve', $approval->id) }}" method="POST" class="w-100">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success w-100 shadow-sm">
                                                                        <i class="fa-solid fa-check me-2"></i> Valider
                                                                    </button>
                                                                </form>
                                                                <button class="btn btn-outline-danger w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $approval->id }}">
                                                                    <i class="fa-solid fa-xmark me-2"></i> Rejeter
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Reject Modal -->
                                                        <div class="modal fade" id="rejectModal{{ $approval->id }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content border-0 shadow-lg">
                                                                    <form action="{{ route('admin.approvals.reject', $approval->id) }}" method="POST">
                                                                        @csrf
                                                                        <div class="modal-header border-bottom-0 pb-0">
                                                                            <h5 class="modal-title fw-bold text-danger">Rejeter la demande</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="text-center mb-4">
                                                                                <div class="avatar avatar-xl bg-label-danger rounded-circle mx-auto mb-3">
                                                                                    <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                                                                                </div>
                                                                                <p class="text-muted">Êtes-vous sûr de vouloir rejeter cette demande ? Cette action est irréversible.</p>
                                                                            </div>
                                                                            <div class="mb-3 text-start">
                                                                                <label class="form-label fw-bold small text-uppercase">Motif du rejet (Obligatoire)</label>
                                                                                <textarea name="comment" class="form-control bg-light" rows="3" required placeholder="Expliquez pourquoi cette demande est rejetée..."></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer border-top-0 pt-0">
                                                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                            <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 py-5">
                                                        <div class="glass-card p-5 text-center opacity-75">
                                                            <div class="mb-4">
                                                                <i class="fa-solid fa-clipboard-check fa-4x text-success opacity-50"></i>
                                                            </div>
                                                            <h4 class="fw-bold text-dark">Tout est en ordre !</h4>
                                                            <p class="text-muted mb-0">Aucune demande d'approbation en attente pour le moment.</p>
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-history" role="tabpanel">
                                            <div class="glass-card overflow-hidden">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th class="ps-4">Demande</th>
                                                                <th>Par</th>
                                                                <th>Traité par</th>
                                                                <th>Date</th>
                                                                <th>Statut</th>
                                                                <th class="pe-4">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($history as $record)
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <span class="fw-bold">{{ strtoupper($record->type) }}</span>
                                                                    </td>
                                                                    <td>{{ $record->requester->name ?? 'N/A' }}</td>
                                                                    <td>{{ $record->handler->name ?? 'N/A' }}</td>
                                                                    <td>{{ $record->updated_at->format('d/m/Y H:i') }}</td>
                                                                    <td>
                                                                        <span class="badge-premium {{ $record->status == 'approved' ? 'badge-premium-success' : 'badge-premium-danger' }}">
                                                                            {{ $record->status == 'approved' ? 'Approuvé' : 'Rejeté' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="pe-4">
                                                                        <button class="btn btn-sm btn-icon btn-label-secondary" title="Détails" data-bs-toggle="tooltip">
                                                                            <i class="fa-solid fa-circle-info"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
