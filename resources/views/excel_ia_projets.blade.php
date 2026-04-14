<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Projets <span class="text-gradient">IA Comptable</span>'])
                <div class="content-wrapper">
                    <!-- CONTENT -->
                    <div class="px-4 py-4" style="max-width: 1400px; margin: 0 auto; padding-top: 20px !important;">
<style>
:root {
    --eia-bg:        #f8fafc;
    --eia-white:     #ffffff;
    --eia-border:    #e2e8f0;
    --eia-text:      #1e293b;
    --eia-muted:     #64748b;
    --eia-accent:    #6366f1;
    --eia-radius:    14px;
}
.eia-layout {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 120px);
    background: var(--eia-bg);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 0 0 1px var(--eia-border);
}
.eia-tabs {
    display: flex; gap: 4px; padding: 12px 16px 0;
    background: var(--eia-white); border-bottom: 1px solid var(--eia-border);
}
.eia-tab {
    display: flex; align-items: center; gap: 7px;
    padding: 9px 16px; font-size: 13px; font-weight: 500;
    color: var(--eia-muted); border-radius: 8px 8px 0 0;
    border: 1px solid transparent; border-bottom: none;
    text-decoration: none; position: relative; bottom: -1px;
}
.eia-tab:hover { color: var(--eia-accent); }
.eia-tab.active {
    color: var(--eia-accent); background: var(--eia-white);
    border-color: var(--eia-border); border-bottom-color: var(--eia-white); font-weight: 600;
}
.p-content { padding: 24px; overflow-y: auto; flex: 1; }

