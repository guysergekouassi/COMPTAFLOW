@include('components.head')

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gouvernance / Liste des Exercices Comptables'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Standardisé -->
                        <div class="d-flex justify-content-between align-items-center mb-6">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Gouvernance / Liste des Exercices Comptables</h5>
                                <p class="text-muted small mb-0">Pilotez et surveillez l'ensemble des exercices comptables du réseau.</p>
                            </div>
                            <a href="{{ route('superadmin.accounting.create') }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fa-solid fa-plus me-2"></i> Nouvel Exercice
                            </a>
                        </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="fw-semibold">Intitulé</th>
                                        <th class="fw-semibold">Entreprise</th>
                                        <th class="fw-semibold">Administrateur</th>
                                        <th class="fw-semibold">Période</th>
                                        <th class="fw-semibold">Statut</th>
                                        <th class="fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exercices as $exercice)
                                        <tr>
                                            <td class="fw-bold text-primary">{{ $exercice->intitule }}</td>
                                            <td>{{ $exercice->company->company_name ?? 'N/A' }}</td>
                                            <td>{{ $exercice->user->name ?? 'N/A' }} {{ $exercice->user->last_name ?? '' }}</td>
                                            <td>
                                                <small class="d-block text-muted">Du {{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }}</small>
                                                <small class="d-block text-muted">Au {{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                @if($exercice->cloturer)
                                                    <span class="badge bg-secondary">Clôturé</span>
                                                @else
                                                    <span class="badge bg-success">Ouvert</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('superadmin.accounting.edit', $exercice->id) }}" class="btn btn-outline-primary" title="Modifier">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('superadmin.accounting.destroy', $exercice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet exercice ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Aucun exercice comptable trouvé.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
