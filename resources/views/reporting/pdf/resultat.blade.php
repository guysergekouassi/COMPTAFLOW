<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte de Résultat (SIG) - {{ $exercice->intitule }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }
        .sig-row td {
            font-size: 10px;
        }
        .sig-row.main td {
            font-weight: bold;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ccc;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-top: 20px;
            margin-bottom: 5px;
            border-bottom: 2px solid #555;
            padding-bottom: 2px;
        }
        .amount {
            text-align: right;
        }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        
        .details-table {
            margin-top: 5px;
            width: 100%;
        }
        .details-table td {
            border-bottom: 1px dashed #eee;
        }
        .badge {
            background: #eee;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPTE DE RÉSULTAT (SIG)</h1>
        <p>Exercice: {{ $exercice->intitule }} | Période: {{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}</p>
    </div>

    <table>
        <tbody>
            <!-- 1. MARGE COMMERCIALE -->
            <tr>
                <td>Ventes de marchandises</td>
                <td class="amount text-success">+ {{ number_format($data['ventes_marchandises'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Achats de marchandises (y compris variations stocks)</td>
                <td class="amount text-danger">- {{ number_format($data['achats_marchandises'] + $data['var_stock_march'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main">
                <td>MARGE COMMERCIALE</td>
                <td class="amount">{{ number_format($data['marge_commerciale'], 0, ',', ' ') }}</td>
            </tr>

            <!-- 2. VALEUR AJOUTEE -->
            <tr>
                <td>Production de l'exercice</td>
                <td class="amount text-success">+ {{ number_format($data['production_exercice'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Consommation de l'exercice</td>
                <td class="amount text-danger">- {{ number_format($data['consommation_exercice'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main">
                <td>VALEUR AJOUTÉE</td>
                <td class="amount">{{ number_format($data['valeur_ajoutee'], 0, ',', ' ') }}</td>
            </tr>

            <!-- 3. EBE -->
            <tr>
                <td>Subventions d'exploitation</td>
                <td class="amount text-success">+ {{ number_format($data['subventions_expl'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Charges de personnel</td>
                <td class="amount text-danger">- {{ number_format($data['charges_personnel'], 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td>Impôts et Taxes</td>
                <td class="amount text-danger">- {{ number_format($data['impots_taxes'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main">
                <td>EXCÉDENT BRUT D'EXPLOITATION (EBE)</td>
                <td class="amount">{{ number_format($data['ebe'], 0, ',', ' ') }}</td>
            </tr>

            <!-- 4. RESULTAT D'EXPLOITATION -->
            <tr>
                <td>Reprises d'amortissements et provisions</td>
                <td class="amount text-success">+ {{ number_format($data['reprises_amort_prov'], 0, ',', ' ') }}</td>
            </tr>
             <tr>
                <td>Dotations aux amortissements et provisions</td>
                <td class="amount text-danger">- {{ number_format($data['dotations_amort_prov'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main">
                <td>RÉSULTAT D'EXPLOITATION</td>
                <td class="amount">{{ number_format($data['resultat_exploitation'], 0, ',', ' ') }}</td>
            </tr>

            <!-- 5. RESULTAT FINANCIER -->
            <tr>
                <td>Revenus financiers</td>
                <td class="amount text-success">+ {{ number_format($data['revenus_financiers'] + $data['reprises_fin'] + $data['transfert_fin'], 0, ',', ' ') }}</td>
            </tr>
             <tr>
                <td>Frais financiers</td>
                <td class="amount text-danger">- {{ number_format($data['frais_financiers'] + $data['dotations_fin'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main">
                <td>RÉSULTAT FINANCIER</td>
                <td class="amount">{{ number_format($data['resultat_financier'], 0, ',', ' ') }}</td>
            </tr>

            <!-- 6. RESULTAT HAO -->
            <tr>
                <td>Produits H.A.O</td>
                <td class="amount text-success">+ {{ number_format($data['produits_hao'], 0, ',', ' ') }}</td>
            </tr>
             <tr>
                <td>Charges H.A.O</td>
                <td class="amount text-danger">- {{ number_format($data['charges_hao'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main">
                <td>RÉSULTAT H.A.O</td>
                <td class="amount">{{ number_format($data['resultat_hao'], 0, ',', ' ') }}</td>
            </tr>

             <!-- 7. RESULTAT NET -->
             <tr>
                <td>Impôts sur le Résultat</td>
                <td class="amount text-danger">- {{ number_format($data['impots_resultat'], 0, ',', ' ') }}</td>
            </tr>
            <tr class="sig-row main" style="background-color: #333; color: white;">
                <td>RÉSULTAT NET</td>
                <td class="amount">{{ number_format($data['resultat_net'], 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    <!-- DETAILS -->
    @if(isset($detailed) && $detailed && !empty($data['details']))
        @foreach($data['details'] as $category => $items)
            <div class="section-title">{{ $category }}</div>
            <table class="details-table">
                @foreach($items as $item)
                <tr>
                    <td width="15%"><span class="badge">{{ $item['numero'] }}</span></td>
                    <td>{{ $item['intitule'] }}</td>
                    <td class="amount">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </table>
        @endforeach
    @endif

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} par ComptaFlow.<br>
        Les montants sont exprimés en FCFA.
    </div>
</body>
</html>
