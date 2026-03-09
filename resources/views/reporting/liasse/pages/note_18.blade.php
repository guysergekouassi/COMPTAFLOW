<div class="card shadow-none border-0">
    <div class="card-header bg-label-warning py-3 mb-4">
        <h5 class="mb-0 text-warning fw-bold text-uppercase"><i class="bx bx-receipt me-2"></i> NOTE 18 : FISCALITE</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des dettes / créances fiscales</th>
                        <th>Dettes (Passif)</th>
                        <th>Créances (Actif)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'ib_is' => 'Impôt sur les bénéfices (IS)',
                            'tva_due_ded' => 'TVA due / TVA déductible',
                            'retenues_source' => 'Retenues à la source',
                            'taxes_sal' => 'Taxes sur les salaires',
                            'autres_taxes' => 'Autres impôts et taxes',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_dette" value="{{ $data[$code.'_dette'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_creance" value="{{ $data[$code.'_creance'] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
