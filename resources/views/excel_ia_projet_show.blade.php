<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../../assets/" data-template="vertical-menu-template-free">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Projet : <span class="text-gradient">' . $projet->titre . '</span>'])
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
    --eia-red:       #ef4444;
}
.eia-layout {
    display: flex; gap: 20px; align-items: stretch;
    height: calc(100vh - 120px);
}
.chat-section {
    flex: 2; background: var(--eia-bg); border-radius: 16px; display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 0 0 1px var(--eia-border);
}
.sidebar-section {
    flex: 1; min-width: 300px; max-width: 400px; display: flex; flex-direction: column; gap: 20px; overflow-y: auto;
}
.s-card {
    background: var(--eia-white); border: 1px solid var(--eia-border);
    border-radius: 12px; padding: 20px;
}
.s-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; font-weight: 600; font-size: 14px; }
.btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; border: 1px solid var(--eia-border); background: #fff; cursor: pointer; }
.btn-sm.primary { background: var(--eia-accent); color: #fff; border-color: var(--eia-accent); }
textarea.eia-input { width: 100%; border: 1px solid var(--eia-border); border-radius: 8px; padding: 10px; font-size: 13px; font-family: inherit; resize: vertical; }

.file-item { display: flex; align-items: center; justify-content: space-between; padding: 10px; border: 1px solid var(--eia-border); border-radius: 8px; margin-bottom: 8px; }
.file-info { display: flex; align-items: center; gap: 10px; overflow: hidden; }
.file-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
.file-meta { font-size: 11px; color: var(--eia-muted); }

/* --- Reprise minimale Chat --- */
.eia-header { padding: 16px 20px; background: var(--eia-white); border-bottom: 1px solid var(--eia-border); display: flex; align-items: center; gap: 12px; }
</style>

<div style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
    <a href="{{ route('excel_ia.projets.index') }}" style="color:var(--eia-muted);text-decoration:none"><i class="fas fa-arrow-left"></i> Retour aux projets</a>
</div>

<div class="eia-layout">
    <!-- CHAT CENTRAL -->
    <div class="chat-section">
        <div class="eia-header">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $projet->couleur }};display:flex;align-items:center;justify-content:center;color:#fff"><i class="fas fa-folder"></i></div>
            <div>
                <h3 style="margin:0;font-size:16px;font-weight:600">{{ $projet->titre }}</h3>
                <div style="font-size:12px;color:var(--eia-muted)">Espace de discussion du projet</div>
            </div>
            <div style="margin-left:auto; display:flex; gap:8px">
                <button class="btn-sm" onclick="viderConversation()" title="Vider la conversation">
                    <i class="fas fa-broom"></i> Vider Chat
                </button>
                <button class="btn-sm" onclick="ouvrirModalEdition()" title="Modifier le projet">
                    <i class="fas fa-edit"></i>
                </button>
                <form action="{{ route('excel_ia.projets.destroy', $projet->id) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce projet et tous ses fichiers ?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-sm" style="color:var(--eia-red)"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>

        <div id="chat-container" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:15px;background:var(--eia-bg)">
            <div class="eia-message ai" style="display:flex;gap:12px;margin-bottom:10px">
                <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-robot"></i></div>
                <div style="background:#fff;padding:12px 16px;border-radius:14px;border-bottom-left-radius:4px;border:1px solid var(--eia-border);font-size:14px;line-height:1.5;box-shadow:0 2px 4px rgba(0,0,0,0.02)">
                    Bonjour ! Je suis prêt pour analyser les données spécifiques au projet <strong>{{ $projet->titre }}</strong>.<br>
                    J'ai pris connaissance de vos <strong><i class="fas fa-database text-warning"></i> fichiers de dépôt</strong> et de vos <strong><i class="fas fa-cogs text-primary"></i> instructions</strong>.<br>Que souhaitez-vous faire ?
                </div>
            </div>
        </div>

        <div style="padding:16px;border-top:1px solid var(--eia-border);background:var(--eia-white);">
            <div style="display:flex;gap:10px">
                <input type="text" id="chat-input" placeholder="Poser une question sur ce projet..." style="flex:1;padding:12px 16px;border:1.5px solid var(--eia-border);border-radius:12px;outline:none;font-size:14px">
                <button id="send-btn" onclick="envoyerMessage()" style="padding:0 20px;background:var(--eia-accent);color:#fff;border:none;border-radius:12px;cursor:pointer;transition:transform 0.1s"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>

