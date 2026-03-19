<div class="space-y-6">
    @forelse($data as $compteGroup)
    <div class="card border-0 shadow-sm rounded-2xl overflow-hidden mb-4">
        <div class="card-header bg-slate-50 border-b border-slate-100 py-3 px-6">
            <h6 class="mb-0 font-bold text-slate-800">
                <span class="text-blue-700 font-mono">{{ $compteGroup['compte']->numero_de_compte }}</span> - {{ $compteGroup['compte']->intitule }}
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="bg-white">
                    <tr>
                        <th class="ps-6 py-2 text-[10px] font-bold text-slate-400 uppercase">Date</th>
                        <th class="py-2 text-[10px] font-bold text-slate-400 uppercase">Journal</th>
                        <th class="py-2 text-[10px] font-bold text-slate-400 uppercase">Libellé</th>
                        <th class="py-2 text-[10px] font-bold text-slate-400 uppercase text-end">Débit</th>
                        <th class="pe-6 py-2 text-[10px] font-bold text-slate-400 uppercase text-end">Crédit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($compteGroup['operations'] as $op)
                    <tr>
                        <td class="ps-6 py-2 text-xs text-slate-600">
                            {{ \Carbon\Carbon::parse($op->date)->format('d/m/Y') }}
                        </td>
                        <td class="py-2 text-xs text-slate-500 font-mono">
                            {{ $op->codeJournal->code ?? '-' }}
                        </td>
                        <td class="py-2 text-xs text-slate-700">
                            {{ $op->libelle }}
                        </td>
                        <td class="py-2 text-xs text-slate-600 text-end">
                            {{ number_format($op->debit, 0, ',', ' ') }}
                        </td>
                        <td class="pe-6 py-2 text-xs text-slate-600 text-end">
                            {{ number_format($op->credit, 0, ',', ' ') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm rounded-2xl p-10 text-center text-slate-400">
        <i class="fas fa-info-circle mb-2 text-xl"></i><br>
        Aucun mouvement trouvé pour cet exercice.
    </div>
    @endforelse
</div>
