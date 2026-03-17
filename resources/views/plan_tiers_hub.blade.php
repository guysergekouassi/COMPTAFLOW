<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');
    
    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .hub-header {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-radius: 32px;
        padding: 4rem 3rem;
        color: white;
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.2);
    }

    .hub-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }

    .scrolling-container {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding: 1rem 0.5rem 2rem 0.5rem;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }

    .scrolling-container::-webkit-scrollbar {
        height: 6px;
    }

    .scrolling-container::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .scrolling-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .category-section {
        margin-bottom: 2rem;
        background: white;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .category-header {
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background 0.2s;
        border-bottom: 1px solid #f1f5f9;
        user-select: none;
    }

    .category-header:hover {
        background: #f8fafc;
    }

    .category-title {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #1e293b;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-grow: 1;
    }

    .category-link {
        color: var(--cat-color);
        text-decoration: none;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        background: var(--cat-bg);
        transition: all 0.2s;
    }

    .category-link:hover {
        filter: brightness(0.95);
        transform: translateX(3px);
    }

    .tier-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        display: block;
        position: relative;
        overflow: hidden;
        min-width: 280px;
        max-width: 320px;
        flex: 0 0 auto;
    }

    .category-content {
        max-height: 500px;
        overflow-y: auto;
        scrollbar-width: thin;
        transition: all 0.3s ease-in-out;
    }

    .category-content.collapsed {
        max-height: 0;
        overflow: hidden;
    }

    .chevron-icon {
        transition: transform 0.3s;
        color: #94a3b8;
    }

    .collapsed .chevron-icon {
        transform: rotate(-90deg);
    }

    .tier-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        border-color: #3b82f6;
    }

    .tier-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
        transition: all 0.3s ease;
    }

    .tier-card:hover .tier-icon {
        transform: scale(1.1);
    }

    .tier-info h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.25rem;
        line-height: 1.2;
    }

    .tier-info p {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 1rem;
        font-weight: 600;
        letter-spacing: 0.025em;
    }

    .tier-stats {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }

    .stat-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        background: #f8fafc;
        color: #475569;
    }

    .activity-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .active-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        position: relative;
    }

    .active-dot::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(2.5); opacity: 0; }
    }

    /* Category colors */
    .cat-blue { --cat-color: #2563eb; --cat-bg: #eff6ff; }
    .cat-indigo { --cat-color: #4f46e5; --cat-bg: #eef2ff; }
    .cat-emerald { --cat-color: #10b981; --cat-bg: #ecfdf5; }
    .cat-amber { --cat-color: #d97706; --cat-bg: #fffbeb; }
    .cat-purple { --cat-color: #9333ea; --cat-bg: #faf5ff; }
    .cat-slate { --cat-color: #475569; --cat-bg: #f8fafc; }

    .tier-icon-bg {
        background: var(--cat-bg);
        color: var(--cat-color);
    }

    .card-accent {
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: var(--cat-color);
        opacity: 0.2;
    }

    .tier-card:hover .card-accent {
        opacity: 1;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Hub des <span class="text-blue-600">Tiers</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Section -->
                        <div class="hub-header">
                            <div class="row align-items-center">
                                <div class="col-lg-7">
                                    <h1 class="display-5 font-black mb-3 tracking-tighter">Répertoire des Tiers</h1>
                                    <p class="fs-5 opacity-90 font-medium">Consultez et gérez vos comptes tiers. Accédez directement aux écritures d'un client ou d'un fournisseur.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('plan_tiers') }}" class="btn btn-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-blue-900/20">
                                            <i class="bx bx-list-check me-2"></i>Liste du Plan Tiers
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-5 text-lg-end pt-4 pt-lg-0">
                                    <div class="px-4 py-3 bg-white/20 backdrop-blur-md rounded-2xl d-inline-block border border-white/30 text-start">
                                        <p class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Contexte Comptable</p>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm">
                                                <i class="bx bx-calendar text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="fs-5 font-black mb-0">{{ $exerciceActif->intitule ?? 'Aucun exercice' }}</p>
                                                <p class="text-[10px] opacity-70 mb-0 font-medium">Affichage des compteurs par exercice</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Categories Section -->
                        @forelse($groupedTiers as $category => $tiers)
                            @php
                                $normalizedCategory = strtoupper($category);
                                $catPrefix = match($normalizedCategory) {
                                    'CLIENTS' => '41',
                                    'FOURNISSEURS' => '40',
                                    'PERSONNEL' => '42',
                                    'ÉTAT & IMPÔTS', 'ORGANISMES SOCIAUX' => '44',
                                    'ASSOCIÉS' => '45',
                                    'PROVISIONS & DÉPRÉCIATIONS' => '49',
                                    default => null
                                };

                                $catInfo = match($normalizedCategory) {
                                    'CLIENT', 'CLIENTS' => ['icon' => 'bx-user-pin', 'color' => 'blue', 'label' => 'Clients'],
                                    'FOURNISSEUR', 'FOURNISSEURS' => ['icon' => 'bx-package', 'color' => 'indigo', 'label' => 'Fournisseurs'],
                                    'SALARIÉ', 'SALARIES', 'PERSONNEL' => ['icon' => 'bx-briefcase-alt', 'color' => 'emerald', 'label' => 'Salariés & Personnel'],
                                    'IMPÔT', 'IMPOTS', 'ETAT', 'ÉTAT & IMPÔTS', 'ORGANISMES SOCIAUX' => ['icon' => 'bx-building-house', 'color' => 'amber', 'label' => 'État, Impôts & Sociaux'],
                                    'ASSOCIÉS', 'ASSOCIES', 'ACTIONNAIRES' => ['icon' => 'bx-group', 'color' => 'purple', 'label' => 'Associés & Actionnaires'],
                                    'DÉBITEURS & CRÉDITEURS DIVERS', 'COMPTES TRANSITOIRES', 'DETTES SUR IMMO', 'DIVERS' => ['icon' => 'bx-category-alt', 'color' => 'slate', 'label' => 'Autres Tiers & Divers'],
                                    'DÉPRÉCIATION', 'PROVISIONS & DÉPRÉCIATIONS' => ['icon' => 'bx-trending-down', 'color' => 'slate', 'label' => 'Provisions & Dépréciations'],
                                    default => ['icon' => 'bx-hash', 'color' => 'slate', 'label' => $category]
                                };
                                $colorClass = 'cat-' . $catInfo['color'];
                            @endphp

                            <div class="category-section {{ $colorClass }}">
                                <div class="category-header" onclick="toggleCategory(this.parentElement)">
                                    <h2 class="category-title">
                                        <i class="bx {{ $catInfo['icon'] }} fs-4" style="color: var(--cat-color)"></i>
                                        {{ $catInfo['label'] }}
                                        <span class="badge bg-slate-100 text-slate-500 rounded-pill ms-2" style="font-size: 0.65rem">{{ count($tiers) }}</span>
                                    </h2>
                                    <div class="d-flex align-items-center gap-4">
                                        @if($catPrefix)
                                        <a href="{{ route('accounting_entry_list', ['tier_prefix' => $catPrefix]) }}" class="category-link" onclick="event.stopPropagation()">
                                            Voir toutes les écritures <i class="bx bx-right-arrow-alt ms-1"></i>
                                        </a>
                                        @endif
                                        <i class="bx bx-chevron-down fs-4 chevron-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="category-content">
                                    <div class="scrolling-container">
                                        @foreach($tiers as $tier)
                                            @php
                                                $entryCount = $counts[$tier->id] ?? 0;
                                            @endphp
                                            <a href="{{ route('accounting_entry_list', ['plan_tiers_id' => $tier->id]) }}" class="tier-card">
                                                <div class="card-accent"></div>
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="tier-icon tier-icon-bg mb-0">
                                                        <i class="bx {{ $catInfo['icon'] }}"></i>
                                                    </div>
                                                    @if($entryCount > 0)
                                                    <div class="activity-indicator">
                                                        <span class="active-dot"></span>
                                                        <span class="text-[10px] font-black text-green-600 uppercase">{{ $entryCount }} actives</span>
                                                    </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="tier-info">
                                                    <p class="mb-1 uppercase">{{ $tier->numero_de_tiers }}</p>
                                                    <h3 class="text-truncate" title="{{ $tier->intitule }}">{{ $tier->intitule }}</h3>
                                                </div>

                                                <div class="tier-stats">
                                                    <span class="stat-badge">
                                                        <i class="bx bx-cog me-1"></i>
                                                        Compte : {{ $tier->compte->numero_de_compte ?? 'N/D' }}
                                                    </span>
                                                    <i class="bx bx-chevron-right ms-auto text-slate-300"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="bx bx-user-x text-slate-300 text-4xl"></i>
                                </div>
                                <h3 class="text-xl font-black text-slate-800 mb-2">Aucun tiers enregistré</h3>
                                <p class="text-slate-500 mb-8 max-w-sm mx-auto font-medium">
                                    Votre plan tiers semble vide. Importez ou créez vos tiers pour commencer la gestion.
                                </p>
                                <a href="{{ route('plan_tiers') }}" class="btn btn-primary px-8 py-3 rounded-2xl font-bold">
                                    Configurer le Plan Tiers
                                </a>
                            </div>
                        @endforelse

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleCategory(section) {
            const content = section.querySelector('.category-content');
            section.classList.toggle('collapsed');
            content.classList.toggle('collapsed');
        }
    </script>
</body>
</html>
