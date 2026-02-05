<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TFT - {{ $exercice->intitule }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        .section-header {
            background-color: #f5f5f5;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .total-row {
            background-color: #eee;
            font-weight: bold;
        }
        .grand-total {
            background-color: #1a237e;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-success { color: #2e7d32; }
        .text-danger { color: #c62828; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tableau des Flux de Trésorerie (TFT)</h1>
        <p>Exercice : {{ $exercice->intitule }} | Généré le : {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <!-- OPÉRATIONNEL -->
        <thead>
            <tr class="section-header">
                <th colspan="2" style="text-align: left;">I. ACTIVITÉS OPÉRATIONNELLES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Capacité d'Autofinancement (CAF)</td>
                <td class="text-end">{{ number_format($data['operationnel']['caf'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Variation du Besoin en Fonds de Roulement (BFR)</td>
                <td class="text-end">{{ number_format($data['operationnel']['variation_bfr'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="total-row">
                <td>FLUX NET DE TRÉSORERIE EXPLOITATION (B)</td>
                <td class="text-end">{{ number_format($data['operationnel']['total'], 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed && !empty($data['operationnel']['details']))
                @foreach($data['operationnel']['details'] as $item)
                <tr>
                    <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">{{ $item['numero'] }} - {{ $item['intitule'] }}</td>
                    <td class="text-end" style="font-size: 10px; color: #555;">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>

        <!-- INVESTISSEMENT -->
        <thead>
            <tr class="section-header">
                <th colspan="2" style="text-align: left;">II. ACTIVITÉS D'INVESTISSEMENT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Produits des cessions d'immobilisations (+)</td>
                <td class="text-end text-success">+ {{ number_format($data['investissement']['cessions'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Acquisitions d'immobilisations (-)</td>
                <td class="text-end text-danger">- {{ number_format($data['investissement']['acquisitions'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="total-row">
                <td>FLUX NET DE TRÉSORERIE INVESTISSEMENT (C)</td>
                <td class="text-end">{{ number_format($data['investissement']['total'], 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed && !empty($data['investissement']['details']))
                @foreach($data['investissement']['details'] as $item)
                <tr>
                    <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">{{ $item['numero'] }} - {{ $item['intitule'] }}</td>
                    <td class="text-end" style="font-size: 10px; color: #555;">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>

        <!-- FINANCEMENT -->
        <thead>
            <tr class="section-header">
                <th colspan="2" style="text-align: left;">III. ACTIVITÉS DE FINANCEMENT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Variations de Capital (+)</td>
                <td class="text-end">{{ number_format($data['financement']['capital'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Variations d'Emprunts et Dettes financières</td>
                <td class="text-end">{{ number_format($data['financement']['emprunts'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="total-row">
                <td>FLUX NET DE TRÉSORERIE FINANCEMENT (D)</td>
                <td class="text-end">{{ number_format($data['financement']['total'], 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed && !empty($data['financement']['details']))
                @foreach($data['financement']['details'] as $item)
                <tr>
                    <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">{{ $item['numero'] }} - {{ $item['intitule'] }}</td>
                    <td class="text-end" style="font-size: 10px; color: #555;">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>

        <!-- SYNTHÈSE -->
        <thead>
            <tr class="section-header">
                <th colspan="2" style="text-align: left;">IV. SYNTHÈSE DE LA TRÉSORERIE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Variation Nette de Trésorerie (B + C + D)</td>
                <td class="text-end font-weight-bold">{{ number_format($data['tresorerie']['variation_nette'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Trésorerie Initiale (A)</td>
                <td class="text-end">{{ number_format($data['tresorerie']['initiale'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="grand-total">
                <td>TRÉSORERIE FINALE (E = A + Var)</td>
                <td class="text-end">{{ number_format($data['tresorerie']['finale'], 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; font-style: italic; font-size: 10px;">
        Note : Ce document est généré par Flow Compta conformément aux normes SYSCOHADA.
    </div>

    <div class="footer">
        Flow Compta - Solution de Gestion Comptable Intelligente
    </div>
</body>
</html>
