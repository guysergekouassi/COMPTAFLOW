<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Suivi des Activités'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    


                    <!-- Statistiques d'activité -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-users text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Utilisateurs Actifs</p>
                                        <h4 class="fw-bold mb-0">{{ $stats['active_users_today'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-building text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Entreprises</p>
                                        <h4 class="fw-bold mb-0">{{ $stats['total_companies'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-file-invoice text-purple-600 fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Écritures Aujourd'hui</p>
                                        <h4 class="fw-bold mb-0">{{ $stats['total_entries_today'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex align-items-center">
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg d-flex align-items-center justify-content-center me-3">
                                        <i class="fa-solid fa-chart-line text-orange-600 fs-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Total Utilisateurs</p>
                                        <h4 class="fw-bold mb-0">{{ $stats['total_users'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Activités récentes -->
                        <div class="col-lg-8">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="p-4 border-bottom">
                                    <h5 class="fw-semibold mb-0">Activités Récentes</h5>
                                </div>
                                
                                <div class="p-4">
                                    <div class="timeline">
                                        @forelse($recentActivities as $activity)
                                            <div class="timeline-item mb-4 pb-4 border-bottom">
                                                <div class="d-flex">
                                                    <div class="timeline-marker me-3">
                                                        <div class="w-10 h-10 bg-blue-100 rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="fa-solid fa-pen text-primary"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <p class="fw-semibold mb-1">
                                                                    {{ $activity->user->name ?? 'Utilisateur inconnu' }}
                                                                    <span class="text-muted fw-normal">a créé une écriture</span>
                                                                </p>
                                                                <p class="text-muted small mb-0">
                                                                    <i class="fa-solid fa-building me-1"></i>
                                                                    {{ $activity->company->company_name ?? 'N/A' }}
                                                                </p>
                                                            </div>
                                                            <span class="badge bg-light text-dark">
                                                                {{ $activity->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                        <p class="text-sm text-gray-600 mb-0">
                                                            Écriture N° {{ $activity->n_saisie }} - 
                                                            {{ number_format($activity->montant_debit + $activity->montant_credit, 0, ',', ' ') }} FCFA
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-clock fa-3x mb-3"></i>
                                                <p class="mb-0">Aucune activité récente</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activités par entreprise -->
                        <div class="col-lg-4">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="p-4 border-bottom">
                                    <h5 class="fw-semibold mb-0">Activité par Entreprise</h5>
                                    <p class="text-muted small mb-0">30 derniers jours</p>
                                </div>
                                
                                <div class="p-4">
                                    @forelse($activitiesByCompany->sortByDesc('ecritures_comptables_count')->take(10) as $company)
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-sm fw-medium">{{ strlen($company->company_name) > 25 ? substr($company->company_name, 0, 25) . '...' : $company->company_name }}</span>
                                                <span class="badge bg-primary">{{ $company->ecritures_comptables_count }}</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                @php
                                                    $maxCount = $activitiesByCompany->max('ecritures_comptables_count');
                                                    $percentage = $maxCount > 0 ? ($company->ecritures_comptables_count / $maxCount) * 100 : 0;
                                                @endphp
                                                <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-chart-bar fa-2x mb-2"></i>
                                            <p class="mb-0 small">Aucune donnée disponible</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
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
