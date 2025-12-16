<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        @include('components.sidebar')

        <div class="layout-page">

            @include('components.header')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- FLASH SUCCESS --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- ********** LISTE DES COMPTES DE TRÉSORERIE ********** --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between">
                            <h5 class="mb-0">Postes de trésorerie</h5>

                            <div>
                                {{-- Bouton pour créer un NOUVEAU POSTE DE TRÉSORERIE (OUVRE LA MODAL) --}}
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreatePoste">
                                     <i class="bx bx-bank"></i> Créer un Poste
                                </button>
                                {{-- Bouton pour GENERER un RAPPORT POSTE DE TRÉSORERIE (OUVRE LA MODAL) --}}
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#periodSelectionModal">
                                     <i class="bx bx-file"></i> Generer un plan
                                </button>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nom du poste</th>

                                            <th>Categorie </th>

                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($comptes as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td> {{-- Nom du Poste (CompteTresorerie) --}}

                                                <td>{{ $item->type ?? '—' }}</td>
                                                <td>
                                                    <button
                                                        class="btn btn-icon btn-sm btn-info btn-update-poste"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalUpdatePoste"
                                                        data-id="{{ $item->id }}"
                                                        data-name="{{ $item->name }}"
                                                        data-type="{{ $item->type }}"
                                                    >
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Aucun poste de trésorerie n'a encore été créé.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>


                    {{-- ********** LISTE DES MOUVEMENTS (SI SHOW) ********** --}}
                    @isset($compte)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    Mouvements du compte : {{ $compte->nom }}
                                    <span class="badge bg-secondary">Solde : {{ number_format($compte->solde_actuel,2,',',' ') }} F CFA</span>
                                </h5>
                            </div>

                            <div class="card-body">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Libellé</th>
                                            <th>Référence</th>
                                            <th>Débit (Décaissement)</th>
                                            <th>Crédit (Encaissement)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($mouvements as $m)
                                        <tr>
                                            <td>{{ $m->date_mouvement }}</td>
                                            <td>{{ $m->libelle }}</td>
                                            <td>{{ $m->reference_piece ?? '—' }}</td>
                                            <td class="text-danger">{{ $m->montant_debit ? number_format($m->montant_debit,2,',',' ') : '' }} F CFA</td>
                                            <td class="text-success">{{ $m->montant_credit ? number_format($m->montant_credit,2,',',' ') : '' }} F CFA</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <div class="mt-2">
                                    {{ $mouvements->links() }}
                                </div>

                            </div>
                        </div>
                    @endisset

                </div>
            </div>

            @include('components.footer')

        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>
{{-- modal de previsualisation  --}}


{{-- ********** MODAL SÉLECTION DE PÉRIODE POUR GÉNÉRATION (Rapport Trésorerie) ********** --}}
<div class="modal fade" id="periodSelectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Générer un Plan de Trésorerie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="start_date">Date de Début</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="end_date">Date de Fin</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                {{-- Bouton pour prévisualiser le PDF --}}
                <button type="button" class="btn btn-primary" id="previewPdfButton">
                    <i class="bx bx-file"></i> Prévisualiser / Télécharger
                </button>
            </div>
        </div>
    </div>
</div>

{{--  fin modal de previsualisation  --}}














{{-- ********** MODAL AJOUT NOUVEAU POSTE (Déplacé de createPoste.blade.php) ********** --}}
{{-- Utilise $comptesComptablesClasse5 qui est maintenant passé par index() --}}
<div class="modal fade" id="modalCreatePoste" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
            <form action="{{ route('postetresorerie.store_poste') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Créer un nouveau Poste de Trésorerie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                                        <div class="col-md-12 mb-3">
                        <label class="form-label" for="name">Nom du Poste de Trésorerie (Ex: Achats, Acquisition)</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('nom') }}" required>
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="type">Catégories de Trésorerie</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="Flux Des Activités Operationnelles" {{ old('type') == 'Flux Des Activités Operationnelles' ? 'selected' : '' }}>Flux Des Activités Opérationnelles</option>
                            <option value="Flux Des Activités Investissement" {{ old('type') == 'Flux Des Activités Investissement' ? 'selected' : '' }}>Flux Des Activités d'Investissement</option>
                            <option value="Flux Des Activités de Financement" {{ old('type') == 'Flux Des Activités De Financement' ? 'selected' : '' }}>Flux Des Activités De Financement</option>

                        </select>
                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Créer le Poste de Trésorerie</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ********** MODAL MODIFICATION POSTE DE TRÉSORERIE ********** --}}
