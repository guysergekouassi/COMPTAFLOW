<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Balance des Tiers</title>
    <style>
        @page {
            margin: 15px 20px 40px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        /* En-tête */
        .header-container {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 5px;
        }

        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            font-size: 9px;
        }

        .header-center {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: top;
            font-size: 8px;
        }

        .main-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .sub-title {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .meta-info {
            font-size: 8px;
            margin-top: 3px;
            border-top: 1px solid #ccc;
            padding-top: 3px;
        }

        /* Tableau principal */
        table.balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8px;
        }

        table.balance-table th {
            border: 1px solid #000;
            padding: 4px 3px;
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }

        table.balance-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 8px;
        }

        .col-compte {
            width: 12%;
            text-align: left;
        }

        .col-intitule {
            width: 38%;
            text-align: left;
        }

        .col-montant {
            width: 12.5%;
            text-align: right;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }
        
        .nowrap {
            white-space: nowrap;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 10px;
            left: 20px;
            right: 20px;
            font-size: 7px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 3px;
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
    </style>
</head>

<body>
    <div class="watermark">COMPTAFLOW</div>

    <!-- En-tête -->
    <div class="header-container">
        <div class="header-row">
            <div class="header-left">
                <strong>{{ $company_name }}</strong><br>
                <span style="font-size: 8px; color: #d97706; font-style: italic;">Impression définitive</span>
            </div>
            <div class="header-center">
                <div class="main-title">Balance des Tiers</div>
                <div class="sub-title">Complète</div>
            </div>
            <div class="header-right">
                Période du {{ \Carbon\Carbon::parse($date_debut)->format('d/m/y') }}<br>
                au {{ \Carbon\Carbon::parse($date_fin)->format('d/m/y') }}<br>
                Tenue de compte : {{ $user->company->currency ?? 'FCFA' }}
            </div>
        </div>
        <div class="meta-info">
            <div class="header-row">
                <div class="header-left">
                    © ComptaFlow - Logiciel de comptabilité
                </div>
                <div class="header-center">
                    Date de tirage : {{ \Carbon\Carbon::now()->format('d/m/y') }} à {{ \Carbon\Carbon::now()->format('H:i:s') }}
                </div>
                <div class="header-right">
                    Tiers : {{ $compte }} → {{ $compte_2 }}
                </div>
            </div>
        </div>
    </div>

    @php
        // Grouper par plan_tiers_id (le tiers concerné)
        $grouped = $ecritures->groupBy('plan_tiers_id');
        
        // Trier les groupes par numero_de_tiers
        $grouped = $grouped->sortBy(function ($operations) {
            return $operations->first()->planTiers->numero_de_tiers ?? '';
        });

        $totalMouvementDebit = 0;
        $totalMouvementCredit = 0;
        $totalSoldeDebit = 0;
        $totalSoldeCredit = 0;
    @endphp

    <table class="balance-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-compte">N° TIERS</th>
                <th rowspan="2" class="col-intitule">INTITULÉ DE TIERS</th>
                <th colspan="2">MOUVEMENTS</th>
                <th colspan="2">SOLDES</th>
            </tr>
            <tr>
                <th class="col-montant">DÉBIT</th>
                <th class="col-montant">CRÉDIT</th>
                <th class="col-montant">DÉBITEUR</th>
                <th class="col-montant">CRÉDITEUR</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grouped as $tiersId => $operations)
                @php
                    $planTiers = $operations->first()->planTiers;
                    if (!$planTiers) continue;
                    $numeroTiers = $planTiers->numero_de_tiers;
                    $intituleComplet = $planTiers->intitule ?? 'Intitulé inconnu';
                    
                    $intitule = mb_strlen($intituleComplet) > 40
                        ? mb_substr($intituleComplet, 0, 40) . '...'
                        : $intituleComplet;

                    $totalDebit = $operations->sum('debit');
                    $totalCredit = $operations->sum('credit');

                    $solde = $totalDebit - $totalCredit;
                    $soldeDebit = $solde > 0 ? $solde : 0;
                    $soldeCredit = $solde < 0 ? abs($solde) : 0;

                    $totalMouvementDebit += $totalDebit;
                    $totalMouvementCredit += $totalCredit;
                    $totalSoldeDebit += $soldeDebit;
                    $totalSoldeCredit += $soldeCredit;
                @endphp
                <tr>
                    <td class="col-compte">{{ $numeroTiers }}</td>
                    <td class="col-intitule">{{ $intitule }}</td>
                    <td class="col-montant nowrap">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                    <td class="col-montant nowrap">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                    <td class="col-montant nowrap">{{ number_format($soldeDebit, 0, ',', ' ') }}</td>
                    <td class="col-montant nowrap">{{ number_format($soldeCredit, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="right bold">Totaux généraux</td>
                <td class="col-montant nowrap">{{ number_format($totalMouvementDebit, 0, ',', ' ') }}</td>
                <td class="col-montant nowrap">{{ number_format($totalMouvementCredit, 0, ',', ' ') }}</td>
                <td class="col-montant nowrap">{{ number_format($totalSoldeDebit, 0, ',', ' ') }}</td>
                <td class="col-montant nowrap">{{ number_format($totalSoldeCredit, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer avec pagination -->
    <div class="footer">
        Impression générée par {{ $user->name ?? 'Utilisateur inconnu' }} le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i:s') }}
    </div>

    <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $size = 7;

        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $text = "{PAGE_NUM} / {PAGE_COUNT}";
        $textWidth = $fontMetrics->get_text_width($text, $font, $size);

        // Positionner à droite, aligné sur la même ligne que le footer
        $x = $w - $textWidth - 20; 
        $y = $h - 13;              

        $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);
    }
    </script>

</body>
</html>
