<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Balance Analytique – {{ $exercice->intitule ?? 'N/A' }}</title>
    <style>
        @page {
            margin: 115px 18px 45px 18px;
            size: A4 landscape;
        }
        /* ── FIXED HEADER ── */
        header {
            position: fixed;
            top: -98px; left: 0; right: 0;
            height: 86px;
            border: 1px solid #1e40af;
            padding: 4px 8px;
            background: #fff;
        }
        /* ── FIXED FOOTER ── */
        footer {
            position: fixed;
            bottom: -36px; left: 0; right: 0;
            height: 28px;
            border-top: 1px solid #bfdbfe;
            padding: 3px 8px; background: #fff;
        }
        .footer-tbl { display: table; width: 100%; }
        .f-l { display: table-cell; text-align: left;   width: 40%; font-size: 7.5px; color: #64748b; }
        .f-c { display: table-cell; text-align: center; width: 20%; font-size: 7.5px; color: #64748b; }
        .f-r { display: table-cell; text-align: right;  width: 40%; font-size: 7.5px; color: #64748b; }
        .page-counter::before { content: counter(page); }

        /* ── BODY ── */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px; color: #1a1a2e;
            margin: 0; padding: 0;
        }
        /* ── WATERMARK ── */
        .watermark {
            position: fixed; top: 36%; left: 0; width: 100%;
            text-align: center; opacity: 0.04; transform: rotate(-45deg);
            font-size: 100px; font-weight: bold; color: #1e40af;
            z-index: -1000; text-transform: uppercase; pointer-events: none;
        }
        /* ── HEADER TABLE ── */
        .hdr-tbl { width: 100%; border-collapse: collapse; }
        .hdr-tbl td { vertical-align: middle; padding: 2px 4px; }
        .co-name  { font-weight: bold; font-size: 10.5px; text-transform: uppercase; color: #1e40af; }
        .co-sub   { font-size: 7.5px; color: #64748b; margin-top: 1px; }
        .doc-title { text-align: center; font-weight: bold; font-size: 13px; text-transform: uppercase; color: #1e3a8a; }
        .doc-sub   { text-align: center; font-size: 8px; color: #64748b; margin-top: 2px; }
        .period-info { text-align: right; font-size: 8px; color: #334155; line-height: 1.5; }
        .hdr-meta { font-size: 7.5px; color: #64748b; padding-top: 2px; }

        /* ── MAIN TABLE ── */
        table { width: 100%; border-collapse: collapse; }

        /* Sage-style column group headers */
        .th-grp {
            background: #1e3a8a; color: #fff; text-align: center;
            font-size: 7px; text-transform: uppercase; letter-spacing: 0.04em;
            padding: 4px 4px; border-right: 1px solid rgba(255,255,255,0.3);
            font-weight: bold;
        }
        .th-sub {
            background: #1e40af; color: #fff; text-align: center;
            font-size: 6.5px; text-transform: uppercase; letter-spacing: 0.03em;
            padding: 3px 4px; border-right: 1px solid rgba(255,255,255,0.2);
        }
        /* Section label cols (sticky header style) */
        .th-plain {
            background: #1e40af; color: #fff; text-align: left;
            font-size: 7px; text-transform: uppercase; letter-spacing: 0.03em;
            padding: 4px 6px; border-right: 1px solid rgba(255,255,255,0.2);
        }

        /* Data rows */
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td {
            padding: 4px 6px; border-bottom: 1px solid #e2e8f0;
            font-size: 8px; color: #334155;
        }
        .tr { text-align: right; }
        .tc { text-align: center; }
        .fw { font-weight: bold; }
        .it { font-style: italic; }

        /* Section rows */
        .row-section td {
            background: #1e3a8a; color: #fff; font-weight: bold;
            font-size: 8.5px; padding: 4px 8px;
        }
        /* Section total rows */
        .row-sect-total td {
            background: #dbeafe; color: #1e3a8a; font-weight: bold;
            font-size: 8px; padding: 4px 6px;
            border-top: 1.5px solid #1e40af;
        }
        /* Account sub-rows */
        .row-acc td {
            background: #f0f4ff; color: #1e3a8a;
            font-size: 7.5px; padding: 3px 6px 3px 20px;
            border-bottom: 1px solid #e0e7ff;
        }
        /* Grand total row */
        .row-grand td {
            background: #1e3a8a; color: #fff; font-weight: bold;
            font-size: 9px; padding: 6px 8px; border: none;
        }

        /* Amount colors */
        .col-d   { color: #16a34a; }
        .col-c   { color: #dc2626; }
        .col-db  { color: #16a34a; font-weight: bold; }
        .col-cb  { color: #dc2626; font-weight: bold; }
        .col-dw  { color: #86efac; font-weight: bold; }
        .col-cw  { color: #fca5a5; font-weight: bold; }
        .sec-badge {
            display: inline-block; background: #dbeafe; color: #1e40af;
            border-radius: 3px; padding: 1px 5px; font-size: 7.5px; font-weight: bold;
        }
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
                <div class="doc-title">Balance Analytique</div>
                <div class="doc-sub">
                    Axe : <strong>{{ $axe->libelle ?? 'Tous les axes' }}</strong>
                    @if(isset($axe)) ({{ $axe->code ?? '' }}) @endif
                </div>
            </td>
            <td style="width:30%; border-left:1px solid #1e40af; text-align:right;">
                <div class="period-info">
                    Exercice : <strong>{{ $exercice->intitule ?? 'N/A' }}</strong><br>
                    @if(isset($exercice) && $exercice->date_debut && $exercice->date_fin)
                        Période du {{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }}
                        au {{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}<br>
                    @endif
                    Tenue de compte : {{ $company->currency ?? 'FCFA' }}
                </div>
            </td>
        </tr>
        <tr style="border-top:1px solid #bfdbfe;">
            <td class="hdr-meta">Date de tirage : {{ date('d/m/Y à H:i:s') }}</td>
            <td class="hdr-meta" style="text-align:center;">{{ $results->count() }} section(s)</td>
            <td class="hdr-meta" style="text-align:right;">Page : <span class="page-counter"></span></td>
        </tr>
    </table>
</header>

<footer>
    <div class="footer-tbl">
        <div class="f-l">© ComptaFlow – Solution de gestion comptable</div>
        <div class="f-c">Balance Analytique — {{ $exercice->intitule ?? '' }}</div>
        <div class="f-r">Page <span class="page-counter"></span></div>
    </div>
</footer>

@php
    $grandDebit   = 0;
    $grandCredit  = 0;
@endphp

<table>
    {{-- ── Column group headers (Sage style) ── --}}
    <thead>
        <tr>
            <th class="th-plain" style="width:80px;">Section</th>
            <th class="th-plain">Intitulé de section</th>
            {{-- Mouvements --}}
            <th colspan="2" class="th-grp" style="width:200px; border-left:1px solid rgba(255,255,255,0.4);">Mouvements</th>
            {{-- Soldes --}}
            <th class="th-grp" style="width:100px; border-left:1px solid rgba(255,255,255,0.4);">Soldes</th>
            <th class="th-grp" style="width:110px; border-left:1px solid rgba(255,255,255,0.4);">Soldes exercice précédent</th>
        </tr>
        <tr>
            <th class="th-sub" style="width:80px;"></th>
            <th class="th-sub"></th>
            <th class="th-sub" style="width:100px; border-left:1px solid rgba(255,255,255,0.4);">Débit</th>
            <th class="th-sub" style="width:100px;">Crédit</th>
            <th class="th-sub" style="width:100px; border-left:1px solid rgba(255,255,255,0.4);">Soldes</th>
            <th class="th-sub" style="width:110px; border-left:1px solid rgba(255,255,255,0.4);">Soldes N-1</th>
        </tr>
    </thead>
    <tbody>
        @forelse($results as $section)
            @php
                $grandDebit  += $section->total_debit;
                $grandCredit += $section->total_credit;
                $solde = $section->total_debit - $section->total_credit;
            @endphp

            {{-- ── Section summary row ── --}}
            <tr class="row-section">
                <td><span class="sec-badge" style="background:rgba(219,234,254,0.25); color:#dbeafe;">{{ $section->section_code }}</span></td>
                <td>{{ $section->section_libelle }}</td>
                <td class="tr col-dw">{{ number_format($section->total_debit, 2, ',', ' ') }}</td>
                <td class="tr col-cw">{{ number_format($section->total_credit, 2, ',', ' ') }}</td>
                <td class="tr fw" style="color: {{ $solde >= 0 ? '#86efac' : '#fca5a5' }}">
                    {{ number_format(abs($solde), 2, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}
                </td>
                <td class="tr" style="color:#cbd5e1; font-style:italic;">—</td>
            </tr>

            {{-- ── Account detail lines ── --}}
            @if(isset($section->lignes) && count($section->lignes) > 0)
                @foreach($section->lignes as $ligne)
                    @php
                        $ligneSolde = $ligne->total_debit - $ligne->total_credit;
                    @endphp
                    <tr class="row-acc">
                        <td style="padding-left:20px;">{{ $ligne->numero_de_compte }}</td>
                        <td class="it">{{ $ligne->compte_libelle }}</td>
                        <td class="tr {{ $ligne->total_debit > 0 ? 'col-d' : '' }}">
                            {{ $ligne->total_debit > 0 ? number_format($ligne->total_debit, 2, ',', ' ') : '' }}
                        </td>
                        <td class="tr {{ $ligne->total_credit > 0 ? 'col-c' : '' }}">
                            {{ $ligne->total_credit > 0 ? number_format($ligne->total_credit, 2, ',', ' ') : '' }}
                        </td>
                        <td class="tr {{ $ligneSolde >= 0 ? 'col-d' : 'col-c' }}">
                            {{ number_format(abs($ligneSolde), 2, ',', ' ') }} {{ $ligneSolde >= 0 ? 'D' : 'C' }}
                        </td>
                        <td class="tr it" style="color:#94a3b8;">—</td>
                    </tr>
                @endforeach

                {{-- ── Section total row ── --}}
                <tr class="row-sect-total">
                    <td colspan="2" class="fw">Total {{ $section->section_code }} — {{ $section->section_libelle }}</td>
                    <td class="tr fw col-db">{{ number_format($section->total_debit, 2, ',', ' ') }}</td>
                    <td class="tr fw col-cb">{{ number_format($section->total_credit, 2, ',', ' ') }}</td>
                    <td class="tr fw {{ $solde >= 0 ? 'col-db' : 'col-cb' }}">
                        {{ number_format(abs($solde), 2, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}
                    </td>
                    <td class="tr" style="color:#94a3b8; font-style:italic;">—</td>
                </tr>
            @endif

        @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:20px; color:#94a3b8; font-style:italic;">
                    Aucune donnée disponible pour les critères sélectionnés.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
