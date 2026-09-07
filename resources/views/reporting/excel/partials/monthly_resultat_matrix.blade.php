@php
    // Somme limitée aux mois réellement affichés (filtre de période).
    $sumVisible = fn ($arr) => array_sum(array_intersect_key((array) ($arr ?? []), $visibleMonths));
@endphp
    <table>
        <thead>
            <tr>
                <th colspan="{{ count($visibleMonths) + 2 }}" style="text-align: center; font-size: 16px;">
                    COMPTE D'EXPLOITATION MENSUEL - {{ $exercice->intitule }}
                </th>
            </tr>
            <tr>
                <th class="label-col">Rubrique</th>
                @foreach($visibleMonths as $month)
                    <th>{{ $month['name'] }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col">PRODUITS / CHIFFRE D'AFFAIRES</td>
            </tr>
            
            @foreach($data['data']['produits'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($visibleMonths as $i => $m)
                            <td>{{ $row['data'][$i] }}</td>
                        @endforeach
                        <td>{{ $sumVisible($row['data']) }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($visibleMonths as $i => $m)
                                <td>{{ $compte['data'][$i] ?? 0 }}</td>
                            @endforeach
                            <td>{{ $sumVisible($compte['data']) }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL PRODUITS</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ $data['data']['produits']['total'][$i] }}</td>
                @endforeach
                <td>{{ $sumVisible($data['data']['produits']['total']) }}</td>
            </tr>

            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col">CHARGES / DÉPENSES</td>
            </tr>
            
            @foreach($data['data']['charges'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($visibleMonths as $i => $m)
                            <td>{{ $row['data'][$i] }}</td>
                        @endforeach
                        <td>{{ $sumVisible($row['data']) }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($visibleMonths as $i => $m)
                                <td>{{ $compte['data'][$i] ?? 0 }}</td>
                            @endforeach
                            <td>{{ $sumVisible($compte['data']) }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL CHARGES</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ $data['data']['charges']['total'][$i] }}</td>
                @endforeach
                <td>{{ $sumVisible($data['data']['charges']['total']) }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">RÉSULTAT NET</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ $data['data']['resultat'][$i] }}</td>
                @endforeach
                <td>{{ $sumVisible($data['data']['resultat']) }}</td>
            </tr>
        </tbody>
    </table>
