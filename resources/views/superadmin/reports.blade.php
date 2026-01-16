<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Rapports de Performance'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    


                    <!-- KPIs -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Entreprises Totales</p>
                                        <h3 class="fw-bold mb-0 mt-2">{{ $kpis['total_companies'] }}</h3>
                                    </div>
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-building text-primary fs-5"></i>
                                    </div>
                                </div>
                                <p class="text-success small mb-0">
                                    <i class="fa-solid fa-arrow-up me-1"></i>
                                    {{ $kpis['active_companies'] }} actives
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Utilisateurs</p>
                                        <h3 class="fw-bold mb-0 mt-2">{{ $kpis['total_users'] }}</h3>
                                    </div>
                                    <div class="w-12 h-12 bg-green-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-users text-success fs-5"></i>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Utilisateurs actifs
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Écritures Totales</p>
                                        <h3 class="fw-bold mb-0 mt-2">{{ number_format($kpis['total_entries']) }}</h3>
                                    </div>
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-file-invoice text-purple-600 fs-5"></i>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Transactions enregistrées
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <p class="text-gray-500 mb-0 small">Taux d'Utilisation</p>
                                        <h3 class="fw-bold mb-0 mt-2">
                                            {{ $kpis['total_companies'] > 0 ? round(($kpis['active_companies'] / $kpis['total_companies']) * 100) : 0 }}%
                                        </h3>
                                    </div>
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-chart-pie text-orange-600 fs-5"></i>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Entreprises actives
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Croissance mensuelle -->
                        <div class="col-lg-8">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-semibold mb-0">Croissance Mensuelle des Entreprises</h5>
                                    <span class="badge bg-primary">12 derniers mois</span>
                                </div>
                                <div id="monthlyGrowthChart" style="min-height: 300px;"></div>
                            </div>
                        </div>

                        <!-- Répartition des utilisateurs -->
                        <div class="col-lg-4">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                <h5 class="fw-semibold mb-4">Utilisateurs par Rôle</h5>
                                <div id="usersByRoleChart" style="min-height: 300px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Top entreprises -->
                    <div class="row g-4 mt-2">
                        <div class="col-12">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="p-4 border-bottom">
                                    <h5 class="fw-semibold mb-0">Top 10 Entreprises par Utilisation</h5>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="fw-semibold">#</th>
                                                <th class="fw-semibold">Entreprise</th>
                                                <th class="fw-semibold">Nombre d'Écritures</th>
                                                <th class="fw-semibold">Activité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($companyUsage as $index => $company)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="fw-medium">{{ $company->company_name }}</td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            {{ number_format($company->ecritures_comptables_count) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 8px; width: 200px;">
                                                            @php
                                                                $maxUsage = $companyUsage->first()->ecritures_comptables_count;
                                                                $percentage = $maxUsage > 0 ? ($company->ecritures_comptables_count / $maxUsage) * 100 : 0;
                                                            @endphp
                                                            <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">
                                                        <i class="fa-solid fa-chart-bar fa-2x mb-2"></i>
                                                        <p class="mb-0">Aucune donnée disponible</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique de croissance mensuelle
            const monthlyGrowthOptions = {
                series: [{
                    name: 'Nouvelles Entreprises',
                    data: @json($monthlyGrowth->pluck('count')->reverse()->values())
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#3b82f6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: @json($monthlyGrowth->pluck('month')->reverse()->values()),
                },
                grid: {
                    borderColor: '#f1f5f9',
                }
            };
            new ApexCharts(document.querySelector("#monthlyGrowthChart"), monthlyGrowthOptions).render();

            // Graphique des utilisateurs par rôle
            const usersByRoleOptions = {
                series: @json($usersByRole->pluck('count')->values()),
                labels: @json($usersByRole->pluck('role')->map(function($role) {
                    return ucfirst($role);
                })->values()),
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#3b82f6', '#8b5cf6', '#10b981'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                        }
                    }
                },
                legend: { position: 'bottom' },
                dataLabels: { enabled: true }
            };
            new ApexCharts(document.querySelector("#usersByRoleChart"), usersByRoleOptions).render();
        });
    </script>
</body>
</html>
