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
        color: #6c757d;
        margin-top: -3px;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    /* Cacher l'ancien design */
    .layout-menu {
        display: none;
    }
</style>

@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\ExerciceComptable;
    use App\Models\JournalSaisi;
    use App\Models\CodeJournal;
    use App\Models\Company;

    $user = Auth::user();
    $currentCompanyId = session('current_company_id', $user->company_id);
    $currentCompany = Company::find($currentCompanyId);

    // Vérifie si un compte comptable est actif
    $isComptaAccountActive = session('plan_comptable', true) && session('current_compta_account_id', true);

    $show_all = $user->isAdmin() && !$user->isSuperAdmin();

    // Récupération unique par intitulé pour éviter les doublons visuels
    $exercices = ExerciceComptable::where('company_id', $currentCompanyId)
        ->orderBy('date_debut', 'desc')
        ->get()
        ->unique(function ($item) {
            return trim($item->intitule);
        });
    $journaux = JournalSaisi::with('codeJournal')
        ->where('company_id', $currentCompanyId)
        ->orderBy('mois', 'asc')
        ->get();
    $code_journaux = CodeJournal::where('company_id', $currentCompanyId)->get()->unique('code_journal');

    // Récupérer l'exercice actif (non clôturé) pour pré-sélection
    $exerciceActif = ExerciceComptable::where('company_id', $currentCompanyId)
        ->where('cloturer', 0)
        ->orderBy('date_debut', 'desc')
        ->first();

    $companies = Company::where('id', $user->company_id)->get();

    // 3. Récupération des compagnies pour le menu de bascule
if ($user->role === 'super_admin') {
        $companies_for_switch = Company::all();
     } elseif ($user->role === 'admin') {
        // Un Admin voit sa compagnie principale ET toutes les sous-compagnies qu'il a créées
        $companies_for_switch = Company::where('id', $user->company_id)
                                    ->orWhere('parent_company_id', $user->company_id)
                                    ->get();
     } else {
         // Les autres utilisateurs voient seulement leur compagnie rattachée
        $companies_for_switch = Company::where('id', $user->company_id)->get();
     }

     $companies = $companies_for_switch;
@endphp

@include('components.modal_saisie_direct', [
    'exercices' => $exercices,
    // 'journaux' => $journaux,
    'code_journaux' => $code_journaux,
    'companies' => $companies,
    'exerciceActif' => $exerciceActif,
])




