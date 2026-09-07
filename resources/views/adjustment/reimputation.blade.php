<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')

<style>
    :root {
        --reimput-primary: #1e40af;
        --reimput-accent: #3b82f6;
        --reimput-success: #059669;
        --reimput-warning: #d97706;
    }

    .reimput-header-card {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 60%, #60a5fa 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .reimput-header-card::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .reimput-header-card::after {
        content: '';
        position: absolute;
        bottom: -70px; left: 40px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 1.5rem;
    }

    .table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    #reimputTable thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    #reimputTable tbody tr {
        transition: background 0.15s;
    }

    #reimputTable tbody tr:hover {
        background: #f0f7ff;
    }

    #reimputTable tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        color: #334155;
    }

    .compte-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 0.25rem 0.6rem;
        font-weight: 700;
        font-size: 0.78rem;
        font-family: monospace;
    }

    /* Floating action bar */
    .reimput-action-bar {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        z-index: 1050;
        background: white;
        border-top: 3px solid var(--reimput-primary);
        box-shadow: 0 -8px 30px rgba(30, 64, 175, 0.15);
        padding: 1rem 2rem;
        display: none;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from { transform: translateY(100%); opacity: 0; }
        to   { transform: translateY(0);   opacity: 1; }
    }

    .reimput-action-bar.visible {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .selection-badge {
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white;
        border-radius: 50px;
        padding: 0.4rem 1rem;
        font-weight: 700;
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 10px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
    }

    .arrow-indicator {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border: 2px solid #bbf7d0;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #059669;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .filter-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.4rem;
        display: block;
    }

    @media (max-width: 768px) {
        .reimput-action-bar.visible { flex-direction: column; align-items: stretch; }
    }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header', ['page_title' => "Réimputation"])
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- Header --}}
                    <div class="reimput-header-card">
                        <div class="position-relative" style="z-index:1;">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                                <div>
                                    <h1 class="h3 fw-bold mb-1" style="color:white;">
                                        <i class="fa-solid fa-right-left me-2"></i>Réimputation de comptes
                                    </h1>
                                    <p class="mb-0" style="color: rgba(255,255,255,0.82); font-size: 0.9rem;">
                                        Sélectionnez des écritures et réimputez-les vers un autre compte général — comme dans Sage.
                                    </p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('adjustment.bulk_edit') }}" class="btn btn-sm rounded-pill" style="background: rgba(255,255,255,0.18); color:white; border: 1px solid rgba(255,255,255,0.3); backdrop-filter:blur(6px);">
                                        <i class="fa-solid fa-list-check me-1"></i> Modification par lot
                                    </a>
                                    <a href="{{ route('adjustment.duplicates') }}" class="btn btn-sm rounded-pill" style="background: rgba(255,255,255,0.18); color:white; border: 1px solid rgba(255,255,255,0.3); backdrop-filter:blur(6px);">
                                        <i class="fa-solid fa-clone me-1"></i> Doublons
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="filter-card">
                        <form action="{{ route('adjustment.reimputation') }}" method="GET" id="filterForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="filter-label">Compte général (filtre)</label>
                                    <select name="compte_id" id="filterCompte" class="form-select select2-filter">
                                        <option value="">Tous les comptes</option>
                                        @foreach($comptes as $c)
                                            <option value="{{ $c->id }}" {{ request('compte_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->numero_de_compte }} - {{ $c->intitule }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="filter-label">Journal</label>
                                    <select name="journal_id" class="form-select">
                                        <option value="">Tous</option>
                                        @foreach($journals as $j)
                                            <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code_journal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="filter-label">Du</label>
                                    <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="filter-label">Au</label>
                                    <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="filter-label">Libellé / Réf.</label>
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher...">
                                </div>
                                <div class="col-md-1 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100">
                                        <i class="fa-solid fa-filter"></i>
                                    </button>
                                    <a href="{{ route('adjustment.reimputation') }}" class="btn btn-outline-secondary rounded-pill px-3">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Info active filter --}}
                    @if($selectedCompte)
                    <div class="alert border-0 d-flex align-items-center gap-3 mb-4" style="background: #eff6ff; border-radius: 12px;">
                        <i class="fa-solid fa-circle-info text-primary fs-5"></i>
                        <div>
                            Filtrage actif : Compte <strong class="font-monospace">{{ $selectedCompte->numero_de_compte }}</strong> — {{ $selectedCompte->intitule }}
                            &nbsp;·&nbsp; <strong>{{ $entries->total() }}</strong> écriture(s) trouvée(s).
                        </div>
                    </div>
                    @endif

                    {{-- Table --}}
                    <div class="table-card">
                        <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 fw-bold text-dark mb-0">Écritures comptables</h2>
                                <small class="text-muted">{{ $entries->total() }} écriture(s) — Cochez pour réimputer</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input" id="checkAllRows" title="Tout sélectionner">
                                <label for="checkAllRows" class="text-muted small mb-0">Tout sélectionner</label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="reimputTable">
                                <thead>
                                    <tr>
                                        <th style="width:48px;"></th>
                                        <th>Date</th>
                                        <th>Journal</th>
                                        <th>N° Saisie</th>
                                        <th>Libellé</th>
                                        <th>Compte actuel</th>
                                        <th>Tiers</th>
                                        <th class="text-end">Débit</th>
                                        <th class="text-end">Crédit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entries as $entry)
                                    <tr data-id="{{ $entry->id }}" data-compte="{{ $entry->planComptable->numero_de_compte ?? '' }}">
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input row-check" value="{{ $entry->id }}">
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary rounded-pill px-2">
                                                {{ $entry->codeJournal->code_journal ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td><small class="text-muted font-monospace">{{ $entry->n_saisie }}</small></td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $entry->description_operation }}">
                                                {{ $entry->description_operation }}
                                            </div>
                                            @if($entry->reference_piece)
                                                <small class="text-muted">Réf: {{ $entry->reference_piece }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="compte-badge">
                                                <i class="fa-solid fa-hashtag" style="font-size:0.65rem;"></i>
                                                {{ $entry->planComptable->numero_de_compte ?? 'N/A' }}
                                            </span>
                                            <div class="text-muted" style="font-size: 0.72rem; margin-top: 2px; max-width: 140px;" class="text-truncate">
                                                {{ $entry->planComptable->intitule ?? '' }}
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted font-monospace">{{ $entry->planTiers->numero_de_tiers ?? '-' }}</small>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            {{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', ' ') : '-' }}
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            {{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', ' ') : '-' }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center gap-2">
                                                <i class="fa-solid fa-inbox fa-2x text-muted opacity-50"></i>
                                                <span class="text-muted">Aucune écriture ne correspond aux filtres sélectionnés.</span>
                                                <a href="{{ route('adjustment.reimputation') }}" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Réinitialiser les filtres</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($entries->hasPages())
                        <div class="px-4 py-3 border-top bg-white">
                            {{ $entries->withQueryString()->links() }}
                        </div>
                        @endif
                    </div>

                </div><!-- /container -->
                @include('components.footer')
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     FLOATING ACTION BAR (Réimputation)
═══════════════════════════════════════════ --}}
<div class="reimput-action-bar" id="reimputActionBar">
    <!-- Selection count -->
    <div class="selection-badge" id="selCountBadge">
        <i class="fa-solid fa-check-circle me-1"></i>
        <span id="selCountNum">0</span> écriture(s) sélectionnée(s)
    </div>

    <div class="arrow-indicator">
        <i class="fa-solid fa-right-long"></i>
    </div>

    <!-- New account select -->
    <div style="flex: 1; min-width: 260px; max-width: 380px;">
        <label class="filter-label mb-1" style="font-size:0.7rem; color:#64748b;">Nouveau compte général</label>
        <select id="newCompteSelect" class="form-select select2-reimput" style="border-radius: 10px;">
            <option value="">-- Chercher le compte cible --</option>
        </select>
    </div>

    <button type="button" id="btnApplyReimput" class="btn btn-success rounded-pill px-4 fw-bold" style="height: 44px; white-space: nowrap;">
        <i class="fa-solid fa-check me-2"></i>Appliquer la réimputation
    </button>

    <button type="button" id="btnCancelReimput" class="btn btn-outline-secondary rounded-pill px-3" style="height: 44px;">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Initialiser Select2 pour le filtre comptes ──
    if (window.jQuery && $.fn.select2) {
        $('#filterCompte').select2({
            theme: 'bootstrap4',
            width: '100%',
            language: 'fr',
            placeholder: 'Tous les comptes'
        });
    }

    // ── Gestion des checkboxes ──
    const checkAll      = document.getElementById('checkAllRows');
    const actionBar     = document.getElementById('reimputActionBar');
    const selCountEl    = document.getElementById('selCountNum');

    function getChecked() {
        return Array.from(document.querySelectorAll('.row-check:checked'));
    }

    function updateBar() {
        const checked = getChecked();
        selCountEl.textContent = checked.length;
        if (checked.length > 0) {
            actionBar.classList.add('visible');
        } else {
            actionBar.classList.remove('visible');
        }
    }

    checkAll?.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
        updateBar();
    });

    document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', updateBar));

    // Décocher checkAll si on décoche une ligne
    document.querySelectorAll('.row-check').forEach(c => {
        c.addEventListener('change', function () {
            if (!this.checked && checkAll) checkAll.checked = false;
            updateBar();
        });
    });

    // ── Select2 pour le compte cible (AJAX) ──
    if (window.jQuery && $.fn.select2) {
        $('#newCompteSelect').select2({
            theme: 'bootstrap4',
            width: '100%',
            language: 'fr',
            placeholder: '-- Chercher le compte cible (n° ou libellé) --',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route("adjustment.search_references") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, type: 'account' };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });
    }

    // ── Annuler ──
    document.getElementById('btnCancelReimput')?.addEventListener('click', function () {
        document.querySelectorAll('.row-check').forEach(c => c.checked = false);
        if (checkAll) checkAll.checked = false;
        if (window.jQuery && $.fn.select2) $('#newCompteSelect').val(null).trigger('change');
        updateBar();
    });

    // ── Appliquer la réimputation ──
    document.getElementById('btnApplyReimput')?.addEventListener('click', function () {
        const ids = getChecked().map(c => c.value);
        const newCompteId = window.jQuery ? $('#newCompteSelect').val() : document.getElementById('newCompteSelect').value;
        const newCompteTxt = window.jQuery
            ? ($('#newCompteSelect').find(':selected').text() || '')
            : (document.getElementById('newCompteSelect')?.options[document.getElementById('newCompteSelect').selectedIndex]?.text || '');

        if (ids.length === 0) {
            alert('Veuillez sélectionner au moins une écriture.');
            return;
        }

        if (!newCompteId) {
            alert('Veuillez choisir le compte cible.');
            return;
        }

        if (!confirm(`Réimputer ${ids.length} écriture(s) vers le compte :\n${newCompteTxt}\n\nCette action est irréversible. Continuer ?`)) {
            return;
        }

        const btn = document.getElementById('btnApplyReimput');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>En cours...';

        fetch('{{ route("adjustment.reimputation.apply") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids, new_compte_id: newCompteId })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Appliquer la réimputation';
            if (data.success) {
                // Flash message and reload
                sessionStorage.setItem('reimput_success', data.message);
                window.location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Appliquer la réimputation';
            console.error(err);
            alert('Erreur réseau. Veuillez réessayer.');
        });
    });

    // ── Flash message après rechargement ──
    const msg = sessionStorage.getItem('reimput_success');
    if (msg) {
        sessionStorage.removeItem('reimput_success');
        // Créer un toast ou alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top:80px;right:20px;z-index:9999;min-width:320px;border-radius:12px;box-shadow:0 8px 24px rgba(5,150,105,0.2);';
        alertDiv.innerHTML = `<i class="fa-solid fa-check-circle me-2"></i>${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // ── Highlight sélectionnée ──
    document.querySelectorAll('.row-check').forEach(c => {
        c.addEventListener('change', function () {
            const row = this.closest('tr');
            if (row) row.style.background = this.checked ? '#f0f7ff' : '';
        });
    });
});
</script>
</body>
</html>
