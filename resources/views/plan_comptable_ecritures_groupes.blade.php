<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('components.header')

                <!-- / Navbar -->

                <!-- Content wrapper -->

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row g-6 mb-6">

                            <div class="mb-3">
                                <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
                                    <i class='bx  bx-reply-stroke'></i>
                                </a>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">

                                <div class="badge bg-primary text-white px-3 py-2 rounded">
                                    {{ $data['numero_de_compte'] ?? 'N/A' }}
                                </div>
                                <div class="badge bg-primary text-white px-3 py-2 rounded">
                                    {{ $data['intitule'] ?? 'N/A' }}
                                </div>

                                @if ($debutExercice && $finExercice)
                                    <form method="GET" action="{{ route('plan_comptable_ecritures') }}"
                                        class="d-flex align-items-center gap-2 flex-wrap m-0">
                                        <input type="date" name="date_debut"
                                            class="form-control form-control-sm w-auto" value="{{ $debutExercice }}"
                                            readonly>
                                        <input type="date" name="date_fin"
                                            class="form-control form-control-sm w-auto" value="{{ $finExercice }}"
                                            readonly>

                                        <!-- Champs cachés pour conserver le contexte -->
                                        <input type="hidden" name="numero_de_compte"
                                            value="{{ $data['numero_de_compte'] ?? '' }}">
                                        <input type="hidden" name="intitule" value="{{ $data['intitule'] ?? '' }}">

                                        {{-- <button type="submit" class="btn btn-sm btn-primary">Filtrer</button> --}}
                                    </form>
                                @endif

                            </div>





                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Total débit</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ rtrim(rtrim(number_format($totalDebit, 2, ',', ' '), '0'), ',') }}
                                                    </h4>

                                                </div>
                                                <!-- <small class="mb-0">Total Users</small> -->
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="icon-base bx bx-arrow-up icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Total crédit</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ rtrim(rtrim(number_format($totalCredit, 2, ',', ' '), '0'), ',') }}
                                                    </h4>

                                                </div>
                                                <!-- <small class="mb-0">Last week analytics </small> -->
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="icon-base bx bx-arrow-down icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fermer"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fermer"></button>
                                </div>
                            @endif

                            <!-- Alerte succès -->
                            <div id="groupSuccessAlert"
                                class="alert alert-success alert-dismissible fade show mt-3 d-none" role="alert">
                                <span id="groupSuccessMessage"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>

                            <!-- Alerte erreur -->
                            <div id="groupErrorAlert" class="alert alert-danger alert-dismissible fade show mt-3 d-none"
                                role="alert">
                                <span id="groupErrorMessage"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>




                            <!-- Section table -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Listing des ecritures du N°{{ $data['n_saisie'] }}</h5>
                                    <div>
                                        {{-- <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button> --}}



                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalTableauStatique">
                                            Modifier les écritures
                                        </button>




                                        {{-- @if ($exercice->cloturer == 0)
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalTableauStatique">
                                                Modifier les écritures
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                Exercice clôturé
                                            </button>
                                        @endif --}}



                                    </div>
                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="date" id="filter-date" class="form-control"
                                                placeholder="Filtrer par date..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-ref" class="form-control"
                                                placeholder="Filtrer par Référence Pièce..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-compte-general" class="form-control"
                                                placeholder="Filtrer par Compte Général..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-compte-tiers" class="form-control"
                                                placeholder="Filtrer par Compte Tiers..." />
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <button class="btn btn-primary w-100" id="apply-filters">Appliquer les
                                                filtres</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-secondary w-100" id="reset-filters">Réinitialiser
                                                les
                                                filtres</button>
                                        </div>
                                    </div>
                                </div>



                                <!-- Table -->
                                <style>
                                    #table-ecritures {
                                        white-space: nowrap;
                                    }

                                    .clickable-row {
                                        cursor: pointer;
                                        transition: background-color 0.3s ease;
                                    }

                                    .clickable-row:hover {
                                        background-color: #cce5ff !important;
                                        /* bleu clair */
                                        border-left: 4px solid #007bff;
                                    }

                                    /* Couleurs de fond selon le groupe */
                                    .couleur1 {
                                        background-color: #d9edf7 !important;
                                        /* bleu clair */
                                    }

                                    .couleur2 {
                                        background-color: #ffffff !important;
                                        /* blanc */
                                    }
                                </style>
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-striped table-bordered" id="table-ecritures">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>N° Saisie</th>

                                                <th>Référence Pièce</th>

                                                <th>Description</th>

                                                <th>Journal</th>
                                                <th>Compte Général</th>
                                                <th>Compte Tiers</th>
                                                <th>Débit</th>
                                                <th>Crédit</th>
                                                <th>Pièce justificatif</th>
                                                {{-- <th>Action</th> --}}
                                            </tr>
                                        </thead>

                                        @php
                                            $currentNSaisie = null;
                                            $currentColor = 0;
                                            $colors = ['couleur1', 'couleur2'];
                                        @endphp


                                        <tbody>
                                            @foreach ($ecritures as $ecriture)
                                                @php
                                                    if ($ecriture->n_saisie !== $currentNSaisie) {
                                                        $currentNSaisie = $ecriture->n_saisie;
                                                        $currentColor = 1 - $currentColor; // toggle entre 0 et 1
                                                    }
                                                    $rowClass = $colors[$currentColor];
                                                @endphp


                                                {{-- <tr class="clickable-row {{ $rowClass }}"
                                                    data-id="{{ $ecriture->planComptable->id }}" 
                                                    data-intitule="{{ $ecriture->planComptable->intitule }}" 
                                                    data-numero_de_compte="{{ $ecriture->planComptable->numero_de_compte }}" 
                                                    data-n_saisie="{{ $ecriture->n_saisie }}" 
                                                    > --}}

                                                <tr>

                                                    <td>{{ $ecriture->date }}</td>
                                                    <td>{{ $ecriture->n_saisie }}</td>
                                                    <td>{{ $ecriture->reference_piece }}</td>
                                                    <td>{{ $ecriture->description_operation }}</td>



                                                    <td>
                                                        {{ $ecriture->JournauxSaisis ? $ecriture->JournauxSaisis->mois . '/' . $ecriture->JournauxSaisis->annee . '-' . $ecriture->JournauxSaisis->codeJournal->code_journal : '-' }}
                                                    </td>

                                                    <td>
                                                        {{ $ecriture->planComptable ? $ecriture->planComptable->numero_de_compte . ' - ' . $ecriture->planComptable->intitule : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ $ecriture->planTiers ? $ecriture->planTiers->numero_de_tiers . ' - ' . $ecriture->planTiers->intitule : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ fmod($ecriture->debit, 1) == 0 ? number_format($ecriture->debit, 0, ',', ' ') : number_format($ecriture->debit, 2, ',', ' ') }}
                                                    </td>
                                                    <td>
                                                        {{ fmod($ecriture->credit, 1) == 0 ? number_format($ecriture->credit, 0, ',', ' ') : number_format($ecriture->credit, 2, ',', ' ') }}
                                                    </td>


                                                    <!-- Bouton pour afficher la pièce justificative -->
                                                    <td class="text-center">
                                                        @if ($ecriture->piece_justificatif)
                                                            <a href="{{ asset('justificatifs/' . $ecriture->piece_justificatif) }}"
                                                                target="_blank"
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                title="Afficher la pièce justificative">
                                                                <i class='bx bx-eye-alt fs-5'></i>
                                                            </a>

                                                            <a href="{{ asset('justificatifs/' . $ecriture->piece_justificatif) }}"
                                                                download
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                title="Télécharger la pièce justificative">
                                                                <i class='bx bx-file-plus fs-5'></i>
                                                            </a>
                                                        @else
                                                            <i class='bx bx-x-circle text-muted fs-5'
                                                                title="Aucune pièce justificative disponible"></i>
                                                        @endif


                                                    </td>

                                                    {{-- <td>
                                                        <button type="button"
                                                            class="btn p-0 border-0 bg-transparent text-primary btn-edit-ecriture"
                                                            data-bs-toggle="modal" data-bs-target="#modalCenterUpdate"
                                                            data-id="{{ $ecriture->id }}"
                                                            data-date="{{ $ecriture->date }}"
                                                            data-n_saisie="{{ $ecriture->n_saisie }}"
                                                            data-reference_piece="{{ $ecriture->reference_piece }}"
                                                            data-description_operation="{{ $ecriture->description_operation }}"
                                                            data-compte_general_id="{{ $ecriture->plan_comptable_id }}"
                                                            data-compte_general_intitule="{{ $ecriture->planComptable->numero_de_compte . ' - ' . $ecriture->planComptable->intitule }}"
                                                            data-compte_tiers_id="{{ $ecriture->plan_tiers_id }}"
                                                            data-compte_tiers_intitule="{{ $ecriture->planTiers->numero_de_tiers . ' - ' . $ecriture->planTiers->intitule }}"
                                                            data-code_journal_id="{{ $ecriture->code_journal_id }}"
                                                            data-code_journal_code="{{ $ecriture->codeJournal->code_journal }}"
                                                            data-debit="{{ $ecriture->debit }}"
                                                            data-credit="{{ $ecriture->credit }}"
                                                            data-plan_analytique="{{ $ecriture->plan_analytique }}"
                                                            data-piece_justificatif="{{ $ecriture->piece_justificatif }}">
                                                            <i class='bx bx-edit-alt fs-5'></i>
                                                        </button>
                                                    </td> --}}



                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- Modal Creation Ecriture-->
                            <!-- STYLE CSS intégré -->
                            <style>
                                .modal-xl-custom {
                                    max-width: 90%;
                                }

                                #tableEcritures th,
                                #tableEcritures td {
                                    vertical-align: middle;
                                    text-align: center;
                                }

                                #tableEcritures thead {
                                    background-color: #f8f9fa;
                                }

                                #tableEcritures tbody tr:hover {
                                    background-color: #f1f1f1;
                                }

                                #tableEcritures {
                                    font-size: 0.875rem;
                                }

                                #totalDebit,
                                #totalCredit {
                                    color: #0d6efd;
                                    font-weight: bold;
                                }

                                .table-responsive {
                                    max-height: 300px;
                                    overflow-y: auto;
                                }

                                @media (max-width: 768px) {
                                    .modal-xl-custom {
                                        max-width: 100%;
                                        margin: 0 10px;
                                    }

                                    #tableEcritures {
                                        font-size: 0.75rem;
                                    }
                                }
                            </style>

                            <!-- MODAL HTML -->


                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl-custom" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Mise à jour de l’écriture comptable</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>

                                        <form id="formEcritureUpdate" method="POST" action=""
                                            enctype="multipart/form-data">

                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <input type="hidden" id="ecriture_id" name="ecriture_id" />

                                                <div class="row g-3">
                                                    <!-- Ligne 1 -->
                                                    <div class="col-md-4">
                                                        <label for="date_update" class="form-label">Date</label>
                                                        <input type="date" id="date_update" name="date"
                                                            class="form-control" readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="n_saisie_update" class="form-label">N° de
                                                            saisie</label>
                                                        <input type="text" id="n_saisie_update" name="n_saisie"
                                                            class="form-control" readonly />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="code_journal_id_update"
                                                            class="form-label">Journal</label>
                                                        <input type="hidden" id="code_journal_id_update"
                                                            name="code_journal_id" />
                                                        <input type="text" id="code_journal_id_update_code"
                                                            class="form-control" readonly />
                                                    </div>

                                                    <!-- Ligne 2 -->
                                                    <div class="col-md-6">
                                                        <label for="reference_piece_update"
                                                            class="form-label">Référence pièce</label>
                                                        <input type="text" id="reference_piece_update"
                                                            name="reference_piece" class="form-control" />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="description_operation_update"
                                                            class="form-label">Description de l'opération</label>
                                                        <input type="text" id="description_operation_update"
                                                            name="description_operation" class="form-control" />
                                                    </div>

                                                    <!-- Ligne 3 -->
                                                    <div class="col-md-6">
                                                        <label for="compte_general_update" class="form-label">Compte
                                                            Général</label>

                                                        <input type="hidden" id="compte_general_update"
                                                            name="compte_general" />

                                                        <input type="text" id="compte_general_update_intitule"
                                                            class="form-control" readonly />
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="compte_tiers_update" class="form-label">Compte
                                                            Tiers</label>

                                                        <input type="hidden" id="compte_tiers_update"
                                                            name="plan_tiers_id" />

                                                        <input type="text" id="compte_tiers_update_intitule"
                                                            class="form-control" readonly />
                                                    </div>

                                                    <!-- Ligne 4 -->
                                                    <div class="col-md-6">
                                                        <label for="plan_analytique_update" class="form-label">Plan
                                                            Analytique</label>
                                                        <select id="plan_analytique_update" name="plan_analytique"
                                                            class="form-select">
                                                            <option value="1">Oui</option>
                                                            <option value="0">Non</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="debit_update" class="form-label">Débit</label>
                                                        <input type="number" id="debit_update" name="debit"
                                                            class="form-control" placeholder="0.00" step="0.01"
                                                            min="0" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="credit_update" class="form-label">Crédit</label>
                                                        <input type="number" id="credit_update" name="credit"
                                                            class="form-control" placeholder="0.00" step="0.01"
                                                            min="0" />
                                                    </div>

                                                    <!-- Ligne 5 -->
                                                    <div class="col-md-12">
                                                        <label for="piece_justificatif_update"
                                                            class="form-label">Pièce justificative</label>

                                                        <!-- Zone pour afficher dynamiquement soit l'input text soit l'input file -->
                                                        <div id="pieceJustificatifContainer">
                                                            <!-- Par défaut, on affiche le file input -->
                                                            <input type="file" id="piece_justificatif_update"
                                                                name="piece_justificatif" class="form-control"
                                                                accept=".pdf,.jpg,.jpeg,.png" />
                                                        </div>
                                                    </div>

                                                </div>


                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary"
                                                    id="btnUpdateEcriture">Mettre à jour</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>


                            <style>
                                #tableau-statique th,
                                #tableau-statique td {
                                    white-space: nowrap !important;
                                    vertical-align: middle;
                                }
                            </style>

                            <div class="modal fade" id="modalTableauStatique" tabindex="-1"
                                aria-labelledby="modalTableauStatiqueLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content position-relative">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Aperçu du tableau statique</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Résumé des totaux -->
                                            <div id="resume-totaux" class="mb-3 d-flex justify-content-between px-3">
                                                <div><strong>Total Débit : </strong><span id="total-debit">0</span>
                                                    FCFA</div>
                                                <div><strong>Total Crédit : </strong><span id="total-credit">0</span>
                                                    FCFA</div>
                                            </div>

                                            <!-- Message d'erreur -->
                                            <div id="erreur-equilibre" class="alert alert-danger d-none mx-3"
                                                role="alert">
                                                Le total Débit est différent du total Crédit. Veuillez corriger les
                                                écritures avant d’exporter.
                                            </div>

                                            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                                                <table class="table table-bordered table-striped"
                                                    id="tableau-statique">
                                                    <thead>
                                                        <tr>
                                                            <th>Id</th>
                                                            <th>Date</th>
                                                            <th>N° Saisie</th>
                                                            <th>Référence</th>
                                                            <th>Description</th>
                                                            <th>Journal</th>
                                                            <th>Compte Général</th>
                                                            <th>Compte Tiers</th>
                                                            <th>Plan Analytique</th>
                                                            <th>Débit</th>
                                                            <th>Crédit</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($ecritures as $ecriture)
                                                            <tr>
                                                                <td>{{ $ecriture->id }}</td>
                                                                <td>{{ $ecriture->date }}</td>
                                                                <td>{{ $ecriture->n_saisie }}</td>
                                                                <td contenteditable="true">
                                                                    {{ $ecriture->reference_piece }}</td>
                                                                <td contenteditable="true">
                                                                    {{ $ecriture->description_operation }}</td>

                                                                {{-- Select Journal --}}
                                                                <td>
                                                                    <select class="selectpicker w-100"
                                                                        data-width="auto" data-live-search="true">
                                                                        @foreach ($JournauxSaisi as $journalS)
                                                                            <option value="{{ $journalS->id }}"
                                                                                {{ $ecriture->JournauxSaisis->id == $journalS->id ? 'selected' : '' }}>
                                                                                {{ $journalS->mois }}/
                                                                                {{ $journalS->annee }}-
                                                                                {{ $journalS->codeJournal->code_journal }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>

                                                                {{-- Select Compte Général --}}
                                                                <td>
                                                                    <select class="selectpicker w-100"
                                                                        data-width="auto" data-live-search="true">
                                                                        @foreach ($plansComptables as $plan)
                                                                            <option value="{{ $plan->id }}"
                                                                                {{ $ecriture->plan_comptable_id == $plan->id ? 'selected' : '' }}>
                                                                                {{ $plan->numero_de_compte }} -
                                                                                {{ $plan->intitule }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>

                                                                {{-- Select Compte Tiers --}}
                                                                <td>
                                                                    <select class="selectpicker w-100"
                                                                        data-width="auto" data-live-search="true">
                                                                        <option value="">-</option>
                                                                        @foreach ($plansTiers as $tier)
                                                                            <option value="{{ $tier->id }}"
                                                                                {{ $ecriture->plan_tiers_id == $tier->id ? 'selected' : '' }}>
                                                                                {{ $tier->numero_de_tiers }} -
                                                                                {{ $tier->intitule }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>

                                                                {{-- Select Plan Analytique --}}
                                                                <td>
                                                                    <select class="selectpicker w-100"
                                                                        data-width="auto" data-live-search="false">
                                                                        <option value="1"
                                                                            {{ $ecriture->plan_analytique ? 'selected' : '' }}>
                                                                            Oui</option>
                                                                        <option value="0"
                                                                            {{ !$ecriture->plan_analytique ? 'selected' : '' }}>
                                                                            Non</option>
                                                                    </select>
                                                                </td>

                                                                <td contenteditable="true" class="cell-debit">
                                                                    {{ $ecriture->debit == (int) $ecriture->debit
                                                                        ? number_format($ecriture->debit, 0, ',', ' ')
                                                                        : number_format($ecriture->debit, 2, ',', ' ') }}
                                                                </td>
                                                                <td contenteditable="true" class="cell-credit">
                                                                    {{ $ecriture->credit == (int) $ecriture->credit
                                                                        ? number_format($ecriture->credit, 0, ',', ' ')
                                                                        : number_format($ecriture->credit, 2, ',', ' ') }}
                                                                </td>

                                                            </tr>
                                                        @endforeach
                                                    </tbody>


                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            <button class="btn btn-success"
                                                onclick="exporterTableau2()">Modifier</button>
                                        </div>
                                    </div>

                                    <div id="modalLoaderOverlay" class="d-none"
                                        style="
                                                position: absolute;
                                                top: 0;
                                                left: 0;
                                                z-index: 1051;
                                                width: 100%;
                                                height: 100%;
                                                background-color: rgba(255,255,255,0.7);
                                                display: flex;
                                                justify-content: center;
                                                align-items: center;">
                                        <div class="spinner-border text-primary" role="status"
                                            style="width: 3rem; height: 3rem;">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                    </div>

                                </div>
                            </div>


                            <!-- Modal Confirmation de suppression -->
                            <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
                                aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header text-white justify-content-center">
                                            <h5 class="modal-title" id="deleteModalLabel">
                                                <i class="bx bx-error-circle me-2"></i>Confirmer la
                                                suppression
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <p class="mb-0">
                                                Êtes-vous sûr de vouloir supprimer ce projet ? Cette
                                                action est <strong>irréversible</strong>.
                                            </p>
                                            <p class="fw-bold text-danger mt-2" id="projectToDelete"></p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->

        <!-- Core JS -->

        <!-- Initialisation JS -->

        @include('components.footer')

        <script>
            const plan_comptable_ecritures_groupesUpdateBaseUrl =
                "{{ route('plan_comptable_ecritures_groupes.miseAJourMassive') }}";
        </script>

        <script src="{{ asset('js/plan_comptable_ecritures_groupes.js') }}"></script>
        <!-- Initialisation Select2 -->


</body>

</html>
