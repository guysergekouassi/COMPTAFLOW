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
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-radius: 32px;
        padding: 4rem 3rem;
        color: white;
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(30, 64, 175, 0.2);
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

    .journal-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 1.25rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        display: block;
        position: relative;
        overflow: hidden;
        min-width: 250px;
        max-width: 280px;
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

    .journal-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        border-color: #3b82f6;
    }

    .journal-icon {
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

    .journal-card:hover .journal-icon {
        transform: scale(1.1);
    }

    .journal-info h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }

    .journal-info p {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1rem;
    }

    .journal-stats {
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

    /* Colors per category */
    .cat-achats { --cat-color: #ef4444; --cat-bg: #fef2f2; }
    .cat-ventes { --cat-color: #10b981; --cat-bg: #ecfdf5; }
    .cat-tresorerie { --cat-color: #3b82f6; --cat-bg: #eff6ff; }
    .cat-od { --cat-color: #8b5cf6; --cat-bg: #f5f3ff; }

    .journal-icon-bg {
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

    .journal-card:hover .card-accent {
        opacity: 1;
    }

</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Hub des <span class="text-blue-600">Journaux</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="hub-header">
                            <div class="row align-items-center">
                                <div class="col-lg-7">
                                    <h1 class="display-5 font-black mb-3 tracking-tighter">Gestion des Écritures</h1>
                                    <p class="fs-5 opacity-90 font-medium">Sélectionnez un journal pour consulter ou saisir de nouvelles écritures comptables.</p>
                                </div>
                                <div class="col-lg-5 text-lg-end">
                                    <div class="px-4 py-3 bg-white/20 backdrop-blur-md rounded-2xl d-inline-block border border-white/30 text-start">
                                        <p class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Exercice Actif</p>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm">
                                                <i class="bx bx-calendar text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="fs-5 font-black mb-0">{{ $exerciceActif->intitule ?? '-' }}</p>
                                                <p class="text-[10px] opacity-70 mb-0 font-medium">
                                                    {{ $exerciceActif && $exerciceActif->date_debut ? \Carbon\Carbon::parse($exerciceActif->date_debut)->format('d/m/Y') : '' }} - {{ $exerciceActif && $exerciceActif->date_fin ? \Carbon\Carbon::parse($exerciceActif->date_fin)->format('d/m/Y') : '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($groupedJournals as $category => $journals)
                            @php
                                $normalizedCategory = strtoupper($category);
                                
                                // Mapping des couleurs
                                $catClass = 'cat-od';
                                $journalType = 'OD';

                                if (str_contains($normalizedCategory, 'ACHAT')) {
                                    $catClass = 'cat-achats';
                                    $journalType = 'Achats';
                                } elseif (str_contains($normalizedCategory, 'VENTE')) {
                                    $catClass = 'cat-ventes';
                                    $journalType = 'Ventes';
                                } elseif (str_contains($normalizedCategory, 'TRES') || str_contains($normalizedCategory, 'BANQUE') || str_contains($normalizedCategory, 'CAISSE')) {
                                    $catClass = 'cat-tresorerie';
                                    if (str_contains($normalizedCategory, 'BANQUE')) $journalType = 'Banque';
                                    elseif (str_contains($normalizedCategory, 'CAISSE')) $journalType = 'Caisse';
                                    else $journalType = 'Trésorerie';
                                }
                                
                                // Mapping des icônes
                                $catIcon = 'bx-spreadsheet';
                                if (str_contains($normalizedCategory, 'ACHAT')) $catIcon = 'bx-shopping-bag';
                                elseif (str_contains($normalizedCategory, 'VENTE')) $catIcon = 'bx-cart';
                                elseif (str_contains($normalizedCategory, 'BANQUE')) $catIcon = 'bx-landmark';
                                elseif (str_contains($normalizedCategory, 'CAISSE')) $catIcon = 'bx-money';
                                elseif (str_contains($normalizedCategory, 'TRES')) $catIcon = 'bx-credit-card';
                                elseif (str_contains($normalizedCategory, 'RAN') || str_contains($normalizedCategory, 'NOUVEAU')) $catIcon = 'bx-history';
                            @endphp
                            
                            @if(count($journals) > 0)
                                <div class="category-section {{ $catClass }}">
                                    <div class="category-header" onclick="toggleCategory(this.parentElement)">
                                        <h2 class="category-title">
                                            <i class="bx {{ $catIcon }} fs-4" style="color: var(--cat-color)"></i>
                                            {{ $category }}
                                            <span class="badge bg-slate-100 text-slate-500 rounded-pill ms-2" style="font-size: 0.65rem">{{ count($journals) }}</span>
                                        </h2>
                                        <div class="d-flex align-items-center gap-4">
                                            <a href="{{ route('accounting_entry_list', ['journal_type' => $category]) }}" class="category-link" onclick="event.stopPropagation()">
                                                Voir toutes les écritures <i class="bx bx-right-arrow-alt ms-1"></i>
                                            </a>
                                            <i class="bx bx-chevron-down fs-4 chevron-icon"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="category-content">
                                        <div class="scrolling-container">
                                            @foreach($journals as $journal)
                                                <a href="{{ route('accounting_entry_list', ['journal_id' => $journal->id]) }}" class="journal-card">
                                                    <div class="card-accent"></div>
                                                    <div class="journal-icon journal-icon-bg">
                                                        <i class="bx {{ $catIcon }}"></i>
                                                    </div>
                                                    <div class="journal-info">
                                                        <h3>{{ $journal->code_journal }}</h3>
                                                        <p class="text-truncate">{{ $journal->intitule }}</p>
                                                    </div>
                                                    <div class="journal-stats">
                                                        <span class="stat-badge">
                                                            <i class="bx bx-list-ol me-1"></i>
                                                            @php $journalId = $journal->id; @endphp
                                                            {{ $counts[$journalId] ?? 0 }} Écritures
                                                        </span>
                                                        <i class="bx bx-chevron-right ms-auto text-slate-300"></i>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

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
