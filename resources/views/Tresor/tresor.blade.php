<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('components.header')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="mb-3">
                            <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
                                <i class='bx bx-reply-stroke'></i> Retour
                            </a>
                        </div>

                        <!-- Badges dates / intitule si fourni -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <div class="badge bg-primary text-white px-3 py-2 rounded">
                                {{ $data['date_debut'] ?? 'N/A' }}
                            </div>

                            <div class="badge bg-secondary text-white px-3 py-2 rounded">
                                {{ $data['date_fin'] ?? 'N/A' }}
                            </div>

                            <div class="badge bg-warning text-dark px-3 py-2 rounded">
                                {{ $data['intitule'] ?? 'N/A' }}
                            </div>
                        </div>

                        <!-- Section table -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Journaux de trésorerie</h5>
                                <a href="{{ route('tresorerie') }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus"></i> Ajouter
                                </a>
                            </div>

                            <!-- Filtre personnalisé -->
                            <div class="collapse px-3 pt-2" id="filterPanel">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <input type="number" id="filter-annee" class="form-control" placeholder="Filtrer par Année" />
                                    </div>
                                    <div class="col-md-3">
                                        <select id="filter-mois" class="form-control">
                                            <option value="">Filtrer par Mois</option>
                                            @foreach (range(1, 12) as $m)
                                                <option value="{{ $m }}">
                                                    {{ \Carbon\Carbon::createFromDate(null, $m)->locale('fr')->monthName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" id="filter-code" class="form-control" placeholder="Filtrer par Code Journal" />
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" id="filter-type" class="form-control" placeholder="Filtrer par Type" />
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <button class="btn btn-primary w-100" id="apply-filters">Appliquer les filtres</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-secondary w-100" id="reset-filters">Réinitialiser les filtres</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table" id="tresorerieTable">
                                    <thead>
                                        <tr>
                                            <th>Année</th>
                                            <th>Mois</th>
                                            <th>Code journal</th>
                                            <th>Intitulé</th>
                                            <th>Type</th>
                                            <th>Compte trésorerie</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($journaux as $journal)
                                            <tr>
                                                <td>{{ $journal->annee }}</td>
                                                <td>{{ \Carbon\Carbon::createFromDate(null, $journal->mois ?? 1)->locale('fr')->monthName }}</td>
                                                <td>{{ $journal->codeJournal->code_journal ?? 'N/A' }}</td>
                                                <td>{{ $journal->intitule }}</td>
                                                <td>{{ $journal->type }}</td>
                                                <td>{{ $journal->compte_de_tresorerie }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('tresorerie.edit', $journal->id) }}" class="btn p-0 border-0 bg-transparent text-success" title="Modifier">
                                                            <i class='bx bx-pencil'></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Aucun journal de trésorerie trouvé.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <!-- Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->

        @include('components.footer')

        <script>
            $(document).ready(function() {
                $('#tresorerieTable').DataTable({
                    pageLength: 5,
                    lengthMenu: [5, 10, 15, 20, 25],
                    language: {
                        search: "Rechercher :",
                        lengthMenu: "Afficher _MENU_ lignes",
                        info: "Affichage de _START_ à _END_ sur _TOTAL_ lignes",
                        paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Précédent" },
                        zeroRecords: "Aucune donnée trouvée",
                        infoEmpty: "Aucune donnée à afficher",
                        infoFiltered: "(filtré depuis _MAX_ lignes totales)"
                    }
                });
            });
        </script>

</body>
</html>
