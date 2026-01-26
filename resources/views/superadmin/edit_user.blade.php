<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => $title ?? 'Modifier l\'utilisateur'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Modifier l'Utilisateur</h5>
                                <p class="text-muted small mb-0">Ajustez les informations de profil et les permissions d'accès.</p>
                            </div>
                        </div>

                        <form action="{{ route('superadmin.users.update', $user->id) }}" method="POST">
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

                                <!-- Section 1: Identité -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-user me-2"></i>Identité de l'Utilisateur
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="email_adresse" class="form-label fw-semibold">Email Professionnel <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email_adresse') is-invalid @enderror" id="email_adresse" name="email_adresse" value="{{ old('email_adresse', $user->email_adresse) }}" required>
                                            @error('email_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="password" class="form-label fw-semibold">Mot de Passe (Laisser vide pour ne pas modifier)</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Min. 5 caractères si spécifié.</small>
                                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Attribution -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-building-user me-2"></i>Assignation et Rôle
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="company_id" class="form-label fw-semibold">Entreprise <span class="text-danger">*</span></label>
                                            <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                                @foreach($companies as $company)
                                                    <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="role" class="form-label fw-semibold">Rôle Système <span class="text-danger">*</span></label>
                                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Utilisateur Simple</option>
                                                <option value="comptable" {{ old('role', $user->role) == 'comptable' ? 'selected' : '' }}>Comptable</option>
                                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrateur Client</option>
                                            </select>
                                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="pack_id" class="form-label fw-semibold">Pack d'Abonnement</label>
                                            <select class="form-select @error('pack_id') is-invalid @enderror" id="pack_id" name="pack_id">
                                                <option value="">Aucun pack</option>
                                                @foreach($packs as $pack)
                                                    <option value="{{ $pack->id }}" {{ old('pack_id', $user->pack_id) == $pack->id ? 'selected' : '' }}>
                                                        {{ $pack->nom_pack }} ({{ $pack->prix_pack }} €)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('pack_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="is_active" class="form-label fw-semibold">Statut du Compte <span class="text-danger">*</span></label>
                                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                                                <option value="1" {{ old('is_active', $user->is_active) == '1' ? 'selected' : '' }}>Actif</option>
                                                <option value="0" {{ old('is_active', $user->is_active) == '0' ? 'selected' : '' }}>Bloqué / Inactif</option>
                                            </select>
                                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Habilitations -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-shield-halved me-2"></i>Habilitations Spécifiques
                                    </h5>
                                    
                                    <div class="row g-3">
                                        @php $currentHabilitations = is_array($user->habilitations) ? $user->habilitations : (json_decode($user->habilitations, true) ?? []); @endphp
                                        @foreach($permissions as $section => $groupPermissions)
                                            <div class="col-12 mb-2 mt-3">
                                                <h6 class="text-[10px] font-black uppercase text-slate-400 tracking-widest border-bottom pb-1">{{ $section }}</h6>
                                            </div>
                                            @foreach($groupPermissions as $key => $label)
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="form-check form-switch p-2 border rounded-lg hover:bg-gray-50 transition-colors">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="habilitations[{{ $key }}]" value="1" id="hab_{{ $key }}" {{ isset($currentHabilitations[$key]) && $currentHabilitations[$key] ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-medium text-gray-700" for="hab_{{ $key }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6 sticky-top" style="top: 100px;">
                                    <h6 class="fw-bold text-blue-900 mb-3">
                                        <i class="fa-solid fa-shield-check me-2"></i>Validation
                                    </h6>
                                    <p class="text-sm text-blue-800 mb-4">
                                        La mise à jour des habilitations prend effet immédiatement lors de la prochaine connexion de l'utilisateur.
                                    </p>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa-solid fa-save me-2"></i>Mettre à jour
                                        </button>
                                        <a href="{{ route('superadmin.users') }}" class="btn btn-outline-secondary">
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
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
