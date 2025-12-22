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
        justify-content: flex-end;
        padding: 20px 40px;
        background: transparent;
        position: relative;
        z-index: 1001;
    }

    .header-dynamic-title {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        white-space: nowrap;
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
</style>
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
    </div>
    
    <div class="flex items-center gap-4">
        @auth
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
                <a href="#" class="dropdown-link">
                    <i class="fa-solid fa-user"></i>
                    <span>Mon profil</span>
                </a>
                <a href="#" class="dropdown-link">
                    <i class="fa-solid fa-user-gear"></i>
                    <span>Paramètres</span>
                </a>
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
</script>

<!-- Ancien header (caché) -->
<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" style="display: none;" id="layout-navbar">
</nav>
