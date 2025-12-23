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
                <div class="modal-dialog modal-dialog-centered modal-fullscreen" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalCenterTitle">
                        Saisie d'une écriture comptable
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-3">
                          <label for="dateEcriture" class="form-label">Date</label>
                          <input type="date" id="dateEcriture" class="form-control" required />
                        </div>
                        <div class="col-md-3">
                          <label for="journal" class="form-label">Journal</label>
                          <select id="journal" class="form-select" required>
                            <option value="">Sélectionner</option>
                            <option value="ACH">Achats</option>
                            <option value="VEN">Ventes</option>
                            <option value="BAN">Banque</option>
                            <option value="OD">Opérations diverses</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label for="referencePiece" class="form-label">Pièce justificative</label>
                          <input type="text" id="referencePiece" class="form-control"
                            placeholder="FAC001, RECU045..." />
                        </div>
                        <div class="col-md-3">
                          <label for="pieceFile" class="form-label">Fichier pièce</label>
                          <input type="file" id="pieceFile" class="form-control" accept="image/*,.pdf" />
                        </div>
                        <div class="col-12">
                          <label for="libelleEcriture" class="form-label">Libellé</label>
                          <input type="text" id="libelleEcriture" class="form-control"
                            placeholder="Ex : Règlement facture client X" />
                        </div>

                       

                                <div class="col-md-4">
                                    <label for="compteGeneral" class="form-label">Compte général</label>
                                    <select id="compteGeneral" class="form-select">
                                        <option value="">Sélectionner un compte</option>
                                        </select>
                                </div>
                            </div>
                        </div>
                             <div class="col-12">
                          <label class="form-label">Lignes d'écriture</label>
                          <div class="table-responsive">
                            <table class="table table-bordered" id="lignesEcritureTable">
                              <thead class="table-light">
                                <tr>
                                  <th style="width: 15%">N° Compte</th>
                                  <th style="width: 25%">Intitulé</th>
                                  <th style="width: 15%">Débit</th>
                                  <th style="width: 15%">Crédit</th>
                                  <th style="width: 10%">Type Flux</th>
                                  <th style="width: 10%">Pièce</th>
                                  <th style="width: 10%">Actions</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr class="ligne-ecriture">
                                  <td><input type="text" class="form-control compte-input" placeholder="ex: 401" required /></td>
                                  <td><input type="text" class="form-control intitule-input" placeholder="Intitulé compte" readonly /></td>
                                  <td><input type="number" class="form-control debit-input" placeholder="0.00" step="0.01" /></td>
                                  <td><input type="number" class="form-control credit-input" placeholder="0.00" step="0.01" /></td>
                                  <td><span class="type-flux-display">-</span></td>
                                  <td><span class="piece-display">-</span></td>
                                  <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="supprimerLigne(this)">
                                      <i class="bx bx-trash"></i>
                                    </button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="ajouterLigne()">
                            <i class="bx bx-plus me-1"></i>Ajouter une ligne
                          </button>
                          <button type="button" class="btn btn-sm btn-info mt-2" onclick="alert('Test JavaScript fonctionne');">
                           Test JS
                          </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        Fermer
                      </button>
                      <button type="button" class="btn btn-primary" onclick="validerEcriture()">
                        <i class="bx bx-save me-1"></i>Enregistrer
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
      // Variables globales
      let comptesGeneraux = [];
      let postesTresorerie = [];
      let fichierSelectionne = null;

      // Initialisation au chargement de la page
      document.addEventListener('DOMContentLoaded', function() {
        chargerPostesTresorerie();
        chargerComptesGeneraux();
        setupEventListeners();
      });

      function chargerPostesTresorerie() {
        // Simulation de chargement des postes de trésorerie
        postesTresorerie = [
          { id: 1, nom: 'Banque ABC', type_flux: 'debit', comptes: ['512000', '521000'] },
          { id: 2, nom: 'Caisse', type_flux: 'credit', comptes: ['530000', '531000'] },
          { id: 3, nom: 'CCP', type_flux: 'debit', comptes: ['514000'] }
        ];

        const select = document.getElementById('posteTresorerie');
        select.innerHTML = '<option value=""> (Pas un flux spécifique)</option>';
        postesTresorerie.forEach(poste => {
          select.innerHTML += `<option value="${poste.id}">${poste.nom}</option>`;
        });
      }

      function chargerComptesGeneraux() {
        // Simulation de chargement des comptes généraux (10110000 à 27210000)
        comptesGeneraux = [
          { numero: '10110000', intitule: 'Capital social', type: 'passif' },
          { numero: '40100000', intitule: 'Fournisseurs', type: 'passif' },
          { numero: '41100000', intitule: 'Clients', type: 'actif' },
          { numero: '51200000', intitule: 'Banque', type: 'actif' },
          { numero: '52100000', intitule: 'CCP', type: 'actif' },
          { numero: '53000000', intitule: 'Caisse', type: 'actif' },
          { numero: '60100000', intitule: 'Achats de matières premières', type: 'charge' },
          { numero: '60700000', intitule: 'Achats de marchandises', type: 'charge' },
          { numero: '64100000', intitule: 'Salaires', type: 'charge' },
          { numero: '70100000', intitule: 'Ventes de produits', type: 'produit' },
          { numero: '70700000', intitule: 'Ventes de marchandises', type: 'produit' },
          { numero: '27210000', intitule: 'Autres créances', type: 'actif' }
        ];

        mettreAJourSelectComptes();
      }

      function mettreAJourSelectComptes(filtreType = null) {
        const select = document.getElementById('compteGeneral');
        select.innerHTML = '<option value="">Sélectionner un compte</option>';

        let comptesFiltres = comptesGeneraux;
        if (filtreType) {
          comptesFiltres = comptesGeneraux.filter(compte => {
            if (filtreType === 'debit') return compte.type === 'actif' || compte.type === 'charge';
            if (filtreType === 'credit') return compte.type === 'actif' || compte.type === 'produit';
            return true;
          });
        }

        comptesFiltres.forEach(compte => {
          select.innerHTML += `<option value="${compte.numero}">${compte.numero} - ${compte.intitule}</option>`;
        });
      }

      function setupEventListeners() {
        // Gestion du type de flux
        document.getElementById('typeFlux').addEventListener('change', function() {
          const typeFlux = this.value;
          const debitInputs = document.querySelectorAll('.debit-input');
          const creditInputs = document.querySelectorAll('.credit-input');

          debitInputs.forEach(input => {
            input.disabled = typeFlux === 'credit';
            if (typeFlux === 'credit') input.value = '';
          });

          creditInputs.forEach(input => {
            input.disabled = typeFlux === 'debit';
            if (typeFlux === 'debit') input.value = '';
          });

          // Mettre à jour les comptes généraux selon le type de flux
          mettreAJourSelectComptes(typeFlux);
        });

        // Gestion du poste de trésorerie
        document.getElementById('posteTresorerie').addEventListener('change', function() {
          const posteId = this.value;
          if (posteId) {
            const poste = postesTresorerie.find(p => p.id == posteId);
            if (poste) {
              document.getElementById('typeFlux').value = poste.type_flux;
              document.getElementById('typeFlux').dispatchEvent(new Event('change'));

              // Pré-remplir les comptes liés
              const selectCompte = document.getElementById('compteGeneral');
              poste.comptes.forEach(numero => {
                const compte = comptesGeneraux.find(c => c.numero.startsWith(numero));
                if (compte) {
                  selectCompte.value = compte.numero;
                }
              });
            }
          }
        });

        // Gestion du fichier
        document.getElementById('pieceFile').addEventListener('change', function(e) {
          const file = e.target.files[0];
          if (file) {
            fichierSelectionne = file;
            // Mettre à jour l'affichage dans le tableau
            document.querySelectorAll('.piece-display').forEach(display => {
              display.textContent = file.name;
            });
          }
        });

        // Gestion de la saisie des numéros de compte
        document.addEventListener('input', function(e) {
          if (e.target.classList.contains('compte-input')) {
            const numero = e.target.value;
            const compte = comptesGeneraux.find(c => c.numero === numero);
            const row = e.target.closest('tr');
            const intituleInput = row.querySelector('.intitule-input');

            if (compte) {
              intituleInput.value = compte.intitule;
            } else {
              intituleInput.value = '';
            }
          }
        });
      }

      function ajouterLigne() {
        console.log('Fonction ajouterLigne appelée');
        const tbody = document.querySelector("#lignesEcritureTable tbody");
        console.log('tbody trouvé:', tbody);

        if (!tbody) {
          console.error('Tableau #lignesEcritureTable tbody non trouvé');
          return;
        }

        const typeFlux = document.getElementById('typeFlux').value;
        const fichierInput = document.getElementById('pieceFile');
        const fichierNom = fichierInput && fichierInput.files[0] ? fichierInput.files[0].name : '-';

        console.log('Ajout d\'une ligne avec typeFlux:', typeFlux, 'fichierNom:', fichierNom);

        const newRow = document.createElement('tr');
        newRow.className = 'ligne-ecriture';
        newRow.innerHTML = `
          <td><input type="text" class="form-control compte-input" placeholder="ex: 401" required /></td>
          <td><input type="text" class="form-control intitule-input" placeholder="Intitulé compte" readonly /></td>
          <td><input type="number" class="form-control debit-input" placeholder="0.00" step="0.01" ${typeFlux === 'credit' ? 'disabled' : ''} /></td>
          <td><input type="number" class="form-control credit-input" placeholder="0.00" step="0.01" ${typeFlux === 'debit' ? 'disabled' : ''} /></td>
          <td><span class="type-flux-display">${typeFlux ? (typeFlux === 'debit' ? 'Décaissement' : 'Encaissement') : '-'}</span></td>
          <td><span class="piece-display">${fichierNom}</span></td>
          <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="supprimerLigne(this)">
              <i class="bx bx-trash"></i>
            </button>
          </td>
        `;
        tbody.appendChild(newRow);
        console.log('Ligne ajoutée avec succès');
      }

      function supprimerLigne(btn) {
        const row = btn.closest('tr');
        row.remove();
      }

      function validerEcriture() {
        const lignes = document.querySelectorAll(".ligne-ecriture");
        let totalDebit = 0;
        let totalCredit = 0;
        let erreurs = [];

        // Validation des lignes
        lignes.forEach((ligne, index) => {
          const debit = parseFloat(ligne.querySelector('.debit-input').value) || 0;
          const credit = parseFloat(ligne.querySelector('.credit-input').value) || 0;
          const compte = ligne.querySelector('.compte-input').value;

          if (!compte) {
            erreurs.push(`Ligne ${index + 1}: Le numéro de compte est requis`);
          }

          totalDebit += debit;
          totalCredit += credit;
        });

        // Vérification de l'équilibre débit/crédit
        if (Math.abs(totalDebit - totalCredit) > 0.01) {
          erreurs.push(`L'équilibre débit/crédit n'est pas respecté: Débit=${totalDebit.toFixed(2)}, Crédit=${totalCredit.toFixed(2)}`);
        }

        if (erreurs.length > 0) {
          alert('Erreurs de validation:\n' + erreurs.join('\n'));
          return;
        }

        // Simulation d'enregistrement
        const ecriture = {
          date: document.getElementById('dateEcriture').value,
          journal: document.getElementById('journal').value,
          reference: document.getElementById('referencePiece').value,
          libelle: document.getElementById('libelleEcriture').value,
          posteTresorerie: document.getElementById('posteTresorerie').value,
          typeFlux: document.getElementById('typeFlux').value,
          fichier: fichierSelectionne,
          lignes: Array.from(lignes).map(ligne => ({
            compte: ligne.querySelector('.compte-input').value,
            intitule: ligne.querySelector('.intitule-input').value,
            debit: parseFloat(ligne.querySelector('.debit-input').value) || 0,
            credit: parseFloat(ligne.querySelector('.credit-input').value) || 0
          }))
        };

        console.log('Écriture à enregistrer:', ecriture);

        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalCenterCreate'));
        modal.hide();

        // Réinitialiser le formulaire
        document.getElementById('dateEcriture').value = '';
        document.getElementById('journal').value = '';
        document.getElementById('referencePiece').value = '';
        document.getElementById('libelleEcriture').value = '';
        document.getElementById('posteTresorerie').value = '';
        document.getElementById('typeFlux').value = '';
        document.getElementById('compteGeneral').value = '';
        document.getElementById('pieceFile').value = '';
        fichierSelectionne = null;

        // Réinitialiser le tableau
        const tbody = document.querySelector("#lignesEcritureTable tbody");
        tbody.innerHTML = `
          <tr class="ligne-ecriture">
            <td><input type="text" class="form-control compte-input" placeholder="ex: 401" required /></td>
            <td><input type="text" class="form-control intitule-input" placeholder="Intitulé compte" readonly /></td>
            <td><input type="number" class="form-control debit-input" placeholder="0.00" step="0.01" /></td>
            <td><input type="number" class="form-control credit-input" placeholder="0.00" step="0.01" /></td>
            <td><span class="type-flux-display">-</span></td>
            <td><span class="piece-display">-</span></td>
            <td>
              <button type="button" class="btn btn-sm btn-danger" onclick="supprimerLigne(this)">
                <i class="bx bx-trash"></i>
              </button>
            </td>
          </tr>
        `;

        alert('Écriture enregistrée avec succès!');
      }

      // Gestion de l'affichage des champs de trésorerie selon le journal
      document.addEventListener('DOMContentLoaded', function() {
        const journalSelect = document.getElementById('journal');
        const tresorerieFields = document.getElementById('tresorerieFields');

        // Définir les codes des journaux qui impliquent des flux de trésorerie
        const tresorerieJournals = ['BAN', 'CAI'];

        function toggleTresorerieFields() {
          if (journalSelect && tresorerieFields) {
            if (tresorerieJournals.includes(journalSelect.value)) {
              tresorerieFields.style.display = 'block';
            } else {
              tresorerieFields.style.display = 'none';
              document.getElementById('posteTresorerie').value = '';
              document.getElementById('typeFlux').value = '';
            }
          }
        }

        if (journalSelect) {
          journalSelect.addEventListener('change', toggleTresorerieFields);
        }
        toggleTresorerieFields();
      });
    </script>

  </body>
</html>
