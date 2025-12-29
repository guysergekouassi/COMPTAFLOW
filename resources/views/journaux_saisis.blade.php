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

                            <div class="mb-3">
                                <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
                                    <i class='bx  bx-reply-stroke'></i>
                                </a>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <div class="badge bg-primary text-white px-3 py-2 rounded">
                                    {{ $data['date_debut'] ?? 'N/A' }}
                                </div>

                                <div class="badge bg-secondary text-white px-3 py-2 rounded">
                                    {{ $data['date_fin'] ?? 'N/A' }}
                                </div>

                                <div class="badge bg-warning text-dark px-3 py-2 rounded">
                                    {{ $data['intitule'] ?? 'N/A' }}
                                </div>
                            </div>


                            <!-- Section table -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Journaux de saisis</h5>
                                    <div>
                                        <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                         <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate">
                                            Nouvelle écriture
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtre personnalisé -->
                                <div class="collapse px-3 pt-2" id="filterPanel">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <input type="number" id="filter-annee" class="form-control"
                                                placeholder="Filtrer par Année" />
                                        </div>
                                        <div class="col-md-3">
                                            <select id="filter-mois" class="form-control">
                                                <option value="">Filtrer par Mois</option>
                                                @foreach (range(1, 12) as $m)
                                                    <option value="{{ $m }}">
                                                        {{ \Carbon\Carbon::createFromDate(null, $m)->locale('fr')->monthName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-code" class="form-control"
                                                placeholder="Filtrer par Code Journal" />
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" id="filter-type" class="form-control"
                                                placeholder="Filtrer par Type" />
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <button class="btn btn-primary w-100" id="apply-filters">Appliquer les
                                                filtres</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-secondary w-100" id="reset-filters">Réinitialiser les
                                                filtres</button>
                                        </div>
                                    </div>
                                </div>



                                <!-- Table -->

                                <script>
                                    $(document).ready(function() {
                                        $('#journauxTable').DataTable({
                                            pageLength: 5,
                                            lengthMenu: [5, 10, 15, 20, 25],
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
                                <table class="table" id="journauxTable">
                                    <thead>
                                        <tr>
                                            <th>Année</th>
                                            <th>Mois</th>
                                            <th>Code journal</th>
                                            <th>Intitulé</th>
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($journaux as $journal)
                                            <tr>
                                                <td>{{ $journal->annee }}</td>

                                                <td data-month="{{ $journal->mois }}">
                                                    {{ \Carbon\Carbon::createFromDate(null, $journal->mois ?? 1)->locale('fr')->monthName }}
                                                </td>


                                                <td>{{ $journal->codeJournal->code_journal ?? 'N/A' }}</td>
                                                <td>{{ $journal->codeJournal->intitule ?? 'N/A' }}</td>
                                                <td>{{ $journal->codeJournal->type ?? 'N/A' }}</td>

                                                <td>
                                                    <div class="d-flex gap-2">

                                                        <!-- Bouton envoi de données -->
                                                        <button type="button"
                                                            class="btn p-0 border-0 bg-transparent text-success show-accounting-journals"
                                                            data-bs-placement="top" title="Afficher les journaux"
                                                            data-id="{{ $journal->id }}"
                                                            data-annee="{{ $journal->annee }}"
                                                            data-mois="{{ $journal->mois }}"
                                                            data-exercices_comptables_id="{{ $journal->exercices_comptables_id }}"
                                                            data-code_journals_id="{{ $journal->code_journals_id }}"
                                                            data-code_journal="{{ $journal->codeJournal->code_journal }}"
                                                            data-compte_de_contrepartie="{{ $journal->codeJournal->compte_de_contrepartie }}"
                                                            data-compte_de_tresorerie="{{ $journal->codeJournalcompte_de_tresorerie }}"
                                                            data-rapprochement_sur="{{ $journal->codeJournal->rapprochement_sur }}"
                                                            data-traitement="{{ $journal->codeJournal->traitement_analytique }}"
                                                            data-intitule="{{ $journal->codeJournal->intitule }}"
                                                            data-type="{{ $journal->codeJournal->type }}">
                                                            <i class='bx  bx-pencil'></i>
                                                        </button>

                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Aucun journal saisi trouvé.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>



                            </div>



                            <!-- MODAL HTML -->



                            <!-- Modal Creation plan update-->


                            <!-- Modal Confirmation de suppression -->

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
            const accounting_entry_realSaisisUrl = "{{ route('accounting_entry_real') }}";
        </script>
        <script src="{{ asset('js/journaux_saisis.js') }}"></script>

</body>

</html>
