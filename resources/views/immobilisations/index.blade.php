<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        transition: all 0.2s;
    }
    .table-header-sticky {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        box-shadow: 0 1px 0 #dedede;
    }
</style>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')

        <div class="layout-page">
            @include('components.header', ['page_title' => 'Gestion des Immobilisations'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-check-circle fs-4 me-2"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error-circle fs-4 me-2"></i>
                                <div>{{ session('error') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- KPIs -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100 border-0 card-hover">
                                <div class="card-body">
                                    <span class="d-block mb-1 text-muted text-uppercase small fw-bold">Total Actifs</span>
                                    <h3 class="card-title text-primary mb-0">{{ $totalImmobilisations }}</h3>
                                    <small class="text-muted">Biens enregistrés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100 border-0 card-hover">
                                <div class="card-body">
                                    <span class="d-block mb-1 text-muted text-uppercase small fw-bold">VNC Totale</span>
                                    <h3 class="card-title text-success mb-0">{{ number_format($vncTotale, 0, ',', ' ') }}</h3>
                                    <small class="text-muted">FCFA</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100 border-0 card-hover">
                                <div class="card-body">
                                    <span class="d-block mb-1 text-muted text-uppercase small fw-bold">Prov. {{ date('Y') }}</span>
                                    <h3 class="card-title text-warning mb-0">{{ number_format($dotationsAnnee, 0, ',', ' ') }}</h3>
                                    <small class="text-muted">FCFA</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100 border-0 d-flex flex-column justify-content-center p-3 gap-2 bg-transparent shadow-none">
                                <a href="{{ route('immobilisations.create') }}" class="btn btn-primary w-100 shadow-sm">
                                    <i class="bx bx-plus me-1"></i> Nouveau Bien
                                </a>
                                <form action="{{ route('immobilisations.generer_dotations') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning w-100 bg-white" onclick="return confirm('Générer les dotations ?')">
                                        <i class="bx bx-cog me-1"></i> Calculer Amort.
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Split -->
                    <div class="row g-3">
                        
                        <!-- Left: Potential Assets -->
                        <div class="col-lg-6">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-header border-bottom py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-primary">
                                        <i class="bx bx-list-ul me-2"></i> Écritures Immobilisables
                                    </h5>
                                    <span class="badge bg-label-primary">{{ $ecrituresImmobilisables->count() }} détectées</span>
                                </div>
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="table-header-sticky">
                                            <tr>
                                                <th>Date</th>
                                                <th>Libellé / Compte</th>
                                                <th class="text-end">Montant</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($ecrituresImmobilisables as $ecriture)
                                            <tr>
                                                <td class="small">{{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold small">{{ Str::limit($ecriture->libelle ?? $ecriture->description_operation, 25) }}</span>
                                                        <small class="text-muted text-xs">
                                                            {{ $ecriture->planComptable->numero_de_compte ?? '' }}
                                                            @if($ecriture->planTiers)
                                                                - {{ $ecriture->planTiers->compte_tiers }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-dark small">
                                                    {{ number_format($ecriture->debit, 0, ',', ' ') }}
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('immobilisations.create', ['ecriture_id' => $ecriture->id]) }}" class="btn btn-sm btn-primary py-1 px-2" title="Créer l'immobilisation">
                                                        <i class="bx bx-right-arrow-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted small">
                                                    Rien à signaler en classe 2.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Registered Assets -->
                        <div class="col-lg-6">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-header border-bottom py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-success">
                                        <i class="bx bx-check-shield me-2"></i> Immobilisations Enregistrées
                                    </h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Filtre
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item" href="{{ route('immobilisations.index') }}">Tous</a></li>
                                            <li><a class="dropdown-item" href="{{ route('immobilisations.index', ['categorie' => 'corporelle']) }}">Corporelles</a></li>
                                            <li><a class="dropdown-item" href="{{ route('immobilisations.index', ['categorie' => 'incorporelle']) }}">Incorporelles</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-header-sticky">
                                            <tr>
                                                <th>Bien</th>
                                                <th>VNC</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($immobilisations as $immo)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold small">{{ $immo->code }}</span>
                                                        <span class="small text-muted">{{ Str::limit($immo->libelle, 25) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark small">{{ number_format($immo->getValeurNetteComptable(), 0, ',', ' ') }}</span>
                                                        <small class="text-xs text-muted">/ {{ number_format($immo->valeur_acquisition, 0, ',', ' ') }}</small>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('immobilisations.show', $immo->id) }}" class="btn btn-outline-secondary border-0"><i class="bx bx-show"></i></a>
                                                        <a href="{{ route('immobilisations.edit', $immo->id) }}" class="btn btn-outline-secondary border-0"><i class="bx bx-edit"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-muted small">
                                                    Aucun actif enregistré.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- / Split Row -->

                </div>
            </div>
            @include('components.footer')
        </div>
    </div>
</div>
</body>
</html>
