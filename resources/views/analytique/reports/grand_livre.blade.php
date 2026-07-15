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
    .sens-d { background: #fee2e2; color: #dc2626; padding: 1px 6px; border-radius: 4px; font-size: 0.68rem; font-weight: 800; }
    .sens-c { background: #dcfce7; color: #16a34a; padding: 1px 6px; border-radius: 4px; font-size: 0.68rem; font-weight: 800; }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Grand Livre <span class="text-gradient">Analytique</span>'])

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
                            <form id="filterForm" action="{{ route('analytique.grand_livre') }}" method="GET">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label class="input-label-premium">Axe Analytique</label>
                                        <select name="axe_id" class="input-field-premium" onchange="updateSections(this.value)">
                                            @foreach($axes as $axe)
                                                <option value="{{ $axe->id }}" {{ $selectedAxeId == $axe->id ? 'selected' : '' }}>
                                                    {{ $axe->libelle }} ({{ $axe->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="input-label-premium">Section Analytique</label>
                                        <select name="section_id" id="sectionSelect" class="input-field-premium">
                                            <option value="all" {{ ($selectedSectionId === 'all' || !$selectedSectionId) ? 'selected' : '' }}>
                                                — Toutes les sections —
                                            </option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}" {{ $selectedSectionId == $section->id ? 'selected' : '' }}>
                                                    {{ $section->code }} - {{ $section->libelle }}
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
                                </div>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('analytique.grand_livre') }}" class="btn-primary-action btn-ghost">
                                        <i class="fas fa-undo"></i> Réinitialiser
                                    </a>
                                    <button type="submit" class="btn-primary-action btn-blue">
                                        <i class="fas fa-search"></i> Afficher
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Results Card --}}
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <h5 class="text-slate-800 font-black mb-0 text-base">Mouvements Analytiques</h5>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                        Axe : {{ $axes->where('id', $selectedAxeId)->first()?->libelle ?? '—' }}
                                        @if($selectedSectionId && $selectedSectionId !== 'all')
                                            &nbsp;|&nbsp; Section :
                                            {{ $sections->where('id', $selectedSectionId)->first()?->libelle ?? $selectedSectionId }}
                                        @else
                                            &nbsp;|&nbsp; Toutes les sections
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('analytique.grand_livre.excel', request()->all()) }}" class="badge-dl badge-excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <a href="{{ route('analytique.grand_livre.pdf', request()->all()) }}" class="badge-dl badge-pdf">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" style="font-size: 0.82rem;">
                                    <thead>
                                        <tr class="bg-slate-50 border-b-2 border-slate-200">
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest w-24">Date</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest w-28">N° Saisie</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest w-32">Compte</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Libellé de l'opération</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center w-16">Vent.%</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-36">Montant Débit</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-36">Montant Crédit</th>
                                            <th class="px-4 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right w-36">Solde Progressif</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalDebit = 0;
                                            $totalCredit = 0;
                                            $currentSection = null;
                                            $sectionDebit = 0;
                                            $sectionCredit = 0;
                                            $runningBalance = 0;
                                        @endphp

                                        @forelse ($results as $item)
                                            @php
                                                $montantDebit = $item->sens == 'D' ? $item->montant : 0;
                                                $montantCredit = $item->sens == 'C' ? $item->montant : 0;
                                                $totalDebit += $montantDebit;
                                                $totalCredit += $montantCredit;
                                                $runningBalance += ($montantDebit - $montantCredit);

                                                // Section separator when showing all sections
                                                $sectionChanged = ($selectedSectionId === 'all') && ($currentSection !== ($item->section_code ?? null));
                                                if ($sectionChanged) {
                                                    $currentSection = $item->section_code ?? null;
                                                }
                                            @endphp

                                            @if($sectionChanged)
                                                <tr class="section-header-row border-t border-blue-100">
                                                    <td class="px-4 py-2.5 font-black text-blue-800 text-xs uppercase tracking-wider" colspan="8">
                                                        <i class="fas fa-folder text-blue-400 mr-2"></i>
                                                        {{ $item->section_code ?? '' }} — {{ $item->section_libelle ?? '' }}
                                                    </td>
                                                </tr>
                                            @endif

                                            <tr class="table-row border-b border-slate-50">
                                                <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap font-medium">
                                                    {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <span class="font-bold text-slate-900 text-xs">{{ $item->n_saisie }}</span>
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <div class="flex flex-col">
                                                        <span class="font-black text-blue-700 text-xs">{{ $item->numero_de_compte }}</span>
                                                        <span class="text-[10px] text-slate-500 truncate" style="max-width: 110px;" title="{{ $item->compte_libelle }}">{{ $item->compte_libelle }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 text-slate-700 font-medium">{{ $item->description_operation }}</td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <span class="bg-blue-50 text-blue-700 rounded px-1.5 py-0.5 text-[10px] font-black">{{ number_format($item->pourcentage, 0) }}%</span>
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-medium {{ $montantDebit > 0 ? 'text-slate-800' : 'text-slate-300' }}">
                                                    {{ $montantDebit > 0 ? number_format($montantDebit, 2, ',', ' ') : '—' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-medium {{ $montantCredit > 0 ? 'text-slate-800' : 'text-slate-300' }}">
                                                    {{ $montantCredit > 0 ? number_format($montantCredit, 2, ',', ' ') : '—' }}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-black {{ $runningBalance >= 0 ? 'text-slate-800' : 'text-red-600' }}">
                                                    {{ number_format(abs($runningBalance), 2, ',', ' ') }}
                                                    <span class="{{ $runningBalance >= 0 ? 'sens-d' : 'sens-c' }}">{{ $runningBalance >= 0 ? 'D' : 'C' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="px-8 py-14 text-center">
                                                    <i class="fas fa-search text-4xl text-slate-200 block mb-3"></i>
                                                    <p class="text-slate-400 font-bold text-sm">Aucun mouvement trouvé.</p>
                                                    <p class="text-slate-300 text-xs mt-1">Sélectionnez un axe et/ou une section pour afficher les mouvements.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($results->count() > 0)
                                    <tfoot>
                                        <tr class="grand-total-row">
                                            <td colspan="4" class="px-4 py-4 text-xs font-black text-white uppercase tracking-widest">
                                                <i class="fas fa-sigma mr-2 text-blue-300"></i>Totaux Période
                                            </td>
                                            <td class="px-4 py-4 text-center text-slate-400">—</td>
                                            <td class="px-4 py-4 text-right font-black text-white">{{ number_format($totalDebit, 2, ',', ' ') }}</td>
                                            <td class="px-4 py-4 text-right font-black text-white">{{ number_format($totalCredit, 2, ',', ' ') }}</td>
                                            @php $soldeTotal = $totalDebit - $totalCredit; @endphp
                                            <td class="px-4 py-4 text-right font-black text-white">
                                                {{ number_format(abs($soldeTotal), 2, ',', ' ') }}
                                                <span class="text-[9px] ml-0.5 text-blue-200">{{ $soldeTotal >= 0 ? 'D' : 'C' }}</span>
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

    <script>
        window.updateSections = function(axeId) {
            fetch(`/analytique/axes/${axeId}/sections`)
                .then(r => r.json())
                .then(data => {
                    const select = document.getElementById('sectionSelect');
                    select.innerHTML = '<option value="all">— Toutes les sections —</option>';
                    data.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = `${s.code} - ${s.libelle}`;
                        select.appendChild(opt);
                    });
                })
                .catch(() => {});
        };
    </script>
</body>
</html>
