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
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Résultat <span class="text-gradient">Analytique</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Filtres -->
                        <div class="glass-card p-6 mb-8">
                            <form action="{{ route('analytique.resultat') }}" method="GET">
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
                                        <select name="axe_id" class="input-field-premium" onchange="this.form.submit()">
                                            @foreach($axes as $axe)
                                                <option value="{{ $axe->id }}" {{ $selectedAxeId == $axe->id ? 'selected' : '' }}>
                                                    {{ $axe->libelle }}
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
                                        <a href="{{ route('analytique.resultat') }}" class="btn btn-label-secondary rounded-xl px-6 py-2.5 font-bold text-xs uppercase">
                                            Vider
                                        </a>
                                        <button type="submit" class="btn-filter uppercase font-bold">
                                            <i class="fas fa-chart-line me-2"></i> Calculer le Résultat
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tableau des Résultats -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/30 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <h5 class="text-slate-800 font-black mb-0">Rentabilité par Section</h5>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Axe : {{ $axes->where('id', $selectedAxeId)->first()?->libelle ?? 'Non sélectionné' }}</p>
                                    </div>
                                    <div class="flex gap-2 ms-auto">
                                        <a href="{{ route('analytique.resultat.excel', request()->all()) }}" class="btn btn-xs btn-outline-primary rounded-lg font-bold"><i class="fas fa-file-excel me-1"></i> Excel</a>
                                        <a href="{{ route('analytique.resultat.pdf', request()->all()) }}" class="btn btn-xs btn-outline-danger rounded-lg font-bold"><i class="fas fa-file-pdf me-1"></i> PDF</a>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white border-b border-slate-100">
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Section</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Charges (Cl. 6)</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Produits (Cl. 7)</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Résultat Net</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Marge (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
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
                                            @endphp
                                            <tr class="table-row group">
                                                <td class="px-8 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="font-black text-slate-800">{{ $item->libelle }}</span>
                                                        <span class="text-[10px] font-mono text-blue-600 font-bold">{{ $item->code }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-4 text-right font-bold text-slate-600">
                                                    {{ number_format($item->total_charges, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-8 py-4 text-right font-bold text-slate-600">
                                                    {{ number_format($item->total_produits, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-8 py-4 text-right font-black {{ $resultat >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($resultat, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-8 py-4 text-center">
                                                    <span class="badge {{ $marge >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} rounded-pill font-black text-[10px] px-2 py-1">
                                                        {{ number_format($marge, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-8 py-12 text-center text-slate-400 font-bold italic">
                                                    Aucun mouvement de charges ou de produits trouvé pour cet axe.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($results->count() > 0)
                                    <tfoot class="bg-slate-50/50">
                                        <tr>
                                            <td class="px-8 py-5 text-sm font-black text-slate-800 uppercase tracking-widest">Global</td>
                                            <td class="px-8 py-5 text-right font-black text-slate-800">{{ number_format($grandTotalCharges, 2, ',', ' ') }}</td>
                                            <td class="px-8 py-5 text-right font-black text-slate-800">{{ number_format($grandTotalProduits, 2, ',', ' ') }}</td>
                                            @php 
                                                $grandResultat = $grandTotalProduits - $grandTotalCharges; 
                                                $grandMarge = $grandTotalProduits > 0 ? ($grandResultat / $grandTotalProduits) * 100 : 0;
                                            @endphp
                                            <td class="px-8 py-5 text-right font-black {{ $grandResultat >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                                {{ number_format($grandResultat, 2, ',', ' ') }}
                                            </td>
                                            <td class="px-8 py-5 text-center font-black {{ $grandMarge >= 0 ? 'text-green-700' : 'text-red-700' }}">
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
