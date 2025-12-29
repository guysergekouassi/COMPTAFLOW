<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<style>
    /* Premium Modal Styles */
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
        border-radius: 12px !important;
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
        border-radius: 12px !important;
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

    /* Premium Modal Content Wide for complex forms */
    .premium-modal-content-wide {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        max-width: 90%;
        margin: auto;
        padding: 1.5rem !important;
    }

    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
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
    }

    #exerciceTable_wrapper .dataTables_length,
    #exerciceTable_wrapper .dataTables_filter {
        display: none;
    }

    #exerciceTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #exerciceTable thead th {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 2rem !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }

    #exerciceTable tbody td {
        padding: 1.5rem 2rem !important;
        vertical-align: middle !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Exercice <span class="text-gradient">Comptable</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Configuration</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Gérez vos périodes comptables : ouverture, clôture et suivi des journaux.
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-green-50 text-green-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-check-circle text-xl"></i>
                                    <span class="font-medium">{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-red-50 text-red-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span class="font-medium">{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Actions Bar (même modèle que Plan Tiers) -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4">
                            <!-- Left Group: Filter -->
                            <div class="flex items-center">
                                <button type="button" id="toggleFilterBtn" onclick="window.toggleAdvancedFilter()"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>

                            <!-- Right Group: Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCenterCreate"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus"></i>
                                    Nouvel exercice
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel (même modèle que Plan Tiers) -->
                        <div id="advancedFilterPanel" style="display: none;" class="mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="relative w-full">
                                        <input type="date" id="filter-date-debut"
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>

                                    <div class="relative w-full">
                                        <input type="date" id="filter-date-fin"
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-calendar-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>

                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="btn btn-secondary rounded-xl px-6 font-semibold" id="reset-filters" onclick="window.resetAdvancedFilters()">
                                            <i class="fas fa-undo me-2"></i>Réinitialiser
                                        </button>
                                        <button type="button" class="btn btn-primary rounded-xl px-6 font-semibold" id="apply-filters" onclick="window.applyAdvancedFilters()">
                                            <i class="fas fa-search me-2"></i>Rechercher
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Exercices Comptables</h3>
                                <p class="text-sm text-slate-500">Liste des exercices et accès rapide aux journaux saisis</p>
                            </div>
                            <div class="table-responsive">
                                <table class="w-full text-left border-collapse" id="exerciceTable">
                                    <thead class="bg-slate-50/50 border-b border-slate-100">
                                        <tr>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Date de début</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Date de fin</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">Durée</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">Journaux</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <!-- Content populated by JS (exercice_comptable.js) -->
                                        <!-- Make sure JS renders rows with class="table-row" and td with px-8 py-4 classes -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <form id="formCreateExercice" method="POST" action="{{ route('exercice_comptable.store') }}" class="w-full">
                                    @csrf
                                    <div class="modal-content premium-modal-content">
                                        <!-- Header -->
                                        <div class="text-center mb-6 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                                Nouvel <span class="text-blue-gradient-premium">Exercice</span>
                                            </h1>
                                            <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                                        </div>

                                        <div class="space-y-4">
                                            <!-- Date de début -->
                                            <div class="space-y-1">
                                                <label for="date_debut" class="input-label-premium">Date de début</label>
                                                <input type="date" class="input-field-premium" id="date_debut" name="date_debut" required>
                                                <span id="error_date_debut" class="text-danger small mt-1 d-block"></span>
                                            </div>

                                            <!-- Date de fin -->
                                            <div class="space-y-1">
                                                <label for="date_fin" class="input-label-premium">Date de fin</label>
                                                <input type="date" class="input-field-premium" id="date_fin" name="date_fin" required>
                                                <span id="error_date_fin" class="text-danger small mt-1 d-block"></span>
                                            </div>

                                            <!-- Intitulé -->
                                            <div class="space-y-1">
                                                <label for="intitule_exercice" class="input-label-premium">Intitulé de l'exercice</label>
                                                <input type="text" class="input-field-premium" id="intitule_exercice" name="intitule" placeholder="Ex : Exercice 2025">
                                                <span id="error_intitule" class="text-danger small mt-1 d-block"></span>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="grid grid-cols-2 gap-4 pt-8">
                                            <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" class="btn-save-premium" id="btnSubmitExercice">
                                                Enregistrer
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <form method="POST" id="deleteForm" action="" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-content premium-modal-content">
                                        <!-- Header -->
                                        <div class="text-center mb-6 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4" style="width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; background-color: #fef2f2; border-radius: 1rem; margin: 0 auto 1rem;">
                                                <i class="fas fa-trash-alt text-red-600 text-xl" style="color: #dc2626; font-size: 1.25rem;"></i>
                                            </div>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                                Confirmer la <span style="color: #dc2626; font-weight: 800;">Suppression</span>
                                            </h1>
                                        </div>

                                        <div class="text-center space-y-3 mb-8">
                                            <p class="text-slate-500 text-sm font-medium leading-relaxed">
                                                Êtes-vous sûr de vouloir supprimer cet exercice ? Cette action est irréversible.
                                            </p>
                                            <p class="text-slate-900 font-bold" id="projectToDelete"></p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" class="btn-save-premium" id="confirmDeleteBtn" style="background-color: #dc2626 !important;">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="modal fade" id="clotureConfirmationModal" tabindex="-1" aria-labelledby="clotureModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <form method="POST" id="clotureForm" class="w-full">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-content premium-modal-content">
                                        <!-- Header -->
                                        <div class="text-center mb-6 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <div class="w-12 h-12 bg-yellow-50 rounded-2xl flex items-center justify-center mx-auto mb-4" style="width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; background-color: #fefce8; border-radius: 1rem; margin: 0 auto 1rem;">
                                                <i class="bx bx-lock-alt text-yellow-600 text-xl" style="color: #ca8a04; font-size: 1.25rem;"></i>
                                            </div>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                                Clôturer l'<span style="color: #ca8a04; font-weight: 800;">Exercice</span>
                                            </h1>
                                        </div>

                                        <div class="text-center space-y-3 mb-8">
                                            <p class="text-slate-500 text-sm font-medium leading-relaxed">
                                                Êtes-vous sûr de vouloir <strong>clôturer</strong> cet exercice ?<br>
                                                Après clôture, aucune modification ne sera possible.
                                            </p>
                                            <p class="text-slate-900 font-bold" id="exerciceToCloture"></p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" class="btn-save-premium" style="background-color: #ca8a04 !important;">
                                                Clôturer
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        @include('components.footer')

        <script>

            const journauxSaisisUrl = "{{ route('journaux_saisis') }}";
            const exercice_comptableDeleteUrl = "{{ route('exercice_comptable.destroy', ['exercice_comptable' => '__ID__']) }}";
            const exercice_comptableCloturerUrl = "{{ route('exercice_comptable.cloturer', ['exercice_comptable' => '__ID__']) }}";

        </script>
        {{-- <script src="{{ asset('js/exercice_compt.js') }}"></script> --}}

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Éléments du DOM
                const formExercice = document.getElementById('formCreateExercice');
                const modalCreate = document.getElementById('modalCenterCreate');
                const dateFinInput = document.getElementById('date_fin');
                const intituleInput = document.getElementById('intitule_exercice');
                let dataTable;

                // Toggle filtre (doit être défini avant initDataTable au cas où DataTables plante)
                window.toggleAdvancedFilter = function() {
                    const panel = document.getElementById('advancedFilterPanel');
                    if (!panel) return;
                    panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
                };

                // Initialisation safe du modal (évite un crash si bootstrap n'est pas chargé)
                const modalInstance = (modalCreate && window.bootstrap?.Modal)
                    ? bootstrap.Modal.getOrCreateInstance(modalCreate)
                    : null;

                // Binding robuste (en plus du onclick inline)
                const toggleBtn = document.getElementById('toggleFilterBtn');
                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (typeof window.toggleAdvancedFilter === 'function') {
                            window.toggleAdvancedFilter();
                        }
                    });
                }

                // Initialiser la DataTable
                try {
                    initDataTable();
                } catch (e) {
                    console.error('Erreur initialisation DataTable exercice_comptable:', e);
                }

                window.applyAdvancedFilters = function() {
                    const dateDebut = document.getElementById('filter-date-debut')?.value || '';
                    const dateFin = document.getElementById('filter-date-fin')?.value || '';
                    if (dataTable) {
                        dataTable.ajax.url(`{{ route('exercice_comptable.data') }}?date_debut=${encodeURIComponent(dateDebut)}&date_fin=${encodeURIComponent(dateFin)}`).load();
                    }
                };

                window.resetAdvancedFilters = function() {
                    const d1 = document.getElementById('filter-date-debut');
                    const d2 = document.getElementById('filter-date-fin');
                    if (d1) d1.value = '';
                    if (d2) d2.value = '';
                    if (dataTable) {
                        dataTable.ajax.url(`{{ route('exercice_comptable.data') }}`).load();
                    }
                };

                // Initialisation du DataTable
                function initDataTable() {
                    if ($.fn.DataTable.isDataTable('#exerciceTable')) {
                        dataTable.destroy();
                    }

                    dataTable = $('#exerciceTable').DataTable({
                        ajax: {
                            url: '{{ route("exercice_comptable.data") }}',
                            type: 'GET'
                        },
                        columns: [
                            {
                                data: 'date_debut',
                                render: function(data, type, row) {
                                    if (type === 'display' && data) {
                                        const date = new Date(data);
                                        return date.toLocaleDateString('fr-FR');
                                    }
                                    return data;
                                }
                            },
                            {
                                data: 'date_fin',
                                render: function(data, type, row) {
                                    if (type === 'display' && data) {
                                        const date = new Date(data);
                                        return date.toLocaleDateString('fr-FR');
                                    }
                                    return data;
                                }
                            },
                            { data: 'intitule' },
                            {
                                data: 'nb_mois',
                                render: function(data, type, row) {
                                    if (type === 'display' && data !== null) {
                                        return parseFloat(data).toFixed(2).replace(/\.?0+$/, '');
                                    }
                                    return data;
                                }
                            },
                            { data: 'nombre_journaux_saisis' },
                            {
                                data: null,
                                orderable: false,
                                render: function(data, type, row) {
                                    return `
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                    class="btn p-0 border-0 bg-transparent text-primary view-btn"
                                                    data-id="${row.id}"
                                                    data-bs-toggle="tooltip"
                                                    title="Voir">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn p-0 border-0 bg-transparent text-warning edit-btn"
                                                    data-id="${row.id}"
                                                    data-bs-toggle="tooltip"
                                                    title="Modifier">
                                                <i class="bx bx-edit-alt"></i>
                                            </button>
                                            ${!row.cloturer ? `
                                                <button type="button"
                                                        class="btn p-0 border-0 bg-transparent text-danger delete-btn"
                                                        data-id="${row.id}"
                                                        data-label="${row.intitule}"
                                                        data-type="delete"
                                                        data-bs-toggle="tooltip"
                                                        title="Supprimer">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn p-0 border-0 bg-transparent text-success cloturer-btn"
                                                        data-id="${row.id}"
                                                        data-label="${row.intitule}"
                                                        data-type="cloture"
                                                        data-bs-toggle="tooltip"
                                                        title="Clôturer">
                                                    <i class="bx bx-lock-alt"></i>
                                                </button>` : ''
                                            }
                                            ${row.cloturer ? `
                                                <button type="button"
                                                        class="btn p-0 border-0 bg-transparent text-secondary"
                                                        disabled
                                                        data-bs-toggle="tooltip"
                                                        title="Exercice clôturé">
                                                    <i class="bx bx-lock"></i>
                                                </button>` : ''
                                            }
                                        </div>
                                    `;
                                }
                            }
                        ],
                        language: {
                            emptyTable: 'Aucune donnée disponible dans le tableau',
                            info: 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
                            infoEmpty: 'Aucune entrée à afficher',
                            infoFiltered: '(filtré à partir de _MAX_ entrées totales)',
                            lengthMenu: 'Afficher _MENU_ entrées',
                            loadingRecords: 'Chargement...',
                            processing: 'Traitement...',
                            search: 'Rechercher :',
                            zeroRecords: 'Aucun enregistrement correspondant trouvé',
                            paginate: {
                                first: 'Premier',
                                last: 'Dernier',
                                next: 'Suivant',
                                previous: 'Précédent'
                            },
                            aria: {
                                sortAscending: ': activer pour trier la colonne par ordre croissant',
                                sortDescending: ': activer pour trier la colonne par ordre décroissant'
                            }
                        },
                        responsive: true,
                        autoWidth: false,
                        pageLength: 5,
                        order: [[0, 'desc']], // Tri par date de début par défaut
                        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                             "<'row'<'col-sm-12'tr>>" +
                             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                        drawCallback: function() {
                            // Initialiser les tooltips après chaque dessin du tableau
                            if (window.bootstrap?.Tooltip) {
                                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                                tooltipTriggerList.map(function (tooltipTriggerEl) {
                                    return new bootstrap.Tooltip(tooltipTriggerEl);
                                });
                            }

                            // Initialiser les gestionnaires d'événements
                            initializeEventHandlers();
                        }
                    });

                    return dataTable;
                }

                // Formater une date au format jj/mm/aaaa
                function formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('fr-FR');
                }

                // Afficher une alerte
                function showAlert(type, message) {
                    // Supprimer les anciennes alertes
                    const oldAlerts = document.querySelectorAll('.alert-dismissible');
                    oldAlerts.forEach(alert => alert.remove());

                    const alertHtml = `
                        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;

                    // Ajouter l'alerte en haut de la page
                    const container = document.querySelector('.container-xxl');
                    if (container) {
                        container.insertAdjacentHTML('afterbegin', alertHtml);

                        // Supprimer l'alerte après 5 secondes
                        setTimeout(() => {
                            const alert = container.querySelector('.alert');
                            if (alert) {
                                alert.remove();
                            }
                        }, 5000);
                    }
                }

                // Générer automatiquement l'intitulé à partir de la date de fin
                function genererIntitule() {
                    if (!dateFinInput || !intituleInput) return;

                    const dateFinValue = dateFinInput.value;
                    if (dateFinValue) {
                        try {
                            const dateObj = new Date(dateFinValue);
                            const annee = dateObj.getFullYear();
                            // Ne générer l'intitulé que si le champ est vide ou contient un format d'exercice
                            if (!intituleInput.value || /^Exercice \d{4}$/.test(intituleInput.value)) {
                                intituleInput.value = `Exercice ${annee}`;
                            }
                        } catch (e) {
                            console.error("Erreur de formatage de la date:", e);
                        }
                    }
                }

                // Gestion de la soumission du formulaire
                if (formExercice) {
                    formExercice.addEventListener('submit', async function(e) {
                        // Ne pas empêcher le comportement par défaut si JavaScript est désactivé
                        const isAjax = window.XMLHttpRequest && 'withCredentials' in new XMLHttpRequest();

                        if (isAjax) {
                            e.preventDefault();

                            console.log('Soumission AJAX du formulaire');
                            const submitButton = this.querySelector('button[type="submit"]');
                            const originalText = submitButton.innerHTML;

                            // Créer un objet FormData pour le formulaire
                            const formData = new FormData(this);

                            // Afficher les données du formulaire dans la console
                            console.log('Données du formulaire :');
                            for (let [key, value] of formData.entries()) {
                                console.log(`${key}: ${value}`);
                            }

                            try {
                                // Désactiver le bouton pendant la soumission
                                submitButton.disabled = true;
                                submitButton.innerHTML = `
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Enregistrement...
                                `;

                                // Récupérer le token CSRF
                                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                // Envoyer la requête avec l'objet FormData existant
                                console.log('Envoi de la requête AJAX à', this.action);
                                const response = await fetch(this.action, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': token,
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: formData
                                });

                                console.log('Réponse reçue, statut:', response.status);
                                const responseData = await response.json();
                                console.log('Données de la réponse:', responseData);

                                if (!response.ok) {
                                    // Gestion des erreurs de validation
                                    if (response.status === 422 && responseData.errors) {
                                        // Réinitialiser les états d'erreur précédents
                                        document.querySelectorAll('.is-invalid').forEach(el => {
                                            el.classList.remove('is-invalid');
                                        });
                                        document.querySelectorAll('.invalid-feedback').forEach(el => {
                                            el.remove();
                                        });

                                        // Afficher les erreurs de validation
                                        let errorMessages = [];

                                        for (const [field, messages] of Object.entries(responseData.errors)) {
                                            const input = document.querySelector(`[name="${field}"]`);
                                            if (input) {
                                                input.classList.add('is-invalid');
                                                const errorDiv = document.createElement('div');
                                                errorDiv.className = 'invalid-feedback';
                                                errorDiv.textContent = messages[0];
                                                input.parentNode.appendChild(errorDiv);
                                                errorMessages.push(messages[0]);
                                            }
                                        }

                                        if (errorMessages.length > 0) {
                                            showAlert('danger', errorMessages.join('<br>'));
                                        } else {
                                            showAlert('danger', 'Veuillez corriger les erreurs dans le formulaire.');
                                        }
                                    } else {
                                        throw new Error(responseData.message || 'Une erreur est survenue');
                                    }
                                    return;
                                }

                                console.log('Réponse du serveur:', responseData);

                                if (responseData.success) {
                                    // Recharger les données du tableau
                                    dataTable.ajax.reload();

                                    // Afficher un message de succès
                                    showAlert('success', responseData.message || 'Exercice enregistré avec succès');

                                    // Fermer le modal et réinitialiser le formulaire
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                    this.reset();
                                } else {
                                    throw new Error(responseData.message || 'Une erreur est survenue');
                                }
                            } catch (error) {
                                console.error('Erreur:', error);
                                showAlert('danger', error.message || 'Une erreur est survenue lors de l\'enregistrement');
                            } finally {
                                // Réactiver le bouton
                                if (submitButton) {
                                    submitButton.disabled = false;
                                    submitButton.innerHTML = originalText;
                                }
                            }
                        } else {
                            // Soumission normale du formulaire (sans AJAX)
                            console.log('Soumission normale du formulaire');
                            // La validation HTML5 s'occupera de la validation côté client
                            // Le serveur gérera la redirection et l'affichage des messages
                        }
                    });
                }

                // Gestion de la génération automatique de l'intitulé
                if (dateFinInput) {
                    dateFinInput.addEventListener('change', genererIntitule);
                    dateFinInput.addEventListener('input', genererIntitule);
                }

                // Fonction pour initialiser les gestionnaires d'événements
                function initializeEventHandlers() {
                    const deleteModalEl = document.getElementById('deleteConfirmationModal');
                    const clotureModalEl = document.getElementById('clotureConfirmationModal');

                    // Gestionnaire pour le bouton de suppression (bouton généré par DataTable)
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            const label = this.getAttribute('data-label') || 'cet exercice';

                            const form = document.getElementById('deleteForm');
                            if (form && id) {
                                form.action = exercice_comptableDeleteUrl.replace('__ID__', id);
                            }

                            const projectToDelete = document.getElementById('projectToDelete');
                            if (projectToDelete) {
                                projectToDelete.textContent = label;
                            }

                            if (deleteModalEl) {
                                if (window.bootstrap?.Modal) {
                                    bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
                                }
                            }
                        });
                    });

                    // Gestionnaire pour le bouton de clôture (bouton généré par DataTable)
                    document.querySelectorAll('.cloturer-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            const label = this.getAttribute('data-label') || 'cet exercice';

                            const form = document.getElementById('clotureForm');
                            if (form && id) {
                                form.action = exercice_comptableCloturerUrl.replace('__ID__', id);
                            }

                            const exerciceToCloture = document.getElementById('exerciceToCloture');
                            if (exerciceToCloture) {
                                exerciceToCloture.textContent = label;
                            }

                            if (clotureModalEl) {
                                if (window.bootstrap?.Modal) {
                                    bootstrap.Modal.getOrCreateInstance(clotureModalEl).show();
                                }
                            }
                        });
                    });
                }

                // Gestion de la fermeture du modal
                if (modalCreate) {
                    modalCreate.addEventListener('hidden.bs.modal', function() {
                        if (formExercice) {
                            formExercice.reset();
                            // Réinitialiser les messages d'erreur
                            document.querySelectorAll('.is-invalid').forEach(el => {
                                el.classList.remove('is-invalid');
                            });
                            document.querySelectorAll('.invalid-feedback').forEach(el => {
                                el.remove();
                            });
                        }
                    });
                }

            });

</script>

</body>

</html>