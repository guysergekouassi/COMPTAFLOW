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
    .btn-action { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Règles <span class="text-gradient">Analytiques</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header -->
                        <div class="glass-card px-8 py-6 mb-8 flex justify-between items-center relative overflow-hidden">
                            <div class="relative z-10">
                                <h4 class="text-slate-800 font-black mb-1 text-2xl">Configuration des Règles</h4>
                                <p class="text-slate-500 font-medium text-sm mb-0">Définissez les ventilations par défaut pour automatiser la saisie</p>
                            </div>
                            <div class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-blue-50/50 to-transparent pointer-events-none"></div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Formulaire de Configuration -->
                            <div class="lg:col-span-2">
                                <div class="glass-card p-8">
                                    <form action="{{ route('analytique.regles.store') }}" method="POST" id="rulesForm">
                                        @csrf
                                        <div class="mb-6">
                                            <label class="input-label-premium">Compte Général (Charges/Produits)</label>
                                            <select name="plan_comptable_id" id="accountSelect" class="select2-premium w-full" required onchange="loadRules(this.value)">
                                                <option value="">Sélectionnez un compte...</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">
                                                        {{ $account->numero_de_compte }} - {{ \Illuminate\Support\Str::limit($account->intitule, 40) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="border-t border-slate-100 my-6"></div>

                                        <div id="ventilationsContainer">
                                            <div class="flex justify-between items-center mb-4">
                                                <label class="input-label-premium mb-0">Répartition Analytique</label>
                                                <button type="button" onclick="addVentilationRow()" class="btn btn-xs btn-outline-primary rounded-lg font-bold">
                                                    <i class="fas fa-plus me-1"></i> Ajouter une ligne
                                                </button>
                                            </div>
                                            
                                            <div id="rowsWrapper" class="space-y-3">
                                                <!-- Rows will be injected here via JS -->
                                            </div>
                                        </div>

                                        <div class="mt-6 p-4 bg-slate-50 rounded-xl flex justify-between items-center border border-slate-200">
                                            <div>
                                                <span class="text-xs font-bold text-slate-500 uppercase">Total Ventilation</span>
                                                <div id="totalPercentageDisplay" class="text-xl font-black text-slate-800">0%</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary font-bold px-6 py-2.5 rounded-xl shadow-lg shadow-blue-200" id="submitBtn" disabled>
                                                <i class="fas fa-save me-2"></i> Enregistrer la Règle
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Explications / Aide -->
                            <div class="lg:col-span-1">
                                <div class="glass-card p-6 bg-gradient-to-br from-white to-blue-50/30">
                                    <h5 class="text-slate-800 font-black mb-4 flex items-center gap-2">
                                        <i class="fas fa-lightbulb text-yellow-500"></i> Fonctionnement
                                    </h5>
                                    <ul class="space-y-4">
                                        <li class="flex gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold text-xs">1</div>
                                            <p class="text-sm text-slate-600 font-medium leading-relaxed">
                                                Sélectionnez un compte comptable (Classe 6 ou 7) pour lequel vous souhaitez définir une règle.
                                            </p>
                                        </li>
                                        <li class="flex gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold text-xs">2</div>
                                            <p class="text-sm text-slate-600 font-medium leading-relaxed">
                                                Ajoutez les sections analytiques sur lesquelles le montant devra être réparti.
                                            </p>
                                        </li>
                                        <li class="flex gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold text-xs">3</div>
                                            <p class="text-sm text-slate-600 font-medium leading-relaxed">
                                                Le total des pourcentages doit impérativement être égal à <strong>100%</strong> pour valider la règle.
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Row (Hidden) -->
    <template id="rowTemplate">
        <div class="ventilation-row grid grid-cols-12 gap-3 items-center group bg-white p-3 rounded-xl border border-slate-100 hover:border-blue-200 transition-colors shadow-sm">
            <div class="col-span-7">
                <select name="ventilations[INDEX][section_id]" class="input-field-premium text-sm section-select" required>
                    <option value="">Sélectionner une section...</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">
                             [{{ $section->axe->libelle }}] {{ $section->code }} - {{ $section->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-3 relaltive">
                <input type="number" name="ventilations[INDEX][pourcentage]" class="input-field-premium text-right font-bold pourcentage-input" 
                       placeholder="0" min="0" max="100" step="0.01" required oninput="calculateTotal()">
                <span class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold pointer-events-none">%</span>
            </div>
            <div class="col-span-2 text-right">
                <button type="button" onclick="removeRow(this)" class="btn btn-xs btn-outline-danger btn-icon rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </template>

    @include('components.footer')

    <script>
        let rowCount = 0;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('.select2-premium').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Rechercher un compte...'
            });
            
            // Add initial row if empty
            // addVentilationRow();
        });

        function addVentilationRow(data = null) {
            const template = document.getElementById('rowTemplate').innerHTML;
            const newRowHtml = template.replace(/INDEX/g, rowCount++);
            const wrapper = document.getElementById('rowsWrapper');
            
            // Create a temporary container to turn string into DOM nodes
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = newRowHtml;
            const newRow = tempDiv.firstElementChild;
            
            wrapper.appendChild(newRow);

            if (data) {
                newRow.querySelector('.section-select').value = data.section_id;
                newRow.querySelector('.pourcentage-input').value = data.pourcentage_defaut;
            }

            calculateTotal();
        }

        function removeRow(btn) {
            btn.closest('.ventilation-row').remove();
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.pourcentage-input').forEach(input => {
                const val = parseFloat(input.value) || 0;
                total += val;
            });

            const display = document.getElementById('totalPercentageDisplay');
            display.textContent = total.toFixed(2) + '%';
            
            const submitBtn = document.getElementById('submitBtn');
            const diff = Math.abs(total - 100);

            if (diff < 0.01 && total > 0) {
                display.classList.remove('text-red-600', 'text-slate-800');
                display.classList.add('text-green-600');
                submitBtn.disabled = false;
            } else {
                display.classList.remove('text-green-600', 'text-slate-800');
                display.classList.add('text-red-600');
                submitBtn.disabled = true;
            }
        }

        function loadRules(accountId) {
            if (!accountId) return;

            const wrapper = document.getElementById('rowsWrapper');
            wrapper.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-blue-500"></i> Chargement...</div>';

            fetch(`{{ url('/analytique/regles/get') }}/${accountId}`)
                .then(response => response.json())
                .then(data => {
                    wrapper.innerHTML = ''; // Clear spinner
                    if (data.length > 0) {
                        data.forEach(rule => {
                            addVentilationRow({
                                section_id: rule.section_id,
                                pourcentage_defaut: rule.pourcentage_defaut
                            });
                        });
                    } else {
                        addVentilationRow(); // Add empty row if no rules
                    }
                    calculateTotal();
                })
                .catch(error => {
                    console.error('Error:', error);
                    wrapper.innerHTML = '';
                    addVentilationRow();
                });
        }
    </script>
</body>
</html>
