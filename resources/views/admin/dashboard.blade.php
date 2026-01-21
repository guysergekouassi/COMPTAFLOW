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
                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px); background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                    <!-- Welcome Section -->
                    <div class="mb-8">
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Bienvenue sur votre Dashboard Admin</h1>
                                    <p class="text-gray-600">Gérez vos comptes comptabilité et suivez les performances de votre entreprise.</p>
                                </div>
                                <div class="hidden md:block">
                                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-chart-line text-white text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="w-full overflow-x-auto pb-4">
                        <div id="stats-section" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 min-w-max">
                            <!-- KPI 1: Utilisateurs rattachés -->
                            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 w-full min-w-[260px]">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                                        <i class="fa-solid fa-users text-white text-xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Équipe</span>
                                    </div>
                                </div>
                                <h6 class="text-gray-500 font-medium text-sm mb-1">Membres rattachés</h6>
                                <h3 class="text-3xl font-bold text-gray-900 mb-0">{{ number_format($totalUsers ?? 0) }}</h3>
                            </div>

                            <!-- KPI 2: En Ligne -->
                            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 w-full min-w-[260px]">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                                        <i class="fa-solid fa-signal text-white text-xl"></i>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Live</span>
                                    </div>
                                </div>
                                <h6 class="text-gray-500 font-medium text-sm mb-1">Collaborateurs actifs</h6>
                                <h3 class="text-3xl font-bold text-gray-900 mb-0">{{ number_format($connectedUsers ?? 0) }}</h3>
                            </div>

                            <!-- KPI 3: Volume Écritures -->
                            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 w-full min-w-[260px]">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-200">
                                        <i class="fa-solid fa-database text-white text-xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Volume</span>
                                    </div>
                                </div>
                                <h6 class="text-gray-500 font-medium text-sm mb-1">Total des écritures</h6>
                                <h3 class="text-3xl font-bold text-gray-900 mb-0">{{ number_format($totalEntries ?? 0) }}</h3>
                            </div>

                            <!-- KPI 4: Activité du jour -->
                            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 w-full min-w-[260px]">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                                        <i class="fa-solid fa-bolt text-white text-xl"></i>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Aujourd'hui</span>
                                    </div>
                                </div>
                                <h6 class="text-gray-500 font-medium text-sm mb-1">Saisies réalisées</h6>
                                <h3 class="text-3xl font-bold text-gray-900 mb-0">{{ number_format($entriesToday ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Chart: Team Activity -->
                        <div class="lg:col-span-2 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h5 class="text-xl font-semibold text-gray-900">Performance de l'équipe</h5>
                                    <p class="text-gray-500 text-sm">Volume de saisie par collaborateur (Mois en cours)</p>
                                </div>
                                <i class="fa-solid fa-ellipsis-vertical text-gray-400 cursor-pointer"></i>
                            </div>
                            <div class="h-80">
                                <canvas id="teamPerformanceChart"></canvas>
                            </div>
                        </div>

                        <!-- Team List Mini -->
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                            <h5 class="text-xl font-semibold text-gray-900 mb-6">Collaborateurs Proactifs</h5>
                            <div class="space-y-4">
                                @forelse ($teamStats->sortByDesc('ecritures_count')->take(5) as $member)
                                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold mr-3">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $member->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $member->role }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-gray-900">{{ $member->ecritures_count }}</div>
                                            <div class="text-xs text-gray-400">écritures</div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-500 py-4">Aucun collaborateur trouvé.</p>
                                @endforelse
                            </div>
                            <a href="{{ route('user_management') }}" class="block text-center mt-6 text-blue-600 hover:text-blue-800 text-sm font-medium">Voir toute l'équipe</a>
                        </div>
                    </div>



                    <!-- Tables Section -->
                    <div id="tables-section" class="grid grid-cols-1 gap-6 mb-8">
                        <!-- Table 1: Comptes Comptabilités -->
                        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h5 class="text-xl font-semibold text-gray-900 mb-1">Comptes Comptabilités</h5>
                                    <p class="text-gray-600 text-sm">Gérez vos comptes d'entreprise</p>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateComptaAccount">
                                    <i class="fa-solid fa-plus me-2"></i>Créer un Compte
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full table-auto">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Entreprise</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Forme Juridique</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Activité</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Ville</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Statut</th>
                                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($comptaAccounts ?? [] as $comptaAccount)
                                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                                            <i class="fa-solid fa-building text-white text-xs"></i>
                                                        </div>
                                                        <span class="font-medium text-gray-900">{{ $comptaAccount->company_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 text-gray-600">{{ $comptaAccount->juridique_form ?? 'N/A' }}</td>
                                                <td class="py-4 px-4 text-gray-600">{{ $comptaAccount->activity ?? 'N/A' }}</td>
                                                <td class="py-4 px-4 text-gray-600">{{ $comptaAccount->city ?? 'N/A' }}</td>
                                                <td class="py-4 px-4">
                                                    @if ($comptaAccount->is_active)
                                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">Actif</span>
                                                    @else
                                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">Inactif</span>
                                                    @endif
                                                </td>
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center space-x-2">
                                                        <a href="{{ route('compta.switch', $comptaAccount->id) }}" class="text-purple-600 hover:text-purple-800 p-1 rounded-lg hover:bg-purple-50 transition-colors duration-200" title="Accéder au compte">
                                                            <i class="fa-solid fa-sign-in-alt"></i>
                                                        </a>
                                                        <button class="text-blue-600 hover:text-blue-800 p-1 rounded-lg hover:bg-blue-50 transition-colors duration-200" title="Détails">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </button>
                                                        <button class="text-green-600 hover:text-green-800 p-1 rounded-lg hover:bg-green-50 transition-colors duration-200 edit-btn" title="Éditer" data-bs-toggle="modal" data-bs-target="#modalUpdateComptaAccount"
                                                            data-account-id="{{ $comptaAccount->id }}"
                                                            data-company-name="{{ $comptaAccount->company_name }}"
                                                            data-activity="{{ $comptaAccount->activity }}"
                                                            data-juridique-form="{{ $comptaAccount->juridique_form }}"
                                                            data-social-capital="{{ $comptaAccount->social_capital }}"
                                                            data-adresse="{{ $comptaAccount->adresse }}"
                                                            data-code-postal="{{ $comptaAccount->code_postal }}"
                                                            data-city="{{ $comptaAccount->city }}"
                                                            data-country="{{ $comptaAccount->country }}"
                                                            data-phone-number="{{ $comptaAccount->phone_number }}"
                                                            data-email-adresse="{{ $comptaAccount->email_adresse }}"
                                                            data-identification-tva="{{ $comptaAccount->identification_TVA }}"
                                                            data-is-active="{{ $comptaAccount->is_active }}">
                                                            <i class="fa-solid fa-edit"></i>
                                                        </button>
                                                        <button class="text-red-600 hover:text-red-800 p-1 rounded-lg hover:bg-red-50 transition-colors duration-200" title="Supprimer" onclick="confirmDelete({{ $comptaAccount->id }})">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-8 text-gray-500">
                                                    <div class="flex flex-col items-center">
                                                        <i class="fa-solid fa-inbox text-4xl mb-4 text-gray-300"></i>
                                                        <p>Aucun compte comptabilité trouvé.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
                                            <label for="email_adresse_create" class="form-label">Email de contact <span class="text-danger"></span></label>
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

                <!-- Modal for delete confirmation -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirmer la suppression</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir supprimer ce compte comptabilité ? Cette action est irréversible.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for updating compta account -->
                <div class="modal fade" id="modalUpdateComptaAccount" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Modifier le Compte Comptabilité</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body">
                                <form id="updateAccountForm" method="POST" action="">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" id="updateAccountId" />

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="update_company_name" class="form-label">Nom de la Société <span class="text-danger">*</span></label>
                                            <input type="text" id="update_company_name" name="company_name" class="form-control" required />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_activity" class="form-label">Secteur d'activité</label>
                                            <input type="text" id="update_activity" name="activity" class="form-control" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_juridique_form" class="form-label">Forme Juridique</label>
                                            <input type="text" id="update_juridique_form" name="juridique_form" class="form-control" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_social_capital" class="form-label">Capital Social</label>
                                            <input type="number" step="0.01" id="update_social_capital" name="social_capital" class="form-control" />
                                        </div>
                                        <div class="col-12">
                                            <label for="update_adresse" class="form-label">Adresse Complète</label>
                                            <input type="text" id="update_adresse" name="adresse" class="form-control" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="update_code_postal" class="form-label">Code Postal</label>
                                            <input type="text" id="update_code_postal" name="code_postal" class="form-control" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="update_city" class="form-label">Ville</label>
                                            <input type="text" id="update_city" name="city" class="form-control" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="update_country" class="form-label">Pays</label>
                                            <input type="text" id="update_country" name="country" class="form-control" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_email_adresse" class="form-label">Email de contact <span class="text-danger">*</span></label>
                                            <input type="email" id="update_email_adresse" name="email_adresse" class="form-control" required />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_phone_number" class="form-label">Numéro de Téléphone</label>
                                            <input type="text" id="update_phone_number" name="phone_number" class="form-control" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_identification_TVA" class="form-label">Identification TVA</label>
                                            <input type="text" id="update_identification_TVA" name="identification_TVA" class="form-control" />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="update_is_active" class="form-label">Statut</label>
                                            <select id="update_is_active" name="is_active" class="form-select">
                                                <option value="1">Actif</option>
                                                <option value="0">Inactif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer justify-content-end mt-4">
                                        <button type="button" class="btn btn-label-secondary"
                                            data-bs-dismiss="modal">Fermer</button>
                                        <button type="submit" class="btn btn-primary">
                                            Enregistrer les modifications
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
            // CHART: Team Performance (Bar Chart)
            // ===================================
            const ctxTeam = document.getElementById('teamPerformanceChart');
            if (ctxTeam) {
                const teamData = @json($teamStats->map(fn($u) => ['name' => $u->name, 'count' => $u->ecritures_count]));
                
                new Chart(ctxTeam, {
                    type: 'bar',
                    data: {
                        labels: teamData.map(d => d.name),
                        datasets: [{
                            label: 'Nombre d\'écritures',
                            data: teamData.map(d => d.count),
                            backgroundColor: 'rgba(30, 64, 175, 0.8)',
                            borderColor: 'rgb(30, 64, 175)',
                            borderWidth: 1,
                            borderRadius: 8,
                            barThickness: 40
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)'
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

            // Handle edit button
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const accountId = this.getAttribute('data-account-id');
                    const companyName = this.getAttribute('data-company-name');
                    const activity = this.getAttribute('data-activity');
                    const juridiqueForm = this.getAttribute('data-juridique-form');
                    const socialCapital = this.getAttribute('data-social-capital');
                    const adresse = this.getAttribute('data-adresse');
                    const codePostal = this.getAttribute('data-code-postal');
                    const city = this.getAttribute('data-city');
                    const country = this.getAttribute('data-country');
                    const phoneNumber = this.getAttribute('data-phone-number');
                    const emailAdresse = this.getAttribute('data-email-adresse');
                    const identificationTva = this.getAttribute('data-identification-tva');
                    const isActive = this.getAttribute('data-is-active');

                    document.getElementById('updateAccountId').value = accountId;
                    document.getElementById('update_company_name').value = companyName;
                    document.getElementById('update_activity').value = activity;
                    document.getElementById('update_juridique_form').value = juridiqueForm;
                    document.getElementById('update_social_capital').value = socialCapital;
                    document.getElementById('update_adresse').value = adresse;
                    document.getElementById('update_code_postal').value = codePostal;
                    document.getElementById('update_city').value = city;
                    document.getElementById('update_country').value = country;
                    document.getElementById('update_phone_number').value = phoneNumber;
                    document.getElementById('update_email_adresse').value = emailAdresse;
                    document.getElementById('update_identification_TVA').value = identificationTva;
                    document.getElementById('update_is_active').value = isActive;

                    // Set the form action
                    document.getElementById('updateAccountForm').action = `/compta-accounts/${accountId}`;
                });
            });
        });
    </script>
</body>
</html>
