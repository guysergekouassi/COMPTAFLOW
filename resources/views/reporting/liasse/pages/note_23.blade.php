<div class="card shadow-none border-0">
    <div class="card-header bg-label-warning py-3 mb-4">
        <h5 class="mb-0 text-warning fw-bold text-uppercase"><i class="bx bx-receipt me-2"></i> NOTE 23 : IMPOTS ET TAXES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Nature des impôts et taxes</th>
                        <th>Montant de l'exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'impots_directs' => 'Impôts directs (Contribution des patentes...)',
                            'impots_indirects' => 'Impôts indirects',
                            'taxes_salariales' => 'Taxes salariales (à la charge de l\'employeur)',
                            'droits_enregistrement' => 'Droits d\'enregistrement et de timbre',
                            'autres_impots' => 'Autres impôts et taxes',
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
