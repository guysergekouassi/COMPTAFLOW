<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Actif/Passif - {{ $exercice->intitule }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #666;
        }
        .content {
            margin-top: 20px;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .col {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        .col:first-child {
            padding-right: 2%;
        }
        .col:last-child {
            padding-left: 2%;
        }
        h3 {
            background-color: #f0f0f0;
            padding: 8px;
            margin: 0 0 10px 0;
            font-size: 13px;
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f8f8f8;
            font-weight: bold;
            font-size: 10px;
        }
        table td.amount {
            text-align: right;
        }
        .total-row {
            background-color: #333;
            color: white;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
        .alert {
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BILAN ACTIF/PASSIF</h1>
        <p>Exercice: {{ $exercice->intitule }}</p>
        <p>Période: {{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}</p>
    </div>

    <div class="content">
        <div class="row">
            <div class="col">
                <h3>ACTIF</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th class="amount">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- ACTIF IMMOBILISE -->
                        <tr>
                            <td style="font-weight:bold;">Actif Immobilisé (Classe 2)</td>
                            <td class="amount" style="font-weight:bold;">{{ number_format($data['actif']['immobilise']['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if(isset($detailed) && $detailed && !empty($data['actif']['immobilise']['details']))
                            @foreach($data['actif']['immobilise']['details'] as $detail)
                            <tr>
                                <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">
                                    {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                </td>
                                <td class="amount" style="font-size: 10px; color: #555;">
                                    {{ number_format($detail['solde'], 0, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif

                        <!-- ACTIF CIRCULANT -->
                        <tr>
                            <td style="font-weight:bold;">Actif Circulant (Stocks & Créances)</td>
                            <td class="amount" style="font-weight:bold;">{{ number_format($data['actif']['circulant']['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if(isset($detailed) && $detailed && !empty($data['actif']['circulant']['details']))
                            @foreach($data['actif']['circulant']['details'] as $detail)
                            <tr>
                                <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">
                                    {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                </td>
                                <td class="amount" style="font-size: 10px; color: #555;">
                                    {{ number_format($detail['solde'], 0, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif

                        <!-- TRESORERIE ACTIF -->
                        <tr>
                            <td style="font-weight:bold;">Trésorerie Actif</td>
                            <td class="amount" style="font-weight:bold;">{{ number_format($data['actif']['tresorerie']['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if(isset($detailed) && $detailed && !empty($data['actif']['tresorerie']['details']))
                            @foreach($data['actif']['tresorerie']['details'] as $detail)
                            <tr>
                                <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">
                                    {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                </td>
                                <td class="amount" style="font-size: 10px; color: #555;">
                                    {{ number_format($detail['solde'], 0, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <th>TOTAL ACTIF</th>
                            <th class="amount">{{ number_format($data['actif']['total'], 0, ',', ' ') }} FCFA</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="col">
                <h3>PASSIF & CAPITAUX</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th class="amount">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- CAPITAUX PROPRES -->
                        <tr>
                            <td style="font-weight:bold;">Capitaux Propres</td>
                            <td class="amount" style="font-weight:bold;">{{ number_format($data['passif']['capitaux']['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if(isset($detailed) && $detailed && !empty($data['passif']['capitaux']['details']))
                            @foreach($data['passif']['capitaux']['details'] as $detail)
                            <tr>
                                <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">
                                    {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                </td>
                                <td class="amount" style="font-size: 10px; color: #555;">
                                    {{ number_format($detail['solde'], 0, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif

                        <!-- DETTES -->
                        <tr>
                            <td style="font-weight:bold;">Dettes à court/long terme</td>
                            <td class="amount" style="font-weight:bold;">{{ number_format($data['passif']['dettes']['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if(isset($detailed) && $detailed && !empty($data['passif']['dettes']['details']))
                            @foreach($data['passif']['dettes']['details'] as $detail)
                            <tr>
                                <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">
                                    {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                </td>
                                <td class="amount" style="font-size: 10px; color: #555;">
                                    {{ number_format($detail['solde'], 0, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif

                        <!-- TRESORERIE PASSIF -->
                        <tr>
                            <td style="font-weight:bold;">Trésorerie Passif</td>
                            <td class="amount" style="font-weight:bold;">{{ number_format($data['passif']['tresorerie']['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if(isset($detailed) && $detailed && !empty($data['passif']['tresorerie']['details']))
                            @foreach($data['passif']['tresorerie']['details'] as $detail)
                            <tr>
                                <td style="padding-left: 20px; font-style: italic; color: #555; font-size: 10px;">
                                    {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                </td>
                                <td class="amount" style="font-size: 10px; color: #555;">
                                    {{ number_format($detail['solde'], 0, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <th>TOTAL PASSIF</th>
                            <th class="amount">{{ number_format($data['passif']['total'], 0, ',', ' ') }} FCFA</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if(!$data['equilibre'])
        <div class="alert alert-danger">
            <strong>Attention :</strong> Le bilan n'est pas équilibré. Différence : {{ number_format($data['difference'], 0, ',', ' ') }} FCFA.
        </div>
        @else
        <div class="alert alert-success">
            <strong>Équilibre :</strong> Le bilan est parfaitement équilibré.
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} - ComptaFlow</p>
    </div>
</body>
</html>
