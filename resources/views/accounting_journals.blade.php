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
                            <!-- <div class="col-sm-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div class="content-left">
                        <span class="text-heading">Nombre de Journaux</span>
                        <div class="d-flex align-items-center my-1">
                          <h4 class="mb-0 me-2">{{ $totalJournauxCompany }}</h4>
                        </div>
                      </div>
                      <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                          <i class="icon-base bx bx-book icon-lg"></i>
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
                        <span class="text-heading">Journaux créés par vous</span>
                        <div class="d-flex align-items-center my-1">
                          <h4 class="mb-0 me-2">{{ $userCreatedJournaux }}</h4>
                        </div>
                      </div>
                      <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger">
                          <i class="icon-base bx bx-user icon-lg"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div> -->

                            <!-- Section table -->
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
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Journaux de saisie</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCreateCodeJournal">
                                            Ajouter un journal
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" id="filter-code" class="form-control"
                                                placeholder="Filtrer par code..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Filtrer par intitulé..." />
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-primary w-100" id="apply-filters">Appliquer les
                                                filtres</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-outline-secondary w-100"
                                                id="reset-filters">Réinitialiser</button>
                                        </div>
                                    </div>
                                </div>



                                <!-- Table -->
                                <script>
                                    $(document).ready(function() {
                                        $('#JournalTable').DataTable({
                                            pageLength: 10,
                                            lengthMenu: [10, 15, 20, 25],
                                            language: {
                                                search: "Rechercher :",
                                                lengthMenu: "Afficher _MENU_ lignes",
                                                info: "Affichage de _START_ à _END_ sur _TOTAL_ lignes",
                                                paginate: {
                                                    first: "Premier",
                                                    last: "Dernier",
                                                    next: "Suivant",
                                                    previous: "Précédent"
                                                },
                                                zeroRecords: "Aucune donnée trouvée",
                                                infoEmpty: "Aucune donnée à afficher",
                                                infoFiltered: "(filtré depuis _MAX_ lignes totales)"
                                            }
                                        });
                                    });
                                </script>


                                <div class="table-responsive text-nowrap" id="journalTable">
                                    <table class="table" id="JournalTable">
                                        <thead>
                                            <tr>

                                                <th>Code</th>
                                                <th>Type</th>
                                                <th>Intitulé</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($code_journaux as $journal)
                                                <tr>
                                                    <!-- <td>{{ $journal->annee }}</td>
                                                  <td>{{ \Carbon\Carbon::createFromDate(null, $journal->mois)->locale('fr')->monthName }}</td> -->

                                                    <td>{{ $journal->code_journal }}</td>
                                                    <td>{{ $journal->type }}</td>
                                                    <td>{{ $journal->intitule }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <!-- Bouton Edit -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $journal->id }}"
                                                                data-annee="{{ $journal->annee }}"
                                                                data-mois="{{ $journal->mois }}"
                                                                data-code="{{ $journal->code_journal }}"
                                                                data-type="{{ $journal->type }}"
                                                                data-intitule="{{ $journal->intitule }}"
                                                                data-traitement="{{ $journal->traitement_analytique }}"
                                                                data-compte_de_contrepartie="{{ $journal->compte_de_contrepartie }}"
                                                                data-compte_de_tresorerie="{{ $journal->compte_de_tresorerie }}"
                                                                data-rapprochement_sur="{{ $journal->rapprochement_sur }}">
                                                                <i class="bx bx-edit-alt fs-5"></i>
                                                            </button>


                                                            <!-- Bouton Delete -->
                                                            <!-- Bouton de suppression dans ta boucle -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmationModal"
                                                                data-id="{{ $journal->id }}"
                                                                data-name="{{ $journal->code_journal }}">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>

                                                            <!-- Nouveau bouton pour afficher l’écriture comptable -->
                                                            <!-- <button type="button"
                                                          class="btn p-0 border-0 bg-transparent text-success show-accounting-entry"
                                                          data-id="{{ $journal->id }}" data-annee="{{ $journal->annee }}"
                                                          data-mois="{{ $journal->mois }}" data-code="{{ $journal->code_journal }}"
                                                          data-type="{{ $journal->type }}" data-intitule="{{ $journal->intitule }}"
                                                          data-traitement="{{ $journal->traitement_analytique }}"
                                                          data-compte_de_contrepartie="{{ $journal->compte_de_contrepartie }}"
                                                          data-compte_de_tresorerie="{{ $journal->compte_de_tresorerie }}"
                                                          data-rapprochement_sur="{{ $journal->rapprochement_sur }}"
                                                          title="Afficher écriture comptable">
                                                          <i class="bx bx-show fs-5"></i>
                                                        </button> -->

                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- Modal Creation Journal-->
                            <div class="modal fade" id="modalCreateCodeJournal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form id="formCodeJournal" method="POST"
                                            action="{{ route('accounting_journals.store') }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Créer un Journal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">

                                                    <div class="col-md-6">
                                                        <label for="code_journal" class="form-label">Code Journal
                                                            *</label>
                                                        <input type="text" id="code_journal" name="code_journal"
                                                            class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="intitule" class="form-label">Intitulé *</label>
                                                        <input type="text" id="intitule" name="intitule"
                                                            class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="traitement_analytique"
                                                            class="form-label">Traitement
                                                            analytique *</label>
                                                        <select id="traitement_analytique"
                                                            name="traitement_analytique" class="form-select" required>
                                                            <option value="">-- Choisir --</option>
                                                            <option value="oui">Oui</option>
                                                            <option value="non">Non</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="type" class="form-label">Type *</label>
                                                        <select id="type" name="type" class="form-select"
                                                            required>
                                                            <option value="">-- Choisir --</option>
                                                            <option value="Achats">Achats</option>
                                                            <option value="Ventes">Ventes</option>
                                                            <option value="Tresorerie">Trésorerie</option>
                                                            <option value="General">Général</option>
                                                            <option value="Situation">Situation</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 d-none" id="group_compte_de_contrepartie">
                                                        <label for="compte_de_contrepartie" class="form-label">Compte
                                                            de contrepartie</label>
                                                        <input type="text" id="compte_de_contrepartie"
                                                            name="compte_de_contrepartie" class="form-control">
                                                    </div>

                                                    <div class="col-md-6 d-none" id="group_compte_de_tresorerie">
                                                        <label for="compte_de_tresorerie" class="form-label">Compte de
                                                            trésorerie</label>
                                                        <select id="compte_de_tresorerie" name="compte_de_tresorerie"
                                                            class="form-select">
                                                            <option value="">-- Compte de trésorerie --</option>
                                                            @foreach ($comptesTresorerie as $compte)
                                                                <option value="{{ $compte->id }}">
                                                                    {{ $compte->numero_de_compte }} -
                                                                    {{ $compte->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>





                                                    <div class="col-md-6 d-none" id="group_rapprochement_sur">
                                                        <label for="rapprochement_sur"
                                                            class="form-label">Rapprochement
                                                            sur</label>
                                                        <select id="rapprochement_sur" name="rapprochement_sur"
                                                            class="form-select">
                                                            <option value="">-- Choisir --</option>
                                                            <option value="Contrepartie">Auto</option>
                                                            <option value="tresorerie">Manuel</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Creation plan update-->
                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form id="formCodeJournalUpdate" method="POST"
                                            action="{{ route('accounting_journals.update', ['id' => '__ID__']) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier un Journal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <input type="hidden" name="journal_id" id="update_journal_id">

                                                    <div class="col-md-6">
                                                        <label for="update_code_journal" class="form-label">Code
                                                            Journal *</label>
                                                        <input type="text" id="update_code_journal"
                                                            name="code_journal" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="update_intitule" class="form-label">Intitulé
                                                            *</label>
                                                        <input type="text" id="update_intitule" name="intitule"
                                                            class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="update_traitement_analytique"
                                                            class="form-label">Traitement analytique *</label>
                                                        <select id="update_traitement_analytique"
                                                            name="traitement_analytique" class="form-select" required>
                                                            <option value="">-- Choisir --</option>
                                                            <option value="0">Non</option>
                                                            <option value="1">Oui</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="update_type" class="form-label">Type *</label>
                                                        <select id="update_type" name="type" class="form-select"
                                                            required>
                                                            <option value="">-- Choisir --</option>
                                                            <option value="Achats">Achats</option>
                                                            <option value="Ventes">Ventes</option>
                                                            <option value="Tresorerie">Trésorerie</option>
                                                            <option value="General">Général</option>
                                                            <option value="Situation">Situation</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 d-none"
                                                        id="update_group_compte_de_contrepartie">
                                                        <label for="update_compte_de_contrepartie"
                                                            class="form-label">Compte de contrepartie</label>
                                                        <input type="text" id="update_compte_de_contrepartie"
                                                            name="compte_de_contrepartie" class="form-control">
                                                    </div>




                                                    <div class="col-md-6 d-none"
                                                        id="update_group_compte_de_tresorerie">
                                                        <label for="update_compte_de_tresorerie"
                                                            class="form-label">Compte de trésorerie</label>
                                                        <select id="update_compte_de_tresorerie"
                                                            name="compte_de_tresorerie" class="form-select">
                                                            <option value="">-- Compte de trésorerie --</option>
                                                            @foreach ($comptesTresorerie as $compte)
                                                                <option value="{{ $compte->id }}">
                                                                    {{ $compte->numero_compte }} -
                                                                    {{ $compte->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>






                                                    <div class="col-md-6 d-none" id="update_group_rapprochement_sur">
                                                        <label for="update_rapprochement_sur"
                                                            class="form-label">Rapprochement sur</label>
                                                        <select id="update_rapprochement_sur" name="rapprochement_sur"
                                                            class="form-select">
                                                            <option value="">-- Choisir --</option>
                                                            <option value="Contrepartie">Contrepartie</option>
                                                            <option value="tresorerie">Trésorerie</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                            </div>
                                        </form>
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
                                                <i class="bx bx-error-circle me-2"></i> Confirmer la suppression
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <p class="mb-0">
                                                Êtes-vous sûr de vouloir supprimer ce journal ? Cette action est
                                                <strong>irréversible</strong>.
                                            </p>
                                            <p class="fw-bold text-danger mt-2" id="journalToDeleteName"></p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <!-- Formulaire Laravel -->
                                            <form id="deleteJournalForm" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </form>
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

        @include('components.footer')

        <script>
            const accounting_journalsUpdateBaseUrl = "{{ route('accounting_journals.update', ['id' => '__ID__']) }}";
            const accounting_journalsDeleteUrl = "{{ route('accounting_journals.destroy', ['id' => '__ID__']) }}";
        </script>
        <script src="{{ asset('js/acc_journals.js') }}"></script>
</body>

</html>
