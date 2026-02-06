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
        {{-- SECTION 1 : PILOTAGE --}}
        <div class="menu-section">
            <div class="menu-section-header">Pilotage</div>
            
            @if(auth()->user()->hasPermission('superadmin.dashboard'))
            <a href="{{ route('superadmin.dashboard') }}" class="menu-link-new {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-rocket"></i>
                <span>Tableau de bord SuperAdmin</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('superadmin.activities'))
            <a href="{{ route('superadmin.activities') }}" class="menu-link-new {{ request()->routeIs('superadmin.activities') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Suivi des Activités</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('superadmin.reports'))
            <a href="{{ route('superadmin.reports') }}" class="menu-link-new {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Rapports Performance</span>
            </a>
            @endif

            <a href="{{ route('notifications.index') }}" class="menu-link-new {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell"></i>
                <span>Notifications</span>
                @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $unreadNotificationsCount }}</span>
                @endif
            </a>
        </div>

        {{-- SECTION NOUVELLE : ADMINISTRATION INTERNE --}}
        @if(auth()->user()->hasPermission('superadmin.secondary.index'))
        <div class="menu-section">
            <div class="menu-section-header">Administration Interne</div>
            
            <a href="{{ route('superadmin.secondary.index') }}" class="menu-link-new {{ request()->is('superadmin/secondary-admins') ? 'active' : '' }}">
                <i class="fa-solid fa-user-tie"></i>
                <span>Super Admins Secondaires</span>
            </a>

            <a href="{{ route('superadmin.secondary.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.secondary.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear"></i>
                <span>Créer super admin secondaire</span>
            </a>
        </div>
        @endif

        {{-- SECTION 2 : GOUVERNANCE --}}
        <div class="menu-section">
            <div class="menu-section-header">Gouvernance</div>
            
            @if(auth()->user()->hasPermission('superadmin.entities'))
            <a href="{{ route('superadmin.entities') }}" class="menu-link-new {{ request()->routeIs('superadmin.entities', 'superadmin.companies.*') ? 'active' : '' }}">
                <i class="fa-solid fa-sitemap"></i>
                <span>Gestion des Entités</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('superadmin.users'))
            <a href="{{ route('superadmin.users') }}" class="menu-link-new {{ request()->routeIs('superadmin.users', 'superadmin.users.*', 'superadmin.admins.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield"></i>
                <span>Gestion Utilisateurs</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('superadmin.accounting.index'))
            <a href="{{ route('superadmin.accounting.index') }}" class="menu-link-new {{ request()->routeIs('superadmin.accounting.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calculator"></i>
                <span>Gestion comptabilités</span>
            </a>
            @endif


            {{-- SECTION : CRÉATION RAPIDE --}}
            <div class="menu-section-header mt-4">Création Rapide</div>

            <a href="{{ route('superadmin.companies.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.companies.create') ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Créer Entreprise</span>
            </a>


            <a href="{{ route('superadmin.admins.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.admins.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-shield"></i>
                <span>Créer Administrateur</span>
            </a>

            <a href="{{ route('admin.secondary_admins.create') }}" class="menu-link-new {{ request()->routeIs('admin.secondary_admins.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-check"></i>
                <span>Créer administrateur secondaire</span>
            </a>

            <a href="{{ route('superadmin.accounting.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.accounting.create') ? 'active' : '' }}">
                <i class="fa-solid fa-calculator"></i>
                <span>Créer comptabilité</span>
            </a>

            <a href="{{ route('superadmin.users.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.users.create') ? 'active' : '' }}">
                <i class="fa-solid fa-user-plus"></i>
                <span>Créer comptable</span>
            </a>

            <a href="{{ route('superadmin.habilitations.index') }}" class="menu-link-new {{ request()->routeIs('superadmin.habilitations.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-check"></i>
                <span>Modification Habilitation</span>
            </a>

            @if(auth()->user()->hasPermission('superadmin.access'))
            <a href="{{ route('superadmin.access') }}" class="menu-link-new {{ request()->routeIs('superadmin.access*') ? 'active' : '' }}">
                <i class="fa-solid fa-lock"></i>
                <span>Contrôle d'Accès</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('superadmin.switch'))
            <a href="{{ route('superadmin.switch') }}" class="menu-link-new {{ request()->routeIs('superadmin.switch*') ? 'active' : '' }}">
                <i class="fa-solid fa-repeat"></i>
                <span>Switch Entreprise</span>
            </a>
            @endif
        </div>

        {{-- SECTION 3 : OPÉRATIONS --}}
        <div class="menu-section">
            <div class="menu-section-header">Opérations</div>
            
            {{-- Tâches Administratives --}}
            @if(auth()->user()->hasPermission('superadmin.tasks.index'))
            <a href="{{ route('superadmin.tasks.index') }}" class="menu-link-new {{ request()->routeIs('superadmin.tasks.*') ? 'active' : '' }}">
                <i class="fa-solid fa-tasks"></i>
                <span>Tâches Administratives</span>
                @if(isset($tasksSentCount) && $tasksSentCount > 0)
                    <span class="badge bg-soft-primary text-primary rounded-pill ms-auto">{{ $tasksSentCount }}</span>
                @endif
            </a>
            @endif
        </div>
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
