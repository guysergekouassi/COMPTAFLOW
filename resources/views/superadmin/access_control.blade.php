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
                @include('components.header', ['page_title' => 'Contrôle d\'Accès'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Contrôle d'Accès</h5>
                                <p class="text-muted small mb-0">Sécurisez la plateforme en gérant les blocages et les restrictions d'accès.</p>
                            </div>
                        </div>


                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Statistiques rapides (Premium TFT Style) -->
                    <div class="row g-4 mb-8">
                        <!-- Entités -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Entités</h6>
                                        <div class="icon-box bg-label-primary text-primary">
                                            <i class="fa-solid fa-building"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $companies->count() }}</h3>
                                    <div class="small fw-semibold text-primary">
                                        <i class="fa-solid fa-circle-check me-1"></i> Total Entreprises
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Restrictions -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Restrictions</h6>
                                        <div class="icon-box bg-label-danger text-danger">
                                            <i class="fa-solid fa-ban"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $companies->where('is_blocked', true)->count() }}</h3>
                                    <div class="small fw-semibold text-danger">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Entités Bloquées
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Membres -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Membres</h6>
                                        <div class="icon-box bg-label-success text-success">
                                            <i class="fa-solid fa-users"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $users->where('is_blocked', false)->count() }}</h3>
                                    <div class="small fw-semibold text-success">
                                        <i class="fa-solid fa-user-check me-1"></i> Utilisateurs Actifs
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alertes -->
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm card-premium h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Alertes</h6>
                                        <div class="icon-box bg-label-warning text-warning">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-2 fw-extrabold text-dark">{{ $users->where('is_blocked', true)->count() }}</h3>
                                    <div class="small fw-semibold text-warning">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Utilisateurs Bloqués
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Entreprises -->
                    <div class="bg-white rounded-xl shadow-sm border mb-6">
                        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-semibold mb-0">Gestion des Entreprises</h5>
                            <span class="badge bg-label-primary">{{ $companies->count() }} Entités</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th>Entreprise</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Raison</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr class="{{ $company->is_blocked ? 'table-danger' : '' }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-label-secondary me-3">
                                                        <i class="fa-solid fa-building"></i>
                                                    </div>
                                                    <strong>{{ $company->company_name }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                @if(is_null($company->parent_company_id))
                                                    <span class="badge bg-label-primary">
                                                        <i class="fa-solid fa-crown me-1"></i>Siège
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-info">
                                                        <i class="fa-solid fa-code-branch me-1"></i>Sous-entité
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($company->is_blocked)
                                                    <span class="badge bg-danger">Bloqué</span>
                                                @else
                                                    <span class="badge bg-success">Actif</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $company->block_reason ?? '-' }}</small></td>
                                            <td class="text-end">
                                                @if($company->is_blocked)
                                                    <form action="{{ route('superadmin.access.unblock.company', $company->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fa-solid fa-unlock me-1"></i>Débloquer
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#blockC{{ $company->id }}">
                                                        <i class="fa-solid fa-ban me-1"></i>Bloquer
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#deleteC{{ $company->id }}">
                                                    <i class="fa-solid fa-trash me-1"></i>Supprimer
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Modal Bloquer -->
                                        <div class="modal fade" id="blockC{{ $company->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5>Bloquer {{ $company->company_name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('superadmin.access.block.company', $company->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <select class="form-select" name="reason" required>
                                                                <option value="">Raison...</option>
                                                                <option>Abonnement expiré</option>
                                                                <option>Non-paiement</option>
                                                                <option>Violation des CGU</option>
                                                                <option>Sanction</option>
                                                            </select>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-danger">Bloquer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Supprimer -->
                                        <div class="modal fade" id="deleteC{{ $company->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="text-danger">Supprimer l'entreprise</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <div class="mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation fa-3x"></i></div>
                                                        <p>Êtes-vous sûr de vouloir supprimer définitivement l'entreprise <strong>{{ $company->company_name }}</strong> ?</p>
                                                        <p class="text-muted small">Toutes les données rattachées seront impactées. Cette action est irréversible.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <form action="{{ route('superadmin.access.destroy.company', $company->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Supprimer Définitivement</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Utilisateurs -->
                    <div class="bg-white rounded-xl shadow-sm border">
                        <div class="p-4 border-bottom"><h5 class="fw-semibold mb-0">Gestion des Utilisateurs</h5></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th>Utilisateur</th><th>Entreprise</th><th>Statut</th><th>Raison</th><th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr class="{{ $user->is_blocked ? 'table-danger' : '' }}">
                                            <td><strong>{{ $user->name }}</strong></td>
                                            <td>{{ $user->company->company_name ?? 'N/A' }}</td>
                                            <td>
                                                @if($user->is_blocked)
                                                    <span class="badge bg-danger">Bloqué</span>
                                                @else
                                                    <span class="badge bg-success">Actif</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $user->block_reason ?? '-' }}</small></td>
                                            <td class="text-end">
                                                @if($user->is_blocked)
                                                    <form action="{{ route('superadmin.access.unblock.user', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fa-solid fa-unlock me-1"></i>Débloquer
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#blockU{{ $user->id }}">
                                                        <i class="fa-solid fa-ban me-1"></i>Bloquer
                                                    </button>
                                                @endif

                                                <button class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#deleteU{{ $user->id }}">
                                                    <i class="fa-solid fa-trash me-1"></i>Supprimer
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Modal Bloquer -->
                                        <div class="modal fade" id="blockU{{ $user->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5>Bloquer {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('superadmin.access.block.user', $user->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <select class="form-select" name="reason" required>
                                                                <option value="">Raison...</option>
                                                                <option>Violation des CGU</option>
                                                                <option>Activité suspecte</option>
                                                                <option>Sanction</option>
                                                            </select>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-danger">Bloquer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Supprimer -->
                                        <div class="modal fade" id="deleteU{{ $user->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="text-danger">Supprimer l'utilisateur</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <div class="mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation fa-3x"></i></div>
                                                        <p>Êtes-vous sûr de vouloir supprimer définitivement l'utilisateur <strong>{{ $user->name }}</strong> ?</p>
                                                        <p class="text-muted small">Toutes les données rattachées seront perdues. Cette action est irréversible.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <form action="{{ route('superadmin.access.destroy.user', $user->id) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Supprimer Définitivement</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @include('components.footer')
            </div>
        </div>
    </div>
</body>
</html>
