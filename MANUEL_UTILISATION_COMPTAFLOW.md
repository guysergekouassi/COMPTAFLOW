# 📘 MANUEL D'UTILISATION COMPLET - COMPTAFLOW

Bienvenue dans le manuel officiel de **COMPTAFLOW**, la solution de gestion comptable SYSCOHADA multi-entités. Ce document détaille les fonctionnalités accessibles pour chaque niveau d'utilisateur.

---

## 📑 SOMMAIRE

1.  [Introduction & Connexion](#1-introduction--connexion)
2.  [Niveau 1 : Le COMPTABLE (Opérationnel)](#2-niveau-1--le-comptable-opérationnel)
3.  [Niveau 2 : L'ADMINISTRATEUR (Gestionnaire)](#3-niveau-2--ladministrateur-gestionnaire)
4.  [Niveau 3 : Le SUPER ADMIN (Gouverneur)](#4-niveau-3--le-super-admin-gouverneur)
5.  [Modules Transversaux](#5-modules-transversaux)

---

## 1. Introduction & Connexion

L'accès à COMPTAFLOW est sécurisé et nécessite un compte utilisateur.
*   **Page de connexion :** Entrez votre email et votre mot de passe fournis par votre administrateur.
*   **Redirection automatique :** Une fois connecté, le logiciel vous dirige automatiquement vers votre tableau de bord spécifique (Comptable, Admin ou Super Admin).

---

## 2. Niveau 1 : Le COMPTABLE (Opérationnel)

**Cible :** L'utilisateur quotidien qui saisit les données, gère la trésorerie et édite les états financiers de base.

### 🏠 Tableau de Bord Comptable
Une vue synthétique de l'activité immédiate :
*   **Dernières écritures :** Accès rapide aux 5 dernières saisies.
*   **Notifications :** Alertes sur les tâches assignées ou les écritures rejetées.
*   **État de la saisie :** Progression mensuelle.

### ⌨️ Saisie Comptable (Le Cœur du Système)
1.  **Saisie Directe (Standard) :**
    *   Interface optimisée "Débit/Crédit".
    *   Sélection intuitive des comptes (recherche par nom ou numéro).
    *   Calcul automatique de la TVA et contrepartie (si configuré).
    *   Contrôle d'équilibre (Impossible de valider si Débit ≠ Crédit).
    *   *Astuce :* Utilisez la touche `Entrée` pour passer d'un champ à l'autre rapidement.

2.  **Importation de Données :**
    *   Import via Excel/CSV pour les gros volumes (Banques, Paie...).
    *   Reconnaissance automatique des colonnes.
    *   **Nouveau :** "Réparation / Audit" pour lier automatiquement les écritures bancaires importées aux postes de trésorerie (TFT).

3.  **Gestion des Brouillons :**
    *   Ne perdez rien ! Vous pouvez enregistrer une saisie en cours comme "Brouillon" et la finir plus tard.

### 💰 Trésorerie & Règlements
*   **Postes de Trésorerie :** Vue de tous les comptes banques et caisses.
*   **Saisie des Règlements :** Enregistrement rapide des encaissements/décaissements clients et fournisseurs.
*   **État de Rapprochement :** Comparaison entre le solde comptable et le solde réel.

### 📊 États & Rapports (Consultation)
Le comptable peut générer et télécharger à tout moment :
*   **Grand Livre :** Détail de tous les comptes.
*   **Balance Générale :** Synthèse des soldes.
*   **Balance Tiers :** Suivi spécifique des dettes fournisseurs et créances clients.
*   **TFT (Tableau des Flux de Trésorerie) :** Rapport de gestion de cash (Opérationnel, Investissement, Financement).

---

## 3. Niveau 2 : L'ADMINISTRATEUR (Gestionnaire)

**Cible :** Le Chef Comptable, DAF ou Gérant qui configure le dossier et supervise l'équipe.
*L'Administrateur a accès à TOUT ce que fait le Comptable, plus les fonctions de gestion.*

### ⚙️ Configuration du Dossier (Le Hub)
C'est ici que l'on paramètre l'ADN de l'entreprise :
1.  **Plan Comptable :** Créer, modifier ou supprimer des comptes généraux (Classe 1 à 8).
2.  **Plan Tiers :** Gérer la base de données Clients et Fournisseurs.
3.  **Codes Journaux :** Définir les journaux auxiliaires (ACH, VTE, BQ, OD...).
    *   *Option :* Possibilité d'importer ces plans depuis Excel pour gagner du temps au démarrage.

### 🛡️ Gestion des Équipes (Utilisateurs)
*   **Créer des utilisateurs :** Ajouter des comptables ou des auditeurs.
*   **Habilitations :** Définir qui a le droit de faire quoi (ex: Interdire la suppression d'écritures à un stagiaire).
*   **Traçabilité (Audit) :** Voir "Qui a fait quoi et quand". L'admin peut voir l'historique de connexion et les actions critiques (suppression/modification).

### ✅ Validation & Clôture
*   **Approbation des écritures :** Valider les saisies des collaborateurs. Une écriture validée ne peut plus être modifiée par un comptable simple.
*   **Gestion des Exercices :**
    *   Ouvrir un nouvel exercice.
    *   **Clôturer** un exercice (Génération automatique des A-Noveaux).
    *   Verrouiller des périodes (ex: Clôture mensuelle TVA).

### 🚀 Outils Avancés
*   **Fusion de Données :** Pour les structures multi-sites, possibilité de consolider les données de plusieurs sous-entités.
*   **Assignation de Tâches :** Donner des ordres de travail précis à l'équipe comptable (ex: "Lettrer le compte 401GEMINI avant vendredi").

---

## 4. Niveau 3 : Le SUPER ADMIN (Gouverneur)

**Cible :** Le Cabinet Comptable, la Holding ou le Service Informatique qui gère PLUSIEURS sociétés sur la plateforme.

### 🌍 Vision Globale (Multi-Tenancy)
Le Super Admin ne gère pas la comptabilité au quotidien, il gère **l'infrastructure**.
*   **Tableau de Bord Global :** Vue hélicoptère de toutes les entreprises gérées sur la plateforme.
*   **Création d'Entreprises :** Créer un nouveau dossier société (Tenant) et lui attribuer un Administrateur principal.

### 👥 Gestion des Administrateurs
Il est le seul à pouvoir :
*   Créer ou bloquer des comptes Administrateurs.
*   Réinitialiser les accès d'un Admin en cas de perte (2FA, Mot de passe).
*   Définir les abonnements (Packs) pour chaque entreprise.

### 🔧 Maintenance & Support
*   **Switch User/Company :** Le Super Admin peut "se connecter en tant que" n'importe quel utilisateur pour résoudre un bug ou aider à la configuration.
*   **Rapports de Performance :** Analyser la charge serveur, le nombre d'écritures par dossier, etc.
*   **Configuration Système :** Mises à jour des modèles standards (Plan comptable SYSCOHADA de référence qui servira aux nouvelles sociétés).

---

## 5. Modules Transversaux

Ces fonctionnalités sont présentes partout mais s'adaptent au rôle :

### 🤖 IA & Automatisation (OCR)
*   **Module "Scan" :** Envoyez une facture PDF ou Photo.
*   **Traitement :** L'intelligence artificielle (Gemini) lit la facture, propose l'écriture comptable (Compte charge, TVA, Tiers, Montants).
*   **Validation :** L'utilisateur n'a plus qu'à vérifier et cliquer sur "Valider".

### 📥 Import / Export Universel
Le moteur d'importation est unifié :
*   Accepte **Excel (.xlsx) et CSV**.
*   Capable d'importer : Comptes, Tiers, Journaux et Écritures (Grand livre).
*   Détection intelligente des erreurs avant intégration.

---

> **Besoin d'aide ?**
> Contactez le support technique ou référez-vous aux bulles d'aide (?) présentes sur chaque écran de l'application.

*Document généré le 10/02/2026 - Version 3.0*
