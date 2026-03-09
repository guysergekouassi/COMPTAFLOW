<div class="card shadow-none border-0">
    <div class="card-header bg-label-dark py-3 mb-4">
        <h5 class="mb-0 text-dark fw-bold text-uppercase"><i class="bx bx-buildings me-2"></i> NOTE 16 : DETTES FINANCIERES ET RESSOURCES ASSIMILEES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 300px;">Désignation</th>
                        <th>Montant début exercice</th>
                        <th>Augmentations</th>
                        <th>Diminutions (Remboursements)</th>
                        <th>Montant fin exercice</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'emprunt_oblig' => 'Emprunts obligataires',
                            'emprunt_etab' => 'Emprunts auprès des étab. de crédit',
                            'dettes_location' => 'Dettes de location-acquisition (Crédit-bail)',
                            'avances_reçues' => 'Avances et acomptes reçus sur commandes',
                            'autres_dettes' => 'Autres dettes financières',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_debut" value="{{ $data[$code.'_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_aug" value="{{ $data[$code.'_aug'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}_dim" value="{{ $data[$code.'_dim'] ?? 0 }}"></td>
                        <td class="bg-light text-end fw-bold px-2">
                             {{ number_format(($data[$code.'_debut'] ?? 0) + ($data[$code.'_aug'] ?? 0) - ($data[$code.'_dim'] ?? 0), 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
