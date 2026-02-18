 # Comprendre la Comptabilité Analytique

Il est normal d'être un peu perdu, car la comptabilité analytique ajoute une couche "invisible" par-dessus la comptabilité générale que vous connaissez déjà.

Voici une explication simple pour clarifier la différence et l'utilité de ces nouvelles pages.

## 1. La Différence Fondamentale

Imaginez que vous recevez une facture d'électricité de **100 000 FCFA**.

### La Comptabilité Générale (Celle que vous aviez déjà)
Elle répond à la question : **"QUOI ?"**
*   **Réponse** : On a payé de l'électricité (Compte 605).
*   C'est obligatoire pour l'État et les impôts.
*   Vos états actuels (Balance Générale, Grand Livre Général) servent à ça.

### La Comptabilité Analytique (Ce qu'on vient d'ajouter)
Elle répond à la question : **"POUR QUI ?"** ou **"POURQUOI ?"**
*   **Réponse** : Sur ces 100 000 FCFA :
    *   **70 000 FCFA** ont servi à l'**Usine** (Production).
    *   **30 000 FCFA** ont servi aux **Bureaux** (Administration).
*   C'est pour **VOUS**, pour savoir quelle activité est rentable ou coûteuse.

---

## 2. Pourquoi des pages en double (Balance, Grand Livre) ?

Vous avez remarqué qu'il y a maintenant deux "Balances" et deux "Grands Livres". Ils ne montrent pas la même chose :

| Document | **Balance Générale** (Existante) | **Balance Analytique** (Nouvelle) |
| :--- | :--- | :--- |
| **Périmètre** | Toute l'entreprise. | Filtrée pour une **Section** précise (ex: Projet A). |
| **Contenu** | Liste tous les comptes (Banque, Clients, Charges...). | Se concentre sur les Charges (6) et Produits (7). |
| **Objectif** | Voir la santé globale et payer les impôts. | Voir si le **Projet A** a gagné ou perdu de l'argent. |

**Exemple concret :**
Si vous ouvrez le **Grand Livre Analytique** et sélectionnez la section "Projet Immeuble Cocody", vous ne verrez **QUE** les achats de ciment, fer, et main d'œuvre liés à *ce* chantier spécifique. Le Grand Livre Général, lui, mélangerait le ciment de tous vos chantiers.

---

### 3. A quoi servent les nouvelles pages de configuration ?

Pour que ce système fonctionne, il faut structurer votre analyse :

1.  **Les Axes (`/analytique/axes`)** : Ce sont les "angles de vue".
    *   *Exemple :* Vous voulez analyser par "Chantiers", par "Départements" (RH, IT, Marketing), ou par "Véhicules". Ce sont vos Axes.

2.  **Les Sections (`/analytique/sections`)** : Ce sont les détails dans chaque axe.
    *   *Dans l'axe Chantiers* : Section "Villa M. Koffi", Section "Immeuble Plateau".
    *   *Dans l'axe Départements* : Section "Direction", Section "Atelier".

3.  **Les Règles (`/analytique/regles`)** : C'est l'automatisation.
    *   Au lieu de dire manuellement à chaque facture de loyer : *"Mets 50% sur le Siège et 50% sur l'Agence"*, vous le définissez une fois ici. Le logiciel le fera ensuite tout seul à chaque saisie.

## En résumé

L'analytique ne remplace pas votre comptabilité, elle la **découpe** pour vous donner une vision de gestionnaire.

*   **Sans Analytique** : "L'entreprise a dépensé 10 millions en carburant." (C'est vague).
*   **Avec Analytique** : "Le Camion A a consommé 8 millions, alors que le Camion B n'a consommé que 2 millions. Le Camion A a un problème." (C'est utile).
