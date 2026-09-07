<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>TFT Mensuel - {{ $exercice->intitule }}</title>
    <style>
        @page {
            margin: 120px 25px 40px 25px;
        }
        header {
            position: fixed;
            top: -110px;
            left: 0px;
            right: 0px;
            height: 100px;
            border: 1px solid #000;
            padding: 5px 10px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
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
            top: 30%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.08;
            transform: rotate(-25deg);
            font-size: 120px;
            font-weight: bold;
            color: #000;
            z-index: -1000;
            text-transform: uppercase;
            pointer-events: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 4px 6px;
            border: 1px solid #ccc;
            text-align: right;
            white-space: nowrap;
        }
        th.label-col, td.label-col {
            text-align: left;
            min-width: 200px;
            white-space: normal;
        }
        .header-row th {
            background-color: #eee;
            color: #000;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000;
        }
        .section-header {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #000;
        }
        .subsection-header {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 8px;
            color: #475569;
        }
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 1px solid #000;
        }
        .activity-net-row {
            background-color: #e2e8f0;
            font-weight: bold;
            border: 1px solid #000;
        }
        .main-total {
            background-color: #0f172a;
            color: #fff;
            font-weight: bold;
        }
        .detail-row td {
            font-style: italic;
            color: #555;
            font-size: 8px;
            border-top: 1px dashed #eee;
        }
        .text-center { text-align: center; }
        .text-success { color: green; }
        .text-danger { color: red; }
    </style>
</head>
<body>
@php
    // ── Comparatif N-1 & restriction de période (filtres de téléchargement) ──
    $dataN1     = $dataN1     ?? null;
    $exerciceN1 = $exerciceN1 ?? null;
    $cmp        = !is_null($dataN1);
    $detailed   = $detailed   ?? false;

    $monthFrom = $monthFrom ?? 1;
    $monthTo   = $monthTo   ?? count($data['months']);

    $sliceMonths = function ($months) use ($monthFrom, $monthTo) {
        $out = [];
        foreach (array_values($months ?? []) as $i => $m) {
            if ($i + 1 >= $monthFrom && $i + 1 <= $monthTo) {
                $out[$i] = $m;
            }
        }
        return $out ?: ($months ?? []);
    };

    $visibleMonths = $sliceMonths($data['months']);
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
                    <div class="doc-title">TABLEAU DES FLUX DE TRÉSORERIE MENSUEL</div>
                    <div class="doc-subtitle">Analyse des flux réels par activité@if($cmp) &mdash; Comparatif {{ $exerciceN1->intitule ?? 'N-1' }}@endif</div>
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

    @include('reporting.pdf.partials.tft_personalized_matrix', ['data' => $data, 'visibleMonths' => $visibleMonths, 'detailed' => $detailed])

    @if($cmp)
        <div style="page-break-before: always;"></div>
        <div style="font-weight: bold; font-size: 13px; text-transform: uppercase; margin: 8px 0 6px 0; border-bottom: 2px solid #000; padding-bottom: 3px;">
            Comparatif exercice précédent &mdash; {{ $exerciceN1->intitule ?? 'N-1' }}
        </div>
        @include('reporting.pdf.partials.tft_personalized_matrix', ['data' => $dataN1, 'visibleMonths' => $sliceMonths($dataN1['months'] ?? []), 'detailed' => $detailed])
    @endif
</body>
</html>
