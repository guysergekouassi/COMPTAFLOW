<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

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
                        <div class="row g-6 mb-6">

                            <!-- Section table -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fermer"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fermer"></button>
                                </div>
                            @endif


                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Flux de tresorerie</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate">
                                            Ajouter
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="text" id="filter-annee" class="form-control"
                                                placeholder="Filtrer par année..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-mois" class="form-control"
                                                placeholder="Filtrer par mois..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-code" class="form-control"
                                                placeholder="Filtrer par code..." />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Filtrer par intitulé..." />
                                        </div>
                                        <div class="col-md-6 pt-2">
                                            <button class="btn btn-primary w-100" id="apply-filters">Appliquer les
                                                filtres</button>
                                        </div>
                                        <div class="col-md-6 pt-2">
                                            <button class="btn btn-outline-secondary w-100"
                                                id="reset-filters">Réinitialiser</button>
                                        </div>

                                    </div>
                                </div>


                                {{-- table  --}}
                                <script>
                                    $(document).ready(function() {
                                        $('#FluxTable').DataTable({
                                            pageLength: 5,
                                            lengthMenu: [5, 10, 15, 20, 25],
                                            language: {
                                                search: "Rechercher :",
                                                lengthMenu: "Afficher _MENU_ lignes",
                                                info: "Affichage de _START_ à _END_ sur _TOTAL_ lignes",
                                                paginate: {
                                                    first: "Premier",
                                                    last: "Dernier",
                                                    next: "Suivant",
                                                    previous: "Précédent"
                                                },
                                                zeroRecords: "Aucune donnée trouvée",
                                                infoEmpty: "Aucune donnée à afficher",
                                                infoFiltered: "(filtré depuis _MAX_ lignes totales)"
                                            }
                                        });
                                    });
                                </script>

                                <div class="table-responsive text-nowrap">
                                    <table class="table" id="FluxTable">
                                        <thead>
                                            <tr>
                                                <th>Categorie</th>
                                                <th>Nature</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
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

        <!-- Core JS -->

        @include('components.footer')

        <script src="{{ asset('js/flux_tresorerie.js') }}"></script>
</body>

</html>
