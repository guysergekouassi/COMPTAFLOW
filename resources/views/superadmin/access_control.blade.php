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
                @include('components.header', ['page_title' => 'Opérations / Contrôle d\'Accès'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Opérations / Contrôle d'Accès</h5>
                                <p class="text-muted small mb-0">Sécurisez la plateforme en gérant les blocages et les restrictions d'accès.</p>
                            </div>
                        </div>


                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Entreprises -->
                    <div class="bg-white rounded-xl shadow-sm border mb-4">
                        <div class="p-4 border-bottom"><h5 class="fw-semibold mb-0">Gestion des Entreprises</h5></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th>Entreprise</th><th>Statut</th><th>Raison</th><th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                        <tr class="{{ $company->is_blocked ? 'table-danger' : '' }}">
                                            <td><strong>{{ $company->company_name }}</strong></td>
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
