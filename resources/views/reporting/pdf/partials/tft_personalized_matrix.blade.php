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
            @php
                $activityNames = [
                    'operationnelle' => 'I. Flux de trésorerie des activités opérationnelles',
                    'investissement' => 'II. Flux de trésorerie des activités d\'investissement',
                    'financement' => 'III. Flux de trésorerie des activités de financement'
                ];
            @endphp

            @foreach($data['activities'] as $key => $activity)
                <tr class="section-header">
                    <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col">{{ $activityNames[$key] }}</td>
                </tr>
                
                {{-- ENCAISSEMENTS --}}
                <tr class="subsection-header">
                    <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col" style="padding-left: 10px;">ENCAISSEMENTS (+)</td>
                </tr>

                @if(isset($detailed) && $detailed)
                    @foreach($activity['encaissements']['categories'] as $category)
                    <tr class="detail-row">
                        <td class="label-col" style="padding-left: 20px;">{{ data_get($category, 'label') }}</td>
                        @foreach($visibleMonths as $i => $m)
                            <td>{{ number_format(data_get($category, "data.$i", 0), 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format($sumVisible((array)data_get($category, 'data', [])), 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @endif

                <tr class="total-row">
                    @php
                        $suffix = $key == 'operationnelle' ? 'OPÉRATIONNELS' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                        $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                    @endphp
                    <td class="label-col" style="padding-left: 10px; color: green;">TOTAL DES ENCAISSEMENTS {{ $suffix }} ({{ $roman }})</td>
                    @foreach($visibleMonths as $i => $m)
                        <td class="text-success">{{ number_format($activity['encaissements']['total'][$i], 0, ',', ' ') }}</td>
                    @endforeach
                    <td class="text-success">{{ number_format($sumVisible($activity['encaissements']['total']), 0, ',', ' ') }}</td>
                </tr>

                {{-- DÉCAISSEMENTS --}}
                <tr class="subsection-header">
                    <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col" style="padding-left: 10px;">DÉCAISSEMENTS (-)</td>
                </tr>

                @if(isset($detailed) && $detailed)
                    @foreach($activity['decaissements']['categories'] as $category)
                    <tr class="detail-row">
                        <td class="label-col" style="padding-left: 20px;">{{ data_get($category, 'label') }}</td>
                        @foreach($visibleMonths as $i => $m)
                            <td>{{ number_format(data_get($category, "data.$i", 0), 0, ',', ' ') }}</td>
                        @endforeach
                        <td>{{ number_format($sumVisible((array)data_get($category, 'data', [])), 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                @endif

                <tr class="total-row">
                    @php
                        $suffix = $key == 'operationnelle' ? 'OPÉRATIONNELS' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                        $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                    @endphp
                    <td class="label-col" style="padding-left: 10px; color: red;">TOTAL DES DÉCAISSEMENTS {{ $suffix }} ({{ $roman }})</td>
                    @foreach($visibleMonths as $i => $m)
                        <td class="text-danger">{{ number_format($activity['decaissements']['total'][$i], 0, ',', ' ') }}</td>
                    @endforeach
                    <td class="text-danger">{{ number_format($sumVisible($activity['decaissements']['total']), 0, ',', ' ') }}</td>
                </tr>

                {{-- Flux Net --}}
                <tr class="activity-net-row">
                    @php
                        $suffixNet = $key == 'operationnelle' ? 'OPÉRATIONNELLE' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                        $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                    @endphp
                    <td class="label-col">FLUX NET DE L'ACTIVITÉ {{ $suffixNet }} ({{ $roman }})</td>
                    @foreach($visibleMonths as $i => $m)
                        <td>{{ number_format($activity['net'][$i], 0, ',', ' ') }}</td>
                    @endforeach
                    <td>{{ number_format($sumVisible($activity['net']), 0, ',', ' ') }}</td>
                </tr>
                <tr><td colspan="{{ count($visibleMonths) + 2 }}" style="border: none; padding: 5px;"></td></tr>
            @endforeach

            <!-- SYNTHÈSE -->
            <tr class="section-header" style="background-color: #0f172a; color: #fff;">
                <td colspan="{{ count($visibleMonths) + 2 }}" class="label-col" style="color: #fff;">VARIATION GLOBALE ET TRÉSORERIE</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">VARIATION NETTE GLOBALE</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['global_net'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>{{ number_format($sumVisible($data['global_net']), 0, ',', ' ') }}</td>
            </tr>

            <tr class="main-total">
                <td class="label-col">TRÉSORERIE FINALE (CUMULÉE)</td>
                @foreach($visibleMonths as $i => $m)
                    <td>{{ number_format($data['cumule'][$i], 0, ',', ' ') }}</td>
                @endforeach
                <td>-</td>
            </tr>
        </tbody>
    </table>
