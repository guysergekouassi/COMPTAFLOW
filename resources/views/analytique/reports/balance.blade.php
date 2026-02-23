<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<style>
    .bg-slate-50\/50 { background-color: rgb(248 250 252 / 0.5); }
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
    .btn-action { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }
    .table-row { transition: background-color 0.2s; }
    .table-row:hover { background-color: #f1f5f9; }
    
    .input-field-premium {
        transition: all 0.2s ease;
        border: 2px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        border-radius: 12px !important;
        padding: 0.65rem 1rem !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        width: 100%;
    }
    .input-field-premium:focus {
        border-color: #1e40af !important;
        background-color: #ffffff !important;
        outline: none !important;
    }
    .input-label-premium {
        font-size: 0.7rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        margin-bottom: 0.35rem !important;
        display: block !important;
    }
    .btn-filter {
        padding: 0.65rem 1.5rem !important;
        border-radius: 12px !important;
        background-color: #1e40af !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 0.75rem !important;
        border: none !important;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        background-color: #1e3a8a !important;
        transform: translateY(-1px);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Balance <span class="text-gradient">Analytique</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Filtres -->
                        <div class="glass-card p-6 mb-8">
                            <form action="{{ route('analytique.balance') }}" method="GET">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                    <div>
                                        <label class="input-label-premium">Exercice</label>
                                        <select name="exercice_id" class="input-field-premium">
                                            @foreach($exercices as $ex)
                                                <option value="{{ $ex->id }}" {{ ($exerciceActif && $exerciceActif->id == $ex->id) ? 'selected' : '' }}>
                                                    {{ $ex->intitule }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
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
                                    <div class="md:col-span-4 flex justify-end gap-3 mt-2">
                                        <a href="{{ route('analytique.balance') }}" class="btn btn-label-secondary rounded-xl px-6 py-2.5 font-bold text-xs uppercase">
                                            Effacer
                                        </a>
                                        <button type="submit" class="btn-filter uppercase">
                                            <i class="fas fa-search me-2"></i> Actualiser
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tableau des Résultats -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/30 flex justify-between items-center">
                                <div>
                                    <h5 class="text-slate-800 font-black mb-0">Résultats de la balance</h5>
                                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Axe : {{ $axes->where('id', $selectedAxeId)->first()?->libelle ?? 'Aucun' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('analytique.balance.excel', request()->all()) }}" class="btn btn-xs btn-outline-primary rounded-lg font-bold"><i class="fas fa-file-excel me-1"></i> Excel</a>
                                    <a href="{{ route('analytique.balance.pdf', request()->all()) }}" class="btn btn-xs btn-outline-danger rounded-lg font-bold"><i class="fas fa-file-pdf me-1"></i> PDF</a>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white border-b border-slate-100">
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 120px;">Code Section</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Libellé Section</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right" style="width: 180px;">Total Débit</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right" style="width: 180px;">Total Crédit</th>
                                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right" style="width: 180px;">Solde</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @php 
                                            $grandTotalDebit = 0;
                                            $grandTotalCredit = 0;
                                        @endphp
                                        @forelse ($results as $item)
                                            @php
                                                $grandTotalDebit += $item->total_debit;
                                                $grandTotalCredit += $item->total_credit;
                                                $solde = $item->total_debit - $item->total_credit;
                                            @endphp
                                            <tr class="table-row group">
                                                <td class="px-6 py-4">
                                                    <span class="font-mono font-black text-blue-700">{{ $item->code }}</span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="font-bold text-slate-800">{{ $item->libelle }}</span>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-slate-700">
                                                    {{ number_format($item->total_debit, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-slate-700">
                                                    {{ number_format($item->total_credit, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-6 py-4 text-right font-black {{ $solde >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format(abs($solde), 2, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-8 py-12 text-center text-slate-400 font-bold italic">
                                                    <i class="fas fa-folder-open text-3xl mb-3 block opacity-20"></i>
                                                    Aucune donnée trouvée pour cet axe sur la période sélectionnée.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($results->count() > 0)
                                    <tfoot class="bg-slate-50/50">
                                        <tr>
                                            <td colspan="2" class="px-6 py-5 text-sm font-black text-slate-800 uppercase tracking-widest">Totaux</td>
                                            <td class="px-6 py-5 text-right font-black text-slate-800">{{ number_format($grandTotalDebit, 2, ',', ' ') }}</td>
                                            <td class="px-6 py-5 text-right font-black text-slate-800">{{ number_format($grandTotalCredit, 2, ',', ' ') }}</td>
                                            @php $grandSolde = $grandTotalDebit - $grandTotalCredit; @endphp
                                            <td class="px-6 py-5 text-right font-black {{ $grandSolde >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                                {{ number_format(abs($grandSolde), 2, ',', ' ') }} {{ $grandSolde >= 0 ? 'D' : 'C' }}
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
