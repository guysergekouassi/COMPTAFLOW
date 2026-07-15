<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<style>
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    .table-row { transition: background-color 0.15s; }
    .table-row:hover { background-color: #f8fafc; }
    .input-field-premium {
        transition: all 0.2s ease;
        border: 1.5px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        border-radius: 10px !important;
        padding: 0.6rem 1rem !important;
        font-size: 0.82rem !important;
        font-weight: 500 !important;
        width: 100%;
        color: #0f172a !important;
    }
    .input-field-premium:focus {
        border-color: #1e40af !important;
        background-color: #fff !important;
        outline: none !important;
    }
    .input-label-premium {
        font-size: 0.68rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        margin-bottom: 0.3rem !important;
        display: block !important;
    }
    .btn-primary-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.4rem !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        border: none !important;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-blue { background-color: #1e40af !important; color: white !important; }
    .btn-blue:hover { background-color: #1e3a8a !important; transform: translateY(-1px); }
    .btn-ghost { background-color: #f1f5f9 !important; color: #475569 !important; }
    .btn-ghost:hover { background-color: #e2e8f0 !important; }
    .grand-total-row { background-color: #1e293b; }
    .badge-dl {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .badge-excel { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .badge-excel:hover { background: #16a34a; color: white; }
    .badge-pdf { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-pdf:hover { background: #dc2626; color: white; }
    .exercice-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    /* Progress bar for marge */
    .marge-bar { height: 4px; border-radius: 2px; margin-top: 4px; }
    .stat-kpi {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .stat-kpi .label { font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.07em; }
    .stat-kpi .value { font-size: 1.35rem; font-weight: 900; color: #0f172a; }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Résultat <span class="text-gradient">Analytique</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        {{-- Exercice badge --}}
                        <div class="flex items-center gap-3 mb-5">
                            <span class="exercice-badge">
                                <i class="fas fa-calendar-check"></i>
                                Exercice : {{ $exerciceActif?->intitule ?? 'Non défini' }}
                            </span>
                            <span class="text-xs text-slate-400 font-medium">
                                @if($exerciceActif)
                                    {{ \Carbon\Carbon::parse($exerciceActif->date_debut)->format('d/m/Y') }}
                                    → {{ \Carbon\Carbon::parse($exerciceActif->date_fin)->format('d/m/Y') }}
                                @endif
                            </span>
                        </div>

                        {{-- KPI Summary --}}
                        @if($results->count() > 0)
                        @php
                            $kpiCharges  = $results->sum('total_charges');
                            $kpiProduits = $results->sum('total_produits');
                            $kpiResultat = $kpiProduits - $kpiCharges;
                            $kpiMarge    = $kpiProduits > 0 ? ($kpiResultat / $kpiProduits) * 100 : 0;
                        @endphp
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="stat-kpi">
                                <span class="label"><i class="fas fa-arrow-down text-red-400 mr-1"></i>Total Charges</span>
                                <span class="value text-red-600">{{ number_format($kpiCharges, 0, ',', ' ') }}</span>
                            </div>
                            <div class="stat-kpi">
                                <span class="label"><i class="fas fa-arrow-up text-green-400 mr-1"></i>Total Produits</span>
                                <span class="value text-green-600">{{ number_format($kpiProduits, 0, ',', ' ') }}</span>
                            </div>
                            <div class="stat-kpi">
                                <span class="label"><i class="fas fa-chart-line text-blue-400 mr-1"></i>Résultat Net</span>
                                <span class="value {{ $kpiResultat >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($kpiResultat, 0, ',', ' ') }}
                                </span>
                            </div>
                            <div class="stat-kpi">
                                <span class="label"><i class="fas fa-percent text-purple-400 mr-1"></i>Marge Globale</span>
                                <span class="value {{ $kpiMarge >= 0 ? 'text-purple-600' : 'text-red-600' }}">
                                    {{ number_format($kpiMarge, 1) }}%
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- Filter Card --}}
                        <div class="glass-card p-6 mb-6">
                            <form action="{{ route('analytique.resultat') }}" method="GET">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                    <div>
                                        <label class="input-label-premium">Axe Analytique</label>
                                        <select name="axe_id" class="input-field-premium">
                                            @foreach($axes as $axe)
                                                <option value="{{ $axe->id }}" {{ $selectedAxeId == $axe->id ? 'selected' : '' }}>
                                                    {{ $axe->libelle }} ({{ $axe->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="input-label-premium">Date Début</label>
                                        <input type="date" name="date_debut" class="input-field-premium" value="{{ $data['date_debut'] ?? '' }}">
                                    </div>
                                    <div>
                                        <label class="input-label-premium">Date Fin</label>
                                        <input type="date" name="date_fin" class="input-field-premium" value="{{ $data['date_fin'] ?? '' }}">
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('analytique.resultat') }}" class="btn-primary-action btn-ghost flex-1 justify-center">
                                            <i class="fas fa-undo"></i> Effacer
                                        </a>
                                        <button type="submit" class="btn-primary-action btn-blue flex-1 justify-center">
                                            <i class="fas fa-chart-line"></i> Calculer
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Table Card --}}
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h5 class="text-slate-800 font-black mb-0 text-base">Rentabilité par Section</h5>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                        Axe : {{ $axes->where('id', $selectedAxeId)->first()?->libelle ?? '—' }}
                                        @if(!empty($data['date_debut']))
                                            &nbsp;|&nbsp; Du {{ \Carbon\Carbon::parse($data['date_debut'])->format('d/m/Y') }}
                                        @endif
                                        @if(!empty($data['date_fin']))
                                            au {{ \Carbon\Carbon::parse($data['date_fin'])->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('analytique.resultat.excel', request()->all()) }}" class="badge-dl badge-excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <a href="{{ route('analytique.resultat.pdf', request()->all()) }}" class="badge-dl badge-pdf">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" style="font-size: 0.82rem;">
                                    <thead>
                                        <tr class="bg-slate-50 border-b-2 border-slate-200">
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Section Analytique</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-40">Charges (Cl. 6)</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-40">Produits (Cl. 7)</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-40">Résultat Net</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-36">Marge (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grandTotalCharges = 0;
                                            $grandTotalProduits = 0;
                                        @endphp
                                        @forelse ($results as $item)
                                            @php
                                                $grandTotalCharges += $item->total_charges;
                                                $grandTotalProduits += $item->total_produits;
                                                $resultat = $item->total_produits - $item->total_charges;
                                                $marge = $item->total_produits > 0 ? ($resultat / $item->total_produits) * 100 : 0;
                                                $margeWidth = min(abs($marge), 100);
                                            @endphp
                                            <tr class="table-row border-b border-slate-50">
                                                <td class="px-5 py-3">
                                                    <div class="flex flex-col">
                                                        <span class="font-black text-slate-800">{{ $item->libelle }}</span>
                                                        <span class="text-[10px] font-mono text-blue-600 font-bold mt-0.5">{{ $item->code }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3 text-right font-bold text-slate-700">
                                                    {{ number_format($item->total_charges, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-5 py-3 text-right font-bold text-slate-700">
                                                    {{ number_format($item->total_produits, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-5 py-3 text-right font-black {{ $resultat >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($resultat, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-5 py-3 text-center">
                                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-black {{ $marge >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                                        {{ number_format($marge, 1) }}%
                                                    </span>
                                                    <div class="marge-bar bg-slate-200 mx-4 mt-1">
                                                        <div class="h-full rounded-2px {{ $marge >= 0 ? 'bg-green-400' : 'bg-red-400' }}"
                                                             style="width: {{ $margeWidth }}%"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-8 py-14 text-center">
                                                    <i class="fas fa-chart-bar text-4xl text-slate-200 block mb-3"></i>
                                                    <p class="text-slate-400 font-bold text-sm">Aucun résultat analytique trouvé.</p>
                                                    <p class="text-slate-300 text-xs mt-1">Vérifiez que des écritures de charges (Cl.6) ou produits (Cl.7) ont été ventilées sur cet axe.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($results->count() > 0)
                                    <tfoot>
                                        <tr class="grand-total-row">
                                            <td class="px-5 py-4 text-xs font-black text-white uppercase tracking-widest">
                                                <i class="fas fa-sigma mr-2 text-blue-300"></i>Totaux Globaux
                                            </td>
                                            <td class="px-5 py-4 text-right font-black text-white">{{ number_format($grandTotalCharges, 2, ',', ' ') }}</td>
                                            <td class="px-5 py-4 text-right font-black text-white">{{ number_format($grandTotalProduits, 2, ',', ' ') }}</td>
                                            @php
                                                $grandResultat = $grandTotalProduits - $grandTotalCharges;
                                                $grandMarge = $grandTotalProduits > 0 ? ($grandResultat / $grandTotalProduits) * 100 : 0;
                                            @endphp
                                            <td class="px-5 py-4 text-right font-black {{ $grandResultat >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                                {{ number_format($grandResultat, 2, ',', ' ') }}
                                            </td>
                                            <td class="px-5 py-4 text-center font-black {{ $grandMarge >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                                {{ number_format($grandMarge, 1) }}%
                                            </td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')
</body>
</html>
