<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

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

    .uppercase-input {
        text-transform: uppercase;
    }

    .text-blue-gradient-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }
    
    .blue-bar-premium {
        height: 4px;
        width: 32px;
        background-color: #1e40af;
        margin: 8px auto 0;
        border-radius: 9999px;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Codes <span class="text-gradient">Journaux</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Configuration</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

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

                        <!-- KPI Summary Cards (Style Plan Tiers) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                            <div class="glass-card !p-6 flex items-center cursor-pointer filter-card filtre-journaux" data-type="all">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                    <i class="fas fa-book text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Total Journaux</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</h3>
                                </div>
                            </div>

                            @foreach ($journauxParType as $type => $count)
                                @php
                                    $color = 'blue';
                                    $icon = 'fas fa-book';
                                    
                                    if (strtolower($type) === 'tresorerie') {
                                        $color = 'emerald';
                                        $icon = 'fas fa-university';
                                    } elseif (strtolower($type) === 'achats') {
                                        $color = 'purple';
                                        $icon = 'fas fa-shopping-cart';
                                    } elseif (strtolower($type) === 'ventes') {
                                        $color = 'indigo';
                                        $icon = 'fas fa-chart-line';
                                    } elseif (strtolower($type) === 'general') {
                                        $color = 'orange';
                                        $icon = 'fas fa-cogs';
                                    } elseif (strtolower($type) === 'situation') {
                                        $color = 'slate';
                                        $icon = 'fas fa-balance-scale';
                                    }
                                @endphp
                                <div class="glass-card !p-6 flex items-center cursor-pointer filter-card filtre-journaux" data-type="{{ $type }}">
                                    <div class="p-3 rounded-full bg-{{ $color }}-100 text-{{ $color }}-600 mr-4">
                                        <i class="{{ $icon }} text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500">{{ ucfirst($type) }}</p>
                                        <h3 class="text-2xl font-bold text-slate-800">{{ $count }}</h3>
                                    </div>
                                </div>
                            @endforeach
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
                                <!-- Bouton Charger depuis l'Admin [NOUVEAU] -->
                                @if (session('current_company_id') && session('current_company_id') != auth()->user()->company_id)
                                <button type="button" id="btnSyncAdminJournals"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-indigo-50 border border-indigo-200 rounded-2xl text-indigo-700 font-semibold text-sm">
                                    <i class="fas fa-sync-alt"></i>
                                    Charger Modèle Admin
                                </button>
                                @endif

                                <!-- Success Message Container -->
                                <div id="successMessage" class="hidden fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="successText">Code journal enregistré</span>
                                </div>
                                
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
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Codes Journaux</h3>
                                <p class="text-sm text-slate-500">Liste et configuration des journaux comptables</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left" id="JournalTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Type</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Code</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Compte</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">ETAT DE RAPPROCHEMENT BANCAIRE</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($code_journaux as $journal)
                                            <tr class="table-row">
                                                <td class="px-8 py-6" data-filter="{{ $journal->type }}">
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
                                                <td class="px-8 py-6 font-mono text-sm text-slate-600">
                                                    {{ $journal->code_tresorerie_display ?? '-' }}
                                                </td>
                                                <td class="px-8 py-6 font-semibold text-slate-700">
                                                    {{ !empty($journal->rapprochement_sur) ? $journal->rapprochement_sur : '-' }}
                                                </td>
                                                <td class="px-8 py-6 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" class="btn-edit-journal w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm"
                                                            data-id="{{ $journal->id }}"
                                                            data-code="{{ $journal->code_journal }}"
                                                            data-type="{{ $journal->type }}"
                                                            data-intitule="{{ $journal->intitule }}"
                                                            data-traitement="{{ $journal->traitement_analytique }}"
                                                            data-compte_de_contrepartie="{{ $journal->compte_de_contrepartie }}"
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
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative pt-4">
                        <button type="button" class="btn-close position-absolute end-0 top-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Nouveau <span class="text-blue-gradient-premium">Journal</span>
                        </h1>
                        <div class="blue-bar-premium"></div>
                    </div>

                    <div class="modal-body py-0">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Code Journal *</label>
                                <input type="text" id="code_journal_input" name="code_journal" class="input-field-premium uppercase-input" maxlength="4" placeholder="ex: VT" oninput="formatCodeJournal(this)">
                                <div id="code_journal_error" class="text-danger small mt-1" style="display: none;"></div>
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
                            <div class="col-md-6 text-start d-none" id="compte_field">
                                <label class="input-label-premium">Compte</label>
                                <select name="compte_de_contrepartie" class="input-field-premium">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($comptesCinq as $compte)
                                        <option value="{{ $compte->numero_de_compte }}">
                                            {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                        </option>
                                    @endforeach
                                </select>
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
                                        <label class="input-label-premium">ETAT DE RAPPROCHEMENT BANCAIRE</label>
                                        <select name="rapprochement_sur" class="input-field-premium">
                                            <option value="">-- Sélectionner --</option>
                                            <option value="Manuel">Manuel</option>
                                            <option value="Automatique">Automatique</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn bg-slate-100 text-slate-500 font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0 shadow-lg shadow-blue-200 hover:bg-blue-800 transition" style="color: white; background-color: #1d4ed8;">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="modalEditJournalUnique" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <form id="formEditJournalUnique" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative pt-4">
                        <button type="button" class="btn-close position-absolute end-0 top-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Modifier <span class="text-blue-gradient-premium">Journal</span>
                        </h1>
                        <div class="blue-bar-premium"></div>
                    </div>

                    <div class="modal-body py-0">
                        <input type="hidden" name="journal_id" id="update_journal_id">
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Code Journal *</label>
                                <input type="text" id="update_code_journal" name="code_journal" class="input-field-premium uppercase-input" maxlength="4" required>
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
                            <div class="col-md-6 text-start d-none" id="update_compte_field">
                                <label class="input-label-premium">Compte</label>
                                <select id="update_compte_de_contrepartie" name="compte_de_contrepartie" class="input-field-premium">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($comptesCinq as $compte)
                                        <option value="{{ $compte->numero_de_compte }}">
                                            {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="input-label-premium">Traitement analytique</label>
                                <select id="update_traitement_analytique" name="traitement_analytique" class="input-field-premium">
                                    <option value="0">Non</option>
                                    <option value="1">Oui</option>
                                </select>
                            </div>
                            <!-- Conditional Fields Group -->
                            <div class="col-12 text-start d-none" id="update_tresorerie_options">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="input-label-premium">ETAT DE RAPPROCHEMENT BANCAIRE</label>
                                        <select id="update_rapprochement_sur" name="rapprochement_sur" class="input-field-premium">
                                            <option value="">-- Sélectionner --</option>
                                            <option value="Manuel">Manuel</option>
                                            <option value="Automatique">Automatique</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-6">
                        <button type="button" class="btn bg-slate-100 text-slate-500 font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn font-bold uppercase text-[10px] tracking-widest px-6 py-3 rounded-xl border-0 shadow-lg shadow-blue-200 hover:bg-blue-800 transition" style="color: white; background-color: #1d4ed8;">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
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
                        Êtes-vous sûr de vouloir supprimer ce journal ? Cette action est irréversible.
                    </p>
                    <div id="journalToDeleteName" class="text-slate-900 font-bold"></div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <form id="deleteJournalForm" method="POST" action="" class="w-full">
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

    @include('components.footer')

    <script>
        // Global variables
        const accounting_journalsUpdateBaseUrl = "{{ route('accounting_journals.update', ['id' => '__ID__']) }}";
        
        // Wait for jQuery and DataTables to be ready
        function initJournalsPage() {
            if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
                console.log("⏳ En attente de jQuery/DataTables...");
                setTimeout(initJournalsPage, 100);
                return;
            }

            console.log("🚀 Initialisation de la page des journaux...");
            
            /** 1. DataTable Initialization **/
            const table = $('#JournalTable').DataTable({
                dom: 't',
                pageLength: 5,
                order: [], // Conserver l'ordre du serveur (trié par créé_le DESC)
                language: {
                    zeroRecords: "Aucun journal trouvé",
                    infoEmpty: "Aucun journal à afficher"
                }
            });

            const updatePagination = () => {
                const info = table.page.info();
                if (info.recordsDisplay > 0) {
                    $('#tableInfo').html(`Affichage de <span class="font-bold text-slate-700">${info.start + 1}</span> à <span class="font-bold text-slate-700">${info.end}</span> sur <span class="font-bold text-slate-700">${info.recordsDisplay}</span> journaux`);
                    let html = `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 transition ${info.page === 0 ? 'opacity-50 cursor-not-allowed' : ''}" id="prevPage" ${info.page === 0 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
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

            /** 2. Filtering Logic **/
            const applyCustomFilters = () => {
                table.column(0).search($('#filterType').val());
                table.column(1).search($('#filterCode').val());
                table.column(2).search($('#filterIntitule').val());
                table.draw();
            };

            $('#filterType, #filterCode, #filterIntitule').on('keyup change', applyCustomFilters);
            $('#applyFilterBtn').on('click', (e) => { e.preventDefault(); applyCustomFilters(); });

            $('#toggleFilterBtn').on('click', function(e) {
                e.preventDefault();
                $('#advancedFilterPanel').toggleClass('hidden');
                $(this).toggleClass('bg-blue-50 border-blue-200 text-blue-700');
            });

            $('.filter-card').on('click', function() {
                $('.filter-card').removeClass('filter-active');
                $(this).addClass('filter-active');
                const type = $(this).data('type');
                
                if (type === 'all') {
                    table.column(0).search('').draw();
                } else {
                    // Utilisation d'une regex exacte pour correspondre au data-filter
                    table.column(0).search('^' + type + '$', true, false).draw();
                }
            });

            $('#resetFilterBtn').on('click', function(e) {
                e.preventDefault();
                $('#filterType, #filterCode, #filterIntitule').val('');
                $('.filter-card').removeClass('filter-active');
                $('#filter-all').addClass('filter-active');
                table.columns().search('').draw();
            });

            /** 3. Modal & Form Logic **/
            const toggleTresorerieFields = (selectEl) => {
                const isTres = $(selectEl).val() === "Tresorerie";
                const root = $(selectEl).closest('.modal-content');
                if (isTres) {
                    root.find('[id$="tresorerie_options"], [id$="compte_field"]').removeClass('d-none');
                } else {
                    root.find('[id$="tresorerie_options"], [id$="compte_field"]').addClass('d-none');
                }
            };

            $('#type_select, #update_type').on('change', function() { toggleTresorerieFields(this); });

            // Create Form Submission
            $('#formCodeJournal').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                const originalText = btn.html();
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Traitement...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert(res.message || "Erreur lors de l'enregistrement");
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : "Erreur serveur";
                        alert("Erreur: " + msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Update Form Submission
            $('#formEditJournalUnique').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');
                const originalText = btn.html();
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Mise à jour...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST', 
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        if (res.success) {
                            window.location.reload();
                        } else {
                            alert(res.message || "Erreur lors de la modification");
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : "Erreur serveur";
                        alert("Erreur: " + msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Update Trigger (Direct Click for Reliability)
            $(document).on('click', '.btn-edit-journal', function() {
                const btn = $(this);
                const id = btn.data('id');
                const modal = $('#modalEditJournalUnique');
                
                if (!id) return;

                // Pre-fill fields
                modal.find('#update_journal_id').val(id);
                modal.find('#update_code_journal').val(btn.data('code'));
                modal.find('#update_type').val(btn.data('type'));
                modal.find('#update_intitule').val(btn.data('intitule'));
                modal.find('#update_traitement_analytique').val(btn.data('traitement'));
                modal.find('#update_compte_de_contrepartie').val(btn.data('compte_de_contrepartie') || '');
                modal.find('#update_rapprochement_sur').val(btn.data('rapprochement_sur') || '');
                
                toggleTresorerieFields(modal.find('#update_type')[0]);
                
                // Set Action URL
                const url = accounting_journalsUpdateBaseUrl.replace('__ID__', id);
                modal.find('#formEditJournalUnique').attr('action', url);

                // Open Modal
                const bsModal = new bootstrap.Modal(document.getElementById('modalEditJournalUnique'));
                bsModal.show();
            });

            // Delete Modal Load
            $('#deleteConfirmationModal').on('show.bs.modal', function(e) {
                const btn = $(e.relatedTarget);
                $('#journalToDeleteName').text(btn.data('name'));
                $('#deleteJournalForm').attr('action', `/accounting_journals/${btn.data('id')}`);
            });

            // Sync Admin Journals
            $('#btnSyncAdminJournals').on('click', function(e) {
                e.preventDefault();
                if (!confirm("Synchroniser les codes journaux ?")) return;
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sync...');
                $.ajax({
                    url: '/admin/config/sync/journals',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() { window.location.reload(); },
                    error: function() { alert("Erreur de synchronisation"); btn.prop('disabled', false).html('Synchroniser'); }
                });
            });

            /** 4. Input Formatting **/
            const enforceUpperAlpha = (selector) => {
                $(selector).on('input', function() {
                    this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, 4);
                });
            };
            enforceUpperAlpha('#code_journal_input');
            enforceUpperAlpha('#update_code_journal');
        }

        // Run initialization
        initJournalsPage();
    </script>
</body>
</html>
