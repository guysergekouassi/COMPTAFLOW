<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .label-col { text-align: left; }
        .section-header { font-weight: bold; background-color: #f0f0f0; }
        .sub-header { font-weight: bold; background-color: #fafafa; }
        .total-row { font-weight: bold; background-color: #e0e0e0; }
        .main-total { font-weight: bold; background-color: #d0d0d0; }
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
                    TABLEAU DE FLUX DE TRÉSORERIE (TFT) - {{ $exercice->intitule }}
                </th>
            </tr>
            <tr>
                <th class="label-col">Flux de trésorerie</th>
                @foreach($data['months'] as $month)
                    <th>{{ $month['name'] }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- I. ACTIVITÉS OPÉRATIONNELLES -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">I. Flux de trésorerie des activités opérationnelles</td>
            </tr>
            
            <tr>
                <td class="label-col">Clients (Encaissements)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['encaissements']['clients'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['encaissements']['clients']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['encaissements']['details']['clients'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

            <tr class="sub-header">
                <td class="label-col">Total des encaissements</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['encaissements']['total'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['encaissements']['total']) }}</td>
            </tr>

            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">Décaissements</td>
            </tr>

            <tr>
                <td class="label-col">Dépenses de production (601-603)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['production'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['production']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['production'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

            <tr>
                <td class="label-col">Autres achats (604-608)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['autres_achats'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['autres_achats']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['autres_achats'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

            <tr>
                <td class="label-col">Transport (61)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['transport'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['transport']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['transport'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

            <tr>
                <td class="label-col">Services Extérieurs (62-63)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['services_exterieurs'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['services_exterieurs']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['services_exterieurs'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

             <tr>
                <td class="label-col">Charges de personnel (66)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['personnel'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['personnel']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['personnel'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

             <tr>
                <td class="label-col">Impôts et Taxes (64)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['impots_taxes'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['impots_taxes']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['impots_taxes'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

            <tr class="total-row">
                <td class="label-col">Total des Décaissements</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['decaissements']['total'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['decaissements']['total']) }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">FLUX NET OPÉRATIONNEL (I)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['net'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['net']) }}</td>
            </tr>

            <!-- II. INVESTISSEMENT -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">II. Flux des activités d'investissement</td>
            </tr>
            <tr>
                <td class="label-col">Cessions d'immobilisations (+)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['investissement']['cessions'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['investissement']['cessions']) }}</td>
            </tr>
            <tr>
                <td class="label-col">Acquisitions d'immobilisations (-)</td>
                @foreach($data['months'] as $i => $m)
                    <td>-{{ $data['flux']['investissement']['acquisitions'][$i] }}</td>
                @endforeach
                <td>-{{ array_sum($data['flux']['investissement']['acquisitions']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['investissement']['details']['acquisitions'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>-{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>-{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif
             <tr class="main-total">
                <td class="label-col">FLUX NET INVESTISSEMENT (II)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['investissement']['net'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['investissement']['net']) }}</td>
            </tr>

            <!-- III. FINANCEMENT -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">III. Flux des activités de financement</td>
            </tr>
            <tr>
                <td class="label-col">Flux Net Financement</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['financement']['net'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['financement']['net']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['financement']['details']['net'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>{{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif
             <tr class="main-total">
                <td class="label-col">FLUX NET FINANCEMENT (III)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['financement']['net'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['financement']['net']) }}</td>
            </tr>

            <!-- TOTAL FLUX -->
            <tr class="main-total">
                <td class="label-col">VARIATION DE TRÉSORERIE (I+II+III)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['tresorerie']['variation'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['tresorerie']['variation']) }}</td>
            </tr>

             <tr class="total-row">
                <td class="label-col">Solde Trésorerie Fin de Période (Cumulé)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['tresorerie']['solde_fin'][$i] }}</td>
                @endforeach
                <td>-</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
