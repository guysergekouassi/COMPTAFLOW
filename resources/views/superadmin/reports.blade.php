@include('components.head')

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
    }

    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: transform 0.2s;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .kpi-icon-container {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .chart-container {
        min-height: 350px;
    }

    .page-header-analytics {
        background: linear-gradient(135deg, #4338ca 0%, #3b82f6 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .bg-icon-overlay {
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 10rem;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'SuperAdmin <span class="text-primary">Analytics</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Premium Analytics -->
                        <div class="page-header-analytics shadow-lg">
                            <div class="position-relative z-index-2">
                                <h2 class="font-black mb-1 text-white">Rapports de Performance</h2>
                                <p class="mb-0 text-indigo-100 font-medium opacity-90">Analyse détaillée de l'utilisation et de la croissance de la plateforme.</p>
                            </div>
                            <div class="bg-icon-overlay">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                        </div>

                        <!-- KPIs Grid -->
                        <div class="row g-4 mb-6">
                            <!-- Entreprises -->
                            <div class="col-md-3">
                                <div class="kpi-card shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="kpi-icon-container bg-blue-50 text-blue-600">
                                            <i class="fa-solid fa-building"></i>
                                        </div>
                                        <span class="badge bg-green-100 text-green-700 rounded-pill font-bold fs-xs">
                                            {{ $kpis['active_companies'] }} Actives
                                        </span>
                                    </div>
                                    <h3 class="font-black text-slate-800 mb-0">{{ $kpis['total_companies'] }}</h3>
                                    <p class="text-slate-400 text-xs font-bold text-uppercase tracking-wider">Entreprises Totales</p>
                                </div>
                            </div>
                            
                            <!-- Utilisateurs -->
                            <div class="col-md-3">
                                <div class="kpi-card shadow-sm">
                                    <div class="kpi-icon-container bg-purple-50 text-purple-600">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 mb-0">{{ $kpis['total_users'] }}</h3>
                                    <p class="text-slate-400 text-xs font-bold text-uppercase tracking-wider">Utilisateurs Enregistrés</p>
                                </div>
                            </div>

                            <!-- Écritures -->
                            <div class="col-md-3">
                                <div class="kpi-card shadow-sm">
                                    <div class="kpi-icon-container bg-emerald-50 text-emerald-600">
                                        <i class="fa-solid fa-file-invoice-dollar"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 mb-0">{{ number_format($kpis['total_entries']) }}</h3>
                                    <p class="text-slate-400 text-xs font-bold text-uppercase tracking-wider">Transactions Globales</p>
                                </div>
                            </div>

                            <!-- Taux d'Utilisation -->
                            <div class="col-md-3">
                                <div class="kpi-card shadow-sm">
                                    <div class="kpi-icon-container bg-orange-50 text-orange-600">
                                        <i class="fa-solid fa-gauge-high"></i>
                                    </div>
                                    <h3 class="font-black text-slate-800 mb-0">
                                        {{ $kpis['total_companies'] > 0 ? round(($kpis['active_companies'] / $kpis['total_companies']) * 100) : 0 }}%
                                    </h3>
                                    <p class="text-slate-400 text-xs font-bold text-uppercase tracking-wider">Taux d'Engagement</p>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="row g-6 mb-6">
                            <!-- Graphique Croissance -->
                            <div class="col-lg-8">
                                <div class="glass-card p-6 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-6">
                                        <div>
                                            <h5 class="font-black text-slate-800 mb-1">Croissance du Réseau</h5>
                                            <p class="text-slate-400 text-sm mb-0">Évolution des inscriptions entreprises (12 mois)</p>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill font-bold">
                                            <i class="fa-solid fa-download me-2"></i>Export
                                        </button>
                                    </div>
                                    <div id="monthlyGrowthChart"></div>
                                </div>
                            </div>

                            <!-- Graphique Répartition -->
                            <div class="col-lg-4">
                                <div class="glass-card p-6 h-100">
                                    <h5 class="font-black text-slate-800 mb-1">Démographie</h5>
                                    <p class="text-slate-400 text-sm mb-6">Répartition par rôle</p>
                                    <div id="usersByRoleChart" class="d-flex justify-content-center"></div>
                                    
                                    <div class="mt-4">
                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 mt-2">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="w-3 h-3 rounded-circle bg-blue-500 me-2"></div>
                                                <span class="text-sm font-bold text-slate-600">Admins</span>
                                                <span class="ms-auto font-black text-slate-800">{{ $usersByRole->where('role', 'admin')->first()->count ?? 0 }}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="w-3 h-3 rounded-circle bg-purple-500 me-2"></div>
                                                <span class="text-sm font-bold text-slate-600">Comptables</span>
                                                <span class="ms-auto font-black text-slate-800">{{ $usersByRole->where('role', 'comptable')->first()->count ?? 0 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Top Entreprises -->
                        <div class="glass-card overflow-hidden">
                            <div class="p-6 border-bottom border-slate-100">
                                <h5 class="font-black text-slate-800 mb-0">Classement Utilisateurs</h5>
                                <p class="text-slate-400 text-sm mb-0 mt-1">Top 10 des entreprises les plus actives.</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="ps-6 py-4 text-xs font-black text-slate-400 text-uppercase">Rang</th>
                                            <th class="py-4 text-xs font-black text-slate-400 text-uppercase">Entreprise</th>
                                            <th class="py-4 text-xs font-black text-slate-400 text-uppercase text-end">Volume Écritures</th>
                                            <th class="pe-6 py-4 text-xs font-black text-slate-400 text-uppercase" style="width: 30%;">Niveau d'activité</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @forelse($companyUsage as $index => $company)
                                            <tr>
                                                <td class="ps-6 fw-bold text-slate-500">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="w-8 h-8 rounded-circle bg-blue-100 text-blue-600 d-flex align-items-center justify-content-center fw-bold me-3 text-xs">
                                                            {{ substr($company->company_name, 0, 2) }}
                                                        </div>
                                                        <span class="fw-bold text-slate-700">{{ $company->company_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end font-black text-slate-800 fs-6">
                                                    {{ number_format($company->ecritures_comptables_count) }}
                                                </td>
                                                <td class="pe-6">
                                                    @php
                                                        $maxUsage = $companyUsage->first()->ecritures_comptables_count;
                                                        $percentage = $maxUsage > 0 ? ($company->ecritures_comptables_count / $maxUsage) * 100 : 0;
                                                        $colorClass = $index < 3 ? 'bg-blue-600' : 'bg-slate-300';
                                                    @endphp
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-slate-400">
                                                    Données insuffisantes pour établir un classement.
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
        </div>
    </div>

    <!-- Scripts ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration commune
            const commonOptions = {
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                toolbar: { show: false }
            };

            // 1. Chart Croissance
            const growthOptions = {
                ...commonOptions,
                series: [{
                    name: 'Nouvelles Inscriptions',
                    data: @json($monthlyGrowth->pluck('count')->reverse()->values())
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    zoom: { enabled: false }
                },
                colors: ['#3b82f6'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: @json($monthlyGrowth->pluck('month')->reverse()->values()),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    show: false
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                }
            };
            new ApexCharts(document.querySelector("#monthlyGrowthChart"), growthOptions).render();

            // 2. Chart Démographie
            const roleOptions = {
                ...commonOptions,
                series: @json($usersByRole->pluck('count')->values()),
                labels: @json($usersByRole->pluck('role')->map(fn($r) => ucfirst($r))->values()),
                chart: {
                    type: 'donut',
                    height: 280
                },
                colors: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: { show: false },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 800,
                                    color: '#1e293b',
                                    offsetY: 8
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total',
                                    fontSize: '14px',
                                    color: '#64748b',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                stroke: { show: false }
            };
            new ApexCharts(document.querySelector("#usersByRoleChart"), roleOptions).render();
        });
    </script>
</body>
</html>
