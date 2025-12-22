<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    body {
        background-color: #f0f4f8;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #0f172a;
    }

    .premium-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        border-radius: 24px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .text-blue-gradient {
        background: linear-gradient(to right, #1e40af, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .table-row {
        transition: all 0.2s ease;
    }

    .table-row:hover {
        background-color: #f8fafc;
    }

    .filter-input {
        border: 1.5px solid #e2e8f0;
        transition: all 0.2s;
    }

    .filter-input:focus {
        border-color: #1e40af;
        outline: none;
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .btn-action-edit {
        background-color: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
    }

    .btn-action-delete {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
    }

    .input-field-premium {
        width: 100%;
        padding: 0.75rem 1rem;
        background-color: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        transition: all 0.2s;
    }

    .input-field-premium:focus {
        background-color: #ffffff;
        border-color: #1e40af;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05);
        outline: none;
    }

    .input-label-premium {
        display: block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
        margin-bottom: 0.5rem;
        margin-left: 0.25rem;
    }

    .premium-modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        padding: 2rem;
    }

    .btn-save-premium {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(to right, #1e40af, #2563eb);
        color: white;
        border: none;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        transition: all 0.3s;
    }

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
    }

    .btn-cancel-premium {
        width: 100%;
        padding: 1rem;
        background-color: #f1f5f9;
        color: #64748b;
        border: none;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        transition: all 0.2s;
    }

    .btn-cancel-premium:hover {
        background-color: #e2e8f0;
        color: #475569;
    }
    
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
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-fluid p-6 md:p-10">
                        
                        {{-- FLASH MESSAGES --}}
                        @if(session('success'))
                            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 animate__animated animate__fadeInDown">
                                <div class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                                    <i class="fas fa-check"></i>
                                </div>
                                <p class="text-emerald-700 font-bold text-sm">{{ session('success') }}</p>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center gap-3 animate__animated animate__fadeInDown">
                                <div class="w-10 h-10 bg-red-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-red-200">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <p class="text-red-700 font-bold text-sm">{{ session('error') }}</p>
                            </div>
                        @endif

                        <!-- En-tête -->
                        <div class="mb-12">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-2.5 h-2.5 bg-blue-700 rounded-full shadow-[0_0_10px_rgba(29,78,216,0.3)]"></div>
                                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900">
                                    Poste de <span class="text-blue-gradient">Trésorerie</span>
                                </h1>
                            </div>
                            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.3em] ml-6">Configuration des rubriques budgétaires et flux</p>
                        </div>

                        <!-- Actions & Filtres -->
                        <div class="mb-8">
                            <div class="flex justify-end items-center gap-3">
                                <button onclick="toggleFilters()" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl text-xs font-black text-slate-700 hover:bg-slate-50 transition shadow-sm flex items-center gap-2 uppercase tracking-widest">
                                    <i class="fas fa-filter text-blue-600"></i> Filtrer
                                </button>
                                <button class="px-8 py-4 bg-blue-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 hover:bg-blue-800 transition transform hover:-translate-y-1 flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCreatePoste">
                                    <i class="fas fa-plus-circle"></i> Ajouter
                                </button>
                                <button class="px-8 py-4 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition transform hover:-translate-y-1 flex items-center gap-2" data-bs-toggle="modal" data-bs-target="#periodSelectionModal">
                                    <i class="fas fa-file-pdf"></i> Générer un plan
                                </button>
                            </div>

                            <!-- Panneau de Filtres (Compact) -->
                            <div id="filterPanel" class="hidden mt-4 p-6 bg-white border border-slate-200 rounded-[24px] shadow-2xl max-w-3xl ml-auto">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Intitulé</label>
                                        <input type="text" id="filterIntitule" placeholder="Rechercher par intitulé..." class="filter-input w-full p-3 rounded-xl text-xs font-bold text-slate-700 bg-slate-50">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Catégorie</label>
                                        <select id="filterCategorie" class="filter-input w-full p-3 rounded-xl text-xs font-bold text-slate-700 bg-slate-50">
                                            <option value="">Toutes les catégories</option>
                                            <option value="OPÉRATIONNEL">OPÉRATIONNEL</option>
                                            <option value="FINANCEMENT">FINANCEMENT</option>
                                            <option value="INVESTISSEMENT">INVESTISSEMENT</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                                    <button onclick="resetFilters()" class="px-5 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition">Réinitialiser</button>
                                    <button onclick="applyFilters()" class="px-6 py-2.5 bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-800 transition shadow-lg shadow-blue-100 flex items-center gap-2">
                                        <i class="fas fa-search text-[9px]"></i> Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau -->
                        <div class="premium-card overflow-hidden mb-12">
                            <div class="overflow-x-auto no-scrollbar">
                                <table class="w-full text-left" id="posteTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Intitulé du Poste</th>
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Catégorie</th>
                                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($comptes as $item)
                                            <tr class="table-row group">
                                                <td class="px-8 py-6">
                                                    <p class="font-bold text-slate-800 text-sm italic">{{ $item->name }}</p>
                                                </td>
                                                <td class="px-8 py-6">
                                                    @php
                                                        $type = strtoupper($item->type ?? '');
                                                        $class = 'bg-slate-50 text-slate-600 border-slate-100';
                                                        $label = $item->type ?? 'NON DÉFINI';
                                                        
                                                        if (str_contains($type, 'OPERATIONNEL')) {
                                                            $class = 'bg-blue-50 text-blue-700 border-blue-100';
                                                            $label = 'OPÉRATIONNEL';
                                                        } elseif (str_contains($type, 'FINANCEMENT')) {
                                                            $class = 'bg-indigo-50 text-indigo-700 border-indigo-100';
                                                            $label = 'FINANCEMENT';
                                                        } elseif (str_contains($type, 'INVESTISSEMENT')) {
                                                            $class = 'bg-amber-50 text-amber-700 border-amber-100';
                                                            $label = 'INVESTISSEMENT';
                                                        }
                                                    @endphp
                                                    <span class="status-pill border {{ $class }}">{{ $label }}</span>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <div class="flex justify-center gap-2">
                                                        <button 
                                                            class="btn-action-edit w-10 h-10 flex items-center justify-center rounded-xl shadow-sm hover:scale-105 transition btn-update-poste"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalUpdatePoste"
                                                            data-id="{{ $item->id }}"
                                                            data-name="{{ $item->name }}"
                                                            data-type="{{ $item->type }}"
                                                        >
                                                            <i class="fas fa-edit text-xs"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-8 py-10 text-center text-slate-400 font-bold italic text-sm">
                                                    Aucun poste de trésorerie trouvé
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Custom Pagination Area -->
                            <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                                <p id="tableInfo" class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic"></p>
                                <div id="tablePagination" class="flex gap-1.5 items-center"></div>
                            </div>
                        </div>

                        {{-- MOUVEMENTS (SI SHOW) --}}
                        @isset($compte)
                            <div class="text-center mb-8">
                                <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">
                                    Mouvements du compte : <span class="text-blue-gradient">{{ $compte->nom }}</span>
                                </h1>
                                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-2">
                                    SOLDE ACTUALISÉ : <span class="text-emerald-600 ml-1 italic">{{ number_format($compte->solde_actuel,2,',',' ') }} F CFA</span>
                                </p>
                            </div>

                            <div class="premium-card overflow-hidden mb-12">
                                <div class="overflow-x-auto no-scrollbar">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Date</th>
                                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Libellé</th>
                                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Référence</th>
                                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Débit (Déc.)</th>
                                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Crédit (Enc.)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse ($mouvements as $m)
                                                <tr class="table-row">
                                                    <td class="px-8 py-6 text-sm font-bold text-slate-600">{{ $m->date_mouvement }}</td>
                                                    <td class="px-8 py-6 text-sm font-bold text-slate-800 italic">{{ $m->libelle }}</td>
                                                    <td class="px-8 py-6 text-sm font-bold text-slate-500">{{ $m->reference_piece ?? '—' }}</td>
                                                    <td class="px-8 py-6 text-sm font-black text-red-600 italic">
                                                        {{ $m->montant_debit ? number_format($m->montant_debit,2,',',' ') . ' F CFA' : '—' }}
                                                    </td>
                                                    <td class="px-8 py-6 text-sm font-black text-emerald-600 italic">
                                                        {{ $m->montant_credit ? number_format($m->montant_credit,2,',',' ') . ' F CFA' : '—' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-8 py-10 text-center text-slate-400 font-bold italic text-sm">
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

                    @include('components.footer')
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- MODALS -->

    <!-- Modal : Ajouter Poste -->
    <div class="modal fade" id="modalCreatePoste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content premium-modal-content">
                <form action="{{ route('postetresorerie.store_poste') }}" method="POST">
                    @csrf
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Nouveau <span class="text-blue-gradient">Poste</span>
                        </h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Nom du Poste *</label>
                            <input type="text" name="name" class="input-field-premium" required placeholder="ex: Ventes de marchandises">
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
                        <button type="submit" class="btn-save-premium shadow-blue-200">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal : Modifier Poste -->
    <div class="modal fade" id="modalUpdatePoste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content premium-modal-content">
                <form id="updatePosteForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Modifier <span class="text-gradient">Poste</span>
                        </h1>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mt-1 italic" id="posteNameTitle"></p>
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
                        <button type="submit" class="btn-save-premium shadow-blue-200">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal : Sélection de période (Plan de Trésorerie) -->
    <div class="modal fade" id="periodSelectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content premium-modal-content">
                <div class="text-center mb-6">
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Plan <span class="text-blue-gradient">Trésorerie</span></h1>
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
            <div class="modal-content premium-modal-content" style="max-width: 90%;">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight italic">Aperçu du Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <iframe id="pdfPreviewFrame" class="w-full h-[75vh] border-0 rounded-2xl bg-slate-50" src=""></iframe>
                <div class="flex justify-end gap-3 mt-6">
                    <a href="#" id="exportCsvLink" class="px-6 py-4 bg-emerald-50 text-emerald-700 rounded-xl font-bold text-xs uppercase tracking-widest transition flex items-center gap-2 border border-emerald-100 shadow-sm" target="_blank">
                        <i class="fas fa-file-csv"></i> Exporter CSV
                    </a>
                    <a href="#" id="downloadPdfLink" class="btn-save-premium !w-auto px-8" target="_blank">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </a>
                    <button type="button" class="btn-cancel-premium !w-auto px-8 font-black uppercase text-[10px] tracking-widest" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        function toggleFilters() {
            const panel = document.getElementById('filterPanel');
            panel.classList.toggle('hidden');
        }

        function resetFilters() {
            $('#filterIntitule').val('');
            $('#filterCategorie').val('');
            if (window.posteTable) {
                window.posteTable.columns().search('').draw();
            }
        }

        function applyFilters() {
            if (window.posteTable) {
                window.posteTable.draw();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // DataTables Initialization
            const initDataTable = () => {
                if (typeof $ !== 'undefined' && $.fn.dataTable) {
                    const table = $('#posteTable').DataTable({
                        dom: 't', 
                        pageLength: 5,
                        language: {
                            zeroRecords: "Aucun poste de trésorerie trouvé",
                        }
                    });

                    window.posteTable = table;

                    // Search by Intitule
                    $('#filterIntitule').on('keyup', function() {
                        table.column(0).search(this.value).draw();
                    });

                    // Search by Categorie
                    $('#filterCategorie').on('change', function() {
                        table.column(1).search(this.value).draw();
                    });

                    // Update Pagination
                    const updatePagination = () => {
                        const info = table.page.info();
                        $('#tableInfo').html(`Affichage de ${info.start + 1} à ${info.end} sur ${info.recordsTotal} postes`);
                        
                        let paginationHtml = '';
                        const totalPages = info.pages;
                        const currentPage = info.page;

                        // Prev
                        paginationHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-700 transition shadow-sm ${currentPage === 0 ? 'opacity-50 cursor-not-allowed' : ''}" onclick="window.posteTable.page('previous').draw('page')"><i class="fas fa-chevron-left text-[10px]"></i></button>`;

                        // Pages
                        for(let i=0; i<totalPages; i++) {
                            if (i === currentPage) {
                                paginationHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-700 text-white text-[11px] font-black shadow-lg shadow-blue-100">${i + 1}</button>`;
                            } else {
                                paginationHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-600 text-[11px] font-bold hover:border-blue-300 hover:text-blue-700 transition shadow-sm" onclick="window.posteTable.page(${i}).draw('page')">${i + 1}</button>`;
                            }
                        }

                        // Next
                        paginationHtml += `<button class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-700 transition shadow-sm ${currentPage === totalPages - 1 ? 'opacity-50 cursor-not-allowed' : ''}" onclick="window.posteTable.page('next').draw('page')"><i class="fas fa-chevron-right text-[10px]"></i></button>`;

                        $('#tablePagination').html(paginationHtml);
                    };

                    table.on('draw', updatePagination);
                    updatePagination();
                }
            };

            initDataTable();

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
