<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<style>
    /* Design Premium pour la Saisie d'Écritures */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f2f4;
        padding: 1.5rem 2rem;
    }
    .card-title {
        font-weight: 700;
        color: #32475c;
        margin: 0;
    }
    .card-body {
        padding: 2rem;
    }

    /* Labels et Contrôles */
    .form-label {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.82rem;
        letter-spacing: 0.5px;
        color: #566a7f;
        margin-bottom: 0.6rem;
    }
    .form-control, .form-select {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 10px;
        border: 1px solid #d9dee3;
        transition: all 0.2s ease;
        background-color: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
    }
    .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }

    /* Table d'aperçu */
    #tableEcritures {
        margin-top: 1.5rem;
    }
    #tableEcritures th {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
        color: #566a7f;
        padding: 1rem;
    }
    #tableEcritures td {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    /* Totaux */
    #totalDebit, #totalCredit {
        font-weight: 700;
        font-size: 1.1rem;
        color: #696cff;
    }

    /* Boutons */
    .btn-primary, .btn-success {
        padding: 0.7rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-primary {
        background-color: #696cff; /* Blue color */
        color: #fff;
        border: 2px solid #566a7f; /* Complementary border */
    }
    .btn-primary:hover {
        background-color: #4a5bcc; /* Darker blue on hover */
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(105, 108, 255, 0.3);
    }
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(113, 221, 55, 0.3);
    }
