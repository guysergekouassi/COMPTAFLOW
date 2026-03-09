<div class="card shadow-none border-0">
    <div class="card-header bg-label-secondary py-3 mb-4">
        <h5 class="mb-0 text-secondary fw-bold text-uppercase"><i class="bx bx-info-circle me-2"></i> NOTE 34 : INFORMATIONS COMPLEMENTAIRES</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm liasse-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 400px;">Libellé</th>
                        <th>Valeur / Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lines = [
                            'dirigeant_nom' => 'Nom du principal dirigeant',
                            'expert_comptable' => 'Expert Comptable / Cabinet',
                            'nombre_etablissements' => 'Nombre d\'établissements en Côte d\'Ivoire',
                            'principale_activite' => 'Détails de l\'activité principale',
                            'evenements_post' => 'Événements post-clôture significatifs',
                        ];
                    @endphp
                    @foreach($lines as $code => $label)
                    <tr>
                        <td class="fw-bold">{{ $label }}</td>
                        <td>
                            @if(str_contains($code, 'nom') || str_contains($code, 'activite') || str_contains($code, 'post'))
                                <textarea class="form-control form-control-sm" name="{{ $code }}" rows="2">{{ $data[$code] ?? '' }}</textarea>
                            @else
                                <input type="text" class="form-control form-control-sm" name="{{ $code }}" value="{{ $data[$code] ?? '' }}">
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
