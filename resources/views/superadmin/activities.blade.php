@include('components.head')

@php use Illuminate\Support\Str; @endphp

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 20px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease-in-out;
    }
    
    .glass-card:hover {
        transform: translateY(-2px);
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


    .activity-timeline-item {
        position: relative;
        padding-left: 3rem;
        padding-bottom: 2rem;
        border-left: 2px solid #e2e8f0;
    }

    .activity-timeline-item:last-child {
        border-left: transparent;
    }

    .activity-icon {
        position: absolute;
        left: -1rem;
        top: 0;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: white;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.875rem;
    }

    .company-progress {
        height: 6px;
        border-radius: 3px;
        background-color: #f1f5f9;
        margin-top: 0.5rem;
    }

    .company-progress-bar {
        height: 100%;
        border-radius: 3px;
        background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    }

    .page-header-premium {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .header-decoration {
        position: absolute;
        right: 0;
        top: 0;
        opacity: 0.1;
        transform: translate(20%, -20%) rotate(-15deg);
        font-size: 8rem;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Suivi des Activités'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Premium -->
                        <div class="page-header-premium shadow-lg">
                            <div class="position-relative z-index-2">
                                <h2 class="font-black mb-1 text-white">Suivi des Activités</h2>
                                <p class="mb-0 text-slate-300 font-medium opacity-80">Vision globale de l'activité sur l'ensemble des dossiers comptables.</p>
                            </div>
                            <div class="header-decoration">
                                <i class="fa-solid fa-tower-broadcast"></i>
                            </div>
                        </div>

                        <!-- KPIs Compacts sur une ligne (Premium TFT Style) -->
                        <div class="row g-4 mb-6">
                            <!-- Utilisateurs Actifs -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm card-premium h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Actifs</h6>
                                            <div class="icon-box bg-label-primary text-primary">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                        </div>
                                        <h3 class="mb-2 fw-extrabold text-dark">{{ $stats['active_users_today'] }}</h3>
                                        <div class="small fw-semibold text-primary">
                                            <i class="fa-solid fa-clock me-1"></i> Aujourd'hui
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Entreprises -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm card-premium h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Entités</h6>
                                            <div class="icon-box bg-label-info text-info">
                                                <i class="fa-solid fa-building"></i>
                                            </div>
                                        </div>
                                        <h3 class="mb-2 fw-extrabold text-dark">{{ $stats['total_companies'] }}</h3>
                                        <div class="small fw-semibold text-info">
                                            <i class="fa-solid fa-database me-1"></i> Total Entreprises
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Écritures 24h -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm card-premium h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Écritures</h6>
                                            <div class="icon-box bg-label-success text-success">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </div>
                                        </div>
                                        <h3 class="mb-2 fw-extrabold text-dark">{{ $stats['total_entries_today'] }}</h3>
                                        <div class="small fw-semibold text-success">
                                            <i class="fa-solid fa-bolt me-1"></i> Dernières 24h
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Utilisateurs -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm card-premium h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Utilisateurs</h6>
                                            <div class="icon-box bg-label-warning text-warning">
                                                <i class="fa-solid fa-users"></i>
                                            </div>
                                        </div>
                                        <h3 class="mb-2 fw-extrabold text-dark">{{ $stats['total_users'] }}</h3>
                                        <div class="small fw-semibold text-warning">
                                            <i class="fa-solid fa-user-check me-1"></i> Plateforme
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-6">
                            <!-- Flux d'Activités Récentes -->
                            <div class="col-lg-8">
                                <div class="glass-card h-100">
                                    <div class="p-4 border-bottom border-slate-100 d-flex justify-content-between align-items-center">
                                        <h5 class="font-black mb-0 text-slate-800">Activités Récentes</h5>
                                        <span class="badge bg-slate-100 text-slate-600 rounded-pill px-3 py-2 font-bold text-xs">Temps Réel</span>
                                    </div>
                                    
                                    <div class="p-5">
                                        <div class="activity-feed">
                                            @forelse($recentActivities as $activity)
                                                <div class="activity-timeline-item">
                                                    <div class="activity-icon shadow-sm">
                                                        <i class="fa-solid fa-bolt text-blue-500"></i>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <div>
                                                            <span class="font-bold text-slate-800">{{ $activity->user->name ?? 'Système' }}</span>
                                                            <span class="text-slate-500 text-sm mx-1">a enregistré une écriture sur</span>
                                                            <span class="font-bold text-blue-600">{{ $activity->company->company_name ?? 'Inconnu' }}</span>
                                                        </div>
                                                        <small class="text-slate-400 font-medium whitespace-nowrap ms-2">
                                                            {{ $activity->created_at->diffForHumans(null, true, true) }}
                                                        </small>
                                                    </div>
                                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mt-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="badge bg-white border border-slate-200 text-slate-600 me-2 text-xs font-bold">#{{ $activity->n_saisie }}</span>
                                                                <span class="text-sm font-medium text-slate-600">{{ Str::limit($activity->libelle, 40) }}</span>
                                                            </div>
                                                            <span class="font-black text-slate-800 fs-6">
                                                                {{ number_format($activity->montant_debit + $activity->montant_credit, 0, ',', ' ') }} <span class="text-xs text-slate-400">FCFA</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5">
                                                    <div class="mb-3">
                                                        <i class="fa-solid fa-mug-hot text-slate-200 fa-3x"></i>
                                                    </div>
                                                    <p class="text-slate-400 font-medium">Hcalme plat pour le moment...</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Classement des Entreprises -->
                            <div class="col-lg-4">
                                <div class="glass-card h-100">
                                    <div class="p-4 border-bottom border-slate-100">
                                        <h5 class="font-black mb-1 text-slate-800">Top Activité (30j)</h5>
                                        <p class="text-slate-400 text-xs mb-0 font-medium">Volumes d'écritures par dossier</p>
                                    </div>
                                    
                                    <div class="p-4 overflow-auto" style="max-height: 600px;">
                                        @php
                                            $maxCount = $activitiesByCompany->max('ecritures_comptables_count');
                                        @endphp

                                        @forelse($activitiesByCompany->sortByDesc('ecritures_comptables_count')->take(10) as $company)
                                            <div class="mb-5 last:mb-0">
                                                <div class="d-flex justify-content-between align-items-end mb-2">
                                                    <div>
                                                        <h6 class="font-bold text-slate-700 mb-0 text-sm">{{ Str::limit($company->company_name, 25) }}</h6>
                                                    </div>
                                                    <span class="badge bg-blue-50 text-blue-700 rounded-pill font-black text-xs">
                                                        {{ number_format($company->ecritures_comptables_count) }}
                                                    </span>
                                                </div>
                                                
                                                @php
                                                    $percentage = $maxCount > 0 ? ($company->ecritures_comptables_count / $maxCount) * 100 : 0;
                                                @endphp
                                                
                                                <div class="company-progress">
                                                    <div class="company-progress-bar" style="width: {{ $percentage }}%"></div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-slate-400">
                                                Aucune donnée disponible
                                            </div>
                                        @endforelse
                                    </div>
                                    
                                    <div class="p-4 bg-slate-50 rounded-bottom-20 text-center border-top border-slate-100">
                                        <a href="{{ route('superadmin.entities') }}" class="text-blue-600 font-bold text-sm text-decoration-none hover:text-blue-800 transition-colors">
                                            Voir toutes les entités <i class="fa-solid fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
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
