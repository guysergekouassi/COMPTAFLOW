<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<!-- Intégration de Tailwind sans réinitialisation des styles par défaut -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false,
        }
    }
</script>

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #0f172a;
    }
    
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
    
    .table-row {
        transition: background-color 0.2s;
    }
    
    .table-row:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }
    
    /* DataTable Customization */
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

    .status-pill {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .btn-action {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-action:hover {
        transform: translateY(-2px);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Journal de <span class="text-gradient">Trésorerie</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Gestion des flux monétaires et rapprochements</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Badge Section -->
                        <div class="text-start mb-8 -mt-4 px-3">
                            <p class="text-slate-500 font-medium">
                                Gestion des flux monétaires et rapprochements.
                            </p>
                        </div>

                        <!-- Messages Flash -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-6 mx-3" role="alert">
                                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-6 mx-3" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Zone d'Actions et Filtres -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4 px-3">
                            <div>
                                <button onclick="toggleFilters()" class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i> Filtrer
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#Plan_defaut_Tresorerie" class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-cloud-download-alt text-blue-600"></i> Par défaut
                                </button>

                                <button type="button" data-bs-toggle="modal" data-bs-target="#periodSelectionModal" class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-file-invoice-dollar text-emerald-600"></i> Plan Trésor.
                                </button>
                                
                                <button data-bs-toggle="modal" data-bs-target="#createModal" class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus-circle"></i> Ajouter
                                </button>
                            </div>
                        </div>

                        <!-- Panneau de Filtres (Masqué par défaut) -->
                        <div id="filterPanel" class="hidden mb-8 transition-all duration-300 px-3">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="relative w-full">
                                        <input type="text" id="filterFlux" placeholder="Filtrer par Flux / Type..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-exchange-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <input type="text" id="filterCode" placeholder="Filtrer par Code Journal..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <input type="text" id="filterIntitule" placeholder="Filtrer par Intitule..." class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-font absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <select id="filterPoste" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm appearance-none cursor-pointer">
                                            <option value="">Tous les Postes</option>
                                            @foreach($comptesTresorerie as $compte)
                                                <option value="{{ $compte->name }}">{{ $compte->name }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-bank absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 mt-4">
                                    <button id="resetFilterBtn" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-semibold hover:bg-slate-200 transition">
                                        <i class="fas fa-undo mr-2"></i>Réinitialiser
                                    </button>
                                    <button id="applyFilterBtn" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                        <i class="fas fa-search mr-2"></i>Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau Journal Trésorerie -->
                        <div class="glass-card overflow-hidden mx-3">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" id="journalTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Code Journal</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Analytique</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Compte</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Poste</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Flux</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Rapprochement</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($tresoreries as $journal)
                                            <tr class="table-row group">
                                                <td class="px-8 py-6">
                                                    <span class="font-mono text-lg font-bold text-blue-700">{{ $journal->code_journal }}</span>
                                                </td>
                                                <td class="px-8 py-6 font-semibold text-slate-800">{{ $journal->intitule }}</td>
                                                <td class="px-8 py-6">
                                                    <span class="status-pill {{ $journal->traitement_analytique == 'oui' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200' }} border">
                                                        {{ strtoupper($journal->traitement_analytique) }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-6 font-mono text-sm text-slate-600">{{ $journal->compte_de_contrepartie }}</td>
                                                <td class="px-8 py-6 font-semibold text-slate-700">
                                                    {{ !empty($journal->poste_tresorerie) ? $journal->poste_tresorerie : '-' }}
                                                </td>
                                                <td class="px-8 py-6">
                                                    @php
                                                        $valFlux = $journal->type_flux;
                                                        if(empty($valFlux)) $valFlux = 'Non défini';
                                                        
                                                        $fluxClass = match($valFlux) {
                                                            'Entrée', 'Encaissement', 'Crédit' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                            'Sortie', 'Décaissement', 'Débit' => 'bg-orange-100 text-orange-700 border-orange-200',
                                                            default => 'bg-slate-100 text-slate-500 border-slate-200'
                                                        };
                                                    @endphp
                                                    <span class="status-pill {{ $fluxClass }} border">{{ $valFlux }}</span>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <span class="status-pill {{ $journal->rapprochement_sur == 'automatique' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-500 border-slate-200' }} border">
                                                        {{ strtoupper($journal->rapprochement_sur) }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-6 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <button data-bs-toggle="modal" data-bs-target="#editModal{{ $journal->id }}" class="w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm bg-white">
                                                            <i class="fas fa-edit text-xs"></i>
                                                        </button>
                                                        <button type="button" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteConfirmationModal" 
                                                                data-id="{{ $journal->id }}" 
                                                                data-code-journal="{{ $journal->code_journal }}" 
                                                                onclick="setDeleteAction(this)"
                                                                class="w-10 h-10 flex items-center justify-center rounded-xl border border-red-100 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm bg-white">
                                                            <i class="fas fa-trash-alt text-xs"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-8 py-12 text-center text-slate-400 font-bold italic">
                                                    <i class="fas fa-folder-open text-3xl mb-3 block"></i>
                                                    Aucun journal de trésorerie configuré
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Info Integration -->
                            <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                                <p class="text-sm text-slate-500 font-medium italic">
                                    <i class="fas fa-info-circle mr-1 text-blue-500"></i> <span id="tableInfo">Calcul en cours...</span>
                                </p>
                                <div id="customPagination" class="flex gap-2">
                                    <!-- Pagination injectée par JS -->
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                @include('components.footer')

            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- MODALS -->

    <!-- Modal : Ajouter Journal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content premium-modal-content">
                <form action="{{ route('storetresorerie') }}" method="POST">
                    @csrf
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Nouveau <span class="text-gradient">Journal</span>
                        </h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label-premium">Code Journal *</label>
                                <input type="text" name="code_journal" class="input-field-premium" required placeholder="ex: BQ01">
                            </div>
                            <div>
                                <label class="input-label-premium">Intitulé *</label>
                                <input type="text" name="intitule" class="input-field-premium" required placeholder="ex: Banque SG">
                            </div>
                        </div>

                        <div>
                            <label class="input-label-premium">Compte de contrepartie *</label>
                            <select name="compte_de_contrepartie" class="input-field-premium" required>
                                <option value="" disabled selected>-- Sélectionner --</option>
                                @foreach($comptesCinq as $compte)
                                    <option value="{{ $compte->numero_de_compte }}">
                                        {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label-premium">Flux / Type</label>
                                <select name="type_flux" class="input-field-premium" required>
                                    <option value="Encaissement">Encaissement</option>
                                    <option value="Décaissement">Décaissement</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label-premium">Analytique</label>
                                <select name="traitement_analytique" class="input-field-premium" required>
                                    <option value="non">Non</option>
                                    <option value="oui">Oui</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label-premium">Poste Trésorerie</label>
                                <select name="poste_tresorerie" class="input-field-premium">
                                    <option value="">-- Aucun --</option>
                                    @foreach($comptesTresorerie as $compte)
                                        <option value="{{ $compte->name }}">
                                            {{ $compte->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="input-label-premium">Rapprochement</label>
                                <select name="rapprochement_sur" class="input-field-premium" required>
                                    <option value="manuel">Manuel</option>
                                    <option value="automatique">Automatique</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn-save-premium shadow-blue-200">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals : Modifier Journal -->
    @foreach($tresoreries as $journal)
        <div class="modal fade" id="editModal{{ $journal->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
                <div class="modal-content premium-modal-content">
                    <form action="{{ route('update_tresorerie', $journal->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="text-center mb-6 position-relative">
                            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal"></button>
                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                Modifier <span class="text-gradient">Journal</span>
                            </h1>
                            <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="input-label-premium">Code Journal *</label>
                                    <input type="text" name="code_journal" class="input-field-premium" value="{{ $journal->code_journal }}" required>
                                </div>
                                <div>
                                    <label class="input-label-premium">Intitulé *</label>
                                    <input type="text" name="intitule" class="input-field-premium" value="{{ $journal->intitule }}" required>
                                </div>
                            </div>

                            <div>
                                <label class="input-label-premium">Compte de contrepartie *</label>
                                <select name="compte_de_contrepartie" class="input-field-premium" required>
                                    @foreach($comptesCinq as $compte)
                                        <option value="{{ $compte->numero_de_compte }}" {{ $journal->compte_de_contrepartie == $compte->numero_de_compte ? 'selected' : '' }}>
                                            {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="input-label-premium">Flux / Type</label>
                                    <select name="type_flux" class="input-field-premium" required>
                                        <option value="Encaissement" {{ ($journal->type_flux == 'Encaissement' || $journal->type_flux == 'Entrée') ? 'selected' : '' }}>Encaissement</option>
                                        <option value="Décaissement" {{ ($journal->type_flux == 'Décaissement' || $journal->type_flux == 'Sortie') ? 'selected' : '' }}>Décaissement</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="input-label-premium">Analytique</label>
                                    <select name="traitement_analytique" class="input-field-premium" required>
                                        <option value="non" {{ $journal->traitement_analytique == 'non' ? 'selected' : '' }}>Non</option>
                                        <option value="oui" {{ $journal->traitement_analytique == 'oui' ? 'selected' : '' }}>Oui</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="input-label-premium">Poste Trésorerie</label>
                                    <select name="poste_tresorerie" class="input-field-premium">
                                        <option value="">-- Aucun --</option>
                                        @foreach($comptesTresorerie as $compte)
                                            <option value="{{ $compte->name }}" {{ $journal->poste_tresorerie == $compte->name ? 'selected' : '' }}>
                                                {{ $compte->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="input-label-premium">Rapprochement</label>
                                    <select name="rapprochement_sur" class="input-field-premium" required>
                                        <option value="manuel" {{ $journal->rapprochement_sur == 'manuel' ? 'selected' : '' }}>Manuel</option>
                                        <option value="automatique" {{ $journal->rapprochement_sur == 'automatique' ? 'selected' : '' }}>Automatique</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-8">
                            <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn-save-premium shadow-blue-200">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal : Sélection de période (Plan de Trésorerie) -->
    <div class="modal fade" id="periodSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content premium-modal-content">
                <form id="cashFlowForm">
                    <div class="text-center mb-6">
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Plan <span class="text-gradient">Trésorerie</span></h1>
                        <p class="text-xs text-slate-400 font-medium mt-1">Définissez la période du rapport PDF.</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Date de Début</label>
                            <input type="date" id="start_date" class="input-field-premium" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Date de Fin</label>
                            <input type="date" id="end_date" class="input-field-premium" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" id="previewPdfButton" class="btn-save-premium !bg-emerald-600 shadow-emerald-100">Exporter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal : Prévisualisation PDF (XL) -->
    <div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content premium-modal-content" style="max-width: 90%;">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Aperçu du Rapport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <iframe id="pdfPreviewFrame" class="w-full h-[75vh] border-0 rounded-2xl bg-slate-50" src=""></iframe>
                <div class="flex justify-end gap-3 mt-6">
                    <a href="#" id="exportCsvLink" class="btn-action flex items-center gap-2 px-6 py-3 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-xl font-bold text-xs uppercase tracking-widest transition">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
                    <button type="button" class="btn-cancel-premium !w-auto px-8" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal : Charger par défaut -->
    <div class="modal fade" id="Plan_defaut_Tresorerie" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content premium-modal-content">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-sync-alt text-2xl"></i>
                    </div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Plan par <span class="text-blue-600">Défaut</span></h1>
                    <p class="text-sm text-slate-500 mt-3 px-4">Charger les journaux de trésorerie standards (Banque, Caisse) pour cette compagnie ?</p>
                </div>
                <form method="POST" action="{{ route('journal_tresorerie.defaut') }}" class="grid grid-cols-2 gap-4">
                    @csrf
                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-save-premium">Confirmer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal : Suppression -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
            <div class="modal-content premium-modal-content">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Confirmer la <span class="text-red-600">Suppression</span></h3>
                    <p class="text-sm text-slate-500 mb-4 px-2">Voulez-vous vraiment supprimer le journal <span id="journalCodeToDelete" class="font-bold text-slate-900"></span> ?</p>
                    
                    <form id="deleteForm" method="POST" class="grid grid-cols-2 gap-3 mt-6">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-red-100 uppercase text-[10px] tracking-widest border-0">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        function toggleFilters() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('hidden');
        }

        function setDeleteAction(button) {
            const journalId = button.getAttribute('data-id');
            const journalCode = button.getAttribute('data-code-journal');
            const journalCodeEl = document.getElementById('journalCodeToDelete');
            if (journalCodeEl) journalCodeEl.textContent = journalCode || '';

            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                const deleteUrl = "{{ route('destroy_tresorerie', 'TEMP_ID') }}".replace('TEMP_ID', journalId);
                deleteForm.setAttribute('action', deleteUrl);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // DataTables Initialization like Plan Comptable
            const initDataTable = () => {
                if (typeof $ !== 'undefined' && $.fn.dataTable) {
                    const table = $('#journalTable').DataTable({
                        dom: 't', 
                        pageLength: 5,
                        destroy: true,
                        language: {
                            zeroRecords: "Aucun journal trouvé",
                        }
                    });

                    // Filtering Logic
                    function applyCustomFilters() {
                        table.column(5).search($('#filterFlux').val());
                        table.column(0).search($('#filterCode').val());
                        table.column(1).search($('#filterIntitule').val());
                        table.column(4).search($('#filterPoste').val());
                        table.draw();
                    }

                    $('#filterFlux, #filterCode, #filterIntitule, #filterPoste').on('keyup change', function() {
                        applyCustomFilters();
                    });
                    $('#resetFilterBtn').on('click', function() {
                        $('#filterFlux, #filterCode, #filterIntitule, #filterPoste').val('');
                        applyCustomFilters();
                    });

                    // Custom Pagination
                    function updatePagination() {
                        const info = table.page.info();
                        console.log("Pagination Info:", info);
                        
                        if (info.recordsDisplay > 0) {
                            $('#tableInfo').html(`Affichage de <span class="font-bold text-slate-700">${info.start + 1}</span> à <span class="font-bold text-slate-700">${info.end}</span> sur <span class="font-bold text-slate-700">${info.recordsDisplay}</span> journaux`);
                            
                            let paginationHtml = '';
                            paginationHtml += `<button class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page === 0 ? 'opacity-50 cursor-not-allowed' : ''}" id="prevPage" ${info.page === 0 ? 'disabled' : ''}><i class="fas fa-chevron-left text-[10px]"></i></button>`;
                            paginationHtml += `<button class="px-3 py-1.5 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200 text-xs">${info.page + 1}</button>`;
                            paginationHtml += `<button class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page >= info.pages - 1 ? 'opacity-50 cursor-not-allowed' : ''}" id="nextPage" ${info.page >= info.pages - 1 ? 'disabled' : ''}><i class="fas fa-chevron-right text-[10px]"></i></button>`;
                            $('#customPagination').html(paginationHtml);
                        } else {
                            $('#tableInfo').html('Aucun journal trouvé');
                            $('#customPagination').empty();
                        }
                    }

                    table.on('draw', updatePagination);
                    $(document).on('click', '#prevPage', function() { table.page('previous').draw('page'); });
                    $(document).on('click', '#nextPage', function() { table.page('next').draw('page'); });

                    updatePagination();
                } else {
                    setTimeout(initDataTable, 200);
                }
            };
            initDataTable();

            // Preview PDF Logic
            const previewPdfBtn = document.getElementById('previewPdfButton');
            if (previewPdfBtn) {
                previewPdfBtn.addEventListener('click', function() {
                    const sd = document.getElementById('start_date').value;
                    const ed = document.getElementById('end_date').value;
                    if (!sd || !ed) { alert('Dates requises'); return; }

                    const streamingUrl = "{{ route('generate_cash_flow_pdf') }}?start_date=" + encodeURIComponent(sd) + "&end_date=" + encodeURIComponent(ed);
                    const exportCsvBaseUrl = "{{ route('export_cash_flow_csv') }}";
                    const csvUrl = exportCsvBaseUrl + "?start_date=" + encodeURIComponent(sd) + "&end_date=" + encodeURIComponent(ed);

                    const iframe = document.getElementById('pdfPreviewFrame');
                    if (iframe) iframe.src = streamingUrl;

                    const exportCsvLink = document.getElementById('exportCsvLink');
                    if (exportCsvLink) exportCsvLink.href = csvUrl;

                    const previewModal = new bootstrap.Modal(document.getElementById('modalPreviewPDF'));
                    previewModal.show();
                    bootstrap.Modal.getInstance(document.getElementById('periodSelectionModal')).hide();
                });
            }
        });
    </script>
</body>
</html>
