<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Grand Livre Analytique – {{ $exercice->intitule ?? 'N/A' }}</title>
    <style>
        @page {
            margin: 115px 18px 45px 18px;
            size: A4 landscape;
        }
        /* ── FIXED HEADER ── */
        header {
            position: fixed;
            top: -98px;
            left: 0; right: 0;
            height: 86px;
            border: 1px solid #1e40af;
            padding: 4px 8px;
            background: #fff;
        }
        /* ── FIXED FOOTER ── */
        footer {
            position: fixed;
            bottom: -36px;
            left: 0; right: 0;
            height: 28px;
            border-top: 1px solid #bfdbfe;
            padding: 3px 8px;
            background: #fff;
        }
        .footer-tbl { display: table; width: 100%; }
        .f-l { display: table-cell; text-align: left;   width: 40%; font-size: 7.5px; color: #64748b; }
        .f-c { display: table-cell; text-align: center; width: 20%; font-size: 7.5px; color: #64748b; }
        .f-r { display: table-cell; text-align: right;  width: 40%; font-size: 7.5px; color: #64748b; }
        .page-counter::before { content: counter(page); }

        /* ── BODY ── */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #1a1a2e;
            margin: 0; padding: 0;
        }
        /* ── WATERMARK ── */
        .watermark {
            position: fixed;
            top: 36%; left: 0; width: 100%;
            text-align: center;
            opacity: 0.04;
            transform: rotate(-45deg);
            font-size: 100px; font-weight: bold;
            color: #1e40af; z-index: -1000;
            text-transform: uppercase; pointer-events: none;
        }
        /* ── HEADER TABLE ── */
        .hdr-tbl { width: 100%; border-collapse: collapse; }
        .hdr-tbl td { vertical-align: middle; padding: 2px 4px; }
        .co-name  { font-weight: bold; font-size: 10.5px; text-transform: uppercase; color: #1e40af; }
        .co-sub   { font-size: 7.5px; color: #64748b; margin-top: 1px; }
        .doc-title { text-align: center; font-weight: bold; font-size: 12px; text-transform: uppercase; color: #1e3a8a; }
        .doc-sub   { text-align: center; font-size: 8px; color: #64748b; margin-top: 2px; }
        .period-info { text-align: right; font-size: 8px; color: #334155; line-height: 1.5; }
        .hdr-meta { font-size: 7.5px; color: #64748b; padding-top: 2px; }

        /* ── SECTION HEADER ROW ── */
        .sec-hdr { background: #1e3a8a; color: #fff; font-weight: bold; font-size: 8.5px;
                   padding: 4px 8px; letter-spacing: 0.03em; }
        .sec-badge { display: inline-block; background: #dbeafe; color: #1e40af;
                     border-radius: 3px; padding: 1px 5px; font-size: 7.5px; font-weight: bold; }

        /* ── ACCOUNT HEADER ── */
        .acc-hdr { background: #e0e7ff; color: #1e3a8a; font-weight: bold;
                   font-size: 8px; padding: 3px 8px; border-left: 3px solid #1e40af; }

        /* ── DATA TABLE ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        thead th {
            background: #1e40af; color: #fff;
            padding: 5px 6px; text-align: left;
            text-transform: uppercase; font-size: 7px;
            letter-spacing: 0.04em; border: none;
        }
        thead th.tr { text-align: right; }
        thead th.tc { text-align: center; }

        /* Sage-style: spans for merged col headers */
        .th-grp { background: #1e3a8a; color: #fff; text-align: center;
                  font-size: 6.5px; text-transform: uppercase; letter-spacing: 0.04em;
                  padding: 3px 4px; border-right: 1px solid rgba(255,255,255,0.3); }
        .th-sub { background: #1e40af; color: #fff; text-align: center;
                  font-size: 6.5px; text-transform: uppercase; letter-spacing: 0.04em;
                  padding: 3px 4px; border-right: 1px solid rgba(255,255,255,0.2); }

        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8px; color: #334155; }
        tbody td.tr { text-align: right; }
        tbody td.tc { text-align: center; }
        .fw { font-weight: bold; }

        /* ── TOTAL ROWS ── */
        .subtot td {
            background: #dbeafe; color: #1e3a8a; font-weight: bold;
            padding: 4px 6px; font-size: 8px; border-top: 1.5px solid #1e40af;
        }
        .grandtot td {
            background: #1e3a8a; color: #fff; font-weight: bold;
            padding: 6px 8px; font-size: 8.5px; border: none;
        }
        .col-d  { color: #16a34a; }
        .col-c  { color: #dc2626; }
        .col-db { color: #16a34a; font-weight: bold; }
        .col-cb { color: #dc2626; font-weight: bold; }
        .col-dw { color: #86efac; font-weight: bold; }
        .col-cw { color: #fca5a5; font-weight: bold; }
    </style>
</head>
<body>

<div class="watermark">COMPTAFLOW</div>

<header>
    <table class="hdr-tbl">
        <tr>
            <td style="width:30%; border-right:1px solid #1e40af;">
                <div class="co-name">{{ $company->company_name ?? 'MA COMPAGNIE' }}</div>
                <div class="co-sub">© ComptaFlow – Logiciel de comptabilité</div>
            </td>
            <td style="width:40%;">
                <div class="doc-title">Grand Livre Analytique</div>
                <div class="doc-sub">
                    @if($section)
                        Section : <strong>{{ $section->code }}</strong> – {{ $section->libelle }}
                    @else
                        Toutes les sections
                    @endif
                </div>
            </td>
            <td style="width:30%; border-left:1px solid #1e40af; text-align:right;">
                <div class="period-info">
                    Exercice : <strong>{{ $exercice->intitule ?? 'N/A' }}</strong><br>
                    @if(isset($exercice) && $exercice->date_debut && $exercice->date_fin)
                        Période : {{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }}
                        → {{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}<br>
                    @endif
                    Monnaie : {{ $company->currency ?? 'FCFA' }}
                </div>
            </td>
        </tr>
        <tr style="border-top:1px solid #bfdbfe;">
            <td class="hdr-meta">Généré le {{ date('d/m/Y à H:i') }}</td>
            <td class="hdr-meta" style="text-align:center;">{{ $results->count() }} mouvement(s)</td>
            <td class="hdr-meta" style="text-align:right;">Page : <span class="page-counter"></span></td>
        </tr>
    </table>
</header>

<footer>
    <div class="footer-tbl">
        <div class="f-l">© ComptaFlow – Solution de gestion comptable</div>
        <div class="f-c">Grand Livre Analytique — {{ $exercice->intitule ?? '' }}</div>
        <div class="f-r">Page <span class="page-counter"></span></div>
    </div>
</footer>

@if($results->isEmpty())
    <table style="margin-top: 10px; width: 100%;">
        <thead>
            {{-- Grouped header: Mouvements / Soldes ── --}}
            <tr>
                <th style="width:58px;">Date</th>
                <th style="width:35px;" class="tc">C.j</th>
                <th style="width:75px;">N° Pièce</th>
                <th>Libellé écriture</th>
                {{-- Mouvements group --}}
                <th colspan="2" class="tc th-grp" style="border-left:1px solid rgba(255,255,255,0.3); width:160px;">Mouvements</th>
                {{-- Soldes group --}}
                <th colspan="2" class="tc th-grp" style="border-left:1px solid rgba(255,255,255,0.3); width:160px;">Soldes</th>
            </tr>
            <tr>
                <th style="width:58px;"></th>
                <th style="width:35px;"></th>
                <th style="width:75px;"></th>
                <th></th>
                <th class="tr th-sub" style="width:80px; border-left:1px solid rgba(255,255,255,0.3);">Débits</th>
                <th class="tr th-sub" style="width:80px;">Crédits</th>
                <th class="tr th-sub" style="width:80px; border-left:1px solid rgba(255,255,255,0.3);">Débiteurs</th>
                <th class="tr th-sub" style="width:80px;">Créditeurs</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" class="tc text-center" style="padding: 20px; color: #94a3b8; font-style: italic;">
                    Aucun mouvement trouvé pour les critères sélectionnés.
                </td>
            </tr>
        </tbody>
    </table>
@else
    @php
        $grandDebit  = 0;
        $grandCredit = 0;
        // Group by section then by account
        $bySect = $results->groupBy('section_code');
    @endphp

    @foreach($bySect as $sectCode => $sectItems)
        @php
            $sectDebit  = 0;
            $sectCredit = 0;
            $first = $sectItems->first();
            $byAcc = $sectItems->groupBy('numero_de_compte');
        @endphp

        {{-- ── Section header ── --}}
        <table style="margin-bottom:0; margin-top:8px;">
            <tbody>
                <tr>
                    <td colspan="8" class="sec-hdr">
                        <span class="sec-badge">{{ $sectCode }}</span>
                        &nbsp;{{ $first->section_libelle ?? $sectCode }}
                    </td>
                </tr>
            </tbody>
        </table>

        @foreach($byAcc as $accNum => $accItems)
            @php
                $accDebit  = 0;
                $accCredit = 0;
                $accFirst  = $accItems->first();
            @endphp

            {{-- ── Account header + column headers ── --}}
            <table style="margin-bottom:0;">
                <thead>
                    <tr>
                        <td colspan="8" class="acc-hdr">
                            {{ $accNum }} – {{ $accFirst->compte_libelle }}
                        </td>
                    </tr>
                    {{-- Grouped header: Mouvements / Soldes ── --}}
                    <tr>
                        <th style="width:58px;">Date</th>
                        <th style="width:35px;" class="tc">C.j</th>
                        <th style="width:75px;">N° Pièce</th>
                        <th>Libellé écriture</th>
                        {{-- Mouvements group --}}
                        <th colspan="2" class="tc th-grp" style="border-left:1px solid rgba(255,255,255,0.3); width:160px;">Mouvements</th>
                        {{-- Soldes group --}}
                        <th colspan="2" class="tc th-grp" style="border-left:1px solid rgba(255,255,255,0.3); width:160px;">Soldes</th>
                    </tr>
                    <tr>
                        <th style="width:58px;"></th>
                        <th style="width:35px;"></th>
                        <th style="width:75px;"></th>
                        <th></th>
                        <th class="tr th-sub" style="width:80px; border-left:1px solid rgba(255,255,255,0.3);">Débits</th>
                        <th class="tr th-sub" style="width:80px;">Crédits</th>
                        <th class="tr th-sub" style="width:80px; border-left:1px solid rgba(255,255,255,0.3);">Débiteurs</th>
                        <th class="tr th-sub" style="width:80px;">Créditeurs</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runDebit = 0; $runCredit = 0; @endphp
                    @foreach($accItems as $row)
                        @php
                            $accDebit  += $row->mouvement_debit;
                            $accCredit += $row->mouvement_credit;
                            $sectDebit  += $row->mouvement_debit;
                            $sectCredit += $row->mouvement_credit;
                            $grandDebit  += $row->mouvement_debit;
                            $grandCredit += $row->mouvement_credit;
                            $runDebit  += $row->mouvement_debit;
                            $runCredit += $row->mouvement_credit;
                            $solde = $runDebit - $runCredit;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                            <td class="tc fw">{{ $row->code_journal }}</td>
                            <td>{{ $row->reference_piece ?? $row->n_saisie }}</td>
                            <td>{{ $row->description_operation }}</td>
                            <td class="tr {{ $row->mouvement_debit > 0 ? 'col-db' : '' }}">
                                {{ $row->mouvement_debit > 0 ? number_format($row->mouvement_debit, 2, ',', ' ') : '' }}
                            </td>
                            <td class="tr {{ $row->mouvement_credit > 0 ? 'col-cb' : '' }}">
                                {{ $row->mouvement_credit > 0 ? number_format($row->mouvement_credit, 2, ',', ' ') : '' }}
                            </td>
                            <td class="tr {{ $solde > 0 ? 'col-d' : '' }}">
                                {{ $solde > 0 ? number_format($solde, 2, ',', ' ') : '' }}
                            </td>
                            <td class="tr {{ $solde < 0 ? 'col-c' : '' }}">
                                {{ $solde < 0 ? number_format(abs($solde), 2, ',', ' ') : '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @php
                        $accSolde = $accDebit - $accCredit;
                    @endphp
                    <tr class="subtot">
                        <td colspan="4" class="fw">Total compte {{ $accNum }}</td>
                        <td class="tr fw col-db">{{ number_format($accDebit, 2, ',', ' ') }}</td>
                        <td class="tr fw col-cb">{{ number_format($accCredit, 2, ',', ' ') }}</td>
                        <td class="tr fw {{ $accSolde >= 0 ? 'col-db' : '' }}">
                            {{ $accSolde >= 0 ? number_format($accSolde, 2, ',', ' ') : '' }}
                        </td>
                        <td class="tr fw {{ $accSolde < 0 ? 'col-cb' : '' }}">
                            {{ $accSolde < 0 ? number_format(abs($accSolde), 2, ',', ' ') : '' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endforeach

        {{-- ── Section total ── --}}
        @php $sectSolde = $sectDebit - $sectCredit; @endphp
        <table style="margin-bottom:4px;">
            <tfoot>
                <tr class="subtot" style="background:#c7d2fe;">
                    <td colspan="4" class="fw" style="color:#1e3a8a;">Total {{ $sectCode }} — {{ $first->section_libelle ?? $sectCode }}</td>
                    <td class="tr fw col-db">{{ number_format($sectDebit, 2, ',', ' ') }}</td>
                    <td class="tr fw col-cb">{{ number_format($sectCredit, 2, ',', ' ') }}</td>
                    <td class="tr fw {{ $sectSolde >= 0 ? 'col-db' : '' }}">
                        {{ $sectSolde >= 0 ? number_format($sectSolde, 2, ',', ' ') : '' }}
                    </td>
                    <td class="tr fw {{ $sectSolde < 0 ? 'col-cb' : '' }}">
                        {{ $sectSolde < 0 ? number_format(abs($sectSolde), 2, ',', ' ') : '' }}
                    </td>
                </tr>
            </tfoot>
        </table>

    @endforeach

    {{-- ── Grand Total ── --}}
    @php $grandSolde = $grandDebit - $grandCredit; @endphp
    <table style="margin-top:10px;">
        <tfoot>
            <tr class="grandtot">
                <td colspan="4" class="fw">TOTAL GÉNÉRAL — {{ $results->count() }} mouvement(s)</td>
                <td class="tr fw col-dw">{{ number_format($grandDebit, 2, ',', ' ') }}</td>
                <td class="tr fw col-cw">{{ number_format($grandCredit, 2, ',', ' ') }}</td>
                <td class="tr fw {{ $grandSolde >= 0 ? 'col-dw' : '' }}">
                    {{ $grandSolde >= 0 ? number_format($grandSolde, 2, ',', ' ') : '' }}
                </td>
                <td class="tr fw {{ $grandSolde < 0 ? 'col-cw' : '' }}">
                    {{ $grandSolde < 0 ? number_format(abs($grandSolde), 2, ',', ' ') : '' }}
                </td>
            </tr>
        </tfoot>
    </table>
@endif

</body>
</html>
