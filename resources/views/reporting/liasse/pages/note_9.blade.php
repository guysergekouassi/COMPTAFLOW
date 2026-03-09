<div class="card shadow-none border-0">
    <div class="card-header bg-label-warning py-3 mb-4">
        <h5 class="mb-0 text-warning fw-bold text-uppercase"><i class="bx bx-star me-2"></i> NOTE 9 : TITRES DE PLACEMENT</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des titres</th>
                        <th>Valeur d'acquisition</th>
                        <th>Valeur boursière / d'inventaire</th>
                        <th>Dépréciations</th>
                        <th>Valeur nette</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'actions' => 'Actions',
                            'obligations' => 'Obligations',
                            'bons_tresor' => 'Bons du Trésor',
                            'autres' => 'Autres titres de placement',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_acq" value="{{ $data[$code.'_acq'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_inv" value="{{ $data[$code.'_inv'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_dep" value="{{ $data[$code.'_dep'] ?? 0 }}"></td>
                        <td class="bg-light text-end fw-bold px-2">
                             {{ number_format(($data[$code.'_acq'] ?? 0) - ($data[$code.'_dep'] ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
