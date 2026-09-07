<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')

<style>
    :root {
        --bulk-primary: #1e40af;
        --bulk-accent: #3b82f6;
    }

    .bulk-header-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e40af 60%, #3b82f6 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .bulk-header-card::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }

    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        margin-bottom: 1.5rem;
    }

    .filter-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.35rem;
        display: block;
    }

    .table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        margin-bottom: 100px; /* Space for action bar */
    }

    #bulkTable thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 1rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    #bulkTable tbody tr {
        transition: background 0.15s;
    }

    #bulkTable tbody tr:hover { background: #f8fafc; }
    #bulkTable tbody tr.selected-row { background: #eff6ff !important; }

    #bulkTable tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        color: #334155;
    }

    .compte-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 0.2rem 0.55rem;
        font-weight: 700;
        font-size: 0.76rem;
        font-family: monospace;
    }

    /* ─── Floating Action Bar ─── */
    .bulk-action-bar {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        z-index: 1050;
        background: white;
        border-top: 3px solid var(--bulk-primary);
        box-shadow: 0 -8px 30px rgba(30,64,175,0.15);
        padding: 0.85rem 2rem;
        display: none;
    }

    .bulk-action-bar.visible {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        animation: slideUpBar 0.28s ease;
    }

    @keyframes slideUpBar {
        from { transform: translateY(100%); opacity: 0; }
        to   { transform: translateY(0);   opacity: 1; }
    }

    .sel-badge {
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        color: white;
        border-radius: 50px;
        padding: 0.35rem 1rem;
        font-weight: 700;
        font-size: 0.82rem;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .field-select-wrap { min-width: 180px; max-width: 220px; flex-shrink: 0; }
    .value-wrap { flex: 1; min-width: 220px; max-width: 360px; }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 10px !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header', ['page_title' => "Modification par lot"])
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- Header --}}
                    <div class="bulk-header-card">
                        <div class="position-relative" style="z-index:1;">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                                <div>
                                    <h1 class="h3 fw-bold mb-1" style="color:white;">
                                        <i class="fa-solid fa-list-check me-2"></i>Modification par lot
                                    </h1>
                                    <p class="mb-0" style="color: rgba(255,255,255,0.78); font-size: 0.9rem;">
                                        Filtrez, sélectionnez les écritures puis modifiez le compte, le tiers, le libellé ou la référence en une seule opération.
                                    </p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('adjustment.reimputation') }}" class="btn btn-sm rounded-pill" style="background: rgba(255,255,255,0.18); color:white; border: 1px solid rgba(255,255,255,0.3);">
                                        <i class="fa-solid fa-right-left me-1"></i> Réimputation
                                    </a>
                                    <a href="{{ route('adjustment.duplicates') }}" class="btn btn-sm rounded-pill" style="background: rgba(255,255,255,0.18); color:white; border: 1px solid rgba(255,255,255,0.3);">
                                        <i class="fa-solid fa-clone me-1"></i> Doublons
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="filter-card">
                        <form action="{{ route('adjustment.bulk_edit') }}" method="GET">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="filter-label">Libellé / Référence</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control border-start-0"
                                            value="{{ request('search') }}" placeholder="Ex: Achat, Facture #123...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="filter-label">Journal</label>
                                    <select name="journal_id" class="form-select">
                                        <option value="">Tous</option>
                                        @foreach($journals as $j)
                                            <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>
                                                {{ $j->code_journal }}
                                            </option>
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
                                <div class="col-md-2 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-fill">
                                        <i class="fa-solid fa-filter me-1"></i> Filtrer
                                    </button>
                                    <a href="{{ route('adjustment.bulk_edit') }}" class="btn btn-outline-secondary rounded-pill px-3">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Table --}}
                    <div class="table-card">
                        <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 fw-bold text-dark mb-0">Écritures comptables</h2>
                                <small class="text-muted">{{ $entries->total() }} écriture(s) — Sélectionnez des lignes pour les modifier</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                                <label for="checkAll" class="text-muted small mb-0">Tout sélectionner</label>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="bulkTable">
                                <thead>
                                    <tr>
                                        <th style="width:48px;"></th>
                                        <th>Date</th>
                                        <th>Journal</th>
                                        <th>N° Saisie</th>
                                        <th>Libellé</th>
                                        <th>Compte Général</th>
                                        <th>Tiers</th>
                                        <th class="text-end">Débit</th>
                                        <th class="text-end">Crédit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entries as $entry)
                                    <tr data-id="{{ $entry->id }}">
                                        <td class="ps-4">
                                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $entry->id }}">
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
                                        </td>
                                        <td>
                                            <span class="compte-badge">
                                                {{ $entry->planComptable->numero_de_compte ?? 'N/A' }}
                                            </span>
                                            <div class="text-muted" style="font-size:0.71rem; margin-top:2px; max-width:130px;" class="text-truncate">
                                                {{ $entry->planComptable->intitule ?? '' }}
                                            </div>
                                        </td>
                                        <td><small class="text-muted font-monospace">{{ $entry->planTiers->numero_de_tiers ?? '-' }}</small></td>
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
                                                <span class="text-muted">Aucune écriture ne correspond aux critères.</span>
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

                </div>
                @include('components.footer')
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════
     FLOATING ACTION BAR
