{{-- resources/views/compta/dashboard.blade.php --}}

<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- Inclure le sidebar pour le contexte de la compagnie --}}
            @include('components.sidebar', ['habilitations' => $habilitations ?? []])

            <div class="layout-page">
                @include('components.header')

<!-- Nouveau contenu du dashboard selon source_design.html -->
                    <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                        <!-- Stats Section -->
                        <div id="stats-section" class="row g-4 mb-8">
                            <!-- KPI 1: Chiffre d'affaires -->
                            <div class="col-md-3">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-coins text-primary fs-4"></i>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded fw-medium">+12.5%</span>
                                    </div>
                                    <h6 class="text-gray-500 fw-medium mb-2">Chiffre d'affaires</h6>
                                    <h3 class="h2 text-gray-900 mb-0">{{ number_format($totalRevenue ?? 245680, 0, ',', ' ') }} €</h3>
                                </div>
                            </div>

                            <!-- KPI 2: Charges -->
                            <div class="col-md-3">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="w-12 h-12 bg-purple-100 rounded-lg d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-receipt text-purple-600 fs-4"></i>
                                        </div>
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded fw-medium">-3.2%</span>
                                    </div>
                                    <h6 class="text-gray-500 fw-medium mb-2">Charges</h6>
                                    <h3 class="h2 text-gray-900 mb-0">{{ number_format($totalExpenses ?? 128450, 0, ',', ' ') }} €</h3>
                                </div>
                            </div>

                            <!-- KPI 3: Résultat net -->
                            <div class="col-md-3">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="w-12 h-12 bg-green-100 rounded-lg d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-chart-line text-success fs-4"></i>
                                        </div>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded fw-medium">+18.7%</span>
                                    </div>
                                    <h6 class="text-gray-500 fw-medium mb-2">Résultat net</h6>
                                    <h3 class="h2 text-gray-900 mb-0">{{ number_format($netResult ?? 117230, 0, ',', ' ') }} €</h3>
                                </div>
                            </div>

                            <!-- KPI 4: Écritures du mois -->
                            <div class="col-md-3">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="w-12 h-12 bg-orange-100 rounded-lg d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-file-invoice text-orange-600 fs-4"></i>
                                        </div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded fw-medium">{{ $monthlyEntries ?? 89 }} total</span>
                                    </div>
                                    <h6 class="text-gray-500 fw-medium mb-2">Écritures du mois</h6>
                                    <h3 class="h2 text-gray-900 mb-0">{{ $monthlyEntries ?? 89 }}</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div id="charts-section" class="row g-4 mb-8">
                            <!-- Chart 1: Évolution du chiffre d'affaires -->
                            <div class="col-md-6">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-6">
                                        <h5 class="text-lg fw-semibold text-gray-900 mb-0">Évolution du chiffre d'affaires</h5>
                                        <select class="form-select form-select-sm" style="width: auto;">
                                            <option>6 derniers mois</option>
                                            <option>12 derniers mois</option>
                                            <option>Année en cours</option>
                                        </select>
                                    </div>
                                    <div id="revenueChart" style="height: 300px;"></div>
                                </div>
                            </div>

                            <!-- Chart 2: Répartition des charges -->
                            <div class="col-md-6">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-6">
                                        <h5 class="text-lg fw-semibold text-gray-900 mb-0">Répartition des charges</h5>
                                        <button class="btn btn-sm btn-link text-primary text-decoration-none fw-medium">Voir détails</button>
                                    </div>
                                    <div id="expensesChart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Tables Section -->
                        <div id="tables-section" class="row g-4 mb-8">
                            <!-- Table 1: Dernières écritures -->
                            <div class="col-md-6">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-6">
                                        <h5 class="text-lg fw-semibold text-gray-900 mb-0">Dernières écritures</h5>
                                        <a href="#" class="text-primary text-decoration-none fw-medium">Voir tout</a>
                                    </div>
                                    <div class="space-y-4">
                                        @forelse($recentEntries ?? [] as $entry)
                                        <div class="d-flex justify-content-between align-items-center pb-4 border-bottom border-gray-100">
                                            <div class="d-flex align-items-center">
                                                <div class="w-10 h-10 bg-{{ $entry['type'] == 'income' ? 'blue' : 'red' }}-100 rounded-lg d-flex align-items-center justify-content-center">
                                                    <i class="fa-solid fa-arrow-{{ $entry['type'] == 'income' ? 'down' : 'up' }} text-{{ $entry['type'] == 'income' ? 'blue' : 'red' }}-600"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <p class="text-sm fw-medium text-gray-900 mb-0">{{ $entry['description'] ?? 'Écriture comptable' }}</p>
                                                    <p class="text-xs text-gray-500 mb-0">{{ $entry['date'] ?? date('d M Y') }} • {{ $entry['journal'] ?? 'Journal' }}</p>
                                                </div>
                                            </div>
                                            <span class="text-sm fw-semibold text-{{ $entry['type'] == 'income' ? 'success' : 'danger' }}">
                                                {{ $entry['type'] == 'income' ? '+' : '-' }}{{ number_format($entry['amount'] ?? 0, 0, ',', ' ') }} €
                                            </span>
                                        </div>
                                        @empty
                                        <div class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-pen-to-square fa-2x mb-2"></i>
                                            <p class="mb-0">Aucune écriture récente</p>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Table 2: Alertes comptables -->
                            <div class="col-md-6">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <div class="d-flex justify-content-between align-items-center mb-6">
                                        <h5 class="text-lg fw-semibold text-gray-900 mb-0">Alertes comptables</h5>
                                        <a href="#" class="text-primary text-decoration-none fw-medium">Voir tout</a>
                                    </div>
                                    <div class="space-y-4">
                                        @forelse($alerts ?? [] as $alert)
                                        <div class="d-flex align-items-center pb-4 border-bottom border-gray-100">
                                            <div class="w-10 h-10 bg-{{ $alert['priority'] == 'high' ? 'red' : ($alert['priority'] == 'medium' ? 'yellow' : 'blue') }}-100 rounded-lg d-flex align-items-center justify-content-center">
                                                <i class="fa-solid fa-{{ $alert['icon'] ?? 'exclamation-triangle' }} text-{{ $alert['priority'] == 'high' ? 'red' : ($alert['priority'] == 'medium' ? 'yellow' : 'blue') }}-600"></i>
                                            </div>
                                            <div class="ms-3 flex-grow-1">
                                                <p class="text-sm fw-medium text-gray-900 mb-0">{{ $alert['title'] ?? 'Alerte comptable' }}</p>
                                                <p class="text-xs text-gray-500 mb-0">{{ $alert['description'] ?? 'Description non disponible' }}</p>
                                            </div>
                                            <span class="badge bg-{{ $alert['priority'] == 'high' ? 'danger' : ($alert['priority'] == 'medium' ? 'warning' : 'info') }} ms-2">
                                                {{ $alert['priority'] == 'high' ? 'Urgent' : ($alert['priority'] == 'medium' ? 'Moyen' : 'Info') }}
                                            </span>
                                        </div>
                                        @empty
                                        <div class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-check-circle fa-2x mb-2"></i>
                                            <p class="mb-0">Aucune alerte active</p>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($currentCompany)
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fa-solid fa-check-circle me-3"></i>
                                <div>
                                    <strong>Bienvenue sur le tableau de bord</strong>
                                    <p class="mb-0">Vous êtes connecté au compte : <strong>{{ $currentCompany->company_name }}</strong></p>
                                </div>
                            </div>
                        @endif

                    </div>


                    @include('components.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

       <!-- Scripts pour les graphiques (doivent être après les éléments Canvas/Div) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===================================
            // CHART 1: Performance des Revenus (Ligne - Chart.js)
            // ===================================
            const ctxRevenue = document.getElementById('revenueChart');
            if (ctxRevenue) {
                new Chart(ctxRevenue, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                        datasets: [{
                            label: 'Revenus Mensuels (€)',
                            data: [12000, 15000, 18000, 22000, 25000, 28000, 26000, 31000, 35000, 32000, 38000, 42000],
                            borderColor: 'rgb(0, 192, 192)',
                            backgroundColor: 'rgba(0, 192, 192, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.formattedValue + ' €';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // ===================================
            // CHART 2: Répartition des Dépenses (Donut - ApexCharts)
            // ===================================
            // NOTE: ApexCharts est inclus dans votre fichier footer.blade.php
            const expensesChartEl = document.querySelector('#expensesChart');
            if (expensesChartEl) {
                const expensesChartOptions = {
                    series: [45, 25, 15, 10, 5], // Pourcentages de dépenses
                    labels: ['Fournisseurs', 'Salaires', 'Marketing', 'Loyer/Frais fixes', 'Autres'],
                    chart: {
                        height: 300,
                        type: 'donut',
                        toolbar: {
                            show: false
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '1rem',
                                        color: '#adb5bd',
                                        offsetY: -10
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '1.5rem',
                                        fontWeight: 'bold',
                                        color: '#344050',
                                        offsetY: 10,
                                        formatter: function(val) {
                                            return val + '%'
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function(w) {
                                            // Afficher le total des pourcentages
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + '%';
                                        }
                                    }
                                }
                            }
                        }
                    },
                    colors: ['#00a76f', '#1890ff', '#ffc107', '#ff4d4f', '#72e128'],
                    dataLabels: {
                        enabled: false,
                    }
                };

                const expensesChart = new ApexCharts(expensesChartEl, expensesChartOptions);
                expensesChart.render();
            }
        });
    </script>
</body>
</html>
