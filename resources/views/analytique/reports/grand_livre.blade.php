<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<style>
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
    }
    /* ── Modal Premium ── */
    .anl-modal .modal-content {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.12);
    }
    .anl-modal .modal-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        border-radius: 20px 20px 0 0;
        padding: 1.2rem 1.5rem;
    }
    .anl-modal .modal-title { color: #fff; font-weight: 800; font-size: 0.95rem; letter-spacing: 0.02em; }
    .anl-modal .modal-body { padding: 1.5rem; }
    .anl-modal .modal-footer { background: #f8fafc; border-radius: 0 0 20px 20px; border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem; }
    /* Fields */
    .field-label { font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.3rem; display: block; }
    .field-input {
        width: 100%; padding: 0.6rem 1rem; border-radius: 10px;
        border: 1.5px solid #e2e8f0; background: #f8fafc;
        font-size: 0.82rem; font-weight: 500; color: #0f172a;
        transition: all 0.2s;
    }
    .field-input:focus { border-color: #1e40af; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(30,64,175,0.08); }
    /* Checkbox toggle row */
    .toggle-row { display: flex; align-items: center; gap: 0.6rem; padding: 0.5rem 0.8rem; background: #eff6ff; border-radius: 10px; border: 1.5px solid #bfdbfe; cursor: pointer; user-select: none; }
    .toggle-row input[type=checkbox] { accent-color: #1e40af; width: 16px; height: 16px; cursor: pointer; }
    .toggle-row span { font-size: 0.78rem; font-weight: 700; color: #1e40af; }
    /* Btn */
    .btn-gen { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; border: none; border-radius: 12px; padding: 0.7rem 1.6rem; font-weight: 800; font-size: 0.8rem; letter-spacing: 0.04em; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px -3px rgba(30,64,175,0.3); }
    .btn-gen:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -3px rgba(30,64,175,0.35); }
    .btn-cancel { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0.7rem 1.4rem; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: all 0.2s; }
    .btn-cancel:hover { background: #e2e8f0; }
    /* Table */
    .table-premium { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .table-premium thead th { padding: 1rem 1.5rem; background: #f8fafc; font-size: 0.68rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.07em; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
    .table-premium thead th:first-child { border-radius: 0; }
    .table-premium tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
    .table-premium tbody tr:hover { background: #f8fafc; }
    .table-premium tbody td { padding: 0.9rem 1.5rem; vertical-align: middle; color: #334155; }
    /* Badges */
    .badge-format { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; }
    .badge-pdf  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .badge-excel { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .badge-all { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    /* Action btns */
    .btn-dl  { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 8px; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; border: none; }
    .btn-dl-pdf   { background: #fee2e2; color: #dc2626; } .btn-dl-pdf:hover   { background: #dc2626; color: #fff; }
    .btn-dl-excel { background: #dcfce7; color: #16a34a; } .btn-dl-excel:hover { background: #16a34a; color: #fff; }
    .btn-dl-del   { background: #f1f5f9; color: #64748b; } .btn-dl-del:hover   { background: #ef4444; color: #fff; }
    /* Scrollable table */
    .scrollable-table-wrap { overflow-x: auto; overflow-y: auto; max-height: 65vh; }
    .scrollable-table-wrap::-webkit-scrollbar { width: 7px; height: 7px; }
    .scrollable-table-wrap::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .scrollable-table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .scrollable-table-wrap::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .table-premium thead th { position: sticky; top: 0; z-index: 2; }
    /* Exercice badge */
    .exercice-badge { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 6px 14px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
    /* Open btn */
    .btn-open { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; border: none; border-radius: 14px; padding: 0.7rem 1.6rem; font-weight: 800; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 15px -3px rgba(30,64,175,0.3); }
    .btn-open:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -3px rgba(30,64,175,0.35); }
    /* Alerts */
    .alert-success-prem { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 14px; padding: 0.9rem 1.2rem; display: flex; align-items: center; gap: 0.7rem; font-weight: 600; font-size: 0.85rem; margin-bottom: 1.5rem; }
    .alert-error-prem   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 14px; padding: 0.9rem 1.2rem; display: flex; align-items: center; gap: 0.7rem; font-weight: 600; font-size: 0.85rem; margin-bottom: 1.5rem; }
    /* Empty */
    .empty-state { padding: 3.5rem 2rem; text-align: center; }
    .empty-state i { font-size: 2.5rem; color: #e2e8f0; margin-bottom: 1rem; display: block; }
    .empty-state p { color: #94a3b8; font-weight: 600; font-size: 0.9rem; margin: 0; }
    /* Sections pills */
    .sections-range { display: inline-flex; align-items: center; gap: 5px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 2px 10px; font-size: 0.72rem; font-weight: 700; color: #1e40af; }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header', ['page_title' => 'Grand Livre <span class="text-gradient">Analytique</span>'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- Exercice badge --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <span class="exercice-badge">
                                <i class="fas fa-calendar-check"></i>
                                Exercice : {{ $exerciceActif?->intitule ?? 'Non défini' }}
                            </span>
                            @if($exerciceActif)
                                <span class="text-xs text-slate-400 font-medium">
                                    {{ \Carbon\Carbon::parse($exerciceActif->date_debut)->format('d/m/Y') }}
                                    → {{ \Carbon\Carbon::parse($exerciceActif->date_fin)->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                        <button type="button" class="btn-open" data-bs-toggle="modal" data-bs-target="#modalGenerateGL">
                            <i class="fas fa-file-export"></i>
                            Générer le Grand Livre Analytique
                        </button>
                    </div>

                    {{-- Flash messages --}}
                    @if(session('success'))
                        <div class="alert-success-prem">
                            <i class="fas fa-check-circle text-green-500 text-lg"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert-error-prem">
                            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- History Table --}}
                    <div class="glass-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div>
                                <h5 class="text-slate-800 font-black mb-0 text-base">Rapports Générés</h5>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                    Grand Livre Analytique — Historique
                                </p>
                            </div>
                            <span class="bg-blue-50 text-blue-700 text-xs font-black px-3 py-1 rounded-full border border-blue-100">
                                {{ $rapports->count() }} rapport(s)
                            </span>
                        </div>

                        <div class="scrollable-table-wrap">
                            <table class="table-premium">
                                <thead>
                                    <tr>
                                        <th>Date de génération</th>
                                        <th>Axe Analytique</th>
                                        <th>Sections</th>
                                        <th>Période</th>
                                        <th>Mouvements</th>
                                        <th>Format</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rapports as $rapport)
                                        <tr>
                                            <td class="font-medium text-slate-700 whitespace-nowrap">
                                                {{ $rapport->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <span class="font-bold text-blue-700">{{ $rapport->axe_libelle ?? '—' }}</span>
                                            </td>
                                            <td>
                                                @if($rapport->toutes_sections)
                                                    <span class="sections-range"><i class="fas fa-layer-group me-1"></i>Toutes les sections</span>
                                                @else
                                                    <span class="sections-range">
                                                        {{ $rapport->section_de_libelle ?? '—' }}
                                                        <i class="fas fa-arrow-right text-blue-400 text-[9px]"></i>
                                                        {{ $rapport->section_a_libelle ?? '—' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap text-slate-600 font-medium">
                                                @if($rapport->toute_periode)
                                                    <span class="badge-format badge-all"><i class="fas fa-infinity me-1"></i>Toute la période</span>
                                                @else
                                                    {{ $rapport->date_debut?->format('d/m/Y') }} → {{ $rapport->date_fin?->format('d/m/Y') }}
                                                @endif
                                            </td>
                                            <td>
                                                <span class="font-black text-slate-800">{{ number_format($rapport->nb_mouvements) }}</span>
                                            </td>
                                            <td>
                                                @if($rapport->format === 'pdf')
                                                    <span class="badge-format badge-pdf"><i class="fas fa-file-pdf"></i> PDF</span>
                                                @else
                                                    <span class="badge-format badge-excel"><i class="fas fa-file-excel"></i> Excel</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($rapport->fichier)
                                                        <a href="{{ asset('rapports_analytiques/' . $rapport->fichier) }}"
                                                           target="_blank"
                                                           class="btn-dl {{ $rapport->format === 'pdf' ? 'btn-dl-pdf' : 'btn-dl-excel' }}">
                                                            <i class="fas {{ $rapport->format === 'pdf' ? 'fa-file-pdf' : 'fa-file-excel' }}"></i>
                                                            Télécharger
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('analytique.grand_livre.destroy', $rapport->id) }}" method="POST"
                                                          onsubmit="return confirm('Supprimer ce rapport ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-dl btn-dl-del">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state">
                                                    <i class="fas fa-folder-open"></i>
                                                    <p>Aucun rapport généré pour l'instant.</p>
                                                    <p class="text-slate-300 text-xs mt-1">Cliquez sur "Générer le Grand Livre Analytique" pour créer votre premier rapport.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     MODAL — Générer Grand Livre Analytique
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade anl-modal" id="modalGenerateGL" tabindex="-1" aria-labelledby="modalGLLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <div style="background:rgba(255,255,255,0.15); border-radius:10px; padding:8px 10px;">
                        <i class="fas fa-book-open text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="modalGLLabel">Générer le Grand Livre Analytique</h5>
                        <p class="text-[10px] text-blue-200 font-semibold mt-0.5 mb-0">Configurez les paramètres du rapport</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('analytique.grand_livre.generate') }}" method="POST" id="formGenerateGL">
                @csrf
                <div class="modal-body">

                    {{-- Row 1 : Axe + Format --}}
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="field-label mb-0">Axe Analytique *</label>
                                <label class="toggle-row" id="toggleAllAxes">
                                    <input type="checkbox" name="tous_axes" id="gl_tous_axes" value="1"
                                           onchange="toggleGLAxes(this.checked)">
                                    <span>✓ Tous les axes</span>
                                </label>
                            </div>
                            <select name="axe_id" id="gl_axe_id" class="field-input" required onchange="loadSectionsForGL(this.value)">
                                <option value="">— Choisir un axe —</option>
                                @foreach($axes as $axe)
                                    <option value="{{ $axe->id }}">{{ $axe->libelle }} ({{ $axe->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Format *</label>
                            <select name="format" class="field-input" required>
                                <option value="pdf">📄 PDF</option>
                                <option value="excel">📊 Excel (.xlsx)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row 2 : Sections De → A + checkbox Toutes --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="field-label mb-0">Sections Analytiques</label>
                            <label class="toggle-row" id="toggleAllSections">
                                <input type="checkbox" name="toutes_sections" id="gl_toutes_sections" value="1"
                                       onchange="toggleGLSections(this.checked)">
                                <span>✓ Toutes les sections</span>
                            </label>
                        </div>
                        <div id="gl_sections_range" class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">De la section</label>
                                <select name="section_de_id" id="gl_section_de" class="field-input">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($sections as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->code }} - {{ $sec->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="field-label">À la section</label>
                                <select name="section_a_id" id="gl_section_a" class="field-input">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($sections as $sec)
                                        <option value="{{ $sec->id }}">{{ $sec->code }} - {{ $sec->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Row 3 : Dates + checkbox Toute la période --}}
                    <div class="mb-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="field-label mb-0">Période</label>
                            <label class="toggle-row" id="toggleAllPeriod">
                                <input type="checkbox" name="toute_periode" id="gl_toute_periode" value="1"
                                       onchange="toggleGLPeriod(this.checked)">
                                <span>✓ Toute la période de l'exercice</span>
                            </label>
                        </div>
                        <div id="gl_dates_range" class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Date Début</label>
                                <input type="date" name="date_debut" id="gl_date_debut" class="field-input"
                                       value="{{ $exerciceActif ? \Carbon\Carbon::parse($exerciceActif->date_debut)->format('Y-m-d') : '' }}">
                            </div>
                            <div>
                                <label class="field-label">Date Fin</label>
                                <input type="date" name="date_fin" id="gl_date_fin" class="field-input"
                                       value="{{ $exerciceActif ? \Carbon\Carbon::parse($exerciceActif->date_fin)->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer flex gap-3">
                    <button type="button" class="btn-cancel flex-1" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn-cancel flex-1 flex items-center justify-center gap-2" id="btnGLPreview" style="background:#eff6ff; color:#1e40af; border-color:#bfdbfe;">
                        <span class="spinner-border spinner-border-sm d-none" id="spinnerPreview" role="status"></span>
                        <i class="fas fa-eye" id="iconPreview"></i> Prévisualiser
                    </button>
                    <button type="submit" class="btn-gen flex-1">
                        <i class="fas fa-cogs me-2"></i> Générer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modale de Prévisualisation PDF ── --}}
<div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content overflow-hidden" style="border-radius: 0;">
            <div class="modal-header border-b border-slate-200 px-6 py-4" style="background:#1e293b;">
                <h5 class="modal-title font-extrabold text-xl text-white">Prévisualisation du Grand Livre Analytique</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0" style="background: #525659;">
                <iframe id="pdfPreviewFrame" style="width:100%; height: calc(100vh - 70px);" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

@include('components.footer')

<script>
    // ── Axes toggle ───────────────────────────────────────────────────
    function toggleGLAxes(checked) {
        const axeSelect = document.getElementById('gl_axe_id');
        axeSelect.disabled = checked;
        axeSelect.required = !checked;
        if (checked) {
            axeSelect.value = '';
            // Force toutes sections
            document.getElementById('gl_toutes_sections').checked = true;
            toggleGLSections(true);
            // Empêche de décocher "Toutes sections"
            document.getElementById('toggleAllSections').style.opacity = '0.4';
            document.getElementById('toggleAllSections').style.pointerEvents = 'none';
        } else {
            document.getElementById('toggleAllSections').style.opacity = '1';
            document.getElementById('toggleAllSections').style.pointerEvents = 'auto';
        }
    }
    // ── Sections toggle ───────────────────────────────────────────────
    function toggleGLSections(checked) {
        const range = document.getElementById('gl_sections_range');
        range.style.opacity     = checked ? '0.4' : '1';
        range.style.pointerEvents = checked ? 'none' : 'auto';
        document.getElementById('gl_section_de').required = !checked;
        document.getElementById('gl_section_a').required  = !checked;
    }
    // ── Period toggle ─────────────────────────────────────────────────
    function toggleGLPeriod(checked) {
        const range = document.getElementById('gl_dates_range');
        range.style.opacity     = checked ? '0.4' : '1';
        range.style.pointerEvents = checked ? 'none' : 'auto';
        document.getElementById('gl_date_debut').required = !checked;
        document.getElementById('gl_date_fin').required   = !checked;
    }
    // ── Load sections by axe ─────────────────────────────────────────
    function loadSectionsForGL(axeId) {
        if (!axeId) return;
        fetch(`/analytique/axes/${axeId}/sections`)
            .then(r => r.json())
            .then(data => {
                const placeholder = '<option value="">— Sélectionner —</option>';
                const options = data.map(s => `<option value="${s.id}">${s.code} - ${s.libelle}</option>`).join('');
                document.getElementById('gl_section_de').innerHTML = placeholder + options;
                document.getElementById('gl_section_a').innerHTML  = placeholder + options;
            })
            .catch(() => {});
    }
    // ── Preview action ───────────────────────────────────────────────
    document.getElementById('btnGLPreview').addEventListener('click', function() {
        const form = document.getElementById('formGenerateGL');
        if (!form.checkValidity() && !document.getElementById('gl_tous_axes').checked) {
            form.reportValidity();
            return;
        }

        const spin = document.getElementById('spinnerPreview');
        const icon = document.getElementById('iconPreview');
        spin.classList.remove('d-none');
        icon.classList.add('d-none');
        this.disabled = true;

        const formData = new FormData(form);

        fetch("{{ route('analytique.grand_livre.preview') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            spin.classList.add('d-none');
            icon.classList.remove('d-none');
            this.disabled = false;

            if (data.success) {
                document.getElementById('pdfPreviewFrame').src = data.url;
                const previewModal = new bootstrap.Modal(document.getElementById('modalPreviewPDF'));
                previewModal.show();
            } else {
                alert('Erreur: ' + (data.error || 'Impossible de charger la prévisualisation.'));
            }
        })
        .catch(err => {
            spin.classList.add('d-none');
            icon.classList.remove('d-none');
            this.disabled = false;
            alert('Une erreur réseau est survenue.');
        });
    });
    // ── Dismiss alerts ───────────────────────────────────────────────
    document.querySelectorAll('.alert-success-prem, .alert-error-prem').forEach(el => {
        setTimeout(() => { el.style.opacity='0'; setTimeout(() => el.remove(), 400); }, 5000);
        el.style.transition = 'opacity 0.4s';
    });
</script>
</body>
</html>
