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

                                <form action="{{ route('superadmin.accounting.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-4">
                                        <!-- Sélection de l'entreprise -->
                                        <div class="col-md-12">
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

                                        <!-- Nom du compte comptable -->
                                        <div class="col-md-12">
                                            <label for="account_name" class="form-label fw-semibold">
                                                Nom du compte comptable <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('account_name') is-invalid @enderror" 
                                                   id="account_name" 
                                                   name="account_name" 
                                                   value="{{ old('account_name') }}"
                                                   placeholder="Ex: Comptabilité Générale 2026"
                                                   required>
                                            @error('account_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Type de comptabilité -->
                                        <div class="col-md-12">
                                            <label for="account_type" class="form-label fw-semibold">
                                                Type de comptabilité <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('account_type') is-invalid @enderror" 
                                                    id="account_type" 
                                                    name="account_type"
                                                    required>
                                                <option value="">Sélectionner un type</option>
                                                <option value="SYSCOHADA" {{ old('account_type') == 'SYSCOHADA' ? 'selected' : '' }}>SYSCOHADA</option>
                                                <option value="PCG" {{ old('account_type') == 'PCG' ? 'selected' : '' }}>Plan Comptable Général (PCG)</option>
                                                <option value="IFRS" {{ old('account_type') == 'IFRS' ? 'selected' : '' }}>IFRS</option>
                                                <option value="Personnalisé" {{ old('account_type') == 'Personnalisé' ? 'selected' : '' }}>Personnalisé</option>
                                            </select>
                                            @error('account_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Exercice comptable -->
                                        <div class="col-md-6">
                                            <label for="fiscal_year_start" class="form-label fw-semibold">
                                                Début d'exercice <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('fiscal_year_start') is-invalid @enderror" 
                                                   id="fiscal_year_start" 
                                                   name="fiscal_year_start" 
                                                   value="{{ old('fiscal_year_start') }}"
                                                   required>
                                            @error('fiscal_year_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="fiscal_year_end" class="form-label fw-semibold">
                                                Fin d'exercice <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('fiscal_year_end') is-invalid @enderror" 
                                                   id="fiscal_year_end" 
                                                   name="fiscal_year_end" 
                                                   value="{{ old('fiscal_year_end') }}"
                                                   required>
                                            @error('fiscal_year_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="d-flex justify-content-end gap-3 mt-5">
                                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-save me-2"></i>Créer la comptabilité
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
                                    <li class="mb-2">Sélectionnez l'entreprise pour laquelle créer la comptabilité</li>
                                    <li class="mb-2">Le type SYSCOHADA est recommandé pour les entreprises en Afrique</li>
                                    <li class="mb-2">L'exercice comptable dure généralement 12 mois</li>
                                    <li class="mb-2">Vous pourrez créer plusieurs exercices après la création</li>
                                </ul>
                            </div>

                            <div class="bg-green-50 rounded-xl border border-green-200 p-4 mt-3">
                                <h6 class="fw-bold text-green-900 mb-3">
                                    <i class="fa-solid fa-lightbulb me-2"></i>Conseil
                                </h6>
                                <p class="text-sm text-green-800 mb-0">
                                    Assurez-vous que l'entreprise existe déjà dans le système avant de créer sa comptabilité.
                                </p>
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
