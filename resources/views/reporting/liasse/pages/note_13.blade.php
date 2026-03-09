<div class="card shadow-none border-0">
    <div class="card-header bg-label-dark py-3 mb-4">
        <h5 class="mb-0 text-dark fw-bold text-uppercase"><i class="bx bx-pyramid me-2"></i> NOTE 13 : CAPITAUX PROPRES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center border-bottom-0">
                    <tr>
                        <th style="width: 300px;">Composantes</th>
                        <th>Capital</th>
                        <th>Primes</th>
                        <th>Réserves</th>
                        <th>Report à nouveau</th>
                        <th>Résultat Net</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="bg-light">Savoir à l'ouverture</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="cap_debut" value="{{ $data['cap_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="pri_debut" value="{{ $data['pri_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="res_debut" value="{{ $data['res_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="rep_debut" value="{{ $data['rep_debut'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="res_net_debut" value="{{ $data['res_net_debut'] ?? 0 }}"></td>
                    </tr>
                    <tr>
                        <td class="bg-light">Affectation du résultat</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="cap_aff" value="{{ $data['cap_aff'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="pri_aff" value="{{ $data['pri_aff'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="res_aff" value="{{ $data['res_aff'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="rep_aff" value="{{ $data['rep_aff'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="res_net_aff" value="{{ $data['res_net_aff'] ?? 0 }}"></td>
                    </tr>
                    <tr>
                        <td class="bg-light">Augmentations / Diminutions</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="cap_var" value="{{ $data['cap_var'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="pri_var" value="{{ $data['pri_var'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="res_var" value="{{ $data['res_var'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="rep_var" value="{{ $data['rep_var'] ?? 0 }}"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="res_net_var" value="{{ $data['res_net_var'] ?? 0 }}"></td>
                    </tr>
                    <tr class="table-dark text-white fw-bold">
                        <td class="text-white">SOLDE A LA CLOTURE</td>
                        <td class="text-end px-2">{{ number_format(($data['cap_debut'] ?? 0) + ($data['cap_aff'] ?? 0) + ($data['cap_var'] ?? 0), 0, ',', ' ') }}</td>
                        <td class="text-end px-2">{{ number_format(($data['pri_debut'] ?? 0) + ($data['pri_aff'] ?? 0) + ($data['pri_var'] ?? 0), 0, ',', ' ') }}</td>
                        <td class="text-end px-2">{{ number_format(($data['res_debut'] ?? 0) + ($data['res_aff'] ?? 0) + ($data['res_var'] ?? 0), 0, ',', ' ') }}</td>
                        <td class="text-end px-2">{{ number_format(($data['rep_debut'] ?? 0) + ($data['rep_aff'] ?? 0) + ($data['rep_var'] ?? 0), 0, ',', ' ') }}</td>
                        <td class="text-end px-2">{{ number_format(($data['res_net_debut'] ?? 0) + ($data['res_net_aff'] ?? 0) + ($data['res_net_var'] ?? 0), 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
