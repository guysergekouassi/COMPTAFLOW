<div class="card shadow-none border-0">
    <div class="card-header bg-label-danger py-3 mb-4">
        <h5 class="mb-0 text-danger fw-bold text-uppercase"><i class="bx bx-bolt-circle me-2"></i> NOTE 27 : CHARGES ET PRODUITS H.A.O.</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Nature des charges et produits HAO</th>
                        <th>Charges (81, 83...)</th>
                        <th>Produits (82, 84...)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'valeur_vnc' => 'Valeur nette comptable des immo. cédées',
                            'produits_cession' => 'Produits de cession d\'immobilisations',
                            'abandons' => 'Abandons de créances / Dettes',
                            'autres' => 'Autres charges et produits H.A.O.',
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
