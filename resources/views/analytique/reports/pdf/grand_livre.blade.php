<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grand Livre Analytique</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .company-name { font-size: 15px; font-weight: bold; color: #1e40af; }
        .report-title { font-size: 13px; margin-top: 5px; text-transform: uppercase; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1e40af; color: white; padding: 6px; text-align: left; text-transform: uppercase; font-size: 8px; }
        td { padding: 6px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 25px; text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->company_name ?? 'MA COMPAGNIE' }}</div>
        <div class="report-title">Grand Livre Analytique</div>
        <div>Exercice : {{ $exercice->intitule ?? 'N/A' }}</div>
    </div>

    <div class="info">
        <strong>Section :</strong> {{ $section->code ?? '' }} - {{ $section->libelle ?? 'Non définie' }}<br>
        <strong>Extraction au :</strong> {{ date('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Date</th>
                <th style="width: 100px;">N° Saisie</th>
                <th style="width: 120px;">Compte</th>
                <th>Libellé Opération</th>
                <th class="text-center" style="width: 40px;">Vent. %</th>
                <th class="text-right" style="width: 80px;">Montant</th>
                <th class="text-center" style="width: 30px;">Sens</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalDebit = 0;
                $totalCredit = 0;
            @endphp
            @foreach ($results as $item)
                @php
                    if ($item->sens == 'D') $totalDebit += $item->montant;
                    else $totalCredit += $item->montant;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                    <td>{{ $item->n_saisie }}</td>
                    <td>{{ $item->numero_de_compte }} - {{ $item->compte_libelle }}</td>
                    <td>{{ $item->description_operation }}</td>
                    <td class="text-center">{{ number_format($item->pourcentage, 0) }}%</td>
                    <td class="text-right">{{ number_format($item->montant, 2, ',', ' ') }}</td>
                    <td class="text-center font-bold">{{ $item->sens }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAUX PÉRIODE</td>
                <td colspan="2" class="text-right">
                    Débit : {{ number_format($totalDebit, 2, ',', ' ') }}<br>
                    Crédit : {{ number_format($totalCredit, 2, ',', ' ') }}<br>
                    @php $solde = $totalDebit - $totalCredit; @endphp
                    Solde : {{ number_format(abs($solde), 2, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Workflow Flow Compta - Solution de gestion
    </div>
</body>
</html>
