<div class="card shadow-none border-0">
    <div class="card-header bg-label-info py-3 mb-4">
        <h5 class="mb-0 text-info fw-bold text-uppercase"><i class="bx bx-repost me-2"></i> NOTE 31 : CONSOMMATIONS DE L'EXERCICE</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center border-bottom-0">
                    <tr>
                        <th style="width: 400px;">Libellé</th>
                        <th>Montant de l'exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'achats_mat_incorp' => 'Matières incorporées aux produits',
                            'fourn_consommees' => 'Fournitures consommées (non stockables)',
                            'transports_consommes' => 'Transports consommés',
                            'services_ext_consommes' => 'Services extérieurs consommés',
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
