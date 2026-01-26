@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Outfit', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        border-radius: 24px;
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .glass-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }

    .nav-tabs-premium .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .nav-tabs-premium .nav-link.active {
        background-color: #eff6ff;
        color: #2563eb;
    }

    .export-option-card {
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .export-option-card:hover {
        border-color: #bfdbfe;
        transform: translateY(-2px);
    }

    .export-option-card.selected {
        border-color: #2563eb;
        background-color: #eff6ff;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Module d\'<span class="text-indigo-600">Exportation</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="master-header shadow-lg">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge bg-indigo-500/20 text-indigo-100 border border-indigo-500/30 mb-4 px-3 py-1 rounded-full d-inline-block">Interopérabilité</span>
                                    <h1 class="font-black mb-2 tracking-tighter text-4xl">Centre d'Exportation</h1>
                                    <p class="opacity-80 font-medium text-lg text-slate-300">Générez des fichiers conformes pour vos auditeurs, l'administration fiscale ou la migration vers d'autres logiciel.</p>
                                </div>
                                <div class="col-lg-4 text-end">
                                    <i class="fa-solid fa-file-export fa-8x opacity-10"></i>
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs nav-tabs-premium mb-6 gap-3 border-0" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabReferentiels">
                                    <i class="fa-solid fa-database me-2"></i> Référentiels (Structure)
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMouvements">
                                    <i class="fa-solid fa-right-left me-2"></i> Mouvements (Écritures)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- ONGLET REFERENTIELS -->
                            <div class="tab-pane fade show active" id="tabReferentiels">
                                <div class="row g-6">
                                    <!-- Carte Plan Comptable -->
                                    <div class="col-md-4">
                                        <div class="glass-card p-6 h-100">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="p-3 rounded-xl bg-blue-50 text-blue-600 me-3">
                                                    <i class="fa-solid fa-book-bookmark fa-xl"></i>
                                                </div>
                                                <h5 class="font-bold mb-0">Plan Comptable</h5>
                                            </div>
                                            <p class="text-slate-500 text-sm mb-6">Export de la liste complète des comptes généraux avec leurs intitulés et types.</p>
                                            <form action="{{ route('admin.export.process') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="type" value="plan_comptable">
                                                <div class="mb-3">
                                                    <label class="form-label text-xs font-bold text-slate-400">Format</label>
                                                    <select name="format" class="form-select border-slate-200 rounded-xl">
                                                        <option value="excel">Excel (.xlsx)</option>
                                                        <option value="csv">CSV Standard</option>
                                                        <option value="sage">Fichier texte (.txt)</option>
                                                    </select>
                                                </div>
                                                <button class="btn btn-primary w-100 rounded-xl py-2 font-bold">
                                                    <i class="fa-solid fa-download me-2"></i> Exporter
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Carte Plan Tiers -->
                                    <div class="col-md-4">
                                        <div class="glass-card p-6 h-100">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600 me-3">
                                                    <i class="fa-solid fa-users-viewfinder fa-xl"></i>
                                                </div>
                                                <h5 class="font-bold mb-0">Plan Tiers</h5>
                                            </div>
                                            <p class="text-slate-500 text-sm mb-6">Export des clients et fournisseurs avec leurs comptes collectifs de rattachement.</p>
                                            <form action="{{ route('admin.export.process') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="type" value="plan_tiers">
                                                <div class="mb-3">
                                                    <label class="form-label text-xs font-bold text-slate-400">Format</label>
                                                    <select name="format" class="form-select border-slate-200 rounded-xl">
                                                        <option value="excel">Excel (.xlsx)</option>
                                                        <option value="csv">CSV Standard</option>
                                                    </select>
                                                </div>
                                                <button class="btn btn-primary w-100 rounded-xl py-2 font-bold">
                                                    <i class="fa-solid fa-download me-2"></i> Exporter
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Carte Codes Journaux -->
                                    <div class="col-md-4">
                                        <div class="glass-card p-6 h-100">
                                            <div class="d-flex align-items-center mb-4">
                                                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600 me-3">
                                                    <i class="fa-solid fa-book-open fa-xl"></i>
                                                </div>
                                                <h5 class="font-bold mb-0">Codes Journaux</h5>
                                            </div>
                                            <p class="text-slate-500 text-sm mb-6">Liste des journaux comptables (Achats, Ventes, Tréso, OD) et leurs paramètres.</p>
                                            <form action="{{ route('admin.export.process') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="type" value="journals">
                                                <div class="mb-3">
                                                    <label class="form-label text-xs font-bold text-slate-400">Format</label>
                                                    <select name="format" class="form-select border-slate-200 rounded-xl">
                                                        <option value="excel">Excel (.xlsx)</option>
                                                        <option value="csv">CSV Standard</option>
                                                    </select>
                                                </div>
                                                <button class="btn btn-primary w-100 rounded-xl py-2 font-bold">
                                                    <i class="fa-solid fa-download me-2"></i> Exporter
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ONGLET MOUVEMENTS -->
                            <div class="tab-pane fade" id="tabMouvements">
                                <div class="glass-card p-8">
                                    <form action="{{ route('admin.export.process') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="ecritures">
                                        
                                        <div class="row g-8">
                                            <div class="col-lg-8 border-end border-slate-100 pe-8">
                                                <h4 class="font-bold mb-6 text-slate-800">1. Paramètres de l'export</h4>
                                                
                                                <div class="row g-4 mb-6">
                                                    <div class="col-md-6">
                                                        <label class="form-label font-bold text-slate-600">Exercice / Période</label>
                                                        <select name="periode" class="form-select border-slate-200 py-3 rounded-xl">
                                                            <option value="all">Tout l'exercice en cours</option>
                                                            <option value="custom">Période personnalisée...</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label font-bold text-slate-600">Journal</label>
                                                        <select name="journal" class="form-select border-slate-200 py-3 rounded-xl">
                                                            <option value="all">Tous les journaux</option>
                                                            @foreach(\App\Models\CodeJournal::where('company_id', auth()->user()->company_id)->get() as $j)
                                                                <option value="{{ $j->id }}">{{ $j->code_journal }} - {{ $j->intitule }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row g-4 d-none" id="customPeriodDates"> 
                                                    <!-- A implémenter avec JS pour afficher si "custom" choisi -->
                                                </div>

                                                <div class="mb-6">
                                                    <label class="form-label font-bold text-slate-600">Filtre Statut</label>
                                                    <div class="d-flex gap-4">
                                                        <div class="form-check custom-option custom-option-basic">
                                                            <label class="form-check-label custom-option-content py-2" for="statusValid">
                                                                <input name="status" class="form-check-input" type="radio" value="validated" id="statusValid" checked>
                                                                <span class="custom-option-header">
                                                                    <span class="h6 mb-0">Validées Uniquement</span>
                                                                    <small class="text-muted">Recommandé</small>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check custom-option custom-option-basic">
                                                            <label class="form-check-label custom-option-content py-2" for="statusAll">
                                                                <input name="status" class="form-check-input" type="radio" value="all" id="statusAll">
                                                                <span class="custom-option-header">
                                                                    <span class="h6 mb-0">Brouillards inclus</span>
                                                                    <small class="text-muted">Pour révision</small>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                                    <i class="fa-solid fa-triangle-exclamation me-3 fa-lg"></i>
                                                    <div>
                                                        Le système vérifiera automatiquement l'équilibre débit/crédit avant de générer le fichier.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 ps-6">
                                                <h4 class="font-bold mb-6 text-slate-800">2. Format de sortie</h4>
                                                
                                                <div class="d-flex flex-column gap-3">
                                                    <label class="export-option-card p-4 rounded-xl d-flex align-items-center selected" onclick="selectFormat(this)">
                                                        <input type="radio" name="format" value="excel" class="d-none" checked>
                                                        <div class="bg-emerald-100 text-emerald-600 p-3 rounded-lg me-3">
                                                            <i class="fa-solid fa-file-excel fa-xl"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-bold mb-0">Excel Standard</h6>
                                                            <small class="text-slate-400">Lisible par humain (.xlsx)</small>
                                                        </div>
                                                    </label>

                                                    <label class="export-option-card p-4 rounded-xl d-flex align-items-center" onclick="selectFormat(this)">
                                                        <input type="radio" name="format" value="fec" class="d-none">
                                                        <div class="bg-blue-100 text-blue-600 p-3 rounded-lg me-3">
                                                            <i class="fa-solid fa-landmark fa-xl"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-bold mb-0">FEC (DGI)</h6>
                                                            <small class="text-slate-400">Norme fiscale A47</small>
                                                        </div>
                                                    </label>

                                                    <label class="export-option-card p-4 rounded-xl d-flex align-items-center" onclick="selectFormat(this)">
                                                        <input type="radio" name="format" value="sage" class="d-none">
                                                        <div class="bg-gray-100 text-gray-600 p-3 rounded-lg me-3">
                                                            <i class="fa-solid fa-terminal fa-xl"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-bold mb-0">Sage / Cegid</h6>
                                                            <small class="text-slate-400">Fichier texte (.txt)</small>
                                                        </div>
                                                    </label>
                                                </div>

                                                <button type="submit" class="btn btn-primary w-100 mt-6 py-4 rounded-xl font-black shadow-lg shadow-indigo-500/30 text-lg">
                                                    Générer l'export <i class="fa-solid fa-arrow-right ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectFormat(element) {
            // Visuel
            document.querySelectorAll('.export-option-card').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            // Input radio
            element.querySelector('input[type="radio"]').click();
        }
    </script>
</body>
