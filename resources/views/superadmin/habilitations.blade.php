<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .btn-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white !important;
        border: none;
        border-radius: 12px;
        padding: 0.6rem 1.2rem;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 64, 175, 0.3);
    }

    .user-select-btn.active {
        background: #eff6ff !important;
        border-left: 4px solid #1e40af !important;
    }

    .restricted-permission {
        opacity: 0.5;
        pointer-events: none;
        background-color: #f8fafc;
    }

    .custom-switch-premium .form-check-input:checked {
        background-color: #1e40af;
        border-color: #1e40af;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gestion Globale des Habilitations'])
                
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Habilitations Système</h5>
                                <p class="text-muted small mb-0">Supervisez et modifiez les accès de tous les utilisateurs du réseau.</p>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Liste des utilisateurs -->
                            <div class="col-md-4 mb-4">
                                <div class="card glass-card h-100">
                                    <div class="card-header border-bottom py-3">
                                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-users me-2 text-primary"></i>Tous les Utilisateurs</h6>
                                    </div>
                                    <div class="list-group list-group-flush user-list overflow-auto" style="max-height: 700px;">
                                        @foreach($users as $user)
                                            <a href="#" class="list-group-item list-group-item-action d-flex align-items-center p-3 user-select-btn" 
                                               data-user-id="{{ $user->id }}"
                                               data-user-name="{{ $user->name }} {{ $user->last_name }}"
                                               data-user-role="{{ $user->role }}"
                                               data-user-email="{{ $user->email_adresse }}"
                                               data-company-name="{{ $user->company->company_name ?? 'N/A' }}"
                                               data-permissions='{{ json_encode($user->getHabilitations()) }}'>
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0 text-dark small">{{ $user->name }} {{ $user->last_name }}</h6>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <span class="badge {{ $user->isSuperAdmin() ? 'bg-danger' : ($user->isAdmin() ? 'bg-primary' : 'bg-success') }} text-[9px] py-0.5">
                                                            {{ ucfirst($user->role) }}
                                                        </span>
                                                        <small class="text-muted text-[10px]">{{ $user->company->company_name ?? 'Freelance/System' }}</small>
                                                    </div>
                                                </div>
                                                <i class="fa-solid fa-chevron-right ms-auto text-muted small"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                    <div class="card-footer py-2 border-top bg-light/50">
                                        {{ $users->links() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Panneau des permissions -->
                            <div class="col-md-8 mb-4">
                                <div class="card glass-card h-100" id="permissions-panel" style="display:none;">
                                    <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar bg-soft-primary text-primary p-2 rounded-circle" id="selected-user-avatar">
                                                <i class="fa-solid fa-user-shield fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold" id="selected-user-name">Permissions...</h6>
                                                <small class="text-muted" id="selected-user-info"></small>
                                            </div>
                                        </div>
                                        <button type="submit" form="permissions-form" class="btn btn-premium btn-sm px-4">
                                            <i class="fa-solid fa-save me-2"></i>Mettre à jour
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <form id="permissions-form" method="POST" action="">
                                            @csrf
                                            {{-- On utilisera AJAX ou le submit classique --}}
                                            
                                            <div class="accordion accordion-flush" id="accordionPermissions">
                                                @foreach($modules as $groupName => $permissions)
                                                    <div class="accordion-item bg-transparent border-bottom-0 mb-2" data-section-name="{{ $groupName }}">
                                                        <h2 class="accordion-header" id="heading{{ \Illuminate\Support\Str::slug($groupName) }}">
                                                            <button class="accordion-button collapsed bg-white border rounded shadow-sm fw-bold text-dark mx-3 mt-2 w-auto" type="button" 
                                                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ \Illuminate\Support\Str::slug($groupName) }}"
                                                                    style="border-left: 4px solid #1e40af !important; min-width: calc(100% - 2rem);">
                                                                <span class="d-flex align-items-center w-100 justify-content-between pe-3">
                                                                    <span>
                                                                        <i class="fa-solid fa-folder me-2 text-primary opacity-75"></i>
                                                                        {{ $groupName }}
                                                                    </span>
                                                                    <span class="badge bg-light text-primary rounded-pill border">{{ count($permissions) }}</span>
                                                                </span>
                                                            </button>
                                                        </h2>
                                                        <div id="collapse{{ \Illuminate\Support\Str::slug($groupName) }}" class="accordion-collapse collapse" 
                                                             data-bs-parent="#accordionPermissions">
                                                            <div class="accordion-body bg-slate-50 border-start border-end border-bottom rounded-bottom shadow-sm mx-3 mb-3 pt-3">
                                                                <div class="row g-3">
                                                                    @foreach($permissions as $key => $label)
                                                                        <div class="col-md-6">
                                                                            <div class="form-check form-switch custom-switch-premium p-3 bg-white rounded border h-100 d-flex align-items-center gap-3">
                                                                                <input class="form-check-input permission-checkbox ms-0" type="checkbox" 
                                                                                       name="habilitations[{{ $key }}]" value="1" id="perm_{{ $key }}" style="float: none;">
                                                                                <label class="form-check-label cursor-pointer fw-semibold text-slate-600 small mb-0" for="perm_{{ $key }}">
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
                                        <div class="avatar avatar-xl bg-blue-50 text-blue-600 rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center">
                                            <i class="fa-solid fa-key fs-1"></i>
                                        </div>
                                        <h4 class="fw-bold text-slate-900 border-0">Gestion Centrale des Accès</h4>
                                        <p class="text-slate-500 mb-0" style="max-width: 400px;">Sélectionnez un administrateur ou un utilisateur dans la liste de gauche pour configurer ses habilitations globales sur le système.</p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userButtons = document.querySelectorAll('.user-select-btn');
            const permissionsPanel = document.getElementById('permissions-panel');
            const emptyState = document.getElementById('empty-state');
            const userNameTitle = document.getElementById('selected-user-name');
            const userInfoText = document.getElementById('selected-user-info');
            const form = document.getElementById('permissions-form');
            const checkboxes = document.querySelectorAll('.permission-checkbox');

            const currentUserId = {{ auth()->id() }};
            const isPrimarySA = {{ auth()->user()->isPrimarySuperAdmin() ? 'true' : 'false' }};

            const accountantPermissions = [
                'compta.dashboard', 'plan_comptable', 'plan_tiers', 'accounting_journals',
                'postetresorerie.index', 'modal_saisie_direct', 'accounting_entry_list', 'ecriture.rejected',
                'brouillons.index', 'accounting_entry_real',
                'gestion_tresorerie', 'accounting_ledger', 'accounting_ledger_tiers',
                'accounting_balance', 'Balance_Tiers', 'flux_tresorerie', 'tasks.view_daily', 'immobilisations.index'
            ];

            userButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    userButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    emptyState.style.display = 'none';
                    permissionsPanel.style.display = 'block';

                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    const userRole = this.getAttribute('data-user-role');
                    const company = this.getAttribute('data-company-name');
                    const email = this.getAttribute('data-user-email');

                    userNameTitle.textContent = userName;
                    userInfoText.textContent = `${userRole} | ${company} | ${email}`;
                    
                    form.action = `/superadmin/habilitations/update/${userId}`;

                    // Reset checkboxes
                    checkboxes.forEach(cb => {
                        cb.checked = false;
                        cb.disabled = false;
                        cb.closest('.form-check').classList.remove('restricted-permission');
                    });

                    // Parse current permissions
                    let userHasPermissions = {};
                    try {
                        const dataHabs = this.getAttribute('data-permissions');
                        userHasPermissions = dataHabs ? JSON.parse(dataHabs) : {};
                    } catch (err) {
                        console.error("Erreur parsing permissions:", err);
                    }

                    // Accountant section logic
                    const sections = document.querySelectorAll('.accordion-item');
                    sections.forEach(section => {
                        section.classList.remove('restricted-permission');
                        const sectionCheckboxes = section.querySelectorAll('.permission-checkbox');
                        let hasAllowedPermission = false;

                        sectionCheckboxes.forEach(cb => {
                            const key = cb.name.match(/habilitations\[(.+)\]/)[1];
                            
                            // Reset
                            cb.disabled = false;
                            cb.closest('.form-check').classList.remove('restricted-permission');

                            // Check current state from data
                            if (userHasPermissions[key] == "1" || userHasPermissions[key] === true || userHasPermissions[key] === 1) {
                                cb.checked = true;
                            } else {
                                cb.checked = false;
                            }

                            // Admin/Comptable filters for Internal Admin
                            if ((userRole === 'admin' || userRole === 'comptable') && key === 'superadmin.secondary.index') {
                                cb.checked = false;
                                cb.disabled = true;
                                cb.closest('.form-check').classList.add('restricted-permission');
                            }

                            // Accountant filters
                            if (userRole === 'comptable') {
                                if (accountantPermissions.includes(key)) {
                                    hasAllowedPermission = true;
                                } else {
                                    cb.checked = false;
                                    cb.disabled = true;
                                    cb.closest('.form-check').classList.add('restricted-permission');
                                }
                            } else if (userRole === 'super_admin') {
                                // For Super Admins, everything is allowed and nothing is grayed out
                                hasAllowedPermission = true;
                            } else {
                                // For other roles (Admins)
                                if (key !== 'superadmin.secondary.index') {
                                    hasAllowedPermission = true;
                                }
                            }

                            // Security: SA secondaire ne peut pas modifier un SA
                            if (userRole === 'super_admin' && !isPrimarySA) {
                                cb.disabled = true;
                                cb.closest('.form-check').classList.add('restricted-permission');
                            }

                            // Empêcher d'enlever ses propres droits
                            if (userId == currentUserId) {
                                cb.disabled = true;
                                cb.closest('.form-check').classList.add('restricted-permission');
                            }
                        });

                        if (!hasAllowedPermission && userRole === 'comptable') {
                            section.classList.add('restricted-permission');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
