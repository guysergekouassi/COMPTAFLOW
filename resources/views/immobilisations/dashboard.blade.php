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

                    <!-- KPIs -->
                    <!-- KPIs -->
                    <div class="d-flex align-items-stretch gap-4 mb-4" style="overflow-x: auto; padding-bottom: 5px;">
                        
                        <!-- Total Actifs -->
                        <div class="card border-0 shadow-sm rounded-4 flex-grow-1 card-hover" style="min-width: 260px;">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Actifs</h6>
                                    <div class="p-2 rounded-3 bg-label-primary text-primary">
                                        <i class="bx bx-cube fs-4"></i>
                                    </div>
                                </div>
                                <h4 class="mb-2 fw-extrabold text-dark">{{ $totalImmobilisations }}</h4>
                                <div class="small fw-semibold text-primary">
                                    <i class="bx bx-check-circle me-1"></i> Biens enregistrés
                                </div>
                            </div>
                        </div>

                        <!-- VNC Totale -->
                        <div class="card border-0 shadow-sm rounded-4 flex-grow-1 card-hover" style="min-width: 260px;">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">VNC Totale</h6>
                                    <div class="p-2 rounded-3 bg-label-success text-success">
                                        <i class="bx bx-wallet fs-4"></i>
                                    </div>
                                </div>
                                <h4 class="mb-2 fw-extrabold text-dark">{{ number_format($vncTotale, 0, ',', ' ') }} <span class="fs-6 text-muted fw-normal">FCFA</span></h4>
                                <div class="small fw-semibold text-success">
                                    <i class="bx bx-trending-up me-1"></i> Valeur comptable
                                </div>
                            </div>
                        </div>

                        <!-- Prov. Année -->
                        <div class="card border-0 shadow-sm rounded-4 flex-grow-1 card-hover" style="min-width: 260px;">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Prov. {{ date('Y') }}</h6>
                                    <div class="p-2 rounded-3 bg-label-warning text-warning">
                                        <i class="bx bx-time-five fs-4"></i>
                                    </div>
                                </div>
                                <h4 class="mb-2 fw-extrabold text-dark">{{ number_format($dotationsAnnee, 0, ',', ' ') }} <span class="fs-6 text-muted fw-normal">FCFA</span></h4>
                                <div class="small fw-semibold text-warning">
                                    <i class="bx bx-loader-circle me-1"></i> Dotations
                                </div>
                            </div>
                        </div>

                        <!-- Actions Rapides -->
                        <div class="card border-0 shadow-sm rounded-4 flex-grow-1 bg-label-secondary" style="min-width: 260px;">
                            <div class="card-body p-4 d-flex flex-column justify-content-center gap-2">
                                <a href="{{ route('immobilisations.create') }}" class="btn btn-primary w-100 shadow-sm fw-bold">
                                    <i class="bx bx-plus me-1"></i> Nouveau Bien
                                </a>
                                <form action="{{ route('immobilisations.generer_dotations') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning w-100 bg-white fw-bold" onclick="return confirm('Générer les dotations ?')">
                                        <i class="bx bx-cog me-1"></i> Calculer Amort.
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Split -->
                    <div class="row g-3">
                        
                        <!-- Left: Potential Assets -->
                        <div class="col-md-6">
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
                                                        <span class="fw-semibold small">{{ \Illuminate\Support\Str::limit($ecriture->libelle ?? $ecriture->description_operation, 25) }}</span>
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
                        <div class="col-md-6">
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
                                                        <span class="small text-muted">{{ \Illuminate\Support\Str::limit($immo->libelle, 25) }}</span>
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
