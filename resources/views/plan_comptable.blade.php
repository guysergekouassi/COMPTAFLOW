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
                @include('components.header', ['page_title' => 'Plan <span class="text-gradient">Comptable</span>'])
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Main Container from User Design (Adapted) -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Badge Section -->
                        <div class="text-center mb-8 -mt-4">
                            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full mb-3">
                                Gestion des comptes
                            </span>
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
                            <div class="glass-card p-6 filter-card flex items-center justify-between" id="filter-all">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Comptes</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $totalPlans }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                    <i class="bx bx-book-content text-2xl"></i>
                                </div>
                            </div>

                            <!-- Manuel -->
                            <div class="glass-card p-6 filter-card flex items-center justify-between" id="filter-manuel">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Créés Manuellement</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $plansByUser }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                    <i class="bx bx-edit text-2xl"></i>
                                </div>
                            </div>

                            <!-- Auto -->
                            <div class="glass-card p-6 filter-card flex items-center justify-between" id="filter-auto">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">SYSCOHADA (Auto)</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $plansSys }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
                                    <i class="bx bx-check-shield text-2xl"></i>
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
                                        <input type="text" id="filterClasse" placeholder="Filtrer par Classe (ex: 1, 6)..."
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
                        <div class="glass-card overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" id="planComptableTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Numéro</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider hidden">Méthode</th> <!-- Hidden but used for filtering -->
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Classe</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse ($plans as $plan)
                                        <tr class="table-row group">
                                            <td class="px-8 py-6">
                                                <span class="font-mono text-lg font-bold text-blue-700">{{ $plan->numero_de_compte }}</span>
                                            </td>
                                            <td class="px-8 py-6">
                                                <p class="font-semibold text-slate-800">{{ $plan->intitule }}</p>
                                                <!-- Optional subtitle if data available -->
                                                <!-- <span class="text-xs text-slate-400 font-medium">Type: {{ $plan->type_de_compte ?? 'Standard' }}</span> -->
                                            </td>
                                            <td class="hidden">{{ $plan->adding_strategy }}</td> <!-- Hidden column for filter -->
                                            <td class="px-8 py-6">
                                                @php
                                                    $classe = substr($plan->numero_de_compte, 0, 1);
                                                    $colors = [
                                                        '1' => 'bg-slate-100 text-slate-600 border-slate-200',
                                                        '2' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                        '3' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                        '4' => 'bg-orange-100 text-orange-700 border-orange-200',
                                                        '5' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                        '6' => 'bg-red-100 text-red-700 border-red-200',
                                                        '7' => 'bg-green-100 text-green-700 border-green-200',
                                                        '8' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                        '9' => 'bg-gray-100 text-gray-700 border-gray-200',
                                                    ];
                                                    $badgeClass = $colors[$classe] ?? 'bg-slate-100 text-slate-600';
                                                @endphp
                                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold border whitespace-nowrap {{ $badgeClass }}">CLASSE {{ $classe }}</span>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <div class="flex justify-end gap-2 transition-opacity">
                                                    <button type="button"
                                                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm"
                                                        data-bs-toggle="modal" data-bs-target="#modalCenterUpdate"
                                                        data-id="{{ $plan->id }}"
                                                        data-numero_de_compte="{{ $plan->numero_de_compte }}"
                                                        data-intitule="{{ $plan->intitule }}"
                                                        data-type_de_compte="{{ $plan->type_de_compte }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="w-10 h-10 flex items-center justify-center rounded-xl border border-red-100 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm"
                                                        data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"
                                                        data-id="{{ $plan->id }}"
                                                        data-intitule="{{ $plan->intitule }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    <!-- Read only / View button -->
                                                    <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-100 text-slate-400 hover:text-gray-600 hover:bg-gray-50 transition donnees-plan-comptable"
                                                        data-id="{{ $plan->id }}"
                                                        data-intitule="{{ $plan->intitule }}"
                                                        data-numero_de_compte="{{ $plan->numero_de_compte }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-8 text-muted">Aucun compte trouvé.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer / Pagination Info -->
                            <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                                <p class="text-sm text-slate-500 font-medium italic">
                                    <i class="fas fa-info-circle mr-1"></i> <span id="tableInfo">Calcul en cours...</span>
                                </p>
                                <div id="customPagination" class="flex gap-2">
                                    <!-- Pagination injected by JS or custom -->
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
            <div class="modal-content">
                <form action="{{ route('plan_comptable.store') }}" method="POST" id="planComptableForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Créer un compte général</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="numero_de_compte" class="form-label">Numéro de compte</label>
                                <input type="text" class="form-control" id="numero_de_compte" name="numero_de_compte" maxlength="8" required>
                                <span id="numero_compte_feedback" class="text-danger small mt-1 d-block"></span>
                            </div>
                            <div class="col-6">
                                <label for="intitule" class="form-label">Intitulé</label>
                                <input type="text" class="form-control" id="intitule" name="intitule" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Update -->
    <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="updatePlanForm" method="POST" action="{{ route('plan_comptable.update', ['id' => '__ID__']) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier un plan comptable</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="update_planId" name="id" />
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="update_numero_de_compte" class="form-label">Numéro de compte</label>
                                <input type="text" class="form-control" id="update_numero_de_compte" name="numero_de_compte" required>
                            </div>
                            <div class="col-6">
                                <label for="update_intitule" class="form-label">Intitulé</label>
                                <input type="text" class="form-control" id="update_intitule" name="intitule" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white justify-content-center bg-danger">
                    <h5 class="modal-title text-white" id="deleteModalLabel"><i class="bx bx-error-circle me-2"></i>Confirmer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-0">Êtes-vous sûr ? Cette action est <strong>irréversible</strong>.</p>
                    <p class="fw-bold text-danger mt-2" id="planToDeleteName"></p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" id="deletePlanForm" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Defaut -->
    <div class="modal fade" id="Plan_defaut" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="Plandefaut" method="POST" action="{{ route('plan_comptable.defaut') }}">
                    @csrf
                    <div class="modal-body">
                        <p>Voulez-vous charger le plan comptable par défaut ?</p>
                        <input type="hidden" name="use_default" value="true">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
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

    <!-- Plugins JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- App Scripts -->
    <script src="{{ asset('js/plan_comptable.js') }}"></script>

    <!-- Custom Logic for New Design -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("🚀 SCRIPT PLAN COMPTABLE INITIALISÉ");

            // On s'assure que jQuery est dispo avant d'initier DataTables
            const initDataTable = () => {
                if (typeof $ !== 'undefined' && $.fn.dataTable) {
                    console.log("✅ DataTables chargé et prêt !");

                    const table = $('#planComptableTable').DataTable({
                        dom: 't',
                        pageLength: 5,
                        destroy: true,
                        stateSave: false,
                        language: {
                            zeroRecords: "Aucune donnée trouvée",
                            infoEmpty: "Aucune donnée à afficher"
                        }
                    });

                    // 2. Filtres
                    function applyCustomFilters() {
                        table.column(0).search($('#filterNumero').val()).draw();
                        table.column(1).search($('#filterIntitule').val()).draw();
                        table.column(3).search($('#filterClasse').val()).draw();
                    }

                    $('#filterNumero, #filterIntitule, #filterClasse').on('keyup', applyCustomFilters);

                    // Boutons
                    $('#applyFilterBtn').on('click', function(e) {
                        e.preventDefault();
                        applyCustomFilters();
                    });

                    $('#resetFilterBtn').on('click', function(e) {
                        e.preventDefault();
                        $('#filterNumero').val('');
                        $('#filterIntitule').val('');
                        $('#filterClasse').val('');
                        applyCustomFilters();
                    });

                    // 3. Toggle Button
                    $('#toggleFilterBtn').on('click', function(e) {
                         e.preventDefault();
                         const panel = $('#advancedFilterPanel');
                         if(panel.hasClass('hidden')) {
                             panel.removeClass('hidden');
                             $(this).addClass('bg-blue-50 border-blue-200 text-blue-700');
                         } else {
                             panel.addClass('hidden');
                             $(this).removeClass('bg-blue-50 border-blue-200 text-blue-700');
                         }
                    });

                    // 4. KPI Cards
                    function activateCard(cardId) {
                        $('.filter-card').removeClass('filter-active');
                        $(`${cardId}`).addClass('filter-active');
                    }

                    $('#filter-all').on('click', function() { table.column(2).search('').draw(); activateCard('#filter-all'); });
                    $('#filter-manuel').on('click', function() { table.column(2).search('manuel').draw(); activateCard('#filter-manuel'); });
                    $('#filter-auto').on('click', function() { table.column(2).search('auto').draw(); activateCard('#filter-auto'); });

                    // 5. Pagination
                    function updatePagination() {
                        const info = table.page.info();
                        console.log("📊 Mise à jour pagination:", info);

                        if (info.recordsDisplay > 0) {
                            $('#tableInfo').html(`Affichage de <span class="font-bold text-slate-700">${info.start + 1}</span> à <span class="font-bold text-slate-700">${info.end}</span> sur <span class="font-bold text-slate-700">${info.recordsDisplay}</span> comptes`);

                            let paginationHtml = '';
                            paginationHtml += `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page === 0 ? 'opacity-50 cursor-not-allowed' : ''}" id="prevPage" ${info.page === 0 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                            paginationHtml += `<button class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200">${info.page + 1}</button>`;
                            paginationHtml += `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page >= info.pages - 1 ? 'opacity-50 cursor-not-allowed' : ''}" id="nextPage" ${info.page >= info.pages - 1 ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                            $('#customPagination').html(paginationHtml);
                        } else {
                            $('#tableInfo').html('Aucun compte trouvé');
                            $('#customPagination').empty();
                        }
                    }

                    // On attache l'event AVANT tout draw potentiel
                    table.on('draw', function() {
                        console.log("🎨 Event draw déclenché");
                        updatePagination();
                    });

                    $(document).on('click', '#prevPage', function() { table.page('previous').draw('page'); });
                    $(document).on('click', '#nextPage', function() { table.page('next').draw('page'); });

                    // Initial call
                    updatePagination();
                    activateCard('#filter-all');
                } else {
                    console.log("⏳ En attente de jQuery/DataTables...");
                    setTimeout(initDataTable, 1200);
                }
            };

            initDataTable();
        });
    </script>
</body>
</html>
