<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Factures <span class="text-gradient">Produites</span>'])
                <div class="content-wrapper">
<div class="fp-wrapper">
    {{-- HEADER --}}
    <div class="fp-header">
        <div class="fp-header-left">
            <div class="fp-icon-wrap">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="fp-title">Factures Produites</h1>
                <p class="fp-subtitle">Bibliothèque de factures émises par l'entreprise</p>
            </div>
        </div>
        <div class="fp-header-right">
            <button class="btn-fp-primary" onclick="ouvrirModalUpload()">
                <i class="fas fa-plus"></i> Nouvelle Facture
            </button>
            <a href="{{ route('excel_ia.index') }}" class="btn-fp-secondary">
                <i class="fas fa-robot"></i> Analyse IA
            </a>
        </div>
    </div>

    {{-- LISTE DES FACTURES --}}
    <div class="fp-card">
        @if($factures->isEmpty())
            <div class="fp-empty">
                <i class="fas fa-folder-open empty-icon"></i>
                <h3>Aucune facture enregistrée</h3>
                <p>Uploadez vos premières factures produites pour les stocker en sécurité.</p>
                <button class="btn-fp-primary mt-3" onclick="ouvrirModalUpload()">
                    <i class="fas fa-upload"></i> Uploader une facture
                </button>
            </div>
        @else
            <div class="table-responsive">
                <table class="table fp-table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Client / Tiers</th>
                            <th class="text-end">Montant</th>
                            <th>Fichier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factures as $f)
                        <tr>
                            <td>
                                <strong>{{ $f->reference }}</strong>
                                @if($f->injectee_comptaflow)
                                    <span class="badge bg-success ms-2"><i class="fas fa-check"></i> BDD</span>
                                @endif
                            </td>
                            <td>{{ $f->date_facture->format('d/m/Y') }}</td>
                            <td>
                                <div style="font-weight: 500; font-size: 0.9rem;">{{ $f->client_nom ?: 'Non spécifié' }}</div>
                                <div style="font-size: 0.75rem; color: #6b7280;">{{ $f->client_tiers_code }}</div>
                            </td>
                            <td class="text-end font-weight-bold">
                                {{ number_format($f->montant, 0, ',', ' ') }} {{ $f->devise }}
                            </td>
                            <td>
                                <a href="{{ route('factures_produites.download', $f->id) }}" class="fp-file-link">
                                    <i class="fas {{ $f->icon }} text-primary"></i> 
                                    {{ Str::limit($f->nom_fichier_original, 20) }}
                                </a>
                            </td>
                            <td>
                                <button class="btn-icon text-danger" onclick="supprimerFacture({{ $f->id }}, '{{ $f->reference }}')" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $factures->links() }}
            </div>
        @endif
    </div>
</div>{{-- / fp-wrapper --}}

{{-- MODAL UPLOAD --}}
<div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content fp-modal" id="formUpload" enctype="multipart/form-data">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fp-modal-title"><i class="fas fa-cloud-upload-alt text-primary"></i> Uploader Facture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                {{-- Dropzone --}}
                <div class="fp-dropzone mb-4" id="dzFacture" onclick="document.getElementById('inputFichier').click()">
                    <i class="fas fa-file-pdf fp-dz-icon"></i>
                    <p class="mb-1 fw-bold">Sélectionnez le fichier</p>
                    <p class="small text-muted mb-0">PDF, JPG, PNG (Max 10 Mo)</p>
                    <p id="nomFichierChoisi" class="text-primary fw-bold mt-2 d-none"></p>
                </div>
                <input type="file" id="inputFichier" name="fichier" accept=".pdf,.jpg,.jpeg,.png" class="d-none" required>

                {{-- Champs --}}
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fp-label">Date de la facture *</label>
                        <input type="date" name="date_facture" class="form-control fp-input" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fp-label">Montant Total *</label>
                        <div class="input-group">
                            <input type="number" name="montant" class="form-control fp-input" required min="0" step="1">
                            <span class="input-group-text">XOF</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fp-label">Client (Nom complet)</label>
                        <input type="text" name="client_nom" class="form-control fp-input" placeholder="Ex: PHARMACIE ELIEL">
                    </div>
                    <div class="col-12">
                        <label class="form-label fp-label">Code Tiers Client</label>
                        <input type="text" name="client_tiers_code" class="form-control fp-input" placeholder="Ex: 410002">
                    </div>
                    <div class="col-12">
                        <label class="form-label fp-label">Notes (Optionnel)</label>
                        <textarea name="notes" class="form-control fp-input" rows="2"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn-fp-primary" id="btnSaveUpload">Enregistrer la facture</button>
            </div>
        </form>
    </div>
</div>

                    </div>
                    <!-- / Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>

    @include('components.footer')

