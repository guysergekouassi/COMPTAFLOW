@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    :root {
        --premium-blue: #1e40af;
        --premium-blue-light: #3b82f6;
        --config-gold: #b45309;
        --glass-bg: rgba(255, 255, 255, 0.9);
    }

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #1e293b;
    }

    .config-card {
        background: var(--glass-bg);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .config-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: var(--premium-blue-light);
    }

    .icon-box {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .config-card:hover .icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-weight: 800;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .bg-gradient-config {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
    }

    .btn-config-action {
        background: white;
        color: var(--premium-blue);
        border: 1px solid var(--premium-blue);
        padding: 0.75rem 1.5rem;
        border-radius: 14px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .btn-config-action:hover {
        background: var(--premium-blue);
        color: white;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Configuration <span class="text-primary">Master</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row mb-8">
                            <div class="col-12">
                                <div class="bg-gradient-config p-8 rounded-[32px] shadow-2xl relative overflow-hidden">
                                    <div class="position-relative z-index-2 flex justify-between items-center">
                                        <div>
                                            <h2 class="font-black mb-2">Dossier de Configuration</h2>
                                            <p class="mb-0 opacity-80 font-medium">Définissez vos standards comptables une seule fois, propagez-les partout.</p>
                                        </div>
                                        @if(isset($exerciceActif) && $exerciceActif)
                                            <div class="bg-white/20 backdrop-blur-md border border-white/30 px-6 py-3 rounded-2xl text-white">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(74,222,128,0.5)]"></div>
                                                    <div>
                                                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-0">Exercice Actif</p>
                                                        <h4 class="text-lg font-black mb-0">{{ $exerciceActif->intitule }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) scale(2);">
                                        <i class="fa-solid fa-gears fa-10x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-6">
                            <!-- Plan Comptable -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-blue-100 text-blue-700">{{ $stats['accounts'] }} comptes</span>
                                    <div class="icon-box bg-blue-50 text-blue-600">
                                        <i class="fa-solid fa-book-bookmark"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Modèle de Plan</h5>
                                    <p class="text-sm text-slate-500 mb-6">Établissez la nomenclature officielle des comptes pour l'ensemble de vos filiales.</p>
                                    <a href="{{ route('admin.config.plan_comptable') }}" class="btn btn-config-action w-100">Gérer la structure</a>
                                </div>
                            </div>

                            <!-- Plan Tiers -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-indigo-100 text-indigo-700">{{ $stats['tiers'] }} fiches</span>
                                    <div class="icon-box bg-indigo-50 text-indigo-600">
                                        <i class="fa-solid fa-address-book"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Modèle de Tiers</h5>
                                    <p class="text-sm text-slate-500 mb-6">Centralisez les collecteurs auxiliaires types (Clients, Fournisseurs) par défaut.</p>
                                    <a href="{{ route('admin.config.plan_tiers') }}" class="btn btn-config-action w-100">Configurer les tiers</a>
                                </div>
                            </div>

                            <!-- Journaux -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-amber-100 text-amber-700">{{ $stats['journals'] }} codes</span>
                                    <div class="icon-box bg-amber-50 text-amber-600">
                                        <i class="fa-solid fa-swatchbook"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Journaux Types</h5>
                                    <p class="text-sm text-slate-500 mb-6">Définissez les codes journaux (ACH, VEN, BQ) standards pour vos entités.</p>
                                    <a href="{{ route('admin.config.journals') }}" class="btn btn-config-action w-100">Définir les codes</a>
                                </div>
                            </div>

                            <!-- Écritures Importées -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-emerald-100 text-emerald-700">
                                        {{ $stats['imported'] }} écritures
                                    </span>
                                    <div class="icon-box bg-emerald-50 text-emerald-600">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Écritures Importées</h5>
                                    <p class="text-sm text-slate-500 mb-6">Visualisez et intégrez les écritures provenant de logiciels externes (Sage, SAP).</p>
                                    
                                    @if($stats['imported'] > 0)
                                        <form action="{{ route('admin.config.charge_imports') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200">
                                                <i class="fa-solid fa-cloud-arrow-down me-2"></i>Charger au centre
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.config.external_import') }}" class="btn btn-config-action w-100">
                                            <i class="fa-solid fa-plus-circle me-2"></i>Importer des données
                                        </a>
                                    @endif
                                </div>
                            </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
