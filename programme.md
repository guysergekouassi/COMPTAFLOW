# 📋 PROGRAMME DE TRAVAIL COMPTAFLOW
**Document de référence pour le développement complet du logiciel**

> [!IMPORTANT]
> Ce document liste **TOUTES** les fonctionnalités manquantes et incomplètes, classées par priorité et impact métier.

---

## 🎯 LÉGENDE DES PRIORITÉS

| Symbole | Priorité | Description |
|---------|----------|-------------|
| 🔴 | **P0 - CRITIQUE** | Fonctionnalité de base attendue dans tout logiciel comptable professionnel |
| 🟠 | **P1 - HAUTE** | Fonctionnalité importante pour la compétitivité face à Sage 100 |
| 🟡 | **P2 - MOYENNE** | Amélioration significative de l'expérience utilisateur |
| 🟢 | **P3 - BASSE** | Optimisation ou fonctionnalité avancée |

**Complexité estimée** : ⭐ Simple | ⭐⭐ Moyenne | ⭐⭐⭐ Complexe | ⭐⭐⭐⭐ Très complexe

---

## 🔴 PRIORITÉ 0 - FONCTIONNALITÉS CRITIQUES MANQUANTES

### 1. COMPTABILITÉ ANALYTIQUE (⭐⭐⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ (seuls des champs booléens existent)

#### Ce qui manque
- [ ] **Tables de base de données**
  - `axes_analytiques` (id, code, libellé, type, company_id)
  - `sections_analytiques` (id, axe_id, code, libellé, company_id)
  - `ventilations_analytiques` (id, ecriture_id, section_id, montant, pourcentage)
  - `regles_ventilation` (id, compte_id, section_id, pourcentage_defaut)

- [ ] **Modèles Laravel**
  - `AxeAnalytique.php`
  - `SectionAnalytique.php`
  - `VentilationAnalytique.php`
  - `RegleVentilation.php`

- [ ] **Contrôleurs**
  - `AxeAnalytiqueController.php` (CRUD axes)
  - `SectionAnalytiqueController.php` (CRUD sections)
  - `VentilationController.php` (gestion ventilations)

- [ ] **Interface utilisateur**
  - Page de configuration des axes analytiques
  - Page de gestion des sections par axe
  - Intégration dans le formulaire de saisie d'écritures
  - Modal de ventilation analytique (répartition manuelle)
  - Affichage des ventilations dans la liste des écritures

- [ ] **Logique métier**
  - Validation : somme des ventilations = montant de l'écriture
  - Héritage automatique des règles de ventilation par compte
  - Ventilation proportionnelle automatique
  - Copie des ventilations lors de la duplication d'écritures

- [ ] **Rapports analytiques**
  - Balance analytique (par axe/section)
  - Compte de résultat analytique
  - Grand livre analytique
  - Comparaison inter-sections
  - Export Excel/PDF des rapports analytiques

**Impact métier** : CRITIQUE - Sans cette fonctionnalité, impossible de suivre la rentabilité par projet, département, ou produit.

---

### 2. VALIDATION FISCALE AUTOMATIQUE (⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ

#### Ce qui manque
- [x] **Recalcul automatique TVA (Assistant)**
  - [x] Champ "Appliquer TVA" avec génération auto de ligne
  - [x] Sélection intelligente du compte de TVA (4452/4431)
  - [x] Verrouillage des champs non essentiels lors de la saisie

- [ ] **Détection des doublons**
  - Vérification de la référence de pièce avant enregistrement
  - Alerte si même référence + même tiers + montant similaire
  - Option de forcer l'enregistrement avec justification

- [ ] **Contrôles de cohérence**
  - Équilibre débit/crédit par écriture
  - Validation des comptes selon le plan comptable SYSCOHADA
  - Vérification des comptes de TVA (classe 44)

**Impact métier** : CRITIQUE - Évite les erreurs de saisie et les problèmes lors des contrôles fiscaux.

---

## 🟠 PRIORITÉ 1 - FONCTIONNALITÉS IMPORTANTES MANQUANTES

### 3. IA CONTEXTUELLE (⭐⭐⭐)
**Statut** : 🟡 PARTIEL (IA existe mais sans contexte)

#### Ce qui est incomplet
- [ ] **Injection du Plan Comptable dans le prompt**
  - Récupérer les comptes réels de l'entreprise
  - Formater en JSON pour Gemini
  - Limiter aux comptes de niveau 4+ pour éviter la surcharge

- [ ] **Injection des Tiers existants**
  - Liste des fournisseurs/clients avec NIF
  - Matching automatique par NIF ou nom
  - Proposition de création si tiers inconnu

