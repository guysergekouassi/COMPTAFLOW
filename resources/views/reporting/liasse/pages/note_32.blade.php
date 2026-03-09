<div class="card shadow-none border-0">
    <div class="card-header bg-label-dark py-3 mb-4">
        <h5 class="mb-0 text-dark fw-bold text-uppercase"><i class="bx bx-calculator me-2"></i> NOTE 32 : TABLEAU DU RESULTAT FISCAL</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center border-bottom-0">
                    <tr>
                        <th style="width: 450px;">Libellé</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-secondary fw-bold">
                        <td>RÉSULTAT NET COMPTABLE (Bénéfice ou Perte)</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold" name="res_net_comptable" value="{{ $data['res_net_comptable'] ?? 0 }}"></td>
                    </tr>
                    <tr><td colspan="2" class="bg-light fw-bold">PLUS : RÉINTÉGRATIONS FISCALES</td></tr>
                    @php
                        $reint = [
                            'reint_amort_excedent' => 'Amortissements excédentaires',
                            'reint_prov_non_ded' => 'Provisions non déductibles',
                            'reint_amendes' => 'Amendes et pénalités',
                            'reint_impots_non_ded' => 'Impôts non déductibles (IS, etc.)',
                            'reint_autres' => 'Autres charges non déductibles',
                        ];
                    @endphp
                    @foreach($reint as $code => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}" value="{{ $data[$code] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                    
                    <tr><td colspan="2" class="bg-light fw-bold">MOINS : DÉDUCTIONS FISCALES</td></tr>
                    @php
                        $deduct = [
                            'deduct_plus_values' => 'Plus-values de cession (exonérées)',
                            'deduct_dividendes' => 'Dividendes reçus (exonérés)',
                            'deduct_autres' => 'Autres produits non imposables',
                        ];
                    @endphp
                    @foreach($deduct as $code => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="{{ $code }}" value="{{ $data[$code] ?? 0 }}"></td>
                    </tr>
                    @endforeach
                    <tr class="table-primary fw-bold">
                        <td>RÉSULTAT FISCAL (Provisoire)</td>
                        <td class="text-end px-2">
                             @php
                                $totalReint = ($data['reint_amort_excedent'] ?? 0) + ($data['reint_prov_non_ded'] ?? 0) + ($data['reint_amendes'] ?? 0) + ($data['reint_impots_non_ded'] ?? 0) + ($data['reint_autres'] ?? 0);
                                $totalDeduct = ($data['deduct_plus_values'] ?? 0) + ($data['deduct_dividendes'] ?? 0) + ($data['deduct_autres'] ?? 0);
                                $resFiscal = ($data['res_net_comptable'] ?? 0) + $totalReint - $totalDeduct;
                             @endphp
                             {{ number_format($resFiscal, 0, ',', ' ') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
