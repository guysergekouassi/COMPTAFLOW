<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>TFT Mensuel - {{ $exercice->intitule }}</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 4px 6px;
            border: 1px solid #ccc;
            text-align: right;
            white-space: nowrap;
        }
        th.label-col, td.label-col {
            text-align: left;
            min-width: 200px;
            white-space: normal;
        }
        .header-row th {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }
        .section-header {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
        }
        .subsection-header {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 8px;
            color: #475569;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 1px solid #000;
        }
        .activity-net-row {
            background-color: #e2e8f0;
            font-weight: bold;
            border: 1px solid #000;
        }
        .main-total {
            background-color: #0f172a;
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
        .text-success { color: green; }
        .text-danger { color: red; }
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
                    <div class="doc-title">TABLEAU DES FLUX DE TRÉSORERIE MENSUEL</div>
                    <div class="doc-subtitle">Analyse des flux réels par activité</div>
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
            @php
                $activityNames = [
                    'operationnelle' => 'I. Flux de trésorerie des activités opérationnelles',
                    'investissement' => 'II. Flux de trésorerie des activités d\'investissement',
                    'financement' => 'III. Flux de trésorerie des activités de financement'
                ];
            @endphp

            @foreach($data['activities'] as $key => $activity)
                <tr class="section-header">
                    <td colspan="{{ count($data['months']) + 2 }}" class="label-col">{{ $activityNames[$key] }}</td>
                </tr>
                
                {{-- ENCAISSEMENTS --}}
                <tr class="subsection-header">
                    <td colspan="{{ count($data['months']) + 2 }}" class="label-col" style="padding-left: 10px;">ENCAISSEMENTS (+)</td>
                </tr>

                @if(isset($detailed) && $detailed)
                    @foreach($activity['encaissements']['categories'] as $category)
                    <tr class="detail-row">
                        <td class="label-col" style="padding-left: 20px;">{{ $category['label'] }}</td>
                        @foreach($data['months'] as $i => $m)
                            <td>{{ number_format($category['data'][$i] ?? 0, 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format(array_sum($category['data']), 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @endif

                <tr class="total-row">
                    @php
                        $suffix = $key == 'operationnelle' ? 'OPÉRATIONNELS' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                        $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                    @endphp
                    <td class="label-col" style="padding-left: 10px; color: green;">TOTAL DES ENCAISSEMENTS {{ $suffix }} ({{ $roman }})</td>
                    @foreach($data['months'] as $i => $m)
                        <td class="text-success">{{ number_format($activity['encaissements']['total'][$i], 0, ',', ' ') }}</td>
                    @endforeach
                    <td class="text-success">{{ number_format(array_sum($activity['encaissements']['total']), 0, ',', ' ') }}</td>
                </tr>

                {{-- DÉCAISSEMENTS --}}
                <tr class="subsection-header">
                    <td colspan="{{ count($data['months']) + 2 }}" class="label-col" style="padding-left: 10px;">DÉCAISSEMENTS (-)</td>
                </tr>

                @if(isset($detailed) && $detailed)
                    @foreach($activity['decaissements']['categories'] as $category)
                    <tr class="detail-row">
                        <td class="label-col" style="padding-left: 20px;">{{ $category['label'] }}</td>
                        @foreach($data['months'] as $i => $m)
                            <td>{{ number_format($category['data'][$i] ?? 0, 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format(array_sum($category['data']), 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @endif

                <tr class="total-row">
                    @php
                        $suffix = $key == 'operationnelle' ? 'OPÉRATIONNELS' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                        $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                    @endphp
                    <td class="label-col" style="padding-left: 10px; color: red;">TOTAL DES DÉCAISSEMENTS {{ $suffix }} ({{ $roman }})</td>
                    @foreach($data['months'] as $i => $m)
                        <td class="text-danger">{{ number_format($activity['decaissements']['total'][$i], 0, ',', ' ') }}</td>
                    @endforeach
                    <td class="text-danger">{{ number_format(array_sum($activity['decaissements']['total']), 0, ',', ' ') }}</td>
                </tr>

                {{-- Flux Net --}}
                <tr class="activity-net-row">
                    @php
                        $suffixNet = $key == 'operationnelle' ? 'OPÉRATIONNELLE' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                        $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                    @endphp
                    <td class="label-col">FLUX NET DE L'ACTIVITÉ {{ $suffixNet }} ({{ $roman }})</td>
                    @foreach($data['months'] as $i => $m)
                        <td>{{ number_format($activity['net'][$i], 0, ',', ' ') }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($activity['net']), 0, ',', ' ') }}</td>
                </tr>
                <tr><td colspan="{{ count($data['months']) + 2 }}" style="border: none; padding: 5px;"></td></tr>
            @endforeach

            <!-- SYNTHÈSE -->
            <tr class="section-header" style="background-color: #0f172a; color: #fff;">
                <td colspan="{{ count($data['months']) + 2 }}" class="label-col" style="color: #fff;">VARIATION GLOBALE ET TRÉSORERIE</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">VARIATION NETTE GLOBALE</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['global_net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format(array_sum($data['global_net']), 0, ',', ' ') }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">TRÉSORERIE FINALE (CUMULÉE)</td>
                @foreach($data['months'] as $i => $m)
                    <td>{{ number_format($data['cumule'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>-</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
