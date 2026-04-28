<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')

<style>
    .bulk-panel {
        background: #fdfdfd;
        border-top: 4px solid #1e40af;
        position: sticky;
        top: 10px;
        z-index: 100;
    }
    .select2-container--bootstrap4 .select2-selection {
        border-radius: 12px !important;
        height: 48px !important;
        display: flex !important;
        align-items: center !important;
    }
    .sticky-actions {
        position: sticky;
        bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 50px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none; /* Show only when items selected */
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
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1">Modification par lot</h1>
                    <p class="text-muted">Filtrez les écritures et modifiez les comptes ou libellés en un clic.</p>
                </div>
                <a href="{{ route('adjustment.duplicates') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fa-solid fa-clone me-2"></i> Détection de doublons
                </a>
            </div>

            <!-- FILTRES -->
            <div class="card border-0 shadow-sm rounded-xl mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('adjustment.bulk_edit') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Recherche (Libellé, Réf)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" value="{{ request('search') }}" placeholder="Ex: Achat, Facture #123...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted text-uppercase">Journal</label>
                            <select name="journal_id" class="form-select">
                                <option value="">Tous</option>
                                @foreach($journals as $j)
                                    <option value="{{ $j->id }}" {{ request('journal_id') == $j->id ? 'selected' : '' }}>{{ $j->code_journal }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted text-uppercase">Du</label>
                            <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted text-uppercase">Au</label>
                            <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                                <i class="fa-solid fa-filter me-2"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLEAU -->
            <div class="card border-0 shadow-sm rounded-xl">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="bulkTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th>Date</th>
                                <th>Journal</th>
                                <th>N° Saisie</th>
                                <th>Libellé</th>
                                <th>Compte Général</th>
                                <th>Compte Tiers</th>
                                <th>Débit</th>
                                <th>Crédit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                                <tr>
                                    <td class="ps-4">
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $entry->id }}">
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-soft-primary text-primary">{{ $entry->codeJournal->code_journal ?? 'N/A' }}</span></td>
                                    <td><small class="text-muted">{{ $entry->n_saisie }}</small></td>
                                    <td>{{ $entry->description_operation }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $entry->planComptable->numero_de_compte ?? 'N/A' }}</span>
                                            <small class="text-muted text-truncate" style="max-width: 150px;">{{ $entry->planComptable->intitule ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $entry->planTiers->numero_de_tiers ?? '-' }}</td>
                                    <td class="fw-bold">{{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', ' ') : '-' }}</td>
                                    <td class="fw-bold">{{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', ' ') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <p class="text-muted">Aucune écriture ne correspond à vos critères.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($entries->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $entries->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- FLOATING ACTION BAR -->
<div class="sticky-actions fixed-bottom w-auto mx-auto mb-4" id="floatingActions">
    <div class="d-flex align-items-center gap-4 px-4 overflow-visible">
        <div class="text-dark fw-bold">
            <span id="selectedCount">0</span> sélectionné(s)
        </div>
        <div class="vr"></div>
        <div class="d-flex align-items-center gap-2">
            <select id="bulkField" class="form-select form-select-sm rounded-pill" style="width: 200px;">
                <option value="">Modifier quel champ ?</option>
                <option value="plan_comptable_id">Compte Général</option>
                <option value="plan_tiers_id">Compte Tiers</option>
                <option value="description_operation">Libellé</option>
                <option value="reference_piece">Référence</option>
            </select>
            
            <div id="bulkValueContainer" style="width: 300px;">
                <input type="text" id="bulkValueInput" class="form-control form-control-sm rounded-pill" placeholder="Nouvelle valeur...">
            </div>

            <button type="button" onclick="applyBulkUpdate()" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-check-circle me-2"></i> Appliquer
            </button>
            <button type="button" onclick="resetSelection()" class="btn btn-light rounded-pill">Annuler</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.row-checkbox');
    const floatingActions = document.getElementById('floatingActions');
    const selectedCountDisplay = document.getElementById('selectedCount');
    const bulkField = document.getElementById('bulkField');
    const bulkValueContainer = document.getElementById('bulkValueContainer');

    function updateSelectionUI() {
        const selected = document.querySelectorAll('.row-checkbox:checked');
        selectedCountDisplay.innerText = selected.length;
        floatingActions.style.display = selected.length > 0 ? 'block' : 'none';
        floatingActions.classList.add('animate__animated', 'animate__fadeInUp');
    }

    checkAll.addEventListener('change', function() {
        rowChecks.forEach(c => c.checked = checkAll.checked);
        updateSelectionUI();
    });

    rowChecks.forEach(c => {
        c.addEventListener('change', updateSelectionUI);
    });

    // Gestion de l'input dynamique (Select2 pour les comptes/tiers)
    bulkField.addEventListener('change', function() {
        const val = this.value;
        bulkValueContainer.innerHTML = '';
        
        if (val === 'plan_comptable_id') {
            const select = document.createElement('select');
            select.id = 'bulkValueInput';
            select.className = 'form-select select2-bulk';
            bulkValueContainer.appendChild(select);
            initSelect2(select, 'account');
        } else if (val === 'plan_tiers_id') {
            const select = document.createElement('select');
            select.id = 'bulkValueInput';
            select.className = 'form-select select2-bulk';
            bulkValueContainer.appendChild(select);
            initSelect2(select, 'tier');
        } else {
            const input = document.createElement('input');
            input.type = 'text';
            input.id = 'bulkValueInput';
            input.className = 'form-control form-control-sm rounded-pill';
            input.placeholder = 'Texte de remplacement...';
            bulkValueContainer.appendChild(input);
        }
    });

    function initSelect2(element, type) {
        $(element).select2({
            theme: 'bootstrap4',
            placeholder: type === 'account' ? 'Chercher un compte général...' : 'Chercher un tiers...',
            allowClear: true,
            ajax: {
                url: '{{ route("adjustment.search_references") }}',
                dataType: 'json',
                data: function (params) {
                    return { q: params.term, type: type };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });
    }
});

function resetSelection() {
    document.getElementById('checkAll').checked = false;
    document.querySelectorAll('.row-checkbox').forEach(c => c.checked = false);
    document.getElementById('floatingActions').style.display = 'none';
}

function applyBulkUpdate() {
    const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(c => c.value);
    const field = document.getElementById('bulkField').value;
    const value = $('#bulkValueInput').val() || document.getElementById('bulkValueInput').value;

    if (!field || !value) {
        alert('Veuillez sélectionner un champ et une valeur.');
        return;
    }

    if (!confirm(`Voulez-vous modifier ${ids.length} écritures ? Cette action est irréversible.`)) return;

    fetch('{{ route("adjustment.bulk_update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ ids, field, value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur réseau.');
    });
}
</script>
                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
