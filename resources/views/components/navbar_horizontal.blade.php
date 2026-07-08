<style>
    /* Design Premium pour la Navbar Horizontale */
    .navbar-horizontal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 70px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        z-index: 1050;
        display: flex;
        align-items: center;
        padding: 0 24px;
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.05);
        font-family: 'Inter', sans-serif;
    }

    .nav-h-brand {
        display: flex;
        align-items: center;
        margin-right: 40px;
        min-width: 200px;
    }

    .nav-h-logo {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
    }

    .nav-h-title {
        margin-left: 12px;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        white-space: nowrap;
    }

    .nav-h-links {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
        overflow-x: auto;
        overflow-y: hidden;
        height: 100%;
        padding-bottom: 4px; /* Espace pour la barre de défilement */
        margin-bottom: -4px; /* Compense le padding pour l'alignement */
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
    }

    /* Style Premium pour la barre de défilement (Chrome/Safari/Edge) */
    .nav-h-links::-webkit-scrollbar {
        height: 4px;
    }
    .nav-h-links::-webkit-scrollbar-track {
        background: transparent;
    }
    .nav-h-links::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 4px;
    }
    .nav-h-links::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.8);
    }

    .nav-h-item {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        color: #475569;
        font-weight: 600;
        font-size: 14px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        white-space: nowrap;
        border: 2px solid transparent;
        position: relative;
    }

    .nav-h-item i {
        margin-right: 8px;
        font-size: 16px;
        transition: transform 0.2s ease;
    }

    .nav-h-item:hover {
        background: #f1f5f9;
        color: #1e40af;
    }

    .nav-h-item.active {
        background: #eff6ff;
        color: #1e40af;
        border-color: rgba(59, 130, 246, 0.2);
    }

    .nav-h-item.active::after {
        content: '';
        position: absolute;
        bottom: -15px; /* Aligne avec le bas de la navbar */
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 4px;
        background: #3b82f6;
        border-radius: 4px 4px 0 0;
    }

    /* Ajustements globaux pour le layout avec la navbar */
    body {
        padding-top: 0 !important; 
    }
    .layout-page {
        margin-top: 70px !important;
        min-height: calc(100vh - 70px) !important;
    }
    .sidebar-new {
        top: 70px !important;
        height: calc(100vh - 70px) !important;
        z-index: 1040 !important;
    }
    /* Collapsed Sidebar Styles */
    body.sidebar-collapsed .sidebar-new {
        left: -288px !important;
    }
    body.sidebar-collapsed .layout-page {
        margin-left: 0 !important;
        width: 100vw !important;
        max-width: 100vw !important;
    }
    /* Smooth transitions */
    .sidebar-new {
        transition: left 0.3s ease-in-out !important;
    }
    .layout-page {
        transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out, max-width 0.3s ease-in-out !important;
    }
    #sidebarToggleBtn:hover {
        background: #f1f5f9 !important;
        transform: scale(1.05);
    }
</style>

