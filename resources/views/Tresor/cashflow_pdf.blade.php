<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Plan de Trésorerie - {{ $startDate }} à {{ $endDate }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .period { text-align: center; margin-bottom: 30px; font-size: 1.1em; }
        .table-treso { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        .table-treso th, .table-treso td { border: 1px solid #ccc; padding: 8px; text-align: right; }
        .table-treso th { background-color: #f0f0f0; text-align: left; }
        .total-row td { font-weight: bold; background-color: #e0f0ff; }
        .compte-column { text-align: left !important; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Plan de Trésorerie</h1>
    </div>

    <div class="period">
        Période: Du **{{ $startDate }}** au **{{ $endDate }}**
    </div>

    <table class="table-treso">
        <thead>
            <tr>
                <th class="compte-column">Compte Trésorerie</th>
                <th>Solde Initial</th>
                <th>Encaissements (Débit)</th>
                <th>Décaissements (Crédit)</th>
                <th>Solde Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashFlowData as $data)
            <tr>
                <td class="compte-column">{{ $data['compte'] }}</td>
                <td>{{ $data['solde_initial'] }}</td>
                <td>{{ $data['encaissements'] }}</td>
                <td>{{ $data['decaissements'] }}</td>
                <td>{{ $data['solde_final'] }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td class="compte-column">TOTAL</td>
                <td>{{ $totals['total_solde_initial'] }}</td>
                <td>{{ $totals['total_encaissements'] }}</td>
                <td>{{ $totals['total_decaissements'] }}</td>
                <td>{{ $totals['total_solde_final'] }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
