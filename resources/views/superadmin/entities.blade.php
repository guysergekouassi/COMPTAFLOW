<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding: 1.5rem !important;
    }

    .input-field-premium {
        transition: all 0.2s ease;
        border: 2px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        width: 100%;
    }

    .input-field-premium:focus {
        border-color: #1e40af !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
        outline: none !important;
    }

    .input-label-premium {
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 0.5rem !important;
        display: block !important;
    }

    .btn-save-premium {
        padding: 0.75rem 1.5rem !important;
        border-radius: 12px !important;
        background-color: #1e40af !important;
        color: white !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
        border: none !important;
    }

    .btn-save-premium:hover {
        background-color: #1e3a8a !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }

    .btn-cancel-premium {
        padding: 0.75rem 1.5rem !important;
        border-radius: 12px !important;
        color: #64748b !important;
        font-weight: 700 !important;
        transition: all 0.2s ease !important;
        background: transparent !important;
        border: 2px solid #f1f5f9 !important;
    }

    .btn-cancel-premium:hover {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        border-color: #e2e8f0 !important;
    }

    .text-gradient {
        background: linear-gradient(to right, #1e40af, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gestion des Entités'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">

                    {{-- BLOC DE MESSAGE DE SUCCÈS À AJOUTER ICI --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6" role="alert" id="successAlert">
                            <div class="flex items-center">
                                <i class="fa-solid fa-check-circle mr-2"></i>
                                {{ session('success') }}
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const successAlert = document.getElementById('successAlert');
                                if (successAlert) {
                                    setTimeout(() => {
                                        successAlert.style.display = 'none';
                                    }, 5000);
                                }
                            });
                        </script>
                    @endif
                    {{-- FIN BLOC DE MESSAGE DE SUCCÈS --}}
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6" role="alert">
                            <div class="flex items-center">
                                <i class="fa-solid fa-exclamation-triangle mr-2"></i>
                                <strong>Erreur :</strong> {{ session('error') }}
                            </div>
                        </div>
                    @endif


                    <!-- Header Standardisé -->
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h5 class="mb-1 text-premium-gradient">Gestion des Entités</h5>
                            <p class="text-muted small mb-0">Pilotez les structures et les administrateurs du réseau.</p>
                        </div>
                        <a href="{{ route('superadmin.companies.create') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="fa-solid fa-plus-circle me-2"></i> Nouvelle Entité
                        </a>
                    </div>

                        <div class="glass-card overflow-hidden">
                            <div class="table-responsive">
                                <table class="w-full text-left border-collapse table-fixed">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100 uppercase text-[11px] font-black tracking-widest text-slate-400">
                                            <th class="px-8 py-5 w-[35%]">Compagnie</th>
                                            <th class="px-8 py-5 w-[20%]">Administrateur</th>
                                            <th class="px-8 py-5 text-center w-[15%]">Statut</th>
                                            <th class="px-8 py-5 text-center w-[10%]">Utilisateurs</th>
                                            <th class="px-8 py-5 text-right w-[20%]">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
    @forelse ($companies as $company)
        {{-- Ligne PRINCIPALE (Master Row) --}}
        {{-- Utilisez l'ID unique du parent pour cibler l'effondrement --}}
            <tr class="accordion-toggle hover:bg-slate-50/80 transition-colors cursor-pointer"
                data-bs-toggle="collapse"
                data-bs-target="#subcompany-details-{{ $company->id }}">
                <td class="px-8 py-6">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-300 transition-transform toggle-icon"></i>
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 border border-blue-100">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <span class="font-bold text-slate-800 text-lg">{{ $company->company_name }}</span>
                    </div>
                </td>
                <td class="px-8 py-6">
                    @php $admin = $company->admin; @endphp
                    <div class="flex flex-col">
                        <span class="font-semibold text-slate-700">{{ $admin ? $admin->name . ' ' . $admin->last_name : 'N/A' }}</span>
                        <span class="text-xs text-slate-400">{{ $admin ? $admin->email_adresse : '' }}</span>
                    </div>
                </td>
                <td class="px-8 py-6 text-center">
                    @if ($company->is_active)
                        <span class="px-3 py-1 bg-green-50 text-green-700 text-[10px] font-black uppercase tracking-wider rounded-lg border border-green-100">Active</span>
                    @else
                        <span class="px-3 py-1 bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-wider rounded-lg border border-red-100">Inactive</span>
                    @endif
                </td>
                <td class="px-8 py-6 text-center">
                    <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-md">{{ $company->users->count() }}</span>
                </td>
                <td class="px-8 py-6 text-right">
                    <div class="flex justify-end gap-2" onclick="event.stopPropagation();">
                        {{-- Toggle Status --}}
                        <form action="{{ route('toggle', $company->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl border transition-all {{ $company->is_active ? 'border-red-100 text-red-600 hover:bg-red-600 hover:text-white' : 'border-green-100 text-green-600 hover:bg-green-600 hover:text-white' }}" title="{{ $company->is_active ? 'Désactiver' : 'Activer' }}">
                                <i class="fa-solid {{ $company->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                        </form>

                        {{-- Edit --}}
                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-all"
                            data-bs-toggle="modal" data-bs-target="#editCompanyModal{{ $company->id }}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>

                        {{-- Delete --}}
                        <form action="{{ route('superadmin.companies.destroy', $company->id) }}" method="POST" class="inline" 
                            onsubmit="return confirm('Souhaitez-vous vraiment supprimer {{ $company->company_name }} ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-100 text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <tr id="subcompany-details-{{ $company->id }}" class="collapse bg-slate-50/30">
                <td colspan="5" class="px-8 py-0">
                    <div class="py-6 border-l-2 border-blue-500 ml-10 pl-8">
                        <h6 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-diagram-project text-blue-500"></i>
                            Unités Comptables de {{ $company->company_name }}
                        </h6>
                        @if ($company->children && $company->children->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($company->children as $subCompany)
                                    <div class="bg-white p-3 rounded-xl border border-slate-100 flex items-center justify-between shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-angle-right text-[10px] text-slate-300"></i>
                                            <span class="text-sm font-semibold text-slate-700">{{ $subCompany->company_name }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 {{ $subCompany->is_active ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100' }} text-[9px] font-bold rounded-full border">
                                            {{ $subCompany->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">Aucune sous-entité enregistrée.</p>
                        @endif
                    </div>
                </td>
            </tr>
@php
    // Collection des modals pour les déplacer hors du tbody
    if(!isset($companyModals)) $companyModals = [];
    $companyModals[] = $company;
@endphp
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


                    </div>
                    @if(isset($companyModals))
                        @foreach ($companyModals as $company)
                            <div class="modal fade" id="editCompanyModal{{ $company->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content premium-modal-content">
                                        <div class="text-center mb-8 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 border-0">
                                                Modifier <span class="text-gradient">{{ $company->company_name }}</span>
                                            </h1>
                                            <div class="h-1 w-12 bg-blue-600 mx-auto mt-3 rounded-full"></div>
                                        </div>

                                        <form action="{{ route('superadmin.companies.update', $company->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="space-y-8">
                                                {{-- SECTION COMPAGNIE --}}
                                                <div>
                                                    <h6 class="text-[11px] font-black uppercase tracking-widest text-blue-600 mb-6 flex items-center gap-2">
                                                        <i class="fa-solid fa-building text-sm"></i>
                                                        Informations Compagnie
                                                    </h6>
                                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Nom de l'entreprise</label>
                                                            <input type="text" name="company_name" class="input-field-premium" value="{{ $company->company_name }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Forme Juridique</label>
                                                            <select name="juridique_form" class="input-field-premium" required>
                                                                <option value="SARL" {{ $company->juridique_form == 'SARL' ? 'selected' : '' }}>SARL</option>
                                                                <option value="SA" {{ $company->juridique_form == 'SA' ? 'selected' : '' }}>SA</option>
                                                                <option value="SAS" {{ $company->juridique_form == 'SAS' ? 'selected' : '' }}>SAS</option>
                                                                <option value="SCI" {{ $company->juridique_form == 'SCI' ? 'selected' : '' }}>SCI</option>
                                                                <option value="EIRL" {{ $company->juridique_form == 'EIRL' ? 'selected' : '' }}>EIRL</option>
                                                                <option value="Auto-entrepreneur" {{ $company->juridique_form == 'Auto-entrepreneur' ? 'selected' : '' }}>Auto-entrepreneur</option>
                                                            </select>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Activité</label>
                                                            <input type="text" name="activity" class="input-field-premium" value="{{ $company->activity }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Capital Social</label>
                                                            <input type="number" step="0.01" name="social_capital" class="input-field-premium" value="{{ $company->social_capital }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Statut</label>
                                                            <select name="is_active" class="input-field-premium" required>
                                                                <option value="1" {{ $company->is_active == 1 ? 'selected' : '' }}>Actif</option>
                                                                <option value="0" {{ $company->is_active == 0 ? 'selected' : '' }}>Inactif</option>
                                                            </select>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Ville</label>
                                                            <input type="text" name="city" class="input-field-premium" value="{{ $company->city }}" required>
                                                        </div>
                                                        <div class="md:col-span-2 space-y-1">
                                                            <label class="input-label-premium">Adresse</label>
                                                            <input type="text" name="adresse" class="input-field-premium" value="{{ $company->adresse }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Code Postal</label>
                                                            <input type="text" name="code_postal" class="input-field-premium" value="{{ $company->code_postal }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Pays</label>
                                                            <input type="text" name="country" class="input-field-premium" value="{{ $company->country }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Téléphone</label>
                                                            <input type="text" name="phone_number" class="input-field-premium" value="{{ $company->phone_number }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Email Pro</label>
                                                            <input type="email" name="email_adresse" class="input-field-premium" value="{{ $company->email_adresse }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">TVA (Identification)</label>
                                                            <input type="text" name="identification_TVA" class="input-field-premium" value="{{ $company->identification_TVA }}">
                                                        </div>
                                                        <div class="md:col-span-2 space-y-1">
                                                            <label class="input-label-premium">Société Parente</label>
                                                            <select name="parent_company_id" class="input-field-premium">
                                                                <option value="">Aucune (Société Mère)</option>
                                                                @foreach($companies->where('id', '!=', $company->id) as $pComp)
                                                                    <option value="{{ $pComp->id }}" {{ $company->parent_company_id == $pComp->id ? 'selected' : '' }}>{{ $pComp->company_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- SECTION ADMIN --}}
                                                <div>
                                                    <h6 class="text-[11px] font-black uppercase tracking-widest text-blue-600 mb-6 mt-4 flex items-center gap-2">
                                                        <i class="fa-solid fa-user-shield text-sm"></i>
                                                        Administrateur Associé
                                                    </h6>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Nom</label>
                                                            <input type="text" name="admin_name" class="input-field-premium" value="{{ $company->admin->name ?? '' }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Prénom</label>
                                                            <input type="text" name="admin_last_name" class="input-field-premium" value="{{ $company->admin->last_name ?? '' }}" required>
                                                        </div>
                                                        <div class="md:col-span-2 space-y-1">
                                                            <label class="input-label-premium">Email de connexion</label>
                                                            <input type="email" name="admin_email_adresse" class="input-field-premium" value="{{ $company->admin->email_adresse ?? '' }}" required>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Nouveau Mot de Passe (Optionnel)</label>
                                                            <input type="password" name="admin_password" class="input-field-premium" placeholder="Laissez vide pour conserver l'actuel">
                                                        </div>
                                                        <div class="space-y-1">
                                                            <label class="input-label-premium">Confirmation MDP</label>
                                                            <input type="password" name="admin_password_confirmation" class="input-field-premium" placeholder="Confirmez le nouveau MDP">
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- SECTION HABILITATIONS --}}
                                                <div>
                                                    <h6 class="text-[11px] font-black uppercase tracking-widest text-blue-600 mb-6 mt-4 flex items-center gap-2">
                                                        <i class="fa-solid fa-lock text-sm"></i>
                                                        Habilitations Admin
                                                    </h6>
                                                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 bg-slate-50/50 p-6 rounded-2xl border border-slate-100 max-h-60 overflow-y-auto">
                                                        @php
                                                            $groupedPermissions = config('accounting_permissions.permissions');
                                                            $currentHabs = $company->admin->habilitations ?? [];
                                                        @endphp
                                                        @foreach ($groupedPermissions as $section => $permissions)
                                                            <div class="col-12 mb-2 mt-2">
                                                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ $section }}</span>
                                                            </div>
                                                            @foreach ($permissions as $key => $label)
                                                                <label class="flex items-center gap-3 cursor-pointer group">
                                                                    <input type="checkbox" name="habilitations[{{ $key }}]" value="1" 
                                                                        class="w-5 h-5 rounded-md border-2 border-slate-200 text-blue-600 focus:ring-blue-500 transition-all checked:bg-blue-600"
                                                                        {{ isset($currentHabs[$key]) && $currentHabs[$key] ? 'checked' : '' }}>
                                                                    <span class="text-sm font-bold text-slate-600 group-hover:text-blue-600 transition-colors">{{ $label }}</span>
                                                                </label>
                                                            @endforeach
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                                                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="btn-save-premium">Enregistrer les modifications</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
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
