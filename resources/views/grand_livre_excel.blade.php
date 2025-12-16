<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Journal</th>
            <th>Compte</th>
            <th>Tiers</th>
            <th>Libellé</th>
            <th>Débit</th>
            <th>Crédit</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ecritures as $ecriture)
            <tr>
                <td>{{ $ecriture->date }}</td>
                <td>{{ $ecriture->codeJournal->code ?? '' }}</td>
                <td>{{ $ecriture->planComptable->numero_de_compte ?? '' }}</td>
                <td>{{ $ecriture->planTiers->numero_de_tiers ?? '' }}</td>
                <td>{{ $ecriture->libelle }}</td>
                <td>{{ $ecriture->debit }}</td>
                <td>{{ $ecriture->credit }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
