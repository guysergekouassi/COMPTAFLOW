<div class="card shadow-none border-0">
    <div class="card-header bg-label-info py-3 mb-4">
        <h5 class="mb-0 text-info fw-bold text-uppercase"><i class="bx bx-purchase-tag me-2"></i> NOTE 22 : ACHATS ET AUTRES CHARGES EXTERNES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Nature des charges</th>
                        <th>Montant de l'exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'achats_march' => 'Achats de marchandises',
                            'achats_mat_prem' => 'Achats de matières premières et fournitures',
                            'achats_fourn_non_stock' => 'Achats de fournitures non stockables (Eau, Elec...)',
                            'transports' => 'Transports',
                            'services_ext_1' => 'Services extérieurs A (Loyers, Entretien...)',
                            'services_ext_2' => 'Services extérieurs B (Honoraires, Publicité...)',
                            'frais_divers' => 'Autres charges externes',
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
