<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')

<style>
    .dup-card {
        border-left: 5px solid #3b82f6;
        transition: all 0.2s;
    }
    .dup-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .dup-group-header {
        background: #f8fafc;
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 12px 12px 0 0;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => "Détection de Doublons"])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Détection de Doublons</h1>
            <p class="text-muted">Analyse des écritures ayant la même date, le même journal et les mêmes montants.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('adjustment.bulk_edit') }}" class="btn btn-outline-primary rounded-pill">
                <i class="fa-solid fa-list-check me-2"></i> Modification par lot
            </a>
            <button onclick="window.location.reload()" class="btn btn-primary rounded-pill">
                <i class="fa-solid fa-rotate me-2"></i> Actualiser l'analyse
            </button>
        </div>
    </div>

    @if(empty($groupedEntries))
        <div class="card border-0 shadow-sm rounded-xl py-5 text-center">
            <div class="card-body">
                <div class="mb-4">
                    <i class="fa-solid fa-circle-check text-success" style="font-size: 50px;"></i>
                </div>
                <h4 class="fw-bold">Aucun doublon détecté</h4>
                <p class="text-muted">Bravo ! Vos écritures semblent cohérentes pour cet exercice.</p>
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm rounded-xl mb-4 d-flex align-items-center">
            <i class="fa-solid fa-triangle-exclamation me-3 fs-4"></i>
            <div>
                <strong>{{ count($groupedEntries) }} groupes suspects trouvés.</strong> 
                Vérifiez si ces écritures sont des erreurs de saisie ou si elles sont légitimes.
            </div>
        </div>

        <div style="max-height: 80vh; overflow-y: auto; padding-right: 10px;">
            @foreach($groupedEntries as $index => $group)
                <div class="card border-0 shadow-sm rounded-xl mb-5 dup-card">
                    <div class="dup-group-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary rounded-pill me-3">{{ count($group['entries']) }} lignes</span>
                            <div>
                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($group['criteria']->date)->format('d/m/Y') }}</span>
                                <span class="mx-2 text-muted">|</span>
                                <span class="text-primary fw-bold">{{ $group['entries']->first()->codeJournal->code_journal ?? 'N/A' }}</span>
                                <span class="mx-2 text-muted">|</span>
                                <span class="text-success fw-bold">{{ number_format($group['criteria']->debit ?: $group['criteria']->credit, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">N° Saisie</th>
                                        <th>Référence</th>
                                        <th>Libellé</th>
                                        <th>Compte</th>
                                        <th>Tiers</th>
                                        <th>Débit</th>
                                        <th>Crédit</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['entries'] as $entry)
                                        <tr id="entry-row-{{ $entry->id }}">
                                            <td class="ps-4">
                                                <small class="text-muted fw-bold">{{ $entry->n_saisie }}</small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $entry->reference_piece ?: '-' }}</span></td>
                                            <td>{{ $entry->description_operation }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark">{{ $entry->planComptable->numero_de_compte ?? 'N/A' }}</span>
                                                    <small class="text-muted truncate-text" title="{{ $entry->planComptable->intitule ?? '' }}">{{ $entry->planComptable->intitule ?? '' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $entry->planTiers->numero_de_tiers ?? '-' }}</td>
                                            <td class="text-primary fw-bold">{{ $entry->debit > 0 ? number_format($entry->debit, 0, ',', ' ') : '-' }}</td>
                                            <td class="text-danger fw-bold">{{ $entry->credit > 0 ? number_format($entry->credit, 0, ',', ' ') : '-' }}</td>
                                            <td class="text-end pe-4">
                                                <button onclick="deleteEntry({{ $entry->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="fa-solid fa-trash me-1"></i> Supprimer
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
function deleteEntry(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette ligne d\'écriture ? Cette action est irréversible.')) return;

    fetch(`/ecriture/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`entry-row-${id}`).style.opacity = '0.3';
            document.getElementById(`entry-row-${id}`).style.pointerEvents = 'none';
        } else {
            alert('Erreur lors de la suppression : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur réseau.');
    });
}
</script>

<style>
.truncate-text {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
}
.rounded-xl { border-radius: 12px !important; }
</style>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
