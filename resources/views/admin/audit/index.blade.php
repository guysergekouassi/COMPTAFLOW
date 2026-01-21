<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Archives & Audit'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Archives & Audit</h5>
                                <p class="text-muted small mb-0">Traçabilité complète des actions effectuées sur la plateforme</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-download me-2"></i> Exporter
                                </button>
                                <button class="btn btn-premium" data-bs-toggle="collapse" data-bs-target="#filterAudit">
                                    <i class="fa-solid fa-filter me-2"></i> Filtrer
                                </button>
                            </div>
                        </div>

                        <div class="collapse mb-4" id="filterAudit">
                            <div class="glass-card p-4">
                                <form action="{{ route('admin.audit') }}" method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Utilisateur</label>
                                        <select name="user_id" class="form-select">
                                            <option value="">Tous les utilisateurs</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Événement</label>
                                        <select name="event" class="form-select">
                                            <option value="">Tous les événements</option>
                                            <option value="created">Création</option>
                                            <option value="updated">Modification</option>
                                            <option value="deleted">Suppression</option>
                                            <option value="login">Connexion</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Appliquer les filtres</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="glass-card p-4">
                            <div class="timeline-premium">
                                @forelse($logs as $log)
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="glass-card p-3 mb-2" style="background: rgba(255,255,255,0.5)">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-card-initials me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold small">{{ $log->user->name ?? 'Système' }}</span>
                                                        <span class="text-muted small mx-1">•</span>
                                                        <span class="badge-premium {{ 
                                                            $log->event == 'created' ? 'badge-premium-success' : 
                                                            ($log->event == 'deleted' ? 'badge-premium-danger' : 'badge-premium-info') 
                                                        }}" style="padding: 2px 8px; font-size: 0.65rem;">
                                                            {{ strtoupper($log->event) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="small fw-bold">{{ $log->created_at->format('H:i:s') }}</div>
                                                    <small class="text-muted">{{ $log->created_at->format('d M Y') }}</small>
                                                </div>
                                            </div>
                                            <div class="p-2 rounded bg-light border-start border-primary border-4">
                                                <p class="mb-1 small fw-medium text-dark">
                                                    Action sur <span class="text-primary">{{ class_basename($log->auditable_type) }}</span> #{{ $log->auditable_id }}
                                                </p>
                                                <div class="text-muted small text-truncate" style="max-width: 100%;">
                                                    {{ json_encode($log->properties) }}
                                                </div>
                                            </div>
                                            <div class="mt-2 d-flex justify-content-end">
                                                <small class="text-muted"><i class="fa-solid fa-desktop me-1"></i> {{ $log->ip_address ?? '127.0.0.1' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5">
                                        <i class="fa-solid fa-history fa-3x text-premium-gradient mb-3 opacity-25"></i>
                                        <p class="text-muted">Aucun journal d'activité trouvé.</p>
                                    </div>
                                @endforelse
                            </div>
                            <div class="p-3 border-top">
                                {{ $logs->links() }}
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
