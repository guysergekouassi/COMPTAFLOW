<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Créer une Comptabilité'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    <div class="container-xxl flex-grow-1 container-p-y p-0">
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Créer une Comptabilité</h5>
                                <p class="text-muted small mb-0">Initialisez un nouvel exercice comptable pour votre entité.</p>
                            </div>
                        </div>

                        <form action="{{ route('compta.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-calendar-check me-2"></i>Configuration de l'Exercice
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="company_id" class="form-label fw-semibold">Entreprise Client <span class="text-danger">*</span></label>
                                            <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                                <option value="">Sélectionner une entreprise</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Sélectionnez l'entreprise pour laquelle vous créer la comptabilité.</small>
                                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <!-- Note: user_id est automatiquement défini sur l'admin connecté -->

                                        <div class="col-md-12">
                                            <label for="intitule" class="form-label fw-semibold">Intitulé de l'Exercice <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('intitule') is-invalid @enderror" id="intitule" name="intitule" value="{{ old('intitule') }}" required placeholder="Ex: Exercice Comptable 2026">
                                            @error('intitule') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="date_debut" class="form-label fw-semibold">Date d'Ouverture <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_debut') is-invalid @enderror" id="date_debut" name="date_debut" value="{{ old('date_debut') }}" required>
                                            @error('date_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="date_fin" class="form-label fw-semibold">Date de Clôture <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_fin') is-invalid @enderror" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" required>
                                            @error('date_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-3 mt-5 d-none">
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fa-solid fa-save me-2"></i>Créer l'exercice
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6 sticky-top" style="top: 20px;">
                                    <h6 class="fw-bold text-blue-900 mb-3 d-flex align-items-center">
                                        <i class="fa-solid fa-info-circle me-2"></i>Actions
                                    </h6>
                                    <p class="text-xs text-blue-800 mb-4 lh-lg">
                                        La création d'un exercice initialise automatiquement la structure de données pour l'année fiscale concernée.
                                    </p>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                            <i class="fa-solid fa-save me-2"></i>Créer l'exercice
                                        </button>
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-white border shadow-sm">
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
