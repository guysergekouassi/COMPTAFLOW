<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Créer un Utilisateur'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    


                    <!-- Formulaire de création -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                
                                @if($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                        <strong>Erreur :</strong> Veuillez corriger les erreurs ci-dessous.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <form action="{{ route('superadmin.users.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-4">
                                        <!-- Prénom et Nom -->
                                        <div class="col-md-6">
                                            <label for="first_name" class="form-label fw-semibold">
                                                Prénom <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('first_name') is-invalid @enderror" 
                                                   id="first_name" 
                                                   name="first_name" 
                                                   value="{{ old('first_name') }}"
                                                   placeholder="Ex: Jean"
                                                   required>
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label fw-semibold">
                                                Nom <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('last_name') is-invalid @enderror" 
                                                   id="last_name" 
                                                   name="last_name" 
                                                   value="{{ old('last_name') }}"
                                                   placeholder="Ex: Dupont"
                                                   required>
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-12">
                                            <label for="email" class="form-label fw-semibold">
                                                Email <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" 
                                                   class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email') }}"
                                                   placeholder="utilisateur@entreprise.com"
                                                   required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Mot de passe -->
                                        <div class="col-md-12">
                                            <label for="password" class="form-label fw-semibold">
                                                Mot de passe <span class="text-danger">*</span>
                                            </label>
                                            <input type="password" 
                                                   class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" 
                                                   name="password" 
                                                   placeholder="Minimum 5 caractères"
                                                   required>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Le mot de passe doit contenir au moins 5 caractères</small>
                                        </div>

                                        <!-- Entreprise -->
                                        <div class="col-md-6">
                                            <label for="company_id" class="form-label fw-semibold">
                                                Entreprise <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('company_id') is-invalid @enderror" 
                                                    id="company_id" 
                                                    name="company_id"
                                                    required>
                                                <option value="">Sélectionner une entreprise</option>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('company_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Rôle -->
                                        <div class="col-md-6">
                                            <label for="role" class="form-label fw-semibold">
                                                Rôle <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('role') is-invalid @enderror" 
                                                    id="role" 
                                                    name="role"
                                                    required>
                                                <option value="">Sélectionner un rôle</option>
                                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                                <option value="comptable" {{ old('role') == 'comptable' ? 'selected' : '' }}>Comptable</option>
                                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Utilisateur</option>
                                            </select>
                                            @error('role')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="d-flex justify-content-end gap-3 mt-5">
                                        <a href="{{ route('superadmin.users') }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-2"></i>Créer l'utilisateur
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Aide contextuelle -->
                        <div class="col-lg-4">
                            <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                                <h6 class="fw-bold text-blue-900 mb-3">
                                    <i class="fa-solid fa-info-circle me-2"></i>Informations sur les Rôles
                                </h6>
                                <div class="mb-3">
                                    <strong class="text-blue-900">Administrateur</strong>
                                    <p class="text-sm text-blue-800 mb-0">Accès complet à la gestion de l'entreprise et des utilisateurs</p>
                                </div>
                                <div class="mb-3">
                                    <strong class="text-blue-900">Comptable</strong>
                                    <p class="text-sm text-blue-800 mb-0">Accès aux fonctionnalités comptables et rapports</p>
                                </div>
                                <div>
                                    <strong class="text-blue-900">Utilisateur</strong>
                                    <p class="text-sm text-blue-800 mb-0">Accès limité selon les permissions définies</p>
                                </div>
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
