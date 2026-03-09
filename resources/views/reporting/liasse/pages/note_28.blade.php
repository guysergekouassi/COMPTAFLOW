<div class="card shadow-none border-0">
    <div class="card-header bg-label-warning py-3 mb-4">
        <h5 class="mb-0 text-warning fw-bold text-uppercase"><i class="bx bx-user-check me-2"></i> NOTE 28 : EFFECTIFS, MASSE SALARIALE ET PERSONNEL EXTERIEUR</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center border-bottom-0">
                    <tr>
                        <th rowspan="2" style="width: 250px;">Catégories</th>
                        <th colspan="2">Effectifs</th>
                        <th rowspan="2">Masse salariale</th>
                    </tr>
                    <tr class="text-center">
                        <th>Hommes</th>
                        <th>Femmes</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'cadres' => 'Cadres et Direction',
                            'agents_maitrise' => 'Agents de maîtrise',
                            'employes' => 'Employés',
                            'ouvriers' => 'Ouvriers',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" class="form-control form-control-sm text-center" name="{{ $code }}_h" value="{{ $data[$code.'_h'] ?? 0 }}"></td>
                        <td><input type="number" class="form-control form-control-sm text-center" name="{{ $code }}_f" value="{{ $data[$code.'_f'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_masse" value="{{ $data[$code.'_masse'] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
