<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header')
                <div class="content-wrapper">
                    <div class="container-md flex-grow-1 container-p-y">

                        <div class="row justify-content-center">
                            <div class="col-12 text-center mb-4">
                                <h2>Choisissez votre Plan d'Abonnement</h2>
                                <p class="lead">Accédez à toutes les fonctionnalités nécessaires pour gérer votre comptabilité en toute simplicité.</p>

                                {{-- Bouton pour charger les journaux par défaut ici --}}
                                {{-- <button class="btn btn-sm btn-outline-info mt-3" data-bs-toggle="modal" data-bs-target="#Plan_defaut_Tresorerie">
                                    <i class="bx bx-download"></i> Charger les Journaux de Trésorerie par Défaut (Banque, Caisse)
                                </button> --}}

                            </div>
                        </div>

                        <div class="row justify-content-center">
                            {{-- Encadrer les cartes pour un meilleur centrage et une largeur stable --}}
                            <div class="col-12 col-xl-10">
                                <div class="row g-4">
                                    {{-- Pack ESSENTIEL --}}
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow border-success" style="border-width: 3px;">
                                            <div class="card-body text-center">
                                                <h4 class="card-title text-success mb-2">Pack Basic</h4>
                                                <h1 class="display-4 fw-bold mb-3 price-display" data-monthly="16,50" data-annual="13,20">50000 fcfa<span class="fs-6 fw-normal">/mois</span></h1>
                                                <p class="text-muted mb-4">Pour les auto-entrepreneurs et PME débutantes.</p>
                                                <ul class="list-unstyled text-start mx-auto mb-4" style="max-width: 250px;">
                                                    <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i> 20 Utilisateurs</li>
                                                    <li class="mb-2"><i class="ti ti-circle-check text-success me-2"></i> Gestion de 100 tiers (clients/fournisseurs)</li>
                                                    <li class="mb-2 text-muted"><i class="ti ti-circle-x me-2"></i> Support Prioritaire (Non Inclus)</li>
                                                </ul>
                                                <button class="btn btn-success mt-3 w-100">Choisir ce Pack</button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Pack CROISSANCE --}}
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow-lg border-primary" style="border-width: 3px;">
                                            <div class="card-header text-center p-0">
                                                <span class="badge bg-primary rounded-pill position-absolute top-0 start-50 translate-middle-x mt-n3 py-2 px-4 shadow-sm">Le plus populaire !</span>
                                            </div>
                                            <div class="card-body text-center pt-5">
                                                <h4 class="card-title text-primary mb-2">Pack Standard</h4>
                                                <h1 class="display-4 fw-bold mb-3 price-display" data-monthly="39,99" data-annual="31,99">100000 fcfa<span class="fs-6 fw-normal">/mois</span></h1>
                                                <p class="text-muted mb-4">Idéal pour PME en développement.</p>
                                                <ul class="list-unstyled text-start mx-auto mb-4" style="max-width: 250px;">
                                                    <li class="mb-2 fw-bold"><i class="ti ti-check text-primary me-2"></i> Toutes les fonctionnalités du Pack Essentiel</li>
                                                    <li class="mb-2"><i class="ti ti-check text-primary me-2"></i> Gestion multi-utilisateurs (jusqu'à 50)</li>
                                                    <li class="mb-2"><i class="ti ti-check text-primary me-2"></i> Plan de tiers illimité</li>
                                                    <li class="mb-2"><i class="ti ti-check text-primary me-2"></i> Support prioritaire</li>
                                                </ul>
                                                <button class="btn btn-primary mt-3 w-100">Choisir ce Pack</button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Pack ENTREPRISE --}}
                                    <div class="col-md-4">
                                        <div class="card h-100 shadow border-secondary" style="border-width: 3px;">
                                            <div class="card-body text-center">
                                                <h4 class="card-title text-secondary mb-2">Pack ENTREPRISE</h4>
                                                 <h1 class="display-4 fw-bold mb-3 price-display" data-monthly="16,50" data-annual="13,20">150000 fcfa<span class="fs-6 fw-normal">/mois</span></h1>
                                                <p class="text-muted mb-4">Pour les grandes structures et les multi-entités.</p>
                                                <ul class="list-unstyled text-start mx-auto mb-4" style="max-width: 250px;">
                                                    <li class="mb-2 fw-bold"><i class="ti ti-check text-secondary me-2"></i> Toutes les fonctionnalités du Pack Croissance</li>
                                                    <li class="mb-2"><i class="ti ti-check text-secondary me-2"></i> Gestion multi-entités</li>
                                                    <li class="mb-2"><i class="ti ti-check text-secondary me-2"></i> Utilisateurs illimités</li>
                                                    <li class="mb-2"><i class="ti ti-check text-secondary me-2"></i> API d'intégration</li>
                                                </ul>
                                               <button class="btn btn-success mt-3 w-100">Choisir ce Pack</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    @include('components.footer')
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>


    {{-- MODAL DE CONFIRMATION DE SUPPRESSION (Conservé car inclus dans le premier code, mais n'est pas utilisé dans le contexte de la page d'abonnement. Je le laisse au cas où il soit géré globalement.) --}}
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">
                        <i class="bx bx-trash"></i> Confirmation de Suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    Êtes-vous sûr de vouloir supprimer le journal de trésorerie avec le code :
                    <strong id="journalCodeToDelete"></strong> ?
                    Cette action est irréversible.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    @php
                        $authorizedRoles = ['admin', 'super_admin'];
                        // Assurez-vous que auth()->user() est disponible.
                        $userRole = auth()->check() ? auth()->user()->role : 'guest';
                    @endphp
                    @if (in_array($userRole, $authorizedRoles))
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Logique pour basculer entre les prix mensuels et annuels (si non-fournie, je la maintiens vide)
        document.addEventListener('DOMContentLoaded', function () {
            // Vous pouvez implémenter ici la logique de bascule Mensuel/Annuel en utilisant
            // les attributs data-monthly et data-annual des éléments .price-display

            // Exemple simple de fonction qui pourrait être déclenchée par un bouton toggle (non inclus ici)
            /*
            function togglePricing(isAnnual) {
                document.querySelectorAll('.price-display').forEach(element => {
                    const monthly = element.getAttribute('data-monthly');
                    const annual = element.getAttribute('data-annual');
                    if (isAnnual) {
                        element.innerHTML = `${annual} $US<span class="fs-6 fw-normal">/an (facturation annuelle)</span>`;
                    } else {
                        element.innerHTML = `${monthly} $US<span class="fs-6 fw-normal">/mois</span>`;
                    }
                });
            }
            */
        });

        // La fonction setDeleteAction et l'écouteur d'événement pour deleteConfirmationModal
        // ne sont pas pertinents sur cette page d'abonnement, mais je les conserve
        // pour la complétude si la page est un template général.
        function setDeleteAction(button) {
            const journalId = button.getAttribute('data-id');
            const journalCode = button.getAttribute('data-code-journal');

            // 1. Mettre à jour le texte dans le modal
            document.getElementById('journalCodeToDelete').textContent = journalCode;

            // 2. Construire l'URL de la route de suppression
            // Assurez-vous que la fonction route() est disponible ou définissez l'URL en dur si nécessaire
            const deleteUrl = "{{ route('destroy_tresorerie', 'TEMP_ID') }}".replace('TEMP_ID', journalId);

            // 3. Mettre à jour l'action du formulaire dans le modal
            document.getElementById('deleteForm').setAttribute('action', deleteUrl);
        }

        const deleteModal = document.getElementById('deleteConfirmationModal');
        if (deleteModal) {
            deleteModal.addEventListener('hidden.bs.modal', function (event) {
                document.getElementById('journalCodeToDelete').textContent = '';
                document.getElementById('deleteForm').removeAttribute('action');
            });
        }
    </script>
</body>
</html>
