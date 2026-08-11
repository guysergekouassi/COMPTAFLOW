<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap');

:root {
    --space-bg: #f8fafc;
    --space-surface: #ffffff;
    --space-card: #ffffff;
    --space-card-hover: #f8fafc;
    --space-border: #e2e8f0;
    --space-border-hover: rgba(30, 64, 175, 0.25);
    --blue: #1e40af;
    --blue-dark: #1d4ed8;
    --blue-glow: rgba(30, 64, 175, 0.05);
    --green: #10b981;
    --green-glow: rgba(16, 185, 129, 0.05);
    --violet: #8b5cf6;
    --amber: #b45309;
    --rose: #ef4444;
    --text-primary: #1e293b;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
}

* { box-sizing: border-box; }

body {
    background: var(--space-bg);
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: var(--text-primary);
    min-height: 100vh;
}

/* ── LAYOUT ── */
.espace-wrapper { min-height: 100vh; }

.espace-content {
    padding: 2rem;
    background: #f8fafc;
}

/* ── SIDEBAR NAV LINKS ── */
.space-nav-section-title {
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
    padding: 0.75rem 0.75rem 0.35rem;
    margin-top: 0.75rem;
}

.space-nav-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.85rem;
    border-radius: 10px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.space-nav-link:hover {
    background: rgba(30, 64, 175, 0.04);
    color: var(--blue);
}

.space-nav-link.active {
    background: rgba(30, 64, 175, 0.08);
    color: var(--blue);
    border: 1px solid rgba(30, 64, 175, 0.12);
}

.space-nav-link .nav-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
    background: rgba(0,0,0,0.03);
    transition: all 0.2s;
    color: var(--text-secondary);
}

.space-nav-link.active .nav-icon { background: rgba(30, 64, 175, 0.12); color: var(--blue); }
.space-nav-link:hover .nav-icon { background: rgba(30, 64, 175, 0.08); color: var(--blue); }

/* ── HERO ── */
.space-hero {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
    border-radius: 20px;
    padding: 2.5rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
    border: 1px solid rgba(30, 64, 175, 0.15);
    box-shadow: 0 10px 25px -5px rgba(30, 64, 175, 0.1);
}

.space-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 250px; height: 250px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    pointer-events: none;
}

.space-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    pointer-events: none;
}

.hero-avatar {
    width: 60px; height: 60px;
    border-radius: 16px;
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; font-weight: 800; color: white;
    backdrop-filter: blur(10px);
}

/* ── KPI CARDS ── */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem; }

.kpi-card {
    background: var(--space-card);
    border: 1px solid var(--space-border);
    border-radius: 16px;
    padding: 1.4rem;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    border-radius: 16px 16px 0 0;
}

.kpi-card.blue::before { background: linear-gradient(90deg, #1e40af, #3b82f6); }
.kpi-card.green::before { background: linear-gradient(90deg, #10b981, #34d399); }
.kpi-card.violet::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.kpi-card.amber::before { background: linear-gradient(90deg, #b45309, #fbbf24); }
.kpi-card.rose::before { background: linear-gradient(90deg, #ef4444, #fb7185); }

.kpi-card:hover { border-color: var(--space-border-hover); transform: translateY(-2px); background: var(--space-card-hover); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }

.kpi-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; margin-bottom: 1rem;
}
.kpi-icon.blue { background: rgba(30,64,175,0.06); color: var(--blue); }
.kpi-icon.green { background: rgba(16,185,129,0.06); color: var(--green); }
.kpi-icon.violet { background: rgba(139,92,246,0.06); color: var(--violet); }
.kpi-icon.amber { background: rgba(180,83,9,0.06); color: var(--amber); }
.kpi-icon.rose { background: rgba(239,68,68,0.06); color: var(--rose); }

.kpi-value { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
.kpi-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-top: 4px; }

/* ── SECTION HEADER ── */
.section-header {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.25rem;
}
.section-title { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); }
.section-divider { flex: 1; height: 1px; background: var(--space-border); }

/* ── COMPANY CARDS ── */
.companies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; }

.company-card {
    background: var(--space-card);
    border: 1px solid var(--space-border);
    border-radius: 18px;
    overflow: hidden;
    transition: all 0.3s;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
}
.company-card:hover { border-color: rgba(30,64,175,0.3); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

.company-card-header {
    padding: 1.25rem 1.25rem 1rem;
    border-bottom: 1px solid var(--space-border);
    background: rgba(0,0,0,0.01);
    display: flex; align-items: center; gap: 0.85rem;
}

.company-logo {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem; font-weight: 800; color: white; text-transform: uppercase;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(30,64,175,0.2);
}

.company-name { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); }
.company-type { font-size: 0.7rem; color: var(--text-muted); margin-top: 2px; font-weight: 500; }

.company-body { padding: 1.25rem; }

/* Code d'accès dans la carte */
.code-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(16,185,129,0.08);
    border: 1px dashed rgba(16,185,129,0.35);
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem; font-weight: 800; color: #10b981;
    letter-spacing: 0.06em;
    margin-bottom: 1rem;
}
.code-badge.missing {
    background: rgba(239,68,68,0.06);
    border-color: rgba(239,68,68,0.3);
    color: #ef4444;
    font-family: inherit;
    font-size: 0.75rem;
}

.company-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1rem; }
.ckpi { text-align: center; background: rgba(0,0,0,0.02); border: 1px solid var(--space-border); border-radius: 8px; padding: 0.5rem 0.25rem; }
.ckpi-val { font-size: 1rem; font-weight: 800; color: var(--blue); line-height: 1.2; }
.ckpi-lbl { font-size: 0.58rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }

.company-actions { display: flex; gap: 0.6rem; }

.btn-work {
    flex: 1; background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white; border: none; border-radius: 10px;
    padding: 0.6rem; font-size: 0.78rem; font-weight: 700;
    cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(30,64,175,0.15);
}
.btn-work:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(30,64,175,0.25); color: white; }

.btn-secondary-dark {
    background: rgba(0,0,0,0.03);
    border: 1px solid var(--space-border);
    color: var(--text-secondary);
    border-radius: 10px; padding: 0.6rem 0.9rem;
    font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
    display: flex; align-items: center; gap: 0.4rem;
}
.btn-secondary-dark:hover { background: rgba(0,0,0,0.06); color: var(--text-primary); border-color: rgba(0,0,0,0.1); }

.btn-generate {
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.25);
    color: #10b981;
    border-radius: 8px; padding: 0.35rem 0.7rem;
    font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
}
.btn-generate:hover { background: rgba(16,185,129,0.14); }

/* ── TABLES ── */
.dark-table { width: 100%; border-collapse: collapse; }
.dark-table th {
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--text-muted);
    padding: 0.9rem 1rem; border-bottom: 1px solid var(--space-border);
    text-align: left; background: rgba(0,0,0,0.01);
}
.dark-table td {
    padding: 1rem; border-bottom: 1px solid var(--space-border);
    font-size: 0.85rem; color: var(--text-secondary);
    vertical-align: middle; font-weight: 500;
}
.dark-table tr:hover td { background: rgba(0,0,0,0.015); }
.dark-table tr:last-child td { border-bottom: none; }

