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

                            <div class="d-flex flex-wrap gap-2">
                                <div class="badge bg-primary text-white px-3 py-2 rounded">{{ $data['annee'] ?? 'N/A' }}
                                </div>
                                <div class="badge bg-secondary text-white px-3 py-2 rounded">
                                    {{ \Carbon\Carbon::createFromDate(null, $data['mois'] ?? 1)->locale('fr')->monthName }}
                                </div>
                                <div class="badge bg-info text-dark px-3 py-2 rounded">{{ $data['code'] ?? 'N/A' }}
                                </div>
                                <div class="badge bg-success text-white px-3 py-2 rounded">{{ $data['type'] ?? 'N/A' }}
                                </div>
                                <div class="badge bg-warning text-dark px-3 py-2 rounded">
                                    {{ $data['intitule'] ?? 'N/A' }}</div>

                                {{-- <div class="badge bg-primary text-white px-3 py-2 rounded">
                                    Période d'exercice : {{ $exercice->date_debut ?? 'N/A' }} au
                                    {{ $exercice->date_fin ?? 'N/A' }}
                                </div> --}}

                            </div>



                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Total débit</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">
                                                        {{ rtrim(rtrim(number_format($totalDebit, 2, ',', ' '), '0'), ',') }}
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
                                                    <h4 class="mb-0 me-2">
                                                        {{ rtrim(rtrim(number_format($totalCredit, 2, ',', ' '), '0'), ',') }}
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


                            <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 d-none"
                                role="alert">
                                <span id="successMessage"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>



                            <!-- Section table -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Listing des ecritures du journal</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>


                                        @if ($exercice->cloturer == 0)
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalCenterCreate">
                                                Nouvelle écriture
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                Exercice clôturé
                                            </button>
                                        @endif
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

                                                <th>Compte Général</th>
                                                <th>Compte Tiers</th>
                                                <th>Plan Analytique</th>
                                                <th>Débit</th>
                                                <th>Crédit</th>
                                                <th>Poste de trésorerie</th>
                                                <th>Type de Flux</th>
                                                <th>Pièce justificatif</th>
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

                                                <tr class="clickable-row {{ $rowClass }} saisie-groupes"
                                                    data-n_saisie="{{ $ecriture->n_saisie }}"
                                                    data-id="{{ $data['id_journal'] }}"
                                                    data-annee="{{ $data['annee'] }}"
                                                    data-mois="{{ $data['mois'] }}"
                                                    data-exercices_comptables_id="{{ $data['id_exercice'] }}"
                                                    data-code_journals_id="{{ $data['id_code'] }}"
                                                    data-code_journal="{{ $data['code'] }}" {{-- data-compte_de_contrepartie="{{ $data['compte_de_contrepartie'] }}"
                                                    data-compte_de_tresorerie="{{ $data['compte_de_tresorerie'] }}"
                                                    data-rapprochement_sur="{{ $data['rapprochement_sur'] }}" --}}
                                                    data-intitule="{{ $data['intitule'] }}"
                                                    data-type="{{ $data['type'] }} ">


                                                    <td>{{ $ecriture->date }}</td>
                                                    <td>{{ $ecriture->n_saisie }}</td>
                                                    <td>{{ $ecriture->reference_piece }}</td>
                                                    <td>{{ $ecriture->description_operation }}</td>
                                                    <td>
                                                        {{ $ecriture->planComptable ? $ecriture->planComptable->numero_de_compte . ' - ' . $ecriture->planComptable->intitule : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ $ecriture->planTiers ? $ecriture->planTiers->numero_de_tiers . ' - ' . $ecriture->planTiers->intitule : '-' }}
                                                    </td>
                                                    <td>{{ $ecriture->plan_analytique == 1 ? 'Oui' : 'Non' }}</td>
                                                    <td>
                                                        {{ fmod($ecriture->debit, 1) == 0 ? number_format($ecriture->debit, 0, ',', ' ') : number_format($ecriture->debit, 2, ',', ' ') }}
                                                    </td>
                                                    <td>
                                                        {{ fmod($ecriture->credit, 1) == 0 ? number_format($ecriture->credit, 0, ',', ' ') : number_format($ecriture->credit, 2, ',', ' ') }}
                                                    </td>

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

                            <!-- Modal d’alerte déséquilibre débit/crédit -->
                            <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content text-center">
                                        <div class="modal-header">
                                            <h5 class="modal-title w-100" id="alertModalLabel">Erreur de validation
                                            </h5>
                                        </div>
                                        <div class="modal-body">
                                            Le total débit est différent du total crédit. Veuillez corriger votre
                                            saisie.
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-primary"
                                                data-bs-dismiss="modal">OK</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl-custom" role="document">
                                    <div class="modal-content position-relative">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalCenterTitle">Saisie d'une écriture
                                                comptable</h5>

                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>

                                        <div id="balance-warning"
                                            class="alert alert-warning alert-dismissible fade mt-5 text-center d-none"
                                            role="alert">
                                            Le total débit est différent du total crédit. Veuillez corriger votre
                                            saisie.
                                            {{-- <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button> --}}
                                        </div>
                                        <div id="ecritures-warning"
                                            class="alert alert-warning alert-dismissible fade mt-5 text-center d-none"
                                            role="alert">
                                            Aucune écriture à enregistrer.
                                            {{-- <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button> --}}
                                        </div>


                                        <div class="modal-body">
                                            <form id="formEcriture" novalidate enctype="multipart/form-data">
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label for="date" class="form-label">Date</label>
                                                        <input type="date" id="date" name="date"
                                                            class="form-control" required min="{{ $dateDebut }}"
                                                            max="{{ $dateFin }}" {{-- min="{{ $exercice->date_debut }}"
                                                            max="{{ $exercice->date_fin }}"  --}} />


                                                        <input type="hidden" id="date_debut_exercice"
                                                            name="date_debut_exercice"
                                                            value="{{ $exercice->date_debut }}">
                                                        <input type="hidden" id="date_fin_exercice"
                                                            name="date_fin_exercice"
                                                            value="{{ $exercice->date_fin }}">

                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="n_saisie" class="form-label">N° de saisie</label>
                                                        <input type="text" id="n_saisie" name="n_saisie"
                                                            class="form-control" value="{{ $nextSaisieNumber }}"
                                                            readonly required />
                                                        <div class="invalid-feedback">Veuillez renseigner le numéro de
                                                            saisie.</div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="imputation" class="form-label">Imputation
                                                            (Journal)</label>
                                                        <input type="text" class="form-control"
                                                            placeholder="{{ $data['code'] ?? 'N/A' }}" readonly />

                                                        <input type="hidden" id="imputation" name="code_journal_id"
                                                            value="{{ $data['id_code'] ?? 'N/A' }}"
                                                            class="form-control"
                                                            data-code_imputation="{{ $data['code'] }}" />


                                                        <!-- <div class="invalid-feedback">Veuillez sélectionner une imputation.</div> -->
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="description_operation"
                                                            class="form-label">Description de l'opération</label>
                                                        <input type="text" id="description_operation"
                                                            name="description_operation" class="form-control"
                                                            required />
                                                        <div class="invalid-feedback">Veuillez entrer la description.
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="reference_piece" class="form-label">Référence
                                                            pièce</label>
                                                        <input type="text" id="reference_piece"
                                                            name="reference_piece" class="form-control"
                                                            placeholder="FAC001, RECU045..." />
                                                    </div>

                                                    <input type="hidden" name="exercices_comptables_id"
                                                        id="exercices_comptables_id"
                                                        value="{{ $data['id_exercice'] ?? 'N/A' }}"
                                                        class="form-control" />

                                                    <input type="hidden" name="journaux_saisis_id"
                                                        id="journaux_saisis_id"
                                                        value="{{ $data['id_journal'] ?? 'N/A' }}"
                                                        class="form-control" />

                                                    <div class="row g-3">
                                                        <!-- Compte Général -->
                                                        <div class="col-md-3">
                                                            <label for="compte_general" class="form-label">Compte
                                                                Général</label>
                                                            <select id="compte_general" name="compte_general"
                                                                class="selectpicker w-100" data-live-search="true"
                                                                title="Selectionner" required>
                                                                {{-- <option value="" selected disabled>Sélectionner</option> --}}
                                                                @foreach ($plansComptables as $plan)
                                                                    <option value="{{ $plan->id }}"
                                                                        data-intitule_compte_general="{{ $plan->numero_de_compte }}">
                                                                        {{ $plan->numero_de_compte }} -
                                                                        {{ $plan->intitule }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <!-- Compte Tiers (à masquer au départ) -->
                                                        <div class="col-md-3" id="compte_tiers_wrapper"
                                                            style="display: none;">
                                                            <label for="compte_tiers" class="form-label">Compte
                                                                Tiers</label>
                                                            <select id="compte_tiers" name="plan_tiers_id"
                                                                class="selectpicker w-100" data-live-search="true"
                                                                title="Selectionner un tiers">
                                                                @foreach ($plansTiers as $plantiers)
                                                                    <option value="{{ $plantiers->id }}"
                                                                        data-intitule_tiers="{{ $plantiers->numero_de_tiers }}">
                                                                        {{ $plantiers->numero_de_tiers }} -
                                                                        {{ $plantiers->intitule }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <script>
                                                            document.addEventListener('DOMContentLoaded', function() {
                                                                const compteGeneral = document.getElementById('compte_general');
                                                                const compteTiersWrapper = document.getElementById('compte_tiers_wrapper');
                                                                const compteTiers = $('#compte_tiers'); // jQuery pour bootstrap-select

                                                                compteGeneral.addEventListener('change', function() {
                                                                    const selectedOption = compteGeneral.options[compteGeneral.selectedIndex];
                                                                    const numeroCompte = selectedOption.getAttribute('data-intitule_compte_general');

                                                                    if (numeroCompte && numeroCompte.startsWith('4')) {
                                                                        // Afficher le select et rafraîchir bootstrap-select
                                                                        compteTiersWrapper.style.display = 'block';
                                                                        // compteTiers.selectpicker('render').selectpicker('refresh');
                                                                        $('#compte_tiers').selectpicker('val', '');
                                                                    } else {
                                                                        // Masquer le select et réinitialiser proprement
                                                                        compteTiersWrapper.style.display = 'none';
                                                                        // compteTiers.selectpicker('val', '').selectpicker('refresh');
                                                                        $('#compte_tiers').selectpicker('val', '');
                                                                    }
                                                                });
                                                            });
                                                        </script>





                                                        <!-- Plan Analytique -->
                                                        <div class="col-md-3">
                                                            <label for="plan_analytique" class="form-label">Plan
                                                                Analytique</label>
                                                            <select id="plan_analytique" name="plan_analytique"
                                                                class="selectpicker w-100" data-live-search="false"
                                                                required>
                                                                {{-- <option value="0" selected disabled>Sélectionner</option> --}}
                                                                <option value="1">Oui</option>
                                                                <option value="0" selected>Non</option>
                                                                <!-- sélection par défaut -->
                                                            </select>
                                                            <div class="invalid-feedback">Veuillez sélectionner une
                                                                option.</div>
                                                        </div>



                                                                  <div class="col-md-3">
                                                                        <label for="compteTresorerieField" class="form-label">Poste de tresorerie</label>
                                                                        {{-- Changement : Utiliser un ID simple pour JS --}}
                                                                        <select id="compteTresorerieField"
                                                                                {{-- Enlever le name pour éviter les soumissions classiques. L'envoi se fera par JS --}}
                                                                                class="selectpicker w-100"
                                                                                data-live-search="true">
                                                                            <option value="">(Pas un flux spécifique)</option>

                                                                            @foreach($comptesTresorerie as $compteTresorerie)
                                                                                <option value="{{ $compteTresorerie->id }}" data-subtext="{{ $compteTresorerie->type }}">
                                                                                    {{ $compteTresorerie->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <div class="invalid-feedback">Veuillez sélectionner une option.</div>
                                                                    </div>

                                                      <div class="col-md-3">
                                                        <label for="typeFluxField" class="form-label">Type de Flux de tresorerie</label>
                                                        {{-- ID simplifié pour être lu par JS. Le 'name' est retiré. --}}
                                                        <select id="typeFluxField"
                                                                class="selectpicker w-100"
                                                                data-live-search="false"
                                                                required>
                                                            <option value="decaissement">Décaissement (Débit)</option>
                                                            <option value="encaissement">Encaissement (Crédit)</option>
                                                        </select>
                                                        <div class="invalid-feedback">Veuillez sélectionner une option.</div>
                                                    </div>




                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="debit" class="form-label">Débit</label>
                                                        <input type="number" id="debit" name="debit"
                                                            class="form-control" placeholder="0.00" step="0.01" />
                                                        <div class="invalid-feedback" id="debitError">
                                                            Saisissez un montant ou remplissez le crédit.
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label for="credit" class="form-label">Crédit</label>
                                                        <input type="number" id="credit" name="credit"
                                                            class="form-control" placeholder="0.00" step="0.01" />
                                                        <div class="invalid-feedback" id="creditError">
                                                            Saisissez un montant ou remplissez le débit.
                                                        </div>
                                                    </div>



                                                    <div class="col-md-12">
                                                        <label for="piece_justificatif" class="form-label">Pièce
                                                            justificative (fichier)</label>
                                                        <input type="file" id="piece_justificatif"
                                                            name="piece_justificatif" class="form-control"
                                                            accept=".pdf,.jpg,.jpeg,.png" />
                                                        <div class="invalid-feedback">Veuillez ajouter un fichier
                                                            justificatif.</div>
                                                    </div>
                                                </div>

                                            </form>
                                            <hr />
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6>Écritures saisies :</h6>
                                                <div>
                                                    <span class="me-3"><strong>Total Débit :</strong> <span
                                                            id="totalDebit">0.00</span></span>
                                                    <span><strong>Total Crédit :</strong> <span
                                                            id="totalCredit">0.00</span></span>
                                                </div>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm" id="tableEcritures">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>N° Saisie</th>
                                                            <th>Journal</th>
                                                            <th>Libellé</th>
                                                            <th>Réf Pièce</th>
                                                            <th>Cpte Général</th>
                                                            <th>Cpte Tiers</th>
                                                            <th>Débit</th>
                                                            <th>Crédit</th>
                                                            <th>Poste de trésorerie</th>
                                                            <th>Type de Flux</th>
                                                            <th>Piece justificatif</th>
                                                            <th>ANALYTIQUE</th>

                                                            <th>Modifier</th>
                                                            <th>Supprimer</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary"
                                                data-bs-dismiss="modal">Fermer</button>

                                            <button type="button" class="btn btn-secondary"
                                                onclick="ajouterEcriture()">Ajouter à la
                                                liste</button>

                                            <script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    const form = document.getElementById("formEcriture");

                                                    form.addEventListener("keydown", function(event) {
                                                        // Si c'est la touche Entrée ET que le focus n'est pas dans un textarea
                                                        if (event.key === "Enter" && event.target.tagName.toLowerCase() !== "textarea") {
                                                            event.preventDefault(); // Empêche la soumission classique
                                                            ajouterEcriture(); // Appelle ta fonction
                                                        }
                                                    });
                                                });
                                            </script>

                                            <button type="button" class="btn btn-primary" id="btnEnregistrer"
                                                onclick="enregistrerEcritures()">
                                                <span id="btnText">Enregistrer</span>
                                                <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"
                                                    role="status" aria-hidden="true"></span>
                                            </button>

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
                            </div>


                            {{-- @include('components.modal_saisie_direct') --}}

                            <!-- Modal update-->
                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalCenterTitle">
                                                Créer un plan
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="nameWithTitle" class="form-label">Nom</label>
                                                    <input type="text" id="nameWithTitle" class="form-control"
                                                        placeholder="Entrer le nom" />
                                                </div>
                                                <div class="col-6">
                                                    <label for="emailWithTitle" class="form-label">Email</label>
                                                    <input type="email" id="emailWithTitle" class="form-control"
                                                        placeholder="xxx@xxx.xx" />
                                                </div>
                                                <div class="col-6">
                                                    <label for="dobWithTitle" class="form-label">Date de
                                                        naissance</label>
                                                    <input type="date" id="dobWithTitle" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary"
                                                data-bs-dismiss="modal">
                                                Fermer
                                            </button>
                                            <button type="button" class="btn btn-primary">
                                                Enregistrer
                                            </button>
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
            $(document).ready(function() {
                $('.selectpicker').selectpicker();
            });
        </script>

        <script>
            const accounting_entry_real_goupesSaisisUrl = "{{ route('accounting_entry_real_goupes') }}";
            const accounting_entry_real_StoreSaisisUrl = "{{ route('storeMultiple.storeMultiple') }}";
        </script>

        <script src="{{ asset('js/acc_entry_real.js') }}"></script>
        <!-- Initialisation Select2 -->


</body>

</html>
