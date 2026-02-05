@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Outfit', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
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

    .import-option-card {
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .import-option-card:hover {
        border-color: #bfdbfe;
        transform: translateY(-2px);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Centre d\'<span class="text-blue-600">Importation</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        {{-- Affichage des Alertes --}}
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-xl mb-6 d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-circle-check me-3 fa-lg"></i>
                                <div>
                                    <h6 class="alert-heading mb-1 font-bold text-success">Succès !</h6>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger border-0 shadow-sm rounded-xl mb-6 d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-circle-xmark me-3 fa-lg"></i>
                                <div>
                                    <h6 class="alert-heading mb-1 font-bold text-danger">Erreur</h6>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="alert alert-info border-0 shadow-sm rounded-xl mb-6 d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-circle-info me-3 fa-lg"></i>
                                <div>
                                    <h6 class="alert-heading mb-1 font-bold text-info">Information</h6>
                                    <span>{{ session('info') }}</span>
                                </div>
                            </div>
                        @endif
                        
                        <div class="master-header shadow-lg text-white">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge bg-white/20 text-white border border-white/30 mb-4 px-3 py-1 rounded-full d-inline-block">Migration & Flux</span>
                                    <h1 class="font-black mb-2 tracking-tighter text-4xl text-white">Importation de données</h1>
                                    <p class="opacity-80 font-medium text-lg text-blue-100 mb-4">Intégrez vos données comptables depuis vos anciens logiciels ou fichiers Excel en quelques clics.</p>
                                    <button type="button" class="btn btn-outline-white bg-white/10 border-white/30 hover:bg-white hover:text-blue-600 rounded-xl font-bold custom-guide-btn" data-bs-toggle="modal" data-bs-target="#modalImportInstructions">
                                        <i class="fa-solid fa-circle-info me-2"></i> Guide d'Importation
                                    </button>
                                </div>
                                <div class="col-lg-4 text-end">
                                    <i class="fa-solid fa-file-import fa-8x opacity-10"></i>
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

                        <div class="tab-content border-0 p-0 shadow-none bg-transparent">
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
                                            <p class="text-slate-500 text-sm mb-6">Importez votre nomenclature de comptes au format Excel, CSV ou XML.</p>
                                            <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="type" value="initial">
                                                <div class="mb-4">
                                                    <div class="row g-2">
                                                        <div class="col-7">
                                                            <label class="form-label text-xs font-black text-slate-400 uppercase">Fichier</label>
                                                            <input type="file" name="file" class="form-control rounded-xl border-slate-200" accept=".xlsx,.xls,.csv,.xml,.txt,.html,.htm" required>
                                                        </div>
                                                        <div class="col-5">
                                                            <label class="form-label text-xs font-black text-slate-400 uppercase">Format</label>
                                                            <select name="source" class="form-select rounded-xl border-slate-200">
                                                                <option value="excel" selected>Excel/CSV</option>
                                                                <option value="sage">Texte (.txt)</option>
                                                                <option value="xml">XML</option>
                                                                <option value="html">HTML</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100 rounded-xl py-2 font-bold shadow-lg shadow-blue-500/20">
                                                    <i class="fa-solid fa-upload me-2"></i> Charger
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
                                            <p class="text-slate-500 text-sm mb-6">Importez vos clients et fournisseurs. Les numéros seront générés par ComptaFlow.</p>
                                            <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="type" value="tiers">
                                                <div class="mb-4">
                                                    <div class="row g-2">
                                                        <div class="col-7">
                                                            <label class="form-label text-xs font-black text-slate-400 uppercase">Fichier</label>
                                                            <input type="file" name="file" class="form-control rounded-xl border-slate-200" accept=".xlsx,.xls,.csv,.xml,.txt" required>
                                                        </div>
                                                        <div class="col-5">
                                                            <label class="form-label text-xs font-black text-slate-400 uppercase">Format</label>
                                                             <select name="source" class="form-select rounded-xl border-slate-200">
                                                                <option value="excel" selected>Excel/CSV</option>
                                                                <option value="sage">Texte (.txt)</option>
                                                                <option value="xml">XML</option>
                                                                <option value="html">HTML</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-indigo w-100 rounded-xl py-2 font-bold shadow-lg shadow-indigo-500/20 text-white" style="background:#4338ca;">
                                                    <i class="fa-solid fa-upload me-2"></i> Charger
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
                                            <p class="text-slate-500 text-sm mb-6">Configurez rapidement vos journaux comptables par importation massive.</p>
                                            <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="type" value="journals">
                                                <div class="mb-4">
                                                    <div class="row g-2">
                                                        <div class="col-7">
                                                            <label class="form-label text-xs font-black text-slate-400 uppercase">Fichier</label>
                                                            <input type="file" name="file" class="form-control rounded-xl border-slate-200" accept=".xlsx,.xls,.csv,.xml,.txt,.html,.htm" required>
                                                        </div>
                                                        <div class="col-5">
                                                            <label class="form-label text-xs font-black text-slate-400 uppercase">Format</label>
                                                            <select name="source" class="form-select rounded-xl border-slate-200">
                                                                <option value="excel" selected>Excel/CSV</option>
                                                                <option value="sage">Texte (.txt)</option>
                                                                <option value="xml">XML</option>
                                                                <option value="html">HTML</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-emerald w-100 rounded-xl py-2 font-bold shadow-lg shadow-emerald-500/20 text-white" style="background:#059669;">
                                                    <i class="fa-solid fa-upload me-2"></i> Charger
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ONGLET MOUVEMENTS -->
                            <div class="tab-pane fade" id="tabMouvements">
                                <div class="glass-card p-8">
                                    <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="type" value="courant">
                                        
                                        <div class="row g-8">
                                            <div class="col-lg-8 border-end border-slate-100 pe-8">
                                                <h4 class="font-black mb-6 text-slate-800">1. Paramètres de l'import</h4>
                                                
                                                <div class="row g-4 mb-8">
                                                    <div class="col-md-6">
                                                        <label class="form-label font-bold text-slate-600">Source des données</label>
                                                        <select name="source" class="form-select border-slate-200 py-3 rounded-xl font-bold">
                                                            <option value="excel" selected>Excel/CSV</option>
                                                            <option value="sage">Texte (.txt)</option>
                                                            <option value="xml">XML</option>
                                                            <option value="html">HTML</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label font-bold text-slate-600">Exercice cible</label>
                                                        <select name="exercice" class="form-select border-slate-200 py-3 rounded-xl font-bold">
                                                            @foreach(\App\Models\ExerciceComptable::where('company_id', auth()->user()->company_id)->where('cloturer', 0)->get() as $ex)
                                                                <option value="{{ $ex->id }}">{{ $ex->intitule }} (En cours)</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="bg-blue-50 p-6 rounded-2xl mb-8">
                                                    <h6 class="font-black text-blue-900 mb-2"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Import Intelligent</h6>
                                                    <p class="text-sm text-blue-700 mb-0">Notre système analysera automatiquement les colonnes. Si des comptes ou journaux sont manquants, vous pourrez les créer à l'étape suivante.</p>
                                                </div>

                                                <div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                                    <i class="fa-solid fa-triangle-exclamation me-3 fa-lg"></i>
                                                    <div>
                                                        L'équilibre débit/crédit sera vérifié ligne par ligne lors de l'analyse.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4 ps-6">
                                                <h4 class="font-black mb-6 text-slate-800">2. Téléchargement</h4>
                                                
                                                <div class="mb-6">
                                                    <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Sélectionner le fichier</label>
                                                    <input type="file" name="file" class="form-control border-slate-200 py-4 rounded-xl" required>
                                                </div>

                                                <button type="submit" class="btn btn-primary w-100 py-4 rounded-xl font-black shadow-lg shadow-blue-500/40 text-lg">
                                                    Lancer l'analyse <i class="fa-solid fa-arrow-right ms-2"></i>
                                                </button>
                                                
                                                <div class="mt-4 text-center">
                                                    <button type="button" class="btn btn-link text-slate-400 font-bold text-[11px] text-decoration-none" onclick="window.location.reload()">
                                                        <i class="fa-solid fa-rotate me-1"></i> Réinitialiser les choix
                                                    </button>
                                                </div>
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
    @include('components.import_instructions')
</body>
</html>
