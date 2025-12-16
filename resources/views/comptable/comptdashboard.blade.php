<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- BIENVENUE & INTRO -->
                        <div class="row mb-5">
                            <div class="col-12">
                                <div class="card bg-primary text-white shadow-lg">
                                    <div class="d-flex align-items-center row">
                                        <div class="col-sm-7">
                                            <div class="card-body">
                                                <h5 class="card-title text-white mb-3">Tableau de Bord Comptable </h5>
                                                <p class="mb-4 text-white-50">
                                                    Bienvenue sur votre espace d'administration. Consultez les indicateurs clés de performance (KPI) de l'exercice en cours.
                                                </p>
                                                <a href="javascript:void(0);" class="btn btn-sm btn-light">Voir le Grand Livre</a>
                                            </div>
                                        </div>
                                        <div class="col-sm-5 text-center text-sm-end">
                                            <div class="card-body pb-0 px-0 px-md-4">
                                                <!-- Image de Man With Laptop - Assurez-vous que le chemin est correct -->
                                                <img src="../assets/img/illustrations/man-with-laptop.png" height="140" alt="Illustration de Bienvenue" style="transform: scaleX(-1);" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                          @if($currentCompany)
                                <div class="alert alert-success">
                                    Bienvenue sur le dashboard du compte : <strong>{{ $currentCompany->company_name }}</strong>.
                                </div>
                            @else
                                {{-- Ce cas ne devrait plus arriver avec la redirection de sécurité dans le contrôleur --}}
                                <div class="alert alert-success">
                                     comptabilité actif sélectionné.
                                </div>
                            @endif

                        <!-- K.P.I. (STATISTIQUES CLÉS) -->
                        <div class="row g-4 mb-5">
                            <!-- KPI 1: Chiffre d'Affaires Net -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-dollar-circle bx-lg text-success"></i>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="text-success small fw-medium">↑ 14.8%</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">TRESORERIE ANNUELLE</small>
                                            <h4 class="mb-0">...</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 2: Dépenses Totales -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-danger">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-wallet bx-lg text-danger"></i>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="text-danger small fw-medium">↓ 5.2%</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Dépenses Totales</small>
                                            <h4 class="mb-0">...</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 3: Nombre de Pièces Saisies -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-file bx-lg text-info"></i>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="text-info small fw-medium">↑ 32%</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Pièces Saisies (Mois)</small>
                                            <h4 class="mb-0">...</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 4: Solde Bancaire / Trésorerie -->
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0">
                                                <i class="bx bx-trending-up bx-lg text-warning"></i>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="text-secondary small fw-medium">0.0%</span>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Solde Trésorerie Actuel</small>
                                            <h4 class="mb-0">...</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GRAPHIQUES ET ANALYTIQUES -->
                        <div class="row g-4">
                            <!-- Graphique 1: Revenus Mensuels (Chart.js) -->
                            <div class="col-xl-7 col-lg-7 col-md-12">
                                <div class="card shadow-lg">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title m-0">Performance Exercice (Annuel)</h5>
                                        <div class="dropdown">
                                            <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="javascript:void(0);">Exporter</a>
                                                <a class="dropdown-item" href="javascript:void(0);">Filtrer</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Le graphique Chart.js est rendu ici -->
                                        <canvas id="revenueChart" class="w-100" height="300"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Graphique 2: Répartition des Dépenses (ApexCharts - Donut) -->
                           <div class="col-xl-5 col-lg-5 col-md-12">
                            <div class="card shadow-lg">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title m-0">Répartition plan tiers</h5>
                                    {{-- Utilisation du total dynamique --}}
                                    <small class="text-muted">Total: € {{ $totalTiersSolde ?? '0,00' }}</small>
                                </div>
                                <div class="card-body pt-0">
                                    <!-- Le graphique ApexCharts est rendu ici -->
                                    <div id="expensesChart"></div>
                                </div>
                            </div>
                        </div>
                            <!-- Exemple de Tableau/Liste d'Alertes -->
                            <div class="col-12 mt-5">
                                <div class="card shadow">
                                    <h5 class="card-header">Alertes Comptables Récentes</h5>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover">
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
                                                    <td><span class="badge bg-label-danger me-1">Écart</span></td>
                                                    <td>Décalage de 45 jours sur le Paiement Fournisseur #2034.</td>
                                                    <td>15 Nov 2025</td>
                                                    <td><span class="badge bg-danger">Urgent</span></td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-label-warning me-1">Erreur</span></td>
                                                    <td>Pièce comptable manquante pour la facture A23-90.</td>
                                                    <td>18 Nov 2025</td>
                                                    <td><span class="badge bg-warning">À Traiter</span></td>
                                                </tr>
                                                <tr>
                                                    <td><span class="badge bg-label-info me-1">Rappel</span></td>
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
                    <!-- / Contenu du Dashboard -->

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
