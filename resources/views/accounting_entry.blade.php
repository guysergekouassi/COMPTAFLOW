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
              <div class="col-sm-6 col-xl-3">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                      <div class="content-left">
                        <span class="text-heading">Nombre d'écritures</span>
                        <div class="d-flex align-items-center my-1">
                          <h4 class="mb-0 me-2">21,459</h4>

                        </div>
                        <!-- <small class="mb-0">Total Users</small> -->
                      </div>
                      <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                          <i class="icon-base bx bx-group icon-lg"></i>
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
                        <span class="text-heading">écriture passés</span>
                        <div class="d-flex align-items-center my-1">
                          <h4 class="mb-0 me-2">4,567</h4>

                        </div>
                        <!-- <small class="mb-0">Last week analytics </small> -->
                      </div>
                      <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger">
                          <i class="icon-base bx bx-user-plus icon-lg"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>



              <!-- Section table -->
              <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Projects</h5>
                  <div>
                    <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                      data-bs-target="#filterPanel">
                      <i class="bx bx-filter-alt me-1"></i> Filtrer
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                      data-bs-target="#modalCenterCreate">
                      Nouvelle écriture
                    </button>
                  </div>
                </div>

                <!-- Filtre personnalisé -->
                <div class="collapse px-3 pt-2" id="filterPanel">
                  <div class="row g-2">
                    <div class="col-md-4">
                      <input type="text" id="filter-client" class="form-control" placeholder="Filtrer par client..." />
                    </div>
                    <div class="col-md-4">
                      <select id="filter-status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <button class="btn btn-primary w-100" id="apply-filters">
                        Appliquer les filtres
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Table -->
                <div class="table-responsive text-nowrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <i class="icon-base bx bxl-angular icon-md text-danger me-2"></i>
                          Angular Project
                        </td>
                        <td>Albert Cook</td>
                        <td>
                          <ul class="list-unstyled m-0 avatar-group d-flex align-items-center">
                            <li class="avatar avatar-xs pull-up" title="Lilian Fuller">
                              <img src="../assets/img/avatars/2.png" class="rounded-circle" />
                            </li>
                            <li class="avatar avatar-xs pull-up" title="Sophia Wilkerson">
                              <img src="../assets/img/avatars/3.png" class="rounded-circle" />
                            </li>
                            <li class="avatar avatar-xs pull-up" title="Christina Parker">
                              <img src="../assets/img/avatars/4.png" class="rounded-circle" />
                            </li>
                          </ul>
                        </td>
                        <td>
                          <span class="badge bg-label-primary">Active</span>
                        </td>
                        <td>
                          <div class="d-flex gap-2">
                            <!-- Bouton Edit avec modal -->
                            <button type="button" class="btn p-0 border-0 bg-transparent text-primary"
                              data-bs-placement="top" title="Edit" data-bs-toggle="modal"
                              data-bs-target="#modalCenterUpdate">
                              <i class="bx bx-edit-alt fs-5"></i>
                            </button>

                            <!-- Bouton Delete -->
                            <button type="button" class="btn p-0 border-0 bg-transparent text-danger"
                              data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"
                              data-project="Angular Project">
                              <i class="bx bx-trash fs-5"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                      <!-- Autres lignes ici -->
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Modal Creation Ecriture-->
              <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalCenterTitle">
                        Saisie d'une écriture comptable
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-4">
                          <label for="dateEcriture" class="form-label">Date</label>
                          <input type="date" id="dateEcriture" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                          <label for="journal" class="form-label">Journal</label>
                          <select id="journal" class="form-select" required>
                            <option value="">Sélectionner</option>
                            <option value="ACH">Achats</option>
                            <option value="VEN">Ventes</option>
                            <option value="BAN">Banque</option>
                            <option value="OD">Opérations diverses</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label for="referencePiece" class="form-label">Pièce justificative</label>
                          <input type="text" id="referencePiece" class="form-control"
                            placeholder="FAC001, RECU045..." />
                        </div>
                        <div class="col-12">
                          <label for="libelleEcriture" class="form-label">Libellé</label>
                          <input type="text" id="libelleEcriture" class="form-control"
                            placeholder="Ex : Règlement facture client X" />
                        </div>

                        <div class="col-12" id="tresorerieFields" >
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="posteTresorerie" class="form-label">Poste de Trésorerie</label>
                                    <select id="posteTresorerie" class="form-select">
                                        <option value=""> (Pas un flux spécifique)</option>
                                        </select>


                                </div>

                                <div class="col-md-6">
                                    <label for="typeFlux" class="form-label">Type de Flux</label>
                                    <select id="typeFlux" class="form-select">
                                        <option value="">Sélectionner le type de flux</option>
                                        <option value="debit">Décaissement (Débit)</option>
                                        <option value="credit">Encaissement (Crédit)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                             <div class="col-12">
                          <label class="form-label">Lignes d’écriture</label>
                          <div id="lignesEcriture">
                            <div class="row g-2 mb-2 ligne-ecriture">
                              <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="N° de compte (ex: 401)" required />
                              </div>
                              <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Intitulé compte" />
                              </div>
                              <div class="col-md-3">
                                <input type="number" class="form-control" placeholder="Débit" step="0.01" />
                              </div>
                              <div class="col-md-3">
                                <input type="number" class="form-control" placeholder="Crédit" step="0.01" />
                              </div>
                            </div>
                          </div>
                          <button type="button" class="btn btn-sm btn-secondary mt-1" onclick="ajouterLigne()">+ Ajouter
                            une ligne</button>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        Fermer
                      </button>
                      <button type="button" class="btn btn-primary" onclick="validerEcriture()">
                        Enregistrer
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Modal Creation plan update-->
              <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalCenterTitle">
                        Créer un plan
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-12">
                          <label for="nameWithTitle" class="form-label">Nom</label>
                          <input type="text" id="nameWithTitle" class="form-control" placeholder="Entrer le nom" />
                        </div>
                        <div class="col-6">
                          <label for="emailWithTitle" class="form-label">Email</label>
                          <input type="email" id="emailWithTitle" class="form-control" placeholder="xxx@xxx.xx" />
                        </div>
                        <div class="col-6">
                          <label for="dobWithTitle" class="form-label">Date de naissance</label>
                          <input type="date" id="dobWithTitle" class="form-control" />
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
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
              <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content border-0 shadow">
                    <div class="modal-header text-white justify-content-center">
                      <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bx bx-error-circle me-2"></i>Confirmer la
                        suppression
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
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

    @include('components.footer')

    <script>
      function ajouterLigne() {
        const lignesEcriture = document.getElementById("lignesEcriture");
        const ligne = document.createElement("div");
        ligne.className = "row g-2 mb-2 ligne-ecriture";
        ligne.innerHTML = `
    <div class="col-md-3">
      <input type="text" class="form-control" placeholder="N° de compte" required />
    </div>
    <div class="col-md-3">
      <input type="text" class="form-control" placeholder="Intitulé compte" />
    </div>
    <div class="col-md-3">
      <input type="number" class="form-control" placeholder="Débit" step="0.01" />
    </div>
    <div class="col-md-3">
      <input type="number" class="form-control" placeholder="Crédit" step="0.01" />
    </div>
  `;
        lignesEcriture.appendChild(ligne);
      }

      function validerEcriture() {
        // Exemple basique de validation
        const lignes = document.querySelectorAll(".ligne-ecriture");
        let totalDebit = 0;
        let totalCredit = 0;

        lignes.forEach(l => {
          const debit = parseFloat(l.children[2].children[0].value) || 0;
          const credit = parseFloat(l.children[3].children[0].value) || 0;
          totalDebit += debit;
          totalCredit += credit;
        });

        if (totalDebit !== totalCredit) {
          alert("Le total des débits doit être égal au total des crédits !");
          return;
        }

        alert("Écriture comptable valide et prête à être enregistrée !");
        // Ici, tu pourrais envoyer les données via AJAX ou soumettre un formulaire
      }
    </script>
