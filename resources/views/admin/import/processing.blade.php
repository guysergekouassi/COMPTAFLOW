@include('components.head')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
    body { font-family: 'Outfit', sans-serif; background-color: #f1f5f9; }

    .processing-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        padding: 2rem;
    }
    .processing-card {
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px rgba(99,102,241,0.10);
        padding: 3rem 2.5rem;
        max-width: 560px;
        width: 100%;
        text-align: center;
    }
    .processing-icon {
        width: 80px; height: 80px;
        margin: 0 auto 1.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        color: #fff;
        transition: background 0.4s;
    }
    .processing-icon svg { width: 40px; height: 40px; }
    .processing-icon.spinning svg { animation: spin 1.2s linear infinite; }
    .processing-icon.done { background: linear-gradient(135deg, #10b981, #059669); }
    .processing-icon.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
    @keyframes spin { to { transform: rotate(360deg); } }

    .processing-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem; }
    .processing-subtitle { color: #64748b; font-size: 0.95rem; margin: 0 0 2rem; }

    .progress-track {
        height: 12px; background: #e2e8f0; border-radius: 99px;
        overflow: hidden; margin-bottom: 0.5rem;
    }
    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #a78bfa);
        border-radius: 99px;
        transition: width 0.6s ease;
        background-size: 200% auto;
        animation: shimmer 2s linear infinite;
    }
    @keyframes shimmer { to { background-position: 200% center; } }
    .progress-labels { display: flex; justify-content: space-between; font-size: 0.82rem; color: #94a3b8; margin-bottom: 2rem; }

    .steps-list { text-align: left; margin-bottom: 2rem; }
    .step {
        display: flex; align-items: center; gap: 1rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9;
        opacity: 0.4; transition: opacity 0.3s;
    }
    .step.active, .step.done { opacity: 1; }
    .step-dot {
        width: 12px; height: 12px; border-radius: 50%;
        background: #cbd5e1; flex-shrink: 0; transition: background 0.3s;
    }
    .step.active .step-dot { background: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.15); }
    .step.done .step-dot { background: #10b981; }
    .step-info { display: flex; justify-content: space-between; flex: 1; align-items: center; }
    .step-label { font-size: 0.9rem; color: #334155; font-weight: 500; }
    .step-status { font-size: 0.82rem; color: #94a3b8; }
    .step.active .step-status { color: #6366f1; font-weight: 600; }
    .step.done .step-status { color: #10b981; font-weight: 600; }

    .error-block {
        background: #fef2f2; border: 1px solid #fecaca;
        border-radius: 0.75rem; padding: 1.25rem;
        margin-bottom: 1.5rem; display: flex; gap: 1rem;
        align-items: flex-start; text-align: left;
    }
    .error-icon { color: #ef4444; font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
    .error-msg { color: #b91c1c; font-size: 0.85rem; line-height: 1.6; }

    .btn-primary-proc {
        display: inline-block; padding: 0.85rem 2.5rem;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff; border-radius: 0.75rem; text-decoration: none;
        font-weight: 600; font-size: 0.95rem;
        transition: transform 0.15s, box-shadow 0.15s;
        margin-bottom: 1.5rem;
    }
    .btn-primary-proc:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,0.35); color: #fff; }
    .processing-note { color: #94a3b8; font-size: 0.78rem; line-height: 1.7; }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Importation <span class="text-indigo-600">en cours</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="processing-wrapper">
                            <div class="processing-card">

                                {{-- Icône animée --}}
                                <div class="processing-icon spinning" id="statusIcon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                                    </svg>
                                </div>

                                <h2 class="processing-title" id="statusTitle">Importation en cours…</h2>
                                <p class="processing-subtitle" id="statusMessage">Analyse et insertion des écritures comptables.</p>

                                {{-- Barre de progression --}}
                                <div class="progress-track">
                                    <div class="progress-bar" id="progressBar" style="width:0%"></div>
                                </div>
                                <div class="progress-labels">
                                    <span id="progressPct">0%</span>
                                    <span id="progressEta"></span>
                                </div>

                                {{-- Étapes --}}
                                <div class="steps-list">
                                    <div class="step done" id="step1">
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

                                {{-- Erreur --}}
                                <div class="error-block" id="errorBlock" style="display:none">
                                    <div class="error-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                                    <div class="error-msg" id="errorMsg"></div>
                                </div>

                                {{-- Bouton rapport --}}
                                <div id="actionBlock" style="display:none">
                                    <a href="{{ route('admin.import.report.view', $import->id) }}" class="btn-primary-proc" id="reportBtn">
                                        <i class="fa-solid fa-chart-bar me-2"></i>Voir le rapport
                                    </a>
                                </div>

                                <p class="processing-note">
                                    Ne fermez pas cette page — l'importation se poursuit en arrière-plan.<br>
                                    <strong>Import #{{ $import->id }}</strong>
                                </p>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
(function() {
    var STATUS_URL = "{{ route('admin.import.job.status', $import->id) }}";
    var REPORT_URL = "{{ route('admin.import.report.view', $import->id) }}";
    var startTime  = Date.now();
    var pollTimer  = null;

    function poll() {
        fetch(STATUS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateUI(data);
                if (data.status === 'done') {
                    clearInterval(pollTimer);
                    onDone();
                } else if (data.status === 'error') {
                    clearInterval(pollTimer);
                    onError(data.error || 'Une erreur est survenue.');
                }
            })
            .catch(function(e) { console.warn('Poll error:', e); });
    }

    function updateUI(data) {
        var pct = Math.min(100, Math.max(0, data.progress || 0));
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('progressPct').textContent = pct + '%';
        if (data.message) document.getElementById('statusMessage').textContent = data.message;
        if (pct >= 5)  setStep('step2', pct < 85 ? 'active' : 'done', pct < 85 ? '…' : '✓');
        if (pct >= 85) setStep('step3', pct < 95 ? 'active' : 'done', pct < 95 ? '…' : '✓');
        if (pct >= 95) setStep('step4', pct < 100 ? 'active' : 'done', pct < 100 ? '…' : '✓');
        var elapsed = (Date.now() - startTime) / 1000;
        if (pct > 5 && pct < 100) {
            var total  = elapsed / (pct / 100);
            var remain = Math.max(0, Math.round(total - elapsed));
            document.getElementById('progressEta').textContent =
                remain > 60 ? Math.round(remain / 60) + ' min restante(s)' : remain + 's restant(es)';
        }
    }

    function setStep(id, state, statusText) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'step ' + state;
        var st = el.querySelector('.step-status');
        if (st) st.textContent = statusText || (state === 'done' ? '✓' : '…');
    }

    function onDone() {
        var icon = document.getElementById('statusIcon');
        icon.className = 'processing-icon done';
        icon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
        document.getElementById('statusTitle').textContent = 'Importation réussie !';
        document.getElementById('statusMessage').textContent = 'Toutes les écritures ont été créées avec succès.';
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressPct').textContent = '100%';
        document.getElementById('progressEta').textContent = '';
        setStep('step2', 'done', '✓');
        setStep('step3', 'done', '✓');
        setStep('step4', 'done', '✓');
        document.getElementById('actionBlock').style.display = 'block';
        setTimeout(function() { window.location.href = REPORT_URL; }, 2500);
    }

    function onError(msg) {
        var icon = document.getElementById('statusIcon');
        icon.className = 'processing-icon error';
        icon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        document.getElementById('statusTitle').textContent = "Erreur d'importation";
        document.getElementById('statusMessage').textContent = 'Des erreurs ont été détectées. Consultez le rapport.';
        document.getElementById('errorMsg').textContent = msg;
        document.getElementById('errorBlock').style.display = 'flex';
        document.getElementById('actionBlock').style.display = 'block';
        document.getElementById('reportBtn').textContent = 'Voir les détails';
    }

    poll();
    pollTimer = setInterval(poll, 2000);
})();
</script>
