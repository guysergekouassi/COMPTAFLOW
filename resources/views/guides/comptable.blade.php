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
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(30, 64, 175, 0.2);
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
    
    .alert-success {
        background: #f0fdf4;
        border-left: 4px solid #10b981;
        padding: 16px 20px;
        border-radius: 8px;
        margin: 20px 0;
    }
    
    .alert-success strong {
        color: #059669;
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
    
    .glossary-term {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin: 12px 0;
    }
    
    .glossary-term strong {
        color: #1e40af;
        font-size: 1.05rem;
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
        <h1><i class="fa-solid fa-book-open me-3"></i>Guide d'utilisation - Comptable</h1>
        <p>Guide complet pour maîtriser COMPTAFLOW, de la première connexion aux états financiers</p>
    </div>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="guide-nav">
                <h3><i class="fa-solid fa-list me-2"></i>Sommaire</h3>
                <ul>
                    <li><a href="#premiere-connexion">1. Première Connexion</a></li>
                    <li><a href="#tableau-bord">2. Tableau de Bord</a></li>
                    <li><a href="#parametrage">3. Paramétrage de Base</a></li>
                    <li><a href="#saisie-ecritures">4. Saisie des Écritures</a></li>
                    <li><a href="#tresorerie">5. Gestion de la Trésorerie</a></li>
                    <li><a href="#lettrage">6. Lettrage des Tiers</a></li>
                    <li><a href="#immobilisations">7. Immobilisations</a></li>
                    <li><a href="#rapports">8. Rapports Comptables</a></li>
                    <li><a href="#etats-financiers">9. États Financiers</a></li>
                    <li><a href="#exercices">10. Exercices Comptables</a></li>
                    <li><a href="#taches">11. Tâches Quotidiennes</a></li>
                    <li><a href="#glossaire">12. Glossaire</a></li>
                </ul>
                <hr class="my-3">
                <button onclick="window.print()" class="btn-print w-100">
                    <i class="fa-solid fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
        
        <div class="col-lg-9">
            <!-- SECTION 1: PREMIÈRE CONNEXION -->
            <div class="guide-section" id="premiere-connexion">
                <h2>1. Première Connexion</h2>
                
                <h3>1.1 Accéder à COMPTAFLOW</h3>
                <p><span class="step-badge">Étape 1</span> Ouvrez votre navigateur web (Chrome, Firefox, Edge ou Safari).</p>
                <p><span class="step-badge">Étape 2</span> Saisissez l'adresse de COMPTAFLOW fournie par votre administrateur.</p>
                <p><span class="step-badge">Étape 3</span> Entrez vos identifiants (email et mot de passe) fournis par votre administrateur.</p>
                <p><span class="step-badge">Étape 4</span> Cliquez sur le bouton <strong>"Se connecter"</strong>.</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-info-circle me-2"></i>Première connexion</strong>
                    Lors de votre première connexion, il est recommandé de changer votre mot de passe. Cliquez sur le rond bleu en haut à droite, puis sur "Paramètres" pour modifier votre mot de passe.
                </div>
                
                <h3>1.2 Découvrir l'Interface</h3>
                <p>Une fois connecté, vous verrez :</p>
                <ul>
                    <li><strong>La barre latérale gauche</strong> : Menu principal avec toutes les fonctionnalités</li>
                    <li><strong>L'en-tête</strong> : Titre de la page actuelle et votre profil (rond bleu avec vos initiales)</li>
                    <li><strong>Le contenu principal</strong> : Zone de travail où s'affichent les informations et formulaires</li>
                </ul>
                
                <h3>1.3 Navigation de Base</h3>
                <p><strong>Menu latéral :</strong> Cliquez sur n'importe quel élément du menu pour accéder à la fonctionnalité correspondante.</p>
                <p><strong>Profil utilisateur :</strong> Cliquez sur le rond bleu en haut à droite pour accéder à :</p>
                <ul>
                    <li>Notifications</li>
                    <li>Mon profil</li>
                    <li>Paramètres</li>
                    <li>Guide d'utilisation (ce guide)</li>
                    <li>Déconnexion</li>
                </ul>
            </div>
            
            <!-- SECTION 2: TABLEAU DE BORD -->
            <div class="guide-section" id="tableau-bord">
                <h2>2. Tableau de Bord Personnel</h2>
                
                <h3>2.1 Vue d'ensemble</h3>
                <p>Le tableau de bord est votre page d'accueil. Il affiche un résumé de l'activité comptable :</p>
                <ul>
                    <li><strong>Indicateurs clés (KPI)</strong> : Nombre d'écritures, soldes, etc.</li>
                    <li><strong>Graphiques</strong> : Évolution des écritures, répartition par journal</li>
                    <li><strong>Activités récentes</strong> : Dernières écritures saisies</li>
                    <li><strong>Tâches en attente</strong> : Tâches qui vous sont assignées</li>
                </ul>
                
                <h3>2.2 Sélecteur d'Exercice</h3>
                <p>En haut du menu latéral, vous verrez un bouton avec l'exercice comptable actif (ex: "Exercice 2026").</p>
                <p><span class="step-badge">Astuce</span> Cliquez dessus pour changer d'exercice si vous devez consulter ou saisir des données sur un autre exercice.</p>
                
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Attention</strong>
                    Toutes vos saisies seront enregistrées dans l'exercice sélectionné. Vérifiez toujours que vous êtes sur le bon exercice avant de saisir des écritures.
                </div>
            </div>
            
            <!-- SECTION 3: PARAMÉTRAGE -->
            <div class="guide-section" id="parametrage">
                <h2>3. Paramétrage de Base</h2>
                
                <p>Avant de commencer à saisir des écritures, familiarisez-vous avec les éléments de base de la comptabilité dans COMPTAFLOW.</p>
                
                <h3>3.1 Plan Comptable</h3>
                <p><strong>Accès :</strong> Menu latéral → Paramétrage → Plan comptable</p>
                <p>Le plan comptable contient tous les comptes utilisés pour enregistrer les opérations de l'entreprise.</p>
                
                <h4>Structure des comptes</h4>
                <p>Les comptes sont organisés en classes selon le référentiel SYSCOHADA :</p>
                <ul>
                    <li><strong>Classe 1</strong> : Comptes de ressources durables (Capital, Emprunts)</li>
                    <li><strong>Classe 2</strong> : Comptes d'actif immobilisé (Immobilisations)</li>
                    <li><strong>Classe 3</strong> : Comptes de stocks</li>
                    <li><strong>Classe 4</strong> : Comptes de tiers (Clients, Fournisseurs)</li>
                    <li><strong>Classe 5</strong> : Comptes de trésorerie (Banque, Caisse)</li>
                    <li><strong>Classe 6</strong> : Comptes de charges (Achats, Salaires)</li>
                    <li><strong>Classe 7</strong> : Comptes de produits (Ventes, Prestations)</li>
                    <li><strong>Classe 8</strong> : Comptes de résultat</li>
                </ul>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce qu'un compte comptable ?</strong>
                    <p class="mb-0">Un compte comptable est un code numérique (ex: 411000) qui représente une catégorie d'opération. Par exemple, le compte 411000 représente les "Clients".</p>
                </div>
                
                <h4>Consulter le plan comptable</h4>
                <p><span class="step-badge">Étape 1</span> Allez dans Paramétrage → Plan comptable</p>
                <p><span class="step-badge">Étape 2</span> Utilisez la barre de recherche pour trouver un compte par son numéro ou son libellé</p>
                <p><span class="step-badge">Étape 3</span> Cliquez sur un compte pour voir ses détails</p>
                
                <h3>3.2 Plan Tiers</h3>
                <p><strong>Accès :</strong> Menu latéral → Paramétrage → Plan tiers</p>
                <p>Le plan tiers contient la liste de vos clients, fournisseurs et autres partenaires.</p>
                
                <h4>Créer un nouveau tiers</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur le bouton <strong>"Nouveau tiers"</strong></p>
                <p><span class="step-badge">Étape 2</span> Remplissez les informations :</p>
                <ul>
                    <li><strong>Compte</strong> : Numéro du compte (ex: 411001 pour un client)</li>
                    <li><strong>Nom</strong> : Raison sociale du tiers</li>
                    <li><strong>Type</strong> : Client, Fournisseur, ou Autre</li>
                    <li><strong>Contact</strong> : Téléphone, email (optionnel)</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Cliquez sur <strong>"Enregistrer"</strong></p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-lightbulb me-2"></i>Astuce</strong>
                    Les comptes clients commencent généralement par 411, et les comptes fournisseurs par 401. Le système peut proposer automatiquement le prochain numéro disponible.
                </div>
                
                <h3>3.3 Journaux Comptables</h3>
                <p><strong>Accès :</strong> Menu latéral → Paramétrage → Journaux</p>
                <p>Les journaux permettent de classer les écritures par type d'opération.</p>
                
                <h4>Types de journaux courants</h4>
                <ul>
                    <li><strong>AC (Achats)</strong> : Factures fournisseurs</li>
                    <li><strong>VE (Ventes)</strong> : Factures clients</li>
                    <li><strong>BQ (Banque)</strong> : Opérations bancaires</li>
                    <li><strong>CA (Caisse)</strong> : Opérations en espèces</li>
                    <li><strong>OD (Opérations Diverses)</strong> : Autres opérations</li>
                </ul>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce qu'un journal ?</strong>
                    <p class="mb-0">Un journal est un registre qui regroupe toutes les écritures d'un même type. Par exemple, toutes les factures de vente sont enregistrées dans le journal "VE".</p>
                </div>
                
                <h3>3.4 Postes de Trésorerie</h3>
                <p><strong>Accès :</strong> Menu latéral → Paramétrage → Poste Trésorerie</p>
                <p>Les postes de trésorerie permettent de suivre les mouvements de trésorerie (banque, caisse) et de générer le Tableau des Flux de Trésorerie (TFT).</p>
                
                <h4>Créer un poste de trésorerie</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur <strong>"Nouveau poste"</strong></p>
                <p><span class="step-badge">Étape 2</span> Remplissez :</p>
                <ul>
                    <li><strong>Libellé</strong> : Nom du poste (ex: "Banque SGCI")</li>
                    <li><strong>Compte général</strong> : Compte de trésorerie (classe 5)</li>
                    <li><strong>Catégorie TFT</strong> : Exploitation, Investissement ou Financement</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
            </div>
            
            <!-- SECTION 4: SAISIE DES ÉCRITURES -->
            <div class="guide-section" id="saisie-ecritures">
                <h2>4. Saisie des Écritures Comptables</h2>
                
                <h3>4.1 Comprendre une Écriture Comptable</h3>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce qu'une écriture comptable ?</strong>
                    <p class="mb-0">Une écriture comptable est l'enregistrement d'une opération dans les comptes de l'entreprise. Chaque écriture respecte le principe de la partie double : le total des débits doit toujours égaler le total des crédits.</p>
                </div>
                
                <h4>Principe de la partie double</h4>
                <p>Chaque opération affecte au moins deux comptes :</p>
                <ul>
                    <li><strong>Débit</strong> : Augmentation d'un actif ou diminution d'un passif</li>
                    <li><strong>Crédit</strong> : Diminution d'un actif ou augmentation d'un passif</li>
                </ul>
                
                <div class="alert-success">
                    <strong><i class="fa-solid fa-check-circle me-2"></i>Exemple simple</strong>
                    Achat de marchandises à crédit pour 100 000 FCFA :
                    <ul class="mb-0 mt-2">
                        <li>Débit : 601000 (Achats de marchandises) → 100 000</li>
                        <li>Crédit : 401000 (Fournisseurs) → 100 000</li>
                    </ul>
                </div>
                
                <h3>4.2 Saisie Rapide (Modale)</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Nouvelle saisie</p>
                <p>La saisie rapide permet d'enregistrer une écriture simple en quelques clics.</p>
                
                <h4>Procédure de saisie</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur <strong>"Nouvelle saisie"</strong> dans le menu</p>
                <p><span class="step-badge">Étape 2</span> Une fenêtre s'ouvre. Remplissez :</p>
                <ul>
                    <li><strong>Journal</strong> : Sélectionnez le journal approprié (AC, VE, BQ, etc.)</li>
                    <li><strong>Date</strong> : Date de l'opération</li>
                    <li><strong>Pièce</strong> : Numéro de la facture ou du document</li>
                </ul>
                
                <p><span class="step-badge">Étape 3</span> Ajoutez les lignes d'écriture :</p>
                <ul>
                    <li><strong>Compte général</strong> : Sélectionnez le compte (tapez le numéro ou le nom)</li>
                    <li><strong>Compte tiers</strong> : Si c'est un client ou fournisseur, sélectionnez-le</li>
                    <li><strong>Libellé</strong> : Description de l'opération</li>
                    <li><strong>Débit ou Crédit</strong> : Montant (vous ne pouvez remplir qu'un seul des deux)</li>
                </ul>
                
                <p><span class="step-badge">Étape 4</span> Cliquez sur <strong>"Ajouter ligne"</strong> pour ajouter d'autres lignes</p>
                <p><span class="step-badge">Étape 5</span> Vérifiez que Total Débit = Total Crédit</p>
                <p><span class="step-badge">Étape 6</span> Cliquez sur <strong>"Enregistrer"</strong></p>
                
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Important</strong>
                    Si le total des débits ne correspond pas au total des crédits, l'écriture ne pourra pas être enregistrée. Vérifiez vos montants avant de valider.
                </div>
                
                <h3>4.3 Saisie Détaillée</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Liste des écritures → Nouvelle écriture</p>
                <p>La saisie détaillée offre plus d'options et permet de gérer des écritures complexes.</p>
                
                <h4>Fonctionnalités supplémentaires</h4>
                <ul>
                    <li><strong>Poste de trésorerie</strong> : Pour les comptes de classe 5</li>
                    <li><strong>Échéance</strong> : Date de paiement prévue</li>
                    <li><strong>Analytique</strong> : Affectation à un centre de coût (si activé)</li>
                </ul>
                
                <h3>4.4 Gestion des Brouillons</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Brouillons</p>
                <p>Les brouillons sont des écritures en cours de saisie que vous pouvez sauvegarder temporairement.</p>
                
                <h4>Utilisation des brouillons</h4>
                <p><span class="step-badge">Sauvegarder</span> Lors de la saisie, cliquez sur "Enregistrer comme brouillon"</p>
                <p><span class="step-badge">Reprendre</span> Allez dans Brouillons, cliquez sur le brouillon pour le modifier</p>
                <p><span class="step-badge">Valider</span> Une fois complété, cliquez sur "Valider" pour l'enregistrer définitivement</p>
                <p><span class="step-badge">Supprimer</span> Si vous ne souhaitez plus utiliser un brouillon, supprimez-le</p>
                
                <h3>4.5 Écritures Rejetées</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Écritures rejetées</p>
                <p>Si votre administrateur rejette une écriture, elle apparaîtra ici avec un commentaire expliquant la raison du rejet.</p>
                
                <h4>Traiter une écriture rejetée</h4>
                <p><span class="step-badge">Étape 1</span> Consultez le commentaire de rejet</p>
                <p><span class="step-badge">Étape 2</span> Corrigez l'écriture selon les indications</p>
                <p><span class="step-badge">Étape 3</span> Soumettez à nouveau pour validation</p>
                
                <h3>4.6 Liste des Écritures</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Liste des écritures</p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h4>Filtres disponibles</h4>
                        <ul>
                            <li><strong>Période</strong> : Date de début et de fin</li>
                            <li><strong>Journal</strong> : Filtrer par type de journal</li>
                            <li><strong>Compte</strong> : Afficher uniquement un compte spécifique</li>
                            <li><strong>Statut</strong> : Validé, En attente, Rejeté</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4>Actions possibles</h4>
                        <ul>
                            <li><strong>Consulter</strong> : Voir les détails</li>
                            <li><strong>Modifier</strong> : Si non validée</li>
                            <li><strong>Supprimer</strong> : Selon vos permissions</li>
                            <li><strong>Exporter</strong> : Excel ou PDF</li>
                        </ul>
                    </div>
                </div>
                
                <h4>4.7 Importation d'Écritures (Mapping)</h4>
                <div class="bg-amber-50 p-6 rounded-2xl mb-4">
                    <h6 class="font-black text-amber-900 mb-2">Structure de mapping pour l'importation</h6>
                    <p class="text-sm text-amber-700 mb-3">Pour importer des écritures en masse, vos colonnes doivent correspondre aux champs suivants :</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white text-sm">
                            <thead class="bg-amber-100">
                                <tr>
                                    <th>Champ de Mapping</th>
                                    <th>Description / Format</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Date / Jour *</strong></td><td>Date de l'opération (JJ/MM/AAAA)</td></tr>
                                <tr><td><strong>N° Saisie / Écriture</strong></td><td>Numéro séquentiel (Auto)</td></tr>
                                <tr><td><strong>Code Journal *</strong></td><td>Code du journal (ex: ACH, VEN)</td></tr>
                                <tr><td><strong>Référence Pièce</strong></td><td>N° de facture ou référence</td></tr>
                                <tr><td><strong>Numéro Compte *</strong></td><td>N° de compte général</td></tr>
                                <tr><td><strong>Libellé Opération *</strong></td><td>Description de l'écriture</td></tr>
                                <tr><td><strong>Montant Débit *</strong> / <strong>Montant Crédit *</strong></td><td>Montants respectifs</td></tr>
                                <tr><td><strong>Compte Tiers</strong></td><td>Numéro de tiers (Facultatif)</td></tr>
                                <tr><td><strong>Type Écrit. (A/G)</strong></td><td>Analytique / Général</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="alert alert-warning border-0 text-sm">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>Attention :</strong> Les Journaux et Comptes doivent exister dans le système avant l'importation.
                </div>
            </div>
            
            <!-- SECTION 5: TRÉSORERIE -->
            <div class="guide-section" id="tresorerie">
                <h2>5. Gestion de la Trésorerie</h2>
                
                <h3>5.1 Comprendre la Trésorerie</h3>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce que la trésorerie ?</strong>
                    <p class="mb-0">La trésorerie représente l'ensemble des liquidités disponibles de l'entreprise : comptes bancaires, caisses, chèques à encaisser, etc.</p>
                </div>
                
                <h3>5.2 Saisir une Opération de Trésorerie</h3>
                <p>Lors de la saisie d'une écriture impliquant un compte de classe 5 (banque, caisse), le champ "Poste Trésorerie" devient actif.</p>
                
                <h4>Exemple : Encaissement d'une facture client</h4>
                <p><span class="step-badge">Étape 1</span> Nouvelle saisie → Journal BQ (Banque)</p>
                <p><span class="step-badge">Étape 2</span> Ligne 1 :</p>
                <ul>
                    <li>Compte général : 521000 (Banque)</li>
                    <li>Poste Trésorerie : Sélectionnez votre banque</li>
                    <li>Débit : 100 000</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Ligne 2 :</p>
                <ul>
                    <li>Compte général : 411000 (Clients)</li>
                    <li>Compte tiers : Sélectionnez le client</li>
                    <li>Crédit : 100 000</li>
                </ul>
                <p><span class="step-badge">Étape 4</span> Enregistrez</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-info-circle me-2"></i>Bon à savoir</strong>
                    Le poste de trésorerie permet de générer automatiquement le Tableau des Flux de Trésorerie (TFT), un état financier obligatoire.
                </div>
                
                <h3>5.3 Consulter les Postes de Trésorerie</h3>
                <p><strong>Accès :</strong> Menu latéral → Paramétrage → Poste Trésorerie</p>
                <p>Vous pouvez consulter le solde de chaque poste de trésorerie et l'historique des mouvements.</p>
            </div>
            
            <!-- SECTION 6: LETTRAGE -->
            <div class="guide-section" id="lettrage">
                <h2>6. Lettrage des Tiers</h2>
                
                <h3>6.1 Qu'est-ce que le Lettrage ?</h3>
                
                <div class="glossary-term">
                    <strong>Définition du lettrage</strong>
                    <p class="mb-0">Le lettrage consiste à rapprocher une facture de son règlement. Cela permet de savoir quelles factures sont payées et lesquelles sont encore dues.</p>
                </div>
                
                <h3>6.2 Lettrer un Compte Client</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Lettrage des Tiers</p>
                
                <h4>Procédure de lettrage</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez un compte tiers (client ou fournisseur)</p>
                <p><span class="step-badge">Étape 2</span> Le système affiche toutes les écritures non lettrées de ce compte</p>
                <p><span class="step-badge">Étape 3</span> Cochez les écritures qui se compensent :</p>
                <ul>
                    <li>Une facture au débit (ce que le client doit)</li>
                    <li>Un règlement au crédit (ce que le client a payé)</li>
                </ul>
                <p><span class="step-badge">Étape 4</span> Vérifiez que le total débit = total crédit</p>
                <p><span class="step-badge">Étape 5</span> Cliquez sur <strong>"Lettrer"</strong></p>
                
                <div class="alert-success">
                    <strong><i class="fa-solid fa-check-circle me-2"></i>Exemple</strong>
                    <ul class="mb-0">
                        <li>Facture client n°001 : 100 000 FCFA (débit)</li>
                        <li>Règlement client : 100 000 FCFA (crédit)</li>
                        <li>→ Ces deux lignes peuvent être lettrées ensemble</li>
                    </ul>
                </div>
                
                <h3>6.3 Délettrer</h3>
                <p>Si vous avez lettré par erreur, vous pouvez délettrer en cliquant sur le bouton "Délettrer" à côté de l'écriture concernée.</p>
                
                <h3>6.4 Lettrage Automatique</h3>
                <p>Le système peut proposer automatiquement des lettrages lorsque les montants correspondent exactement. Vérifiez toujours avant de valider.</p>
            </div>
            
            <!-- SECTION 7: IMMOBILISATIONS -->
            <div class="guide-section" id="immobilisations">
                <h2>7. Gestion des Immobilisations</h2>
                
                <h3>7.1 Qu'est-ce qu'une Immobilisation ?</h3>
                
                <div class="glossary-term">
                    <strong>Définition</strong>
                    <p class="mb-0">Une immobilisation est un bien durable utilisé par l'entreprise sur plusieurs années (véhicule, ordinateur, bâtiment, etc.). Sa valeur diminue chaque année par l'amortissement.</p>
                </div>
                
                <h3>7.2 Créer une Immobilisation</h3>
                <p><strong>Accès :</strong> Menu latéral → Traitement → Immobilisations</p>
                
                <h4>Procédure</h4>
                <p><span class="step-badge">Étape 1</span> Cliquez sur <strong>"Nouvelle immobilisation"</strong></p>
                <p><span class="step-badge">Étape 2</span> Remplissez les informations :</p>
                <ul>
                    <li><strong>Libellé</strong> : Description du bien (ex: "Véhicule Toyota Corolla")</li>
                    <li><strong>Compte</strong> : Compte d'immobilisation (classe 2)</li>
                    <li><strong>Date d'acquisition</strong> : Date d'achat</li>
                    <li><strong>Valeur d'acquisition</strong> : Prix d'achat TTC</li>
                    <li><strong>Durée d'amortissement</strong> : Nombre d'années (ex: 5 ans pour un véhicule)</li>
                    <li><strong>Mode d'amortissement</strong> : Linéaire ou Dégressif</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Enregistrez</p>
                
                <div class="glossary-term">
                    <strong>Amortissement linéaire vs dégressif</strong>
                    <p class="mb-0"><strong>Linéaire :</strong> La même somme est déduite chaque année (ex: 20% par an sur 5 ans).<br>
                    <strong>Dégressif :</strong> Les premières années, on déduit plus, puis moins les années suivantes.</p>
                </div>
                
                <h3>7.3 Générer les Dotations aux Amortissements</h3>
                <p>À la fin de chaque exercice, vous devez générer les écritures d'amortissement.</p>
                
                <p><span class="step-badge">Étape 1</span> Allez dans Immobilisations</p>
                <p><span class="step-badge">Étape 2</span> Cliquez sur <strong>"Générer les dotations"</strong></p>
                <p><span class="step-badge">Étape 3</span> Sélectionnez l'exercice</p>
                <p><span class="step-badge">Étape 4</span> Le système calcule automatiquement les dotations et génère les écritures</p>
                
                <h3>7.4 Tableau d'Amortissement</h3>
                <p>Pour chaque immobilisation, vous pouvez consulter et exporter le tableau d'amortissement qui détaille année par année la valeur résiduelle du bien.</p>
            </div>
            
            <!-- SECTION 8: RAPPORTS -->
            <div class="guide-section" id="rapports">
                <h2>8. Rapports Comptables</h2>
                
                <h3>8.1 Grand Livre</h3>
                <p><strong>Accès :</strong> Menu latéral → Rapports → Grand livre</p>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce que le Grand Livre ?</strong>
                    <p class="mb-0">Le Grand Livre présente l'historique complet de tous les mouvements d'un ou plusieurs comptes sur une période donnée.</p>
                </div>
                
                <h4>Générer un Grand Livre</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez la période (date de début et de fin)</p>
                <p><span class="step-badge">Étape 2</span> Choisissez les comptes :</p>
                <ul>
                    <li><strong>Tous les comptes</strong> : Grand Livre complet</li>
                    <li><strong>Plage de comptes</strong> : Ex: de 411000 à 419999 (tous les clients)</li>
                    <li><strong>Compte spécifique</strong> : Un seul compte</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Cliquez sur <strong>"Générer"</strong></p>
                <p><span class="step-badge">Étape 4</span> Prévisualisez le rapport</p>
                <p><span class="step-badge">Étape 5</span> Exportez en PDF ou Excel</p>
                
                <h3>8.2 Balance</h3>
                <p><strong>Accès :</strong> Menu latéral → Rapports → Balance</p>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce que la Balance ?</strong>
                    <p class="mb-0">La Balance récapitule pour chaque compte les totaux débit, crédit et solde sur une période. C'est un outil de contrôle essentiel.</p>
                </div>
                
                <h4>Générer une Balance</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez la période</p>
                <p><span class="step-badge">Étape 2</span> Choisissez le niveau de détail :</p>
                <ul>
                    <li><strong>Balance à 4 chiffres</strong> : Comptes agrégés</li>
                    <li><strong>Balance à 6 chiffres</strong> : Comptes détaillés</li>
                    <li><strong>Balance à 8 chiffres</strong> : Maximum de détails</li>
                </ul>
                <p><span class="step-badge">Étape 3</span> Générez et exportez</p>
                
                <div class="alert-info">
                    <strong><i class="fa-solid fa-lightbulb me-2"></i>Astuce</strong>
                    La balance doit toujours être équilibrée : Total Débit = Total Crédit. Si ce n'est pas le cas, il y a une erreur de saisie.
                </div>
            </div>
            
            <!-- SECTION 9: ÉTATS FINANCIERS -->
            <div class="guide-section" id="etats-financiers">
                <h2>9. États Financiers</h2>
                
                <p>Les états financiers sont des documents de synthèse obligatoires qui présentent la situation financière de l'entreprise.</p>
                
                <h3>9.1 Bilan Actif/Passif</h3>
                <p><strong>Accès :</strong> Menu latéral → ETATS FINANCIERS → Bilan Actif/Passif</p>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce que le Bilan ?</strong>
                    <p class="mb-0">Le Bilan est une photographie du patrimoine de l'entreprise à une date donnée. Il se compose de l'Actif (ce que possède l'entreprise) et du Passif (ce que doit l'entreprise).</p>
                </div>
                
                <h4>Structure du Bilan</h4>
                <p><strong>ACTIF :</strong></p>
                <ul>
                    <li>Actif immobilisé (Immobilisations)</li>
                    <li>Actif circulant (Stocks, Créances clients, Trésorerie)</li>
                </ul>
                
                <p><strong>PASSIF :</strong></p>
                <ul>
                    <li>Capitaux propres (Capital, Réserves, Résultat)</li>
                    <li>Dettes (Emprunts, Dettes fournisseurs)</li>
                </ul>
                
                <h4>Consulter le Bilan</h4>
                <p><span class="step-badge">Étape 1</span> Sélectionnez l'exercice</p>
                <p><span class="step-badge">Étape 2</span> Le bilan s'affiche automatiquement</p>
                <p><span class="step-badge">Étape 3</span> Exportez en PDF pour impression</p>
                
                <h3>9.2 Compte de Résultat</h3>
                <p><strong>Accès :</strong> Menu latéral → ETATS FINANCIERS → Compte de Résultat</p>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce que le Compte de Résultat ?</strong>
                    <p class="mb-0">Le Compte de Résultat présente les produits (revenus) et les charges (dépenses) de l'entreprise sur une période, permettant de calculer le résultat (bénéfice ou perte).</p>
                </div>
                
                <h4>Structure du Compte de Résultat</h4>
                <ul>
                    <li><strong>Produits</strong> : Ventes, Prestations (Classe 7)</li>
                    <li><strong>Charges</strong> : Achats, Salaires, Loyers (Classe 6)</li>
                    <li><strong>Résultat</strong> : Produits - Charges</li>
                </ul>
                
                <h3>9.3 Résultat Mensuel</h3>
                <p><strong>Accès :</strong> Menu latéral → ETATS FINANCIERS → Résultat Mensuel</p>
                <p>Affiche le résultat mois par mois pour suivre l'évolution de la performance.</p>
                
                <h3>9.4 Tableau des Flux de Trésorerie (TFT)</h3>
                <p><strong>Accès :</strong> Menu latéral → ETATS FINANCIERS → Flux de Trésorerie (TFT)</p>
                
                <div class="glossary-term">
                    <strong>Qu'est-ce que le TFT ?</strong>
                    <p class="mb-0">Le TFT explique les variations de trésorerie en classant les flux en trois catégories : Exploitation, Investissement et Financement.</p>
                </div>
                
                <h4>Les 3 Règles d'Or du Calcul</h4>
                <ul>
                    <li><strong>1. Le Classement</strong> :
                        <ul>
                            <li><strong>Priorité 1</strong> : Poste de Trésorerie affecté manuellement.</li>
                            <li><strong>Priorité 2 (Secours Automatique)</strong> : Si aucun poste, l'IA affecte selon la contrepartie (Classe 2 → Investissement, Classe 1 → Financement).</li>
                            <li><strong>Défaut</strong> : Tout le reste va en Exploitation (Opérationnel).</li>
                        </ul>
                    </li>
                    <li><strong>2. Le Sens (+ ou -)</strong> : Débit Banque = Encaissement (+), Crédit Banque = Décaissement (-).</li>
                    <li><strong>3. L'Affichage</strong> : Le tableau montre le compte de contrepartie (ex: Fournisseur) pour plus de clarté.</li>
                </ul>
                
                <div class="alert-success">
                    <strong><i class="fa-solid fa-magic me-2"></i>Nouveauté : Imports Automatiques</strong>
                    Plus besoin d'affecter un poste à chaque ligne ! Lors de vos imports, le système classe automatiquement les Achats d'Immobilisations (Comptes 2xxx) et les Emprunts (Comptes 16xx) dans les bonnes sections.
                </div>

                <div class="alert-info">
                    <strong><i class="fa-solid fa-info-circle me-2"></i>Note sur les Écarts</strong>
                    Le TFT Mensuel suit les <strong>encaissements réels</strong>. Il peut différer du TFT Annuel (Normal) qui inclut des flux théoriques basés sur les factures non payées.
                </div>
            </div>
            
            <!-- SECTION 10: EXERCICES -->
            <div class="guide-section" id="exercices">
                <h2>10. Exercices Comptables</h2>
                
                <h3>10.1 Qu'est-ce qu'un Exercice Comptable ?</h3>
                
                <div class="glossary-term">
                    <strong>Définition</strong>
                    <p class="mb-0">Un exercice comptable est une période de 12 mois pendant laquelle l'entreprise enregistre ses opérations. Généralement, il correspond à l'année civile (1er janvier au 31 décembre).</p>
                </div>
                
                <h3>10.2 Changer d'Exercice</h3>
                <p>Pour consulter ou saisir des données sur un exercice différent :</p>
                
                <p><span class="step-badge">Méthode 1</span> Cliquez sur le sélecteur d'exercice en haut du menu latéral</p>
                <p><span class="step-badge">Méthode 2</span> Sélectionnez l'exercice souhaité dans la liste</p>
                <p><span class="step-badge">Méthode 3</span> Toutes vos actions seront désormais dans cet exercice</p>
                
                <div class="alert-warning">
                    <strong><i class="fa-solid fa-exclamation-triangle me-2"></i>Attention</strong>
                    Vérifiez toujours l'exercice sélectionné avant de saisir des écritures. Une écriture dans le mauvais exercice peut fausser les états financiers.
                </div>
                
                <h3>10.3 Quitter le Contexte d'Exercice</h3>
                <p>Pour revenir à l'exercice par défaut (exercice actif), cliquez sur "Quitter le contexte" dans le sélecteur d'exercice.</p>
                
                <h3>10.4 Clôture d'Exercice</h3>
                <p>La clôture d'exercice est généralement effectuée par l'administrateur. Une fois clôturé, un exercice ne peut plus être modifié.</p>
            </div>
            
            <!-- SECTION 11: TÂCHES -->
            <div class="guide-section" id="taches">
                <h2>11. Tâches Quotidiennes</h2>
                
                <h3>11.1 Consulter vos Tâches</h3>
                <p><strong>Accès :</strong> Menu latéral → Gestion des Tâches → Tâches Quotidiennes</p>
                
                <p>Votre administrateur peut vous assigner des tâches (saisies à effectuer, vérifications, etc.).</p>
                
                <h3>11.2 Marquer une Tâche comme Terminée</h3>
                <p><span class="step-badge">Étape 1</span> Consultez la liste de vos tâches</p>
                <p><span class="step-badge">Étape 2</span> Cliquez sur une tâche pour voir les détails</p>
                <p><span class="step-badge">Étape 3</span> Une fois la tâche accomplie, cochez "Terminé"</p>
                <p><span class="step-badge">Étape 4</span> Ajoutez un commentaire si nécessaire</p>
                <p><span class="step-badge">Étape 5</span> Enregistrez</p>
                
                <h3>11.3 Notifications</h3>
                <p>Vous recevez des notifications lorsque :</p>
                <ul>
                    <li>Une nouvelle tâche vous est assignée</li>
                    <li>Une écriture est rejetée</li>
                    <li>Un message important de l'administrateur</li>
                </ul>
                
                <p>Cliquez sur l'icône cloche en haut à droite pour consulter vos notifications.</p>
            </div>
            
            <!-- SECTION 12: GLOSSAIRE -->
            <div class="guide-section" id="glossaire">
                <h2>12. Glossaire des Termes Comptables</h2>
                
                <div class="glossary-term">
                    <strong>Actif</strong>
                    <p class="mb-0">Ensemble des biens et créances de l'entreprise (ce qu'elle possède).</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Amortissement</strong>
                    <p class="mb-0">Répartition du coût d'une immobilisation sur sa durée d'utilisation.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Balance</strong>
                    <p class="mb-0">Document récapitulatif présentant pour chaque compte les totaux débit, crédit et solde.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Bilan</strong>
                    <p class="mb-0">État financier présentant l'actif et le passif de l'entreprise à une date donnée.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Charge</strong>
                    <p class="mb-0">Dépense de l'entreprise (achats, salaires, loyers, etc.). Classe 6.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Compte de Résultat</strong>
                    <p class="mb-0">État financier présentant les produits et charges sur une période.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Crédit</strong>
                    <p class="mb-0">Colonne de droite dans une écriture comptable. Diminution d'un actif ou augmentation d'un passif.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Débit</strong>
                    <p class="mb-0">Colonne de gauche dans une écriture comptable. Augmentation d'un actif ou diminution d'un passif.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Écriture comptable</strong>
                    <p class="mb-0">Enregistrement d'une opération dans les comptes selon le principe de la partie double.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Exercice comptable</strong>
                    <p class="mb-0">Période de 12 mois pendant laquelle l'entreprise enregistre ses opérations.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Grand Livre</strong>
                    <p class="mb-0">Document présentant l'historique complet des mouvements d'un ou plusieurs comptes.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Immobilisation</strong>
                    <p class="mb-0">Bien durable utilisé par l'entreprise sur plusieurs années. Classe 2.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Journal</strong>
                    <p class="mb-0">Registre regroupant les écritures d'un même type (achats, ventes, banque, etc.).</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Lettrage</strong>
                    <p class="mb-0">Rapprochement d'une facture et de son règlement.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Partie double</strong>
                    <p class="mb-0">Principe comptable selon lequel chaque opération affecte au moins deux comptes, avec Total Débit = Total Crédit.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Passif</strong>
                    <p class="mb-0">Ensemble des ressources de l'entreprise (capital, dettes).</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Plan comptable</strong>
                    <p class="mb-0">Liste de tous les comptes utilisés par l'entreprise.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Produit</strong>
                    <p class="mb-0">Revenu de l'entreprise (ventes, prestations, etc.). Classe 7.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>SYSCOHADA</strong>
                    <p class="mb-0">Système Comptable OHADA, référentiel comptable utilisé dans les pays de l'OHADA.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Tiers</strong>
                    <p class="mb-0">Client, fournisseur ou autre partenaire de l'entreprise.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>TFT (Tableau des Flux de Trésorerie)</strong>
                    <p class="mb-0">État financier expliquant les variations de trésorerie.</p>
                </div>
                
                <div class="glossary-term">
                    <strong>Trésorerie</strong>
                    <p class="mb-0">Ensemble des liquidités disponibles (banque, caisse). Classe 5.</p>
                </div>
            </div>
            
            <!-- FOOTER -->
            <div class="guide-section" style="background: #eff6ff; border-left: 4px solid #1e40af;">
                <h3><i class="fa-solid fa-question-circle me-2"></i>Besoin d'aide ?</h3>
                <p>Si vous avez des questions ou rencontrez des difficultés, n'hésitez pas à contacter votre administrateur ou à consulter ce guide régulièrement.</p>
                <p class="mb-0"><strong>Bonne utilisation de COMPTAFLOW !</strong></p>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth scroll pour les liens d'ancrage
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
