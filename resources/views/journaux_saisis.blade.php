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
                            <!-- Page Header -->
                            <div class="text-center mb-5">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; border-radius: 20px; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);">
                                    <i class="bx bx-book-content text-white" style="font-size: 32px;"></i>
                                </div>
                                <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Journaux Saisis</h1>
                                <p class="text-muted mb-0" style="font-size: 1.1rem;"><i class="bx bx-info-circle me-1"></i>Détails des journaux pour l'exercice sélectionné</p>
                            </div>

                            <div class="mb-3">
                                <a href="javascript:history.back()" class="btn btn-sm" style="background: linear-gradient(135deg, #90a4ae 0%, #607d8b 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(96, 125, 139, 0.3);">
                                    <i class='bx bx-arrow-back me-1'></i> Retour
                                </a>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <div class="badge px-3 py-2 rounded" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 0.9rem; font-weight: 600;">
                                    Début : {{ $data['date_debut'] ?? 'N/A' }}
                                </div>

                                <div class="badge px-3 py-2 rounded" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; font-size: 0.9rem; font-weight: 600;">
                                    Fin : {{ $data['date_fin'] ?? 'N/A' }}
                                </div>

                                <div class="badge px-3 py-2 rounded" style="background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%); color: white; font-size: 0.9rem; font-weight: 600;">
                                    {{ $data['intitule'] ?? 'N/A' }}
                                </div>
                            </div>


                            <!-- Section table -->
                            <div class="card" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
                                <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-bottom: 2px solid #e7e9ed; padding: 1.5rem;">
                                    <h5 class="mb-0" style="font-weight: 700; color: #566a7f; font-size: 1.25rem;"><i class="bx bx-list-ul me-2"></i>Journaux de saisis</h5>
                                    <div>
                                        <button class="btn btn-sm me-2" data-bs-toggle="collapse"
                                            data-bs-target="#filterPanel" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(79, 172, 254, 0.3);">
                                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                                        </button>
                                         <button type="button" class="btn btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalCenterCreate" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);">
                                            <i class="bx bx-plus me-1"></i> Nouvelle écriture
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
                                <div class="table-responsive text-nowrap" style="padding: 1.5rem;">
                                <table class="table table-hover align-middle" id="journauxTable" style="border-radius: 8px; overflow: hidden;">
                                    <thead style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                        <tr>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Année</th>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Mois</th>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Code journal</th>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Intitulé</th>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;">Type</th>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; text-align: center;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($journaux as $journal)
                                            <tr>
                                                <td style="padding: 1rem; color: #566a7f;">{{ $journal->annee }}</td>

                                                <td data-month="{{ $journal->mois }}" style="padding: 1rem; color: #566a7f;">
                                                    {{ \Carbon\Carbon::createFromDate(null, $journal->mois ?? 1)->locale('fr')->monthName }}
                                                </td>


                                                <td style="padding: 1rem; color: #566a7f;">{{ $journal->codeJournal->code_journal ?? 'N/A' }}</td>
                                                <td style="padding: 1rem; color: #566a7f;">{{ $journal->codeJournal->intitule ?? 'N/A' }}</td>
                                                <td style="padding: 1rem; font-weight: 600; color: #667eea;">{{ $journal->codeJournal->type ?? 'N/A' }}</td>

                                                <td style="padding: 1rem; text-align: center;">
                                                    <div class="d-flex gap-2 justify-content-center">

                                                        <!-- Bouton envoi de données -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-icon show-accounting-journals"
                                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);"
                                                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)'"
                                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)'"
                                                            data-bs-placement="top" title="Afficher les journaux"
                                                            data-id="{{ $journal->id }}"
                                                            data-annee="{{ $journal->annee }}"
                                                            data-mois="{{ $journal->mois }}"
                                                            data-exercices_comptables_id="{{ $journal->exercices_comptables_id }}"
                                                            data-code_journals_id="{{ $journal->code_journals_id }}"
                                                            data-code_journal="{{ optional($journal->codeJournal)->code_journal }}"
                                                            data-compte_de_contrepartie="{{ optional($journal->codeJournal)->compte_de_contrepartie }}"
                                                            data-compte_de_tresorerie="{{ optional($journal->codeJournal)->compte_de_tresorerie }}"
                                                            data-rapprochement_sur="{{ optional($journal->codeJournal)->rapprochement_sur }}"
                                                            data-traitement="{{ optional($journal->codeJournal)->traitement_analytique }}"
                                                            data-intitule="{{ optional($journal->codeJournal)->intitule }}"
                                                            data-type="{{ optional($journal->codeJournal)->type }}">
                                                            <i class='bx bx-pencil' style="font-size: 18px;"></i>
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
