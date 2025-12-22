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
                        <!-- Table 1: Comptes Comptabilités -->
                        <div class="col-12">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="d-flex justify-content-between align-items-center mb-6">
                                    <h5 class="text-lg fw-semibold text-gray-900 mb-0">Liste des Comptes Comptabilités</h5>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateComptaAccount">Créer un Compte</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nom de l'Entreprise</th>
                                                <th>Forme Juridique</th>
                                                <th>Activité</th>
                                                <th>Ville</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            @forelse ($comptaAccounts ?? [] as $comptaAccount)
                                                <tr class="clickable-row" data-href="{{ route('compta_accounts.access', ['companyId' => $comptaAccount->id]) }}" style="cursor: pointer;">
                                                    <td>
                                                        <i class="bx bx-buildings me-2"></i>
                                                        <strong>{{ $comptaAccount->company_name }}</strong>
                                                    </td>
                                                    <td>{{ $comptaAccount->juridique_form ?? 'N/A' }}</td>
                                                    <td>{{ $comptaAccount->activity ?? 'N/A' }}</td>
                                                    <td>{{ $comptaAccount->city ?? 'N/A' }}</td>
                                                    <td>
                                                        @if ($comptaAccount->is_active)
                                                            <span class="badge bg-label-success me-1">Actif</span>
                                                        @else
                                                            <span class="badge bg-label-danger me-1">Inactif</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="{{ route('compta_accounts.index') }}">
                                                                    <i class="bx bx-show me-1"></i> Détails
                                                                </a>
                                                                <a class="dropdown-item" href="{{ route('compta_accounts.index') }}">
                                                                    <i class="bx bx-edit-alt me-1"></i> Éditer
                                                                </a>
                                                                <a class="dropdown-item text-danger" href="#" onclick="confirmDelete({{ $comptaAccount->id }})">
                                                                    <i class="bx bx-trash me-1"></i> Supprimer
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Aucun compte comptabilité trouvé.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal for creating compta account -->
                <div class="modal fade" id="modalCreateComptaAccount" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Créer un nouveau Compte Comptabilité</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <form id="createAccountForm" method="POST" action="{{ route('compta_accounts.store') }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="company_name" class="form-label">Nom de la Société <span class="text-danger">*</span></label>
                                            <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name') }}" required />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="activity" class="form-label">Secteur d'activité</label>
                                            <input type="text" id="activity" name="activity" class="form-control" value="{{ old('activity') }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="juridique_form" class="form-label">Forme Juridique</label>
                                            <input type="text" id="juridique_form" name="juridique_form" class="form-control" value="{{ old('juridique_form') }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="social_capital" class="form-label">Capital Social</label>
                                            <input type="number" step="0.01" id="social_capital" name="social_capital" class="form-control" value="{{ old('social_capital') }}" />
                                        </div>
                                        <div class="col-12">
                                            <label for="adresse" class="form-label">Adresse Complète</label>
                                            <input type="text" id="adresse" name="adresse" class="form-control" value="{{ old('adresse') }}" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="code_postal" class="form-label">Code Postal</label>
                                            <input type="text" id="code_postal" name="code_postal" class="form-control" value="{{ old('code_postal') }}" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="city" class="form-label">Ville</label>
                                            <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="country" class="form-label">Pays</label>
                                            <input type="text" id="country" name="country" class="form-control" value="{{ old('country') }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email_adresse_create" class="form-label">Email de contact <span class="text-danger">*</span></label>
                                            <input type="email" id="email_adresse_create" name="email_adresse" class="form-control" value="{{ old('email_adresse') }}" required />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone_number" class="form-label">Numéro de Téléphone</label>
                                            <input type="text" id="phone_number" name="phone_number" class="form-control" value="{{ old('phone_number') }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="identification_TVA" class="form-label">Identification TVA</label>
                                            <input type="text" id="identification_TVA" name="identification_TVA" class="form-control" value="{{ old('identification_TVA') }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="is_active_create" class="form-label">Statut</label>
                                            <select id="is_active_create" name="is_active" class="form-select">
                                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Actif</option>
                                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer justify-content-end mt-4">
                                        <button type="button" class="btn btn-label-secondary"
                                            data-bs-dismiss="modal">Fermer</button>
                                        <button type="submit" class="btn btn-primary">
                                            Enregistrer le Compte
                                        </button>
                                    </div>
                                </form>
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

            // --- Logique pour la LIGNE CLICABLE ---
             document.querySelectorAll('.clickable-row').forEach(row => {
                const rowDataHref = row.getAttribute('data-href');

                // Gérer le clic sur la ligne entière (y compris les cellules TD)
                row.addEventListener('click', function(e) {
                    // Clic sur un bouton d'action ou un lien dans la dernière colonne (Actions)
                    if (e.target.closest('.dropdown') || e.target.closest('a')) {
                        // Ne rien faire si l'utilisateur clique sur le menu déroulant ou une action
                        return;
                    }
                    if (rowDataHref) {
                        window.location.href = rowDataHref;
                    }
                });
            });

            // Function for delete confirmation
            window.confirmDelete = function(id) {
                if (confirm('Êtes-vous sûr de vouloir supprimer ce compte ?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/compta-accounts/${id}`;
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrf);
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);
                    document.body.appendChild(form);
                    form.submit();
                }
            };
        });
    </script>
</body>
</html>
