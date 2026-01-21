<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => "Contrôle d'Accès"])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Contrôle d'Accès</h5>
                                <p class="text-muted small mb-0">Gérez l'éligibilité et l'accès des membres de votre cabinet</p>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="split-layout">
                            <div class="split-aside glass-card p-3">
                                <div class="mb-3 px-2">
                                    <input type="text" class="form-control form-control-sm" placeholder="Rechercher un membre...">
                                </div>
                                <div class="list-group list-group-premium">
                                    @foreach($users as $user)
                                        <a href="?selected_user={{ $user->id }}" class="list-group-item list-group-item-action d-flex align-items-center {{ (request('selected_user') == $user->id || ($loop->first && !request('selected_user'))) ? 'active' : '' }}">
                                            <div class="user-card-initials me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ $user->initiales }}
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="small fw-bold text-truncate">{{ $user->name }} {{ $user->last_name }}</div>
                                                <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">{{ $user->role }}</small>
                                            </div>
                                            @if($user->is_blocked)
                                                <i class="fa-solid fa-circle-xmark text-danger ms-2" style="font-size: 0.6rem;"></i>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="split-main">
                                @php
                                    $selectedId = request('selected_user') ?? ($users->first()->id ?? null);
                                    $selectedUser = $users->where('id', $selectedId)->first();
                                @endphp

                                @if($selectedUser)
                                    <div class="glass-card p-5 h-100 d-flex flex-column align-items-center text-center">
                                        <div class="user-card-initials mb-4" style="width: 100px; height: 100px; font-size: 2.5rem; background: var(--premium-gradient-1); color: white;">
                                            {{ $selectedUser->initiales }}
                                        </div>
                                        <h4 class="fw-bold mb-1">{{ $selectedUser->name }} {{ $selectedUser->last_name }}</h4>
                                        <p class="text-muted mb-4">{{ $selectedUser->email_adresse }}</p>

                                        <div class="row w-100 mb-5">
                                            <div class="col-6">
                                                <div class="p-3 bg-light rounded-3">
                                                    <small class="text-muted d-block mb-1">Rôle</small>
                                                    <span class="badge-premium badge-premium-info">{{ strtoupper($selectedUser->role) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="p-3 bg-light rounded-3">
                                                    <small class="text-muted d-block mb-1">Statut</small>
                                                    <span class="badge {{ $selectedUser->is_blocked ? 'bg-label-danger' : 'bg-label-success' }} rounded-pill">
                                                        {{ $selectedUser->is_blocked ? 'Compte Bloqué' : 'Compte Actif' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-auto w-100">
                                            <form action="{{ route('admin.access.toggle_user', $selectedUser->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-lg w-100 {{ $selectedUser->is_blocked ? 'btn-success' : 'btn-danger' }}">
                                                    <i class="fa-solid {{ $selectedUser->is_blocked ? 'fa-unlock' : 'fa-lock' }} me-2"></i>
                                                    {{ $selectedUser->is_blocked ? 'Débloquer cet utilisateur' : 'Révoquer l\'accès' }}
                                                </button>
                                            </form>
                                            <p class="text-muted small mt-3">
                                                <i class="fa-solid fa-circle-info me-1"></i>
                                                {{ $selectedUser->is_blocked ? 'Le déblocage redonnera immédiatement l\'accès à toutes les fonctionnalités autorisées.' : 'Le blocage empêchera toute connexion ou action future sur la plateforme.' }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="glass-card p-5 h-100 d-flex flex-column align-items-center justify-content-center text-center opacity-50">
                                        <i class="fa-solid fa-user-shield fa-4x mb-3"></i>
                                        <h5>Sélectionnez un membre</h5>
                                        <p class="text-muted">Choisissez un membre dans la liste de gauche pour gérer ses accès.</p>
                                    </div>
                                @endif
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