<!-- Nouveau Sidebar Design -->
<div class="sidebar-new">
    <div class="sidebar-header">
        <div class="brand-container">
            <div class="brand-logo">
                <i class="fa-solid fa-calculator"></i>
            </div>
            <div class="brand-text">
                <h1 class="brand-title">Flow Compta</h1>
                @if ($currentCompany)
                    <small class="company-name-sidebar fw-semibold">
                        {{ $currentCompany->company_name }}
                    </small>
                @else
                    <small class="text-muted fw-semibold">Super Admin</small>
                @endif
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="menu-section">
            <a href="{{ $isComptaAccountActive && !auth()->user()->isSuperAdmin() ? route('compta.dashboard') : route('admin.dashboard') }}" class="menu-link-new {{ ($isComptaAccountActive && !auth()->user()->isSuperAdmin() && request()->routeIs('compta.dashboard')) || (!$isComptaAccountActive || auth()->user()->isSuperAdmin()) && request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </div>

        @if ($isComptaAccountActive && !auth()->user()->isSuperAdmin())
            {{-- Section Paramétrage --}}
            @php
                $parametrage_permissions = ['plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie','postetresorerie.index'];
                $show_parametrage_header = $show_all || count(array_intersect($parametrage_permissions, $habilitations)) > 0;
            @endphp

            @if ($show_parametrage_header)
            <div class="menu-section">
                <div class="menu-section-header">Paramétrage</div>
                @if($show_all || in_array('plan_comptable', $habilitations))
                <a href="{{ route('plan_comptable') }}" class="menu-link-new {{ request()->routeIs('plan_comptable*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book"></i>
                    <span>Plan comptable</span>
                </a>
                @endif
                @if($show_all || in_array('plan_tiers', $habilitations))
                <a href="{{ route('plan_tiers') }}" class="menu-link-new {{ request()->routeIs('plan_tiers*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Plan tiers</span>
                </a>
                @endif
                @if($show_all || in_array('journaux', $habilitations))
                <a href="{{ route('accounting_journals') }}" class="menu-link-new {{ request()->routeIs('accounting_journals') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Journaux</span>
                </a>
                @endif
                @if($show_all || in_array('tresorerie', $habilitations))
                <a href="{{ route('indextresorerie') }}" class="menu-link-new {{ request()->routeIs('indextresorerie') ? 'active' : '' }}">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Journal Trésorerie</span>
                </a>
                @endif
                @if($show_all || in_array('tresorerie', $habilitations))
                <a href="{{ route('postetresorerie.index') }}" class="menu-link-new {{ request()->routeIs('postetresorerie.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Poste Trésorerie</span>
                </a>
                @endif
            </div>
            @endif

            {{-- Section Traitement --}}
            @php
                $traitement_permissions = [
                    'nouvelle_saisie', 'exercice_comptable', 'rapprochement', 'gestion_tresorerie',
                    'gestion_comptes', 'gestion_tiers', 'gestion_analytique', 'gestion_immobilisations',
                    'gestion_stocks', 'gestion_reportings'
                ];
                $show_traitement_header = $show_all || count(array_intersect($traitement_permissions, $habilitations)) > 0;
            @endphp

            @if ($show_traitement_header)
            <div class="menu-section">
                <div class="menu-section-header">Traitement</div>
                @if($show_all || in_array('modal_saisie_direct', $habilitations))
                <a href="{{ route('modal_saisie_direct') }}" class="menu-link-new {{ request()->routeIs('modal_saisie_direct') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#saisieRedirectModal">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Nouvelle saisie</span>
                </a>
                @endif

                <!-- Afficher le bouton Liste des écritures si l'utilisateur a modal_saisie_direct -->
                @if($show_all || in_array('modal_saisie_direct', $habilitations))
                <a href="{{ route('accounting_entry_list') }}" class="menu-link-new {{ request()->routeIs('accounting_entry_list') ? 'active' : '' }}">
                    <i class="fa-solid fa-list"></i>
                    <span>Liste des écritures</span>
                </a>
                @endif
                @if($show_all || in_array('exercice_comptable', $habilitations))
                <a href="{{ route('exercice_comptable') }}" class="menu-link-new {{ request()->routeIs('exercice_comptable') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-alt"></i>
                    <span>Exercice comptable</span>
                </a>
                @endif
                @if(in_array('accounting_entry_real', $habilitations))
                <a href="#" class="menu-link-new">
                    <i class="fa-solid fa-list"></i>
                    <span>Liste des écritures</span>
                </a>
                @endif
            </div>
            @endif

            {{-- Section Rapports Comptables --}}
            @php
                $rapports_permissions = [
                    'grand_livre', 'grand_livre_tiers', 'balance', 'balance_tiers',
                    'compte_exploitation', 'flux_tresorerie', 'tableau_amortissements',
                    'etat_tiers', 'compte_resultat', 'bilan', 'etats_analytiques', 'etats_previsionnels'
                ];
                $show_rapports_header = $show_all || count(array_intersect($rapports_permissions, $habilitations)) > 0;
            @endphp

            @if ($show_rapports_header)
            <div class="menu-section">
                <div class="menu-section-header">Rapports Comptables</div>
                @if($show_all || in_array('grand_livre', $habilitations))
                <a href="{{ route('accounting_ledger') }}" class="menu-link-new {{ request()->is('accounting_ledger') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Grand livre</span>
                </a>
                @endif
                @if($show_all || in_array('balance', $habilitations))
                <a href="{{ route('accounting_balance') }}" class="menu-link-new {{ request()->is('accounting_balance') ? 'active' : '' }}">
                    <i class="fa-solid fa-balance-scale"></i>
                    <span>Balance</span>
                </a>
                @endif
            </div>
            @endif

            {{-- Section Paramètres pour Admin --}}
            @if (Auth::check() && Auth::user()->role === 'admin')
            <div class="menu-section">
                <div class="menu-section-header">Paramètres</div>
                @if($show_all || in_array('user_management', $habilitations))
                <a href="{{ route('user_management') }}" class="menu-link-new {{ request()->routeIs('user_management') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-cog"></i>
                    <span>Gestion des utilisateurs</span>
                </a>
                @endif
                @if($show_all || in_array('compagny_information', $habilitations))
                <a href="{{ route('compagny_information') }}" class="menu-link-new {{ request()->routeIs('compagny_information') ? 'active' : '' }}">
                    <i class="fa-solid fa-building"></i>
                    <span>Information de l'entreprise</span>
                </a>
                @endif
            </div>
            @endif
        @else
            {{-- Menu pour Super Admin et autres rôles sans compte comptable actif --}}
            @if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && !session('current_compta_account_id'))
            <div class="menu-section">
                <div class="menu-section-header">Compta Accounts</div>
                <a href="{{ route('compta_accounts.index') }}" class="menu-link-new {{ request()->routeIs('compta_accounts.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Créer compte-comptabilité</span>
                </a>
                <a href="{{ route('compta_accounts.index') }}" class="menu-link-new {{ request()->routeIs('compta_accounts.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-list"></i>
                    <span>Liste/Modifier compte-comptabilité</span>
                </a>
            </div>
            @endif
            @if (auth()->user()->isSuperAdmin())
                <div class="menu-section">
                    <div class="menu-section-header">Paramétrage</div>
                    <a href="{{ route('plan_comptable') }}" class="menu-link-new {{ request()->routeIs('plan_comptable*') ? 'active' : '' }}">
                        <i class="fa-solid fa-book"></i>
                        <span>Plan comptable</span>
                    </a>
                    <a href="{{ route('plan_tiers') }}" class="menu-link-new {{ request()->routeIs('plan_tiers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Plan tiers</span>
                    </a>
                    <a href="{{ route('accounting_journals') }}" class="menu-link-new {{ request()->routeIs('accounting_journals') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Journaux</span>
                    </a>
                    <a href="{{ route('indextresorerie') }}" class="menu-link-new {{ request()->routeIs('indextresorerie') ? 'active' : '' }}">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <span>Journal Trésorerie</span>
                    </a>
                    <a href="{{ route('postetresorerie.index') }}" class="menu-link-new {{ request()->routeIs('postetresorerie.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-wallet"></i>
                        <span>Poste Trésorerie</span>
                    </a>
                </div>

                <div class="menu-section">
                    <div class="menu-section-header">Traitement</div>
                    <a href="{{ route('modal_saisie_direct') }}" class="menu-link-new {{ request()->routeIs('modal_saisie_direct') ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#saisieRedirectModal">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Nouvelle saisie</span>
                    </a>
                    <a href="{{ route('exercice_comptable') }}" class="menu-link-new {{ request()->routeIs('exercice_comptable') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-alt"></i>
                        <span>Exercice comptable</span>
                    </a>
                </div>

                <div class="menu-section">
                    <div class="menu-section-header">Rapports Comptables</div>
                    <a href="{{ route('accounting_ledger') }}" class="menu-link-new {{ request()->is('accounting_ledger') ? 'active' : '' }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Grand livre</span>
                    </a>
                    <a href="{{ route('accounting_balance') }}" class="menu-link-new {{ request()->is('accounting_balance') ? 'active' : '' }}">
                        <i class="fa-solid fa-balance-scale"></i>
                        <span>Balance</span>
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

