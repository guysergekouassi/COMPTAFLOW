<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Année</th>
                <th>Base Amortissable</th>
                <th>Dotation</th>
                <th>Cumul</th>
                <th>VNC Fin Exercice</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($immobilisation->amortissements as $ligne)
            <tr>
                <td><strong>{{ $ligne->annee }}</strong></td>
                <td>{{ number_format($ligne->base_amortissable, 0, ',', ' ') }}</td>
                <td class="fw-bold">{{ number_format($ligne->dotation_annuelle, 0, ',', ' ') }}</td>
                <td>{{ number_format($ligne->cumul_amortissement, 0, ',', ' ') }}</td>
                <td class="text-primary fw-bold">{{ number_format($ligne->valeur_nette_comptable, 0, ',', ' ') }}</td>
                <td>
                    @if($ligne->statut == 'comptabilise')
                        <span class="badge bg-label-success">
                            <i class="bx bx-check-circle me-1"></i> Comptabilisé
                        </span>
                        @if($ligne->ecriture_comptable_id)
                            <br><small class="text-muted"><a href="#" class="text-muted">Voir écriture</a></small>
                        @endif
                    @else
                        <span class="badge bg-label-warning">
                            <i class="bx bx-time-five me-1"></i> Prévisionnel
                        </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