<script>
    // ... (vos fonctions ajouterLigne() et validerEcriture() existantes) ...

    document.addEventListener('DOMContentLoaded', function() {
        const journalSelect = document.getElementById('journal');
        const tresorerieFields = document.getElementById('tresorerieFields');

        // Définir les codes des journaux qui impliquent des flux de trésorerie
        // 'BAN' pour Banque, 'CAI' pour Caisse (à ajuster si vos codes sont différents)
        const tresorerieJournals = ['BAN', 'CAI'];

        // Fonction pour gérer l'affichage/masquage
        function toggleTresorerieFields() {
            if (journalSelect && tresorerieFields) {
                // Vérifie si la valeur sélectionnée correspond à un journal de trésorerie
                if (tresorerieJournals.includes(journalSelect.value)) {
                    tresorerieFields.style.display = 'block';
                } else {
                    tresorerieFields.style.display = 'none';
                    // Optionnel : Réinitialiser les champs quand ils sont masqués
                    document.getElementById('posteTresorerie').value = '';
                    document.getElementById('typeFlux').value = '';
                }
            }
        }

        // 1. Écouter le changement de sélection dans le champ Journal
        if (journalSelect) {
            journalSelect.addEventListener('change', toggleTresorerieFields);
        }

        // 2. Exécuter au chargement pour le cas où le champ aurait une valeur par défaut
        toggleTresorerieFields();
    });

    // ... (fin de votre balise script) ...
</script>
</body>

</html>
