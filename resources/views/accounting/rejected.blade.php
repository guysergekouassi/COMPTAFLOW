<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<style>
    .rejected-card {
        border-left: 5px solid #ff4d49;
        transition: transform 0.2s;
    }
    .rejected-card:hover {
        transform: translateY(-2px);
    }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => "Écritures Rejetées"])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1 text-danger">Écritures Rejetées</h4>
                                <p class="text-muted mb-0">Consultez les motifs et apportez les corrections nécessaires</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="card shadow-none border-0 bg-transparent">
                                    <div class="card-body p-0">
                                        @forelse($ecritures->groupBy('n_saisie') as $nSaisie => $group)
                                            @php 
                                                $first = $group->first();
                                                // Retrouver la demande d'approbation correspondante pour avoir le motif
                                                $approval = \App\Models\Approval::where('type', 'accounting_entry')
                                                    ->where('approvable_id', $first->id)
                                                    ->where('status', 'rejected')
                                                    ->latest()
                                                    ->first();
                                            @endphp
                                            <div class="card mb-4 border-0 shadow-sm rejected-card">
                                                <div class="card-header d-flex justify-content-between align-items-center bg-label-danger py-2">
                                                    <div>
                                                        <span class="fw-bold text-danger">Saisie N° {{ $nSaisie }}</span>
                                                        <span class="mx-2 text-muted">|</span>
                                                        <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i> {{ $first->date }}</small>
                                                    </div>
                                                    <span class="badge bg-danger rounded-pill px-3">Rejeté</span>
                                                </div>
                                                <div class="card-body pt-4">
                                                    <div class="row mb-4">
                                                        <div class="col-md-12">
                                                            <div class="p-3 rounded-3 bg-lighter d-flex align-items-center border border-danger border-opacity-25">
                                                                <div class="avatar avatar-md bg-label-danger rounded-circle me-3">
                                                                    <i class="fa-solid fa-comment-slash fs-4"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Motif du rejet</div>
                                                                    <div class="fw-bold text-dark fs-5 italic">"{{ $approval->comment ?? 'Aucun motif précisé' }}"</div>
                                                                    <small class="text-muted">Par {{ $approval->handler->name ?? 'Admin' }} le {{ $approval->updated_at->format('d/m/Y à H:i') }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless align-middle">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th>Journal</th>
                                                                    <th>Description</th>
                                                                    <th>Compte G.</th>
                                                                    <th>Débit</th>
                                                                    <th>Crédit</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($group as $item)
                                                                <tr class="border-bottom border-light">
                                                                    <td><span class="badge bg-label-secondary">{{ $item->codeJournal->code_journal }}</span></td>
                                                                    <td class="small">{{ $item->description_operation }}</td>
                                                                    <td><span class="fw-medium">{{ $item->planComptable->numero_de_compte }}</span></td>
                                                                    <td class="text-dark fw-bold">{{ number_format($item->debit, 0, ',', ' ') }}</td>
                                                                    <td class="text-dark fw-bold">{{ number_format($item->credit, 0, ',', ' ') }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-end gap-2">
                                                    <a href="{{ route('accounting_entry_real', ['n_saisie' => $nSaisie, 'action' => 'edit_rejected']) }}" class="btn btn-label-primary shadow-none">
                                                        <i class="fa-solid fa-pen-to-square me-2"></i> Corriger l'écriture
                                                    </a>
                                                    <form action="{{ route('ecriture.delete_saisie', $nSaisie) }}" method="POST" onsubmit="return confirm('Supprimer définitivement cette saisie rejetée ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-label-danger shadow-none">
                                                            <i class="fa-solid fa-trash-can me-2"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 glass-card">
                                                <div class="avatar avatar-xl bg-label-success rounded-circle mx-auto mb-4">
                                                    <i class="fa-solid fa-check fa-2x"></i>
                                                </div>
                                                <h4 class="fw-bold">Félicitations !</h4>
                                                <p class="text-muted">Vous n'avez aucune écriture rejetée.</p>
                                                <a href="{{ route('accounting_entry_list') }}" class="btn btn-primary px-4">Retour à la liste</a>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
