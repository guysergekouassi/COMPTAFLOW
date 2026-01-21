@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        border-radius: 24px;
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .glass-table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .btn-premium {
        background: #059669;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    .btn-premium:hover {
        background: #047857;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
    }

    .journal-badge {
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.65rem;
        padding: 0.4rem 1rem;
        border-radius: 30px;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Structure <span class="text-emerald-600">Master</span> des Journaux'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="master-header shadow-2xl">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge border border-emerald-400 text-emerald-100 mb-4 d-inline-block rounded-pill px-4 py-1 text-xs font-black uppercase">Standardisation Flux</span>
                                    <h1 class="font-black mb-2 tracking-tighter">Modèle des Journaux</h1>
                                    <p class="opacity-70 font-medium">Configurez les codes journaux (ACH, VEN, CSH) standards du groupe. Cette structure garantit la cohérence des rapports consolidés à travers toutes vos filiales.</p>
                                </div>
                                <div class="col-lg-4 text-end d-flex flex-column gap-2 border-start border-white/10 ps-6">
                                    <button class="btn btn-premium w-100" data-bs-toggle="modal" data-bs-target="#modalCreateCodeJournal">
                                        <i class="fa-solid fa-folder-plus me-2"></i> Nouveau Code Master
                                    </button>
                                    <form action="{{ route('admin.config.load_standard_journals') }}" method="POST" class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-light w-100 border-2 font-black rounded-xl">
                                            <i class="fa-solid fa-bolt-lightning me-2"></i> Charger Journaux Standards
                                        </button>
                                    </form>
                                    <button class="btn btn-outline-light w-100 border-2 font-black rounded-xl" data-bs-toggle="modal" data-bs-target="#modalImportJournals">
                                        <i class="fa-solid fa-file-import me-2"></i> Importer Excel/CSV
                                    </button>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="fa-solid fa-swatchbook fa-10x"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Nomenclature des Journaux</h4>
                                    <p class="text-slate-400 text-sm mb-0">Définition des flux de trésorerie et d'opérations.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-emerald-50/30">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-emerald-700">Code</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">Type de Journal</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">Intitule</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-emerald-700 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($journals as $journal)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <span class="font-black text-emerald-700 fs-5">{{ $journal->code_journal }}</span>
                                            </td>
                                            <td class="py-6">
                                                <span class="journal-badge border border-emerald-200 text-emerald-700 bg-emerald-50">{{ $journal->type }}</span>
                                            </td>
                                            <td class="py-6 font-bold text-slate-800">
                                                {{ $journal->intitule }}
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-emerald border-0 rounded-circle"><i class="fa-solid fa-sliders"></i></button>
                                                    <button class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle"><i class="fa-solid fa-box-archive"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Create Journal -->
    <div class="modal fade" id="modalCreateCodeJournal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.store_journal') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-emerald-900 p-6">
                        <h5 class="modal-title text-white font-black">Nouveau Journal Master</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Code Journal</label>
                                <input type="text" name="code_journal" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-emerald-500" placeholder="Ex: ACH" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Type</label>
                                <select name="type" class="form-select border-slate-200 py-3 rounded-xl focus:border-emerald-500" required>
                                    <option value="Achats">Achats</option>
                                    <option value="Ventes">Ventes</option>
                                    <option value="Caisse">Caisse</option>
                                    <option value="Banque">Banque</option>
                                    <option value="Opérations Diverses">Opérations Diverses</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700">Intitulé</label>
                                <input type="text" name="intitule" class="form-control border-slate-200 py-3 rounded-xl focus:border-emerald-500" placeholder="Ex: JOURNAL DES ACHATS" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-emerald font-black px-8 py-3 rounded-xl shadow-lg shadow-emerald/20 text-white" style="background: #059669;">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Journals -->
    <div class="modal fade" id="modalImportJournals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.import_journals') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-emerald-900 p-6">
                        <h5 class="modal-title text-white font-black">Importer des Journaux</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="bg-emerald-50 p-6 rounded-2xl mb-6 border border-emerald-100">
                            <h6 class="font-black text-emerald-800 mb-2"><i class="fa-solid fa-circle-info me-2"></i> Format Requis</h6>
                            <p class="text-sm text-emerald-600 mb-0">Colonnes obligatoires : <strong>code_journal</strong>, <strong>intitule</strong>, <strong>type</strong>.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-black text-slate-700">Sélectionner le fichier (Excel/CSV)</label>
                            <input type="file" name="file" class="form-control border-slate-200 py-3 rounded-xl" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-emerald font-black px-8 py-3 rounded-xl shadow-lg shadow-emerald/20 text-white" style="background: #059669;">Lancer l'importation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
