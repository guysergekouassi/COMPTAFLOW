@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .staging-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
    }

    .staging-table-container {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .table-staging thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-staging tbody td {
        padding: 0.75rem 1rem;
        font-size: 0.825rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .cell-error {
        background-color: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
        color: #b91c1c !important;
        position: relative;
    }

    .cell-error::after {
        content: "\f06a";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.7rem;
        opacity: 0.5;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .step-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.875rem;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Importation / <span class="text-primary">' . $importTitle . '</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-white p-6 rounded-[24px] shadow-sm d-flex align-items-center justify-content-between border border-slate-100">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="step-indicator bg-primary text-white">3</div>
                                        <div>
                                            <h4 class="font-black mb-1 text-slate-900">{{ $importTitle }} : Revue</h4>
                                            <p class="text-slate-500 mb-0">
                                                Fichier : <strong class="text-primary">{{ $import->file_name }}</strong> 
                                                <span class="mx-2 text-slate-300">|</span> 
                                                Importé le : <strong class="text-slate-700">{{ $import->created_at->format('d/m/Y à H:i') }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-xs font-bold text-slate-400 uppercase mb-1">Total lignes analysées</div>
                                        <div class="h3 font-black mb-0 text-primary">{{ count($import->raw_data) - 1 }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-3">
                                <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                                    <div class="text-xs font-bold text-emerald-600 uppercase mb-1">Lignes Valides</div>
                                    <div class="h4 font-black text-emerald-700 mb-0">{{ $validCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100">
                                    <div class="text-xs font-bold text-rose-600 uppercase mb-1">Erreurs détectées</div>
                                    <div class="h4 font-black text-rose-700 mb-0">{{ $errorCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-4 rounded-2xl border border-slate-100">
                                    <div class="text-xs font-bold text-slate-400 uppercase mb-2">Légende</div>
                                    <div class="d-flex gap-4">
                                        <div class="text-xs d-flex align-items-center gap-2">
                                            <span class="status-indicator bg-emerald-500"></span> Prêt à l'import
                                        </div>
                                        <div class="text-xs d-flex align-items-center gap-2">
                                            <span class="status-indicator bg-rose-500"></span> Erreur bloquante
                                        </div>
                                        <div class="text-xs d-flex align-items-center gap-2">
                                            <span class="status-indicator bg-warning"></span> Attention / Hors OHADA
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="staging-card p-0 overflow-hidden mb-6">
                            <div class="staging-table-container">
                                <div class="table-responsive" style="max-height: 500px;">
                                    <table class="table table-staging mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">STATUT</th>
                                                @if($import->type == 'initial')
                                                    <th>NUMÉRO DE COMPTE</th>
                                                    <th>INTITULÉ DU COMPTE</th>
                                                @elseif($import->type == 'journals')
                                                    <th>CODE JOURNAL</th>
                                                    <th>INTITULÉ DU JOURNAL</th>
                                                    <th>TYPE</th>
                                                    <th>COMPTE</th>
                                                    <th>ANALYTIQUE</th>
                                                    <th>RAPPROCHEMENT</th>
                                                @elseif($import->type == 'tiers')
                                                    <th>N° TIERS / IDENTIFIANT</th>
                                                    <th>NOM / INTITULÉ</th>
                                                    <th>CATÉGORIE</th>
                                                    <th>COMPTE GÉNÉRAL</th>
                                                @else
                                                    <th>DATE</th>
                                                    <th>JOURNAL</th>
                                                    <th>RÉFÉRENCE</th>
                                                    <th>COMPTE</th>
                                                    <th>LIBELLÉ</th>
                                                    <th class="text-end">DÉBIT</th>
                                                    <th class="text-end">CRÉDIT</th>
                                                @endif
                                                <th class="text-center">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $mapping = $import->mapping;
                                            @endphp

                                            @foreach(array_slice($rowsWithStatus, 0, 50) as $rowIndex => $row)
                                                <tr>
                                                    <td class="text-center">
                                                        <span class="status-indicator {{ $row['status'] == 'valid' ? 'bg-emerald-500' : 'bg-rose-500' }}" 
                                                              title="{{ implode(', ', $row['errors']) }}"></span>
                                                    </td>
                                                    
                                                    @if($import->type == 'initial')
                                                        <td class="@if($row['status'] == 'error') cell-error @endif">
                                                            {{ $row['data']['numero_de_compte'] ?? '-' }}
                                                        </td>
                                                        <td>{{ $row['data']['intitule'] ?? '-' }}</td>
                                                    @elseif($import->type == 'journals')
                                                        <td class="@if($row['status'] == 'error') cell-error @endif">
                                                            {{ $row['data']['code_journal'] ?? '-' }}
                                                        </td>
                                                        <td>{{ $row['data']['intitule'] ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge bg-label-info">
                                                                {{ $row['data']['type'] ?? 'Achats' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $row['data']['compte_de_tresorerie'] ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                                {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'OUI' : 'NON' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $row['data']['rapprochement_sur'] ?? '-' }}</td>
                                                        <td class="@if($row['status'] == 'error' && empty($row['data'][$mapping['numero_de_tiers'] ?? '']) && empty($row['data']['auto_num'])) cell-warning @endif">
                                                            @if(!empty($row['data']['auto_num']))
                                                                <span class="text-primary font-black"><i class="fa-solid fa-magic me-1"></i> {{ $row['data']['auto_num'] }}</span>
                                                            @elseif(!empty($row['data'][$mapping['numero_de_tiers'] ?? '']))
                                                                {{ $row['data'][$mapping['numero_de_tiers']] }}
                                                            @else
                                                                <span class="badge bg-label-warning italic text-[10px]">Sera auto-généré</span>
                                                            @endif
                                                        </td>
                                                        <td class="fw-bold">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge bg-label-primary">
                                                                {{ $row['data']['type_de_tiers'] ?? 'Client' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $row['data']['compte_general'] ?? '-' }}</td>
                                                    @else
                                                        <td>{{ $row['data']['jour'] ?? '-' }}</td>
                                                        <td>{{ $row['data']['journal'] ?? '-' }}</td>
                                                        <td>{{ $row['data']['reference'] ?? '-' }}</td>
                                                        <td class="@if(in_array('error', $row['errors']) || str_contains(implode(' ', $row['errors']), 'Compte')) cell-error @endif" 
                                                            title="{{ implode(', ', array_filter($row['errors'], fn($e) => str_contains($e, 'Compte'))) }}">
                                                            {{ $row['data']['compte'] ?? '-' }}
                                                        </td>
                                                        <td>{{ Str::limit($row['data']['libelle'] ?? '-', 30) }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                                                    @endif

                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center">
                                                            @if(!in_array($import->type, ['initial', 'journals']) && $row['status'] == 'error' && str_contains(implode(' ', $row['errors']), 'Compte'))
                                                                <button class="btn btn-icon btn-sm btn-label-success rounded-pill me-1" 
                                                                        onclick='quickCreateAccount(@json($row["data"]["compte"] ?? null), @json($row["data"]["libelle"] ?? null))'
                                                                        title="Créer ce compte à la volée">
                                                                    <i class="fa-solid fa-plus-circle"></i>
                                                                </button>
                                                            @endif
                                                            <button class="btn btn-icon btn-sm btn-label-primary rounded-pill me-1" 
                                                                    onclick='editStagingRow(@json($import->id), {{ $row["index"] }}, @json($row["data"]), @json($mapping))'
                                                                    title="Modifier cette ligne">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </button>
                                                            <button class="btn btn-icon btn-sm btn-label-info rounded-pill" 
                                                                    onclick='showRowDetails(@json($row["data"]), @json($row["errors"]))'
                                                                    title="Voir les détails">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-icon btn-sm btn-label-danger rounded-pill ms-1" 
                                                                    onclick="deleteStagingRow({{ $import->id }}, {{ $row['index'] }})"
                                                                    title="Supprimer cette ligne de l'import">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if(count($rowsWithStatus) > 50)
                            <div class="p-4 bg-slate-50 text-center border-top">
                                <span class="text-xs text-slate-500">Affichage limité aux 50 premières lignes pour la revue. Toutes les <strong>{{ count($rowsWithStatus) }} lignes</strong> seront traitées lors de la validation.</span>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center bg-white p-6 rounded-[24px] shadow-sm border border-slate-100">
                            <div>
                                <a href="{{ route('admin.import.mapping', $import->id) }}" class="btn btn-outline-secondary rounded-xl px-6 py-3">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Retour au mapping
                                </a>
                            </div>
                            <div class="d-flex gap-3">
                                <form action="{{ route('admin.import.cancel', $import->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment annuler cette importation ? Toutes les données temporaires seront supprimées.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-label-danger rounded-xl px-6 py-3 border-0">
                                        <i class="fa-solid fa-trash me-2"></i> Annuler l'import
                                    </button>
                                </form>
                                <form action="{{ route('admin.import.commit', $import->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary rounded-xl px-10 py-3 font-bold shadow-lg shadow-primary/20">
                                        <i class="fa-solid fa-cloud-arrow-down me-2"></i> Lancer la migration finale
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteStagingRow(importId, rowIndex) {
            Swal.fire({
                title: 'Supprimer cette ligne ?',
                text: "Cette action retirera définitivement la ligne de l'importation en cours.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/config/import/delete-row/${importId}/${rowIndex}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    });
                }
            });
        }

        function editStagingRow(importId, rowIndex, rawRowData, mapping) {
            let html = '<div class="text-start">';
            
            // On crée un champ pour chaque colonne mappée
            Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                if (colIndex === null || colIndex === "") return;
                
                let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                let val = rawRowData[colIndex] || "";
                
                html += `<div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">${label}</label>
                            <input type="text" class="form-control swal-edit-input" data-col="${colIndex}" value="${val}">
                         </div>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Modifier la ligne',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Enregistrer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    let values = {};
                    document.querySelectorAll('.swal-edit-input').forEach(input => {
                        values[input.dataset.col] = input.value;
                    });
                    return values;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/config/import/update-row/${importId}/${rowIndex}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ values: result.value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    });
                }
            });
        }

        function showRowDetails(data, errors) {
            let dataHtml = '<div class="text-start"><pre style="background: #f1f5f9; padding: 1rem; border-radius: 8px; font-size: 0.75rem;">' + JSON.stringify(data, null, 2) + '</pre>';
            dataHtml += '<hr><strong class="text-slate-700">Erreurs détectées :</strong><br>';
            
            if (errors && errors.length > 0) {
                dataHtml += '<ul class="mt-2 mb-0">';
                errors.forEach(err => {
                    dataHtml += '<li class="text-danger small">' + err + '</li>';
                });
                dataHtml += '</ul>';
            } else {
                dataHtml += '<p class="text-emerald-600 small mb-0">Aucune erreur détectée.</p>';
            }
            dataHtml += '</div>';

            Swal.fire({
                title: 'Détails de la ligne',
                html: dataHtml,
                icon: 'info',
                confirmButtonText: 'Fermer',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4'
                },
                buttonsStyling: false
            });
        }

        function quickCreateAccount(numero, libelle) {
            Swal.fire({
                title: 'Création du compte',
                text: `Voulez-vous créer le compte ${numero} - ${libelle} ?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, créer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("{{ route('admin.import.quick_account') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            numero_compte: numero,
                            intitule: libelle,
                            type_de_compte: 'Bilan'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Succès',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Erreur', 'Une erreur est survenue lors de la création.', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>
