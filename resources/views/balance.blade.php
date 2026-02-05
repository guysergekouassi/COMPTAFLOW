<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Balance des comptes</title>
    <style>
        @page {
            margin: 15px 20px 40px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        /* En-tête */
        .header-container {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 5px;
        }

        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            font-size: 9px;
        }

        .header-center {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: top;
            font-size: 8px;
        }

        .main-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .sub-title {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .meta-info {
            font-size: 8px;
            margin-top: 3px;
            border-top: 1px solid #ccc;
            padding-top: 3px;
        }

        /* Tableau principal */
        table.balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8px;
        }

        table.balance-table th {
            border: 1px solid #000;
            padding: 4px 3px;
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }

        table.balance-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 8px;
        }

        .col-compte {
            width: 10%;
            text-align: left;
        }

        .col-intitule {
            width: 35%;
            text-align: left;
        }

        .col-montant {
            width: 13.75%;
            text-align: right;
        }

        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .total-label {
            text-align: center;
            font-weight: bold;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 10px;
            left: 20px;
            right: 20px;
            font-size: 7px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 3px;
        }
    </style>
</head>

<body>

    <!-- En-tête -->
    <div class="header-container">
        <div class="header-row">
            <div class="header-left">
                <strong>{{ $company_name }}</strong><br>
                <span style="font-size: 8px;">Impression définitive</span>
            </div>
            <div class="header-center">
                <div class="main-title">Balance des comptes</div>
                <div class="sub-title">Complète</div>
            </div>
            <div class="header-right">
                Période du {{ \Carbon\Carbon::parse($date_debut)->format('d/m/y') }}<br>
                au {{ \Carbon\Carbon::parse($date_fin)->format('d/m/y') }}<br>
                Tenue de compte : FCFA
            </div>
        </div>
        <div class="meta-info">
            <div class="header-row">
                <div class="header-left">
                    © ComptaFlow - Logiciel de comptabilité
                </div>
                <div class="header-center">
                    Date de tirage : {{ \Carbon\Carbon::now()->format('d/m/y') }} à {{ \Carbon\Carbon::now()->format('H:i:s') }}
                </div>
                <div class="header-right">
                    Page : 1
                </div>
            </div>
        </div>
    </div>

    @php
        // Trier les écritures par numéro de compte
        $ecritures = $ecritures->sortBy(function ($item) {
            return $item->planComptable->numero_de_compte ?? 0;
        });

        // Grouper par plan_comptable_id
        $grouped = $ecritures->groupBy('plan_comptable_id');
        
        // Totaux généraux
        $totalMouvementDebit = 0;
        $totalMouvementCredit = 0;
        $totalSoldeDebit = 0;
        $totalSoldeCredit = 0;
        
        // Totaux comptes de bilan (classes 1-5)
        $totalBilanMouvDebit = 0;
        $totalBilanMouvCredit = 0;
        $totalBilanSoldeDebit = 0;
        $totalBilanSoldeCredit = 0;
        
        // Totaux comptes de gestion (classes 6-7)
        $totalGestionMouvDebit = 0;
        $totalGestionMouvCredit = 0;
        $totalGestionSoldeDebit = 0;
        $totalGestionSoldeCredit = 0;
        
        // Préparer les données par compte
        $comptesData = [];
        foreach ($grouped as $compteId => $operations) {
            $compte = $operations->first()->planComptable;
            if (!$compte) continue;
            
            $numeroCompte = $compte->numero_de_compte;
            $intitule = $compte->intitule;
            
            $totalDebit = $operations->sum('debit');
            $totalCredit = $operations->sum('credit');
            
            $solde = $totalDebit - $totalCredit;
            $soldeDebit = $solde > 0 ? $solde : 0;
            $soldeCredit = $solde < 0 ? abs($solde) : 0;
            
            // Déterminer si c'est un compte de bilan ou de gestion
            $premiereClasse = substr($numeroCompte, 0, 1);
            $estBilan = in_array($premiereClasse, ['1', '2', '3', '4', '5']);
            
            $comptesData[] = [
                'numero' => $numeroCompte,
                'numero_original' => $compte->numero_original,
                'intitule' => $intitule,
                'mouv_debit' => $totalDebit,
                'mouv_credit' => $totalCredit,
                'solde_debit' => $soldeDebit,
                'solde_credit' => $soldeCredit,
                'est_bilan' => $estBilan,
            ];
            
            // Accumuler dans les totaux
            $totalMouvementDebit += $totalDebit;
            $totalMouvementCredit += $totalCredit;
            $totalSoldeDebit += $soldeDebit;
            $totalSoldeCredit += $soldeCredit;
            
            if ($estBilan) {
                $totalBilanMouvDebit += $totalDebit;
                $totalBilanMouvCredit += $totalCredit;
                $totalBilanSoldeDebit += $soldeDebit;
                $totalBilanSoldeCredit += $soldeCredit;
            } else {
                $totalGestionMouvDebit += $totalDebit;
                $totalGestionMouvCredit += $totalCredit;
                $totalGestionSoldeDebit += $soldeDebit;
                $totalGestionSoldeCredit += $soldeCredit;
            }
        }
    @endphp

    <!-- Tableau principal -->
    <table class="balance-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-compte">Numéro<br>de<br>compte</th>
                <th rowspan="2" class="col-intitule">Intitulé des comptes</th>
                <th colspan="2">Mouvements</th>
                <th colspan="2">Soldes</th>
            </tr>
            <tr>
                <th class="col-montant">Débit</th>
                <th class="col-montant">Crédit</th>
                <th class="col-montant">Débit</th>
                <th class="col-montant">Crédit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($comptesData as $compte)
                <tr>
                    <td class="col-compte">
                        @php
                            $displayMode = $display_mode ?? 'comptaflow';
                        @endphp
                        
                        @if($displayMode === 'origine')
                            {{-- Afficher uniquement le numéro original --}}
                            {{ $compte['numero_original'] ?? $compte['numero'] }}
                        @elseif($displayMode === 'comptaflow')
                            {{-- Afficher uniquement le numéro ComptaFlow --}}
                            {{ $compte['numero'] }}
                        @else
                            {{-- Afficher les deux (ComptaFlow en haut, origine en bas) --}}
                            {{ $compte['numero'] }}
                            @if(!empty($compte['numero_original']) && $compte['numero_original'] !== $compte['numero'])
                                <br><span style="font-size: 7px; color: #666;">({{ $compte['numero_original'] }})</span>
                            @endif
                        @endif
                    </td>
                    <td class="col-intitule">{{ $compte['intitule'] }}</td>
                    <td class="col-montant">{{ $compte['mouv_debit'] > 0 ? number_format($compte['mouv_debit'], 0, ',', ' ') : '' }}</td>
                    <td class="col-montant">{{ $compte['mouv_credit'] > 0 ? number_format($compte['mouv_credit'], 0, ',', ' ') : '' }}</td>
                    <td class="col-montant">{{ $compte['solde_debit'] > 0 ? number_format($compte['solde_debit'], 0, ',', ' ') : '' }}</td>
                    <td class="col-montant">{{ $compte['solde_credit'] > 0 ? number_format($compte['solde_credit'], 0, ',', ' ') : '' }}</td>
                </tr>
            @endforeach

            <!-- Totaux comptes de bilan -->
            <tr class="total-row">
                <td colspan="2" class="total-label">Totaux comptes de bilan</td>
                <td class="col-montant">{{ number_format($totalBilanMouvDebit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalBilanMouvCredit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalBilanSoldeDebit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalBilanSoldeCredit, 0, ',', ' ') }}</td>
            </tr>

            <!-- Totaux comptes de gestion -->
            <tr class="total-row">
                <td colspan="2" class="total-label">Totaux comptes de gestion</td>
                <td class="col-montant">{{ number_format($totalGestionMouvDebit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalGestionMouvCredit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalGestionSoldeDebit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalGestionSoldeCredit, 0, ',', ' ') }}</td>
            </tr>

            <!-- Totaux de la balance -->
            <tr class="total-row">
                <td colspan="2" class="total-label">Totaux de la balance</td>
                <td class="col-montant">{{ number_format($totalMouvementDebit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalMouvementCredit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalSoldeDebit, 0, ',', ' ') }}</td>
                <td class="col-montant">{{ number_format($totalSoldeCredit, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Impression générée par {{ $user->name ?? 'Utilisateur inconnu' }} le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
    </div>

    <script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $size = 7;

        $w = $pdf->get_width();
        $h = $pdf->get_height();

        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $textWidth = $fontMetrics->get_text_width($text, $font, $size);

        $x = $w - $textWidth - 20;
        $y = $h - 25;

        $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);
    }
    </script>

</body>

</html>
