<div class="card shadow-none border-0">
    <div class="card-header bg-label-danger py-3 mb-4">
        <h5 class="mb-0 text-danger fw-bold text-uppercase"><i class="bx bx-hard-hat me-2"></i> NOTE 24 : CHARGES DE PERSONNEL</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Libellé</th>
                        <th>Montant de l'exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'salaires_nationaux' => 'Salaires et traitements (Nationaux)',
                            'salaires_expatries' => 'Salaires et traitements (Expatriés)',
                            'charges_sociales' => 'Charges sociales (Employeur)',
                            'indemnites' => 'Indemnités et primes diverses',
                            'personnel_externe' => 'Personnel extérieur',
                            'autres_charges' => 'Autres charges de personnel',
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
