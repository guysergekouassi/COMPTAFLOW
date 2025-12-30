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
    .btn-primary:hover {
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
                                    <input type="date" id="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required />
                                    <div class="invalid-feedback">Veuillez renseigner la date.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="imputation" class="form-label">Journal d'imputation</label>
                                    <input type="text" class="form-control" placeholder="{{ $data['code'] ?? 'N/A' }}" readonly />
                                    <input type="hidden" id="imputation" name="code_journal_id" value="{{ $data['id_code'] ?? 'N/A' }}" class="form-control" data-code_imputation="{{ $data['code'] ?? 'N/A' }}" />
                                </div>
                                <div class="col-md-3">
                                    <label for="n_saisie" class="form-label">N° de Saisie</label>
                                    <input type="text" id="n_saisie" name="n_saisie" class="form-control" value="{{ $nextSaisieNumber ?? '' }}" readonly />
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Écritures saisies :</h6>
                            <div class="d-flex align-items-center">
                                <span class="me-4">
                                    <span class="text-muted">Débit:</span> 
                                    <span id="totalDebit" class="fw-bold text-primary">0,00</span> €
                                </span>
                                <span class="me-4">
                                    <span class="text-muted">Crédit:</span> 
                                    <span id="totalCredit" class="fw-bold text-primary">0,00</span> €
                                </span>
                                <span id="ecart" class="me-3"></span>
                                <button type="button" class="btn btn-primary" onclick="ajouterEcriture()">
                                    <i class="fas fa-plus me-2"></i>Ajouter
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="tableEcritures" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Date</th>
                                        <th>Compte Général</th>
                                        <th>Compte Tiers</th>
                                        <th>Libellé</th>
                                        <th class="text-end" style="width: 150px;">Débit</th>
                                        <th class="text-end" style="width: 150px;">Crédit</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    <!-- Les écritures seront ajoutées ici dynamiquement -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Totaux :</th>
                                        <th class="text-end" id="totalDebitFooter">0,00 €</th>
                                        <th class="text-end" id="totalCreditFooter">0,00 €</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
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
    // Initialisation du numéro de saisie
    document.addEventListener('DOMContentLoaded', function() {
        // Si le champ n_saisie est vide, on le remplit avec le numéro généré côté serveur
        const nSaisieField = document.getElementById('n_saisie');
        if (nSaisieField) {
            if (!nSaisieField.value) {
                // Si le numéro n'est pas défini, on en génère un nouveau côté client (au format 12 chiffres)
                const now = new Date();
                const timestamp = now.getTime().toString();
                const uniqueId = timestamp.slice(-12).padStart(12, '0');
                nSaisieField.value = uniqueId;
            }
            // S'assurer que le numéro est bien formaté sur 12 chiffres
            let currentNumber = parseInt(nSaisieField.value, 10) || 0;
            nSaisieField.value = currentNumber.toString().padStart(12, '0');
        }
    });

    // Fonction globale pour ajouter une écriture
    function ajouterEcriture() {
        try {
            const date = document.getElementById('date');
            const libelle = document.getElementById('description_operation');
            const compteGeneral = document.getElementById('compte_general');
            const compteTiers = document.getElementById('compte_tiers');
            const debit = document.getElementById('debit');
            const credit = document.getElementById('credit');
            const referencePiece = document.getElementById('reference_piece');
            const pieceFile = document.getElementById('piece_justificatif');

            // Validation des champs obligatoires
            if (!date.value || !libelle.value || !compteGeneral.value || (!debit.value && !credit.value)) {
                showAlert('Veuillez remplir tous les champs obligatoires.', 'error');
                return;
            }

            // Vérification qu'un seul des champs débit ou crédit est rempli
            if (debit.value && credit.value) {
                showAlert('Veuillez saisir soit un débit, soit un crédit, mais pas les deux.', 'error');
                return;
            }

            // Récupération des valeurs des champs
            const compteGeneralText = compteGeneral.options[compteGeneral.selectedIndex].text;
            const compteTiersText = compteTiers && compteTiers.value ? compteTiers.options[compteTiers.selectedIndex].text : '';
            const compteTiersId = compteTiers && compteTiers.value ? compteTiers.value : '';

            // Création d'une nouvelle ligne dans le tableau
            let tbody = document.querySelector('#tableEcritures tbody');
            if (!tbody) {
                tbody = document.createElement('tbody');
                document.getElementById('tableEcritures').appendChild(tbody);
            }

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${date.value}</td>
                <td data-compte-id="${compteGeneral.value}">${compteGeneralText}</td>
                <td data-tiers-id="${compteTiersId}">${compteTiersText}</td>
                <td>${libelle.value}</td>
                <td class="text-end">${debit.value ? parseFloat(debit.value).toFixed(2).replace('.', ',') : '0,00'}</td>
                <td class="text-end">${credit.value ? parseFloat(credit.value).toFixed(2).replace('.', ',') : '0,00'}</td>
                <td class="text-nowrap text-center">
                    <button type="button" class="btn btn-sm btn-primary me-1" onclick="modifierEcriture(this.closest('tr'))">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="supprimerEcriture(this.closest('tr'))">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);

            // Réinitialisation du formulaire
            libelle.value = '';
            if (compteGeneral.selectedIndex > 0) compteGeneral.selectedIndex = 0;
            if (compteTiers && compteTiers.selectedIndex > 0) compteTiers.selectedIndex = 0;
            debit.value = '';
            credit.value = '';
            if (referencePiece) referencePiece.value = '';
            if (pieceFile) pieceFile.value = '';

            // Mise à jour des totaux
            updateTotals();

            // Défilement vers le bas du tableau
            newRow.scrollIntoView({ behavior: 'smooth' });

            // Message de succès
            showAlert('Écriture ajoutée avec succès !', 'success');

        } catch (error) {
            console.error('Erreur lors de l\'ajout de l\'écriture :', error);
            showAlert('Une erreur est survenue lors de l\'ajout de l\'écriture : ' + error.message, 'error');
        }
    }

    // Fonction utilitaire pour afficher des messages
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed bottom-0 end-0 m-3`;
        alertDiv.role = 'alert';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <i class="${type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Supprimer le message après 3 secondes
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
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

            // On ne génère pas de nouveau numéro ici, il ne changera qu'à l'enregistrement

            // Mise à jour des totaux
            updateTotals();

            alert('Écriture ajoutée avec succès !');

        } catch (error) {
            console.error('Erreur lors de l\'ajout de l\'écriture:', error);
            alert('Une erreur est survenue: ' + error.message);
        }
    }

    // Fonction pour formater les nombres avec séparateurs de milliers
    function formatNumber(number) {
        return number.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    // Fonction pour mettre à jour les totaux
    function updateTotals() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) return;

        let totalDebit = 0;
        let totalCredit = 0;

        // Parcourir chaque ligne du tableau pour calculer les totaux
        const rows = tbody.getElementsByTagName('tr');
        for (let row of rows) {
            const cells = row.getElementsByTagName('td');
            if (cells.length >= 6) {
                // Récupération des valeurs débit et crédit
                const debit = parseFloat(cells[4].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
                const credit = parseFloat(cells[5].textContent.replace(/\s/g, '').replace(',', '.')) || 0;

                totalDebit += debit;
                totalCredit += credit;
            }
        }

        // Mise à jour de l'affichage des totaux
        const totalDebitElement = document.getElementById('totalDebit');
        const totalCreditElement = document.getElementById('totalCredit');
        const totalDebitFooter = document.getElementById('totalDebitFooter');
        const totalCreditFooter = document.getElementById('totalCreditFooter');
        const ecartElement = document.getElementById('ecart');

        const formattedDebit = formatNumber(totalDebit);
        const formattedCredit = formatNumber(totalCredit);
        const ecart = Math.abs(totalDebit - totalCredit);
        
        if (totalDebitElement) totalDebitElement.textContent = formattedDebit;
        if (totalCreditElement) totalCreditElement.textContent = formattedCredit;
        if (totalDebitFooter) totalDebitFooter.textContent = formattedDebit + ' €';
        if (totalCreditFooter) totalCreditFooter.textContent = formattedCredit + ' €';

        // Mise à jour de l'affichage de l'écart
        if (ecartElement) {
            if (ecart > 0.01) { // Tolérance pour les erreurs d'arrondi
                ecartElement.innerHTML = `<span class="badge bg-danger">Déséquilibre: ${formatNumber(ecart)} €</span>`;
            } else {
                ecartElement.innerHTML = '<span class="badge bg-success">Équilibre</span>';
            }
        }
    }

    // Fonction pour supprimer une écriture
    function supprimerEcriture(row) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette écriture ?')) {
            row.remove();
            updateTotals();
            showAlert('Écriture supprimée avec succès !', 'success');
        }
    }

    // Fonction pour modifier une écriture
    function modifierEcriture(row) {
        // Récupérer les valeurs de la ligne
        const cells = row.getElementsByTagName('td');
        const date = cells[0].textContent;
        const compteGeneralId = cells[1].getAttribute('data-compte-id');
        const compteTiersId = cells[2].getAttribute('data-tiers-id');
        const libelle = cells[3].textContent;
        const debit = cells[4].textContent.replace(/\s/g, '').replace(',', '.');
        const credit = cells[5].textContent.replace(/\s/g, '').replace(',', '.');

        // Mettre à jour le formulaire avec les valeurs de la ligne
        document.getElementById('date').value = date;
        document.getElementById('description_operation').value = libelle;
        
        const compteGeneral = document.getElementById('compte_general');
        if (compteGeneral) {
            compteGeneral.value = compteGeneralId;
            // Déclencher l'événement change pour mettre à jour les comptes tiers si nécessaire
            const event = new Event('change');
            compteGeneral.dispatchEvent(event);
        }
        
        // Mettre à jour le compte tiers après un court délai pour laisser le temps au changement de compte général
        setTimeout(() => {
            const compteTiers = document.getElementById('compte_tiers');
            if (compteTiers) {
                compteTiers.value = compteTiersId || '';
            }
        }, 100);
        
        document.getElementById('debit').value = parseFloat(debit) > 0 ? debit : '';
        document.getElementById('credit').value = parseFloat(credit) > 0 ? credit : '';

        // Supprimer la ligne du tableau
        row.remove();
        
        // Mettre à jour les totaux
        updateTotals();
        
        // Afficher un message
        showAlert('Écriture chargée pour modification', 'info');
        
        // Faire défiler vers le haut du formulaire
        document.querySelector('.card-body').scrollIntoView({ behavior: 'smooth' });
    }
    }

    // Fonction pour générer un nouveau numéro de saisie
    function generateNewSaisieNumber() {
        const nSaisieField = document.getElementById('n_saisie');
        if (nSaisieField) {
            // Incrémenter le numéro de saisie actuel
            let currentNumber = parseInt(nSaisieField.value || '0', 10);
            // Si le numéro est trop grand, on recommence à 1
            currentNumber = currentNumber >= 999999999999 ? 1 : currentNumber + 1;
            // Mettre à jour le champ avec le nouveau numéro (formaté sur 12 chiffres)
            nSaisieField.value = currentNumber.toString().padStart(12, '0');
            return nSaisieField.value;
        }
        return '';
    }

    // Fonction pour enregistrer les écritures
    function enregistrerEcritures() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) {
            alert('Aucun tableau d\'écritures trouvé.');
            return;
        }

        const rows = tbody.getElementsByTagName('tr');
        if (rows.length === 0) {
            alert('Aucune écriture à enregistrer.');
            return;
        }

        // Récupérer les données du formulaire principal
        const formData = new FormData(document.getElementById('formEcriture'));
        const ecritures = [];
        const nSaisie = document.getElementById('n_saisie').value;

        // Parcourir chaque ligne du tableau
        Array.from(rows).forEach(row => {
            const cells = row.getElementsByTagName('td');
            if (cells.length >= 7) { // Vérifier qu'on a suffisamment de colonnes
                const debit = parseFloat(cells[4].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
                const credit = parseFloat(cells[5].textContent.replace(/\s/g, '').replace(',', '.')) || 0;

                ecritures.push({
                    date: formData.get('date'),
                    n_saisie: nSaisie,
                    description: cells[3].textContent.trim(),
                    reference: '', // À adapter si vous avez une colonne référence
                    compte_general: cells[1].getAttribute('data-compte-id'),
                    compte_tiers: cells[2].getAttribute('data-tiers-id') || null,
                    debit: debit,
                    credit: credit,
                    journal: formData.get('code_journal_id'),
                    exercices_comptables_id: '{{ $data['id_exercice'] ?? 1 }}',
                    journaux_saisis_id: '{{ $data['id_code'] ?? null }}',
                    typeFlux: 'operationnelles', // Valeur par défaut
                    analytique: 'Non' // Valeur par défaut
                });
            }
        });

        // Envoyer les données au serveur
        fetch('{{ route("ecriture.store.multiple") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ecritures: ecritures })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Générer un nouveau numéro de saisie pour la prochaine fois
            generateNewSaisieNumber();
            
            // Vider le tableau
            tbody.innerHTML = '';
            updateTotals();
            
            alert('Écritures enregistrées avec succès !');
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'enregistrement : ' + error.message);
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
