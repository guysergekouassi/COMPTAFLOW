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
            <!-- I. ACTIVITÉS OPÉRATIONNELLES (Méthode Indirecte) -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">I. Flux de trésorerie des activités opérationnelles (Méthode Indirecte)</td>
            </tr>
            
            <!-- A. CAF -->
            <tr class="sub-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">A. Capacité d'Autofinancement (CAF)</td>
            </tr>
            <tr>
                <td class="label-col">Produits encaissables (+)</td>
                @foreach($data['months'] as $i => $m)
                    <td style="color: #008000;">+ {{ $data['flux']['operationnel']['caf']['produits_encaissables'][$i] }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ array_sum($data['flux']['operationnel']['caf']['produits_encaissables']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['caf']['details']['produits'] as $compte)
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
                <td class="label-col">Charges décaissables (-)</td>
                @foreach($data['months'] as $i => $m)
                    <td style="color: #ff0000;">- {{ $data['flux']['operationnel']['caf']['charges_decaissables'][$i] }}</td>
                @endforeach
                <td style="font-weight: bold;">- {{ array_sum($data['flux']['operationnel']['caf']['charges_decaissables']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['caf']['details']['charges'] as $compte)
                <tr class="detail-row">
                    <td class="label-col">   {{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>- {{ $compte['months'][$i] ?? 0 }}</td>
                    @endforeach
                    <td>- {{ array_sum($compte['months'] ?? []) }}</td>
                </tr>
                @endforeach
            @endif

            <tr class="total-row">
                <td class="label-col">Marge Brute d'Autofinancement (CAF)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['caf']['total'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['caf']['total']) }}</td>
            </tr>

            <!-- B. VAR BFR -->
            <tr class="sub-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">B. Variation du BFR</td>
            </tr>

            <tr>
                <td class="label-col">Variation Stocks</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['bfr']['variation_stocks'][$i] }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ array_sum($data['flux']['operationnel']['bfr']['variation_stocks']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['bfr']['details']['stocks'] as $compte)
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
                <td class="label-col">Variation Créances</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['bfr']['variation_creances'][$i] }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ array_sum($data['flux']['operationnel']['bfr']['variation_creances']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['bfr']['details']['creances'] as $compte)
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
                <td class="label-col">Variation Dettes Circulantes</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['bfr']['variation_dettes'][$i] }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ array_sum($data['flux']['operationnel']['bfr']['variation_dettes']) }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['bfr']['details']['dettes'] as $compte)
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
                <td class="label-col">Variation Totale du BFR</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ $data['flux']['operationnel']['bfr']['total'][$i] }}</td>
                @endforeach
                <td>{{ array_sum($data['flux']['operationnel']['bfr']['total']) }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">FLUX NET OPÉRATIONNEL (A + B)</td>
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