- [ ] **Système de mémoire (Learning)**
  - Table `ia_mappings` (fournisseur_id, compte_id, confiance, user_id)
  - Enregistrement des corrections manuelles
  - Réutilisation lors des prochains scans

- [ ] **Audit de qualité IA**
  - Table `ia_logs` (image_hash, json_brut, json_corrigé, taux_correction, date)
  - Dashboard de statistiques (taux de succès, erreurs fréquentes)
  - Export des logs pour analyse

**Impact métier** : HAUTE - Réduit drastiquement les erreurs de l'IA et le temps de correction manuelle.

---

### 4. RAPPROCHEMENT BANCAIRE INTELLIGENT (⭐⭐⭐⭐)
**Statut** : 🟡 SQUELETTE (modèle `Lettrage` existe, rien derrière)

#### Ce qui manque
- [ ] **Import de relevés bancaires**
  - Parser MT940 (format SWIFT)
  - Parser CSV (format libre configurable)
  - Interface de mapping des colonnes

- [ ] **Lettrage automatique par IA**
  - Matching par montant exact
  - Matching par référence de pièce
  - Matching par nom de tiers (fuzzy search)
  - Apprentissage des patterns de l'utilisateur

- [ ] **Interface de lettrage manuel**
  - Vue côte à côte : relevé bancaire vs écritures
  - Sélection multiple pour lettrage groupé
  - Création d'écriture directement depuis le relevé
  - Historique des lettrages

- [ ] **Rapports de rapprochement**
  - État de rapprochement bancaire (format OHADA)
  - Liste des opérations non lettrées
  - Écarts de caisse

**Impact métier** : HAUTE - Gain de temps énorme sur une tâche répétitive et critique.

---

### 5. REPORTING COMPARATIF N-1 (⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ

#### Ce qui manque
- [ ] **Modification des services de reporting**
  - `AccountingReportingService::getBilanData()` : ajouter colonne N-1
  - `AccountingReportingService::getSIGData()` : ajouter colonne N-1
  - Calcul automatique de l'écart (montant et %)

- [ ] **Mise à jour des vues**
  - `reporting/bilan.blade.php` : nouvelle colonne "N-1"
  - `reporting/resultat.blade.php` : nouvelle colonne "N-1"
  - Colonne "Évolution %" avec code couleur (vert/rouge)

- [ ] **Export PDF/Excel**
  - Adapter les exports pour inclure N-1
  - Graphiques d'évolution (optionnel)

**Impact métier** : HAUTE - Fonctionnalité standard attendue par les experts-comptables.

---

### 6. DRILL-DOWN DANS LES RAPPORTS (⭐⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ

#### Ce qui manque
- [ ] **Liens cliquables dans les rapports**
  - Transformer les montants en liens
  - Passage de paramètres (compte, période, exercice)

- [ ] **Page de détail des écritures**
  - Affichage du Grand Livre filtré
  - Retour au rapport avec breadcrumb
  - Export du détail

- [ ] **Navigation contextuelle**
  - Depuis Bilan → Grand Livre du compte
  - Depuis Résultat → Détail des charges/produits
  - Depuis TFT → Écritures de trésorerie

**Impact métier** : HAUTE - Améliore drastiquement l'analyse financière.

---

## 🟡 PRIORITÉ 2 - FONCTIONNALITÉS MOYENNES

### 7. GESTION BUDGÉTAIRE (⭐⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ

#### Ce qui manque
- [ ] **Tables de base de données**
  - `budgets` (id, exercice_id, compte_id, montant_annuel, company_id)
  - `budgets_mensuels` (id, budget_id, mois, montant)

- [ ] **Interface de saisie**
  - Page de définition des budgets par compte
  - Import Excel de budgets
  - Répartition automatique mensuelle (linéaire ou personnalisée)

- [ ] **Tableau de bord budgétaire**
  - Comparaison Réalisé vs Budget
  - Calcul des écarts (montant et %)
  - Alertes si dépassement > seuil
  - Graphiques d'évolution

**Impact métier** : MOYENNE - Utile pour le pilotage, mais pas bloquant.

---

### 8. TRÉSORERIE PRÉVISIONNELLE (⭐⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ

#### Ce qui manque
- [ ] **Plan de trésorerie glissant**
  - Calcul basé sur les échéances clients/fournisseurs
  - Projection sur 3/6/12 mois
  - Prise en compte des encours

- [ ] **Indicateurs de performance**
  - BFR (Besoin en Fonds de Roulement)
  - Cash Burn Rate
  - Délai moyen de paiement clients/fournisseurs

- [ ] **Alertes de trésorerie**
  - Notification si solde prévisionnel < seuil
  - Suggestion d'actions (relance clients, report fournisseurs)

