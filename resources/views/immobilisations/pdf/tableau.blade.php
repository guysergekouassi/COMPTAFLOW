<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tableau d'amortissement - {{ $immobilisation->code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1a73e8;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .info-section {
            margin-bottom: 20px;
            width: 100%;
        }
        .info-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
            color: #666;
            width: 150px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            font-size: 10pt;
        }
        td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 10pt;
            text-align: right;
        }
        td.text-center { text-align: center; }
        td.text-left { text-align: left; }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
            text-align: center;
            color: #777;
        }
        .badge {
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 8pt;
            color: white;
        }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: #333; }
        .valeur-nette {
            font-weight: bold;
            color: #1a73e8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $immobilisation->company->company_name ?? 'MA COMPAGNIE' }}</div>
        <div class="report-title">TABLEAU D'AMORTISSEMENT</div>
        <div>{{ $immobilisation->libelle }} ({{ $immobilisation->code }})</div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-item"><span class="label">Catégorie:</span> {{ ucfirst($immobilisation->categorie) }}</div>
            <div class="info-item"><span class="label">Date d'acquisition:</span> {{ $immobilisation->date_acquisition->format('d/m/Y') }}</div>
            <div class="info-item"><span class="label">Mise en service:</span> {{ $immobilisation->date_mise_en_service->format('d/m/Y') }}</div>
            <div class="info-item"><span class="label">Valeur d'acquisition:</span> {{ number_format($immobilisation->valeur_acquisition, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="info-box">
            <div class="info-item"><span class="label">Durée d'amortiss.:</span> {{ $immobilisation->duree_amortissement }} ans</div>
            <div class="info-item"><span class="label">Méthode:</span> {{ ucfirst($immobilisation->methode_amortissement) }}</div>
            <div class="info-item"><span class="label">Taux:</span> {{ $immobilisation->getTauxAmortissement() }}%</div>
            <div class="info-item"><span class="label">Valeur Résiduelle:</span> {{ number_format($immobilisation->valeur_residuelle, 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Année</th>
                <th>Base Amortissable</th>
                <th>Dotation</th>
                <th>Cumul Amort.</th>
                <th>VNC Fin Exercice</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($immobilisation->amortissements as $ligne)
            <tr>
                <td class="text-center"><strong>{{ $ligne->annee }}</strong></td>
                <td>{{ number_format($ligne->base_amortissable, 0, ',', ' ') }}</td>
                <td style="font-weight: bold;">{{ number_format($ligne->dotation_annuelle, 0, ',', ' ') }}</td>
                <td>{{ number_format($ligne->cumul_amortissement, 0, ',', ' ') }}</td>
                <td class="valeur-nette">{{ number_format($ligne->valeur_nette_comptable, 0, ',', ' ') }}</td>
                <td class="text-center">
                    @if($ligne->statut == 'comptabilise')
                        Comptabilisé
                    @else
                        Prévisionnel
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="2" class="text-center">TOTAUX / VNC FINALE</td>
                <td>{{ number_format($immobilisation->amortissements->sum('dotation_annuelle'), 0, ',', ' ') }}</td>
                <td>{{ number_format($immobilisation->amortissements->max('cumul_amortissement'), 0, ',', ' ') }}</td>
                <td class="valeur-nette">{{ number_format($immobilisation->amortissements->min('valeur_nette_comptable'), 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Généré le {{ date('d/m/Y H:i') }} | COMPTAFLOW - Gestion des Immobilisations
    </div>
</body>
</html>
