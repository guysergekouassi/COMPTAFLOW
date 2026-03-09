<div class="card shadow-none border-0">
    <div class="card-header bg-label-secondary py-3 mb-4">
        <h5 class="mb-0 text-secondary fw-bold text-uppercase"><i class="bx bx-layers me-2"></i> NOTE 25 : AUTRES CHARGES ET PRODUITS</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center border-bottom-0">
                    <tr>
                        <th style="width: 400px;">Libellé</th>
                        <th>Charges</th>
                        <th>Produits</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'indemnites' => 'Indemnités, dommages et intérêts',
                            'pertes_creances' => 'Pertes sur créances / Recouvrements',
                            'quote_part' => 'Quote-part de résultat sur opé. jointes',
                            'autres' => 'Autres charges et produits divers',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_charges" value="{{ $data[$code.'_charges'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_produits" value="{{ $data[$code.'_produits'] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
