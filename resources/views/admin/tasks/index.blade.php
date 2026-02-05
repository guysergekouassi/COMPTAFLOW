<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Assignation de Tâches'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header & Intro -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                            <div>
                                <h4 class="fw-bold py-3 mb-0 text-premium-gradient">
                                    <span class="text-muted fw-light">Opérations /</span> Assignation
                                </h4>
                                <p class="text-muted small mb-0">Pilotez l'activité du cabinet en distribuant les missions.</p>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-label-primary rounded-pill p-2 px-3">
                                    <i class="fa-solid fa-tasks me-1"></i> {{ count($tasks) }} Tâches actives
                                </span>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #198754 !important;">
                                <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row g-4">
                            <!-- Left Column: Create Form -->
                            <div class="col-lg-4">
                                <div class="card glass-card border-0 shadow-lg h-100 position-sticky" style="top: 20px;">
                                    <div class="card-header bg-transparent border-bottom p-4">
                                        <h5 class="card-title mb-0 text-primary fw-bold">
                                            <i class="fa-solid fa-paper-plane me-2"></i>Nouvelle Mission
                                        </h5>
                                    </div>
                                    <div class="card-body p-4">
                                        <form action="{{ route('admin.tasks.store') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            
                                            <!-- Title -->
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control border-0 bg-light" id="taskTitle" name="title" placeholder="Titre" required>
                                                <label for="taskTitle">Intitulé de la tâche</label>
                                            </div>

                                            <!-- Users Selector -->
                                            <div class="mb-3">
                                                <label class="form-label small text-uppercase text-muted fw-bold mb-2">Assigner à</label>
                                                <div class="user-selector-container bg-light rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                                    @foreach($users as $user)
                                                        <div class="form-check custom-option custom-option-basic mb-2">
                                                            <label class="form-check-label custom-option-content d-flex align-items-center justify-content-between p-2 rounded cursor-pointer transition-hover" for="user_{{ $user->id }}">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar avatar-xs me-2">
                                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                                            {{ substr($user->name, 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="d-block fw-semibold text-dark">{{ $user->name }} {{ $user->last_name }}</span>
                                                                        <small class="text-muted">{{ $user->role }}</small>
                                                                    </div>
                                                                </div>
                                                                <input class="form-check-input" type="checkbox" name="assigned_to[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('assigned_to')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Metadata Row -->
                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <div class="form-floating">
                                                        <input type="date" class="form-control border-0 bg-light" id="dueDate" name="due_date">
                                                        <label for="dueDate">Échéance</label>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-floating">
                                                        <select class="form-select border-0 bg-light" id="priority" name="priority" required>
                                                            <option value="low">Basse</option>
                                                            <option value="medium" selected>Moyenne</option>
                                                            <option value="high">Haute</option>
                                                            <option value="urgent">Urgente</option>
                                                        </select>
                                                        <label for="priority">Priorité</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="form-floating mb-3">
                                                <textarea class="form-control border-0 bg-light" placeholder="Instructions" id="description" name="description" style="height: 100px"></textarea>
                                                <label for="description">Instructions détaillées</label>
                                            </div>

                                            <!-- File -->
                                            <div class="mb-4">
                                                <label class="form-label small text-muted">Pièce jointe (facultatif)</label>
                                                <input class="form-control form-control-sm" type="file" name="file">
                                            </div>

                                            <!-- Submit -->
                                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm hover-elevate">
                                                <i class="fa-solid fa-paper-plane me-2"></i> Envoyer la tâche
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: List -->
                            <div class="col-lg-8">
                                <div class="row g-3">
                                    @forelse($tasks as $task)
                                        <div class="col-md-6 mb-2">
                                            <div class="card h-100 border-0 shadow-sm task-card hover-card-effect position-relative overflow-hidden">
                                                <!-- Priority Indicator Strip -->
                                                <div class="position-absolute start-0 top-0 bottom-0" 
                                                     style="width: 4px; background-color: {{ match($task->priority) { 'urgent'=>'#ef4444', 'high'=>'#f59e0b', 'medium'=>'#3b82f6', default=>'#10b981' } }};">
                                                </div>
                                                
                                                <div class="card-body p-4 ps-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div class="badge rounded-pill bg-label-{{ match($task->priority) { 'urgent'=>'danger', 'high'=>'warning', 'medium'=>'primary', default=>'success' } }} px-3">
                                                            {{ ucfirst($task->priority) }}
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette tâche ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="dropdown-item text-danger">Supprimer</button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    
                                                    <h6 class="card-title fw-bold text-dark mb-2">{{ $task->title }}</h6>
                                                    <p class="card-text text-muted small mb-3 text-truncate-2" style="min-height: 40px;">
                                                        {{ $task->description ?? 'Aucune instruction supplémentaire.' }}
                                                    </p>

                                                    <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-group me-2">
                                                                @foreach($task->assignees as $assignee)
                                                                    <div class="avatar avatar-xs" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $assignee->name }}">
                                                                        <span class="avatar-initial rounded-circle bg-primary text-white text-xs">
                                                                            {{ substr($assignee->name, 0, 1) }}
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <small class="text-muted ms-1" style="font-size: 0.75rem;">
                                                                <i class="fa-regular fa-calendar me-1"></i>
                                                                {{ $task->due_date ? date('d/m', strtotime($task->due_date)) : '--' }}
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($task->file_path)
                                                                <a href="{{ Storage::url($task->file_path) }}" target="_blank" class="btn btn-xs btn-light text-secondary" data-bs-toggle="tooltip" title="Voir pièce jointe">
                                                                    <i class="fa-solid fa-paperclip"></i>
                                                                </a>
                                                            @endif
                                                            <!-- Status Check -->
                                                            @if($task->assignees->contains(fn($u) => $u->pivot->status === 'completed'))
                                                                 <i class="fa-solid fa-circle-check text-success fs-5" data-bs-toggle="tooltip" title="Terminé"></i>
                                                            @else
                                                                 <i class="fa-regular fa-clock text-warning fs-5" data-bs-toggle="tooltip" title="En attente"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 py-5 text-center">
                                            <div class="empty-state-container p-5 bg-white rounded shadow-sm">
                                                <div class="avatar avatar-xl bg-light rounded-circle mb-3 mx-auto">
                                                    <i class="fa-solid fa-clipboard-check text-muted fs-2"></i>
                                                </div>
                                                <h5 class="text-dark fw-bold">Aucune tâche assignée</h5>
                                                <p class="text-muted">Utilisez le formulaire à gauche pour distribuer du travail.</p>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>

    <style>
        .hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
        .hover-card-effect { transition: all 0.3s ease; }
        .hover-card-effect:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; }
        .custom-option:hover { background-color: #f3f4f6; }
        .custom-option-content { transition: background-color 0.2s; }
        .user-selector-container::-webkit-scrollbar { width: 5px; }
        .user-selector-container::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    
    <script>
        // Tooltip init
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });
    </script>
        </div>
    </div>
</body>
</html>
