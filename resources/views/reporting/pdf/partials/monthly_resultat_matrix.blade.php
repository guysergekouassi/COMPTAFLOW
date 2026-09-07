@php
    // Somme limitée aux mois réellement affichés (filtre de période).
    $sumVisible = fn ($arr) => array_sum(array_intersect_key((array) ($arr ?? []), $visibleMonths));
@endphp
    <table>
        <thead>
            <tr class="header-row">
                <th class="label-col">Rubrique</th>
                @foreach($visibleMonths as $month)
                    <th>{{ $month['name'] }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- PRODUITS -->
            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col text-success">PRODUITS / CHIFFRE D'AFFAIRES</td>
            </tr>
            
            @foreach($data['data']['produits'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($visibleMonths as $i => $m)
                            <td>{{ number_format($row['data'][$i], 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format($sumVisible($row['data']), 0, ',', ' ') }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($visibleMonths as $i => $m)
                                <td>{{ isset($compte['data'][$i]) ? number_format($compte['data'][$i], 0, ',', ' ') : '-' }}</td>
                            @endforeach
                            <td>{{ number_format($sumVisible($compte['data']), 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL PRODUITS</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['data']['produits']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['data']['produits']['total']), 0, ',', ' ') }}</td>
            </tr>

            <!-- CHARGES -->
            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col text-danger" style="border-top: 2px solid #000;">CHARGES / DÉPENSES</td>
            </tr>
            
            @foreach($data['data']['charges'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($visibleMonths as $i => $m)
                            <td>{{ number_format($row['data'][$i], 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format($sumVisible($row['data']), 0, ',', ' ') }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($visibleMonths as $i => $m)
                                <td>{{ isset($compte['data'][$i]) ? number_format($compte['data'][$i], 0, ',', ' ') : '-' }}</td>
                            @endforeach
                            <td>{{ number_format($sumVisible($compte['data']), 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL CHARGES</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['data']['charges']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['data']['charges']['total']), 0, ',', ' ') }}</td>
            </tr>

            <!-- RÉSULTAT -->
            <tr class="main-total">
                <td class="label-col">RÉSULTAT NET</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['data']['resultat'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['data']['resultat']), 0, ',', ' ') }}</td>
            </tr>

        </tbody>
    </table>
