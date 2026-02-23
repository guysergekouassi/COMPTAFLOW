<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Résultat Analytique</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e40af; }
        .report-title { font-size: 14px; margin-top: 5px; text-transform: uppercase; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1e40af; color: white; padding: 8px; text-align: left; text-transform: uppercase; font-size: 9px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; font-size: 11px; }
        .result-positive { color: green; font-weight: bold; }
        .result-negative { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->company_name ?? 'MA COMPAGNIE' }}</div>
        <div class="report-title">Résultat Analytique</div>
        <div>Exercice : {{ $exercice->intitule ?? 'N/A' }}</div>
    </div>

    <div class="info">
        <strong>Axe Analytique :</strong> {{ $axe->libelle ?? 'Non défini' }}<br>
        <strong>Extraction le :</strong> {{ date('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Section</th>
                <th class="text-right">Charges (Cl. 6)</th>
                <th class="text-right">Produits (Cl. 7)</th>
                <th class="text-right">Résultat Net</th>
                <th class="text-right">Marge %</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalCharges = 0;
                $totalProduits = 0;
            @endphp
            @foreach ($results as $item)
                @php
                    $totalCharges += $item->total_charges;
                    $totalProduits += $item->total_produits;
                    $resultat = $item->total_produits - $item->total_charges;
                    $marge = $item->total_produits > 0 ? ($resultat / $item->total_produits) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $item->code }} - {{ $item->libelle }}</td>
                    <td class="text-right">{{ number_format($item->total_charges, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($item->total_produits, 2, ',', ' ') }}</td>
                    <td class="text-right {{ $resultat >= 0 ? 'result-positive' : 'result-negative' }}">
                        {{ number_format($resultat, 2, ',', ' ') }}
                    </td>
                    <td class="text-right">{{ number_format($marge, 2, ',', ' ') }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td>TOTAL GÉNÉRAL</td>
                <td class="text-right">{{ number_format($totalCharges, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totalProduits, 2, ',', ' ') }}</td>
                @php 
                    $grandResultat = $totalProduits - $totalCharges;
                    $grandMarge = $totalProduits > 0 ? ($grandResultat / $totalProduits) * 100 : 0;
                @endphp
                <td class="text-right {{ $grandResultat >= 0 ? 'result-positive' : 'result-negative' }}">
                    {{ number_format($grandResultat, 2, ',', ' ') }}
                </td>
                <td class="text-right">{{ number_format($grandMarge, 2, ',', ' ') }}%</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Workflow Flow Compta - Analyse de Performance
    </div>
</body>
</html>
