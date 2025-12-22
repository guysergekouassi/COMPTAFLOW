<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<!-- Tailwind Integration with Preflight disabled -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false,
        }
    }
</script>

<style>
    /* Global harmonized styles */
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
        cursor: pointer;
    }

    /* Active state for filter cards */
    .filter-card.filter-active {
        border: 2px solid #1e40af;
        background-color: #eff6ff;
    }
    .filter-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-card:hover {
        transform: translateY(-2px);
    }

    /* DataTable Customization */
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        display: none;
    }

    /* Premium Modal Design */
    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
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
    }
    .input-field-premium:focus {
        border-color: #1e40af !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
        outline: none !important;
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
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Codes <span class="text-gradient">Journaux</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Configuration</span>'])

                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">

                        <!-- Description Section -->
                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Configurez vos journaux comptables pour une saisie rapide et organisée de vos flux financiers.
                            </p>
                        </div>

                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm rounded-2xl" role="alert">
                                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm rounded-2xl" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- KPI Summary Cards (Style Mirror) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="glass-card p-6 filter-card flex items-center justify-between filter-type-card" data-type="all">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Journaux</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $code_journaux->count() }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                    <i class="fas fa-book text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="glass-card p-6 filter-card flex items-center justify-between filter-type-card" data-type="Tresorerie">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Trésorerie</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $code_journaux->where('type', 'Tresorerie')->count() }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                                    <i class="fas fa-university text-2xl"></i>
                                </div>
                            </div>

                            <div class="glass-card p-6 filter-card flex items-center justify-between filter-type-card" data-type="Ventes">
                                <div>
                                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Achats / Ventes</p>
                                    <h3 class="text-3xl font-bold text-slate-800 mt-1">{{ $code_journaux->whereIn('type', ['Achats', 'Ventes'])->count() }}</h3>
                                </div>
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                    <i class="fas fa-exchange-alt text-2xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Bar -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4">
                            <!-- Left: Filter -->
                            <div class="flex items-center">
                                <button type="button" id="toggleFilterBtn"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>
                            
                            <!-- Right: New Journal -->
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCreateCodeJournal"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus"></i>
                                    Nouveau Journal
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel -->
                        <div id="advancedFilterPanel" class="hidden mb-8">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="relative w-full">
                                        <input type="text" id="filterType" placeholder="Type (Achats, Ventes...)" 
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-tags absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <input type="text" id="filterCode" placeholder="Code Journal..." 
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <input type="text" id="filterIntitule" placeholder="Intitulé..." 
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-font absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-4">
                                    <button type="button" id="resetFilterBtn" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-semibold hover:bg-slate-200 transition">
                                        Réinitialiser
                                    </button>
                                    <button type="button" id="applyFilterBtn" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                        Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left" id="JournalTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Type</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Code</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($code_journaux as $journal)
                                            <tr class="table-row">
                                                <td class="px-8 py-6">
                                                    @php
                                                        $badge = match($journal->type) {
                                                            'Achats' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                            'Ventes' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                            'Tresorerie' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                            'General' => 'bg-orange-100 text-orange-700 border-orange-200',
                                                            'Situation' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                                            default => 'bg-slate-100 text-slate-600 border-slate-200'
                                                        };
                                                    @endphp
                                                    <span class="inline-flex px-3 py-1 rounded-lg text-xs font-bold border {{ $badge }}">{{ strtoupper($journal->type ?? 'Général') }}</span>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <span class="font-mono text-lg font-bold text-blue-700">{{ $journal->code_journal }}</span>
                                                </td>
                                                <td class="px-8 py-6 font-semibold text-slate-800">{{ $journal->intitule }}</td>
                                                <td class="px-8 py-6 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm"
                                                            data-bs-toggle="modal" data-bs-target="#modalCenterUpdate"
                                                            data-id="{{ $journal->id }}"
                                                            data-code="{{ $journal->code_journal }}"
                                                            data-type="{{ $journal->type }}"
                                                            data-intitule="{{ $journal->intitule }}"
                                                            data-traitement="{{ $journal->traitement_analytique }}"
                                                            data-compte_de_contrepartie="{{ $journal->compte_de_contrepartie }}"
                                                            data-compte_de_tresorerie="{{ $journal->compte_de_tresorerie }}"
                                                            data-rapprochement_sur="{{ $journal->rapprochement_sur }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl border border-red-100 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm"
                                                            data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"
                                                            data-id="{{ $journal->id }}"
                                                            data-name="{{ $journal->code_journal }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100" id="tableFooter">
                                <p class="text-sm text-slate-500 font-medium italic">
                                    <i class="fas fa-info-circle mr-1"></i> <span id="tableInfo">Calcul en cours...</span>
                                </p>
                                <div id="customPagination" class="flex gap-2"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Create Modal -->
    <div class="modal fade" id="modalCreateCodeJournal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <form id="formCodeJournal" method="POST" action="{{ route('accounting_journals.store') }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="text-xl font-extrabold text-blue-900 uppercase tracking-tight">Nouveau Journal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Code Journal *</label>
                                <input type="text" name="code_journal" class="input-field-premium" required placeholder="ex: VT">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Type *</label>
                                <select id="type_select" name="type" class="input-field-premium" required>
                                    <option value="Achats">Achats</option>
                                    <option value="Ventes">Ventes</option>
                                    <option value="Tresorerie">Trésorerie</option>
                                    <option value="General" selected>Général</option>
                                    <option value="Situation">Situation</option>
                                </select>
                            </div>
                            <div class="col-12 text-start">
                                <label class="input-label-premium">Intitulé *</label>
                                <input type="text" name="intitule" class="input-field-premium" required placeholder="ex: Journal des Ventes">
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Traitement analytique</label>
                                <select name="traitement_analytique" class="input-field-premium">
                                    <option value="non">Non</option>
                                    <option value="oui">Oui</option>
                                </select>
                            </div>
                            <!-- Conditional Fields Group -->
                            <div class="col-12 text-start d-none" id="tresorerie_options">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="input-label-premium">Compte de trésorerie associés</label>
                                        <select name="compte_de_tresorerie" class="input-field-premium">
                                            <option value="">-- Choisir --</option>
                                            @foreach ($comptesTresorerie as $compte)
                                                <option value="{{ $compte->id }}">{{ $compte->numero_de_compte }} - {{ $compte->intitule }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label-premium">Rapprochement</label>
                                        <select name="rapprochement_sur" class="input-field-premium">
                                            <option value="Contrepartie">Auto</option>
                                            <option value="tresorerie">Manuel</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label-premium">Contrepartie</label>
                                        <input type="text" name="compte_de_contrepartie" class="input-field-premium" placeholder="ex: 521000">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn bg-slate-100 text-slate-500 font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn bg-blue-700 text-white font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0 shadow-lg shadow-blue-200 hover:bg-blue-800 transition">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <form id="formCodeJournalUpdate" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-0 pb-0">
                        <h5 class="text-xl font-extrabold text-blue-900 uppercase tracking-tight">Modifier Journal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body py-4">
                        <input type="hidden" name="journal_id" id="update_journal_id">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Code Journal *</label>
                                <input type="text" id="update_code_journal" name="code_journal" class="input-field-premium" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Type *</label>
                                <select id="update_type" name="type" class="input-field-premium" required>
                                    <option value="Achats">Achats</option>
                                    <option value="Ventes">Ventes</option>
                                    <option value="Tresorerie">Trésorerie</option>
                                    <option value="General">Général</option>
                                    <option value="Situation">Situation</option>
                                </select>
                            </div>
                            <div class="col-12 text-start">
                                <label class="input-label-premium">Intitulé *</label>
                                <input type="text" id="update_intitule" name="intitule" class="input-field-premium" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Traitement analytique</label>
                                <select id="update_traitement_analytique" name="traitement_analytique" class="input-field-premium">
                                    <option value="0">Non</option>
                                    <option value="1">Oui</option>
                                </select>
                            </div>
                            <div class="col-12 text-start d-none" id="update_tresorerie_options">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="input-label-premium">Compte de trésorerie associés</label>
                                        <select id="update_compte_de_tresorerie" name="compte_de_tresorerie" class="input-field-premium">
                                            <option value="">-- Choisir --</option>
                                            @foreach ($comptesTresorerie as $compte)
                                                <option value="{{ $compte->id }}">{{ $compte->numero_de_compte }} - {{ $compte->intitule }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label-premium">Rapprochement</label>
                                        <select id="update_rapprochement_sur" name="rapprochement_sur" class="input-field-premium">
                                            <option value="Contrepartie">Auto</option>
                                            <option value="tresorerie">Manuel</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="input-label-premium">Contrepartie</label>
                                        <input type="text" id="update_compte_de_contrepartie" name="compte_de_contrepartie" class="input-field-premium">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn bg-slate-100 text-slate-500 font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn bg-blue-700 text-white font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0 shadow-lg shadow-blue-200">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content premium-modal-content border-0">
                <div class="bg-red-600 p-8 text-center text-white -m-5 rounded-t-2xl mb-4">
                    <i class="fas fa-trash-alt text-4xl mb-4"></i>
                    <h3 class="text-xl font-black uppercase tracking-widest">Confirmation</h3>
                </div>
                <div class="p-4 text-center text-slate-600">
                    <p class="text-sm font-bold">Supprimer ce journal ?</p>
                    <div id="journalToDeleteName" class="mt-4 p-3 bg-red-50 text-red-600 rounded-xl font-mono text-xs font-black"></div>
                </div>
                <div class="flex flex-col gap-2 mt-4">
                    <form id="deleteJournalForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-4 bg-red-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition shadow-xl shadow-red-100">
                            Confirmer
                        </button>
                    </form>
                    <button type="button" data-bs-dismiss="modal" class="w-full py-4 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')

    <!-- Plugins JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        const accounting_journalsUpdateBaseUrl = "{{ route('accounting_journals.update', ['id' => '__ID__']) }}";
        const accounting_journalsDeleteUrl = "{{ route('accounting_journals.destroy', ['id' => '__ID__']) }}";

        document.addEventListener("DOMContentLoaded", function() {
            console.log("🚀 SCRIPT JOURNAUX INITIALISÉ");

            const initDataTable = () => {
                if (typeof $ !== 'undefined' && $.fn.dataTable) {
                    const table = $('#JournalTable').DataTable({
                        dom: 't',
                        pageLength: 5,
                        language: { 
                            zeroRecords: "Aucun journal trouvé",
                            infoEmpty: "Aucun journal à afficher"
                        }
                    });

                    // 2. Pagination Logic
                    const updatePagination = () => {
                        const info = table.page.info();
                        if (info.recordsDisplay > 0) {
                            $('#tableInfo').html(`Affichage de <span class="font-bold text-slate-700">${info.start + 1}</span> à <span class="font-bold text-slate-700">${info.end}</span> sur <span class="font-bold text-slate-700">${info.recordsDisplay}</span> journaux`);
                            let html = '';
                            html += `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 transition ${info.page === 0 ? 'opacity-50 cursor-not-allowed' : ''}" id="prevPage" ${info.page === 0 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                            html += `<button class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold font-mono shadow-lg shadow-blue-200">${info.page + 1}</button>`;
                            html += `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 transition ${info.page >= info.pages - 1 ? 'opacity-50 cursor-not-allowed' : ''}" id="nextPage" ${info.page >= info.pages - 1 ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                            $('#customPagination').html(html);
                        } else {
                            $('#tableInfo').html('Aucun journal trouvé');
                            $('#customPagination').empty();
                        }
                    };

                    table.on('draw', updatePagination);
                    $(document).on('click', '#prevPage', function() { table.page('previous').draw('page'); });
                    $(document).on('click', '#nextPage', function() { table.page('next').draw('page'); });
                    updatePagination();

                    // 3. Filters Logic
                    function applyCustomFilters() {
                        table.column(0).search($('#filterType').val());
                        table.column(1).search($('#filterCode').val());
                        table.column(2).search($('#filterIntitule').val());
                        table.draw();
                    }

                    // Live search
                    $('#filterType, #filterCode, #filterIntitule').on('keyup change', function() {
                        applyCustomFilters();
                    });

                    // Search Button
                    $('#applyFilterBtn').on('click', function(e) {
                        e.preventDefault();
                        applyCustomFilters();
                    });

                    // Reset Button
                    $('#resetFilterBtn').on('click', function(e) {
                        e.preventDefault();
                        $('#filterType, #filterCode, #filterIntitule').val('');
                        $('.filter-card').removeClass('filter-active');
                        $('#filter-all').addClass('filter-active');
                        table.columns().search('').draw();
                    });

                    // Filter Panel Toggle
                    $('#toggleFilterBtn').on('click', function(e) {
                        e.preventDefault();
                        const panel = $('#advancedFilterPanel');
                        panel.toggleClass('hidden');
                        $(this).toggleClass('bg-blue-50 border-blue-200 text-blue-700');
                    });

                    // KPI Cards
                    $('.filter-type-card').on('click', function() {
                        $('.filter-card').removeClass('filter-active');
                        $(this).addClass('filter-active');
                        const type = $(this).data('type');
                        table.column(0).search(type === 'all' ? '' : type).draw();
                    });

                    // 4. Modal Interactions (Treasury Toggle)
                    $('#type_select, #update_type').on('change', function() {
                        const isTresorerie = $(this).val() === 'Tresorerie';
                        const optionsId = $(this).attr('id') === 'type_select' ? '#tresorerie_options' : '#update_tresorerie_options';
                        if (isTresorerie) $(optionsId).removeClass('d-none');
                        else $(optionsId).addClass('d-none');
                    });
                }
            };

            initDataTable();
        });
    </script>
</body>
</html>
