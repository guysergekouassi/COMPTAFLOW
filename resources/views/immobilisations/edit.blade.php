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
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .form-section-title {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #6366f1;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-section-title::after {
        content: "";
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, #e2e8f0 0%, transparent 100%);
    }
    .input-premium {
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease !important;
    }
    .input-premium:focus {
        background: #ffffff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }
    .btn-premium {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')

        <div class="layout-page">
            @include('components.header', ['page_title' => 'Modifier <span class="text-gradient">Immobilisation</span>'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-check-circle fs-4 me-2"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error-circle fs-4 me-2"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-x-circle fs-4 me-2"></i>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <div class="glass-card p-8">
                                <div class="d-flex justify-content-between align-items-center mb-10">
                                    <div>
                                        <h4 class="fw-bold text-premium-gradient mb-1">Mise à jour : {{ $immobilisation->code }}</h4>
                                        <p class="text-muted small mb-0">Seules les informations signalétiques sont modifiables pour garantir l'intégrité du calcul.</p>
                                    </div>
                                    <div class="avatar bg-warning bg-opacity-10 text-warning p-3 rounded-circle" style="width: 60px; height: 60px;">
                                        <i class="bx bx-edit fs-2"></i>
                                    </div>
                                </div>

                                <form action="{{ route('immobilisations.update', $immobilisation->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <!-- Identification -->
                                    <div class="form-section-title">1. Informations de base</div>
                                    <div class="row g-6 mb-8">
                                        <div class="col-md-6">
                                            <label class="form-label text-xs fw-extrabold text-slate-500 uppercase">Nom de l'immobilisation *</label>
                                            <input type="text" class="form-control input-premium" name="libelle" required value="{{ old('libelle', $immobilisation->libelle) }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-xs fw-extrabold text-slate-500 uppercase">Fournisseur</label>
                                            <input type="text" class="form-control input-premium" name="fournisseur" value="{{ old('fournisseur', $immobilisation->fournisseur) }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-xs fw-extrabold text-slate-500 uppercase">N° Facture</label>
                                            <input type="text" class="form-control input-premium" name="numero_facture" value="{{ old('numero_facture', $immobilisation->numero_facture) }}" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-xs fw-extrabold text-slate-500 uppercase">Commentaire</label>
                                            <textarea name="description" class="form-control input-premium" rows="2">{{ old('description', $immobilisation->description) }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Read-only parameters -->
                                    <div class="form-section-title">2. Paramètres d'Amortissement (Lecture Seule)</div>
                                    <div class="row g-6 mb-10">
                                        <div class="col-md-4">
                                            <div class="p-4 rounded-4 bg-slate-50 border border-slate-100">
                                                <p class="text-xs fw-bold text-slate-400 uppercase mb-1">Date Mise en Service</p>
                                                <p class="mb-0 fw-bold text-slate-700">{{ $immobilisation->date_mise_en_service->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-4 rounded-4 bg-slate-50 border border-slate-100">
                                                <p class="text-xs fw-bold text-slate-400 uppercase mb-1">Valeur d'Acquisition</p>
                                                <p class="mb-0 fw-bold text-slate-700">{{ number_format($immobilisation->valeur_acquisition, 0, ',', ' ') }} FCFA</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-4 rounded-4 bg-slate-50 border border-slate-100">
                                                <p class="text-xs fw-bold text-slate-400 uppercase mb-1">Durée & Méthode</p>
                                                <p class="mb-0 fw-bold text-slate-700">{{ $immobilisation->duree_amortissement }} ans / {{ ucfirst($immobilisation->methode_amortissement) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="pt-6 border-top d-flex gap-3">
                                        <button type="submit" class="btn btn-premium px-10">
                                            <i class="bx bx-save me-1"></i> Enregistrer les modifications
                                        </button>
                                        <a href="{{ route('immobilisations.show', $immobilisation->id) }}" class="btn btn-light bg-white border rounded-pill px-8">
                                            Annuler
                                        </a>
                                    </div>
                                </form>
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
