<div class="card shadow-none border-0">
    <div class="card-header bg-label-primary py-3 mb-4">
        <h5 class="mb-0 text-primary fw-bold text-uppercase"><i class="bx bx-dollar me-2"></i> NOTE 21 : CHIFFRE D'AFFAIRES ET AUTRES PRODUITS</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des produits</th>
                        <th>Ventes en Côte d'Ivoire</th>
                        <th>Ventes Hors Côte d'Ivoire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'ventes_march' => 'Ventes de marchandises',
                            'prod_vendue_biens' => 'Production vendue (Biens)',
                            'prod_vendue_serv' => 'Production vendue (Services)',
                            'travaux_fact' => 'Travaux facturés',
                            'produits_access' => 'Produits accessoires',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_loc" value="{{ $data[$code.'_loc'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_exp" value="{{ $data[$code.'_exp'] ?? 0 }}"></td>
                        <td class="bg-light text-end fw-bold px-2">
                             {{ number_format(($data[$code.'_loc'] ?? 0) + ($data[$code.'_exp'] ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
