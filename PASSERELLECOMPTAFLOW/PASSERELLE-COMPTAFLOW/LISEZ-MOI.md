# Le correctif Comptaflow, à appliquer

Ce dossier ne contient **rien qui s'exécute dans Selflow**. Il porte le côté
Comptaflow de la passerelle, écrit et vérifié ici, mais qui n'a pas pu être
poussé : la session de développement n'autorise pas l'écriture sur un dépôt
d'un autre propriétaire que `meledjeabrahamagnimel-lgtm`.

Le travail est complet et compile ; il attend seulement d'être appliqué.

## Appliquer

```bash
git clone https://github.com/guysergekouassi/comptaflow
cd comptaflow
git checkout -b claude/deversement-selflow
git am < /chemin/vers/0001-deversement-selflow.patch
php artisan migrate
```

Puis, **avant tout déploiement**, poser la variable d'environnement — sans elle
la synchronisation refuse désormais tout appel, ce qui est le comportement
voulu :

```
EXTERNAL_SYNC_SECRET=<la même valeur des deux côtés, tirée au hasard>
```

La même variable doit exister dans le `.env` de Selflow.

## Ce que le correctif change

### Le secret partagé n'a plus de valeur de repli

Elle valait `selflow-comptaflow-secret-2026`, en clair dans le dépôt, à **sept
endroits**. Quiconque a lu ce dépôt pouvait déverser des écritures dans la
comptabilité de n'importe quelle entreprise liée, ou lire la liste de toutes les
entreprises de la plateforme.

La comparaison passe de `!==` à `hash_equals` : une comparaison de chaînes
ordinaire s'arrête au premier caractère différent, et le temps de réponse révèle
alors combien de caractères sont justes.

**Selflow avait le même défaut**, à six endroits, qui annulaient la correction du
lot 0 — corrigé dans le même mouvement, côté Selflow.

### Le déversement devient idempotent

`n_saisie` recevait la référence de pièce, ou `SELF_ . time()` à défaut. Ni
l'une ni l'autre ne distingue un **renvoi** d'une écriture **nouvelle** :
rejouer une synchronisation — après une coupure réseau, après un retry, ou en
relançant simplement la commande — dupliquait tout, et **la balance doublait**
sans que rien ne le signale.

Selflow transmet désormais `SELFLOW-{entreprise}-{écriture}`. La colonne
`cle_selflow` porte une contrainte d'unicité par entreprise.

### Le compte de tiers arrive

Il n'était pas transmis : Comptaflow le cherchait dans son `plan_tiers` à partir
du compte général, ne le trouvait pas, et rattachait l'écriture au seul compte
collectif `411000`. **Le relevé d'un client particulier était impossible à
établir.**

Un tiers inconnu de Comptaflow **n'est pas créé** : le plan de tiers est sa
configuration d'origine, et deux référentiels concurrents feraient qu'aucun ne
ferait foi. L'écriture reste sur son compte collectif — juste, quoique moins
précis — et le comptable la rattache lui-même.

### Les exercices sont comparés

Comptaflow prenait le sien, actif, sans jamais regarder celui de Selflow. Une
pièce d'un exercice que Selflow vient de clore se serait rangée dans l'exercice
courant, et les deux balances auraient divergé en silence. Des exercices
disjoints donnent maintenant un **409** explicite.

### Un journal inconnu ne retombe plus sur le premier de la liste

Une vente pouvait atterrir au journal de caisse, et personne ne s'en apercevait
avant la révision. Le journal de Comptaflow fait foi — c'est sa configuration
d'origine — mais un code qu'il ne connaît pas est une erreur à **signaler**, pas
à rattraper au hasard.

### `type_de_compte` n'est plus `actif` en dur

Un compte de vente `701000` créé à la volée arrivait au bilan, du côté de
l'actif : les états devenaient faux, et l'erreur ne se voyait qu'au compte de
résultat, vide. La classe SYSCOHADA dit le type sans ambiguïté — c'est le
premier chiffre du numéro.

| Classe | Nature | Type |
|---|---|---|
| 1 | Ressources durables | `passif` |
| 2, 3, 5 | Immobilisations, stocks, trésorerie | `actif` |
| 4 | Tiers | `actif` par défaut, l'utilisateur tranche |
| 6, 8 | Charges | `charge` |
| 7 | Produits | `produit` |
| 9 | Analytique | `analytique` |

### Le compte rendu dit la vérité

La réponse porte désormais `count`, `ignorees` et `refus`. Une synchronisation
qui annonce « succès » en ayant écarté la moitié des lignes est pire qu'un
échec.

## Le principe respecté

**Le déversement suit la logique de Comptaflow, qui conserve ses données
d'origine.** Code journal, plan comptable et plan de tiers déjà en place ne sont
jamais réécrits par Selflow ; ce qui manque est créé en dessous, en respectant
la configuration initiale. C'est la règle que vous aviez posée, et chaque
méthode du correctif la commente là où elle s'applique.
