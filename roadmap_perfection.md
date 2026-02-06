# Roadmap : Vers la Perfection Comptable (ComptaFlow vs Sage 100)

Ce document définit les étapes clés pour transformer ComptaFlow en une solution leader, surpassant Sage 100 par l'automatisation, l'IA et une expérience utilisateur moderne.

---

## 🟢 PHASE 1 : Hyper-Automatisation de la Saisie (Intelligence Artificielle)
*L'objectif est d'éliminer la saisie manuelle et les erreurs humaines.*

### 1.1 Sécurisation et Robustesse de l'IA
- [ ] **Migration du script IA** : Déplacer `ia_traitement_standalone.php` vers un contrôleur Laravel (ex: `IAController`) pour sécuriser la clé API.
- [ ] **Audit de Qualité** : Implémenter un système de log pour analyser les taux de succès de l'IA sur différents types de factures.

### 1.2 IA Contextuelle (Précision SYSCOHADA)
- [ ] **Injection du Plan Comptable** : Envoyer les comptes réels de l'entreprise à l'IA pour éviter les codes "inventés".
- [ ] **Mapping des Tiers auto** : Faire en sorte que l'IA identifie le tiers existant (NIF/Nom) ou propose sa création automatique.
- [ ] **Apprentissage (Memory)** : Créer une table de mapping `fournisseur_id => compte_comptable_id` pour que le système retienne les corrections manuelles de l'utilisateur.

### 1.3 Validation Fiscale Automatique
- [ ] **Recalcul TVA** : Vérifier automatiquement la cohérence HT/TVA/TTC et alerter en cas d'écart.
- [ ] **Anti-Doublon** : Bloquer ou alerter si une facture avec la même référence existe déjà en base.

---

## 🔵 PHASE 2 : Pilotage et Reporting Dynamique
*Donner au chef d'entreprise une vision immédiate et comparée de sa santé financière.*

### 2.1 États de Synthèse Évolués
- [ ] **Comparatif N-1** : Ajouter une colonne "Année Précédente" sur le Compte de Résultat et le Bilan.
- [ ] **Drill-down (Analyse Directe)** : Pouvoir cliquer sur un montant dans un rapport pour voir la liste des écritures qui le composent.

### 2.2 Gestion Budgétaire
- [ ] **Saisie de Budget** : Permettre de définir des budgets annuels par compte de charge.
- [ ] **Analyse des Écarts** : Tableau de bord comparant le Réalisé vs Budget.

---

## 🟡 PHASE 3 : Trésorerie Intelligente
*Anticiper les besoins de cash au lieu de simplement les constater.*

### 3.1 Tableau de Bord de Trésorerie
- [ ] **Plan de Trésorerie Glissant** : Tableau automatique basé sur les échéances de factures (Journal Client/Fournisseur).
- [ ] **Indicateurs de Performance (KPI)** : Calculer automatiquement le BFR (Besoin en Fonds de Roulement) et le Cash Burn Rate.

### 3.2 Rapprochement Bancaire (Prochaine étape majeure)
- [ ] **Import Relevés (MT940/CSV)** : Créer une interface d'importation de relevés bancaires.
- [ ] **Lettrage Automatique** : Algorithme pour faire correspondre les lignes de banque aux factures via l'IA.

---

## 🔴 PHASE 4 : Conformité et Expertise (OHADA)
*Le socle de confiance pour les experts-comptables.*

### 4.1 Automatisation de fin de période
- [ ] **Amortissements Auto** : Générer les écritures de dotations mensuelles/annuelles basées sur le tableau des immobilisations.
- [ ] **Liasse Fiscale OHADA** : Générer automatiquement les 36 notes annexes requises pour le bilan annuel.

---

## 🚀 Prochaine Étape Immédiate
> **Priorité :** Sécuriser l'IA (Phase 1.1) et injecter le contexte du plan comptable (Phase 1.2) pour rendre l'interface de Scan infaillible.
