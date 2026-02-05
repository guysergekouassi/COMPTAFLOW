<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    /* Custom styles for the new design */
    .bg-slate-50\/50 { background-color: rgb(248 250 252 / 0.5); }
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    .btn-action {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }
    .table-row {
        transition: background-color 0.2s;
    }
    .table-row:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }

    /* Active state for filter cards */
    .filter-card.filter-active {
        border: 2px solid #1e40af; /* Blue-700 */
        background-color: #eff6ff; /* Blue-50 */
    }
    .filter-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-card:hover {
        transform: translateY(-2px);
    }

    /* DataTable Customization to hide default elements */
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        display: none;
    }

    /* Nouveau Design Premium pour les Modaux */
    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        max-width: 400px;
        margin: auto;
        padding: 1.25rem !important;
    }

    .input-field-premium {
        transition: all 0.2s ease;
        border: 2px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        border-radius: 16px !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        width: 100%;
        box-sizing: border-box;
    }

    .input-field-premium:focus {
        border-color: #1e40af !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
        outline: none !important;
    }

    .text-blue-gradient-premium {
        background: linear-gradient(to right, #1e40af, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    .input-label-premium {
        font-size: 0.7rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-left: 0.1rem !important;
        margin-bottom: 0.35rem !important;
        display: block !important;
    }

    .btn-save-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 16px !important;
        background-color: #1e40af !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.1) !important;
        transition: all 0.2s ease !important;
        border: none !important;
        width: 100%;
    }

    .btn-save-premium:hover {
        background-color: #1e3a8a !important;
        transform: translateY(-2px) !important;
    }

    .btn-cancel-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        transition: all 0.2s ease !important;
        border: none !important;
        background: transparent !important;
        width: 100%;
    }

    .btn-cancel-premium:hover {
        background-color: #f8fafc !important;
        color: #475569 !important;
    }
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('components.header', ['page_title' => 'Plan <span class="text-gradient">Comptable</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Gestion des comptes</span>'])
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Main Container from User Design (Adapted) -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Badge Section -->
                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Organisez et structurez votre comptabilité avec la nomenclature officielle de COMPTAFLOW.
                            </p>
                        </div>

                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- KPI Filters Section (Preserving functionality with new Look) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Total -->
                            <div class="glass-card !p-6 flex items-center cursor-pointer filter-card filter-active" data-filter-type="all">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                    <i class="bx bx-layer text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Total des plans</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalPlans }}</h3>
                                </div>
                            </div>

                            <!-- Manuel -->
                            <div class="glass-card !p-6 flex items-center cursor-pointer filter-card" data-filter-type="user">
                                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                    <i class="bx bx-user text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Plans par utilisateur</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $plansByUser }}</h3>
                                </div>
                            </div>

                            <!-- Auto -->
                            <div class="glass-card !p-6 flex items-center cursor-pointer filter-card" data-filter-type="system">
                                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                    <i class="bx bx-cog text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Plan SYSCOHADA</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $plansSys }}</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Bar -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4">
                            <!-- Left Group: Filter -->
                            <div class="flex items-center">
                                <button type="button" id="toggleFilterBtn"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>

                            <!-- Right Group: Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                @if ($hasAutoStrategy == false)
                                <button type="button" data-bs-toggle="modal" data-bs-target="#Plan_defaut"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-download text-blue-600"></i>
                                    Charger Défaut
                                </button>
                                @endif

                                <!-- Bouton Charger depuis l'Admin [NOUVEAU] -->
                                @if (session('current_company_id') && session('current_company_id') != auth()->user()->company_id)
                                <button type="button" id="btnSyncAdmin"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-indigo-50 border border-indigo-200 rounded-2xl text-indigo-700 font-semibold text-sm">
                                    <i class="fas fa-sync-alt"></i>
                                    Charger Modèle Admin
                                </button>
                                @endif

                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCenterCreate"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus"></i>
                                    Nouveau Compte
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel (Hidden by default) -->
                        <div id="advancedFilterPanel" class="hidden mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Filter Numéro -->
                                    <div class="relative w-full">
                                        <input type="text" id="filterNumero" placeholder="Filtrer par Numéro..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <!-- Filter Intitulé -->
                                    <div class="relative w-full">
                                        <input type="text" id="filterIntitule" placeholder="Filtrer par Intitulé..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-font absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <!-- Filter Classe -->
                                    <div class="relative w-full">
                                        <input type="text" id="filterClasse" placeholder="Filtrer par Classe (ex: 1, 8, 68)..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-layer-group absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                </div>
                                <!-- Filter Actions -->
                                <div class="flex justify-end gap-3 mt-4">
                                    <button type="button" id="resetFilterBtn" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-semibold hover:bg-slate-200 transition">
                                        <i class="fas fa-undo mr-2"></i>Réinitialiser
                                    </button>
                                    <button type="button" id="applyFilterBtn" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                        <i class="fas fa-search mr-2"></i>Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Card -->
                        <!-- En-tête de la vue par défaut -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Plan Comptable Général</h3>
                                <p class="text-sm text-slate-500">Liste complète des comptes par défaut</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" id="planComptableTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Numéro</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Classe</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Mode</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Date de création</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @php
                                            // Debug pour voir les données
                                            if(isset($plansComptables) && $plansComptables->count() > 0) {
                                                $firstPlan = $plansComptables->first();
                                                echo "<!-- DEBUG: Premier plan - Classe: '" . ($firstPlan->classe ?? 'NULL') . "', Strategy: '" . ($firstPlan->adding_strategy ?? 'NULL') . "' -->";
                                                echo "<!-- DEBUG: Strategy comparaison - Manuel: " . ($firstPlan->adding_strategy == 'Manuel' ? 'TRUE' : 'FALSE') . " -->";
                                            }
                                        @endphp
                                        
                                        @forelse ($plansComptables as $plan)
                                        <tr class="table-row group">
                                            <td class="px-8 py-6">
                                                <div class="flex flex-col justify-center">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-magic text-blue-400 text-xs"></i>
                                                        <span class="font-mono text-lg font-bold text-blue-700">{{ $plan->numero_de_compte }}</span>
                                                    </div>
                                                    @if(!empty($plan->numero_original))
                                                        <div class="text-[10px] text-slate-400 font-medium italic mt-1 flex items-center gap-1">
                                                            <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $plan->numero_original }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <p class="font-medium text-slate-800">{{ $plan->intitule }}</p>
                                            </td>

                                            <td class="px-8 py-6">
                                                @php
                                                    $classeValue = $plan->classe;
                                                    if (empty($classeValue)) {
                                                        $numero = $plan->numero_de_compte;
                                                        $premierChiffre = substr($numero, 0, 1);
                                                        $classeValue = 'Classe ' . $premierChiffre;
                                                    }
                                                    $classeAffiche = substr($classeValue, 0, 8);
                                                @endphp
                                                <span class="px-3 py-1 text-xs font-medium rounded-full" style="background-color: #3b82f6; color: white;">
                                                    {{ $classeAffiche }}
                                                </span>
                                            </td>

                                            <td class="px-8 py-6">
                                                @php
                                                    $strategy = $plan->adding_strategy ?? 'Manuel';
                                                    $isManuel = (strtolower($strategy) === 'manuel' || strtolower($strategy) === 'manual');
                                                    $color = $isManuel ? '#3b82f6' : '#10b981';
                                                @endphp
                                                <span class="px-3 py-1 text-xs font-medium rounded-full" style="background-color: {{ $color }}; color: white;">
                                                    {{ $strategy }}
                                                </span>
                                            </td>

                                            <td class="px-8 py-6">
                                                <span class="text-sm text-slate-600">{{ $plan->created_at->format('d/m/Y') }}</span>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="btn-group" role="group">
                                                    <!-- Bouton Modifier -->
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-primary"
                                                        onclick="modifierPlanComptable({{ $plan->id }})"
                                                        title="Modifier le plan comptable">
                                                        <i class='bx bx-edit-alt'></i>
                                                    </button>
                                                    
                                                    <!-- Bouton Supprimer -->
                                                    <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="supprimerPlanComptable({{ $plan->id }})"
                                                        title="Supprimer le plan comptable">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-8 text-muted">Aucun plan comptable par défaut trouvé.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Footer / Pagination Info -->
                            <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                                <div class="flex flex-col sm:flex-row items-center justify-between w-full gap-4">
                                <p class="text-sm text-slate-500 font-medium italic table-info">
                                    <i class="fas fa-info-circle mr-1"></i> 
                                    <span>{{ $plansComptables->count() }} comptes affichés</span>
                                </p>
                                <div class="pagination-container flex gap-2"></div>
                            </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- --- MODALS (Bootstrap) --- -->

    <!-- Modal Creation -->
    <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route('plan_comptable.store') }}" method="POST" id="planComptableForm" class="w-full">
                @csrf
                <div class="modal-content premium-modal-content">
                    
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Nouveau <span class="text-blue-gradient-premium">Compte</span>
                        </h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>

                    <div class="space-y-4">
                        <!-- Numéro de compte -->
                        <div class="space-y-1">
                            <label for="numero_de_compte" class="input-label-premium">Numéro de compte</label>
                            <input type="text" class="input-field-premium" id="numero_de_compte" name="numero_de_compte" 
                                maxlength="8" placeholder="Ex: 41110000" required>
                            <span id="numero_compte_feedback" class="text-danger small mt-1 d-block"></span>
                        </div>

                        <!-- Intitulé -->
                        <div class="space-y-1">
                            <label for="intitule" class="input-label-premium">Intitulé du compte</label>
                            <input type="text" class="input-field-premium" id="intitule" name="intitule" 
                                placeholder="Entrez l'intitulé du compte" required>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn-save-premium">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update -->
    <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form id="updatePlanForm" method="POST" action="{{ route('plan_comptable.update', ['id' => '__ID__']) }}" class="w-full">
                @csrf
                @method('PUT')
                <div class="modal-content premium-modal-content">
                    
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Modifier <span class="text-blue-gradient-premium">Compte</span>
                        </h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>

                    <div class="space-y-4">
                        <input type="hidden" id="update_planId" name="id" />
                        
                        <!-- Numéro de compte -->
                        <div class="space-y-1">
                            <label for="update_numero_de_compte" class="input-label-premium">Numéro de compte</label>
                            <input type="text" class="input-field-premium" id="update_numero_de_compte" name="numero_de_compte" required>
                        </div>

                        <!-- Intitulé -->
                        <div class="space-y-1">
                            <label for="update_intitule" class="input-label-premium">Intitulé du compte</label>
                            <input type="text" class="input-field-premium" id="update_intitule" name="intitule" required>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn-save-premium">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content premium-modal-content">
                <!-- Header -->
                <div class="text-center mb-6 position-relative">
                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                    </div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                        Confirmer la <span class="text-red-600">Suppression</span>
                    </h1>
                </div>

                <div class="text-center space-y-3 mb-8">
                    <p class="text-slate-500 text-sm font-medium leading-relaxed">
                        Êtes-vous sûr de vouloir supprimer ce compte ? Cette action est irréversible.
                    </p>
                    <p class="text-slate-900 font-bold" id="planToDeleteName"></p>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <form method="POST" id="deletePlanForm" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-save-premium !bg-red-600 hover:!bg-red-700 shadow-red-200">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Defaut -->
    <div class="modal fade" id="Plan_defaut" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="Plandefaut" method="POST" action="{{ route('plan_comptable.defaut') }}" class="w-full">
                @csrf
                <div class="modal-content premium-modal-content">
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-magic text-blue-600 text-xl"></i>
                        </div>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Plan par <span class="text-blue-600">Défaut</span>
                        </h1>
                    </div>

                    <div class="text-center mb-8">
                        <p class="text-slate-500 text-sm font-medium leading-relaxed">
                            Souhaitez-vous charger le plan comptable standard ? Cela facilitera la configuration initiale.
                        </p>
                        <input type="hidden" name="use_default" value="true">
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn-save-premium">
                            Confirmer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const plan_comptable_ecrituresSaisisUrl = "{{ route('plan_comptable_ecritures') }}";
        const planComptableDefautUrl = "{{ route('plan_comptable.defaut') }}";
        const verifierNumeroUrl = "{{ route('verifierNumeroCompte') }}";
        const planComptableUpdateBaseUrl = "{{ route('plan_comptable.update', ['id' => '__ID__']) }}";
        const plan_comptableDeleteUrl = "{{ route('plan_comptable.destroy', ['id' => '__ID__']) }}";
    </script>

    @include('components.footer')
    <!-- Diagnostic Script -->
    <script>
        (function() {
            window.checkJSLoad = function(context) {
                console.log(`[Diagnostic] ${context}: jQuery=${typeof jQuery}, $=${typeof $}`);
                if (typeof jQuery !== 'undefined') {
                    console.log(`[Diagnostic] ${context}: DataTables Plugin=${typeof jQuery.fn.DataTable}`);
                }
            };
            checkJSLoad("App Scripts Start");
        })();
    </script>

    <!-- Custom Logic for New Design -->
    <!-- Custom Logic for New Design -->
    <script>
        (function($) {
            "use strict";
            
            // --- FONCTIONS GLOBALES (Exposées sur window) ---
            window.modifierPlanComptable = function(id) {
                const btn = $(`.btn-outline-primary[onclick*="modifierPlanComptable(${id})"]`);
                const row = btn.closest('tr');
                const numero = row.find('td:first-child span').text().trim();
                const intitule = row.find('td:nth-child(2) p').text().trim();
                
                $('#update_planId').val(id);
                $('#update_numero_de_compte').val(numero);
                $('#update_intitule').val(intitule);
                $('#updatePlanForm').attr('action', planComptableUpdateBaseUrl.replace('__ID__', id));
                
                new bootstrap.Modal(document.getElementById('modalCenterUpdate')).show();
            };

            window.supprimerPlanComptable = function(id) {
                const btn = $(`.btn-outline-danger[onclick*="supprimerPlanComptable(${id})"]`);
                const row = btn.closest('tr');
                const intitule = row.find('td:nth-child(2) p').text().trim();
                
                $('#planToDeleteName').text(intitule);
                $('#deletePlanForm').attr('action', plan_comptableDeleteUrl.replace('__ID__', id));
                
                new bootstrap.Modal(document.getElementById('deleteConfirmationModal')).show();
            };

            // --- INITIALISATION SÉCURISÉE ---
            function initDataTable(retryCount = 0) {
                if (typeof $.fn.DataTable !== 'function') {
                    if (retryCount < 5) {
                        console.warn(`[PlanComptable] DataTable non trouvé, tentative ${retryCount + 1}/5...`);
                        setTimeout(() => initDataTable(retryCount + 1), 200);
                    } else {
                        console.error("[PlanComptable] ÉCHEC CRITIQUE : DataTables n'est pas chargé après 5 tentatives.");
                    }
                    return;
                }

                console.log("[PlanComptable] Initialisation DataTables...");
                const table = $('#planComptableTable').DataTable({
                    destroy: true,
                    dom: 't',
                    pageLength: 5,
                    order: [], 
                    language: {
                        zeroRecords: "Aucun compte trouvé",
                        infoEmpty: "Aucun compte disponible",
                    },
                    columnDefs: [
                        { width: "15%", targets: 0 },
                        { width: "35%", targets: 1 },
                        { targets: [2,3,4,5], width: "12.5%" }
                    ],
                    autoWidth: false,
                    drawCallback: function() { updatePagination(this.api()); }
                });

                window.comptableDataTable = table;
                setupEventListeners(table);
            }

            function setupEventListeners(table) {
                // Navigation vers écritures
                $(document).off('click', '.donnees-plan-comptable').on('click', '.donnees-plan-comptable', function() {
                    const id = $(this).data('id');
                    const intitule = $(this).data('intitule');
                    const numero = $(this).data('numero_de_compte');
                    const params = new URLSearchParams({ id_plan_comptable: id, intitule, numero_de_compte: numero });
                    window.location.href = `${plan_comptable_ecrituresSaisisUrl}?${params.toString()}`;
                });

                // Filtrage avancé
                $(document).off('click', '#applyFilterBtn').on('click', '#applyFilterBtn', function() {
                    const numero = $('#filterNumero').val().trim();
                    const intitule = $('#filterIntitule').val().trim();
                    const classe = $('#filterClasse').val().trim();
                    
                    // Filtrage Intitulé (Colonne 1)
                    table.column(1).search(intitule);
                    
                    // Filtrage Numéro & Classe (Colonne 0)
                    let searchCol0 = "";
                    if (classe && numero) {
                        // Regex : Commence par 'classe' ET contient 'numero'
                        // On échappe les caractères spéciaux au cas où
                        const escClasse = classe.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                        const escNumero = numero.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                        searchCol0 = '^' + escClasse + '.*' + escNumero;
                    } else if (classe) {
                        // Regex : Commence par 'classe'
                        searchCol0 = '^' + classe.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    } else if (numero) {
                        // Recherche standard pour le numéro
                        searchCol0 = numero;
                    }
                    
                    // Le second paramètre 'true' active le mode Regex
                    table.column(0).search(searchCol0, searchCol0.startsWith('^'));
                    table.draw();
                });

                $(document).off('click', '#resetFilterBtn').on('click', '#resetFilterBtn', function() {
                    $('#filterNumero, #filterIntitule, #filterClasse').val('');
                    table.search('').columns().search('').draw();
                });

                $(document).off('click', '#toggleFilterBtn').on('click', '#toggleFilterBtn', function(e) {
                    e.preventDefault();
                    $('#advancedFilterPanel').toggleClass('hidden');
                    $(this).toggleClass('bg-blue-50 border-blue-200 text-blue-700');
                });

                // KPI Cards
                $(document).off('click', '.filter-card').on('click', '.filter-card', function() {
                    const type = $(this).data('filter-type');
                    $('.filter-card').removeClass('filter-active bg-blue-50 border-blue-200 text-blue-700');
                    $(this).addClass('filter-active bg-blue-50 border-blue-200 text-blue-700');
                    
                    if (type === 'user') table.column(3).search('manuel').draw();
                    else if (type === 'system') table.column(3).search('auto').draw();
                    else table.column(3).search('').draw();
                });
            }

            function updatePagination(table) {
                const info = table.page.info();
                const $info = $('.table-info');
                const $container = $('.pagination-container');

                if (info.recordsDisplay > 0) {
                    $info.html(`Affichage de <span class="font-bold text-slate-700">${info.start + 1}</span> à <span class="font-bold text-slate-700">${info.end}</span> sur <span class="font-bold text-slate-700">${info.recordsDisplay}</span> comptes`);
                    
                    let pagHtml = `<button class="px-4 py-2 border rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page === 0 ? 'opacity-50' : ''}" onclick="window.comptableDataTable.page('previous').draw('page')" ${info.page === 0 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                    for (let i = 0; i < info.pages; i++) {
                        if (i === info.page) pagHtml += `<button class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold">${i + 1}</button>`;
                        else if (i === 0 || i === info.pages - 1 || (i >= info.page - 1 && i <= info.page + 1)) {
                            pagHtml += `<button class="px-4 py-2 border rounded-xl bg-white text-slate-600 hover:bg-slate-50" onclick="window.comptableDataTable.page(${i}).draw('page')">${i + 1}</button>`;
                        }
                    }
                    pagHtml += `<button class="px-4 py-2 border rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page >= info.pages - 1 ? 'opacity-50' : ''}" onclick="window.comptableDataTable.page('next').draw('page')" ${info.page >= info.pages - 1 ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                    $container.html(pagHtml);
                } else {
                    $info.html('Aucun compte trouvé');
                    $container.empty();
                }
            }

            // --- AJAX & SYNC ---
            $(function() {
                initDataTable();

                let lastPaddedVal = "";
                $("#numero_de_compte").on("input", function() {
                    let raw = $(this).val().replace(/\D/g, "");
                    let padded = raw.padEnd(8, "0");
                    if (raw.length === 0) { $("#numero_compte_feedback").text(""); return; }
                    if (padded === lastPaddedVal) return;
                    lastPaddedVal = padded;

                    $.post(verifierNumeroUrl, { _token: "{{ csrf_token() }}", numero_de_compte: padded }, function(res) {
                        if (res.exists) $("#numero_compte_feedback").text("❌ Ce numéro existe déjà.").removeClass("text-success").addClass("text-danger");
                        else $("#numero_compte_feedback").text("✅ Numéro disponible.").removeClass("text-danger").addClass("text-success");
                    });
                });

                $('#btnSyncAdmin').on('click', function() {
                    if (confirm("Charger les comptes manquants depuis le modèle admin ?")) {
                        const btn = $(this);
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>...');
                        $.post('/admin/config/sync/plan-comptable', { _token: "{{ csrf_token() }}" })
                            .done(res => { if(res.success) window.location.reload(); else alert(res.message); })
                            .fail(() => alert('Erreur serveur'))
                            .always(() => btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Charger Modèle Admin'));
                    }
                });
            });

        })(jQuery);
    </script>
    
    <style>
        .filter-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .filter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .filter-active {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6;
        }
    </style>
</body>
</html>
