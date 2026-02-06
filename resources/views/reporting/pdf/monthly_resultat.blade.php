<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Résultat Mensuel - {{ $exercice->intitule }}</title>
    <style>
        @page {
            margin: 120px 25px 40px 25px;
        }
        header {
            position: fixed;
            top: -100px;
            left: 0px;
            right: 0px;
            height: 90px;
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
            min-width: 150px;
        }
        .header-row th {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }
        .section-header td {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sub-header {
            font-weight: bold;
            background-color: #fafafa;
        }
        .total-row td {
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
        .text-success { color: #000; font-weight: bold; }
        .text-danger { color: #000; font-weight: bold; }
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
                    <div class="doc-title">COMPTE D'EXPLOITATION MENSUEL</div>
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
                <th class="label-col">Rubrique</th>
                @foreach($data['months'] as $month)
                    <th>{{ $month['name'] }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- PRODUITS -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col text-success">PRODUITS / CHIFFRE D'AFFAIRES</td>
            </tr>
            
            @foreach($data['data']['produits'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($data['months'] as $i => $m)
                            <td>{{ number_format($row['data'][$i], 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format(array_sum($row['data']), 0, ',', ' ') }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($data['months'] as $i => $m)
                                <td>{{ isset($compte['data'][$i]) ? number_format($compte['data'][$i], 0, ',', ' ') : '-' }}</td>
                            @endforeach
                            <td>{{ number_format(array_sum($compte['data']), 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL PRODUITS</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['data']['produits']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['data']['produits']['total']), 0, ',', ' ') }}</td>
            </tr>

            <!-- CHARGES -->
            <tr class="section-header">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col text-danger" style="border-top: 2px solid #000;">CHARGES / DÉPENSES</td>
            </tr>
            
            @foreach($data['data']['charges'] as $key => $row)
                @if($key !== 'total')
                    <tr>
                        <td class="label-col">{{ $row['label'] }}</td>
                        @foreach($data['months'] as $i => $m)
                            <td>{{ number_format($row['data'][$i], 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format(array_sum($row['data']), 0, ',', ' ') }}</td>
                    </tr>
                    @if(isset($detailed) && $detailed && !empty($row['details']))
                        @foreach($row['details'] as $compte)
                        <tr class="detail-row">
                            <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                            @foreach($data['months'] as $i => $m)
                                <td>{{ isset($compte['data'][$i]) ? number_format($compte['data'][$i], 0, ',', ' ') : '-' }}</td>
                            @endforeach
                            <td>{{ number_format(array_sum($compte['data']), 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach

            <tr class="total-row">
                <td class="label-col">TOTAL CHARGES</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['data']['charges']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['data']['charges']['total']), 0, ',', ' ') }}</td>
            </tr>

            <!-- RÉSULTAT -->
            <tr class="main-total">
                <td class="label-col">RÉSULTAT NET</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['data']['resultat'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['data']['resultat']), 0, ',', ' ') }}</td>
            </tr>

        </tbody>
    </table>

    <div style="font-size: 8px; color: #999; text-align: center; margin-top: 20px;">
        Généré par ComptaFlow le {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>
