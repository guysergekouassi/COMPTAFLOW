<!DOCTYPE html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Créer une Entreprise'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Créer une Entreprise</h5>
                                <p class="text-muted small mb-0">Enregistrez une nouvelle structure juridique dans l'écosystème.</p>
                            </div>
                        </div>

                        <form action="{{ route('superadmin.companies.store') }}" method="POST">
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
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <!-- Section 1: Informations Générales -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-building me-2"></i>Informations Générales
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="company_name" class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required placeholder="Ex: Ma Super Entreprise">
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
                                                @foreach(['SARL','SA','SAS','SCI','EI','SASU','Association','GIE','Autre'] as $fj)
                                                <option value="{{ $fj }}" {{ old('juridique_form') == $fj ? 'selected' : '' }}>{{ $fj }}</option>
                                                @endforeach
                                            </select>
                                            @error('juridique_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="regime" class="form-label fw-semibold">Régime d'Imposition</label>
                                            <select class="form-select @error('regime') is-invalid @enderror" id="regime" name="regime">
                                                <option value="">Sélectionner...</option>
                                                @foreach(['Réel Normal','Réel Simplifié','Bénéfice Forfaitaire','Micro-Entreprise','Exonéré'] as $reg)
                                                <option value="{{ $reg }}" {{ old('regime') == $reg ? 'selected' : '' }}>{{ $reg }}</option>
                                                @endforeach
                                            </select>
                                            @error('regime') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="is_active" class="form-label fw-semibold">Statut</label>
                                            <select class="form-select" id="is_active" name="is_active">
                                                <option value="1" selected>Actif</option>
                                                <option value="0">Inactif</option>
                                            </select>
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
                                            <label for="adresse" class="form-label fw-semibold">Adresse Siège Social</label>
                                            <input type="text" class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" value="{{ old('adresse') }}" placeholder="Ex: Plateau, Abidjan">
                                            @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="city" class="form-label fw-semibold">Ville</label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', 'Abidjan') }}">
                                            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="country" class="form-label fw-semibold">Pays</label>
                                            <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', "Côte d'Ivoire") }}">
                                            @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="phone_number" class="form-label fw-semibold">Téléphone</label>
                                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="Ex: +225 07 00 00 00">
                                            @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email_adresse" class="form-label fw-semibold">Email Professionnel <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email_adresse') is-invalid @enderror" id="email_adresse" name="email_adresse" value="{{ old('email_adresse') }}" required placeholder="Ex: contact@entreprise.ci">
                                            @error('email_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Fiscalité -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Fiscalité & Identifiants Légaux
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="ncc" class="form-label fw-semibold">NCC (N° Contribuable)</label>
                                            <input type="text" class="form-control" id="ncc" name="ncc" value="{{ old('ncc') }}" placeholder="Ex: 123456...">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="rccm" class="form-label fw-semibold">RCCM</label>
                                            <input type="text" class="form-control" id="rccm" name="rccm" value="{{ old('rccm') }}" placeholder="Ex: CI-ABJ-...">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="compte_contribuable" class="form-label fw-semibold">N° Compte Contribuable CC</label>
                                            <input type="text" class="form-control" id="compte_contribuable" name="compte_contribuable" value="{{ old('compte_contribuable') }}" placeholder="Ex: CC-1234...">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="cnps" class="form-label fw-semibold">N° CNPS</label>
                                            <input type="text" class="form-control" id="cnps" name="cnps" value="{{ old('cnps') }}" placeholder="Ex: CNPS-...">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 4: Administrateur -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-user-tie me-2"></i>Administrateur du Compte
                                    </h5>
                                    <p class="text-muted small mb-4">L'email professionnel de l'entreprise servira d'identifiant de connexion.</p>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="admin_nom" class="form-label fw-semibold">Nom de l'admin <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('admin_nom') is-invalid @enderror" id="admin_nom" name="admin_nom" value="{{ old('admin_nom') }}" required placeholder="Ex: Dupont">
                                            @error('admin_nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="admin_prenom" class="form-label fw-semibold">Prénom de l'admin</label>
                                            <input type="text" class="form-control" id="admin_prenom" name="admin_prenom" value="{{ old('admin_prenom') }}" placeholder="Ex: Jean">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="admin_password" class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('admin_password') is-invalid @enderror" id="admin_password" name="admin_password" required placeholder="Min. 8 caractères">
                                            @error('admin_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="admin_password_confirmation" class="form-label fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="admin_password_confirmation" name="admin_password_confirmation" required placeholder="Répéter le mot de passe">
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 5: Liaison Selflow -->
                                <div class="bg-white rounded-xl shadow-sm border p-6 mb-6" style="border-color: #002B5C !important;">
                                    <h5 class="fw-bold mb-4 border-bottom pb-2" style="color:#002B5C;">
                                        <i class="fa-solid fa-link me-2"></i>Liaison SELFLOW (Optionnel)
                                    </h5>

                                    <label class="d-flex align-items-start gap-3 cursor-pointer p-3 rounded-lg mb-3" style="background:#f0f4ff; border:1px solid #c8deff; cursor:pointer;">
                                        <input type="checkbox" id="cb-selflow" name="creer_compte_selflow" value="1" 
                                               {{ old('creer_compte_selflow') ? 'checked' : '' }} 
                                               onchange="toggleSelflow(this)" style="width:18px;height:18px;margin-top:3px;flex-shrink:0;">
                                        <span>
                                            <span class="fw-bold" style="color:#002B5C;">Créer simultanément un compte SELFLOW (ERP)</span><br>
                                            <small class="text-muted">Le compte de gestion opérationnelle (ventes, achats, stock) sera créé dans Selflow.</small>
                                        </span>
                                    </label>

                                    <div id="selflow-fields" style="display:none; flex-direction:column; gap:12px;">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Mot de passe admin Selflow <span class="text-danger">*</span></label>
                                                <input type="password" name="selflow_password" id="selflow_password" class="form-control @error('selflow_password') is-invalid @enderror" placeholder="Min. 8 caractères">
                                                @error('selflow_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                                <input type="password" name="selflow_password_confirmation" class="form-control" placeholder="Répéter le mot de passe">
                                            </div>
                                        </div>
                                        <div class="alert alert-info py-2 mb-0">
                                            <i class="fa-solid fa-info-circle me-2"></i>
                                            Les informations de l'entreprise seront transmises automatiquement à Selflow pour créer le dossier d'exploitation.
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
                                        Vérifiez toutes les informations avant de valider. L'entreprise sera immédiatement disponible dans le réseau.
                                    </p>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                            <i class="fa-solid fa-save me-2"></i>Enregistrer l'entreprise
                                        </button>
                                        <a href="{{ route('superadmin.entities') }}" class="btn btn-white border shadow-sm">
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

<script>
function toggleSelflow(cb) {
    const fields = document.getElementById('selflow-fields');
    fields.style.display = cb.checked ? 'flex' : 'none';
    const pwdInput = document.getElementById('selflow_password');
    if (pwdInput) pwdInput.required = cb.checked;
}
document.addEventListener('DOMContentLoaded', function() {
    const cb = document.getElementById('cb-selflow');
    if (cb && cb.checked) toggleSelflow(cb);
});
</script>
</body>
</html>
