<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    .bg-indigo-50 {
        background-color: #eef2ff !important;
    }
    .text-indigo-900 {
        color: #1e1b4b !important;
    }
    .text-indigo-800 {
        color: #3730a3 !important;
    }
    .text-indigo-700 {
        color: #4338ca !important;
    }
    .border-indigo-200 {
        border-color: #c7d2fe !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Nouveau Super Admin Secondaire'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Super Admin Secondaire</h5>
                                <p class="text-muted small mb-0">Créez un assistant super administrateur avec des droits de supervision spécifiques.</p>
                            </div>
                        </div>

                        <form action="{{ route('superadmin.secondary.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-user-gear me-2"></i>Identité du Super Admin Secondaire
                                    </h5>
                                    
                                    <div class="row g-4 text-start">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ex: Marc" required>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="Ex: Morel" required>
                                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Adresse Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email_adresse" class="form-control @error('email_adresse') is-invalid @enderror" value="{{ old('email_adresse') }}" placeholder="m.morel@comptaflow.com" required>
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
                                        <i class="fa-solid fa-list-check me-2"></i>Entreprises à Superviser
                                    </h5>

                                    <div class="row g-4 text-start">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Sélectionner les Entreprises <span class="text-danger">*</span></label>
                                            <div class="row">
                                                @foreach($companies as $company)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="supervised_companies[]" value="{{ $company->id }}" id="company_{{ $company->id }}">
                                                        <label class="form-check-label" for="company_{{ $company->id }}">
                                                            {{ $company->company_name }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @error('supervised_companies') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-shield-check me-2"></i>Permissions & Habilitations
                                    </h5>

                                    <div class="accordion accordion-flush" id="accordionPermissions">
                                        @foreach($modules as $groupName => $permissions)
                                            <div class="accordion-item bg-transparent border-bottom-0 mb-2">
                                                <h2 class="accordion-header" id="heading{{ \Illuminate\Support\Str::slug($groupName) }}">
                                                    <button class="accordion-button collapsed bg-white border rounded shadow-sm fw-bold text-dark mx-0 mt-2 w-100" type="button" 
                                                            data-bs-toggle="collapse" data-bs-target="#collapse{{ \Illuminate\Support\Str::slug($groupName) }}"
                                                            style="border-left: 4px solid #1e40af !important;">
                                                        <span class="d-flex align-items-center w-100 justify-content-between pe-3">
                                                            <span>
                                                                <i class="fa-solid fa-folder me-2 text-primary opacity-75"></i>
                                                                {{ $groupName }}
                                                            </span>
                                                            <span class="badge bg-light text-primary rounded-pill border">{{ count($permissions) }}</span>
                                                        </span>
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ \Illuminate\Support\Str::slug($groupName) }}" class="accordion-collapse collapse" 
                                                     data-bs-parent="#accordionPermissions">
                                                    <div class="accordion-body bg-slate-50 border rounded-bottom shadow-sm mx-0 mb-3 pt-3">
                                                        <div class="row g-3">
                                                            @foreach($permissions as $key => $label)
                                                                <div class="col-md-6 text-start">
                                                                    <div class="form-check form-switch p-2 bg-white rounded border d-flex align-items-center gap-3">
                                                                        <input class="form-check-input ms-0" type="checkbox" 
                                                                               name="habilitations[{{ $key }}]" value="1" id="perm_{{ $key }}" style="float: none;">
                                                                        <label class="form-check-label cursor-pointer fw-semibold text-slate-600 small mb-0" for="perm_{{ $key }}">
                                                                            {{ $label }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-indigo-50 rounded-xl border border-indigo-200 p-4 sticky-top" style="top: 100px;">
                                    <h6 class="fw-bold text-indigo-900 mb-3">
                                        <i class="fa-solid fa-shield-halved me-2"></i>Rôle & Pouvoirs
                                    </h6>
                                    <p class="text-sm text-indigo-800 mb-3">
                                        Un Super Admin Secondaire dispose des capacités suivantes :
                                    </p>
                                    <ul class="text-xs text-indigo-700 ps-3 mb-4">
                                        <li class="mb-1">Vue d'ensemble sur les entreprises assignées.</li>
                                        <li class="mb-1">Gestion des utilisateurs de ces entreprises.</li>
                                        <li class="mb-1">Audit des activités.</li>
                                        <li>Accès granulaire aux fonctionnalités Super Admin.</li>
                                    </ul>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa-solid fa-user-check me-2"></i>Créer le Super Admin
                                        </button>
                                        <a href="{{ route('superadmin.secondary.index') }}" class="btn btn-outline-secondary">
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
    </div>
</body>
</html>
