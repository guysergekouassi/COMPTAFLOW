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
                        <!-- Page Header -->
                        <div class="text-center mb-5">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; border-radius: 20px; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);">
                                <i class="bx bx-book-content text-white" style="font-size: 32px;"></i>
                            </div>
                            <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Plan Comptable</h1>
                            <p class="text-muted mb-0" style="font-size: 1.1rem;"><i class="bx bx-info-circle me-1"></i>Gérez vos comptes généraux et le référentiel SYSCOHADA</p>
                        </div>

                        <div class="row g-6 mb-6">
                            <!-- Total plans in company -->

                            <style>
                                .card.filter-active {
                                    border: 2px solid #696cff;
                                    box-shadow: 0 0 10px rgba(105, 108, 255, 0.4);
                                    transition: all 0.3s ease;
                                }
                                
                                .stats-card {
                                    transition: all 0.3s ease;
                                    border: none;
                                    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                                    cursor: pointer;
                                }
                                
                                .stats-card:hover {
                                    transform: translateY(-5px);
                                    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
                                }
                                
                                .stats-card .avatar {
                                    width: 56px;
                                    height: 56px;
                                }
                                
                                .stats-card .avatar-initial {
                                    font-size: 28px;
                                }
                                
                                .stats-card h4 {
                                    font-size: 2rem;
                                    font-weight: 700;
                                }
                                
                                .stats-card .text-heading {
                                    font-size: 0.875rem;
                                    font-weight: 600;
                                    color: #697a8d;
                                    text-transform: uppercase;
                                    letter-spacing: 0.5px;
                                }
                            </style>
                            <div class="col-sm-6 col-xl-4" id="filter-all">
                                <div class="card filter-card stats-card">
                                    <div class="card-body" style="padding: 1.5rem;">
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
                            <div class="col-sm-6 col-xl-4" id="filter-manuel">
                                <div class="card filter-card stats-card">
                                    <div class="card-body" style="padding: 1.5rem;">
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
                            <div class="col-sm-6 col-xl-4" id="filter-auto">
                                <div class="card filter-card stats-card">
                                    <div class="card-body" style="padding: 1.5rem;">
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

                            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-bottom: 2px solid #e7e9ed; padding: 1.5rem;">
                                    <h5 class="mb-0" style="font-weight: 700; color: #566a7f; font-size: 1.25rem;"><i class="bx bx-list-ul me-2"></i>Compte général</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel" style="border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>


                                        @if ($hasAutoStrategy == false)
                                            <button type="button" class="btn btn-info btn-sm me-2" data-bs-toggle="modal"
                                                data-bs-target="#Plan_defaut" style="border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                                                <i class="bx bx-download me-1"></i> Charger le plan par défaut
                                            </button>
                                        @endif


                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(105, 108, 255, 0.3); transition: all 0.3s;">
                                            <i class="bx bx-plus-circle me-1"></i> Ajouter un compte
                                        </button>

                                    </div>

                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2 pb-3" id="filterPanel" style="background: #f8f9fa; border-radius: 8px; margin: 0 1rem 1rem 1rem;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-hash"></i> Numéro</label>
                                            <input type="text" id="filter-numero" class="form-control"
                                                placeholder="Numéro de compte" style="border-radius: 8px;">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-text"></i> Intitulé</label>
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Intitulé" style="border-radius: 8px;">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-cog"></i> Méthode</label>
                                            <select id="filter-adding_strategy" class="form-control" style="border-radius: 8px;">
                                                <option value="">Toutes</option>
                                                <option value="auto">Auto</option>
                                                <option value="manuel">Manuel</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100" id="apply-filters" style="border-radius: 8px; font-weight: 600;"><i class="bx bx-search-alt me-1"></i>Filtrer</button>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-secondary w-100"
                                                id="reset-filters" style="border-radius: 8px; font-weight: 600;"><i class="bx bx-reset me-1"></i>Réinitialiser</button>
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



                                <div class="table-responsive text-nowrap" style="padding: 1.5rem;">
                                    <table class="table table-hover align-middle"
                                        id="planComptableTable" style="border-radius: 8px; overflow: hidden;">

                                        <thead style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                            <tr>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-hash me-1"></i>Numéro de compte</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-text me-1"></i>Intitulé</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-cog me-1"></i>Méthode</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; text-align: center;"><i class="bx bx-slider me-1"></i>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($plans as $plan)
                                                <tr style="transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor=''">
                                                    <td style="padding: 1rem; font-weight: 600; color: #667eea;">{{ $plan->numero_de_compte }}</td>
                                                    <td style="padding: 1rem; color: #566a7f;">{{ $plan->intitule }}</td>
                                                    <td style="padding: 1rem;">
                                                        @if($plan->adding_strategy === 'auto')
                                                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">AUTO</span>
                                                        @else
                                                            <span class="badge" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem;">MANUEL</span>
                                                        @endif
                                                    </td>

                                                    <td style="padding: 1rem;">
                                                        <div class="d-flex gap-3 justify-content-center">

                                                            {{-- Vérifie si l'utilisateur est un ADMIN avant d'afficher les boutons --}}
                                                            @if (auth()->check() && auth()->user()->role === 'admin')
                                                            <!-- Bouton Edit avec modal -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)'"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $plan->id }}"
                                                                data-numero_de_compte="{{ $plan->numero_de_compte }}"
                                                                data-intitule="{{ $plan->intitule }}"
                                                                data-type_de_compte="{{ $plan->type_de_compte }}"
                                                                title="Modifier">
                                                                <i class="bx bx-edit-alt" style="font-size: 18px;"></i>
                                                            </button>


                                                            <!-- Bouton Delete -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(245, 87, 108, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(245, 87, 108, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(245, 87, 108, 0.3)'"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmationModal"
                                                                data-id="{{ $plan->id }}"
                                                                data-intitule="{{ $plan->intitule }}"
                                                                title="Supprimer">
                                                                <i class="bx bx-trash" style="font-size: 18px;"></i>
                                                            </button>
                                                            {{-- FIN de la vérification ADMIN --}}
                                                            @endif
                                                            <!-- Bouton Voir -->
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon donnees-plan-comptable"
                                                                style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(79, 172, 254, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(79, 172, 254, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(79, 172, 254, 0.3)'"
                                                                data-id="{{ $plan->id }}"
                                                                data-intitule="{{ $plan->intitule }}"
                                                                data-numero_de_compte="{{ $plan->numero_de_compte }}"
                                                                title="Voir les détails">
                                                                <i class='bx bx-show' style="font-size: 18px;"></i>
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
                                            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                                                <h5 class="modal-title text-white" style="font-weight: 700;"><i class="bx bx-plus-circle me-2"></i>Créer un compte général</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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

                                            <div class="modal-footer" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                                <button type="button" class="btn btn-label-secondary" style="border-radius: 8px;"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(245, 87, 108, 0.3);">Enregistrer</button>
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
                                            <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-bottom: none;">
                                                <h5 class="modal-title text-white" style="font-weight: 700;"><i class="bx bx-edit-alt me-2"></i>Modifier un plan comptable</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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
                                        <div class="modal-header text-white justify-content-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-bottom: none;">
                                            <h5 class="modal-title" id="deleteModalLabel" style="font-weight: 700;">
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
                                        <div class="modal-footer justify-content-center" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                            <button type="button" class="btn btn-secondary" style="border-radius: 8px;"
                                                data-bs-dismiss="modal">Annuler</button>

                                            <form method="POST" id="deletePlanForm" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(234, 84, 85, 0.3);"
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
