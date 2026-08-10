<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; min-height: 100vh; }

/* ── HERO ── */
.space-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #1e3a8a 100%);
    padding: 2.5rem 3rem;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(99,102,241,0.2);
}
.space-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 80% 50%, rgba(99,102,241,0.15) 0%, transparent 60%);
    pointer-events: none;
}
.hero-avatar {
    width: 64px; height: 64px;
    border-radius: 18px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; font-weight: 800; color: white;
    box-shadow: 0 8px 24px -4px rgba(99,102,241,0.5);
}

/* ── TABS ── */
.space-tabs {
    background: #1e293b;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    padding: 0 3rem;
    display: flex; gap: 0;
}
.space-tab {
    padding: 1rem 1.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    cursor: pointer;
    border: none;
    background: none;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    display: flex; align-items: center; gap: 0.5rem;
}
.space-tab.active {
    color: #6366f1;
    border-bottom-color: #6366f1;
}
.space-tab:hover { color: #94a3b8; }

/* ── MAIN CONTENT AREA ── */
.space-body {
    background: #0f172a;
    min-height: calc(100vh - 200px);
    padding: 2rem 3rem;
}

/* ── STAT CARDS ── */
.stat-card {
    background: linear-gradient(135deg, #1e293b 0%, #162032 100%);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px;
    padding: 1.5rem;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}
.stat-card::before {
    content: '';
    position: absolute; top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 50%;
    opacity: 0.08;
    transform: translate(20px, -20px);
}
.stat-card.indigo::before { background: #6366f1; }
.stat-card.violet::before { background: #8b5cf6; }
.stat-card.emerald::before { background: #10b981; }
.stat-card.amber::before { background: #f59e0b; }
.stat-card:hover { border-color: rgba(99,102,241,0.3); transform: translateY(-2px); }
.stat-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 1rem;
}
.stat-icon.indigo { background: rgba(99,102,241,0.15); color: #818cf8; }
.stat-icon.violet { background: rgba(139,92,246,0.15); color: #a78bfa; }
.stat-icon.emerald { background: rgba(16,185,129,0.15); color: #34d399; }
.stat-icon.amber { background: rgba(245,158,11,0.15); color: #fbbf24; }
.stat-value { font-size: 2rem; font-weight: 800; color: white; line-height: 1; }
.stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; margin-top: 0.25rem; }

/* ── COMPANY CARDS ── */
.company-card {
    background: linear-gradient(135deg, #1e293b 0%, #162032 100%);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px;
    overflow: hidden;
    transition: all 0.3s;
}
.company-card:hover { border-color: rgba(99,102,241,0.4); transform: translateY(-3px); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.5); }
.company-card-header {
    padding: 1.25rem 1.5rem;
    display: flex; align-items: center; gap: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.company-logo {
    width: 46px; height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #1e3a8a 0%, #6366f1 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800; color: white; text-transform: uppercase;
    flex-shrink: 0;
}
.company-name { font-size: 0.95rem; font-weight: 700; color: white; }
.company-type { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-top: 1px; }
.company-body { padding: 1.25rem 1.5rem; }
.company-kpi { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
.kpi-item { flex: 1; min-width: 70px; text-align: center; }
.kpi-val { font-size: 1.3rem; font-weight: 800; color: white; line-height: 1; }
.kpi-label { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #475569; margin-top: 3px; }
.company-code-badge {
    background: rgba(99,102,241,0.1);
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 8px;
    padding: 0.4rem 0.75rem;
    font-size: 0.72rem;
    font-weight: 700;
    color: #818cf8;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.05em;
    display: inline-block;
    margin-bottom: 1rem;
}
.btn-work {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 0.4rem;
    transition: all 0.2s;
}
.btn-work:hover { box-shadow: 0 8px 20px -4px rgba(99,102,241,0.5); transform: translateY(-1px); color: white; }
.btn-details {
    background: rgba(255,255,255,0.06);
    color: #94a3b8;
    border: 1px solid rgba(255,255,255,0.08);
    padding: 0.55rem 0.85rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 0.4rem;
    transition: all 0.2s;
}
.btn-details:hover { background: rgba(255,255,255,0.1); color: white; }

/* ── FORMS ── */
.space-form-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 18px;
    padding: 1.75rem;
}
.space-form-card label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.4rem; display: block; }
.space-form-card .form-control, .space-form-card .form-select {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
    border-radius: 10px;
    font-size: 0.85rem;
}
.space-form-card .form-control:focus, .space-form-card .form-select:focus {
    background: rgba(255,255,255,0.08);
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
    color: white;
}
.space-form-card .form-control::placeholder { color: #475569; }
.space-form-card select option { background: #1e293b; }

/* ── SECTION HEADER ── */
.section-title-dark {
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.1em; color: #475569;
    display: flex; align-items: center; gap: 0.5rem;
    margin-bottom: 1.25rem;
}
.section-title-dark::after {
    content: ''; flex: 1; height: 1px;
    background: rgba(255,255,255,0.06);
}

/* ── COLLABORATORS TABLE ── */
.collabs-table { width: 100%; border-collapse: collapse; }
.collabs-table th {
    font-size: 0.62rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: #475569;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    background: rgba(255,255,255,0.02);
    text-align: left;
}
.collabs-table td {
    font-size: 0.82rem; color: #cbd5e1;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    vertical-align: middle;
}
.collabs-table tr:last-child td { border-bottom: none; }
.badge-role {
    padding: 0.25rem 0.65rem; border-radius: 999px;
    font-size: 0.62rem; font-weight: 700; text-transform: uppercase;
}
.badge-admin { background: rgba(99,102,241,0.15); color: #818cf8; }
.badge-comptable { background: rgba(16,185,129,0.15); color: #34d399; }

/* ── CHAT UI ── */
.chat-container { display: flex; height: 520px; border: 1px solid rgba(255,255,255,0.06); border-radius: 18px; overflow: hidden; }
.chat-sidebar { width: 260px; border-right: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); overflow-y: auto; flex-shrink: 0; }
.chat-user-item {
    padding: 0.9rem 1rem; display: flex; align-items: center; gap: 0.75rem;
    cursor: pointer; transition: background 0.15s; border-bottom: 1px solid rgba(255,255,255,0.04);
}
.chat-user-item:hover, .chat-user-item.active { background: rgba(99,102,241,0.1); }
.chat-avatar-sm {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, #1e3a8a 0%, #6366f1 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 800; color: white; text-transform: uppercase; flex-shrink: 0;
}
.chat-user-name { font-size: 0.82rem; font-weight: 600; color: #e2e8f0; }
.chat-user-role { font-size: 0.65rem; color: #475569; }
.chat-main { flex: 1; display: flex; flex-direction: column; }
.chat-header { padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); }
.chat-messages { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
.msg-bubble { max-width: 72%; padding: 0.65rem 1rem; border-radius: 14px; font-size: 0.82rem; }
.msg-me { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
.msg-other { background: rgba(255,255,255,0.06); color: #cbd5e1; align-self: flex-start; border-bottom-left-radius: 4px; }
.msg-time { font-size: 0.6rem; color: rgba(255,255,255,0.5); margin-top: 3px; text-align: right; }
.chat-input-area { padding: 1rem; border-top: 1px solid rgba(255,255,255,0.06); display: flex; gap: 0.75rem; }
.chat-input {
    flex: 1; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
    color: white; border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem;
    outline: none;
}
.chat-input::placeholder { color: #475569; }
.chat-input:focus { border-color: #6366f1; }
.chat-send { background: #6366f1; border: none; color: white; border-radius: 12px; padding: 0.65rem 1.1rem; cursor: pointer; font-size: 1rem; transition: all 0.2s; }
.chat-send:hover { background: #4f46e5; }
.tab-content-section { display: none; }
.tab-content-section.active { display: block; }

/* ── ALERTS ── */
.space-alert {
    border-radius: 12px; padding: 0.85rem 1.25rem;
    font-size: 0.82rem; font-weight: 500;
    display: flex; align-items: center; gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.space-alert.success { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25); color: #34d399; }
.space-alert.error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); color: #f87171; }
.space-alert.info { background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.25); color: #818cf8; }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
@include('components.sidebar')
<div class="layout-page">
@include('components.header', ['page_title' => 'Mon Espace Comptable'])

<div class="content-wrapper p-0">

    <!-- HERO -->
    <div class="space-hero">
        <div class="d-flex align-items-center gap-4 position-relative">
            <div class="hero-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}</div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Espace de gestion comptable</div>
                <h2 style="color:white; font-weight:800; font-size:1.5rem; margin:0;">Bonjour, {{ auth()->user()->name }} {{ auth()->user()->last_name }}</h2>
                <div style="color:rgba(255,255,255,0.5); font-size:0.82rem; margin-top:4px;">{{ $stats['total_companies'] }} entreprise(s) dans votre portefeuille · {{ $stats['total_entries'] }} écritures au total</div>
            </div>
            <div class="ms-auto d-flex gap-2">
                <button class="btn btn-sm" style="background:rgba(99,102,241,0.2); border:1px solid rgba(99,102,241,0.3); color:#818cf8; border-radius:10px; font-size:0.75rem; font-weight:700;" data-bs-toggle="modal" data-bs-target="#newCompanyModal">
                    <i class="fas fa-plus me-1"></i>Nouvelle entreprise
                </button>
                <button class="btn btn-sm" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:#94a3b8; border-radius:10px; font-size:0.75rem; font-weight:700;" data-bs-toggle="modal" data-bs-target="#newMemberModal">
                    <i class="fas fa-user-plus me-1"></i>Ajouter collaborateur
                </button>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="space-tabs">
        <button class="space-tab active" id="tab-dashboard" onclick="switchSpaceTab('dashboard')"><i class="fas fa-th-large"></i>Tableau de bord</button>
        <button class="space-tab" id="tab-collaborators" onclick="switchSpaceTab('collaborators')"><i class="fas fa-users"></i>Collaborateurs</button>
        <button class="space-tab" id="tab-fusion" onclick="switchSpaceTab('fusion')"><i class="fas fa-code-branch"></i>Fusion & Déversement</button>
        <button class="space-tab" id="tab-chat" onclick="switchSpaceTab('chat')"><i class="fas fa-comments"></i>Messagerie</button>
    </div>

    <div class="space-body">

        @if(session('success'))
        <div class="space-alert success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="space-alert error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <!-- TAB 1 : DASHBOARD -->
        <div class="tab-content-section active" id="section-dashboard">

            <!-- KPI Stats -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card indigo">
                        <div class="stat-icon indigo"><i class="fas fa-building"></i></div>
                        <div class="stat-value">{{ $stats['total_companies'] }}</div>
                        <div class="stat-label">Entreprises gérées</div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card violet">
                        <div class="stat-icon violet"><i class="fas fa-file-invoice"></i></div>
                        <div class="stat-value">{{ number_format($stats['total_entries']) }}</div>
                        <div class="stat-label">Écritures comptables</div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card emerald">
                        <div class="stat-icon emerald"><i class="fas fa-user-friends"></i></div>
                        <div class="stat-value">{{ $stats['total_collaborators'] }}</div>
                        <div class="stat-label">Collaborateurs</div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card amber">
                        <div class="stat-icon amber"><i class="fas fa-link"></i></div>
                        <div class="stat-value">{{ $stats['assigned_companies'] }}</div>
                        <div class="stat-label">Affectations externes</div>
                    </div>
                </div>
            </div>

            <!-- Company Cards -->
            <div class="section-title-dark"><i class="fas fa-building" style="color:#6366f1;"></i>Mes entreprises</div>
            <div class="row g-3">
                @forelse($companiesData as $data)
                @php $comp = $data['model']; @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="company-card h-100">
                        <div class="company-card-header">
                            <div class="company-logo">{{ strtoupper(substr($comp->company_name, 0, 2)) }}</div>
                            <div>
                                <div class="company-name">{{ $comp->company_name }}</div>
                                <div class="company-type">
                                    @if($data['is_owner'])<span style="color:#6366f1;">★ Propriétaire</span>@else <span style="color:#10b981;">→ Affecté</span>@endif
                                    &nbsp;·&nbsp; {{ $comp->activity }}
                                </div>
                            </div>
                            @if($comp->is_blocked)
                                <span class="ms-auto badge" style="background:rgba(239,68,68,0.15);color:#f87171;border-radius:8px;font-size:0.6rem;">Bloquée</span>
                            @endif
                        </div>
                        <div class="company-body">
                            @if($comp->company_code)
                            <div class="company-code-badge"><i class="fas fa-key me-1"></i>{{ $comp->company_code }}</div>
                            @else
                            <div class="company-code-badge" style="color:#ef4444; border-color:rgba(239,68,68,0.3);">Code non généré</div>
                            @endif

                            <div class="company-kpi">
                                <div class="kpi-item">
                                    <div class="kpi-val">{{ number_format($data['entries_count']) }}</div>
                                    <div class="kpi-label">Écritures</div>
                                </div>
                                <div class="kpi-item">
                                    <div class="kpi-val">{{ $data['accounts_count'] }}</div>
                                    <div class="kpi-label">Comptes</div>
                                </div>
                                <div class="kpi-item">
                                    <div class="kpi-val">{{ $data['tiers_count'] }}</div>
                                    <div class="kpi-label">Tiers</div>
                                </div>
                                <div class="kpi-item">
                                    <div class="kpi-val">{{ $data['exercice_actif'] }}</div>
                                    <div class="kpi-label">Exercice</div>
                                </div>
                            </div>

                            <!-- Assigned users -->
                            @if($data['assigned_users']->count() > 0)
                            <div style="margin-bottom:0.75rem; display:flex; gap:0.35rem; flex-wrap:wrap; align-items:center;">
                                <span style="font-size:0.62rem; color:#475569; font-weight:700; text-transform:uppercase;">Équipe:</span>
                                @foreach($data['assigned_users']->take(4) as $au)
                                <span title="{{ $au->name }} {{ $au->last_name }} ({{ $au->role }})" style="width:28px; height:28px; border-radius:8px; background:rgba(99,102,241,0.2); color:#818cf8; font-size:0.6rem; font-weight:800; display:inline-flex; align-items:center; justify-content:center; cursor:default;">
                                    {{ strtoupper(substr($au->name,0,1) . substr($au->last_name ?? '',0,1)) }}
                                </span>
                                @endforeach
                                @if($data['assigned_users']->count() > 4)
                                <span style="font-size:0.7rem; color:#475569;">+{{ $data['assigned_users']->count() - 4 }}</span>
                                @endif
                            </div>
                            @endif

                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('accountant.space.switch', $comp->id) }}" class="btn-work">
                                    <i class="fas fa-arrow-right"></i> Travailler
                                </a>
                                <button class="btn-details" data-bs-toggle="modal" data-bs-target="#detailModal{{ $comp->id }}">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div class="modal fade" id="detailModal{{ $comp->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:16px;">
                            <div class="modal-header" style="border-color:rgba(255,255,255,0.08);">
                                <h5 class="modal-title text-white fw-bold">{{ $comp->company_name }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="section-title-dark"><i class="fas fa-code" style="color:#6366f1;"></i>Code d'accès</div>
                                    <div class="company-code-badge fs-6">{{ $comp->company_code ?? 'Non généré' }}</div>
                                    <small style="color:#475569; font-size:0.7rem;">Partagez ce code avec un collaborateur pour lui permettre d'accéder directement à cette comptabilité.</small>
                                </div>
                                <div class="mb-3">
                                    <div class="section-title-dark"><i class="fas fa-users" style="color:#6366f1;"></i>Équipe affectée</div>
                                    @foreach($data['assigned_users'] as $au)
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="chat-avatar-sm">{{ strtoupper(substr($au->name,0,1).substr($au->last_name??'',0,1)) }}</div>
                                            <div>
                                                <div style="color:white; font-size:0.82rem; font-weight:600;">{{ $au->name }} {{ $au->last_name }}</div>
                                                <div style="color:#475569; font-size:0.65rem;">{{ $au->email_adresse }}</div>
                                            </div>
                                        </div>
                                        <span class="badge-role badge-{{ $au->role }}">{{ $au->role }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div style="text-align:center; padding:3rem; color:#475569;">
                        <i class="fas fa-building" style="font-size:3rem; margin-bottom:1rem; display:block;"></i>
                        <div style="font-size:1.1rem; font-weight:700; color:#64748b; margin-bottom:0.5rem;">Aucune entreprise</div>
                        <div style="font-size:0.82rem;">Créez votre première entreprise pour commencer.</div>
                        <button class="btn btn-sm mt-3" style="background:#6366f1; color:white; border:none; border-radius:10px; padding:0.55rem 1.25rem; font-weight:700;" data-bs-toggle="modal" data-bs-target="#newCompanyModal">
                            <i class="fas fa-plus me-1"></i>Créer une entreprise
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- TAB 2 : COLLABORATORS -->
        <div class="tab-content-section" id="section-collaborators">
            <div class="row g-4">
                <!-- Form Assign -->
                <div class="col-lg-4">
                    <div class="space-form-card">
                        <div class="section-title-dark mb-3"><i class="fas fa-user-plus" style="color:#6366f1;"></i>Affecter un collaborateur</div>
                        <form method="POST" action="{{ route('accountant.space.assign') }}">
                            @csrf
                            <div class="mb-3">
                                <label>Entreprise cible</label>
                                <select name="company_id" class="form-select form-select-sm">
                                    @foreach($companiesData as $d)
                                        <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Collaborateur</label>
                                <select name="user_id" class="form-select form-select-sm">
                                    @foreach($collaborators as $collab)
                                        <option value="{{ $collab->id }}">{{ $collab->name }} {{ $collab->last_name }} ({{ $collab->role }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Rôle dans l'entreprise</label>
                                <select name="role" class="form-select form-select-sm">
                                    <option value="admin">Admin</option>
                                    <option value="comptable">Comptable</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm w-100" style="background:#6366f1; color:white; border:none; border-radius:10px; font-weight:700; padding:0.6rem;">
                                <i class="fas fa-link me-1"></i>Affecter
                            </button>
                        </form>
                    </div>
                </div>
                <!-- Table -->
                <div class="col-lg-8">
                    <div class="space-form-card">
                        <div class="section-title-dark mb-3"><i class="fas fa-users" style="color:#6366f1;"></i>Collaborateurs créés par moi</div>
                        <table class="collabs-table">
                            <thead>
                                <tr>
                                    <th>Collaborateur</th>
                                    <th>Email</th>
                                    <th>Rôle global</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collaborators as $collab)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="chat-avatar-sm" style="width:32px;height:32px;font-size:0.65rem;">{{ strtoupper(substr($collab->name,0,1).substr($collab->last_name??'',0,1)) }}</div>
                                            <span style="color:white; font-weight:600;">{{ $collab->name }} {{ $collab->last_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $collab->email_adresse }}</td>
                                    <td><span class="badge-role badge-{{ $collab->role }}">{{ $collab->role }}</span></td>
                                    <td>
                                        @if($collab->is_active)
                                            <span style="color:#34d399; font-size:0.7rem; font-weight:700;">● Actif</span>
                                        @else
                                            <span style="color:#f87171; font-size:0.7rem; font-weight:700;">● Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" style="text-align:center; color:#475569; padding:2rem;">Aucun collaborateur créé</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3 : FUSION -->
        <div class="tab-content-section" id="section-fusion">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="space-form-card">
                        <div class="section-title-dark mb-3"><i class="fas fa-code-branch" style="color:#6366f1;"></i>Déverser des données</div>
                        <div class="space-alert info" style="margin-bottom:1.5rem;">
                            <i class="fas fa-info-circle"></i>
                            <div>Cette opération copie les configurations (plan comptable, journaux, tiers) d'une entreprise source vers une autre. Aucune écriture n'est transférée.</div>
                        </div>
                        <form method="POST" action="{{ route('accountant.space.fusion') }}" onsubmit="return confirm('Confirmer le déversement ? Les données seront copiées vers l\'entreprise cible.');">
                            @csrf
                            <div class="mb-3">
                                <label>Entreprise source (données à copier)</label>
                                <select name="source_company_id" class="form-select form-select-sm">
                                    @foreach($companiesData as $d)
                                        <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Entreprise cible (destination)</label>
                                <select name="target_company_id" class="form-select form-select-sm">
                                    @foreach($companiesData as $d)
                                        <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Éléments à déverser</label>
                                <div class="d-flex flex-column gap-2 mt-2">
                                    <label style="text-transform:none; font-size:0.82rem; color:#94a3b8; display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
                                        <input type="checkbox" name="scope[]" value="accounts" class="form-check-input" checked>
                                        <i class="fas fa-book" style="color:#818cf8;"></i> Plan Comptable
                                    </label>
                                    <label style="text-transform:none; font-size:0.82rem; color:#94a3b8; display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
                                        <input type="checkbox" name="scope[]" value="journals" class="form-check-input" checked>
                                        <i class="fas fa-clipboard-list" style="color:#34d399;"></i> Codes Journaux
                                    </label>
                                    <label style="text-transform:none; font-size:0.82rem; color:#94a3b8; display:flex; align-items:center; gap:0.6rem; cursor:pointer;">
                                        <input type="checkbox" name="scope[]" value="tiers" class="form-check-input">
                                        <i class="fas fa-user-tag" style="color:#fbbf24;"></i> Plan Tiers
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm w-100" style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:white; border:none; border-radius:10px; font-weight:700; padding:0.65rem;">
                                <i class="fas fa-rocket me-1"></i>Lancer le déversement
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="space-form-card h-100">
                        <div class="section-title-dark mb-3"><i class="fas fa-info-circle" style="color:#6366f1;"></i>Informations</div>
                        <div style="color:#64748b; font-size:0.82rem; line-height:1.8;">
                            <p><strong style="color:#94a3b8;">Comment fonctionne le déversement ?</strong></p>
                            <ul style="padding-left:1.2rem;">
                                <li>Seuls les éléments <strong style="color:#818cf8;">non existants</strong> dans l'entreprise cible sont copiés.</li>
                                <li>Les doublons (même numéro de compte, même code journal) sont ignorés.</li>
                                <li>Les écritures comptables <strong style="color:#f87171;">ne sont jamais transférées</strong>.</li>
                                <li>L'opération est <strong style="color:#34d399;">réversible</strong> : vous pouvez supprimer manuellement les éléments déversés.</li>
                            </ul>
                            <p class="mt-3"><strong style="color:#94a3b8;">Cas d'usage :</strong> Utiliser la configuration d'une entreprise principale pour initialiser rapidement une filiale ou une nouvelle comptabilité.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4 : CHAT -->
        <div class="tab-content-section" id="section-chat">
            <div class="chat-container">
                <!-- Chat Sidebar -->
                <div class="chat-sidebar">
                    <div style="padding:0.85rem 1rem; font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#475569; border-bottom:1px solid rgba(255,255,255,0.06);">Discussions</div>
                    @forelse($chatUsers as $cu)
                    <div class="chat-user-item" id="chat-user-{{ $cu->id }}" onclick="openChat({{ $cu->id }}, '{{ $cu->name }} {{ $cu->last_name }}', '{{ strtoupper(substr($cu->name,0,1).substr($cu->last_name??'',0,1)) }}')">
                        <div class="chat-avatar-sm">{{ strtoupper(substr($cu->name,0,1).substr($cu->last_name??'',0,1)) }}</div>
                        <div>
                            <div class="chat-user-name">{{ $cu->name }} {{ $cu->last_name }}</div>
                            <div class="chat-user-role">{{ $cu->role }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="padding:1.5rem; text-align:center; color:#475569; font-size:0.75rem;">Aucun contact disponible</div>
                    @endforelse
                </div>
                <!-- Chat Main -->
                <div class="chat-main">
                    <div class="chat-header" id="chat-header">
                        <div style="color:#64748b; font-size:0.82rem;">Sélectionnez un contact pour démarrer la discussion</div>
                    </div>
                    <div class="chat-messages" id="chat-messages">
                        <div style="text-align:center; color:#334155; padding:3rem 0;">
                            <i class="fas fa-comments" style="font-size:2.5rem; margin-bottom:0.75rem; display:block;"></i>
                            Sélectionnez un contact
                        </div>
                    </div>
                    <div class="chat-input-area" id="chat-input-area" style="display:none;">
                        <input type="hidden" id="current_recipient_id" value="">
                        <textarea class="chat-input" id="chat-message-input" rows="1" placeholder="Écrire un message..."></textarea>
                        <button class="chat-send" onclick="sendChatMessage()"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- end space-body -->
</div><!-- end content-wrapper -->
</div><!-- end layout-page -->
</div><!-- end layout-container -->
</div><!-- end layout-wrapper -->

<!-- Modal : Nouvelle Entreprise -->
<div class="modal fade" id="newCompanyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:18px;">
            <div class="modal-header" style="border-color:rgba(255,255,255,0.08);">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-building me-2" style="color:#6366f1;"></i>Créer une nouvelle entreprise</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('accountant.space.company.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="space-alert info">
                        <i class="fas fa-key"></i>
                        Un code unique sécurisé sera automatiquement généré pour cette entreprise.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Nom de l'entreprise *</label><input type="text" class="form-control" name="company_name" required placeholder="Ex: Groupe ABC SARL" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Email *</label><input type="email" class="form-control" name="email_adresse" required placeholder="contact@entreprise.com" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Activité *</label><input type="text" class="form-control" name="activity" required placeholder="Commerce, Services, BTP..." style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Forme juridique *</label><select name="juridique_form" class="form-select" required style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"><option>SARL</option><option>SA</option><option>SAS</option><option>SASU</option><option>SNC</option><option>EI</option><option>EIRL</option><option>Association</option><option>ONG</option></select></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Capital social</label><input type="number" class="form-control" name="social_capital" placeholder="1000000" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Téléphone *</label><input type="text" class="form-control" name="phone_number" required placeholder="+225 XX XX XX XX" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-8"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Adresse *</label><input type="text" class="form-control" name="adresse" required placeholder="Rue, Avenue, Quartier" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-4"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Code postal *</label><input type="text" class="form-control" name="code_postal" required placeholder="01 BP..." style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Ville *</label><input type="text" class="form-control" name="city" required placeholder="Abidjan, Dakar..." style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-md-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Pays *</label><input type="text" class="form-control" name="country" required value="Côte d'Ivoire" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:rgba(255,255,255,0.08);">
                    <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;border-radius:10px;" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border:none;border-radius:10px;font-weight:700;padding:0.55rem 1.5rem;">
                        <i class="fas fa-plus me-1"></i>Créer l'entreprise
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal : Nouveau Collaborateur -->
<div class="modal fade" id="newMemberModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:18px;">
            <div class="modal-header" style="border-color:rgba(255,255,255,0.08);">
                <h5 class="modal-title text-white fw-bold"><i class="fas fa-user-plus me-2" style="color:#10b981;"></i>Créer un collaborateur</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('accountant.space.member.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Prénom *</label><input type="text" class="form-control" name="name" required placeholder="Jean" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-6"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Nom *</label><input type="text" class="form-control" name="last_name" required placeholder="DUPONT" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-12"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Email *</label><input type="email" class="form-control" name="email_adresse" required placeholder="jean.dupont@email.com" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-12"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Mot de passe *</label><input type="password" class="form-control" name="password" required placeholder="Minimum 6 caractères" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"></div>
                        <div class="col-12"><label style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#64748b;">Rôle *</label><select name="role" class="form-select" required style="background:#1e293b;border:1px solid rgba(255,255,255,0.1);color:white;border-radius:10px;"><option value="admin">Admin</option><option value="comptable">Comptable</option></select></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-color:rgba(255,255,255,0.08);">
                    <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;border-radius:10px;" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-sm" style="background:linear-gradient(135deg,#10b981,#059669);color:white;border:none;border-radius:10px;font-weight:700;padding:0.55rem 1.5rem;">
                        <i class="fas fa-user-plus me-1"></i>Créer le collaborateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

<script>
function switchSpaceTab(tab) {
    // Update tabs
    document.querySelectorAll('.space-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');

    // Update sections
    document.querySelectorAll('.tab-content-section').forEach(s => s.classList.remove('active'));
    document.getElementById('section-' + tab).classList.add('active');

    if (tab === 'chat') {
        // Auto-select first chat user if available
        const firstUser = document.querySelector('.chat-user-item');
        if (firstUser) firstUser.click();
    }
}

let currentRecipientId = null;

function openChat(userId, userName, initials) {
    currentRecipientId = userId;
    document.getElementById('current_recipient_id').value = userId;

    // Update active state
    document.querySelectorAll('.chat-user-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('chat-user-' + userId);
    if (el) el.classList.add('active');

    document.getElementById('chat-header').innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <div class="chat-avatar-sm">${initials}</div>
            <div>
                <div style="color:white;font-size:0.88rem;font-weight:700;">${userName}</div>
                <div style="color:#475569;font-size:0.65rem;">En ligne</div>
            </div>
        </div>`;

    document.getElementById('chat-input-area').style.display = 'flex';
    loadMessages(userId);
}

function loadMessages(userId) {
    fetch(`{{ route('accountant.space.chat.messages') }}?recipient_id=${userId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(messages => {
        const container = document.getElementById('chat-messages');
        if (messages.length === 0) {
            container.innerHTML = '<div style="text-align:center; color:#334155; padding:2rem; font-size:0.82rem;">Aucun message. Dites bonjour ! 👋</div>';
            return;
        }
        container.innerHTML = messages.map(m => `
            <div style="display:flex; flex-direction:column; align-items:${m.is_me ? 'flex-end' : 'flex-start'};">
                <div class="msg-bubble ${m.is_me ? 'msg-me' : 'msg-other'}">${m.message}</div>
                <div class="msg-time">${m.time}</div>
            </div>
        `).join('');
        container.scrollTop = container.scrollHeight;
    })
    .catch(() => {});
}

function sendChatMessage() {
    const input = document.getElementById('chat-message-input');
    const message = input.value.trim();
    if (!message || !currentRecipientId) return;

    fetch('{{ route("accountant.space.chat.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ recipient_id: currentRecipientId, message: message })
    })
    .then(r => r.json())
    .then(() => {
        input.value = '';
        loadMessages(currentRecipientId);
    });
}

document.getElementById('chat-message-input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChatMessage(); }
});

// Auto-open tab from query param
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');
if (tabParam && ['dashboard', 'collaborators', 'fusion', 'chat'].includes(tabParam)) {
    switchSpaceTab(tabParam);
}

// Auto-open chat user from query param
const chatUserId = urlParams.get('chat_user_id');
if (chatUserId) {
    setTimeout(() => {
        const el = document.getElementById('chat-user-' + chatUserId);
        if (el) el.click();
    }, 500);
}
</script>
</body>
</html>
