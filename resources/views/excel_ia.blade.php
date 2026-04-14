<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Analyse <span class="text-gradient">IA Comptable</span>'])
                <div class="content-wrapper">
                    <!-- CONTENT -->
                    <div class="px-4 py-4" style="max-width: 1400px; margin: 0 auto; padding-top: 20px !important;">
<style>
/* ═══════════════════════════════════════════
   ASSISTANT COMPTABLE IA — THÈME CLAIR
   ═══════════════════════════════════════════ */
:root {
    --eia-bg:        #f8fafc;
    --eia-white:     #ffffff;
    --eia-border:    #e2e8f0;
    --eia-border-md: #cbd5e1;
    --eia-text:      #1e293b;
    --eia-muted:     #64748b;
    --eia-accent:    #6366f1;
    --eia-accent-s:  #4f46e5;
    --eia-user-bg:   #6366f1;
    --eia-ai-bg:     #f1f5f9;
    --eia-ai-border: #e2e8f0;
    --eia-shadow:    0 1px 3px rgba(0,0,0,.07), 0 1px 2px rgba(0,0,0,.05);
    --eia-shadow-md: 0 4px 12px rgba(99,102,241,.15);
    --eia-radius:    14px;
    --eia-radius-sm: 8px;
}

/* LAYOUT */
.eia-layout {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 120px);
    min-height: 600px;
    background: var(--eia-bg);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 0 0 1px var(--eia-border);
}

/* ─── ONGLETS ──────────────────────────────── */
.eia-tabs {
    display: flex;
    gap: 4px;
    padding: 12px 16px 0;
    background: var(--eia-white);
    border-bottom: 1px solid var(--eia-border);
    overflow-x: auto;
    flex-shrink: 0;
}
.eia-tabs::-webkit-scrollbar { display: none; }

.eia-tab {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 500;
    color: var(--eia-muted);
    border-radius: 8px 8px 0 0;
    border: 1px solid transparent;
    border-bottom: none;
    text-decoration: none;
    white-space: nowrap;
    transition: all .15s ease;
    position: relative;
    bottom: -1px;
    cursor: pointer;
    background: transparent;
}
.eia-tab:hover { color: var(--eia-accent); background: rgba(99,102,241,.05); }
.eia-tab.active {
    color: var(--eia-accent);
    background: var(--eia-white);
    border-color: var(--eia-border);
    border-bottom-color: var(--eia-white);
    font-weight: 600;
}
.eia-tab i { font-size: 12px; }
.eia-tab .tab-badge {
    background: var(--eia-accent);
    color: #fff;
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 600;
}

/* ─── ZONE MESSAGES ───────────────────────── */
.eia-messages {
    flex: 1;
    overflow-y: auto;
    padding: 28px 20px;
    scroll-behavior: smooth;
}
.eia-messages::-webkit-scrollbar { width: 4px; }
.eia-messages::-webkit-scrollbar-track { background: transparent; }
.eia-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* État d'accueil */
.eia-welcome {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 40px 20px;
    text-align: center;
    min-height: 340px;
}
.eia-welcome-icon {
    width: 68px;
    height: 68px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    box-shadow: 0 8px 24px rgba(99,102,241,.3);
}
.eia-welcome h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--eia-text);
    margin: 0;
}
.eia-welcome p {
    font-size: 0.95rem;
    color: var(--eia-muted);
    max-width: 480px;
    margin: 0;
    line-height: 1.6;
}

.eia-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    max-width: 680px;
}
.eia-suggestion {
    padding: 10px 18px;
    border: 1px solid var(--eia-border);
    background: var(--eia-white);
    border-radius: 24px;
    font-size: 13px;
    color: var(--eia-text);
    cursor: pointer;
    transition: all .15s ease;
    text-align: left;
    line-height: 1.4;
    box-shadow: var(--eia-shadow);
}
.eia-suggestion:hover {
    border-color: var(--eia-accent);
    color: var(--eia-accent);
    background: rgba(99,102,241,.04);
    box-shadow: var(--eia-shadow-md);
    transform: translateY(-1px);
}

/* Messages individuels */
.eia-message {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    max-width: 860px;
    margin-left: auto;
    margin-right: auto;
    animation: msgIn .25s ease;
}
@keyframes msgIn {
    from { opacity:0; transform: translateY(8px); }
    to   { opacity:1; transform: translateY(0); }
}

.eia-message.user { flex-direction: row-reverse; }

.eia-msg-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}
.eia-message.user .eia-msg-avatar {
    background: var(--eia-accent);
    color: #fff;
}
.eia-message.ai .eia-msg-avatar {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
}

.eia-msg-body {
    flex: 1;
    max-width: calc(100% - 50px);
}
.eia-msg-bubble {
    padding: 13px 17px;
    border-radius: 14px;
    font-size: 14px;
    line-height: 1.65;
    word-break: break-word;
}
.eia-message.user .eia-msg-bubble {
    background: var(--eia-accent);
    color: #fff;
    border-bottom-right-radius: 4px;
}
.eia-message.ai .eia-msg-bubble {
    background: var(--eia-white);
    color: var(--eia-text);
    border: 1px solid var(--eia-ai-border);
    border-bottom-left-radius: 4px;
    box-shadow: var(--eia-shadow);
}

