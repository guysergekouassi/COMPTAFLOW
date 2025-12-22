<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header')

                <!-- Nouveau contenu du dashboard selon source_design.html -->
                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    <!-- Stats Section -->
                    <div id="stats-section" class="row g-4 mb-8">
                        <!-- KPI 1: Nombre total d'utilisateurs -->
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-users text-primary fs-4"></i>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded fw-medium">+12.5%</span>
                                </div>
                                <h6 class="text-gray-500 fw-medium mb-2">Nombre total d'utilisateurs</h6>
                                <h3 class="h2 text-gray-900 mb-0">{{ number_format($totalUsers ?? 0) }}</h3>
                            </div>
                        </div>

                        <!-- KPI 2: Comptes Connectés -->
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-user-check text-purple-600 fs-4"></i>
                                    </div>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded fw-medium">-3.2%</span>
                                </div>
                                <h6 class="text-gray-500 fw-medium mb-2">Comptes Connectés</h6>
                                <h3 class="h2 text-gray-900 mb-0">{{ number_format($connectedUsers ?? 0) }}</h3>
                            </div>
                        </div>

                        <!-- KPI 3: Plans Comptables créés -->
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-file-lines text-success fs-4"></i>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded fw-medium">+18.7%</span>
                                </div>
                                <h6 class="text-gray-500 fw-medium mb-2">Plans Comptables créés</h6>
                                <h3 class="h2 text-gray-900 mb-0">{{ number_format($plansToday ?? 0) }}</h3>
                            </div>
                        </div>

                        <!-- KPI 4: Exercices comptables créés -->
                        <div class="col-md-3">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="w-12 h-12 bg-orange-100 rounded-lg d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-calendar-days text-orange-600 fs-4"></i>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded fw-medium">{{ number_format($exercicesToday ?? 0) }} total</span>
                                </div>
                                <h6 class="text-gray-500 fw-medium mb-2">Exercices comptables créés</h6>
                                <h3 class="h2 text-gray-900 mb-0">{{ number_format($exercicesToday ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>

                    

                    <!-- Tables Section -->
                    <div id="tables-section" class="row g-4 mb-8">
                        <!-- Table 1: Alertes Comptables -->
                        <div class="col-12">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="d-flex justify-content-between align-items-center mb-6">
                                    <h5 class="text-lg fw-semibold text-gray-900 mb-0">Alertes Comptables Récentes</h5>
                                    <a href="#" class="text-primary text-decoration-none fw-medium">Voir tout</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Type d'Alerte</th>
                                                <th>Description</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            <tr>
                                                <td><span class="badge bg-danger me-1">Écart</span></td>
                                                <td>Décalage de 45 jours sur le Paiement Fournisseur #2034.</td>
                                                <td>15 Nov 2025</td>
                                                <td><span class="badge bg-danger">Urgent</span></td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning me-1">Erreur</span></td>
                                                <td>Pièce comptable manquante pour la facture A23-90.</td>
                                                <td>18 Nov 2025</td>
                                                <td><span class="badge bg-warning">À Traiter</span></td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-info me-1">Rappel</span></td>
                                                <td>Clôture de l'exercice dans 30 jours.</td>
                                                <td>19 Nov 2025</td>
                                                <td><span class="badge bg-info">Planifié</span></td>
                                            </tr>
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
        </div>
        <!-- Overlay -->
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
