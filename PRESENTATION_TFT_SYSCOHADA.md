# 🚀 Présentation : Optimisation Avancée du Tableau des Flux de Trésorerie (TFT)

## 1. Le Contexte : Pourquoi cette mise à jour ?

Le **Tableau des Flux de Trésorerie (TFT)** est un rapport vital qui explique comment l'argent a bougé dans l'entreprise durant l'année. Il ne se contente pas de dire "il reste 1 million en banque", il explique **d'où vient** cet argent et **où il est allé**.

Le **SYSCOHADA** (la loi comptable) impose de classer ces mouvements en trois grandes familles :
1.  **L'Opérationnel :** L'argent du quotidien (Ventes, Achats de marchandises, Salaires...).
2.  **L'Investissement :** L'argent pour l'avenir (Achat de machines, de terrains...).
3.  **Le Financement :** L'argent des banques et des actionnaires (Emprunts, Capital...).

### Le Problème Avant
Le logiciel essayait de deviner tout seul si une dépense était de l'investissement ou du financement en regardant le nom de la catégorie (ex: "Mes Investissements"). C'était imprécis et risqué. Si le comptable nommait mal sa catégorie, le rapport était faux.

### La Solution Maintenant
Nous avons ajouté un système d'**étiquettes officielles (Codes SYSCOHADA)**. Désormais, le comptable peut dire explicitement au logiciel : *"Cette dépense précise, c'est un Achat de Machine"*, sans aucune ambiguïté.

---

## 2. Ce que nous avons implémenté (Technique vulgarisée)

### A. Dans la Base de Données (Le Cerveau)
Nous avons ajouté une "case" supplémentaire sur chaque Poste de Trésorerie. Avant, un poste avait juste un Nom et une Catégorie. Maintenant, il a aussi une **Fonction Officielle** (le fameux `syscohada_line_id`).

### B. Dans l'Interface (Ce que voit l'utilisateur)
Quand vous créez ou modifiez un poste de trésorerie (ex: "Ligne de Crédit BOA"), une nouvelle liste déroulante apparaît : **"Flux SYSCOHADA (TFT)"**.
C'est ici que l'utilisateur choisit l'étiquette officielle.

### C. Dans le Calcul (Le Moteur)
Le logiciel suit désormais une règle simple et stricte :
*   **Si une étiquette officielle est collée sur le poste**, le logiciel l'utilise en priorité absolue et range le montant dans la bonne case du rapport.
*   **Sinon**, il continue de faire de son mieux avec les catégories classiques (pour les petites dépenses courantes).

---

## 3. Et pour l'Importation de données ? (La Magie)

Une question cruciale : **"Que se passe-t-il si j'importe mes écritures depuis Excel ?"**

### Cas 1 : Configuration PRÉALABLE (Idéal)
Vous avez déjà créé vos Postes de Trésorerie.
> **Résultat :** Lors de l'import, le logiciel reconnaît les comptes bancaires et applique **automatiquement** le bon code SYSCOHADA à toutes les lignes. C'est magique.

### Cas 2 : Configuration POSTÉRIEURE (Nouvelle Entreprise)
Vous importez vos écritures "en vrac", **avant** d'avoir configuré vos Postes de Trésorerie.
> **Problème :** Le logiciel ne sait pas encore où ranger ces flux.
> **Solution :** Pas de panique !
> 1. Allez dans la configuration des Postes de Trésorerie.
> 2. Créez vos postes (ex: "Emprunt BOA" -> `FIN_EMP`).
> 3. Cliquez sur le bouton **"Réparer les liens / Audit"**.
> 4. **BINGO !** Le logiciel scanne tout votre historique, retrouve les écritures orphelines, et leur colle la bonne étiquette rétroactivement.

---

## 4. Comprendre les Codes SYSCOHADA (Le Glossaire)

Voici la traduction concrète de chaque "étiquette" technique que vous avez vue. Imaginez que ce sont des cartons de rangement :

### 🏗️ Section Investissement (L'entreprise achète ou vend du "gros matériel")

*   **`INV_ACQ` (Acquisition d'immobilisations)**
    *   *Ce que c'est :* L'entreprise sort de l'argent pour acheter quelque chose de durable (Ordinateurs, Véhicules, Terrains, Logiciels).
    *   *Pourquoi "Flux Négatif" ?* C'est une **sortie d'argent** (dépense), donc cela diminue la trésorerie. Sur le rapport, ce chiffre doit être précédé d'un signe moins (-).

*   **`INV_CES` (Cession d'immobilisations)**
    *   *Ce que c'est :* L'entreprise vend un vieux camion ou un vieux l'ordinateur. De l'argent rentre.
    *   *Pourquoi "Flux Positif" ?* C'est une **entrée d'argent** (recette), donc cela augmente la trésorerie.

### 🏦 Section Financement (L'entreprise trouve de l'argent frais ou rembourse ses dettes)

*   **`FIN_EMP` (Emprunts)**
    *   *Ce que c'est :* La banque débloque un prêt et verse 50 millions sur le compte.
    *   *Sens du flux :* **Positif (+)**. L'argent rentre dans les caisses.

*   **`FIN_RMB` (Remboursement d'emprunts)**
    *   *Ce que c'est :* L'entreprise paie sa mensualité à la banque pour rembourser le prêt.
    *   *Sens du flux :* **Négatif (-)**. L'argent sort des caisses.
    *   *Attention :* On ne parle ici que du remboursement du capital (la dette "pure"), pas des intérêts (qui sont souvent dans l'opérationnel).

*   **`FIN_CAP` (Augmentation de capital)**
    *   *Ce que c'est :* Les actionnaires mettent de l'argent de leur poche dans l'entreprise pour la renforcer.
    *   *Sens du flux :* **Positif (+)**. L'argent rentre.

*   **`FIN_DIV` (Dividendes versés)**
    *   *Ce que c'est :* L'entreprise a fait des bénéfices et verse une partie de cet argent aux actionnaires pour les récompenser.
    *   *Sens du flux :* **Négatif (-)**. L'argent sort.

*   **`FIN_SUB` (Subvention d'investissement)**
    *   *Ce que c'est :* L'État ou un organisme donne de l'argent (sans demander remboursement) pour aider l'entreprise à acheter du matériel.
    *   *Sens du flux :* **Positif (+)**. L'argent rentre.

---

## 5. Résumé pour la présentation

> "Nous avons sécurisé et fiabilisé le rapport financier le plus complexe (le TFT). Désormais, chaque mouvement important (Emprunt, Achat de matériel...) est identifié par un 'Code Unique' officiel. Le système est flexible : que vous configuriez **avant** ou **après** l'importation de vos données, nous garantissons un historique propre, conforme SYSCOHADA, et auditable."
