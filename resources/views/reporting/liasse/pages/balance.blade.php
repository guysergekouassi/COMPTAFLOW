<div class="card border-0 shadow-sm rounded-2xl overflow-hidden">
    <div class="card-header bg-white border-b border-slate-100 py-4 px-6 flex justify-between items-center">
        <div>
            <h5 class="mb-0 font-bold text-slate-800">Balance des comptes</h5>
            <p class="text-xs text-slate-500 mb-0">Données calculées pour l'exercice en cours</p>
        </div>
    </div>
    <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-slate-50">
                <tr>
                    <th class="ps-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Compte</th>
                    <th class="py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Intitulé</th>
                    <th class="py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-end">Débit</th>
                    <th class="py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-end">Crédit</th>
                    <th class="py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-end">Solde Débiteur</th>
                    <th class="pe-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-end">Solde Créditeur</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($data as $row)
                <tr>
                    <td class="ps-6 py-3 font-mono text-sm font-bold text-blue-700">{{ $row['numero'] }}</td>
                    <td class="py-3 text-sm text-slate-700">{{ $row['intitule'] }}</td>
                    <td class="py-3 text-sm text-slate-600 text-end">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                    <td class="py-3 text-sm text-slate-600 text-end">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                    <td class="py-3 text-sm font-bold text-slate-900 text-end">
                        {{ $row['solde_debiteur'] > 0 ? number_format($row['solde_debiteur'], 0, ',', ' ') : '-' }}
                    </td>
                    <td class="pe-6 py-3 text-sm font-bold text-slate-900 text-end">
                        {{ $row['solde_crediteur'] > 0 ? number_format($row['solde_crediteur'], 0, ',', ' ') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-slate-400">
                        <i class="fas fa-info-circle mb-2 text-xl"></i><br>
                        Aucune donnée disponible pour cet exercice.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        @if(count($data) > 0)
        @php
            $totalDebit = collect($data)->sum('debit');
            $totalCredit = collect($data)->sum('credit');
            $totalSoldeDeb = collect($data)->sum('solde_debiteur');
            $totalSoldeCred = collect($data)->sum('solde_crediteur');
        @endphp
        <div class="bg-slate-100 border-t border-slate-200 py-3 px-6">
            <div class="row font-bold text-slate-800 text-sm">
                <div class="col-md-6 text-end pe-4 uppercase tracking-wider text-xs text-slate-500">
                    TOTAUX GENERAUX
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-3 text-end px-2">{{ number_format($totalDebit, 0, ',', ' ') }}</div>
                        <div class="col-3 text-end px-2">{{ number_format($totalCredit, 0, ',', ' ') }}</div>
                        <div class="col-3 text-end px-2">{{ number_format($totalSoldeDeb, 0, ',', ' ') }}</div>
                        <div class="col-3 text-end px-2">{{ number_format($totalSoldeCred, 0, ',', ' ') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
