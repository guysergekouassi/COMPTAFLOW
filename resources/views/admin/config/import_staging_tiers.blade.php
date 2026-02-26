@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .staging-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
    }

    .staging-table-container {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .table-staging thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-staging tbody td {
        padding: 0.75rem 1rem;
        font-size: 0.825rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .cell-error {
        background-color: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
        color: #b91c1c !important;
        position: relative;
    }

    .cell-error::after {
        content: "\f06a";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.7rem;
        opacity: 0.5;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .step-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.875rem;
    }

    .cursor-pointer { cursor: pointer; }
    .card-filter:hover { transform: translateY(-3px); transition: all 0.2s ease; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
    .card-filter.active { ring: 2px; ring-color: var(--bs-primary); }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Importation / <span class="text-primary">' . $importTitle . '</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
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
                                            $missingAccountErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'Générale absente'))->count();
                                            $missingPrefixErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'compte collectif'))->count();
                                            $missingIdErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'Numéro de tiers manquant'))->count();
                                            $otherErrors = collect($rowsWithStatus)->filter(fn($r) => !empty($r['errors']) && 
                                                !str_contains(implode(' ', $r['errors']), 'Générale absente') && 
                                                !str_contains(implode(' ', $r['errors']), 'compte collectif') && 
                                                !str_contains(implode(' ', $r['errors']), 'Numéro de tiers manquant'))->count();
                                        @endphp

                                        @if($missingAccountErrors > 0 || $missingPrefixErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-rose-100 text-rose-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-book"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Comptes Généraux Manquants ({{ $missingAccountErrors + $missingPrefixErrors }})</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-[10px] fw-black text-rose-500 uppercase tracking-widest">RAISON</span>
                                                    <p class="text-xs text-slate-500 mb-0">Comptes collectifs inexistants ou format incorrect.</p>
                                                </div>
                                                <div>
                                                    <span class="text-[10px] fw-black text-emerald-500 uppercase tracking-widest">ACTION REQUISE</span>
                                                    @php
                                                        $missingAccountNumbers = collect($rowsWithStatus)
                                                            ->flatMap(function($r) {
                                                                return collect($r['errors'])
                                                                    ->filter(fn($e) => str_contains($e, "n'existe pas. Veuillez le créer au préalable."))
                                                                    ->map(function($e) {
                                                                        preg_match('/Le compte collectif (.*) n\'existe pas/', $e, $matches);
                                                                        return $matches[1] ?? null;
                                                                    });
                                                            })
                                                            ->filter()
                                                            ->unique()
                                                            ->values();
                                                    @endphp
                                                    @if($missingAccountNumbers->count() > 0)
                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                            @foreach($missingAccountNumbers as $accNum)
                                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill fw-bold" 
                                                                        data-accnum="{{ trim($accNum) }}"
                                                                        data-acclen="{{ $accountDigits ?? 8 }}"
                                                                        onclick="quickCreateGeneralAccount(this.dataset.accnum, parseInt(this.dataset.acclen) || 8)"
                                                                        title="Cliquer pour créer ce compte">
                                                                    <i class="fa-solid fa-plus me-1"></i> {{ trim($accNum) }}
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                        <p class="text-xs text-slate-500 mt-2 mb-0 italic">Cliquez sur un compte rouge pour le créer. Les lignes seront mises à jour automatiquement.</p>
                                                    @else
                                                        <div class="alert alert-emerald py-2 px-3 text-[10px] mb-0 border-0 bg-emerald-50 text-emerald-700">
                                                            Les correspondances seront automatiquement établies lors de l'importation.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($missingIdErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-orange-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-id-card"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Tiers Invalides ({{ $missingIdErrors }})</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <span class="text-[10px] fw-black text-rose-500 uppercase tracking-widest">RAISON</span>
                                                    <p class="text-xs text-slate-500 mb-0">Numéro ne commençant pas par '4' ou manquant.</p>
                                                </div>
                                                <div>
                                                    <span class="text-[10px] fw-black text-emerald-500 uppercase tracking-widest">SOLUTION</span>
                                                    <div class="alert alert-warning py-2 px-3 text-[10px] mb-0 border-0">
                                                        Modifiez la ligne pour fournir un numéro commençant par 4 (Ex: 411CLIENT).
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($otherErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-slate-100 text-slate-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-bug"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Autres Anomalies ({{ $otherErrors }})</h6>
                                                </div>
                                                
                                                <div class="d-flex flex-column gap-2 mt-3">
                                                    @php
                                                        $uniqueOtherErrors = collect($rowsWithStatus)
                                                            ->flatMap(fn($r) => $r['errors'])
                                                            ->unique()
                                                            ->filter(fn($e) => 
                                                                !str_contains($e, 'Générale absente') && 
                                                                !str_contains($e, 'collectif') && 
                                                                !str_contains($e, 'Numéro de tiers manquant')
                                                            )
                                                            ->take(3);
                                                    @endphp
                                                    
                                                    @foreach($uniqueOtherErrors as $err)
                                                        <div class="alert alert-warning py-2 px-3 text-[10px] mb-0 border-0 text-slate-700 d-flex align-items-start">
                                                            <i class="fa-solid fa-triangle-exclamation me-2 mt-1"></i>
                                                            <span>{{ $err }}</span>
                                                        </div>
                                                    @endforeach

                                                    @if($uniqueOtherErrors->isEmpty())
                                                        <p class="text-xs text-slate-500 mb-0">Erreur non classifiée. Voir lignes.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endif
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
                                        <h5 class="font-black text-emerald-900 mb-0">Tiers Prêts pour l'Importation !</h5>
                                        <p class="text-emerald-700 text-sm mb-0">Tous les comptes tiers sont valides.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mb-6">
                            <div class="col-md-3">
                                <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 cursor-pointer card-filter" onclick="filterTable('valid', event)">
                                    <div class="text-xs font-bold text-emerald-600 uppercase mb-1">Lignes Valides</div>
                                    <div class="h4 font-black text-emerald-700 mb-0">{{ $validCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100 cursor-pointer card-filter" onclick="filterTable('error', event)">
                                    <div class="text-xs font-bold text-rose-600 uppercase mb-1">Erreurs détectées</div>
                                    <div class="h4 font-black text-rose-700 mb-0">{{ $errorCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 h-100">
                                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 cursor-pointer card-filter active border-primary" onclick="filterTable('all', event)">
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
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 d-flex align-items-center gap-2" style="width: 450px;">
                                        <div class="input-group input-group-merge border-0 bg-slate-50 rounded-xl px-2 flex-grow-1">
                                            <span class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-magnifying-glass text-slate-400"></i></span>
                                            <input type="text" id="stagingSearch" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrer numéro / libellé..." onkeyup="filterTable()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="staging-card p-0 overflow-hidden mb-6">
                            <div class="staging-table-container">
                                <div class="table-responsive" style="max-height: 500px;">
                                    <table class="table table-staging mb-0">
                                        <thead>
                                            <tr>
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
                                                    <th>NOM / INTITULÉ</th>
                                                    <th>CATÉGORIE</th>
                                                    <th>COMPTE GÉNÉRAL</th>
                                                @else
                                                    <th>DATE</th>
                                                    <th>JOURNAL</th>
                                                    <th>RÉFÉRENCE</th>
                                                    <th>COMPTE</th>
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

                                            @foreach($rowsWithStatus as $rowIndex => $row)
                                                 <tr class="{{ $row['status'] == 'valid' ? 'row-valid' : 'row-error' }}">
                                                     <td class="text-center">
                                                         <span class="status-indicator {{ $row['status'] == 'valid' ? 'bg-emerald-500' : 'bg-rose-500' }}" 
                                                               title="{{ implode(', ', $row['errors']) }}"></span>
                                                     </td>
                                                     
                                                     <td class="@if($row['status'] == 'error' && empty($row['data']['numero_de_tiers'])) cell-error @endif search-target">
                                                         <div class="d-flex flex-column">
                                                              @if(!empty($row['data']['numero_de_tiers']) && $row['data']['numero_de_tiers'] !== 'NON GÉNÉRÉ')
                                                                   <div class="d-flex align-items-center gap-2">
                                                                       <span class="text-primary font-black">
                                                                           <i class="fa-solid fa-magic me-1"></i> {{ $row['data']['numero_de_tiers'] }}
                                                                       </span>
                                                                       @if($row['data']['is_virtual'] ?? false)
                                                                           <i class="fa-solid fa-triangle-exclamation text-warning text-xs" title="Numéro virtuel : Compte collectif manquant"></i>
                                                                       @endif
                                                                   </div>
                                                              @else
                                                                    <span class="text-rose-500 font-bold text-xs italic">
                                                                        <i class="fa-solid fa-circle-xmark me-1"></i> NON GÉNÉRÉ
                                                                    </span>
                                                              @endif

                                                             @if(!empty($row['data']['numero_original']))
                                                                 <div class="text-[10px] text-slate-400 font-medium italic mt-1 d-flex align-items-center gap-1">
                                                                     <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $row['data']['numero_original'] }}
                                                                 </div>
                                                             @endif
                                                         </div>
                                                     </td>
                                                     <td class="fw-bold search-target">
                                                         {{ $row['data']['intitule'] ?? 'Sans nom' }}
                                                     </td>
                                                     <td>
                                                         <div class="d-flex flex-column">
                                                            <span class="badge bg-label-primary rounded-pill">
                                                                {{ $row['data']['type_de_tiers'] ?? 'Client' }}
                                                            </span>
                                                            <span class="text-[9px] text-slate-400 mt-1 italic text-center">Auto-détecté</span>
                                                         </div>
                                                     </td>
                                                     <td class="font-mono text-xs @if(($row['data']['is_virtual'] ?? false) || !empty($row['data']['suggested_account'])) text-danger @endif">
                                                         <div class="d-flex flex-column">
                                                             <span class="fw-bold">{{ $row['data']['compte_general'] ?? '-' }}</span>
                                                             @if(!empty($row['data']['numero_original_compte']))
                                                                 <div class="text-[9px] text-slate-400 font-medium italic mt-1">
                                                                     Orig: {{ $row['data']['numero_original_compte'] }}
                                                                 </div>
                                                             @endif
                                                         </div>
                                                         @if($row['data']['is_virtual'] ?? false)
                                                             <i class="fa-solid fa-circle-exclamation ms-1"></i>
                                                         @endif
                                                     </td>

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
                                                                    data-row-index="{{ $row["index"] }}"
                                                                    data-raw-data="{{ json_encode($import->raw_data[$row["index"]]) }}"
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


                        </div>

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
                                <form action="{{ route('admin.import.commit', $import->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary rounded-xl px-10 py-3 font-bold shadow-lg shadow-primary/20" @if($errorCount > 0) disabled title="Veuillez corriger toutes les erreurs avant la migration." @endif>
                                        <i class="fa-solid fa-cloud-arrow-down me-2"></i> Lancer la migration finale
                                    </button>
                                </form>
                            </div>
                        </div>
                                    @if(request()->has('debug'))
                                        <div class="mt-8 p-6 bg-slate-900 rounded-[24px] text-white font-mono text-[10px]">
                                            <h6 class="text-white border-bottom border-slate-700 pb-2 mb-4">DIAGNOSTIC AGENTIC (v4 - Padded Mode)</h6>
                                            <div class="row">
                                                <div class="col-md-4 border-end border-slate-800">
                                                    <div class="text-blue-400 mb-2">MAPPING ACTUEL:</div>
                                                    <pre>{{ json_encode($import->mapping, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                                <div class="col-md-4 border-end border-slate-800">
                                                    <div class="text-emerald-400 mb-2">LIGNE 1 TRAITÉE:</div>
                                                    <pre>{{ json_encode($rowsWithStatus[0]['data'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-amber-400 mb-2">LIGNE 1 BRUTE (COLONNES):</div>
                                                    @php $firstRowRaw = array_slice($import->raw_data, ($import->mapping['_header_index'] ?? 0) + 1, 1)[0] ?? []; @endphp
                                                    <div class="space-y-1">
                                                        @foreach($firstRowRaw as $ki => $vi)
                                                            <div><span class="text-slate-500">[{{ $ki }}]:</span> {{ var_export($vi, true) }}</div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        let currentFilter = 'all';

        function syncSameColInputs(input) {
            const col = input.dataset.col;
            const value = input.value;
            document.querySelectorAll(`.swal-edit-input[data-col="${col}"]`).forEach(other => {
                if (other !== input) other.value = value;
            });
        }

        function filterTable(type, event) {
            if (type) currentFilter = type;
            
            const searchText = document.getElementById('stagingSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.table-staging tbody tr');
            
            rows.forEach(row => {
                const rowStatus = row.classList.contains('row-valid') ? 'valid' : 'row-error' ? 'error' : '';
                
                // Content Match (Search targets)
                let textMatch = true;
                if (searchText) {
                    const searchTargets = row.querySelectorAll('.search-target');
                    textMatch = Array.from(searchTargets).some(td => td.textContent.toLowerCase().includes(searchText));
                }

                // Status Match
                let statusMatch = true;
                if (currentFilter === 'valid') {
                    statusMatch = row.classList.contains('row-valid');
                } else if (currentFilter === 'error') {
                    statusMatch = row.classList.contains('row-error');
                }

                row.style.display = (textMatch && statusMatch) ? '' : 'none';
            });

            // Update active state of cards
            if (type) {
                document.querySelectorAll('.card-filter').forEach(card => card.classList.remove('active', 'border-primary'));
                if (currentFilter !== 'all') {
                    event.currentTarget.classList.add('active', 'border-primary');
                }
            }
        }

        function deleteStagingRow(importId, rowIndex) {
            Swal.fire({
                title: 'Supprimer cette ligne ?',
                text: "Cette action retirera définitivement la ligne de l'importation en cours.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/import/delete-row/${importId}/${rowIndex}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    });
                }
            });
        }

        function editStagingRow(btn) {
            console.log("Edit Tiers - Button clicked", btn);
            try {
                const importId = btn.dataset.importId;
                const rowIndex = btn.dataset.rowIndex;
                const rawData = JSON.parse(btn.dataset.rawData);
                const mapping = JSON.parse(btn.dataset.mapping);

                console.log("Edit Tiers - Data parsed", { importId, rowIndex, rawData, mapping });

                let html = '<div class="text-start">';
                
                // On crée un champ pour chaque colonne mappée
                Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                    if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
                    
                    let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                    let val = rawData[colIndex] || "";
                    
                    html += `<div class="mb-3">
                                <label class="form-label text-xs font-bold text-slate-500">${label}</label>
                                <input type="text" class="form-control swal-edit-input" 
                                       data-field="${fieldKey}" 
                                       data-col="${colIndex}" 
                                       value="${val}"
                                       oninput="syncSameColInputs(this)">
                             </div>`;
                });
                html += '</div>';

                Swal.fire({
                    title: 'Modifier la ligne',
                    html: html,
                    showCancelButton: true,
                    confirmButtonText: 'Enregistrer',
                    cancelButtonText: 'Annuler',
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                        cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                    },
                    buttonsStyling: false,
                    preConfirm: () => {
                        let values = {};
                        let inputs = document.querySelectorAll('.swal-edit-input');
                        if (inputs.length === 0) return null;
                        inputs.forEach(input => {
                            if (input.dataset.col !== undefined) {
                                values[input.dataset.col] = input.value;
                            }
                        });
                        console.log("Staging Edit - Collected values:", values);
                        return values;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        Swal.showLoading();
                        fetch(`/admin/import/update-row/${importId}/${rowIndex}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ values: result.value })
                        })
                        .then(async response => {
                            console.log("Staging Edit - Response status:", response.status);
                            const text = await response.text();
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error("Staging Edit - Invalid JSON response:", text);
                                throw new Error(`Réponse serveur invalide (${response.status}).`);
                            }
                        })
                        .then(data => {
                            console.log("Staging Edit - Response data:", data);
                            if (data.success) {
                                window.location.reload();
                            } else {
                                Swal.fire('Erreur', data.message || 'Erreur lors de la mise à jour.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error("Staging Edit - Fetch error:", err);
                            Swal.fire('Erreur', 'Détail : ' + err.message, 'error');
                        });
                    }
                });
            } catch (err) {
                console.error("Edit Tiers - Error in editStagingRow", err);
                Swal.fire('Erreur', 'Impossible d\'ouvrir le modal de modification : ' + err.message, 'error');
            }
        }

        function showRowDetails(btn) {
            const data = JSON.parse(btn.dataset.rowData);
            const errors = JSON.parse(btn.dataset.errors);
            let dataHtml = '<div class="text-start">';
            
            dataHtml += '<div class="bg-slate-50 p-4 rounded-2xl mb-4 border border-slate-100 shadow-inner">';
            dataHtml += '<h6 class="font-black text-[10px] uppercase text-slate-400 mb-3 tracking-widest">Fiche de la ligne</h6>';
            
            Object.entries(data).forEach(([key, val]) => {
                // Exclure les champs techniques
                if (val !== null && !key.toLowerCase().includes('header') && !['debit_val', 'credit_val', 'auto_num'].includes(key)) {
                    dataHtml += `<div class="d-flex justify-content-between border-bottom border-slate-100 py-2">
                                    <span class="text-xs font-bold text-slate-500">${key.replace(/_/g, ' ').toUpperCase()}</span>
                                    <span class="text-xs fw-black text-slate-800">${val}</span>
                                 </div>`;
                }
            });
            dataHtml += '</div>';

            if (errors && errors.length > 0) {
                dataHtml += '<div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">';
                dataHtml += '<h6 class="font-black text-[10px] uppercase text-rose-600 mb-3 tracking-widest">Anomalies détectées</h6>';
                dataHtml += '<ul class="ps-4 mb-0">';
                errors.forEach(err => {
                    dataHtml += '<li class="text-rose-700 text-xs font-bold mb-1">' + err + '</li>';
                });
                dataHtml += '</ul></div>';
            } else {
                dataHtml += '<div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 d-flex align-items-center gap-3">';
                dataHtml += '<div class="bg-emerald-500 text-white p-2 rounded-full"><i class="fa-solid fa-check"></i></div>';
                dataHtml += '<div class="text-xs font-bold text-emerald-700">Cette ligne est prête pour l\'importation.</div></div>';
            }
            dataHtml += '</div>';

            Swal.fire({
                title: 'Détails de la ligne',
                html: dataHtml,
                icon: (errors && errors.length > 0) ? 'warning' : 'info',
                confirmButtonText: 'Fermer',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-12 py-3'
                },
                buttonsStyling: false
            });
        }

        function quickCreateAccount(btn) {
            const numero = btn.dataset.compte;
            const libelle = btn.dataset.libelle;
            
            Swal.fire({
                title: 'Création du compte',
                text: `Voulez-vous créer le compte ${numero} - ${libelle} ?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, créer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('admin.import.quick_account') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            numero_compte: numero,
                            intitule: libelle,
                            type_de_compte: 'Bilan'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Succès',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Erreur', 'Une erreur est survenue lors de la création.', 'error');
                    });
                }
            });
        }

        function quickCreateGeneralAccount(baseNumero, requiredLength) {
            // Propose a padded number if shorter than required
            let proposedNumero = baseNumero.toString();
            if (proposedNumero.length < requiredLength) {
                proposedNumero = proposedNumero.padEnd(requiredLength, '0');
            } else if (proposedNumero.length > requiredLength) {
                proposedNumero = proposedNumero.substring(0, requiredLength);
            }

            Swal.fire({
                title: 'Créer le compte collectif',
                html: `
                    <div class="text-start">
                        <p class="text-sm text-slate-500 mb-3">Le système propose ce numéro de compte basé sur vos paramètres (${requiredLength} caractères max).</p>
                        <div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">Numéro de Compte</label>
                            <input type="text" id="swal-new-acc-num" class="form-control" value="${proposedNumero}" maxlength="${requiredLength}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">Intitulé du Compte</label>
                            <input type="text" id="swal-new-acc-name" class="form-control" placeholder="Ex: FOURNISSEURS COLLECTIF" value="COMPTE COLLECTIF ${baseNumero}">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Créer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const num = document.getElementById('swal-new-acc-num').value.trim();
                    const name = document.getElementById('swal-new-acc-name').value.trim();
                    if (!num || !name) {
                        Swal.showValidationMessage('Veuillez remplir tous les champs');
                        return false;
                    }
                    if (num.length !== requiredLength && requiredLength > 0) {
                        Swal.showValidationMessage(`Le numéro doit faire exactement ${requiredLength} caractères`);
                        return false;
                    }
                    return { numero: num, intitule: name };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch("{{ route('admin.import.quick_account') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            numero_compte: result.value.numero,
                            intitule: result.value.intitule,
                            type_de_compte: 'Bilan'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Succès',
                                text: 'Création réussie. Actualisation en cours...',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Erreur', 'Une erreur est survenue lors de la création.', 'error');
                    });
                }
            });
        }

    </script>
</body>
</html>
