<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Assignation de Tâches'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Assignation de Tâches</h5>
                                <p class="text-muted small mb-0">Déléguez et suivez l'avancement des travaux du cabinet</p>
                            </div>
                            <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                <i class="fa-solid fa-plus me-2"></i> Nouvelle Tâche
                            </button>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row">
                            @forelse($tasks as $task)
                                <div class="col-md-4 mb-4">
                                    <div class="glass-card p-4 h-100 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="badge {{ 
                                                $task->priority == 'urgent' ? 'bg-danger' : 
                                                ($task->priority == 'high' ? 'bg-warning' : 'bg-info') 
                                            }} rounded-pill small">
                                                {{ strtoupper($task->priority) }}
                                            </span>
                                            <small class="text-muted">Échéance: {{ $task->due_date ? date('d/m/Y', strtotime($task->due_date)) : 'N/A' }}</small>
                                        </div>
                                        <h6 class="fw-bold mb-2">{{ $task->title }}</h6>
                                        <p class="text-muted small mb-4 flex-grow-1 text-truncate" style="max-height: 40px;">
                                            {{ $task->description ?? 'Aucune description' }}
                                        </p>
                                        <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary small">{{ substr($task->assignee->name ?? 'U', 0, 1) }}</span>
                                                </div>
                                                <small class="fw-medium">{{ $task->assignee->name ?? 'N/A' }}</small>
                                            </div>
                                            <span class="badge-premium {{ $task->status == 'completed' ? 'badge-premium-success' : 'badge-premium-warning' }}">
                                                {{ str_replace('_', ' ', $task->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 glass-card">
                                    <i class="fa-solid fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <h5>Aucune tâche assignée</h5>
                                    <p class="text-muted">Commencez par déléguer une tâche à votre équipe.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Add Task Modal -->
                    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0">
                                <form action="{{ route('admin.tasks.store') }}" method="POST">
                                    @csrf
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title fw-bold">Assigner une nouvelle tâche</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Titre de la tâche</label>
                                            <input type="text" name="title" class="form-control" placeholder="Ex: Révision du dossier Client A" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Assigner à</label>
                                            <select name="assigned_to" class="form-select" required>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }} ({{ $user->role }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Date d'échéance</label>
                                                <input type="date" name="due_date" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Priorité</label>
                                                <select name="priority" class="form-select" required>
                                                    <option value="low">Bas</option>
                                                    <option value="medium" selected>Moyen</option>
                                                    <option value="high">Haut</option>
                                                    <option value="urgent">Urgent</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Description (Optionnel)</label>
                                            <textarea name="description" class="form-control" rows="3" placeholder="Détails de la mission..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-premium">Confirmer l'assignation</button>
                                    </div>
                                </form>
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
