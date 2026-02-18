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
                @include('components.header', ['page_title' => 'Grand Livre <span class="text-gradient">Analytique</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Filtres -->
                        <div class="glass-card p-6 mb-8">
                            <form id="filterForm" action="{{ route('analytique.grand_livre') }}" method="GET">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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
                                        <label class="input-label-premium">Section Analytique</label>
                                        <select name="section_id" class="input-field-premium" required>
                                            <option value="">Sélectionner une section...</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ $selectedSectionId == $section->id ? 'selected' : '' }}>
                                                    {{ $section->code }} - {{ $section->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                                    <div class="flex gap-4">
                                        <div class="flex-1">
                                            <label class="input-label-premium">Date Début</label>
                                            <input type="date" name="date_debut" class="input-field-premium" value="{{ $data['date_debut'] ?? '' }}">
                                        </div>
                                        <div class="flex-1">
                                            <label class="input-label-premium">Date Fin</label>
                                            <input type="date" name="date_fin" class="input-field-premium" value="{{ $data['date_fin'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('analytique.grand_livre') }}" class="btn btn-label-secondary rounded-xl px-6 py-2.5 font-bold text-xs uppercase">
                                            Réinitialiser
                                        </a>
                                        <button type="submit" class="btn-filter uppercase">
                                            <i class="fas fa-search me-2"></i> Afficher
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tableau des Résultats -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/30 flex justify-between items-center">
                                <div>
                                    <h5 class="text-slate-800 font-black mb-0">Mouvements de la section</h5>
                                    @if($selectedSectionId)
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">
                                            Section : {{ $sections->where('id', $selectedSectionId)->first()?->libelle ?? 'Non définie' }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <button class="btn btn-xs btn-outline-primary rounded-lg font-bold"><i class="fas fa-file-excel me-1"></i> Excel</button>
                                    <button class="btn btn-xs btn-outline-danger rounded-lg font-bold"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-white border-b border-slate-100">
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">N° Saisie</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Compte</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Libellé de l'opération</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Vent. %</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Montant</th>
                                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Sens</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @php 
                                            $totalDebit = 0;
                                            $totalCredit = 0;
                                        @endphp
                                        @forelse ($results as $item)
                                            @php
                                                if ($item->sens == 'D') $totalDebit += $item->montant;
                                                else $totalCredit += $item->montant;
                                            @endphp
                                            <tr class="table-row group">
                                                <td class="px-8 py-4 text-slate-600 font-medium whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-8 py-4">
                                                    <span class="font-bold text-slate-900">{{ $item->n_saisie }}</span>
                                                </td>
                                                <td class="px-8 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="font-black text-blue-700 text-[11px]">{{ $item->numero_de_compte }}</span>
                                                        <span class="text-[10px] text-slate-500 truncate" style="max-width: 120px;" title="{{ $item->compte_libelle }}">{{ $item->compte_libelle }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-4 text-slate-700 font-medium">
                                                    {{ $item->description_operation }}
                                                </td>
                                                <td class="px-8 py-4 text-center">
                                                    <span class="badge bg-blue-50 text-blue-700 rounded-pill font-black text-[10px] px-2 py-1">
                                                        {{ number_format($item->pourcentage, 0) }}%
                                                    </span>
                                                </td>
                                                <td class="px-8 py-4 text-right font-black text-slate-800">
                                                    {{ number_format($item->montant, 2, ',', ' ') }}
                                                </td>
                                                <td class="px-8 py-4 text-center">
                                                    <span class="inline-flex w-7 h-7 items-center justify-center rounded-lg font-black text-[10px] {{ $item->sens == 'D' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                                        {{ $item->sens }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-8 py-12 text-center text-slate-400 font-bold italic">
                                                    <i class="fas fa-search text-3xl mb-3 block opacity-20"></i>
                                                    @if($selectedSectionId)
                                                        Aucun mouvement trouvé pour cette section.
                                                    @else
                                                        Veuillez sélectionner une section pour afficher les mouvements.
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($results->count() > 0)
                                    <tfoot class="bg-slate-50/50">
                                        <tr class="border-t-2 border-slate-200">
                                            <td colspan="5" class="px-8 py-4 text-[10px] font-black text-slate-700 uppercase tracking-widest">Totaux Période</td>
                                            <td class="px-8 py-4 text-right">
                                                <div class="flex flex-col items-end gap-1">
                                                    <div class="text-xs font-black text-slate-900 border-b border-slate-200 pb-1">
                                                        Débit : {{ number_format($totalDebit, 2, ',', ' ') }}
                                                    </div>
                                                    <div class="text-xs font-black text-slate-900 border-b border-slate-200 pb-1">
                                                        Crédit : {{ number_format($totalCredit, 2, ',', ' ') }}
                                                    </div>
                                                    @php $solde = $totalDebit - $totalCredit; @endphp
                                                    <div class="text-xs font-black {{ $solde >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                                        Solde : {{ number_format(abs($solde), 2, ',', ' ') }} {{ $solde >= 0 ? 'D' : 'C' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td></td>
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
