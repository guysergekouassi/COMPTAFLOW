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
                            <div class="col-sm-6 col-xl-3">
                                <div class="card filtre-tiers" data-type="all" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Nombre de tiers</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ $totalPlanTiers }}</h4>
                                                </div>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="icon-base bx bx-group icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach ($tiersParType as $type => $count)
                                <div class="col-sm-6 col-xl-3">
                                    <div class="card filtre-tiers" data-type="{{ $type }}"
                                        style="cursor: pointer;">

                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="content-left">
                                                    <span class="text-heading"> Tiers : {{ $type . 's' }}</span>
                                                    <div class="d-flex align-items-center my-1">
                                                        <h4 class="mb-0 me-2">{{ $count }}</h4>
                                                    </div>
                                                </div>
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="icon-base bx bx-group icon-lg"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Section table -->
                            <!-- Plan tiers creer avec succes -->
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
                                <!-- En-tête avec bouton Filtrer -->
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Liste des Tiers</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel" aria-expanded="false"
                                            aria-controls="filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate">
                                            Nouveau Tiers
                                        </button>
                                    </div>
                                </div>



                                <!-- Panneau de filtre -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Filtrer par intitulé">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" id="filter-type" class="form-control"
                                                placeholder="Filtrer par type (ex: Client)">
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <button class="btn btn-primary w-100" id="apply-filters">Appliquer</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-secondary w-100"
                                                id="reset-filters">Réinitialiser</button>
                                        </div>
                                    </div>
                                </div>


                                <!-- Table -->

                                <style>
                                    .card.filtre-tiers.selected {
                                        border: 2px solid #696cff;
                                        /* violet par défaut Bootstrap */
                                        box-shadow: 0 0 15px rgba(105, 108, 255, 0.4);
                                        /* belle ombre */
                                    }
                                </style>



                                <script>
                                    $(document).ready(function() {
                                        const table = $('#tiersTable').DataTable({
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

                                        // Action au clic sur une carte
                                        $(".filtre-tiers").on("click", function() {
                                            const type = $(this).data("type");

                                            // Filtrage DataTable
                                            if (type === "all") {
                                                table.column(2).search("").draw();
                                            } else {
                                                table.column(2).search("^" + type + "$", true, false).draw();
                                            }

                                            // Mise à jour des classes visuelles
                                            $(".filtre-tiers").removeClass("selected");
                                            $(this).addClass("selected");
                                        });

                                    });
                                </script>


                                <div class="table-responsive text-nowrap">
                                    <table class="table" id="tiersTable">
                                        <thead>
                                            <tr>
                                                <th>Numéro Tiers</th>
                                                <th>Intitulé</th>
                                                <th>Type</th>
                                                <th>Compte Général</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($tiers as $tier)
                                                <tr>
                                                    <td>
                                                        <i class="icon-base bx bxs-user icon-md text-info me-2"></i>
                                                        {{ $tier->numero_de_tiers }}
                                                    </td>
                                                    <td>{{ $tier->intitule }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-label-secondary">{{ $tier->type_de_tiers }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $tier->compte?->numero_de_compte }} -
                                                        {{ $tier->compte?->intitule }}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-primary"
                                                                title="Modifier" data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $tier->id }}"
                                                                data-numero="{{ $tier->numero_de_tiers }}"
                                                                data-intitule="{{ $tier->intitule }}"
                                                                data-type="{{ $tier->type_de_tiers }}"
                                                                data-compte="{{ $tier->compte_general }}">
                                                                <i class="bx bx-edit-alt fs-5"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmationModalTiers"
                                                                data-id="{{ $tier->id }}"
                                                                data-name="{{ $tier->intitule }}">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger donnees-plan-tiers"
                                                                data-id="{{ $tier->id }}"
                                                                data-intitule="{{ $tier->intitule }}"
                                                                data-numero_de_tiers="{{ $tier->numero_de_tiers }}">
                                                                <i class='bx bx-eye fs-5'></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                {{-- Rien ici, on gère le message en dehors du <table> --}}
                                            @endforelse
                                        </tbody>
                                    </table>

                                    {{-- @if ($tiers->isEmpty())
                                        <div class="alert alert-warning text-center mt-2">
                                            Aucun plan tiers enregistré.
                                        </div>
                                    @endif
                                </div> --}}


                                </div>

                                <!-- Modal Creation Plan Tiers -->
                                <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <form id="planTiersForm" method="POST"
                                            action="{{ route('plan_tiers.store') }}">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalCenterTitle">Créer un compte
                                                        tiers
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Fermer"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row g-3">

                                                        <div class="col-12">
                                                            <label for="type_de_tiers" class="form-label">Type de
                                                                tiers</label>
                                                            <select id="type_de_tiers" name="type_de_tiers"
                                                                class="form-select" required>
                                                                <option value="">-- Sélectionnez un type --
                                                                </option>
                                                                @foreach (['Fournisseur', 'Client', 'Personnel', 'Impots', 'CNPS', 'Associé', 'Divers Tiers'] as $type)
                                                                    <option value="{{ $type }}">
                                                                        {{ $type }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="invalid-feedback">Veuillez sélectionner un type
                                                                de
                                                                tiers.</div>
                                                        </div>



                                                        <div class="col-12">
                                                            <label for="compte_general" class="form-label">Compte
                                                                général associé</label>
                                                            <select id="compte_general" name="compte_general"
                                                                class="form-select" required>

                                                                <option value="">-- Sélectionnez un compte --
                                                                </option>
                                                                @foreach ($comptesGeneraux as $compte)
                                                                    <option value="{{ $compte->id }}"
                                                                        data-numero="{{ $compte->numero_de_compte }}">
                                                                        {{ $compte->numero_de_compte }} -
                                                                        {{ $compte->intitule }}
                                                                    </option>
                                                                @endforeach

                                                            </select>
                                                            <div class="invalid-feedback">Veuillez sélectionner un
                                                                compte
                                                                général.</div>
                                                        </div>


                                                        <div class="col-12">
                                                            <label for="numero_de_tiers" class="form-label">Numéro de
                                                                tiers</label>
                                                            <input type="text" id="numero_de_tiers"
                                                                name="numero_de_tiers" class="form-control"
                                                                placeholder="Entrer le numéro de tiers" required
                                                                readonly />
                                                            <div class="invalid-feedback">Veuillez entrer un numéro de
                                                                tiers
                                                                valide.</div>
                                                        </div>



                                                        <div class="col-12">
                                                            <label for="intitule" class="form-label">Intitulé</label>
                                                            <input type="text" id="intitule" name="intitule"
                                                                class="form-control" placeholder="Entrer l'intitulé"
                                                                required />
                                                            <div class="invalid-feedback">Veuillez entrer un intitulé.
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-label-secondary"
                                                        data-bs-dismiss="modal">Fermer</button>
                                                    <button type="submit"
                                                        class="btn btn-primary">Enregistrer</button>
                                                </div>
                                            </div>

                                            <script>
                                                const getDernierNumeroUrl = "{{ url('/plan_tiers') }}"; // ou route() avec JS dynamique si besoin
                                            </script>


                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    const correspondances =
                                                    @json($correspondances); // format : { "Client": [{id, numero, intitule}, ...], ... }
                                                    const compteGeneralSelect = document.getElementById('compte_general');
                                                    const typeTiers = document.getElementById('type_de_tiers');
                                                    const numeroTiers = document.getElementById('numero_de_tiers');

                                                    // Générer un numéro de tiers
                                                    const genererNumero = (numeroCompte) => {
                                                        const racine = numeroCompte.replace(/0+$/, '');
                                                        fetch(`${getDernierNumeroUrl}/${racine}`)
                                                            .then(response => response.json())
                                                            .then(data => {
                                                                if (data.numero) {
                                                                    numeroTiers.value = data.numero;
                                                                    console.log("[INFO] Nouveau numéro généré :", data.numero);
                                                                } else {
                                                                    numeroTiers.value = '';
                                                                    console.warn("[WARN] Aucun numéro généré.");
                                                                }
                                                            })
                                                            .catch(error => {
                                                                numeroTiers.value = '';
                                                                console.error("[ERREUR] lors de la récupération du numéro :", error);
                                                            });
                                                    };

                                                    // Quand on change le type de tiers
                                                    typeTiers.addEventListener('change', function() {
                                                        const selectedType = this.value;
                                                        const comptes = correspondances[selectedType] || [];

                                                        // Réinitialiser les options du select compte général
                                                        compteGeneralSelect.innerHTML = `<option value="">-- Sélectionnez un compte --</option>`;

                                                        comptes.forEach(compte => {
                                                            const option = document.createElement('option');
                                                            option.value = compte.id;
                                                            option.setAttribute('data-numero', compte.numero);
                                                            option.textContent = `${compte.numero} - ${compte.intitule}`;
                                                            compteGeneralSelect.appendChild(option);
                                                        });

                                                        // Si au moins un compte trouvé, le sélectionner automatiquement
                                                        if (comptes.length > 0) {
                                                            compteGeneralSelect.selectedIndex = 1;
                                                            // console.log(comptes[0].numero)
                                                            genererNumero(comptes[0].numero);
                                                        } else {
                                                            numeroTiers.value = '';
                                                            console.warn(`[INFO] Aucun compte associé au type "${selectedType}".`);
                                                        }
                                                    });

                                                    // Quand on change le compte général manuellement
                                                    compteGeneralSelect.addEventListener('change', function() {
                                                        const selectedOption = this.options[this.selectedIndex];
                                                        const numeroCompte = selectedOption.getAttribute('data-numero');

                                                        if (numeroCompte) {
                                                            // console.log(numeroCompte)
                                                            genererNumero(numeroCompte);
                                                        } else {
                                                            numeroTiers.value = '';
                                                        }
                                                    });
                                                });
                                            </script>



                                        </form>
                                    </div>
                                </div>

                                <!-- Modal plan update-->
                                <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <form method="POST"
                                            action="{{ route('plan_tiers.update', ['id' => '__ID__']) }}"
                                            id="updateTiersForm">

                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="update_id" name="id">

                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modifier le plan tiers</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Fermer"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="row g-3">

                                                        <div class="col-12">
                                                            <label class="form-label">Type de tiers</label>
                                                            <select id="update_type_de_tiers" name="type_de_tiers"
                                                                class="form-select" required>
                                                                <option value="">-- Sélectionnez un type --
                                                                </option>
                                                                @foreach (['Fournisseur', 'Client', 'Personnel', 'Impots', 'CNPS', 'Associé', 'Divers Tiers'] as $type)
                                                                    <option value="{{ $type }}">
                                                                        {{ $type }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-12">
                                                            <label class="form-label">Compte général</label>
                                                            <select id="update_compte" name="compte_general"
                                                                class="form-select" required>
                                                                <option value="">-- Sélectionnez un compte --
                                                                </option>
                                                                @foreach ($comptesGeneraux as $compte)
                                                                    <option value="{{ $compte->id }}">
                                                                        {{ $compte->numero_de_compte }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Numéro de tiers</label>
                                                            <input type="text" id="update_numero"
                                                                name="numero_de_tiers" class="form-control" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Intitulé</label>
                                                            <input type="text" id="update_intitule"
                                                                name="intitule" class="form-control" required>
                                                        </div>


                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-label-secondary"
                                                        data-bs-dismiss="modal">
                                                        Fermer
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">Mettre à
                                                        jour</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>



                                <!-- Modal de confirmation de suppression -->
                                <div class="modal fade" id="deleteConfirmationModalTiers" tabindex="-1"
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
                                                    Êtes-vous sûr de vouloir supprimer ce plan de tiers ? Cette action
                                                    est
                                                    <strong>irréversible</strong>.
                                                </p>
                                                <p class="fw-bold text-danger mt-2" id="planToDeleteNameTiers"></p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Annuler</button>

                                                <form method="POST" id="deletePlanFormTiers"
                                                    style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        id="confirmDeleteBtn">Supprimer</button>
                                                </form>
                                            </div>
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
                const plan_tiers_ecrituresSaisisUrl = "{{ route('plan_tiers_ecritures') }}";

                const plan_tiersUpdateBaseUrl = "{{ route('plan_tiers.update', ['id' => '__ID__']) }}";
                const plan_tiersDeleteUrl = "{{ route('plan_tiers.destroy', ['id' => '__ID__']) }}";
            </script>

            @include('components.footer')
            <script src="{{ asset('js/plan_tiers.js') }}"></script>



</body>

</html>
