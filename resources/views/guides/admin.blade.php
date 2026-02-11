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
        background: white;
        color: #1f2937;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 40px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
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
        color: #1e40af;
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
        background: #eff6ff;
        color: #1e40af;
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
        color: #1e40af;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 3px solid #eff6ff;
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
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 16px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .alert-info strong {
        color: #1e40af;
        display: block;
        margin-bottom: 8px;
        font-size: 1.05rem;
    }
    
    .alert-warning {
        background: #fff7ed;
        border-left: 4px solid #f59e0b;
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
        background: #1e40af;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 8px;
    }
    
    .btn-print {
        background: white;
        border: 2px solid #1e40af;
        color: #1e40af;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-print:hover {
        background: #1e40af;
        color: white;
    }
    
    @media print {
        .guide-nav, .btn-print, .guide-header { display: none; }
        .guide-section { page-break-inside: avoid; }
    }
</style>

<div class="guide-container">
    <div class="guide-header">
        <h1><i class="fa-solid fa-user-shield me-3"></i>Guide d'utilisation - Administrateur</h1>
        <p>Guide complet pour gérer votre entreprise et votre équipe sur COMPTAFLOW</p>
    </div>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="guide-nav">
                <h3><i class="fa-solid fa-list me-2"></i>Sommaire</h3>
                <ul>
                    <li><a href="#premiere-connexion">1. Première Connexion</a></li>
                    <li><a href="#tableau-bord">2. Tableau de Bord</a></li>
                    <li><a href="#configuration">3. Configuration Entreprise</a></li>
                    <li><a href="#equipe">4. Gestion d'Équipe</a></li>
                    <li><a href="#import-export">5. Import/Export</a></li>
                    <li><a href="#fusion">6. Fusion de Données</a></li>
                    <li><a href="#gouvernance">7. Gouvernance</a></li>
                    <li><a href="#validation">8. Validation & Approbations</a></li>
                    <li><a href="#operations">9. Opérations & Audit</a></li>
                    <li><a href="#taches">10. Gestion des Tâches</a></li>
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
                
                <h3>1.1 Accès Administrateur</h3>
                <p>En tant qu'administrateur, vous avez accès à toutes les fonctionnalités de gestion de votre entreprise.</p>
                <p><span class="step-badge">Étape 1</span> Connectez-vous avec vos identifiants administrateur</p>
                <p><span class="step-badge">Étape 2</span> Vous arrivez sur le tableau de bord administrateur</p>
                
                <h3>1.2 Interface Administrateur</h3>
                <p>Votre interface comprend :</p>
                <ul>
                    <li><strong>Menu Pilotage</strong> : Tableaux de bord et notifications</li>
                    <li><strong>Configuration Entreprise</strong> : Paramétrage des modèles</li>
                    <li><strong>Gouvernance</strong> : Gestion des utilisateurs et permissions</li>
                    <li><strong>Opérations</strong> : Audit et contrôle d'accès</li>
                    <li><strong>Fonctions comptables</strong> : Toutes les fonctions d'un comptable</li>
                </ul>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-info-circle me-2"></i>Double rôle</strong>
                    En tant qu'administrateur, vous pouvez également effectuer toutes les tâches comptables. Vous êtes à la fois gestionnaire et utilisateur.
                </div>
            </div>
            
            <!-- SECTION 2 -->
            <div class="guide-section" id="tableau-bord">
                <h2>2. Tableaux de Bord</h2>
                
                <h3>2.1 Tableau de Bord Admin</h3>
                <p><strong>Accès :</strong> Menu → Pilotage → Tableau de bord Admin</p>
                <p>Vue d'ensemble de la performance de l'entreprise :</p>
                <ul>
                    <li>Indicateurs clés de performance (KPI)</li>
                    <li>Statistiques d'utilisation</li>
                    <li>Activité des utilisateurs</li>
                    <li>Graphiques de synthèse</li>
                </ul>
                
                <h3>2.2 Tableau de Bord Personnel</h3>
                <p><strong>Accès :</strong> Menu → Pilotage → Tableau de bord personnel</p>
                <p>Votre activité comptable personnelle (si vous saisissez également des écritures).</p>
            </div>
            
            <!-- SECTION 3 -->
            <div class="guide-section" id="configuration">
                <h2>3. Configuration de l'Entreprise</h2>
                
                <h3>3.1 Dossier de Configuration</h3>
                <p><strong>Accès :</strong> Menu → Configuration Entreprise → Dossier de Configuration</p>
                <p>Hub central pour configurer tous les paramètres de l'entreprise.</p>
                
                <h3>3.2 Modèle de Plan Comptable</h3>
                <p><strong>Accès :</strong> Menu → Configuration Entreprise → Modèle de Plan</p>
                
                <h4>Charger le plan SYSCOHADA</h4>
                <p><span class="step-badge">Option 1</span> Plan complet (toutes les classes)</p>
                <p><span class="step-badge">Option 2</span> Plan classe 4 uniquement (Tiers)</p>
                <p><span class="step-badge">Option 3</span> Plan classe 6 uniquement (Charges)</p>
                <p><span class="step-badge">Option 4</span> Plan classe 8 uniquement (Résultat)</p>
                
                <h4>Gérer les comptes</h4>
                <ul>
                    <li><strong>Ajouter</strong> : Créer un nouveau compte</li>
                    <li><strong>Modifier</strong> : Changer le libellé d'un compte</li>
                    <li><strong>Supprimer</strong> : Retirer un compte non utilisé</li>
                </ul>
                
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Attention</strong>
                    Les modifications du plan comptable maître affectent toutes les comptabilités de l'entreprise. Soyez prudent.
                </div>
                
                <h3>3.3 Modèle de Tiers</h3>
                <p><strong>Accès :</strong> Menu → Configuration Entreprise → Modèle de Tiers</p>
                <p>Gérez les tiers (clients, fournisseurs) au niveau entreprise.</p>
                
                <h3>3.4 Modèle des Journaux</h3>
                <p><strong>Accès :</strong> Menu → Configuration Entreprise → Modèle des Journaux</p>
                
                <h4>Charger les journaux standards</h4>
                <p>Cliquez sur "Charger journaux standards" pour créer automatiquement :</p>
                <ul>
                    <li>AC - Achats</li>
                    <li>VE - Ventes</li>
                    <li>BQ - Banque</li>
                    <li>CA - Caisse</li>
                    <li>OD - Opérations Diverses</li>
                </ul>
                
                <h3>3.5 Postes de Trésorerie</h3>
                <p><strong>Accès :</strong> Menu → Configuration Entreprise → Postes de Trésorerie</p>
                <p>Configurez les postes de trésorerie pour le suivi des flux et le TFT.</p>
                
                <h4>Créer un poste</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur "Nouveau poste"</p>
                <p><span class="step-badge">Étape 2</span> Remplissez :</p>
                <ul>
                    <li>Libellé (ex: "Banque SGCI")</li>
                    <li>Compte général (classe 5)</li>
                    <li>Catégorie TFT (Exploitation, Investissement, Financement)</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-magic me-2"></i>Note</strong>
                    Même si aucun poste n'est affecté, le système classera automatiquement les flux (Inv/Fin) selon le compte de contrepartie.
                </div>
            </div>
            
            <!-- SECTION 4 -->
            <div class="guide-section" id="equipe">
                <h2>4. Gestion d'Équipe</h2>
                
                <h3>4.1 Équipe & Permissions</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Équipe & Permissions</p>
                <p>Gérez tous les utilisateurs de votre entreprise.</p>
                
                <h4>Créer un utilisateur</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur "Créer Comptable" ou "Créer Administrateur"</p>
                <p><span class="step-badge">Étape 2</span> Remplissez les informations :</p>
                <ul>
                    <li>Nom complet</li>
                    <li>Email (identifiant de connexion)</li>
                    <li>Mot de passe initial</li>
                    <li>Rôle (Comptable ou Admin secondaire)</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <h3>4.2 Modification des Habilitations</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Modification Habilitation</p>
                <p>Personnalisez les permissions de chaque utilisateur.</p>
                
                <h4>Modifier les permissions</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez un utilisateur</p>
                <p><span class="step-badge">Étape 2</span> Cochez/décochez les permissions :</p>
                <ul>
                    <li><strong>Pilotage</strong> : Accès aux tableaux de bord</li>
                    <li><strong>Paramétrage</strong> : Plan comptable, tiers, journaux</li>
                    <li><strong>Traitement</strong> : Saisie, brouillons, écritures</li>
                    <li><strong>Rapports</strong> : Grand livre, balance</li>
                    <li><strong>États financiers</strong> : Bilan, compte de résultat, TFT</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez les modifications</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-lightbulb me-2"></i>Astuce</strong>
                    Les permissions grisées ne peuvent pas être modifiées car elles sont réservées à certains rôles.
                </div>
            </div>
            
            <!-- SECTION 5 -->
            <div class="guide-section" id="import-export">
                <h2>5. Importation / Exportation</h2>
                
                <h3>5.1 Importation de Données</h3>
                <p><strong>Accès :</strong> Menu → Importation → Importation de données</p>
                
                <h4>Champs de Mapping par type de données</h4>
                
                <div class="row g-4 mt-2 mb-4">
                    <!-- Plan Comptable -->
                    <div class="col-md-6">
                        <div class="card border border-blue-100 shadow-none">
                            <div class="card-body">
                                <h5 class="text-blue-700 font-bold mb-3"><i class="fa-solid fa-list-numeric me-2"></i>Modèle de Plan</h5>
                                <p class="text-[0.7rem] text-muted mb-2">Vérifier la configuration avant d'importer le plan général. Les numéros de compte généraux seront générés par le système en fonction de l'original et du nombre de caractères défini dans la configuration, tout en conservant l'origine pour avoir une trace.</p>
                                <ul class="text-sm space-y-2">
                                    <li><strong>Numéro de compte</strong> <span class="text-muted italic">(Auto-généré)</span></li>
                                    <li><strong>Intitulé du compte *</strong> : Le nom du compte</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modèle de Tiers -->
                    <div class="col-md-6">
                        <div class="card border border-blue-100 shadow-none">
                            <div class="card-body">
                                <h5 class="text-blue-700 font-bold mb-3"><i class="fa-solid fa-users-gear me-2"></i>Modèle de Tiers</h5>
                                <p class="text-[0.7rem] text-muted mb-2">Le Numéro de Tiers est généré automatiquement par le système en fonction du préfixe du tiers original et de la configuration (le nombre de caractères et le type de code). La catégorie ou type est aussi déterminée en fonction du préfixe du tiers original.</p>
                                <ul class="text-sm space-y-2">
                                    <li><strong>Numéro de Tiers</strong> <span class="text-muted italic">(Auto-généré)</span></li>
                                    <li><strong>Nom / Intitulé *</strong> : Nom du client ou fournisseur</li>
                                    <li><strong>Catégorie / Type</strong> <span class="text-muted italic">(Auto-généré)</span></li>
                                    <li><strong>Compte général</strong> <span class="text-muted italic">(Auto-généré)</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Structure Journaux -->
                    <div class="col-md-6">
                        <div class="card border border-blue-100 shadow-none h-100">
                            <div class="card-body">
                                <h5 class="text-blue-700 font-bold mb-3"><i class="fa-solid fa-book me-2"></i>Structure Journaux</h5>
                                <p class="text-[0.7rem] text-muted mb-2">Structure simple pour créer vos codes journaux.</p>
                                <ul class="text-sm space-y-2">
                                    <li><strong>Code Journal *</strong> : Ex: ACH, VEN, BQ1</li>
                                    <li><strong>Intitulé du Journal *</strong> : Libellé complet</li>
                                    <li><strong>Type</strong> : Achats, Ventes, Banque, Caisse, OD <span class="text-muted italic">(Optionnel, défaut: OD)</span></li>
                                </ul>
                                <div class="mt-3 alert alert-secondary p-2 text-xs mb-0">
                                    <i class="fa-solid fa-circle-info me-1"></i> Astuce : Les comptes de trésorerie se configurent après l'import.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Écritures -->
                    <div class="col-md-6">
                        <div class="card border border-blue-100 shadow-none h-100">
                            <div class="card-body">
                                 <h5 class="text-blue-700 font-bold mb-3"><i class="fa-solid fa-pen-to-square me-2"></i>Écritures</h5>
                                    <p class="text-[0.7rem] text-muted mb-2">Les colonnes doivent respecter cet ordre ou avoir des entêtes claires.</p>
                                    <ul class="text-sm space-y-1">
                                        <li><strong>Date *</strong> : JJ/MM/AAAA</li>
                                        <li><strong>Code Journal *</strong> : Doit exister</li>
                                        <li><strong>Numéro Compte *</strong> : Compte général</li>
                                        <li><strong>Libellé Opération *</strong></li>
                                        <li><strong>Débit *</strong> / <strong>Crédit *</strong> : Montants numériques</li>
                                        <li><strong>N° Pièce / Réf</strong> <span class="text-muted italic">(Optionnel)</span></li>
                                        <li><strong>Compte Tiers</strong> <span class="text-muted italic">(Optionnel)</span></li>
                                        <li><strong>N° Saisie</strong> <span class="text-muted italic">(Optionnel)</span></li>
                                        <li><strong>Type (A/G)</strong> <span class="text-muted italic">(Optionnel, défaut: G)</span></li>
                                    </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 mt-4 rounded-2xl d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-info text-2xl"></i>
                    <div>
                        <p class="mb-0 text-sm font-bold">Besoin d'un modèle ?</p>
                        <p class="mb-0 text-xs text-muted">Sur la page d'importation, cliquez sur le bouton <span class="badge bg-primary">Guide d'Importation</span> en haut à droite pour voir les instructions détaillées.</p>
                    </div>
                </div>

                <div class="alert alert-danger border-0 mt-3 rounded-2xl d-flex align-items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    <p class="mb-0 text-sm font-black">NB : Avant l'importation, assurez-vous de faire la configuration pour l'unicité du nombre de caractères et le type.</p>
                </div>
                
                <h3>5.2 Exportation de Données</h3>
                <p><strong>Accès :</strong> Menu → Exportation → Exportation de données</p>
                <p>Exportez vos données comptables vers Excel, CSV ou PDF.</p>
                
                <h4>Types d'export disponibles</h4>
                <ul>
                    <li>Plan comptable</li>
                    <li>Plan tiers</li>
                    <li>Journaux</li>
                    <li>Écritures comptables</li>
                    <li>Grand livre</li>
                    <li>Balance</li>
                </ul>
            </div>
            
            <!-- SECTION 6 -->
            <div class="guide-section" id="fusion">
                <h2>6. Fusion de Données (Sous-entreprises)</h2>
                
                <p><strong>Accès :</strong> Menu → Fusion & Démarrage → Fusion Données Mère</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-info-circle me-2"></i>Qu'est-ce que la fusion ?</strong>
                    Si votre entreprise est une sous-entreprise (filiale), vous pouvez importer les données de l'entreprise mère (plan comptable, tiers, journaux) pour démarrer rapidement.
                </div>
                
                <h3>6.1 Lancer une Fusion</h3>
                <p><span class="step-badge">Étape 1</span> Accédez à la page Fusion</p>
                <p><span class="step-badge">Étape 2</span> Sélectionnez les éléments à fusionner :</p>
                <ul>
                    <li>Plan comptable</li>
                    <li>Plan tiers</li>
                    <li>Journaux</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Cliquez sur "Lancer la fusion"</p>
                <p><span class="step-badge">Étape 4</span> Attendez la fin du processus</p>
                
                <h3>6.2 Réinitialiser une Fusion</h3>
                <p>Si nécessaire, vous pouvez annuler une fusion et recommencer.</p>
            </div>
            
            <!-- SECTION 7 -->
            <div class="guide-section" id="gouvernance">
                <h2>7. Gouvernance</h2>
                
                <h3>7.1 Gestion des Entités</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Gestion des Entités</p>
                <p>Vue d'ensemble de toutes les comptabilités de votre entreprise.</p>
                
                <h3>7.2 Créer une Comptabilité</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Créer Comptabilité</p>
                
                <h4>Procédure</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur "Créer Comptabilité"</p>
                <p><span class="step-badge">Étape 2</span> Remplissez :</p>
                <ul>
                    <li>Nom de la comptabilité</li>
                    <li>Description</li>
                    <li>Exercice de départ</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <h3>7.3 Switch Comptabilité</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Switch Comptabilité</p>
                <p>Basculez entre différentes comptabilités si vous en gérez plusieurs.</p>
                
                <h3>7.4 Créer une Sous-Entreprise</h3>
                <p><strong>Accès :</strong> Menu → Gouvernance → Créer Entreprise</p>
                <p>Créez une filiale rattachée à votre entreprise principale.</p>
            </div>
            
            <!-- SECTION 8 -->
            <div class="guide-section" id="validation">
                <h2>8. Validation & Approbations</h2>
                
                <h3>8.1 Gérer les Approbations</h3>
                <p><strong>Accès :</strong> Menu → Validation → Approbations</p>
                
                <p>Vous pouvez approuver ou rejeter les écritures saisies par vos comptables.</p>
                
                <h4>Approuver une écriture</h4>
                <p><span class="step-badge">Étape 1</span> Consultez la liste des écritures en attente</p>
                <p><span class="step-badge">Étape 2</span> Cliquez sur une écriture pour voir les détails</p>
                <p><span class="step-badge">Étape 3</span> Vérifiez la cohérence comptable</p>
                <p><span class="step-badge">Étape 4</span> Cliquez sur "Approuver" ou "Rejeter"</p>
                <p><span class="step-badge">Étape 5</span> Si rejet, ajoutez un commentaire explicatif</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-lightbulb me-2"></i>Bonne pratique</strong>
                    Lorsque vous rejetez une écriture, expliquez clairement la raison pour que le comptable puisse corriger efficacement.
                </div>
            </div>
            
            <!-- SECTION 9 -->
            <div class="guide-section" id="operations">
                <h2>9. Opérations & Audit</h2>
                
                <h3>9.1 Traçabilité & Activités</h3>
                <p><strong>Accès :</strong> Menu → Opérations → Traçabilité & Activités</p>
                
                <p>Consultez l'historique de toutes les actions effectuées dans le système :</p>
                <ul>
                    <li>Connexions/déconnexions</li>
                    <li>Créations/modifications/suppressions</li>
                    <li>Approbations/rejets</li>
                    <li>Exports de données</li>
                </ul>
                
                <h4>Filtrer l'audit</h4>
                <ul>
                    <li>Par utilisateur</li>
                    <li>Par type d'action</li>
                    <li>Par période</li>
                </ul>
                
                <h3>9.2 Contrôle d'Accès</h3>
                <p><strong>Accès :</strong> Menu → Opérations → Contrôle d'Accès</p>
                
                <p>Bloquez ou débloquez l'accès des utilisateurs temporairement.</p>
                
                <h4>Bloquer un utilisateur</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez l'utilisateur</p>
                <p><span class="step-badge">Étape 2</span> Cliquez sur "Bloquer"</p>
                <p><span class="step-badge">Étape 3</span> L'utilisateur ne pourra plus se connecter</p>
                
                <h4>Débloquer un utilisateur</h4>
                <p>Même procédure, cliquez sur "Débloquer"</p>
            </div>
            
            <!-- SECTION 10 -->
            <div class="guide-section" id="taches">
                <h2>10. Gestion des Tâches</h2>
                
                <h3>10.1 Assigner des Tâches</h3>
                <p><strong>Accès :</strong> Menu → Gestion des Tâches → Assigner Tâche</p>
                
                <h4>Créer une tâche</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur "Nouvelle tâche"</p>
                <p><span class="step-badge">Étape 2</span> Remplissez :</p>
                <ul>
                    <li><strong>Titre</strong> : Nom de la tâche</li>
                    <li><strong>Description</strong> : Instructions détaillées</li>
                    <li><strong>Assigné à</strong> : Sélectionnez un comptable</li>
                    <li><strong>Priorité</strong> : Basse, Normale, Haute</li>
                    <li><strong>Date limite</strong> : Échéance</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <h3>10.2 Suivre les Tâches</h3>
                <p>Consultez l'état d'avancement des tâches assignées :</p>
                <ul>
                    <li><strong>En attente</strong> : Pas encore commencée</li>
                    <li><strong>En cours</strong> : En cours de traitement</li>
                    <li><strong>Terminée</strong> : Complétée par le comptable</li>
                </ul>
                
                <h3>10.3 Vos Tâches Quotidiennes</h3>
                <p><strong>Accès :</strong> Menu → Gestion des Tâches → Tâches Quotidiennes</p>
                <p>Si d'autres administrateurs vous assignent des tâches, elles apparaissent ici.</p>
            </div>
            
            <!-- FOOTER -->
            <div class="guide-section" style="background: #eff6ff; border-left: 4px solid #1e40af;">
                <h3><i class="fa-solid fa-question-circle me-2"></i>Besoin d'aide ?</h3>
                <p>En tant qu'administrateur, vous êtes le pilier de la gestion comptable de votre entreprise. N'hésitez pas à consulter régulièrement ce guide.</p>
                <p class="mb-0"><strong>Pour toute question technique, contactez le support COMPTAFLOW.</strong></p>
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

