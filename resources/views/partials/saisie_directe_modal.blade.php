<!-- Modal de saisie directe -->
<div class="modal fade" id="saisieDirecteModal" tabindex="-1" aria-labelledby="saisieDirecteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saisieDirecteModalLabel">
                    <i class="fas fa-edit"></i> Saisie Directe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="saisieDirecteForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="n_saisie" class="form-label">Numéro de saisie</label>
                                <input type="text" class="form-control" id="n_saisie" name="n_saisie" value="{{ $nextSaisieNumber ?? '000000000001' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_ecriture" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date_ecriture" name="date_ecriture" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code_journal" class="form-label">Journal</label>
                                <select class="form-select" id="code_journal" name="code_journal" required>
                                    <option value="">Sélectionner un journal</option>
                                    @if(isset($code_journaux))
                                        @foreach($code_journaux as $journal)
                                            <option value="{{ $journal->id }}">{{ $journal->code }} - {{ $journal->intitule }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference" class="form-label">Référence</label>
                                <input type="text" class="form-control" id="reference" name="reference" placeholder="Numéro de pièce">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="libelle" class="form-label">Libellé</label>
                                <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé de l'écriture" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="plan_tiers_id" class="form-label">Tiers</label>
                                <select class="form-select" id="plan_tiers_id" name="plan_tiers_id">
                                    <option value="">Sélectionner un tiers</option>
                                    @if(isset($plansTiers))
                                        @foreach($plansTiers as $tiers)
                                            <option value="{{ $tiers->id }}">{{ $tiers->numero_de_tiers }} - {{ $tiers->intitule }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau des écritures -->
                    <div class="mb-3">
                        <label class="form-label">Détail des écritures</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="ecrituresTable">
                                <thead>
                                    <tr>
                                        <th>Compte</th>
                                        <th>Libellé</th>
                                        <th>Débit</th>
                                        <th>Crédit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-select form-select-sm compte-select" name="comptes[]">
                                                <option value="">Sélectionner</option>
                                                @if(isset($plansComptables))
                                                    @foreach($plansComptables as $compte)
                                                        <option value="{{ $compte->id }}">{{ $compte->numero_de_compte }} - {{ $compte->intitule }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm" name="libelles[]" placeholder="Libellé"></td>
                                        <td><input type="number" class="form-control form-control-sm debit-input" name="debits[]" step="0.01" min="0"></td>
                                        <td><input type="number" class="form-control form-control-sm credit-input" name="credits[]" step="0.01" min="0"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Totaux</th>
                                        <th id="totalDebit">0.00</th>
                                        <th id="totalCredit">0.00</th>
                                        <th><button type="button" class="btn btn-sm btn-success" id="addRow"><i class="fas fa-plus"></i></button></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <input type="hidden" name="exercice_id" value="{{ $exerciceActif->id ?? '' }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveSaisieDirecte">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Ajouter une ligne
    $('#addRow').click(function() {
        var newRow = `
            <tr>
                <td>
                    <select class="form-select form-select-sm compte-select" name="comptes[]">
                        <option value="">Sélectionner</option>
                        @if(isset($plansComptables))
                            @foreach($plansComptables as $compte)
                                <option value="{{ $compte->id }}">{{ $compte->numero_de_compte }} - {{ $compte->intitule }}</option>
                            @endforeach
                        @endif
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm" name="libelles[]" placeholder="Libellé"></td>
                <td><input type="number" class="form-control form-control-sm debit-input" name="debits[]" step="0.01" min="0"></td>
                <td><input type="number" class="form-control form-control-sm credit-input" name="credits[]" step="0.01" min="0"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#ecrituresTable tbody').append(newRow);
    });

    // Supprimer une ligne
    $(document).on('click', '.remove-row', function() {
        if ($('#ecrituresTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });

    // Calculer les totaux
    function calculateTotals() {
        var totalDebit = 0;
        var totalCredit = 0;

        $('.debit-input').each(function() {
            totalDebit += parseFloat($(this).val()) || 0;
        });

        $('.credit-input').each(function() {
            totalCredit += parseFloat($(this).val()) || 0;
        });

        $('#totalDebit').text(totalDebit.toFixed(2));
        $('#totalCredit').text(totalCredit.toFixed(2));
    }

    $(document).on('input', '.debit-input, .credit-input', function() {
        calculateTotals();
    });

    // Enregistrer la saisie
    $('#saveSaisieDirecte').click(function() {
        var formData = $('#saisieDirecteForm').serialize();
        
        $.ajax({
            url: '/ecritures-comptables/store-multiple',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#saisieDirecteModal').modal('hide');
                    location.reload();
                } else {
                    alert('Erreur: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Erreur: ' + xhr.responseJSON.message);
            }
        });
    });
});
</script>
