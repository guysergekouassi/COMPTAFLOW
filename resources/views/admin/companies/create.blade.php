<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Créer une Entité'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    <div class="container-xxl flex-grow-1 container-p-y p-0">
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Créer une Entité</h5>
                                <p class="text-muted small mb-0">Enregistrez une nouvelle structure juridique dans votre écosystème.</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.companies.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-8">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fa-solid fa-check-circle me-2"></i>
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                        <strong>Erreur :</strong> Veuillez corriger les erreurs ci-dessous.
                                        <button type="button" class="btn-close" data-bs-toggle="alert"></button>
                                    </div>
                                @endif
                                <!-- Section 1: Informations Générales -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-building me-2"></i>Informations de l'Entité
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="company_name" class="form-label fw-semibold">Nom de l'entité / dossier <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required placeholder="Ex: Filiale Nord, Dossier Client X">
                                            @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="activity" class="form-label fw-semibold">Secteur d'Activité <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('activity') is-invalid @enderror" id="activity" name="activity" value="{{ old('activity') }}" required placeholder="Ex: Informatique, Commerce">
                                            @error('activity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="juridique_form" class="form-label fw-semibold">Forme Juridique <span class="text-danger">*</span></label>
                                            <select class="form-select @error('juridique_form') is-invalid @enderror" id="juridique_form" name="juridique_form" required>
                                                <option value="">Sélectionner...</option>
                                                <option value="SARL" {{ old('juridique_form') == 'SARL' ? 'selected' : '' }}>SARL</option>
                                                <option value="SA" {{ old('juridique_form') == 'SA' ? 'selected' : '' }}>SA</option>
                                                <option value="SAS" {{ old('juridique_form') == 'SAS' ? 'selected' : '' }}>SAS</option>
                                                <option value="SCI" {{ old('juridique_form') == 'SCI' ? 'selected' : '' }}>SCI</option>
                                                <option value="EIRL" {{ old('juridique_form') == 'EIRL' ? 'selected' : '' }}>EIRL</option>
                                                <option value="Auto-entrepreneur" {{ old('juridique_form') == 'Auto-entrepreneur' ? 'selected' : '' }}>Auto-entrepreneur</option>
                                            </select>
                                            @error('juridique_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="social_capital" class="form-label fw-semibold">Capital Social <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control @error('social_capital') is-invalid @enderror" id="social_capital" name="social_capital" value="{{ old('social_capital', 0) }}" required>
                                            @error('social_capital') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Localisation et Contact -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-location-dot me-2"></i>Adresse et Contact
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="adresse" class="form-label fw-semibold">Adresse Siège Social <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" value="{{ old('adresse') }}" required>
                                            @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="code_postal" class="form-label fw-semibold">Code Postal <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('code_postal') is-invalid @enderror" id="code_postal" name="code_postal" value="{{ old('code_postal') }}" required>
                                            @error('code_postal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="city" class="form-label fw-semibold">Ville <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required>
                                            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="country" class="form-label fw-semibold">Pays <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', 'Côte d\'Ivoire') }}" required>
                                            @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="phone_number" class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                                            @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email_adresse" class="form-label fw-semibold">Email Professionnel <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email_adresse') is-invalid @enderror" id="email_adresse" name="email_adresse" value="{{ old('email_adresse') }}" required>
                                            @error('email_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Fiscalité -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Fiscalité
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="identification_TVA" class="form-label fw-semibold">Numéro TVA / RCCM</label>
                                            <input type="text" class="form-control @error('identification_TVA') is-invalid @enderror" id="identification_TVA" name="identification_TVA" value="{{ old('identification_TVA') }}">
                                            @error('identification_TVA') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6 sticky-top" style="top: 20px;">
                                    <h6 class="fw-bold text-blue-900 mb-3 d-flex align-items-center">
                                        <i class="fa-solid fa-info-circle me-2"></i>Actions
                                    </h6>
                                    <p class="text-xs text-blue-800 mb-4 lh-lg">
                                        En créant cette entité, elle sera automatiquement rattachée à votre cabinet/entreprise principale.
                                    </p>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                            <i class="fa-solid fa-save me-2"></i>Créer l'entité
                                        </button>
                                        <a href="{{ route('compagny_information') }}" class="btn btn-white border shadow-sm">
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
