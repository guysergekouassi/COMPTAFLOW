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
                    <div class="container-xxl flex-grow-1 container-p-y">

                        {{-- Alerts --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Journal de Trésorerie</h5>
                                <div class="d-flex gap-2">
                                    {{-- <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#periodSelectionModal">
                                        <i class="bx bx-file"></i> Générer Solde Trésor
                                    </button> --}}

                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                                        <i class="bx bx-plus"></i> Ajouter
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive text-nowrap p-3">
                                <table class="table table-hover table-striped table-bordered align-middle" id="journalTable">
                                    <thead>
                                        <tr>
                                            <th>Code journal</th>
                                            <th>Intitulé</th>
                                            <th>Traitement analytique</th>
                                            <th>Compte</th>
                                            <th>Poste de Tresorerie </th>
                                            <th>Type flux</th>
                                            <th>Rapprochement sur</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tresoreries as $journal)
                                            <tr>
                                                <td>{{ $journal->code_journal }}</td>
                                                <td>{{ $journal->intitule }}</td>
                                                <td>{{ $journal->traitement_analytique }}</td>
                                                <td>{{ $journal->compte_de_contrepartie }}</td>
                                                <td>{{ $journal->poste_tresorerie }}</td>
                                                <td>{{ $journal->type_flux }}</td>
                                                <td>{{ $journal->rapprochement_sur }}</td>
                                                <td class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $journal->id }}">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteConfirmationModal"
                                                            data-id="{{ $journal->id }}"
                                                            data-code-journal="{{ $journal->code_journal }}"
                                                            onclick="setDeleteAction(this)">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Aucune donnée trouvée</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- If report generated, show dedicated modal (kept but will be shown via JS / server side flag) --}}
                        @if(isset($reportGenerated) && $reportGenerated)
                            <div class="modal fade" id="cashFlowReportModal" tabindex="-1" aria-labelledby="cashFlowReportModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="cashFlowReportModalLabel">
                                                <i class="bx bx-table"></i> Prévisualisation du Plan de Trésorerie
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            {{-- Contenu de prévisualisation côté serveur (si tu veux l'injecter) --}}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer la prévisualisation</button>
                                            <a href="{{ route('export_cash_flow_csv') }}" class="btn btn-success">
                                                <i class="bx bx-download"></i> Exporter et Télécharger
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Modal : Sélection de période (pour générer/preview) --}}
                        <div class="modal fade" id="periodSelectionModal" tabindex="-1" aria-labelledby="periodSelectionModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('generate_cash_flow_plan') }}" method="GET" id="cashFlowForm">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="periodSelectionModalLabel">Sélectionner la Période du Plan de Trésorerie</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Définissez la période pour laquelle vous souhaitez visualiser le flux de trésorerie.</p>
                                            <div class="mb-3">
                                                <label for="start_date" class="form-label">Date de Début</label>
                                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="end_date" class="form-label">Date de Fin</label>
                                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="button" class="btn btn-info" id="previewPdfButton">
                                                <i class="bx bx-show"></i> Prévisualiser le PDF
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal : Create --}}
                        <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('storetresorerie') }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Ajouter un Journal</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="code_journal" class="form-label">Code journal</label>
                                                    <input type="text" name="code_journal" class="form-control form-control-sm" required>
                                                    @error('code_journal') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="intitule" class="form-label">Intitulé</label>
                                                    <input type="text" name="intitule" class="form-control form-control-sm" required>
                                                    @error('intitule') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="traitement_analytique" class="form-label">Traitement analytique</label>
                                                    <select name="traitement_analytique" id="traitement_analytique" class="form-control form-control-sm" required>
                                                        <option value="">-- Sélectionnez --</option>
                                                        <option value="oui" {{ old('traitement_analytique') == 'oui' ? 'selected' : '' }}>Oui</option>
                                                        <option value="non" {{ old('traitement_analytique') == 'non' ? 'selected' : '' }}>Non</option>
                                                    </select>
                                                    @error('traitement_analytique') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="compte_de_contrepartie" class="form-label">Comptes</label>
                                                    <select name="compte_de_contrepartie" id="compte_de_contrepartie" class="form-control form-control-sm" required>
                                                        <option value="">-- Sélectionner un compte --</option>
                                                        @if(isset($comptesCinq) && $comptesCinq->count() > 0)
                                                            @foreach($comptesCinq as $compte)
                                                                <option value="{{ $compte->numero_de_compte }}" {{ old('compte_de_contrepartie') == $compte->numero_de_compte ? 'selected' : '' }}>
                                                                    {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                                </option>
                                                            @endforeach
                                                        @else
                                                            <option disabled>Aucun compte disponible</option>
                                                        @endif
                                                    </select>
                                                    @error('compte_de_contrepartie') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="rapprochement_sur" class="form-label">Rapprochement sur</label>
                                                    <select name="rapprochement_sur" id="rapprochement_sur" class="form-control form-control-sm" required>
                                                        <option value="">-- Sélectionnez --</option>
                                                        <option value="automatique" {{ old('rapprochement_sur') == 'automatique' ? 'selected' : '' }}>Automatique</option>
                                                        <option value="manuel" {{ old('rapprochement_sur') == 'manuel' ? 'selected' : '' }}>Manuel</option>
                                                    </select>
                                                    @error('rapprochement_sur') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="Poste" class="form-label">Poste de tresorerie</label>
                                                    <select name="poste_tresorerie" id="Poste" class="form-control form-control-sm" required>
                                                        <option value="">-- Sélectionnez --</option>
                                                        @if(isset($comptesTresorerie) && $comptesTresorerie->count() > 0)
                                                            @foreach($comptesTresorerie as $compte)
                                                                <option value="{{ $compte->name }}" {{ old('poste_tresorerie') == $compte->name ? 'selected' : '' }}>
                                                                    {{ $compte->name }} ({{ $compte->type }})
                                                                </option>
                                                            @endforeach
                                                        @else
                                                            <option disabled>Aucun poste disponible</option>
                                                        @endif
                                                    </select>
                                                    @error('poste_tresorerie') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="flux" class="form-label">Type de flux</label>
                                                    <select name="type_flux" id="flux" class="form-control form-control-sm" required>
                                                        <option value="">-- Sélectionnez --</option>
                                                        <option value="Crédit">Encaissement</option>
                                                        <option value="Débit">Decaissement</option>
                                                    </select>
                                                    @error('type_flux') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal : Charger par défaut --}}
                        <div class="modal fade" id="Plan_defaut_Tresorerie" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form id="PlandefautTresorerieForm" method="POST" action="{{ route('journal_tresorerie.defaut') }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Charger les Journaux par Défaut</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Voulez-vous charger la liste des <strong>Journaux de Trésorerie par défaut</strong> (Banque, Caisse) ?</p>
                                            <p class="text-danger small">Ceci n'effacera pas les journaux déjà existants, mais pourrait créer des doublons si vous les avez déjà entrés manuellement.</p>
                                            <input type="hidden" name="use_default" value="true">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Confirmer</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal : Prévisualisation PDF (séparé, en dehors des autres modals) --}}
                        <div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-labelledby="modalPreviewPDFLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalPreviewPDFLabel">Prévisualisation du Plan de Trésorerie</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-0">
                                        <iframe id="pdfPreviewFrame"
                                                class="embed-responsive-item"
                                                style="width:100%;height:80vh;border:none;display:block;"
                                                frameborder="0"></iframe>
                                    </div>

                                    <div class="modal-footer">
                                        <a href="#" id="exportCsvLink" class="btn btn-success" download="Plan_Tresorerie.csv">
                                            <i class="bx bx-download"></i> Exporter en CSV
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modals d'édition : placés après le tableau pour éviter l'imbrication dans le <table> --}}
                        @foreach($tresoreries as $journal)
                            <div class="modal fade" id="editModal{{ $journal->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <form action="{{ route('update_tresorerie', $journal->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier le Journal</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="code_journal_{{ $journal->id }}" class="form-label">Code journal</label>
                                                        <input type="text" id="code_journal_{{ $journal->id }}" name="code_journal" class="form-control form-control-sm" value="{{ $journal->code_journal }}" required>
                                                        @error('code_journal') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="intitule_{{ $journal->id }}" class="form-label">Intitulé</label>
                                                        <input type="text" id="intitule_{{ $journal->id }}" name="intitule" class="form-control form-control-sm" value="{{ $journal->intitule }}" required>
                                                        @error('intitule') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="traitement_analytique_{{ $journal->id }}" class="form-label">Traitement analytique</label>
                                                        <select name="traitement_analytique" id="traitement_analytique_{{ $journal->id }}" class="form-control form-control-sm" required>
                                                            <option value="">-- Sélectionnez --</option>
                                                            <option value="oui" {{ $journal->traitement_analytique == 'oui' ? 'selected' : '' }}>Oui</option>
                                                            <option value="non" {{ $journal->traitement_analytique == 'non' ? 'selected' : '' }}>Non</option>
                                                        </select>
                                                        @error('traitement_analytique') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="compte_de_contrepartie_{{ $journal->id }}" class="form-label">Compte</label>
                                                        <select name="compte_de_contrepartie" id="compte_de_contrepartie_{{ $journal->id }}" class="form-control form-control-sm" required>
                                                            <option value="">-- Sélectionner un compte --</option>
                                                            @if(isset($comptesCinq))
                                                                @foreach($comptesCinq as $compte)
                                                                    <option value="{{ $compte->numero_de_compte }}" {{ $journal->compte_de_contrepartie == $compte->numero_de_compte ? 'selected' : '' }}>
                                                                        {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @error('compte_de_contrepartie') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="rapprochement_sur_{{ $journal->id }}" class="form-label">Rapprochement sur</label>
                                                        <select name="rapprochement_sur" id="rapprochement_sur_{{ $journal->id }}" class="form-control form-control-sm" required>
                                                            <option value="">-- Sélectionnez --</option>
                                                            <option value="automatique" {{ $journal->rapprochement_sur == 'automatique' ? 'selected' : '' }}>Automatique</option>
                                                            <option value="manuel" {{ $journal->rapprochement_sur == 'manuel' ? 'selected' : '' }}>Manuel</option>
                                                        </select>
                                                        @error('rapprochement_sur') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="poste_tresorerie_{{ $journal->id }}" class="form-label">Poste de tresorerie</label>
                                                        <select name="poste_tresorerie" id="poste_tresorerie_{{ $journal->id }}" class="form-control form-control-sm" required>
                                                            <option value="">-- Sélectionnez --</option>
                                                            @if(isset($comptesTresorerie) && $comptesTresorerie->count() > 0)
                                                                @foreach($comptesTresorerie as $compte)
                                                                    <option value="{{ $compte->name }}" {{ $journal->poste_tresorerie == $compte->name ? 'selected' : '' }}>
                                                                        {{ $compte->name }} ({{ $compte->type }})
                                                                    </option>
                                                                @endforeach
                                                            @else
                                                                <option disabled>Aucun poste disponible</option>
                                                            @endif
                                                        </select>
                                                        @error('poste_tresorerie') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="type_flux_{{ $journal->id }}" class="form-label">Type de flux</label>
                                                        <select name="type_flux" id="type_flux_{{ $journal->id }}" class="form-control form-control-sm" required>
                                                            <option value="">-- Sélectionnez --</option>
                                                            <option value="Entrée" {{ $journal->type_flux == 'Entrée' ? 'selected' : '' }}>Entrée</option>
                                                            <option value="Sortie" {{ $journal->type_flux == 'Sortie' ? 'selected' : '' }}>Sortie</option>
                                                        </select>
                                                        @error('type_flux') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-warning">Modifier</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Modal : Confirmation de suppression --}}
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
                                            $userRole = auth()->user()->role ?? null;
                                        @endphp

                                        @if ($userRole && in_array($userRole, $authorizedRoles))
                                            <form id="deleteForm" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Oui, Supprimer</button>
                                            </form>
                                        @else
                                            <button class="btn btn-danger" disabled>Non autorisé</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> {{-- container-xxl --}}
                </div> {{-- content-wrapper --}}

                @include('components.footer')

            </div> {{-- layout-page --}}
        </div> {{-- layout-container --}}

        <div class="layout-overlay layout-menu-toggle"></div>
    </div> {{-- layout-wrapper --}}

    {{-- Scripts et JS personnalisés --}}
    <script>
        // Fonction globale pour mettre à jour l'action du formulaire de suppression
        function setDeleteAction(button) {
            const journalId = button.getAttribute('data-id');
            const journalCode = button.getAttribute('data-code-journal');

            const journalCodeEl = document.getElementById('journalCodeToDelete');
            if (journalCodeEl) {
                journalCodeEl.textContent = journalCode || '';
            }

            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                // Construire l'URL (remplace TEMP_ID)
                const deleteUrl = "{{ route('destroy_tresorerie', 'TEMP_ID') }}".replace('TEMP_ID', journalId);
                deleteForm.setAttribute('action', deleteUrl);
            }
        }

        // Réinitialiser lorsque modal suppression fermé
        (function() {
            const deleteModal = document.getElementById('deleteConfirmationModal');
            if (deleteModal) {
                deleteModal.addEventListener('hidden.bs.modal', function () {
                    const journalCodeEl = document.getElementById('journalCodeToDelete');
                    if (journalCodeEl) journalCodeEl.textContent = '';
                    const deleteForm = document.getElementById('deleteForm');
                    if (deleteForm) deleteForm.removeAttribute('action');
                });
            }
        })();

        // Si reportGenerated côté serveur -> afficher modal (si jQuery présent)
        @if(isset($reportGenerated) && $reportGenerated)
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    var cfModalEl = document.getElementById('cashFlowReportModal');
                    if (cfModalEl) {
                        var cfModal = new bootstrap.Modal(cfModalEl);
                        cfModal.show();
                    }
                } catch (e) {
                    // fallback to jQuery if bootstrap modal not initialisé de cette façon
                    if (window.jQuery) {
                        $('#cashFlowReportModal').modal('show');
                    }
                }
            });
        @endif
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Elements
            const previewPdfButton = document.getElementById('previewPdfButton');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const periodSelectionModalEl = document.getElementById('periodSelectionModal');
            const exportCsvLink = document.getElementById('exportCsvLink');
            const modalPreviewPDFEl = document.getElementById('modalPreviewPDF');

            // Ensure periodSelectionModal exists
            let periodSelectionModal = null;
            if (periodSelectionModalEl) {
                periodSelectionModal = new bootstrap.Modal(periodSelectionModalEl);
            }

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

                    // Construire URL streaming PDF et CSV (routes côté serveur)
                    const streamingUrl = "{{ route('generate_cash_flow_pdf') }}" + `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                    const exportCsvBaseUrl = "{{ route('export_cash_flow_csv') }}";
                    const csvUrl = `${exportCsvBaseUrl}?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

                    // Mettre l'iframe dans le modalPreviewPDF (si présent) et ouvrir le modal
                    if (modalPreviewPDFEl) {
                        const iframe = document.getElementById('pdfPreviewFrame');
                        if (iframe) {
                            iframe.src = streamingUrl;
                        }
                        const previewModal = new bootstrap.Modal(modalPreviewPDFEl);
                        previewModal.show();
                    } else {
                        // Sinon ouvrir dans un nouvel onglet
                        window.open(streamingUrl, '_blank');
                    }

                    // Mettre à jour le lien CSV si existant
                    if (exportCsvLink) {
                        exportCsvLink.href = csvUrl;
                    }

                    // Fermer le modal de sélection de période
                    if (periodSelectionModal) {
                        periodSelectionModal.hide();
                    }
                });
            }
        });
    </script>

</body>
</html>