/* Markdown simplifié dans bulles IA */
.eia-msg-bubble code {
    background: rgba(99,102,241,.1);
    color: #4f46e5;
    padding: 1px 5px;
    border-radius: 4px;
    font-size: 12.5px;
    font-family: 'Fira Code', monospace;
}
.eia-msg-bubble pre {
    background: #0f172a;
    color: #e2e8f0;
    padding: 14px;
    border-radius: 8px;
    font-size: 12px;
    overflow-x: auto;
    margin: 10px 0 0;
    font-family: 'Fira Code', monospace;
    line-height: 1.7;
}
.eia-msg-bubble strong { font-weight: 600; }
.eia-msg-bubble ul { padding-left: 20px; margin: 6px 0; }
.eia-msg-bubble li { margin: 3px 0; }

/* Bouton actions sur bulles IA */
.eia-msg-actions {
    display: flex;
    gap: 6px;
    margin-top: 6px;
    opacity: 0;
    transition: opacity .2s;
}
.eia-msg-body:hover .eia-msg-actions { opacity: 1; }
.eia-msg-actions button {
    padding: 4px 10px;
    font-size: 11px;
    border-radius: 6px;
    border: 1px solid var(--eia-border);
    background: var(--eia-white);
    color: var(--eia-muted);
    cursor: pointer;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 4px;
}
.eia-msg-actions button:hover { border-color: var(--eia-accent); color: var(--eia-accent); }

/* Typing indicator */
.eia-typing {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 12px 16px;
    background: var(--eia-white);
    border: 1px solid var(--eia-border);
    border-radius: 14px;
    border-bottom-left-radius: 4px;
    width: fit-content;
}
.eia-typing span {
    width: 7px; height: 7px;
    background: var(--eia-muted);
    border-radius: 50%;
    animation: dot .8s infinite ease-in-out;
}
.eia-typing span:nth-child(2) { animation-delay: .15s; }
.eia-typing span:nth-child(3) { animation-delay: .3s; }
@keyframes dot {
    0%, 80%, 100% { transform: scale(1); opacity: .5; }
    40%           { transform: scale(1.2); opacity: 1; }
}

/* ─── ZONE INPUT ──────────────────────────── */
.eia-input-area {
    flex-shrink: 0;
    padding: 14px 20px 16px;
    background: var(--eia-white);
    border-top: 1px solid var(--eia-border);
}

/* Prévisualisation fichiers joints */
.eia-file-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
}
.eia-file-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 5px 12px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 20px;
    font-size: 12px;
    color: var(--eia-accent);
    font-weight: 500;
}
.eia-file-chip .chip-remove {
    background: none;
    border: none;
    color: var(--eia-accent);
    cursor: pointer;
    padding: 0;
    font-size: 13px;
    line-height: 1;
    opacity: .6;
    transition: opacity .15s;
}
.eia-file-chip .chip-remove:hover { opacity: 1; }

.eia-input-box {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    background: var(--eia-bg);
    border: 1.5px solid var(--eia-border);
    border-radius: 12px;
    padding: 10px 12px;
    transition: border-color .15s, box-shadow .15s;
}
.eia-input-box:focus-within {
    border-color: var(--eia-accent);
    box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}

.eia-chat-textarea {
    flex: 1;
    border: none;
    background: transparent;
    resize: none;
    font-size: 14px;
    color: var(--eia-text);
    line-height: 1.6;
    max-height: 160px;
    min-height: 22px;
    overflow-y: auto;
    outline: none;
    font-family: inherit;
}
.eia-chat-textarea::placeholder { color: #94a3b8; }

.eia-input-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.eia-attach-btn {
    width: 34px; height: 34px;
    border-radius: 8px;
    border: 1px solid var(--eia-border);
    background: var(--eia-white);
    color: var(--eia-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all .15s;
    flex-shrink: 0;
}
.eia-attach-btn:hover { border-color: var(--eia-accent); color: var(--eia-accent); background: rgba(99,102,241,.05); }

.eia-send-btn {
    width: 36px; height: 36px;
    border-radius: 9px;
    border: none;
    background: var(--eia-accent);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all .15s;
    flex-shrink: 0;
}
.eia-send-btn:hover:not(:disabled) { background: var(--eia-accent-s); transform: scale(1.05); }
.eia-send-btn:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }

.eia-hint {
    font-size: 11.5px;
    color: var(--eia-muted);
    margin: 8px 0 0;
    text-align: center;
}

/* Mode analyse fichiers */
.eia-analyse-panel {
    background: var(--eia-white);
    border: 1px solid var(--eia-border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    display: none;
}
.eia-analyse-panel.active { display: block; }
.eia-analyse-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.eia-analyse-title { font-size: 13px; font-weight: 600; color: var(--eia-text); }

.eia-mois-select {
    font-size: 13px;
    border: 1px solid var(--eia-border);
    border-radius: 8px;
    padding: 6px 10px;
    color: var(--eia-text);
    background: var(--eia-white);
    cursor: pointer;
}

.eia-analyse-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.eia-btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all .15s;
    display: flex;
    align-items: center;
    gap: 7px;
}
.eia-btn-primary { background: var(--eia-accent); color: #fff; }
.eia-btn-primary:hover { background: var(--eia-accent-s); }
.eia-btn-outline { background: transparent; border-color: var(--eia-border); color: var(--eia-text); }
.eia-btn-outline:hover { border-color: var(--eia-accent); color: var(--eia-accent); }
.eia-btn:disabled { opacity: .5; cursor: not-allowed; }

/* Résultat d'analyse (dans les bulles IA) */
.eia-result-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--eia-border);
}

