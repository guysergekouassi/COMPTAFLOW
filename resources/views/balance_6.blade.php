<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Balance à 6 colonnes</title>
    <style>
        @page {
            margin: 100px 20px 60px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
        }

        header {
            top: -80px;
            left: 0;
            right: 0;
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 5px;
        }

        th {
            background: #eee;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .nowrap {
            white-space: nowrap;
        }

        .header-table td {
            padding: 2px 5px;
        }
    </style>
</head>

<body>
    <header>
        <table class="header-table">
            <tr>
                <td class="left bold">{{ $company_name }}</td>
                <td class="right">
                    <span class="bold">Période du</span> {{ \Carbon\Carbon::parse($date_debut)->format('d/m/y') }}<br>
                    <span class="bold">au</span> {{ \Carbon\Carbon::parse($date_fin)->format('d/m/y') }}
                </td>
            </tr>
        </table>
        <h2 class="center" style="margin:8px 0 2px 0;">BALANCE À 6 COLONNES</h2>
        <div class="center bold">Du {{ $compte }} au {{ $compte_2 }}</div>
    </header>

    @php
        // --- Préparation des données ---
        $ecritures = $ecritures->sortBy(fn($item) => $item->planComptable->numero_de_compte ?? 0);
        $grouped = $ecritures->groupBy('plan_comptable_id');

        $totalSI_D = $totalSI_C = 0;
        $totalMD = $totalMC = 0;
        $totalSF_D = $totalSF_C = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Compte</th>
                <th style="width: 25%;">Intitulé</th>
                <th class="right" style="width: 10%;">Solde initial D</th>
                <th class="right" style="width: 10%;">Solde initial C</th>
                <th class="right" style="width: 10%;">Mvt Débit</th>
                <th class="right" style="width: 10%;">Mvt Crédit</th>
                <th class="right" style="width: 10%;">Solde final D</th>
                <th class="right" style="width: 10%;">Solde final C</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grouped as $compte => $operations)
                @php
                    $plan = $operations->first()->planComptable;
                    $intitule = $plan->intitule ?? 'Intitulé inconnu';
                    $num = $plan->numero_de_compte ?? 'N/A';

                    // (À adapter selon ta logique d'ouverture)
                    $soldeInitialDebit = 0;
                    $soldeInitialCredit = 0;

                    $mouvementDebit = $operations->sum('debit');
                    $mouvementCredit = $operations->sum('credit');

                    $soldeFinal = ($soldeInitialDebit + $mouvementDebit) - ($soldeInitialCredit + $mouvementCredit);
                    $soldeFinalDebit = $soldeFinal > 0 ? $soldeFinal : 0;
                    $soldeFinalCredit = $soldeFinal < 0 ? abs($soldeFinal) : 0;

                    // Totaux
                    $totalSI_D += $soldeInitialDebit;
                    $totalSI_C += $soldeInitialCredit;
                    $totalMD += $mouvementDebit;
                    $totalMC += $mouvementCredit;
                    $totalSF_D += $soldeFinalDebit;
                    $totalSF_C += $soldeFinalCredit;
                @endphp
                <tr>
                    <td>{{ $num }}</td>
                    <td>{{ $intitule }}</td>
                    <td class="right">{{ number_format($soldeInitialDebit, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($soldeInitialCredit, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($mouvementDebit, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($mouvementCredit, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($soldeFinalDebit, 0, ',', ' ') }}</td>
                    <td class="right">{{ number_format($soldeFinalCredit, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td colspan="2" class="right">Totaux généraux</td>
                <td class="right">{{ number_format($totalSI_D, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalSI_C, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalMD, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalMC, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalSF_D, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalSF_C, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 10px; text-align: center; font-size: 9px; border-top: 1px solid #000; padding-top: 5px;">
        © Flow Compta — Généré par {{ $user->name ?? 'Utilisateur inconnu' }} le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 8;
            $w = $pdf->get_width();
            $h = $pdf->get_height();
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $textWidth = $fontMetrics->get_text_width($text, $font, $size);
            $x = $w - $textWidth - 10;
            $y = $h - 30;
            $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);
        }
    </script>
</body>

</html>
