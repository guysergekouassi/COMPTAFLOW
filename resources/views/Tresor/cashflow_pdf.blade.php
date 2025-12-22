<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Flux de Trésorerie Mensuel - {{ $startDate }} à {{ $endDate }}</title>
    <style>
        /* Styles pour DomPDF */
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 10px;
            font-size: 10px;
            color: #343a40; /* Texte sombre pour la lisibilité */
        }
        h1, h2, h3 {
            font-weight: bold;
            margin: 5px 0;
        }
        h1 { font-size: 1.5em; color: #0d6efd; } /* Couleur principale pour le titre */
        h2 { font-size: 1.2em; }
        h3 { font-size: 1em; color: #333; }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 2px solid #0d6efd; /* Ligne de séparation bleue */
        }
        .period {
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.1em;
            color: #495057; /* Couleur distincte pour la période */
            font-style: italic;
        }

        /* Tableau principal */
        .table-flux {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-flux th {
            /* En-tête du tableau (Mois et Total Global) */
            background-color: #6c757d; /* Gris foncé/bleu-gris */
            color: white;
            border: 1px solid #6c757d;
            padding: 8px 5px;
            text-align: right;
        }
        .table-flux td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: right;
            vertical-align: middle;
        }

        /* Colonne de description */
        .description-column {
            text-align: left;
            width: 30%;
        }

        /* Styles de lignes */
        .group-header {
            /* 1. Flux Opérationnels, 2. Investissement, 3. Financement */
            background-color: #e6f0ff; /* Bleu très clair */
            color: #0d6efd; /* Texte bleu principal */
            font-weight: bold;
            text-align: left !important;
            border-bottom: 2px solid #0d6efd;
        }
        .sub-group-header {
            /* Encaissements Opérationnels, Décaissements, etc. */
            background-color: #f8f9fa; /* Gris très clair */
            color: #343a40; /* Texte foncé */
            font-weight: bold;
            text-align: left !important;
        }
        .flow-line:nth-child(even) { /* Bandes de lignes alternées */
            background-color: #fcfcfc;
        }
        .total-line {
            /* Total des encaissements / décaissements */
            background-color: #fff3cd; /* Jaune très clair pour l'emphase */
            color: #664d03; /* Texte brun foncé */
            font-weight: bold;
        }
        .grand-total-line {
            /* Solde Net des Opérations, d'Investissement, etc. */
            background-color: #d1e7dd; /* Vert très clair pour les totaux nets */
            color: #0f5132; /* Texte vert foncé */
            font-weight: bolder;
            border-top: 2px solid #0f5132;
        }

        /* Style pour les détails de transaction */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            /* Assurer que le tableau de détails ne prend pas les bordures externes */
            border: none !important;
        }
        .detail-table tr, .detail-table td {
             border: none !important; /* Supprimer les bordures des cellules de détails */
        }
        .detail-row {
            font-size: 0.8em;
            background-color: #ffffff; /* Fond blanc pour les détails */
        }
        .detail-row td {
            padding: 2px 5px;
            vertical-align: top;
            white-space: normal;
        }
        .detail-description {
            padding-left: 20px !important; /* Indentation pour les détails */
            text-align: left !important;
            font-style: italic;
            width: auto;
            color: #5a6268; /* Texte gris pour les détails */
        }
        .detail-amount {
            width: 80px; /* Largeur fixe pour Débit/Crédit */
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PLAN DE TRÉSORERIE - FLUX MENSUELS</h1>
        <div class="period">Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    @php
        // Calcul du nombre total de colonnes (description + périodes + total global)
        $totalColumns = count($periods) + 2;
    @endphp

    <table class="table-flux">
        <thead>
            <tr class="group-header">
                <th class="description-column">Flux de Trésorerie</th>
                @foreach($periods as $period)
                    <th class="period-column">{{ $period }}</th>
                @endforeach
                <th class="total-column">Total Global</th>
            </tr>
        </thead>
        <tbody>
            {{-- 1. Flux de trésorerie des activités opérationnelles --}}
            <tr class="group-header">
                <td colspan="{{ $totalColumns }}">1. Flux de trésorerie des activités opérationnelles</td>
            </tr>

            {{-- Encaissements Opérationnels --}}
            <tr>
                <td class="sub-group-header" colspan="{{ $totalColumns }}">Encaissements Opérationnels</td>
            </tr>
            @foreach($incomes as $flow)
            <tr class="flow-line">
                <td class="description-column">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>
            
            @endforeach

            {{-- Ligne Total des Encaissements Opérationnels --}}
            <tr class="total-line">
                <td class="description-column">Total des encaissements opérationnels</td>
                @foreach($periods as $key => $period)
                    <td>{{ $totalEncaissementsByPeriod[$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $grandTotalEncaissements ?? '—' }}</td>
            </tr>

            {{-- Décaissements Opérationnels --}}
            <tr>
                <td class="sub-group-header" colspan="{{ $totalColumns }}">Décaissements</td>
            </tr>

            {{-- Dépenses de Production --}}
            <tr>
                <td class="sub-group-header" style="padding-left: 10px;" colspan="{{ $totalColumns }}">Dépenses de production</td>
            </tr>
            @foreach($productionExpenses as $flow)
            <tr class="flow-line">
                <td class="description-column" style="padding-left: 20px;">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>

            @endforeach

            {{-- Autres Achats / Dépenses --}}
            <tr>
                <td class="sub-group-header" style="padding-left: 10px;" colspan="{{ $totalColumns }}">Autres décaissements opérationnels</td>
            </tr>
            @foreach($otherExpenses as $flow)
            <tr class="flow-line">
                <td class="description-column" style="padding-left: 20px;">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>

            @endforeach

            {{-- Ligne Total des Décaissements Opérationnels --}}
            <tr class="total-line">
                <td class="description-column">Total des décaissements opérationnels</td>
                @foreach($periods as $key => $period)
                    <td>{{ $totalDecaissementsByPeriod[$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $grandTotalDecaissements ?? '—' }}</td>
            </tr>

            {{-- SOLDE NET DES OPÉRATIONS --}}
            <tr class="grand-total-line">
                <td class="description-column">Solde Net des Opérations (Encaissements - Décaissements)</td>
                @foreach($periods as $key => $period)
                    <td>{{ $netCashFlowByPeriod[$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $grandNetCashFlow ?? '—' }}</td>
            </tr>

            {{-- 2. Flux de trésorerie des activités d'investissement --}}
            <tr class="group-header">
                <td colspan="{{ $totalColumns }}">2. Flux de trésorerie des activités d'investissement</td>
            </tr>

            <tr>
                <td class="sub-group-header" colspan="{{ $totalColumns }}">Encaissements d'Investissement</td>
            </tr>
            @foreach($investmentFlow['incomes'] as $flow)
            <tr class="flow-line">
                <td class="description-column">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>

            @endforeach

            <tr>
                <td class="sub-group-header" colspan="{{ $totalColumns }}">Décaissements d'Investissement</td>
            </tr>
            @foreach($investmentFlow['expenses'] as $flow)
            <tr class="flow-line">
                <td class="description-column">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>

            @endforeach

            {{-- Lignes Totaux et Solde Net Investissement --}}
            <tr class="total-line">
                <td class="description-column">Total des encaissements d'Investissement</td>
                @foreach($periods as $key => $period)
                    <td>{{ $investmentTotals['totalIncomeByPeriod'][$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $investmentTotals['totalIncome'] ?? '—' }}</td>
            </tr>

            <tr class="total-line">
                <td class="description-column">Total des décaissements d'Investissement</td>
                @foreach($periods as $key => $period)
                    <td>{{ $investmentTotals['totalExpenseByPeriod'][$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $investmentTotals['totalExpense'] ?? '—' }}</td>
            </tr>

            <tr class="grand-total-line">
                <td class="description-column">Solde Net d'Investissement</td>
                @foreach($periods as $key => $period)
                    <td>{{ $investmentTotals['netByPeriod'][$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $investmentTotals['grandNet'] ?? '—' }}</td>
            </tr>


            {{-- 3. Flux de trésorerie des activités de financement --}}
            <tr class="group-header">
                <td colspan="{{ $totalColumns }}">3. Flux de trésorerie des activités de financement</td>
            </tr>

            <tr>
                <td class="sub-group-header" colspan="{{ $totalColumns }}">Encaissements de Financement</td>
            </tr>
            @foreach($financingFlow['incomes'] as $flow)
            <tr class="flow-line">
                <td class="description-column">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>

            @endforeach

            <tr>
                <td class="sub-group-header" colspan="{{ $totalColumns }}">Décaissements de Financement</td>
            </tr>
            @foreach($financingFlow['expenses'] as $flow)
            <tr class="flow-line">
                <td class="description-column">{{ $flow['name'] }}</td>
                @foreach($flow['data'] as $value)
                    <td>{{ $value }}</td>
                @endforeach
                <td>{{ $flow['total'] }}</td>
            </tr>
            {{-- BLOC AJOUTÉ : Détails des transactions (Débit/Crédit) --}}
            @if(!empty($flow['details']))

            @endif
            {{-- FIN BLOC AJOUTÉ --}}
            @endforeach

            {{-- Lignes Totaux et Solde Net Financement --}}
            <tr class="total-line">
                <td class="description-column">Total des encaissements de Financement</td>
                @foreach($periods as $key => $period)
                    <td>{{ $financingTotals['totalIncomeByPeriod'][$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $financingTotals['totalIncome'] ?? '—' }}</td>
            </tr>

            <tr class="total-line">
                <td class="description-column">Total des décaissements de Financement</td>
                @foreach($periods as $key => $period)
                    <td>{{ $financingTotals['totalExpenseByPeriod'][$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $financingTotals['totalExpense'] ?? '—' }}</td>
            </tr>

            <tr class="grand-total-line">
                <td class="description-column">Solde Net de Financement</td>
                @foreach($periods as $key => $period)
                    <td>{{ $financingTotals['netByPeriod'][$key] ?? '—' }}</td>
                @endforeach
                <td>{{ $financingTotals['grandNet'] ?? '—' }}</td>
            </tr>


        </tbody>
    </table>
    <table class="table-flux">
    <tbody>
        <tr class="grand-total-line" style="background-color: #0d6efd; color: white;">
            <td class="description-column">VARIATION NETTE DE TRÉSORERIE (1+2+3)</td>
            @foreach($periods as $key => $period)
                <td>{{ $formattedVariationGlobale[$key] }}</td>
            @endforeach
            <td>{{ number_format($grandVariationGlobale, 2, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>
</body>
</html>
