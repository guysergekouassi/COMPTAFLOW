<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    .bg-blue-50 { background-color: #eff6ff !important; }
    .text-blue-900 { color: #1e3a8a !important; }
    .text-blue-800 { color: #1e40af !important; }
    .text-blue-700 { color: #1d4ed8 !important; }
    .border-blue-200 { border-color: #bfdbfe !important; }
    
    .permission-card {
        transition: all 0.2s ease;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 12px;
        height: 100%;
    }
    .permission-card:hover {
        border-color: #3b82f6;
        background-color: #f8fafc;
    }
    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Nouveau Admin Sécondaire'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    <form action="{{ route('admin.secondary_admins.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-8">
                                {{-- Identité --}}
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-user-shield me-2"></i>Identité de l'Admin Sécondaire
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

                                {{-- Entreprise --}}
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
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
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Habilitations --}}
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-lock me-2"></i>Habilitations et Permissions
                                    </h5>
                                    <p class="text-muted small mb-4">Définissez les accès spécifiques pour cet administrateur sécondaire.</p>

                                    @foreach(config('accounting_permissions.permissions') as $section => $permissions)
                                        <div class="mb-4 permission-section" data-section-name="{{ $section }}">
                                            <h6 class="text-xs font-bold text-slate-600 uppercase mb-2">{{ $section }}</h6>
                                            <div class="row g-3">
                                                @foreach($permissions as $key => $label)
                                                    <div class="col-md-4 col-sm-6">
                                                        <div class="permission-card">
                                                            <div class="form-check">
                                                                <input class="form-check-input permission-checkbox" type="checkbox" name="habilitations[{{ $key }}]" value="1" id="perm_{{ $key }}" checked>
                                                                <label class="form-check-label fw-medium text-dark cursor-pointer" for="perm_{{ $key }}">
                                                                    {{ $label }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-4 sticky-top" style="top: 100px;">
                                    <h6 class="fw-bold text-blue-900 mb-3">
                                        <i class="fa-solid fa-circle-info me-2"></i>Admin Sécondaire
                                    </h6>
                                    <p class="text-sm text-blue-800 mb-3">
                                        Contrairement à l'Admin principal, vous pouvez limiter les accès de cet utilisateur.
                                    </p>
                                    <ul class="text-xs text-blue-700 ps-3 mb-4">
                                        <li class="mb-1">Accès restreint selon les habilitations choisies.</li>
                                        <li class="mb-1">Peut gérer les données de l'entreprise assignée.</li>
                                        <li>Idéal pour des rôles de supervision limités.</li>
                                    </ul>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa-solid fa-user-check me-2"></i>Créer l'Admin
                                        </button>
                                        <a href="{{ route('user_management') }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isSubCompany = {{ isset($currentCompany) && $currentCompany->parent_company_id ? 'true' : 'false' }};
            const permissionSections = document.querySelectorAll('.permission-section');

            permissionSections.forEach(section => {
                const sectionName = section.getAttribute('data-section-name');
                const checkboxes = section.querySelectorAll('.permission-checkbox');

                let isRestricted = false;
                let forceUnchecked = false;

                // 1. Restriction Super Admin
                if (sectionName.includes('Super Admin')) {
                    isRestricted = true;
                    forceUnchecked = true;
                }

                // 2. Restriction Fusion
                if (sectionName.includes('Fusion & Démarrage') && !isSubCompany) {
                    isRestricted = true;
                    forceUnchecked = true;
                }

                if (isRestricted) {
                    checkboxes.forEach(cb => {
                        if (forceUnchecked) cb.checked = false;
                        const container = cb.closest('.permission-card');
                        if (container) {
                            container.style.opacity = '0.4';
                            container.style.pointerEvents = 'none';
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
