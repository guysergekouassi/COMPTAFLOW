<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<style>
    /* Premium Modal Styles */
    .premium-modal-content-wide {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .input-field-premium {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background-color: white;
    }

    .input-field-premium:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .input-label-premium {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .btn-save-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.3);
        width: 100%;
    }

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.4);
    }

    .btn-cancel-premium {
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        width: 100%;
    }

    .btn-cancel-premium:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .text-blue-gradient-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Search dropdown styles */
    .search-select-container {
        position: relative;
    }

    .search-select-dropdown {
        position: absolute;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1060;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-top: 5px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .search-select-dropdown .list-group-item {
        border: none;
        padding: 10px 15px;
        font-size: 0.95rem;
        transition: background 0.2s;
    }

    .search-select-dropdown .list-group-item:hover {
        background-color: #f0f2ff;
        color: #696cff;
    }
</style>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      @include('components.sidebar')
      <div class="layout-page">
                     @include('components.header', ['page_title' => 'Liste des <span class="text-gradient">écritures</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Gestion comptable</span>'])
                    <div class="content-wrapper">
                        <style>
                            .glass-card {
                                background: #ffffff;
                                border: 1px solid #e2e8f0;
                                border-radius: 16px;
                                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                                transition: all 0.3s ease;
                            }

                            .table-row {
                                transition: background-color 0.2s;
                            }

                            .table-row:hover {
                                background-color: #f1f5f9;
                            }

                            #tableEcritures_wrapper .dataTables_length,
                            #tableEcritures_wrapper .dataTables_filter {
                                display: none;
                            }

                            #tableEcritures {
                                border-collapse: separate !important;
                                border-spacing: 0 !important;
                            }

                            #tableEcritures thead th {
                                background-color: #f8fafc;
                                border-bottom: 2px solid #e2e8f0;
                                padding: 1.25rem 2rem !important;
                                font-size: 0.875rem !important;
                                font-weight: 700 !important;
                                color: #64748b !important;
                                text-transform: uppercase !important;
                                letter-spacing: 0.05em !important;
                            }
                            
                            #tableEcritures tbody td {
                                padding: 1rem 1rem !important;
                                vertical-align: middle !important;
                                border-bottom: 1px solid #f1f5f9 !important;
                                font-size: 0.85rem !important;
                                color: #1e293b !important;
                                font-weight: 500 !important;
                            }

                            /* Table Premium - Refonte Conteneur */
                            .table-container-premium {
                                background: #ffffff !important;
                                border-radius: 24px !important;
                                border: 1px solid #e2e8f0 !important;
                                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
                                overflow: hidden !important;
                                margin-bottom: 2rem !important;
                            }

                            .table-responsive-premium {
                                overflow-x: auto !important;
                                -webkit-overflow-scrolling: touch;
                                scrollbar-width: thin;
                                scrollbar-color: #888 #f5f5f5;
                            }

                            .table-responsive-premium::-webkit-scrollbar {
                                height: 8px;
                            }
                            .table-responsive-premium::-webkit-scrollbar-thumb {
                                background-color: #cbd5e1;
                                border-radius: 4px;
                            }

                            .table-badge {
                                padding: 0.35rem 0.6rem !important;
                                border-radius: 8px !important;
                                font-weight: 700 !important;
                                font-size: 0.7rem !important;
                                display: inline-flex !important;
                                align-items: center !important;
                                gap: 0.3rem !important;
                                white-space: nowrap !important;
                            }

                            .badge-journal { background-color: #eff6ff !important; color: #1e40af !important; border: 1px solid #dbeafe !important; }
                            .badge-compte { background-color: #f8fafc !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }
                            .amount-debit { color: #dc2626 !important; font-weight: 700 !important; text-align: right !important; }
                            .amount-credit { color: #059669 !important; font-weight: 700 !important; text-align: right !important; }

                            /* Nouveau Design Premium pour les Modaux */
                            .premium-modal-content, .premium-modal-content-wide {
                                background: rgba(255, 255, 255, 0.98);
                                backdrop-filter: blur(15px);
                                border: 1px solid rgba(255, 255, 255, 1);
                                border-radius: 20px;
                                box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
                                font-family: 'Plus Jakarta Sans', sans-serif;
                            }
                            
                            .premium-modal-content {
                                max-width: 400px;
                                margin: auto;
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
                            }

                            .btn-cancel-premium:hover {
                                background-color: #f8fafc !important;
                                color: #475569 !important;
                            }
                        </style>

                        <div class="container-fluid flex-grow-1 container-p-y">
                            
                            <div class="text-center mb-8 -mt-4">
                                <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                    Consultez, saisissez et gérez vos écritures comptables avec précision.
                                </p>
                                <div class="mt-5 inline-flex items-center gap-3 px-5 py-2.5 bg-blue-50 border border-blue-100 rounded-2xl text-blue-700 shadow-sm transition-all hover:shadow-md">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-md shrink-0">
                                        <i class='bx bx-calendar text-xl'></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[10px] font-bold uppercase tracking-wider leading-tight opacity-80 mb-0">Exercice en cours</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-lg font-black leading-tight mb-0">
                                                {{ $exerciceActif->intitule ?? '-' }}
                                            </p>
                                            @if(isset($exerciceActif) && $exerciceActif->is_active)
                                                <div class="flex items-center gap-1.5 px-2 py-0.5 bg-green-100 border border-green-200 rounded-full">
                                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                                                    <span class="text-[9px] font-bold text-green-700 uppercase">Activé</span>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-slate-500 font-medium italic mt-1 mb-0">{{ isset($exerciceActif) && data_get($exerciceActif, 'date_debut') ? \Carbon\Carbon::parse($exerciceActif->date_debut)->format('d/m/Y') : '-' }} - {{ isset($exerciceActif) && data_get($exerciceActif, 'date_fin') ? \Carbon\Carbon::parse($exerciceActif->date_fin)->format('d/m/Y') : '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <style>
                                #tableEcritures,
                                #tableEcritures * {
                                    filter: none !important;
                                    -webkit-filter: none !important;
                                    backdrop-filter: none !important;
                                    -webkit-backdrop-filter: none !important;
                                }

                                button,
                                .btn {
                                    filter: none !important;
                                    -webkit-filter: none !important;
                                }
                            </style>
                            
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 w-full gap-4">
                                <div class="px-6 py-4 flex items-center gap-6 w-full md:w-auto">
                                </div>
    
                                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                                    </div>
                            </div>

                            <div class="flex justify-between items-center mb-8 w-full gap-4">
                                <div class="flex items-center">
                                    <button type="button" id="toggleFilterBtn" onclick="window.toggleAdvancedFilter()"
                                        class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                        <i class="fas fa-filter text-blue-600"></i>
                                        Filtrer
                                    </button>
                                </div>

                                <div class="flex flex-wrap items-center justify-end gap-3">
                                    </div>
                            </div>

                            <div id="advancedFilterPanel" style="display: none;" class="mb-8 transition-all duration-300">
                                <div class="glass-card p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="relative w-full">
                                            <input type="text" id="filterNumeroSaisie" placeholder="Numéro de saisie" 
                                                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm"
                                                value="{{ $data['numero_saisie'] ?? '' }}">
                                            <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        </div>

                                        <div class="relative w-full">
                                            <select id="filterMois" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                                <option value="">Tous les mois</option>
                                                @for ($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ (isset($data['mois']) && (string) $data['mois'] === (string) $i) ? 'selected' : '' }}>
                                                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                            <i class="fas fa-clock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        </div>

                                        <div class="relative w-full">
                                            <input type="text" id="filterCodeJournal" placeholder="Code journal" 
                                                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm"
                                                value="{{ $data['code_journal'] ?? '' }}">
                                            <i class="fas fa-book absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        </div>

                                        <div class="relative w-full md:col-span-3">
                                            <input type="text" id="filterGlobalSearch" placeholder="Recherche globale (N° compte, tiers, description...)" 
                                                class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm"
                                                value="{{ $data['recherche'] ?? '' }}">
                                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        </div>

                                        <div class="relative w-full md:col-span-3">
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">État du Poste Trésorerie</label>
                                            <div class="flex p-1 bg-slate-100 rounded-xl w-full md:w-fit">
                                                <input type="hidden" id="filterEtatPoste" value="{{ $data['etat_poste'] ?? '' }}">
                                                <button type="button" data-value="tous" onclick="setEtatPoste('tous')" 
                                                    class="etat-poste-btn flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all {{ ($data['etat_poste'] ?? '') === 'tous' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                                    Tous
                                                </button>
                                                <button type="button" data-value="defini" onclick="setEtatPoste('defini')" 
                                                    class="etat-poste-btn flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all {{ ($data['etat_poste'] ?? '') === 'defini' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                                    Définis
                                                </button>
                                                <button type="button" data-value="non_defini" onclick="setEtatPoste('non_defini')" 
                                                    class="etat-poste-btn flex-1 md:flex-none px-6 py-2 rounded-lg text-sm font-bold transition-all {{ ($data['etat_poste'] ?? '') === 'non_defini' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                                    Non Définis
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-3 mt-4">
                                        <button type="button" class="btn btn-secondary rounded-xl px-6 font-semibold" onclick="window.resetAdvancedFilters()">
                                            <i class="fas fa-undo me-2"></i>Réinitialiser
                                        </button>
                                        <button type="button" class="btn btn-primary rounded-xl px-6 font-semibold" onclick="window.applyAdvancedFilters()">
                                            <i class="fas fa-search me-2"></i>Rechercher
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="glass-card overflow-hidden">
                                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-800">Liste des écritures</h3>
                                        <p class="text-sm text-slate-500">Consultation et suivi des écritures comptables</p>
                                    </div>
                                    <div class="bg-blue-50 px-4 py-2 rounded-xl">
                                        <span class="text-blue-700 font-bold">{{ $totalEntries ?? 0 }}</span>
                                        <span class="text-blue-600 text-sm font-medium"> écritures au total</span>
                                    </div>
                                </div>
                            <div class="table-container-premium px-0 pb-0">
                                <div class="table-responsive-premium">
                                    <table class="w-full text-left border-collapse" id="tableEcritures" style="min-width: 100%; width: max-content;">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Saisie</th>
                                            <th class="text-center">Statut</th>
                                            <th>Code Journal</th>
                                            <th>Poste Trésorerie</th>
                                            <th>Réf. Pièce</th>
                                            <th>Description</th>
                                            <th>Compte Général</th>
                                            <th>Compte Tiers</th>
                                            <th>An.</th>
                                            <th class="text-right">Débit</th>
                                            <th class="text-right">Crédit</th>
                                            <th class="text-center">Pièce</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $groupedEcritures = ($ecritures ?? collect())->groupBy('n_saisie');
                                        @endphp
                                        
                                        @foreach ($groupedEcritures as $nSaisie => $group)
                                            @php
                                                $first = $group->first();
                                                $rowCount = $group->count();
                                            @endphp
                                            
                                            @foreach ($group as $index => $ecriture)
                                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                                    @if($index === 0)
                                                        <td class="px-4 py-3 text-sm text-slate-700" rowspan="{{ $rowCount }}">{{ $ecriture->date }}</td>
                                                        <td class="px-4 py-3 text-sm" rowspan="{{ $rowCount }}">
                                                            <div class="font-bold text-slate-900">{{ $ecriture->n_saisie }}</div>
                                                            <div class="text-[10px] text-slate-500 font-medium mt-1">{{ $ecriture->n_saisie_user }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center" rowspan="{{ $rowCount }}">
                                                            @php
                                                                $status = $ecriture->statut ?? 'pending';
                                                                $badgeClass = match($status) {
                                                                    'valid', 'approved' => 'bg-green-100 text-green-700',
                                                                    'rejected' => 'bg-red-100 text-red-700',
                                                                    default => 'bg-slate-100 text-slate-700'
                                                                };
                                                                $statusLabel = match($status) {
                                                                    'valid', 'approved' => 'Validé',
                                                                    'rejected' => 'Rejeté',
                                                                    default => 'En Attente'
                                                                };
                                                                $iconClass = match($status) {
                                                                    'valid', 'approved' => 'bx-check-circle',
                                                                    'rejected' => 'bx-x-circle',
                                                                    default => 'bx-time'
                                                                };
                                                            @endphp
                                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $badgeClass }}">
                                                                <i class='bx {{ $iconClass }} text-sm'></i>
                                                                {{ $statusLabel }}
                                                            </span>
                                                            @if($ecriture->admin_modified)
                                                                <div class="mt-1">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800" title="Cette écriture a été modifiée par un administrateur avant validation">
                                                                        <i class="bx bx-edit-alt mr-1"></i> Modifié par Admin
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ $rowCount }}">
                                                            <div class="flex flex-col">
                                                                <span class="table-badge badge-journal">{{ $ecriture->codeJournal ? $ecriture->codeJournal->code_journal : '-' }}</span>
                                                                @if($ecriture->codeJournal && !empty($ecriture->codeJournal->numero_original))
                                                                    <div class="text-[10px] text-slate-400 font-medium italic mt-1 flex items-center gap-1">
                                                                        <i class="fas fa-file-import text-[8px]"></i> Orig: {{ $ecriture->codeJournal->numero_original }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    @endif

                                                     <td class="td-poste-treso" data-ecriture-id="{{ $ecriture->id }}">
                                                            <div class="flex items-center gap-2 group">
                                                                @if($ecriture->posteTresorerie)
                                                                    <span class="table-badge badge-compte">
                                                                        {{ $ecriture->posteTresorerie->name }}
                                                                        @if($ecriture->posteTresorerie->category)
                                                                            - {{ $ecriture->posteTresorerie->category->name }}
                                                                        @endif
                                                                    </span>
                                                                    <button type="button" class="btn btn-xs btn-icon btn-label-secondary transition-opacity" 
                                                                        onclick="window.quickEditPoste({{ $ecriture->id }}, {{ $ecriture->poste_tresorerie_id }}, '{{ addslashes($ecriture->posteTresorerie->name) }}', {{ $ecriture->posteTresorerie->category_id }}, '{{ $ecriture->posteTresorerie->syscohada_line_id ?? '' }}')" title="Modifier le poste">
                                                                        <i class="bx bx-edit-alt text-xs"></i>
                                                                    </button>
                                                                @elseif($ecriture->compteTresorerie)
                                                                    <span class="table-badge badge-compte" title="Compte de trésorerie (Banque)">{{ $ecriture->compteTresorerie->name }}</span>
                                                                    <button type="button" class="btn btn-xs btn-icon btn-label-primary transition-opacity" 
                                                                        onclick="window.quickCreatePoste({{ $ecriture->id }}, {{ $ecriture->compte_tresorerie_id }}, '{{ addslashes($ecriture->compteTresorerie->name) }}')" title="Créer un poste pour ce compte">
                                                                        <i class="bx bx-plus text-xs"></i>
                                                                    </button>
                                                                @else
                                                                    @php
                                                                        $isClass5 = $ecriture->planComptable && str_starts_with($ecriture->planComptable->numero_de_compte, '5');
                                                                    @endphp
                                                                    @if($isClass5)
                                                                        <span class="text-slate-400 small">Non défini</span>
                                                                        <button type="button" class="btn btn-xs btn-icon btn-label-warning transition-opacity" 
                                                                            onclick="window.quickCreatePoste({{ $ecriture->id }}, null)" title="Créer un poste trésorerie">
                                                                            <i class="bx bx-plus text-xs"></i>
                                                                        </button>
                                                                    @else
                                                                        <span class="text-slate-200">-</span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </td>

                                                        @if($index === 0)
                                                            <td rowspan="{{ $rowCount }}">{{ $ecriture->reference_piece }}</td>
                                                        @endif

                                                    <td class="description-operation">
                                                        @php
                                                            $numeroCompte = $ecriture->planComptable ? $ecriture->planComptable->numero_de_compte : '';
                                                            $intituleCompte = $ecriture->planComptable ? strtoupper($ecriture->planComptable->intitule) : '';
                                                            $descUpper = strtoupper($ecriture->description_operation);
                                                            
                                                            // Détection robuste : Compte de TVA (443, 445) ou compte 44 avec "TVA" dans l'intitulé
                                                            $isVatLine = (str_starts_with($numeroCompte, '443') || str_starts_with($numeroCompte, '445') || 
                                                                         (str_starts_with($numeroCompte, '44') && str_contains($intituleCompte, 'TVA')));
                                                            
                                                            $hasPrefix = str_starts_with($descUpper, 'TVA');
                                                        @endphp
                                                        {{ ($isVatLine && !$hasPrefix) ? 'TVA / ' : '' }}{{ $ecriture->description_operation }}
                                                    </td>
                                                    <td>
                                                        <div class="flex flex-col">
                                                            <span class="font-bold text-slate-800">{{ $ecriture->planComptable ? $ecriture->planComptable->numero_de_compte : '-' }}</span>
                                                            <span class="text-[10px] text-slate-500 truncate" style="max-width: 150px;" title="{{ $ecriture->planComptable ? $ecriture->planComptable->intitule : '' }}">
                                                                {{ $ecriture->planComptable ? $ecriture->planComptable->intitule : '' }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="flex flex-col">
                                                            <span class="font-bold text-slate-800">{{ $ecriture->planTiers ? $ecriture->planTiers->numero_de_tiers : '-' }}</span>
                                                            <span class="text-[10px] text-slate-500 truncate" style="max-width: 150px;" title="{{ $ecriture->planTiers ? $ecriture->planTiers->intitule : '' }}">
                                                                {{ $ecriture->planTiers ? $ecriture->planTiers->intitule : '' }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">{{ (int) $ecriture->plan_analytique === 1 ? 'Oui' : 'Non' }}</td>
                                                    <td class="amount-debit">{{ number_format((float) $ecriture->debit, 2, ',', ' ') }}</td>
                                                    <td class="amount-credit">{{ number_format((float) $ecriture->credit, 2, ',', ' ') }}</td>
                                                    
                                                    @if($index === 0)
                                                        <td class="text-center" rowspan="{{ $rowCount }}">
                                                            @if ($ecriture->piece_justificatif)
                                                                <a href="{{ asset('justificatifs/' . $ecriture->piece_justificatif) }}" target="_blank" 
                                                                   class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors" title="Voir la pièce">
                                                                    <i class='bx bx-file text-lg'></i>
                                                                </a>
                                                            @else
                                                                <span class="text-slate-300">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center whitespace-nowrap" rowspan="{{ $rowCount }}">
                                                            <div class="flex items-center justify-center gap-1">
                                                                <a href="{{ route('accounting_entry_real') }}?n_saisie={{ $ecriture->n_saisie }}" 
                                                                   class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-all" title="Modifier l'écriture">
                                                                    <i class="bx bx-edit-alt text-lg"></i>
                                                                </a>
                                                                <a href="{{ route('ecriture.show', $ecriture->id) }}" 
                                                                   class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-all" title="Détails">
                                                                    <i class="bx bx-show text-lg"></i>
                                                                </a>
                                                                <button onclick="confirmDeleteBySaisie('{{ $ecriture->n_saisie }}')" 
                                                                        class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-all" title="Supprimer">
                                                                    <i class="bx bx-trash text-lg"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            
                                            <!-- Separator between entries -->
                                            <tr>
                                                <td colspan="13" class="border-0 p-0">
                                                    <div class="border-t-2 border-slate-200 my-0"></div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (!isset($ecritures) || $ecritures->isEmpty())
                                            <tr>
                                                <td colspan="13" class="text-center text-slate-500 py-6">
                                                    Aucune écriture trouvée pour les critères sélectionnés
                                                </td>
                                            </tr>
                                        @endif
                            </tbody>
                            <tfoot>
                                </tfoot>
                        </table>
                    </div>
                </div>
                    
                    @if(isset($pagination) && $pagination->hasPages())
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                            {{ $pagination->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
          </div>
          </div>
        <div class="layout-overlay layout-menu-toggle"></div>
      </div>
      @include('components.footer')

      <div class="modal fade" id="nouvelleEcritureModal" tabindex="-1" aria-labelledby="nouvelleEcritureModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" style="max-width: 98vw; width: 98vw; margin: auto;">
              <div class="modal-content premium-modal-content-wide" style="padding: 1.5rem; max-height: 90vh; overflow-y: auto;">
                  <form id="formNouvelleEcriture">
                      <input type="hidden" id="hiddenNumeroSaisie" name="numero_saisie" />
                      <input type="hidden" id="hiddenCodeJournal" name="code_journal" />

                      <div class="text-center mb-3 position-relative">
                          <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer" style="top: -0.5rem; right: -0.5rem;"></button>
                          <h1 class="text-xl font-extrabold tracking-tight text-slate-900" style="font-size: 1.5rem; font-weight: 800;">
                              Nouvelle <span class="text-blue-gradient-premium">Écriture</span>
                          </h1>
                      </div>

                      <div class="modal-body" style="padding: 0;">
                          <div class="row g-2 mb-3">
                              <div class="col-md-4">
                                  <label for="dateEcriture" class="input-label-premium" style="font-size: 0.7rem;">Date</label>
                                  <input type="date" id="dateEcriture" name="date" class="input-field-premium" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-4">
                                  <label for="journalEcriture" class="input-label-premium" style="font-size: 0.7rem;">Journal</label>
                                  <input type="text" id="journalEcriture" name="journal" class="input-field-premium" readonly style="padding: 0.625rem 0.875rem; font-size: 0.8rem; background-color: #f8f9fa;" />
                              </div>
                              <div class="col-md-4">
                                  <label for="numeroSaisie" class="input-label-premium" style="font-size: 0.7rem;">N° Saisie</label>
                                  <input type="text" id="numeroSaisie" name="numero_saisie" class="input-field-premium" readonly style="padding: 0.625rem 0.875rem; font-size: 0.8rem; background-color: #f8f9fa;" />
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-12">
                                  <label for="libelleEcriture" class="input-label-premium" style="font-size: 0.7rem;">Libellé / Intitulé de l'opération</label>
                                  <input type="text" id="libelleEcriture" name="libelle" class="input-field-premium" placeholder="Entrez le libellé de l'écriture..." required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-md-6">
                                  <label for="compteGeneralSearch" class="input-label-premium" style="font-size: 0.7rem;">Compte Général</label>
                                  <div class="search-select-container">
                                      <div class="input-group">
                                          <span class="input-group-text" style="background-color: #f8f9fa; border-radius: 8px 0 0 8px; border-right: none; color: #3b82f6;"><i class="bx bx-search"></i></span>
                                          <input type="text" id="compteGeneralSearch" class="form-control" placeholder="Rechercher un compte général (ex: 701...)" autocomplete="off" style="border-radius: 0 8px 8px 0 !important; padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                          <input type="hidden" id="compteGeneralEcriture" name="compte_general" required>
                                      </div>
                                      <div class="search-select-dropdown" id="compteGeneralDropdown" style="display: none;">
                                          <div class="list-group">
                                              @if(isset($plansComptables))
                                                  @foreach ($plansComptables as $plan)
                                                      <a href="#" class="list-group-item list-group-item-action option-compte" 
                                                         data-value="{{ $plan->id }}" 
                                                         data-numero="{{ $plan->numero_de_compte }}">
                                                          <strong>{{ $plan->numero_de_compte }}</strong> - {{ $plan->intitule }}
                                                      </a>
                                                  @endforeach
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <label for="compteTiersSearch" class="input-label-premium" style="font-size: 0.7rem;">Compte Tiers</label>
                                  <div class="search-select-container">
                                      <div class="input-group">
                                          <span class="input-group-text" style="background-color: #f8f9fa; border-radius: 8px 0 0 8px; border-right: none; color: #3b82f6;"><i class="bx bx-search"></i></span>
                                          <input type="text" id="compteTiersSearch" class="form-control" placeholder="Rechercher un tiers (Client, Fournisseur...)" autocomplete="off" style="border-radius: 0 8px 8px 0 !important; padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                          <input type="hidden" id="compteTiersEcriture" name="compte_tiers">
                                      </div>
                                      <div class="search-select-dropdown" id="compteTiersDropdown" style="display: none;">
                                          <div class="list-group">
                                              @if(isset($tiers))
                                                  @foreach ($tiers as $tier)
                                                      <a href="#" class="list-group-item list-group-item-action option-tier"
                                                         data-value="{{ $tier->id }}"
                                                         data-compte-general="{{ $tier->compte_general_id ?? '' }}"
                                                         data-numero-compte="{{ $tier->numero_compte ?? '' }}"
                                                         data-libelle="{{ $tier->intitule ?? '' }}"
                                                         data-adresse="{{ $tier->adresse ?? '' }}"
                                                         data-telephone="{{ $tier->telephone ?? '' }}"
                                                         data-email="{{ $tier->email ?? '' }}">
                                                          @if(!empty($tier->code_tiers))
                                                              <strong>{{ $tier->code_tiers }}</strong> - 
                                                          @endif
                                                          {{ $tier->intitule }}
                                                      </a>
                                                  @endforeach
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-md-3">
                                  <label for="referencePieceEcriture" class="input-label-premium" style="font-size: 0.7rem;">Référence Pièce</label>
                                  <input type="text" id="referencePieceEcriture" name="reference_piece" class="input-field-premium" placeholder="N° facture..." style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-3">
                                  <label for="debitEcriture" class="input-label-premium" style="font-size: 0.7rem;">Montant Débit</label>
                                  <input type="number" id="debitEcriture" name="debit" class="input-field-premium" step="0.01" min="0" placeholder="0.00" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-3">
                                  <label for="creditEcriture" class="input-label-premium" style="font-size: 0.7rem;">Montant Crédit</label>
                                  <input type="number" id="creditEcriture" name="credit" class="input-field-premium" step="0.01" min="0" placeholder="0.00" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-3">
                                  <label for="planAnalytiqueEcriture" class="input-label-premium" style="font-size: 0.7rem;">Analytique</label>
                                  <select id="planAnalytiqueEcriture" name="plan_analytique" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                      <option value="0">Non</option>
                                      <option value="1">Oui</option>
                                  </select>
                              </div>
                          </div>

                          <div class="row g-2">
                              <div class="col-12">
                                  <label for="pieceJustificativeEcriture" class="input-label-premium" style="font-size: 0.7rem;">Pièce justificative (PDF, Image)</label>
                                  <input type="file" id="pieceJustificativeEcriture" name="piece_justificative" class="input-field-premium" accept=".pdf,.jpg,.jpeg,.png" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                          </div>
                      </div>

                      <div class="d-flex gap-2 mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                          <button type="button" class="btn btn-cancel-premium flex-fill" data-bs-dismiss="modal" style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                              <i class="bx bx-x me-1"></i>Annuler
                          </button>
                          <button type="button" class="btn-save-premium flex-fill" onclick="ajouterEcritureModal()" style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                              <i class="bx bx-check me-1"></i>Ajouter l'écriture
                          </button>
                      </div>
                  </form>
              </div>
          </div>
      </div>

      <!-- Modal de modification d'écriture -->
      <div class="modal fade" id="editEcritureModal" tabindex="-1" aria-labelledby="editEcritureModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" style="max-width: 98vw; width: 98vw; margin: auto;">
              <div class="modal-content premium-modal-content-wide" style="padding: 1.5rem; max-height: 90vh; overflow-y: auto;">
                  <form id="formEditEcriture" method="POST">
                      @csrf
                      @method('PUT')
                      <input type="hidden" id="edit_ecriture_id" name="id">

                      <div class="text-center mb-3 position-relative">
                          <h5 class="modal-title fw-bold" id="editEcritureModalLabel">Modifier l'écriture comptable</h5>
                          <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>

                      <div class="modal-body" style="padding: 0;">
                          <div class="row g-2 mb-3">
                              <div class="col-md-4">
                                  <label for="edit_date" class="input-label-premium" style="font-size: 0.7rem;">Date</label>
                                  <input type="date" id="edit_date" name="date" class="input-field-premium" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-4">
                                  <label for="edit_n_saisie" class="input-label-premium" style="font-size: 0.7rem;">N° de saisie</label>
                                  <input type="text" id="edit_n_saisie" name="n_saisie" class="input-field-premium" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" readonly />
                              </div>
                              <div class="col-md-4">
                                  <label for="edit_code_journal_id" class="input-label-premium" style="font-size: 0.7rem;">Code Journal</label>
                                  <select id="edit_code_journal_id" name="code_journal_id" class="input-field-premium" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                      <option value="" disabled selected>Sélectionner un journal</option>
                                      @foreach($codeJournaux ?? [] as $journal)
                                          <option value="{{ $journal->id }}">{{ $journal->code_journal }} - {{ $journal->libelle }}</option>
                                      @endforeach
                                  </select>
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-12">
                                  <label for="edit_description_operation" class="input-label-premium" style="font-size: 0.7rem;">Description</label>
                                  <input type="text" id="edit_description_operation" name="description_operation" class="input-field-premium" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-md-6">
                                  <label for="edit_plan_comptable_id" class="input-label-premium" style="font-size: 0.7rem;">Compte Général</label>
                                  <select id="edit_plan_comptable_id" name="plan_comptable_id" class="input-field-premium" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                      <option value="">Sélectionner un compte</option>
                                      @foreach($plansComptables ?? [] as $compte)
                                          <option value="{{ $compte->id }}">{{ $compte->numero_de_compte }} - {{ $compte->intitule }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div class="col-md-6">
                                  <label for="edit_plan_tiers_id" class="input-label-premium" style="font-size: 0.7rem;">Compte Tiers</label>
                                  <select id="edit_plan_tiers_id" name="plan_tiers_id" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                      <option value="">Sélectionner un tiers</option>
                                      @foreach($plansTiers ?? [] as $tiers)
                                          <option value="{{ $tiers->id }}">{{ $tiers->numero_de_tiers }} - {{ $tiers->intitule }}</option>
                                      @endforeach
                                  </select>
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-md-3">
                                  <label for="edit_debit" class="input-label-premium" style="font-size: 0.7rem;">Débit</label>
                                  <input type="number" step="0.01" id="edit_debit" name="debit" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-3">
                                  <label for="edit_credit" class="input-label-premium" style="font-size: 0.7rem;">Crédit</label>
                                  <input type="number" step="0.01" id="edit_credit" name="credit" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-3">
                                  <label for="edit_reference_piece" class="input-label-premium" style="font-size: 0.7rem;">Référence Pièce</label>
                                  <input type="text" id="edit_reference_piece" name="reference_piece" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;" />
                              </div>
                              <div class="col-md-3">
                                  <label for="edit_plan_analytique" class="input-label-premium" style="font-size: 0.7rem;">Analytique</label>
                                  <select id="edit_plan_analytique" name="plan_analytique" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                      <option value="" disabled selected>Sélectionner...</option>
                                      <option value="0">Non</option>
                                      <option value="1">Oui</option>
                                  </select>
                              </div>
                          </div>

                          <div class="row g-2 mb-3">
                              <div class="col-md-6">
                                  <label for="edit_compte_tresorerie_id" class="input-label-premium" style="font-size: 0.7rem;">Compte de trésorerie</label>
                                  <select id="edit_compte_tresorerie_id" name="compte_tresorerie_id" class="input-field-premium" style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                      <option value="">Sélectionner un compte</option>
                                      @foreach($comptesTresorerie ?? [] as $compte)
                                          <option value="{{ $compte->id }}">{{ $compte->name }} ({{ $compte->type }})</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div class="col-md-6">
                                  <label for="edit_piece_justificative" class="input-label-premium" style="font-size: 0.7rem;">Pièce justificative</label>
                                  <input type="file" id="edit_piece_justificative" name="piece_justificative" class="input-field-premium" accept=".pdf,.jpg,.jpeg,.png" style="padding: 0.5rem 0.875rem; font-size: 0.8rem;" />
                                  <div id="current_piece" class="mt-1 text-sm text-slate-500"></div>
                              </div>
                          </div>
                      </div>

                      <div class="d-flex gap-2 mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                          <button type="button" class="btn btn-cancel-premium flex-fill" data-bs-dismiss="modal" style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                              <i class="bx bx-x me-1"></i>Annuler
                          </button>
                          <button type="submit" class="btn-save-premium flex-fill" style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                              <i class="bx bx-check me-1"></i>Enregistrer les modifications
                          </button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
    </body>

</html>

<script>
    // Fonction pour remplir automatiquement les champs du modal
    document.addEventListener('DOMContentLoaded', function() {
        // Remplir automatiquement la date du jour
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('dateEcriture');
        if (dateInput) dateInput.value = today;

        // Récupérer le numéro de saisie depuis le champ caché
        const hiddenSaisie = document.getElementById('hiddenNumeroSaisie');
        const nextSaisie = hiddenSaisie ? hiddenSaisie.value : null;
        
        // Si le numéro de saisie n'est pas défini, en générer un nouveau
        if (!nextSaisie) {
            fetch('{{ route("ecriture.get-next-saisie") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.nextSaisieNumber) {
                    const numSaisieInput = document.getElementById('numeroSaisie');
                    if (numSaisieInput) numSaisieInput.value = data.nextSaisieNumber;
                    if (hiddenSaisie) hiddenSaisie.value = data.nextSaisieNumber;
                }
            });
        }

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('code')) {
            const journalInput = document.getElementById('journalEcriture');
            const hiddenJournal = document.getElementById('hiddenCodeJournal');
            if (journalInput) journalInput.value = urlParams.get('code');
            if (hiddenJournal) hiddenJournal.value = urlParams.get('code');
        }

        if (urlParams.has('id_journal')) {
            const hiddenJournal = document.getElementById('hiddenCodeJournal');
            if (hiddenJournal) hiddenJournal.value = urlParams.get('id_journal');
        }

        // Exclusion mutuelle Débit / Crédit
        const debitInput = document.getElementById('debitEcriture');
        const creditInput = document.getElementById('creditEcriture');

        if (debitInput && creditInput) {
            debitInput.addEventListener('input', function() {
                if (this.value && parseFloat(this.value) > 0) {
                    creditInput.value = '';
                    creditInput.readOnly = true;
                    creditInput.style.backgroundColor = '#f8f9fa';
                    creditInput.style.cursor = 'not-allowed';
                } else {
                    creditInput.readOnly = false;
                    creditInput.style.backgroundColor = '';
                    creditInput.style.cursor = '';
                }
            });

            creditInput.addEventListener('input', function() {
                if (this.value && parseFloat(this.value) > 0) {
                    debitInput.value = '';
                    debitInput.readOnly = true;
                    debitInput.style.backgroundColor = '#f8f9fa';
                    debitInput.style.cursor = 'not-allowed';
                } else {
                    debitInput.readOnly = false;
                    debitInput.style.backgroundColor = '';
                    debitInput.style.cursor = '';
                }
            });
        }
    });

    // Fonction pour ajouter une écriture depuis le modal
    function ajouterEcritureModal() {
        const form = document.getElementById('formNouvelleEcriture');
        const formData = new FormData(form);

        // Validation basique
        const date = document.getElementById('dateEcriture').value;
        const libelle = document.getElementById('libelleEcriture').value;
        const compteGeneral = document.getElementById('compteGeneralEcriture').value;
        const debit = parseFloat(document.getElementById('debitEcriture').value) || 0;
        const credit = parseFloat(document.getElementById('creditEcriture').value) || 0;

        if (!date || !libelle || !compteGeneral) {
            alert('Veuillez remplir les champs obligatoires (Date, Libellé, Compte Général).');
            return;
        }

        if (debit === 0 && credit === 0) {
            alert('Veuillez saisir un montant au débit ou au crédit.');
            return;
        }

        // Ajouter la ligne au tableau (simulation)
        const table = document.getElementById('tableEcritures').getElementsByTagName('tbody')[0];
        
        if (table.rows.length === 1 && table.rows[0].cells.length === 1) {
            table.deleteRow(0);
        }

        const newRow = table.insertRow(0);

        const compteText = document.getElementById('compteGeneralSearch').value;
        const tierText = document.getElementById('compteTiersSearch').value || '-';
        const analytiqueText = document.getElementById('planAnalytiqueEcriture').value === '1' ? 'Oui' : 'Non';
        const referencePiece = document.getElementById('referencePieceEcriture').value || '-';
        const pieceFile = document.getElementById('pieceJustificativeEcriture');
        const pieceFileName = pieceFile && pieceFile.files[0] ? pieceFile.files[0].name : '-';

        newRow.innerHTML = `
            <td class="px-4 py-3 text-sm text-slate-700">${date}</td>
            <td class="px-4 py-3 text-sm font-semibold text-slate-800">${document.getElementById('numeroSaisie').value}</td>
            <td class="px-4 py-3 text-sm text-slate-700">${referencePiece}</td>
            <td class="px-4 py-3 text-sm text-slate-700">${libelle}</td>
            <td class="px-4 py-3 text-sm text-slate-700">${compteText}</td>
            <td class="px-4 py-3 text-sm text-slate-700">${tierText}</td>
            <td class="px-4 py-3 text-sm text-slate-700">${analytiqueText}</td>
            <td class="px-4 py-3 text-sm text-slate-700 text-right">${debit > 0 ? debit.toLocaleString('fr-FR', {minimumFractionDigits: 2}) : ''}</td>
            <td class="px-4 py-3 text-sm text-slate-700 text-right">${credit > 0 ? credit.toLocaleString('fr-FR', {minimumFractionDigits: 2}) : ''}</td>
            <td class="px-4 py-3 text-sm text-slate-700 text-center">${pieceFileName}</td>
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <div class="flex items-center justify-center space-x-2">
                    <span class="text-slate-400 text-xs italic">Nouvelle ligne</span>
                </div>
            </td>
        `;

        newRow.classList.add('border-b', 'border-slate-100', 'hover:bg-slate-50');

        updateTableTotals();

        const modalEl = document.getElementById('nouvelleEcritureModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
        
        const prevSaisie = document.getElementById('numeroSaisie').value;
        form.reset();
        document.getElementById('numeroSaisie').value = prevSaisie;
        document.getElementById('hiddenNumeroSaisie').value = prevSaisie;
        document.getElementById('dateEcriture').value = new Date().toISOString().split('T')[0];
        
        document.getElementById('creditEcriture').readOnly = false;
        document.getElementById('creditEcriture').style.backgroundColor = '';
        document.getElementById('creditEcriture').style.cursor = '';
        document.getElementById('debitEcriture').readOnly = false;
        document.getElementById('debitEcriture').style.backgroundColor = '';
        document.getElementById('debitEcriture').style.cursor = '';

        alert('Écriture ajoutée avec succès !');
    }

    function updateTableTotals() {
        const table = document.getElementById('tableEcritures').getElementsByTagName('tbody')[0];
        let totalDebit = 0;
        let totalCredit = 0;

        for (let i = 0; i < table.rows.length; i++) {
            const row = table.rows[i];
            if (row.cells.length < 9) continue;
            
            const debitVal = parseFloat(row.cells[7].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            const creditVal = parseFloat(row.cells[8].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            
            totalDebit += debitVal;
            totalCredit += creditVal;
        }

        const footerDebit = document.getElementById('footerTotalDebit');
        const footerCredit = document.getElementById('footerTotalCredit');
        
        if (footerDebit) footerDebit.textContent = totalDebit.toLocaleString('fr-FR', {minimumFractionDigits: 2});
        if (footerCredit) footerCredit.textContent = totalCredit.toLocaleString('fr-FR', {minimumFractionDigits: 2});
    }

    function filterEcritures() {
        const numeroSaisie = document.getElementById('filterNumeroSaisie').value;
        const mois = document.getElementById('filterMois').value;
        const codeJournal = document.getElementById('filterCodeJournal').value;
        const recherche = document.getElementById('filterGlobalSearch').value;
        const etatPoste = document.getElementById('filterEtatPoste').value;

        const params = new URLSearchParams();
        if (numeroSaisie) params.append('numero_saisie', numeroSaisie);
        if (mois) params.append('mois', mois);
        if (codeJournal) params.append('code_journal', codeJournal);
        if (recherche) params.append('recherche', recherche);
        if (etatPoste && etatPoste !== '') params.append('etat_poste', etatPoste);

        window.location.href = window.location.pathname + '?' + params.toString();
    }

    window.setEtatPoste = function(value) {
        document.getElementById('filterEtatPoste').value = value;
        document.querySelectorAll('.etat-poste-btn').forEach(btn => {
            if (btn.getAttribute('data-value') === value) {
                btn.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                btn.classList.remove('text-slate-500', 'hover:text-slate-700');
            } else {
                btn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                btn.classList.add('text-slate-500', 'hover:text-slate-700');
            }
        });
        filterEcritures();
    };

    window.toggleAdvancedFilter = function() {
        const panel = document.getElementById('advancedFilterPanel');
        if (!panel) return;
        panel.style.display = (panel.style.display === 'none' || !panel.style.display) ? 'block' : 'none';
    };

    window.applyAdvancedFilters = function() {
        filterEcritures();
    };

    window.resetAdvancedFilters = function() {
        const numeroSaisie = document.getElementById('filterNumeroSaisie');
        const mois = document.getElementById('filterMois');
        const codeJournal = document.getElementById('filterCodeJournal');
        const recherche = document.getElementById('filterGlobalSearch');
        const etatPoste = document.getElementById('filterEtatPoste');
        
        if (numeroSaisie) numeroSaisie.value = '';
        if (mois) mois.value = '';
        if (codeJournal) codeJournal.value = '';
        if (recherche) recherche.value = '';
        if (etatPoste) {
            etatPoste.value = '';
            setEtatPoste('');
        }
        filterEcritures();
    };

    function initSearchSelect(inputId, dropdownId, hiddenInputId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(hiddenInputId);
        
        if (!input || !dropdown) return;
        
        input.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = dropdown.getElementsByClassName('list-group-item');
            let hasVisibleItems = false;
            
            const isDigit = /^\d/.test(searchText);
            
            for (let item of items) {
                const text = item.textContent.toLowerCase().trim();
                
                let matches = false;
                if (isDigit) {
                    // Start matching at the beginning of any word/part (especially for account numbers)
                    matches = text.startsWith(searchText);
                    if (!matches) {
                        const parts = text.split(/[\s-]+/);
                        for (let part of parts) {
                            if (part.startsWith(searchText)) {
                                matches = true;
                                break;
                            }
                        }
                    }
                } else {
                    matches = text.indexOf(searchText) > -1;
                }

                if (matches) {
                    item.style.display = '';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            }
            
            dropdown.style.display = hasVisibleItems ? 'block' : 'none';
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Add similar logic for focus event to consistency
        input.addEventListener('focus', function() {
            const searchText = this.value.toLowerCase();
            const items = dropdown.getElementsByClassName('list-group-item');
            let hasVisibleItems = false;
            
            const isDigit = /^\d/.test(searchText);
            
            for (let item of items) {
                const text = item.textContent.toLowerCase().trim();
                let matches = false;
                
                if (searchText === '') {
                    matches = true;
                } else if (isDigit) {
                    matches = text.startsWith(searchText);
                    if (!matches) {
                        const parts = text.split(/[\s-]+/);
                        for (let part of parts) {
                            if (part.startsWith(searchText)) {
                                matches = true;
                                break;
                            }
                        }
                    }
                } else {
                    matches = text.indexOf(searchText) > -1;
                }

                if (matches) {
                    item.style.display = '';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            }
            dropdown.style.display = hasVisibleItems ? 'block' : 'none';
        });
        
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const item = e.target.closest('.list-group-item');
            if (!item) return;
            
            input.value = item.textContent.trim();
            hiddenInput.value = item.dataset.value;
            dropdown.style.display = 'none';
            
            if (hiddenInputId === 'compteTiersEcriture') {
                remplirChampsPlanTiers(item);
            }
        });
    }

    function remplirChampsPlanTiers(selectedItem) {
        if (selectedItem.dataset.libelle) {
            document.getElementById('libelleEcriture').value = selectedItem.dataset.libelle;
        }
    }

    // Gestion de la soumission du formulaire de modification
    document.getElementById('formEditEcriture').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const ecritureId = document.getElementById('edit_ecriture_id').value;
        
        fetch(`/ecriture/${ecritureId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur : ' + (data.message || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Une erreur est survenue lors de la modification.');
        });
    });

    const syscohadaOptions = {
        '': 'Aucun (Non spécifié)',
        'INV_ACQ': 'INV - Acquisition d\'immobilisations',
        'INV_CES': 'INV - Cession d\'immobilisations',
        'FIN_EMP': 'FIN - Emprunt (Encaissement)',
        'FIN_RMB': 'FIN - Remboursement d\'emprunt',
        'FIN_DIV': 'FIN - Dividendes versés',
        'FIN_CAP': 'FIN - Augmentation de capital',
        'FIN_SUB': 'FIN - Subvention d\'investissement'
    };

    function getSyscohadaOptionsHtml(selected = '') {
        return Object.entries(syscohadaOptions).map(([key, label]) => 
            `<option value="${key}" ${key === selected ? 'selected' : ''}>${label}</option>`
        ).join('');
    }

    window.quickEditPoste = function(ecritureId, posteId, currentName = '', currentCategoryId = '', currentSyscohadaId = '') {
        const categories = @json($treasury_categories ?? []);
        const categoryOptions = categories.map(c => `<option value="${c.id}" data-name="${c.name}">${c.name}</option>`).join('');
        const syscohadaOptionsHtml = getSyscohadaOptionsHtml(currentSyscohadaId);
        
        Swal.fire({
            title: 'Modifier le poste de trésorerie',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label">Nom du poste</label>
                    <input type="text" id="swal_poste_name" class="form-control" placeholder="Ex: Caisse Menue Dépense">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Catégorie</label>
                    <select id="swal_poste_category" class="form-select" onchange="toggleSyscohadaVisibility('swal_poste_category', 'swal_syscohada_container')">
                        <option value="">Sélectionner une catégorie...</option>
                        ${categoryOptions}
                    </select>
                </div>
                <div id="swal_syscohada_container" class="mb-3 text-start" style="display: none;">
                    <label class="form-label">Flux SYSCOHADA (TFT)</label>
                    <div class="form-text text-muted mb-1 text-xs">Obligatoire pour Inv/Fin, ignoré pour Opérationnel</div>
                    <select id="swal_poste_syscohada" class="form-select">
                        ${syscohadaOptionsHtml}
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Enregistrer',
            cancelButtonText: 'Annuler',
            didOpen: () => {
                const nameInput = document.getElementById('swal_poste_name');
                const catSelect = document.getElementById('swal_poste_category');
                const sysSelect = document.getElementById('swal_poste_syscohada');
                
                if (nameInput) nameInput.value = currentName;
                if (catSelect) catSelect.value = currentCategoryId;
                if (sysSelect) sysSelect.value = currentSyscohadaId;

                // Trigger visibility check for pre-selected category if editing
                toggleSyscohadaVisibility('swal_poste_category', 'swal_syscohada_container');
            },
            preConfirm: () => {
                const name = document.getElementById('swal_poste_name').value;
                const category_id = document.getElementById('swal_poste_category').value;
                const syscohada_line_id = document.getElementById('swal_poste_syscohada').value;
                if (!name || !category_id) {
                    Swal.showValidationMessage('Veuillez remplir le nom et la catégorie');
                    return false;
                }
                return { name, category_id, syscohada_line_id };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                saveQuickPoste(ecritureId, result.value.name, result.value.category_id, result.value.syscohada_line_id);
            }
        });
    };

    window.quickCreatePoste = function(ecritureId, compteTresorerieId, defaultName = '') {
        const categories = @json($treasury_categories ?? []);
        const categoryOptions = categories.map(c => `<option value="${c.id}" data-name="${c.name}">${c.name}</option>`).join('');
        const syscohadaOptionsHtml = getSyscohadaOptionsHtml();

        Swal.fire({
            title: 'Nouveau poste de trésorerie',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label">Nom du poste</label>
                    <input type="text" id="swal_poste_name" class="form-control" placeholder="Ex: Caisse Menue Dépense">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Catégorie</label>
                    <select id="swal_poste_category" class="form-select" onchange="toggleSyscohadaVisibility('swal_poste_category', 'swal_syscohada_container')">
                        <option value="">Sélectionner une catégorie...</option>
                        ${categoryOptions}
                    </select>
                </div>
                <div id="swal_syscohada_container" class="mb-3 text-start" style="display: none;">
                    <label class="form-label">Flux SYSCOHADA (TFT)</label>
                     <div class="form-text text-muted mb-1 text-xs">Obligatoire pour Inv/Fin, ignoré pour Opérationnel</div>
                    <select id="swal_poste_syscohada" class="form-select">
                        ${syscohadaOptionsHtml}
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Créer et Assigner',
            cancelButtonText: 'Annuler',
            didOpen: () => {
                const nameInput = document.getElementById('swal_poste_name');
                if (nameInput) nameInput.value = defaultName;
            },
            preConfirm: () => {
                const name = document.getElementById('swal_poste_name').value;
                const category_id = document.getElementById('swal_poste_category').value;
                const syscohada_line_id = document.getElementById('swal_poste_syscohada').value;
                if (!name || !category_id) {
                    Swal.showValidationMessage('Veuillez remplir le nom et la catégorie');
                    return false;
                }
                return { name, category_id, syscohada_line_id };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                saveQuickPoste(ecritureId, result.value.name, result.value.category_id, result.value.syscohada_line_id);
            }
        });
    };

    window.toggleSyscohadaVisibility = function(selectId, containerId) {
        const select = document.getElementById(selectId);
        const container = document.getElementById(containerId);
        if (!select || !container) return;
        
        const selectedOption = select.options[select.selectedIndex];
        const categoryName = selectedOption ? selectedOption.getAttribute('data-name') : '';
        
        if (categoryName && (categoryName.includes('II.') || categoryName.includes('III.'))) {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
            // Optionally clear the syscohada field if not applicable
            const sysSelect = document.getElementById('swal_poste_syscohada');
            if (sysSelect) sysSelect.value = '';
        }
    };

    function saveQuickPoste(ecritureId, name, categoryId, syscohadaLineId) {
        fetch('{{ route("postetresorerie.store_quick") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ecriture_id: ecritureId,
                name: name,
                category_id: categoryId,
                syscohada_line_id: syscohadaLineId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Succès !', text: data.message, timer: 1500, showConfirmButton: false });
                
                // Dimanic update of the table cell
                const td = document.querySelector(`.td-poste-treso[data-ecriture-id="${ecritureId}"]`);
                if (td) {
                    td.innerHTML = `
                        <div class="flex items-center gap-2 group">
                            <span class="badge bg-label-info">
                                ${data.name} - ${data.category_name}
                            </span>
                            <button type="button" class="btn btn-xs btn-icon btn-label-secondary opacity-0 group-hover:opacity-100 transition-opacity" 
                                onclick="window.quickEditPoste(${ecritureId}, ${data.id}, '${data.name.replace(/'/g, "\\'")}', '${data.category_id}', '${data.syscohada_line_id || ''}')" title="Modifier le poste">
                                <i class="bx bx-edit-alt text-xs"></i>
                            </button>
                        </div>
                    `;
                }
            } else {
                throw new Error(data.error || 'Erreur lors de la sauvegarde');
            }
        })
        .catch(err => {
            console.error('Erreur:', err);
            Swal.fire({ icon: 'error', title: 'Oups...', text: err.message });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSearchSelect('compteGeneralSearch', 'compteGeneralDropdown', 'compteGeneralEcriture');
        initSearchSelect('compteTiersSearch', 'compteTiersDropdown', 'compteTiersEcriture');
    });

    function confirmDeleteBySaisie(nSaisie) {
        if (confirm('Êtes-vous sûr de vouloir supprimer TOUTES les lignes liées à l\'écriture n° ' + nSaisie + ' ?')) {
            // Logic to delete by n_saisie
            // This might require a new backend route or a search for all IDs with this n_saisie.
            // For now, let's look for a generic route if it exists or explain we need to implement it.
            fetch(`{{ url('ecriture-delete-by-saisie') }}/${nSaisie}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression : ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la suppression.');
            });
        }
    }

    function confirmDelete(ecritureId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cette action est irréversible !',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteEntry(ecritureId);
                }
            });
        } else {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette écriture ?')) {
                deleteEntry(ecritureId);
            }
        }
    }

    async function deleteEntry(ecritureId) {
        try {
            const response = await fetch(`/ecriture/${ecritureId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Supprimé !', result.message, 'success').then(() => window.location.reload());
                } else {
                    alert(result.message);
                    window.location.reload();
                }
            } else {
                throw new Error(result.message || 'Erreur lors de la suppression.');
            }
        } catch (error) {
            alert(error.message);
        }
    }

    function editEntry(ecritureId) {
        window.location.href = `/ecriture/${ecritureId}/edit`;
    }
</script>