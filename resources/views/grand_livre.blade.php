<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Grand-livre des comptes</title>
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
                <td class="left"></td>
                <td class="right">Page : 1</td>
            </tr>
        </table>

        <h2 class="center">Grand-livre des comptes</h2>
        <div class="center bold">Du {{ $compte }} au {{ $compte_2 }}</div>
    </div> --}}

    @php
        // Trier les écritures par numéro de compte
        $ecritures = $ecritures->sortBy(function ($item) {
            return $item->planComptable->numero_de_compte ?? 0;
        });

        // Grouper ensuite par plan_comptable_id
        $grouped = $ecritures->groupBy('plan_comptable_id');

        $totaux_debit = 0;
        $totaux_credit = 0;
    @endphp


    @foreach ($grouped as $compteId => $operations)
        {{-- @php
            $intituleComplet = $operations->first()->planComptable->intitule ?? 'Intitulé inconnu';
            $intitule = strlen($intituleComplet) > 30 ? substr($intituleComplet, 0, 30) . '...' : $intituleComplet;

            $numero = $operations->first()->planComptable->numero_de_compte ?? '-';
            $solde = 0;
            $total_debit = 0;
            $total_credit = 0;
        @endphp --}}

        @php
            if ($titre === 'Grand-livre des comptes' || $titre === 'Prévisualisation Grand-livre des comptes') {
                $intituleComplet = optional($operations->first()->planComptable)->intitule ?? 'Intitulé inconnu';
                $numero = optional($operations->first()->planComptable)->numero_de_compte ?? '-';
            } else {
                $intituleComplet = optional($operations->first()->planTiers)->intitule ?? 'Intitulé inconnu';
                $numero = optional($operations->first()->planTiers)->numero_de_tiers ?? '-';
            }

            $intitule = strlen($intituleComplet) > 100 ? substr($intituleComplet, 0, 100) . '...' : $intituleComplet;

            $solde = 0;
            $total_debit = 0;
            $total_credit = 0;
        @endphp


        <br>
        <div class="section-title">
            Compte {{ $numero }} - {{ $intitule }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Compte général</th>
                    <th>Tiers</th>
                    <th>C.J</th>
                    <th>N° Saisie</th>
                    <th>Libellé écriture</th>
                    <th>Let</th>
                    <th class="right">Débit</th>
                    <th class="right">Crédit</th>
                    <th class="right">Solde progressif</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($operations as $ecriture)
                    @php
                        $debit = $ecriture->debit ?? 0;
                        $credit = $ecriture->credit ?? 0;
                        $solde += $debit - $credit;
                        $total_debit += $debit;
                        $total_credit += $credit;
                    @endphp
                    <tr>
                        <td class="nowrap">{{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }}</td>
                        <td>{{ $ecriture->planComptable->numero_de_compte ?? '-' }}</td>
                        <td>{{ $ecriture->planTiers->numero_de_tiers ?? '-' }}</td>
                        <td>{{ $ecriture->codeJournal->code_journal ?? '-' }}</td>
                        <td>{{ $ecriture->n_saisie ?? '-' }}</td>
                        <td>{{ $ecriture->description_operation }}</td>
                        <td>{{ $ecriture->lettrage ?? '' }}</td>
                        <td class="right nowrap">{{ number_format($debit, 0, ',', ' ') }}</td>
                        <td class="right nowrap">{{ number_format($credit, 0, ',', ' ') }}</td>
                        <td class="right nowrap">{{ number_format($solde, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                <tr class="bold">
                    <td colspan="7" class="right">Total compte {{ $numero }}</td>
                    <td class="right nowrap">{{ number_format($total_debit, 0, ',', ' ') }}</td>
                    <td class="right nowrap">{{ number_format($total_credit, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        @php
            $totaux_debit += $total_debit;
            $totaux_credit += $total_credit;
        @endphp
    @endforeach

    <br>
    <table>
        <tr class="bold">
            <td colspan="7" class="right">Totaux</td>
            <td class="right">{{ number_format($totaux_debit, 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($totaux_credit, 0, ',', ' ') }}</td>
            <td class="right">{{ number_format($totaux_debit - $totaux_credit, 0, ',', ' ') }}</td>
        </tr>
    </table>

    <!-- Footer avec pagination -->
    <div class="footer">
        <div
            style="width: 100%; display: flex; justify-content: space-between; font-size: 10px; border-top: 1px solid #000; padding-top: 5px;">
            <div style="text-align: center">
                Impression générée par {{ $user->name ?? 'Utilisateur inconnu' }}
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
