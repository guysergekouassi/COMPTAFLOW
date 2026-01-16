<style>
    /* Dark Sidebar Theme for Super Admin - Using same base structure */
    .sidebar-new.sidebar-governance {
        background-color: #1e293b; /* Slate 800 */
        color: #e2e8f0;
    }

    .sidebar-new.sidebar-governance .sidebar-header {
        background-color: #0f172a; /* Slate 900 */
        border-bottom-color: #334155;
    }

    .sidebar-new.sidebar-governance .brand-logo {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .sidebar-new.sidebar-governance .brand-title {
        color: white;
    }

    .sidebar-new.sidebar-governance .brand-text small {
        color: #94a3b8;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    .sidebar-new.sidebar-governance .menu-section-header {
        color: #64748b;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 700;
        padding: 1.5rem 1.5rem 0.5rem;
    }

    .sidebar-new.sidebar-governance .menu-link-new {
        color: #cbd5e1;
        border-left: 3px solid transparent;
    }

    .sidebar-new.sidebar-governance .menu-link-new:hover {
        background-color: #334155;
        color: white;
    }

    .sidebar-new.sidebar-governance .menu-link-new.active {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.1) 0%, transparent 100%);
        color: #60a5fa;
        border-left-color: #60a5fa;
    }

    .sidebar-new.sidebar-governance .sidebar-footer {
        border-top-color: #334155;
        background-color: #0f172a;
    }
    
    .sidebar-new.sidebar-governance .user-avatar-sidebar {
        background-color: #334155;
    }

    .sidebar-new.sidebar-governance .user-name-sidebar {
        color: white;
    }

    .sidebar-new.sidebar-governance .user-role-sidebar {
        color: #94a3b8;
    }

    .sidebar-new.sidebar-governance .logout-btn-sidebar {
        color: #94a3b8;
    }

    .sidebar-new.sidebar-governance .logout-btn-sidebar:hover {
        color: white;
    }
</style>

<div class="sidebar-new sidebar-governance">
    <div class="sidebar-header">
        <div class="brand-container">
            <div class="brand-logo">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div class="brand-text">
                <h1 class="brand-title">Flow Compta</h1>
                <small>Mode Gouvernance</small>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        
        <!-- VUE GLOBALE -->
        <div class="menu-section-header">Vue Globale</div>
        <a href="{{ route('superadmin.dashboard') }}" class="menu-link-new {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard Master</span>
        </a>

        <!-- GOUVERNANCE -->
        <div class="menu-section-header">Gouvernance</div>
        <a href="{{ route('superadmin.entities') }}" class="menu-link-new {{ request()->routeIs('superadmin.entities') ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns"></i>
            <span>Gestion des Entités</span>
        </a>
        <a href="#" class="menu-link-new">
            <i class="fa-solid fa-users-gear"></i>
            <span>Admin. Utilisateurs</span>
        </a>

        <!-- OPERATIONS -->
        <div class="menu-section-header">Opérations</div>
        <a href="#" class="menu-link-new">
            <i class="fa-solid fa-list-check"></i>
            <span>Suivi des Activités</span>
        </a>
        <a href="{{ route('pricing.show') }}" class="menu-link-new {{ request()->routeIs('pricing.show') ? 'active' : '' }}">
            <i class="fa-solid fa-sliders"></i>
            <span>Paramétrage Système</span>
        </a>

        <!-- ANALYSES -->
        <div class="menu-section-header">Analyses</div>
        <a href="#" class="menu-link-new">
            <i class="fa-solid fa-arrow-trend-up"></i>
            <span>Rapports Performance</span>
        </a>

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
