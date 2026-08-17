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
                @include('components.header', ['page_title' => 'Gouvernance / Switch Entreprise'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Switch Entreprise</h5>
                                <p class="text-muted small mb-0">Basculez entre les contextes entreprises ou incarnez un collaborateur.</p>
                            </div>
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

                    <!-- Filtres -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
                        <form method="GET" action="{{ route('superadmin.switch') }}">
                            <div class="row g-3 align-items-end">

                                {{-- Recherche --}}
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small text-muted mb-1">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>Recherche
                                    </label>
                                    <input type="text" name="search"
                                           class="form-control form-control-sm"
                                           placeholder="Nom ou code entreprise…"
                                           value="{{ request('search') }}">
                                </div>

                                {{-- Entreprise --}}
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small text-muted mb-1">
                                        <i class="fa-solid fa-building me-1"></i>Entreprise
                                    </label>
                                    <select name="company_id" class="form-select form-select-sm">
                                        <option value="">Toutes les entreprises</option>
                                        @foreach($allCompanies as $item)
                                            <option value="{{ $item->id }}" {{ request('company_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Boutons --}}
                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100" title="Filtrer">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                    @if(request()->hasAny(['search', 'company_id']))
                                        <a href="{{ route('superadmin.switch') }}" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                                            <i class="fa-solid fa-xmark"></i>
                                        </a>
                                    @endif
                                </div>

                            </div>

                            @if(request()->hasAny(['search', 'company_id']))
                                <div class="mt-2 pt-2 border-top d-flex align-items-center gap-2">
                                    <span class="badge bg-primary rounded-pill">{{ $companies->total() }} résultat(s)</span>
                                    <span class="text-muted small">filtre(s) actif(s)</span>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Liste des entreprises -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-semibold mb-0">Liste des Entreprises</h5>
                            <span class="text-muted small">{{ $companies->total() }} entreprise(s) · classées par ordre alphabétique</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="fw-semibold">Entreprise</th>
                                        <th class="fw-semibold">Type</th>
                                        <th class="fw-semibold">Utilisateurs</th>
                                        <th class="fw-semibold">Date de création</th>
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
                                                @if(is_null($company->parent_company_id))
                                                    <span class="badge bg-label-primary">Siège</span>
                                                @else
                                                    <span class="badge bg-label-info">Sous-entité</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $company->users->count() }} utilisateurs</span>
                                            </td>
                                            <td>
                                                @if($company->created_at)
                                                    <span class="text-muted">{{ $company->created_at->format('d/m/y') }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
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
                                            <td colspan="6" class="bg-light">
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
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-building fa-2x mb-2"></i>
                                                <p class="mb-0">
                                                    @if(request()->hasAny(['search', 'company_id']))
                                                        Aucune entreprise ne correspond à ce filtre
                                                    @else
                                                        Aucune entreprise trouvée
                                                    @endif
                                                </p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($companies->hasPages())
                            <div class="p-4 border-top">
                                {{ $companies->links() }}
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
