# Plan d'Implémentation : Modules Avancés (Lettrage & Analytique)

Ce document détaille la roadmap technique pour transformer COMPTAFLOW en une solution comptable complète ("ERP Grade") en ajoutant les deux piliers manquants : le **Lettrage des Tiers** et la **Comptabilité Analytique**.

## 1. Module de Lettrage (Rapprochement de Tiers) 🧩

**Objectif :** Permettre de lier des écritures comptables entre elles (ex: une Facture avec son Règlement) pour justifier le solde d'un tiers.

### A. Base de Données
Nous devons créer une structure pour stocker les groupes de lettrage.

#### [NEW] Table `lettrages`
*   `id` (PK)
*   `code` (string, unique par an/tiers) : ex: "A", "AB", "Z1"...
*   `date_lettrage` (date)
*   `user_id` (FK) : Qui a fait le lettrage.
*   `company_id` (FK)

#### [MODIFY] Table `ecriture_comptables`
*   Ajouter `lettrage_id` (FK nullable) -> Lien vers la table `lettrages`.

### B. Interface Utilisateur (UI)
*   **Nouvelle Page :** `Comptabilite/Lettrage.blade.php`
*   **Fonctionnalité :**
    1.  Sélection d'un compte Tiers (411Client ou 401Fournisseur).
    2.  Affichage de deux colonnes : **Débit** (Factures) et **Crédit** (Paiements).
    3.  Cocher les cases.
    4.  Contrôle JS : `Somme(Débit) === Somme(Crédit)`.
    5.  Bouton "Lettrer" -> Génère un code unique et affecte les lignes.

---

## 2. Module de Comptabilité Analytique 🏗️

**Objectif :** Suivre la rentabilité par activité, projet ou département, indépendamment du plan comptable général.

### A. Base de Données
#### [NEW] Table `axes_analytiques`
*   `id` (PK)
*   `code` (ex: "ADM", "CHANTIER_A")
*   `libelle` (ex: "Administration", "Chantier Cocody")
*   `company_id` (FK)
*   `is_active` (boolean)

#### [MODIFY] Table `ecriture_comptables`
*   Ajouter `axe_analytique_id` (FK nullable).
*   *Note :* Ce champ ne sera actif que pour les comptes de Classe 6 (Charges) et 7 (Produits).

### B. Interface Utilisateur (UI)
*   **Configuration :** Page `Admin/Config/Analytique` pour créer les codes.
*   **Saisie Comptable :**
    *   Ajout d'une colonne "Analytique" dans le tableau de saisie.
    *   Dropdown dynamique affichant les codes actifs.
*   **Reporting :**
    *   Nouveau rapport : **"Grand Livre Analytique"**.
    *   Filtre par Code Analytique sur le Compte de Résultat.

---

## 3. Plan de Développement Prioritaire

### Étape 1 : Infrastructure Lettrage (En cours)
- [ ] Migration `create_lettrages_table` & Update `ecriture_comptables`.
- [ ] Modèle `Lettrage` & Relations.

### Étape 2 : UI Lettrage
- [ ] Contrôleur `LettrageController`.
- [ ] Vue `lettrage.index` (Sélection Tiers).
- [ ] Vue `lettrage.show` (Tableau de pointage JS).

### Étape 3 : Infrastructure Analytique
- [ ] Migration `axes_analytiques` & Update `ecriture_comptables`.
- [ ] CRUD des codes analytiques dans `AdminConfigController`.

### Étape 4 : Intégration Saisie
- [ ] Modifier `accounting_entry_real.blade.php` pour inclure le champ Analytique.
