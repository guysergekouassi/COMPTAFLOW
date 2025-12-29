<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    /* Custom styles from plan_comptable for consistency */
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
    .btn-action {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }
    .table-row {
        transition: background-color 0.2s;
    }
    .table-row:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }

    /* Nouveau Design Premium pour les Modaux */
    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        max-width: 500px; /* Slight adjustment for form width */
        margin: auto;
        padding: 1.25rem !important;
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

    .btn-save-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        background-color: #1e40af !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.1) !important;
        transition: all 0.2s ease !important;
        border: none !important;
        width: 100%;
    }

    .btn-save-premium:hover {
        background-color: #1e3a8a !important;
        transform: translateY(-2px) !important;
    }

    .btn-cancel-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        transition: all 0.2s ease !important;
        border: none !important;
        background: transparent !important;
        width: 100%;
    }

    .btn-cancel-premium:hover {
        background-color: #f8fafc !important;
        color: #475569 !important;
    }

    /* Status Pills for Categories */
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('components.header', ['page_title' => 'Poste <span class="text-gradient">Trésorerie</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Configuration</span>'])
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Main Container -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Badge/Subtitle Section -->
                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Configuration des rubriques budgétaires et flux de trésorerie.
                            </p>
                        </div>

                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Actions Bar -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4">
                            <!-- Left Group: Filter -->
                            <div class="flex items-center">
                                <button type="button" id="toggleFilterBtn"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>

                            <!-- Right Group: Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#periodSelectionModal"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-file-pdf text-red-500"></i>
                                    Générer Plan
                                </button>

                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCreatePoste"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus"></i>
                                    Nouveau Poste
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel (Hidden by default) -->
                        <div id="advancedFilterPanel" class="hidden mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Filter Intitulé -->
                                    <div class="relative w-full">
                                        <input type="text" id="filterIntitule" placeholder="Rechercher par intitulé..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <!-- Filter Catégorie -->
                                    <div class="relative w-full">
                                        <select id="filterCategorie"
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm appearance-none">
                                            <option value="">Toutes les catégories</option>
                                            <option value="OPÉRATIONNEL">OPÉRATIONNEL</option>
                                            <option value="FINANCEMENT">FINANCEMENT</option>
                                            <option value="INVESTISSEMENT">INVESTISSEMENT</option>
                                        </select>
                                        <i class="fas fa-tags absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    </div>
                                </div>
                                <!-- Filter Actions -->
                                <div class="flex justify-end gap-3 mt-4">
                                    <button type="button" id="resetFilterBtn" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-semibold hover:bg-slate-200 transition">
                                        <i class="fas fa-undo mr-2"></i>Réinitialiser
                                    </button>
                                    <button type="button" id="applyFilterBtn" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                        <i class="fas fa-search mr-2"></i>Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Card -->
                        <div class="glass-card overflow-hidden mb-12">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Postes de Trésorerie</h3>
                                <p class="text-sm text-slate-500">Liste des postes et catégories de trésorerie</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse" id="posteTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Intitulé du Poste</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Catégorie</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse ($comptes as $item)
                                            <tr class="table-row group">
                                                <td class="px-8 py-6">
                                                    <p class="font-semibold text-slate-800">{{ $item->name }}</p>
                                                </td>
                                                <td class="px-8 py-6">
                                                    @php
                                                        $type = strtoupper($item->type ?? '');
                                                        $class = 'bg-slate-100 text-slate-600 border-slate-200';
                                                        $label = $item->type ?? 'NON DÉFINI';
                                                        
                                                        if (str_contains($type, 'OPERATIONNEL')) {
                                                            $class = 'bg-blue-100 text-blue-700 border-blue-200';
                                                            $label = 'OPÉRATIONNEL';
                                                        } elseif (str_contains($type, 'FINANCEMENT')) {
                                                            $class = 'bg-indigo-100 text-indigo-700 border-indigo-200';
                                                            $label = 'FINANCEMENT';
                                                        } elseif (str_contains($type, 'INVESTISSEMENT')) {
                                                            $class = 'bg-amber-100 text-amber-700 border-amber-200';
                                                            $label = 'INVESTISSEMENT';
                                                        }
                                                    @endphp
                                                    <span class="status-pill border {{ $class }}">{{ $label }}</span>
                                                </td>
                                                <td class="px-8 py-6 text-center">
                                                    <div class="flex justify-center gap-2 transition-opacity">
                                                        <button type="button"
                                                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm btn-update-poste"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalUpdatePoste"
                                                            data-id="{{ $item->id }}"
                                                            data-name="{{ $item->name }}"
                                                            data-type="{{ $item->type }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-8 text-muted italic">Aucun poste de trésorerie trouvé.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer / Pagination Info -->
                            <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                                <p class="text-sm text-slate-500 font-medium italic">
                                    <i class="fas fa-info-circle mr-1"></i> <span id="tableInfo">Calcul en cours...</span>
                                </p>
                                <div id="customPagination" class="flex gap-2">
                                    <!-- Pagination injected by JS -->
                                </div>
                            </div>
                        </div>

                        {{-- MOUVEMENTS (Optional Section if $compte is set) --}}
                        @isset($compte)
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold tracking-tight text-slate-800">
                                    Mouvements : <span class="text-blue-gradient">{{ $compte->nom }}</span>
                                </h2>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-2">
                                    SOLDE ACTUALISÉ : <span class="text-emerald-600 ml-1">{{ number_format($compte->solde_actuel, 2, ',', ' ') }} F CFA</span>
                                </p>
                            </div>

                            <div class="glass-card overflow-hidden mb-12">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                                <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Date</th>
                                                <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Libellé</th>
                                                <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Référence</th>
                                                <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Débit</th>
                                                <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Crédit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-50">
                                            @forelse ($mouvements as $m)
                                                <tr class="table-row">
                                                    <td class="px-8 py-6 text-sm font-semibold text-slate-600">{{ $m->date_mouvement }}</td>
                                                    <td class="px-8 py-6 text-sm font-semibold text-slate-800">{{ $m->libelle }}</td>
                                                    <td class="px-8 py-6 text-sm text-slate-500">{{ $m->reference_piece ?? '—' }}</td>
                                                    <td class="px-8 py-6 text-sm font-bold text-red-600">
                                                        {{ $m->montant_debit ? number_format($m->montant_debit, 2, ',', ' ') . ' F CFA' : '—' }}
                                                    </td>
                                                    <td class="px-8 py-6 text-sm font-bold text-emerald-600">
                                                        {{ $m->montant_credit ? number_format($m->montant_credit, 2, ',', ' ') . ' F CFA' : '—' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-8 py-10 text-center text-slate-400 italic">
                                                        Aucun mouvement enregistré
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-6 bg-slate-50 border-t border-slate-100">
                                    {{ $mouvements->links() }}
                                </div>
                            </div>
                        @endisset

                    </div>
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- --- MODALS --- -->

    <!-- Modal : Ajouter Poste -->
    <div class="modal fade" id="modalCreatePoste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('postetresorerie.store_poste') }}" method="POST" class="w-full">
                @csrf
                <div class="modal-content premium-modal-content">
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Nouveau <span class="text-blue-gradient-premium">Poste</span>
                        </h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Nom du Poste *</label>
                            <input type="text" name="name" class="input-field-premium" required placeholder="Ex: Ventes de marchandises">
                        </div>
                        <div>
                            <label class="input-label-premium">Catégorie *</label>
                            <select name="type" class="input-field-premium" required>
                                <option value="" disabled selected>-- Sélectionner --</option>
                                <option value="Flux Des Activités Operationnelles">Flux Des Activités Opérationnelles</option>
                                <option value="Flux Des Activités Investissement">Flux Des Activités d'Investissement</option>
                                <option value="Flux Des Activités de Financement">Flux Des Activités de Financement</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn-save-premium">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Modifier Poste -->
    <div class="modal fade" id="modalUpdatePoste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="updatePosteForm" method="POST" class="w-full">
                @csrf
                @method('PUT')
                <div class="modal-content premium-modal-content">
                    <!-- Header -->
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Modifier <span class="text-blue-gradient-premium">Poste</span>
                        </h1>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1" id="posteNameTitle"></p>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Nom du Poste *</label>
                            <input type="text" name="name" id="update_name" class="input-field-premium" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Catégorie *</label>
                            <select name="type" id="update_type" class="input-field-premium" required>
                                <option value="Flux Des Activités Operationnelles">Flux Des Activités Opérationnelles</option>
                                <option value="Flux Des Activités Investissement">Flux Des Activités d'Investissement</option>
                                <option value="Flux Des Activités de Financement">Flux Des Activités de Financement</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn-save-premium">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Sélection de période (Plan de Trésorerie) -->
    <div class="modal fade" id="periodSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <!-- Header -->
                <div class="text-center mb-6 position-relative">
                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                        Plan <span class="text-blue-gradient-premium">Trésorerie</span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium mt-1">Définissez la période du rapport PDF.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="input-label-premium">Date de Début</label>
                        <input type="date" id="start_date" class="input-field-premium" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="input-label-premium">Date de Fin</label>
                        <input type="date" id="end_date" class="input-field-premium" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-8">
                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" id="previewPdfButton" class="btn-save-premium shadow-emerald-100">Continuer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal : Prévisualisation PDF (XL) -->
    <div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content premium-modal-content" style="max-width: 90% !important;">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Aperçu du Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <iframe id="pdfPreviewFrame" class="w-full h-[75vh] border-0 rounded-2xl bg-slate-50" src=""></iframe>
                <div class="flex justify-end gap-3 mt-6">
                    <a href="#" id="exportCsvLink" class="px-6 py-4 bg-emerald-50 text-emerald-700 rounded-xl font-bold text-xs uppercase tracking-widest transition flex items-center gap-2 border border-emerald-100 shadow-sm" target="_blank">
                        <i class="fas fa-file-csv"></i> Exporter CSV
                    </a>
                    <a href="#" id="downloadPdfLink" class="btn-save-premium !w-auto px-8 flex items-center justify-center gap-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </a>
                    <button type="button" class="btn-cancel-premium !w-auto px-8" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')

    <!-- Plugins JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom Logic -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. Toggle Filter Logic
            const toggleBtn = document.getElementById('toggleFilterBtn');
            const filterPanel = document.getElementById('advancedFilterPanel');

            if (toggleBtn && filterPanel) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (filterPanel.classList.contains('hidden')) {
                        filterPanel.classList.remove('hidden');
                        this.classList.add('bg-blue-50', 'border-blue-200', 'text-blue-700');
                    } else {
                        filterPanel.classList.add('hidden');
                        this.classList.remove('bg-blue-50', 'border-blue-200', 'text-blue-700');
                    }
                });
            }

            // 2. DataTables logic
            const initDataTable = () => {
                if (typeof $ !== 'undefined' && $.fn.dataTable) {
                    const table = $('#posteTable').DataTable({
                        dom: 't',
                        pageLength: 5,
                        language: {
                            zeroRecords: "Aucun poste de trésorerie trouvé",
                            infoEmpty: "Aucune donnée à afficher"
                        }
                    });

                    window.posteTable = table;

                    // Pagination
                    const updatePagination = () => {
                        const info = table.page.info();
                        
                        if (info.recordsDisplay > 0) {
                            $('#tableInfo').html(`Affichage de <span class="font-bold text-slate-700">${info.start + 1}</span> à <span class="font-bold text-slate-700">${info.end}</span> sur <span class="font-bold text-slate-700">${info.recordsDisplay}</span> postes`);
                        
                            let paginationHtml = '';
                            paginationHtml += `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page === 0 ? 'opacity-50 cursor-not-allowed' : ''}" onclick="window.posteTable.page('previous').draw('page')" ${info.page === 0 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                            paginationHtml += `<button class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200">${info.page + 1}</button>`;
                            paginationHtml += `<button class="px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page >= info.pages - 1 ? 'opacity-50 cursor-not-allowed' : ''}" onclick="window.posteTable.page('next').draw('page')" ${info.page >= info.pages - 1 ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                            
                            $('#customPagination').html(paginationHtml);
                        } else {
                            $('#tableInfo').html('Aucun poste trouvé');
                            $('#customPagination').empty();
                        }
                    };

                    table.on('draw', updatePagination);
                    updatePagination();

                } else {
                    setTimeout(initDataTable, 500);
                }
            };
            
            initDataTable();

            // 3. Independent Filter Logic
            const applyFilter = () => {
                 if (window.posteTable) {
                      const intitule = document.getElementById('filterIntitule').value;
                      const categorie = document.getElementById('filterCategorie').value;
                      window.posteTable.column(0).search(intitule);
                      window.posteTable.column(1).search(categorie);
                      window.posteTable.draw();
                 }
            };

            const resetFilter = () => {
                 document.getElementById('filterIntitule').value = '';
                 document.getElementById('filterCategorie').value = '';
                 if (window.posteTable) {
                      window.posteTable.columns().search('').draw();
                 }
            };

            // Attach events
            const applyBtn = document.getElementById('applyFilterBtn');
            const resetBtn = document.getElementById('resetFilterBtn');

            if (applyBtn) applyBtn.addEventListener('click', function(e) { e.preventDefault(); applyFilter(); });
            if (resetBtn) resetBtn.addEventListener('click', function(e) { e.preventDefault(); resetFilter(); });
            
            // Auto-filter on keyup/change
            document.getElementById('filterIntitule').addEventListener('keyup', applyFilter);
            document.getElementById('filterCategorie').addEventListener('change', applyFilter);


            // Modal Update logic
            const modalUpdatePosteEl = document.getElementById('modalUpdatePoste');
            if (modalUpdatePosteEl) {
                modalUpdatePosteEl.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const name = button.getAttribute('data-name');
                    const type = button.getAttribute('data-type');
                    const form = this.querySelector('#updatePosteForm');
                    const nameInput = this.querySelector('#update_name');
                    const typeSelect = this.querySelector('#update_type');
                    const titleSpan = this.querySelector('#posteNameTitle');

                    form.setAttribute('action', `/postetresorerie/${id}`);
                    titleSpan.textContent = name;
                    nameInput.value = name;
                    typeSelect.value = type;
                });
            }

            // PDF Preview logic
            const previewPdfButton = document.getElementById('previewPdfButton');
            if (previewPdfButton) {
                previewPdfButton.addEventListener('click', function() {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;

                    if (!startDate || !endDate) {
                        alert('Veuillez sélectionner les dates.');
                        return;
                    }

                    const pdfUrl = "{{ route('generate_cash_flow_pdf') }}" + `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                    const csvUrl = "{{ route('export_cash_flow_csv') }}" + `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

                    document.getElementById('pdfPreviewFrame').src = pdfUrl;
                    document.getElementById('exportCsvLink').href = csvUrl;
                    document.getElementById('downloadPdfLink').href = pdfUrl;

                    bootstrap.Modal.getInstance(document.getElementById('periodSelectionModal')).hide();
                    new bootstrap.Modal(document.getElementById('modalPreviewPDF')).show();
                });
            }
        });
    </script>
</body>
</html>
