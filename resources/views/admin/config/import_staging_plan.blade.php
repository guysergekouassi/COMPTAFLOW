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
                                            $existingAccountsErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'déjà présent'))->count();
                                            $lengthErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'Longueur incorrecte'))->count();
                                            $missingErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'manquant'))->count();
                                            $otherErrors = collect($rowsWithStatus)->filter(fn($r) => !empty($r['errors']) && 
                                                !str_contains(implode(' ', $r['errors']), 'déjà présent') && 
                                                !str_contains(implode(' ', $r['errors']), 'Longueur incorrecte') && 
                                                !str_contains(implode(' ', $r['errors']), 'manquant'))->count();
                                        @endphp

                                        @if($existingAccountsErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-rose-100 text-rose-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-copy"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Comptes Existants ({{ $existingAccountsErrors }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Ces comptes sont déjà enregistrés.</p>
                                                <div class="d-flex flex-column gap-2">
                                                    <span class="badge bg-label-primary text-start py-2 px-3 fw-normal whitespace-normal">
                                                        <i class="fa-solid fa-pen me-1"></i> Modifiez le numéro du compte.
                                                    </span>
                                                    <span class="badge bg-label-danger text-start py-2 px-3 fw-normal whitespace-normal">
                                                        <i class="fa-solid fa-trash me-1"></i> Supprimez la ligne.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($lengthErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-orange-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-ruler-horizontal"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Format Incorrect ({{ $lengthErrors }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">La longueur ne respecte pas les <strong>{{ $user->company->account_digits }} chiffres</strong>.</p>
                                                <div class="d-flex flex-column gap-2">
                                                    <span class="badge bg-label-warning text-start py-2 px-3 fw-normal whitespace-normal text-dark">
                                                        <i class="fa-solid fa-wrench me-1"></i> Les numéros seront automatiquement standardisés à l'importation.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($missingErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-slate-100 text-slate-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Données Manquantes ({{ $missingErrors }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Certaines colonnes obligatoires sont vides.</p>
                                                <div class="alert alert-secondary py-2 px-3 text-[10px] mb-0 border-0">
                                                    Complétez via le bouton modifier <i class="fa-solid fa-pen"></i>.
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
                                                                !str_contains($e, 'existant') && 
                                                                !str_contains($e, 'Longueur') && 
                                                                !str_contains($e, 'manquant')
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
                                        <h5 class="font-black text-emerald-900 mb-0">Plan Comptable Conforme !</h5>
                                        <p class="text-emerald-700 text-sm mb-0">Toutes les lignes sont prêtes pour l'importation.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mb-6">
                            <div class="col-md-3">
                                <div class="bg-emerald-50 p-4 rounded-2xl border {{ $statusFilter == 'valid' ? 'border-emerald-500 active' : 'border-emerald-100' }} cursor-pointer card-filter" onclick="window.location.href='{{ request()->fullUrlWithQuery(['status' => 'valid', 'page' => 1]) }}'">
                                    <div class="text-xs font-bold text-emerald-600 uppercase mb-1">Lignes Valides</div>
                                    <div class="h4 font-black text-emerald-700 mb-0">{{ $validCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-rose-50 p-4 rounded-2xl border {{ $statusFilter == 'error' ? 'border-rose-500 active' : 'border-rose-100' }} cursor-pointer card-filter" onclick="window.location.href='{{ request()->fullUrlWithQuery(['status' => 'error', 'page' => 1]) }}'">
                                    <div class="text-xs font-bold text-rose-600 uppercase mb-1">Erreurs détectées</div>
                                    <div class="h4 font-black text-rose-700 mb-0">{{ $errorCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 h-100">
                                    <div class="bg-white p-4 rounded-2xl border {{ $statusFilter == 'all' ? 'border-primary active' : 'border-slate-100' }} cursor-pointer card-filter flex-grow-1" onclick="window.location.href='{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}'">
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
                                        <button type="button" id="addStagingRowBtn" class="btn btn-sm btn-primary py-2 px-3 rounded-xl" 
                                                data-import-id="{{ $import->id }}"
                                                data-mapping="{{ json_encode($mapping) }}"
                                                onclick="addStagingRow(this)">
                                            <i class="fa-solid fa-plus-circle me-1"></i> Ajouter une ligne
                                        </button>
                                        <div class="input-group input-group-merge border-0 bg-slate-50 rounded-xl px-2 flex-grow-1">
                                            <span class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-magnifying-glass text-slate-400"></i></span>
                                            <input type="text" id="stagingSearch" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrer numéro / libellé..." value="{{ $searchFilter }}" onkeyup="if(event.key === 'Enter') window.location.href='{{ request()->fullUrlWithQuery(['search' => '']) }}'.replace('search=', 'search=' + encodeURIComponent(this.value))">
                                        </div>
                                    </div>
                                </div>
                            </div>
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

                                            @foreach($rowsWithStatusPaged as $rowIndex => $row)
                                                <tr class="{{ $row['status'] == 'valid' ? 'row-valid' : 'row-error' }}" data-status="{{ $row['status'] }}">
                                                    <td class="text-center">
                                                        <input type="checkbox" class="row-checkbox form-check-input" data-row-index="{{ $row['index'] ?? $rowIndex }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="status-indicator {{ $row['status'] == 'valid' ? 'bg-emerald-500' : 'bg-rose-500' }}" 
                                                              title="{{ implode(', ', $row['errors']) }}"></span>
                                                    </td>
                                                    
                                                    @if($import->type == 'initial')
                                                        <td class="@if($row['status'] == 'error') cell-error @endif search-target">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-black">{{ $row['data']['numero_de_compte'] ?? '-' }}</span>
                                                                 @if(!empty($row['data']['numero_original']))
                                                                     <div class="text-[9px] text-slate-400 font-medium italic mt-1 d-flex align-items-center gap-1">
                                                                         <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $row['data']['numero_original'] }}
                                                                     </div>
                                                                 @endif
                                                                 @if(!empty($row['data']['suggested_account']) && $row['data']['suggested_account'] != ($row['data']['numero_de_compte'] ?? ''))
                                                                     <div class="text-[9px] text-emerald-500 font-black italic mt-1">
                                                                         <i class="fa-solid fa-arrow-turn-up fa-rotate-90 me-1"></i> Suggéré: {{ $row['data']['suggested_account'] }}
                                                                     </div>
                                                                 @endif
                                                            </div>
                                                        </td>
                                                        <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                    @elseif($import->type == 'journals')
                                                        <td class="@if($row['status'] == 'error') cell-error @endif">
                                                            {{ $row['data']['code_journal'] ?? '-' }}
                                                        </td>
                                                        <td>{{ $row['data']['intitule'] ?? '-' }}</td>
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
                                                        <td class="@if($row['status'] == 'error' && empty($row['data'][$mapping['numero_de_tiers'] ?? '']) && empty($row['data']['auto_num'])) cell-warning @endif">
                                                            @if(!empty($row['data']['auto_num']))
                                                                <span class="text-primary font-black"><i class="fa-solid fa-magic me-1"></i> {{ $row['data']['auto_num'] }}</span>
                                                            @elseif(!empty($row['data'][$mapping['numero_de_tiers'] ?? '']))
                                                                {{ $row['data'][$mapping['numero_de_tiers']] }}
                                                            @else
                                                                <span class="badge bg-label-warning italic text-[10px]">Sera auto-généré</span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-bold">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge bg-label-primary">
                                                                {{ $row['data']['type_de_tiers'] ?? 'Client' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $row['data']['compte_general'] ?? '-' }}</td>
                                                    @else
                                                        <td>{{ $row['data']['jour'] ?? '-' }}</td>
                                                        <td>{{ $row['data']['journal'] ?? '-' }}</td>
                                                        <td>{{ $row['data']['reference'] ?? '-' }}</td>
                                                        <td class="@if(in_array('error', $row['errors']) || str_contains(implode(' ', $row['errors']), 'Compte')) cell-error @endif" 
                                                            title="{{ implode(', ', array_filter($row['errors'], fn($e) => str_contains($e, 'Compte'))) }}">
                                                            {{ $row['data']['compte'] ?? '-' }}
                                                        </td>
                                                        <td>{{ Str::limit($row['data']['libelle'] ?? '-', 30) }}</td>
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
                                @if($errorCount > 0)
                                <button type="button" class="btn btn-warning rounded-xl px-6 py-3 font-bold" onclick="exportErrorsToCSV()">
                                    <i class="fa-solid fa-file-excel me-2"></i> Exporter erreurs
                                </button>
                                @endif
                                 <form action="{{ route('admin.import.commit', $import->id) }}" method="POST">
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

        // filterTable() remplacée par la logique côté serveur via URL params
                                    

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
                    fetch(`/admin/config/import-staging/delete-row/${importId}/${rowIndex}`, {
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

        function addStagingRow(btn) {
            const importId = btn.dataset.importId;
            const mapping = JSON.parse(btn.dataset.mapping);

            let html = '<div class="text-start">';
            Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
                
                let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                html += `<div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">${label}</label>
                            <input type="text" class="form-control swal-add-input" 
                                   data-field="${fieldKey}" 
                                   data-col="${colIndex}" 
                                   value="">
                         </div>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Ajouter une ligne',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Ajouter',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    let values = {};
                    let inputs = document.querySelectorAll('.swal-add-input');
                    inputs.forEach(input => {
                        values[input.dataset.col] = input.value;
                    });
                    return values;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.showLoading();
                    fetch(`/admin/config/import-staging/add-row/${importId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ values: result.value })
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
            const importId = btn.dataset.importId;
            const rowIndex = btn.dataset.rowIndices || btn.dataset.rowIndex;
            // recordId variable removed
            const rowData = JSON.parse(btn.dataset.rowData);
            const mapping = JSON.parse(btn.dataset.mapping);

            let html = '<div class="text-start">';
            
            // On crée un champ pour chaque colonne mappée
            Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
                
                let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                let val = rowData[fieldKey] || "";
                
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
                        values[input.dataset.col] = input.value;
                        values[input.dataset.field] = input.value;
                    });
                    console.log("Staging Edit - Collected values:", values);
                    return values;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.showLoading();
                    fetch(`/admin/config/import-staging/update-row/${importId}/${rowIndex}`, {
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

        function exportErrorsToCSV() {
            window.location.href = "{{ route('admin.import.export_errors', $import->id) }}";
        }

        function selectAndBulkDeleteErrors() {
            Swal.fire({
                title: 'Supprimer TOUTES les erreurs ?',
                text: 'Cette action supprimera toutes les lignes en erreur ({{ $errorCount }} lignes) de cet import.',
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Oui, tout supprimer', cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-danger rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch("{{ route('admin.import.delete_errors', $import->id) }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    }).then(response => {
                        if (!response.ok) throw new Error(response.statusText);
                        return response.json();
                    }).catch(error => { Swal.showValidationMessage(`Erreur: ${error}`); });
                }
            }).then(result => {
                if (result.isConfirmed && result.value.success) {
                    Swal.fire('Supprimé !', result.value.message, 'success').then(() => { window.location.reload(); });
                }
            });
        }

    </script>
</body>
</html>
