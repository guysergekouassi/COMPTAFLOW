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
                                    <div class="position-relative z-index-2">
                                        <h2 class="font-black mb-2">Dossier de Configuration</h2>
                                        <p class="mb-0 opacity-80 font-medium">Définissez vos standards comptables une seule fois, propagez-les partout.</p>
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
                        </div>

                        <div class="row mt-12 mb-4">
                            <div class="col-12">
                                <h5 class="font-black border-start border-4 border-primary ps-4">Paramètres Globaux de l'Architecture</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="config-card p-8 bg-white shadow-sm border-0">
                                    <form action="{{ route('admin.config.update_settings') }}" method="POST">
                                        @csrf
                                        <div class="row g-6 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label font-bold text-slate-700">Système Comptable</label>
                                                <select name="accounting_system" class="form-select border-slate-200 focus:border-primary py-3 rounded-xl shadow-none">
                                                    <option value="SYSCOHADA" {{ $mainCompany->accounting_system == 'SYSCOHADA' ? 'selected' : '' }}>SYSCOHADA (Afrique Centrale & Ouest)</option>
                                                    <option value="PCG" {{ $mainCompany->accounting_system == 'PCG' ? 'selected' : '' }}>PCG (France/International)</option>
                                                    <option value="CUSTOM" {{ $mainCompany->accounting_system == 'CUSTOM' ? 'selected' : '' }}>Personnalisé</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label font-bold text-slate-700">Nombre de chiffres (Comptes)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-slate-50 border-slate-200">
                                                        <i class="fa-solid fa-hashtag text-slate-400"></i>
                                                    </span>
                                                    <input type="number" name="account_digits" value="{{ $mainCompany->account_digits ?? 8 }}" min="4" max="12" class="form-control border-slate-200 focus:border-primary py-3 rounded-e-xl shadow-none">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-xl font-black shadow-lg shadow-primary/20">
                                                    <i class="fa-solid fa-floppy-disk me-2"></i> Mettre à jour les paramètres
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-4 mb-0">
                                            <i class="fa-solid fa-circle-info me-1"></i> 
                                            Ces paramètres influencent la génération automatique et l'importation de vos modèles.
                                        </p>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-12 mb-4">
                            <div class="col-12">
                                <h5 class="font-black border-start border-4 border-primary ps-4">Guides & Outils d'Architecture</h5>
                            </div>
                        </div>

                        <div class="row g-6">
                            <div class="col-lg-6">
                                <div class="config-card p-6 border-0 shadow-sm bg-white d-flex align-items-center gap-4">
                                    <div class="bg-slate-100 p-4 rounded-xl">
                                        <i class="fa-solid fa-shield-halved fa-2x text-slate-600"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-black mb-1">Standardisation SYSCOHADA</h6>
                                        <p class="text-xs text-slate-400 mb-0">Appliquez les normes IAS/IFRS à votre configuration globale.</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right ms-auto text-slate-300"></i>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="config-card p-6 border-0 shadow-sm bg-white d-flex align-items-center gap-4">
                                    <div class="bg-slate-100 p-4 rounded-xl">
                                        <i class="fa-solid fa-arrows-spin fa-2x text-slate-600"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-black mb-1">Audit de Cohérence</h6>
                                        <p class="text-xs text-slate-400 mb-0">Vérifiez l'alignement des filiales avec votre modèle Master.</p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right ms-auto text-slate-300"></i>
                                </div>
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