<style>
/* ═══════════════════════════════════════════════════════
   FACTURES PRODUITES — Design Premium
═══════════════════════════════════════════════════════ */
:root {
    --fp-surface: #ffffff;
    --fp-bg: #f8fafc;
    --fp-border: #e2e8f0;
    --fp-text: #1e293b;
    --fp-muted: #64748b;
    --fp-primary: #10b981; /* Vert émeraude */
    --fp-primary-hover: #059669;
}

body { background-color: var(--fp-bg); }

.fp-wrapper { padding: 32px; max-width: 1400px; margin: 0 auto; font-family: 'Inter', sans-serif; }

/* Header */
.fp-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 16px; }
.fp-header-left { display: flex; align-items: center; gap: 16px; }
.fp-icon-wrap { width: 56px; height: 56px; background: linear-gradient(135deg, #10b981, #34d399); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; box-shadow: 0 8px 24px rgba(16,185,129,0.3); }
.fp-title { font-size: 1.6rem; font-weight: 700; color: var(--fp-text); margin: 0; }
.fp-subtitle { font-size: 0.9rem; color: var(--fp-muted); margin: 0; }
.fp-header-right { display: flex; gap: 12px; }

/* Boutons */
.btn-fp-primary { background: linear-gradient(135deg, var(--fp-primary), #3b82f6); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16,185,129,0.25); transition: all 0.3s; }
.btn-fp-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16,185,129,0.35); color: white; }
.btn-fp-secondary { background: white; border: 1px solid var(--fp-border); color: var(--fp-text); padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s; }
.btn-fp-secondary:hover { border-color: #3b82f6; color: #3b82f6; background: #f8fafc; }
.btn-icon { background: none; border: none; padding: 6px; border-radius: 6px; transition: background 0.2s; }
.btn-icon:hover { background: rgba(239, 68, 68, 0.1); }

/* Card & Tableau */
.fp-card { background: var(--fp-surface); border-radius: 16px; border: 1px solid var(--fp-border); box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; }
.fp-empty { padding: 60px 20px; text-align: center; }
.empty-icon { font-size: 4rem; color: var(--fp-muted); opacity: 0.2; margin-bottom: 20px; }
.fp-empty h3 { color: var(--fp-text); font-weight: 600; }
.fp-empty button { margin: 0 auto; }
.fp-table { margin: 0; white-space: nowrap; }
.fp-table th { background: #f1f5f9; color: var(--fp-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; padding: 16px 24px; border-bottom: 1px solid var(--fp-border); }
.fp-table td { padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: var(--fp-text); font-size: 0.9rem; }
.fp-table tr:last-child td { border-bottom: none; }
.fp-table tr:hover { background-color: #f8fafc; }
.fp-file-link { color: #3b82f6; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; padding: 4px 12px; border-radius: 20px; transition: background 0.2s; }
.fp-file-link:hover { background: #dbeafe; color: #1d4ed8; }

/* Modal & Form */
.fp-modal { border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
.fp-modal-title { font-weight: 700; font-size: 1.2rem; }
.fp-label { font-size: 0.8rem; font-weight: 600; color: var(--fp-muted); margin-bottom: 6px; }
.fp-input { border-radius: 8px; border: 1px solid var(--fp-border); padding: 10px 14px; font-size: 0.9rem; }
.fp-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.fp-dropzone { border: 2px dashed var(--fp-border); border-radius: 12px; padding: 30px 20px; text-align: center; cursor: pointer; transition: all 0.2s; background: #f8fafc; }
.fp-dropzone:hover { border-color: #3b82f6; background: #eff6ff; }
.fp-dz-icon { font-size: 2.5rem; color: #94a3b8; margin-bottom: 10px; }
.fp-dropzone:hover .fp-dz-icon { color: #3b82f6; }
</style>

<script>
// Affichage fichier sélectionné
document.getElementById('inputFichier').addEventListener('change', function(e) {
    const fn = document.getElementById('nomFichierChoisi');
    if (this.files.length > 0) {
        fn.textContent = this.files[0].name;
        fn.classList.remove('d-none');
    } else {
        fn.classList.add('d-none');
    }
});

function ouvrirModalUpload() {
    new bootstrap.Modal(document.getElementById('modalUpload')).show();
}

// Upload via AJAX
document.getElementById('formUpload').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveUpload');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

    try {
        const rep = await fetch('{{ route("factures_produites.store") }}', {
            method: 'POST',
            body: new FormData(this)
        });
        const data = await rep.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert('Erreur: ' + (data.message || 'Validation échouée'));
            btn.disabled = false;
            btn.innerHTML = 'Enregistrer la facture';
        }
    } catch (err) {
        alert('Erreur réseau');
        btn.disabled = false;
        btn.innerHTML = 'Enregistrer la facture';
    }
});

// Suppression
async function supprimerFacture(id, ref) {
    if (!confirm('Voulez-vous vraiment supprimer la facture : ' + ref + ' ?')) return;

    try {
        const rep = await fetch('/factures-produites/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await rep.json();
        if (data.success) {
            window.location.reload();
        }
    } catch(err) {
        alert('Erreur de suppression');
    }
}
</script>
</body>
</html>