/* Stats rapides */
.eia-stats-mini {
    display: flex;
    gap: 12px;
    margin-top: 10px;
}
.eia-stat-mini {
    padding: 8px 14px;
    background: var(--eia-bg);
    border-radius: 8px;
    text-align: center;
    flex: 1;
}
.eia-stat-mini .stat-val { font-size: 20px; font-weight: 700; color: var(--eia-accent); }
.eia-stat-mini .stat-lbl { font-size: 11px; color: var(--eia-muted); }

/* Écritures dans bulle */
.eia-ecritures-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin-top: 12px;
}
.eia-ecritures-table th {
    background: #f1f5f9;
    padding: 6px 10px;
    text-align: left;
    font-weight: 600;
    color: var(--eia-muted);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.eia-ecritures-table td {
    padding: 6px 10px;
    border-bottom: 1px solid var(--eia-border);
    color: var(--eia-text);
}
.eia-ecritures-table tr:last-child td { border-bottom: none; }
.eia-ecritures-table tr:hover td { background: rgba(99,102,241,.04); }

/* Équilibre badge */
.badge-equilibre { color: #16a34a; background: #dcfce7; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
.badge-desequilibre { color: #dc2626; background: #fee2e2; padding: 2px 8px; border-radius: 10px; font-size: 11px; }

/* ─── MODAL REVIEW & GRID ───────────────────── */
.eia-review-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn .3s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.eia-review-modal {
    background: var(--eia-white);
    width: 100%;
    max-width: 1200px;
    max-height: 90vh;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}

.eia-review-header {
    padding: 24px 30px;
    border-bottom: 1px solid var(--eia-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
}

.eia-review-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.eia-review-footer {
    padding: 20px 30px;
    border-top: 1px solid var(--eia-border);
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Grid Editable */
.eia-grid-container {
    border: 1px solid var(--eia-border);
    border-radius: 12px;
    overflow: hidden;
}

.eia-grid-table {
    width: 100%;
    border-collapse: collapse;
}

.eia-grid-table th {
    background: #f1f5f9;
    padding: 12px 15px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--eia-muted);
    letter-spacing: 0.05em;
    text-align: left;
    border-bottom: 1px solid var(--eia-border);
}

.eia-grid-table td {
    padding: 10px 15px;
    border-bottom: 1px solid var(--eia-border);
    background: #fff;
}

.eia-grid-table tr:hover td {
    background: #f8fafc;
}

.eia-grid-input {
    width: 100%;
    border: 1px solid transparent;
    background: transparent;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 13px;
    transition: all .15s;
}

.eia-grid-input:focus {
    border-color: var(--eia-accent);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    outline: none;
}

.eia-grid-table select.eia-grid-input {
    cursor: pointer;
}

.btn-remove-row {
    color: #ef4444;
    background: transparent;
    border: none;
    cursor: pointer;
    opacity: 0.4;
    transition: opacity .15s;
    font-size: 14px;
}
.btn-remove-row:hover { opacity: 1; }

.eia-grid-summary {
    display: flex;
    gap: 30px;
    background: #f1f5f9;
    padding: 15px 25px;
    border-top: 1px solid var(--eia-border);
}

.summary-item {
    display: flex;
    flex-direction: column;
}
.summary-label { font-size: 11px; color: var(--eia-muted); text-transform: uppercase; font-weight: 600; }
.summary-value { font-size: 18px; font-weight: 700; color: var(--eia-text); }
.summary-value.balanced { color: #16a34a; }
.summary-value.unbalanced { color: #dc2626; }

/* Status Badge */
.review-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

/* ─── RESPONSIVE ──────────────────────────── */
@media (max-width: 640px) {
    .eia-layout { height: calc(100vh - 100px); border-radius: 0; }
    .eia-tab span { display: none; }
    .eia-welcome h2 { font-size: 1.2rem; }
    .eia-suggestions { flex-direction: column; }
    .eia-message { max-width: 100%; }
}
@media (max-width: 1024px) {
    .eia-review-modal { max-width: 95%; height: 95vh; }
}
</style>

<div class="eia-layout">

    {{-- ─── ONGLETS ──────────────────────── --}}
    <div class="eia-tabs">
        <a href="{{ route('excel_ia.index') }}" class="eia-tab active" id="tabChat">
            <i class="fas fa-comments"></i> <span>Chat IA</span>
        </a>
        <a href="{{ route('excel_ia.historique') }}" class="eia-tab">
            <i class="fas fa-history"></i> <span>Historique</span>
        </a>
        <a href="{{ route('excel_ia.projets.index') }}" class="eia-tab">
            <i class="fas fa-folder-open"></i> <span>Projets</span>
        </a>
        <a href="{{ route('factures_produites.index') }}" class="eia-tab">
            <i class="fas fa-file-invoice"></i> <span>Factures Produites</span>
        </a>
    </div>

    {{-- ─── MESSAGES ─────────────────────── --}}
    <div class="eia-messages" id="chatMessages">
        <div class="eia-welcome" id="welcomeState">
            <div class="eia-welcome-icon">
                <i class="fas fa-robot"></i>
            </div>
            <h2>Assistant Comptable IA</h2>
            <p>Posez vos questions comptables en français ou uploadez vos fichiers.<br>
               L'IA utilise le référentiel SYSCOHADA révisé et le plan comptable de votre entreprise.</p>
            <div class="eia-suggestions">
                <button class="eia-suggestion" onclick="sendSuggestion(this)">
                    <i class="fas fa-question-circle" style="color:#6366f1"></i>
                    Comment comptabiliser une avance sur salaire ?
                </button>
                <button class="eia-suggestion" onclick="sendSuggestion(this)">
                    <i class="fas fa-file-excel" style="color:#16a34a"></i>
                    Analyser un fichier Excel de dépenses
                </button>
                <button class="eia-suggestion" onclick="sendSuggestion(this)">
                    <i class="fas fa-university" style="color:#0ea5e9"></i>
                    Quel journal utiliser pour un virement bancaire ?
                </button>
                <button class="eia-suggestion" onclick="sendSuggestion(this)">
                    <i class="fas fa-users" style="color:#f59e0b"></i>
                    Générer des écritures de paie SYSCOHADA
                </button>
                <button class="eia-suggestion" onclick="sendSuggestion(this)">
                    <i class="fas fa-balance-scale" style="color:#8b5cf6"></i>
                    Expliquer la règle de la partie double
                </button>
                <button class="eia-suggestion" onclick="sendSuggestion(this)">
                    <i class="fas fa-receipt" style="color:#ec4899"></i>
                    Comment traiter une note de frais en OHADA ?
                </button>
            </div>
        </div>
    </div>

    {{-- ─── ZONE INPUT ───────────────────── --}}
    <div class="eia-input-area">
        {{-- Fichiers joints --}}
        <div class="eia-file-chips" id="fileChips" style="display:none"></div>

        {{-- Panneau mode analyse (apparaît si fichiers joints) --}}
        <div class="eia-analyse-panel" id="analysePanel">
            <div class="eia-analyse-header">
                <span class="eia-analyse-title">
                    <i class="fas fa-chart-bar" style="color:#6366f1"></i>
                    Mode Analyse — Génération d'écritures Sage 100
                </span>
                <select class="eia-mois-select" id="moisCible">
                    <option value="TOUS">Tous les mois</option>
                    <option value="JANVIER">Janvier</option>
                    <option value="FEVRIER">Février</option>
                    <option value="MARS">Mars</option>
                    <option value="AVRIL">Avril</option>
                    <option value="MAI">Mai</option>
                    <option value="JUIN">Juin</option>
                    <option value="JUILLET">Juillet</option>
                    <option value="AOUT">Août</option>
                    <option value="SEPTEMBRE">Septembre</option>
                    <option value="OCTOBRE">Octobre</option>
                    <option value="NOVEMBRE">Novembre</option>
                    <option value="DECEMBRE">Décembre</option>
                </select>
            </div>
            <div class="eia-analyse-actions" id="analyseActions">
                <button class="eia-btn eia-btn-primary" onclick="lancerAnalyse('bdd_only')" id="btnBdd">
                    <i class="fas fa-database"></i> Injecter BDD
                </button>
                <button class="eia-btn eia-btn-outline" onclick="lancerAnalyse('txt_only')" id="btnTxt">
                    <i class="fas fa-file-export"></i> Export TXT Sage
                </button>
                <button class="eia-btn eia-btn-outline" onclick="lancerAnalyse('both')" id="btnBoth">
                    <i class="fas fa-bolt"></i> Les deux
                </button>
            </div>
        </div>

        {{-- Champ de saisie --}}
        <div class="eia-input-box">
            <textarea
                class="eia-chat-textarea"
                id="chatInput"
                placeholder="Posez votre question comptable ou décrivez ce que vous souhaitez analyser..."
                rows="1"
                maxlength="5000"
            ></textarea>
            <div class="eia-input-actions">
                <label for="chatFiles" class="eia-attach-btn" title="Joindre des fichiers (Excel, CSV, PDF, Image)">
                    <i class="fas fa-paperclip"></i>
                </label>
                <input type="file" id="chatFiles" multiple
                       accept=".xlsx,.xls,.csv,.pdf,.jpg,.jpeg,.png"
                       style="display:none" onchange="handleFiles(this.files)">
                <button class="eia-send-btn" id="sendBtn" onclick="envoyerMessage()" title="Envoyer (Ctrl+Entrée)">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        <p class="eia-hint">
            <kbd>Ctrl+Entrée</kbd> pour envoyer &nbsp;·&nbsp;
            Max 20 fichiers (20MB total) &nbsp;·&nbsp;
            Modèle : Vertex AI Pro
        </p>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════
   ASSISTANT COMPTABLE IA — JAVASCRIPT
   ═══════════════════════════════════════════ */
const CHAT_URL    = "{{ route('excel_ia.chat') }}";
const ANALYSE_URL = "{{ route('excel_ia.analyser') }}";
const CSRF_TOKEN  = "{{ csrf_token() }}";

let chatHistory   = [];
let attachedFiles = [];
let lastAnalyse   = null; // Résultat de la dernière analyse (pour injection)
let isTyping      = false;

// ─── Auto-resize textarea ─────────────────
const textarea = document.getElementById('chatInput');
textarea.addEventListener('input', () => {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
});

// ─── Ctrl+Enter pour envoyer ──────────────
textarea.addEventListener('keydown', e => {
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        envoyerMessage();
    }
});

// ─── Suggestion rapide ────────────────────
function sendSuggestion(btn) {
    const text = btn.innerText.trim();
    document.getElementById('chatInput').value = text;
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
    envoyerMessage();
}

// ─── Gestion fichiers joints ──────────────
function handleFiles(files) {
    if (!files.length) return;
    for (const f of files) {
        if (!attachedFiles.find(x => x.name === f.name && x.size === f.size)) {
            attachedFiles.push(f);
        }
    }
    renderFileChips();
    // Réinitialiser l'input pour permettre re-sélection du même fichier
    document.getElementById('chatFiles').value = '';
}

function renderFileChips() {
    const container  = document.getElementById('fileChips');
    const panel      = document.getElementById('analysePanel');
    container.innerHTML = '';
    if (attachedFiles.length === 0) {
        container.style.display = 'none';
        panel.classList.remove('active');
        return;
    }
    container.style.display = 'flex';
    panel.classList.add('active');
    attachedFiles.forEach((f, i) => {
        const ext  = f.name.split('.').pop().toLowerCase();
        const icon = ext === 'pdf' ? 'fa-file-pdf' :
                     ['jpg','jpeg','png'].includes(ext) ? 'fa-file-image' :
                     ['xlsx','xls'].includes(ext) ? 'fa-file-excel' : 'fa-file-csv';
        const color = ext === 'pdf' ? '#ef4444' :
                      ['jpg','jpeg','png'].includes(ext) ? '#f59e0b' : '#16a34a';
        const chip = document.createElement('div');
        chip.className = 'eia-file-chip';
        chip.innerHTML = `<i class="fas ${icon}" style="color:${color}"></i>
                          <span>${f.name}</span>
                          <button class="chip-remove" onclick="removeFile(${i})">×</button>`;
        container.appendChild(chip);
    });
}

function removeFile(idx) {
    attachedFiles.splice(idx, 1);
    renderFileChips();
}

// ─── Envoi message chat ───────────────────
async function envoyerMessage() {
    const msg = textarea.value.trim();
    if (!msg && attachedFiles.length === 0) return;
    if (isTyping) return;

    // Si des fichiers sont attachés → mode analyse
    if (attachedFiles.length > 0) {
        await lancerAnalyse('chat_with_files', msg);
        return;
    }

    // Mode chat texte seul
    if (!msg) return;

    afficherMessage('user', msg);
    textarea.value = '';
    textarea.style.height = 'auto';
    chatHistory.push({ role: 'user', content: msg });

    afficherTyping();
    isTyping = true;
    document.getElementById('sendBtn').disabled = true;

    try {
        const formData = new FormData();
        formData.append('message', msg);
        formData.append('_token', CSRF_TOKEN);
        chatHistory.slice(-8).forEach((h, i) => {
            formData.append(`historique[${i}][role]`, h.role);
            formData.append(`historique[${i}][content]`, h.content);
        });

        const resp = await fetch(CHAT_URL, { method: 'POST', body: formData });
        const data = await resp.json();

        retirerTyping();

        if (data.success) {
            const reponse = data.reponse || '';
            chatHistory.push({ role: 'ai', content: reponse });
            afficherMessage('ai', reponse);
        } else {
            afficherErreur(data.error || 'Erreur lors de la communication avec l\'IA.');
        }
    } catch (e) {
        retirerTyping();
        afficherErreur('Erreur réseau : ' + e.message);
    } finally {
        isTyping = false;
        document.getElementById('sendBtn').disabled = false;
    }
}

// ─── Lancer analyse fichiers ──────────────
async function lancerAnalyse(mode, userMsg = '') {
    if (!attachedFiles.length) return;
    if (isTyping) return;

    const moisCible = document.getElementById('moisCible')?.value || 'TOUS';
    const msg = userMsg || textarea.value.trim() || `Analyse des fichiers (${moisCible})`;

    afficherMessage('user', msg + (attachedFiles.length > 0 ? `\n\n📎 ${attachedFiles.length} fichier(s) joint(s)` : ''));
    textarea.value = '';
    textarea.style.height = 'auto';

    afficherTyping();
    isTyping = true;
    document.getElementById('sendBtn').disabled = true;
    ['btnBdd','btnTxt','btnBoth'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = true;
    });

    try {
        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('mois_cible', moisCible);
        formData.append('action', mode);
        if (msg) formData.append('message', msg);
        attachedFiles.forEach(f => formData.append('fichiers[]', f));

        const resp = await fetch(ANALYSE_URL, { method: 'POST', body: formData });
        const data = await resp.json();

        retirerTyping();

        if (data.success) {
            lastAnalyse = data;
            afficherResultatAnalyse(data);
            // Vider les fichiers après analyse
            attachedFiles = [];
            renderFileChips();
        } else {
            afficherErreur(data.error || 'Erreur lors de l\'analyse.');
        }
    } catch (e) {
        retirerTyping();
        afficherErreur('Erreur réseau : ' + e.message);
    } finally {
        isTyping = false;
        document.getElementById('sendBtn').disabled = false;
        ['btnBdd','btnTxt','btnBoth'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = false;
        });
    }
}

// ─── Affichage message ────────────────────
function afficherMessage(role, texte) {
    masquerBienvenue();
    const container = document.getElementById('chatMessages');

    const msg = document.createElement('div');
    msg.className = `eia-message ${role}`;

    const initiales = role === 'user' ? '{{ auth()->user()->initiales ?? "U" }}' : '🤖';
    const avatarHtml = `<div class="eia-msg-avatar">${role === 'ai' ? '<i class="fas fa-robot"></i>' : initiales}</div>`;

    const formatted = role === 'ai' ? formatMarkdown(texte) : escapeHtml(texte).replace(/\n/g, '<br>');

    const actionsHtml = role === 'ai' ? `
        <div class="eia-msg-actions">
            <button onclick="copierTexte(this)" data-text="${escapeAttr(texte)}">
                <i class="fas fa-copy"></i> Copier
            </button>
        </div>` : '';

    msg.innerHTML = `
        ${avatarHtml}
        <div class="eia-msg-body">
            <div class="eia-msg-bubble">${formatted}</div>
            ${actionsHtml}
        </div>`;

    container.appendChild(msg);
    scrollBas();
}

// ─── Afficher résultat analyse ────────────
function afficherResultatAnalyse(data) {
    masquerBienvenue();
    const container = document.getElementById('chatMessages');

    const nb       = data.nb_ecritures || 0;
    const equilibre = data.equilibre;
    const badgeHtml = equilibre
        ? '<span class="badge-equilibre"><i class="fas fa-check-circle"></i> Équilibré</span>'
        : '<span class="badge-desequilibre"><i class="fas fa-exclamation-triangle"></i> Déséquilibré</span>';

    let ecrituresHtml = '';
    if (data.ecritures && data.ecritures.length > 0) {
        const rows = data.ecritures.slice(0, 20).map(e => `
            <tr>
                <td>${e.date || ''}</td>
                <td><code>${e.journal || ''}</code></td>
                <td><strong>${e.compte || ''}</strong></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${e.libelle || ''}</td>
                <td style="text-align:right;color:#16a34a">${e.debit > 0 ? formatMontant(e.debit) : ''}</td>
                <td style="text-align:right;color:#dc2626">${e.credit > 0 ? formatMontant(e.credit) : ''}</td>
            </tr>`).join('');

        ecrituresHtml = `
            <div style="overflow-x:auto;margin-top:8px;border-radius:8px;border:1px solid var(--eia-border)">
                <table class="eia-ecritures-table">
                    <thead><tr>
                        <th>Date</th><th>Journal</th><th>Compte</th>
                        <th>Libellé</th><th>Débit</th><th>Crédit</th>
                    </tr></thead>
                    <tbody>${rows}</tbody>
                </table>
                ${data.ecritures.length > 20 ? `<div style="padding:8px 12px;font-size:12px;color:var(--eia-muted);border-top:1px solid var(--eia-border)">… et ${data.ecritures.length - 20} écritures supplémentaires</div>` : ''}
            </div>`;
    }

    const msg = document.createElement('div');
    msg.className = 'eia-message ai';
    msg.innerHTML = `
        <div class="eia-msg-avatar"><i class="fas fa-robot"></i></div>
        <div class="eia-msg-body">
            <div class="eia-msg-bubble">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                    <strong>Analyse terminée</strong> ${badgeHtml}
                </div>
                <div class="eia-stats-mini">
                    <div class="eia-stat-mini">
                        <div class="stat-val">${nb}</div>
                        <div class="stat-lbl">Écritures</div>
                    </div>
                    <div class="eia-stat-mini">
                        <div class="stat-val" style="color:#16a34a">${formatMontant(data.total_debit || 0)}</div>
                        <div class="stat-lbl">Total Débit</div>
                    </div>
                    <div class="eia-stat-mini">
                        <div class="stat-val" style="color:#dc2626">${formatMontant(data.total_credit || 0)}</div>
                        <div class="stat-lbl">Total Crédit</div>
                    </div>
                </div>
                ${ecrituresHtml}
                <div class="eia-result-actions">
                    <button class="eia-btn eia-btn-primary" onclick="lancerReview()">
                        <i class="fas fa-edit"></i> Réviser & Éditer
                    </button>
                    <button class="eia-btn eia-btn-outline" onclick="exporterTxt()">
                        <i class="fas fa-file-export"></i> Export TXT Sage
                    </button>
                </div>
            </div>
        </div>`;
    container.appendChild(msg);
    scrollBas();
}

// ─── Injection BDD ────────────────────────
async function injecterBdd() {
    if (!lastAnalyse) return;
    await actionPostAnalyse("{{ route('excel_ia.injecter_bdd') }}", 'injecter-bdd');
}
async function exporterTxt() {
    if (!lastAnalyse) return;
    // Soumettre le formulaire pour téléchargement
    const form = Object.assign(document.createElement('form'), {
        method: 'POST', action: "{{ route('excel_ia.export_txt') }}"
    });
    form.innerHTML = `<input name="_token" value="${CSRF_TOKEN}">
                      <input name="txt_sage" value="${escapeAttr(lastAnalyse.txt_sage || '')}">`;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
async function injecterEtTelecharger() {
    if (!lastAnalyse) return;
    await actionPostAnalyse("{{ route('excel_ia.injecter_et_telecharger') }}", 'injecter-et-telecharger');
}

async function actionPostAnalyse(url, mode) {
    try {
        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('ecritures_json', JSON.stringify(lastAnalyse.ecritures || []));
        formData.append('txt_sage', lastAnalyse.txt_sage || '');

        const resp = await fetch(url, { method: 'POST', body: formData });
        const data = await resp.json();

        if (data.success) {
            afficherMessage('ai', '✅ ' + (data.message || 'Opération réussie.'));
        } else {
            afficherErreur(data.error || data.message || 'Erreur.');
        }
    } catch (e) {
        afficherErreur('Erreur : ' + e.message);
    }
}

// ─── Typing indicator ─────────────────────
function afficherTyping() {
    const container = document.getElementById('chatMessages');
    const el = document.createElement('div');
    el.className = 'eia-message ai';
    el.id = 'typingIndicator';
    el.innerHTML = `
        <div class="eia-msg-avatar"><i class="fas fa-robot"></i></div>
        <div class="eia-msg-body">
            <div class="eia-typing"><span></span><span></span><span></span></div>
        </div>`;
    container.appendChild(el);
    scrollBas();
}
function retirerTyping() {
    const el = document.getElementById('typingIndicator');
    if (el) el.remove();
}

function afficherErreur(msg) {
    const container = document.getElementById('chatMessages');
    const el = document.createElement('div');
    el.className = 'eia-message ai';
    el.innerHTML = `
        <div class="eia-msg-avatar" style="background:#fee2e2"><i class="fas fa-exclamation-triangle" style="color:#dc2626"></i></div>
        <div class="eia-msg-body">
            <div class="eia-msg-bubble" style="background:#fee2e2;border-color:#fecaca;color:#dc2626">
                <strong>Erreur :</strong> ${escapeHtml(msg)}
            </div>
        </div>`;
    container.appendChild(el);
    scrollBas();
}

// ─── Utilitaires ──────────────────────────
function masquerBienvenue() {
    const el = document.getElementById('welcomeState');
    if (el) el.style.display = 'none';
}
function scrollBas() {
    const c = document.getElementById('chatMessages');
    setTimeout(() => { c.scrollTop = c.scrollHeight; }, 60);
}
function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escapeAttr(str) {
    return (str || '').replace(/"/g, '&quot;').replace(/\n/g,' ');
}
function formatMontant(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n));
}
function copierTexte(btn) {
    navigator.clipboard.writeText(btn.dataset.text || '').then(() => {
        btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i> Copier', 1800);
    });
}

// Markdown simplifié
function formatMarkdown(text) {
    if (!text) return '';
    return escapeHtml(text)
        .replace(/```([\s\S]*?)```/g, '<pre>$1</pre>')
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/\*([^*]+)\*/g, '<em>$1</em>')
        .replace(/^#{3} (.+)$/gm, '<h5 style="margin:10px 0 4px;font-size:13px;font-weight:700">$1</h5>')
        .replace(/^#{2} (.+)$/gm, '<h4 style="margin:12px 0 6px;font-size:14px;font-weight:700">$1</h4>')
        .replace(/^## (.+)$/gm, '<h4 style="margin:12px 0 6px">$1</h4>')
        .replace(/^- (.+)$/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>')
        .replace(/\n/g, '<br>');
}

// ─── DATA AUTOCOMPLETE ───────────────────
const PLAN_COMPTABLE = @json($comptes);
const PLAN_TIERS     = @json($tiers);

// ─── GESTION MODAL RÉVISION ───────────────
function lancerReview() {
    if (!lastAnalyse || !lastAnalyse.ecritures) {
        afficherErreur("Aucune donnée d'analyse disponible pour la révision.");
        return;
    }
    
    const overlay = document.getElementById('reviewOverlay');
    const body    = document.getElementById('gridBody');
    body.innerHTML = '';
    
    lastAnalyse.ecritures.forEach((e, i) => {
        ajouterLigne(e);
    });
    
    updateReviewTotals();
    overlay.style.display = 'flex';
}

function fermerReview() {
    document.getElementById('reviewOverlay').style.display = 'none';
}

function ajouterLigne(data = {}) {
    const body = document.getElementById('gridBody');
    const tr   = document.createElement('tr');
    
    tr.innerHTML = `
        <td><input type="text" class="eia-grid-input grid-date" value="${data.date || ''}" placeholder="JJMMAA"></td>
        <td><input type="text" class="eia-grid-input grid-journal" value="${data.journal || ''}"></td>
        <td>
            <input type="text" class="eia-grid-input grid-compte" value="${data.compte || ''}" list="dlComptes">
        </td>
        <td><input type="text" class="eia-grid-input grid-libelle" value="${data.libelle || ''}"></td>
        <td><input type="number" class="eia-grid-input grid-debit text-end" value="${data.debit || 0}" oninput="updateReviewTotals()"></td>
        <td><input type="number" class="eia-grid-input grid-credit text-end" value="${data.credit || 0}" oninput="updateReviewTotals()"></td>
        <td>
            <input type="text" class="eia-grid-input grid-tiers" value="${data.tiers || ''}" list="dlTiers">
        </td>
        <td>
            <button class="btn-remove-row" onclick="this.closest('tr').remove(); updateReviewTotals();">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    body.appendChild(tr);
    updateReviewTotals();
}

function updateReviewTotals() {
    let totalDebit  = 0;
    let totalCredit = 0;
    
    document.querySelectorAll('#gridBody tr').forEach(tr => {
        totalDebit  += parseFloat(tr.querySelector('.grid-debit').value) || 0;
        totalCredit += parseFloat(tr.querySelector('.grid-credit').value) || 0;
    });
    
    const balance = totalDebit - totalCredit;
    const isBalanced = Math.abs(balance) < 0.01;
    
    document.getElementById('valTotalDebit').innerText  = formatMontant(totalDebit);
    document.getElementById('valTotalCredit').innerText = formatMontant(totalCredit);
    document.getElementById('valBalance').innerText     = formatMontant(balance);
    
    const balEl = document.getElementById('valBalance');
    const badge = document.getElementById('reviewStatusBadge');
    
    if (isBalanced) {
        balEl.className = 'summary-value balanced';
        badge.className = 'review-badge badge-equilibre';
        badge.innerHTML = '<i class="fas fa-check-circle"></i> Équilibré';
    } else {
        balEl.className = 'summary-value unbalanced';
        badge.className = 'review-badge badge-desequilibre';
        badge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Déséquilibré';
    }
}

async function actionReview(mode) {
    const ecritures = [];
    document.querySelectorAll('#gridBody tr').forEach(tr => {
        ecritures.push({
            date:    tr.querySelector('.grid-date').value,
            journal: tr.querySelector('.grid-journal').value,
            compte:  tr.querySelector('.grid-compte').value,
            libelle: tr.querySelector('.grid-libelle').value,
            debit:   parseFloat(tr.querySelector('.grid-debit').value) || 0,
            credit:  parseFloat(tr.querySelector('.grid-credit').value) || 0,
            tiers:   tr.querySelector('.grid-tiers').value
        });
    });

    if (mode === 'txt_only') {
        // Pour l'export TXT, on passe par le backend avec les données modifiées
        const form = Object.assign(document.createElement('form'), {
            method: 'POST', action: "{{ route('excel_ia.export_txt') }}"
        });
        form.innerHTML = `<input name="_token" value="${CSRF_TOKEN}">
                          <input name="analyse_id" value="${lastAnalyse.analyse_id || 0}">
                          <input name="ecritures_json" value='${JSON.stringify(ecritures)}'>`;
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        return;
    }

    // Injection BDD
    const btn = document.getElementById('btnReviewInject');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Injection...';

    try {
        const formData = new FormData();
        formData.append('_token', CSRF_TOKEN);
        formData.append('ecritures_json', JSON.stringify(ecritures));
        if (lastAnalyse.analyse_id) formData.append('analyse_id', lastAnalyse.analyse_id);

        const resp = await fetch("{{ route('excel_ia.injecter_bdd') }}", { method: 'POST', body: formData });
        const data = await resp.json();

        if (data.success) {
            fermerReview();
            afficherMessage('ai', '✅ ' + data.message);
        } else {
            alert("Erreur: " + (data.error || 'Inconnue'));
        }
    } catch (e) {
        alert("Erreur réseau: " + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-database"></i> Valider & Injecter en BDD';
    }
}

// DataLists pour autocompletion légère
document.body.insertAdjacentHTML('beforeend', `
    <datalist id="dlComptes">
        ${PLAN_COMPTABLE.map(c => `<option value="${c.numero_de_compte}">${c.numero_de_compte} - ${c.intitule}</option>`).join('')}
    </datalist>
    <datalist id="dlTiers">
        ${PLAN_TIERS.map(t => `<option value="${t.numero_de_tiers}">${t.numero_de_tiers} - ${t.intitule}</option>`).join('')}
    </datalist>
`);
</script>
                    </div> <!-- /px-4 -->

{{-- ─── MODAL DE RÉVISION IA ────────────────── --}}
<div class="eia-review-overlay" id="reviewOverlay">
    <div class="eia-review-modal">
        <div class="eia-review-header">
            <div>
                <h4 class="mb-0" style="font-weight:700">Révision des Écritures IA</h4>
                <p class="text-muted mb-0" style="font-size:12px">Validez ou corrigez les écritures avant l'enregistrement final.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div id="reviewStatusBadge" class="review-badge badge-equilibre">
                    <i class="fas fa-check-circle"></i> Équilibré
                </div>
                <button class="btn btn-icon btn-outline-secondary" onclick="fermerReview()" style="border-radius:50%">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="eia-review-body">
            <div class="eia-grid-container">
                <table class="eia-grid-table" id="reviewGrid">
                    <thead>
                        <tr>
                            <th style="width:100px">Date</th>
                            <th style="width:100px">Journal</th>
                            <th style="width:150px">Compte</th>
                            <th>Libellé</th>
                            <th style="width:120px; text-align:right">Débit</th>
                            <th style="width:120px; text-align:right">Crédit</th>
                            <th style="width:120px">Tiers</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="gridBody">
                        <!-- Lignes injectées via JS -->
                    </tbody>
                </table>
            </div>
            <button class="btn btn-link btn-sm mt-3" onclick="ajouterLigne()" style="color:var(--eia-accent)">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </button>
        </div>

        <div class="eia-grid-summary">
            <div class="summary-item">
                <span class="summary-label">Total Débit</span>
                <span class="summary-value" id="valTotalDebit">0</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Crédit</span>
                <span class="summary-value" id="valTotalCredit">0</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Équilibre</span>
                <span class="summary-value balanced" id="valBalance">0</span>
            </div>
        </div>

        <div class="eia-review-footer">
            <button class="btn btn-outline-secondary" onclick="fermerReview()">Annuler</button>
            <div class="d-flex gap-2">
                <button class="eia-btn eia-btn-outline" onclick="actionReview('txt_only')" id="btnReviewTxt">
                    <i class="fas fa-file-export"></i> Export Sage TXT
                </button>
                <button class="eia-btn eia-btn-primary" onclick="actionReview('bdd_only')" id="btnReviewInject">
                    <i class="fas fa-database"></i> Valider & Injecter en BDD
                </button>
            </div>
        </div>
    </div>
</div>
                </div> <!-- /content-wrapper -->
            </div> <!-- /layout-page -->
        </div> <!-- /layout-container -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @include('components.footer')
</body>
</html>
