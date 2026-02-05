<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<style>
    .audit-table thead th {
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        border-top: none;
    }
    .audit-row:hover {
        background-color: rgba(59, 130, 246, 0.02) !important;
    }
    .event-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .bg-created { background: #ecfdf5; color: #059669; }
    .bg-updated { background: #eff6ff; color: #2563eb; }
    .bg-deleted { background: #fef2f2; color: #dc2626; }
    .bg-login { background: #f5f3ff; color: #7c3aed; }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Traçabilité & <span class="text-primary">Activités</span>'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-white p-6 rounded-[24px] shadow-sm d-flex align-items-center justify-content-between border border-slate-100">
                                    <div>
                                        <h4 class="font-black mb-1 text-slate-800">Traçabilité & Activités</h4>
                                        <p class="text-slate-500 mb-0">Historique complet des modifications effectuées sur votre dossier.</p>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <button class="btn btn-outline-secondary px-4 py-2 rounded-xl font-bold" data-bs-toggle="collapse" data-bs-target="#filterAudit">
                                            <i class="fa-solid fa-filter me-2"></i>Filtrer
                                        </button>
                                        <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-primary px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20">
                                            <i class="fa-solid fa-download me-2"></i>Exporter
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="collapse mb-6" id="filterAudit">
                            <div class="bg-white p-6 rounded-[24px] border border-slate-100 shadow-sm">
                                <form action="{{ route('admin.audit') }}" method="GET" class="row g-4">
                                    <div class="col-md-4">
                                        <label class="form-label font-bold text-xs text-slate-500 uppercase">Utilisateur</label>
                                        <select name="user_id" class="form-select border-slate-200 rounded-xl py-2.5">
                                            <option value="">Tous les utilisateurs</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} {{ $user->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label font-bold text-xs text-slate-500 uppercase">Événement</label>
                                        <select name="event" class="form-select border-slate-200 rounded-xl py-2.5">
                                            <option value="">Tous les événements</option>
                                            <option value="CREATE">Création</option>
                                            <option value="UPDATE">Modification</option>
                                            <option value="DELETE">Suppression</option>
                                            <option value="LOGIN">Connexion</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-xl font-bold">Appliquer les filtres</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table audit-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-6">Date & Heure</th>
                                            <th>Utilisateur</th>
                                            <th>Événement</th>
                                            <th>Module</th>
                                            <th>Détails de l'action</th>
                                            <th class="text-end pe-6">IP / Terminal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        @forelse($logs as $log)
                                            <tr class="audit-row">
                                                <td class="ps-6 py-4">
                                                    <div class="font-bold text-slate-800">{{ $log->created_at->format('d/m/Y') }}</div>
                                                    <div class="text-xs text-slate-400">{{ $log->created_at->format('H:i:s') }}</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <span class="avatar-initial rounded-circle bg-label-primary shadow-sm">
                                                                {{ substr($log->user->name ?? 'S', 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-sm">{{ $log->user->name ?? 'Système' }}</div>
                                                            <div class="text-xs text-slate-400">{{ $log->user->email ?? '' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $action = strtoupper($log->action);
                                                        $badgeClass = match($action) {
                                                            'CREATE' => 'bg-created',
                                                            'UPDATE' => 'bg-updated',
                                                            'DELETE' => 'bg-deleted',
                                                            'LOGIN' => 'bg-login',
                                                            default => 'bg-slate-100'
                                                        };
                                                        $icon = match($action) {
                                                            'CREATE' => 'fa-plus-circle',
                                                            'UPDATE' => 'fa-edit',
                                                            'DELETE' => 'fa-trash-alt',
                                                            'LOGIN' => 'fa-sign-in-alt',
                                                            default => 'fa-info-circle'
                                                        };
                                                    @endphp
                                                    <span class="event-badge {{ $badgeClass }}">
                                                        <i class="fa-solid {{ $icon }}"></i>
                                                        {{ $action }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-medium text-xs px-2 py-1 rounded bg-slate-50 border border-slate-100 text-slate-600">
                                                        {{ class_basename($log->model_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-sm text-slate-600" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        @if($log->description)
                                                            {{ $log->description }}
                                                        @else
                                                            #{{ $log->model_id }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end pe-6">
                                                    <div class="text-xs font-mono text-slate-400">
                                                        <i class="fa-solid fa-desktop me-1"></i> {{ $log->ip_address ?? '127.0.0.1' }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-20">
                                                    <div class="opacity-20 mb-4">
                                                        <i class="fa-solid fa-history fa-4x text-primary"></i>
                                                    </div>
                                                    <h6 class="text-slate-400">Aucun journal d'activité trouvé pour cette période.</h6>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-6 border-top border-slate-100">
                                {{ $logs->appends(request()->query())->links() }}
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
