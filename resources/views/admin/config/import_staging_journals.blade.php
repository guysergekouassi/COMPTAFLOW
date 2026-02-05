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

                        @php
                            $existingAccountsErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'déjà présent') || str_contains(implode(' ', $r['errors']), 'existe déjà'))->count();
                            $existingJournalsErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'déjà existant') || str_contains(implode(' ', $r['errors']), 'existe déjà') || str_contains(implode(' ', $r['errors']), 'Doublon'))->count();
                            $lengthErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'ne respecte pas la configuration') || str_contains(implode(' ', $r['errors']), 'invalide') || str_contains(implode(' ', $r['errors']), 'Max'))->count();
                            $formatErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'Longueur incorrecte') || str_contains(implode(' ', $r['errors']), 'inconnu'))->count();
                            $missingErrors = collect($rowsWithStatus)->filter(fn($r) => str_contains(implode(' ', $r['errors']), 'manquant') || str_contains(implode(' ', $r['errors']), 'Configuration'))->count();
                            $otherErrors = collect($rowsWithStatus)->filter(fn($r) => !empty($r['errors']) && 
                                !str_contains(implode(' ', $r['errors']), 'manquant') && 
                                !str_contains(implode(' ', $r['errors']), 'Configuration') && 
                                !str_contains(implode(' ', $r['errors']), 'Longueur') && 
                                !str_contains(implode(' ', $r['errors']), 'invalide') && 
                                !str_contains(implode(' ', $r['errors']), 'Max') && 
                                !str_contains(implode(' ', $r['errors']), 'inconnu') && 
                                !str_contains(implode(' ', $r['errors']), 'déjà') && 
                                !str_contains(implode(' ', $r['errors']), 'Doublon'))->count();
                        @endphp

                        @if($errorCount > 0)
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-white p-6 rounded-[24px] border border-rose-200 bg-rose-50/20 shadow-sm">
                                    <h5 class="font-black mb-4 d-flex align-items-center gap-2 text-rose-800">
                                        <i class="fa-solid fa-clipboard-check"></i> Rapport de Validation & Manuel de Correction
                                    </h5>
                                    
                                    <div class="row g-4">
                                        @if($lengthErrors > 0)
                                        <div class="col-md-3">
                                            <div class="p-3 bg-red-50 rounded-xl border border-red-100">
                                            <div class="text-xs font-bold text-red-600 uppercase mb-1">Nb de caractères invalide (max 4)</div>
                                                <div class="h5 font-black text-red-700 mb-0">{{ $lengthErrors }} <span class="text-xs font-normal">lignes dépassant la limite</span></div>
                                            </div>
                                        </div>
                                        @endif
                                        @if($existingAccountsErrors > 0 || $existingJournalsErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-rose-100 text-rose-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-copy"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Doublons ({{ $existingAccountsErrors + $existingJournalsErrors }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Ces éléments existent déjà dans votre base de données.</p>
                                                <div class="d-flex flex-column gap-2">
                                                    <span class="badge bg-label-primary text-start py-2 px-3 fw-normal whitespace-normal">
                                                        <i class="fa-solid fa-pen me-1"></i> Modifiez les codes ou comptes.
                                                    </span>
                                                    <span class="badge bg-label-danger text-start py-2 px-3 fw-normal whitespace-normal">
                                                        <i class="fa-solid fa-trash me-1"></i> Supprimez les lignes inutiles.
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if($formatErrors > 0)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-orange-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-ruler-horizontal"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Format Incorrect ({{ $formatErrors }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Problèmes de longueur ou codes inconnus détectés.</p>
                                                <div class="d-flex flex-column gap-2">
                                                    <span class="badge bg-label-warning text-start py-2 px-3 fw-normal whitespace-normal text-dark">
                                                        <i class="fa-solid fa-wrench me-1"></i> Vérifiez la configuration (chiffres, racines).
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
                                                    <h6 class="font-bold mb-0">Champs Manquants ({{ $missingErrors }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Certaines colonnes obligatoires ne sont pas renseignées.</p>
                                                <div class="alert alert-secondary py-2 px-3 text-[10px] mb-0 border-0">
                                                    Complétez les données via le bouton modifier <i class="fa-solid fa-pen"></i>.
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
                                                                !str_contains($e, 'manquant') && 
                                                                !str_contains($e, 'Configuration') &&
                                                                !str_contains($e, 'Longueur') && 
                                                                !str_contains($e, 'invalide') &&
                                                                !str_contains($e, 'Max') &&
                                                                !str_contains($e, 'inconnu') &&
                                                                !str_contains($e, 'déjà') &&
                                                                !str_contains($e, 'Doublon')
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
                                        <h5 class="font-black text-emerald-900 mb-0">Félicitations ! Aucune erreur détectée.</h5>
                                        <p class="text-emerald-700 text-sm mb-0">Toutes les lignes sont conformes aux règles métiers. Vous pouvez lancer la migration finale en toute sécurité.</p>
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
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 cursor-pointer card-filter flex-grow-1 active border-primary" onclick="filterTable('all', event)">
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
                                                    
                                                    @if($import->type == 'initial')
                                                        <td class="@if($row['status'] == 'error') cell-error @endif search-target">
                                                            {{ $row['data']['numero_de_compte'] ?? '-' }}
                                                        </td>
                                                        <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                    @elseif($import->type == 'journals')
                                                        <td class="@if($row['status'] == 'error' && (str_contains(implode(' ', $row['errors']), 'Code') || str_contains(implode(' ', $row['errors']), 'long'))) cell-error @endif search-target">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-black">{{ $row['data']['code_journal'] ?? '-' }}</span>
                                                                 @if(!empty($row['data']['numero_original']))
                                                                     <div class="text-[9px] text-slate-400 font-medium italic mt-1 d-flex align-items-center gap-1">
                                                                         <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $row['data']['numero_original'] }}
                                                                     </div>
                                                                 @endif
                                                            </div>
                                                        </td>
                                                        <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge bg-label-info">
                                                                {{ $row['data']['type'] ?? 'Achats' }}
                                                            </span>
                                                        </td>
                                                        <td class="@if($row['status'] == 'error' && (str_contains(implode(' ', $row['errors']), 'trésorerie') || str_contains(implode(' ', $row['errors']), 'Compte Inconnu'))) cell-error @endif">
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold">{{ $row['data']['compte_de_tresorerie'] ?? '-' }}</span>
                                                                @if(!empty($row['data']['numero_original_compte']))
                                                                    <div class="text-[9px] text-slate-400 font-medium italic mt-1">
                                                                        Orig: {{ $row['data']['numero_original_compte'] }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                                {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'OUI' : 'NON' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $row['data']['rapprochement_sur'] ?? '-' }}</td>
                                                    @elseif($import->type == 'tiers')
                                                        <td class="@if($row['status'] == 'error' && empty($row['data']['numero_de_tiers']) && empty($row['data']['auto_num'])) cell-warning @endif">
                                                            @if(!empty($row['data']['auto_num']))
                                                                <span class="text-primary font-black"><i class="fa-solid fa-magic me-1"></i> {{ $row['data']['auto_num'] }}</span>
                                                            @elseif(!empty($row['data']['numero_de_tiers']))
                                                                {{ $row['data']['numero_de_tiers'] }}
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
                                                                    data-row-index="{{ $row["index"] }}"
                                                                    data-raw-data="{{ json_encode($import->raw_data[$row["index"]]) }}"
                                                                    data-mapping="{{ json_encode($mapping) }}"
                                                                    data-overrides="{{ json_encode([
                                                                        'type' => $row['data']['type_override_index'] ?? null,
                                                                        'poste' => $row['data']['poste_override_index'] ?? null,
                                                                        'compte' => $row['data']['compte_override_index'] ?? null,
                                                                        'analytique' => $row['data']['analytique_override_index'] ?? null,
                                                                        'rapprochement' => $row['data']['rapprochement_override_index'] ?? null,
                                                                        'codeOrig' => $row['data']['code_journal_override_index'] ?? null
                                                                    ]) }}"
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
                                    <button type="submit" class="btn btn-primary rounded-xl px-10 py-3 font-bold shadow-lg shadow-primary/20">
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
        const plansComptablesJS = @json($plansComptables);

        function toggleTresorerieFields(type, context = 'form') {
            const containerId = context === 'swal' ? 'tresorerie_fields_swal' : 'tresorerie_fields';
            const container = document.getElementById(containerId);
            if (!container) return;

            if (['Trésorerie', 'Tresorerie', 'Banque', 'Caisse'].includes(type)) {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }

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

        // Liste des comptes de classe 5 déjà définie plus haut

        function editStagingRow(btn) {
            console.log("Edit Journal - Button clicked", btn);
            try {
                const importId = btn.dataset.importId;
                const rowIndex = btn.dataset.rowIndex;
                const rawData = JSON.parse(btn.dataset.rawData);
                const mapping = JSON.parse(btn.dataset.mapping);
                const overrideIndexes = JSON.parse(btn.dataset.overrides || '{}');

                console.log("Edit Journal - Data parsed", { importId, rowIndex, rawData, mapping, overrideIndexes });

                // Récupération des données actuelles
                const intituleCol = mapping['intitule'] !== "AUTO" ? mapping['intitule'] : null;
                let currentIntitule = intituleCol !== null ? (rawData[intituleCol] || "") : "";

                const codeOrigCol = overrideIndexes.codeOrig;
                const codeMappéCol = mapping['code_journal'] !== "AUTO" ? mapping['code_journal'] : null;
                let currentCodeOrig = (codeOrigCol !== null && rawData[codeOrigCol] !== undefined) ? rawData[codeOrigCol] : (codeMappéCol !== null ? (rawData[codeMappéCol] || "") : "");

                const typeCol = overrideIndexes.type;
                const typeMappéCol = mapping['type'] !== "AUTO" ? mapping['type'] : null;
                let currentType = (typeCol !== null && rawData[typeCol] !== undefined) ? rawData[typeCol] : (typeMappéCol !== null ? (rawData[typeMappéCol] || "") : "");
                
                if (currentType === 'Trésorerie') currentType = 'Tresorerie';

                let currentPoste = (overrideIndexes.poste !== null) ? (rawData[overrideIndexes.poste] || "") : "";
                let currentCompte = (overrideIndexes.compte !== null) ? (rawData[overrideIndexes.compte] || "") : (mapping['compte_de_tresorerie'] !== "AUTO" ? (rawData[mapping['compte_de_tresorerie']] || "") : "");
                let currentAnalytique = (overrideIndexes.analytique !== null) ? (rawData[overrideIndexes.analytique] || "non") : (mapping['traitement_analytique'] !== "AUTO" ? (rawData[mapping['traitement_analytique']] || "non") : "non");
                let currentRapprochement = (overrideIndexes.rapprochement !== null) ? (rawData[overrideIndexes.rapprochement] || "") : (mapping['rapprochement_sur'] !== "AUTO" ? (rawData[mapping['rapprochement_sur']] || "") : "");

                // Génération des options de comptes
                let optionsCompteTreso = '<option value="">-- Sélectionner un compte --</option>';
                plansComptablesJS.forEach(plan => {
                    const isSelected = plan.numero_de_compte === currentCompte ? 'selected' : '';
                    optionsCompteTreso += `<option value="${plan.numero_de_compte}" ${isSelected}>${plan.numero_de_compte} - ${(plan.intitule || '').replace(/"/g, "'")}</option>`;
                });

                const esc = (str) => String(str || '').replace(/"/g, '&quot;');

                let html = `
                <div class="text-start">
                    <div class="row g-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-black text-slate-700">Code Journal</label>
                            <input type="text" id="swal_code_journal" class="form-control swal-edit-input" data-col="${codeOrigCol}" value="${esc(currentCodeOrig)}" placeholder="Ex: ACH">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-black text-slate-700">Type</label>
                            <select class="form-select swal-edit-input" data-col="${typeCol}" onchange="toggleTresorerieFields(this.value, 'swal')">
                                <option value="Achats" ${currentType === 'Achats' ? 'selected' : ''}>Achats</option>
                                <option value="Ventes" ${currentType === 'Ventes' ? 'selected' : ''}>Ventes</option>
                                <option value="Tresorerie" ${['Tresorerie', 'Trésorerie', 'Banque', 'Caisse'].includes(currentType) ? 'selected' : ''}>Trésorerie</option>
                                <option value="Opérations Diverses" ${currentType === 'Opérations Diverses' ? 'selected' : ''}>Opérations Diverses</option>
                                <option value="Standard" ${currentType === 'Standard' ? 'selected' : ''}>Standard</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label font-black text-slate-700">Intitulé</label>
                            <input type="text" class="form-control swal-edit-input" data-col="${intituleCol || ''}" value="${esc(currentIntitule)}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label font-black text-slate-700">Traitement Analytique</label>
                            <select class="form-select swal-edit-input" data-col="${overrideIndexes.analytique}">
                                <option value="non" ${String(currentAnalytique).toLowerCase() === 'non' ? 'selected' : ''}>Non</option>
                                <option value="oui" ${String(currentAnalytique).toLowerCase() === 'oui' ? 'selected' : ''}>Oui</option>
                            </select>
                        </div>

                        <div id="tresorerie_fields_swal" class="col-12 mt-2 ${['Tresorerie', 'Trésorerie', 'Banque', 'Caisse'].includes(currentType) ? '' : 'd-none'}">
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div class="mb-3">
                                    <label class="form-label font-black">Compte (Classe 5)</label>
                                    <select class="form-select swal-edit-input" data-col="${overrideIndexes.compte}">
                                        ${optionsCompteTreso}
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label font-black">Type de Trésorerie</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="swal_poste" id="treso_caisse_swal" value="Caisse" ${currentPoste === 'Caisse' ? 'checked' : ''} onchange="syncPosteToInput('Caisse')">
                                            <label class="form-check-label" for="treso_caisse_swal">Caisse</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="swal_poste" id="treso_banque_swal" value="Banque" ${currentPoste === 'Banque' ? 'checked' : ''} onchange="syncPosteToInput('Banque')">
                                            <label class="form-check-label" for="treso_banque_swal">Banque</label>
                                        </div>
                                    </div>
                                    <input type="hidden" class="swal-edit-input" data-col="${overrideIndexes.poste}" id="swal_poste_hidden" value="${esc(currentPoste)}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label font-black text-slate-700">Autre (Optionnel)</label>
                                    <input type="text" id="treso_autre_swal" class="form-control border-slate-200 py-3 rounded-xl" placeholder="Saisir un autre libellé..." value="${esc(!['Banque', 'Caisse'].includes(currentPoste) ? currentPoste : '')}" oninput="syncPosteToInput(this.value)">
                                </div>
                                <div>
                                    <label class="form-label font-black">Rapprochement</label>
                                    <select class="form-select swal-edit-input" data-col="${overrideIndexes.rapprochement}">
                                        <option value="">-- Aucun --</option>
                                        <option value="Manuel" ${currentRapprochement === 'Manuel' ? 'selected' : ''}>Manuel</option>
                                        <option value="Automatique" ${currentRapprochement === 'Automatique' ? 'selected' : ''}>Automatique</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                // Fonctions utilitaires
                window.toggleTresorerieFields = function(val, prefix) {
                    const fields = document.getElementById(`tresorerie_fields_` + prefix);
                    if (fields) {
                        if (val === 'Tresorerie' || val === 'Banque' || val === 'Caisse') fields.classList.remove('d-none');
                        else fields.classList.add('d-none');
                    }
                };

                window.syncPosteToInput = function(val) {
                    const hidden = document.getElementById('swal_poste_hidden');
                    const caisse = document.getElementById('treso_caisse_swal');
                    const banque = document.getElementById('treso_banque_swal');
                    const autre = document.getElementById('treso_autre_swal');
                    const codeInput = document.getElementById('swal_code_journal');

                    hidden.value = val;

                    if (val === 'Banque' || val === 'Caisse') {
                        // Sélection via Radio
                        autre.value = '';
                        caisse.disabled = false;
                        banque.disabled = false;
                        if (codeInput) codeInput.value = (val === 'Banque') ? 'BQ' : 'CAI';
                    } else {
                        // Saisie via Autre
                        if (val && val.trim() !== '') {
                            caisse.checked = false;
                            banque.checked = false;
                            caisse.disabled = true;
                            banque.disabled = true;
                            
                            // Génération automatique du code (3 premières lettres majuscules)
                            if (codeInput) {
                                let clean = val.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                                codeInput.value = clean.substring(0, 3);
                            }
                        } else {
                            // Si on vide le champ Autre, on réactive les radios
                            caisse.disabled = false;
                            banque.disabled = false;
                        }
                    }
                };

                Swal.fire({
                    title: '<span class="text-white font-black">Modification du Journal</span>',
                    html: html,
                    width: '600px',
                    showCancelButton: true,
                    confirmButtonText: 'Enregistrer',
                    cancelButtonText: 'Annuler',
                    customClass: {
                        title: 'bg-slate-900 p-6 m-0 rounded-t-3xl text-start',
                        popup: 'border-0 shadow-2xl rounded-3xl overflow-hidden p-0',
                        confirmButton: 'btn btn-primary px-8 py-3 rounded-xl me-2',
                        cancelButton: 'btn btn-outline-secondary px-6 py-3 rounded-xl'
                    },
                    buttonsStyling: false,
                    preConfirm: () => {
                        let values = {};
                        document.querySelectorAll('.swal-edit-input').forEach(input => {
                            const col = input.dataset.col;
                            if (col && col !== "null") values[col] = input.value;
                        });
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
                            const text = await response.text();
                            try { return JSON.parse(text); } catch (e) { throw new Error(`Réponse serveur invalide.`); }
                        })
                        .then(data => {
                            if (data.success) window.location.reload();
                            else Swal.fire('Erreur', data.message || 'Erreur lors de la mise à jour.', 'error');
                        })
                        .catch(err => Swal.fire('Erreur', 'Détail : ' + err.message, 'error'));
                    }
                });
            } catch (err) {
                console.error("Edit Journal - Error", err);
                Swal.fire('Erreur', 'Impossible d\'ouvrir le modal : ' + err.message, 'error');
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
                title: 'Création rapide de compte',
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
    </script>
</body>
</html>
