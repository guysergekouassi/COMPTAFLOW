<style>
    .dropdown-divider {
        height: 1px;
        background: #e5e7eb;
        margin: 8px 0;
    }

    .user-init-circle-wrapper {
        position: relative;
        cursor: pointer;
    }

    .user-init-circle {
        width: 42px;
        height: 42px;
        background: #1e40af;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 2px 8px rgba(30, 64, 175, 0.15);
        transition: all 0.2s ease;
        border: 2px solid white;
    }

    .online-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        background: #10b981;
        border: 2px solid white;
        border-radius: 50%;
        z-index: 10;
    }

    .user-init-circle:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);
    }

    .global-header-minimal {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 40px;
        position: relative;
        z-index: 1001;
    }

    .header-dynamic-title {
        white-space: nowrap;
        flex-grow: 1;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        display: inline-block !important;
    }

    .header-dynamic-title h1 {
        font-size: 2.5rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        margin: 0;
        color: #0f172a;
    }

    /* Ajuster le contenu principal */
    .layout-page {
        margin-left: 288px !important;
        width: calc(100vw - 288px) !important;
        min-height: 100vh;
        max-width: calc(100vw - 288px) !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }

    /* Ajuster le content-wrapper pour occuper 100% de l'espace */
    .content-wrapper {
        margin-left: 0 !important;
        padding: 20px !important;
        width: 100% !important;
        min-height: 100vh;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /* Assurer que le layout-wrapper occupe 100% */
    .layout-wrapper {
        width: 100vw !important;
        min-height: 100vh;
        max-width: 100vw !important;
        overflow-x: hidden !important;
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    /* Assurer que le layout-container occupe 100% */
    .layout-container {
        width: 100vw !important;
        min-height: 100vh;
        max-width: 100vw !important;
        overflow-x: hidden !important;
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }

    /* Assurer que le body occupe 100% */
    body {
        width: 100vw !important;
        min-height: 100vh;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    /* Assurer que html occupe 100% */
    html {
        width: 100vw !important;
        min-height: 100vh;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }

    /* Forcer les conteneurs à utiliser tout l'espace */
    .container-xxl, .container-xl, .container-lg, .container-md, .container-sm {
        max-width: none !important;
        width: 100% !important;
        padding-left: 20px !important;
        padding-right: 20px !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Forcer le contenu à prendre toute la largeur */
    .content-wrapper > *, .content-wrapper > div > *, .content-wrapper > div > div > * {
        max-width: 100% !important;
        width: 100% !important;
    }

    /* CORRECTION SPÉCIFIQUE POUR LES CARTES KPI - TRÈS SPÉCIFIQUE */
    .content-wrapper .row.g-4 .col-lg-3 .card,
    .content-wrapper .row.g-4 .col-md-6 .card,
    .content-wrapper .row.g-4 .col-sm-6 .card,
    .content-wrapper .row .col-lg-3 .card,
    .content-wrapper .row .col-md-6 .card,
    .content-wrapper .row .col-sm-6 .card {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 0 !important;
        flex: 1 1 auto !important;
        display: flex !important;
        flex-direction: column !important;
    }

    /* Forcer les grilles à être correctes */
    .content-wrapper .row.g-4,
    .content-wrapper .row.g-3,
    .content-wrapper .row.g-2,
    .content-wrapper .row {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-left: -0.75rem !important;
        margin-right: -0.75rem !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Forcer les colonnes à avoir les bonnes tailles */
    .content-wrapper .row .col-lg-3 {
        flex: 0 0 25% !important;
        max-width: 25% !important;
        width: 25% !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .content-wrapper .row .col-md-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .content-wrapper .row .col-sm-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
        width: 50% !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .content-wrapper .row .col-12 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    /* Forcer tous les card-body à prendre toute la largeur */
    .card-body {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 auto !important;
    }

    /* CORRECTION SPÉCIFIQUE POUR LES FORMULAIRES */
    /* Centrer les formulaires de balance et grand livre */
    .modal-content,
    .modal-dialog {
        max-width: 600px !important;
        width: 600px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        text-align: left !important;
    }

    /* CORRECTION PLUS SPÉCIFIQUE POUR GRAND LIVRE ET BALANCE */
    /* Forcer UNIQUEMENT les modaux spécifiques à être centrés */
    .content-wrapper .balance-modal,
    .content-wrapper .ledger-modal,
    .content-wrapper .grand-livre-modal {
        max-width: 600px !important;
        width: 600px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        text-align: left !important;
        float: none !important;
        position: relative !important;
        left: 0 !important;
        right: 0 !important;
    }

    /* NE PAS APPLIQUER À TOUTES LES CARTES - seulement aux formulaires spécifiques */

    /* Corriger le formulaire de Journal Trésorerie */
    .content-wrapper .card.form-journal-tresorerie,
    .content-wrapper .card.tresorerie-form,
    .content-wrapper .form-tresorerie .card {
        max-width: 800px !important;
        width: 800px !important;
        margin-left: auto !important;
        margin-right: auto !important;
        float: none !important;
    }

    /* Centrer tous les formulaires dans les pages */
    .content-wrapper .form-container,
    .content-wrapper .card.form-card,
    .content-wrapper .form-section {
        max-width: 800px !important;
        width: 800px !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    /* Supprimer les marges et paddings indésirables */
    * {
        box-sizing: border-box !important;
    }

    /* CORRECTION POUR LES FILTRES QUI LAISSENT LEUR ESPACE VIDE */
    /* Forcer les filtres cachés à ne pas prendre d'espace */
    .filter-section:not(.show),
    .filter-panel:not(.show),
    .filter-container:not(.show),
    .filter-collapse:not(.show),
    .collapse:not(.show) {
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
    }

    /* Forcer les filtres visibles à prendre leur espace normal */
    .filter-section.show,
    .filter-panel.show,
    .filter-container.show,
    .filter-collapse.show,
    .collapse.show {
        display: block !important;
        height: auto !important;
        visibility: visible !important;
        overflow: visible !important;
    }

    /* Dropdown user styles */
    .user-profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 12px;
        width: 220px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        padding: 8px;
        display: none;
        z-index: 1100;
    }

    .user-profile-dropdown.show {
        display: block;
        animation: slideIn 0.2s ease-out;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dropdown-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        color: #374151;
        text-decoration: none !important;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .dropdown-link:hover {
        background: #f9fafb;
        color: #1e40af;
    }

    .dropdown-link.logout {
        color: #dc2626 !important;
    }

    .dropdown-link.logout:hover {
        background: #fef2f2;
    }

    .dropdown-link i {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    /* Switch Mode Banner */
    .switch-mode-banner {
        background: linear-gradient(135deg, #1e1b4b 0%, #1e40af 100%);
        color: #f1f5f9;
        padding: 10px 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 500;
        font-size: 14px;
        border-bottom: 2px solid #3b82f6;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1002;
        position: sticky;
        top: 0;
    }

    .switch-mode-badge {
        background: #3b82f6;
        color: white;
        padding: 4px 12px;
        border-radius: 6px;
        margin-right: 15px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .btn-return-admin {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 6px 18px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        display: flex;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(4px);
    }

    .btn-return-admin:hover {
        background: white;
        color: #1e1b4b;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255,255,255,0.2);
    }
</style>

@php
    $isSuperAdminSwitch = session('original_super_admin_id');
    $isAdminImpersonation = session('original_admin_id');
    $isContextSwitch = session('current_company_id') && auth()->user()->company_id != session('current_company_id');
    
    $showBanner = $isSuperAdminSwitch || $isAdminImpersonation || $isContextSwitch;
    
    // Déterminer la route de retour
    $returnRoute = '#'; // Valeur par défaut sûre
    
    if ($isSuperAdminSwitch) {
        $returnRoute = route('superadmin.switch.return');
    } elseif ($isAdminImpersonation) {
        $returnRoute = route('admin.leave_impersonation');
    } elseif ($isContextSwitch) {
        $returnRoute = route('admin.context.reset'); 
    }
@endphp

@if($showBanner)
    <div class="switch-mode-banner">
        <div class="d-flex align-items-center">
            <span class="switch-mode-badge">Mode Switch Actif</span>
            <span class="text-white d-flex align-items-center gap-2">
                Connecté en tant que : <strong>{{ auth()->user()->name }}</strong>
                
                <span class="mx-2 opacity-50">|</span>
                <span class="fw-bold">{{ strtoupper(auth()->user()->role === 'comptable' ? 'Comptable' : (auth()->user()->role === 'super_admin' ? 'Super Admin' : auth()->user()->role)) }}</span>

                @php 
                    $switchedId = session('current_company_id') ?? session('switched_company_id');
                    $switchedCompany = $switchedId ? \App\Models\Company::find($switchedId) : null; 
                @endphp

                @if($switchedCompany)
                    <span class="mx-2 opacity-50">|</span>
                    <i class="fa-solid fa-building opacity-50 small"></i>
                    <span><strong>{{ $switchedCompany->company_name }}</strong></span>
                @endif
            </span>
        </div>
        
        <form action="{{ $returnRoute }}" method="{{ $isSuperAdminSwitch ? 'POST' : 'GET' }}" class="m-0">
            @if($isSuperAdminSwitch) @csrf @endif
            <button type="submit" class="btn-return-admin">
                <i class="fa-solid fa-arrow-left"></i>
                Quitter le mode switch
            </button>
        </form>
    </div>
@endif

<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base bx bx-menu icon-md"></i>
    </a>
</div>

<div class="global-header-minimal">
    <div class="header-dynamic-title">
        <h1 class="text-slate-900 font-extrabold tracking-tight m-0">
            {!! $page_title ?? '' !!}
        </h1>
        @if(isset($company_name))
            <span class="text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 px-3 py-1 rounded-full">
                {{ $company_name }}
            </span>
        @endif
    </div>
    
    <div class="flex items-center gap-4">
        @auth
        <!-- Notification Bell -->
        <a href="{{ route('notifications.index') }}" class="position-relative me-3 text-slate-600 hover:text-blue-600 transition-colors" title="Notifications">
            <i class="fa-solid fa-bell fs-4"></i>
            <span id="unreadNotificationsCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem; display: none;">0</span>
        </a>

        <div class="user-init-circle-wrapper" onclick="toggleUserMenu(event)">
            <div class="user-init-circle" title="{{ auth()->user()->name }}">
                {{ auth()->user()->initiales }}
            </div>
            <span class="online-indicator"></span>
            
            <div id="globalUserDropdown" class="user-profile-dropdown">
                <div class="px-4 py-2 border-b border-slate-50 mb-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0">Compte</p>
                    <p class="text-sm font-semibold text-slate-700 truncate mb-0">{{ auth()->user()->name }}</p>
                </div>
                <a href="{{ route('notifications.index') }}" class="dropdown-link">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="{{ route('user.profile') }}" class="dropdown-link">
                    <i class="fa-solid fa-user"></i>
                    <span>Mon profil</span>
                </a>
                <a href="{{ route('user.settings') }}" class="dropdown-link">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Paramètres</span>
                </a>
                <a href="{{ route('guide.index') }}" class="dropdown-link">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Guide d'utilisation</span>
                </a>

                @php
                    $canSeeAdminOptions = auth()->user()->isAdmin() || 
                                          auth()->user()->isSuperAdmin() ||
                                          auth()->user()->hasPermission('admin.config.hub') || 
                                          auth()->user()->hasPermission('user_management');
                    $isAdminHidden = session('sidebar_admin_hidden', false);
                @endphp

                @if($canSeeAdminOptions)
                <a href="javascript:void(0)" class="dropdown-link" onclick="toggleSidebarSection('admin')">
                    <i class="fa-solid {{ $isAdminHidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                    <span>{{ $isAdminHidden ? 'Afficher les options' : 'Masquer les options' }}</span>
                </a>
                @endif

                <div class="h-px bg-slate-100 my-1"></div>
                <a href="#" class="dropdown-link logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
        @endauth
    </div>
</div>

<div style="display: none;">
    @auth
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    @endauth
</div>

<script>
function toggleUserMenu(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('globalUserDropdown');
    menu.classList.toggle('show');
}

// Fermer le menu quand on clique ailleurs
document.addEventListener('click', function(event) {
    const menu = document.getElementById('globalUserDropdown');
    if (menu && menu.classList.contains('show')) {
        menu.classList.remove('show');
    }
});

function toggleSidebarSection(section) {
    fetch("{{ route('ui.toggle_sidebar') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ section: section })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Charger le compteur de notifications au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    fetch("{{ route('api.notifications.unread_count') }}")
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('unreadNotificationsCount');
            if (data.count > 0) {
                badge.innerText = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
});
</script>

<!-- Ancien header (caché) -->
<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" style="display: none;" id="layout-navbar">
</nav>
