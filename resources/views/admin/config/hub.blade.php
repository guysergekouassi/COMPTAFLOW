@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    :root {
        --premium-blue: #1e40af;
        --premium-blue-light: #3b82f6;
        --config-gold: #b45309;
        --glass-bg: rgba(255, 255, 255, 0.9);
    }

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #1e293b;
    }

    .config-card {
        background: var(--glass-bg);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .config-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border-color: var(--premium-blue-light);
    }

    .icon-box {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .config-card:hover .icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-weight: 800;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .bg-gradient-config {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
    }

    .btn-config-action {
        background: white;
        color: var(--premium-blue);
        border: 1px solid var(--premium-blue);
        padding: 0.75rem 1.5rem;
        border-radius: 14px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .btn-config-action:hover {
        background: var(--premium-blue);
        color: white;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Configuration <span class="text-primary">Master</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row mb-8">
                            <div class="col-12">
                                <div class="bg-gradient-config p-8 rounded-[32px] shadow-2xl relative overflow-hidden">
                                    <div class="position-relative z-index-2 flex justify-between items-center">
                                        <div>
                                            <h2 class="font-black mb-2">Dossier de Configuration</h2>
                                            <p class="mb-0 opacity-80 font-medium">Définissez vos standards comptables une seule fois, propagez-les partout.</p>
                                        </div>
                                        @if(isset($exerciceActif) && $exerciceActif)
                                            <div class="bg-white/20 backdrop-blur-md border border-white/30 px-6 py-3 rounded-2xl text-white">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(74,222,128,0.5)]"></div>
                                                    <div>
                                                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-0">Exercice Actif</p>
                                                        <h4 class="text-lg font-black mb-0">{{ $exerciceActif->intitule }}</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) scale(2);">
                                        <i class="fa-solid fa-gears fa-10x"></i>
                                    </div>
                                </div>
                        </div>

                        <!-- ═══ CODE D'ACCÈS ENTREPRISE ═══ -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div style="background:white; border:1px solid rgba(226,232,240,0.8); border-radius:20px; padding:1.75rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#1e40af,#3b82f6);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(30,64,175,0.2);">
                                                <i class="fas fa-key" style="color:white;font-size:1.1rem;"></i>
                                            </div>
                                            <div>
                                                <div style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:2px;">Code d'accès entreprise</div>
                                                <div style="font-size:0.8rem;color:#475569;font-weight:500;">Partagez ce code avec vos collaborateurs pour les associer à ce dossier</div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($mainCompany->company_code)
                                                <div style="background:#f0fdf4;border:2px dashed #86efac;border-radius:12px;padding:0.6rem 1.2rem;font-family:'Courier New',monospace;font-size:1.1rem;font-weight:800;color:#16a34a;letter-spacing:0.1em;">
                                                    {{ $mainCompany->company_code }}
                                                </div>
                                                <button onclick="navigator.clipboard.writeText('{{ $mainCompany->company_code }}'); this.innerHTML='<i class=\'fas fa-check\'></i> Copié'; setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i> Copier',2000);"
                                                    style="background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;border-radius:10px;padding:0.5rem 1rem;font-size:0.78rem;font-weight:700;cursor:pointer;">
                                                    <i class="fas fa-copy"></i> Copier
                                                </button>
                                            @else
                                                <div style="background:#fef2f2;border:2px dashed #fca5a5;border-radius:12px;padding:0.6rem 1.2rem;font-size:0.82rem;font-weight:700;color:#ef4444;">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Aucun code généré
                                                </div>
                                            @endif
                                            <form method="POST" action="{{ route('accountant.space.generate_code', $mainCompany->id) }}"
                                                  onsubmit="return {{ $mainCompany->company_code ? 'confirm(\'Régénérer le code ? L\\\'ancien code deviendra immédiatement invalide.\')' : 'true' }};">
                                                @csrf
                                                <button type="submit" style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:white;border:none;border-radius:12px;padding:0.6rem 1.2rem;font-size:0.78rem;font-weight:700;cursor:pointer;box-shadow:0 4px 10px rgba(30,64,175,0.2);transition:all 0.2s;"
                                                    onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 18px rgba(30,64,175,0.3)'"
                                                    onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 10px rgba(30,64,175,0.2)'">
                                                    <i class="fas fa-sync-alt me-1"></i>{{ $mainCompany->company_code ? 'Régénérer' : 'Générer le code' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liaison SELFLOW -->
                        <div class="row mb-8">
                            <div class="col-12">
                                <div class="card p-6" style="border: 2px solid #1e40af; border-radius: 24px; background: linear-gradient(135deg, #fbfdff 0%, #eff6ff 100%); box-shadow: 0 10px 25px -5px rgba(30,64,175,0.1);">

                                    {{-- En-tête : Titre + Badge --}}
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                                        <div>
                                            <h5 style="font-weight:800; color:#1e40af; margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                                                <i class="fa-solid fa-link"></i> Liaison SELFLOW (ERP Opérationnel)
                                            </h5>
                                            <p style="font-size:13px; color:#475569; margin-bottom:0;">
                                                Connectez COMPTAFLOW avec SELFLOW pour déverser automatiquement les écritures de ventes, achats et règlements.
                                            </p>
                                        </div>
                                        <div style="text-align:right;">
                                            @if($mainCompany->selflow_sync_status === 'active' && $mainCompany->selflow_company_id)
                                                <div style="background:#dcfce7; color:#166534; padding:8px 18px; border-radius:30px; font-weight:700; font-size:12px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap;">
                                                    <i class="fa-solid fa-circle-check" style="color:#16a34a;"></i>
                                                    <span>Liaison active</span>
                                                    <span style="color:#94a3b8; font-size:10px; font-weight:500; margin-left:4px;">comptaflow → selflow</span>
                                                </div>
                                                <div style="font-size:10px; color:#94a3b8; margin-top:4px; text-align:right;">
                                                    ID Selflow : #{{ $mainCompany->selflow_company_id }}
                                                    @if($mainCompany->selflow_last_sync_at)
                                                        &nbsp;·&nbsp; Dernière sync : {{ \Carbon\Carbon::parse($mainCompany->selflow_last_sync_at)->format('d/m/Y H:i') }}
                                                    @endif
                                                </div>
                                            @else
                                                <span style="background:#fef3c7; color:#92400e; padding:8px 18px; border-radius:30px; font-weight:700; font-size:12px; display:inline-flex; align-items:center; gap:6px;">
                                                    <i class="fa-solid fa-circle-notch fa-spin"></i> Non connectée
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Card infos entreprise Selflow liée (si liaison active) --}}
                                    @if($mainCompany->selflow_sync_status === 'active' && $mainCompany->selflow_company_id && isset($selflowCompanyInfo) && $selflowCompanyInfo)
                                    <div style="margin-top:16px; background:white; border:1px solid #bfdbfe; border-radius:16px; padding:16px;">
                                        <p style="font-size:11px; font-weight:700; color:#3b82f6; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:12px;">
                                            <i class="fa-solid fa-building"></i>&nbsp; Entreprise Selflow liée
                                        </p>
                                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                                            <div>
                                                <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">Nom</div>
                                                <div style="font-weight:700; color:#1e293b; font-size:13px;">{{ $selflowCompanyInfo['nom'] ?? '—' }}</div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">RCCM</div>
                                                <div style="font-weight:600; color:#334155; font-size:13px;">{{ $selflowCompanyInfo['rccm'] ?? '—' }}</div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">NCC</div>
                                                <div style="font-weight:600; color:#334155; font-size:13px;">{{ $selflowCompanyInfo['ncc'] ?? '—' }}</div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">Administrateur</div>
                                                <div style="font-weight:600; color:#334155; font-size:13px;">{{ $selflowCompanyInfo['admin_nom'] ?? '—' }}</div>
                                                <div style="font-size:11px; color:#64748b;">{{ $selflowCompanyInfo['admin_email'] ?? '' }}</div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">Date de création</div>
                                                <div style="font-weight:600; color:#334155; font-size:13px;">{{ $selflowCompanyInfo['created_at'] ?? '—' }}</div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;">Téléphone</div>
                                                <div style="font-weight:600; color:#334155; font-size:13px;">{{ $selflowCompanyInfo['telephone'] ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    @elseif($mainCompany->selflow_sync_status === 'active' && $mainCompany->selflow_company_id)
                                    <div style="margin-top:12px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; padding:12px; font-size:12px; color:#64748b; display:flex; align-items:center; gap:8px;">
                                        <i class="fa-solid fa-circle-info"></i>
                                        Les informations détaillées de l'entreprise Selflow ne sont pas disponibles (serveur Selflow hors ligne ou inaccessible).
                                    </div>
                                    @endif

                                    <hr style="margin:16px 0; border:0; border-top:1px solid #e2e8f0;">
                                    <div style="display:flex; align-items:center; flex-wrap:wrap; gap:16px;">
                                        {{-- La clé de synchronisation ne s'affiche plus, et cet écran ne
                                             la fabrique plus.

                                             Il en tirait une au passage — un `update()` dans un gabarit,
                                             déclenché par un simple affichage de page — puis la montrait
                                             au gérant avec un bouton « Copier », en lui demandant de la
                                             coller dans SELFLOW. C'est précisément la faille refermée de
                                             l'autre côté : le champ libre où l'on collait cette clé
                                             acceptait aussi celle d'une autre entreprise, et ouvrait la
                                             liaison vers ses livres.

                                             La clé désigne un dossier ; elle n'a rien à faire dans les
                                             mains de qui que ce soit. ComptaFlow la génère au
                                             provisionnement, SELFLOW la range chiffrée, et l'entreprise
                                             ne la voit jamais. Ce qui lui est utile — l'état de la
                                             liaison — tient dans les lignes ci-dessous. --}}
                                        <div style="flex:1; min-width:300px;">
                                            <label style="font-weight:700; color:#334155; display:block; margin-bottom:4px; font-size:13px;">Liaison SELFLOW</label>
                                            @if($mainCompany->selflow_sync_key_revoked_at)
                                                <div style="font-size:13px; color:#b91c1c; font-weight:600;">
                                                    <i class="fa-solid fa-plug-circle-xmark"></i>
                                                    Liaison coupée le {{ $mainCompany->selflow_sync_key_revoked_at->format('d/m/Y') }}.
                                                </div>
                                                <small style="font-size:11px; color:#94a3b8; margin-top:4px; display:block;">
                                                    Vos écritures déjà reçues sont conservées. Demandez le rétablissement de la
                                                    liaison depuis SELFLOW.
                                                </small>
                                            @elseif($mainCompany->selflow_company_id)
                                                <div style="font-size:13px; color:#15803d; font-weight:600;">
                                                    <i class="fa-solid fa-plug-circle-check"></i>
                                                    Liaison active@if($mainCompany->selflow_linked_at) depuis le {{ $mainCompany->selflow_linked_at->format('d/m/Y') }}@endif.
                                                </div>
                                                <small style="font-size:11px; color:#94a3b8; margin-top:4px; display:block;">
                                                    Dernière réception de données :
                                                    {{ $mainCompany->selflow_last_deposit_at?->format('d/m/Y à H:i') ?? 'aucune à ce jour' }}.
                                                </small>
                                            @else
                                                <div style="font-size:13px; color:#64748b; font-weight:600;">
                                                    <i class="fa-solid fa-plug"></i> Aucune liaison SELFLOW.
                                                </div>
                                                <small style="font-size:11px; color:#94a3b8; margin-top:4px; display:block;">
                                                    La liaison se demande depuis vos paramètres SELFLOW : elle s'établit sans
                                                    qu'aucune clé ne transite par vous.
                                                </small>
                                            @endif
                                        </div>
                                        <div style="text-align:right; font-size:11px; color:#64748b;">
                                            <div>API COMPTAFLOW :</div>
                                            <code style="background:#f1f5f9; padding:2px 8px; border-radius:4px; font-family:monospace;">{{ url('/') }}</code>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row g-6">
                            <!-- Plan Comptable -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-blue-100 text-blue-700">{{ $stats['accounts'] }} comptes</span>
                                    <div class="icon-box bg-blue-50 text-blue-600">
                                        <i class="fa-solid fa-book-bookmark"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Modèle de Plan</h5>
                                    <p class="text-sm text-slate-500 mb-6">Établissez la nomenclature officielle des comptes pour l'ensemble de vos filiales.</p>
                                    <a href="{{ route('admin.config.plan_comptable') }}" class="btn btn-config-action w-100">Gérer la structure</a>
                                </div>
                            </div>

                            <!-- Plan Tiers -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-indigo-100 text-indigo-700">{{ $stats['tiers'] }} fiches</span>
                                    <div class="icon-box bg-indigo-50 text-indigo-600">
                                        <i class="fa-solid fa-address-book"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Modèle de Tiers</h5>
                                    <p class="text-sm text-slate-500 mb-6">Centralisez les collecteurs auxiliaires types (Clients, Fournisseurs) par défaut.</p>
                                    <a href="{{ route('admin.config.plan_tiers') }}" class="btn btn-config-action w-100">Configurer les tiers</a>
                                </div>
                            </div>

                            <!-- Journaux -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-amber-100 text-amber-700">{{ $stats['journals'] }} codes</span>
                                    <div class="icon-box bg-amber-50 text-amber-600">
                                        <i class="fa-solid fa-swatchbook"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Journaux Types</h5>
                                    <p class="text-sm text-slate-500 mb-6">Définissez les codes journaux (ACH, VEN, BQ) standards pour vos entités.</p>
                                    <a href="{{ route('admin.config.journals') }}" class="btn btn-config-action w-100">Définir les codes</a>
                                </div>
                            </div>

                            <!-- Écritures Importées -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-emerald-100 text-emerald-700">
                                        {{ $stats['imported'] }} écritures
                                    </span>
                                    <div class="icon-box bg-emerald-50 text-emerald-600">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Écritures Importées</h5>
                                    <p class="text-sm text-slate-500 mb-6">Visualisez et intégrez les écritures provenant de logiciels externes.</p>
                                    
                                    @if($stats['imported'] > 0)
                                        <form action="{{ route('admin.config.charge_imports') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-xl font-bold shadow-lg shadow-emerald-200">
                                                <i class="fa-solid fa-cloud-arrow-down me-2"></i>Charger au centre
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.config.external_import') }}" class="btn btn-config-action w-100">
                                            <i class="fa-solid fa-plus-circle me-2"></i>Importer des données
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Postes de Trésorerie -->
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-blue-100 text-blue-700">Trésorerie</span>
                                    <div class="icon-box bg-blue-50 text-blue-600">
                                        <i class="fa-solid fa-wallet"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Postes Trésorerie</h5>
                                    <p class="text-sm text-slate-500 mb-6">Définissez les rubriques et catégories de flux pour le tableau de trésorerie.</p>
                                    <a href="{{ route('admin.config.tresorerie_posts') }}" class="btn btn-config-action w-100">Définir les postes</a>
                                </div>
                            </div>

                            <!-- Catégories de Trésorerie -->
                            {{-- 
                            <div class="col-md-4">
                                <div class="config-card p-6 h-100">
                                    <span class="stat-badge bg-purple-100 text-purple-700">{{ $stats['treasury_categories'] }} types</span>
                                    <div class="icon-box bg-purple-50 text-purple-600">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <h5 class="font-black mb-3">Catégories de Trésorerie</h5>
                                    <p class="text-sm text-slate-500 mb-6">Organisez vos flux de trésorerie par catégories analytiques selon vos besoins.</p>
                                    <a href="{{ route('admin.config.treasury_categories') }}" class="btn btn-config-action w-100">Gérer les catégories</a>
                                </div>
                            </div>
                            --}}

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
{{-- `copierCleSync()` est parti avec le champ qu'il copiait : la clé de
     synchronisation ne s'affiche plus, et n'a donc plus à finir dans un
     presse-papiers — c'est l'un des endroits où un secret traîne le plus
     longtemps sans que personne y pense. --}}
</body>
</html>
