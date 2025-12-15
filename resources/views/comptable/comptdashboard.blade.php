<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<style>
    /* Effet hover sur les cartes */
    .hover-lift {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    /* Gradient pour la bannière */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    /* Animation des avatars */
    .avatar i {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => $habilitations])

            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        {{-- 1. BANNIÈRE DE BIENVENUE PERSONNALISÉE --}}
                        <div class="card bg-gradient-primary text-white shadow-lg mb-4">
                            <div class="row align-items-center g-0">
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h4 class="text-white mb-2">
                                            👋 Bonjour, {{ $user->name }} {{ $user->last_name }}
                                        </h4>
                                        <p class="text-white-75 mb-3" style="opacity: 0.9;">
                                            <i class="bx bx-calendar me-1"></i> {{ now()->isoFormat('dddd D MMMM YYYY') }}
                                            <span class="mx-2">•</span>
                                            <i class="bx bx-time me-1"></i> Dernière connexion : {{ $userStats['last_login']->diffForHumans() }}
                                        </p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('modal_saisie_direct') }}" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#saisieRedirectModal">
                                                <i class="bx bx-plus-circle me-1"></i>Nouvelle Saisie
                                            </a>
                                            <a href="{{ route('accounting_entry_real') }}" class="btn btn-outline-light btn-sm">
                                                <i class="bx bx-book me-1"></i>Voir Écritures
                                            </a>
                                            <a href="{{ route('profile') }}" class="btn btn-outline-light btn-sm">
                                                <i class="bx bx-user me-1"></i>Mon Profil
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center d-none d-md-block">
                                    <div class="card-body pb-0">
                                        <img src="../assets/img/illustrations/man-with-laptop.png" height="150" alt="Dashboard" style="transform: scaleX(-1);" />
                                    </div>
                                </div>
                            </div>
                        </div>



                        {{-- 3. KPIs COMPTABLES (6 Cartes) --}}
                        <div class="row g-4 mb-4">
                            <!-- KPI 1 : Trésorerie -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-start border-success border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted fw-semibold">TRÉSORERIE ACTUELLE</small>
                                                <h4 class="mb-0 mt-2 text-success">
                                                    {{ number_format($comptaStats['solde_tresorerie'], 0, ',', ' ') }} FCFA
                                                </h4>
                                                <span class="badge bg-label-success mt-2">
                                                    <i class="bx bx-trending-up"></i> {{ $comptaStats['tresorerie_variation'] }}
                                                </span>
                                            </div>
                                            <div class="avatar avatar-lg bg-success">
                                                <i class="bx bx-wallet bx-lg text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 2 : Revenus du mois -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-start border-primary border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted fw-semibold">REVENUS DU MOIS</small>
                                                <h4 class="mb-0 mt-2 text-primary">
                                                    {{ number_format($comptaStats['revenus_mois'], 0, ',', ' ') }} FCFA
                                                </h4>
                                                <span class="badge bg-label-primary mt-2">
                                                    {{ $comptaStats['revenus_variation'] }}
                                                </span>
                                            </div>
                                            <div class="avatar avatar-lg bg-primary">
                                                <i class="bx bx-trending-up bx-lg text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 3 : Dépenses du mois -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-start border-danger border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted fw-semibold">DÉPENSES DU MOIS</small>
                                                <h4 class="mb-0 mt-2 text-danger">
                                                    {{ number_format($comptaStats['depenses_mois'], 0, ',', ' ') }} FCFA
                                                </h4>
                                                <span class="badge bg-label-danger mt-2">
                                                    {{ $comptaStats['depenses_variation'] }}
                                                </span>
                                            </div>
                                            <div class="avatar avatar-lg bg-danger">
                                                <i class="bx bx-trending-down bx-lg text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 4 : Pièces saisies -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-start border-info border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted fw-semibold">PIÈCES SAISIES</small>
                                                <h4 class="mb-0 mt-2 text-info">
                                                    {{ $comptaStats['pieces_saisies_mois'] }}
                                                </h4>
                                                <small class="text-muted">ce mois</small>
                                            </div>
                                            <div class="avatar avatar-lg bg-info">
                                                <i class="bx bx-file bx-lg text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 5 : Comptes Tiers -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-start border-warning border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted fw-semibold">COMPTES TIERS</small>
                                                <h4 class="mb-0 mt-2 text-warning">
                                                    {{ $comptaStats['total_tiers'] }}
                                                </h4>
                                                <small class="text-muted">actifs</small>
                                            </div>
                                            <div class="avatar avatar-lg bg-warning">
                                                <i class="bx bx-group bx-lg text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KPI 6 : Exercice en cours -->
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-start border-secondary border-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <small class="text-muted fw-semibold">EXERCICE EN COURS</small>
                                                <h6 class="mb-0 mt-2">
                                                    {{ $comptaStats['exercice_actuel']->libelle ?? 'Aucun exercice actif' }}
                                                </h6>
                                                <small class="text-muted">
                                                    @if($comptaStats['jours_restants'] > 0)
                                                        {{ intval($comptaStats['jours_restants']) }} jours restants
                                                    @else
                                                        Exercice terminé
                                                    @endif
                                                </small>
                                            </div>
                                            <div class="avatar avatar-lg bg-secondary">
                                                <i class="bx bx-calendar-event bx-lg text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. GRAPHIQUES DYNAMIQUES --}}
                        <div class="row g-4 mb-4">
                            <!-- Graphique 1 : Évolution Revenus (Ligne) -->
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <h5 class="mb-0">
                                            <i class="bx bx-line-chart me-2"></i>Évolution des Revenus (Année {{ now()->year }})
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="revenusChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>

                            <!-- Graphique 2 : Revenus vs Dépenses (Donut) -->
                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bx bx-pie-chart me-2"></i>Revenus vs Dépenses
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="revenuDepenseChart"></div>
                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Revenus</span>
                                                <span class="fw-semibold text-success">
                                                    {{ number_format($comptaStats['revenus_annee'], 0, ',', ' ') }} FCFA
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Dépenses</span>
                                                <span class="fw-semibold text-danger">
                                                    {{ number_format($comptaStats['depenses_annee'], 0, ',', ' ') }} FCFA
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 5. ACCÈS RAPIDES --}}
                        <div class="row g-4 mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="bx bx-zap me-2"></i>Accès Rapides
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            @if(in_array('plan_comptable', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('plan_comptable') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-list-ul me-2 fs-4"></i>
                                                    <span>Plan Comptable</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('plan_tiers', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('plan_tiers') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-group me-2 fs-4"></i>
                                                    <span>Plan Tiers</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('accounting_journals', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('accounting_journals') }}" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-book me-2 fs-4"></i>
                                                    <span>Journaux</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('gestion_tresorerie', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('gestion_tresorerie') }}" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-wallet me-2 fs-4"></i>
                                                    <span>Trésorerie</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('accounting_ledger', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('accounting_ledger') }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-book-open me-2 fs-4"></i>
                                                    <span>Grand Livre</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('accounting_balance', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('accounting_balance') }}" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-balance-scale me-2 fs-4"></i>
                                                    <span>Balance</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('exercice_comptable', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('exercice_comptable') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-calendar-event me-2 fs-4"></i>
                                                    <span>Exercices</span>
                                                </a>
                                            </div>
                                            @endif

                                            @if(in_array('indextresorerie', $habilitations))
                                            <div class="col-lg-3 col-md-4 col-sm-6">
                                                <a href="{{ route('indextresorerie') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center" style="height: 60px;">
                                                    <i class="bx bx-dollar-circle me-2 fs-4"></i>
                                                    <span>Journal Trésorerie</span>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
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

    <!-- Scripts pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique 1: Évolution des Revenus (Ligne)
            const ctxRevenus = document.getElementById('revenusChart');
            if (ctxRevenus) {
                new Chart(ctxRevenus, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                        datasets: [{
                            label: 'Revenus Mensuels (FCFA)',
                            data: @json($comptaStats['revenus_mensuels']),
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
                                        return context.dataset.label + ': ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
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

            // Graphique 2: Revenus vs Dépenses (Donut)
            const revenuDepenseChartEl = document.querySelector('#revenuDepenseChart');
            if (revenuDepenseChartEl && typeof ApexCharts !== 'undefined') {
                const revenuDepenseChartOptions = {
                    series: [{{ $comptaStats['revenus_annee'] }}, {{ $comptaStats['depenses_annee'] }}],
                    labels: ['Revenus', 'Dépenses'],
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
                                            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA'
                                        }
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function(w) {
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                            return total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA';
                                        }
                                    }
                                }
                            }
                        }
                    },
                    colors: ['#00a76f', '#ff4d4f'],
                    dataLabels: {
                        enabled: false,
                    }
                };

                const revenuDepenseChart = new ApexCharts(revenuDepenseChartEl, revenuDepenseChartOptions);
                revenuDepenseChart.render();
            }
        });
    </script>
</body>
</html>
