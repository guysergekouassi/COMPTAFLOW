<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }

    /* ── PAGE HEADER ── */
    .page-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .page-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='30'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }

    /* ── SECTION CARDS ── */
    .settings-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px -8px rgba(0,0,0,0.06);
        transition: all 0.25s ease;
    }
    .settings-card:hover { box-shadow: 0 8px 30px -10px rgba(30,64,175,0.1); }
    .section-title {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .section-title i { color: #2563eb; font-size: 0.85rem; }

    /* ── DISPLAY FIELDS ── */
    .info-row {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f8fafc;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
    }
    .info-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: #0f172a;
    }
    .info-value.missing { color: #cbd5e1; font-style: italic; font-weight: 400; }

    /* ── BADGE STATUS ── */
    .status-badge {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-active { background: #dcfce7; color: #16a34a; }
    .badge-blocked { background: #fee2e2; color: #dc2626; }
    .badge-missing { background: #fef9c3; color: #ca8a04; }

    /* ── LOGO AREA ── */
    .logo-upload-zone {
        border: 2px dashed #e2e8f0;
        border-radius: 14px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    .logo-upload-zone:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .logo-preview {
        max-height: 80px;
        max-width: 200px;
        object-fit: contain;
        border-radius: 8px;
    }

    /* ── FORM FIELDS ── */
    .form-field-group { display: flex; flex-direction: column; gap: 0.3rem; }
    .form-label-premium {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .req { color: #E53E3E; }
    .form-control-premium {
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        border-radius: 10px;
        padding: 0.6rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f172a;
        transition: all 0.2s ease;
        width: 100%;
        outline: none;
    }
    .form-control-premium:focus {
        border-color: #2563eb;
        background: white;
        box-shadow: 0 0 0 4px rgba(37,99,235,0.07);
    }
    .form-legend {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 0.5rem;
    }
    .form-legend .req { font-style: normal; }

    /* ── MODAL ── */
    .modal-premium .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 30px 60px -10px rgba(0,0,0,0.15);
        overflow: hidden;
    }
    .modal-premium .modal-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: white;
        border: none;
        padding: 1.25rem 1.75rem;
    }
    .modal-premium .modal-body { padding: 1.75rem; background: #fafbff; }
    .modal-premium .modal-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 1.75rem; }

    /* ── SAVE BUTTON ── */
    .btn-save-dgi {
        background: linear-gradient(135deg, #1e40af, #2563eb);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 0.7rem 2rem;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(37,99,235,0.25);
    }
    .btn-save-dgi:hover {
        background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }

    /* ── TAB PILLS ── */
    .settings-tabs .nav-link {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-radius: 10px;
        padding: 0.55rem 1rem;
        border: none;
        transition: all 0.2s ease;
    }
    .settings-tabs .nav-link.active {
        background: #2563eb;
        color: white;
        box-shadow: 0 4px 12px rgba(37,99,235,0.25);
    }
    .settings-tabs .nav-link:hover:not(.active) { background: #f1f5f9; color: #1e40af; }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- ── Alerts ── --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- ── Hero ── --}}
                    <div class="page-hero mb-5">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h1 class="fw-900 fs-4 mb-1" style="font-weight:800;">
                                    <i class="fas fa-building me-2 opacity-75"></i>
                                    Paramètres & Informations
                                </h1>
                                <p class="mb-0 opacity-75" style="font-size:0.85rem;">
                                    Configuration générale, fiscale et DGI de votre entreprise
                                </p>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                @if($company->is_blocked)
                                    <span class="status-badge badge-blocked"><i class="fas fa-lock"></i> Bloquée</span>
                                @else
                                    <span class="status-badge badge-active"><i class="fas fa-check-circle"></i> Active</span>
                                @endif
                                <button class="btn btn-sm btn-light fw-bold" data-bs-toggle="modal" data-bs-target="#editCompanyModal">
                                    <i class="fas fa-edit me-1"></i> Modifier
                                </button>
                                <a href="{{ route('admin.companies.create') }}" class="btn btn-sm btn-outline-light fw-bold">
                                    <i class="fas fa-plus-circle me-1"></i> Nouvelle entreprise
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- ── Logo + Identité ── --}}
                    <div class="row g-4 mb-4">
                        {{-- Logo --}}
                        <div class="col-lg-3 col-md-4">
                            <div class="settings-card h-100 d-flex flex-column align-items-center justify-content-center text-center">
                                <div class="section-title justify-content-center w-100">
                                    <i class="fas fa-image"></i> Logo
                                </div>
                                @if($company->logo_path)
                                    <img src="{{ asset('storage/' . $company->logo_path) }}"
                                         alt="Logo {{ $company->company_name }}"
                                         class="logo-preview mb-3">
                                @else
                                    <div class="w-20 h-20 bg-blue-50 rounded-2xl flex items-center justify-center mb-3"
                                         style="width:80px;height:80px;background:#eff6ff;border-radius:14px;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-building text-blue-400" style="font-size:2rem;color:#93c5fd;"></i>
                                    </div>
                                    <p style="font-size:0.75rem;color:#94a3b8;" class="mb-0">Aucun logo enregistré</p>
                                @endif
                                <button class="btn btn-sm btn-outline-primary mt-3 fw-bold"
                                        data-bs-toggle="modal" data-bs-target="#editCompanyModal"
                                        style="font-size:0.72rem;">
                                    <i class="fas fa-upload me-1"></i> Changer le logo
                                </button>
                            </div>
                        </div>

                        {{-- Identité --}}
                        <div class="col-lg-9 col-md-8">
                            <div class="settings-card">
                                <div class="section-title">
                                    <i class="fas fa-briefcase"></i> Identité de l'entreprise
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <span class="info-label">Nom de l'entreprise</span>
                                            <span class="info-value">{{ $company->company_name ?? '—' }}</span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Forme juridique</span>
                                            <span class="info-value {{ !$company->juridique_form ? 'missing' : '' }}">
                                                {{ $company->juridique_form ?? 'Non renseigné' }}
                                            </span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Activité</span>
                                            <span class="info-value {{ !$company->activity ? 'missing' : '' }}">
                                                {{ $company->activity ?? 'Non renseigné' }}
                                            </span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Capital Social</span>
                                            <span class="info-value">
                                                {{ number_format($company->social_capital ?? 0, 0, ',', ' ') }} FCFA
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <span class="info-label">Administrateur Principal</span>
                                            <span class="info-value">
                                                {{ $company->admin->name ?? '—' }} {{ $company->admin->last_name ?? '' }}
                                            </span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Téléphone</span>
                                            <span class="info-value {{ !$company->phone_number ? 'missing' : '' }}">
                                                {{ $company->phone_number ?? 'Non renseigné' }}
                                            </span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">E-mail</span>
                                            <span class="info-value {{ !$company->email_adresse ? 'missing' : '' }}">
                                                {{ $company->email_adresse ?? 'Non renseigné' }}
                                            </span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Statut</span>
                                            <span>
                                                @if($company->is_blocked)
                                                    <span class="status-badge badge-blocked"><i class="fas fa-lock"></i> Bloquée</span>
                                                @else
                                                    <span class="status-badge badge-active"><i class="fas fa-circle"></i> Active</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Localisation ── --}}
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="settings-card h-100">
                                <div class="section-title">
                                    <i class="fas fa-map-marker-alt"></i> Localisation
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Adresse</span>
                                    <span class="info-value {{ !$company->adresse ? 'missing' : '' }}">{{ $company->adresse ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Siège Social</span>
                                    <span class="info-value {{ !$company->siege_social ? 'missing' : '' }}">{{ $company->siege_social ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Quartier</span>
                                    <span class="info-value {{ !$company->quartier ? 'missing' : '' }}">{{ $company->quartier ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Commune</span>
                                    <span class="info-value {{ !$company->commune ? 'missing' : '' }}">{{ $company->commune ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Ville / Code Postal</span>
                                    <span class="info-value">{{ $company->city ?? '—' }} {{ $company->code_postal ? '(' . $company->code_postal . ')' : '' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Pays</span>
                                    <span class="info-value {{ !$company->country ? 'missing' : '' }}">{{ $company->country ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Infos Local ── --}}
                        <div class="col-md-6">
                            <div class="settings-card h-100">
                                <div class="section-title">
                                    <i class="fas fa-home"></i> Local Commercial
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Propriétaire du local</span>
                                    <span class="info-value {{ !$company->proprietaire_local ? 'missing' : '' }}">{{ $company->proprietaire_local ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Référence cadastrale</span>
                                    <span class="info-value {{ !$company->reference_cadastrale ? 'missing' : '' }}">{{ $company->reference_cadastrale ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Expert-comptable</span>
                                    <span class="info-value {{ !$company->expert_comptable_nom ? 'missing' : '' }}">{{ $company->expert_comptable_nom ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">NCC Expert-comptable</span>
                                    <span class="info-value {{ !$company->expert_comptable_ncc ? 'missing' : '' }}">{{ $company->expert_comptable_ncc ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Fiscal DGI ── --}}
                    <div class="settings-card mb-4">
                        <div class="section-title">
                            <i class="fas fa-landmark"></i> Informations Fiscales & DGI
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-row">
                                    <span class="info-label">IDU <small class="text-danger">DGI</small></span>
                                    <span class="info-value {{ !$company->idu ? 'missing' : '' }}">
                                        @if($company->idu)
                                            <span class="status-badge badge-active" style="font-size:0.75rem;padding:0.2rem 0.6rem;">
                                                <i class="fas fa-check-circle"></i> {{ $company->idu }}
                                            </span>
                                        @else
                                            <span class="status-badge badge-missing">
                                                <i class="fas fa-exclamation-triangle"></i> Non renseigné
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">NCC (N° Compte Contribuable)</span>
                                    <span class="info-value {{ !$company->ncc ? 'missing' : '' }}">{{ $company->ncc ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Identification TVA</span>
                                    <span class="info-value {{ !$company->identification_TVA ? 'missing' : '' }}">{{ $company->identification_TVA ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <span class="info-label">RCCM</span>
                                    <span class="info-value {{ !$company->rccm ? 'missing' : '' }}">{{ $company->rccm ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">CNPS</span>
                                    <span class="info-value {{ !$company->cnps ? 'missing' : '' }}">{{ $company->cnps ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Régime fiscal</span>
                                    <span class="info-value {{ !$company->regime ? 'missing' : '' }}">{{ $company->regime ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <span class="info-label">Rattachement DGI (Centre d'impôts)</span>
                                    <span class="info-value {{ !$company->rattachement_dgi ? 'missing' : '' }}">{{ $company->rattachement_dgi ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Compte contribuable</span>
                                    <span class="info-value {{ !$company->compte_contribuable ? 'missing' : '' }}">{{ $company->compte_contribuable ?? 'Non renseigné' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Alerte stickers (seuil)</span>
                                    <span class="info-value">
                                        <span class="status-badge" style="background:#ede9fe;color:#7c3aed;font-size:0.72rem;">
                                            <i class="fas fa-bell"></i> {{ $company->sticker_solde_alerte ?? 5 }} stickers
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Modal Modification ── --}}
                    <div class="modal fade modal-premium" id="editCompanyModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <form method="POST" action="{{ route('compagny_information.update', $company->id) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-800">
                                            <i class="fas fa-building me-2"></i>
                                            Modifier les informations de l'entreprise
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">

                                        {{-- ── Tabs ── --}}
                                        <ul class="nav settings-tabs gap-1 mb-4 flex-wrap" id="editTabs" role="tablist">
                                            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-identite">Identité</a></li>
                                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-localisation">Localisation</a></li>
                                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-fiscal">Fiscal & DGI</a></li>
                                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-local">Local & Expert</a></li>
                                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-logo">Logo & Alertes</a></li>
                                        </ul>

                                        <div class="tab-content">

                                            {{-- ── Tab Identité ── --}}
                                            <div class="tab-pane fade show active" id="tab-identite">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Nom de l'entreprise <span class="req">*</span></label>
                                                            <input type="text" name="company_name" class="form-control-premium"
                                                                   value="{{ old('company_name', $company->company_name) }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Forme Juridique</label>
                                                            <select name="juridique_form" class="form-control-premium">
                                                                <option value="">-- Sélectionner --</option>
                                                                @foreach(['SARL','SA','SAS','SASU','SNC','EI','EIRL','GIE','Association','Autre'] as $f)
                                                                    <option value="{{ $f }}" {{ old('juridique_form', $company->juridique_form) === $f ? 'selected' : '' }}>{{ $f }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Activité</label>
                                                            <input type="text" name="activity" class="form-control-premium"
                                                                   value="{{ old('activity', $company->activity) }}" placeholder="ex: Commerce de détail">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Capital Social (FCFA)</label>
                                                            <input type="number" name="social_capital" class="form-control-premium"
                                                                   value="{{ old('social_capital', $company->social_capital) }}" min="0" step="1">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Téléphone</label>
                                                            <input type="text" name="phone_number" class="form-control-premium"
                                                                   value="{{ old('phone_number', $company->phone_number) }}" placeholder="+225 07 00 00 00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Adresse e-mail</label>
                                                            <input type="email" name="email_adresse" class="form-control-premium"
                                                                   value="{{ old('email_adresse', $company->email_adresse) }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ── Tab Localisation ── --}}
                                            <div class="tab-pane fade" id="tab-localisation">
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Adresse complète</label>
                                                            <input type="text" name="adresse" class="form-control-premium"
                                                                   value="{{ old('adresse', $company->adresse) }}" placeholder="Rue, numéro...">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Siège Social</label>
                                                            <input type="text" name="siege_social" class="form-control-premium"
                                                                   value="{{ old('siege_social', $company->siege_social) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Commune</label>
                                                            <input type="text" name="commune" class="form-control-premium"
                                                                   value="{{ old('commune', $company->commune) }}" placeholder="ex: Cocody">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Quartier</label>
                                                            <input type="text" name="quartier" class="form-control-premium"
                                                                   value="{{ old('quartier', $company->quartier) }}" placeholder="ex: Zone 4">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Ville</label>
                                                            <input type="text" name="city" class="form-control-premium"
                                                                   value="{{ old('city', $company->city) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Code Postal</label>
                                                            <input type="text" name="code_postal" class="form-control-premium"
                                                                   value="{{ old('code_postal', $company->code_postal) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Pays</label>
                                                            <input type="text" name="country" class="form-control-premium"
                                                                   value="{{ old('country', $company->country) }}" placeholder="Côte d'Ivoire">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ── Tab Fiscal & DGI ── --}}
                                            <div class="tab-pane fade" id="tab-fiscal">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">IDU (Identifiant Unique DGI) <span class="req">*</span></label>
                                                            <input type="text" name="idu" class="form-control-premium"
                                                                   value="{{ old('idu', $company->idu) }}" placeholder="ex: CI-2024-XXXXXXX">
                                                            <small class="text-muted" style="font-size:0.7rem;">Obligatoire sur les factures normalisées</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">NCC (N° Compte Contribuable) <span class="req">*</span></label>
                                                            <input type="text" name="ncc" class="form-control-premium"
                                                                   value="{{ old('ncc', $company->ncc) }}" placeholder="ex: A000123456">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Identification TVA</label>
                                                            <input type="text" name="identification_TVA" class="form-control-premium"
                                                                   value="{{ old('identification_TVA', $company->identification_TVA) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">RCCM</label>
                                                            <input type="text" name="rccm" class="form-control-premium"
                                                                   value="{{ old('rccm', $company->rccm) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">CNPS</label>
                                                            <input type="text" name="cnps" class="form-control-premium"
                                                                   value="{{ old('cnps', $company->cnps) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Compte Contribuable</label>
                                                            <input type="text" name="compte_contribuable" class="form-control-premium"
                                                                   value="{{ old('compte_contribuable', $company->compte_contribuable) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Régime fiscal <span class="req">*</span></label>
                                                            <select name="regime" class="form-control-premium">
                                                                <option value="">-- Sélectionner --</option>
                                                                @foreach(['RNI - Réel Normal d\'Imposition','RSI - Réel Simplifié d\'Imposition','TEE - Taxe de l\'Entreprenant','RNE - Régime de la Microentreprise'] as $r)
                                                                    <option value="{{ $r }}" {{ old('regime', $company->regime) === $r ? 'selected' : '' }}>{{ $r }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Rattachement DGI (Centre d'impôts)</label>
                                                            <input type="text" name="rattachement_dgi" class="form-control-premium"
                                                                   value="{{ old('rattachement_dgi', $company->rattachement_dgi) }}"
                                                                   placeholder="ex: Centre des Grandes Entreprises">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ── Tab Local & Expert ── --}}
                                            <div class="tab-pane fade" id="tab-local">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Propriétaire du local</label>
                                                            <input type="text" name="proprietaire_local" class="form-control-premium"
                                                                   value="{{ old('proprietaire_local', $company->proprietaire_local) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Référence cadastrale</label>
                                                            <input type="text" name="reference_cadastrale" class="form-control-premium"
                                                                   value="{{ old('reference_cadastrale', $company->reference_cadastrale) }}"
                                                                   placeholder="ex: 12-A-045-00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Nom de l'expert-comptable</label>
                                                            <input type="text" name="expert_comptable_nom" class="form-control-premium"
                                                                   value="{{ old('expert_comptable_nom', $company->expert_comptable_nom) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">NCC Expert-comptable</label>
                                                            <input type="text" name="expert_comptable_ncc" class="form-control-premium"
                                                                   value="{{ old('expert_comptable_ncc', $company->expert_comptable_ncc) }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ── Tab Logo & Alertes ── --}}
                                            <div class="tab-pane fade" id="tab-logo">
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">Logo de l'entreprise</label>
                                                            <div class="logo-upload-zone" onclick="document.getElementById('logo_input').click()">
                                                                <i class="fas fa-cloud-upload-alt text-blue-400 mb-2" style="font-size:2rem;color:#93c5fd;display:block;"></i>
                                                                <p style="font-size:0.8rem;color:#64748b;margin:0;">
                                                                    Cliquer pour sélectionner un logo<br>
                                                                    <small style="color:#94a3b8;">JPG, PNG, SVG — max 2 Mo</small>
                                                                </p>
                                                            </div>
                                                            <input type="file" id="logo_input" name="logo"
                                                                   accept="image/*" class="d-none"
                                                                   onchange="previewLogo(this)">
                                                            <div id="logo_preview_wrapper" class="mt-2 text-center" style="display:none">
                                                                <img id="logo_preview_img" src="" alt="Aperçu" class="logo-preview">
                                                                <p style="font-size:0.7rem;color:#94a3b8;" id="logo_filename"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-field-group">
                                                            <label class="form-label-premium">
                                                                Seuil d'alerte stickers <span class="req">*</span>
                                                            </label>
                                                            <input type="number" name="sticker_solde_alerte"
                                                                   class="form-control-premium"
                                                                   value="{{ old('sticker_solde_alerte', $company->sticker_solde_alerte ?? 5) }}"
                                                                   min="0" max="9999">
                                                            <small class="text-muted" style="font-size:0.72rem;">
                                                                Une alerte s'affiche quand le solde stickers descend à ce niveau
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>{{-- /tab-content --}}

                                        {{-- Légende --}}
                                        <p class="form-legend mt-3"><span class="req">*</span> Champs obligatoires</p>

                                    </div>{{-- /modal-body --}}
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn-save-dgi">
                                            <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    @include('components.footer')

    <script>
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logo_preview_img').src = e.target.result;
                    document.getElementById('logo_filename').textContent = input.files[0].name;
                    document.getElementById('logo_preview_wrapper').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
