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
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Section 1: Profil et Identité -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-user-circle me-2"></i>Identité de l'Utilisateur
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="last_name" class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="email_adresse" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email_adresse') is-invalid @enderror" id="email_adresse" name="email_adresse" value="{{ old('email_adresse') }}" required>
                                            @error('email_adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label for="password" class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Assignation et Rôle -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-briefcase me-2"></i>Assignation et Rôle
                                    </h5>
                                    
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="company_id" class="form-label fw-semibold">Entreprise <span class="text-danger">*</span></label>
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

                                        <div class="col-md-6">
                                            <label for="role" class="form-label fw-semibold">Rôle Plateforme <span class="text-danger">*</span></label>
                                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                                <option value="comptable" {{ old('role') == 'comptable' ? 'selected' : '' }}>Comptable</option>
                                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                            </select>
                                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="is_active" class="form-label fw-semibold">Statut Compte <span class="text-danger">*</span></label>
                                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Actif</option>
                                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Suspendu</option>
                                            </select>
                                            @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Habilitatons (Permissions) -->
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                    <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">
                                        <i class="fa-solid fa-shield-halved me-2"></i>Habilitations Spécifiques
                                    </h5>
                                    
                                    <div class="row">
                                    @foreach($permissions as $section => $sectionPermissions)
                                        <div class="col-12 mb-4">
                                            <h6 class="text-xs font-bold text-slate-600 uppercase mb-2 border-bottom pb-2">{{ $section }}</h6>
                                            <div class="row">
                                                @foreach($sectionPermissions as $key => $label)
                                                    <div class="col-md-6 mb-3">
                                                <div class="form-check form-switch p-2 border rounded bg-light bg-opacity-50 h-100 d-flex align-items-center">
                                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="habilitations[{{ $key }}]" value="1" id="hab_{{ $key }}" 
                                                        {{ is_array(old('habilitations')) && isset(old('habilitations')[$key]) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-xs fw-bold text-slate-700 w-100 cursor-pointer" for="hab_{{ $key }}">
                                                        {{ $label }}
                                                    </label>
                                                    </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-blue-50 rounded-xl border border-blue-200 p-6 sticky-top" style="top: 100px;">
                                    <h6 class="fw-bold text-blue-900 mb-3">
                                        <i class="fa-solid fa-info-circle me-2"></i>Actions
                                    </h6>
                                    <p class="text-sm text-blue-800 mb-4">
                                        L'utilisateur recevra ses accès immédiatement après la création.
                                    </p>
                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fa-solid fa-user-plus me-2"></i>Créer l'utilisateur
                                        </button>
                                        <a href="{{ route('user_management') }}" class="btn btn-outline-secondary">
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const habilitationCheckboxes = document.querySelectorAll('input[name^="habilitations["]');
        
        // Permissions par rôle (depuis la config)
        const rolePermissions = {
            'admin': @json(config('accounting_permissions.role_permissions_map.admin')),
            'comptable': @json(config('accounting_permissions.role_permissions_map.comptable'))
        };

        // Sections restreintes par rôle (pour bloquer/griser visuellement)
        const roleRestrictions = {
            'comptable': ['Gouvernance', 'Configuration', 'Super Admin'],
            'admin': ['Super Admin'], 
            'utilisateur': ['Gouvernance', 'Configuration', 'Paramétrage']
        };
        
        const roleSelect = document.getElementById('role');
        const habilitationCheckboxes = document.querySelectorAll('input[name^="habilitations["]');
        const isSubCompany = {{ isset($currentCompany) && $currentCompany->parent_company_id ? 'true' : 'false' }};
        
        function updatePermissions() {
            const selectedRole = roleSelect.value;
            
            // Permissions par défaut pour affichage (cochage initial)
            const rolePermissions = {
                'admin': @json(config('accounting_permissions.role_permissions_map.admin')),
                'comptable': @json(config('accounting_permissions.role_permissions_map.comptable'))
            };
            const allowedPermissions = rolePermissions[selectedRole] || [];

            habilitationCheckboxes.forEach(checkbox => {
                const match = checkbox.name.match(/habilitations\[(.+)\]/);
                if (match) {
                    const permissionKey = match[1];
                    const sectionTitle = checkbox.closest('.col-12.mb-4').querySelector('h6').textContent;
                    
                    let isRestricted = false;
                    let forceUnchecked = false;

                    // 1. Restriction Super Admin (Toujours)
                    if (sectionTitle.includes('Super Admin')) {
                        isRestricted = true;
                        forceUnchecked = true;
                    }

                    // 2. Restriction Fusion (Uniquement sous-entreprises)
                    if (sectionTitle.includes('Fusion & Démarrage') && !isSubCompany) {
                        isRestricted = true;
                        forceUnchecked = true;
                    }

                    // 3. Restriction Niveau (Comptable ne voit pas Gouvernance/Config Admin)
                    if (selectedRole === 'comptable') {
                        const higherSections = ['Gouvernance', 'Configuration Entreprise'];
                        for (const s of higherSections) {
                            if (sectionTitle.includes(s)) {
                                isRestricted = true;
                                forceUnchecked = true;
                                break;
                            }
                        }
                    }

                    // Application
                    checkbox.checked = forceUnchecked ? false : allowedPermissions.includes(permissionKey);
                    
                    const container = checkbox.closest('.form-check');
                    if (container) {
                        if (isRestricted) {
                            container.style.opacity = '0.4';
                            container.style.pointerEvents = 'none';
                            container.title = "Non disponible";
                        } else {
                            container.style.opacity = '1';
                            container.style.pointerEvents = 'auto';
                            container.title = "";
                        }
                    }
                }
            });
        }
        
        // Écouter les changements de rôle
        roleSelect.addEventListener('change', updatePermissions);
        
        // Initialiser au chargement
        updatePermissions();
    });
    </script>
</body>
</html>
