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
                                <i class="bx bx-group text-white" style="font-size: 32px;"></i>
                            </div>
                            <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Plan Tiers</h1>
                            <p class="text-muted mb-0" style="font-size: 1.1rem;"><i class="bx bx-info-circle me-1"></i>Gérez vos comptes tiers (Clients, Fournisseurs, Personnel, etc.)</p>
                        </div>

                        <div class="row g-6 mb-6">
                            <style>
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
                            <div class="col-sm-6 col-xl-4">
                                <div class="card filtre-tiers stats-card" data-type="all">
                                    <div class="card-body" style="padding: 1.5rem;">
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
                                <div class="col-sm-6 col-xl-4">
                                    <div class="card filtre-tiers stats-card" data-type="{{ $type }}">
                                        <div class="card-body" style="padding: 1.5rem;">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="content-left">
                                                    <span class="text-heading">{{ $type }}s</span>
                                                    <div class="d-flex align-items-center my-1">
                                                        <h4 class="mb-0 me-2">{{ $count }}</h4>
                                                    </div>
                                                </div>
                                                <div class="avatar">
                                                    <span class="avatar-initial rounded bg-label-success">
                                                        <i class="icon-base bx bx-user icon-lg"></i>
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
                            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
                                <!-- En-tête avec bouton Filtrer -->
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-bottom: 2px solid #e7e9ed; padding: 1.5rem;">
                                    <h5 class="mb-0" style="font-weight: 700; color: #566a7f; font-size: 1.25rem;"><i class="bx bx-list-ul me-2"></i>Liste des Tiers</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel" aria-expanded="false"
                                            aria-controls="filterPanel" style="border-radius: 8px; font-weight: 600; transition: all 0.3s;">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(105, 108, 255, 0.3); transition: all 0.3s;">
                                            <i class="bx bx-plus-circle me-1"></i> Nouveau Tiers
                                        </button>
                                    </div>
                                </div>



                                <!-- Panneau de filtre -->
                                <div class="collapse px-3 pt-2 pb-3" id="filterPanel" style="background: #f8f9fa; border-radius: 8px; margin: 0 1rem 1rem 1rem;">
                                    <div class="row g-2">
                                        <div class="col-md-5">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-text"></i> Intitulé</label>
                                            <input type="text" id="filter-intitule" class="form-control"
                                                placeholder="Filtrer par intitulé" style="border-radius: 8px;">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label text-muted small mb-1"><i class="bx bx-category"></i> Type</label>
                                            <input type="text" id="filter-type" class="form-control"
                                                placeholder="Filtrer par type (ex: Client)" style="border-radius: 8px;">
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label text-muted small mb-1">&nbsp;</label>
                                            <button class="btn btn-primary w-100" id="apply-filters" style="border-radius: 8px; font-weight: 600;"><i class="bx bx-search-alt"></i></button>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label text-muted small mb-1">&nbsp;</label>
                                            <button class="btn btn-secondary w-100"
                                                id="reset-filters" style="border-radius: 8px; font-weight: 600;"><i class="bx bx-reset"></i></button>
                                        </div>
                                    </div>
                                </div>


                                <!-- Table -->

                                <style>
                                    .card.filtre-tiers.selected {
                                        border: 2px solid #696cff;
                                        box-shadow: 0 0 15px rgba(105, 108, 255, 0.4);
                                        transition: all 0.3s ease;
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


                                <div class="table-responsive text-nowrap" style="padding: 1.5rem;">
                                    <table class="table table-hover align-middle" id="tiersTable" style="border-radius: 8px; overflow: hidden;">
                                        <thead style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                            <tr>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-hash me-1"></i>Numéro Tiers</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-text me-1"></i>Intitulé</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-category me-1"></i>Type</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-book me-1"></i>Compte Général</th>
                                                <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; text-align: center;"><i class="bx bx-slider me-1"></i>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($tiers as $tier)
                                                <tr style="transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor=''">
                                                    <td style="padding: 1rem; font-weight: 600; color: #667eea;">
                                                        <i class="icon-base bx bxs-user icon-md text-info me-2"></i>
                                                        {{ $tier->numero_de_tiers }}
                                                    </td>
                                                    <td style="padding: 1rem; color: #566a7f;">{{ $tier->intitule }}</td>
                                                    <td style="padding: 1rem;">
                                                        <span class="badge" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 600; font-size: 0.75rem; color: white;">{{ $tier->type_de_tiers }}</span>
                                                    </td>
                                                    <td style="padding: 1rem; color: #566a7f;">
                                                        {{ $tier->compte?->numero_de_compte }} -
                                                        {{ $tier->compte?->intitule }}
                                                    </td>
                                                    <td style="padding: 1rem;">
                                                        <div class="d-flex gap-3 justify-content-center">
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)'"
                                                                title="Modifier" data-bs-toggle="modal"
                                                                data-bs-target="#modalCenterUpdate"
                                                                data-id="{{ $tier->id }}"
                                                                data-numero="{{ $tier->numero_de_tiers }}"
                                                                data-intitule="{{ $tier->intitule }}"
                                                                data-type="{{ $tier->type_de_tiers }}"
                                                                data-compte="{{ $tier->compte_general }}">
                                                                <i class="bx bx-edit-alt" style="font-size: 18px;"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="btn btn-sm btn-icon"
                                                                style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(245, 87, 108, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(245, 87, 108, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(245, 87, 108, 0.3)'"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmationModalTiers"
                                                                data-id="{{ $tier->id }}"
                                                                data-name="{{ $tier->intitule }}"
                                                                title="Supprimer">
                                                                <i class="bx bx-trash" style="font-size: 18px;"></i>
                                                            </button>

                                                            <button type="button"
                                                                class="btn btn-sm btn-icon donnees-plan-tiers"
                                                                style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(79, 172, 254, 0.3);"
                                                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(79, 172, 254, 0.4)'"
                                                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(79, 172, 254, 0.3)'"
                                                                data-id="{{ $tier->id }}"
                                                                data-intitule="{{ $tier->intitule }}"
                                                                data-numero_de_tiers="{{ $tier->numero_de_tiers }}"
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
                                                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                                                    <h5 class="modal-title text-white" style="font-weight: 700;" id="modalCenterTitle"><i class="bx bx-plus-circle me-2"></i>Créer un compte tiers
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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

                                                <div class="modal-footer" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                                    <button type="button" class="btn btn-label-secondary" style="border-radius: 8px;"
                                                        data-bs-dismiss="modal">Fermer</button>
                                                    <button type="submit"
                                                        class="btn btn-primary" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(105, 108, 255, 0.3);">Enregistrer</button>
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
                                                <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-bottom: none;">
                                                    <h5 class="modal-title text-white" style="font-weight: 700;"><i class="bx bx-edit-alt me-2"></i>Modifier le plan tiers</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
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

                                                <div class="modal-footer" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                                    <button type="button" class="btn btn-label-secondary" style="border-radius: 8px;"
                                                        data-bs-dismiss="modal">
                                                        Fermer
                                                    </button>
                                                    <button type="submit" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(245, 87, 108, 0.3);">Mettre à
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
                                            <div class="modal-header text-white justify-content-center" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-bottom: none;">
                                                <h5 class="modal-title" id="deleteModalLabel" style="font-weight: 700;">
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
                                            <div class="modal-footer justify-content-center" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                                                <button type="button" class="btn btn-secondary" style="border-radius: 8px;"
                                                    data-bs-dismiss="modal">Annuler</button>

                                                <form method="POST" id="deletePlanFormTiers"
                                                    style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" style="border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(234, 84, 85, 0.3);"
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
