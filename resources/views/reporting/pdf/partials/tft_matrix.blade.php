@php
    // Somme limitée aux mois réellement affichés (filtre de période).
    $sumVisible = fn ($arr) => array_sum(array_intersect_key((array) ($arr ?? []), $visibleMonths));
@endphp
    <table>
        <thead>
            <tr class="header-row">
                <th class="label-col">Flux de trésorerie</th>
                @foreach($visibleMonths as $month)
                    <th>{{ $month['name'] }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <!-- I. ACTIVITÉS OPÉRATIONNELLES (Méthode Indirecte) -->
            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col">I. Flux de trésorerie des activités opérationnelles (Méthode Indirecte)</td>
            </tr>
            
            <!-- A. CAF -->
            <tr class="sub-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col" style="padding-left: 20px;">A. Capacité d'Autofinancement (CAF)</td>
            </tr>
            <tr>
                <td class="label-col">Produits encaissables (+)</td>
                @foreach($visibleMonths as $i => $m)
                    <td style="color: green;">+ {{ number_format($data['flux']['operationnel']['caf']['produits_encaissables'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ number_format($sumVisible($data['flux']['operationnel']['caf']['produits_encaissables']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['caf']['details']['produits'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 40px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <tr>
                <td class="label-col">Charges décaissables (-)</td>
                @foreach($visibleMonths as $i => $m)
                    <td style="color: red;">- {{ number_format($data['flux']['operationnel']['caf']['charges_decaissables'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td style="font-weight: bold;">- {{ number_format($sumVisible($data['flux']['operationnel']['caf']['charges_decaissables']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['caf']['details']['charges'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 40px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>- {{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>- {{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <tr class="total-row">
                <td class="label-col">Marge Brute d'Autofinancement (CAF)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['caf']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['operationnel']['caf']['total']), 0, ',', ' ') }}</td>
            </tr>

            <!-- B. VAR BFR -->
            <tr class="sub-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col" style="padding-left: 20px;">B. Variation du BFR</td>
            </tr>

            <tr>
                <td class="label-col">Variation Stocks</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['bfr']['variation_stocks'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ number_format($sumVisible($data['flux']['operationnel']['bfr']['variation_stocks']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['bfr']['details']['stocks'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 40px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <tr>
                <td class="label-col">Variation Créances</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['bfr']['variation_creances'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ number_format($sumVisible($data['flux']['operationnel']['bfr']['variation_creances']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['bfr']['details']['creances'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 40px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <tr>
                <td class="label-col">Variation Dettes Circulantes</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['bfr']['variation_dettes'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td style="font-weight: bold;">{{ number_format($sumVisible($data['flux']['operationnel']['bfr']['variation_dettes']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['operationnel']['bfr']['details']['dettes'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 40px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif

            <tr class="total-row">
                <td class="label-col">Variation Totale du BFR</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['bfr']['total'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['operationnel']['bfr']['total']), 0, ',', ' ') }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">FLUX NET OPÉRATIONNEL (A + B)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['operationnel']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['operationnel']['net']), 0, ',', ' ') }}</td>
            </tr>

            <!-- II. INVESTISSEMENT -->
            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col">II. Flux des activités d'investissement</td>
            </tr>
            <tr>
                <td class="label-col">Cessions d'immobilisations (+)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['investissement']['cessions'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['investissement']['cessions']), 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="label-col">Acquisitions d'immobilisations (-)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>-{{ number_format($data['flux']['investissement']['acquisitions'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>-{{ number_format($sumVisible($data['flux']['investissement']['acquisitions']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['investissement']['details']['acquisitions'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>-{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>-{{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
             <tr class="main-total">
                <td class="label-col">FLUX NET INVESTISSEMENT (II)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['investissement']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['investissement']['net']), 0, ',', ' ') }}</td>
            </tr>


            <!-- III. FINANCEMENT -->
            <tr class="section-header">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col">III. Flux des activités de financement</td>
            </tr>
            <tr>
                <td class="label-col">Flux Net Financement</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['financement']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['financement']['net']), 0, ',', ' ') }}</td>
            </tr>
            @if(isset($detailed) && $detailed)
                @foreach($data['flux']['financement']['details']['net'] as $compte)
                <tr class="detail-row">
                    <td class="label-col" style="padding-left: 20px;">{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($sumVisible($compte['months'] ?? []), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endif
             <tr class="main-total">
                <td class="label-col">FLUX NET FINANCEMENT (III)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['financement']['net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['financement']['net']), 0, ',', ' ') }}</td>
            </tr>


            <!-- TOTAL FLUX -->
            <tr class="main-total">
                <td class="label-col">VARIATION DE TRÉSORERIE (I+II+III)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['tresorerie']['variation'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['flux']['tresorerie']['variation']), 0, ',', ' ') }}</td>
            </tr>

             <tr class="total-row">
                <td class="label-col">Solde Trésorerie Fin de Période (Cumulé)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['flux']['tresorerie']['solde_fin'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>-</td>
            </tr>

        </tbody>
    </table>
