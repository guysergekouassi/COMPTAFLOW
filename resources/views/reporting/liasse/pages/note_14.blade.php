<div class="card shadow-none border-0">
    <div class="card-header bg-label-info py-3 mb-4">
        <h5 class="mb-0 text-info fw-bold text-uppercase"><i class="bx bx-gift me-2"></i> NOTE 14 : SUBVENTIONS D'INVESTISSEMENT</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des subventions</th>
                        <th>Montant brut</th>
                        <th>Amortissements (Reprise en résultat)</th>
                        <th>Valeur nette</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'etat' => 'Subventions d\'État',
                            'collectivites' => 'Subventions des collectivités publiques',
                            'autres' => 'Autres subventions d\'investissement',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_brut" value="{{ $data[$code.'_brut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_amort" value="{{ $data[$code.'_amort'] ?? 0 }}"></td>
                        <td class="bg-light text-end fw-bold px-2">
                             {{ number_format(($data[$code.'_brut'] ?? 0) - ($data[$code.'_amort'] ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
