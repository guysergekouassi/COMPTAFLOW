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
                        <h4 class="fw-bold py-3 mb-4">
                            <span class="text-muted fw-light">Super Admin /</span> Tableau de Bord Global
                        </h4>

                    {{-- BLOC DE MESSAGE DE SUCCÈS À AJOUTER ICI --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <script>
                            // Script pour masquer l'alerte automatiquement après 5 secondes
                            document.addEventListener('DOMContentLoaded', function() {
                                const successAlert = document.getElementById('successAlert');
                                if (successAlert) {
                                    setTimeout(() => {
                                        const alert = bootstrap.Alert.getInstance(successAlert) || new bootstrap.Alert(successAlert);
                                        alert.close();
                                    }, 5000); // 5000 ms = 5 secondes
                                }
                            });
                        </script>
                    @endif
                    {{-- FIN BLOC DE MESSAGE DE SUCCÈS --}}
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Erreur :</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                    <div class="row mb-5">
                    {{-- ... le reste de votre contenu ... --}}
                        <div class="row mb-5">
                            <div class="col-12">
                                <div class="card bg-primary text-white shadow-lg">
                                    <div class="d-flex align-items-center row">
                                        <div class="col-sm-7">
                                            <div class="card-body">
                                                <h5 class="card-title text-white mb-3">Tableau de Bord Super Administrateur</h5>
                                                <p class="mb-4 text-white-50">
                                                    Contrôle global des entités et des utilisateurs.
                                                </p>
                                                {{-- Nous retirons le bouton de lien ici car la liste est directement en dessous --}}
                                            </div>
                                        </div>
                                        <div class="col-sm-5 text-center text-sm-end">
                                            <div class="card-body pb-0 px-0 px-md-4">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-2">

                            {{-- KPI 1: Total Compagnies --}}
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0"><i class="bx bx-building bx-lg text-success"></i></div>
                                            <div class="d-flex align-items-center gap-1"><span class="text-success small fw-medium">Total</span></div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Nombre total de Compagnies</small>
                                            <h4 class="mb-0">{{ number_format($totalCompanies ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KPI 2: Compagnies Actives --}}
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-danger">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0"><i class="bx bx-check-shield bx-lg text-danger"></i></div>
                                            <div class="d-flex align-items-center gap-1"><span class="text-danger small fw-medium">Actives</span></div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Compagnies Actives</small>
                                            <h4 class="mb-0">{{ number_format($activeCompanies ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KPI 3: Total Admins de Compagnies --}}
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0"><i class="bx bx-user-check bx-lg text-info"></i></div>
                                            <div class="d-flex align-items-center gap-1"><span class="text-info small fw-medium">Admins</span></div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Admins de Compagnie</small>
                                            <h4 class="mb-0">{{ number_format($totalAdmins ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KPI 4: Total Utilisateurs Comptables (Example) --}}
                            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                                <div class="card shadow-sm border border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="avatar flex-shrink-0"><i class="bx bx-group bx-lg text-warning"></i></div>
                                            <div class="d-flex align-items-center gap-1"><span class="text-warning small fw-medium">Total</span></div>
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted fw-semibold">Total Utilisateurs </small>
                                            <h4 class="mb-0">{{ number_format($totalUsers ?? 0) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <hr class="my-3 ">
                        <div class="container-xxl flex-grow-1 container-p-y">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="card shadow">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="m-0">Liste et Gestion des Compagnies</h5>
                                             <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
                                                    <i class="bx bx-buildings me-1"></i> Créer Compagnie/Admin
                                            </button>

                                        </div>
                                        <div class="table-responsive text-nowrap">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Nom de la Compagnie</th>
                                                        <th>Admin Associé</th>
                                                        <th>Statut</th>
                                                        <th>Total Utilisateurs</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="table-border-bottom-0">
    @forelse ($companies as $company)
        {{-- Ligne PRINCIPALE (Master Row) --}}
        {{-- Utilisez l'ID unique du parent pour cibler l'effondrement --}}
        <tr class="accordion-toggle collapsed"
            data-bs-toggle="collapse"
            data-bs-target="#subcompany-details-{{ $company->id }}"
            aria-expanded="false"
            aria-controls="subcompany-details-{{ $company->id }}"
            style="cursor: pointer;">
            <td>
                {{-- Ajout d'un indicateur visuel (optionnel) --}}
                <i class="bx bxs-chevron-right me-2 toggle-icon" style="transition: transform 0.3s;"></i>
                <i class="bx bx-buildings me-2"></i>
                <strong>{{ $company->company_name }}</strong>
            </td>
            <td>
                @php
                    // Cherche l'administrateur de cette compagnie
                    $admin = $company->admin;
                @endphp
                {{ $admin ? $admin->name . ' ' . $admin->last_name : 'N/A' }}
            </td>
            <td>
                @if ($company->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-danger">Inactive</span>
                @endif
            </td>
            <td><span class="badge bg-label-info">{{ $company->users->count() }}</span></td>
            <td>
                 {{-- Maintenez votre dropdown d'actions ici --}}
                 <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                    <div class="dropdown-menu">
                        {{-- Bouton Activer/Désactiver --}}
                        <form action="{{ route('toggle', $company->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="dropdown-item text-{{ $company->is_active ? 'danger' : 'success' }}">
                                <i class="bx {{ $company->is_active ? 'bx-block' : 'bx-check' }} me-1"></i>
                                {{ $company->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <a class="dropdown-item" href="javascript:void(0);"
                            data-bs-toggle="modal"
                            data-bs-target="#editCompanyModal{{ $company->id }}">
                                <i class="bx bx-edit-alt me-1"></i> Modifier Infos
                        </a>

                                                <form action="{{ route('companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la compagnie **{{ $company->company_name }}** ? Cette action est irréversible et supprimera également toutes les données associées (utilisateurs, sous-compagnies, etc.) si votre modèle le permet.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bx bx-trash me-1"></i> Supprimer
                                                    </button>
                                                </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>


        {{-- Cible : l'ID doit correspondre au data-bs-target de la ligne parente --}}
        <tr id="subcompany-details-{{ $company->id }}" class="collapse">
            <td colspan="5"> {{-- Étendez sur toutes les colonnes --}}
                <div class="p-3 bg-light border-start border-4 border-primary rounded shadow-sm">
                    <h6 class="mb-3 text-primary">Sous-Comptabilité de **{{ $company->company_name }}**</h6>

                    {{-- 🚨 VÉRIFICATION CRITIQUE : Utiliser la relation 'children' --}}
                    @if ($company->children && $company->children->count() > 0)
                        <ul class="list-unstyled mb-0 small">
                            @foreach ($company->children as $subCompany)
                                <li class="mb-1 p-2 rounded bg-white border">
                                    <i class="bx bx-subdirectory-right me-1 text-muted"></i>
                                    <strong>{{ $subCompany->company_name }}</strong>
                                    <span class="badge bg-label-{{ $subCompany->is_active ? 'success' : 'danger' }} ms-3">
                                        {{ $subCompany->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    {{-- <span class="text-muted ms-3">ID: {{ $subCompany->id }}</span> --}}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">
                            <i class="bx bx-info-circle me-1"></i> Aucune sous-compagnie associée pour l'instant.
                        </p>
                    @endif
                </div>
            </td>
        </tr>
     <div class="modal fade" id="editCompanyModal{{ $company->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier : {{ $company->company_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.companies.update', $company->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-4">
                        {{-- SECTION COMPAGNIE --}}
                        <div class="col-12"><h6 class="border-bottom pb-2 text-primary"><i class="bx bx-buildings me-2"></i>Infos Compagnie</h6></div>

                        <div class="col-md-4">
                            <label class="form-label">Nom de l'entreprise</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $company->company_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Forme Juridique</label>
                            <input type="text" name="juridique_form" class="form-control" value="{{ $company->juridique_form }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Activité</label>
                            <input type="text" name="activity" class="form-control" value="{{ $company->activity }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capital Social</label>
                            <input type="number" name="social_capital" class="form-control" value="{{ $company->social_capital }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ville</label>
                            <input type="text" name="city" class="form-control" value="{{ $company->city }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Identification TVA</label>
                            <input type="text" name="identification_TVA" class="form-control" value="{{ $company->identification_TVA }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="{{ $company->adresse }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Code Postal</label>
                            <input type="text" name="code_postal" class="form-control" value="{{ $company->code_postal }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Pays</label>
                            <input type="text" name="country" class="form-control" value="{{ $company->country }}" required>
                        </div>

                        {{-- SECTION ADMIN --}}
                        <div class="col-12 mt-4"><h6 class="border-bottom pb-2 text-primary"><i class="bx bx-user me-2"></i>Infos Administrateur</h6></div>

                        <div class="col-md-6">
                            <label class="form-label">Nom (Admin)</label>
                            <input type="text" name="admin_name" class="form-control" value="{{ $company->admin->name ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom (Admin)</label>
                            <input type="text" name="admin_last_name" class="form-control" value="{{ $company->admin->last_name ?? '' }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Email (Admin)</label>
                            <input type="email" name="admin_email_adresse" class="form-control" value="{{ $company->admin->email_adresse ?? '' }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nouveau mot de passe <small class="text-muted">(Laisser vide si inchangé)</small></label>
                            <input type="password" name="admin_password" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmer mot de passe</label>
                            <input type="password" name="admin_password_confirmation" class="form-control">
                        </div>

                        {{-- SECTION HABILITATIONS --}}
                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 text-primary"><i class="bx bx-shield-quarter me-2"></i>Habilitations de l'Administrateur</h6>
                        </div>

                        <div class="col-12">
                            <div class="row">
                                @php
                                    $habilitations = [
                                        'dashboard', 'plan_comptable', 'plan_tiers', 'journaux', 'tresorerie',
                                        'grand_livre','balance','etats_financiers', 'fichier_joindre',
                                        'parametre','accounting_journals','exercice_comptable',
                                        'Etat de rapprochement bancaire', 'Gestion de la trésorerie',
                                        'gestion_analytique', 'gestion_tiers','user_management',
                                        'gestion_immobilisations','gestion_reportings','gestion_stocks','grand_livre_tiers',
                                        'poste','Balance_Tiers','modal_saisie_direct'
                                    ];

                                    // Récupération des habilitations actuelles de l'admin
                                    $currentHabilitations = $company->admin && is_array($company->admin->habilitations)
                                        ? $company->admin->habilitations
                                        : [];
                                @endphp

                                @foreach ($habilitations as $habilitation)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            @php
                                                // Remplacez cette ligne dans votre boucle d'habilitations
                                                $uniqueId = 'hab_' . $company->id . '_' . \Illuminate\Support\Str::slug($habilitation);
                                            @endphp
                                            <input class="form-check-input" type="checkbox"
                                                id="{{ $uniqueId }}"
                                                name="habilitations[{{ $habilitation }}]"
                                                value="1"
                                                {{ (isset($currentHabilitations[$habilitation]) && $currentHabilitations[$habilitation]) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $uniqueId }}">
                                                {{ ucfirst(str_replace('_', ' ', $habilitation)) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>
    @empty
        <tr>
            <td colspan="5" class="text-center">Aucune compagnie principale n'a encore été créée.</td>
        </tr>

    @endforelse
</tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                        </div>
                       {{-- ///////////// --}}


                            <hr>


                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    {{-- INCLUSION DU MODAL DE CRÉATION DE COMPAGNIE --}}


    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Vos scripts Chart.js et ApexCharts restent ici) ...

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
                            data: [12000, 15000, 18000, 22000, 25000, 28000, 26000, 31000, 35000, 32000, 38000, 42000], // Données réelles à passer
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
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.formattedValue + ' €';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { drawBorder: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // ===================================
            // CHART 2: Répartition des Dépenses (Donut - ApexCharts) - Utilisé ici pour la répartition des entités
            // ===================================
            const expensesChartEl = document.querySelector('#expensesChart');
            if (expensesChartEl) {
                const expensesChartOptions = {
                    series: [50, 30, 20], // Exemple: Actives, Inactives, En cours
                    labels: ['Actives (50%)', 'Inactives (30%)', 'En Attente (20%)'], // Labels personnalisés
                    chart: {
                        height: 300,
                        type: 'donut',
                        toolbar: { show: false }
                    },
                    legend: { position: 'bottom' },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: { show: true, fontSize: '1rem', color: '#adb5bd', offsetY: -10 },
                                    value: { show: true, fontSize: '1.5rem', fontWeight: 'bold', color: '#344050', offsetY: 10, formatter: function(val) { return val + '%' } },
                                    total: { show: true, label: 'Total', formatter: function(w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + '%'; } }
                                }
                            }
                        }
                    },
                    colors: ['#00a76f', '#ff4d4f', '#ffc107'], // Couleurs: Vert, Rouge, Jaune
                    dataLabels: { enabled: false }
                };

                const expensesChart = new ApexCharts(expensesChartEl, expensesChartOptions);
                expensesChart.render();
            }

            // Logique pour réafficher le modal en cas d'erreur de validation
            @if ($errors->any())
                const myModal = new bootstrap.Modal(document.getElementById('createCompanyModal'));
                myModal.show();
            @endif

        });
    </script>




                   <div class="modal fade" id="createCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCompanyModalTitle">
                    Créer une Nouvelle Compagnie et son Administrateur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Fermer"></button>
            </div>
            {{-- La route doit pointer vers le Controller qui gère les deux créations --}}
            <form id="createCompanyForm" method="POST" action="{{ route('companies.store') }}" novalidate>
                @csrf
                <div class="modal-body">
                    @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <p>Veuillez corriger les erreurs suivantes :</p>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                    <div class="row g-4">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Informations sur la Compagnie</h6>
                        </div>
                        {{-- Champs de la Compagnie --}}
                        <div class="col-md-4">
                            <label for="company_name" class="form-label">Nom de l'entreprise</label>
                            <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="juridique_form" class="form-label">Forme Juridique</label>
                            <input type="text" id="juridique_form" name="juridique_form" class="form-control" value="{{ old('juridique_form') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="activity" class="form-label">Activité</label>
                            <input type="text" id="activity" name="activity" class="form-control" value="{{ old('activity') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="social_capital" class="form-label">Capital Social</label>
                            <input type="text" id="social_capital" name="social_capital" class="form-control" value="{{ old('social_capital') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="adresse" class="form-label">Adresse</label>
                            <input type="text" id="adresse" name="adresse" class="form-control" value="{{ old('adresse') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="code_postal" class="form-label">Code Postal</label>
                            <input type="text" id="code_postal" name="code_postal" class="form-control" value="{{ old('code_postal') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label">Pays</label>
                            <input type="text" id="country" name="country" class="form-control" value="{{ old('country') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="phone_number" class="form-label">Téléphone</label>
                            <input type="text" id="phone_number" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
                        </div>
                        {{-- <div class="col-md-4">
                            <label for="email_adresse_company" class="form-label">Adresse e-mail (Compagnie)</label>
                            <input type="email" id="email_adresse_company" name="email_adresse_company" class="form-control" value="{{ old('email_adresse_company') }}">
                        </div> --}}
                        <div class="col-md-4">
                            <label for="identification_TVA" class="form-label">Identification TVA</label>
                            <input type="text" id="identification_TVA" name="identification_TVA" class="form-control" value="{{ old('identification_TVA') }}" placeholder="Ex:CI**************">
                        </div>


                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 mb-3">Informations sur l'Administrateur</h6>
                        </div>

                        {{-- Champs de l'Administrateur --}}
                        <div class="col-md-3">
                            <label for="name" class="form-label">Nom (Admin)</label>
                            <input type="text" id="name" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="last_name" class="form-label">Prénom (Admin)</label>
                            <input type="text" id="last_name" name="admin_last_name" class="form-control" value="{{ old('admin_last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_email_adresse" class="form-label">Email (Admin)</label>
                            <input type="email" id="admin_email_adresse" name="admin_email_adresse" class="form-control" value="{{ old('admin_email_adresse') }}" required>
                            <div class="invalid-feedback" id="errorAdminEmail"></div>
                            @error('admin_email_adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                      </div>
                        <div class="col-md-6">
                            <label for="admin_password" class="form-label">Mot de passe (Admin)</label>
                            <input type="password" id="admin_password" name="admin_password" class="form-control" required>
                            <div class="form-text">8 caractères minimum, une majuscule, un chiffre.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe (Admin)</label>
                            <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" class="form-control" required>
                        </div>

                        {{-- CHAMP RÔLE FIXE POUR LA LOGIQUE (Admin est implicite) --}}
                        {{-- On utilise un champ caché pour définir le rôle à 'admin' ou le rendre sélectif si besoin --}}
                        <input type="hidden" name="role" value="admin">

                        {{-- Bloc Habilitations (optionnel si le rôle est fixé à 'admin', car l'admin a tout) --}}
                        {{-- Je le laisse ici si vous voulez permettre de créer un 'comptable' directement avec la compagnie --}}

                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2 mb-3">Habilitations de l'Administrateur</h6>

                            <div class="row">
                                @php
                                    $habilitations = [

                                        'dashboard', 'plan_comptable', 'plan_tiers', 'journaux', 'tresorerie',
                                        'grand_livre','balance','etats_financiers', 'fichier_joindre',
                                        'parametre','accounting_journals','exercice_comptable',
                                        'Etat de rapprochement bancaire', 'Gestion de la trésorerie',
                                        'gestion_analytique', 'gestion_tiers','user_management',
                                        'gestion_immobilisations','gestion_reportings','gestion_stocks','grand_livre_tiers'
                                            ,'poste','Balance_Tiers'
                                    ];


                                @endphp

                                @foreach ($habilitations as $habilitation)
                                    <div class="mb-2 col-md-4">
                                        <div class="form-check">
                                            @php
                                                $input_id = 'company_admin_' . str_replace([' ', '_'], '', strtolower($habilitation));
                                                $input_name = 'habilitations[' . $habilitation . ']';
                                            @endphp
                                            {{-- Par défaut, l'administrateur a tout, donc "checked" --}}
                                            <input class="form-check-input" type="checkbox"
                                                id="{{ $input_id }}"
                                                name="{{ $input_name }}" value="1" checked disabled>
                                            <label class="form-check-label"
                                                for="{{ $input_id }}">
                                                {{ ucfirst(str_replace('_', ' ', $habilitation)) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        Fermer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Créer Compagnie et Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.accordion-toggle').forEach(row => {
            row.addEventListener('click', function() {
                const icon = this.querySelector('.toggle-icon');
                const targetId = this.getAttribute('data-bs-target');
                const targetEl = document.querySelector(targetId);

                // Petite astuce pour attendre que Bootstrap ait fait son travail
                setTimeout(() => {
                    // La classe 'show' est ajoutée par Bootstrap lorsque l'élément est ouvert
                    if (targetEl.classList.contains('show')) {
                        icon.style.transform = 'rotate(90deg)';
                    } else {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }, 150);
            });
        });
    });
</script>


</body>
</html>
