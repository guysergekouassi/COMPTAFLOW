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
                        <!-- Page Header -->
                        <div class="text-center mb-5">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; border-radius: 20px; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);">
                                <i class="bx bx-book text-white" style="font-size: 32px;"></i>
                            </div>
                            <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Journaux de Saisie</h1>
                            <p class="text-muted mb-0" style="font-size: 1.1rem;"><i class="bx bx-info-circle me-1"></i>Gérez vos journaux comptables (Achats, Ventes, Trésorerie, etc.)</p>
                        </div>

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
                            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-bottom: 2px solid #e7e9ed; padding: 1.5rem;">
                                    <h5 class="mb-0" style="font-weight: 700; color: #566a7f; font-size: 1.25rem;"><i class="bx bx-list-ul me-2"></i>Journaux de saisie</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel" style="border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCreateCodeJournal" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(105, 108, 255, 0.3); transition: all 0.3s;">
                                            <i class="bx bx-plus-circle me-1"></i> Ajouter un journal
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2 pb-3" id="filterPanel" style="background: #f8f9fa; border-radius: 8px; margin: 0 1rem 1rem 1rem;">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-code-alt"></i> Code</label>
                                            <input type="text" id="filter-code" class="form-control"
                                                placeholder="Filtrer par code..." style="border-radius: 8px;" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-text"></i> Intitulé</label>
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Filtrer par intitulé..." style="border-radius: 8px;" />
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-muted small mb-1">&nbsp;</label>
                                            <button class="btn btn-primary w-100" id="apply-filters" style="border-radius: 8px; font-weight: 600;"><i class="bx bx-search-alt me-1"></i>Appliquer</button>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-muted small mb-1">&nbsp;</label>
                                            <button class="btn btn-outline-secondary w-100"
                                                id="reset-filters" style="border-radius: 8px; font-weight: 600;"><i class="bx bx-reset me-1"></i>Réinitialiser</button>
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


                                <div class="table-responsive text-nowrap" id="journalTable" style="padding: 1.5rem;">
                                    <table class="table table-hover align-middle" id="JournalTable" style="border-radius: 8px; overflow: hidden;">
                                        <thead style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                            <tr>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-code-alt me-1"></i>Code</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-category me-1"></i>Type</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-text me-1"></i>Intitulé</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; text-align: center;"><i class="bx bx-slider me-1"></i>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($codeJournaux as $journal)
                                                <tr>
                                                    <!-- <td>{{ $journal->annee }}</td>
                                                  <td>{{ \Carbon\Carbon::createFromDate(null, $journal->mois)->locale('fr')->monthName }}</td> -->

                                                    <td style="padding: 1rem; font-weight: 600; color: #667eea;">{{ $journal->code_journal }}</td>
                                                    <td style="padding: 1rem;">
                                                        <span class="badge" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem; color: white;">{{ $journal->type }}</span>
                                                    </td>
                                                    <td style="padding: 1rem; color: #566a7f;">{{ $journal->intitule }}</td>
                                                    <td style="padding: 1rem;">
                                                        <div class="d-flex gap-3 justify-content-center">
                                                            <!-- Bouton Edit -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)'"
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
                                                                data-rapprochement_sur="{{ $journal->rapprochement_sur }}"
                                                                title="Modifier">
                                                                <i class="bx bx-edit-alt" style="font-size: 18px;"></i>
                                                            </button>


                                                            <!-- Bouton Voir -->
                                                            <a href="{{ route('accounting_entry_real', [
                                                                'annee' => $journal->annee,
                                                                'mois' => $journal->mois,
                                                                'id_journal' => $journal->id,
                                                                'code' => $journal->code_journal,
                                                                'type' => $journal->type,
                                                                'intitule' => $journal->intitule
                                                            ]) }}"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(79, 172, 254, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(79, 172, 254, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(79, 172, 254, 0.3)'"
                                                                title="Voir les écritures">
                                                                <i class="bx bx-show" style="font-size: 18px;"></i>
                                                            </a>


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
                                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                                                <h5 class="modal-title text-white" style="font-weight: 700;"><i class="bx bx-plus-circle me-2"></i>Créer un Journal</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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
                                            <div class="modal-footer" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                                <button type="button" class="btn btn-label-secondary" style="border-radius: 8px;"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(105, 108, 255, 0.3);">Enregistrer</button>
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
                                            <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-bottom: none;">
                                                <h5 class="modal-title text-white" style="font-weight: 700;"><i class="bx bx-edit-alt me-2"></i>Modifier un Journal</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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
                                            <div class="modal-footer" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                                <button type="button" class="btn btn-label-secondary" style="border-radius: 8px;"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(245, 87, 108, 0.3);">Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>


                            {{-- Modal Confirmation de suppression - Désactivé car les utilisateurs n'ont pas le droit de supprimer --}}
                            {{-- <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
                                aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header text-white justify-content-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-bottom: none;">
                                            <h5 class="modal-title" id="deleteModalLabel" style="font-weight: 700;">
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
                                        <div class="modal-footer justify-content-center" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">
                                                Annuler
                                            </button>
                                            <form id="deleteJournalForm" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(234, 84, 85, 0.3);">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

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
