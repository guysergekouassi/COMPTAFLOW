<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => $habilitations])
            
            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0"><i class="bx bx-home-heart me-2"></i>Mon Dashboard Personnel</h4>
                            <a href="{{ route('profile') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-user me-1"></i>Voir mon profil
                            </a>
                        </div>

                        {{-- Cartes de statistiques --}}
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Connexions</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $stats['total_logins'] }}</h4>
                                                </div>
                                                <small class="mb-0">Total de connexions</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="bx bx-log-in icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Dernière connexion</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h6 class="mb-0 me-2">{{ $stats['last_login']->format('d/m/Y') }}</h6>
                                                </div>
                                                <small class="mb-0">{{ $stats['last_login']->diffForHumans() }}</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class="bx bx-time icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Ancienneté</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $stats['account_age_days'] }}</h4>
                                                </div>
                                                <small class="mb-0">Jours de membre</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-warning">
                                                    <i class="bx bx-calendar icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Sessions actives</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $stats['active_sessions'] }}</h4>
                                                </div>
                                                <small class="mb-0">Appareil(s) connecté(s)</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-info">
                                                    <i class="bx bx-devices icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Informations rapides --}}
                        <div class="row g-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0">Aperçu du compte</h5>
                                        <a href="{{ route('settings') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-cog me-1"></i>Paramètres
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-lg rounded-circle bg-primary text-white me-3"
                                                        style="font-size: 1.5rem; font-weight: bold;">
                                                        {{ $user->initiales }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $user->last_name }} {{ $user->name }}</h6>
                                                        <small class="text-muted">{{ $user->email_adresse }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small mb-1">Rôle</label>
                                                <div>
                                                    <span class="badge bg-label-{{ $user->role === 'admin' ? 'warning' : ($user->role === 'super_admin' ? 'danger' : 'info') }}">
                                                        {{ $user->role === 'super_admin' ? 'Super Admin' : ucfirst($user->role) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small mb-1">Entreprise</label>
                                                <p class="mb-0 fw-semibold">{{ $company->company_name ?? 'Non renseigné' }}</p>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label text-muted small mb-1">Statut</label>
                                                <div>
                                                    <span class="badge bg-label-{{ $user->is_online ? 'success' : 'secondary' }}">
                                                        {{ $user->is_online ? 'En ligne' : 'Hors ligne' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Actions rapides</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('settings') }}" class="btn btn-outline-primary">
                                                <i class="bx bx-cog me-2"></i>Paramètres du compte
                                            </a>
                                            <a href="{{ route('profile') }}" class="btn btn-outline-primary">
                                                <i class="bx bx-user me-2"></i>Voir mon profil
                                            </a>
                                            @if($user->role === 'admin' || $user->role === 'super_admin')
                                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-success">
                                                <i class="bx bx-bar-chart me-2"></i>Dashboard Admin
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Activité récente (placeholder) --}}
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="bx bx-history me-2"></i>Activité récente</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-0">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Fonctionnalité à venir : Consultez l'historique de vos actions et activités sur la plateforme.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    @include('components.footer')
</body>
</html>
