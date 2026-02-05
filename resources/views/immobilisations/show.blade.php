<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f8fafc;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 16px;
        box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .profile-sidebar {
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
        border-right: 1px solid #e2e8f0;
    }
    .info-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #94a3b8;
        letter-spacing: 0.05em;
        margin-bottom: 2px;
    }
    .info-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    .badge-premium {
        padding: 8px 16px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
    }
    .kpi-mini-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #f1f5f9;
        transition: transform 0.3s ease;
    }
    .kpi-mini-card:hover {
        transform: translateY(-3px);
    }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')

        <div class="layout-page">
            @include('components.header', ['page_title' => 'Fiche <span class="text-gradient">Immobilisation</span>'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    
                    <div class="row g-6">
                        <!-- Sidebar: Identity Card -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="glass-card mb-4 overflow-hidden">
                                <div class="p-6 text-center profile-sidebar">
                                    <div class="avatar avatar-xl mx-auto mb-4" style="width: 80px; height: 80px;">
                                        <span class="avatar-initial rounded-circle bg-primary bg-opacity-10 text-primary fs-2">
                                            <i class="bx bx-building"></i>
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-premium-gradient mb-1">{{ $immobilisation->libelle }}</h5>
                                    <span class="badge badge-premium bg-primary bg-opacity-10 text-primary">{{ ucfirst($immobilisation->categorie) }}</span>
                                    
                                    <div class="d-flex justify-content-center gap-2 mt-4">
                                        <a href="{{ route('immobilisations.edit', $immobilisation->id) }}" class="btn btn-primary bg-slate-800 border-0 rounded-pill px-4 py-2" style="font-size: 0.8rem;">
                                            <i class="bx bx-edit-alt me-1"></i> Modifier
                                        </a>
                                        <a href="{{ route('immobilisations.index') }}" class="btn btn-light bg-white border rounded-pill px-4 py-2" style="font-size: 0.8rem;">
                                            <i class="bx bx-arrow-back me-1"></i> Retour
                                        </a>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <h6 class="text-xs fw-extrabold text-slate-400 uppercase tracking-tighter mb-4 border-bottom pb-2">Informations Générales</h6>
                                    <div class="row g-4">
                                        <div class="col-6">
                                            <p class="info-label">Référence</p>
                                            <p class="info-value mb-0">{{ $immobilisation->code }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Statut</p>
                                            <p class="info-value mb-0">
                                                @if($immobilisation->statut == 'en_cours')
                                                    <span class="text-success"><i class="bx bxs-circle me-1 fs-xs"></i>Actif</span>
                                                @elseif($immobilisation->statut == 'totalement_amorti')
                                                    <span class="text-secondary"><i class="bx bxs-circle me-1 fs-xs"></i>Amorti</span>
                                                @else
                                                    <span class="text-danger"><i class="bx bxs-circle me-1 fs-xs"></i>Cédé</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Date Acquisition</p>
                                            <p class="info-value mb-0 text-xs">{{ $immobilisation->date_acquisition->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Mise en service</p>
                                            <p class="info-value mb-0 text-xs">{{ $immobilisation->date_mise_en_service->format('d/m/Y') }}</p>
                                        </div>
                                        @if($immobilisation->statut == 'cede')
                                        <div class="col-12 mt-2">
                                            <div class="p-3 rounded bg-danger bg-opacity-5 border border-danger border-opacity-10">
                                                <p class="info-label text-danger">Sortie d'actif</p>
                                                <p class="info-value mb-0 text-danger text-xs">Cédé le {{ $immobilisation->date_cession->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <h6 class="text-xs fw-extrabold text-slate-400 uppercase tracking-tighter mt-8 mb-4 border-bottom pb-2">Paramètres Comptables</h6>
                                    <div class="d-flex flex-column gap-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-xs font-semibold text-slate-500">Compte Immo.</span>
                                            <span class="badge bg-slate-50 text-slate-700 border border-slate-200">{{ $immobilisation->compteImmobilisation->numero_de_compte }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-xs font-semibold text-slate-500">Compte Amort.</span>
                                            <span class="badge bg-slate-50 text-slate-700 border border-slate-200">{{ $immobilisation->compteAmortissement->numero_de_compte }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-xs font-semibold text-slate-500">Compte Dotation</span>
                                            <span class="badge bg-slate-50 text-slate-700 border border-slate-200">{{ $immobilisation->compteDotation->numero_de_compte }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content: Schedule & Financials -->
                        <div class="col-xl-8 col-lg-7">
                            
                            <!-- Financial Highlights -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="kpi-mini-card glass-card d-flex align-items-center p-4">
                                        <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="bx bx-wallet"></i>
                                        </div>
                                        <div>
                                            <p class="info-label mb-0">Valeur Nette</p>
                                            <h5 class="fw-black text-premium-gradient mb-0">{{ number_format($immobilisation->getValeurNetteComptable(), 0, ',', ' ') }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="kpi-mini-card glass-card d-flex align-items-center p-4">
                                        <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning me-3" style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="bx bx-trending-down"></i>
                                        </div>
                                        <div>
                                            <p class="info-label mb-0">Amort. Cumulé</p>
                                            <h5 class="fw-black text-warning mb-0">
                                                {{ number_format($immobilisation->valeur_acquisition - $immobilisation->getValeurNetteComptable(), 0, ',', ' ') }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule Table Card -->
                            <div class="glass-card shadow-none border">
                                <div class="p-4 border-bottom d-flex align-items-center justify-content-between bg-white rounded-top-4">
                                    <div>
                                        <h5 class="fw-bold text-premium-gradient mb-1">Tableau d'Amortissement</h5>
                                        <p class="text-xs text-muted mb-0">Méthode : <span class="badge bg-light text-dark">{{ ucfirst($immobilisation->methode_amortissement) }}</span> | Durée : <span class="badge bg-light text-dark">{{ $immobilisation->duree_amortissement }} ans</span></p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('immobilisations.export_tableau', ['id' => $immobilisation->id, 'format' => 'pdf']) }}" class="btn btn-sm btn-outline-danger border-0">
                                            <i class="bx bxs-file-pdf fs-4"></i>
                                        </a>
                                        <a href="{{ route('immobilisations.export_tableau', ['id' => $immobilisation->id, 'format' => 'excel']) }}" class="btn btn-sm btn-outline-success border-0">
                                            <i class="bx bxs-file-excel fs-4"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="p-0 table-responsive">
                                    @include('immobilisations.partials.tableau_amortissement')
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            @include('components.footer')
        </div>
    </div>
</div>
</body>
</html>
