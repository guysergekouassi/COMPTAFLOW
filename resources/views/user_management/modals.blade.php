
<!-- Modal Creation -->
<div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content premium-modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h4 class="modal-title font-black text-slate-800" id="modalCenterTitle">Nouveau Collaborateur</h4>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-0">Créer une fiche utilisateur sécurisée</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-8">
                <form id="createUserForm" method="POST" action="{{ route('users.store') }}" novalidate>
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="name" class="input-label-premium text-slate-400">Nom Complet <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="input-field-premium" placeholder="Ex: Jean" required />
                            <div class="invalid-feedback" id="errorFirstName"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="input-label-premium">Prénom <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name" class="input-field-premium" placeholder="Ex: Dupont" required />
                            <div class="invalid-feedback" id="errorLastName"></div>
                        </div>
                        <div class="col-12">
                            <label for="email_adresse" class="input-label-premium">Adresse Email Professionnelle <span class="text-danger">*</span></label>
                            <input type="email" id="email_adresse" name="email_adresse" class="input-field-premium" placeholder="exemple@entreprise.com" required />
                            <div class="invalid-feedback" id="errorEmail"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="input-label-premium">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="input-field-premium" placeholder="••••••••" required />
                            <div class="text-[10px] text-slate-400 mt-1 font-bold">8 caractères, majuscule, chiffre</div>
                            <div class="invalid-feedback" id="errorPassword"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmPassword" class="input-label-premium">Confirmation <span class="text-danger">*</span></label>
                            <input type="password" id="confirmPassword" class="input-field-premium" placeholder="••••••••" required />
                            <div class="invalid-feedback" id="errorConfirmPassword"></div>
                        </div>

                        <div class="col-12 mt-4" id="newCompanyNameField" style="display: none;">
                            <label for="new_company_name" class="input-label-premium">Nom de la nouvelle structure</label>
                            <input type="text" id="new_company_name" name="new_company_name" class="input-field-premium" placeholder="Ex: Ma Comptabilité SARL" />
                        </div>
                        <div class="col-md-6">
                            <label for="company_id" class="input-label-premium">Entité de rattachement</label>
                            <select name="company_id" id="company_id" class="input-field-premium form-select">
                                <option value="new">-- Nouvelle Entité --</option>
                                @foreach($managedCompanies as $company)
                                    <option value="{{ $company->id }}" data-is-sub="{{ $company->parent_company_id ? 'true' : 'false' }}" {{ $company->id == ($currentCompanyId ?? '') ? 'selected' : '' }}>
                                        {{ $company->company_name }}
                                        @if($company->parent) (Filiale de : {{ $company->parent->company_name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="input-label-premium">Rôle Système <span class="text-danger">*</span></label>
                            <select id="role" name="role" class="input-field-premium form-select" required>
                                <option value="">Choisir un rôle</option>
                                <option value="admin">Administrateur</option>
                                <option value="comptable">Comptable</option>
                            </select>
                            <div class="invalid-feedback" id="errorRole"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="is_active" class="input-label-premium">Statut Compte <span class="text-danger">*</span></label>
                            <select id="is_active" name="is_active" class="input-field-premium form-select" required>
                                <option value="1" selected>Actif</option>
                                <option value="0">Suspendu</option>
                            </select>
                        </div>
                        
                        <div id="habilitationsGroup" class="col-12 mt-6 d-none">
                            <h6 class="text-xs font-black text-slate-400 uppercase tracking-widest border-bottom pb-2 mb-4">Périmètre des Habilitations</h6>
                            @foreach(config('accounting_permissions.permissions') as $section => $groupPermissions)
                                <div class="mb-4 permission-section" data-section-name="{{ $section }}">
                                    <h6 class="text-xs font-bold text-slate-600 uppercase mb-2">{{ $section }}</h6>
                                    <div class="row g-3">
                                        @foreach($groupPermissions as $key => $label)
                                            <div class="col-md-6">
                                                <div class="form-check custom-option custom-option-basic p-3 border rounded-xl hover:bg-slate-50 transition-colors d-flex align-items-center">
                                                    <input class="form-check-input permission-checkbox" type="checkbox" name="habilitations[{{ $key }}]" value="1" id="hab_{{ $key }}" style="float: none;">
                                                    <label class="form-check-label font-bold text-sm text-slate-700 ms-3 mb-0" for="hab_{{ $key }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-0 mt-8 justify-content-end gap-3">
                        <button type="button" class="btn btn-label-secondary font-black px-6 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary font-black px-8 rounded-xl shadow-lg" onclick="return validerCreationUtilisateur(event)">Finaliser la création</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update -->
<div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content premium-modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h4 class="modal-title font-black text-slate-800">Modifier l'accès</h4>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-0">Mise à jour du profil collaborateur</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-8">
                <form id="updateUserForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="updateUserId" />

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="updateFirstName" class="input-label-premium">Nom</label>
                            <input type="text" id="updateFirstName" name="name" class="input-field-premium" />
                            <div class="invalid-feedback" id="updateFirstNameError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="updateLastName" class="input-label-premium">Prénom</label>
                            <input type="text" id="updateLastName" name="last_name" class="input-field-premium" />
                            <div class="invalid-feedback" id="updateLastNameError"></div>
                        </div>
                        <div class="col-12">
                            <label for="updateEmail" class="input-label-premium">Email</label>
                            <input type="email" id="updateEmail" name="email_adresse" class="input-field-premium" />
                            <div class="invalid-feedback" id="updateEmailError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="updateRole" class="input-label-premium">Rôle</label>
                            <select id="updateRole" name="role" class="input-field-premium form-select">
                                <option value="admin">Administrateur</option>
                                <option value="comptable">Comptable</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="updateCompanyField">
                            <label for="updateCompanyId" class="input-label-premium">Entité</label>
                            <select name="company_id" id="updateCompanyId" class="input-field-premium form-select">
                                @foreach($managedCompanies as $company)
                                    <option value="{{ $company->id }}" data-is-sub="{{ $company->parent_company_id ? 'true' : 'false' }}">{{ $company->company_name }} @if($company->parent) (Filiale de : {{ $company->parent->company_name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="updateHabilitationsSection" class="mt-8">
                        <h6 class="text-xs font-black text-slate-400 uppercase tracking-widest border-bottom pb-2 mb-4">Gestion des Habilitations</h6>
                        @foreach(config('accounting_permissions.permissions') as $section => $groupPermissions)
                            <div class="mb-4 permission-section" data-section-name="{{ $section }}">
                                <h6 class="text-xs font-bold text-slate-600 uppercase mb-2">{{ $section }}</h6>
                                <div class="row g-3">
                                    @foreach($groupPermissions as $key => $label)
                                        <div class="col-md-6">
                                            <div class="form-check custom-option custom-option-basic p-3 border rounded-xl hover:bg-slate-50 transition-colors d-flex align-items-center">
                                                <input type="hidden" name="habilitations[{{ $key }}]" value="0">
                                                <input class="form-check-input permission-checkbox" type="checkbox" id="update_{{ $key }}" name="habilitations[{{ $key }}]" value="1" style="float: none;">
                                                <label class="form-check-label font-bold text-sm text-slate-700 ms-3 mb-0" for="update_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="modal-footer border-0 p-0 mt-8 justify-content-end gap-3">
                        <button type="button" class="btn btn-label-secondary font-black px-6 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary font-black px-8 rounded-xl shadow-lg">Sauvegarder les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal See -->
<div class="modal fade" id="modalCenterSee" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content premium-modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h4 class="modal-title font-black text-slate-800">Fiche Collaborateur</h4>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-0">Vue détaillée des accès et habilitations</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body pt-8">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="input-label-premium">Identité</label>
                        <div class="bg-slate-50 p-3 rounded-xl font-bold text-slate-800" id="seeIdentite">--</div>
                        <input type="hidden" id="seeFirstName">
                        <input type="hidden" id="seeLastName">
                    </div>
                    <div class="col-md-6">
                        <label class="input-label-premium">Contact</label>
                        <div class="bg-slate-50 p-3 rounded-xl font-bold text-slate-800" id="seeEmailDisplay">--</div>
                        <input type="hidden" id="seeEmail">
                    </div>
                    <div class="col-md-6">
                        <label class="input-label-premium">Statut & Rôle</label>
                        <div class="bg-slate-50 p-3 rounded-xl font-bold text-slate-800" id="seeRoleDisplay">--</div>
                        <input type="hidden" id="seeRole">
                    </div>
                    <div class="col-md-6">
                        <label class="input-label-premium">Entité Assignée</label>
                        <div class="bg-slate-50 p-3 rounded-xl font-bold text-blue-900 border border-blue-100" id="seeCompanyDisplay">--</div>
                        <input type="hidden" id="seeCompany">
                    </div>
                </div>

                <div id="seeHabilitationsSection" class="mt-8">
                    <h6 class="text-xs font-black text-slate-400 uppercase tracking-widest border-bottom pb-2 mb-4">Habilitations Actives</h6>
                    @foreach(config('accounting_permissions.permissions') as $section => $groupPermissions)
                        <div class="mb-4 permission-section" data-section-name="{{ $section }}">
                            <h6 class="text-xs font-bold text-slate-600 uppercase mb-2">{{ $section }}</h6>
                            <div class="row g-3">
                                @foreach($groupPermissions as $key => $label)
                                    <div class="col-md-6">
                                        <div class="form-check custom-option custom-option-basic p-3 border rounded-xl opacity-75 d-flex align-items-center">
                                            <input class="form-check-input permission-checkbox" type="checkbox" id="see_{{ $key }}" disabled style="float: none;">
                                            <label class="form-check-label font-bold text-sm text-slate-700 ms-3 mb-0" for="see_{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-0 p-0 mt-8 justify-content-end">
                <button type="button" class="btn btn-label-secondary font-black px-6 rounded-xl" data-bs-dismiss="modal">Fermer la vue</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content premium-modal-content">
            <!-- Header -->
            <div class="text-center mb-6 position-relative">
                <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                    Confirmer la <span class="text-red-600">Suppression</span>
                </h1>
            </div>

            <div class="text-center space-y-3 mb-8">
                <p class="text-slate-500 text-sm font-medium leading-relaxed">
                    Êtes-vous sûr de vouloir supprimer ce collaborateur ? Cette action est irréversible.
                </p>
                <p class="text-slate-900 font-bold" id="userToDelete"></p>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-2 gap-4">
                <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form id="deleteUserForm" method="POST" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-save-premium !bg-red-600 hover:!bg-red-700 shadow-red-200">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
    <style>
        .restricted-permission {
            opacity: 0.5 !important;
            pointer-events: none !important;
            background-color: #f8fafc !important;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountantPermissions = [
            'compta.dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals',
            'postetresorerie.index', 'modal_saisie_direct', 'accounting_entry_list', 'ecriture.rejected',
            'brouillons.index', 'accounting_entry_real',
            'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
            'accounting_balance', 'Balance_Tiers', 'flux_tresorerie', 'tasks.view_daily', 'immobilisations.index'
        ];

        // Logique de grisement pour les modales
        function getIsSubCompany(modal) {
            const companySelect = modal.querySelector('select[name="company_id"]');
            if (!companySelect) return {{ isset($currentCompany) && $currentCompany->parent_company_id ? 'true' : 'false' }};
            const selectedOption = companySelect.options[companySelect.selectedIndex];
            return selectedOption ? selectedOption.getAttribute('data-is-sub') === 'true' : false;
        }

        function applyGrisementToContainer(container, role, sectionName, key, isSubCompany) {
            let isRestricted = false;
            let forceUnchecked = false;

            // 1. Restriction Super Admin
            if (sectionName.includes('Super Admin')) {
                isRestricted = true;
                forceUnchecked = true;
            }

            // 2. Restriction Fusion
            if (sectionName.includes('Fusion & Démarrage') && !isSubCompany) {
                isRestricted = true;
                forceUnchecked = true;
            }

            // 3. Accountant Restrictions
            if (role === 'comptable' && !accountantPermissions.includes(key)) {
                isRestricted = true;
                forceUnchecked = true;
            }

            const checkbox = container.querySelector('.permission-checkbox');
            if (isRestricted) {
                if (checkbox && forceUnchecked && !checkbox.disabled) checkbox.checked = false;
                if (checkbox) checkbox.disabled = true;
                container.classList.add('restricted-permission');
            } else {
                if (checkbox && !container.id.includes('see_')) checkbox.disabled = false;
                container.classList.remove('restricted-permission');
            }
        }

        function updateModalPermissions(modalId, roleSelectId) {
            const modal = document.getElementById(modalId);
            const roleSelect = document.getElementById(roleSelectId);
            if (!modal || !roleSelect) return;

            const isSubCompany = getIsSubCompany(modal);
            const sections = modal.querySelectorAll('.permission-section');
            const role = roleSelect.value;

            sections.forEach(section => {
                const sectionName = section.getAttribute('data-section-name');
                const containers = section.querySelectorAll('.form-check');
                
                containers.forEach(container => {
                    const checkbox = container.querySelector('.permission-checkbox');
                    const key = checkbox.id.split('_').pop(); // hab_key or update_key or see_key
                    applyGrisementToContainer(container, role, sectionName, key, isSubCompany);
                });
            });
        }

        // Écouteurs pour la création
        const createRole = document.getElementById('role');
        const createCompany = document.getElementById('company_id');
        if (createRole) {
            createRole.addEventListener('change', () => updateModalPermissions('modalCenterCreate', 'role'));
        }
        if (createCompany) {
            createCompany.addEventListener('change', () => updateModalPermissions('modalCenterCreate', 'role'));
        }

        // Écouteurs pour la modification
        const updateRole = document.getElementById('updateRole');
        const updateCompany = document.getElementById('updateCompanyId');
        if (updateRole) {
            updateRole.addEventListener('change', () => updateModalPermissions('modalCenterUpdate', 'updateRole'));
        }
        if (updateCompany) {
            updateCompany.addEventListener('change', () => updateModalPermissions('modalCenterUpdate', 'updateRole'));
        }

        // Pour la visualisation, on applique le grisement à l'ouverture du modal
        const seeModal = document.getElementById('modalCenterSee');
        if (seeModal) {
            seeModal.addEventListener('show.bs.modal', function() {
                // On attend un peu que le role soit injecté par user_m.js
                setTimeout(() => {
                    const role = document.getElementById('seeRole').value;
                    const isSubCompany = {{ isset($currentCompany) && $currentCompany->parent_company_id ? 'true' : 'false' }};
                    const sections = seeModal.querySelectorAll('.permission-section');
                    sections.forEach(section => {
                        const sectionName = section.getAttribute('data-section-name');
                        const containers = section.querySelectorAll('.form-check');
                        containers.forEach(container => {
                            const checkbox = container.querySelector('.permission-checkbox');
                            const key = checkbox.id.split('_').pop();
                            applyGrisementToContainer(container, role, sectionName, key, isSubCompany);
                        });
                    });
                }, 100);
            });
        }
        
        // Initialisation si nécessaire
        const createModal = document.getElementById('modalCenterCreate');
        if (createModal) {
            createModal.addEventListener('show.bs.modal', () => updateModalPermissions('modalCenterCreate', 'role'));
        }
    });
    </script>
