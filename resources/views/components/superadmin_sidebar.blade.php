{{-- Le sidebar superadmin utilise les mêmes styles que le sidebar utilisateur définis dans sidebar.blade.php --}}

<div class="sidebar-new">
    <div class="sidebar-header">
        <div class="brand-container">
            <div class="brand-logo">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div class="brand-text">
                <h1 class="brand-title">Flow Compta</h1>
                <small class="brand-subtitle">Super Admin</small>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="menu-section">
            <a href="{{ route('superadmin.dashboard') }}" class="menu-link-new {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-header">Gouvernance</div>
            <a href="{{ route('superadmin.entities') }}" class="menu-link-new {{ request()->routeIs('superadmin.entities') ? 'active' : '' }}">
                <i class="fa-solid fa-building"></i>
                <span>Gestion des Entités</span>
            </a>
            <a href="{{ route('superadmin.companies.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.companies.create') ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Créer Entreprise</span>
            </a>
            <a href="{{ route('superadmin.accounting.create') }}" class="menu-link-new {{ request()->routeIs('superadmin.accounting.create') ? 'active' : '' }}">
                <i class="fa-solid fa-calculator"></i>
                <span>Créer Comptabilité</span>
            </a>
            <a href="{{ route('superadmin.users') }}" class="menu-link-new {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-cog"></i>
                <span>Admin. Utilisateurs</span>
            </a>
            <a href="{{ route('superadmin.switch') }}" class="menu-link-new {{ request()->routeIs('superadmin.switch*') ? 'active' : '' }}">
                <i class="fa-solid fa-exchange-alt"></i>
                <span>Switch Entreprise</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-header">Opérations</div>
            <a href="{{ route('superadmin.activities') }}" class="menu-link-new {{ request()->routeIs('superadmin.activities') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check"></i>
                <span>Suivi des Activités</span>
            </a>
            <a href="{{ route('superadmin.access') }}" class="menu-link-new {{ request()->routeIs('superadmin.access*') ? 'active' : '' }}">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Contrôle d'Accès</span>
            </a>
            <a href="{{ route('pricing.show') }}" class="menu-link-new {{ request()->routeIs('pricing.show') ? 'active' : '' }}">
                <i class="fa-solid fa-sliders"></i>
                <span>Paramétrage Système</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-header">Analyses</div>
            <a href="{{ route('superadmin.reports') }}" class="menu-link-new {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Rapports Performance</span>
            </a>
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
