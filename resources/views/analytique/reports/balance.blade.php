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
    .section-header-row { background: linear-gradient(90deg, #eff6ff 0%, #f0f9ff 100%); border-left: 3px solid #1e40af; }
    .section-total-row { background-color: #f8fafc; }
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
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Balance <span class="text-gradient">Analytique</span>'])

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

                        {{-- Filter Card --}}
                        <div class="glass-card p-6 mb-6">
                            <form action="{{ route('analytique.balance') }}" method="GET">
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
                                        <a href="{{ route('analytique.balance') }}" class="btn-primary-action btn-ghost flex-1 justify-center">
                                            <i class="fas fa-times"></i> Effacer
                                        </a>
                                        <button type="submit" class="btn-primary-action btn-blue flex-1 justify-center">
                                            <i class="fas fa-search"></i> Afficher
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Table Card --}}
                        <div class="glass-card overflow-hidden">
                            {{-- Table Header --}}
                            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h5 class="text-slate-800 font-black mb-0 text-base">Balance Analytique</h5>
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
                                    <a href="{{ route('analytique.balance.excel', request()->all()) }}" class="badge-dl badge-excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <a href="{{ route('analytique.balance.pdf', request()->all()) }}" class="badge-dl badge-pdf">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>

                            {{-- Table --}}
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" style="font-size: 0.82rem;">
                                    <thead>
                                        <tr class="bg-slate-50 border-b-2 border-slate-200">
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest w-32">Code</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Intitulé Section / Compte</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-40">Débit</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-40">Crédit</th>
                                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-40">Solde</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grandTotalDebit = 0;
                                            $grandTotalCredit = 0;
                                        @endphp
                                        @forelse ($results as $section)
                                            @php
                                                $grandTotalDebit  += $section->total_debit;
                                                $grandTotalCredit += $section->total_credit;
                                                $soldeSec = $section->total_debit - $section->total_credit;
                                            @endphp

                                            {{-- Section Header --}}
                                            <tr class="section-header-row border-t border-blue-100">
                                                <td class="px-5 py-2.5 font-black text-blue-800 text-xs uppercase tracking-wider" colspan="5">
                                                    <i class="fas fa-folder text-blue-400 mr-2"></i>
                                                    {{ $section->section_code }} — {{ $section->section_libelle }}
                                                </td>
                                            </tr>

                                            {{-- Account Lines --}}
                                            @foreach ($section->lignes as $ligne)
                                                @php $solde = $ligne->total_debit - $ligne->total_credit; @endphp
                                                <tr class="table-row border-b border-slate-50">
                                                    <td class="px-5 py-2 pl-10">
                                                        <span class="font-mono text-blue-700 text-xs font-bold">{{ $ligne->numero_de_compte }}</span>
                                                    </td>
                                                    <td class="px-5 py-2 text-slate-700">{{ $ligne->compte_libelle }}</td>
                                                    <td class="px-5 py-2 text-right text-slate-700 font-medium">
                                                        {{ $ligne->total_debit > 0 ? number_format($ligne->total_debit, 2, ',', ' ') : '—' }}
                                                    </td>
                                                    <td class="px-5 py-2 text-right text-slate-700 font-medium">
                                                        {{ $ligne->total_credit > 0 ? number_format($ligne->total_credit, 2, ',', ' ') : '—' }}
                                                    </td>
                                                    <td class="px-5 py-2 text-right font-bold {{ $solde >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ number_format(abs($solde), 2, ',', ' ') }}
                                                        <span class="text-[9px] ml-0.5">{{ $solde >= 0 ? 'D' : 'C' }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            {{-- Section Total --}}
                                            <tr class="section-total-row border-t border-slate-200">
                                                <td class="px-5 py-2 pl-10 text-xs font-black text-slate-600 uppercase" colspan="2">
                                                    Total {{ $section->section_libelle }}
                                                </td>
                                                <td class="px-5 py-2 text-right font-black text-slate-800">{{ number_format($section->total_debit, 2, ',', ' ') }}</td>
                                                <td class="px-5 py-2 text-right font-black text-slate-800">{{ number_format($section->total_credit, 2, ',', ' ') }}</td>
                                                <td class="px-5 py-2 text-right font-black {{ $soldeSec >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                                    {{ number_format(abs($soldeSec), 2, ',', ' ') }}
                                                    <span class="text-[9px] ml-0.5">{{ $soldeSec >= 0 ? 'D' : 'C' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-8 py-14 text-center">
                                                    <i class="fas fa-folder-open text-4xl text-slate-200 block mb-3"></i>
                                                    <p class="text-slate-400 font-bold text-sm">Aucune donnée trouvée pour cet axe sur la période sélectionnée.</p>
                                                    <p class="text-slate-300 text-xs mt-1">Vérifiez que des écritures ont été ventilées sur cet axe.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($results->count() > 0)
                                    <tfoot>
                                        <tr class="grand-total-row">
                                            <td colspan="2" class="px-5 py-4 text-xs font-black text-white uppercase tracking-widest">
                                                <i class="fas fa-sigma mr-2 text-blue-300"></i>Totaux Généraux de la Balance
                                            </td>
                                            <td class="px-5 py-4 text-right font-black text-white">{{ number_format($grandTotalDebit, 2, ',', ' ') }}</td>
                                            <td class="px-5 py-4 text-right font-black text-white">{{ number_format($grandTotalCredit, 2, ',', ' ') }}</td>
                                            @php $grandSolde = $grandTotalDebit - $grandTotalCredit; @endphp
                                            <td class="px-5 py-4 text-right font-black text-white">
                                                {{ number_format(abs($grandSolde), 2, ',', ' ') }}
                                                <span class="text-[9px] ml-0.5 text-blue-200">{{ $grandSolde >= 0 ? 'D' : 'C' }}</span>
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