</style>

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
                            <div class="row g-4">
                                <div class="col-md-3">
                                    <label for="date" class="form-label">Date de l'écriture</label>
                                    <input type="date" id="date" name="date" class="form-control" required />
                                    <div class="invalid-feedback">Veuillez renseigner la date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="imputation" class="form-label">Journal d'imputation</label>
                                    <input type="text" class="form-control" placeholder="{{ $data['code'] ?? 'N/A' }}" readonly />
                                    <input type="hidden" id="imputation" name="code_journal_id" value="{{ $data['id_code'] ?? 'N/A' }}" class="form-control" data-code_imputation="{{ $data['code'] ?? 'N/A' }}" />
                                </div>
                                <div class="col-md-3">
                                    <label for="n_saisie" class="form-label">N° de Saisie</label>
                                    <input type="text" id="n_saisie" name="n_saisie" class="form-control" placeholder="Automatique" />
                                </div>

                                <div class="col-md-12">
                                    <label for="description_operation" class="form-label">Libellé / Description de l'opération</label>
                                    <input type="text" id="description_operation" name="description_operation" class="form-control" placeholder="Saisissez le libellé de l'opération..." required />
                                    <div class="invalid-feedback">Veuillez entrer la description.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="compte_general" class="form-label">Compte Général (Classe 1-7)</label>
                                    <select id="compte_general" name="compte_general"
                                        class="form-select select2 w-100" data-live-search="true"
                                        title="Sélectionner un compte général" required>
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
                                <div class="col-md-6">
                                    <label for="compte_tiers" class="form-label">Compte Tiers (Le cas échéant)</label>
                                    <select id="compte_tiers" name="compte_tiers" class="form-select select2 w-100" data-live-search="true">
                                        <option value="">Sélectionner un compte tiers</option>
                                        @if(isset($tiers))
                                            @foreach ($tiers as $tier)
                                                <option value="{{ $tier->id }}">{{ $tier->intitule }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="reference_piece" class="form-label">Référence Pièce</label>
                                    <input type="text" id="reference_piece" name="reference_piece" class="form-control" placeholder="N° Facture, Chèque..." />
                                </div>
                                <div class="col-md-3">
                                    <label for="debit" class="form-label">Montant Débit</label>
                                    <input type="number" id="debit" name="debit" class="form-control" step="0.01" min="0" placeholder="0.00" />
                                </div>
                                <div class="col-md-3">
                                    <label for="credit" class="form-label">Montant Crédit</label>
                                    <input type="number" id="credit" name="credit" class="form-control" step="0.01" min="0" placeholder="0.00" />
                                </div>
                                <div class="col-md-3">
                                    <label for="plan_analytique" class="form-label">Analytique</label>
                                    <select id="plan_analytique" name="plan_analytique"
                                        class="form-select w-100" required>
                                        <option value="1">Oui</option>
                                        <option value="0" selected>Non</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label for="piece_justificatif" class="form-label">Pièce justificative (PDF, Scan...)</label>
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
                        <button type="button" class="btn btn-primary" id="btnEnregistrer"
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
// Fonction pour obtenir le prochain numéro de saisie (12 chiffres)
function getNextSaisieNumber() {
    // Récupérer le dernier numéro de saisie depuis le stockage local ou initialiser à 1
    let lastNumber = localStorage.getItem('lastSaisieNumber');
    let nextNumber = lastNumber ? parseInt(lastNumber, 10) : 0;
    
    // Formater le numéro sur 12 chiffres avec des zéros devant
    return (nextNumber + 1).toString().padStart(12, '0');
}

// Fonction pour incrémenter le numéro de saisie
function incrementSaisieNumber() {
    const currentNumber = parseInt(document.getElementById('n_saisie').value || '0', 10);
    const nextNumber = currentNumber + 1;
    localStorage.setItem('lastSaisieNumber', nextNumber);
    return nextNumber.toString().padStart(12, '0');
}

// Au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Définir la date du jour par défaut
    const today = new Date().toISOString().split('T')[0];
    const dateField = document.getElementById('date');
    if (dateField) {
        dateField.value = today;
    }
    
    // Définir le numéro de saisie initial
    const nSaisieField = document.getElementById('n_saisie');
    if (nSaisieField) {
        nSaisieField.value = getNextSaisieNumber();
    }
    
    // Ajouter la classe 'form-control' si elle n'existe pas
    if (nSaisieField && !nSaisieField.classList.contains('form-control')) {
        nSaisieField.classList.add('form-control');
    }
});
    // Fonction globale pour ajouter une écriture
    function ajouterEcriture() {
        try {
            const date = document.getElementById('date');
            const nSaisie = document.getElementById('n_saisie');
            const libelle = document.getElementById('description_operation');
            const debit = document.getElementById('debit');
            const credit = document.getElementById('credit');
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
            const pieceFileName = pieceFile && pieceFile.files[0] ? pieceFile.files[0].name : '';

            // Créer les cellules une par une pour pouvoir ajouter des attributs
            const cells = [
                date.value,
                nSaisie ? nSaisie.value : '',
                imputationValue,
                libelle.value,
                referencePiece ? referencePiece.value || '' : '',
                '', // Compte général - sera rempli avec l'élément personnalisé
                compteTiersValue,
                debit.value || '',
                credit.value || '',
                pieceFileName,
                analytiqueValue
            ];

            // Ajouter chaque cellule avec son contenu
            cells.forEach((content, index) => {
                const cell = newRow.insertCell();
                if (index === 5) {
                    // Pour la cellule du compte général, ajouter l'attribut data-plan-comptable-id
                    cell.textContent = compteText;
                    cell.setAttribute('data-plan-comptable-id', compteGeneral.value);
                } else if (index === 6 && compteTiers && compteTiers.value) {
                    // Pour la cellule du compte tiers, ajouter l'attribut data-tiers-id
                    cell.textContent = compteTiersValue;
                    cell.setAttribute('data-tiers-id', compteTiers.value);
                } else {
                    cell.textContent = content;
                }
            });

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

    // Fonction pour afficher des alertes stylisées
    function showAlert(type, message) {
        // Supprimer les alertes existantes
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());

        // Créer l'élément d'alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        
        // Ajouter le contenu de l'alerte
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Positionner l'alerte en haut à droite
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';

        // Ajouter l'alerte au body
        document.body.appendChild(alertDiv);

        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Fonction pour enregistrer les écritures
    function enregistrerEcritures() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) {
            alert('Erreur: Tableau des écritures introuvable');
            return;
        }

        const rows = tbody.getElementsByTagName('tr');
        if (rows.length === 0) {
            alert('Aucune écriture à enregistrer.');
            return;
        }

        // Récupérer les données du formulaire
        const formData = new FormData(document.getElementById('formEcriture'));
        const ecritures = [];
        const nSaisie = document.getElementById('n_saisie').value;
        const codeJournalId = formData.get('code_journal_id');
        const dateEcriture = formData.get('date');
        
        // Vérifier que tous les champs requis sont présents
        if (!dateEcriture || !nSaisie || !codeJournalId) {
            showAlert('danger', 'Veuillez remplir tous les champs obligatoires.');
            return;
        }

        // Préparer les données pour l'envoi
        Array.from(rows).forEach(row => {
            const cells = row.cells;
            const debit = parseFloat(cells[7].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            const credit = parseFloat(cells[8].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            
            // Récupérer l'ID du plan comptable depuis l'attribut data-id de la cellule du compte
            const compteCell = cells[2]; // La cellule du compte général
            const planComptableId = compteCell.getAttribute('data-plan-comptable-id');
            
            if (!planComptableId) {
                showAlert('danger', 'Veuillez sélectionner un compte général valide pour chaque ligne.');
                return;
            }
            
            ecritures.push({
                date: dateEcriture,
                n_saisie: nSaisie,
                description_operation: cells[3].textContent.trim(),
                reference_piece: cells[4].textContent.trim(),
                plan_comptable_id: planComptableId,
                plan_tiers_id: cells[6].getAttribute('data-tiers-id') || null,
                code_journal_id: codeJournalId,
                debit: debit,
                credit: credit,
                piece_justificatif: cells[9].textContent.trim(),
                plan_analytique: cells[10].textContent.trim() === 'Oui' ? 1 : 0
            });
        });

        // Afficher les données dans la console pour le débogage
        console.log('Données à envoyer:', ecritures);

        // Afficher l'indicateur de chargement
        const btnEnregistrer = document.getElementById('btnEnregistrer');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        
        btnText.textContent = 'Enregistrement...';
        btnEnregistrer.disabled = true;
        btnSpinner.classList.remove('d-none');

        // Envoyer les données au serveur
        fetch('{{ route("ecriture.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ecritures: ecritures })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Incrémenter le numéro de saisie après un enregistrement réussi
                document.getElementById('n_saisie').value = incrementSaisieNumber();
                
                // Vider le tableau
                tbody.innerHTML = '';
                updateTotals();
                
                showAlert('success', 'Écritures enregistrées avec succès !');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('danger', 'Erreur lors de l\'enregistrement: ' + (error.message || 'Veuillez réessayer'));
        })
        .finally(() => {
            // Réinitialiser le bouton
            btnText.textContent = 'Enregistrer';
            btnEnregistrer.disabled = false;
            btnSpinner.classList.add('d-none');
        });
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
</script>
