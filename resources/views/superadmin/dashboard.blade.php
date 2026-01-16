<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .text-gradient-gov {
        background: linear-gradient(to right, #0f172a, #334155);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .kpi-icon-box {
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
                @include('components.header')

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    
                    <!-- En-tête -->
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
                                Dashboard <span class="text-gradient-gov">Master</span>
                            </h1>
                            <p class="text-slate-500 font-medium">Vue globale de l'écosystème ComptaFlow</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold border border-slate-200 flex items-center gap-2">
                                <i class="fa-solid fa-calendar"></i> {{ now()->format('F Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- 1. CARTES DE SCORE (KPIs) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        
                        <!-- KPI 1: Compagnies Actives -->
                        <div class="glass-card p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Entités Actives</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($activeCompanies) }} <span class="text-sm font-normal text-slate-400">/ {{ $totalCompanies }}</span></h3>
                                </div>
                                <div class="kpi-icon-box bg-blue-50 text-blue-600">
                                    <i class="fa-solid fa-server"></i>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                @php $activeRate = $totalCompanies > 0 ? ($activeCompanies/$totalCompanies)*100 : 0; @endphp
                                <div class="bg-blue-600 h-full rounded-full" style="width: {{ $activeRate }}%"></div>
                            </div>
                        </div>

                        <!-- KPI 2: Volume de Traitement -->
                        <div class="glass-card p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Volume Écritures</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($volumeTraitement) }}</h3>
                                </div>
                                <div class="kpi-icon-box bg-purple-50 text-purple-600">
                                    <i class="fa-solid fa-database"></i>
                                </div>
                            </div>
                             <p class="text-xs text-slate-400 flex items-center gap-1">
                                <i class="fa-solid fa-arrow-trend-up text-green-500"></i> Transactions comptabilisées
                            </p>
                        </div>

                         <!-- KPI 3: Taux de Complétion -->
                        <div class="glass-card p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Taux Complétion</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $tauxCompletion }}%</h3>
                                </div>
                                <div class="kpi-icon-box bg-green-50 text-green-600">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </div>
                            </div>
                             <p class="text-xs text-slate-400">Exercices clôturés à ce jour</p>
                        </div>

                         <!-- KPI 4: Alertes Sécurité -->
                        <div class="glass-card p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sécurité</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $securityAlerts }}</h3>
                                </div>
                                <div class="kpi-icon-box {{ $securityAlerts > 0 ? 'bg-red-50 text-red-600' : 'bg-slate-50 text-slate-400' }}">
                                    <i class="fa-solid fa-shield-cat"></i>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400">Incidents détectés ce mois</p>
                        </div>
                    </div>

                    <!-- 2. GRAPHIQUES -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        
                        <!-- Chart: Croissance des Données -->
                        <div class="glass-card p-6 lg:col-span-2">
                             <div class="flex items-center justify-between mb-6">
                                <h5 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-chart-line text-blue-600"></i>
                                    Dynamique de Croissance
                                </h5>
                            </div>
                            <div id="growthChart" style="min-height: 300px;"></div>
                        </div>

                        <!-- Chart: Répartition par Secteur -->
                        <div class="glass-card p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h5 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fa-solid fa-chart-pie text-purple-600"></i>
                                    Secteurs d'Activité
                                </h5>
                            </div>
                            <div id="sectorsChart" style="min-height: 300px;"></div>
                        </div>
                    </div>

                    @include('components.footer')
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- Chart 1: Croissance (Area Chart) ---
            const growthOptions = {
                series: [{
                    name: 'Nouvelles Compagnies',
                    data: @json($growthCounts)
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#2563eb'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: @json($growthLabels),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                }
            };
            const growthChart = new ApexCharts(document.querySelector("#growthChart"), growthOptions);
            growthChart.render();


            // --- Chart 2: Secteurs (Donut) ---
            const sectorsOptions = {
                series: @json($sectorCounts),
                labels: @json($sectorLabels),
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                colors: ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: '#64748b',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                legend: { position: 'bottom' },
                dataLabels: { enabled: false }
            };
            const sectorsChart = new ApexCharts(document.querySelector("#sectorsChart"), sectorsOptions);
            sectorsChart.render();
            
        });
    </script>
</body>
</html>