<script>
const csrfToken = '{{ csrf_token() }}';
const projetId = {{ $projet->id }};
const storageKey = 'excel_ia_chat_historique_' + projetId;

let historique = [];
try {
    const saved = localStorage.getItem(storageKey);
    if (saved) {
        historique = JSON.parse(saved);
    }
} catch(e) {}

// Restaurer l'interface
document.addEventListener('DOMContentLoaded', () => {
    if (historique.length > 0) {
        // Optionnel : on pourrait re-dessiner tout l'historique ici...
        // Pour éviter d'effacer le message de bienvenue, on l'ajoute à la suite.
        historique.forEach(h => {
             ajouterMessageUI((h.role === 'model' ? 'ai' : 'user'), h.content, false);
        });
    }
});

function saveHistorique() {
    localStorage.setItem(storageKey, JSON.stringify(historique));
}

async function envoyerMessage() {
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if (!msg) return;

    input.value = '';
    ajouterMessageUI('user', msg);
    
    // Sauvegarde immédiate du message de l'utilisateur
    historique.push({ role: 'user', content: msg });
    saveHistorique();

    const btn = document.getElementById('send-btn');
    btn.disabled = true;
    btn.style.opacity = '0.5';

    // Afficher l'indicateur d'écriture
    const typingId = 'typing-' + Date.now();
    const chatContainer = document.getElementById('chat-container');
    chatContainer.insertAdjacentHTML('beforeend', `
        <div id="${typingId}" class="eia-message ai" style="display:flex;gap:12px;margin-bottom:10px">
            <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-robot"></i></div>
            <div style="background:#fff;padding:12px 16px;border-radius:14px;border-bottom-left-radius:4px;border:1px solid var(--eia-border);display:flex;align-items:center;gap:4px">
                <div style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:pulse 1s infinite"></div>
                <div style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:pulse 1s infinite .2s"></div>
                <div style="width:6px;height:6px;background:#94a3b8;border-radius:50%;animation:pulse 1s infinite .4s"></div>
            </div>
        </div>
    `);
    chatContainer.scrollTop = chatContainer.scrollHeight;

    try {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('message', msg);
        formData.append('projet_id', projetId);
        
        // On envoie l'historique (sans le message actuel en cours)
        historique.slice(0, -1).forEach((h, index) => {
            formData.append(`historique[${index}][role]`, h.role);
            formData.append(`historique[${index}][content]`, h.content);
        });

        const response = await fetch("{{ route('excel_ia.chat') }}", {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        document.getElementById(typingId)?.remove();
        btn.disabled = false;
        btn.style.opacity = '1';

        if (data.success) {
            ajouterMessageUI('ai', data.reponse);
            historique.push({ role: 'model', content: data.reponse });
            saveHistorique();
        } else {
            let errorMsg = '<span style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> ' + (data.error || 'Erreur serveur') + '</span><br><br><i>Note : La clé API a probablement épuisé les requêtes de son quota gratuit pour lire un fichier de cette taille aujourd\'hui.</i>';
            ajouterMessageUI('ai', errorMsg);
            historique.push({ role: 'model', content: "Erreur technique du modèle AI (Quota ou Fichier trop gros)." });
            saveHistorique();
        }

    } catch (err) {
        document.getElementById(typingId)?.remove();
        btn.disabled = false;
        btn.style.opacity = '1';
        ajouterMessageUI('ai', '<span style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> Erreur de réseau local ou Timeout.</span>');
    }
}

function viderConversation() {
    if (!confirm('Vider tout l\'historique de cette discussion ?')) return;
    historique = [];
    saveHistorique();
    // Recharger la page ou vider l'UI proprement
    window.location.reload();
}

function ouvrirModalEdition() {
    const modal = new bootstrap.Modal(document.getElementById('modalEditProjet'));
    modal.show();
}

function ajouterMessageUI(role, htmlContent, scroll = true) {
    const chatContainer = document.getElementById('chat-container');
    const isUser = role === 'user';
    let formattedContent = htmlContent.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
    const html = `
        <div class="eia-message ${role}" style="display:flex;gap:12px;margin-bottom:10px;${isUser ? 'flex-direction:row-reverse' : ''}">
            <div style="background:${isUser ? 'var(--eia-accent)' : 'linear-gradient(135deg,#6366f1,#8b5cf6)'};color:#fff;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-${isUser ? 'user' : 'robot'}"></i>
            </div>
            <div style="background:${isUser ? 'var(--eia-accent)' : '#fff'};color:${isUser ? '#fff' : 'inherit'};padding:12px 16px;border-radius:14px;border:1px solid ${isUser ? 'transparent' : 'var(--eia-border)'};font-size:14px;line-height:1.5;max-width:85%;${isUser ? 'border-bottom-right-radius:4px' : 'border-bottom-left-radius:4px'};overflow-x:auto;">
                ${formattedContent}
            </div>
        </div>
    `;
    chatContainer.insertAdjacentHTML('beforeend', html);
    if(scroll) chatContainer.scrollTop = chatContainer.scrollHeight;
}

// Entrée avec Entrée
document.getElementById('chat-input').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        envoyerMessage();
    }
});
</script>
    </div>

    <!-- COLONNE CONTEXTE -->
    <div class="sidebar-section">
        <!-- Action Principale -->
        <div class="s-card" style="background:#f8fafc; border-color:var(--eia-accent); position:relative">
            <div id="generate-loader" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.9);border-radius:12px;z-index:10;align-items:center;justify-content:center;flex-direction:column">
                <i class="fas fa-circle-notch fa-spin" style="font-size:24px;color:var(--eia-accent);margin-bottom:10px"></i>
                <div style="font-size:12px;color:var(--eia-text);font-weight:600">Génération en cours...</div>
                <div style="font-size:10px;color:var(--eia-muted);text-align:center;padding:0 10px">L'IA parcourt tous vos fichiers (jusqu'à 1 min)</div>
            </div>
            <form id="genererEcrituresForm" onsubmit="event.preventDefault(); lancerAnalyse();">
                @csrf
                <input type="hidden" name="projet_id" value="{{ $projet->id }}">
                <input type="hidden" name="mois_cible" value="TOUS">
                <button type="submit" style="width:100%; padding:15px; background:linear-gradient(135deg,var(--eia-accent),#8b5cf6); color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px; font-size:14px; transition:0.2s">
                    <i class="fas fa-magic"></i> Générer les Écritures Comptables
                </button>
                <div style="font-size:11px; text-align:center; color:var(--eia-muted); margin-top:8px">Traite le centre de dépôt pour préparer l'export.</div>
            </form>
        </div>

        <script>
        function lancerAnalyse() {
            document.getElementById('generate-loader').style.display = 'flex';
            const formData = new FormData(document.getElementById('genererEcrituresForm'));
            
            // On ajoute l'historique local du chat au formulaire pour que l'IA respecte la conversation
            if (typeof historique !== 'undefined' && historique.length > 0) {
                historique.forEach((h, index) => {
                    formData.append(`historique[${index}][role]`, h.role);
                    formData.append(`historique[${index}][content]`, h.content);
                });
            }

            fetch("{{ route('excel_ia.analyser') }}", {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "{{ url('/excel-ia/historique') }}/" + data.analyse_id;
                } else {
                    alert("Erreur de génération : " + data.error);
                    document.getElementById('generate-loader').style.display = 'none';
                }
            })
            .catch(err => {
                alert("Erreur réseau requise trop longue.");
                document.getElementById('generate-loader').style.display = 'none';
            });
        }
        </script>

        <!-- Instructions -->
        <div class="s-card">
            <form action="{{ route('excel_ia.projets.instructions.update', $projet->id) }}" method="POST">
                @csrf
                <div class="s-header">
                    <span><i class="fas fa-cogs" style="color:var(--eia-accent)"></i> Instructions IA</span>
                    <button type="submit" class="btn-sm primary">Sauver</button>
                </div>
                <textarea name="instructions" class="eia-input" rows="5" placeholder="Règles métier, Tiers spécifiques, etc.">{{ $projet->instructions }}</textarea>
                <div style="font-size:11px;color:var(--eia-muted);margin-top:8px">Ces règles seront ajoutées à chaque question envoyée dans ce projet.</div>
            </form>
        </div>

        <!-- Data Dépôt -->
        <div class="s-card" style="flex:1;display:flex;flex-direction:column">
            <div class="s-header">
                <span><i class="fas fa-database" style="color:#f59e0b"></i> Centre de Dépôt</span>
            </div>
            <div style="font-size:12px;color:var(--eia-muted);margin-bottom:16px">Ajoutez des fichiers (Balance, Grand Livre, Contrats) que l'IA utilisera comme base de connaissance pour ses analyses.</div>

            <div style="flex:1;overflow-y:auto;margin-bottom:15px">
                @forelse($projet->fichiers as $f)
                    <div class="file-item">
                        <div class="file-info">
                            <i class="fas fa-file-alt" style="color:var(--eia-muted)"></i>
                            <div>
                                <div class="file-name" title="{{ $f->nom }}">{{ $f->nom }}</div>
                                <div class="file-meta">{{ number_format($f->taille / 1024, 0) }} KB</div>
                            </div>
                        </div>
                        <form action="{{ route('excel_ia.projets.fichiers.destroy', $f->id) }}" method="POST" onsubmit="event.preventDefault(); fetch(this.action, {method:'POST', body: new FormData(this)}).then(() => this.closest('.file-item').remove());">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm" style="color:var(--eia-red);border:none"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                @empty
                    <div style="text-align:center;padding:20px 0;opacity:0.5;font-size:13px">
                        Aucun fichier de référence.
                    </div>
                @endforelse
            </div>

            <form id="uploadForm" action="{{ route('excel_ia.projets.fichiers.upload', $projet->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label id="drop-zone" style="display:block;width:100%;padding:15px;border:2px dashed var(--eia-accent);border-radius:10px;text-align:center;cursor:pointer;background:rgba(99,102,241,0.02);transition:all 0.2s">
                    <i class="fas fa-upload btn-icon" style="font-size:20px;color:var(--eia-accent);margin-bottom:8px"></i>
                    <div class="upload-text" style="font-size:13px;font-weight:600;color:var(--eia-accent)">Téléverser des fichiers</div>
                    <div style="font-size:11px;color:var(--eia-muted)">PDF, Excel, CSV max 10MB (Jusqu'à 20 fichiers)</div>
                    <input type="file" name="fichiers[]" multiple accept=".pdf,.csv,.xlsx,.xls" style="display:none" onchange="runAjaxUpload(this.form)">
                </label>
            </form>
            <script>
            function runAjaxUpload(form, files = null) {
                const textDiv = form.querySelector('.upload-text');
                const oldText = textDiv.innerText;
                textDiv.innerText = "Téléversement en cours...";
                
                const formData = new FormData(form);
                if (files) {
                    // Si on a des fichiers via Drop, on les ajoute manuellement
                    formData.delete('fichiers[]'); // On vide ceux de l'input
                    for (let i = 0; i < files.length; i++) {
                        formData.append('fichiers[]', files[i]);
                    }
                }

                fetch(form.action, { method: 'POST', body: formData })
                .then(r => r.text())
                .then(() => {
                    textDiv.innerText = "✅ Ajoutés ! L'IA les lira.";
                    setTimeout(() => {
                        textDiv.innerText = oldText;
                        window.location.reload(); // Recharger pour voir la liste à jour
                    }, 1500);
                    form.reset();
                });
            }

            // Drag & Drop Logic
            const dropZone = document.getElementById('drop-zone');
            if (dropZone) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, e => {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.style.background = 'rgba(99,102,241,0.1)';
                        dropZone.style.borderColor = '#4f46e5';
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.style.background = 'rgba(99,102,241,0.02)';
                        dropZone.style.borderColor = 'var(--eia-accent)';
                    }, false);
                });

                dropZone.addEventListener('drop', e => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files.length > 0) {
                        runAjaxUpload(document.getElementById('uploadForm'), files);
                    }
                }, false);
            }
            </script>
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

    <!-- Modal Edition Projet -->
    <div class="modal fade" id="modalEditProjet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('excel_ia.projets.update', $projet->id) }}" method="POST" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le Projet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre du Projet</label>
                        <input type="text" name="titre" class="form-control" value="{{ $projet->titre }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
