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
                                    <h5 class="mb-0">Gestion des flux de tresorerie</h5>
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
                                            pageLength: 10,
                                            lengthMenu: [10, 15, 20, 25],
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
                                                <th>De </th>
                                                <th>A </th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($flux_types as $flux_type)
                                                <tr>
                                                    <td>{{ $flux_type->categorie }}</td>
                                                    <td>{{ $flux_type->nature }}</td>
                                                    <td>{{ $flux_type->PlanComptable1->numero_de_compte ?? 'N/A' }}
                                                    </td>

                                                    <td>{{ $flux_type->PlanComptable2->numero_de_compte ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">

                                                            <!-- Bouton Modifier -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $flux_type->id }}"
                                                                data-categorie="{{ $flux_type->categorie }}"
                                                                data-nature="{{ $flux_type->nature }}"
                                                                data-plan-comptable1="{{ $flux_type->PlanComptable1->id }}"
                                                                data-plan-comptable2="{{ $flux_type->PlanComptable2->id }}">
                                                                <i class="bx bx-edit-alt fs-5"></i>
                                                            </button>

                                                            <!-- Bouton Supprimer -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                data-bs-toggle="modal" data-bs-target="#modalDeleteFlux"
                                                                data-id="{{ $flux_type->id }}"
                                                                data-label="{{ $flux_type->categorie }}">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>

                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>


                            </div>


                            {{-- Create type de flux --}}
                            <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <form id="formCreateTresorerie" method="POST"
                                        action="{{ route('gestion_tresorerie.store') }}">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCenterTitle">
                                                    Créer un Type de Flux
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">


                                                    <div class="col-12">
                                                        <label for="categorie" class="form-label">Categorie</label>
                                                        <input type="text" id="categorie" name="categorie"
                                                            class="form-control"
                                                            placeholder="Ex : Opérationnel, Investissement..." />
                                                        <div class="text-danger small mt-1" id=""></div>
                                                    </div>

                                                    <div class="col-12">
                                                        <label for="nature" class="form-label">Nature</label>
                                                        <input type="text" id="nature" name="nature"
                                                            class="form-control"
                                                            placeholder="Ex : Marchandise, Matériel..." />
                                                        <div class="text-danger small mt-1" id=""></div>
                                                    </div>

                                                </div>
                                                <div class="row g-3 align-items-end mt-2">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Comptes généraux</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label for="plan_comptable_id_1" class="form-label">Du</label>
                                                        <select id="plan_comptable_id_1" name="plan_comptable_id_1"
                                                            class="selectpicker w-100" data-width="auto"
                                                            data-live-search="true" required>
                                                            <option value="">-- Sélectionnez un compte --
                                                            </option>
                                                            @foreach ($PlanComptable as $plan)
                                                                <option value="{{ $plan->id }}">
                                                                    {{ $plan->numero_de_compte }} -
                                                                    {{ $plan->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Veuillez sélectionner un compte.
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label for="plan_comptable_id_2" class="form-label">Au</label>
                                                        <select id="plan_comptable_id_2" name="plan_comptable_id_2"
                                                            class="selectpicker w-100" data-width="auto"
                                                            data-live-search="true" required>
                                                            <option value="">-- Sélectionnez un compte --
                                                            </option>
                                                            @foreach ($PlanComptable as $plan)
                                                                <option value="{{ $plan->id }}">
                                                                    {{ $plan->numero_de_compte }} -
                                                                    {{ $plan->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback" id="compte2-error">Veuillez
                                                            sélectionner un compte.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">
                                                    Fermer
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    Enregistrer
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            {{-- Modal Update Type de Flux --}}
                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <form id="formUpdateFlux" method="POST"
                                        action="{{ route('gestion_tresorerie.update') }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" id="update_id" name="id">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier le Type de Flux</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label for="update_categorie"
                                                            class="form-label">Catégorie</label>
                                                        <input type="text" id="update_categorie" name="categorie"
                                                            class="form-control">
                                                    </div>
                                                    <div class="col-12">
                                                        <label for="update_nature" class="form-label">Nature</label>
                                                        <input type="text" id="update_nature" name="nature"
                                                            class="form-control">
                                                    </div>
                                                </div>

                                                <div class="row g-3 align-items-end mt-2">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Comptes généraux</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label for="update_plan_comptable_id_1"
                                                            class="form-label">Du</label>
                                                        <select id="update_plan_comptable_id_1"
                                                            name="plan_comptable_id_1" class="selectpicker w-100"
                                                            data-width="auto" data-live-search="true" required>
                                                            <option value="">
                                                            </option>
                                                            @foreach ($PlanComptable as $plan)
                                                                <option value="{{ $plan->id }}">
                                                                    {{ $plan->numero_de_compte }} -
                                                                    {{ $plan->intitule }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Veuillez sélectionner un compte.
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label for="update_plan_comptable_id_2"
                                                            class="form-label">Au</label>
                                                        <select id="update_plan_comptable_id_2"
                                                            name="plan_comptable_id_2" class="selectpicker w-100"
                                                            data-width="auto" data-live-search="true" required>
                                                            <option value="">
                                                            </option>
                                                            @foreach ($PlanComptable as $plan)
                                                                <option value="{{ $plan->id }}">
                                                                    {{ $plan->numero_de_compte }} -
                                                                    {{ $plan->intitule }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">Veuillez sélectionner un compte.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            {{-- Modal de suppression --}}
                            <div class="modal fade" id="modalDeleteFlux" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-top" role="document">
                                    <form id="formDeleteFlux" method="POST"
                                        action="{{ route('gestion_tresorerie.destroy') }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" id="delete_id" name="id">

                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Confirmer la suppression</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Êtes-vous sûr de vouloir supprimer le flux <strong
                                                        id="delete_label"></strong> ?</p>
                                                <p class="text-muted mb-0">Cette action est irréversible.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-danger">Supprimer</button>
                                            </div>
                                        </div>
                                    </form>
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

        <script src="{{ asset('js/gestion_tresorerie.js') }}"></script>
</body>

</html>
