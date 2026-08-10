<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    :root {
        --premium-blue: #1e40af;
        --premium-blue-light: #3b82f6;
        --premium-slate-900: #0f172a;
        --premium-slate-800: #1e293b;
        --premium-slate-400: #94a3b8;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(226, 232, 240, 0.8);
    }

    body {
        background: #f8fafc url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66 3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-45c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm54 24c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM57 11c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM25 34c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm23 40c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-3-47c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm47 9c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM9 53c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28 24c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm33-47c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-8 48c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-48-8c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm54-32c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM22 63c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm30-26c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28-4c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM59 71c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM33 18c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm44 64c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM9 29c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm37 13c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM61 81c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM4 62c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm35 24c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm47-9c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm7-48c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z' fill='%23e2e8f0' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--premium-slate-800);
        min-height: 100vh;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(30, 64, 175, 0.05);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .space-hero {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        padding: 3rem;
        border-radius: 24px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(30, 64, 175, 0.3);
        margin-bottom: 2rem;
    }
    
    .space-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 80% 50%, rgba(255,255,255,0.15) 0%, transparent 60%);
        pointer-events: none;
    }

    .hero-avatar {
        width: 64px; height: 64px;
        border-radius: 18px;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.4);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; font-weight: 800; color: white;
    }

    /* ── TABS ── */
    .space-tabs-container {
        display: flex;
        gap: 0.5rem;
        background: rgba(241, 245, 249, 0.8);
        padding: 0.4rem;
        border-radius: 14px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        margin-bottom: 2rem;
        width: fit-content;
    }
    
    .space-tab {
        padding: 0.65rem 1.25rem;
        font-size: 0.82rem;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        border: none;
        background: none;
        border-radius: 10px;
        transition: all 0.2s;
        display: flex; align-items: center; gap: 0.5rem;
    }
    
    .space-tab.active {
        background: white;
        color: var(--premium-blue);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    
    .space-tab:hover:not(.active) {
        color: var(--premium-blue-light);
        background: rgba(255,255,255,0.4);
    }

    /* ── COMPANY CARDS ── */
    .company-card {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
    }
    
    .company-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(30, 64, 175, 0.05);
        border-color: rgba(30, 64, 175, 0.2);
    }
    
    .company-card-header {
        padding: 1.5rem;
        display: flex; align-items: center; gap: 1rem;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    
    .company-logo {
        width: 48px; height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--premium-blue) 0%, var(--premium-blue-light) 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; font-weight: 800; color: white; text-transform: uppercase;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(30, 64, 175, 0.15);
    }
    
    .company-name { font-size: 0.95rem; font-weight: 700; color: var(--premium-slate-900); }
    .company-type { font-size: 0.72rem; font-weight: 600; color: var(--premium-slate-400); margin-top: 2px; }
    
    .company-body { padding: 1.5rem; }
    
    .company-kpi { display: flex; gap: 0.75rem; margin-bottom: 1.25rem; }
    .kpi-item { flex: 1; text-align: center; background: #f8fafc; padding: 0.6rem; border-radius: 10px; border: 1px solid #f1f5f9; }
    .kpi-val { font-size: 1.1rem; font-weight: 800; color: var(--premium-blue); line-height: 1.2; }
    .kpi-label { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--premium-slate-400); margin-top: 3px; }
    
    .company-code-badge {
        background: rgba(30, 64, 175, 0.05);
        border: 1px dashed rgba(30, 64, 175, 0.3);
        border-radius: 8px;
        padding: 0.4rem 0.75rem;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--premium-blue);
        font-family: 'Courier New', monospace;
        letter-spacing: 0.05em;
        display: inline-block;
        margin-bottom: 1.25rem;
    }
    
    .btn-work {
        background: linear-gradient(135deg, var(--premium-blue) 0%, var(--premium-blue-light) 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 0.4rem;
        transition: all 0.2s;
        box-shadow: 0 4px 10px rgba(30, 64, 175, 0.2);
    }
    .btn-work:hover { box-shadow: 0 8px 18px rgba(30, 64, 175, 0.3); transform: translateY(-1px); color: white; }
    
    .btn-details {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
        padding: 0.6rem 0.85rem;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 0.4rem;
        transition: all 0.2s;
    }
    .btn-details:hover { background: #e2e8f0; color: var(--premium-slate-900); }

    /* ── FORMS ── */
    .premium-form-card {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
    }
    
    .premium-form-card label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--premium-slate-400); margin-bottom: 0.4rem; display: block; }
    
    .premium-form-card .form-control, .premium-form-card .form-select {
        background: #f8fafc;
        border: 2px solid #f1f5f9;
        color: var(--premium-slate-900);
        border-radius: 12px;
        font-size: 0.85rem;
        padding: 0.75rem 1rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .premium-form-card .form-control:focus, .premium-form-card .form-select:focus {
        background: white;
        border-color: var(--premium-blue-light);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    /* ── TABLES ── */
    .premium-table { width: 100%; border-collapse: collapse; }
    
    .premium-table th {
        font-size: 0.7rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.08em; color: var(--premium-slate-400);
        padding: 1rem 1.25rem;
        border-bottom: 2px solid #f1f5f9;
        background: #f8fafc;
        text-align: left;
    }
    
    .premium-table td {
        font-size: 0.85rem; color: var(--premium-slate-800);
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-weight: 500;
    }
    
    .premium-table tr:hover td { background: #f8fafc; }
    
    .badge-role-collab {
        padding: 0.35rem 0.75rem; border-radius: 8px;
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    }
    
    .badge-collab-admin { background: #dbeafe; color: #1e40af; }
    .badge-collab-comptable { background: #f1f5f9; color: #475569; }

    /* ── CHAT UI ── */
    .chat-container { display: flex; height: 550px; border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 20px; overflow: hidden; background: white; }
    
    .chat-sidebar { width: 280px; border-right: 1px solid #f1f5f9; background: #f8fafc; overflow-y: auto; flex-shrink: 0; }
    
    .chat-user-item {
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;
        cursor: pointer; transition: all 0.2s; border-bottom: 1px solid #f1f5f9;
    }
    
    .chat-user-item:hover, .chat-user-item.active { background: white; box-shadow: inset 4px 0 0 var(--premium-blue); }
    
    .chat-avatar-sm {
        width: 38px; height: 38px; border-radius: 10px;
        background: linear-gradient(135deg, var(--premium-blue) 0%, var(--premium-blue-light) 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 800; color: white; text-transform: uppercase; flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(30, 64, 175, 0.15);
    }
    
    .chat-user-name { font-size: 0.85rem; font-weight: 700; color: var(--premium-slate-900); }
    .chat-user-role { font-size: 0.7rem; color: var(--premium-slate-400); margin-top: 1px; }
    
    .chat-main { flex: 1; display: flex; flex-direction: column; background: white; }
    
    .chat-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc; }
    
    .chat-messages { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; background: #fafbfc; }
    
    .msg-bubble { max-width: 70%; padding: 0.75rem 1.1rem; border-radius: 16px; font-size: 0.85rem; line-height: 1.4; }
    
    .msg-me { background: var(--premium-blue); color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 4px 10px rgba(30, 64, 175, 0.15); }
    
    .msg-other { background: #f1f5f9; color: var(--premium-slate-900); align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
    
    .msg-time { font-size: 0.62rem; color: var(--premium-slate-400); margin-top: 4px; text-align: right; }
    
    .chat-input-area { padding: 1.25rem; border-top: 1px solid #f1f5f9; display: flex; gap: 0.75rem; background: white; }
    
    .chat-input {
        flex: 1; background: #f8fafc; border: 2px solid #f1f5f9;
        color: var(--premium-slate-900); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.85rem;
        outline: none; resize: none; font-weight: 500; transition: all 0.2s;
    }
    
    .chat-input:focus { border-color: var(--premium-blue-light); background: white; }
    
    .chat-send { background: var(--premium-blue); border: none; color: white; border-radius: 12px; padding: 0.75rem 1.25rem; cursor: pointer; font-size: 1rem; transition: all 0.2s; box-shadow: 0 4px 10px rgba(30, 64, 175, 0.2); }
    
    .chat-send:hover { background: var(--premium-blue-light); transform: scale(1.02); }

    .tab-content-section { display: none; }
    .tab-content-section.active { display: block; }

    /* ── STAT CARDS ── */
    .stat-card-white {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 20px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01);
        transition: all 0.3s;
    }
    
    .stat-card-white:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.02);
        border-color: rgba(30,64,175,0.15);
    }
    
    .stat-icon-circle {
        width: 50px; height: 50px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
    }
    
    .stat-icon-circle.indigo { background: rgba(30, 64, 175, 0.08); color: var(--premium-blue); }
    .stat-icon-circle.violet { background: rgba(139, 92, 246, 0.08); color: #8b5cf6; }
    .stat-icon-circle.emerald { background: rgba(16, 185, 129, 0.08); color: #10b981; }
    .stat-icon-circle.amber { background: rgba(245, 158, 11, 0.08); color: #f59e0b; }

    /* ── ALERTS ── */
    .premium-alert {
        border-radius: 12px; padding: 0.85rem 1.25rem;
        font-size: 0.82rem; font-weight: 600;
        display: flex; align-items: center; gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .premium-alert.success { background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; }
    .premium-alert.error { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; }
    .premium-alert.info { background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af; }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
<div class="layout-container">
@include('components.sidebar')
<div class="layout-page">
@include('components.header', ['page_title' => 'Mon Espace Comptable'])

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y p-0">

        <!-- HERO -->
        <div class="space-hero">
            <div class="d-flex align-items-center gap-4 position-relative">
                <div class="hero-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}</div>
                <div>
                    <div style="color: rgba(255,255,255,0.7); font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:4px;">Portail de gestion multi-sociétés</div>
                    <h2 style="color:white; font-weight:800; font-size:1.6rem; margin:0;">Bonjour, {{ auth()->user()->name }} {{ auth()->user()->last_name }}</h2>
                    <div style="color:rgba(255,255,255,0.8); font-size:0.85rem; margin-top:4px;">{{ $stats['total_companies'] }} entreprise(s) dans votre portefeuille · {{ $stats['total_entries'] }} écritures au total</div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-sm btn-white shadow-sm" style="border-radius:12px; font-weight:700; padding:0.6rem 1.1rem; color:var(--premium-blue);" data-bs-toggle="modal" data-bs-target="#newCompanyModal">
                        <i class="fas fa-plus me-1"></i>Nouvelle entreprise
                    </button>
                    <button class="btn btn-sm" style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); color:white; border-radius:12px; font-weight:700; padding:0.6rem 1.1rem;" data-bs-toggle="modal" data-bs-target="#newMemberModal">
                        <i class="fas fa-user-plus me-1"></i>Collaborateur
                    </button>
                </div>
            </div>
        </div>

        <!-- TABS -->
        <div class="space-tabs-container">
            <button class="space-tab active" id="tab-dashboard" onclick="switchSpaceTab('dashboard')"><i class="fas fa-th-large"></i>Tableau de bord</button>
            <button class="space-tab" id="tab-collaborators" onclick="switchSpaceTab('collaborators')"><i class="fas fa-users"></i>Collaborateurs</button>
            <button class="space-tab" id="tab-fusion" onclick="switchSpaceTab('fusion')"><i class="fas fa-code-branch"></i>Fusion & Déversement</button>
            <button class="space-tab" id="tab-chat" onclick="switchSpaceTab('chat')"><i class="fas fa-comments"></i>Messagerie</button>
        </div>

        @if(session('success'))
        <div class="premium-alert success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="premium-alert error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        <!-- TAB 1 : DASHBOARD -->
        <div class="tab-content-section active" id="section-dashboard">
            <!-- KPI Stats -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card-white">
                        <div class="stat-icon-circle indigo"><i class="fas fa-building"></i></div>
                        <div>
                            <div class="fw-extrabold text-dark fs-4" style="line-height:1;">{{ $stats['total_companies'] }}</div>
                            <small class="text-muted font-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.05em;">Entreprises gérées</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card-white">
                        <div class="stat-icon-circle violet"><i class="fas fa-file-invoice"></i></div>
                        <div>
                            <div class="fw-extrabold text-dark fs-4" style="line-height:1;">{{ number_format($stats['total_entries']) }}</div>
                            <small class="text-muted font-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.05em;">Écritures comptables</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card-white">
                        <div class="stat-icon-circle emerald"><i class="fas fa-user-friends"></i></div>
                        <div>
                            <div class="fw-extrabold text-dark fs-4" style="line-height:1;">{{ $stats['total_collaborators'] }}</div>
                            <small class="text-muted font-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.05em;">Collaborateurs</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card-white">
                        <div class="stat-icon-circle amber"><i class="fas fa-link"></i></div>
                        <div>
                            <div class="fw-extrabold text-dark fs-4" style="line-height:1;">{{ $stats['assigned_companies'] }}</div>
                            <small class="text-muted font-bold text-uppercase" style="font-size:0.65rem; letter-spacing:0.05em;">Affectations externes</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Cards -->
            <div class="d-flex align-items-center mb-3">
                <span style="font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:var(--premium-slate-400);">Mon Portefeuille Sociétés</span>
                <div style="flex:1; height:1px; background:#e2e8f0; margin-left:1rem;"></div>
            </div>
            
            <div class="row g-3">
                @forelse($companiesData as $data)
                @php $comp = $data['model']; @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="company-card h-100 d-flex flex-column">
                        <div class="company-card-header">
                            <div class="company-logo">{{ strtoupper(substr($comp->company_name, 0, 2)) }}</div>
                            <div>
                                <div class="company-name">{{ $comp->company_name }}</div>
                                <div class="company-type">
                                    @if($data['is_owner'])<span style="color:var(--premium-blue);">★ Propriétaire</span>@else <span style="color:#10b981;">→ Affecté</span>@endif
                                    &nbsp;·&nbsp; {{ $comp->activity }}
                                </div>
                            </div>
                            @if($comp->is_blocked)
                                <span class="ms-auto badge" style="background:#fee2e2; color:#ef4444; border-radius:8px; font-size:0.65rem;">Bloquée</span>
                            @endif
                        </div>
                        <div class="company-body d-flex flex-column flex-grow-1">
                            @if($comp->company_code)
                            <div class="company-code-badge"><i class="fas fa-key me-1"></i>{{ $comp->company_code }}</div>
                            @else
                            <div class="company-code-badge" style="color:#ef4444; border-color:rgba(239,68,68,0.3); background:rgba(239,68,68,0.05);">Code non généré</div>
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
                            <div style="margin-top:auto; margin-bottom:1.25rem; display:flex; gap:0.35rem; flex-wrap:wrap; align-items:center;">
                                <span style="font-size:0.65rem; color:var(--premium-slate-400); font-weight:700; text-transform:uppercase;">Équipe :</span>
                                @foreach($data['assigned_users']->take(4) as $au)
                                <span title="{{ $au->name }} {{ $au->last_name }} ({{ $au->role }})" style="width:28px; height:28px; border-radius:8px; background:rgba(30,64,175,0.08); color:var(--premium-blue); font-size:0.65rem; font-weight:800; display:inline-flex; align-items:center; justify-content:center; cursor:default;">
                                    {{ strtoupper(substr($au->name,0,1) . substr($au->last_name ?? '',0,1)) }}
                                </span>
                                @endforeach
                                @if($data['assigned_users']->count() > 4)
                                <span style="font-size:0.7rem; color:var(--premium-slate-400);">+{{ $data['assigned_users']->count() - 4 }}</span>
                                @endif
                            </div>
                            @endif

                            <div class="d-flex gap-2 align-items-center mt-auto">
                                <a href="{{ route('accountant.space.switch', $comp->id) }}" class="btn-work">
                                    <i class="fas fa-arrow-right"></i> Travailler
                                </a>
                                <button class="btn-details" data-bs-toggle="modal" data-bs-target="#detailModal{{ $comp->id }}">
                                    <i class="fas fa-cog"></i> Configuration
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div class="modal fade" id="detailModal{{ $comp->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content premium-modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title text-dark fw-extrabold">{{ $comp->company_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body py-4">
                                <div class="mb-4">
                                    <label class="text-uppercase font-bold tracking-widest text-muted" style="font-size: 0.65rem;">Code d'accès collaborateur</label>
                                    <div class="company-code-badge fs-6 my-2">{{ $comp->company_code ?? 'Non généré' }}</div>
                                    <p class="text-muted small mb-0">Partagez ce code de sécurité avec votre collaborateur pour lui donner un accès direct à cette société.</p>
                                </div>
                                <div class="mb-0">
                                    <label class="text-uppercase font-bold tracking-widest text-muted mb-3" style="font-size: 0.65rem;">Membres affectés à cette entreprise</label>
                                    @forelse($data['assigned_users'] as $au)
                                    <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="chat-avatar-sm" style="width:32px;height:32px;font-size:0.7rem;">{{ strtoupper(substr($au->name,0,1).substr($au->last_name??'',0,1)) }}</div>
                                            <div>
                                                <div style="color:var(--premium-slate-900); font-size:0.82rem; font-weight:700;">{{ $au->name }} {{ $au->last_name }}</div>
                                                <div style="color:var(--premium-slate-400); font-size:0.7rem;">{{ $au->email_adresse }}</div>
                                            </div>
                                        </div>
                                        <span class="badge-role-collab badge-collab-{{ $au->role }}">{{ $au->role }}</span>
                                    </div>
                                    @empty
                                    <p class="text-muted small mb-0">Aucun collaborateur n'a été affecté pour le moment.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="glass-card text-center p-5">
                        <i class="fas fa-building text-muted mb-3" style="font-size:3rem; display:block;"></i>
                        <h5 class="fw-extrabold mb-1">Aucune entreprise</h5>
                        <p class="text-muted small mb-3">Créez votre première entreprise pour démarrer.</p>
                        <button class="btn-work" data-bs-toggle="modal" data-bs-target="#newCompanyModal">
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
                    <div class="premium-form-card">
                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--premium-blue); margin-bottom:1rem; display:block;"><i class="fas fa-user-plus me-2"></i>Affecter un collaborateur</span>
                        <form method="POST" action="{{ route('accountant.space.assign') }}">
                            @csrf
                            <div class="mb-3">
                                <label>Entreprise cible</label>
                                <select name="company_id" class="form-select">
                                    @foreach($companiesData as $d)
                                        <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Collaborateur</label>
                                <select name="user_id" class="form-select">
                                    @foreach($collaborators as $collab)
                                        <option value="{{ $collab->id }}">{{ $collab->name }} {{ $collab->last_name }} ({{ $collab->role }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Rôle dans l'entreprise</label>
                                <select name="role" class="form-select">
                                    <option value="admin">Admin</option>
                                    <option value="comptable">Comptable</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-work w-100 justify-content-center mt-2">
                                <i class="fas fa-link me-1"></i>Lier à l'entreprise
                            </button>
                        </form>
                    </div>
                </div>
                <!-- Table -->
                <div class="col-lg-8">
                    <div class="glass-card p-4 overflow-hidden">
                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--premium-blue); margin-bottom:1rem; display:block;"><i class="fas fa-users me-2"></i>Collaborateurs de mon espace</span>
                        <div class="table-responsive">
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>Collaborateur</th>
                                        <th>Email</th>
                                        <th>Rôle Global</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($collaborators as $collab)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="chat-avatar-sm" style="width:32px;height:32px;font-size:0.7rem;">{{ strtoupper(substr($collab->name,0,1).substr($collab->last_name??'',0,1)) }}</div>
                                                <span class="fw-bold">{{ $collab->name }} {{ $collab->last_name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $collab->email_adresse }}</td>
                                        <td><span class="badge-role-collab badge-collab-{{ $collab->role }}">{{ $collab->role }}</span></td>
                                        <td>
                                            @if($collab->is_active)
                                                <span class="badge bg-light-success text-success" style="font-size:0.7rem; font-weight:700;">Actif</span>
                                            @else
                                                <span class="badge bg-light-danger text-danger" style="font-size:0.7rem; font-weight:700;">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" style="text-align:center; color:var(--premium-slate-400); padding:2rem;">Aucun collaborateur enregistré</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3 : FUSION -->
        <div class="tab-content-section" id="section-fusion">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="premium-form-card">
                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--premium-blue); margin-bottom:1rem; display:block;"><i class="fas fa-exchange-alt me-2"></i>Déverser la configuration</span>
                        <div class="premium-alert info mb-3">
                            <i class="fas fa-info-circle"></i>
                            <div>Cette fonction copie les configurations (plan comptable, journaux, tiers) d'une entreprise source vers une entreprise cible. Aucune écriture comptable n'est déversée.</div>
                        </div>
                        <form method="POST" action="{{ route('accountant.space.fusion') }}" onsubmit="return confirm('Confirmer l\'injection ? Les configurations de l\'entreprise cible seront complétées.');">
                            @csrf
                            <div class="mb-3">
                                <label>Entreprise source (données à copier)</label>
                                <select name="source_company_id" class="form-select">
                                    @foreach($companiesData as $d)
                                        <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Entreprise cible (destination)</label>
                                <select name="target_company_id" class="form-select">
                                    @foreach($companiesData as $d)
                                        <option value="{{ $d['model']->id }}">{{ $d['model']->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="mb-2">Éléments à copier</label>
                                <div class="d-flex flex-column gap-2 mt-1">
                                    <label class="form-check-label d-flex align-items-center gap-2 cursor-pointer font-medium text-dark" style="text-transform:none; font-size:0.85rem;">
                                        <input type="checkbox" name="scope[]" value="accounts" class="form-check-input" checked>
                                        Plan Comptable
                                    </label>
                                    <label class="form-check-label d-flex align-items-center gap-2 cursor-pointer font-medium text-dark" style="text-transform:none; font-size:0.85rem;">
                                        <input type="checkbox" name="scope[]" value="journals" class="form-check-input" checked>
                                        Codes Journaux
                                    </label>
                                    <label class="form-check-label d-flex align-items-center gap-2 cursor-pointer font-medium text-dark" style="text-transform:none; font-size:0.85rem;">
                                        <input type="checkbox" name="scope[]" value="tiers" class="form-check-input">
                                        Fiches Tiers
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn-work w-100 justify-content-center py-2.5">
                                <i class="fas fa-rocket me-1"></i>Lancer l'injection
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card p-4 h-100">
                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--premium-blue); margin-bottom:1rem; display:block;"><i class="fas fa-info-circle me-2"></i>Fonctionnement du déversement</span>
                        <div style="color:var(--premium-slate-800); font-size:0.85rem; line-height:1.8;">
                            <p class="mb-3">La fusion et déversement inter-sociétés vous permet de standardiser vos comptabilités sans double-saisie :</p>
                            <ul style="padding-left:1.2rem; margin-bottom:1.5rem;" class="d-flex flex-column gap-2">
                                <li><strong>Ignorer les doublons :</strong> Si un compte ou journal existe déjà dans la cible, il est ignoré pour préserver l'historique local.</li>
                                <li><strong>Isolation totale :</strong> Seuls les modèles de structure sont transférés, aucune écriture ou transaction n'est exposée.</li>
                                <li><strong>Rapide & efficace :</strong> Idéal pour déployer un plan comptable type sur vos nouvelles structures.</li>
                            </ul>
                            <p class="text-muted small"><i class="fas fa-shield-alt text-primary me-1"></i> Vos fiches restent parfaitement étanches d'un dossier à un autre.</p>
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
                    <div style="padding:1rem 1.25rem; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--premium-slate-400); border-bottom:1px solid #f1f5f9;">Discussions</div>
                    @forelse($chatUsers as $cu)
                    <div class="chat-user-item" id="chat-user-{{ $cu->id }}" onclick="openChat({{ $cu->id }}, '{{ $cu->name }} {{ $cu->last_name }}', '{{ strtoupper(substr($cu->name,0,1).substr($cu->last_name??'',0,1)) }}')">
                        <div class="chat-avatar-sm">{{ strtoupper(substr($cu->name,0,1).substr($cu->last_name??'',0,1)) }}</div>
                        <div>
                            <div class="chat-user-name">{{ $cu->name }} {{ $cu->last_name }}</div>
                            <div class="chat-user-role">{{ $cu->role }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="padding:2rem; text-align:center; color:var(--premium-slate-400); font-size:0.8rem;">Aucun contact disponible</div>
                    @endforelse
                </div>
                <!-- Chat Main -->
                <div class="chat-main">
                    <div class="chat-header" id="chat-header">
                        <div style="color:var(--premium-slate-400); font-size:0.85rem;">Sélectionnez un contact pour démarrer la discussion</div>
                    </div>
                    <div class="chat-messages" id="chat-messages">
                        <div style="text-align:center; color:var(--premium-slate-400); padding:5rem 0;">
                            <i class="fas fa-comments text-muted mb-3" style="font-size:3rem; display:block;"></i>
                            Sélectionnez un membre de votre réseau
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

    </div>
</div>
</div>
</div>
</div>

<!-- Modal : Nouvelle Entreprise -->
<div class="modal fade" id="newCompanyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content premium-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-dark fw-extrabold"><i class="fas fa-building me-2 text-primary"></i>Créer une nouvelle entreprise</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('accountant.space.company.store') }}">
                @csrf
                <div class="modal-body py-4">
                    <div class="premium-alert info mb-3">
                        <i class="fas fa-key"></i>
                        Un code d'accès unique sera généré automatiquement pour ce nouveau dossier.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Nom de l'entreprise *</label>
                            <input type="text" class="form-control" name="company_name" required placeholder="Ex: Groupe ABC SARL" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-6">
                            <label>Email *</label>
                            <input type="email" class="form-control" name="email_adresse" required placeholder="contact@entreprise.com" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-6">
                            <label>Activité *</label>
                            <input type="text" class="form-control" name="activity" required placeholder="Commerce, Services, BTP..." style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-6">
                            <label>Forme juridique *</label>
                            <select name="juridique_form" class="form-select" required style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                                <option>SARL</option><option>SA</option><option>SAS</option><option>SASU</option><option>SNC</option><option>EI</option><option>EIRL</option><option>Association</option><option>ONG</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Capital social</label>
                            <input type="number" class="form-control" name="social_capital" placeholder="1000000" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-6">
                            <label>Téléphone *</label>
                            <input type="text" class="form-control" name="phone_number" required placeholder="+225 XX XX XX XX" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-8">
                            <label>Adresse *</label>
                            <input type="text" class="form-control" name="adresse" required placeholder="Rue, Avenue, Quartier" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-4">
                            <label>Code postal *</label>
                            <input type="text" class="form-control" name="code_postal" required placeholder="01 BP..." style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-6">
                            <label>Ville *</label>
                            <input type="text" class="form-control" name="city" required placeholder="Abidjan, Dakar..." style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-md-6">
                            <label>Pays *</label>
                            <input type="text" class="form-control" name="country" required value="Côte d'Ivoire" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:12px; font-weight:700; padding:0.6rem 1.2rem;">Annuler</button>
                    <button type="submit" class="btn-work">
                        <i class="fas fa-plus me-1"></i>Créer la société
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal : Nouveau Collaborateur -->
<div class="modal fade" id="newMemberModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content premium-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-dark fw-extrabold"><i class="fas fa-user-plus me-2 text-success"></i>Créer un nouveau membre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('accountant.space.member.store') }}">
                @csrf
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label>Prénom *</label>
                            <input type="text" class="form-control" name="name" required placeholder="Jean" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-6">
                            <label>Nom *</label>
                            <input type="text" class="form-control" name="last_name" required placeholder="DUPONT" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-12">
                            <label>Email *</label>
                            <input type="email" class="form-control" name="email_adresse" required placeholder="jean.dupont@email.com" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-12">
                            <label>Mot de passe *</label>
                            <input type="password" class="form-control" name="password" required placeholder="Minimum 6 caractères" style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                        </div>
                        <div class="col-12">
                            <label>Rôle Global *</label>
                            <select name="role" class="form-select" required style="background:#f8fafc; border:2px solid #f1f5f9; border-radius:12px; font-weight:600;">
                                <option value="admin">Admin</option>
                                <option value="comptable">Comptable</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:12px; font-weight:700; padding:0.6rem 1.2rem;">Annuler</button>
                    <button type="submit" class="btn-work" style="background:linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);">
                        <i class="fas fa-user-plus me-1"></i>Créer le compte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchSpaceTab(tab) {
    // Activer l'onglet horizontal
    document.querySelectorAll('.space-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');

    // Afficher la bonne section
    document.querySelectorAll('.tab-content-section').forEach(s => s.classList.remove('active'));
    document.getElementById('section-' + tab).classList.add('active');

    if (tab === 'chat') {
        // Sélectionner automatiquement la 1ère conversation si disponible
        const firstUser = document.querySelector('.chat-user-item');
        if (firstUser) firstUser.click();
    }
}

let currentRecipientId = null;

function openChat(userId, userName, initials) {
    currentRecipientId = userId;
    document.getElementById('current_recipient_id').value = userId;

    // Classe active dans la barre
    document.querySelectorAll('.chat-user-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('chat-user-' + userId);
    if (el) el.classList.add('active');

    // En-tête
    document.getElementById('chat-header').innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <div class="chat-avatar-sm">${initials}</div>
            <div>
                <div style="color:var(--premium-slate-900);font-size:0.88rem;font-weight:700;">${userName}</div>
                <div style="color:#10b981;font-size:0.7rem;font-weight:600;"><i class="fas fa-circle me-1 small"></i>En ligne</div>
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
            container.innerHTML = '<div style="text-align:center; color:var(--premium-slate-400); padding:3rem 0; font-size:0.85rem;">Aucun message. Dites bonjour ! 👋</div>';
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

// Lecture des paramètres de l'URL
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');
if (tabParam && ['dashboard', 'collaborators', 'fusion', 'chat'].includes(tabParam)) {
    switchSpaceTab(tabParam);
}

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