<div class="modal fade" id="modalUpdatePoste" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="updatePosteForm" method="POST">
                {{-- CSRF Token et méthode PATCH/PUT pour Laravel --}}
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Modifier le Poste de Trésorerie: <span id="posteNameTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="update_name">Nom du Poste de Trésorerie</label>
                        {{-- Notez l'ID unique 'update_name' pour le ciblage JS --}}
                        <input type="text" name="name" id="update_name" class="form-control" required>
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="update_type">Catégories de Trésorerie</label>
                        {{-- Notez l'ID unique 'update_type' pour le ciblage JS --}}
                        <select name="type" id="update_type" class="form-select" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="Flux Des Activités Operationnelles">Flux Des Activités Opérationnelles</option>
                            <option value="Flux Des Activités Investissement">Flux Des Activités d'Investissement</option>
                            <option value="Flux Des Activités de Financement">Flux Des Activités De Financement</option>
                        </select>
                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Sauvegarder les modifications</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ********** MODAL PRÉVISUALISATION ET TÉLÉCHARGEMENT PDF ********** --}}
<div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-labelledby="modalPreviewPDFLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPreviewPDFLabel">Plan de Trésorerie - Prévisualisation PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                {{-- L'iframe chargera le PDF en streaming/URL --}}
                <iframe id="pdfPreviewFrame" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                {{-- Le lien sera mis à jour dynamiquement par le JS pour l'export CSV --}}
                <a href="#" id="exportCsvLink" class="btn btn-success" target="_blank">
                    <i class="bx bx-download"></i> Exporter CSV
                </a>
                {{-- Le lien sera mis à jour dynamiquement par le JS pour le téléchargement PDF --}}
                <a href="#" id="downloadPdfLink" class="btn btn-primary" target="_blank" data-bs-dismiss="modal">
                    <i class="bx bx-printer"></i> Télécharger PDF
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Elements
        const previewPdfButton = document.getElementById('previewPdfButton');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const periodSelectionModalEl = document.getElementById('periodSelectionModal');
        const exportCsvLink = document.getElementById('exportCsvLink');
        const downloadPdfLink = document.getElementById('downloadPdfLink'); // NOUVEL ÉLÉMENT
        const modalPreviewPDFEl = document.getElementById('modalPreviewPDF');
        const modalUpdatePosteEl = document.getElementById('modalUpdatePoste'); // OK, ciblé ici

        // Ensure periodSelectionModal exists
        let periodSelectionModal = null;
        if (periodSelectionModalEl) {
            periodSelectionModal = new bootstrap.Modal(periodSelectionModalEl);
        }

        // ===================================================================
        // 1. Logique de Génération / Prévisualisation du PDF (Déjà existante)
        // ===================================================================

        if (previewPdfButton) {
            previewPdfButton.addEventListener('click', function(e) {
                e.preventDefault();

                if (!startDateInput || !endDateInput) {
                    alert('Les champs de date sont introuvables.');
                    return;
                }

                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                if (!startDate || !endDate) {
                    alert('Veuillez sélectionner les dates de début et de fin pour le plan.');
                    return;
                }

                const pdfStreamingUrl = "{{ route('generate_cash_flow_pdf') }}" + `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                const exportCsvBaseUrl = "{{ route('export_cash_flow_csv') }}";
                const csvUrl = `${exportCsvBaseUrl}?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

                if (modalPreviewPDFEl) {
                    const iframe = document.getElementById('pdfPreviewFrame');
                    if (iframe) {
                        iframe.src = pdfStreamingUrl;
                    }
                    const previewModal = new bootstrap.Modal(modalPreviewPDFEl);
                    previewModal.show();
                } else {
                    window.open(pdfStreamingUrl, '_blank');
                }

                if (exportCsvLink) {
                    exportCsvLink.href = csvUrl;
                }

                if (downloadPdfLink) {
                    downloadPdfLink.href = pdfStreamingUrl;
                }

                if (periodSelectionModal) {
                    periodSelectionModal.hide();
                }
            });
        }

        // ===================================================================
        // 2. Logique de Remplissage du Modal de Modification (Relocalisée)
        // C'EST CETTE PARTIE QUI ÉTAIT MAL PLACÉE DANS VOTRE CODE ORIGINAL
        // ===================================================================
        if (modalUpdatePosteEl) {
            // Écouter l'événement 'show.bs.modal' de Bootstrap, qui se déclenche juste avant l'ouverture du modal.
            modalUpdatePosteEl.addEventListener('show.bs.modal', function (event) {
                // Le bouton qui a déclenché l'ouverture est dans event.relatedTarget
                const button = event.relatedTarget;

                // Récupération des données via les data-attributes du bouton
                const posteId = button.getAttribute('data-id');
                const posteName = button.getAttribute('data-name');
                const posteType = button.getAttribute('data-type');

                // Cibler les éléments du formulaire à mettre à jour
                const form = modalUpdatePosteEl.querySelector('#updatePosteForm');
                const nameInput = modalUpdatePosteEl.querySelector('#update_name');
                const typeSelect = modalUpdatePosteEl.querySelector('#update_type');
                const titleSpan = modalUpdatePosteEl.querySelector('#posteNameTitle');

                if (form) {
                    // 1. Mise à jour de l'action du formulaire (route de mise à jour)
                    // !!! ASSUREZ-VOUS QUE LA ROUTE SUIVANTE EST CORRECTE DANS VOTRE CONFIGURATION LARAVEL !!!
                    const updateUrl = `/postetresorerie/${posteId}`; // Remplacer par la bonne route si différente
                    form.setAttribute('action', updateUrl);
                }

                // 2. Pré-remplissage des champs du modal
                if (titleSpan) {
                    titleSpan.textContent = posteName;
                }
                if (nameInput) {
                    nameInput.value = posteName;
                }
                if (typeSelect) {
                    // Sélectionner l'option correspondante dans le <select>
                    typeSelect.value = posteType;
                }
            });
        }
    });
</script>


</body>
</html>
