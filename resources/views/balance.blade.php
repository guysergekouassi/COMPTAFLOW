<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Balance comptes</title>
    <style>
        /* Réserve de l’espace : top=100px, bottom=60px  */
        @page {
            margin: 100px 20px 60px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
        }

        header {
            /* position: fixed; */
            top: -80px;
            /* remonte dans la marge top réservée */
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
            text-align: left;
        }

        th {
            background: #eee;
        }

        .section-title {
            background: #ddd;
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

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 2px 5px;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>

    {{-- <div class="header">
        <table>
            <tr>
                <td class="left bold">{{ $company_name }}</td>
                <td class="right">
                    <span class="bold">Période du</span> {{ \Carbon\Carbon::parse($date_debut)->format('d/m/y') }}<br>
                    <span class="bold">au</span> {{ \Carbon\Carbon::parse($date_fin)->format('d/m/y') }}
                </td>
            </tr>
            <tr>
                <td class="left">© Flow Compta</td>
                <td class="right">Page : 1</td>
            </tr>
        </table>

        <h2 class="center">Balance des comptes</h2>
        <div class="center bold">Du {{ $compte }} au {{ $compte_2 }}</div>


    </div> --}}

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
        <h2 class="center" style="margin:8px 0 2px 0; text-transform:uppercase;">{{ $titre }}</h2>
        <div class="center bold">Du {{ $compte }} au {{ $compte_2 }}</div>
    </header>



    @php
        // Trier les écritures par numéro de compte
        $ecritures = $ecritures->sortBy(function ($item) {
            return $item->planComptable->numero_de_compte ?? 0;
        });

        // Grouper ensuite par plan_comptable_id
        $grouped = $ecritures->groupBy('plan_comptable_id');
        $totalMouvementDebit = 0;
        $totalMouvementCredit = 0;
        $totalSoldeDebit = 0;
        $totalSoldeCredit = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Compte général</th>
                <th style="width: 35%;">Intitulé</th>
                <th class="right" style="width: 12%;">Mouvement Débit</th>
                <th class="right" style="width: 12%;">Mouvement Crédit</th>
                <th class="right" style="width: 12%;">Solde Débit</th>
                <th class="right" style="width: 12%;">Solde Crédit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grouped as $compte => $operations)
                @php
                    $intituleComplet = $operations->first()->planComptable->intitule ?? 'Intitulé inconnu';
                    $intitule =
                        mb_strlen($intituleComplet) > 30
                            ? mb_substr($intituleComplet, 0, 30) . '...'
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
                    <td>{{ $operations->first()->planComptable->numero_de_compte ?? 'N/A' }}</td>
                    <td>{{ $intitule }}</td>
                    <td class="right nowrap">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                    <td class="right nowrap">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                    <td class="right nowrap">{{ number_format($soldeDebit, 0, ',', ' ') }}</td>
                    <td class="right nowrap">{{ number_format($soldeCredit, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bold">
                <td colspan="2" class="right">Totaux généraux</td>
                <td class="right">{{ number_format($totalMouvementDebit, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalMouvementCredit, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalSoldeDebit, 0, ',', ' ') }}</td>
                <td class="right">{{ number_format($totalSoldeCredit, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>


    <br>

    <table>
        <thead>
            <tr class="bold">
                <th colspan="2" class="right">Totaux généraux</th>
                <th class="right">{{ number_format($totalMouvementDebit, 0, ',', ' ') }}</th>
                <th class="right">{{ number_format($totalMouvementCredit, 0, ',', ' ') }}</th>
                <th class="right">{{ number_format($totalSoldeDebit, 0, ',', ' ') }}</th>
                <th class="right">{{ number_format($totalSoldeCredit, 0, ',', ' ') }}</th>
            </tr>
        </thead>
    </table>

    <!-- Footer avec pagination -->
    <div class="footer">
        <div
            style="width: 100%; display: flex; justify-content: space-between; font-size: 10px; border-top: 1px solid #000; padding-top: 5px;">
            <div style="text-align: center">
                © Flow Compta — Impression générée par {{ $user->name ?? 'Utilisateur inconnu' }}
                le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
            </div>
            <div id="pagination" style="text-align: center"></div>
        </div>
    </div>

    <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $size = 8;

        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $text = "{PAGE_NUM} / {PAGE_COUNT}";
        $textWidth = $fontMetrics->get_text_width($text, $font, $size);

        // Positionner à droite, aligné sur la même ligne que le footer
        $x = $w - $textWidth - 0; // marge droite
        $y = $h - 30;              // même hauteur que le footer

        $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);
    }
    </script>

</body>

</html>
