<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper" style="padding: 32px; width: 100%; min-height: calc(100vh - 80px); background: #f5f7fa;">

<style>
    .guide-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .guide-header {
        background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.2);
    }
    
    .guide-header h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .guide-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0;
    }
    
    .guide-nav {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        position: sticky;
        top: 20px;
        z-index: 100;
    }
    
    .guide-nav h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #7c3aed;
        margin-bottom: 16px;
    }
    
    .guide-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .guide-nav li {
        margin-bottom: 8px;
    }
    
    .guide-nav a {
        color: #374151;
        text-decoration: none;
        display: block;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 0.95rem;
    }
    
    .guide-nav a:hover {
        background: #ede9fe;
        color: #7c3aed;
        transform: translateX(4px);
    }
    
    .guide-section {
        background: white;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .guide-section h2 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #7c3aed;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 3px solid #ede9fe;
    }
    
    .guide-section h3 {
        font-size: 1.4rem;
        font-weight: 600;
        color: #374151;
        margin-top: 28px;
        margin-bottom: 16px;
    }
    
    .guide-section h4 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #4b5563;
        margin-top: 20px;
        margin-bottom: 12px;
    }
    
    .guide-section p {
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 16px;
    }
    
    .guide-section ul, .guide-section ol {
        color: #4b5563;
        line-height: 1.7;
        margin-bottom: 16px;
        padding-left: 24px;
    }
    
    .guide-section li {
        margin-bottom: 8px;
    }
    
    .alert-info {
        background: #ede9fe;
        border-left: 4px solid #a78bfa;
        padding: 16px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .alert-info strong {
        color: #7c3aed;
        display: block;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
    
    .alert-warning {
        background: #fef3c7;
        border-left: 4px solid #fbbf24;
        padding: 16px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .alert-warning strong {
        color: #d97706;
        display: block;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
    
    .step-badge {
        display: inline-block;
        background: #7c3aed;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 8px;
    }
    
    .btn-print {
        background: white;
        border: 2px solid #7c3aed;
        color: #7c3aed;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-print:hover {
        background: #7c3aed;
        color: white;
    }
    
    @media print {
        .guide-nav, .btn-print, .guide-header { display: none; }
        .guide-section { page-break-inside: avoid; }
    }
</style>

<div class="guide-container">
    <div class="guide-header">
        <h1><i class="fa-solid fa-crown me-3"></i>Guide d'utilisation - Super Administrateur</h1>
        <p>Guide complet pour gérer la plateforme COMPTAFLOW au niveau global</p>
    </div>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="guide-nav">
                <h3><i class="fa-solid fa-list me-2"></i>Sommaire</h3>
                <ul>
                    <li><a href="#premiere-connexion">1. Première Connexion</a></li>
                    <li><a href="#tableau-bord">2. Tableau de Bord</a></li>
                    <li><a href="#gestion-entites">3. Gestion des Entités</a></li>
                    <li><a href="#gestion-entreprises">4. Gestion des Entreprises</a></li>
                    <li><a href="#gestion-utilisateurs">5. Gestion des Utilisateurs</a></li>
                    <li><a href="#administration-interne">6. Administration Interne</a></li>
                    <li><a href="#switch">7. Switch & Contexte</a></li>
                    <li><a href="#operations">8. Opérations & Audit</a></li>
                    <li><a href="#rapports">9. Rapports de Performance</a></li>
                    <li><a href="#controle-acces">10. Contrôle d'Accès</a></li>
                </ul>
                <hr class="my-3">
                <button onclick="window.print()" class="btn-print w-100">
                    <i class="fa-solid fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
        
        <div class="col-lg-9">
            <!-- SECTION 1 -->
            <div class="guide-section" id="premiere-connexion">
                <h2>1. Première Connexion</h2>
                
                <h3>1.1 Accès Super Administrateur</h3>
                <p>En tant que Super Administrateur, vous avez un contrôle total sur la plateforme COMPTAFLOW.</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-crown me-2"></i>Privilèges SuperAdmin</strong>
                    Vous pouvez créer et gérer toutes les entreprises, tous les utilisateurs, et accéder à toutes les données de la plateforme.
                </div>
                
                <h3>1.2 Interface SuperAdmin</h3>
                <p>Votre interface est organisée en sections :</p>
                <ul>
                    <li><strong>Pilotage</strong> : Tableau de bord global</li>
                    <li><strong>Gouvernance</strong> : Entreprises, comptabilités, utilisateurs</li>
                    <li><strong>Opérations</strong> : Activités, tâches, contrôle d'accès</li>
                    <li><strong>Analyses</strong> : Rapports de performance</li>
                    <li><strong>Administration Interne</strong> : Gestion des super admins secondaires</li>
                </ul>
            </div>
            
            <!-- SECTION 2 -->
            <div class="guide-section" id="tableau-bord">
                <h2>2. Tableau de Bord SuperAdmin</h2>
                
                <p><strong>Accès :</strong> Menu → Pilotage → Tableau de bord SuperAdmin</p>
                
                <h3>2.1 Vue d'ensemble</h3>
                <p>Le tableau de bord affiche les statistiques globales de la plateforme :</p>
                <ul>
                    <li><strong>Nombre d'entreprises</strong> : Total des entreprises actives</li>
                    <li><strong>Nombre de comptabilités</strong> : Total des comptabilités créées</li>
                    <li><strong>Nombre d'utilisateurs</strong> : Admins et comptables</li>
                    <li><strong>Activité récente</strong> : Dernières actions sur la plateforme</li>
                    <li><strong>Graphiques</strong> : Évolution de l'utilisation</li>
                </ul>
                
                <h3>2.2 Indicateurs de Performance</h3>
                <ul>
                    <li>Taux d'utilisation par entreprise</li>
                    <li>Nombre d'écritures saisies (global)</li>
                    <li>Utilisateurs actifs</li>
                    <li>Entreprises bloquées/actives</li>
                </ul>
            </div>
            
            <!-- SECTION 3 -->
            <div class="guide-section" id="gestion-entites">
                <h2>3. Gestion des Entités</h2>
                
                <p><strong>Accès :</strong> Menu → Gouvernance → Gestion des Entités</p>
                
                <h3>3.1 Vue d'ensemble des Entités</h3>
                <p>Cette page affiche toutes les entreprises et leurs comptabilités dans une structure hiérarchique.</p>
                
                <h4>Structure</h4>
                <ul>
                    <li><strong>Entreprise Mère</strong>
                        <ul>
                            <li>Comptabilité 1</li>
                            <li>Comptabilité 2</li>
                        </ul>
                    </li>
                    <li><strong>Sous-entreprise (Filiale)</strong>
                        <ul>
                            <li>Comptabilité A</li>
                        </ul>
                    </li>
                </ul>
                
                <h3>3.2 Actions Rapides</h3>
                <ul>
                    <li>Créer une entreprise</li>
                    <li>Créer une comptabilité</li>
                    <li>Consulter les détails</li>
                    <li>Bloquer/Débloquer</li>
                </ul>
            </div>
            
            <!-- SECTION 4 -->
            <div class="guide-section" id="gestion-entreprises">
                <h2>4. Gestion des Entreprises</h2>
                
                <h3>4.1 Créer une Entreprise</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Créer Entreprise</p>
                
                <h4>Procédure</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur "Créer Entreprise"</p>
                <p><span class="step-badge">Étape 2</span> Remplissez les informations :</p>
                <ul>
                    <li><strong>Nom de l'entreprise</strong> : Raison sociale</li>
                    <li><strong>Adresse</strong> : Siège social</li>
                    <li><strong>Téléphone / Email</strong> : Contacts</li>
                    <li><strong>Entreprise mère</strong> : Si c'est une filiale, sélectionnez l'entreprise mère</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-info-circle me-2"></i>Entreprise mère vs Filiale</strong>
                    Une entreprise mère peut avoir plusieurs filiales. Les filiales peuvent hériter des configurations (plan comptable, tiers, journaux) de l'entreprise mère via la fonction "Fusion".
                </div>
                
                <h3>4.2 Modifier une Entreprise</h3>
                <p>Cliquez sur l'icône d'édition à côté de l'entreprise pour modifier ses informations.</p>
                
                <h3>4.3 Bloquer/Débloquer une Entreprise</h3>
                <p>Une entreprise bloquée ne peut plus être utilisée par ses administrateurs et comptables.</p>
                
                <h3>4.4 Supprimer une Entreprise</h3>
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Attention</strong>
                    La suppression d'une entreprise est irréversible et supprime toutes ses données (comptabilités, utilisateurs, écritures).
                </div>
            </div>
            
            <!-- SECTION 5 -->
            <div class="guide-section" id="gestion-utilisateurs">
                <h2>5. Gestion des Utilisateurs</h2>
                
                <p><strong>Accès :</strong> Menu → Gouvernance → Gestion Utilisateurs</p>
                
                <h3>5.1 Vue d'ensemble</h3>
                <p>Liste de tous les utilisateurs de la plateforme (tous rôles confondus).</p>
                
                <h3>5.2 Créer un Administrateur</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Créer Administrateur</p>
                
                <h4>Procédure</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez l'entreprise</p>
                <p><span class="step-badge">Étape 2</span> Remplissez :</p>
                <ul>
                    <li>Nom complet</li>
                    <li>Email</li>
                    <li>Mot de passe initial</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <h3>5.3 Créer un Comptable</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Créer Comptable</p>
                <p>Même procédure que pour un administrateur.</p>
                
                <h3>5.4 Modifier les Habilitations</h3>
                <p>Vous pouvez personnaliser les permissions de n'importe quel utilisateur, même les administrateurs.</p>
                
                <h3>5.5 Bloquer/Débloquer un Utilisateur</h3>
                <p>Un utilisateur bloqué ne peut plus se connecter.</p>
            </div>
            
            <!-- SECTION 6 -->
            <div class="guide-section" id="administration-interne">
                <h2>6. Administration Interne</h2>
                
                <p><strong>Accès :</strong> Menu → Administration Interne → Gestion de l'Administration Interne</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-users-gear me-2"></i>Super Admins Secondaires</strong>
                    Vous pouvez créer d'autres super administrateurs pour vous aider à gérer la plateforme. Ils auront les mêmes privilèges que vous, sauf la gestion des super admins (réservée au super admin primaire).
                </div>
                
                <h3>6.1 Créer un Super Admin Secondaire</h3>
                <p><span class="step-badge">Étape 1</span> Cliquez sur "Nouveau Super Admin"</p>
                <p><span class="step-badge">Étape 2</span> Remplissez les informations</p>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <h3>6.2 Gérer les Super Admins Secondaires</h3>
                <ul>
                    <li>Modifier les informations</li>
                    <li>Bloquer/Débloquer</li>
                    <li>Supprimer (avec prudence)</li>
                </ul>
            </div>
            
            <!-- SECTION 7 -->
            <div class="guide-section" id="switch">
                <h2>7. Switch & Contexte</h2>
                
                <p><strong>Accès :</strong> Menu → Gouvernance → Switch Entreprise</p>
                
                <h3>7.1 Basculer vers une Entreprise</h3>
                <p>Vous pouvez vous connecter en tant qu'administrateur d'une entreprise pour effectuer des actions en son nom.</p>
                
                <h4>Procédure</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez une entreprise</p>
                <p><span class="step-badge">Étape 2</span> Cliquez sur "Switch"</p>
                <p><span class="step-badge">Étape 3</span> Vous êtes maintenant dans le contexte de cette entreprise</p>
                <p><span class="step-badge">Étape 4</span> Pour revenir, cliquez sur "Quitter le mode switch" dans la bannière en haut</p>
                
                <h3>7.2 Basculer vers un Utilisateur</h3>
                <p>Vous pouvez également vous connecter en tant qu'utilisateur spécifique (impersonation).</p>
                
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Utilisation responsable</strong>
                    Le switch doit être utilisé uniquement pour le support technique ou la résolution de problèmes. Toutes les actions en mode switch sont tracées dans l'audit.
                </div>
            </div>
            
            <!-- SECTION 8 -->
            <div class="guide-section" id="operations">
                <h2>8. Opérations & Audit</h2>
                
                <h3>8.1 Suivi des Activités</h3>
                <p><strong>Accès :</strong> Menu → Opérations → Suivi des Activités</p>
                
                <p>Consultez l'historique complet de toutes les actions sur la plateforme :</p>
                <ul>
                    <li>Connexions/déconnexions</li>
                    <li>Créations d'entreprises, comptabilités, utilisateurs</li>
                    <li>Modifications de configurations</li>
                    <li>Suppressions</li>
                    <li>Switch et impersonations</li>
                </ul>
                
                <h4>Filtres disponibles</h4>
                <ul>
                    <li>Par entreprise</li>
                    <li>Par utilisateur</li>
                    <li>Par type d'action</li>
                    <li>Par période</li>
                </ul>
                
                <h3>8.2 Gestion des Tâches</h3>
                <p><strong>Accès :</strong> Menu → Opérations → Assigner Tâche</p>
                <p>Vous pouvez assigner des tâches à n'importe quel utilisateur de la plateforme.</p>
            </div>
            
            <!-- SECTION 9 -->
            <div class="guide-section" id="rapports">
                <h2>9. Rapports de Performance</h2>
                
                <p><strong>Accès :</strong> Menu → Analyses → Rapports Performance</p>
                
                <h3>9.1 Rapports Disponibles</h3>
                <ul>
                    <li><strong>Utilisation par entreprise</strong> : Nombre d'écritures, utilisateurs actifs</li>
                    <li><strong>Croissance de la plateforme</strong> : Évolution du nombre d'entreprises et utilisateurs</li>
                    <li><strong>Activité globale</strong> : Statistiques d'utilisation</li>
                    <li><strong>Performance technique</strong> : Temps de réponse, erreurs</li>
                </ul>
                
                <h3>9.2 Exporter les Rapports</h3>
                <p>Tous les rapports peuvent être exportés en Excel ou PDF pour analyse.</p>
            </div>
            
            <!-- SECTION 10 -->
            <div class="guide-section" id="controle-acces">
                <h2>10. Contrôle d'Accès</h2>
                
                <p><strong>Accès :</strong> Menu → Opérations → Contrôle d'Accès</p>
                
                <h3>10.1 Bloquer une Entreprise</h3>
                <p><span class="step-badge">Étape 1</span> Sélectionnez l'entreprise</p>
                <p><span class="step-badge">Étape 2</span> Cliquez sur "Bloquer"</p>
                <p><span class="step-badge">Étape 3</span> Tous les utilisateurs de cette entreprise ne pourront plus se connecter</p>
                
                <h3>10.2 Bloquer un Utilisateur</h3>
                <p>Même procédure pour bloquer un utilisateur spécifique.</p>
                
                <h3>10.3 Débloquer</h3>
                <p>Pour débloquer, cliquez sur "Débloquer" à côté de l'entité bloquée.</p>
                
                <h3>10.4 Supprimer Définitivement</h3>
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Suppression irréversible</strong>
                    La suppression d'une entreprise ou d'un utilisateur est définitive et supprime toutes les données associées. Utilisez cette fonction avec une extrême prudence.
                </div>
            </div>
            
            <!-- FOOTER -->
            <div class="guide-section" style="background: #ede9fe; border-left: 4px solid #7c3aed;">
                <h3><i class="fa-solid fa-shield-halved me-2"></i>Responsabilités du Super Administrateur</h3>
                <p>En tant que Super Administrateur, vous êtes responsable de :</p>
                <ul>
                    <li>La sécurité et l'intégrité de toutes les données de la plateforme</li>
                    <li>La gestion des accès et des permissions</li>
                    <li>Le support technique aux administrateurs d'entreprise</li>
                    <li>La surveillance de l'activité globale</li>
                </ul>
                <p class="mb-0"><strong>Utilisez vos privilèges avec responsabilité et prudence.</strong></p>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.guide-nav a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>

                </div>
                @include('components.footer')
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</body>
</html>

