<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => "Centre d'Approbation"])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1 text-premium-gradient">Centre d'Approbation</h5>
                                <p class="text-muted small mb-0">Validez ou rejetez les demandes en attente de traitement</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="nav-align-top mb-4">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pending" aria-controls="navs-pending" aria-selected="true">
                                                En attente ({{ $pendingApprovals->count() }})
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-history" aria-controls="navs-history" aria-selected="false">
                                                Historique
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content border-0 bg-transparent p-0 pt-4">
                                        <div class="tab-pane fade show active" id="navs-pending" role="tabpanel">
                                            @if($pendingApprovals->isNotEmpty())
                                                <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-label-warning rounded-3 border border-warning">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md bg-warning text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                            <i class="fa-solid fa-bell fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark">Actions requises</h6>
                                                            <p class="mb-0 small text-muted">Vous avez {{ $pendingApprovals->count() }} demande(s) en attente de traitement.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                                                            <div class="row g-4">
                                                @forelse($pendingApprovals as $approval)
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="glass-card h-100 d-flex flex-column border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                                                            <div class="p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-bottom: 1px solid rgba(0,0,0,0.05);">
                                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                                    <span class="badge {{ $approval->type === 'accounting_entry' ? 'bg-label-primary text-primary' : 'bg-label-info text-info' }} px-3 py-2 rounded-pill fw-bold" style="font-size: 0.7rem;">
                                                                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ strtoupper(str_replace('_', ' ', $approval->type)) }}
                                                                    </span>
                                                                    <div class="text-end">
                                                                        <div class="small fw-bold text-dark"><i class="fa-regular fa-calendar me-1"></i> {{ $approval->created_at->format('d/m/Y') }}</div>
                                                                        <div class="small text-muted"><i class="fa-regular fa-clock me-1"></i> {{ $approval->created_at->format('H:i') }}</div>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar avatar-md me-3">
                                                                        <div class="avatar-initial rounded-circle bg-label-secondary text-dark fw-bold">
                                                                            {{ substr($approval->requester->name ?? 'S', 0, 1) }}
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-bold text-dark fs-6">{{ $approval->requester->name ?? 'Système' }}</div>
                                                                        <small class="text-muted"><i class="fa-solid fa-user-tag me-1"></i> {{ $approval->requester->role ?? 'Collaborateur' }}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="p-4 flex-grow-1">
                                                                @if($approval->type === 'accounting_entry')
                                                                    <div class="p-3 bg-label-light rounded-3 mb-3 border border-dashed">
                                                                        <div class="d-flex justify-content-between mb-2">
                                                                            <span class="text-muted small">N° Saisie</span>
                                                                            <span class="fw-bold text-primary">{{ $approval->data['n_saisie'] ?? 'N/A' }}</span>
                                                                        </div>
                                                                        @php
                                                                            // Chercher les écritures avec le numéro de saisie
                                                                            $ecrituresForTotal = \App\Models\EcritureComptable::where(function($query) use ($approval) {
                                                                                $nSaisie = $approval->data['n_saisie'] ?? '';
                                                                                $query->where('n_saisie', $nSaisie)
                                                                                      ->orWhere('n_saisie_user', $nSaisie);
                                                                            })->get();
                                                                            $totalDebit = $ecrituresForTotal->sum('debit');
                                                                        @endphp
                                                                        <div class="d-flex justify-content-between">
                                                                            <span class="text-muted small">Montant Total</span>
                                                                            <span class="fw-bold text-dark">{{ number_format($totalDebit, 0, ',', ' ') }} FCFA</span>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                @if($approval->data)
                                                                    <div class="small text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Détails de la demande</div>
                                                                    <ul class="list-unstyled mb-0">
                                                                        @foreach(array_slice($approval->data, 0, 4) as $key => $value)
                                                                            <li class="mb-1 d-flex justify-content-between small">
                                                                                <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }} :</span>
                                                                                <span class="fw-medium text-dark ms-2">{{ is_array($value) ? '...' : $value }}</span>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>

                                                            <div class="p-3 p-4 pt-0">
                                                                <div class="d-flex gap-2 mb-2">
                                                                    <button class="btn btn-primary w-100 shadow-sm py-2" onclick="openDetailsModal('{{ $approval->id }}')">
                                                                        <i class="fa-solid fa-eye me-2"></i> Détails
                                                                    </button>
                                                                    <a href="{{ route('accounting_entry_real', ['approval_edit' => $approval->id]) }}" class="btn btn-warning w-100 shadow-sm py-2">
                                                                        <i class="fa-solid fa-pen-to-square me-2"></i> Modifier
                                                                    </a>
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <form action="{{ route('admin.approvals.approve', $approval->id) }}" method="POST" class="w-100">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-success w-100 shadow-sm py-2">
                                                                            <i class="fa-solid fa-check-circle me-2"></i> Valider
                                                                        </button>
                                                                    </form>
                                                                    <button class="btn btn-outline-danger w-100 shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $approval->id }}">
                                                                        <i class="fa-solid fa-times-circle me-2"></i> Rejeter
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Reject Modal -->
                                                        <div class="modal fade" id="rejectModal{{ $approval->id }}" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered">
                                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                                                    <form action="{{ route('admin.approvals.reject', $approval->id) }}" method="POST">
                                                                        @csrf
                                                                        <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                                                            <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-ban me-2"></i> Rejeter l'écriture</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body p-4">
                                                                            <div class="mb-4">
                                                                                <label class="form-label fw-bold text-dark mb-3">Sélectionnez le motif du rejet :</label>
                                                                                <div class="d-flex flex-column gap-2">
                                                                                    <div class="form-check custom-option custom-option-basic">
                                                                                        <label class="form-check-label custom-option-content p-2 border rounded-3 w-100 cursor-pointer hover-bg-light" for="motif1-{{$approval->id}}">
                                                                                            <input type="radio" name="comment" value="Libellé incorrect" class="form-check-input" id="motif1-{{$approval->id}}" checked>
                                                                                            <span class="ms-2">Libellé incorrect</span>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div class="form-check custom-option custom-option-basic">
                                                                                        <label class="form-check-label custom-option-content p-2 border rounded-3 w-100 cursor-pointer hover-bg-light" for="motif2-{{$approval->id}}">
                                                                                            <input type="radio" name="comment" value="Comptes incorrects" class="form-check-input" id="motif2-{{$approval->id}}">
                                                                                            <span class="ms-2">Comptes incorrects</span>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div class="form-check custom-option custom-option-basic">
                                                                                        <label class="form-check-label custom-option-content p-2 border rounded-3 w-100 cursor-pointer hover-bg-light" for="motif3-{{$approval->id}}">
                                                                                            <input type="radio" name="comment" value="Comptes imprécis" class="form-check-input" id="motif3-{{$approval->id}}">
                                                                                            <span class="ms-2">Comptes imprécis</span>
                                                                                        </label>
                                                                                    </div>
                                                                                    <div class="form-check custom-option custom-option-basic">
                                                                                        <label class="form-check-label custom-option-content p-2 border rounded-3 w-100 cursor-pointer hover-bg-light" for="motif4-{{$approval->id}}">
                                                                                            <input type="radio" name="comment" value="Autre" class="form-check-input" id="motif4-{{$approval->id}}" onchange="toggleComment('{{$approval->id}}')">
                                                                                            <span class="ms-2">Autre motif</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div id="otherCommentDiv-{{$approval->id}}" style="display: none;">
                                                                                <label class="form-label fw-bold small">Précisez le motif :</label>
                                                                                <textarea id="otherComment-{{$approval->id}}" class="form-control bg-light" rows="2" placeholder="Saisissez le motif précis..."></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                                                                            <button type="button" class="btn btn-label-secondary w-100" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $approval->id }}">Annuler</button>
                                                                            <button type="button" onclick="submitRejection('{{$approval->id}}')" class="btn btn-danger w-100 shadow-sm py-2">
                                                                                <i class="fa-solid fa-check me-1"></i> Confirmer le rejet
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 py-5">
                                                        <div class="glass-card p-5 text-center opacity-75">
                                                            <div class="mb-4">
                                                                <i class="fa-solid fa-clipboard-check fa-4x text-success opacity-50"></i>
                                                            </div>
                                                            <h4 class="fw-bold text-dark">Tout est en ordre !</h4>
                                                            <p class="text-muted mb-0">Aucune demande d'approbation en attente pour le moment.</p>
                                                        </div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="navs-history" role="tabpanel">
                                            <div class="glass-card overflow-hidden">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th class="ps-4">Demande</th>
                                                                <th>Par</th>
                                                                <th>Traité par</th>
                                                                <th>Date</th>
                                                                <th>Statut</th>
                                                                <th class="pe-4">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($history as $record)
                                                                <tr>
                                                                    <td class="ps-4">
                                                                        <span class="fw-bold">{{ strtoupper($record->type) }}</span>
                                                                    </td>
                                                                    <td>{{ $record->requester->name ?? 'N/A' }}</td>
                                                                    <td>{{ $record->handler->name ?? 'N/A' }}</td>
                                                                    <td>{{ $record->updated_at->format('d/m/Y H:i') }}</td>
                                                                    <td>
                                                                        <span class="badge-premium {{ $record->status == 'approved' ? 'badge-premium-success' : 'badge-premium-danger' }}">
                                                                            {{ $record->status == 'approved' ? 'Approuvé' : 'Rejeté' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="pe-4">
                                                                        <button class="btn btn-sm btn-icon btn-label-secondary" title="Détails" data-bs-toggle="tooltip">
                                                                            <i class="fa-solid fa-circle-info"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
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
    </div>
    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-label-primary p-4">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-eye me-2"></i> Détails complets de la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="modalLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Chargement des détails...</p>
                    </div>
                    <div id="modalContent" style="display: none;">
                        <div class="p-4 border-bottom">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase fw-bold">Demandeur</small>
                                    <h6 id="detailRequester" class="fw-bold mb-3"></h6>
                                    
                                    <small class="text-muted text-uppercase fw-bold">Type</small>
                                    <h6 id="detailType" class="fw-bold mb-0"></h6>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Compte</th>
                                        <th>Tiers</th>
                                        <th>Trésorerie</th>
                                        <th>Libellé</th>
                                        <th class="text-end">Débit</th>
                                        <th class="text-end">Crédit</th>
                                    </tr>
                                </thead>
                                <tbody id="detailTableBody">
                                </tbody>
                                <tfoot class="bg-label-secondary">
                                    <tr>
                                        <th colspan="4" class="text-end fw-bold">TOTAUX</th>
                                        <th id="detailTotalDebit" class="text-end fw-bold"></th>
                                        <th id="detailTotalCredit" class="text-end fw-bold"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="detailAttachment" class="p-4 border-top bg-light" style="display: none;">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-paperclip me-2"></i>Pièce Justificative</h6>
                            <div id="attachmentContent"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a href="#" id="btnModifyModal" class="btn btn-warning"><i class="fa-solid fa-pen-to-square me-2"></i> Modifier</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDetailsModal(id) {
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            document.getElementById('modalLoader').style.display = 'block';
            document.getElementById('modalContent').style.display = 'none';
            modal.show();

            fetch(`/admin/approvals/${id}/details`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        document.getElementById('detailRequester').innerText = data.requester;
                        document.getElementById('detailType').innerText = data.type.toUpperCase().replace('_', ' ');
                        document.getElementById('btnModifyModal').href = `/accounting_entry_real?approval_edit=${id}`;

                        const tbody = document.getElementById('detailTableBody');
                        tbody.innerHTML = '';
                        let tDebit = 0, tCredit = 0;
                        let attachment = null;

                        data.ecritures.forEach(e => {
                            tDebit += parseFloat(e.debit || 0);
                            tCredit += parseFloat(e.credit || 0);
                            if(e.piece_justificatif) attachment = e.piece_justificatif;

                            const tr = `
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>${e.plan_comptable ? e.plan_comptable.numero_de_compte + ' - ' + e.plan_comptable.intitule : '-'}</span>
                                            ${e.plan_comptable && e.plan_comptable.numero_original ? `<div class="text-[10px] text-slate-400 italic mt-1 font-medium"><i class="fa-solid fa-file-import text-[8px] me-1"></i>Orig: ${e.plan_comptable.numero_original}</div>` : ''}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>${e.plan_tiers ? e.plan_tiers.numero_de_tiers + ' - ' + e.plan_tiers.intitule : '-'}</span>
                                            ${e.plan_tiers && e.plan_tiers.numero_original ? `<div class="text-[10px] text-slate-400 italic mt-1 font-medium"><i class="fa-solid fa-file-import text-[8px] me-1"></i>Orig: ${e.plan_tiers.numero_original}</div>` : ''}
                                        </div>
                                    </td>
                                    <td>
                                        ${e.compte_tresorerie ? `<span class="badge bg-label-info">${e.compte_tresorerie.name}</span>` : '<span class="text-muted">-</span>'}
                                    </td>
                                    <td>
                                        ${(() => {
                                            const desc = e.description_operation || '';
                                            const num = (e.plan_comptable && e.plan_comptable.numero_de_compte) ? e.plan_comptable.numero_de_compte : '';
                                            const intitule = (e.plan_comptable && e.plan_comptable.intitule) ? e.plan_comptable.intitule.toUpperCase() : '';
                                            const isVatLine = num.startsWith('443') || num.startsWith('445') || (num.startsWith('44') && intitule.includes('TVA'));
                                            const hasPrefix = desc.toUpperCase().startsWith('TVA');
                                            return (isVatLine && !hasPrefix) ? 'TVA / ' + desc : desc;
                                        })()}
                                    </td>
                                    <td class="text-end text-success fw-medium">${new Intl.NumberFormat('fr-FR').format(e.debit)}</td>
                                    <td class="text-end text-danger fw-medium">${new Intl.NumberFormat('fr-FR').format(e.credit)}</td>
                                </tr>
                            `;
                            tbody.innerHTML += tr;
                        });

                        document.getElementById('detailTotalDebit').innerText = new Intl.NumberFormat('fr-FR').format(tDebit);
                        document.getElementById('detailTotalCredit').innerText = new Intl.NumberFormat('fr-FR').format(tCredit);

                        const attachDiv = document.getElementById('detailAttachment');
                        const attachContent = document.getElementById('attachmentContent');
                        if (attachment) {
                            attachDiv.style.display = 'block';
                            attachContent.innerHTML = `<a href="/justificatifs/${attachment}" target="_blank" class="btn btn-outline-primary"><i class="fa-solid fa-file-pdf me-2"></i> Voir le document</a>`;
                        } else {
                            attachDiv.style.display = 'none';
                        }

                        document.getElementById('modalLoader').style.display = 'none';
                        document.getElementById('modalContent').style.display = 'block';
                    } else {
                        alert(data.message);
                        modal.hide();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Erreur lors du chargement des détails");
                    modal.hide();
                });
        }

        function toggleComment(id) {
            const radioOther = document.getElementById('motif4-' + id);
            const otherDiv = document.getElementById('otherCommentDiv-' + id);
            if (radioOther.checked) {
                otherDiv.style.display = 'block';
            } else {
                otherDiv.style.display = 'none';
            }
        }

        function submitRejection(id) {
            const form = document.querySelector(`#rejectModal${id} form`);
            const radioOther = document.getElementById('motif4-' + id);
            
            if (radioOther.checked) {
                const otherInput = document.getElementById('otherComment-' + id);
                if (!otherInput.value.trim()) {
                    alert('Veuillez préciser le motif du rejet.');
                    return;
                }
                // Update the value of motif4 to the custom text
                radioOther.value = otherInput.value.trim();
            }
            
            form.submit();
        }

        // Add event listeners for radio buttons to hide 'Other' if needed
        document.querySelectorAll('input[type=radio][name=comment]').forEach(radio => {
            radio.addEventListener('change', function() {
                const id = this.id.split('-')[1];
                if (this.value !== 'Autre') {
                    document.getElementById('otherCommentDiv-' + id).style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
