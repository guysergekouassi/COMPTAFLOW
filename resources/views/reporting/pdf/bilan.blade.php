<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan Actif/Passif - {{ $exercice->intitule }}</title>
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
            font-size: 11px;
            color: #000;
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
        }
        table th, table td {
            padding: 4px;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
@php
    $periode = $periode ?? app(\App\Services\AccountingReportingService::class)->resolvePeriode($exercice, $month ?? null);

    // Comparatif N-1 : $dataN1 est fourni par le contrôleur uniquement si l'option est cochée.
    $dataN1     = $dataN1     ?? null;
    $exerciceN1 = $exerciceN1 ?? null;
    $cmp        = !is_null($dataN1);
    $libN       = $exercice->intitule ?? 'N';
    $libN1      = $exerciceN1->intitule ?? 'N-1';
    $nf         = fn($v) => number_format((float) $v, 0, ',', ' ');
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
                    <div class="doc-title">BILAN ACTIF / PASSIF</div>
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


    <div class="content">
        <div class="row">
            <div class="col">
                <h3>ACTIF</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th class="amount">{{ $cmp ? $libN : 'Montant' }}</th>
                            @if($cmp)<th class="amount">{{ $libN1 }}</th><th class="amount">Var.</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['immobilise' => 'Actif Immobilisé', 'circulant' => 'Actif Circulant', 'tresorerie' => 'Trésorerie Actif'] as $key => $title)
                            <!-- Section Header -->
                            <tr>
                                <td style="font-weight:bold; background-color: #eee;">{{ $title }}</td>
                                <td class="amount" style="font-weight:bold; background-color: #eee;">{{ $nf($data['actif'][$key]['total_net']) }} FCFA</td>
                                @if($cmp)
                                    @php $n1 = $dataN1['actif'][$key]['total_net'] ?? 0; @endphp
                                    <td class="amount" style="font-weight:bold; background-color: #eee;">{{ $nf($n1) }}</td>
                                    <td class="amount" style="font-weight:bold; background-color: #eee;">{{ $nf($data['actif'][$key]['total_net'] - $n1) }}</td>
                                @endif
                            </tr>
                            
                            <!-- Subcategories -->
                            @foreach($data['actif'][$key]['subcategories'] as $subKey => $subData)
                                @if($subData['net'] != 0 || !empty($subData['details']))
                                <tr>
                                    <td style="padding-left: 10px; font-weight:600;">{{ $subData['label'] }}</td>
                                    <td class="amount" style="font-weight:600;">{{ $nf($subData['net']) }}</td>
                                    @if($cmp)
                                        @php $n1 = $dataN1['actif'][$key]['subcategories'][$subKey]['net'] ?? 0; @endphp
                                        <td class="amount" style="font-weight:600;">{{ $nf($n1) }}</td>
                                        <td class="amount" style="font-weight:600;">{{ $nf($subData['net'] - $n1) }}</td>
                                    @endif
                                </tr>
                                
                                    @if(isset($detailed) && $detailed && !empty($subData['details']))
                                        @foreach($subData['details'] as $detail)
                                        <tr>
                                            <td style="padding-left: 25px; font-style: italic; color: #333; font-size: 10px;">
                                                {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                            </td>
                                            <td class="amount" style="font-size: 10px; color: #333;" @if($cmp) colspan="3" @endif>
                                                {{ $nf($detail['solde']) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <th>TOTAL ACTIF</th>
                            <th class="amount">{{ $nf($data['actif']['total_net']) }} FCFA</th>
                            @if($cmp)
                                <th class="amount">{{ $nf($dataN1['actif']['total_net'] ?? 0) }}</th>
                                <th class="amount">{{ $nf($data['actif']['total_net'] - ($dataN1['actif']['total_net'] ?? 0)) }}</th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="col">
                <h3>PASSIF & CAPITAUX</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th class="amount">{{ $cmp ? $libN : 'Montant' }}</th>
                            @if($cmp)<th class="amount">{{ $libN1 }}</th><th class="amount">Var.</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['capitaux' => 'Capitaux Propres', 'dettes_fin' => 'Dettes Financières', 'passif_circ' => 'Passif Circulant', 'tresorerie' => 'Trésorerie Passif'] as $key => $title)
                            <!-- Section Header -->
                            <tr>
                                <td style="font-weight:bold; background-color: #eee;">{{ $title }}</td>
                                <td class="amount" style="font-weight:bold; background-color: #eee;">{{ $nf($data['passif'][$key]['total']) }} FCFA</td>
                                @if($cmp)
                                    @php $n1 = $dataN1['passif'][$key]['total'] ?? 0; @endphp
                                    <td class="amount" style="font-weight:bold; background-color: #eee;">{{ $nf($n1) }}</td>
                                    <td class="amount" style="font-weight:bold; background-color: #eee;">{{ $nf($data['passif'][$key]['total'] - $n1) }}</td>
                                @endif
                            </tr>
                            
                            <!-- Subcategories -->
                            @foreach($data['passif'][$key]['subcategories'] as $subKey => $subData)
                                @if($subData['total'] != 0 || !empty($subData['details']))
                                <tr>
                                    <td style="padding-left: 10px; font-weight:600;">{{ $subData['label'] }}</td>
                                    <td class="amount" style="font-weight:600;">{{ $nf($subData['total']) }}</td>
                                    @if($cmp)
                                        @php $n1 = $dataN1['passif'][$key]['subcategories'][$subKey]['total'] ?? 0; @endphp
                                        <td class="amount" style="font-weight:600;">{{ $nf($n1) }}</td>
                                        <td class="amount" style="font-weight:600;">{{ $nf($subData['total'] - $n1) }}</td>
                                    @endif
                                </tr>
                                
                                    @if(isset($detailed) && $detailed && !empty($subData['details']))
                                        @foreach($subData['details'] as $detail)
                                        <tr>
                                            <td style="padding-left: 25px; font-style: italic; color: #333; font-size: 10px;">
                                                {{ $detail['numero'] }} - {{ $detail['intitule'] }}
                                            </td>
                                            <td class="amount" style="font-size: 10px; color: #333;" @if($cmp) colspan="3" @endif>
                                                {{ $nf($detail['solde']) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <th>TOTAL PASSIF</th>
                            <th class="amount">{{ $nf($data['passif']['total']) }} FCFA</th>
                            @if($cmp)
                                <th class="amount">{{ $nf($dataN1['passif']['total'] ?? 0) }}</th>
                                <th class="amount">{{ $nf($data['passif']['total'] - ($dataN1['passif']['total'] ?? 0)) }}</th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if(!$data['equilibre'])
        <div style="border: 2px solid #000; padding: 10px; margin-top: 15px;">
            <strong>Attention :</strong> Le bilan n'est pas équilibré. Différence : {{ number_format($data['difference'], 0, ',', ' ') }} FCFA.
        </div>
        @else
        <div style="border: 1px solid #000; padding: 10px; margin-top: 15px;">
            <strong>Équilibre :</strong> Le bilan est parfaitement équilibré.
        </div>
        @endif
    </div>

    <div class="footer">
        <p>Document généré le {{ now()->format('d/m/Y à H:i') }} - ComptaFlow</p>
    </div>
</body>
</html>
