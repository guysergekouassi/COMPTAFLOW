<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Switch Entreprise / Utilisateur'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    


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

                    <!-- Statut actuel -->
                    @if($currentSwitchedCompany || $currentSwitchedUser)
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="fa-solid fa-info-circle fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <strong>Mode Switch Actif</strong>
                                <p class="mb-0">
                                    @if($currentSwitchedUser)
                                        @php $user = \App\Models\User::find($currentSwitchedUser); @endphp
                                        Vous êtes actuellement connecté en tant que : <strong>{{ $user->name ?? 'N/A' }}</strong>
                                    @endif
                                    @if($currentSwitchedCompany)
                                        @php $company = \App\Models\Company::find($currentSwitchedCompany); @endphp
                                        (Entreprise : <strong>{{ $company->company_name ?? 'N/A' }}</strong>)
                                    @endif
                                </p>
                            </div>
                            <form action="{{ route('superadmin.switch.return') }}" method="POST" class="ms-3">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa-solid fa-arrow-left me-2"></i>Retour Super Admin
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Liste des entreprises -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-bottom">
                            <h5 class="fw-semibold mb-0">Liste des Entreprises</h5>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="fw-semibold">Entreprise</th>
                                        <th class="fw-semibold">Utilisateurs</th>
                                        <th class="fw-semibold">Statut</th>
                                        <th class="fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($companies as $company)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2">
                                                        {{ strtoupper(substr($company->company_name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <span class="fw-medium">{{ $company->company_name }}</span>
                                                        @if($company->is_blocked)
                                                            <span class="badge bg-danger ms-2">Bloqué</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $company->users->count() }} utilisateurs</span>
                                            </td>
                                            <td>
                                                @if($company->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('superadmin.switch.company', $company->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary" 
                                                                @if($company->is_blocked) disabled title="Entreprise bloquée" @endif>
                                                            <i class="fa-solid fa-sign-in-alt me-1"></i>Accéder
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-outline-secondary" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#users-{{ $company->id }}">
                                                        <i class="fa-solid fa-users"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Ligne dépliable pour les utilisateurs -->
                                        <tr class="collapse" id="users-{{ $company->id }}">
                                            <td colspan="4" class="bg-light">
                                                <div class="p-3">
                                                    <h6 class="fw-semibold mb-3">Utilisateurs de {{ $company->company_name }}</h6>
                                                    <div class="row g-2">
                                                        @forelse($company->users as $user)
                                                            <div class="col-md-6">
                                                                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                                                    <div>
                                                                        <strong>{{ $user->name }}</strong>
                                                                        <span class="badge bg-{{ $user->role === 'admin' ? 'success' : ($user->role === 'comptable' ? 'primary' : 'secondary') }} ms-2">
                                                                            {{ ucfirst($user->role) }}
                                                                        </span>
                                                                        @if($user->is_blocked)
                                                                            <span class="badge bg-danger ms-1">Bloqué</span>
                                                                        @endif
                                                                    </div>
                                                                    <form action="{{ route('superadmin.switch.user', $user->id) }}" method="POST">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-sm btn-outline-primary"
                                                                                @if($user->is_blocked) disabled title="Utilisateur bloqué" @endif>
                                                                            <i class="fa-solid fa-user-check me-1"></i>Se connecter
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="col-12">
                                                                <p class="text-muted mb-0">Aucun utilisateur dans cette entreprise</p>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-building fa-2x mb-2"></i>
                                                <p class="mb-0">Aucune entreprise trouvée</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
