# 📘 DOCUMENTATION TECHNIQUE — LIASSE FISCALE SYSCOHADA
## Système de génération XML pour e-impôts.ci (DGI Côte d'Ivoire)
**Entité de référence :** CONCEPT IMAGE SARL  
**NCC :** 1530324W | **Exercice :** 2024 | **Type :** NO (Système Normal)  
**Auteur original outil Excel :** IKA Conseil — Justine Ventalon v1.3 (2019)  
**Documentation générée pour :** Équipe développement logiciel comptable

---

## 🎯 OBJECTIF GLOBAL DU SYSTÈME

Transformer une **balance comptable** (sorties du logiciel de comptabilité) en un **fichier XML normalisé** à déposer sur **e-impôts.ci** (portail DGI Côte d'Ivoire), conformément aux normes **SYSCOHADA** (Système Comptable OHADA — Système Normal).

```
[Logiciel Comptable]
       ↓ Export Balance (Débit/Crédit par compte)
[Onglet Balance & Reporting]
       ↓ Formules SYSCOHADA + règles fiscales CI
[États Financiers: ACTIF, PASSIF, RESULTAT, TFT]
       ↓ + Notes Annexes (NOTE 1 à NOTE 39) + Fiches DGI
[Onglets XML : tableaux → champs fixes → champs variables → export]
       ↓ Macro VBA : GenererXML()
[Fichier XML : NO-2024-1530324W-ddmmyyyy-hhnnss.xml]
       ↓ Upload manuel
[e-impôts.ci — DGI Côte d'Ivoire]
```

---

## 📋 STRUCTURE DES 98 ONGLETS (Groupes fonctionnels)

### GROUPE 1 — Saisie et Données Sources
| Onglet | Rôle |
|--------|------|
| `Présentation_Entité` | Données d'identification de l'entreprise (NCC, NTD, adresse, signataires, expert-comptable) |
| `Collage_Balance` | Zone de collage de la balance brute depuis le logiciel comptable |
| `Balance & Reporting` | Balance structurée par numéro de compte SYSCOHADA (Débit/Crédit N et N-1) |
| `Table_TFT` | Données source pour le Tableau des Flux de Trésorerie |
| `Referentiels` | Tables de codes : pays, types de contrat, nomenclatures d'activité, unités de mesure |
| `Ctrl_Actif` | Contrôle de cohérence de l'Actif (totaux, équilibres) |
| `Ctrl_Passif` | Contrôle de cohérence du Passif |
| `Ctrl_Charges_Produits` | Contrôle de cohérence du Compte de Résultat |
| `Visa_Comptable` | Visa et signature de l'expert-comptable |

### GROUPE 2 — États Financiers SYSCOHADA
| Onglet | Rôle | Type DGI |
|--------|------|----------|
| `COUVERTURE` | Page de couverture de la liasse | Informatif |
| `GARDE` | Page de garde (DGI) : documents déposés, nb exemplaires | Informatif |
| `RECEVABILITE` | Conditions de recevabilité SYSCOHADA | Informatif |
| `BILAN` | Bilan complet (Actif + Passif sur une page) — vue synthétique | Informatif |
| `ACTIF` | Bilan Actif — Page 1/2 | **Tableau : ACTIF** |
| `PASSIF` | Bilan Passif — Page 2/2 | **Tableau : PASSIF** |
| `RESULTAT` | Compte de Résultat | **Tableau : RESULTAT** |
| `TFT` | Tableau des Flux de Trésorerie | **Tableau : TFT** |

### GROUPE 3 — Fiches de Renseignements (Identification DGI)
| Onglet | Rôle | Tableaux DGI |
|--------|------|-------------|
| `FICHE R1` | Identification exercice, RCCM, CNPS, adresse, signataires | FR1 (Fixe) |
| `FICHE R2` | Activités de l'entité, nomenclature, CA | FR2A, FR2B, FR2C, FR2D |
| `FICHE R3` | Dirigeants et membres du CA | FR3A, FR3B |
| `FICHE R4` | Récapitulatif des Notes Annexes (grisé — non exporté XML) | — |

### GROUPE 4 — Notes Annexes SYSCOHADA (NOTE 1 à NOTE 39)
| Note | Contenu |
|------|---------|
| NOTE 1 | Dettes garanties, engagements financiers |
| NOTE 2 | Informations obligatoires |
| NOTE 3A | Immobilisations brutes |
| NOTE 3B | Biens en location-acquisition |
| NOTE 3C | Amortissements des immobilisations |
| NOTE 3C BIS | Dépréciations des immobilisations |
| NOTE 3D | Plus/moins-values de cession |
| NOTE 3E | Réévaluations d'actifs |
| NOTE 4 | Immobilisations financières, filiales, participations |
| NOTE 5 | Actif/Passif circulant HAO |
| NOTE 6 | Stocks et en-cours |
| NOTE 7 | Clients |
| NOTE 8 | Autres créances |
| NOTE 8A | Charges immobilisées (étalement) |
| NOTE 8B | Provisions pour charges à répartir |
| NOTE 8C | Provisions pour engagements de retraite |
| NOTE 9 | Titres de placement |
| NOTE 10 | Valeurs à encaisser |
| NOTE 11 | Disponibilités |
| NOTE 12 | Écarts de conversion, transferts de charges |
| NOTE 13 | Capital (valeur nominale, détail par actionnaire) |
| NOTE 14 | Primes et réserves |
| NOTE 15A | Subventions et provisions réglementées |
| NOTE 15B | Autres fonds propres |
| NOTE 16A | Dettes financières et ressources assimilées |
| NOTE 16B | Retraites (hypothèses actuarielles, variation) |
| NOTE 16B BIS | Actifs/passifs régimes de retraite financés |
| NOTE 16C | Actifs et passifs éventuels |
| NOTE 17 | Fournisseurs |
| NOTE 18 | Dettes fiscales et sociales |
| NOTE 19 | Autres dettes, provisions CT |
| NOTE 20 | Banques, crédits de trésorerie |
| NOTE 21 | CA et autres produits |
| NOTE 22 | Achats |
| NOTE 23 | Transports |
| NOTE 24 | Services extérieurs |
| NOTE 25 | Impôts et taxes |
| NOTE 26 | Autres charges |
| NOTE 27A | Charges de personnel |
| NOTE 27B | Effectifs, masse salariale, frais de personnel |
| NOTE 28 | Dotations aux provisions et dépréciations |
| NOTE 29 | Charges et revenus financiers |
| NOTE 30 | Charges et produits HAO |
| NOTE 31 | Résultats des 5 derniers exercices |
| NOTE 32 | Détail de la production |
| NOTE 33 | Détail des achats |
| NOTE 34 | Indicateurs financiers clés |
| NOTE 35 | Informations sociales/environnementales/sociétales |
| NOTE 36 | Table des codes (référentiel) |
| NOTE 37 | Détermination de l'impôt sur le résultat |
| NOTE 38 | Événements postérieurs à la clôture |
| NOTE 39 | Changements de méthodes comptables |

### GROUPE 5 — États Supplémentaires DGI (Spécifique Côte d'Ivoire)
| Onglet | Contenu |
|--------|---------|
| `GARDE (DGI-INS)` | Page de garde INS (Institut National de la Statistique) |
| `NOTES DGI - INS` | Notes complémentaires pour l'INS |
| `COMP-CHARGES` | Détail des charges (tableau CHARGES) |
| `COMP-TVA` | État TVA (tableau TVA) |
| `COMP-TVA (2)` | TVA supportée non déductible (tableau TVA2) |
| `SUPPL1` | Biens d'occasion, complément NOTE 32, NOTE 27B, NOTE 33 |
| `SUPPL2` | Résultat fiscal sociétés de personnes |
| `SUPPL3` | Informations entités individuelles |
| `SUPPL4` | Tableau des amortissements et inventaire immobilisations |
| `SUPPL5` | Frais accessoires sur achats |
| `SUPPL6` | Avantages en nature et en espèces — personnel |
| `SUPPL7` | Créances et dettes échues |
| `Garde_Bic` / `Garde_BNC` / `Garde_BA` | Pages de garde BIC/BNC/BA |
| `Garde_301` / `Garde_302` | Autres gardes fiscales |
| `COMMENTAIRE` | Zone commentaire libre (tableau COMMENT) |

### GROUPE 6 — Moteur XML (Technique)
| Onglet | Rôle |
|--------|------|
| `Export XML` | Interface utilisateur : saisie NCC/exercice, bouton génération, instructions |
| `tableaux` | Mapping des 119 tableaux DGI → onglet Excel + cellule de départ |
| `champs fixes` | Mapping des ~334 champs à position fixe → code DGI + cellule Excel |
| `champs variables` | Mapping des 139 colonnes de tableaux à lignes variables |
| `export` | Zone de vérification : valeurs récupérées avant export XML |

---

## 🔄 FLUX DE TRAVAIL ÉTAPE PAR ÉTAPE

### ÉTAPE 1 — Saisie des données d'identification
**Onglet :** `Présentation_Entité`

Données à renseigner :
- Désignation entité (ex: CONCEPT IMAGE SARL)
- Adresse, BP, ville
- NCC (N° Compte Contribuable) → ex: `1530324W`
- NTD (N° Télédéclarant) → ex: `5981165412185`
- N° RCCM → ex: `CI-ABJ-2015-B-14268`
- N° CNPS → ex: `253375`
- Code activité → ex: `C180100`
- Date début exercice → `01/01/2024`
- Date fin exercice → `31/12/2024`
- Nom signataire, qualité
- Expert-comptable (nom, tél, N° ordre)

**Ces données alimentent automatiquement** tous les autres onglets via formules de référence.

---

### ÉTAPE 2 — Import de la Balance
**Onglet :** `Collage_Balance` → `Balance & Reporting`

- Coller la balance de sortie du logiciel comptable dans `Collage_Balance`
- La balance doit être structurée par numéro de compte à 4 chiffres (plan SYSCOHADA)
- `Balance & Reporting` agrège les soldes par compte avec les colonnes :
  - N° Compte (4 chiffres) | Compte détaillé (6 chiffres)
  - Débit N | Crédit N | Double solde contrôle
  - Débit N-1 | Crédit N-1
  - Colonnes "Manuel" pour ajustements manuels si nécessaire

**Comptes SYSCOHADA utilisés :** Classe 1 (capitaux), 2 (immobilisations), 3 (stocks), 4 (tiers), 5 (trésorerie), 6 (charges), 7 (produits)

---

### ÉTAPE 3 — Construction du Bilan
**Onglets :** `ACTIF`, `PASSIF`, `BILAN`

#### ACTIF (Page 1/2)
Structure par REF SYSCOHADA :
| REF | Poste | Colonne N (BRUT) | Colonne N (AMORT/DEPREC) | Colonne N (NET) | Colonne N-1 (NET) |
|-----|-------|------------------|--------------------------|-----------------|-------------------|
| AD | Immobilisations incorporelles | Comptes 21x | Comptes 281x | Calcul | N-1 |
| AI | Immobilisations corporelles | Comptes 22x-24x | Comptes 282x-284x | Calcul | N-1 |
| AQ | Immobilisations financières | Comptes 26x-27x | — | Calcul | N-1 |
| AZ | **TOTAL ACTIF IMMOBILISÉ** | Σ | Σ | **60 073 207** | 10 062 090 |
| BB | Stocks et en-cours | Comptes 31x-38x | — | 0 | 0 |
| BG | Créances et emplois assimilés | Comptes 40x-48x | — | 0 | 0 |
| BT | **TOTAL ACTIF CIRCULANT** | — | — | 0 | 0 |
| BU | Titres de placement | Compte 50x | — | 0 | 0 |
| BV | Valeurs à encaisser | Compte 51x | — | 0 | 0 |
| BW | Disponibilités | Comptes 52x-57x | — | 0 | 0 |
| BZ | **TOTAL TRÉSORERIE-ACTIF** | — | — | 0 | 0 |
| **BX** | **TOTAL GÉNÉRAL ACTIF** | — | — | **4 545 435** | 10 062 090 |

#### PASSIF (Page 2/2)
Structure :
| REF | Poste | N (NET) | N-1 (NET) |
|-----|-------|---------|-----------|
| CA | Capital | 1 000 000 | 1 000 000 |
| CH | Report à nouveau | 35 359 083 | 3 257 816 |
| CJ | Résultat net | **145 237 440** | 32 101 267 |
| CP | **TOTAL CAPITAUX PROPRES** | **181 596 523** | 36 359 083 |
| DA | Emprunts et dettes financières | 0 | 0 |
| DF | **TOTAL RESSOURCES STABLES** | **181 596 523** | 36 359 083 |
| DK | Dettes fiscales et sociales | 9 079 789 | 8 626 106 |
| DM | Autres dettes | 2 005 145 | 2 005 145 |
| DQ | **TOTAL PASSIF CIRCULANT** | 11 084 934 | 10 631 251 |
| DZ | **TOTAL GÉNÉRAL PASSIF** | **192 681 457** | 46 990 334 |

---

### ÉTAPE 4 — Compte de Résultat
**Onglet :** `RESULTAT`

Structure (REF SYSCOHADA) :
| REF | Libellé | N | N-1 |
|-----|---------|---|-----|
| TC | Travaux, services vendus | 156 500 000 | 155 379 178 |
| **XB** | **CA Total** | **156 500 000** | **155 379 178** |
| RE | Autres achats | -821 945 | -91 919 000 |
| RI | Impôts et taxes | -61 844 | -9 444 344 |
| **XD** | **Valeur Ajoutée** | Calcul | Calcul |
| **XI** | **Résultat d'exploitation** | Calcul | Calcul |
| **XL** | **Résultat financier** | Calcul | Calcul |
| **XP** | **Résultat HAO** | Calcul | Calcul |
| **XS** | **Résultat net** | **145 237 440** | 32 101 267 |

---

### ÉTAPE 5 — Tableau des Flux de Trésorerie (TFT)
**Onglet :** `TFT` (alimente par `Table_TFT`)

| REF | Libellé | N | N-1 |
|-----|---------|---|-----|
| ZA | Trésorerie nette au 1er janvier | 36 928 244 | 27 370 387 |
| FA | CAFG (Capacité d'Autofinancement Globale) | 152 294 095 | 50 025 742 |
| **ZB** | **Flux activités opérationnelles** | **152 747 778** | 21 557 857 |
| FG | Acquisitions immobilisations corporelles | -1 540 000 | 0 |
| **ZC** | **Flux activités d'investissement** | **-1 540 000** | -12 000 000 |
| **ZD** | **Flux financement** | 0 | 0 |
| **ZH** | **Trésorerie nette à la clôture** | Calcul | Calcul |

---

### ÉTAPE 6 — Notes Annexes
**Onglets :** NOTE 1 à NOTE 39 + SUPPL1-7

- Les notes à **lignes fixes** : remplissage direct des cellules
- Les notes à **lignes variables** (ex: NOTE 13B — détail capital, NOTE 3E3, NOTE 32) : l'utilisateur peut **ajouter des lignes** via bouton VBA `AjouterLigne(codeTableau)`
- Toutes les notes récupèrent leurs valeurs directement depuis la Balance & Reporting ou saisie manuelle

---

### ÉTAPE 7 — États Supplémentaires DGI (spécifiques CI)
**Onglets :** COMP-CHARGES, COMP-TVA, COMP-TVA(2), SUPPL1 à SUPPL7

Ces onglets contiennent des tableaux **spécifiques à la DGI Côte d'Ivoire** qui ne font pas partie du SYSCOHADA standard mais sont obligatoires pour la télédéclaration :
- Détail des charges par nature
- État de TVA collectée / déductible
- Inventaire des immobilisations
- Détail de la production et des achats

---

### ÉTAPE 8 — Génération du Fichier XML
**Onglet :** `Export XML`

**Prérequis :**
1. Activer les macros à l'ouverture du fichier ("Activer le contenu")
2. Vérifier NCC en cellule `G11` et Exercice en cellule `G12`

**Processus de la macro `GenererXML()` :**

```
1. Vérifier que NCC (Export XML!G11) et Exercice (G12) sont renseignés
2. Appeler GenererListeChampsFixes()
   → Parcourir "champs fixes"!ListeChampsTableauxFixes
   → Pour chaque champ : récupérer valeur dans cellule mappée
   → Écrire dans "export"!ExportChampsTableauxFixes (colonnes E:F = code/valeur)
3. Appeler GenererListeChampsVariables()
   → Parcourir "champs variables"!ListeColonnesTableauxVariables
   → Pour chaque colonne variable : itérer sur lignes non vides
   → Écrire dans "export"!ExportChampsTableauxVariables (colonnes A:C = colonne/ligne/valeur)
4. Générer le XML via ActiveWorkbook.SaveAsXMLData
   → Mappage : "EDI_Mappage" (défini dans le fichier)
   → Nom du fichier : NO-{exercice}-{NCC}-{ddmmyyyy-hhnnss}.xml
   → Dossier : /Documents sur le poste
5. Confirmation à l'utilisateur
```

**Règles de formatage des valeurs :**
- Dates → format `dd/mm/yyyy` avec préfixe `'`
- Chaînes numériques commençant par `0` → préfixe `'` (évite suppression du zéro)
- Nombres → arrondis à **4 décimales**
- Cellules vides → **non incluses** dans l'XML (tableaux variables seulement)

---

### ÉTAPE 9 — Dépôt sur e-impôts.ci
1. Se connecter sur **e-impôts.ci** (DGI Côte d'Ivoire)
2. Aller à "Déclaration > États Financiers"
3. Étape 1 : Section "Import des états financiers au format XML"
4. Charger le fichier XML généré
5. **⚠️ ATTENTION : Tous les champs déjà saisis manuellement seront écrasés**

---

## 🔐 SÉCURITÉ ET ACCÈS

- **Mot de passe feuilles :** `teleliasse`
- **Feuilles NON protégées :** `Export XML`, `export`
- **Feuilles grisées (non exportées) :** GARDE, RECEVABILITE, FICHE R4, NOTES DGI-INS
- **Expiration outil :** Codée pour expirer le 31/10/2021 (logique inactive)

---

## 📐 STRUCTURE XML GÉNÉRÉ

```xml
<?xml version="1.0" encoding="UTF-8"?>
<EDI>
  <informations>
    <type>NO</type>           <!-- Type liasse : NO = Normal -->
    <ncc>1530324W</ncc>       <!-- N° Compte Contribuable -->
    <exercice>2024</exercice> <!-- Année fiscale -->
  </informations>

  <champsTableauxFixes>
    <!-- Un nœud par cellule remplie des tableaux fixes -->
    <!-- ~334 champs possibles -->
    <champTableauFixe>
      <code>NO_FR1_ZA1_1</code>    <!-- Pattern : {type}_{tableau}_{ligne}_{col} -->
      <valeur>01/01/2024</valeur>
    </champTableauFixe>
    <!-- ... -->
  </champsTableauxFixes>

  <champsTableauxVariables>
    <!-- Un nœud par cellule NON VIDE des tableaux variables -->
    <!-- 139 colonnes définies -->
    <champTableauVariable>
      <colonne>NO_FR2B_1</colonne>      <!-- Code colonne DGI -->
      <ligne>1</ligne>                   <!-- N° de ligne (commence à 1) -->
      <valeur>IMPRESSION SUR...</valeur>
    </champTableauVariable>
    <!-- ... -->
  </champsTableauxVariables>
</EDI>
```

---

## 🏗️ CE QUE LE LOGICIEL DOIT REPRODUIRE

Pour remplacer ce fichier Excel, le logiciel doit implémenter :

### Module 1 — Import Balance
- Lire balance comptable (compte 4-6 chiffres, débit, crédit) pour N et N-1
- Agréger par classe SYSCOHADA

### Module 2 — Calcul des États Financiers
- Calculer ACTIF (brut/amort/net) selon règles de regroupement SYSCOHADA
- Calculer PASSIF selon règles de regroupement SYSCOHADA
- Calculer RESULTAT (produits - charges → soldes intermédiaires)
- Calculer TFT (méthode indirecte depuis CAFG)

### Module 3 — Saisie des Notes Annexes
- Interface pour les 39 notes (certaines auto-calculées, d'autres saisie manuelle)
- Support des tableaux à lignes variables (ajout dynamique)

### Module 4 — Génération XML e-impôts
- Construire l'objet XML selon schéma DGI (voir `schema_xml_edi.json`)
- Appliquer règles de formatage (dates, zéros, décimales)
- N'inclure que les cellules non vides pour les tableaux variables
- Nommer le fichier `NO-{exercice}-{NCC}-{timestamp}.xml`

### Module 5 — Validation
- Vérifier équilibre Actif = Passif
- Vérifier cohérence Résultat ↔ Capitaux propres
- Vérifier NCC + Exercice renseignés avant export

---

## 📊 DONNÉES RÉELLES CONCEPT IMAGE 2024

| Indicateur | Valeur |
|------------|--------|
| CA (Travaux/Services) | 156 500 000 FCFA |
| Résultat net | 145 237 440 FCFA |
| Total Actif Net | ~4 545 435 FCFA (actif immobilisé net) |
| Capitaux propres | 181 596 523 FCFA |
| Trésorerie opérationnelle | 152 747 778 FCFA |
| Trésorerie d'investissement | -1 540 000 FCFA |
| Dettes fiscales et sociales | 9 079 789 FCFA |

---

*Document généré automatiquement depuis Bilan_2024_CONCEPT_IMAGE.xlsm*  
*Tous montants en Francs CFA (XOF) sauf mention contraire*
