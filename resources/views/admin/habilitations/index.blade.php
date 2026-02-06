<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Modification Habilitation'])
                
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gestion des Habilitations</h5>
                                <p class="text-muted small mb-0">Définissez les accès pour chaque collaborateur de l'entreprise.</p>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Liste des utilisateurs -->
                            <div class="col-md-4 mb-4">
                                <div class="card glass-card h-100">
                                    <div class="card-header border-bottom">
                                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-users me-2"></i>Collaborateurs</h6>
                                    </div>
                                    <div class="list-group list-group-flush user-list">
                                        @foreach($users as $user)
                                            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center p-3 user-select-btn" 
                                               data-user-id="{{ $user->id }}"
                                               data-user-name="{{ $user->name }} {{ $user->last_name }}"
                                               data-user-role="{{ $user->role }}"
                                               data-created-by="{{ $user->created_by_id }}"
                                               data-is-principal="{{ $user->isPrincipalAdmin() ? '1' : '0' }}"
                                               data-permissions='{{ json_encode($user->getHabilitations()) }}'>
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-dark">{{ $user->name }} {{ $user->last_name }}</h6>
                                                    <small class="text-muted">{{ ucfirst($user->role) }}</small>
                                                </div>
                                                <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Panneau des permissions -->
                            <div class="col-md-8 mb-4">
                                <div class="card glass-card h-100" id="permissions-panel" style="display:none;">
                                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 fw-bold" id="selected-user-name">Permissions de ...</h6>
                                            <small class="text-muted">Cochez les modules autorisés</small>
                                        </div>
                                        <button type="submit" form="permissions-form" class="btn btn-premium btn-sm">
                                            <i class="fa-solid fa-save me-2"></i>Enregistrer
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <form id="permissions-form" method="POST" action="">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="accordion accordion-flush" id="accordionPermissions">
                                                @foreach($modules as $groupName => $permissions)
                                                    <div class="accordion-item bg-transparent border-bottom-0 mb-2" data-section-name="{{ $groupName }}">
                                                        <h2 class="accordion-header" id="heading{{ \Illuminate\Support\Str::slug($groupName) }}">
                                                            <button class="accordion-button collapsed bg-white border rounded shadow-sm fw-bold text-dark" type="button" 
                                                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ \Illuminate\Support\Str::slug($groupName) }}"
                                                                    style="border-left: 4px solid #4f46e5 !important;">
                                                                <span class="d-flex align-items-center w-100 justify-content-between pe-3">
                                                                    <span>
                                                                        @php
                                                                            $icon = match($groupName) {
                                                                                'Pilotage', 'Pilotage (Super Admin)' => 'fa-chart-pie',
                                                                                'Configuration Entreprise', 'Configuration' => 'fa-cogs',
                                                                                'Gouvernance', 'Gouvernance (Super Admin)' => 'fa-user-shield',
                                                                                'Opérations', 'Opérations (Super Admin)' => 'fa-list-check',
                                                                                'Gestion des Tâches' => 'fa-tasks',
                                                                                'Validation' => 'fa-check-double',
                                                                                'Paramétrage' => 'fa-sliders',
                                                                                'Importation' => 'fa-file-import',
                                                                                'Exportation' => 'fa-file-export',
                                                                                'Traitement' => 'fa-file-invoice',
                                                                                'Rapports', 'Analyses (Super Admin)' => 'fa-chart-line',
                                                                                'Fusion & Démarrage' => 'fa-bolt',
                                                                                'ETATS FINANCIERS' => 'fa-file-invoice-dollar',
                                                                                default => 'fa-folder'
                                                                            };
                                                                        @endphp
                                                                        <i class="fa-solid {{ $icon }} me-2 text-primary opacity-75"></i>
                                                                        {{ $groupName }}
                                                                    </span>
                                                                    <span class="badge bg-soft-primary text-primary rounded-pill">{{ count($permissions) }}</span>
                                                                </span>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse{{ \Illuminate\Support\Str::slug($groupName) }}" class="accordion-collapse collapse" 
                                                             data-bs-parent="#accordionPermissions">
                                                            <div class="accordion-body bg-white border-start border-end border-bottom rounded-bottom shadow-sm pt-3">
                                                                <div class="row g-3">
                                                                    @foreach($permissions as $key => $label)
                                                                        <div class="col-md-6">
                                                                            <div class="form-check form-switch custom-switch-premium p-3 rounded border h-100 d-flex align-items-center transition-hover">
                                                                                <input class="form-check-input permission-checkbox me-3 fs-5" type="checkbox" 
                                                                                       name="habilitations[{{ $key }}]" value="1" id="perm_{{ $key }}"
                                                                                       style="cursor: pointer;">
                                                                                <label class="form-check-label w-100 cursor-pointer fw-medium text-secondary" for="perm_{{ $key }}">
                                                                                    {{ $label }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Empty State -->
                                <div class="card glass-card h-100 d-flex justify-content-center align-items-center text-center p-5 border-0 shadow-sm" id="empty-state">
                                    <div class="py-5">
                                        <div class="avatar avatar-xl bg-soft-primary text-primary rounded-circle mb-4 mx-auto pulse-animation">
                                            <i class="fa-solid fa-user-gear fs-1"></i>
                                        </div>
                                        <h4 class="fw-bold text-dark">Gestion des accès</h4>
                                        <p class="text-muted mb-0" style="max-width: 300px;">Sélectionnez un collaborateur dans la liste pour configurer ses permissions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-switch-premium .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        .custom-switch-premium:hover {
            background-color: #f9fafb;
            border-color: #4f46e5 !important;
        }
        .transition-hover {
            transition: all 0.2s ease;
        }
        .bg-soft-primary {
            background-color: rgba(79, 70, 229, 0.1) !important;
        }
        .list-group-item.active {
            background-color: #f3f4f6;
            border-color: #e5e7eb;
            color: inherit;
            border-left: 4px solid #4f46e5;
        }
        /* Style pour les éléments "restreints" mais envoyés quand même */
        .restricted-permission {
            opacity: 0.5;
            pointer-events: none; /* Empêche le clic */
            background-color: #f3f4f6; /* Gris léger */
        }
    </style>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userButtons = document.querySelectorAll('.user-select-btn');
            const permissionsPanel = document.getElementById('permissions-panel');
            const emptyState = document.getElementById('empty-state');
            const userNameTitle = document.getElementById('selected-user-name');
            const form = document.getElementById('permissions-form');
            const checkboxes = document.querySelectorAll('.permission-checkbox');

            // Variables globales passées depuis PHP
            const currentUserId = {{ auth()->id() }};
            const isSubCompany = {{ $isSubCompany ? 'true' : 'false' }};

            const accountantPermissions = [
                'compta.dashboard', 'notifications.index', 'plan_comptable', 'plan_tiers', 'accounting_journals',
                'postetresorerie.index', 'modal_saisie_direct', 'accounting_entry_list', 'ecriture.rejected',
                'brouillons.index', 'accounting_entry_real',
                'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
                'accounting_balance', 'Balance_Tiers', 'flux_tresorerie', 'tasks.view_daily', 'immobilisations.index'
            ];

            userButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    userButtons.forEach(b => b.classList.remove('active', 'bg-light'));
                    this.classList.add('active');

                    emptyState.style.display = 'none';
                    permissionsPanel.style.display = 'block';

                    const name = this.getAttribute('data-user-name');
                    const userId = parseInt(this.getAttribute('data-user-id'));
                    const userRole = this.getAttribute('data-user-role');
                    const createdBy = parseInt(this.getAttribute('data-created-by'));
                    const isPrincipal = this.getAttribute('data-is-principal') === '1';

                    userNameTitle.innerHTML = `<span class="text-primary me-2"><i class="fa-solid fa-shield-alt"></i></span>Permissions de <span class="text-dark fw-bold">${name}</span> <span class="badge bg-label-secondary ms-2">${userRole}</span>`;
                    form.action = "/admin/habilitations/" + userId;

                    let rawPermissions = this.getAttribute('data-permissions');
                    let userExistingPermissions = [];
                    try {
                        const parsed = JSON.parse(rawPermissions);
                        userExistingPermissions = Array.isArray(parsed) ? parsed : Object.keys(parsed).filter(key => parsed[key] == "1" || parsed[key] === true);
                    } catch (e) { console.error(e); }

                    // LOGIQUE INTELLIGENTE DES HABILITATIONS
                    checkboxes.forEach(cb => {
                        const match = cb.name.match(/habilitations\[(.+)\]/);
                        const permissionKey = match ? match[1] : cb.value;
                        const sectionItem = cb.closest('.accordion-item');
                        const sectionName = sectionItem ? sectionItem.getAttribute('data-section-name') : '';

                        // 1. Initialisation de l'état coché
                        const hasPermission = userExistingPermissions.includes(permissionKey);
                        cb.checked = hasPermission;

                        // 2. Détermination des restrictions
                        let isRestricted = false;
                        let forceUnchecked = false;

                        // RÈGLE A : Sections Super Admin (Toujours interdites pour tous sauf Super Admin)
                        if (sectionName.includes('Super Admin')) {
                            isRestricted = true;
                            forceUnchecked = true;
                        }

                        // RÈGLE B : Section Fusion (Uniquement pour sous-entreprises)
                        if (sectionName.includes('Fusion & Démarrage')) {
                            if (!isSubCompany) {
                                isRestricted = true;
                                forceUnchecked = true;
                            }
                        }

                        // RÈGLE C : Auto-modification (On ne peut pas modifier ses propres droits)
                        if (userId === currentUserId) {
                            isRestricted = true;
                        }

                        // RÈGLE D : Admin Principal (Droits fixes, tout coché sauf SA/Fusion)
                        if (isPrincipal) {
                            if (!forceUnchecked) {
                                cb.checked = true;
                            }
                            isRestricted = true;
                        }

                        // RÈGLE E : Admin Sécondaire / Autre Utilisateur
                        // Seul le créateur peut modifier les habilitations
                        if (!isPrincipal && createdBy !== currentUserId) {
                            isRestricted = true;
                        }

                        // RÈGLE F : Restrictions Comptable
                        if (userRole === 'comptable') {
                            if (!accountantPermissions.includes(permissionKey)) {
                                isRestricted = true;
                                forceUnchecked = true;
                            }
                        }

                        // RÈGLE F : Droits supérieurs (Comptable ne voit pas les droits Admin)
                        // Note: Ici on est dans l'admin, donc l'admin voit tout. 
                        // Mais si un "user" (comptable) accède ici via une faille ou autre...
                        // On vérifie si la permission est de type admin.* (Optionnel car protégé par middleware)

                        // 3. Application visuelle
                        if (forceUnchecked) {
                            cb.checked = false;
                        }

                        const container = cb.closest('.form-check'); 
                        if (isRestricted) {
                            container.classList.add('restricted-permission');
                            cb.onclick = (e) => e.preventDefault();
                        } else {
                            container.classList.remove('restricted-permission');
                            cb.onclick = null;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
