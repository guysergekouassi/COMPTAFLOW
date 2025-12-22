<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">

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
          @include('components.header', ['page_title' => 'NOUVELLE <span class="text-gradient">ÉCRITURE</span>'])
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <div class="col-12">
              </div>
            </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Nouvelle écriture</h4>
                    </div>
                    <div class="card-body">
                        <form id="formEcriture">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" id="date" name="date" class="form-control" required />
                                    <div class="invalid-feedback">Veuillez renseigner la date.</div>
                                </div>
                                <div class="col-md-2">
                                    <label for="n_saisie" class="form-label">N° Saisie</label>
                                    <input type="text" id="n_saisie" name="n_saisie" class="form-control" />
                                    <div class="invalid-feedback">Veuillez renseigner le numéro de saisie.</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="imputation" class="form-label">Imputation (Journal)</label>
                                    <input type="text" class="form-control" placeholder="{{ $data['code'] ?? 'N/A' }}" readonly />
                                    <input type="hidden" id="imputation" name="code_journal_id" value="{{ $data['id_code'] ?? 'N/A' }}" class="form-control" data-code_imputation="{{ $data['code'] ?? 'N/A' }}" />
                                </div>
                                <div class="col-md-6">
                                    <label for="description_operation" class="form-label">Description de l'opération</label>
                                    <input type="text" id="description_operation" name="description_operation" class="form-control" required />
                                    <div class="invalid-feedback">Veuillez entrer la description.</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="reference_piece" class="form-label">Référence Pièce</label>
                                    <input type="text" id="reference_piece" name="reference_piece" class="form-control" />
                                </div>
                                <div class="col-md-3">
                                    <label for="compte_general" class="form-label">Compte Général</label>
                                    <select id="compte_general" name="compte_general"
                                        class="form-control w-100" data-live-search="true"
                                        title="Selectionner" required>
                                        <option value="" selected disabled>Sélectionner un compte</option>
                                        @if(isset($plansComptables))
                                            @foreach ($plansComptables as $plan)
                                                <option value="{{ $plan->id }}"
                                                    data-intitule_compte_general="{{ $plan->numero_de_compte }}">
                                                    {{ $plan->numero_de_compte }} -
                                                    {{ $plan->intitule }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="compte_tiers" class="form-label">Compte Tiers</label>
                                    <select id="compte_tiers" name="compte_tiers" class="form-control w-100" data-live-search="true">
                                        <option value="">Sélectionner un compte tiers</option>
                                        @if(isset($tiers))
                                            @foreach ($tiers as $tier)
                                                <option value="{{ $tier->id }}">{{ $tier->intitule }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="debit" class="form-label">Débit</label>
                                    <input type="number" id="debit" name="debit" class="form-control" step="0.01" min="0" />
                                </div>
                                <div class="col-md-2">
                                    <label for="credit" class="form-label">Crédit</label>
                                    <input type="number" id="credit" name="credit" class="form-control" step="0.01" min="0" />
                                </div>
                                <div class="col-md-3">
                                    <label for="compteTresorerieField" class="form-label">Poste de trésorerie</label>
                                    <select id="compteTresorerieField" name="compte_tresorerie" class="form-control w-100" data-live-search="true">
                                        <option value="">Sélectionner un poste</option>
                                        @if(isset($postesTresorerie))
                                            @foreach ($postesTresorerie as $poste)
                                                <option value="{{ $poste->id }}">{{ $poste->intitule }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="typeFluxField" class="form-label">Type de Flux de tresorerie</label>
                                    <select id="typeFluxField" name="type_flux" class="form-control w-100" required>
                                        <option value="decaissement">Décaissement (Débit)</option>
                                        <option value="encaissement">Encaissement (Crédit)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="plan_analytique" class="form-label">Plan Analytique</label>
                                    <select id="plan_analytique" name="plan_analytique"
                                        class="form-control w-100" required>
                                        <option value="1">Oui</option>
                                        <option value="0" selected>Non</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label for="piece_justificatif" class="form-label">Pièce justificative (fichier)</label>
                                    <input type="file" id="piece_justificatif" name="piece_justificatif"
                                        class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                                    <div class="invalid-feedback">Veuillez ajouter un fichier justificatif.</div>
                                </div>
                            </div>
<<<<<<< HEAD

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
                                                            max="{{ $dateFin }}" value="{{ now()->format('Y-m-d') }}" />


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

                                                    <div class="col-md-6">
                                                        <label for="plan_tiers_select" class="form-label">Plan Tiers</label>
                                                        <select id="plan_tiers_select" class="form-control">
                                                            <option value="">Sélectionner un tiers</option>
                                                            @foreach ($plansTiers as $plantiers)
                                                                <option value="{{ $plantiers->id }}" data-intitule="{{ $plantiers->intitule }}" data-numero="{{ $plantiers->numero_de_tiers }}" data-compte-general="{{ $plantiers->compte->id ?? '' }}">
                                                                    {{ $plantiers->numero_de_tiers }} - {{ $plantiers->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="form-check mt-2">
                                                            <input class="form-check-input" type="checkbox" id="auto_add_entry">
                                                            <label class="form-check-label" for="auto_add_entry">
                                                                Auto-ajouter à la liste
                                                            </label>
                                                        </div>
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
=======
                        </form>
                        <hr />
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Écritures saisies :</h6>
                            <div class="d-flex align-items-center">
                                <span class="me-3">Total Débit : <span id="totalDebit">0.00</span></span>
                                <span>Total Crédit : <span id="totalCredit">0.00</span></span>
>>>>>>> origin/main
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
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-success" id="btnEnregistrer"
                            onclick="enregistrerEcritures()">
                            <span id="btnText">Enregistrer</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none"
                                role="status" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="btn btn-primary"
                            onclick="ajouterEcriture()">Ajouter à la
                            liste</button>
                    </div>
                </div>
            </div>
          </div>
          <!-- / Content wrapper -->

        </div>
        <!-- / Layout container -->

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
      </div>
      <!-- / Layout wrapper -->

<<<<<<< HEAD
        <!-- Initialisation JS -->

        @include('components.footer')

        <script>
            $(document).ready(function() {
                $('.selectpicker').selectpicker();

                const planTiersSelect = document.getElementById('plan_tiers_select');
                const descriptionOperation = document.getElementById('description_operation');
                const compteTiers = document.getElementById('compte_tiers');
                const autoAddEntry = document.getElementById('auto_add_entry');

                planTiersSelect.addEventListener('change', function() {
                    const selectedOption = planTiersSelect.options[planTiersSelect.selectedIndex];
                    const intitule = selectedOption.getAttribute('data-intitule');
                    const numero = selectedOption.getAttribute('data-numero');
                    const compteGeneral = selectedOption.getAttribute('data-compte-general');

                    if (intitule) {
                        descriptionOperation.value = intitule;
                    }

                    if (compteGeneral) {
                        $('#compte_general').selectpicker('val', compteGeneral);
                    }

                    if (numero && compteTiers) {
                        // Set the compte_tiers select to the selected value
                        $('#compte_tiers').selectpicker('val', selectedOption.value);
                        // Show the compte_tiers_wrapper if hidden
                        document.getElementById('compte_tiers_wrapper').style.display = 'block';
                    }

                    // If auto-add is checked, add to list
                    if (autoAddEntry.checked) {
                        // Delay to allow fields to fill
                        setTimeout(() => {
                            ajouterEcriture();
                        }, 100);
                    }
                });
            });
        </script>

        <script>
            const accounting_entry_real_goupesSaisisUrl = "{{ route('accounting_entry_real_goupes') }}";
            const accounting_entry_real_StoreSaisisUrl = "{{ route('storeMultiple.storeMultiple') }}";
        </script>

        <script src="{{ asset('js/acc_entry_real.js') }}"></script>
        <!-- Initialisation Select2 -->
=======
      <!-- Core JS -->
      @include('components.footer')
>>>>>>> origin/main


    </body>

    </html>

<script>
    // Fonction globale pour ajouter une écriture
    function ajouterEcriture() {
        try {
            const date = document.getElementById('date');
            const nSaisie = document.getElementById('n_saisie');
            const libelle = document.getElementById('description_operation');
            const debit = document.getElementById('debit');
            const credit = document.getElementById('credit');
            const posteTresorerie = document.getElementById('compteTresorerieField');
            const typeFlux = document.getElementById('typeFluxField');
            const compteGeneral = document.getElementById('compte_general');
            const referencePiece = document.getElementById('reference_piece');
            const compteTiers = document.getElementById('compte_tiers');
            const pieceFile = document.getElementById('piece_justificatif');
            const imputationInput = document.querySelector('input[readonly][placeholder*="N/A"]');
            const planAnalytique = document.getElementById('plan_analytique');

            if (!date || !libelle || !compteGeneral) {
                alert('Champs du formulaire introuvables.');
                return;
            }

            if (!date.value || !libelle.value || !compteGeneral.value || compteGeneral.value === '') {
                alert('Veuillez remplir tous les champs obligatoires (Date, Description, Compte Général).');
                return;
            }

            if (!debit.value && !credit.value) {
                alert('Veuillez saisir un montant au débit ou au crédit.');
                return;
            }

            const tbody = document.querySelector('#tableEcritures tbody');
            if (!tbody) {
                alert('Tableau des écritures introuvable.');
                return;
            }

            const newRow = tbody.insertRow();

            const imputationValue = imputationInput ? imputationInput.value : '';
            const analytiqueValue = planAnalytique ? (planAnalytique.value === '1' ? 'Oui' : 'Non') : '';
            const compteText = compteGeneral.options[compteGeneral.selectedIndex].text;
            const compteTiersValue = compteTiers && compteTiers.value ? compteTiers.options[compteTiers.selectedIndex].text : '';
            const posteText = posteTresorerie ? posteTresorerie.options[posteTresorerie.selectedIndex].text : '';
            const fluxText = typeFlux ? typeFlux.options[typeFlux.selectedIndex].text : '';
            const pieceFileName = pieceFile && pieceFile.files[0] ? pieceFile.files[0].name : '';

            newRow.innerHTML = `
                <td>${date.value}</td>
                <td>${nSaisie ? nSaisie.value : ''}</td>
                <td>${imputationValue}</td>
                <td>${libelle.value}</td>
                <td>${referencePiece ? referencePiece.value || '' : ''}</td>
                <td>${compteText}</td>
                <td>${compteTiersValue}</td>
                <td>${debit.value || ''}</td>
                <td>${credit.value || ''}</td>
                <td>${posteText}</td>
                <td>${fluxText}</td>
                <td>${pieceFileName}</td>
                <td>${analytiqueValue}</td>
            `;

            const modifierCell = document.createElement('td');
            modifierCell.innerHTML = `
                <button class="btn btn-sm btn-warning" onclick="modifierEcriture(this.closest('tr'));">
                    <i class="bx bx-edit"></i>
                </button>
            `;
            newRow.appendChild(modifierCell);

            const supprimerCell = document.createElement('td');
            supprimerCell.innerHTML = `
                <button class="btn btn-sm btn-danger" onclick="supprimerEcriture(this.closest('tr'));">
                    <i class="bx bx-trash"></i>
                </button>
            `;
            newRow.appendChild(supprimerCell);

            // Réinitialisation du formulaire
            libelle.value = '';
            debit.value = '';
            credit.value = '';
            if (referencePiece) referencePiece.value = '';
            if (pieceFile) pieceFile.value = '';

            // Mise à jour des totaux
            updateTotals();

            alert('Écriture ajoutée avec succès !');

        } catch (error) {
            console.error('Erreur lors de l\'ajout de l\'écriture:', error);
            alert('Une erreur est survenue: ' + error.message);
        }
    }

    // Fonction pour mettre à jour les totaux
    function updateTotals() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) return;

        let totalDebit = 0;
        let totalCredit = 0;

        const rows = tbody.getElementsByTagName('tr');
        for (let row of rows) {
            const debitCell = row.cells[7]; // Colonne Débit
            const creditCell = row.cells[8]; // Colonne Crédit

            if (debitCell && debitCell.textContent) {
                totalDebit += parseFloat(debitCell.textContent.replace(/\s/g, '').replace(',', '.') || 0);
            }
            if (creditCell && creditCell.textContent) {
                totalCredit += parseFloat(creditCell.textContent.replace(/\s/g, '').replace(',', '.') || 0);
            }
        }

        const totalDebitElement = document.getElementById('totalDebit');
        const totalCreditElement = document.getElementById('totalCredit');

        if (totalDebitElement) {
            totalDebitElement.textContent = totalDebit.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }
        if (totalCreditElement) {
            totalCreditElement.textContent = totalCredit.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }
    }

    // Fonction pour enregistrer les écritures
    function enregistrerEcritures() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (tbody) {
            const rows = tbody.getElementsByTagName('tr');

            if (rows.length === 0) {
                alert('Aucune écriture à enregistrer.');
                return;
            }

            alert('Écritures enregistrées avec succès !');

            setTimeout(() => {
                tbody.innerHTML = '';
                updateTotals();
            }, 2000);
        }
    }

    // Fonction pour modifier une écriture
    function modifierEcriture(row) {
        alert('Fonction de modification à implémenter');
    }

    // Fonction pour supprimer une écriture
    function supprimerEcriture(row) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette écriture ?')) {
            row.remove();
            updateTotals();
            alert('Écriture supprimée avec succès !');
        }
    }

    // Gestion du type de flux pour activer/désactiver les champs
    document.addEventListener('DOMContentLoaded', function() {
        const typeFlux = document.getElementById('typeFluxField');
        const debit = document.getElementById('debit');
        const credit = document.getElementById('credit');

        if (typeFlux && debit && credit) {
            function updateFields() {
                if (typeFlux.value === 'decaissement') {
                    credit.disabled = false;
                    debit.disabled = false;
                    credit.style.backgroundColor = '';
                    credit.style.cursor = 'text';
                    debit.style.backgroundColor = '#f8f9fa';
                    debit.style.boxShadow = '0 0 8px rgba(108, 117, 125, 0.4), inset 0 0 4px rgba(108, 117, 125, 0.2)';
                    debit.style.border = '1px solid #ced4da';
                    debit.style.cursor = 'not-allowed';
                } else if (typeFlux.value === 'encaissement') {
                    debit.disabled = false;
                    credit.disabled = false;
                    debit.style.backgroundColor = '';
                    debit.style.cursor = 'text';
                    credit.style.backgroundColor = '#f8f9fa';
                    credit.style.boxShadow = '0 0 8px rgba(108, 117, 125, 0.4), inset 0 0 4px rgba(108, 117, 125, 0.2)';
                    credit.style.border = '1px solid #ced4da';
                    credit.style.cursor = 'not-allowed';
                } else {
                    debit.disabled = false;
                    credit.disabled = false;
                    debit.style.backgroundColor = '';
                    credit.style.backgroundColor = '';
                    debit.style.cursor = 'text';
                    credit.style.cursor = 'text';
                }
            }

            typeFlux.addEventListener('change', updateFields);
            updateFields();
        }
    });
</script>
