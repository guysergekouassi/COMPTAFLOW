<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .lettrage-container {
        padding: 2rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: calc(100vh - 80px);
    }

    .column-height {
        height: 600px;
    }

    .sticky-header th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        z-index: 10;
        border-bottom: 1px solid #e2e8f0;
    }

    .row-lettree {
        background-color: #f0fdf4 !important;
        opacity: 0.7;
    }

    .btn-lettrer-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.4);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Lettrage des <span class="text-gradient">Tiers</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Opérations</span>'])

                <div class="content-wrapper lettrage-container">
                    <div class="container-fluid">
                        
                        <!-- Filtre Tiers -->
                        <div class="glass-card p-6 mb-8">
                            <form action="{{ route('lettrage.index') }}" method="GET" class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="tier_id" class="form-label font-bold text-slate-700 uppercase tracking-wider text-xs">Sélectionner un Tiers</label>
                                    <select name="tier_id" id="tier_id" class="form-select rounded-xl border-slate-200" onchange="this.form.submit()">
                                        <option value="">-- Choisir un client ou fournisseur --</option>
                                        @foreach($tiers as $tier)
                                            <option value="{{ $tier->id }}" {{ (isset($selectedTier) && $selectedTier->id == $tier->id) ? 'selected' : '' }}>
                                                {{ $tier->numero_de_tiers }} - {{ $tier->intitule }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_lettrees" id="show_lettrees" value="1" {{ request()->boolean('show_lettrees') ? 'checked' : '' }} onchange="this.form.submit()">
                                        <label class="form-check-label text-sm text-slate-600" for="show_lettrees">Afficher les écritures lettrées</label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @if($selectedTier)
                            <div class="row g-4">
                                <!-- Colonne DÉBIT -->
                                <div class="col-md-6">
                                    <div class="glass-card flex flex-col h-[650px]">
                                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center rounded-t-2xl">
                                            <h3 class="font-bold text-slate-800 mb-0">Débit</h3>
                                            <span class="badge bg-blue-100 text-blue-700 rounded-pill px-3 count-debit">0 sélectionnées</span>
                                        </div>
                                        <div class="overflow-y-auto flex-1 p-0">
                                            <table class="table table-hover mb-0">
                                                <thead class="sticky-header">
                                                    <tr>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase w-10">
                                                            <input type="checkbox" class="form-check-input check-all-debit">
                                                        </th>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Date</th>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Libellé</th>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase text-end">Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($ecritures->where('debit', '>', 0) as $ecriture)
                                                        <tr class="{{ $ecriture->lettrage_id ? 'row-lettree' : '' }}">
                                                            <td class="px-4 py-3">
                                                                @if(!$ecriture->lettrage_id)
                                                                    <input type="checkbox" name="ecritures[]" value="{{ $ecriture->id }}" class="form-check-input ecriture-check debit-check" data-amount="{{ $ecriture->debit }}">
                                                                @else
                                                                    <span class="badge bg-success font-bold text-xs" title="Lettrage: {{ $ecriture->lettrage->code }}">{{ $ecriture->lettrage->code }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }}</td>
                                                            <td class="px-4 py-3 text-sm text-slate-800">{{ Str::limit($ecriture->libelle ?? $ecriture->description_operation, 40) }}</td>
                                                            <td class="px-4 py-3 text-sm font-bold text-end">{{ number_format($ecriture->debit, 0, ',', ' ') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="p-8 text-center text-slate-400 italic">Aucune écriture au débit</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne CRÉDIT -->
                                <div class="col-md-6">
                                    <div class="glass-card flex flex-col h-[650px]">
                                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center rounded-t-2xl">
                                            <h3 class="font-bold text-slate-800 mb-0">Crédit</h3>
                                            <span class="badge bg-green-100 text-green-700 rounded-pill px-3 count-credit">0 sélectionnées</span>
                                        </div>
                                        <div class="overflow-y-auto flex-1 p-0">
                                            <table class="table table-hover mb-0">
                                                <thead class="sticky-header">
                                                    <tr>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase w-10">
                                                            <input type="checkbox" class="form-check-input check-all-credit">
                                                        </th>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Date</th>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Libellé</th>
                                                        <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase text-end">Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($ecritures->where('credit', '>', 0) as $ecriture)
                                                        <tr class="{{ $ecriture->lettrage_id ? 'row-lettree' : '' }}">
                                                            <td class="px-4 py-3">
                                                                @if(!$ecriture->lettrage_id)
                                                                    <input type="checkbox" name="ecritures[]" value="{{ $ecriture->id }}" class="form-check-input ecriture-check credit-check" data-amount="{{ $ecriture->credit }}">
                                                                @else
                                                                    <span class="badge bg-success font-bold text-xs" title="Lettrage: {{ $ecriture->lettrage->code }}">{{ $ecriture->lettrage->code }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-sm text-slate-600">{{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }}</td>
                                                            <td class="px-4 py-3 text-sm text-slate-800">{{ Str::limit($ecriture->libelle ?? $ecriture->description_operation, 40) }}</td>
                                                            <td class="px-4 py-3 text-sm font-bold text-end">{{ number_format($ecriture->credit, 0, ',', ' ') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="p-8 text-center text-slate-400 italic">Aucune écriture au crédit</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BARRE D'ACTION FLOTTANTE -->
                            <div class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-slate-900 border border-slate-700/50 backdrop-blur-md text-white px-8 py-5 rounded-3xl shadow-2xl flex items-center gap-12 z-50 transition-all duration-500" id="action-bar" style="display: none; min-width: 600px;">
                                <div class="flex gap-10">
                                    <div class="text-center">
                                        <span class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">Total Débit</span>
                                        <span class="font-black text-xl text-blue-400" id="total-debit">0</span>
                                    </div>
                                    <div class="text-center">
                                        <span class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">Total Crédit</span>
                                        <span class="font-black text-xl text-emerald-400" id="total-credit">0</span>
                                    </div>
                                    <div class="border-l border-slate-700 pl-10 text-center">
                                        <span class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-1">Écart</span>
                                        <span class="font-black text-xl" id="ecart">0</span>
                                    </div>
                                </div>
                                
                                <button id="btn-lettrer" class="btn btn-lettrer-premium text-white px-10 py-3 rounded-2xl font-black uppercase tracking-wider text-sm disabled:opacity-30 disabled:grayscale transition-all hover:scale-105 active:scale-95" disabled>
                                    <i class="fas fa-link mr-2"></i> Lettrer
                                </button>
                            </div>
                        @else
                            <div class="glass-card py-24 text-center">
                                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                                    <i class="fas fa-search text-3xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800 mb-2">Prêt à lettrer ?</h3>
                                <p class="text-slate-500 max-w-sm mx-auto">Veuillez sélectionner un tiers ci-dessus pour afficher et rapprocher les factures et règlements correspondants.</p>
                            </div>
                        @endif
                    </div>
                </div>

                @include('components.footer')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.ecriture-check');
            const actionBar = document.getElementById('action-bar');
            const totalDebitEl = document.getElementById('total-debit');
            const totalCreditEl = document.getElementById('total-credit');
            const ecartEl = document.getElementById('ecart');
            const btnLettrer = document.getElementById('btn-lettrer');

            let totalDebit = 0;
            let totalCredit = 0;

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateTotals);
            });

            function formatNumber(num) {
                return new Intl.NumberFormat('fr-FR').format(num);
            }

            function updateTotals() {
                totalDebit = 0;
                totalCredit = 0;
                let count = 0;

                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        count++;
                        const amount = parseFloat(cb.dataset.amount);
                        if (cb.classList.contains('debit-check')) {
                            totalDebit += amount;
                        } else {
                            totalCredit += amount;
                        }
                    }
                });

                if (count > 0) {
                    actionBar.style.display = 'flex';
                    actionBar.classList.remove('opacity-0');
                } else {
                    actionBar.style.display = 'none';
                }

                totalDebitEl.textContent = formatNumber(totalDebit);
                totalCreditEl.textContent = formatNumber(totalCredit);
                
                const ecart = Math.abs(totalDebit - totalCredit);
                ecartEl.textContent = formatNumber(ecart);

                if (ecart < 0.01 && count >= 2) {
                    btnLettrer.disabled = false;
                    ecartEl.className = 'font-black text-xl text-emerald-400';
                    ecartEl.innerHTML = '<i class="fas fa-check-circle mr-1"></i>0';
                } else {
                    btnLettrer.disabled = true;
                    ecartEl.className = 'font-black text-xl text-rose-400';
                }
            }

            btnLettrer.addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('.ecriture-check:checked')).map(cb => cb.value);

                if (!confirm('Confirmer le lettrage de ces ' + selectedIds.length + ' écritures ?')) {
                    return;
                }

                fetch("{{ route('lettrage.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ ecriture_ids: selectedIds })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur est survenue lors du lettrage.');
                });
            });

            // "Check All" logic
            $('.check-all-debit').on('change', function() {
                $('.debit-check').prop('checked', $(this).is(':checked')).trigger('change');
            });
            $('.check-all-credit').on('change', function() {
                $('.credit-check').prop('checked', $(this).is(':checked')).trigger('change');
            });
        });
    </script>
</body>
</html>
