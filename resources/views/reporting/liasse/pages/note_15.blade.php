<div class="card shadow-none border-0">
    <div class="card-header bg-label-danger py-3 mb-4">
        <h5 class="mb-0 text-danger fw-bold text-uppercase"><i class="bx bx-error-circle me-2"></i> NOTE 15 : PROVISIONS POUR RISQUES ET CHARGES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Nature des provisions</th>
                        <th>Montant début exercice</th>
                        <th>Dotations de l'exercice</th>
                        <th>Reprises de l'exercice</th>
                        <th>Montant fin exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'litiges' => 'Provisions pour litiges',
                            'garanties' => 'Provisions pour garanties donées aux clients',
                            'pertes_change' => 'Provisions pour pertes de change',
                            'impots' => 'Provisions pour impôts',
                            'pensions' => 'Provisions pour pensions et oblig. similaires',
                            'autres' => 'Autres provisions pour risques et charges',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_debut" value="{{ $data[$code.'_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_dot" value="{{ $data[$code.'_dot'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_rep" value="{{ $data[$code.'_rep'] ?? 0 }}"></td>
                        <td class="bg-light text-end fw-bold px-2">
                             {{ number_format(($data[$code.'_debut'] ?? 0) + ($data[$code.'_dot'] ?? 0) - ($data[$code.'_rep'] ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
