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
                    <form action="{{ route('superadmin.accounting.store') }}" method="POST">
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
                                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="user_id" class="form-label fw-semibold">Administrateur Référent <span class="text-danger">*</span></label>
                                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                                <option value="">Sélectionner l'admin responsable</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                        {{ $user->last_name }} {{ $user->name }} ({{ $user->company->company_name ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">L'utilisateur qui sera le propriétaire principal de cet exercice.</small>
                                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

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

                                    <div class="d-flex justify-content-end gap-3 mt-5">
                                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-secondary">
                                            <i class="fa-solid fa-times me-2"></i>Annuler
                                        </a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fa-solid fa-save me-2"></i>Créer l'exercice
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-green-50 rounded-xl border border-green-200 p-4">
                                    <h6 class="fw-bold text-green-900 mb-3">
                                        <i class="fa-solid fa-circle-info me-2"></i>Fonctionnement
                                    </h6>
                                    <p class="text-sm text-green-800 mb-3">
                                        La création d'un exercice initialise automatiquement la structure de données pour l'année fiscale concernée.
                                    </p>
                                    <ul class="text-xs text-green-700 ps-3 mb-0">
                                        <li class="mb-1">Période standard de 12 mois recommandée.</li>
                                        <li class="mb-1">L'administrateur référent aura les pleins pouvoirs de validation.</li>
                                        <li>Possibilité de synchroniser les journaux immédiatement après création.</li>
                                    </ul>
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
</body>
</html>
