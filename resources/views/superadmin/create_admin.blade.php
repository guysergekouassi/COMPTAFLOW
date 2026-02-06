<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    /* Styles spécifiques pour le panneau d'information */
    .bg-blue-50 {
        background-color: #eff6ff !important;
    }
    .text-blue-900 {
        color: #1e3a8a !important;
    }
    .text-blue-800 {
        color: #1e40af !important;
    }
    .text-blue-700 {
        color: #1d4ed8 !important;
    }
    .border-blue-200 {
        border-color: #bfdbfe !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => $title ?? 'Nouveau Administrateur'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Nouveau Administrateur</h5>
                                <p class="text-muted small mb-0">Créez un profil administrateur pour gérer une entité spécifique.</p>
                            </div>
                        </div>

                        <form action="{{ route('superadmin.admins.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-user-shield me-2"></i>Identité de l'Administrateur
                                    </h5>
                                    
                                    <div class="row g-4 text-start">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ex: Jean" required>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Ex: Dupont" required>
                                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Adresse Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email_adresse" class="form-control @error('email_adresse') is-invalid @enderror" value="{{ old('email_adresse') }}" placeholder="jean.dupont@entreprise.com" required>
                                            @error('email_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Mot de passe provisoire <span class="text-danger">*</span></label>
                                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-building me-2"></i>Assignation Entreprise
                                    </h5>

                                    <div class="row g-4 text-start">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Sélectionner l'Entreprise <span class="text-danger">*</span></label>
                                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                                <option value="" disabled selected>Choisir une entreprise...</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                        @if($company->parent) (Filiale de : {{ $company->parent->company_name }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6 sticky-top" style="top: 20px;">
                                    <h6 class="fw-bold text-blue-900 mb-3 d-flex align-items-center">
                                        <i class="fa-solid fa-info-circle me-2"></i>Actions
                                    </h6>
                                    <p class="text-xs text-blue-800 mb-3 lh-lg">
                                        Créer un administrateur configure automatiquement les accès suivants :
                                    </p>
                                    <ul class="text-xs text-blue-700 ps-3 mb-4">
                                        <li class="mb-1">Accès complet à la comptabilité de l'entreprise.</li>
                                        <li class="mb-1">Gestion des clients et fournisseurs.</li>
                                        <li class="mb-1">Toutes les habilitations activées par défaut.</li>
                                        <li>Le rôle sera défini sur <strong>Administrateur</strong>.</li>
                                    </ul>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                            <i class="fa-solid fa-user-check me-2"></i>Finaliser la création
                                        </button>
                                        <a href="{{ route('superadmin.users') }}" class="btn btn-white border shadow-sm">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>

                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
