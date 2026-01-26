<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Tâches Quotidiennes'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="mb-4">
                            <h5 class="mb-1 text-premium-gradient">Tâches Quotidiennes</h5>
                            <p class="text-muted small mb-0">Liste des missions qui vous ont été assignées.</p>
                        </div>


                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            @forelse($tasks as $task)
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all position-relative overflow-hidden group-hover-parent">
                                    <!-- Priority Indicator Strip -->
                                    <div class="position-absolute top-0 bottom-0 start-0" 
                                         style="width: 4px; background-color: {{ 
                                            $task->priority == 'urgent' ? '#ef4444' : 
                                            ($task->priority == 'high' ? '#f59e0b' : 
                                            ($task->priority == 'medium' ? '#3b82f6' : '#10b981')) 
                                         }};">
                                    </div>

                                    <div class="card-body p-4 d-flex flex-column h-100 ps-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                 @php
                                                    $creatorInitial = substr($task->creator->name ?? '?', 0, 1);
                                                    $bgClass = match(strtoupper($creatorInitial)) {
                                                        'A','E','I','O','U' => 'bg-label-danger',
                                                        'B','C','D','F','G' => 'bg-label-primary',
                                                        'H','J','K','L','M' => 'bg-label-success',
                                                        default => 'bg-label-info'
                                                    };
                                                @endphp
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle {{ $bgClass }} fw-bold">
                                                        {{ $creatorInitial }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-dark small">{{ $task->creator->name ?? 'Inconnu' }}</span>
                                                    <span class="text-muted extra-small" style="font-size: 0.75rem;">
                                                        {{ $task->created_at->format('d/m H:i') }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            @php
                                                $priorityBadge = match($task->priority) {
                                                    'urgent' => 'bg-danger-subtle text-danger',
                                                    'high' => 'bg-warning-subtle text-warning',
                                                    'medium' => 'bg-primary-subtle text-primary',
                                                    'low' => 'bg-success-subtle text-success',
                                                    default => 'bg-secondary-subtle text-secondary'
                                                };
                                                $priorityIcon = match($task->priority) {
                                                    'urgent' => 'fa-fire',
                                                    'high' => 'fa-exclamation-circle',
                                                    'medium' => 'fa-circle-notch',
                                                    'low' => 'fa-arrow-down',
                                                    default => 'fa-circle'
                                                };
                                            @endphp
                                            <span class="badge {{ $priorityBadge }} rounded-pill d-flex align-items-center gap-1">
                                                <i class="fa-solid {{ $priorityIcon }}" style="font-size: 0.7rem;"></i>
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </div>

                                        <h5 class="card-title fw-bold text-dark mb-2">{{ $task->title }}</h5>
                                        <p class="card-text text-muted small flex-grow-1 text-clamp-3 mb-4">
                                            {{ $task->description ?? 'Aucune description disponible.' }}
                                        </p>

                                        @if($task->file_path)
                                        <div class="mb-4">
                                            <a href="{{ Storage::url($task->file_path) }}" target="_blank" class="d-flex align-items-center p-2 rounded border bg-light text-decoration-none group-hover-bg-white transition-all">
                                                <div class="p-2 bg-white rounded shadow-sm me-3 text-primary">
                                                    <i class="fa-solid fa-file-contract"></i>
                                                </div>
                                                <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                                    <span class="fw-medium text-dark text-truncate small">Pièce jointe</span>
                                                    <span class="text-muted extra-small">Cliquer pour voir</span>
                                                </div>
                                                <i class="fa-solid fa-external-link-alt text-muted small me-2"></i>
                                            </a>
                                        </div>
                                        @endif

                                        <div class="mt-auto border-top pt-3 d-flex justify-content-between align-items-center">
                                            <div class="d-flex flex-column">
                                                <span class="text-uppercase extra-small fw-bold text-muted mb-1">Échéance</span>
                                                <div class="d-flex align-items-center {{ $task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() ? 'text-danger fw-bold' : 'text-dark fw-medium' }}">
                                                    <i class="fa-regular fa-calendar me-2"></i>
                                                    {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'N/A' }}
                                                </div>
                                            </div>

                                            @php
                                                // Status logic
                                                $myPivot = $task->assignees->where('id', auth()->id())->first()->pivot ?? null;
                                                $status = $myPivot ? $myPivot->status : 'pending';
                                            @endphp

                                            @if($status === 'completed')
                                                <span class="btn btn-success btn-sm pe-none rounded-pill px-3">
                                                    <i class="fa-solid fa-check me-1"></i> Terminé
                                                </span>
                                            @else
                                                <form action="{{ route('admin.tasks.complete', $task->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3 hover-scale">
                                                        <i class="fa-regular fa-square-check me-1"></i> Terminer
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="card border-0 shadow-none bg-transparent">
                                    <div class="card-body text-center py-5">
                                        <div class="avatar avatar-xl bg-label-success rounded-circle mb-4 mx-auto animate__animated animate__bounceIn">
                                            <i class="fa-solid fa-mug-hot fs-1"></i>
                                        </div>
                                        <h4 class="fw-bold text-dark">Tout est calme !</h4>
                                        <p class="text-muted">Vous n'avez aucune tâche en attente pour le moment.</p>
                                    </div>
                                </div>
                            </div>
                            @endforelse
                        </div>

                        <style>
                            .hover-shadow:hover {
                                transform: translateY(-3px);
                                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
                            }
                            .transition-all {
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                            }
                            .text-clamp-3 {
                                display: -webkit-box;
                                -webkit-line-clamp: 3;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                            }
                            .bg-danger-subtle { background-color: #fef2f2 !important; }
                            .bg-warning-subtle { background-color: #fffbeb !important; }
                            .bg-primary-subtle { background-color: #eff6ff !important; }
                            .bg-success-subtle { background-color: #f0fdf4 !important; }
                            .bg-secondary-subtle { background-color: #f3f4f6 !important; }
                            
                            .hover-scale:hover {
                                transform: scale(1.05);
                            }
                            .extra-small {
                                font-size: 0.75rem;
                            }
                        </style>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
