<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>TFT - {{ $exercice->intitule }}</title>
    <style>
        @page {
            margin: 120px 25px 40px 25px;
        }
        header {
            position: fixed;
            top: -110px;
            left: 0px;
            right: 0px;
            height: 100px;
            border: 1px solid #000;
            padding: 5px 10px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 2px 0;
        }
        .company-name {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10px;
            margin-top: 2px;
        }
        .period {
            text-align: right;
            font-size: 9px;
        }
        .footer-row td {
            font-size: 8px;
            color: #555;
            padding-top: 5px !important;
        }
        .page-counter:before {
            content: counter(page);
        }
        .watermark {
            position: fixed;
            top: 30%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.08;
            transform: rotate(-25deg);
            font-size: 120px;
            font-weight: bold;
            color: #000;
            z-index: -1000;
            text-transform: uppercase;
            pointer-events: none;
        }
        /* Existing table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 4px 6px;
            border: 1px solid #ccc;
            text-align: right;
        }
        th.label-col, td.label-col {
            text-align: left;
            min-width: 200px;
        }
        .header-row th {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }
        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sub-header {
            font-weight: bold;
            background-color: #fafafa;
        }
        .total-row {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .main-total {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }
        .detail-row td {
            font-style: italic;
            color: #555;
            font-size: 8px;
            border-top: 1px dashed #eee;
        }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="watermark">COMPTAFLOW</div>
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 30%; border-bottom: 1px solid #000; text-align: left;">
                    <div class="company-name">{{ $exercice->company->company_name ?? 'Entreprise' }}</div>
                    <div style="font-size: 8px; margin-top: 2px; font-weight: normal;">Impression définitive</div>
                </td>
                <td style="width: 40%; border-bottom: 1px solid #000; text-align: center;">
                    <div class="doc-title">TABLEAU DE FLUX DE TRÉSORERIE (TFT)</div>
                    <div class="doc-subtitle">Comptes Annuels</div>
                </td>
                <td style="width: 30%; border-bottom: 1px solid #000; text-align: right;">
                    <div class="period">
                        Période du {{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }}<br>
                        au {{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}<br>
                        Tenue de compte : {{ $exercice->company->currency ?? 'FCFA' }}
                    </div>
                </td>
            </tr>
            <tr class="footer-row">
                <td style="text-align: left; width: 30%;">
                    &copy; ComptaFlow - Logiciel de comptabilité
                </td>
                <td style="text-align: center; width: 40%;">
                    Date de tirage : {{ date('d/m/Y à H:i:s') }}
                </td>
                <td style="text-align: right; width: 30%;">
                    Page : <span class="page-counter"></span>
                </td>
            </tr>
        </table>
    </header>

    <table>
        <thead>
            <tr class="header-row">
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
            
            <!-- Encaissements -->
            <tr>
                <td class="label-col">Clients (Encaissements)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['encaissements']['clients'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['encaissements']['clients']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['encaissements']['details']['clients'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <tr class="sub-header">
                <td class="label-col">Total des encaissements</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['encaissements']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['encaissements']['total']), 0, ',', ' ') }}</td>
            </tr>

            <!-- Décaissements -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">Décaissements</td>
            </tr>

            <!-- Production -->
            <tr>
                <td class="label-col">Dépenses de production (601-603)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['production'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['production']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['production'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <!-- Autres Achats -->
            <tr>
                <td class="label-col">Autres achats (604-608)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['autres_achats'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['autres_achats']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['autres_achats'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <!-- Transport -->
            <tr>
                <td class="label-col">Transport (61)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['transport'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['transport']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['transport'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <!-- Services Extérieurs -->
            <tr>
                <td class="label-col">Services Extérieurs (62-63)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['services_exterieurs'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['services_exterieurs']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['services_exterieurs'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <!-- Personnel -->
             <tr>
                <td class="label-col">Charges de personnel (66)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['personnel'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['personnel']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['personnel'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

             <!-- Impôts -->
             <tr>
                <td class="label-col">Impôts et Taxes (64)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['impots_taxes'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['impots_taxes']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['decaissements']['details']['impots_taxes'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif


            <tr class="total-row">
                <td class="label-col">Total des Décaissements</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['decaissements']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['decaissements']['total']), 0, ',', ' ') }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">FLUX NET OPÉRATIONNEL (I)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['operationnel']['net']), 0, ',', ' ') }}</td>
            </tr>

            <!-- II. INVESTISSEMENT -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">II. Flux des activités d'investissement</td>
            </tr>
            <tr>
                <td class="label-col">Cessions d'immobilisations (+)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['investissement']['cessions'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['investissement']['cessions']), 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="label-col">Acquisitions d'immobilisations (-)</td>
                @foreach($data['months'] as $i => $m)
                    <td>-{{ number_format($data['flux']['investissement']['acquisitions'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>-{{ number_format(array_sum($data['flux']['investissement']['acquisitions']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['investissement']['details']['acquisitions'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>-{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>-{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
             <tr class="main-total">
                <td class="label-col">FLUX NET INVESTISSEMENT (II)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['investissement']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['investissement']['net']), 0, ',', ' ') }}</td>
            </tr>


            <!-- III. FINANCEMENT -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col">III. Flux des activités de financement</td>
            </tr>
            <tr>
                <td class="label-col">Flux Net Financement</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['financement']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['financement']['net']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['financement']['details']['net'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
             <tr class="main-total">
                <td class="label-col">FLUX NET FINANCEMENT (III)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['financement']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['financement']['net']), 0, ',', ' ') }}</td>
            </tr>


            <!-- TOTAL FLUX -->
            <tr class="main-total">
                <td class="label-col">VARIATION DE TRÉSORERIE (I+II+III)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['tresorerie']['variation'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['flux']['tresorerie']['variation']), 0, ',', ' ') }}</td>
            </tr>

             <tr class="total-row">
                <td class="label-col">Solde Trésorerie Fin de Période (Cumulé)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['flux']['tresorerie']['solde_fin'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>-</td>
            </tr>

        </tbody>
    </table>


</body>
</html>
