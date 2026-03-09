<div class="card shadow-none border-0">
    <div class="card-header bg-label-secondary py-3 mb-4">
        <h5 class="mb-0 text-secondary fw-bold text-uppercase"><i class="bx bx-dots-horizontal-rounded me-2"></i> NOTE 20 : AUTRES DETTES ET COMPTES DE REGULARISATION</h5>
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
                            'credits_divers' => 'Créditeurs divers',
                            'comptes_groupe' => 'Comptes d\'associés et du groupe',
                            'cca_pca' => 'CCA / PCA (Régularisation)',
                            'ecarts_conv' => 'Écarts de conversion',
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
