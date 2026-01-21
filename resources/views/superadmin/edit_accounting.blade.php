<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Modifier l\'Exercice'])

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px);">
                    <form action="{{ route('superadmin.accounting.update', $exercice->id) }}" method="POST">
                        @csrf
                        @method('PUT')

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

                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-calendar-check me-2"></i>Configuration de l'Exercice
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label for="intitule" class="form-label fw-semibold">Nom de l'exercice (ex: Exercice 2024) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('intitule') is-invalid @enderror" id="intitule" name="intitule" value="{{ old('intitule', $exercice->intitule) }}" required>
                                            @error('intitule') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="company_id" class="form-label fw-semibold">Entreprise concernée <span class="text-danger">*</span></label>
                                            <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ old('company_id', $exercice->company_id) == $company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="user_id" class="form-label fw-semibold">Administrateur référent <span class="text-danger">*</span></label>
                                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}" {{ old('user_id', $exercice->user_id) == $user->id ? 'selected' : '' }}>
                                                        {{ $user->name }} {{ $user->last_name }} ({{ $user->email_adresse }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="date_debut" class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_debut') is-invalid @enderror" id="date_debut" name="date_debut" value="{{ old('date_debut', $exercice->date_debut) }}" required>
                                            @error('date_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="date_fin" class="form-label fw-semibold">Date de fin <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('date_fin') is-invalid @enderror" id="date_fin" name="date_fin" value="{{ old('date_fin', $exercice->date_fin) }}" required>
                                            @error('date_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="cloturer" class="form-label fw-semibold">État de l'exercice <span class="text-danger">*</span></label>
                                            <select class="form-select @error('cloturer') is-invalid @enderror" id="cloturer" name="cloturer" required>
                                                <option value="0" {{ old('cloturer', $exercice->cloturer) == '0' ? 'selected' : '' }}>Ouvert</option>
                                                <option value="1" {{ old('cloturer', $exercice->cloturer) == '1' ? 'selected' : '' }}>Clôturé</option>
                                            </select>
                                            @error('cloturer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6 sticky-top" style="top: 100px;">
                                    <h6 class="fw-bold text-blue-900 mb-3">
                                        <i class="fa-solid fa-info-circle me-2"></i>Note
                                    </h6>
                                    <p class="text-sm text-blue-800 mb-4">
                                        La clôture d'un exercice est une opération importante qui peut limiter certaines modifications comptables.
                                    </p>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa-solid fa-save me-2"></i>Mettre à jour
                                        </button>
                                        <a href="{{ route('superadmin.accounting.index') }}" class="btn btn-outline-secondary">
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
</body>
</html>
