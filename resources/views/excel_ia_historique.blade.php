<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Historique <span class="text-gradient">Analyse IA</span>'])
                <div class="content-wrapper">
<div class="px-4 py-4" style="max-width: 1200px; margin: 0 auto; padding-top: 40px !important;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('excel_ia.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="fas fa-arrow-left"></i> Retour à l'Analyse IA
            </a>
            <h2 class="fw-bold fs-3 text-dark mb-0">Historique des analyses intelligentes</h2>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            @if($analyses->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-history text-muted fs-1 mb-3" style="opacity: 0.4;"></i>
                    <h5 class="text-dark fw-bold">Aucune analyse terminée</h5>
                    <p class="text-muted">Les prochains historiques apparaîtront ici.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4 text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Date / Utilisateur</th>
                                <th class="py-3 text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Mois Cible</th>
                                <th class="py-3 text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Bilan Écritures</th>
                                <th class="py-3 text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em;">Statut BD</th>
                                <th class="py-3 text-end px-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analyses as $a)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark">{{ $a->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-muted small"><i class="fas fa-user-circle me-1"></i> {{ $a->user->name ?? 'Système' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                        <i class="fas fa-calendar-alt me-1"></i> {{ $a->mois_cible }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="fw-semibold text-dark">{{ $a->nb_ecritures }} lignes générées</span>
                                        <span class="small {{ $a->equilibre ? 'text-success' : 'text-danger' }}">
                                            <i class="fas fa-{{ $a->equilibre ? 'check' : 'times' }}-circle"></i>
                                            {{ number_format($a->total_debit, 0, ',', ' ') }} XOF
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($a->injecte_bdd)
                                        <span class="badge bg-success px-2 py-1"><i class="fas fa-database me-1"></i> Injecté</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1"><i class="fas fa-clock me-1"></i> En attente</span>
                                    @endif
                                    
                                    @if($a->txt_telecharge)
                                        <span class="badge bg-info text-dark px-2 py-1 ms-1"><i class="fas fa-file-alt"></i> TXT Exporté</span>
                                    @endif
                                </td>
                                <td class="text-end px-4">
                                    <a href="{{ route('excel_ia.historique.show', $a->id) }}" class="btn btn-sm btn-light text-primary fw-bold shadow-sm rounded-3">
                                        Voir détails <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $analyses->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</div>
                    <!-- / Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>

    @include('components.footer')

</body>
</html>