.avatar-sm {
    width: 34px; height: 34px; border-radius: 9px;
    background: linear-gradient(135deg, #1e40af, #3b82f6);
    color: white; font-size: 0.7rem; font-weight: 800;
    display: inline-flex; align-items: center; justify-content: center;
    text-transform: uppercase;
}

.badge-role {
    padding: 0.3rem 0.7rem; border-radius: 6px;
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
}
.badge-admin { background: rgba(30,64,175,0.08); color: var(--blue); }
.badge-comptable { background: rgba(0,0,0,0.04); color: var(--text-secondary); }

.btn-danger-sm {
    background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2);
    color: #ef4444; border-radius: 7px; padding: 0.3rem 0.7rem;
    font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.2s;
}
.btn-danger-sm:hover { background: rgba(239,68,68,0.12); border-color: rgba(239,68,68,0.3); }

/* ── FORMS ── */
.dark-card {
    background: var(--space-card);
    border: 1px solid var(--space-border);
    border-radius: 18px;
    padding: 1.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
}

.dark-label {
    display: block;
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.07em; color: var(--text-muted); margin-bottom: 0.4rem;
}

.dark-input {
    width: 100%; background: rgba(0,0,0,0.02);
    border: 1px solid var(--space-border);
    color: var(--text-primary);
    border-radius: 10px; padding: 0.7rem 0.9rem;
    font-size: 0.85rem; font-weight: 500; outline: none; transition: all 0.2s;
    font-family: inherit;
}
.dark-input:focus { border-color: rgba(30,64,175,0.4); background: rgba(30,64,175,0.02); box-shadow: 0 0 0 3px rgba(30,64,175,0.08); }
.dark-input::placeholder { color: var(--text-muted); }

