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

                          <div class="row g-3">
                              <div class="col-md-2">
                                  <label for="dateEcriture" class="form-label">Date</label>
                                  <input type="date" id="dateEcriture" name="date" class="form-control" required />
                              </div>
                              <div class="col-md-2">
                                  <label for="numeroSaisie" class="form-label">N° Saisie</label>
                                  <input type="text" id="numeroSaisie" name="numero_saisie" class="form-control" readonly />
                              </div>
                              <div class="col-md-3">
                                  <label for="journalEcriture" class="form-label">Journal</label>
                                  <input type="text" id="journalEcriture" name="journal" class="form-control" readonly />
                              </div>
                              <div class="col-md-5">
                                  <label for="libelleEcriture" class="form-label">Libellé</label>
                                  <input type="text" id="libelleEcriture" name="libelle" class="form-control" required />
                              </div>
                              <div class="col-md-2">
                                  <label for="referencePieceEcriture" class="form-label">Référence Pièce</label>
                                  <input type="text" id="referencePieceEcriture" name="reference_piece" class="form-control" />
                              </div>
                              <div class="col-md-3">
                                  <label for="compteGeneralEcriture" class="form-label">Compte Général</label>
                                  <select id="compteGeneralEcriture" name="compte_general" class="form-select" required>
                                      <option value="">Sélectionner...</option>
                                      @if(isset($plansComptables))
                                          @foreach ($plansComptables as $plan)
                                              <option value="{{ $plan->id }}">{{ $plan->numero_de_compte }} - {{ $plan->intitule }}</option>
                                          @endforeach
                                      @endif
                                  </select>
                              </div>
                              <div class="col-md-3">
                                  <label for="compteTiersEcriture" class="form-label">Compte Tiers</label>
                                  <select id="compteTiersEcriture" name="compte_tiers" class="form-select">
                                      <option value="">Aucun</option>
                                      @if(isset($tiers))
                                          @foreach ($tiers as $tier)
                                              <option value="{{ $tier->id }}">{{ $tier->intitule }}</option>
                                          @endforeach
                                      @endif
                                  </select>
                              </div>
                              <div class="col-md-2">
                                  <label for="debitEcriture" class="form-label">Débit</label>
                                  <input type="number" id="debitEcriture" name="debit" class="form-control" step="0.01" min="0" />
                              </div>
                              <div class="col-md-2">
                                  <label for="creditEcriture" class="form-label">Crédit</label>
                                  <input type="number" id="creditEcriture" name="credit" class="form-control" step="0.01" min="0" />
                              </div>
                              <div class="col-md-3">
                                  <label for="planAnalytiqueEcriture" class="form-label">Plan analytique</label>
                                  <select id="planAnalytiqueEcriture" name="plan_analytique" class="form-select">
                                      <option value="0">Non</option>
                                      <option value="1">Oui</option>
                                  </select>
                              </div>
                              <div class="col-md-12">
                                  <label for="pieceJustificativeEcriture" class="form-label">Pièce justificative</label>
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
</script>