.header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.header-actions h3 { font-size: 1.2rem; font-weight: 700; color: var(--eia-text); margin: 0; }
.btn-new { background: var(--eia-accent); color: #fff; padding: 8px 16px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.btn-new:hover { background: #4f46e5; }

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.project-card {
    background: var(--eia-white);
    border: 1px solid var(--eia-border);
    border-radius: 12px;
    padding: 20px;
    transition: all .2s;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.project-card:hover {
    border-color: var(--eia-accent);
    box-shadow: 0 4px 12px rgba(99,102,241,.1);
    transform: translateY(-2px);
}
.pc-header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.pc-icon {
    width: 40px; height: 40px; border-radius: 10px; display: flex;
    align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0;
}
.pc-title { font-weight: 600; font-size: 15px; color: var(--eia-text); line-height: 1.3; }
.pc-date { font-size: 11.5px; color: var(--eia-muted); margin-top: 4px; }
.pc-body { flex: 1; font-size: 13px; color: var(--eia-muted); margin-bottom: 16px; line-height: 1.5; }
.pc-footer { display: flex; gap: 12px; border-top: 1px solid var(--eia-border); padding-top: 12px; }
.pc-stat { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--eia-muted); font-weight: 500; }

/* Modal */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15,23,42,.6); backdrop-filter: blur(2px);
    display: none; align-items: center; justify-content: center; z-index: 1000;
}
.modal-overlay.active { display: flex; }
.eia-modal {
    background: #fff; border-radius: 16px; width: 100%; max-width: 480px; padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,.1);
}
.eia-modal h4 { margin: 0 0 20px; font-weight: 600; font-size: 1.1rem; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--eia-muted); margin-bottom: 6px; }
.eia-input {
    width: 100%; padding: 10px 14px; border: 1px solid var(--eia-border);
    border-radius: 8px; font-size: 14px; transition: border-color .15s; outline: none;
}
.eia-input:focus { border-color: var(--eia-accent); box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 24px; }
.btn-cancel { padding: 8px 16px; border: 1px solid var(--eia-border); background: #fff; border-radius: 8px; cursor: pointer; }
</style>

<div class="eia-layout">
    <div class="eia-tabs">
        <a href="{{ route('excel_ia.index') }}" class="eia-tab">
            <i class="fas fa-comments"></i> <span>Chat IA</span>
        </a>
        <a href="{{ route('excel_ia.historique') }}" class="eia-tab">
            <i class="fas fa-history"></i> <span>Historique</span>
        </a>
        <a href="{{ route('excel_ia.projets.index') }}" class="eia-tab active">
            <i class="fas fa-folder-open"></i> <span>Projets</span>
        </a>
        <a href="{{ route('factures_produites.index') }}" class="eia-tab">
            <i class="fas fa-file-invoice"></i> <span>Factures Produites</span>
        </a>
    </div>

    <div class="p-content">
        @if(session('success'))
            <div style="background:#dcfce7;color:#16a34a;padding:12px;border-radius:8px;margin-bottom:20px;font-size:13px"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <div class="header-actions">
            <div>
                <h3>Vos Projets</h3>
                <div style="font-size:13px;color:var(--eia-muted);margin-top:4px;">Regroupez vos données persistantes et vos instructions d'analyse.</div>
            </div>
            <button class="btn-new" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                <i class="fas fa-plus"></i> Nouveau Projet
            </button>
        </div>

        @if($projets->isEmpty())
            <div style="text-align:center;padding:60px 20px;background:#fff;border-radius:12px;border:1px dashed var(--eia-border)">
                <div style="width:64px;height:64px;background:#f1f5f9;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#94a3b8">
                    <i class="fas fa-folder-plus"></i>
                </div>
                <h4 style="font-weight:600;margin:0 0 8px">Aucun projet</h4>
                <p style="color:var(--eia-muted);font-size:14px;max-width:400px;margin:0 auto 20px">Créez un projet pour configurer un contexte spécifique (Plan tiers, Règles OHADA) qui sera mémorisé par l'IA.</p>
                <button class="btn-new" style="margin:0 auto" data-bs-toggle="modal" data-bs-target="#newProjectModal">Créer mon premier projet</button>
            </div>
        @else
            <div class="projects-grid">
                @foreach($projets as $projet)
                    <a href="{{ route('excel_ia.projets.show', $projet->id) }}" class="project-card">
                        <div class="pc-header">
                            <div class="pc-icon" style="background: {{ $projet->couleur }}"><i class="fas fa-folder"></i></div>
                            <div>
                                <div class="pc-title">{{ $projet->titre }}</div>
                                <div class="pc-date">Mis à jour {{ $projet->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="pc-body">
                            {{ \Illuminate\Support\Str::limit($projet->instructions ?: 'Aucune instruction spécifique.', 80) }}
                        </div>
                        <div class="pc-footer">
                            <div class="pc-stat" title="Fichiers de référence">
                                <i class="fas fa-database"></i> {{ $projet->fichiers_count }} dépôts
                            </div>
                            <div class="pc-stat" title="Analyses générées">
                                <i class="fas fa-bolt"></i> {{ $projet->analyses_count }} saisies
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Modal Créer (Bootstrap 5) -->
<div class="modal fade" id="newProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:16px;">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nouveau Projet IA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('excel_ia.projets.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted" style="font-size:12px">Nom du projet (ex: Clôture 2025, Paie Mensuelle)</label>
                        <input type="text" name="titre" class="form-control eia-input" required placeholder="Saisir un nom...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted" style="font-size:12px">Couleur</label>
                        <input type="color" name="couleur" class="form-control form-control-color" value="#6366f1" style="width:50px;height:40px;cursor:pointer" title="Choisir une couleur">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted" style="font-size:12px">Instructions globales (Optionnelles)</label>
                        <textarea name="instructions" class="form-control eia-input" rows="3" placeholder="Ex: Toujours utiliser le compte X pour Y..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px">Annuler</button>
                    <button type="submit" class="btn-new">Créer le projet</button>
                </div>
            </form>
        </div>
    </div>
</div>
                    </div> <!-- /px-4 -->
                </div> <!-- /content-wrapper -->
            </div> <!-- /layout-page -->
        </div> <!-- /layout-container -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @include('components.footer')
</body>
</html>
