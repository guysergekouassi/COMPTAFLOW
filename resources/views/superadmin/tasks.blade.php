@include('components.head')

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }
    .priority-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gestion des Tâches Administratives'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gestion des Tâches Administratives</h5>
                                <p class="text-muted small mb-0">Créez, assignez et suivez les tâches administratives de la plateforme.</p>
                            </div>
                            <a href="{{ route('superadmin.tasks.create') }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fa-solid fa-plus me-2"></i> Nouvelle Tâche
                            </a>
                        </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Statistiques rapides -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-tasks text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Total Tâches</p>
                                        <h4 class="fw-bold mb-0">{{ $tasks->total() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-clock text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">En Attente</p>
                                        <h4 class="fw-bold mb-0">{{ $tasks->where('status', 'pending')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-spinner text-purple-600 fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">En Cours</p>
                                        <h4 class="fw-bold mb-0">{{ $tasks->where('status', 'in_progress')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-check-circle text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Complétées</p>
                                        <h4 class="fw-bold mb-0">{{ $tasks->where('status', 'completed')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                        <form method="GET" action="{{ route('superadmin.tasks.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En Attente</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Cours</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complétée</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Priorité</label>
                                <select name="priority" class="form-select">
                                    <option value="">Toutes les priorités</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Basse</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Haute</option>
                                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgente</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Assigné à</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">Tous les utilisateurs</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fa-solid fa-filter me-1"></i> Filtrer
                                </button>
                                <a href="{{ route('superadmin.tasks.index') }}" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-times"></i> Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des tâches -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-bottom">
                            <h5 class="fw-semibold mb-0">Liste des tâches</h5>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="fw-semibold">Titre</th>
                                        <th class="fw-semibold">Assigné à</th>
                                        <th class="fw-semibold">Entreprise</th>
                                        <th class="fw-semibold">Priorité</th>
                                        <th class="fw-semibold">Statut</th>
                                        <th class="fw-semibold">Date d'échéance</th>
                                        <th class="fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tasks as $task)
                                        <tr>
                                            <td>
                                                <div>
                                                    <span class="fw-medium">{{ $task->title }}</span>
                                                    @if($task->description)
                                                        <p class="text-muted small mb-0">{{ Str::limit($task->description, 50) }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($task->assignedTo)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2">
                                                            {{ $task->assignedTo->initiales }}
                                                        </div>
                                                        <span>{{ $task->assignedTo->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Non assignée</span>
                                                @endif
                                            </td>
                                            <td>{{ $task->company->company_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($task->priority === 'urgent')
                                                    <span class="priority-badge bg-danger text-white">
                                                        <i class="fa-solid fa-exclamation-triangle"></i> Urgente
                                                    </span>
                                                @elseif($task->priority === 'high')
                                                    <span class="priority-badge bg-warning text-dark">
                                                        <i class="fa-solid fa-arrow-up"></i> Haute
                                                    </span>
                                                @elseif($task->priority === 'medium')
                                                    <span class="priority-badge bg-info text-white">
                                                        <i class="fa-solid fa-minus"></i> Moyenne
                                                    </span>
                                                @else
                                                    <span class="priority-badge bg-secondary text-white">
                                                        <i class="fa-solid fa-arrow-down"></i> Basse
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->status === 'completed')
                                                    <span class="status-badge bg-success text-white">
                                                        <i class="fa-solid fa-check"></i> Complétée
                                                    </span>
                                                @elseif($task->status === 'in_progress')
                                                    <span class="status-badge bg-primary text-white">
                                                        <i class="fa-solid fa-spinner"></i> En Cours
                                                    </span>
                                                @elseif($task->status === 'cancelled')
                                                    <span class="status-badge bg-dark text-white">
                                                        <i class="fa-solid fa-ban"></i> Annulée
                                                    </span>
                                                @else
                                                    <span class="status-badge bg-warning text-dark">
                                                        <i class="fa-solid fa-clock"></i> En Attente
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->due_date)
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                                    @if(\Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed')
                                                        <span class="badge bg-danger ms-1">En retard</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('superadmin.tasks.show', $task->id) }}" class="btn btn-outline-info" title="Voir">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('superadmin.tasks.edit', $task->id) }}" class="btn btn-outline-primary" title="Modifier">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('superadmin.tasks.destroy', $task->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-tasks fa-2x mb-2"></i>
                                                <p class="mb-0">Aucune tâche trouvée</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($tasks->hasPages())
                            <div class="p-4 border-top">
                                {{ $tasks->links() }}
                            </div>
                        @endif
                    </div>

                </div>

                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
