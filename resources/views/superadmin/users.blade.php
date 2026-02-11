@include('components.head')

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }
    .card-premium {
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }
    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Administration des Utilisateurs'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Administration des Utilisateurs</h5>
                                <p class="text-muted small mb-0">Gérez les accès et les rôles de tous les collaborateurs de la plateforme.</p>
                            </div>
                            <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fa-solid fa-plus me-2"></i> Nouvel Utilisateur
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

                    <!-- Statistiques rapides (Premium TFT Style) -->
                    <div class="row g-4 mb-8">
                        <!-- Total Utilisateurs -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Utilisateurs</h6>
                                        <div class="icon-box bg-label-primary text-primary">
                                            <i class="fa-solid fa-users"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $totalUsers }}</h3>
                                    <div class="small fw-semibold text-primary">
                                        <i class="fa-solid fa-users-cog me-1"></i> Total Inscrits
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Administrateurs -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Administrateurs</h6>
                                        <div class="icon-box bg-label-success text-success">
                                            <i class="fa-solid fa-user-shield"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $totalAdmins }}</h3>
                                    <div class="small fw-semibold text-success">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Gestionnaires
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comptables -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Comptables</h6>
                                        <div class="icon-box bg-label-info text-info">
                                            <i class="fa-solid fa-calculator"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $totalComptables }}</h3>
                                    <div class="small fw-semibold text-info">
                                        <i class="fa-solid fa-keyboard me-1"></i> Opérateurs Saisie
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Utilisateurs Actifs -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Actifs</h6>
                                        <div class="icon-box bg-label-warning text-warning">
                                            <i class="fa-solid fa-user-check"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $totalActive }}</h3>
                                    <div class="small fw-semibold text-warning">
                                        <i class="fa-solid fa-bolt me-1"></i> Comptes Opérationnels
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des utilisateurs -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-bottom">
                            <h5 class="fw-semibold mb-0">Liste des utilisateurs</h5>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="fw-semibold">Nom</th>
                                        <th class="fw-semibold">Email</th>
                                        <th class="fw-semibold">Entreprise</th>
                                        <th class="fw-semibold">Rôle</th>
                                        <th class="fw-semibold">Créé le</th>
                                        <th class="fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2">
                                                        {{ $user->initiales }}
                                                    </div>
                                                    <span class="fw-medium">{{ $user->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                <div>
                                                    <div class="fw-medium text-slate-700">{{ $user->company->company_name ?? 'N/A' }}</div>
                                                    @if($user->company && $user->company->parent)
                                                        <div class="text-[10px] font-bold text-blue-500 uppercase tracking-tighter mt-1">
                                                            <i class="fa-solid fa-link me-1"></i>Filiale de {{ $user->company->parent->company_name }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($user->role === 'admin')
                                                    <span class="badge bg-success">Admin</span>
                                                @elseif($user->role === 'comptable')
                                                    <span class="badge bg-primary">Comptable</span>
                                                @elseif($user->role === 'super_admin')
                                                    <span class="badge bg-danger">Super Admin</span>
                                                @else
                                                    <span class="badge bg-secondary">Utilisateur</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                            <td class="text-end">
                                                @if($user->role !== 'super_admin')
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-outline-primary" title="Modifier">
                                                            <i class="fa-solid fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('superadmin.users.destroy', $user->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Protégé</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-users fa-2x mb-2"></i>
                                                <p class="mb-0">Aucun utilisateur trouvé</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($users->hasPages())
                            <div class="p-4 border-top">
                                {{ $users->links() }}
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
