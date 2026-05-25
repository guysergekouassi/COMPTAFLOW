    <style>
        @page {
            margin: 110px 20px 35px 20px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            margin: 0;
            color: #000;
            background: #fff;
        }

        .container {
            width: 100%;
        }

        /* En-tête global complet en position FIXE */
        #header-box {
            position: fixed;
            top: -95px;
            left: 0;
            right: 0;
            height: 75px;
            border: 1px solid #000;
            padding: 5px;
        }

        .header-top-row {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-top-row td {
            border: none !important;
            vertical-align: top;
            padding: 2px 5px;
        }

        .header-bottom-bar {
            border-top: 1px solid #ccc;
            padding-top: 3px;
            font-size: 8.5px;
            color: #333;
        }

        .header-bottom-row {
            width: 100%;
            border-collapse: collapse;
        }

        .header-bottom-row td {
            border: none !important;
            padding: 0 5px;
        }

        .company-name {
            font-weight: bold;
            font-size: 11px;
            text-transform: lowercase;
        }
        
        .title-large {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .title-sub {
            font-size: 9px;
            text-align: center;
            font-weight: normal;
        }

        .info-right {
            text-align: right;
            font-size: 9px;
        }

        .subtitle-range {
            text-align: center;
            font-weight: bold;
            font-size: 9.5px;
            margin-top: 2px;
        }

        /* Style des blocs de compte (Box style comme l'image) */
        .account-box {
            margin-bottom: 20px;
        }

        .account-box-header {
            background-color: #f3f4f6;
            padding: 6px 10px;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: -1px; /* Pour fusionner avec la table */
        }

        /* Table de données avec bordures noires (Non déteint) */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            table-layout: fixed;
        }

        table.data-table th {
            background-color: #f9fafb;
            border: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        table.data-table td {
            padding: 4px 6px;
            border: 1px solid #000;
            font-size: 9px;
            vertical-align: middle;
        }

        /* Lignes spéciales */
        .initial-balance-row {
            background-color: #fafafa;
            font-style: italic;
        }

        .account-total-row {
            font-weight: bold;
            background-color: #fff;
        }

        /* Colonnes montants */
        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .right { text-align: right; }

        /* Pied de page */
        .page-footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #333;
            text-align: right;
        }

        .page-break {
            page-break-after: always;
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

    <!-- Header Sage 100 Restauration Complète en position FIXE -->
    <div id="header-box">
        <table class="header-top-row">
            <tr>
                <td style="width: 30%;">
                    <div class="company-name">{{ $company_name }}</div>
                    <div style="font-size: 9px;">Impression définitive</div>
                </td>
                <td style="width: 40%;">
                    <div class="title-large">{{ $titre }}</div>
                    <div class="title-sub">Complet</div>
                    @if(isset($compte) && isset($compte_2))
                        <div class="subtitle-range">Du {{ $compte }} au {{ $compte_2 }}</div>
                    @endif
                </td>
                <td style="width: 30%;" class="info-right">
                    Période du {{ \Carbon\Carbon::parse($date_debut)->format('d/m/y') }}<br>
                    au {{ \Carbon\Carbon::parse($date_fin)->format('d/m/y') }}<br>
                    Tenue de compte : {{ $user->company->currency ?? 'FCFA' }}
                </td>
            </tr>
        </table>

        <div class="header-bottom-bar">
            <table class="header-bottom-row">
                <tr>
                    <td style="width: 33%; text-align: left;">© ComptaFlow - Logiciel de comptabilité</td>
                    <td style="width: 34%; text-align: center;">Date de tirage : {{ now()->format('d/m/y à H:i:s') }}</td>
                    <td style="width: 33%; text-align: right; color: transparent;">Page : 000</td> <!-- Rendu transparent car dessiné dynamiquement par le canvas PHP -->
                </tr>
            </table>
        </div>
    </div>

    @php
        $pages = $paginatedData['pages'];
        $grandTotalDebit = $paginatedData['grand_total_debit'];
        $grandTotalCredit = $paginatedData['grand_total_credit'];
    @endphp

    @foreach ($pages as $index => $page)
        <div class="container {{ $index < count($pages) - 1 ? 'page-break' : '' }}">

            @php $tableOpen = false; @endphp

            @foreach ($page as $row)
                @if ($row['type'] === 'account_spacer')
                    @if($tableOpen) </tbody></table></div> @php $tableOpen = false; @endphp @endif
                    <div style="height: 15px;"></div>

                @elseif ($row['type'] === 'account_header')
                    @if($tableOpen) </tbody></table></div> @endif
                    <div class="account-box">
                        <div class="account-box-header">
                            Compte {{ $row['numero'] }} - {{ $row['intitule'] }}
                            @if(isset($row['numero_secondaire']) && $row['numero_secondaire'])
                                <span style="font-weight:normal; font-size:9px; margin-left:10px;">({{ $row['numero_secondaire'] }})</span>
                            @endif
                        </div>
                        <table class="data-table">
                        <colgroup>
                            <col style="width: 7%;">
                            <col style="width: 8%;">
                            <col style="width: 8%;">
                            <col style="width: 4%;">
                            <col style="width: 7%;">
                            <col style="width: 7%;">
                            <col style="width: 24%;">
                            <col style="width: 3%;">
                            <col style="width: 11%;">
                            <col style="width: 11%;">
                            <col style="width: 10%;">
                        </colgroup>
                        <tbody>
                        @php $tableOpen = true; @endphp

                @elseif ($row['type'] === 'table_header')
                            @if(!$tableOpen)
                                <div class="account-box">
                                <table class="data-table">
                                <colgroup>
                                    <col style="width: 7%;">
                                    <col style="width: 8%;">
                                    <col style="width: 8%;">
                                    <col style="width: 4%;">
                                    <col style="width: 7%;">
                                    <col style="width: 7%;">
                                    <col style="width: 24%;">
                                    <col style="width: 3%;">
                                    <col style="width: 11%;">
                                    <col style="width: 11%;">
                                    <col style="width: 10%;">
                                </colgroup>
                                <tbody>
                                @php $tableOpen = true; @endphp
                            @endif
                            <tr style="background-color: #f3f4f6;">
                                <th style="width: 7%;">Date</th>
                                <th style="width: 8%;">compte</th>
                                <th style="width: 8%;">tiers</th>
                                <th style="width: 4%;">C.J</th>
                                <th style="width: 7%;">N° pièce</th>
                                <th style="width: 7%;">N° saisie</th>
                                <th style="width: 24%;">Libellé écriture</th>
                                <th style="width: 3%;">Let</th>
                                <th style="width: 11%;">Mouvement débit</th>
                                <th style="width: 11%;">Mouvement crédit</th>
                                <th style="width: 10%;">Solde progressif</th>
                            </tr>

                @elseif ($row['type'] === 'initial_balance')
                            @if(!$tableOpen)
                                <div class="account-box"><table class="data-table">
                                <colgroup>
                                    <col style="width: 7%;">
                                    <col style="width: 8%;">
                                    <col style="width: 8%;">
                                    <col style="width: 4%;">
                                    <col style="width: 7%;">
                                    <col style="width: 7%;">
                                    <col style="width: 24%;">
                                    <col style="width: 3%;">
                                    <col style="width: 11%;">
                                    <col style="width: 11%;">
                                    <col style="width: 10%;">
                                </colgroup>
                                <tbody>
                                @php $tableOpen = true; @endphp
                            @endif
                            <tr class="initial-balance-row">
                                <td colspan="8" class="bold" style="text-align: right; padding-right: 20px;">REPORT SOLDE INITIAL (OUVERTURE)</td>
                                <td class="amount">{{ $row['debit'] != 0 ? number_format($row['debit'], 0, ',', ' ') : '0' }}</td>
                                <td class="amount">{{ $row['credit'] != 0 ? number_format($row['credit'], 0, ',', ' ') : '0' }}</td>
                                <td class="amount">{{ number_format($row['solde'], 0, ',', ' ') }}</td>
                            </tr>

                @elseif ($row['type'] === 'entry_line')
                            @if(!$tableOpen)
                                <div class="account-box"><table class="data-table">
                                <colgroup>
                                    <col style="width: 7%;">
                                    <col style="width: 8%;">
                                    <col style="width: 8%;">
                                    <col style="width: 4%;">
                                    <col style="width: 7%;">
                                    <col style="width: 7%;">
                                    <col style="width: 24%;">
                                    <col style="width: 3%;">
                                    <col style="width: 11%;">
                                    <col style="width: 11%;">
                                    <col style="width: 10%;">
                                </colgroup>
                                <tbody>
                                @php $tableOpen = true; @endphp
                            @endif
                            @php $data = $row['data']; @endphp
                            <tr>
                                <td class="center">{{ \Carbon\Carbon::parse($data['date'])->format('d/m/y') }}</td>
                                <td style="font-size: 8px;">{!! $data['aff_compte'] !!}</td>
                                <td style="font-size: 8px;">{!! $data['aff_tiers'] !!}</td>
                                <td class="center">{!! $data['aff_jl'] !!}</td>
                                <td class="center">{{ $data['n_piece'] }}</td>
                                <td class="center">{!! $data['aff_n_saisie'] !!}</td>
                                <td>{{ $data['libelle'] }}</td>
                                <td class="center">{{ $data['lettrage'] }}</td>
                                <td class="amount">{{ $data['debit'] != 0 ? number_format($data['debit'], 0, ',', ' ') : '' }}</td>
                                <td class="amount">{{ $data['credit'] != 0 ? number_format($data['credit'], 0, ',', ' ') : '' }}</td>
                                <td class="amount">{{ number_format($data['solde_progressif'], 0, ',', ' ') }}</td>
                            </tr>

                @elseif ($row['type'] === 'account_total')
                            @if($tableOpen)
                                <tr class="account-total-row">
                                    <td colspan="8" style="text-align: right; padding-right: 20px;">Total compte {{ $row['numero'] }}</td>
                                    <td class="amount">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                                    <td class="amount">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                                    <td class="amount"></td>
                                </tr>
                                </tbody></table></div>
                                @php $tableOpen = false; @endphp
                            @endif

                @elseif ($row['type'] === 'to_report')
                            @if($tableOpen)
                                <tr style="background-color: #f1f5f9; font-weight: bold; border-top: 1px solid #333;">
                                    <td colspan="8" style="text-align: right; padding-right: 20px;">A REPORTER</td>
                                    <td class="amount">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                                    <td class="amount">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                                    <td></td>
                                </tr>
                            @endif

                @elseif ($row['type'] === 'reported')
                            @if(!$tableOpen)
                                <div class="account-box"><table class="data-table">
                                <colgroup>
                                    <col style="width: 7%;">
                                    <col style="width: 8%;">
                                    <col style="width: 8%;">
                                    <col style="width: 4%;">
                                    <col style="width: 7%;">
                                    <col style="width: 7%;">
                                    <col style="width: 24%;">
                                    <col style="width: 3%;">
                                    <col style="width: 11%;">
                                    <col style="width: 11%;">
                                    <col style="width: 10%;">
                                </colgroup>
                                <tbody>
                                @php $tableOpen = true; @endphp
                            @endif
                            <tr style="background-color: #f1f5f9; font-weight: bold;">
                                <td colspan="8" style="text-align: right; padding-right: 20px;">REPORT</td>
                                <td class="amount">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                                <td class="amount">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                                <td></td>
                            </tr>
                @endif
            @endforeach

            @if($tableOpen) </tbody></table></div> @endif


        </div>
    @endforeach

    @if($grandTotalDebit > 0)
        <div style="margin-top: 20px; border: 1px solid #333; padding: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="font-weight: bold; font-size: 11px;">
                    <td style="width: 70%; text-align: right; padding-right: 20px;">TOTAUX GÉNÉRAUX</td>
                    <td class="amount" style="width: 10%;">{{ number_format($grandTotalDebit, 0, ',', ' ') }}</td>
                    <td class="amount" style="width: 10%;">{{ number_format($grandTotalCredit, 0, ',', ' ') }}</td>
                    <td class="amount" style="width: 10%;">{{ number_format($grandTotalDebit - $grandTotalCredit, 0, ',', ' ') }}</td>
                </tr>
            </table>
        </div>
    @endif



    <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("helvetica", "normal");
        $size = 7;
        $w = $pdf->get_width();
        $h = $pdf->get_height();
        
        // Page number centré en bas
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $textWidth = $fontMetrics->get_text_width($text, $font, $size);
        $x = ($w - $textWidth) / 2; 
        $y = $h - 25;              
        $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);

        // Impression générée par... à gauche
        $user_name = "{{ $user->name ?? 'Utilisateur inconnu' }}";
        $leftText = "© ComptaFlow | Impression générée par " . $user_name;
        $pdf->page_text(20, $y, $leftText, $font, 7, [0,0,0]);

        // Date de tirage à droite
        $dateText = "tirage le {{ now()->format('d/m/Y H:i') }}";
        $dateTextWidth = $fontMetrics->get_text_width($dateText, $font, 7);
        $pdf->page_text($w - $dateTextWidth - 20, $y, $dateText, $font, 7, [0,0,0]);

        // Page number en haut à droite dans le header-box (y = 86)
        $headerPageText = "Page : {PAGE_NUM} / {PAGE_COUNT}";
        $headerPageTextWidth = $fontMetrics->get_text_width($headerPageText, $font, 8.5);
        $pdf->page_text($w - $headerPageTextWidth - 25, 86, $headerPageText, $font, 8.5, [0,0,0]);
    }
    </script>
</body>
</html>
