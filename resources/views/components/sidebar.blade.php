<style>
    /* PARAMETRAGE = Bleu */
    .menu-param a       { color:#0d6efd !important; }
    .menu-param a:hover { background:#e7f1ff !important; border-radius:8px; }

    /* TRAITEMENT = Orange */
    .menu-trait a       { color:#fd7e14 !important; }
    .menu-trait a:hover { background:#fff3e6 !important; border-radius:8px; }

    /* RAPPORTS = Vert */
    .menu-rapport a       { color:#198754 !important; }
    .menu-rapport a:hover { background:#e9f7ef !important; border-radius:8px; }

    .menu-title {
        font-size: 12px;
        text-transform: uppercase;
        opacity: .7;
        padding: 10px 18px;
        font-weight: bold;
    }

    .menu-link i {
        margin-right: 10px;
        font-size: 20px;
    }
    .company-name-sidebar {
    /* Rendre le texte plus petit et moins visible que le titre principal */
    font-size: 0.75rem; /* Environ 12px */
    color: #6c757d; /* Couleur Bootstrap 'muted' */
    margin-top: -3px;

    /* Gérer les noms longs et l'état replié du menu */
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block; /* S'assurer qu'il prend toute la largeur */
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

    $exercices = ExerciceComptable::where('company_id', $currentCompanyId)->orderBy('date_debut', 'desc')->get();
    $journaux = JournalSaisi::with('codeJournal')
        ->where('company_id', $currentCompanyId)
        ->orderBy('mois', 'asc')
        ->get();
    $code_journaux = CodeJournal::where('company_id', $currentCompanyId)->get();

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
])




<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
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

{{-- FIN DE BLOC SUPER_ADMIN --}}

        <!-- LOGIQUE D'AFFICHAGE DE TOUT LE MENU DE COMPTABILITÉ -->
        {{-- La condition utilise la variable $isComptaAccountActive pour l'affichage conditionnel --}}
        @if ($isComptaAccountActive && !auth()->user()->isSuperAdmin())

        {{-- Tableau de bord Comptabilité. --}}
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

         {{-- @if(in_array('gestion_reportings', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('accounting_ledger_tiers') ? 'active' : '' }}">
            <a href="{{ route('accounting_ledger_tiers') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-pin"></i>
                <div class="text-truncate" data-i18n="Basic">Gestion des reportings</div>
            </a>
        </li>
        @endif --}}

        @if(in_array('balance', $habilitations))
        <li class="menu-item menu-rapport {{ request()->is('accounting_balance') ? 'active' : '' }}">
            <a href="{{ route('accounting_balance') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-horizontal-center"></i>
                <div class="text-truncate" data-i18n="Basic">Balance</div>
            </a>
        </li>
        @endif

        @if(in_array('accounting_balance_tiers', $habilitations))
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

        @if (Auth::check() && Auth::user()->role === 'admin' && $has_parametre_access)
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
