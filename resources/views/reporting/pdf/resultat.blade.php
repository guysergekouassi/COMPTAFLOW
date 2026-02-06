<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte de Résultat (SIG) - {{ $exercice->intitule }}</title>
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
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
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
            top: 35%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.08;
            transform: rotate(-45deg);
            font-size: 110px;
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
            background-color: #eee;
            border-bottom: 2px solid #000;
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
        .text-success { color: #000; font-weight: bold; }
        .text-danger { color: #000; font-weight: bold; }
        
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
        .resultat-net-row td {
            background-color: #000 !important;
            color: #fff !important;
            font-weight: bold;
            font-size: 11px;
            padding: 8px;
        }
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
                    <div class="doc-title">COMPTE DE RÉSULTAT (SIG)</div>
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
            <tr class="resultat-net-row">
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


</body>
</html>
