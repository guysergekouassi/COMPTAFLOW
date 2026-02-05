@include('components.head')

<style>
    .fusion-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .fusion-card:hover {
        border-color: #6366f1;
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .stat-badge {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.2rem;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Fusion & Configuration <span class="text-indigo-600">Parent/Enfant</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="row mb-5">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm bg-gradient-primary text-white overflow-hidden">
                                    <div class="card-body p-5 position-relative">
                                        <div class="position-absolute top-0 end-0 opacity-10">
                                            <i class="fa-solid fa-code-branch fa-10x"></i>
                                        </div>
                                        <h2 class="fw-bold text-white mb-2">Bienvenue, filiale de <span class="text-warning">{{ $parentCompany->company_name }}</span></h2>
                                        <p class="mb-4 text-white-50 fs-5" style="max-width: 700px;">
                                            Votre entreprise a été configurée comme une entité dépendante. Vous pouvez accélérer votre démarrage en injectant les configurations de votre maison mère.
                                        </p>
                                        <div class="d-flex gap-3">
                                            <div class="badge bg-white text-primary rounded-pill px-4 py-2">
                                                <i class="fa-solid fa-building me-2"></i>Parent : {{ $parentCompany->company_name }}
                                            </div>
                                            <div class="badge bg-white text-primary rounded-pill px-4 py-2">
                                                <i class="fa-solid fa-child me-2"></i>Vous : {{ $company->company_name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Messages -->
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4">
                                <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4">
                                <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.fusion.run') }}" method="POST" onsubmit="return confirm('Attention : Cette action va copier les éléments sélectionnés. Voulez-vous continuer ?');">
                            @csrf
                            <input type="hidden" name="mode" value="append">

                            <div class="row g-4 mb-5">
                                <!-- PLAN COMPTABLE -->
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm border-0 fusion-card position-relative">
                                        <div class="card-body p-4 text-center">
                                            <div class="stat-badge bg-indigo-50 text-indigo-600 mx-auto mb-3">
                                                <i class="fa-solid fa-book"></i>
                                            </div>
                                            <h5 class="fw-bold mb-1">Plan Comptable</h5>
                                            <p class="text-muted small mb-4">Comptes Généraux</p>

                                            <div class="d-flex justify-content-center gap-4 mb-4">
                                                <div class="text-center">
                                                    <span class="d-block fw-bold fs-4 text-indigo-600">{{ $stats['accounts']['parent'] }}</span>
                                                    <span class="text-xs text-uppercase text-muted fw-bold">Disponibles</span>
                                                </div>
                                                <div class="vr"></div>
                                                <div class="text-center">
                                                    <span class="d-block fw-bold fs-4 text-dark">{{ $stats['accounts']['current'] }}</span>
                                                    <span class="text-xs text-uppercase text-muted fw-bold">Actuels</span>
                                                </div>
                                            </div>

                                            <label class="btn btn-outline-indigo w-100 fw-bold border-2">
                                                <input type="checkbox" name="scope[]" value="accounts" class="form-check-input me-2" checked>
                                                Importer les Comptes
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- JOURNAUX -->
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm border-0 fusion-card position-relative">
                                        <div class="card-body p-4 text-center">
                                            <div class="stat-badge bg-emerald-50 text-emerald-600 mx-auto mb-3">
                                                <i class="fa-solid fa-swatchbook"></i>
                                            </div>
                                            <h5 class="fw-bold mb-1">Codes Journaux</h5>
                                            <p class="text-muted small mb-4">Structure des écritures</p>

                                            <div class="d-flex justify-content-center gap-4 mb-4">
                                                <div class="text-center">
                                                    <span class="d-block fw-bold fs-4 text-emerald-600">{{ $stats['journals']['parent'] }}</span>
                                                    <span class="text-xs text-uppercase text-muted fw-bold">Disponibles</span>
                                                </div>
                                                <div class="vr"></div>
                                                <div class="text-center">
                                                    <span class="d-block fw-bold fs-4 text-dark">{{ $stats['journals']['current'] }}</span>
                                                    <span class="text-xs text-uppercase text-muted fw-bold">Actuels</span>
                                                </div>
                                            </div>

                                            <label class="btn btn-outline-success w-100 fw-bold border-2">
                                                <input type="checkbox" name="scope[]" value="journals" class="form-check-input me-2" checked>
                                                Importer les Journaux
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- PLAN TIERS -->
                                <div class="col-md-4">
                                    <div class="card h-100 shadow-sm border-0 fusion-card position-relative">
                                        <div class="card-body p-4 text-center">
                                            <div class="stat-badge bg-orange-50 text-orange-600 mx-auto mb-3">
                                                <i class="fa-solid fa-users-viewfinder"></i>
                                            </div>
                                            <h5 class="fw-bold mb-1">Plan Tiers</h5>
                                            <p class="text-muted small mb-4">Clients & Fournisseurs</p>

                                            <div class="d-flex justify-content-center gap-4 mb-4">
                                                <div class="text-center">
                                                    <span class="d-block fw-bold fs-4 text-orange-600">{{ $stats['tiers']['parent'] }}</span>
                                                    <span class="text-xs text-uppercase text-muted fw-bold">Disponibles</span>
                                                </div>
                                                <div class="vr"></div>
                                                <div class="text-center">
                                                    <span class="d-block fw-bold fs-4 text-dark">{{ $stats['tiers']['current'] }}</span>
                                                    <span class="text-xs text-uppercase text-muted fw-bold">Actuels</span>
                                                </div>
                                            </div>

                                            <label class="btn btn-outline-warning w-100 fw-bold border-2">
                                                <input type="checkbox" name="scope[]" value="tiers" class="form-check-input me-2" checked>
                                                Importer les Tiers
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 col-lg-6 mx-auto">
                                <button type="submit" class="btn btn-primary btn-lg py-3 shadow-lg rounded-xl">
                                    <i class="fa-solid fa-bolt me-2"></i> Lancer la Fusion des Données
                                </button>
                                <p class="text-center text-muted small mt-2">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Mode sécurisé : Les données existantes ne seront pas écrasées (ajout pur).
                                </p>
                            </div>
                        </form>

                        <div class="row mt-5">
                            <div class="col-lg-6 mx-auto">
                                <hr class="border-danger opacity-25 mb-4">
                                <form action="{{ route('admin.fusion.reset') }}" method="POST" onsubmit="return confirm('DANGER : Vous êtes sur le point de SUPPRIMER toutes les données comptables (Comptes, Journaux, Tiers) de cette entreprise. Cette action est irréversible. Voulez-vous vraiment continuer ?');">
                                    @csrf
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-outline-danger py-2 rounded-xl fw-bold">
                                            <i class="fa-solid fa-trash-can me-2"></i> Annuler la fusion (Réinitialiser les données)
                                        </button>
                                        <p class="text-center text-danger small mt-2 fw-bold">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Attention : Supprime tout le paramétrage comptable actuel.
                                        </p>
                                    </div>
                                </form>
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
