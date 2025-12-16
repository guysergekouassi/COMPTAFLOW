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
                            <!-- Total plans in company -->

                            <style>
                                .card.filter-active {
                                    border: 2px solid #696cff;
                                    box-shadow: 0 0 10px rgba(105, 108, 255, 0.4);
                                    transition: all 0.3s ease;
                                }
                            </style>
                            <div class="col-sm-6 col-xl-3" id="filter-all">
                                <div class="card filter-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Total des comptes généraux</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $totalPlans }}</h4>
                                                </div>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="icon-base bx bx-book-content icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Plans created by COMPANY -->
                            <div class="col-sm-6 col-xl-3" id="filter-manuel">
                                <div class="card filter-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Compte généraux créés</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $plansByUser }}</h4>
                                                </div>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="icon-base bx bx-book-content icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Plans SYSCOHADA -->
                            <div class="col-sm-6 col-xl-3" id="filter-auto">
                                <div class="card filter-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">comptes SYSCOHADA</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $plansSys }}</h4>
                                                </div>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="icon-base bx bx-book-content icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Section table -->
                            <!-- Plan comptable creer avec succes -->
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
                                    <h5 class="mb-0">Compte général</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>


                                        @if ($hasAutoStrategy == false)
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#Plan_defaut">
                                                Charger le plan comptable par défaut
                                            </button>
                                        @endif


                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate">
                                            Ajouter un nouveau Compte
                                        </button>

                                    </div>

                                </div>

                                <!-- Filtre personnalisé -->
                                <!-- Filtre personnalisé compact -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <input type="text" id="filter-numero" class="form-control"
                                                placeholder="Numéro de compte">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Intitulé">
                                        </div>
                                        <div class="col-md-2">
                                            <select id="filter-adding_strategy" class="form-control">
                                                <option value="">Méthode</option>
                                                <option value="auto">Auto</option>
                                                <option value="manuel">Manuel</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100" id="apply-filters">Filtrer</button>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-secondary w-100"
                                                id="reset-filters">Réinitialiser</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Table -->
                                <script>
                                    $(document).ready(function() {
                                        const table = $('#planComptableTable').DataTable({
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

                                        function activateCard(cardId) {
                                            $('.filter-card').removeClass('filter-active');
                                            $(`${cardId} .filter-card`).addClass('filter-active');
                                        }

                                        $('#filter-all').on('click', function() {
                                            table.column(2).search('').draw(); // ✅ efface aussi le filtre colonne 2
                                            activateCard('#filter-all');
                                        });


                                        $('#filter-manuel').on('click', function() {
                                            table.column(2).search('manuel').draw();
                                            activateCard('#filter-manuel');
                                        });

                                        $('#filter-auto').on('click', function() {
                                            table.column(2).search('auto').draw();
                                            activateCard('#filter-auto');
                                        });

                                        // Activer le filtre "Tous" au démarrage
                                        activateCard('#filter-all');
                                    });
                                </script>



                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover table-striped table-bordered align-middle"
                                        id="planComptableTable">

                                        <thead>
                                            <tr>
                                                <th>Numéro de compte</th>
                                                <th>Intitulé</th>
                                                <th>Methode d'ajout</th>

                                                {{-- <th>Poste</th>
                                                <th>Extrait du compte</th>
                                                <th>Traitement analytique</th> --}}
                                                <!-- <th>Classe</th> -->

                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($plans as $plan)
                                                <tr>
                                                    <td>{{ $plan->numero_de_compte }}</td>
                                                    <td>{{ $plan->intitule }}</td>
                                                    <td>{{ $plan->adding_strategy }}</td>

                                                    <!-- <td>{{ $plan->classe }}</td> -->
                                                    <td>
                                                        <div class="d-flex gap-2">

                                                            {{-- Vérifie si l'utilisateur est un ADMIN avant d'afficher les boutons --}}
                                                            @if (auth()->check() && auth()->user()->role === 'admin')
                                                            <!-- Bouton Edit avec modal -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $plan->id }}"
                                                                data-numero_de_compte="{{ $plan->numero_de_compte }}"
                                                                data-intitule="{{ $plan->intitule }}"
                                                                data-type_de_compte="{{ $plan->type_de_compte }}"
                                                                title="Edit">
                                                                <i class="bx bx-edit-alt fs-5"></i>
                                                            </button>


                                                            <!-- Bouton Delete -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmationModal"
                                                                data-id="{{ $plan->id }}"
                                                                data-intitule="{{ $plan->intitule }}">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>
                                                            {{-- FIN de la vérification ADMIN --}}
                                                            @endif
                                                            <!-- Bouton envoi -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger donnees-plan-comptable"
                                                                data-id="{{ $plan->id }}"
                                                                data-intitule="{{ $plan->intitule }}"
                                                                data-numero_de_compte="{{ $plan->numero_de_compte }}">
                                                                <i class='bx  bx-eye fs-5'></i>
                                                            </button>

                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                {{-- Rien ici, on gère le message en dehors du <table> --}}
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>

                            <!-- Modal Creation plan-->
                            <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('plan_comptable.store') }}" method="POST"
                                            id="planComptableForm">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Créer un compte général</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>

                                            <div class="modal-body">



                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <label for="numero_de_compte" class="form-label">Numéro de
                                                            compte</label>
                                                        <input type="text" class="form-control"
                                                            id="numero_de_compte" name="numero_de_compte"
                                                            maxlength="8" required>
                                                        <span id="numero_compte_feedback"
                                                            class="text-danger small mt-1 d-block"></span>
                                                    </div>


                                                    <div class="col-6">
                                                        <label for="intitule" class="form-label">Intitulé</label>
                                                        <input type="text" class="form-control" id="intitule"
                                                            name="intitule" required>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Creation plan update-->
                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form id="updatePlanForm" method="POST"
                                            action="{{ route('plan_comptable.update', ['id' => '__ID__']) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier un plan comptable</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>

                                            <div class="modal-body">
                                                <input type="hidden" id="update_planId" name="id" />
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <label for="update_numero_de_compte" class="form-label">Numéro
                                                            de compte</label>
                                                        <input type="text" class="form-control"
                                                            id="update_numero_de_compte" name="numero_de_compte"
                                                            required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="update_intitule"
                                                            class="form-label">Intitulé</label>
                                                        <input type="text" class="form-control"
                                                            id="update_intitule" name="intitule" required>
                                                    </div>


                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>




                            <!-- Modal Confirmation de suppression -->
                            <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
                                aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header text-white justify-content-center">
                                            <h5 class="modal-title" id="deleteModalLabel">
                                                <i class="bx bx-error-circle me-2"></i>Confirmer la suppression
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white"
                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <p class="mb-0">
                                                Êtes-vous sûr de vouloir supprimer ce plan comptable ? Cette action est
                                                <strong>irréversible</strong>.
                                            </p>
                                            <p class="fw-bold text-danger mt-2" id="planToDeleteName"></p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Annuler</button>

                                            <form method="POST" id="deletePlanForm" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"
                                                    id="confirmDeleteBtn">Supprimer</button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Confirmation de plan comptable par defauts -->

                            <div class="modal fade" id="Plan_defaut" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form id="Plandefaut" method="POST"
                                            action="{{ route('plan_comptable.defaut') }}">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Voulez-vous charger le plan comptable par défaut ?</p>
                                                <!-- Champ caché qui contient la valeur 'true' -->
                                                <input type="hidden" name="use_default" value="true">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Confirmer</button>
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
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

        <script>
            const plan_comptable_ecrituresSaisisUrl = "{{ route('plan_comptable_ecritures') }}";
            const planComptableDefautUrl = "{{ route('plan_comptable.defaut') }}";
            const verifierNumeroUrl = "{{ route('verifierNumeroCompte') }}";
            const planComptableUpdateBaseUrl = "{{ route('plan_comptable.update', ['id' => '__ID__']) }}";
            const plan_comptableDeleteUrl = "{{ route('plan_comptable.destroy', ['id' => '__ID__']) }}";
        </script>



        <script>
            $(document).ready(function() {
                $('.selectpicker').selectpicker();
            });
        </script>

        @include('components.footer')
        <script src="{{ asset('js/plan_comptable.js') }}"></script>

</body>

</html>
