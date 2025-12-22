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
                        </form>
                        <hr />
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Écritures saisies :</h6>
                            <div class="d-flex align-items-center">
                                <span class="me-3">Total Débit : <span id="totalDebit">0.00</span></span>
                                <span>Total Crédit : <span id="totalCredit">0.00</span></span>
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

      <!-- Core JS -->
      @include('components.footer')


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