**Impact métier** : MOYENNE - Très utile pour anticiper les besoins de cash.

---

## 🟢 PRIORITÉ 3 - OPTIMISATIONS ET FONCTIONNALITÉS AVANCÉES

### 9. MIGRATION COMPLÈTE DE L'IA (⭐)
**Statut** : 🟡 PARTIEL (`IaController` existe, `ia_traitement_standalone.php` aussi)

#### Ce qui manque
- [ ] **Suppression du fichier standalone**
  - Vérifier que toutes les fonctionnalités sont dans `IaController`
  - Supprimer `ia_traitement_standalone.php`
  - Mettre à jour les routes si nécessaire

**Impact métier** : BASSE - Nettoyage technique, pas d'impact fonctionnel.

---

### 10. LIASSE FISCALE OHADA (⭐⭐⭐⭐)
**Statut** : ❌ NON IMPLÉMENTÉ

#### Ce qui manque
- [ ] **Génération automatique des 36 notes annexes**
  - Note 1 : Principes comptables
  - Note 2 : Immobilisations
  - Note 3 : Amortissements
  - ... (33 autres notes)

- [ ] **Templates OHADA**
  - Modèles Word/PDF conformes
  - Remplissage automatique depuis la base de données

- [ ] **Validation de conformité**
  - Vérification des données obligatoires
  - Alertes si informations manquantes

**Impact métier** : BASSE - Utile en fin d'exercice, mais peut être fait manuellement.

---

### 11. AMORTISSEMENTS AUTOMATIQUES MENSUELS (⭐⭐)
**Statut** : 🟡 PARTIEL (génération annuelle existe)

#### Ce qui manque
- [ ] **Génération mensuelle**
  - Calcul de la dotation mensuelle (dotation annuelle / 12)
  - Création automatique des écritures chaque mois
  - Planification via CRON ou scheduler Laravel

- [ ] **Tableau de bord amortissements**
  - Vue d'ensemble des dotations du mois
  - Historique des générations
  - Correction/annulation possible

**Impact métier** : BASSE - La génération annuelle suffit pour la plupart des cas.

---

## 📊 RÉSUMÉ PAR CATÉGORIE

| Catégorie | Total | P0 | P1 | P2 | P3 |
|-----------|-------|----|----|----|----|
| **Comptabilité Analytique** | 1 | 1 | 0 | 0 | 0 |
| **Validation & Contrôles** | 1 | 1 | 0 | 0 | 0 |
| **Intelligence Artificielle** | 2 | 0 | 1 | 0 | 1 |
| **Trésorerie** | 2 | 0 | 1 | 1 | 0 |
| **Reporting** | 2 | 0 | 2 | 0 | 0 |
| **Gestion Budgétaire** | 1 | 0 | 0 | 1 | 0 |
| **Conformité OHADA** | 2 | 0 | 0 | 0 | 2 |
| **TOTAL** | **11** | **2** | **4** | **2** | **3** |

---

## 🎯 PLAN D'ACTION RECOMMANDÉ

### Phase 1 : Fondations Critiques (2-3 mois)
1. ✅ **Comptabilité Analytique** (P0) - 4 semaines
2. ✅ **Validation Fiscale Automatique** (P0) - 2 semaines

### Phase 2 : Différenciation Concurrentielle (2-3 mois)
3. ✅ **IA Contextuelle** (P1) - 3 semaines
4. ✅ **Reporting Comparatif N-1** (P1) - 1 semaine
5. ✅ **Drill-down dans les rapports** (P1) - 2 semaines
6. ✅ **Rapprochement Bancaire** (P1) - 4 semaines

### Phase 3 : Pilotage Avancé (1-2 mois)
7. ✅ **Gestion Budgétaire** (P2) - 3 semaines
8. ✅ **Trésorerie Prévisionnelle** (P2) - 3 semaines

### Phase 4 : Optimisations (1 mois)
9. ✅ **Migration IA** (P3) - 1 jour
10. ✅ **Amortissements mensuels** (P3) - 1 semaine
11. ✅ **Liasse Fiscale OHADA** (P3) - 3 semaines

---

## 📝 NOTES IMPORTANTES

> [!WARNING]
> **Dépendances techniques** : La comptabilité analytique doit être implémentée en premier car elle impacte la structure de la base de données et le formulaire de saisie.

> [!TIP]
> **Quick Wins** : Commencer par la validation fiscale et le reporting N-1 pour des résultats visibles rapidement.

> [!NOTE]
> **Estimation totale** : ~7-9 mois de développement pour l'ensemble du programme (1 développeur full-time).
