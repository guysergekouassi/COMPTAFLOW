<div class="card shadow-none border-0">
    <div class="card-header bg-label-info py-3 mb-4">
        <h5 class="mb-0 text-info fw-bold text-uppercase"><i class="bx bx-receipt me-2"></i> NOTE 33 : TABLEAU RECAPITULATIF DES TAXES SUR LE CHIFFRE D'AFFAIRES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Libellé</th>
                        <th>Base imposable</th>
                        <th>Taux (%)</th>
                        <th>Montant de la taxe</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'tva_18' => 'TVA au taux normal (18%)',
                            'tva_9' => 'TVA au taux réduit (9%)',
                            'exo' => 'Opérations exonérées',
                            'export' => 'Exportations',
                            'autres_taxes_ca' => 'Autres taxes sur le CA / TSE',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_base" value="{{ $data[$code.'_base'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-center" name="{{ $code }}_taux" value="{{ $data[$code.'_taux'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_taxe" value="{{ $data[$code.'_taxe'] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