═══════════════════════════ --}}
<div class="bulk-action-bar" id="bulkActionBar">
    <div class="sel-badge">
        <i class="fa-solid fa-check-circle me-1"></i>
        <span id="selCount">0</span> sélectionné(s)
    </div>

    <div class="vr d-none d-md-block"></div>

    <!-- Field selector -->
    <div class="field-select-wrap">
        <select id="bulkField" class="form-select form-select-sm" style="border-radius: 10px; height:42px;">
            <option value="">Modifier quel champ ?</option>
            <option value="plan_comptable_id">Compte Général</option>
            <option value="plan_tiers_id">Compte Tiers</option>
            <option value="description_operation">Libellé</option>
            <option value="reference_piece">Référence</option>
        </select>
    </div>

    <!-- Value input (dynamic) -->
    <div class="value-wrap" id="bulkValueWrap">
        <input type="text" id="bulkValueText" class="form-control form-control-sm"
            style="border-radius:10px; height:42px;" placeholder="Choisissez d'abord un champ..." disabled>
    </div>

    <button type="button" id="btnApplyBulk" class="btn btn-primary rounded-pill px-4 fw-bold" style="height:42px; white-space:nowrap;">
        <i class="fa-solid fa-check me-2"></i>Appliquer
    </button>

    <button type="button" id="btnCancelBulk" class="btn btn-outline-secondary rounded-pill px-3" style="height:42px;">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll      = document.getElementById('checkAll');
    const actionBar     = document.getElementById('bulkActionBar');
    const selCountEl    = document.getElementById('selCount');
    const bulkField     = document.getElementById('bulkField');
    const bulkValueWrap = document.getElementById('bulkValueWrap');

    // ── Helpers ──
    function getCheckedIds() {
        return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(c => c.value);
    }

    function updateBar() {
        const ids = getCheckedIds();
        selCountEl.textContent = ids.length;
        if (ids.length > 0) {
            actionBar.classList.add('visible');
        } else {
            actionBar.classList.remove('visible');
        }
    }

    // ── Checkboxes ──
    checkAll?.addEventListener('change', function () {
        document.querySelectorAll('.row-checkbox').forEach(c => {
            c.checked = checkAll.checked;
            const row = c.closest('tr');
            if (row) row.classList.toggle('selected-row', c.checked);
        });
        updateBar();
    });

    document.querySelectorAll('.row-checkbox').forEach(c => {
        c.addEventListener('change', function () {
            const row = this.closest('tr');
            if (row) row.classList.toggle('selected-row', this.checked);
            if (!this.checked && checkAll) checkAll.checked = false;
            updateBar();
        });
    });

    // ── Dynamic field input ──
    bulkField.addEventListener('change', function () {
        const val = this.value;
        bulkValueWrap.innerHTML = '';

        if (!val) {
            const inp = document.createElement('input');
            inp.type = 'text'; inp.id = 'bulkValueText';
            inp.className = 'form-control form-control-sm';
            inp.style.cssText = 'border-radius:10px;height:42px;';
            inp.placeholder = 'Choisissez d\'abord un champ...';
            inp.disabled = true;
            bulkValueWrap.appendChild(inp);
            return;
        }

        if (val === 'plan_comptable_id' || val === 'plan_tiers_id') {
            const sel = document.createElement('select');
            sel.id = 'bulkValueSelect';
            sel.className = 'form-select form-select-sm select2-bulk';
            bulkValueWrap.appendChild(sel);

            const type = (val === 'plan_comptable_id') ? 'account' : 'tier';
            const placeholder = (type === 'account') ? 'Chercher un compte général...' : 'Chercher un tiers...';

            if (window.jQuery && $.fn.select2) {
                $(sel).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    language: 'fr',
                    placeholder: placeholder,
                    allowClear: true,
                    minimumInputLength: 1,
                    ajax: {
                        url: '{{ route("adjustment.search_references") }}',
                        dataType: 'json',
                        delay: 250,
                        data: params => ({ q: params.term, type }),
                        processResults: data => ({ results: data }),
                        cache: true
                    }
                });
            }
        } else {
            const inp = document.createElement('input');
            inp.type = 'text'; inp.id = 'bulkValueText';
            inp.className = 'form-control form-control-sm';
            inp.style.cssText = 'border-radius:10px;height:42px;';
            inp.placeholder = val === 'description_operation' ? 'Nouveau libellé...' : 'Nouvelle référence...';
            bulkValueWrap.appendChild(inp);
        }
    });

    // ── Annuler ──
    document.getElementById('btnCancelBulk')?.addEventListener('click', function () {
        document.querySelectorAll('.row-checkbox').forEach(c => {
            c.checked = false;
            const row = c.closest('tr');
            if (row) row.classList.remove('selected-row');
        });
        if (checkAll) checkAll.checked = false;
        bulkField.value = '';
        bulkField.dispatchEvent(new Event('change'));
        actionBar.classList.remove('visible');
    });

    // ── Appliquer ──
    document.getElementById('btnApplyBulk')?.addEventListener('click', function () {
        const ids = getCheckedIds();
        const field = bulkField.value;

        let value;
        const sel = document.getElementById('bulkValueSelect');
        const txt = document.getElementById('bulkValueText');
        if (sel) {
            value = window.jQuery ? $('#bulkValueSelect').val() : sel.value;
        } else if (txt) {
            value = txt.value.trim();
        }

        if (!ids.length) { alert('Sélectionnez au moins une écriture.'); return; }
        if (!field) { alert('Choisissez le champ à modifier.'); return; }
        if (!value) { alert('Entrez ou sélectionnez une valeur.'); return; }

        const fieldLabels = {
            plan_comptable_id: 'Compte Général',
            plan_tiers_id: 'Compte Tiers',
            description_operation: 'Libellé',
            reference_piece: 'Référence'
        };

        if (!confirm(`Modifier "${fieldLabels[field]}" pour ${ids.length} écriture(s) ?\nCette action est irréversible.`)) return;

        const btn = document.getElementById('btnApplyBulk');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>En cours...';

        fetch('{{ route("adjustment.bulk_update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids, field, value })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Appliquer';
            if (data.success) {
                sessionStorage.setItem('bulk_success', data.message);
                window.location.reload();
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Appliquer';
            console.error(err);
            alert('Erreur réseau.');
        });
    });

    // ── Flash message ──
    const msg = sessionStorage.getItem('bulk_success');
    if (msg) {
        sessionStorage.removeItem('bulk_success');
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top:80px;right:20px;z-index:9999;min-width:320px;border-radius:12px;box-shadow:0 8px 24px rgba(5,150,105,0.2);';
        alertDiv.innerHTML = `<i class="fa-solid fa-check-circle me-2"></i>${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
});
</script>
</body>
</html>
