@extends('layouts.app')

@section('title', 'Importation en cours — COMPTAFLOW')

@section('content')
<div class="import-processing-wrapper">
    <div class="import-card" id="processingCard">

        {{-- En-tête --}}
        <div class="import-header">
            <div class="import-icon spinning" id="statusIcon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
            </div>
            <h1 class="import-title" id="statusTitle">Importation en cours…</h1>
            <p class="import-subtitle" id="statusMessage">Analyse et insertion des écritures comptables.</p>
        </div>

        {{-- Barre de progression --}}
        <div class="progress-container">
            <div class="progress-track">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="progress-labels">
                <span id="progressPct">0%</span>
                <span id="progressEta"></span>
            </div>
        </div>

        {{-- Étapes visuelles --}}
        <div class="steps-list">
            <div class="step active" id="step1">
                <div class="step-dot"></div>
                <div class="step-info">
                    <span class="step-label">Chargement des données</span>
                    <span class="step-status">✓</span>
                </div>
            </div>
            <div class="step" id="step2">
                <div class="step-dot"></div>
                <div class="step-info">
                    <span class="step-label">Traitement des lignes</span>
                    <span class="step-status" id="step2Status">—</span>
                </div>
            </div>
            <div class="step" id="step3">
                <div class="step-dot"></div>
                <div class="step-info">
                    <span class="step-label">Vérification de l'équilibre</span>
                    <span class="step-status" id="step3Status">—</span>
                </div>
            </div>
            <div class="step" id="step4">
                <div class="step-dot"></div>
                <div class="step-info">
                    <span class="step-label">Finalisation</span>
                    <span class="step-status" id="step4Status">—</span>
                </div>
            </div>
        </div>

        {{-- Message d'erreur (caché par défaut) --}}
        <div class="error-block" id="errorBlock" style="display:none">
            <div class="error-icon">✕</div>
            <div class="error-msg" id="errorMsg"></div>
        </div>

        {{-- Bouton de redirection (caché par défaut) --}}
        <div class="action-block" id="actionBlock" style="display:none">
            <a href="{{ $reportUrl }}" class="btn-primary" id="reportBtn">
                Voir le rapport
            </a>
        </div>

        <p class="import-note">
            Ne fermez pas cette page. L'importation continue même si vous attendez.<br>
            <strong>Import #{{ $import->id }}</strong>
        </p>
    </div>
</div>

@push('styles')
<style>
    .import-processing-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 2rem;
    }
    .import-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 1.5rem;
        padding: 3rem;
        max-width: 560px;
        width: 100%;
        backdrop-filter: blur(12px);
        box-shadow: 0 32px 64px rgba(0,0,0,0.4);
        text-align: center;
        color: #e2e8f0;
        font-family: 'Inter', sans-serif;
    }
    .import-icon {
        width: 72px; height: 72px;
        margin: 0 auto 1.5rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white;
        transition: background 0.4s;
    }
    .import-icon svg { width: 36px; height: 36px; }
    .import-icon.spinning svg { animation: spin 1.2s linear infinite; }
    .import-icon.done { background: linear-gradient(135deg, #10b981, #059669); }
    .import-icon.error { background: linear-gradient(135deg, #ef4444, #dc2626); }

    @keyframes spin { to { transform: rotate(360deg); } }

    .import-title {
        font-size: 1.5rem; font-weight: 700;
        margin: 0 0 0.5rem; color: #f8fafc;
    }
    .import-subtitle {
        color: #94a3b8; margin: 0 0 2rem;
        font-size: 0.9rem; line-height: 1.6;
    }
    .progress-container { margin-bottom: 2rem; }
    .progress-track {
        height: 10px; background: rgba(255,255,255,0.08);
        border-radius: 99px; overflow: hidden; margin-bottom: 0.5rem;
    }
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #a78bfa);
        border-radius: 99px;
        transition: width 0.6s ease;
        background-size: 200% auto;
        animation: shimmer 2s linear infinite;
    }
    @keyframes shimmer {
        to { background-position: 200% center; }
    }
    .progress-labels {
        display: flex; justify-content: space-between;
        font-size: 0.8rem; color: #64748b;
    }
    .steps-list { text-align: left; margin-bottom: 2rem; }
    .step {
        display: flex; align-items: center; gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        opacity: 0.4;
        transition: opacity 0.3s;
    }
    .step.active { opacity: 1; }
    .step-dot {
        width: 10px; height: 10px; border-radius: 50%;
        background: #334155; flex-shrink: 0;
        transition: background 0.3s;
    }
    .step.active .step-dot { background: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.2); }
    .step.done .step-dot { background: #10b981; }
    .step-info { display: flex; justify-content: space-between; flex: 1; align-items: center; }
    .step-label { font-size: 0.9rem; color: #e2e8f0; }
    .step-status { font-size: 0.8rem; color: #64748b; }
    .step.active .step-status { color: #a78bfa; }
    .step.done .step-status { color: #10b981; }

    .error-block {
        background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
        border-radius: 0.75rem; padding: 1.25rem;
        margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;
        text-align: left;
    }
    .error-icon { color: #ef4444; font-size: 1.2rem; flex-shrink: 0; }
    .error-msg { color: #fca5a5; font-size: 0.85rem; line-height: 1.6; }
    .btn-primary {
        display: inline-block; padding: 0.9rem 2.5rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; border-radius: 0.75rem; text-decoration: none;
        font-weight: 600; font-size: 1rem;
        transition: transform 0.15s, box-shadow 0.15s;
        margin-bottom: 1.5rem;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,0.4); }
    .import-note { color: #475569; font-size: 0.78rem; line-height: 1.6; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const STATUS_URL = "{{ $statusUrl }}";
    const REPORT_URL = "{{ $reportUrl }}";
    let pollInterval = null;
    let startTime   = Date.now();

    function poll() {
        fetch(STATUS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                updateUI(data);
                if (data.status === 'done') {
                    clearInterval(pollInterval);
                    onDone();
                } else if (data.status === 'error') {
                    clearInterval(pollInterval);
                    onError(data.error || 'Une erreur est survenue.');
                }
            })
            .catch(err => console.warn('Poll error:', err));
    }

    function updateUI(data) {
        const pct  = Math.min(100, Math.max(0, data.progress || 0));
        const msg  = data.message || '';

        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('progressPct').textContent = pct + '%';
        if (msg) document.getElementById('statusMessage').textContent = msg;

        // Étapes
        if (pct >= 5)  setStep('step2', pct < 85 ? 'active' : 'done');
        if (pct >= 85) setStep('step3', pct < 95 ? 'active' : 'done');
        if (pct >= 95) setStep('step4', pct < 100 ? 'active' : 'done');

        // ETA estimé
        const elapsed = (Date.now() - startTime) / 1000;
        if (pct > 5 && pct < 100) {
            const totalEst = elapsed / (pct / 100);
            const remain   = Math.max(0, Math.round(totalEst - elapsed));
            document.getElementById('progressEta').textContent =
                remain > 60
                    ? Math.round(remain/60) + ' min restante(s)'
                    : remain + 's restante(s)';
        }
    }

    function setStep(id, state) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('active', 'done');
        el.classList.add(state);
        const statusEl = el.querySelector('.step-status');
        if (statusEl) statusEl.textContent = state === 'done' ? '✓' : '…';
    }

    function onDone() {
        const icon = document.getElementById('statusIcon');
        icon.classList.remove('spinning');
        icon.classList.add('done');
        icon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';

        document.getElementById('statusTitle').textContent = 'Importation réussie !';
        document.getElementById('statusMessage').textContent = 'Toutes les écritures ont été créées avec succès.';

        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressPct').textContent = '100%';
        document.getElementById('progressEta').textContent = '';

        setStep('step2', 'done');
        setStep('step3', 'done');
        setStep('step4', 'done');

        document.getElementById('actionBlock').style.display = 'block';

        // Redirection automatique après 2 secondes
        setTimeout(() => { window.location.href = REPORT_URL; }, 2500);
    }

    function onError(msg) {
        const icon = document.getElementById('statusIcon');
        icon.classList.remove('spinning');
        icon.classList.add('error');
        icon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';

        document.getElementById('statusTitle').textContent = 'Erreur d\'importation';
        document.getElementById('statusMessage').textContent = 'Une erreur est survenue pendant le traitement.';
        document.getElementById('errorMsg').textContent = msg;
        document.getElementById('errorBlock').style.display = 'flex';
        document.getElementById('actionBlock').style.display = 'block';
        document.getElementById('reportBtn').textContent = 'Voir les détails';
    }

    // Lance le polling toutes les 1.5 secondes
    poll();
    pollInterval = setInterval(poll, 1500);
})();
</script>
@endpush
@endsection
