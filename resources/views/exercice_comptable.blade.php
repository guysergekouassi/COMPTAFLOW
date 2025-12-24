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
                                    // Suppression de l'initialisation en double de DataTable
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
                                                <th>Intitulé</th>
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
                                                    <td>{{ $exercice->intitule ?? 'N/A' }}</td>
                                                    <td>{{ number_format($exercice->nb_mois, 2, ',', ' ') }}</td>
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
        // Éléments du DOM
        const formExercice = document.getElementById('formCreateExercice');
        const modalCreate = document.getElementById('modalCenterCreate');
        const modalInstance = modalCreate ? new bootstrap.Modal(modalCreate) : null;
        const dateFinInput = document.getElementById('date_fin');
        const intituleInput = document.getElementById('intitule_exercice');
        let dataTable;

        // Initialisation du DataTable
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#exerciceTable')) {
                dataTable.destroy();
            }
            
            dataTable = $('#exerciceTable').DataTable({
                language: {
                    emptyTable: 'Aucune donnée disponible dans le tableau',
                    info: 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
                    infoEmpty: 'Affichage de 0 à 0 sur 0 entrées',
                    infoFiltered: '(filtré à partir de _MAX_ entrées totales)',
                    lengthMenu: 'Afficher _MENU_ entrées',
                    loadingRecords: 'Chargement...',
                    processing: 'Traitement...',
                    search: 'Rechercher :',
                    zeroRecords: 'Aucun enregistrement trouvé',
                    paginate: {
                        first: 'Premier',
                        last: 'Dernier',
                        next: 'Suivant',
                        previous: 'Précédent'
                    },
                    aria: {
                        sortAscending: ': activer pour trier par ordre croissant',
                        sortDescending: ': activer pour trier par ordre décroissant'
                    }
                },
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true,
                autoWidth: false,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });
            
            return dataTable;
        }
        
        // Initialiser le DataTable au chargement de la page
        $(document).ready(function() {
            // Vérifier si le tableau existe avant de l'initialiser
            if ($('#exerciceTable').length) {
                dataTable = initDataTable();
            }

        // Formater une date au format jj/mm/aaaa
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR');
        }

        // Ajouter une ligne au DataTable
        function addRowToTable(exercice) {
            if (!dataTable) return;
            
            // Créer la ligne avec toutes les colonnes nécessaires
            const rowNode = dataTable.row.add([
                formatDate(exercice.date_debut),
                formatDate(exercice.date_fin),
                exercice.intitule || 'N/A',
                exercice.nb_mois ? parseFloat(exercice.nb_mois).toFixed(2).replace(/\.?0+$/, '') : '0',
                exercice.nombre_journaux_saisis || '0',
                `
                <div class="d-flex gap-2">
                    <button type="button"
                            class="btn p-0 border-0 bg-transparent text-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteConfirmationModal"
                            data-id="${exercice.id}"
                            data-label="${exercice.intitule || 'cet exercice'}">
                        <i class="bx bx-trash fs-5"></i>
                    </button>
                    
                    <button type="button"
                            class="btn p-0 border-0 bg-transparent text-success show-accounting-entries"
                            data-bs-placement="top" 
                            title="Afficher les journaux"
                            data-id="${exercice.id}"
                            data-date_debut="${exercice.date_debut}"
                            data-date_fin="${exercice.date_fin}"
                            data-intitule="${exercice.intitule || ''}">
                        <i class='bx bx-pencil'></i>
                    </button>
                    
                    ${exercice.cloturer !== undefined && exercice.cloturer === 0 ? `
                    <button type="button"
                            class="btn p-0 border-0 bg-transparent text-warning open-cloture-modal"
                            data-bs-target="#clotureConfirmationModal"
                            data-bs-toggle="modal" 
                            data-bs-placement="top"
                            title="Cloturer l'exercice"
                            data-id="${exercice.id}"
                            data-date_debut="${exercice.date_debut}"
                            data-date_fin="${exercice.date_fin}"
                            data-intitule="${exercice.intitule || ''}">
                        <i class='bx bx-lock-open-alt'></i>
                    </button>
                    ` : ''}
                </div>
                `
            ]).draw(false).node();
            
            // Animation de la nouvelle ligne
            $(rowNode).css('background-color', '#e8f5e9');
            setTimeout(() => {
                $(rowNode).css('background-color', '');
                
                // Réinitialiser les tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
                
                // Réinitialiser les gestionnaires d'événements pour les boutons
                initializeEventHandlers();
            }, 100);
            
            return rowNode;
        }

        // Afficher une alerte
        function showAlert(type, message) {
            // Supprimer les anciennes alertes
            const oldAlerts = document.querySelectorAll('.alert-dismissible');
            oldAlerts.forEach(alert => alert.remove());

            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Ajouter l'alerte en haut de la page
            const container = document.querySelector('.container-xxl');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
                
                // Supprimer l'alerte après 5 secondes
                setTimeout(() => {
                    const alert = container.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }
        }

        // Générer automatiquement l'intitulé à partir de la date de fin
        function genererIntitule() {
            if (!dateFinInput || !intituleInput) return;
            
            const dateFinValue = dateFinInput.value;
            if (dateFinValue) {
                try {
                    const dateObj = new Date(dateFinValue);
                    const annee = dateObj.getFullYear();
                    // Ne générer l'intitulé que si le champ est vide ou contient un format d'exercice
                    if (!intituleInput.value || /^Exercice \d{4}$/.test(intituleInput.value)) {
                        intituleInput.value = `Exercice ${annee}`;
                    }
                } catch (e) {
                    console.error("Erreur de formatage de la date:", e);
                }
            }
        }

        // Gestion de la soumission du formulaire
        if (formExercice) {
            formExercice.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                
                try {
                    // Désactiver le bouton pendant la soumission
                    submitButton.disabled = true;
                    submitButton.innerHTML = `
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Enregistrement...
                    `;
                    
                    // Récupérer le token CSRF
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Créer un objet FormData
                    const formData = new FormData(this);
                    
                    // Envoyer la requête
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Gestion des erreurs de validation
                        if (response.status === 422 && data.errors) {
                            // Réinitialiser les états d'erreur précédents
                            document.querySelectorAll('.is-invalid').forEach(el => {
                                el.classList.remove('is-invalid');
                            });
                            document.querySelectorAll('.invalid-feedback').forEach(el => {
                                el.remove();
                            });
                            
                            // Afficher les erreurs de validation
                            let errorMessages = [];
                            
                            for (const [field, messages] of Object.entries(data.errors)) {
                                const input = document.querySelector(`[name="${field}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'invalid-feedback';
                                    errorDiv.textContent = messages[0];
                                    input.parentNode.appendChild(errorDiv);
                                    errorMessages.push(messages[0]);
                                }
                            }
                            
                            if (errorMessages.length > 0) {
                                showAlert('danger', errorMessages.join('<br>'));
                            } else {
                                showAlert('danger', 'Veuillez corriger les erreurs dans le formulaire.');
                            }
                        } else {
                            throw new Error(data.message || 'Une erreur est survenue');
                        }
                        return;
                    }

                    if (data.success) {
                        // Ajouter la nouvelle ligne au tableau
                        addRowToTable(data.exercice);
                        
                        // Afficher un message de succès
                        showAlert('success', data.message || 'Exercice enregistré avec succès');
                        
                        // Fermer le modal et réinitialiser le formulaire
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        this.reset();
                    } else {
                        throw new Error(data.message || 'Une erreur est survenue');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    showAlert('danger', error.message || 'Une erreur est survenue lors de l\'enregistrement');
                } finally {
                    // Réactiver le bouton
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            });
        }

        // Gestion de la génération automatique de l'intitulé
        if (dateFinInput) {
            dateFinInput.addEventListener('change', genererIntitule);
            dateFinInput.addEventListener('input', genererIntitule);
        }

        // Fonction pour initialiser les gestionnaires d'événements
        function initializeEventHandlers() {
            // Gestionnaire pour le bouton de suppression
            document.querySelectorAll('[data-bs-target="#deleteConfirmationModal"]').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const label = this.getAttribute('data-label') || 'cet exercice';
                    
                    const form = document.getElementById('deleteForm');
                    if (form) {
                        form.action = exercice_comptableDeleteUrl.replace('__ID__', id);
                    }
                    
                    const modalLabel = document.getElementById('deleteModalLabel');
                    if (modalLabel) {
                        modalLabel.textContent = `Supprimer ${label}`;
                    }
                });
            });
            
            // Gestionnaire pour le bouton de clôture
            document.querySelectorAll('.open-cloture-modal').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const label = this.getAttribute('data-intitule') || 'cet exercice';
                    
                    const form = document.getElementById('clotureForm');
                    if (form) {
                        form.action = exercice_comptableCloturerUrl.replace('__ID__', id);
                    }
                    
                    const modalLabel = document.getElementById('clotureModalLabel');
                    if (modalLabel) {
                        modalLabel.textContent = `Clôturer ${label}`;
                    }
                });
            });
        }
        
        // Initialiser le DataTable et les gestionnaires d'événements
        $(document).ready(function() {
            if ($('#exerciceTable').length) {
                dataTable = initDataTable();
                initializeEventHandlers();
            }
        });

        // Gestion de la fermeture du modal
        if (modalCreate) {
            modalCreate.addEventListener('hidden.bs.modal', function() {
                if (formExercice) {
                    formExercice.reset();
                    // Réinitialiser les messages d'erreur
                    document.querySelectorAll('.is-invalid').forEach(el => {
                        el.classList.remove('is-invalid');
                    });
                    document.querySelectorAll('.invalid-feedback').forEach(el => {
                        el.remove();
                    });
                }
            });
        }
        });
    });
</script>
</body>

</html>
