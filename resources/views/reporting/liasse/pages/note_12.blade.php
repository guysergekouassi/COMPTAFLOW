<div class="card shadow-none border-0">
    <div class="card-header bg-label-secondary py-3 mb-4">
        <h5 class="mb-0 text-secondary fw-bold text-uppercase"><i class="bx bx-transfer me-2"></i> NOTE 12 : ECARTS DE CONVERSION</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des écarts</th>
                        <th>Actif (Augmentation Créance / Diminution Dette)</th>
                        <th>Passif (Diminution Créance / Augmentation Dette)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'immo_fin' => 'Immobilisations financières',
                            'creances' => 'Créances circulant',
                            'disponibilites' => 'Disponibilités',
                            'dettes_fin' => 'Dettes financières',
                            'dettes_circ' => 'Dettes circulant',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_actif" value="{{ $data[$code.'_actif'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_passif" value="{{ $data[$code.'_passif'] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
