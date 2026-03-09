<div class="card shadow-none border-0">
    <div class="card-header bg-label-warning py-3 mb-4">
        <h5 class="mb-0 text-warning fw-bold text-uppercase"><i class="bx bx-cart-alt me-2"></i> NOTE 30 : ACHATS DESTINES A LA PRODUCTION</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Libellé</th>
                        <th>Montant de l'exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'achats_mat_prem' => 'Achats de matières premières',
                            'achats_fourn_liees' => 'Achats de fournitures liées',
                            'var_stocks_mat_prem' => 'Variation des stocks (Matières premières)',
                            'var_stocks_fourn' => 'Variation des stocks (Fournitures)',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}" value="{{ $data[$code] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