<div class="navbar-horizontal">
    <div class="nav-h-brand">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggleBtn" class="btn btn-icon btn-light rounded-circle me-3" style="border: 1px solid #e2e8f0; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: white; transition: all 0.2s;">
            <i class="fa-solid fa-bars" id="sidebarToggleIcon"></i>
        </button>
        <div class="nav-h-logo">
            <i class="fa-solid fa-bolt"></i>
        </div>
        <div class="nav-h-title">Flow Compta</div>
    </div>
    
    <div class="nav-h-links" id="horizontalNavLinks">
        <div class="nav-h-item" data-target="pilotage">
            <i class="fa-solid fa-rocket"></i> Pilotage
        </div>
        <div class="nav-h-item" data-target="configuration">
            <i class="fa-solid fa-gears"></i> Configuration Entreprise
        </div>
        <div class="nav-h-item" data-target="importation">
            <i class="fa-solid fa-file-import"></i> Importation
        </div>
        <div class="nav-h-item" data-target="fusion">
            <i class="fa-solid fa-bolt"></i> Fusion & Démarrage
        </div>
        <div class="nav-h-item" data-target="exportation">
            <i class="fa-solid fa-file-export"></i> Exportation
        </div>
        <div class="nav-h-item" data-target="gouvernance">
            <i class="fa-solid fa-sitemap"></i> Gouvernance
        </div>
        <div class="nav-h-item" data-target="creation_rapide">
            <i class="fa-solid fa-plus-circle"></i> Création Rapide
        </div>
        <div class="nav-h-item" data-target="operations">
            <i class="fa-solid fa-shield-halved"></i> Opérations
        </div>
        <div class="nav-h-item" data-target="tasks">
            <i class="fa-solid fa-list-check"></i> Gestion des Tâches
        </div>
        <div class="nav-h-item" data-target="validation">
            <i class="fa-solid fa-stamp"></i> Validation
        </div>
        <div class="nav-h-item" data-target="parametrage">
            <i class="fa-solid fa-sliders"></i> Paramétrage
        </div>
        <div class="nav-h-item" data-target="traitement">
            <i class="fa-solid fa-layer-group"></i> Traitement
        </div>
        <div class="nav-h-item" data-target="analytique">
            <i class="fa-solid fa-chart-line"></i> Analytique
        </div>
        <div class="nav-h-item" data-target="admin_interne">
            <i class="fa-solid fa-user-tie"></i> Administration Interne
        </div>
        <div class="nav-h-item" data-target="correction_ecriture">
            <i class="fa-solid fa-screwdriver-wrench"></i> Correction Écriture
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle logic
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const isSidebarCollapsed = localStorage.getItem('flowcompta_sidebar_collapsed') === 'true';
    if (isSidebarCollapsed) {
        document.body.classList.add('sidebar-collapsed');
    }
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            const collapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('flowcompta_sidebar_collapsed', collapsed);
        });
    }

    const navItems = document.querySelectorAll('.nav-h-item');
    const verticalSections = document.querySelectorAll('.sidebar-new .menu-section');
    
    // Nettoyer et lier les sections verticales aux cibles horizontales
    verticalSections.forEach(section => {
        const header = section.querySelector('.menu-section-header');
        if (header && !section.hasAttribute('data-section-id')) {
            let text = header.innerText.toLowerCase().trim();
            let sectionId = 'pilotage'; // défaut
            
            if (text.includes('pilotage')) sectionId = 'pilotage';
            else if (text.includes('configuration entreprise')) sectionId = 'configuration';
            else if (text.includes('importation')) sectionId = 'importation';
            else if (text.includes('fusion & démarrage') || text.includes('fusion')) sectionId = 'fusion';
            else if (text.includes('exportation')) sectionId = 'exportation';
            else if (text.includes('gouvernance') && !text.includes('rapide')) sectionId = 'gouvernance';
            else if (text.includes('création rapide')) sectionId = 'creation_rapide';
            else if (text.includes('opération') || text === 'opérations') sectionId = 'operations';
            else if (text.includes('gestion des tâches')) sectionId = 'tasks';
            else if (text.includes('validation')) sectionId = 'validation';
            else if (text.includes('paramétrage')) sectionId = 'parametrage';
            else if (text.includes('traitement')) sectionId = 'traitement';
            else if (text.includes('analytique')) sectionId = 'analytique';
            else if (text.includes('administration interne')) sectionId = 'admin_interne';
            else if (text.includes('correction écriture') || text.includes('correction')) sectionId = 'correction_ecriture';
            
            section.setAttribute('data-section-id', sectionId);
        }
    });

    // Cacher les onglets horizontaux qui n'ont pas de menu-section correspondante (droits insuffisants)
    navItems.forEach(item => {
        const target = item.getAttribute('data-target');
        const hasMatchingSection = document.querySelector(`.sidebar-new .menu-section[data-section-id="${target}"]`);
        if (!hasMatchingSection) {
            item.style.display = 'none';
        }
    });

    // Fonction pour activer une section
    function activateSection(sectionId) {
        // Mettre à jour les boutons horizontaux
        navItems.forEach(item => {
            if (item.getAttribute('data-target') === sectionId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

        // Mettre à jour les sections verticales
        let hasVisibleSubMenu = false;
        verticalSections.forEach(section => {
            if (section.getAttribute('data-section-id') === sectionId) {
                section.style.display = 'block';
                hasVisibleSubMenu = true;
            } else {
                section.style.display = 'none';
            }
        });

        // Afficher/Cacher le sélecteur d'exercice seulement si on est dans Traitement/Paramétrage
        const exoSelector = document.querySelector('.sidebar-new .px-3.mb-4');
        if (exoSelector) {
            // Le sélecteur d'exercice est généralement utile partout, ou on peut le laisser toujours visible
            // exoSelector.style.display = 'block'; 
        }

        // Sauvegarder dans le localStorage
        localStorage.setItem('flowcompta_active_section', sectionId);
    }

    // Gérer le clic
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const sectionId = item.getAttribute('data-target');
            activateSection(sectionId);

            // Naviguer vers le 1er lien réel de la section
            const section = document.querySelector(`.sidebar-new .menu-section[data-section-id="${sectionId}"]`);
            if (section) {
                if (sectionId === 'traitement') {
                    const listLink = Array.from(section.querySelectorAll('a[href]')).find(function(a) {
                        const href = a.getAttribute('href');
                        return href && href.includes('accounting_entry_list');
                    });
                    if (listLink) {
                        window.location.href = listLink.getAttribute('href');
                        return;
                    }
                }
                // Chercher le premier <a> avec un vrai href (pas modal, pas #)
                const firstLink = Array.from(section.querySelectorAll('a[href]')).find(function(a) {
                    const href = a.getAttribute('href');
                    return href && href !== '#' && !a.hasAttribute('data-bs-toggle');
                });
                if (firstLink) {
                    window.location.href = firstLink.getAttribute('href');
                }
            }
        });
    });

    // Initialisation au chargement
    const savedSection = localStorage.getItem('flowcompta_active_section');
    
    // Vérifier si la section sauvegardée existe bien pour cet utilisateur (permission)
    let sectionToActivate = 'pilotage'; 
    
    if (savedSection && document.querySelector(`.nav-h-item[data-target="${savedSection}"]`) && document.querySelector(`.nav-h-item[data-target="${savedSection}"]`).style.display !== 'none') {
        sectionToActivate = savedSection;
    } else {
        // Trouver la première section visible
        for (let item of navItems) {
            if (item.style.display !== 'none') {
                sectionToActivate = item.getAttribute('data-target');
                break;
            }
        }
    }

    activateSection(sectionToActivate);
});
</script>
