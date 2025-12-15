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

                            <!-- Page Header -->
                            <div class="text-center mb-5">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; border-radius: 20px; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);">
                                    <i class="bx bx-money text-white" style="font-size: 32px;"></i>
                                </div>
                                <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Gestion de Trésorerie</h1>
                                <p class="text-muted mb-0" style="font-size: 1.1rem;"><i class="bx bx-info-circle me-1"></i>Gérez vos flux de trésorerie et configurations</p>
                            </div>

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


                            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-bottom: 2px solid #e7e9ed; padding: 1.5rem;">
                                    <h5 class="mb-0" style="font-weight: 700; color: #566a7f; font-size: 1.25rem;"><i class="bx bx-list-ul me-2"></i>Gestion des flux de trésorerie</h5>
                                    <div>
                                        <button class="btn btn-sm me-2" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(79, 172, 254, 0.3);">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);">
                                            <i class="bx bx-plus me-1"></i> Ajouter
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
                                    <table class="table table-hover align-middle" id="FluxTable" style="border-radius: 8px; overflow: hidden;">
                                        <thead style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                            <tr>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Categorie</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Nature</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">De</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">A</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; text-align: center;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($flux_types as $flux_type)
                                                <tr>
                                                    <td style="padding: 1rem; font-weight: 600; color: #667eea;">{{ $flux_type->categorie }}</td>
                                                    <td style="padding: 1rem; color: #566a7f;">{{ $flux_type->nature }}</td>
                                                    <td style="padding: 1rem; color: #566a7f;">{{ $flux_type->PlanComptable1->numero_de_compte ?? 'N/A' }}
                                                    </td>

                                                    <td style="padding: 1rem; color: #566a7f;">{{ $flux_type->PlanComptable2->numero_de_compte ?? 'N/A' }}
                                                    </td>
                                                    <td style="padding: 1rem; text-align: center;">
                                                        <div class="d-flex gap-2 justify-content-center">

                                                            <!-- Bouton Modifier -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)'"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $flux_type->id }}"
                                                                data-categorie="{{ $flux_type->categorie }}"
                                                                data-nature="{{ $flux_type->nature }}"
                                                                data-plan-comptable1="{{ optional($flux_type->PlanComptable1)->id }}"
                                                                data-plan-comptable2="{{ optional($flux_type->PlanComptable2)->id }}">
                                                                <i class="bx bx-edit-alt" style="font-size: 16px;"></i>
                                                            </button>

                                                            <!-- Bouton Supprimer -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: white; border: none; border-radius: 8px; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(255, 154, 158, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(255, 154, 158, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(255, 154, 158, 0.3)'"
                                                                data-bs-toggle="modal" data-bs-target="#modalDeleteFlux"
                                                                data-id="{{ $flux_type->id }}"
                                                                data-label="{{ $flux_type->categorie }}">
                                                                <i class="bx bx-trash" style="font-size: 16px;"></i>
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
                                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                                <h5 class="modal-title text-white" id="modalCenterTitle">
                                                    <i class="bx bx-plus-circle me-2"></i>Créer un Type de Flux
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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
                                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                                <h5 class="modal-title text-white"><i class="bx bx-edit-alt me-2"></i>Modifier le Type de Flux</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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
