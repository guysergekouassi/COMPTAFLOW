<div class="card shadow-none border-0">
    <div class="card-header bg-label-success py-3 mb-4">
        <h5 class="mb-0 text-success fw-bold text-uppercase"><i class="bx bx-factory me-2"></i> NOTE 29 : PRODUCTION DE L'EXERCICE</h5>
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
                            'ventes_prod_finis' => 'Ventes de produits finis',
                            'ventes_prod_interm' => 'Ventes de produits intermédiaires',
                            'travaux_serv_vendus' => 'Travaux et services vendus',
                            'prod_stockee' => 'Production stockée (Variation des stocks)',
                            'prod_immo' => 'Production immobilisée',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}" value="{{ $data[$code] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                    <tr class="table-success fw-bold">
                        <td>TOTAL PRODUCTION DE L'EXERCICE</td>
                        <td class="text-end px-2">
                             {{ number_format(
                                ($data['ventes_prod_finis'] ?? 0) + 
                                ($data['ventes_prod_interm'] ?? 0) + 
                                ($data['travaux_serv_vendus'] ?? 0) + 
                                ($data['prod_stockee'] ?? 0) + 
                                ($data['prod_immo'] ?? 0), 0, ',', ' '
                            ) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
