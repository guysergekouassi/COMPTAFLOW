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
@php
    $periode = $periode ?? app(\App\Services\AccountingReportingService::class)->resolvePeriode($exercice, $month ?? null);

    // Comparatif N-1 : $dataN1 n'est fourni que si l'option a été cochée au téléchargement.
    $dataN1     = $dataN1     ?? null;
    $exerciceN1 = $exerciceN1 ?? null;
    $cmp        = !is_null($dataN1);
    $libN       = $exercice->intitule ?? 'N';
    $libN1      = $exerciceN1->intitule ?? 'N-1';
    $nf         = fn($v) => number_format((float) $v, 0, ',', ' ');

    // Trame du compte de résultat : une seule définition, rendue en N et en N-1.
    $sigRows = [
        ['Ventes de marchandises',                              '+', 'text-success', '',                 fn($d) => $d['ventes_marchandises']],
        ['Achats de marchandises (y compris variations stocks)','-', 'text-danger',  '',                 fn($d) => $d['achats_marchandises'] + $d['var_stock_march']],
        ['MARGE COMMERCIALE',                                   '',  '',             'sig-row main',     fn($d) => $d['marge_commerciale']],
        ["Production de l'exercice",                            '+', 'text-success', '',                 fn($d) => $d['production_exercice']],
        ["Consommation de l'exercice",                          '-', 'text-danger',  '',                 fn($d) => $d['consommation_exercice']],
        ['VALEUR AJOUTÉE',                                      '',  '',             'sig-row main',     fn($d) => $d['valeur_ajoutee']],
        ["Subventions d'exploitation",                          '+', 'text-success', '',                 fn($d) => $d['subventions_expl']],
        ['Charges de personnel',                                '-', 'text-danger',  '',                 fn($d) => $d['charges_personnel']],
        ['Impôts et Taxes',                                     '-', 'text-danger',  '',                 fn($d) => $d['impots_taxes']],
        ["EXCÉDENT BRUT D'EXPLOITATION (EBE)",                  '',  '',             'sig-row main',     fn($d) => $d['ebe']],
        ["Reprises d'amortissements et provisions",             '+', 'text-success', '',                 fn($d) => $d['reprises_amort_prov']],
        ['Dotations aux amortissements et provisions',          '-', 'text-danger',  '',                 fn($d) => $d['dotations_amort_prov']],
        ["RÉSULTAT D'EXPLOITATION",                             '',  '',             'sig-row main',     fn($d) => $d['resultat_exploitation']],
        ['Revenus financiers',                                  '+', 'text-success', '',                 fn($d) => $d['revenus_financiers'] + $d['reprises_fin'] + $d['transfert_fin']],
        ['Frais financiers',                                    '-', 'text-danger',  '',                 fn($d) => $d['frais_financiers'] + $d['dotations_fin']],
        ['RÉSULTAT FINANCIER',                                  '',  '',             'sig-row main',     fn($d) => $d['resultat_financier']],
        ['Produits H.A.O',                                      '+', 'text-success', '',                 fn($d) => $d['produits_hao']],
        ['Charges H.A.O',                                       '-', 'text-danger',  '',                 fn($d) => $d['charges_hao']],
        ['RÉSULTAT H.A.O',                                      '',  '',             'sig-row main',     fn($d) => $d['resultat_hao']],
        ['Impôts sur le Résultat',                              '-', 'text-danger',  '',                 fn($d) => $d['impots_resultat']],
        ['RÉSULTAT NET',                                        '',  '',             'resultat-net-row', fn($d) => $d['resultat_net']],
    ];
@endphp

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
                    <div class="doc-subtitle">{{ $periode['is_month'] ? $periode['label'] : 'Comptes Annuels' }}@if($cmp) &mdash; Comparatif {{ $libN1 }}@endif</div>
                </td>
                <td style="width: 30%; border-bottom: 1px solid #000; text-align: right;">
                    <div class="period">
                        Période du {{ $periode['debut']->format('d/m/Y') }}<br>
                        au {{ $periode['fin']->format('d/m/Y') }}<br>
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
            <tr>
                <th>Libellé</th>
                <th class="amount">{{ $cmp ? $libN : 'Montant' }}</th>
                @if($cmp)
                    <th class="amount">{{ $libN1 }}</th>
                    <th class="amount">Var.</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($sigRows as [$label, $signe, $couleur, $rowClass, $valeur])
                @php
                    $vN     = (float) $valeur($data);
                    $vN1    = $cmp ? (float) $valeur($dataN1) : 0;
                    $isNet  = $rowClass === 'resultat-net-row';
                @endphp
                <tr @if($rowClass) class="{{ $rowClass }}" @endif>
                    <td>{{ $label }}</td>
                    <td class="amount {{ $couleur }}">{{ $signe ? $signe . ' ' : '' }}{{ $nf($vN) }}{{ $isNet ? ' FCFA' : '' }}</td>
                    @if($cmp)
                        <td class="amount {{ $couleur }}">{{ $signe ? $signe . ' ' : '' }}{{ $nf($vN1) }}</td>
                        <td class="amount">{{ $nf($vN - $vN1) }}</td>
                    @endif
                </tr>
            @endforeach
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
