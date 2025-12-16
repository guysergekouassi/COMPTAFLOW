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
                                <h5 class="mb-0">Balance des Tiers générés</h5>
                                <div>
                                    <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#filterPanel">
                                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modalCenterCreate">
                                        Generer
                                    </button>
                                </div>
                            </div>

                            <!-- Filtre personnalisé -->
                            <div class="collapse px-3 pt-2" id="filterPanel">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" id="filter-client" class="form-control"
                                            placeholder="Filtrer par client..." />
                                    </div>
                                    <div class="col-md-4">
                                        <select id="filter-status" class="form-select">
                                            <option value="">Tous les statuts</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-primary w-100" id="apply-filters">
                                            Appliquer les filtres
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Période</th>
                                            <th>Date de génération</th>
                                            <th>Format</th>
                                            <th>De</th>
                                            <th>A</th>
                                            <th>Fichier</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($Balance as $balances)
                                            <tr>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($balances->date_debut)->format('d/m/Y') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($balances->date_fin)->format('d/m/Y') }}
                                                </td>

                                                <td>
                                                    
                                                    {{ $balances->updated_at }}
                                                </td>

                                                <td>
                                                    {{ $balances->format }}
                                                </td>

                                                <td>{{ $balances->PlanTiers1->numero_de_tiers ?? 'N/A' }}
                                                </td>

                                                <td>{{ $balances->PlanTiers2->numero_de_tiers ?? 'N/A' }}
                                                </td>

                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary btn-preview-pdf"
                                                        data-pdf-url="{{ asset('balances_tiers/' . $balances->balance_tiers) }}"
                                                        data-bs-toggle="modal" data-bs-target="#pdfPreviewModal"
                                                        title="Afficher le grand livre">
                                                        Voir PDF
                                                    </button>
                                                </td>
                                                
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <!-- Téléchargement -->
                                                        <a href="{{ asset('balances_tiers/' . $balances->balance_tiers) }}" download
                                                            class="btn p-0 border-0 bg-transparent text-danger"
                                                            title="Télécharger le grand livre">
                                                            <i class='bx bx-arrow-down-square fs-5'></i>
                                                        </a>

                                                        <!-- Suppression -->
                                                        <button type="button"
                                                            class="btn p-0 border-0 bg-transparent text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteConfirmationModal"
                                                            data-id="{{ $balances->id }}"
                                                            data-filename="{{ $balances->balance_tiers }}">
                                                            <i class="bx bx-trash fs-5"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Aucune Balance
                                                    disponible.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>


                        </div>

                        <!-- Modal Creation-->
                        <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                <form method="POST" action="{{ route('accounting_balance_tiers.generateBalance') }}"
                                    id="grandLivreForm">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Générer une Balance</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>

                                        <div class="modal-body">
                                            {{-- <div class="row g-3"> --}}

                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-2">
                                                    <label class="form-label">Période</label>
                                                </div>
                                                <div class="col-md-5">

                                                    <label for="date_debut" class="form-label">Du</label>
                                                    <input type="date" id="date_debut" name="date_debut"
                                                        class="form-control" required />
                                                    <div class="invalid-feedback">Veuillez renseigner la date de début.
                                                    </div>

                                                </div>
                                                <div class="col-md-5">
                                                    <label for="date_fin" class="form-label">Au</label>
                                                    <input type="date" id="date_fin" name="date_fin"
                                                        class="form-control" required />
                                                    <div class="invalid-feedback">Veuillez renseigner la date de fin.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-3 align-items-end mt-2">
                                                <div class="col-md-2">
                                                    <label class="form-label">Tiers</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <label for="plan_tiers_id_1" class="form-label">Du</label>
                                                    <select id="plan_tiers_id_1" name="plan_tiers_id_1"
                                                        class="selectpicker w-100" data-width="auto"
                                                        data-live-search="true" required>
                                                        <option value="">-- Sélectionnez un compte --</option>
                                                        @foreach ($PlanTiers as $plan)
                                                            <option value="{{ $plan->id }}">
                                                                {{ $plan->numero_de_tiers }} - {{ $plan->intitule }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">Veuillez sélectionner un compte.
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <label for="plan_tiers_id_2" class="form-label">Au</label>
                                                    <select id="plan_tiers_id_2" name="plan_tiers_id_2"
                                                        class="selectpicker w-100" data-width="auto"
                                                        data-live-search="true" required>
                                                        <option value="">-- Sélectionnez un compte --</option>
                                                        @foreach ($PlanTiers as $plan)
                                                            <option value="{{ $plan->id }}">
                                                                {{ $plan->numero_de_tiers }} - {{ $plan->intitule }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback" id="compte2-error">Veuillez
                                                        sélectionner un compte.</div>
                                                </div>
                                            </div>

                                            <div class="row g-3 align-items-end mt-2">
                                                <div class="col-md-2">
                                                    <label class="form-label">Format</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select id="format_fichier" name="format_fichier"
                                                        class="selectpicker w-100" data-live-search="false" required>
                                                        <option value="pdf" selected>PDF</option>
                                                        <option value="excel">EXCEL</option>
                                                        <option value="csv">CSV</option>
                                                    </select>
                                                    <div class="invalid-feedback">Veuillez sélectionner une
                                                        option.</div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary"
                                                data-bs-dismiss="modal" id="btnCloseModal">
                                                Fermer
                                            </button>
                                            <button type="button" class="btn btn-info" id="btnPreview">
                                                Prévisualiser
                                            </button>
                                            <button type="submit" class="btn btn-primary" id="btnSaveModal">
                                                Enregistrer
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- previsualisation avant sauvegarde --}}
                        <div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Prévisualisation du Grand Livre</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <iframe id="pdfPreviewFrame" style="width:100%;height:80vh;"
                                            frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- modal pdf --}}
                        <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-xl" style="max-width:90%;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="pdfModalLabel">Prévisualisation du PDF</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body" style="height: 80vh;">
                                        <iframe id="pdfViewer" src="" frameborder="0"
                                            style="width: 100%; height: 100%;"></iframe>
                                    </div>
                                </div>
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
                                                <label for="dobWithTitle" class="form-label">Date de naissance</label>
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
                        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1"
                            aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-danger text-white justify-content-center">
                                        <h5 class="modal-title" id="deleteModalLabel">
                                            <i class="bx bx-error-circle me-2"></i>Confirmer la suppression
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white"
                                            data-bs-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p class="mb-0">
                                            Êtes-vous sûr de vouloir supprimer cette Balance?<br>
                                            Cette action est <strong>irréversible</strong>.
                                        </p>
                                        <p class="fw-bold text-danger mt-2" id="fileNameToDelete"></p>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <form id="deleteForm" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger">Supprimer</button>
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

    @include('components.footer')

    <script>
        const accounting_balanceTiersDeleteUrl = "{{ route('accounting_balance_tiers.destroy', ['id' => '__ID__']) }}";
        const accounting_ledgerpreviewBalanceTiersUrl = "{{ route('accounting_balance_tiers.previewBalanceTiers') }}";

    </script>
    <script src="{{ asset('js/acc_balance_tiers.js') }}"></script>


</body>

</html>
