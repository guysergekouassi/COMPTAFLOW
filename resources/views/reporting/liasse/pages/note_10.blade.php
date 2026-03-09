<div class="card shadow-none border-0">
    <div class="card-header bg-label-secondary py-3 mb-4">
        <h5 class="mb-0 text-secondary fw-bold text-uppercase"><i class="bx bx-money me-2"></i> NOTE 10 : VALEURS A ENCAISSER</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des valeurs</th>
                        <th>Montant au début de l'exercice</th>
                        <th>Valeurs reçues</th>
                        <th>Valeurs encaissées / remises</th>
                        <th>Montant en fin d'exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'cheques' => 'Chèques à encaisser',
                            'effets' => 'Effets à encaisser / à vue',
                            'coupons' => 'Coupons à encaisser',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_debut" value="{{ $data[$code.'_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_recues" value="{{ $data[$code.'_recues'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_encaissees" value="{{ $data[$code.'_encaissees'] ?? 0 }}"></td>
                        <td class="bg-light text-end fw-bold px-2">
                             {{ number_format(($data[$code.'_debut'] ?? 0) + ($data[$code.'_recues'] ?? 0) - ($data[$code.'_encaissees'] ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