/* ── ALERTS ── */
.space-alert {
    border-radius: 12px; padding: 0.85rem 1.25rem;
    font-size: 0.82rem; font-weight: 600;
    display: flex; align-items: center; gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.space-alert.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #10b981; }
.space-alert.error { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.18); color: #ef4444; }
.space-alert.info { background: rgba(30,64,175,0.08); border: 1px solid rgba(30,64,175,0.18); color: var(--blue); }

/* ── CHAT ── */
.chat-wrap { display: flex; height: 540px; border: 1px solid var(--space-border); border-radius: 18px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03); }
.chat-contacts { width: 260px; background: var(--space-surface); border-right: 1px solid var(--space-border); overflow-y: auto; flex-shrink: 0; }
.chat-contact-item { padding: 0.85rem 1rem; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; transition: all 0.2s; border-bottom: 1px solid var(--space-border); }
.chat-contact-item:hover, .chat-contact-item.active { background: rgba(30,64,175,0.04); border-left: 3px solid var(--blue); }
.chat-main { flex: 1; display: flex; flex-direction: column; background: var(--space-card); }
.chat-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--space-border); background: var(--space-surface); }
.chat-messages { flex: 1; overflow-y: auto; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; background: #f8fafc; }
.msg-me { background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; border-radius: 14px 14px 4px 14px; padding: 0.7rem 1rem; align-self: flex-end; max-width: 65%; font-size: 0.85rem; box-shadow: 0 4px 10px rgba(30,64,175,0.15); }
.msg-other { background: var(--space-card); border: 1px solid var(--space-border); color: var(--text-secondary); border-radius: 14px 14px 14px 4px; padding: 0.7rem 1rem; align-self: flex-start; max-width: 65%; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.msg-time { font-size: 0.6rem; color: var(--text-muted); margin-top: 3px; }
.chat-input-area { padding: 1rem 1.25rem; border-top: 1px solid var(--space-border); display: flex; gap: 0.75rem; background: var(--space-surface); }
.chat-input { flex: 1; background: rgba(0,0,0,0.02); border: 1px solid var(--space-border); color: var(--text-primary); border-radius: 10px; padding: 0.65rem 0.9rem; font-size: 0.85rem; outline: none; resize: none; font-family: inherit; transition: all 0.2s; }
.chat-input:focus { border-color: rgba(30,64,175,0.3); }
.chat-send { background: var(--blue); border: none; color: white; border-radius: 10px; padding: 0.65rem 1.1rem; cursor: pointer; transition: all 0.2s; }
.chat-send:hover { background: var(--blue-dark); }

/* ── FILTER KPI ── */
.filter-select {
    background: var(--space-card); border: 1px solid var(--space-border);
    color: var(--text-primary); border-radius: 10px; padding: 0.5rem 1rem;
    font-size: 0.82rem; font-weight: 600; outline: none; cursor: pointer;
}

/* ── MODALS ── */
.dark-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1060; display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
.dark-modal-backdrop.show { display: flex; }
.dark-modal { background: var(--space-card); border: 1px solid var(--space-border); border-radius: 22px; padding: 2rem; width: 100%; max-width: 580px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); animation: fadeInUp 0.25s ease; }
.dark-modal.large { max-width: 760px; max-height: 85vh; overflow-y: auto; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
.modal-close { background: rgba(0,0,0,0.04); border: 1px solid var(--space-border); color: var(--text-secondary); border-radius: 8px; padding: 0.3rem 0.6rem; cursor: pointer; font-size: 1rem; transition: all 0.2s; }
.modal-close:hover { background: rgba(239,68,68,0.08); color: #ef4444; border-color: rgba(239,68,68,0.2); }

/* ── SECTIONS ── */
.page-section { display: none; }
.page-section.active { display: block; }

@media (max-width: 1100px) {
    .espace-wrapper { margin-left: 0; flex-direction: column; }
    .espace-sidebar { width: 100%; height: auto; position: relative; flex-direction: row; flex-wrap: wrap; padding: 1rem; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .kpi-grid { grid-template-columns: 1fr; }
    .companies-grid { grid-template-columns: 1fr; }
}
</style>

<body>

<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
    @include('components.sidebar')
    <div class="layout-page">
        @include('components.header', ['page_title' => 'Mon Espace'])

        <div class="content-wrapper p-0">
        <div class="espace-wrapper">
            <div class="espace-content">

                @if(session('success'))
                <div class="space-alert success"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="space-alert error"><i class="fas fa-exclamation-circle"></i>{{ session('error') }}</div>
                @endif
                @if(session('info'))
                <div class="space-alert info"><i class="fas fa-info-circle"></i>{{ session('info') }}</div>
                @endif

                <!-- ──────────────── DASHBOARD ──────────────── -->
                <div class="page-section active" id="section-dashboard">

                    <!-- Hero -->
                    <div class="space-hero">
                        <div class="d-flex align-items-center gap-4 position-relative" style="z-index:1;">
                            <div class="hero-avatar">{{ strtoupper(substr(auth()->user()->name,0,1).substr(auth()->user()->last_name??'',0,1)) }}</div>
                            <div>
                                <div style="color:rgba(255,255,255,0.6);font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">Portail de gestion multi-sociétés</div>
                                <h2 style="color:white;font-weight:800;font-size:1.5rem;margin:0;">Bonjour, {{ auth()->user()->name }} {{ auth()->user()->last_name }}</h2>
                                <div style="color:rgba(255,255,255,0.75);font-size:0.82rem;margin-top:4px;">{{ $stats['total_companies'] }} société(s) · {{ number_format($stats['total_entries']) }} écritures au total</div>
                            </div>
                        </div>
                    </div>

                    <!-- KPIs globaux -->
                    <div class="kpi-grid">
                        <div class="kpi-card blue">
                            <div class="kpi-icon blue"><i class="fas fa-building"></i></div>
                            <div class="kpi-value">{{ $stats['total_companies'] }}</div>
                            <div class="kpi-label">Sociétés gérées</div>
                        </div>
                        <div class="kpi-card green">
                            <div class="kpi-icon green"><i class="fas fa-file-invoice"></i></div>
                            <div class="kpi-value">{{ number_format($stats['total_entries']) }}</div>
                            <div class="kpi-label">Écritures totales</div>
                        </div>
                        <div class="kpi-card violet">
                            <div class="kpi-icon violet"><i class="fas fa-user-friends"></i></div>
                            <div class="kpi-value">{{ $stats['total_collaborators'] }}</div>
                            <div class="kpi-label">Collaborateurs</div>
                        </div>
                        <div class="kpi-card amber">
                            <div class="kpi-icon amber"><i class="fas fa-link"></i></div>
                            <div class="kpi-value">{{ $stats['assigned_companies'] }}</div>
                            <div class="kpi-label">Affectations ext.</div>
                        </div>
                    </div>

                    <!-- KPIs financiers par société (filtrés) -->
                    <div class="dark-card mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4" style="flex-wrap:wrap;">
                            <div>
                                <div style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:2px;">Indicateurs Financiers</div>
                                <div style="font-size:0.82rem;color:var(--text-secondary);">Sélectionnez une société pour voir ses KPIs</div>
                            </div>
                            <select class="filter-select ms-auto" id="company-filter" onchange="filterCompanyKpi(this.value)">
                                <option value="all">Toutes les sociétés</option>
                                @foreach($companiesData as $d)
                                <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="kpi-grid" id="financial-kpis">
                            @php
                                $totalCa = $companiesData->sum('ca');
                                $totalTreso = $companiesData->sum('tresorerie');
                                $totalResult = $companiesData->sum('resultat_net');
                                $totalVentes = $companiesData->sum('ventes_count');
                            @endphp
                            <div class="kpi-card green">
                                <div class="kpi-icon green"><i class="fas fa-chart-line"></i></div>
                                <div class="kpi-value" style="font-size:1.2rem;">{{ number_format($totalCa, 0, ',', ' ') }}</div>
                                <div class="kpi-label">CA (FCFA)</div>
                            </div>
                            <div class="kpi-card blue">
                                <div class="kpi-icon blue"><i class="fas fa-shopping-cart"></i></div>
                                <div class="kpi-value">{{ $totalVentes }}</div>
                                <div class="kpi-label">Ventes (Écritures)</div>
                            </div>
                            <div class="kpi-card amber">
                                <div class="kpi-icon amber"><i class="fas fa-piggy-bank"></i></div>
                                <div class="kpi-value" style="font-size:1.1rem;">{{ number_format($totalTreso, 0, ',', ' ') }}</div>
                                <div class="kpi-label">Trésorerie (FCFA)</div>
                            </div>
                            <div class="kpi-card {{ $totalResult >= 0 ? 'green' : 'rose' }}">
                                <div class="kpi-icon {{ $totalResult >= 0 ? 'green' : 'rose' }}"><i class="fas fa-balance-scale"></i></div>
                                <div class="kpi-value" style="font-size:1.1rem; color:{{ $totalResult >= 0 ? 'var(--green)' : 'var(--rose)' }}">{{ number_format($totalResult, 0, ',', ' ') }}</div>
                                <div class="kpi-label">Résultat Net (FCFA)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Data hidden for JS filtering -->
                    <div id="companies-kpi-data" style="display:none;">
                        @foreach($companiesData as $d)
                        <span data-id="{{ $d['model']->id }}"
                              data-ca="{{ $d['ca'] }}"
                              data-ventes="{{ $d['ventes_count'] }}"
                              data-treso="{{ $d['tresorerie'] }}"
                              data-result="{{ $d['resultat_net'] }}"></span>
                        @endforeach
                    </div>

                </div>

                <!-- ──────────────── MES SOCIÉTÉS ──────────────── -->
                <div class="page-section" id="section-companies">
                    <div class="section-header">
                        <div class="section-title">Mon portefeuille sociétés</div>
                        <div class="section-divider"></div>
                        <button class="btn-work" onclick="document.getElementById('modal-new-company').classList.add('show')" style="flex:0;white-space:nowrap;padding:0.5rem 1rem;font-size:0.75rem;">
                            <i class="fas fa-plus"></i> Nouvelle
                        </button>
                    </div>

                    <div class="companies-grid">
                        @forelse($companiesData as $data)
                        @php $comp = $data['model']; @endphp
                        <div class="company-card">
                            <div class="company-card-header">
                                <div class="company-logo">{{ strtoupper(substr($comp->company_name,0,2)) }}</div>
                                <div style="flex:1; min-width:0;">
                                    <div class="company-name">{{ $comp->company_name }}</div>
                                    <div class="company-type">
                                        @if($data['is_owner'])
                                            <span style="color:#34d399;">★ Propriétaire</span>
                                        @else
                                            <span style="color:var(--blue);">→ Affecté</span>
                                        @endif
                                        &nbsp;· {{ $comp->activity }}
                                    </div>
                                </div>
                                @if($comp->is_blocked)
                                <span style="background:rgba(244,63,94,0.1);color:#fb7185;border-radius:6px;padding:2px 8px;font-size:0.62rem;font-weight:800;">BLOQUÉE</span>
                                @endif
                            </div>

                            <div class="company-body">
                                <!-- Code d'accès -->
                                @if($comp->company_code)
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="code-badge"><i class="fas fa-key"></i>{{ $comp->company_code }}</div>
                                    <button onclick="navigator.clipboard.writeText('{{ $comp->company_code }}');this.innerHTML='✓';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',1500);" class="btn-secondary-dark" style="padding:0.3rem 0.6rem;font-size:0.7rem;" title="Copier">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                @else
                                <div class="code-badge missing mb-3"><i class="fas fa-exclamation-triangle"></i>Code non généré</div>
                                @endif

                                <!-- Mini KPIs -->
                                <div class="company-kpis mb-3">
                                    <div class="ckpi">
                                        <div class="ckpi-val">{{ number_format($data['entries_count']) }}</div>
                                        <div class="ckpi-lbl">Écritures</div>
                                    </div>
                                    <div class="ckpi">
                                        <div class="ckpi-val">{{ $data['accounts_count'] }}</div>
                                        <div class="ckpi-lbl">Comptes</div>
                                    </div>
                                    <div class="ckpi">
                                        <div class="ckpi-val">{{ $data['tiers_count'] }}</div>
                                        <div class="ckpi-lbl">Tiers</div>
                                    </div>
                                    <div class="ckpi">
                                        <div class="ckpi-val" style="font-size:0.85rem;">{{ $data['exercice_actif'] }}</div>
                                        <div class="ckpi-lbl">Exercice</div>
                                    </div>
                                </div>

                                <!-- Équipe -->
                                @if($data['assigned_users']->count() > 0)
                                <div class="d-flex align-items-center gap-1 mb-3" style="flex-wrap:wrap;">
                                    <span style="font-size:0.6rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;margin-right:4px;">Équipe :</span>
                                    @foreach($data['assigned_users']->take(5) as $au)
                                    <span title="{{ $au->name }} ({{ $au->role }})"
                                          style="width:26px;height:26px;border-radius:7px;background:rgba(59,130,246,0.12);color:var(--blue);font-size:0.6rem;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">
                                        {{ strtoupper(substr($au->name,0,1).substr($au->last_name??'',0,1)) }}
                                    </span>
                                    @endforeach
                                    @if($data['assigned_users']->count() > 5)
                                    <span style="font-size:0.7rem;color:var(--text-muted);">+{{ $data['assigned_users']->count()-5 }}</span>
                                    @endif
                                </div>
                                @endif

                                <div class="company-actions">
                                    <a href="{{ route('accountant.space.switch', $comp->id) }}" class="btn-work" style="width:100%; text-align:center; justify-content:center;">
                                        <i class="fas fa-arrow-right"></i> Travailler
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="dark-card text-center" style="grid-column:1/-1;padding:3rem;">
                            <i class="fas fa-building" style="font-size:2.5rem;color:var(--text-muted);display:block;margin-bottom:1rem;"></i>
                            <div style="font-weight:700;color:var(--text-primary);margin-bottom:0.5rem;">Aucune société</div>
                            <div style="color:var(--text-muted);font-size:0.82rem;margin-bottom:1.5rem;">Créez votre première entreprise pour commencer.</div>
                            <button class="btn-work" onclick="document.getElementById('modal-new-company').classList.add('show')" style="flex:0;margin:0 auto;">
                                <i class="fas fa-plus"></i> Créer une société
                            </button>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- ──────────────── COLLABORATEURS ──────────────── -->
                <div class="page-section" id="section-collaborators">
                    <div class="row g-4">
                        <!-- Formulaire affecter -->
                        <div class="col-lg-4">
                            <div class="dark-card">
                                <div style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--blue);margin-bottom:1.25rem;"><i class="fas fa-user-plus me-2"></i>Affecter un collaborateur</div>
                                <form method="POST" action="{{ route('accountant.space.assign') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="dark-label">Entreprise cible</label>
                                        <select name="company_id" class="dark-input">
                                            @foreach($companiesData as $d)
                                            <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="dark-label">Collaborateur</label>
                                        <select name="user_id" class="dark-input">
                                            @foreach($collaborators as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} {{ $c->last_name }} ({{ $c->role }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="dark-label">Rôle</label>
                                        <select name="role" class="dark-input">
                                            <option value="admin">Admin</option>
                                            <option value="comptable">Comptable</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-work w-100" style="justify-content:center;">
                                        <i class="fas fa-link me-1"></i>Lier à l'entreprise
                                    </button>
                                </form>
                            </div>
                        </div>
                        <!-- Tableau -->
                        <div class="col-lg-8">
                            <div class="dark-card">
                                <div style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--blue);margin-bottom:1.25rem;"><i class="fas fa-users me-2"></i>Mes collaborateurs</div>
                                <div class="table-responsive">
                                    <table class="dark-table">
                                        <thead>
                                            <tr>
                                                <th>Collaborateur</th>
                                                <th>Email</th>
                                                <th>Rôle</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($collaborators as $collab)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-sm">{{ strtoupper(substr($collab->name,0,1).substr($collab->last_name??'',0,1)) }}</div>
                                                        <span style="color:var(--text-primary);font-weight:600;">{{ $collab->name }} {{ $collab->last_name }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $collab->email_adresse }}</td>
                                                <td><span class="badge-role badge-{{ $collab->role }}">{{ $collab->role }}</span></td>
                                                <td>
                                                    @if($collab->is_active)
                                                    <span style="background:rgba(16,185,129,0.1);color:#34d399;padding:0.25rem 0.6rem;border-radius:6px;font-size:0.65rem;font-weight:700;">Actif</span>
                                                    @else
                                                    <span style="background:rgba(244,63,94,0.1);color:#fb7185;padding:0.25rem 0.6rem;border-radius:6px;font-size:0.65rem;font-weight:700;">Inactif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <!-- Retirer accès par société -->
                                                    @foreach($companiesData as $d)
                                                        @if($d['assigned_users']->contains('id', $collab->id))
                                                        <form method="POST" action="{{ route('accountant.space.remove_user') }}" onsubmit="return confirm('Retirer l\'accès de {{ $collab->name }} à {{ $d['model']->company_name }} ?');" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="company_id" value="{{ $d['model']->id }}">
                                                            <input type="hidden" name="user_id" value="{{ $collab->id }}">
                                                            <button type="submit" class="btn-danger-sm" title="Retirer de {{ $d['model']->company_name }}">
                                                                <i class="fas fa-unlink"></i> {{ Str::limit($d['model']->company_name, 12) }}
                                                            </button>
                                                        </form>
                                                        @endif
                                                    @endforeach
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">Aucun collaborateur</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ──────────────── FUSION ──────────────── -->
                <div class="page-section" id="section-fusion">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="dark-card">
                                <div style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--blue);margin-bottom:1.25rem;"><i class="fas fa-exchange-alt me-2"></i>Déverser la configuration</div>
                                <div class="space-alert info mb-4">
                                    <i class="fas fa-info-circle"></i>
                                    Copie la structure (plan, journaux, tiers) d'une société source vers une cible. Aucune écriture n'est transférée.
                                </div>
                                <form method="POST" action="{{ route('accountant.space.fusion') }}" onsubmit="return confirm('Confirmer le déversement ?');">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="dark-label">Source (données à copier)</label>
                                        <select name="source_company_id" class="dark-input">
                                            @foreach($companiesData as $d)
                                            <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="dark-label">Cible (destination)</label>
                                        <select name="target_company_id" class="dark-input">
                                            @foreach($companiesData as $d)
                                            <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="dark-label" style="margin-bottom:0.75rem;">Éléments à copier</label>
                                        <div class="d-flex flex-column gap-2">
                                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.85rem;color:var(--text-secondary);">
                                                <input type="checkbox" name="scope[]" value="accounts" checked style="accent-color:var(--blue);"> Plan Comptable
                                            </label>
                                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.85rem;color:var(--text-secondary);">
                                                <input type="checkbox" name="scope[]" value="journals" checked style="accent-color:var(--blue);"> Codes Journaux
                                            </label>
                                            <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;font-size:0.85rem;color:var(--text-secondary);">
                                                <input type="checkbox" name="scope[]" value="tiers" style="accent-color:var(--blue);"> Fiches Tiers
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn-work w-100" style="justify-content:center;">
                                        <i class="fas fa-rocket me-1"></i>Lancer le déversement
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="dark-card" style="height:100%;">
                                <div style="font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:1.25rem;"><i class="fas fa-info-circle me-2"></i>Comment ça fonctionne</div>
                                <div style="color:var(--text-secondary);font-size:0.85rem;line-height:1.8;">
                                    <p class="mb-3">Le déversement inter-sociétés standardise vos comptabilités :</p>
                                    <ul style="padding-left:1.2rem;display:flex;flex-direction:column;gap:0.75rem;">
                                        <li><strong style="color:var(--text-primary);">Doublons ignorés :</strong> Si un compte existe déjà dans la cible, il est conservé.</li>
                                        <li><strong style="color:var(--text-primary);">Isolation totale :</strong> Les écritures et transactions restent dans leurs dossiers.</li>
                                        <li><strong style="color:var(--text-primary);">Rapide :</strong> Idéal pour déployer un plan comptable type sur de nouvelles structures.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ──────────────── MESSAGERIE ──────────────── -->
                <div class="page-section" id="section-chat">
                    <div class="chat-wrap">
                        <div class="chat-contacts">
                            <div style="padding:0.85rem 1rem;font-size:0.62rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);border-bottom:1px solid var(--space-border);">Contacts</div>
                            @forelse($chatUsers as $cu)
                            <div class="chat-contact-item" id="contact-{{ $cu->id }}"
                                 onclick="openChat({{ $cu->id }},'{{ $cu->name }} {{ $cu->last_name }}','{{ strtoupper(substr($cu->name,0,1).substr($cu->last_name??'',0,1)) }}')">
                                <div class="avatar-sm">{{ strtoupper(substr($cu->name,0,1).substr($cu->last_name??'',0,1)) }}</div>
                                <div>
                                    <div style="color:var(--text-primary);font-size:0.82rem;font-weight:700;">{{ $cu->name }} {{ $cu->last_name }}</div>
                                    <div style="color:var(--text-muted);font-size:0.68rem;">{{ $cu->role }}</div>
                                </div>
                            </div>
                            @empty
                            <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:0.8rem;">Aucun contact</div>
                            @endforelse
                        </div>
                        <div class="chat-main">
                            <div class="chat-header" id="chat-header">
                                <div style="color:var(--text-muted);font-size:0.85rem;">Sélectionnez un contact</div>
                            </div>
                            <div class="chat-messages" id="chat-messages">
                                <div style="text-align:center;color:var(--text-muted);padding:4rem 0;">
                                    <i class="fas fa-comments" style="font-size:2.5rem;display:block;margin-bottom:0.75rem;"></i>
                                    Choisissez un contact pour démarrer
                                </div>
                            </div>
                            <div class="chat-input-area" id="chat-input-area" style="display:none;">
                                <input type="hidden" id="current_recipient_id">
                                <textarea class="chat-input" id="chat-msg-input" rows="1" placeholder="Écrire un message..."></textarea>
                                <button class="chat-send" onclick="sendMsg()"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        </div>
    </div>
</div>
</div>

<!-- ══════════ MODALS ══════════ -->

<!-- Nouvelle Société -->
<div class="dark-modal-backdrop" id="modal-new-company" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="dark-modal large">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 style="font-weight:800;color:var(--text-primary);margin:0;"><i class="fas fa-building me-2" style="color:var(--blue);"></i>Créer une nouvelle société</h5>
            <button class="modal-close" onclick="document.getElementById('modal-new-company').classList.remove('show')">✕</button>
        </div>
        <div class="space-alert info mb-4"><i class="fas fa-key"></i>Un code d'accès unique sera généré automatiquement.</div>
        <form method="POST" action="{{ route('accountant.space.company.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="dark-label">Nom de la société *</label><input type="text" name="company_name" class="dark-input" required placeholder="Ex: Groupe ABC SARL"></div>
                <div class="col-md-6"><label class="dark-label">Email *</label><input type="email" name="email_adresse" class="dark-input" required placeholder="contact@société.com"></div>
                <div class="col-md-6"><label class="dark-label">Activité *</label><input type="text" name="activity" class="dark-input" required placeholder="Commerce, BTP..."></div>
                <div class="col-md-6"><label class="dark-label">Forme juridique *</label>
                    <select name="juridique_form" class="dark-input" required>
                        <option>SARL</option><option>SA</option><option>SAS</option><option>SASU</option><option>SNC</option><option>EI</option><option>Association</option><option>ONG</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="dark-label">Capital social <span style="color:var(--text-muted);font-weight:500;font-size:0.7rem;">(optionnel)</span></label><input type="number" name="social_capital" class="dark-input" placeholder="1000000"></div>
                <div class="col-md-6"><label class="dark-label">Téléphone <span style="color:var(--text-muted);font-weight:500;font-size:0.7rem;">(optionnel)</span></label><input type="text" name="phone_number" class="dark-input" placeholder="+225 XX XX XX XX"></div>
                <div class="col-md-8"><label class="dark-label">Adresse <span style="color:var(--text-muted);font-weight:500;font-size:0.7rem;">(optionnel)</span></label><input type="text" name="adresse" class="dark-input" placeholder="Rue, Quartier..."></div>
                <div class="col-md-4"><label class="dark-label">Code postal <span style="color:var(--text-muted);font-weight:500;font-size:0.7rem;">(optionnel)</span></label><input type="text" name="code_postal" class="dark-input" placeholder="01 BP..."></div>
                <div class="col-md-6"><label class="dark-label">Ville <span style="color:var(--text-muted);font-weight:500;font-size:0.7rem;">(optionnel)</span></label><input type="text" name="city" class="dark-input" placeholder="Abidjan"></div>
                <div class="col-md-6"><label class="dark-label">Pays <span style="color:var(--text-muted);font-weight:500;font-size:0.7rem;">(optionnel)</span></label><input type="text" name="country" class="dark-input" placeholder="Côte d'Ivoire"></div>
            </div>
            <div class="d-flex gap-2 mt-4 justify-content-end">
                <button type="button" class="btn-secondary-dark" onclick="document.getElementById('modal-new-company').classList.remove('show')">Annuler</button>
                <button type="submit" class="btn-work" style="flex:0;padding:0.6rem 1.5rem;"><i class="fas fa-plus me-1"></i>Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Nouveau Membre -->
<div class="dark-modal-backdrop" id="modal-new-member" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="dark-modal">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 style="font-weight:800;color:var(--text-primary);margin:0;"><i class="fas fa-user-plus me-2" style="color:var(--green);"></i>Créer un collaborateur</h5>
            <button class="modal-close" onclick="document.getElementById('modal-new-member').classList.remove('show')">✕</button>
        </div>
        <form method="POST" action="{{ route('accountant.space.member.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-6"><label class="dark-label">Prénom *</label><input type="text" name="name" class="dark-input" required placeholder="Jean"></div>
                <div class="col-6"><label class="dark-label">Nom *</label><input type="text" name="last_name" class="dark-input" required placeholder="DUPONT"></div>
                <div class="col-12"><label class="dark-label">Email *</label><input type="email" name="email_adresse" class="dark-input" required placeholder="jean@email.com"></div>
                <div class="col-12"><label class="dark-label">Mot de passe *</label><input type="password" name="password" class="dark-input" required placeholder="Minimum 6 caractères"></div>
                <div class="col-12"><label class="dark-label">Rôle *</label>
                    <select name="role" class="dark-input" required>
                        <option value="admin">Admin</option>
                        <option value="comptable">Comptable</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4 justify-content-end">
                <button type="button" class="btn-secondary-dark" onclick="document.getElementById('modal-new-member').classList.remove('show')">Annuler</button>
                <button type="submit" class="btn-work" style="flex:0;padding:0.6rem 1.5rem;background:linear-gradient(135deg,#059669,#10b981);"><i class="fas fa-user-plus me-1"></i>Créer</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── NAVIGATION ──
function showSection(section) {
    document.querySelectorAll('.page-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.menu-link-new').forEach(l => {
        if (l.getAttribute('data-page-link')) {
            l.classList.remove('active');
        }
    });

    document.getElementById('section-' + section).classList.add('active');
    
    // Activer l'élément dans la sidebar principale
    const activeLink = document.querySelector(`.menu-link-new[data-page-link="${section}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }

    if (section === 'chat') {
        const first = document.querySelector('.chat-contact-item');
        if (first) first.click();
    }
    history.replaceState(null, '', '?page=' + section);
}

// Lire la page depuis l'URL
const urlPage = new URLSearchParams(window.location.search).get('page');
if (urlPage) showSection(urlPage);

// ── FILTRAGE KPIs FINANCIERS ──
function filterCompanyKpi(companyId) {
    const spans = document.querySelectorAll('#companies-kpi-data span');
    let ca = 0, ventes = 0, treso = 0, result = 0;
    spans.forEach(s => {
        if (companyId === 'all' || s.dataset.id === companyId) {
            ca += parseFloat(s.dataset.ca) || 0;
            ventes += parseInt(s.dataset.ventes) || 0;
            treso += parseFloat(s.dataset.treso) || 0;
            result += parseFloat(s.dataset.result) || 0;
        }
    });
    const fmt = n => Math.round(n).toLocaleString('fr-FR');
    const cards = document.querySelectorAll('#financial-kpis .kpi-card');
    cards[0].querySelector('.kpi-value').textContent = fmt(ca);
    cards[1].querySelector('.kpi-value').textContent = ventes;
    cards[2].querySelector('.kpi-value').textContent = fmt(treso);
    const resCard = cards[3];
    resCard.querySelector('.kpi-value').textContent = fmt(result);
    resCard.querySelector('.kpi-value').style.color = result >= 0 ? 'var(--green)' : 'var(--rose)';
}

// ── CHAT ──
let currentRecipient = null;

function openChat(id, name, initials) {
    currentRecipient = id;
    document.getElementById('current_recipient_id').value = id;
    document.querySelectorAll('.chat-contact-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('contact-' + id);
    if (el) el.classList.add('active');
    document.getElementById('chat-header').innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-sm">${initials}</div>
            <div>
                <div style="color:var(--text-primary);font-weight:700;font-size:0.85rem;">${name}</div>
                <div style="color:var(--green);font-size:0.68rem;font-weight:600;"><i class="fas fa-circle" style="font-size:0.4rem;"></i> En ligne</div>
            </div>
        </div>`;
    document.getElementById('chat-input-area').style.display = 'flex';
    loadMessages(id);
}

function loadMessages(id) {
    fetch(`{{ route('accountant.space.chat.messages') }}?recipient_id=${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(messages => {
        const c = document.getElementById('chat-messages');
        if (!messages.length) {
            c.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:2rem;font-size:0.82rem;">Aucun message. Dites bonjour ! 👋</div>';
            return;
        }
        c.innerHTML = messages.map(m => `
            <div style="display:flex;flex-direction:column;align-items:${m.is_me?'flex-end':'flex-start'};">
                <div class="${m.is_me?'msg-me':'msg-other'}">${m.message}</div>
                <div class="msg-time">${m.time}</div>
            </div>`).join('');
        c.scrollTop = c.scrollHeight;
    }).catch(()=>{});
}

function sendMsg() {
    const input = document.getElementById('chat-msg-input');
    const msg = input.value.trim();
    if (!msg || !currentRecipient) return;
    fetch('{{ route("accountant.space.chat.send") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ recipient_id: currentRecipient, message: msg })
    }).then(() => { input.value = ''; loadMessages(currentRecipient); });
}

document.getElementById('chat-msg-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
});
</script>
</body>
</html>
