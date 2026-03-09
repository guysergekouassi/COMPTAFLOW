<div class="card shadow-none border-0">
    <div class="card-header bg-label-primary py-3 mb-4">
        <h5 class="mb-0 text-primary fw-bold text-uppercase"><i class="bx bx-trending-up me-2"></i> NOTE 26 : CHARGES ET PRODUITS FINANCIERS</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Nature des charges et produits financiers</th>
                        <th>Charges</th>
                        <th>Produits</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'interets' => 'Intérêts et charges/produits assimilés',
                            'escomptes' => 'Escomptes accordés / obtenus',
                            'pertes_gains_change' => 'Pertes et gains de change',
                            'charges_prod_titres' => 'Charges et produits nets sur titres',
                            'autres' => 'Autres charges et produits financiers',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_charges" value="{{ $data[$code.'_charges'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_produits" value="{{ $data[$code.'_produits'] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
