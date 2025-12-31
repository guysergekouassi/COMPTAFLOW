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
                                border-bottom: 1px solid #e2e8f0;
                                padding: 1.25rem 2rem !important;
                                font-size: 0.875rem !important;
                                font-weight: 700 !important;
                                color: #64748b !important;
                                text-transform: uppercase !important;
                                letter-spacing: 0.05em !important;
                            }
                            
                            #tableEcritures tbody td {
                                padding: 1.5rem 2rem !important;
                                vertical-align: middle !important;
                            }

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
                                <div class="glass-card px-6 py-4 flex items-center gap-6 w-full md:w-auto">
                                   <div class="flex items-center gap-3">
                                       <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                           <i class='bx bx-notepad text-xl'></i>
                                       </div>
                                       <div>
                                           <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Journal</p>
                                           <p class="text-sm font-bold text-slate-800">{{ data_get($journal ?? null, 'code_journal', '-') }} - {{ data_get($journal ?? null, 'intitule', '-') }}</p>
                                       </div>
                                   </div>
                                   <div class="h-8 w-px bg-slate-200"></div>
                                   <div class="flex items-center gap-3">
                                       <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                            <i class='bx bx-calendar text-xl'></i>
                                       </div>
                                       <div>
                                           <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Période</p>
                                           <p class="text-sm font-bold text-slate-800">{{ isset($exercice) && data_get($exercice, 'date_debut') ? \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') : '-' }} - {{ isset($exercice) && data_get($exercice, 'date_fin') ? \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') : '-' }}</p>
                                       </div>
                                   </div>
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
                                            <select id="filterExercice" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                                <option value="">Tous les exercices</option>
                                                @foreach (($exercices ?? []) as $exerciceItem)
                                                    <option value="{{ $exerciceItem->id }}" {{ (isset($data['exercice_id']) && (string) $data['exercice_id'] === (string) $exerciceItem->id) ? 'selected' : '' }}>
                                                        {{ $exerciceItem->intitule ?? $exerciceItem->annee ?? $exerciceItem->id }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
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
                                            <select id="filterJournal" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                                <option value="">Tous les journaux</option>
                                                @foreach (($code_journaux ?? []) as $j)
                                                    <option value="{{ $j->id }}" {{ (isset($data['journal_id']) && (string) $data['journal_id'] === (string) $j->id) ? 'selected' : '' }}>
                                                        {{ $j->code_journal }} - {{ $j->intitule }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <i class="fas fa-book absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
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
                                <div class="px-6 py-4 border-b border-slate-100">
                                    <h3 class="text-lg font-bold text-slate-800">Liste des écritures</h3>
                                    <p class="text-sm text-slate-500">Consultation et suivi des écritures comptables</p>
                                </div>
                                <style>
                                    /* Style personnalisé pour la barre de défilement */
                                    .table-responsive::-webkit-scrollbar {
                                        height: 10px;
                                        background-color: #f5f5f5;
                                    }
                                    .table-responsive::-webkit-scrollbar-thumb {
                                        background-color: #888;
                                        border-radius: 5px;
                                    }
                                    .table-responsive::-webkit-scrollbar-thumb:hover {
                                        background-color: #555;
                                    }
                                    .table-responsive {
                                        overflow-x: auto;
                                        -webkit-overflow-scrolling: touch;
                                        scrollbar-width: thin;
                                        scrollbar-color: #888 #f5f5f5;
                                    }
                                </style>
                                <div class="table-responsive" style="margin-bottom: 15px;">
                                    <table class="w-full text-left border-collapse" id="tableEcritures" style="min-width: 100%; width: max-content;">
                                    <thead class="bg-slate-50/50 border-b border-slate-100">
                                        <tr>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">N° Saisie</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Réf. Pièce</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Compte Général</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Compte Tiers</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Analytique</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Débit</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Crédit</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Pièce</th>
                                            <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (($ecritures ?? collect()) as $ecriture)
                                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                                <td class="px-4 py-3 text-sm text-slate-700">{{ $ecriture->date }}</td>
                                                <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $ecriture->n_saisie }}</td>
                                                <td class="px-4 py-3 text-sm text-slate-700">{{ $ecriture->reference_piece }}</td>
                                                <td class="px-4 py-3 text-sm text-slate-700">{{ $ecriture->description_operation }}</td>
                                                <td class="px-4 py-3 text-sm text-slate-700">
                                                    {{ $ecriture->planComptable ? $ecriture->planComptable->numero_de_compte . ' - ' . $ecriture->planComptable->intitule : '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-slate-700">
                                                    {{ $ecriture->planTiers ? $ecriture->planTiers->numero_de_tiers . ' - ' . $ecriture->planTiers->intitule : '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-slate-700">{{ (int) $ecriture->plan_analytique === 1 ? 'Oui' : 'Non' }}</td>
                                                <td class="px-4 py-3 text-sm text-slate-700 text-right">{{ number_format((float) $ecriture->debit, 2, ',', ' ') }}</td>
                                                <td class="px-4 py-3 text-sm text-slate-700 text-right">{{ number_format((float) $ecriture->credit, 2, ',', ' ') }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($ecriture->piece_justificatif)
                                                        <a href="{{ asset('justificatifs/' . $ecriture->piece_justificatif) }}" target="_blank" class="text-blue-700 font-semibold">Voir</a>
                                                    @else
                                                        <span class="text-slate-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <a href="{{ route('ecriture.show', $ecriture->id) }}" class="p-1.5 text-info hover:text-blue-800 transition-colors duration-200" title="Voir">
                                                            <i class="bx bx-show text-xl"></i>
                                                        </a>
                                                        <button onclick="editEntry({{ $ecriture->id }})" class="p-1.5 text-blue-600 hover:text-blue-800 transition-colors duration-200" title="Modifier">
                                                            <i class="bx bx-edit-alt text-xl"></i>
                                                        </button>
                                                        <button onclick="confirmDelete({{ $ecriture->id }})" class="p-1.5 text-red-600 hover:text-red-800 transition-colors duration-200" title="Supprimer">
                                                            <i class="bx bx-trash text-xl"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (!isset($ecritures) || $ecritures->isEmpty())
                                            <tr>
                                                <td colspan="11" class="text-center text-slate-500 py-6">
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
        const exercice = document.getElementById('filterExercice').value;
        const mois = document.getElementById('filterMois').value;
        const journal = document.getElementById('filterJournal').value;

        const params = new URLSearchParams();
        if (exercice) params.append('exercice_id', exercice);
        if (mois) params.append('mois', mois);
        if (journal) params.append('journal_id', journal);

        window.location.href = window.location.pathname + '?' + params.toString();
    }

    window.toggleAdvancedFilter = function() {
        const panel = document.getElementById('advancedFilterPanel');
        if (!panel) return;
        panel.style.display = (panel.style.display === 'none' || !panel.style.display) ? 'block' : 'none';
    };

    window.applyAdvancedFilters = function() {
        filterEcritures();
    };

    window.resetAdvancedFilters = function() {
        const ex = document.getElementById('filterExercice');
        const mois = document.getElementById('filterMois');
        const j = document.getElementById('filterJournal');
        if (ex) ex.value = '';
        if (mois) mois.value = '';
        if (j) j.value = '';
        filterEcritures();
    };

    function initSearchSelect(inputId, dropdownId, hiddenInputId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(hiddenInputId);
        
        if (!input || !dropdown) return;
        
        input.addEventListener('focus', function() {
            const searchText = this.value.toLowerCase();
            const items = dropdown.getElementsByClassName('list-group-item');
            let hasVisibleItems = false;
            
            for (let item of items) {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchText)) {
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
        
        input.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = dropdown.getElementsByClassName('list-group-item');
            let hasVisibleItems = false;
            
            for (let item of items) {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchText)) {
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

    document.addEventListener('DOMContentLoaded', function() {
        initSearchSelect('compteGeneralSearch', 'compteGeneralDropdown', 'compteGeneralEcriture');
        initSearchSelect('compteTiersSearch', 'compteTiersDropdown', 'compteTiersEcriture');
    });

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