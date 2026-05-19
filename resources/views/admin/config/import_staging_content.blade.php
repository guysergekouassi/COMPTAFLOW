<div class="container-xxl flex-grow-1 container-p-y staging-fade-in" id="staging-dynamic-content" style="animation: fadeIn 0.3s ease-in-out;">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-[20px] mb-4 border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger/10 p-2 rounded-lg me-3">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading font-black mb-1">Erreur d'importation</h6>
                                        <p class="mb-0 text-sm font-medium">{{ session('error') }}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(!empty($missingAccounts) || !empty($missingJournals) || !empty($missingTiers))
                            <div class="alert alert-warning rounded-[20px] mb-4 shadow-sm border border-warning-subtle">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fa-solid fa-exclamation-triangle text-warning fs-3 me-3"></i>
                                    <h5 class="mb-0 fw-bold">Entités Manquantes Détectées</h5>
                                </div>
                                <p class="text-sm mb-3">Certaines données font référence à des comptes, journaux ou tiers inexistants. Cliquez sur les boutons ci-dessous pour les créer rapidement avec des valeurs par défaut.</p>
                                
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($missingJournals ?? [] as $code => $intitule)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="quickCreateJournal('{{ $code }}', '{{ addslashes($intitule) }}', {{ $import->id }})" title="Créer automatiquement le journal {{ $code }}">
                                            <i class="fa-solid fa-book me-1"></i> Créer Journal : {{ $code }}
                                        </button>
                                    @endforeach

                                    @foreach($missingAccounts ?? [] as $num => $intitule)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="quickCreateAccount('{{ $num }}', '{{ addslashes($intitule) }}', {{ $import->id }})" title="Créer automatiquement le compte {{ $num }}">
                                            <i class="fa-solid fa-hashtag me-1"></i> Créer Compte : {{ $num }}
                                        </button>
                                    @endforeach

                                    @foreach($missingTiers ?? [] as $num => $intitule)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="quickCreateTier('{{ $num }}', '{{ addslashes($intitule) }}', {{ $import->id }})" title="Créer automatiquement le tiers {{ $num }}">
                                            <i class="fa-solid fa-user-tag me-1"></i> Créer Tiers : {{ $num }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-[20px] mb-4 border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success/10 p-2 rounded-lg me-3">
                                        <i class="fa-solid fa-circle-check text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading font-black mb-1">Succès !</h6>
                                        <p class="mb-0 text-sm font-medium">{{ session('success') }}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errorCount > 0)
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-white p-6 rounded-[24px] border border-rose-200 bg-rose-50/20 shadow-sm">
                                    <h5 class="font-black mb-4 d-flex align-items-center gap-2 text-rose-800">
                                        <i class="fa-solid fa-clipboard-check"></i> Rapport de Validation & Manuel de Correction
                                    </h5>
                                    
                                    <div class="row g-4">
                                        @php
                                            $rowsForTotals = collect($rowsWithStatus)->filter(fn($r) => ($r['status'] ?? null) !== 'ignored');
                                            $totalDebit = $rowsForTotals->sum('debit');
                                            $totalCredit = $rowsForTotals->sum('credit');
                                            $balance = abs($totalDebit - $totalCredit);
                                        @endphp

                                        @if($balance > 0.01)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-rose-100 text-rose-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-scale-unbalanced"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Déséquilibre ({{ number_format($balance, 0, ',', ' ') }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Le total débit ne correspond pas au total crédit.</p>
                                                <div class="alert alert-danger py-2 px-3 text-[10px] mb-0 border-0">
                                                    Vérifiez les montants ou les lignes manquantes.
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-info-circle"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Diagnostic</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-2">Utilisez le bouton <i class="fa-solid fa-eye text-info"></i> pour voir les erreurs détaillées par ligne.</p>
                                                <p class="text-[10px] text-slate-400 italic">Corrigez les comptes inconnus via le bouton modifier <i class="fa-solid fa-pen text-primary"></i>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-emerald-50 p-6 rounded-[24px] border border-emerald-200 shadow-sm d-flex align-items-center gap-4">
                                    <div class="bg-emerald-500 text-white p-3 rounded-full pulse">
                                        <i class="fa-solid fa-check-double fa-xl"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-black text-emerald-900 mb-0">Ecritures équilibrées et valides !</h5>
                                        <p class="text-emerald-700 text-sm mb-0">Toutes les lignes sont conformes aux règles comptables.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mb-6">
                            <div class="col-md-3">
                                <div class="bg-emerald-50 p-4 rounded-2xl border {{ $statusFilter == 'valid' ? 'border-emerald-500 active' : 'border-emerald-100' }} cursor-pointer card-filter card-filter-valid" id="filterCardValid" onclick="loadStagingPage('{{ request()->fullUrlWithQuery(['status' => 'valid', 'page' => 1]) }}')">
                                    <div class="text-xs font-bold text-emerald-600 uppercase mb-1">Lignes Valides</div>
                                    <div class="h4 font-black text-emerald-700 mb-0">{{ $validCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-rose-50 p-4 rounded-2xl border {{ $statusFilter == 'error' ? 'border-rose-500 active' : 'border-rose-100' }} cursor-pointer card-filter card-filter-error" id="filterCardError" onclick="loadStagingPage('{{ request()->fullUrlWithQuery(['status' => 'error', 'page' => 1]) }}')">
                                    <div class="text-xs font-bold text-rose-600 uppercase mb-1">Erreurs détectées</div>
                                    <div class="h4 font-black text-rose-700 mb-0">{{ $errorCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 h-100">
                                    <div class="bg-white p-4 rounded-2xl border {{ $statusFilter == 'all' ? 'border-primary active' : 'border-slate-100' }} cursor-pointer card-filter card-filter-all" id="filterCardAll" onclick="loadStagingPage('{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}')">
                                        <div class="text-xs font-bold text-slate-400 uppercase mb-2">Tout afficher</div>
                                        <div class="d-flex gap-4">
                                            <div class="text-xs d-flex align-items-center gap-2">
                                                <span class="status-indicator bg-emerald-500"></span> Prêt à l'import
                                            </div>
                                            <div class="text-xs d-flex align-items-center gap-2">
                                                <span class="status-indicator bg-rose-500"></span> Erreur bloquante
                                            </div>
                                            <div class="text-xs d-flex align-items-center gap-2">
                                                <span class="status-indicator bg-warning"></span> Attention / Hors OHADA
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 d-flex align-items-center" style="width: 300px;">
                                        <div class="input-group input-group-merge border-0 bg-slate-50 rounded-xl px-2">
                                            <span class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-magnifying-glass text-slate-400"></i></span>
                                            <input type="text" id="stagingSearch" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrer numéro / libellé..." value="{{ $searchFilter }}" onkeyup="if(event.key === 'Enter') loadStagingPage('{{ request()->fullUrlWithQuery(['search' => '']) }}'.replace('search=', 'search=' + encodeURIComponent(this.value)))">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-center gap-2">
                            <button type="button" id="addStagingRowBtn" class="btn btn-sm btn-primary" 
                                    data-import-id="{{ $import->id }}"
                                    data-mapping="{{ json_encode($mapping) }}"
                                    onclick="addStagingRow(this)">
                                <i class="fa-solid fa-plus-circle me-1"></i> Ajouter une ligne
                            </button>
                            @if($errorCount > 0)
                            <button type="button" id="bulkDeleteErrorsBtn" class="btn btn-sm btn-outline-danger" onclick="selectAndBulkDeleteErrors()">
                                <i class="fa-solid fa-trash-can me-1"></i> Suppression Totale ({{ $errorCount }} erreurs)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="exportErrorsToCSV()">
                                <i class="fa-solid fa-file-excel me-1"></i> Exporter Erreurs
                            </button>
                            @endif
                        </div>

                        <div class="staging-card p-0 mb-6">
                            <div class="staging-table-container" style="overflow: hidden;">
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-staging mb-0" style="min-width: 1200px;">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;" class="text-center">
                                                    <input type="checkbox" id="masterCheckbox" class="form-check-input" onclick="toggleAllCheckboxes(this)">
                                                </th>
                                                <th style="width: 50px;">STATUT</th>
                                                @if($import->type == 'initial')
                                                    <th>NUMÉRO DE COMPTE</th>
                                                    <th>INTITULÉ DU COMPTE</th>
                                                @elseif($import->type == 'journals')
                                                    <th>CODE JOURNAL</th>
                                                    <th>INTITULÉ DU JOURNAL</th>
                                                    <th>TYPE</th>
                                                    <th>COMPTE</th>
                                                    <th>ANALYTIQUE</th>
                                                    <th>RAPPROCHEMENT</th>
                                                @elseif($import->type == 'tiers')
                                                    <th>N° TIERS / IDENTIFIANT</th>
                                                    <th>NOM / RAISON SOCIALE</th>
                                                    <th>CATÉGORIE</th>
                                                    <th>COMPTE GÉNÉRAL</th>
                                                @else
                                                    <th>N° SAISIE</th>
                                                    <th>ÉQUILIBRE</th>
                                                    <th>DATE</th>
                                                    <th>JOURNAL</th>
                                                    <th>RÉFÉRENCE</th>
                                                    <th>COMPTE</th>
                                                    <th>TIERS</th>
                                                    <th>LIBELLÉ</th>
                                                    <th class="text-end">DÉBIT</th>
                                                <th class="text-end">CRÉDIT</th>
                                                @endif
                                                <th class="text-center">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $mapping = $import->mapping;
                                            @endphp
 
                                            @foreach($rowsWithStatusPaged as $rowIndex => $row)
                                                <tr class="staging-row {{ $row['status'] == 'valid' ? 'row-valid' : ($row['status'] == 'ignored' ? 'row-warning' : 'row-error') }}" data-status="{{ $row['status'] }}">
                                                    <td class="text-center">
                                                        <input type="checkbox" class="row-checkbox form-check-input" data-row-index="{{ $row['index'] ?? $rowIndex }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="status-indicator {{ $row['status'] == 'valid' ? 'bg-emerald-500' : ($row['status'] == 'ignored' ? 'bg-warning' : 'bg-rose-500') }}" 
                                                               title="{{ count($row['errors'] ?? []) ? implode(', ', $row['errors']) : 'Erreur de validation inconnue' }}"></span>
                                                    </td>
                                                     
                                                     @if($import->type == 'initial')
                                                         <td class="@if($row['status'] == 'error') cell-error @endif search-target">
                                                             {{ $row['data']['numero_de_compte'] ?? '-' }}
                                                         </td>
                                                         <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                     @elseif($import->type == 'journals')
                                                         <td class="@if($row['status'] == 'error') cell-error @endif search-target">
                                                             {{ $row['data']['code_journal'] ?? '-' }}
                                                         </td>
                                                         <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                         <td>
                                                             <span class="badge bg-label-info">
                                                                 {{ $row['data']['type'] ?? 'Achats' }}
                                                             </span>
                                                         </td>
                                                         <td>{{ $row['data']['compte_de_tresorerie'] ?? '-' }}</td>
                                                         <td>
                                                             <span class="badge {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                                 {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'OUI' : 'NON' }}
                                                             </span>
                                                         </td>
                                                         <td>{{ $row['data']['rapprochement_sur'] ?? '-' }}</td>
                                                     @elseif($import->type == 'tiers')
                                                         <td class="@if($row['status'] == 'error' && empty($row['data']['numero_de_tiers']) && empty($row['data']['auto_num'])) cell-warning @endif search-target">
                                                             @if(!empty($row['data']['auto_num']))
                                                                 <span class="text-primary font-black"><i class="fa-solid fa-magic me-1"></i> {{ $row['data']['auto_num'] }}</span>
                                                             @elseif(!empty($row['data']['numero_de_tiers']))
                                                                 {{ $row['data']['numero_de_tiers'] }}
                                                             @else
                                                                 <span class="badge bg-label-warning italic text-[10px]">Sera auto-généré</span>
                                                             @endif
                                                         </td>
                                                         <td class="fw-bold search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                         <td>
                                                             <span class="badge bg-label-primary">
                                                                 {{ $row['data']['type_de_tiers'] ?? 'Client' }}
                                                             </span>
                                                         </td>
                                                         <td class="font-mono">{{ $row['data']['compte_general'] ?? '-' }}</td>
                                                     @else
                                                        <td class="search-target">{{ $row['data']['n_saisie'] ?? '-' }}</td>
                                                        <td>
                                                            @php $gd = $row['group_diff'] ?? null; @endphp
                                                            @if($row['status'] == 'ignored')
                                                                <span class="badge bg-label-warning">Ignorée</span>
                                                            @elseif($gd === null)
                                                                <span class="badge bg-label-secondary">-</span>
                                                            @elseif(abs((float)$gd) <= 0.01)
                                                                <span class="badge bg-label-success">Équilibré</span>
                                                            @else
                                                                <span class="badge bg-label-danger">Déséquilibré ({{ number_format(abs((float)$gd), 0, ',', ' ') }})</span>
                                                            @endif

                                                            @if(($row['group_debit'] ?? null) !== null && ($row['group_credit'] ?? null) !== null)
                                                                <div class="text-[10px] text-slate-400 italic">D={{ number_format((float)$row['group_debit'], 0, ',', ' ') }} / C={{ number_format((float)$row['group_credit'], 0, ',', ' ') }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['data']['jour'] ?? '-' }}</td>
                                                        <td>
                                                            <div class="fw-bold">{{ $row['data']['journal'] ?? '-' }}</div>
                                                            @if(!empty($row['data']['code_original_journal']))
                                                                <div class="text-[10px] text-slate-400 italic">{{ $row['data']['code_original_journal'] }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['data']['reference'] ?? '-' }}</td>
                                                        <td class="@if(in_array('error', $row['errors']) || str_contains(implode(' ', $row['errors']), 'Compte')) cell-error @endif search-target" 
                                                            title="{{ implode(', ', array_filter($row['errors'], fn($e) => str_contains($e, 'Compte'))) }}">
                                                            <div class="fw-bold">{{ $row['data']['compte'] ?? '-' }}</div>
                                                             @if(!empty($row['data']['numero_original_compte']))
                                                                 <div class="text-[10px] text-slate-400 italic">{{ $row['data']['numero_original_compte'] }}</div>
                                                             @endif
                                                        </td>
                                                        <td class="@if(str_contains(implode(' ', $row['errors']), 'Tiers inconnu')) cell-error @endif">
                                                            <div class="fw-bold">{{ $row['data']['tiers'] ?? '-' }}</div>
                                                             @if(!empty($row['data']['numero_original_tiers']))
                                                                 <div class="text-[10px] text-slate-400 italic">{{ $row['data']['numero_original_tiers'] }}</div>
                                                             @endif
                                                        </td>
                                                        <td class="search-target">{{ \Illuminate\Support\Str::limit($row['data']['libelle'] ?? '-', 30) }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                                                     @endif

                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center">
                                                            @if(!in_array($import->type, ['initial', 'journals']) && $row['status'] == 'error' && str_contains(implode(' ', $row['errors']), 'Compte'))
                                                                <button class="btn btn-icon btn-sm btn-label-success rounded-pill me-1" 
                                                                        data-compte="{{ $row["data"]["compte"] ?? '' }}"
                                                                        data-libelle="{{ $row["data"]["libelle"] ?? '' }}"
                                                                        onclick="quickCreateAccount(this)"
                                                                        title="Créer ce compte à la volée">
                                                                    <i class="fa-solid fa-plus-circle"></i>
                                                                </button>
                                                            @endif
                                                            <button class="btn btn-icon btn-sm btn-label-primary rounded-pill me-1" 
                                                                    data-import-id="{{ $import->id }}"
                                                                    data-row-index="{{ $row['index'] }}"
                                                                    data-row-data="{{ json_encode($row['data']) }}"
                                                                    data-mapping="{{ json_encode($mapping) }}"
                                                                    onclick="editStagingRow(this)"
                                                                    title="Modifier cette ligne">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </button>
                                                            <button class="btn btn-icon btn-sm btn-label-info rounded-pill" 
                                                                    data-row-data="{{ json_encode($row['data']) }}"
                                                                    data-errors="{{ json_encode($row['errors']) }}"
                                                                    onclick="showRowDetails(this)"
                                                                    title="Voir les détails">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-icon btn-sm btn-label-danger rounded-pill ms-1" 
                                                                    onclick="deleteStagingRow({{ $import->id }}, {{ $row['index'] }})"
                                                                    title="Supprimer cette ligne de l'import">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- PAGINATION --}}
                            @if($totalPages > 1)
                            <div class="d-flex justify-content-between align-items-center mt-4 px-2">
                                <div class="text-slate-500 text-sm">
                                    Page <strong>{{ $currentPage }}</strong> / {{ $totalPages }} &nbsp;&mdash;&nbsp;
                                    Affichage lignes {{ (($currentPage-1)*$perPage)+1 }} à {{ min($currentPage*$perPage, $totalRows) }}
                                    sur <strong>{{ $totalRows }}</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($currentPage > 1)
                                        <a href="javascript:void(0)" onclick="loadStagingPage('{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}')" class="btn btn-sm btn-outline-secondary rounded-xl px-4">
                                            <i class="fa-solid fa-chevron-left me-1"></i> Préc.
                                        </a>
                                    @endif
                                    @for($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++)
                                        <a href="javascript:void(0)" onclick="loadStagingPage('{{ request()->fullUrlWithQuery(['page' => $p]) }}')"
                                           class="btn btn-sm {{ $p == $currentPage ? 'btn-primary' : 'btn-outline-secondary' }} rounded-xl px-3">
                                            {{ $p }}
                                        </a>
                                    @endfor
                                    @if($currentPage < $totalPages)
                                        <a href="javascript:void(0)" onclick="loadStagingPage('{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}')" class="btn btn-sm btn-outline-secondary rounded-xl px-4">
                                            Suiv. <i class="fa-solid fa-chevron-right ms-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @endif

                        <div class="d-flex justify-content-between align-items-center bg-white p-6 rounded-[24px] shadow-sm border border-slate-100">
                            <div>
                                <a href="{{ route('admin.import.mapping', $import->id) }}" class="btn btn-outline-secondary rounded-xl px-6 py-3">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Retour au mapping
                                </a>
                            </div>
                            <div class="d-flex gap-3">
                                <form action="{{ route('admin.import.cancel', $import->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment annuler cette importation ? Toutes les données temporaires seront supprimées.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-label-danger rounded-xl px-6 py-3 border-0">
                                        <i class="fa-solid fa-trash me-2"></i> Annuler l'import
                                    </button>
                                </form>
                                <form id="commitForm" action="{{ route('admin.import.commit', $import->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary rounded-xl px-10 py-3 font-bold shadow-lg shadow-primary/20" @if($errorCount > 0) disabled title="Veuillez corriger toutes les erreurs avant la migration." @endif>
                                        <i class="fa-solid fa-cloud-arrow-down me-2"></i> Lancer la migration finale
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>