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
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gouvernance / Administration des Utilisateurs'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Administration des Utilisateurs</h5>
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

                    <!-- Statistiques rapides -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-users text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Total Utilisateurs</p>
                                        <h4 class="fw-bold mb-0">{{ $users->total() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-user-shield text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Administrateurs</p>
                                        <h4 class="fw-bold mb-0">{{ $users->where('role', 'admin')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-calculator text-purple-600 fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Comptables</p>
                                        <h4 class="fw-bold mb-0">{{ $users->where('role', 'comptable')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-user text-orange-600 fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Utilisateurs</p>
                                        <h4 class="fw-bold mb-0">{{ $users->where('role', 'user')->count() }}</h4>
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
                                            <td>{{ $user->company->company_name ?? 'N/A' }}</td>
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
