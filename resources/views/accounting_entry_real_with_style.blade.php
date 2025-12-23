@extends('components.layout')

@section('title', 'Listing des ecritures du journal')

@section('content')
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

<!-- Modal d'alerte déséquilibre débit/crédit -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title w-100" id="alertModalLabel">Erreur de validation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Le total des débits doit être égal au total des crédits.</p>
                <p>Veuillez corriger votre saisie avant de continuer.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de succès -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title w-100" id="successModalLabel">Succès</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage">Opération réussie !</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal principal pour la saisie des écritures -->
<div class="modal fade modal-xl-custom" id="modalCenterCreate" tabindex="-1"
    aria-labelledby="modalCenterCreateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCenterCreateLabel">Nouvelle saisie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form id="formEcriture">
                    <input type="hidden" name="exercice_id" value="{{ $data['id_exercice'] ?? 'N/A' }}"
                        class="form-control" />

                    <input type="hidden" name="journaux_saisis_id" id="journaux_saisis_id"
                        value="{{ $data['id_journal'] ?? 'N/A' }}" class="form-control" />

                    <div class="row g-3">
                        <!-- Date -->
                        <div class="col-md-2">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" id="date" name="date" class="form-control" required />
                            <div class="invalid-feedback">Veuillez renseigner la date.</div>
                        </div>

                        <!-- N° Saisie -->
                        <div class="col-md-2">
                            <label for="n_saisie" class="form-label">N° Saisie</label>
                            <input type="text" id="n_saisie" name="n_saisie" class="form-control" />
                            <div class="invalid-feedback">Veuillez renseigner le numéro de saisie.</div>
                        </div>

                        <!-- Imputation (Journal) -->
                        <div class="col-md-4">
                            <label for="imputation" class="form-label">Imputation (Journal)</label>
                            <input type="text" class="form-control" placeholder="{{ $data['code'] ?? 'N/A' }}" readonly />

                            <input type="hidden" id="imputation" name="code_journal_id"
                                value="{{ $data['id_code'] ?? 'N/A' }}" class="form-control"
                                data-code_imputation="{{ $data['code'] }}" />

                        </div>

                        <!-- Description de l'opération -->
                        <div class="col-md-6">
                            <label for="description_operation" class="form-label">Description de l'opération</label>
                            <input type="text" id="description_operation" name="description_operation"
                                class="form-control" required />
                            <div class="invalid-feedback">Veuillez entrer la description.</div>
                        </div>

                        <!-- Référence Pièce -->
                        <div class="col-md-3">
                            <label for="reference_piece" class="form-label">Référence Pièce</label>
                            <input type="text" id="reference_piece" name="reference_piece" class="form-control" />
                        </div>

                        <!-- Compte Général -->
                        <div class="col-md-3">
                            <label for="compte_general" class="form-label">Compte Général</label>
                            <select id="compte_general" name="compte_general"
                                class="form-control w-100" data-live-search="true"
                                title="Selectionner" required>
                                <option value="" selected disabled>Sélectionner un compte</option>
                                @foreach ($plansComptables as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-intitule_compte_general="{{ $plan->numero_de_compte }}">
                                        {{ $plan->numero_de_compte }} -
                                        {{ $plan->intitule }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Compte Tiers -->
                        <div class="col-md-3">
                            <label for="compte_tiers" class="form-label">Compte Tiers</label>
                            <select id="compte_tiers" name="compte_tiers" class="form-control w-100"
                                data-live-search="true">
                                <option value="">Sélectionner un compte tiers</option>
                                @foreach ($tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->intitule }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Débit -->
                        <div class="col-md-2">
                            <label for="debit" class="form-label">Débit</label>
                            <input type="number" id="debit" name="debit" class="form-control" step="0.01"
                                min="0" />
                        </div>

                        <!-- Crédit -->
                        <div class="col-md-2">
                            <label for="credit" class="form-label">Crédit</label>
                            <input type="number" id="credit" name="credit" class="form-control" step="0.01"
                                min="0" />
                        </div>

                        <!-- Plan Analytique -->
                        <div class="col-md-3">
                            <label for="plan_analytique" class="form-label">Plan Analytique</label>
                            <select id="plan_analytique" name="plan_analytique"
                                class="form-control w-100" required>
                                <option value="1">Oui</option>
                                <option value="0" selected>Non</option>
                            </select>
                        </div>

                        <!-- Pièce justificative -->
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
                    <i class="bx bx-plus me-1"></i> Nouvelle saisie
                </button>
            @endif
        </div>
    </div>
    <div class="card-body">

        <!-- Panel de filtres -->
        <div class="collapse" id="filterPanel">
            <div class="card card-body mb-3">
                <form method="GET" action="{{ route('compta.dashboard') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filter_exercice" class="form-label">Exercice</label>
                            <select name="exercice_id" id="filter_exercice" class="form-select">
                                @foreach ($exercices as $exerciceItem)
                                    <option value="{{ $exerciceItem->id }}"
                                        {{ $exerciceItem->id == $exercice->id ? 'selected' : '' }}>
                                        {{ $exerciceItem->annee }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_mois" class="form-label">Mois</label>
                            <select name="mois" id="filter_mois" class="form-select">
                                <option value="">Tous les mois</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == $data['mois'] ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_journal" class="form-label">Journal</label>
                            <select name="journal_id" id="filter_journal" class="form-select">
                                <option value="">Tous les journaux</option>
                                @foreach ($journaux as $journal)
                                    <option value="{{ $journal->id }}"
                                        {{ $journal->id == $data['id_code'] ? 'selected' : '' }}>
                                        {{ $journal->code }} - {{ $journal->intitule }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search me-1"></i> Rechercher
                            </button>
                            <a href="{{ route('compta.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tableau des écritures existantes -->
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
                        <th>Pièce justificatif</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        $currentColor = 0;
                        $colors = ['#fff', '#f8f9fa', '#e9ecef', '#dee2e6'];
                        $rowClass = '';
                    @endphp

                    @foreach ($ecritures as $ecriture)
                        @php
                            $totalDebit += $ecriture->debit;
                            $totalCredit += $ecriture->credit;

                            // Changer de couleur tous les 3 écritures
                            if ($ecriture->n_saisie != $previous_saisie) {
                                $currentColor = ($currentColor + 1) % count($colors);
                                $rowClass = $colors[$currentColor];
                            }
                            $previous_saisie = $ecriture->n_saisie;
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
                <tfoot>
                    <tr class="table-active fw-bold">
                        <td colspan="7" class="text-end">TOTAL</td>
                        <td>
                            {{ fmod($totalDebit, 1) == 0 ? number_format($totalDebit, 0, ',', ' ') : number_format($totalDebit, 2, ',', ' ') }}
                        </td>
                        <td>
                            {{ fmod($totalCredit, 1) == 0 ? number_format($totalCredit, 0, ',', ' ') : number_format($totalCredit, 2, ',', ' ') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
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
    });
</script>
@endsection
