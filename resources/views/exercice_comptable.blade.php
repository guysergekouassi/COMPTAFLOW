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

                @include('components.header', ['page_title' => 'EXERCICE <span class="text-gradient">COMPTABLE</span>'])

                <!-- / Navbar -->

                <!-- Content wrapper -->

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row g-6 mb-6">

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


                            <!-- Section table -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Exercice comptable</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate">
                                            Nouvel exercice
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2">
                                        <!-- Champ Date de début -->
                                        <div class="col-md-4">
                                            <input type="date" id="filter-date-debut" class="form-control"
                                                placeholder="Date de début">
                                        </div>

                                        <!-- Champ Date de fin -->
                                        <div class="col-md-4">
                                            <input type="date" id="filter-date-fin" class="form-control"
                                                placeholder="Date de fin">
                                        </div>

                                        <!-- Boutons Appliquer et Réinitialiser -->
                                        <div class="col-md-4 d-flex gap-2">
                                            <button class="btn btn-primary w-100" id="apply-filters">
                                                Appliquer les filtres
                                            </button>
                                            <button class="btn btn-secondary w-100" id="reset-filters">
                                                Réinitialiser
                                            </button>
                                        </div>
                                    </div>
                                </div>


                                <!-- Table -->

                                <script>
                                    $(document).ready(function() {
                                        $('#exerciceTable').DataTable({
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
                                    <table class="table" id="exerciceTable">

                                        <thead>
                                            <tr>
                                                <th>Date de debut</th>
                                                <th>Date de fin</th>
                                                <th>Nombre de mois</th>
                                                <th>Nombre de journaux</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($exercices as $exercice)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($exercice->date_debut)->format('d/m/Y') }}
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($exercice->date_fin)->format('d/m/Y') }}
                                                    </td>
                                                    <td>
                                                        {{ $exercice->nb_mois }}
                                                    </td>





                                                    <td>{{ $exercice->nombre_journaux_saisis }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">


                                                            <!-- Bouton Supprimer -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteConfirmationModal"
                                                                data-id="{{ $exercice->id }}"
                                                                data-label="{{ $exercice->intitule }}">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>

                                                            <!-- Bouton envoi de données -->
                                                            <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-success show-accounting-entries"
                                                                data-bs-placement="top" title="Afficher les journaux"
                                                                data-id="{{ $exercice->id }}"
                                                                data-date_debut="{{ $exercice->date_debut }}"
                                                                data-date_fin="{{ $exercice->date_fin }}"
                                                                data-intitule="{{ $exercice->intitule }}">
                                                                <i class='bx  bx-pencil'></i>
                                                            </button>

                                                            @if ($exercice->cloturer == 0)
                                                                <!-- Bouton pour afficher les journaux (non clôturé) -->
                                                                <button type="button"
                                                                    class="btn p-0 border-0 bg-transparent text-danger open-cloture-modal"
                                                                    data-bs-target="#clotureConfirmationModal"
                                                                    data-bs-toggle="modal" data-bs-placement="top"
                                                                    title="Cloturer l'exercice"
                                                                    data-id="{{ $exercice->id }}"
                                                                    data-date_debut="{{ $exercice->date_debut }}"
                                                                    data-date_fin="{{ $exercice->date_fin }}"
                                                                    data-intitule="{{ $exercice->intitule }}">
                                                                    <i class='bx bx-lock-open-alt'></i>
                                                                </button>
                                                            @else
                                                                <!-- Bouton pour afficher les journaux (clôturé) -->
                                                                <button type="button"
                                                                    class="btn p-0 border-0 bg-transparent text-danger"
                                                                    title="Exercice cloturer">
                                                                    <i class='bx bx-lock'></i>
                                                                </button>
                                                            @endif



                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>

                            <!-- Modal Creation Ecriture-->
                            <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                    <form id="formCreateExercice" method="POST"
                                        action="{{ route('exercice_comptable.store') }}">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCenterTitle">
                                                    Créer un nouvel exercice comptable
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label for="date_debut" class="form-label">Date de
                                                            début</label>
                                                        <input type="date" id="date_debut" name="date_debut"
                                                            class="form-control" required />
                                                        <div class="text-danger small mt-1" id="error_date_debut">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="date_fin" class="form-label">Date de fin</label>
                                                        <input type="date" id="date_fin" name="date_fin"
                                                            class="form-control" required />
                                                        <div class="text-danger small mt-1" id="error_date_fin"></div>
                                                    </div>
                                                    <div class="col-12">
                                                        <label for="intitule_exercice" class="form-label">Intitulé de
                                                            l'exercice</label>
                                                        <input type="text" id="intitule_exercice" name="intitule"
                                                            class="form-control" placeholder="Ex : Exercice 2025" />
                                                        <div class="text-danger small mt-1" id="error_intitule"></div>
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




                            <!-- Modal Creation plan update-->
                            <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalCenterTitle">
                                                Créer un plan
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="nameWithTitle" class="form-label">Nom</label>
                                                    <input type="text" id="nameWithTitle" class="form-control"
                                                        placeholder="Entrer le nom" />
                                                </div>
                                                <div class="col-6">
                                                    <label for="emailWithTitle" class="form-label">Email</label>
                                                    <input type="email" id="emailWithTitle" class="form-control"
                                                        placeholder="xxx@xxx.xx" />
                                                </div>
                                                <div class="col-6">
                                                    <label for="dobWithTitle" class="form-label">Date de
                                                        naissance</label>
                                                    <input type="date" id="dobWithTitle" class="form-control" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary"
                                                data-bs-dismiss="modal">
                                                Fermer
                                            </button>
                                            <button type="button" class="btn btn-primary">
                                                Enregistrer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Confirmation de suppression -->
                            <!-- Modal de confirmation de suppression -->
                            <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
                                aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <form method="POST" id="deleteForm">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header text-white justify-content-center bg-danger">
                                                <h5 class="modal-title" id="deleteModalLabel">
                                                    <i class="bx bx-error-circle me-2"></i>Confirmer la suppression
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p class="mb-0">
                                                    Êtes-vous sûr de vouloir supprimer ce projet ? Cette
                                                    action est <strong>irréversible</strong>.
                                                </p>
                                                <p class="fw-bold text-danger mt-2" id="projectToDelete"></p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                            <!-- Modal de confirmation de clôture -->
                            <div class="modal fade" id="clotureConfirmationModal" tabindex="-1"
                                aria-labelledby="clotureModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <form method="POST" id="clotureForm">
                                        @csrf
                                        @method('PATCH') <!-- Ou PUT si tu préfères -->
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header text-white justify-content-center bg-warning">
                                                <h5 class="modal-title" id="clotureModalLabel">
                                                    <i class="bx bx-lock-alt me-2"></i>Clôturer l'exercice
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p class="mb-0">
                                                    Êtes-vous sûr de vouloir <strong>clôturer</strong> cet exercice
                                                    ?<br>
                                                    Après clôture, aucune modification ne sera possible.
                                                </p>
                                                <p class="fw-bold text-danger mt-2" id="exerciceToCloture"></p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <button type="submit" class="btn btn-warning">
                                                    Clôturer
                                                </button>
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

        <script>

            const journauxSaisisUrl = "{{ route('journaux_saisis') }}";
            const exercice_comptableDeleteUrl = "{{ route('exercice_comptable.destroy', ['id' => '__ID__']) }}";
            const exercice_comptableCloturerUrl = "{{ route('exercice_comptable.cloturer', ['id' => '__ID__']) }}";

        </script>
        <script src="{{ asset('js/exercice_compt.js') }}"></script>

        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateFinInput = document.getElementById('date_fin');
        const intituleInput = document.getElementById('intitule_exercice');

        // Fonction pour générer l'intitulé
        function genererIntitule() {
            const dateFinValue = dateFinInput.value;

            if (dateFinValue) {
                try {
                    // Crée un objet Date à partir de la valeur de l'input
                    const dateObj = new Date(dateFinValue);

                    // Extrait l'année (méthode getFullYear() pour avoir les 4 chiffres)
                    const annee = dateObj.getFullYear();

                    // Formate l'intitulé
                    const nouvelIntitule = 'Exercice ' + annee;

                    // Met à jour le champ Intitulé
                    intituleInput.value = nouvelIntitule;

                } catch (e) {
                    // Gérer les erreurs si la date n'est pas valide
                    console.error("Erreur de formatage de la date:", e);
                }
            } else {
                // Si la date de fin est vide, vider aussi l'intitulé
                intituleInput.value = '';
            }
        }

        // 1. Écouter le changement sur le champ 'Date de fin'
        dateFinInput.addEventListener('change', genererIntitule);

        // 2. Écouter la saisie clavier (utile si l'utilisateur saisit la date manuellement)
        dateFinInput.addEventListener('input', genererIntitule);

        // Optionnel : Générer l'intitulé au chargement du modal si des données sont pré-remplies
        // genererIntitule();
    });
</script>
</body>

</html>
