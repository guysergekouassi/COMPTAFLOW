<style>
    /* Styles pour le nouveau design */
    .sidebar-new {
        position: fixed;
        left: 0;
        top: 0;
        width: 288px;
        height: 100vh;
        background: white;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .sidebar-header {
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .brand-container {
        display: flex;
        align-items: center;
    }

    .brand-logo {
        width: 40px;
        height: 40px;
        background: #1e40af;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-logo i {
        color: white;
        font-size: 20px;
    }

    .brand-text {
        margin-left: 12px;
    }

    .brand-title {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
        font-family: 'Inter', sans-serif;
    }

    .brand-subtitle {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
        font-family: 'Inter', sans-serif;
    }

    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 16px 12px;
        height: calc(100vh - 80px) !important;
        max-height: calc(100vh - 80px) !important;
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .menu-section {
        margin-bottom: 24px;
    }

    .menu-section-header {
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .menu-link-new {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        color: #374151;
        border-radius: 8px;
        margin-bottom: 4px;
        text-decoration: none;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
    }

    .menu-link-new:hover {
        background: #eff6ff;
        color: #1e40af;
    }

    .menu-link-new.active {
        background: #1e40af;
        color: white;
    }

    .menu-link-quick {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        color: #374151;
        border-radius: 8px;
        margin-bottom: 6px;
        text-decoration: none;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
        font-weight: 500;
    }

    .menu-link-quick:hover {
        background: #eff6ff;
        color: #1e40af;
    }

    .menu-link-quick.active {
        background: #1e40af;
        color: white !important;
        box-shadow: 0 2px 4px rgba(30, 64, 175, 0.2);
    }

    .menu-link-new i {
        width: 20px;
        margin-right: 12px;
        font-size: 18px;
    }

    .sidebar-footer {
        padding: 16px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .user-profile-mini {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px;
        border-radius: 8px;
        transition: background 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .user-profile-mini:hover {
        background: #f3f4f6;
    }

    .user-avatar-sidebar {
        width: 36px;
        height: 36px;
        background: #1e40af;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }

    .user-info-sidebar {
        flex: 1;
        min-width: 0;
    }

    .user-name-sidebar {
        font-size: 13px;
        font-weight: 600;
        color: #111827;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role-sidebar {
        font-size: 11px;
        color: #6b7280;
        display: block;
    }

    .logout-btn-sidebar {
        color: #dc2626;
        padding: 8px;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .logout-btn-sidebar:hover {
        background: #fef2f2;
    }

    .help-box {
        background: #eff6ff;
        border-radius: 8px;
        padding: 12px;
    }

    .help-box-content {
        display: flex;
        align-items: flex-start;
    }

    .help-box i {
        color: #1e40af;
        font-size: 18px;
        margin-top: 4px;
    }

    .help-box-text {
        margin-left: 12px;
    }

    .help-box-title {
        font-size: 12px;
        font-weight: 500;
        color: #111827;
        margin-bottom: 4px;
    }

    .help-box-desc {
        font-size: 12px;
        color: #6b7280;
    }

    /* Conserver les styles existants pour la compatibilité */
    .menu-param a { color:#0d6efd !important; }
    .menu-param a:hover { background:#e7f1ff !important; border-radius:8px; }
    .menu-trait a { color:#fd7e14 !important; }
    .menu-trait a:hover { background:#fff3e6 !important; border-radius:8px; }
    .menu-rapport a { color:#198754 !important; }
    .menu-rapport a:hover { background:#e9f7ef !important; border-radius:8px; }

    .company-name-sidebar {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 2px;
        font-weight: 600;
        display: block;
        line-height: 1.2;
    }

    .role-badge-sidebar {
        font-size: 0.65rem;
        color: #1e40af;
        background: #eff6ff;
        padding: 2px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.025em;
        display: inline-block;
        margin-top: 4px;
    }

    /* Cacher l'ancien design */
    .layout-menu {
        display: none;
    }
</style>

@include('components.modal_saisie_direct', [
    'exercices' => $exercices,
    'code_journaux' => $code_journaux,
    'companies' => $companies,
    'exerciceActif' => $exerciceActif,
])





<!-- Nouveau Sidebar Design -->
@if (auth()->check() && auth()->user()->isSuperAdmin())
    @include('components.superadmin_sidebar')
@else
<div class="sidebar-new">
    <div class="sidebar-header">
        <div class="brand-container">
            <div class="brand-logo">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div class="brand-text">
                <h1 class="brand-title">Flow Compta</h1>
                @if ($currentCompany)
                    <div class="company-name-sidebar">
                        {{ $currentCompany->company_name }}
                    </div>
                    <div class="role-badge-sidebar">
                        {{ auth()->user()->role === 'comptable' ? 'Comptable' : (auth()->user()->role === 'super_admin' ? 'Super Admin' : auth()->user()->role) }}
                    </div>
                @else
                    <div class="role-badge-sidebar mt-1">Super Admin</div>
                @endif
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        {{-- SÉLECTEUR D'EXERCICE (NOUVEAU) --}}
        @if(isset($exercices) && $exercices->count() > 0 && $isComptaAccountActive)
            <div class="px-3 mb-4">
                <div class="dropdown">
                    @php
                        $exerciceEnContexte = session('current_exercice_id') ? true : false;
                    @endphp
                    <button class="btn btn-white w-100 text-start d-flex align-items-center justify-content-between border shadow-sm rounded-lg py-2 px-3 {{ $exerciceEnContexte ? 'border-primary' : '' }}" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-calendar-check {{ $exerciceEnContexte ? 'text-primary' : 'text-primary' }} me-2"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark">{{ $exerciceActif->intitule ?? 'Exercice non défini' }}</span>
                                {{-- <span class="text-muted" style="font-size: 0.7rem;">
                                    @if($exerciceEnContexte)
                                        <i class="fa-solid fa-lock me-1"></i>Exercice Sélectionné
                                    @else
                                        Exercice Actif (Défaut)
                                    @endif
                                </span> --}}
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-down text-muted" style="font-size: 0.7rem;"></i>
                    </button>
                    <ul class="dropdown-menu w-100 shadow-lg border-0 rounded-lg p-2">
                        <li class="px-2 py-1 text-xs text-muted font-bold text-uppercase">Changer d'exercice</li>
                        @foreach($exercices as $exo)
                            <li>
                                <a class="dropdown-item rounded-md py-2 d-flex align-items-center justify-content-between {{ ($exerciceActif && $exerciceActif->id === $exo->id) ? 'active bg-primary text-white' : '' }}" 
                                   href="{{ route('exercice_comptable.switch', $exo->id) }}"
                                   data-exercice-switch="{{ $exo->id }}">
                                   <span>{{ $exo->intitule }}</span>
                                   @if($exo->is_active)
                                       <span class="badge bg-warning text-dark ms-2" style="font-size: 0.6rem;">DÉFAUT</span>
                                   @endif
                                </a>
                            </li>
                        @endforeach
                        @if($exerciceEnContexte)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-success font-bold rounded-md py-2" 
                                   href="{{ route('exercice_comptable.switch', 0) }}"
                                   data-exercice-switch="0">
                                    <i class="fa-solid fa-arrow-rotate-left me-2"></i> Quitter le contexte
                                </a>
                            </li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-primary font-bold" href="{{ route('exercice_comptable') }}">
                                <i class="fa-solid fa-cog me-2"></i> Gérer les exercices
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        @endif

            {{-- SECTION 1 : PILOTAGE --}}
        <div class="menu-section">
            @php
                $showPilotage = auth()->user()->hasPermission('compta.dashboard') || auth()->user()->hasPermission('admin.performance') || auth()->user()->hasPermission('tasks.view_daily');
            @endphp
            
            @if($showPilotage)
                <div class="menu-section-header">Pilotage</div>
                
                @php
                    $dashboardRoute = route('admin.dashboard');
                    if (auth()->user()->isComptable()) {
                        $dashboardRoute = route('comptable.comptdashboard');
                    } elseif ($isComptaAccountActive) {
                        $dashboardRoute = route('compta.dashboard');
                    }
                @endphp

                @if(auth()->user()->hasPermission('admin.performance'))
                    <a href="{{ route('admin.performance') }}" class="menu-link-new {{ request()->routeIs('admin.performance', 'admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-rocket"></i>
                        <span>Tableau de bord Admin</span>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('compta.dashboard'))
                    <a href="{{ $dashboardRoute }}" class="menu-link-new {{ request()->routeIs('comptable.comptdashboard', 'compta.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Tableau de bord personnel</span>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('notifications.index'))
                <a href="{{ route('notifications.index') }}" class="menu-link-new {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadNotificationsCount }}</span>
                    @endif
                </a>
                @endif



            @endif
        </div>

            {{-- SECTION 2 : CONFIGURATION ENTREPRISE --}}
            {{-- (Inchangé) --}}
            @php
                $showConfig = (auth()->user()->hasPermission('admin.config.hub') || 
                               auth()->user()->hasPermission('admin.config.plan_comptable') || 
                               auth()->user()->hasPermission('admin.config.plan_tiers') || 
                               auth()->user()->hasPermission('admin.config.journals') ||
                               auth()->user()->hasPermission('admin.config.external_import')) && !session('sidebar_config_hidden', false);
                
                $showExport = auth()->user()->hasPermission('admin.export.hub') && !session('sidebar_config_hidden', false);
            @endphp

            @if($showConfig && !session('sidebar_admin_hidden', false))
            <div class="menu-section">
                <div class="menu-section-header">Configuration Entreprise</div>
                @if(auth()->user()->hasPermission('admin.config.hub'))
                <a href="{{ route('admin.config.hub') }}" class="menu-link-new {{ request()->routeIs('admin.config.hub') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears"></i>
                    <span>Dossier de Configuration</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.config.plan_comptable'))
                <a href="{{ route('admin.config.plan_comptable') }}" class="menu-link-new {{ request()->routeIs('admin.config.plan_comptable') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span>Modèle de Plan</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.config.plan_tiers'))
                <a href="{{ route('admin.config.plan_tiers') }}" class="menu-link-new {{ request()->routeIs('admin.config.plan_tiers') ? 'active' : '' }}">
                    <i class="fa-solid fa-address-book"></i>
                    <span>Modèle de Tiers</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.config.journals'))
                <a href="{{ route('admin.config.journals') }}" class="menu-link-new {{ request()->routeIs('admin.config.journals') ? 'active' : '' }}">
                    <i class="fa-solid fa-swatchbook"></i>
                    <span>Modèle des Journaux</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.config.tresorerie_posts'))
                <a href="{{ route('admin.config.tresorerie_posts') }}" class="menu-link-new {{ request()->routeIs('admin.config.tresorerie_posts') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Postes de Trésorerie</span>
                </a>
                @endif
                {{-- @if(auth()->user()->hasPermission('admin.config.treasury_categories'))
                <a href="{{ route('admin.config.treasury_categories') }}" class="menu-link-new {{ request()->routeIs('admin.config.treasury_categories') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Catégories de Trésorerie</span>
                </a>
                @endif --}}
            </div>
            @endif

            @if(auth()->user()->hasPermission('admin.config.external_import') && !session('sidebar_admin_hidden', false))
            <div class="menu-section">
                <div class="menu-section-header">IMPORTATION</div>
                <a href="{{ route('admin.config.external_import') }}" class="menu-link-new {{ request()->routeIs('admin.config.external_import') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-import"></i>
                    <span>Importation de données</span>
                </a>
            </div>
            @endif

            {{-- SECTION FUSION (Sous-entreprises uniquement) --}}
            @if(isset($currentCompany) && $currentCompany->parent_company_id && !session('sidebar_admin_hidden', false) && (auth()->user()->isAdmin() || auth()->user()->hasPermission('admin.fusion.index')))
            <div class="menu-section">
                <div class="menu-section-header">Fusion & Démarrage</div>
                <a href="{{ route('admin.fusion.index') }}" class="menu-link-new {{ request()->routeIs('admin.fusion.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bolt text-warning"></i>
                    <span>Fusion Données Mère</span>
                </a>
            </div>
            @endif

            @if($showExport && !session('sidebar_admin_hidden', false))
            <div class="menu-section">
                <div class="menu-section-header">Exportation</div>
                <a href="{{ route('admin.export.hub') }}" class="menu-link-new {{ request()->routeIs('admin.export.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-export"></i>
                    <span>Exportation de données</span>
                </a>
            </div>
            @endif

            {{-- SECTION 3 : GOUVERNANCE --}}
            @php
                $hasGouvernance = auth()->user()->hasPermission('compta_accounts.index') || 
                                  auth()->user()->hasPermission('admin.companies.create') || 
                                  auth()->user()->hasPermission('user_management') || 
                                  auth()->user()->hasPermission('admin.habilitations.index') ||
                                  auth()->user()->hasPermission('admin.switch') ||
                                  auth()->user()->isAdmin();
            @endphp
            @if($hasGouvernance && !session('sidebar_admin_hidden', false))
            <div class="menu-section">
                <div class="menu-section-header">Gouvernance</div>
                @if(auth()->user()->hasPermission('compta_accounts.index'))
                <a href="{{ route('compta_accounts.index') }}" class="menu-link-new {{ request()->routeIs('compta_accounts.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-sitemap"></i>
                    <span>Gestion des Entités</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('user_management'))
                <a href="{{ route('user_management') }}" class="menu-link-new {{ request()->routeIs('user_management') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>Équipe & Permissions</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.habilitations.index') || auth()->user()->isAdmin())
                <a href="{{ route('admin.habilitations.index') }}" class="menu-link-new {{ request()->routeIs('admin.habilitations.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Modification Habilitation</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.switch'))
                <a href="{{ route('admin.switch') }}" class="menu-link-new {{ request()->routeIs('admin.switch') ? 'active' : '' }}">
                    <i class="fa-solid fa-repeat"></i>
                    <span>Switch Comptabilité</span>
                </a>
                @endif

                {{-- Quick Actions Sub-Section --}}
                @php
                    $hasQuickActions = auth()->user()->hasPermission('compta.create') || 
                                      auth()->user()->hasPermission('admin.admins.create') || 
                                      auth()->user()->hasPermission('admin.secondary_admins.create') || 
                                      auth()->user()->hasPermission('admin.users.create');
                @endphp
                @if($hasQuickActions)
                <div class="mt-2 pt-2 border-top border-light">
                    <small class="text-muted text-uppercase px-3 mb-2 d-block" style="font-size: 0.65rem;">Création Rapide</small>
                    @if(auth()->user()->hasPermission('admin.companies.create'))
                    <a href="{{ route('admin.companies.create') }}" class="menu-link-quick {{ request()->routeIs('admin.companies.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Créer Entreprise</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('compta.create'))
                    <a href="{{ route('compta.create') }}" class="menu-link-quick {{ request()->routeIs('compta.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Créer Comptabilité</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('admin.admins.create'))
                    <a href="{{ route('admin.admins.create') }}" class="menu-link-quick {{ request()->routeIs('admin.admins.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Créer Administrateur</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('admin.secondary_admins.create'))
                    <a href="{{ route('admin.secondary_admins.create') }}" class="menu-link-quick {{ request()->routeIs('admin.secondary_admins.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-gear"></i>
                        <span>Créer Admin Sécondaire</span>
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('admin.users.create'))
                    <a href="{{ route('admin.users.create') }}" class="menu-link-quick {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Créer Comptable</span>
                    </a>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- SECTION 3 : OPÉRATIONS --}}
            @php
                $hasOperations = auth()->user()->hasPermission('admin.audit') || 
                                 auth()->user()->hasPermission('admin.access') || 
                                 auth()->user()->hasPermission('tasks.assign') ||
                                 auth()->user()->isAdmin();
            @endphp
            @if($hasOperations && !session('sidebar_admin_hidden', false))
            <div class="menu-section">
                <div class="menu-section-header">Opérations</div>
                @if(auth()->user()->hasPermission('admin.audit'))
                <a href="{{ route('admin.audit') }}" class="menu-link-new {{ request()->routeIs('admin.audit') ? 'active' : '' }}">
                    <i class="fa-solid fa-history"></i>
                    <span>Traçabilité & Activités</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('admin.access'))
                <a href="{{ route('admin.access') }}" class="menu-link-new {{ request()->routeIs('admin.access') ? 'active' : '' }}">
                    <i class="fa-solid fa-lock-open"></i>
                    <span>Contrôle d'Accès</span>
                </a>
                @endif

            </div>
            @endif

            {{-- SECTION 3 BIS : GESTION DES TÂCHES (Demande spécifique User) --}}
            @php
                $hasTasks = auth()->user()->hasPermission('tasks.assign') || 
                            auth()->user()->hasPermission('tasks.view_daily') ||
                            auth()->user()->hasPermission('admin.tasks.index'); // Legacy check just in case
            @endphp
            @if($hasTasks)
            <div class="menu-section">
                <div class="menu-section-header">Gestion des Tâches</div>
                
                @if(auth()->user()->hasPermission('tasks.assign') || auth()->user()->isAdmin())
                <a href="{{ route('admin.tasks.index') }}" class="menu-link-new {{ request()->routeIs('admin.tasks.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-pen"></i>
                    <span>Assigner Tâche</span>
                    @if(isset($tasksSentCount) && $tasksSentCount > 0)
                        <span class="badge bg-soft-primary text-primary rounded-pill ms-auto">{{ $tasksSentCount }}</span>
                    @endif
                </a>
                @endif
                
                @if(auth()->user()->hasPermission('tasks.view_daily'))
                <a href="{{ route('admin.tasks.daily') }}" class="menu-link-new {{ request()->routeIs('admin.tasks.daily') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Tâches Quotidiennes</span>
                    @if(isset($tasksReceivedCompletedCount) && $tasksReceivedCompletedCount > 0)
                        <span class="badge bg-soft-success text-success rounded-pill ms-auto">{{ $tasksReceivedCompletedCount }}</span>
                    @endif
                </a>
                @endif
            </div>
            @endif

            {{-- SECTION 4 : VALIDATION --}}
            @if(auth()->user()->hasPermission('admin.approvals'))
            <div class="menu-section">
                <div class="menu-section-header">Validation</div>
                <a href="{{ route('admin.approvals') }}" class="menu-link-new {{ request()->routeIs('admin.approvals') ? 'active' : '' }}">
                    <i class="fa-solid fa-stamp"></i>
                    <span>Approbations</span>
                    <span class="badge bg-soft-warning text-warning ms-auto">
                        {{ $pendingApprovalsCount }}
                    </span>
                </a>
            </div>
            @endif

        @if ($isComptaAccountActive && !auth()->user()->isSuperAdmin())
            {{-- MODE COMPTABILITÉ ACTIVE --}}

            {{-- Paramétrage --}}
            @php
                $showParametrage = auth()->user()->hasPermission('plan_comptable') || 
                                   auth()->user()->hasPermission('plan_tiers') || 
                                   auth()->user()->hasPermission('accounting_journals') || 
                                   auth()->user()->hasPermission('postetresorerie.index');
            @endphp
            @if ($showParametrage)
            <div class="menu-section">
                <div class="menu-section-header">Paramétrage</div>
                @if(auth()->user()->hasPermission('plan_comptable'))
                <a href="{{ route('plan_comptable') }}" class="menu-link-new {{ request()->routeIs('plan_comptable*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book"></i>
                    <span>Plan comptable</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('plan_tiers'))
                <a href="{{ route('plan_tiers') }}" class="menu-link-new {{ request()->routeIs('plan_tiers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Plan tiers</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('accounting_journals'))
                <a href="{{ route('accounting_journals') }}" class="menu-link-new {{ request()->routeIs('accounting_journals') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Journaux</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('postetresorerie.index'))
                <a href="{{ route('postetresorerie.index') }}" class="menu-link-new {{ request()->routeIs('postetresorerie.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Poste Trésorerie</span>
                </a>
                @endif
            </div>
            @endif

            {{-- Traitement --}}
            @php
                $showTraitement = auth()->user()->hasPermission('modal_saisie_direct') || 
                                  auth()->user()->hasPermission('accounting_entry_list') || 
                                  auth()->user()->hasPermission('brouillons.index') || 
                                  auth()->user()->hasPermission('immobilisations.index') || 
                                  auth()->user()->hasPermission('exercice_comptable');
            @endphp
            @if ($showTraitement)
            <div class="menu-section">
                <div class="menu-section-header">Traitement</div>
                @if(auth()->user()->hasPermission('modal_saisie_direct'))
                <a href="#" class="menu-link-new" data-bs-toggle="modal" data-bs-target="#saisieRedirectModal">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Nouvelle saisie</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('accounting_entry_list'))
                <a href="{{ route('accounting_entry_list') }}" class="menu-link-new {{ request()->routeIs('accounting_entry_list') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Liste des écritures</span>
                </a>
                <a href="{{ route('lettrage.index') }}" class="menu-link-new {{ request()->routeIs('lettrage.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-link"></i>
                    <span>Lettrage des Tiers</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('ecriture.rejected'))
                <a href="{{ route('ecriture.rejected') }}" class="menu-link-new {{ request()->routeIs('ecriture.rejected') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-circle-xmark"></i>
                    <span>Écritures rejetées</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('brouillons.index'))
                <a href="{{ route('brouillons.index') }}" class="menu-link-new {{ request()->routeIs('brouillons.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-pen"></i>
                    <span>Brouillons</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('exercice_comptable') && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                <a href="{{ route('exercice_comptable') }}" class="menu-link-new {{ request()->routeIs('exercice_comptable') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Exercice comptable</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('immobilisations.index'))
                <a href="{{ route('immobilisations.index') }}" class="menu-link-new {{ request()->routeIs('immobilisations.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-city"></i>
                    <span>Immobilisations</span>
                </a>
                @endif
            </div>
            @endif

            {{-- ANALYTIQUE --}}
            @php
                $showAnalytique = auth()->user()->hasPermission('analytique.axes.index') || 
                                  auth()->user()->hasPermission('analytique.sections.index') || 
                                  auth()->user()->hasPermission('analytique.regles.index') || 
                                  auth()->user()->hasPermission('analytique.balance');
            @endphp
            @if ($showAnalytique)
            <div class="menu-section">
                <div class="menu-section-header">Analytique</div>
                @if(auth()->user()->hasPermission('analytique.axes.index'))
                <a href="{{ route('analytique.axes.index') }}" class="menu-link-new {{ request()->routeIs('analytique.axes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Axes analytiques</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('analytique.sections.index'))
                <a href="{{ route('analytique.sections.index') }}" class="menu-link-new {{ request()->routeIs('analytique.sections.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shapes"></i>
                    <span>Sections analytiques</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('analytique.regles.index'))
                <a href="{{ route('analytique.regles.index') }}" class="menu-link-new {{ request()->routeIs('analytique.regles.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears"></i>
                    <span>Règles de ventilation</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('analytique.balance'))
                <a href="{{ route('analytique.balance') }}" class="menu-link-new {{ request()->routeIs('analytique.balance') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Balance analytique</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('analytique.grand_livre'))
                <a href="{{ route('analytique.grand_livre') }}" class="menu-link-new {{ request()->routeIs('analytique.grand_livre') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-contract"></i>
                    <span>Grand livre analytique</span>
                </a>
                @endif
            </div>
            @endif

            {{-- Rapports Comptables --}}
            @php
                $showRapports = auth()->user()->hasPermission('accounting_ledger') || 
                                auth()->user()->hasPermission('accounting_balance');
            @endphp
            @if ($showRapports)
            <div class="menu-section">
                <div class="menu-section-header">Rapports</div>
                @if(auth()->user()->hasPermission('accounting_ledger'))
                <a href="{{ route('accounting_ledger') }}" class="menu-link-new {{ request()->is('accounting_ledger') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Grand livre</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('accounting_balance'))
                <a href="{{ route('accounting_balance') }}" class="menu-link-new {{ request()->is('accounting_balance') ? 'active' : '' }}">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <span>Balance</span>
                </a>
                @endif
            </div>
            @endif

            {{-- États Financiers --}}
            @php
                $showEtatsFinanciers = auth()->user()->hasPermission('bilan') || 
                                       auth()->user()->hasPermission('compte_resultat');
            @endphp
            @if ($showEtatsFinanciers)
            <div class="menu-section">
                <div class="menu-section-header">ETATS FINANCIERS</div>
                @if(auth()->user()->hasPermission('bilan'))
                <a href="{{ route('reporting.bilan') }}" class="menu-link-new {{ request()->routeIs('reporting.bilan') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ol"></i>
                    <span>Bilan Actif/Passif</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('compte_resultat'))
                <a href="{{ route('reporting.resultat') }}" class="menu-link-new {{ request()->routeIs('reporting.resultat') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Compte de Résultat</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('compte_resultat'))
                <a href="{{ route('reporting.monthly_resultat') }}" class="menu-link-new {{ request()->routeIs('reporting.monthly_resultat') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-week"></i>
                    <span>Compte d'Exp. Mensuel</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('bilan'))
                <a href="{{ route('reporting.tft') }}" class="menu-link-new {{ request()->routeIs('reporting.tft') ? 'active' : '' }}">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <span>Flux de Trésorerie (TFT)</span>
                </a>
                @endif
                @if(auth()->user()->hasPermission('bilan'))
                <a href="{{ route('reporting.tft_personalized') }}" class="menu-link-new {{ request()->routeIs('reporting.tft_personalized') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>TFT Mensuel</span>
                </a>
                @endif
            </div>
            @endif


        @else
            {{-- MODE GOUVERNANCE (Pas de compte actif) --}}
            @if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
            <div class="menu-section">
                <div class="menu-section-header">Organisation</div>
                <a href="{{ route('compta_accounts.index') }}" class="menu-link-new {{ request()->routeIs('compta_accounts.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i>
                    <span>Mes Compagnies</span>
                </a>
                <a href="{{ route('admin.companies.create') }}" class="menu-link-new {{ request()->routeIs('admin.companies.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Créer Entreprise</span>
                </a>
                <a href="{{ route('user_management') }}" class="menu-link-new {{ request()->routeIs('user_management') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-group"></i>
                    <span>Équipe & Permissions</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-header">Support</div>
                <a href="#" class="menu-link-new text-muted" title="Bientôt disponible">
                    <i class="fa-solid fa-history"></i>
                    <span>Traçabilité & Activités</span>
                    <span class="badge bg-soft-primary text-primary ms-auto" style="font-size: 10px;">Pro</span>
                </a>
            </div>
            @endif

            @if (auth()->user()->isSuperAdmin())
                {{-- Garder l'existant pour Super Admin si non géré par superadmin_sidebar --}}
                <div class="menu-section">
                    <div class="menu-section-header">Paramétrage</div>
                    <a href="{{ route('plan_comptable') }}" class="menu-link-new">
                        <i class="fa-solid fa-book"></i>
                        <span>Plan comptable</span>
                    </a>
                </div>
            @endif
        @endif
    </nav>

    @auth
    <div class="sidebar-footer">
        <div class="flex items-center justify-between">
            <a href="#" class="user-profile-mini">
                <div class="user-avatar-sidebar">
                    {{ auth()->user()->initiales }}
                </div>
                <div class="user-info-sidebar">
                    <span class="user-name-sidebar">{{ auth()->user()->name }}</span>
                    <span class="user-role-sidebar">{{ auth()->user()->role }}</span>
                </div>
            </a>
            <a href="#" class="logout-btn-sidebar" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
    @endauth
</div>

<script>
// Intercepteur pour forcer le rechargement complet après changement d'exercice
document.addEventListener('DOMContentLoaded', function() {
    const exerciceSwitchLinks = document.querySelectorAll('[data-exercice-switch]');
    
    exerciceSwitchLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            
            // Afficher un indicateur de chargement
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Changement en cours...';
            this.style.pointerEvents = 'none';
            
            // Faire la requête de switch
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                // Recharger complètement la page pour appliquer le nouveau contexte
                window.location.reload();
            })
            .catch(error => {
                console.error('Erreur lors du changement d\'exercice:', error);
                // Fallback: redirection classique
                window.location.href = url;
            });
        });
    });
});
</script>
@endif

<!-- Ancien sidebar (supprimé car doublon inutile) -->

