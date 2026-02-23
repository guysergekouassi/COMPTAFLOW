<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Analytique</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e40af; }
        .report-title { font-size: 14px; margin-top: 5px; text-transform: uppercase; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1e40af; color: white; padding: 8px; text-align: left; text-transform: uppercase; font-size: 9px; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; font-size: 11px; }
        .solde-d { color: green; }
        .solde-c { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->company_name ?? 'MA COMPAGNIE' }}</div>
        <div class="report-title">Balance Analytique</div>
        <div>Exercice : {{ $exercice->intitule ?? 'N/A' }}</div>
    </div>

    <div class="info">
        <strong>Axe Analytique :</strong> {{ $axe->libelle ?? 'Non défini' }}<br>
        <strong>Date d'extraction :</strong> {{ date('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Libellé Section</th>
                <th class="text-right">Total Débit</th>
                <th class="text-right">Total Crédit</th>
                <th class="text-right">Solde</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalDebit = 0;
                $grandTotalCredit = 0;
            @endphp
            @foreach ($results as $item)
                @php
                    $grandTotalDebit += $item->total_debit;
                    $grandTotalCredit += $item->total_credit;
                    $solde = $item->total_debit - $item->total_credit;
                @endphp
                <tr>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->libelle }}</td>
                    <td class="text-right">{{ number_format($item->total_debit, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($item->total_credit, 2, ',', ' ') }}</td>
                    <td class="text-right font-bold {{ $solde >= 0 ? 'solde-d' : 'solde-c' }}">
                        {{ number_format(abs($solde), 2, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="2">TOTAL GÉNÉRAL</td>
                <td class="text-right">{{ number_format($grandTotalDebit, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($grandTotalCredit, 2, ',', ' ') }}</td>
                @php $grandSolde = $grandTotalDebit - $grandTotalCredit; @endphp
                <td class="text-right {{ $grandSolde >= 0 ? 'solde-d' : 'solde-c' }}">
                    {{ number_format(abs($grandSolde), 2, ',', ' ') }} {{ $grandSolde >= 0 ? 'D' : 'C' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Imprimé par Flow Compta
    </div>
</body>
</html>
