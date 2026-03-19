<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1.5px solid #333;
            padding-bottom: 8px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e50;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
            color: #1a4a7a;
            text-transform: uppercase;
        }
        .exercice-info {
            font-size: 11px;
            font-style: italic;
            margin-top: 3px;
            color: #7f8c8d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 15px;
        }
        th, td {
            border: 0.5pt solid #aaa;
            padding: 4px 6px;
            word-wrap: break-word;
            overflow: hidden;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .bg-section { background-color: #ecf0f1; font-weight: bold; }
        .bg-total { background-color: #f9f9f9; font-weight: bold; }
        .page-break { page-break-after: always; }
        
        .footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            height: 0.5cm;
            text-align: center;
            font-size: 8px;
            color: #95a5a6;
            border-top: 0.5pt solid #eee;
        }
        
        /* Cacher les éléments inutiles en export */
        .no-export, .btn, .alert, .badge, script { display: none !important; }
        
        /* Forcer l'affichage correct des bordures */
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="footer">
        Généré par COMPTAFLOW le {{ date('d/m/Y H:i') }} | 
        Page <script type="text/php">
            if (isset($pdf)) {
                $text = "{PAGE_NUM} / {PAGE_COUNT}";
                $size = 8;
                $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) / 1.1;
                $y = $pdf->get_height() - 25;
                $pdf->page_text($x, $y, $text, $font, $size, array(0,0,0));
            }
        </script>
    </div>

    @foreach($pages as $index => $page)
        <div class="@if(!$loop->last) page-break @endif">
            <div class="header">
                <table style="border: none; margin-bottom: 0;">
                    <tr>
                        <td style="border: none; text-align: left; width: 20%;">
                            @if(file_exists(public_path('logo_armoiries.png')))
                                @php
                                    $logoData = base64_encode(file_get_contents(public_path('logo_armoiries.png')));
                                    $logoSrc = 'data:image/png;base64,' . $logoData;
                                @endphp
                                <img src="{{ $logoSrc }}" style="height: 60px;">
                            @endif
                        </td>
                        <td style="border: none; text-align: center; width: 60%;">
                            <div class="company-name">{{ $company->company_name ?? 'ENTREPRISE' }}</div>
                            <div class="report-title">{{ $page['title'] }}</div>
                            <div class="exercice-info">Exercice : {{ $exercice->libelle ?? $exercice->intitule }} ({{ date('Y', strtotime($exercice->date_debut)) }})</div>
                        </td>
                        <td style="border: none; text-align: right; width: 20%;">
                            <div style="font-size: 8px; font-weight: bold;">RÉPUBLIQUE DE CÔTE D'IVOIRE</div>
                            <div style="font-size: 7px;">Direction Générale des Impôts</div>
                        </td>
                    </tr>
                </table>
            </div>

            {!! $page['html'] !!}
        </div>
    @endforeach
</body>
</html>
