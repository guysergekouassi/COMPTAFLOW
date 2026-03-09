<div class="card shadow-none border-0">
    <div class="card-header bg-label-success py-3 mb-4">
        <h5 class="mb-0 text-success fw-bold text-uppercase"><i class="bx bx-group me-2"></i> NOTE 19 : PERSONNEL</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Libellé</th>
                        <th>Dettes (Passif)</th>
                        <th>Créances (Actif)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'salaires_dus' => 'Rémunérations dues (Salaires)',
                            'conges_payes' => 'Provisions pour congés payés',
                            'organismes_soc' => 'Organismes sociaux (CNPS, etc.)',
                            'opp_saisie' => 'Oppositions et saisies-arrêts',
                            'avances_acomptes' => 'Avances et acomptes au personnel',
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
