<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Créer une Entreprise'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    


                    <!-- Formulaire de création -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('superadmin.companies.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-4">
                                        <!-- Nom de l'entreprise -->
                                        <div class="col-md-12">
                                            <label for="company_name" class="form-label fw-semibold">
                                                Nom de l'entreprise <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('company_name') is-invalid @enderror" 
                                                   id="company_name" 
                                                   name="company_name" 
                                                   value="{{ old('company_name') }}"
                                                   placeholder="Ex: SARL ComptaFlow CI"
                                                   required>
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Adresse -->
                                        <div class="col-md-12">
                                            <label for="address" class="form-label fw-semibold">Adresse</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                                      id="address" 
                                                      name="address" 
                                                      rows="3"
                                                      placeholder="Adresse complète de l'entreprise">{{ old('address') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Téléphone et Email -->
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label fw-semibold">Téléphone</label>
                                            <input type="text" 
                                                   class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" 
                                                   name="phone" 
                                                   value="{{ old('phone') }}"
                                                   placeholder="+225 XX XX XX XX XX">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email" class="form-label fw-semibold">Email</label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email') }}"
                                                   placeholder="contact@entreprise.com">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Secteur d'activité -->
                                        <div class="col-md-6">
                                            <label for="sector" class="form-label fw-semibold">Secteur d'activité</label>
                                            <select class="form-select @error('sector') is-invalid @enderror" 
                                                    id="sector" 
                                                    name="sector">
                                                <option value="">Sélectionner un secteur</option>
                                                <option value="Commerce" {{ old('sector') == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                                                <option value="Services" {{ old('sector') == 'Services' ? 'selected' : '' }}>Services</option>
                                                <option value="Industrie" {{ old('sector') == 'Industrie' ? 'selected' : '' }}>Industrie</option>
                                                <option value="BTP" {{ old('sector') == 'BTP' ? 'selected' : '' }}>BTP</option>
                                                <option value="Agriculture" {{ old('sector') == 'Agriculture' ? 'selected' : '' }}>Agriculture</option>
                                                <option value="Technologie" {{ old('sector') == 'Technologie' ? 'selected' : '' }}>Technologie</option>
                                                <option value="Santé" {{ old('sector') == 'Santé' ? 'selected' : '' }}>Santé</option>
                                                <option value="Éducation" {{ old('sector') == 'Éducation' ? 'selected' : '' }}>Éducation</option>
                                                <option value="Autre" {{ old('sector') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                            </select>
                                            @error('sector')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Statut -->
                                        <div class="col-md-6">
                                            <label for="status" class="form-label fw-semibold">
                                                Statut <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('status') is-invalid @enderror" 
                                                    id="status" 
                                                    name="status"
                                                    required>
                                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="d-flex justify-content-end gap-3 mt-5">
                                        <a href="{{ route('superadmin.entities') }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-2"></i>Créer l'entreprise
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Aide contextuelle -->
                        <div class="col-lg-4">
                            <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                                <h6 class="fw-bold text-blue-900 mb-3">
                                    <i class="fa-solid fa-info-circle me-2"></i>Informations
                                </h6>
                                <ul class="text-sm text-blue-800 mb-0 ps-3">
                                    <li class="mb-2">Le nom de l'entreprise est obligatoire</li>
                                    <li class="mb-2">Les autres champs sont optionnels mais recommandés</li>
                                    <li class="mb-2">Une entreprise inactive ne pourra pas se connecter</li>
                                    <li class="mb-2">Vous pourrez modifier ces informations ultérieurement</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