<!-- Ancien sidebar (caché) -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" style="display: none;">
   <div class="app-brand demo">
    <a href="index.html" class="app-brand-link">
        <span class="app-brand-logo demo">
            <span class="text-primary">
            </span>
        </span>
        <div class="d-flex flex-column ms-1">
            <span class="app-brand-text demo menu-text fw-bold">Flow Compta</span>
            {{-- DEBUT DE L'AJOUT --}}
        @if ($currentCompany)
            <small class="company-name-sidebar fw-semibold">
                {{ $currentCompany->company_name }}
            </small>
        @else
          <small class="text-muted fw-semibold">Super Admin</small>
        @endif
        {{-- FIN DE L'AJOUT --}}
        </div>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="icon-base bx bx-chevron-left"></i>
    </a>
</div>
    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">




        @if (in_array('dashboard', $habilitations))
        <li class="menu-item {{ request()->routeIs('index') || request()->routeIs('admin.dashboard') || request()->routeIs('compta.dashboard') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div class="text-truncate" data-i18n="Dashboards">Dashboards</div>
            </a>
            <ul class="menu-sub">
                {{-- Si un compte comptable est actif, le lien va vers le Dashboard Comptabilité --}}
                @if ($isComptaAccountActive && !auth()->user()->isSuperAdmin())
                    <li class="menu-item {{ request()->routeIs('compta.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('compta.dashboard') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Analytics">Comptabilité Dashboard</div>
                        </a>
                    </li>
                @else
                    {{-- Sinon (SuperAdmin ou pas de compte compta actif), il va vers l'Admin Dashboard --}}
                    <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="menu-link">
                            <div class="text-truncate" data-i18n="Analytics">Analytics </div>
                        </a>
                    </li>
                @endif
            </ul>


        </li>
        @endif



      <ul class="menu-inner py-1">

        {{-- Logique d'affichage pour la création/modification de ComptaAccount --}}
        @if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin() && !session('current_compta_account_id'))
    <li class="menu-item {{ request()->routeIs('compta_accounts.index') ? 'active' : '' }}">
        {{-- Lien mis à jour pour pointer vers la route 'create_compta_account' --}}
        <a href="{{ route('compta_accounts.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-news"></i>
            <div class="text-truncate" data-i18n="Create">  Créer compte-comptabilité</div>
        </a>
    </li>

    {{-- Lien mis à jour pour pointer vers la route 'edit_compta_account' --}}
    <li class="menu-item {{ request()->routeIs('compta_accounts.index') ? 'active' : '' }}">
        <a href="{{ route('compta_accounts.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-news"></i>
            <div class="text-truncate" data-i18n="Edit">Liste/Modifier compte-comptabilité</div>
        </a>
    </li>
   @endif

   {{-- DEBUT BLOC SUPER ADMIN --}}
       @php
            $parametrage_permissions = ['plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie'];
            $show_parametrage_header = count(array_intersect($parametrage_permissions, $habilitations)) > 0;
        @endphp

        @if (auth()->user()->isSuperAdmin())
        <li class="menu-item {{ request()->is('activation') ? 'active' : '' }}">
            <a href="{{ route('pricing.show') }}" class="menu-link">
                <i class="bx bx-book-open"></i>
                <div class="text-truncate" data-i18n="Boxicons">Activer un pack</div>
            </a>
        </li>
        @endif
         @if (auth()->user()->isSuperAdmin())
        <li class="menu-item {{ request()->is('activation') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wallet-alt"></i>
                <div class="text-truncate" data-i18n="Boxicons">Valider pack</div>
            </a>
        </li>
        @endif
          @if (auth()->user()->isSuperAdmin())
        <li class="menu-item {{ request()->is('activation') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wallet-alt"></i>
                <div class="text-truncate" data-i18n="Boxicons">Voir les packs validés</div>
            </a>
        </li>
        @endif
            @if (auth()->user()->isSuperAdmin())
        <li class="menu-item {{ request()->is('activation') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wallet-alt"></i>
                <div class="text-truncate" data-i18n="Boxicons">Ajouter un pack</div>
            </a>
        </li>
        @endif

   @php
            $parametrage_permissions = ['plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie','postetresorerie.index'];
            $show_parametrage_header = count(array_intersect($parametrage_permissions, $habilitations)) > 0;
        @endphp



        <!--parametrage = Bleu-->
        @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-param {{ request()->routeIs('plan_comptable*') ? 'active' : '' }}">
            <a href="{{ route('plan_comptable') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-list-ul"></i>
                <div class="text-truncate" data-i18n="Email">Plan comptable</div>
            </a>
        </li>
        @endif
       @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-param {{ request()->routeIs('plan_tiers*') ? 'active' : '' }}">
            <a href="{{ route('plan_tiers') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-widget"></i>
                <div class="text-truncate" data-i18n="Email">Plan tiers</div>
            </a>
        </li>
        @endif

        @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-param {{ request()->routeIs('accounting_journals') ? 'active' : '' }}">
            <a href="{{ route('accounting_journals') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div class="text-truncate" data-i18n="Email">Journaux</div>
            </a>
        </li>
        @endif

       @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-param {{ request()->routeIs('indextresorerie') ? 'active' : '' }}">
            <a href="{{ route('indextresorerie') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div class="text-truncate" data-i18n="Email">Trésorerie</div>
            </a>
        </li>
        @endif


       @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-param {{ request()->routeIs('postetresorerie.index') ? 'active' : '' }}">
            <a href="postetresorerie.index" class="menu-link">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div class="text-truncate" data-i18n="Email">Poste de trésorerie</div>
            </a>
        </li>
        @endif

        @php
            $traitement_permissions = [
                'nouvelle_saisie', 'exercice_comptable', 'rapprochement', 'gestion_tresorerie',
                'gestion_comptes', 'gestion_tiers', 'gestion_analytique', 'gestion_immobilisations',
                'gestion_stocks', 'gestion_reportings'
            ];
            $show_traitement_header = count(array_intersect($traitement_permissions, $habilitations)) > 0;
        @endphp



        @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-trait {{ request()->routeIs('modal_saisie_direct') ? 'active' : '' }}">
            <a href="{{ route('modal_saisie_direct') }}" class="menu-link" data-bs-toggle="modal" data-bs-target="#saisieRedirectModal">
                <i class="menu-icon tf-icons bx bx-calculator"></i>
                <div class="text-truncate" data-i18n="Email">Nouvelle Saisie</div>
            </a>
        </li>
        @endif

      @if (auth()->user()->isSuperAdmin())
        <li class="menu-item menu-trait {{ request()->routeIs('exercice_comptable') ? 'active' : '' }}">
            <a href="{{ route('exercice_comptable') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i> {{-- Changement d'icône pour l'exercice --}}
                <div class="text-truncate" data-i18n="Email">Exercice comptable</div>
            </a>
        </li>
        @endif

        @if(in_array('accounting_entry_real', $habilitations))
        <li class="menu-item menu-trait {{ request()->routeIs('accounting_entry_real') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-transfer-alt"></i> {{-- Changement d'icône pour rapprochement --}}
                <div class="text-truncate" data-i18n="Email">Etat de rapprochement bancaire</div>
            </a>
        </li>
        @endif

        @if(in_array('gestion_tresorerie', $habilitations))
        <li class="menu-item menu-trait {{ request()->routeIs('gestion_tresorerie') ? 'active' : '' }}">
            <a href="{{ route('gestion_tresorerie') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trending-up"></i> {{-- Changement d'icône pour trésorerie --}}
                <div class="text-truncate" data-i18n="Email">Gestion de la trésorerie</div>
            </a>
        </li>
        @endif

        {{-- FIN BLOC SUPER ADMIN --}}


        @if ($isComptaAccountActive && !auth()->user()->isSuperAdmin())


        <li class="menu-item {{ request()->routeIs('compta.dashboard') ? 'active' : '' }}">
            <a href="{{ route('compta.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div class="text-truncate" data-i18n="DashboardCompta">Tableau de bord comptabilité</div>
            </a>
        </li>


        {{-- Calcul et affichage de l'en-tête Parametrage --}}
        @php
            $parametrage_permissions = ['plan_comptable', 'plan_tiers', 'accounting_journals', 'indextresorerie','postetresorerie.index'];
            $show_parametrage_header = count(array_intersect($parametrage_permissions, $habilitations)) > 0;
        @endphp

        @if ($show_parametrage_header)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text menu-param">Parametrage</span>
        </li>
        @endif

        <!--parametrage = Bleu-->
        @if(in_array('plan_comptable', $habilitations))
        <li class="menu-item menu-param {{ request()->routeIs('plan_comptable*') ? 'active' : '' }}">
            <a href="{{ route('plan_comptable') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-list-ul"></i>
                <div class="text-truncate" data-i18n="Email">Plan comptable</div>
            </a>
        </li>
        @endif
        @if(in_array('plan_tiers', $habilitations))
        <li class="menu-item menu-param {{ request()->routeIs('plan_tiers*') ? 'active' : '' }}">
            <a href="{{ route('plan_tiers') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-widget"></i>
                <div class="text-truncate" data-i18n="Email">Plan tiers</div>
            </a>
        </li>
        @endif

        @if(in_array('journaux', $habilitations))
        <li class="menu-item menu-param {{ request()->routeIs('accounting_journals') ? 'active' : '' }}">
            <a href="{{ route('accounting_journals') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div class="text-truncate" data-i18n="Email">Journaux</div>
            </a>
        </li>
        @endif

        @if(in_array('tresorerie', $habilitations))
        <li class="menu-item menu-param {{ request()->routeIs('indextresorerie') ? 'active' : '' }}">
            <a href="{{ route('indextresorerie') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div class="text-truncate" data-i18n="Email">Journal Trésorerie</div>
            </a>
        </li>
        @endif
        @if(in_array('tresorerie', $habilitations))
        <li class="menu-item menu-param {{ request()->routeIs('postetresorerie.index') ? 'active' : '' }}">
            <a href="{{ route('postetresorerie.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-news"></i>
                <div class="text-truncate" data-i18n="Email">poste Trésorerie</div>
            </a>
        </li>
        @endif

        @php
            $traitement_permissions = [
                'nouvelle_saisie', 'exercice_comptable', 'rapprochement', 'gestion_tresorerie',
                'gestion_comptes', 'gestion_tiers', 'gestion_analytique', 'gestion_immobilisations',
                'gestion_stocks', 'gestion_reportings'
            ];
            $show_traitement_header = count(array_intersect($traitement_permissions, $habilitations)) > 0;
        @endphp

        @if ($show_traitement_header)
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text menu-trait">Traitement</span>
        </li>
        @endif

        @if(in_array('modal_saisie_direct', $habilitations))
        <li class="menu-item menu-trait {{ request()->routeIs('modal_saisie_direct') ? 'active' : '' }}">
            <a href="{{ route('modal_saisie_direct') }}" class="menu-link" data-bs-toggle="modal" data-bs-target="#saisieRedirectModal">
                <i class="menu-icon tf-icons bx bx-calculator"></i>
                <div class="text-truncate" data-i18n="Email">Nouvelle Saisie</div>
            </a>
        </li>
        @endif


        @if(in_array('exercice_comptable', $habilitations))
        <li class="menu-item menu-trait {{ request()->routeIs('exercice_comptable') ? 'active' : '' }}">
            <a href="{{ route('exercice_comptable') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i> {{-- Changement d'icône pour l'exercice --}}
                <div class="text-truncate" data-i18n="Email">Exercice comptable</div>
            </a>
        </li>
        @endif

        @if(in_array('accounting_entry_real', $habilitations))
        <li class="menu-item menu-trait {{ request()->routeIs('accounting_entry_real') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-transfer-alt"></i> {{-- Changement d'icône pour rapprochement --}}
                <div class="text-truncate" data-i18n="Email">Rapprochement bancaire</div>
            </a>
        </li>
        @endif

        @if(in_array('gestion_tresorerie', $habilitations))
        <li class="menu-item menu-trait {{ request()->routeIs('gestion_tresorerie') ? 'active' : '' }}">
            <a href="{{ route('gestion_tresorerie') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trending-up"></i> {{-- Changement d'icône pour trésorerie --}}
                <div class="text-truncate" data-i18n="Email">Gestion de la trésorerie</div>
            </a>
        </li>
        @endif

        {{-- Section "Rapports comptable" --}}
        @php
            $rapports_permissions = [
                'grand_livre', 'grand_livre_tiers', 'balance', 'balance_tiers',
                'compte_exploitation', 'flux_tresorerie', 'tableau_amortissements',
                'etat_tiers', 'compte_resultat', 'bilan', 'etats_analytiques', 'etats_previsionnels'
            ];
            $show_rapports_header = count(array_intersect($rapports_permissions, $habilitations)) > 0;
        @endphp

        @if ($show_rapports_header)
        <li class="menu-header small text-uppercase"><span class="menu-header-text menu-rapport">Rapports comptable</span></li>
        @endif

        @if(in_array('grand_livre', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('accounting_ledger') ? 'active' : '' }}">
            <a href="{{ route('accounting_ledger') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-book"></i>
                <div class="text-truncate" data-i18n="Basic">Grand livre</div>
            </a>
        </li>
        @endif

        @if(in_array('grand_livre_tiers', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('accounting_ledger_tiers') ? 'active' : '' }}">
            <a href="{{ route('accounting_ledger_tiers') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-pin"></i> {{-- Icône spécifique pour les tiers --}}
                <div class="text-truncate" data-i18n="Basic">Grand livre des tiers</div>
            </a>
        </li>
        @endif



        @if(in_array('balance', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('accounting_balance') ? 'active' : '' }}">
            <a href="{{ route('accounting_balance') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-horizontal-center"></i>
                <div class="text-truncate" data-i18n="Basic">Balance</div>
            </a>
        </li>
        @endif

        @if(in_array('Balance_Tiers', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('accounting_balance_tiers') ? 'active' : '' }}">
            <a href="{{ route('accounting_balance_tiers') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-pin"></i> {{-- Icône spécifique pour les tiers --}}
                <div class="text-truncate" data-i18n="Basic">Balance des Tiers</div>
            </a>
        </li>
        @endif

        @if(in_array('compte_exploitation', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-line-chart"></i> {{-- Icône pour compte d'exploitation --}}
                <div class="text-truncate" data-i18n="Boxicons">Compte d’exploitation</div>
            </a>
        </li>
        @endif

        @if(in_array('flux_tresorerie', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('flux_tresorerie') ? 'active' : '' }}">
            <a href="{{ route('flux_tresorerie') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i> {{-- Icône pour flux de trésorerie --}}
                <div class="text-truncate" data-i18n="Boxicons">Flux de trésorerie</div>
            </a>
        </li>
        @endif

        @if(in_array('tableau_amortissements', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="" class="menu-link">
                <i class="menu-icon tf-icons bx bx-table"></i> {{-- Icône pour tableau d'amortissements --}}
                <div class="text-truncate" data-i18n="Boxicons">Tableau des amortissements</div>
            </a>
        </li>
        @endif

        @if(in_array('etat_tiers', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i> {{-- Icône pour état des tiers --}}
                <div class="text-truncate" data-i18n="Boxicons">Etat des tiers</div>
            </a>
        </li>
        @endif

        @if(in_array('compte_resultat', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-trending-up"></i> {{-- Icône pour compte de résultat --}}
                <div class="text-truncate" data-i18n="Boxicons">Compte de résultat</div>
            </a>
        </li>
        @endif

        @if(in_array('bilan', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card-alt"></i> {{-- Icône pour bilan --}}
                <div class="text-truncate" data-i18n="Boxicons">Bilan</div>
            </a>
        </li>
        @endif

        @if(in_array('etats_analytiques', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-pie-chart-alt-2"></i> {{-- Icône pour états analytiques --}}
                <div class="text-truncate" data-i18n="Boxicons">Etats analytiques</div>
            </a>
        </li>
        @endif

        @if(in_array('etats_previsionnels', $habilitations))
        <li class="menu-item menu-rapport">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons bx bx-analyse"></i> {{-- Icône pour états prévisionnels --}}
                <div class="text-truncate" data-i18n="Boxicons">Etats prévisionnels</div>
            </a>
        </li>
        @endif

        {{-- Section Paramètres (Gestion Utilisateurs / Informations Entreprise) --}}
        @php
            $parametre_permissions = ['user_management', 'compagny_information'];
            $has_parametre_access = count(array_intersect($parametre_permissions, $habilitations)) > 0;
        @endphp

        @if (Auth::check() && Auth::user()->role === 'admin')
        <li
            class="menu-item {{ request()->routeIs('user_management') || request()->routeIs('compagny_information') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div class="text-truncate" data-i18n="Dashboards">Paramètre</div>
            </a>
            <ul class="menu-sub">
                @if(in_array('user_management', $habilitations))
                <li class="menu-item {{ request()->routeIs('user_management') ? 'active' : '' }}">
                    <a href="{{ route('user_management') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Analytics">Gestion des utilisateurs</div>
                    </a>
                </li>
                @endif

                @if(in_array('compagny_information', $habilitations))
                <li class="menu-item {{ request()->routeIs('compagny_information') ? 'active' : '' }}">
                    <a href="{{ route('compagny_information') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Analytics">Information de l'entreprise</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        @endif {{-- Fin de la condition @if ($isComptaAccountActive && !auth()->user()->isSuperAdmin()) --}}

    </ul>
</aside>
