<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .label-col { text-align: left; }
        .section-header { font-weight: bold; background-color: #f0f0f0; }
        .total-row { font-weight: bold; background-color: #e0e0e0; }
        .main-total { font-weight: bold; background-color: #000; color: #fff; }
        .detail-row { font-style: italic; color: #555; }
        th { background-color: #eee; font-weight: bold; border: 1px solid #000; }
        td { border: 1px solid #ccc; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="{{ count($data['months']) + 2 }}" style="text-align: center; font-size: 16px;">
                    COMPTE D'EXPLOITATION MENSUEL - {{ $exercice->intitule }}
                </th>
            </tr>
            <tr>
                <th class="label-col">Rubrique</th>
                @foreach($data['months'] as $month)
                    <th>{{ $month['name'] }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">PRODUITS / CHIFFRE D'AFFAIRES</td>
            </tr>
            
            @foreach($data['data']['produits'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($data['months'] as $i => $m)
                            <td>{{ $row['data'][$i] }}</td>
                        @endforeach
                        <td>{{ array_sum($row['data']) }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($data['months'] as $i => $m)
                                <td>{{ $compte['data'][$i] ?? 0 }}</td>
                            @endforeach
                            <td>{{ array_sum($compte['data']) }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL PRODUITS</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['data']['produits']['total'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['data']['produits']['total']) }}</td>
            </tr>

            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">CHARGES / DÉPENSES</td>
            </tr>
            
            @foreach($data['data']['charges'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($data['months'] as $i => $m)
                            <td>{{ $row['data'][$i] }}</td>
                        @endforeach
                        <td>{{ array_sum($row['data']) }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($data['months'] as $i => $m)
                                <td>{{ $compte['data'][$i] ?? 0 }}</td>
                            @endforeach
                            <td>{{ array_sum($compte['data']) }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL CHARGES</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['data']['charges']['total'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['data']['charges']['total']) }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">RÉSULTAT NET</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['data']['resultat'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['data']['resultat']) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
