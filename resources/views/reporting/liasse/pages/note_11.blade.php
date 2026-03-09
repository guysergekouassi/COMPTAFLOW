<div class="card shadow-none border-0">
    <div class="card-header bg-label-primary py-3 mb-4">
        <h5 class="mb-0 text-primary fw-bold text-uppercase"><i class="bx bx-wallet me-2"></i> NOTE 11 : DISPONIBILITES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des disponibilités</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'banques' => 'Banques',
                            'cheques_postaux' => 'Chèques postaux',
                            'caisse' => 'Caisse',
                            'regies_avances' => 'Régies d\'avances et accréditifs',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}" value="{{ $data[$code] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                    <tr class="table-secondary fw-bold">
                        <td>TOTAL DISPONIBILITES</td>
                        <td class="text-end px-2">
                             {{ number_format(
                                ($data['banques'] ?? 0) + 
                                ($data['cheques_postaux'] ?? 0) + 
                                ($data['caisse'] ?? 0) + 
                                ($data['regies_avances'] ?? 0), 0, ',', ' '
                            ) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
