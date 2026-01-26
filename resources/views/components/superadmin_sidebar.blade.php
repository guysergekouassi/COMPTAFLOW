{{-- Le sidebar superadmin utilise les mêmes styles que le sidebar utilisateur définis dans sidebar.blade.php --}}

<div class="sidebar-new">
    <div class="sidebar-header">
        <div class="brand-container">
            <div class="brand-logo">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div class="brand-text">
                <h1 class="brand-title">Flow Compta</h1>
                <div class="role-badge-sidebar">Super Admin</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="menu-section">
            @if(auth()->user()->hasPermission('superadmin.dashboard'))
            <a href="{{ route('superadmin.dashboard') }}" class="menu-link-new {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-home"></i>
                <span>Tableau de bord SuperAdmin</span>
            </a>
            @endif
        </div>

        @php
            $showGouvernance = auth()->user()->hasPermission('superadmin.entities') || 
                               auth()->user()->hasPermission('superadmin.companies.create') || 
                               auth()->user()->hasPermission('superadmin.accounting.create') || 
                               auth()->user()->hasPermission('superadmin.users') || 
                               auth()->user()->hasPermission('superadmin.users.create') || 
                               auth()->user()->hasPermission('superadmin.admins.create') || 
                               auth()->user()->hasPermission('superadmin.switch');
        @endphp
        @if($showGouvernance)
        <div class="menu-section">
            <div class="menu-section-header">Gouvernance</div>
            @if(auth()->user()->hasPermission('superadmin.entities'))
            <a href="{{ route('superadmin.entities') }}" class="menu-link-new {{ request()->routeIs('superadmin.entities') ? 'active' : '' }}">
                <i class="fa-solid fa-building"></i>
                <span>Gestion des Entités</span>
            </a>
            @endif
            @if(auth()->user()->hasPermission('superadmin.companies.create'))
            <a href="{{ route('superadmin.companies.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.companies.create') ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Créer Entreprise</span>
            </a>
            @endif
            @if(auth()->user()->hasPermission('superadmin.accounting.create'))
            <a href="{{ route('superadmin.accounting.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.accounting.create') ? 'active' : '' }}">
                <i class="fa-solid fa-calculator"></i>
                <span>Créer Comptabilité</span>
            </a>
            @endif
            @if(auth()->user()->hasPermission('superadmin.users'))
            <a href="{{ route('superadmin.users') }}" class="menu-link-new {{ request()->routeIs('superadmin.users') ? 'active' : '' }}">
                <i class="fa-solid fa-users-cog"></i>
                <span>Gestion Utilisateurs</span>
            </a>
            @endif
            @if(auth()->user()->hasPermission('superadmin.users.create'))
            <a href="{{ route('superadmin.users.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.users.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-plus"></i>
                <span>Créer Utilisateur</span>
            </a>
            @endif
            @if(auth()->user()->hasPermission('superadmin.admins.create'))
            <a href="{{ route('superadmin.admins.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.admins.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield"></i>
                <span>Créer Administrateur</span>
            </a>
            @endif
            <a href="{{ route('admin.habilitations.index') }}" class="menu-link-new {{ request()->routeIs('admin.habilitations.index') ? 'active' : '' }}">
                <i class="fa-solid fa-user-check"></i>
                <span>Modification Habilitation</span>
            </a>
            @if(auth()->user()->hasPermission('superadmin.switch'))
            <a href="{{ route('superadmin.switch') }}" class="menu-link-new {{ request()->routeIs('superadmin.switch*') ? 'active' : '' }}">
                <i class="fa-solid fa-exchange-alt"></i>
                <span>Switch Entreprise</span>
            </a>
            @endif
        </div>
        @endif

        @php
            $showOperations = auth()->user()->hasPermission('superadmin.activities') || 
                              auth()->user()->hasPermission('superadmin.access') || 
                              auth()->user()->hasPermission('pricing.show');
        @endphp
        @if($showOperations)
        <div class="menu-section">
            <div class="menu-section-header">Opérations</div>
            @if(auth()->user()->hasPermission('superadmin.activities'))
            <a href="{{ route('superadmin.activities') }}" class="menu-link-new {{ request()->routeIs('superadmin.activities') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check"></i>
                <span>Suivi des Activités</span>
            </a>
            @endif
            <a href="{{ route('admin.tasks.index') }}" class="menu-link-new {{ request()->routeIs('admin.tasks.index') ? 'active' : '' }}">
                <i class="fa-solid fa-file-pen"></i>
                <span>Assigner Tâche</span>
            </a>
            @if(auth()->user()->hasPermission('superadmin.access'))
            <a href="{{ route('superadmin.access') }}" class="menu-link-new {{ request()->routeIs('superadmin.access*') ? 'active' : '' }}">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Contrôle d'Accès</span>
            </a>
            @endif
        </div>
        @endif

        <div class="menu-section">
            <div class="menu-section-header">Configuration/Importation</div>
            <a href="{{ route('admin.import.hub') }}" class="menu-link-new {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-import"></i>
                <span>Tunnel d'Importation</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-header">Exportation</div>
            <a href="{{ route('admin.export.hub') }}" class="menu-link-new {{ request()->routeIs('admin.export.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-export"></i>
                <span>Exportation de données</span>
            </a>
        </div>

        @if(auth()->user()->hasPermission('superadmin.reports'))
        <div class="menu-section">
            <div class="menu-section-header">Analyses</div>
            <a href="{{ route('superadmin.reports') }}" class="menu-link-new {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Rapports Performance</span>
            </a>
        </div>
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
                    <span class="user-role-sidebar">Super Admin</span>
                </div>
            </a>
            <a href="#" class="logout-btn-sidebar" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
    @endauth
</div>
