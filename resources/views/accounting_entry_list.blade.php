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
          @include('components.header', ['page_title' => 'LISTE DES <span class="text-gradient">ÉCRITURES</span>'])
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <div class="col-12">
              </div>
            </div>

            <!-- Card avec tableau et bouton -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Liste des écritures</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#nouvelleEcritureModal">
                        <i class="bx bx-plus me-1"></i> Nouvelle écriture
                    </button>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filterExercice" class="form-label">Exercice</label>
                            <select id="filterExercice" class="form-select">
                                <option value="">Tous les exercices</option>
                                @if(isset($exercices))
                                    @foreach ($exercices as $exercice)
                                        <option value="{{ $exercice->id }}" {{ ($data['exercice_id'] ?? '') == $exercice->id ? 'selected' : '' }}>
                                            {{ $exercice->intitule }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterMois" class="form-label">Mois</label>
                            <select id="filterMois" class="form-select">
                                <option value="">Tous les mois</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ ($data['mois'] ?? '') == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterJournal" class="form-label">Journal</label>
                            <select id="filterJournal" class="form-select">
                                <option value="">Tous les journaux</option>
                                @if(isset($code_journaux))
                                    @foreach ($code_journaux as $journal)
                                        <option value="{{ $journal->id }}" {{ ($data['journal_id'] ?? '') == $journal->id ? 'selected' : '' }}>
                                            {{ $journal->code }} - {{ $journal->intitule }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="filterEcritures()">
                                <i class="bx bx-filter me-1"></i> Filtrer
                            </button>
                        </div>
                    </div>

                    <!-- Tableau des écritures -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" id="tableEcrituresList">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>N° Saisie</th>
                                    <th>Journal</th>
                                    <th>Libellé</th>
                                    <th>Référence Pièce</th>
                                    <th>Compte Général</th>
                                    <th>Compte Tiers</th>
                                    <th>Débit</th>
                                    <th>Crédit</th>
                                    <th>Plan Analytique</th>
                                    <th>Pièce Justificative</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($ecritures) && count($ecritures) > 0)
                                    @foreach ($ecritures as $ecriture)
                                        <tr>
                                            <td>{{ $ecriture->date ?? '' }}</td>
                                            <td>{{ $ecriture->n_saisie ?? '' }}</td>
                                            <td>{{ $ecriture->code_journal ?? '' }}</td>
                                            <td>{{ $ecriture->description_operation ?? '' }}</td>
                                            <td>{{ $ecriture->reference_piece ?? '' }}</td>
                                            <td>{{ $ecriture->compte_general ?? '' }}</td>
                                            <td>{{ $ecriture->compte_tiers ?? '' }}</td>
                                            <td class="text-end">
                                                {{ $ecriture->debit ? number_format($ecriture->debit, 2, ',', ' ') : '' }}
                                            </td>
                                            <td class="text-end">
                                                {{ $ecriture->credit ? number_format($ecriture->credit, 2, ',', ' ') : '' }}
                                            </td>
                                            <td>{{ $ecriture->plan_analytique ? 'Oui' : 'Non' }}</td>
                                            <td>
                                                @if($ecriture->piece_justificatif)
                                                    <a href="{{ asset('justificatifs/' . $ecriture->piece_justificatif) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-file"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="editEcriture({{ $ecriture->id }})">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteEcriture({{ $ecriture->id }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="13" class="text-center text-muted py-4">
                                            Aucune écriture trouvée pour les critères sélectionnés
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr class="table-active fw-bold">
                                    <td colspan="7" class="text-end">TOTAL</td>
                                    <td class="text-end">
                                        {{ isset($totalDebit) ? number_format($totalDebit, 2, ',', ' ') : '0,00' }}
                                    </td>
                                    <td class="text-end">
                                        {{ isset($totalCredit) ? number_format($totalCredit, 2, ',', ' ') : '0,00' }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
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

      <!-- Core JS -->
      @include('components.footer')

      <!-- Modal Nouvelle écriture -->
      <div class="modal fade" id="nouvelleEcritureModal" tabindex="-1" aria-labelledby="nouvelleEcritureModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="nouvelleEcritureModalLabel">Nouvelle écriture</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                  </div>
                  <div class="modal-body">
                      <form id="formNouvelleEcriture">
                          <input type="hidden" id="hiddenNumeroSaisie" name="numero_saisie" />
                          <input type="hidden" id="hiddenCodeJournal" name="code_journal" />

                          <div class="row g-4">
                              <div class="col-md-4">
                                  <label for="dateEcriture" class="form-label">Date</label>
                                  <input type="date" id="dateEcriture" name="date" class="form-control" required />
                              </div>
                              <div class="col-md-4">
                                  <label for="journalEcriture" class="form-label">Journal</label>
                                  <input type="text" id="journalEcriture" name="journal" class="form-control" readonly />
                              </div>
                              <div class="col-md-4">
                                  <label for="numeroSaisie" class="form-label">N° Saisie</label>
                                  <input type="text" id="numeroSaisie" name="numero_saisie" class="form-control" readonly />
                              </div>
                              
                              <div class="col-md-12">
                                  <label for="libelleEcriture" class="form-label">Libellé / Intitulé de l'opération</label>
                                  <input type="text" id="libelleEcriture" name="libelle" class="form-control" placeholder="Entrez le libellé de l'écriture..." required />
                              </div>

                              <div class="col-md-6">
                                  <label for="compteGeneralSearch" class="form-label">Compte Général Search</label>
                                  <div class="search-select-container">
                                      <div class="input-group">
                                          <span class="input-group-text"><i class="bx bx-search"></i></span>
                                          <input type="text" id="compteGeneralSearch" class="form-control" placeholder="Rechercher un compte général (ex: 701...)" autocomplete="off">
                                          <input type="hidden" id="compteGeneralEcriture" name="compte_general" required>
                                      </div>
                                      <div class="search-select-dropdown" id="compteGeneralDropdown" style="display: none;">
                                          <div class="list-group">
                                              @if(isset($plansComptables))
                                                  @foreach ($plansComptables as $plan)
                                                      <a href="#" class="list-group-item list-group-item-action option-compte" 
                                                         data-value="{{ $plan->id }}" 
                                                         data-numero="{{ $plan->numero_de_compte }}">
                                                          <strong>{{ $plan->numero_de_compte }}</strong> - {{ $plan->intitule }}
                                                      </a>
                                                  @endforeach
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="col-md-6">
                                  <label for="compteTiersSearch" class="form-label">Compte Tiers Search</label>
                                  <div class="search-select-container">
                                      <div class="input-group">
                                          <span class="input-group-text"><i class="bx bx-search"></i></span>
                                          <input type="text" id="compteTiersSearch" class="form-control" placeholder="Rechercher un tiers (Client, Fournisseur...)" autocomplete="off">
                                          <input type="hidden" id="compteTiersEcriture" name="compte_tiers">
                                      </div>
                                      <div class="search-select-dropdown" id="compteTiersDropdown" style="display: none;">
                                          <div class="list-group">
                                              @if(isset($tiers))
                                                  @foreach ($tiers as $tier)
                                                      <a href="#" class="list-group-item list-group-item-action option-tier"
                                                         data-value="{{ $tier->id }}"
                                                         data-compte-general="{{ $tier->compte_general_id ?? '' }}"
                                                         data-numero-compte="{{ $tier->numero_compte ?? '' }}"
                                                         data-libelle="{{ $tier->intitule ?? '' }}"
                                                         data-adresse="{{ $tier->adresse ?? '' }}"
                                                         data-telephone="{{ $tier->telephone ?? '' }}"
                                                         data-email="{{ $tier->email ?? '' }}">
                                                          @if(!empty($tier->code_tiers))
                                                              <strong>{{ $tier->code_tiers }}</strong> - 
                                                          @endif
                                                          {{ $tier->intitule }}
                                                      </a>
                                                  @endforeach
                                              @endif
                                          </div>
                                      </div>
                                  </div>
                              </div>

                              <div class="col-md-3">
                                  <label for="referencePieceEcriture" class="form-label">Référence Pièce</label>
                                  <input type="text" id="referencePieceEcriture" name="reference_piece" class="form-control" placeholder="N° de facture, chèque..." />
                              </div>
                              <div class="col-md-3">
                                  <label for="debitEcriture" class="form-label">Montant Débit</label>
                                  <input type="number" id="debitEcriture" name="debit" class="form-control" step="0.01" min="0" placeholder="0.00" />
                              </div>
                              <div class="col-md-3">
                                  <label for="creditEcriture" class="form-label">Montant Crédit</label>
                                  <input type="number" id="creditEcriture" name="credit" class="form-control" step="0.01" min="0" placeholder="0.00" />
                              </div>
                              <div class="col-md-3">
                                  <label for="planAnalytiqueEcriture" class="form-label">Analytique</label>
                                  <select id="planAnalytiqueEcriture" name="plan_analytique" class="form-select">
                                      <option value="0">Non</option>
                                      <option value="1">Oui</option>
                                  </select>
                              </div>
                              <div class="col-md-12">
                                  <label for="pieceJustificativeEcriture" class="form-label">Pièce justificative (PDF, Image)</label>
                                  <input type="file" id="pieceJustificativeEcriture" name="piece_justificative" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                              </div>
                          </div>
                      </form>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                      <button type="button" class="btn btn-primary" onclick="ajouterEcritureModal()">Ajouter l'écriture</button>
                  </div>
              </div>
          </div>
      </div>

    </body>

</html>

<script>
    // Fonction pour remplir automatiquement les champs du modal
    document.addEventListener('DOMContentLoaded', function() {
        // Remplir automatiquement la date du jour
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('dateEcriture').value = today;

        // Récupérer les paramètres de l'URL
        const urlParams = new URLSearchParams(window.location.search);

        // Remplir les champs si les paramètres existent
        if (urlParams.has('numero_saisie')) {
            document.getElementById('numeroSaisie').value = urlParams.get('numero_saisie');
            document.getElementById('hiddenNumeroSaisie').value = urlParams.get('numero_saisie');
        }

        if (urlParams.has('code')) {
            document.getElementById('journalEcriture').value = urlParams.get('code');
            document.getElementById('hiddenCodeJournal').value = urlParams.get('code');
        }

        if (urlParams.has('id_journal')) {
            document.getElementById('hiddenCodeJournal').value = urlParams.get('id_journal');
        }
    });

    // Fonction pour ajouter une écriture depuis le modal
    function ajouterEcritureModal() {
        const form = document.getElementById('formNouvelleEcriture');
        const formData = new FormData(form);

        // Validation basique
        const date = document.getElementById('dateEcriture').value;
        const libelle = document.getElementById('libelleEcriture').value;
        const compteGeneral = document.getElementById('compteGeneralEcriture').value;
        const debit = parseFloat(document.getElementById('debitEcriture').value) || 0;
        const credit = parseFloat(document.getElementById('creditEcriture').value) || 0;

        if (!date || !libelle || !compteGeneral) {
            alert('Veuillez remplir les champs obligatoires (Date, Libellé, Compte Général).');
            return;
        }

        if (debit === 0 && credit === 0) {
            alert('Veuillez saisir un montant au débit ou au crédit.');
            return;
        }

        // Ajouter la ligne au tableau (simulation)
        const table = document.getElementById('tableEcrituresList').getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();

        newRow.innerHTML = `
            <td>${date}</td>
            <td>${document.getElementById('numeroSaisie').value}</td>
            <td>${document.getElementById('journalEcriture').value}</td>
            <td>${libelle}</td>
            <td>${document.getElementById('referencePieceEcriture').value}</td>
            <td>${document.getElementById('compteGeneralEcriture').options[document.getElementById('compteGeneralEcriture').selectedIndex].text}</td>
            <td>${document.getElementById('compteTiersEcriture').options[document.getElementById('compteTiersEcriture').selectedIndex]?.text || ''}</td>
            <td class="text-end">${debit > 0 ? debit.toFixed(2).replace('.', ',') : ''}</td>
            <td class="text-end">${credit > 0 ? credit.toFixed(2).replace('.', ',') : ''}</td>
            <td>${document.getElementById('planAnalytiqueEcriture').value === '1' ? 'Oui' : 'Non'}</td>
            <td>
                <button type="button" class="btn btn-sm btn-warning" onclick="editEcriture(0)">
                    <i class="bx bx-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteEcriture(0)">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        `;

        // Fermer le modal et réinitialiser le formulaire
        const modal = bootstrap.Modal.getInstance(document.getElementById('nouvelleEcritureModal'));
        modal.hide();
        form.reset();

        alert('Écriture ajoutée avec succès !');
    }

    // Fonction pour filtrer les écritures
    function filterEcritures() {
        const exercice = document.getElementById('filterExercice').value;
        const mois = document.getElementById('filterMois').value;
        const journal = document.getElementById('filterJournal').value;

        // Construire l'URL avec les filtres
        const params = new URLSearchParams();
        if (exercice) params.append('exercice_id', exercice);
        if (mois) params.append('mois', mois);
        if (journal) params.append('journal_id', journal);

        // Recharger la page avec les filtres
        window.location.href = window.location.pathname + '?' + params.toString();
    }

    // Fonctions d'édition et suppression (placeholders)
    function editEcriture(id) {
        alert('Fonction de modification à implémenter pour l\'écriture ID: ' + id);
    }

    function deleteEcriture(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette écriture ?')) {
            alert('Fonction de suppression à implémenter pour l\'écriture ID: ' + id);
        }
    }

    // Fonction pour filtrer les options d'un menu déroulant de recherche
    function filtrerOptions(searchInputId, dropdownId) {
        const searchText = document.getElementById(searchInputId).value.toLowerCase();
        const dropdown = document.getElementById(dropdownId);
        const items = dropdown.getElementsByClassName('list-group-item');
        let hasVisibleItems = false;
        
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const text = item.textContent.toLowerCase();
            
            if (text.includes(searchText)) {
                item.style.display = '';
                hasVisibleItems = true;
            } else {
                item.style.display = 'none';
            }
        }
        
        // Afficher/masquer le dropdown
        if (searchText.length > 0) {
            dropdown.style.display = hasVisibleItems ? 'block' : 'none';
        } else {
            dropdown.style.display = 'none';
        }
    }

    // Fonction pour ajuster dynamiquement la taille du modal
    function ajusterTailleModal() {
        const modal = document.querySelector('#nouvelleEcritureModal .modal-dialog');
        if (!modal) return;
        
        // Réinitialiser la taille
        modal.style.maxWidth = '90%';
        modal.style.margin = '1.75rem auto';
        
        // Ajuster en fonction du contenu
        const windowHeight = window.innerHeight;
        const modalContent = modal.querySelector('.modal-content');
        
        if (modalContent.scrollHeight > windowHeight * 0.8) {
            modal.style.maxHeight = '90vh';
            modalContent.style.maxHeight = 'calc(90vh - 3.5rem)';
            modalContent.style.overflowY = 'auto';
        } else {
            modal.style.maxHeight = '';
            modalContent.style.maxHeight = '';
            modalContent.style.overflowY = '';
        }
    }

    // Mettre à jour l'affichage des sélecteurs au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Ajouter des styles pour les menus de recherche
        const style = document.createElement('style');
        style.textContent = `
            /* Taille du modal */
            #nouvelleEcritureModal .modal-dialog {
                max-width: 80%;
                width: 850px;
                max-height: 90vh;
                margin: 1.75rem auto;
            }
            
            #nouvelleEcritureModal .modal-content {
                border: none;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            
            #nouvelleEcritureModal .modal-header {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-bottom: 1px solid #dee2e6;
                padding: 1.5rem;
                border-radius: 12px 12px 0 0;
            }

            #nouvelleEcritureModal .modal-body {
                padding: 2rem;
            }
            
            /* Ajustements pour les champs du formulaire */
            #nouvelleEcritureModal .form-control,
            #nouvelleEcritureModal .form-select {
                padding: 0.75rem 1rem;
                font-size: 1.05rem;
                border-radius: 8px;
                border: 1px solid #ced4da;
                transition: all 0.2s ease;
                background-color: #fff;
            }
            
            #nouvelleEcritureModal .form-control:focus,
            #nouvelleEcritureModal .form-select:focus {
                border-color: #696cff;
                box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
                background-color: #fff;
            }

            #nouvelleEcritureModal input[readonly] {
                background-color: #f8f9fa;
                cursor: not-allowed;
                border-color: #e9ecef;
            }
            
            #nouvelleEcritureModal .form-label {
                font-weight: 600;
                color: #566a7f;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* Styles pour les menus de recherche */
            .search-select-container { 
                position: relative;
            }

            .search-select-dropdown {
                position: absolute;
                width: 100%;
                max-height: 250px;
                overflow-y: auto;
                z-index: 1060;
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                margin-top: 5px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }

            .search-select-dropdown .list-group-item {
                border: none;
                padding: 10px 15px;
                font-size: 0.95rem;
                transition: background 0.2s;
            }

            .search-select-dropdown .list-group-item:hover {
                background-color: #f0f2ff;
                color: #696cff;
            }

            .input-group-text {
                background-color: #f8f9fa;
                border-radius: 8px 0 0 8px;
                border-right: none;
                color: #696cff;
            }

            .input-group .form-control {
                border-radius: 0 8px 8px 0 !important;
            }
            
            .modal-footer {
                padding: 1.5rem;
                border-top: 1px solid #dee2e6;
            }
            
            .btn-primary {
                padding: 0.75rem 2rem;
                border-radius: 8px;
                font-weight: 600;
            }
            
            .btn-secondary {
                padding: 0.75rem 2rem;
                border-radius: 8px;
            }

            @media (max-width: 992px) {
                #nouvelleEcritureModal .modal-dialog {
                    max-width: 95%;
                    margin: 10px auto;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Initialiser les champs de recherche
        initSearchSelect('compteGeneralSearch', 'compteGeneralDropdown', 'compteGeneralEcriture');
        initSearchSelect('compteTiersSearch', 'compteTiersDropdown', 'compteTiersEcriture');
        
        // Ajouter un écouteur pour le redimensionnement de la fenêtre
        window.addEventListener('resize', ajusterTailleModal);
        
        // Ajuster la taille du modal après son affichage
        const modal = document.getElementById('nouvelleEcritureModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', ajusterTailleModal);
        }
    });
    
    // Fonction pour initialiser les champs de recherche
    function initSearchSelect(inputId, dropdownId, hiddenInputId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(hiddenInputId);
        
        if (!input || !dropdown) return;
        
        // Gérer le focus et le clic en dehors
        input.addEventListener('focus', function() {
            if (this.value) {
                dropdown.style.display = 'block';
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        
        // Gérer la recherche
        input.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = dropdown.getElementsByClassName('list-group-item');
            let hasVisibleItems = false;
            
            for (let item of items) {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    item.style.display = '';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            }
            
            dropdown.style.display = hasVisibleItems ? 'block' : 'none';
        });
        
        // Gérer la sélection d'un élément
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const item = e.target.closest('.list-group-item');
            if (!item) return;
            
            input.value = item.textContent.trim();
            hiddenInput.value = item.dataset.value;
            dropdown.style.display = 'none';
            
            // Déclencher l'événement de changement si c'est un compte tiers
            if (hiddenInputId === 'compteTiersEcriture') {
                remplirChampsPlanTiers(item);
            }
        });
    }
    
    // Fonction pour sélectionner automatiquement le compte général correspondant
    function selectionnerCompteGeneralParNumero(numeroCompte) {
        const compteGeneralSelect = document.getElementById('compteGeneralEcriture');
        if (!compteGeneralSelect) return false;
        
        // Rechercher le compte par son numéro
        for (let i = 0; i < compteGeneralSelect.options.length; i++) {
            const option = compteGeneralSelect.options[i];
            if (option.dataset.numero === numeroCompte) {
                compteGeneralSelect.value = option.value;
                return true;
            }
        }
        return false;
    }

    // Fonction pour remplir automatiquement les champs lors de la sélection d'un plan tiers
    function remplirChampsPlanTiers(selectedItem) {
        if (!selectedItem.dataset) {
            // Si c'est un élément select (pour la rétrocompatibilité)
            if (selectedItem.options) {
                selectedItem = selectedItem.options[selectedItem.selectedIndex];
            } else {
                return;
            }
        }
        
        // Remplir le libellé en priorité
        if (selectedItem.dataset.libelle) {
            document.getElementById('libelleEcriture').value = selectedItem.dataset.libelle;
        }
        
        // Si un numéro de compte est fourni, essayer de sélectionner le compte général correspondant
        if (selectedItem.dataset.numeroCompte) {
            const numeroCompte = selectedItem.dataset.numeroCompte;
            const compteTrouve = selectionnerCompteGeneralParNumero(numeroCompte);
            
            if (!compteTrouve) {
                console.warn('Aucun compte général trouvé pour le numéro:', numeroCompte);
                // Si aucun compte n'est trouvé, utiliser le compte général fourni en fallback
                if (selectedItem.dataset.compteGeneral) {
                    document.getElementById('compteGeneralEcriture').value = selectedItem.dataset.compteGeneral;
                    // Mettre à jour le champ de recherche du compte général
                    const compteGeneralSearch = document.getElementById('compteGeneralSearch');
                    if (compteGeneralSearch) {
                        // Trouver le libellé du compte général
                        const compteGeneralSelect = document.getElementById('compteGeneralEcriture');
                        if (compteGeneralSelect) {
                            const selectedOption = Array.from(compteGeneralSelect.options).find(
                                opt => opt.value === selectedItem.dataset.compteGeneral
                            );
                            if (selectedOption) {
                                compteGeneralSearch.value = selectedOption.text.trim();
                            }
                        }
                    }
                }
            }
        } else if (selectedItem.dataset.compteGeneral) {
            // Fallback si seul compte_general_id est fourni
            document.getElementById('compteGeneralEcriture').value = selectedItem.dataset.compteGeneral;
        }
        
        // Remplir les autres champs si disponibles
        const fields = ['adresse', 'telephone', 'email'];
        fields.forEach(field => {
            const element = document.getElementById(field + 'Ecriture');
            if (element && selectedItem.dataset[field]) {
                element.value = selectedItem.dataset[field];
            }
        });
        
        // Ajuster la taille du modal si nécessaire
        ajusterTailleModal();
    }
    
    // Gérer la suppression de la sélection
    document.addEventListener('click', function(e) {
        // Si on clique sur la croix dans le champ de recherche
        if (e.target.matches('.search-clear') || e.target.closest('.search-clear')) {
            const input = e.target.closest('.input-group').querySelector('input[type="text"]');
            const hiddenInput = e.target.closest('.input-group').querySelector('input[type="hidden"]');
            if (input && hiddenInput) {
                input.value = '';
                hiddenInput.value = '';
                
                // Si c'est le champ des tiers, vider aussi le libellé
                if (hiddenInput.id === 'compteTiersEcriture') {
                    document.getElementById('libelleEcriture').value = '';
                }
            }
        }
    });
</script>
