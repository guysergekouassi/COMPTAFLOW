<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <style>
        /* Premium Modal Styles */
        .premium-modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 20px;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            max-width: 500px;
            margin: auto;
            padding: 1.5rem !important;
        }

        .input-field-premium {
            transition: all 0.2s ease;
            border: 2px solid #f1f5f9 !important;
            background-color: #f8fafc !important;
            border-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            width: 100%;
            box-sizing: border-box;
        }

        .input-field-premium:focus {
            border-color: #1e40af !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
            outline: none !important;
        }

        .text-blue-gradient-premium {
            background: linear-gradient(to right, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .input-label-premium {
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-left: 0.1rem !important;
            margin-bottom: 0.35rem !important;
            display: block !important;
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .table-row {
            transition: background-color 0.2s;
        }

        .table-row:hover {
            background-color: #f1f5f9;
        }

        .table-premium {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        .table-premium thead th {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 2rem !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }
        
        .table-premium tbody td {
            padding: 1.5rem 2rem !important;
            vertical-align: middle !important;
        }
    </style>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Flux de <span class="text-gradient">Trésorerie</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Configuration</span>'])

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        
                        <!-- Badge Section -->
                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Configurez les catégories et natures de flux pour vos analyses de trésorerie.
                            </p>
                        </div>

                        <!-- Section table -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-green-50 text-green-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-check-circle text-xl"></i>
                                    <span class="font-medium">{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-red-50 text-red-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span class="font-medium">{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Actions Bar -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-8 w-full gap-4">
                            <!-- Left Group: Filter -->
                            <div class="w-full md:w-auto">
                                <button type="button" id="toggleFilterBtn" onclick="window.toggleAdvancedFilter()"
                                    class="btn-action flex items-center justify-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm w-full md:w-auto shadow-sm hover:bg-slate-50 transition-all">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>

                            <!-- Right Group: Actions -->
                            <div class="w-full md:w-auto">
                                <button type="button" class="btn bg-blue-700 hover:bg-blue-800 text-white rounded-xl px-6 py-3 font-semibold shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-0.5 w-full md:w-auto flex items-center justify-center gap-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalCenterCreate">
                                    <i class="fas fa-plus"></i> Nouveau Flux
                                </button>
                            </div>
                        </div>

                        <!-- Filtre personnalisé -->
                        <!-- Advanced Filter Panel (Identical to Plan Tiers) -->
                        <div id="advancedFilterPanel" style="display: none;" class="mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="relative w-full">
                                        <label class="input-label-premium mb-2">Année</label>
                                        <input type="text" id="filter-annee" class="input-field-premium" placeholder="Année...">
                                    </div>
                                    <div class="relative w-full">
                                        <label class="input-label-premium mb-2">Mois</label>
                                        <input type="text" id="filter-mois" class="input-field-premium" placeholder="Mois...">
                                    </div>
                                    <div class="relative w-full">
                                        <label class="input-label-premium mb-2">Code</label>
                                        <input type="text" id="filter-code" class="input-field-premium" placeholder="Code...">
                                    </div>
                                    <div class="relative w-full">
                                        <label class="input-label-premium mb-2">Intitulé</label>
                                        <input type="text" id="filter-intitule" class="input-field-premium" placeholder="Intitulé...">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-4">
                                    <button type="button" class="btn btn-secondary rounded-xl px-6 font-semibold" id="reset-filters" onclick="window.resetAdvancedFilters()">
                                        <i class="fas fa-undo me-2"></i>Réinitialiser
                                    </button>
                                    <button type="button" class="btn btn-primary rounded-xl px-6 font-semibold" id="apply-filters" onclick="window.applyAdvancedFilters()">
                                        <i class="fas fa-search me-2"></i>Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- table  --}}
                        <script>
                            $(document).ready(function() {
                                $('#FluxTable').DataTable({
                                    pageLength: 10,
                                    lengthMenu: [10, 15, 20, 25],
                                    language: {
                                        search: "Rechercher :",
                                        lengthMenu: "Afficher _MENU_ lignes",
                                        info: "Affichage de _START_ à _END_ sur _TOTAL_ lignes",
                                        paginate: {
                                            first: "Premier",
                                            last: "Dernier",
                                            next: "Suivant",
                                            previous: "Précédent"
                                        },
                                        zeroRecords: "Aucune donnée trouvée",
                                        infoEmpty: "Aucune donnée à afficher",
                                        infoFiltered: "(filtré depuis _MAX_ lignes totales)"
                                    },
                                    dom: 'rtip' // Hide default search and length
                                });
                            });
                        </script>

                        <div class="glass-card overflow-hidden">
                            <div class="table-responsive">
                                <table class="w-full text-left border-collapse table-premium" id="FluxTable">
                                    <thead>
                                        <tr>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Catégorie</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Nature</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Compte Début</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Compte Fin</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($flux_types as $flux_type)
                                            <tr class="table-row">
                                                <td class="px-8 py-4 font-semibold text-slate-700">{{ $flux_type->categorie }}</td>
                                                <td class="px-8 py-4 text-slate-600">{{ $flux_type->nature }}</td>
                                                <td class="px-8 py-4 font-mono text-sm text-blue-600 font-bold">{{ $flux_type->PlanComptable1->numero_de_compte ?? 'N/A' }}</td>
                                                <td class="px-8 py-4 font-mono text-sm text-blue-600 font-bold">{{ $flux_type->PlanComptable2->numero_de_compte ?? 'N/A' }}</td>
                                                <td class="px-8 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <!-- Bouton Modifier -->
                                                        <button type="button"
                                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm bg-white"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalCenterUpdate"
                                                            data-id="{{ $flux_type->id }}"
                                                            data-categorie="{{ $flux_type->categorie }}"
                                                            data-nature="{{ $flux_type->nature }}"
                                                            data-plan-comptable1="{{ $flux_type->PlanComptable1->id }}"
                                                            data-plan-comptable2="{{ $flux_type->PlanComptable2->id }}">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </button>

                                                        <!-- Bouton Supprimer -->
                                                        <button type="button"
                                                            class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-100 text-red-500 hover:bg-red-50 hover:text-red-600 transition shadow-sm bg-white"
                                                            data-bs-toggle="modal" data-bs-target="#modalDeleteFlux"
                                                            data-id="{{ $flux_type->id }}"
                                                            data-label="{{ $flux_type->categorie }}">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>


                            {{-- Create type de flux --}}
                            <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <form id="formCreateTresorerie" method="POST"
                                        action="{{ route('gestion_tresorerie.store') }}">
                                        @csrf
                                        <div class="modal-content premium-modal-content">
                                            <div class="text-center mb-6 position-relative">
                                                <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer" style="top: -0.5rem; right: -0.5rem;"></button>
                                                <h4 class="text-2xl font-bold mb-1">Nouveau <span class="text-blue-gradient-premium">Flux</span></h4>
                                                <p class="text-slate-500 text-sm">Créez un nouveau type de flux de trésorerie.</p>
                                            </div>

                                            <div class="modal-body p-0">
                                                <div class="row g-4">
                                                    <div class="col-12">
                                                        <label for="categorie" class="input-label-premium">Catégorie</label>
                                                        <input type="text" id="categorie" name="categorie"
                                                            class="input-field-premium"
                                                            placeholder="Ex : Opérationnel, Investissement..." autocomplete="off"/>
                                                        <div class="text-danger small mt-1" id=""></div>
                                                    </div>

                                                    <div class="col-12">
                                                        <label for="nature" class="input-label-premium">Nature</label>
                                                        <input type="text" id="nature" name="nature"
                                                            class="input-field-premium"
                                                            placeholder="Ex : Marchandise, Matériel..." autocomplete="off"/>
                                                        <div class="text-danger small mt-1" id=""></div>
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="input-label-premium mb-2 border-b border-slate-100 pb-1 w-full text-slate-400">Comptes Généraux Associés</label>
                                                        
                                                        <div class="grid grid-cols-1 gap-4">
                                                            <div>
                                                                <label for="plan_comptable_id_1" class="input-label-premium text-xs text-blue-600">Compte de Début</label>
                                                                <select id="plan_comptable_id_1" name="plan_comptable_id_1"
                                                                    class="input-field-premium selectpicker w-100" data-width="100%"
                                                                    data-live-search="true" required>
                                                                    <option value="">-- Sélectionner --</option>
                                                                    @foreach ($PlanComptable as $plan)
                                                                        <option value="{{ $plan->id }}" data-subtext="{{ $plan->numero_de_compte }}">
                                                                            {{ $plan->intitule }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback">Veuillez sélectionner un compte.</div>
                                                            </div>
                                                            
                                                            <div>
                                                                <label for="plan_comptable_id_2" class="input-label-premium text-xs text-blue-600">Compte de Fin</label>
                                                                <select id="plan_comptable_id_2" name="plan_comptable_id_2"
                                                                    class="input-field-premium selectpicker w-100" data-width="100%"
                                                                    data-live-search="true" required>
                                                                    <option value="">-- Sélectionner --</option>
                                                                    @foreach ($PlanComptable as $plan)
                                                                        <option value="{{ $plan->id }}" data-subtext="{{ $plan->numero_de_compte }}">
                                                                            {{ $plan->intitule }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback" id="compte2-error">Veuillez sélectionner un compte.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                                                <button type="button" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors" data-bs-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 transition-all transform hover:-translate-y-0.5">
                                                    Enregistrer
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            {{-- Modal Update Type de Flux --}}
                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <form id="formUpdateFlux" method="POST"
                                        action="{{ route('gestion_tresorerie.update') }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="update_id" name="id">
                                        <div class="modal-content premium-modal-content">
                                            <div class="text-center mb-6 position-relative">
                                                <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer" style="top: -0.5rem; right: -0.5rem;"></button>
                                                <h4 class="text-2xl font-bold mb-1">Modifier <span class="text-blue-gradient-premium">Flux</span></h4>
                                                <p class="text-slate-500 text-sm">Mettez à jour les informations du flux.</p>
                                            </div>

                                            <div class="modal-body p-0">
                                                <div class="row g-4">
                                                    <div class="col-12">
                                                        <label for="update_categorie" class="input-label-premium">Catégorie</label>
                                                        <input type="text" id="update_categorie" name="categorie"
                                                            class="input-field-premium" autocomplete="off">
                                                    </div>
                                                    <div class="col-12">
                                                        <label for="update_nature" class="input-label-premium">Nature</label>
                                                        <input type="text" id="update_nature" name="nature"
                                                            class="input-field-premium" autocomplete="off">
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <label class="input-label-premium mb-2 border-b border-slate-100 pb-1 w-full text-slate-400">Comptes Généraux Associés</label>
                                                        
                                                        <div class="grid grid-cols-1 gap-4">
                                                            <div>
                                                                <label for="update_plan_comptable_id_1" class="input-label-premium text-xs text-blue-600">Compte de Début</label>
                                                                <select id="update_plan_comptable_id_1"
                                                                    name="plan_comptable_id_1" class="input-field-premium selectpicker w-100"
                                                                    data-width="100%" data-live-search="true" required>
                                                                    <option value="">-- Sélectionner --</option>
                                                                    @foreach ($PlanComptable as $plan)
                                                                        <option value="{{ $plan->id }}" data-subtext="{{ $plan->numero_de_compte }}">
                                                                            {{ $plan->intitule }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback">Veuillez sélectionner un compte.</div>
                                                            </div>
                                                            
                                                            <div>
                                                                <label for="update_plan_comptable_id_2" class="input-label-premium text-xs text-blue-600">Compte de Fin</label>
                                                                <select id="update_plan_comptable_id_2"
                                                                    name="plan_comptable_id_2" class="input-field-premium selectpicker w-100"
                                                                    data-width="100%" data-live-search="true" required>
                                                                    <option value="">-- Sélectionner --</option>
                                                                    @foreach ($PlanComptable as $plan)
                                                                        <option value="{{ $plan->id }}" data-subtext="{{ $plan->numero_de_compte }}">
                                                                            {{ $plan->intitule }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback">Veuillez sélectionner un compte.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                                                <button type="button" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors" data-bs-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 transition-all transform hover:-translate-y-0.5">
                                                    Mettre à jour
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            {{-- Modal de suppression --}}
                            <div class="modal fade" id="modalDeleteFlux" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <form id="formDeleteFlux" method="POST"
                                        action="{{ route('gestion_tresorerie.destroy') }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" id="delete_id" name="id">

                                        <div class="modal-content premium-modal-content">
                                            <div class="text-center mb-6">
                                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                                    <i class="bx bx-trash text-3xl text-red-500"></i>
                                                </div>
                                                <h4 class="text-xl font-bold text-slate-800 mb-2">Confirmer la suppression</h4>
                                                <p class="text-slate-500 text-sm">
                                                    Êtes-vous sûr de vouloir supprimer le flux <strong id="delete_label" class="text-slate-700"></strong> ?
                                                    <br>
                                                    <span class="text-red-500 font-medium mt-1 block">Cette action est irréversible.</span>
                                                </p>
                                            </div>
                                            
                                            <div class="flex items-center justify-center gap-3 mt-4">
                                                <button type="button" class="px-5 py-2.5 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-50 transition-colors w-1/3" data-bs-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-500 text-white font-bold text-sm shadow-lg shadow-red-200 hover:bg-red-600 hover:shadow-red-300 transition-all transform hover:-translate-y-0.5 w-1/3">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>



                        </div>
                    </div>
                    <!-- Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->

        <!-- Core JS -->

        @include('components.footer')

        <script>
            window.toggleAdvancedFilter = function() {
                const panel = document.getElementById('advancedFilterPanel');
                if (panel) {
                    panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
                }
            };

            window.resetAdvancedFilters = function() {
                document.getElementById('filter-annee').value = '';
                document.getElementById('filter-mois').value = '';
                document.getElementById('filter-code').value = '';
                document.getElementById('filter-intitule').value = '';
                // Si vous utilisez DataTables, ajoutez la logique de filtrage ici
                const table = $('#FluxTable').DataTable();
                table.search('').columns().search('').draw();
            };

            window.applyAdvancedFilters = function() {
                const annee = document.getElementById('filter-annee').value;
                const mois = document.getElementById('filter-mois').value;
                const code = document.getElementById('filter-code').value;
                const intitule = document.getElementById('filter-intitule').value;
                
                const table = $('#FluxTable').DataTable();
                table.column(0).search(annee);
                table.column(1).search(mois);
                table.column(2).search(code);
                table.column(3).search(intitule);
                table.draw();
            };
        </script>
        <script src="{{ asset('js/gestion_tresorerie.js') }}"></script>
</body>

</html>
