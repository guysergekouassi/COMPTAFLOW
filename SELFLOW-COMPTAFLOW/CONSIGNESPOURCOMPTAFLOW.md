# Consignes pour une session Comptaflow

> **À qui s'adresse ce fichier.** À quelqu'un — humain ou assistant — qui
> travaille dans le dépôt **Comptaflow** (`guysergekouassi/comptaflow`) et n'a
> pas Selflow sous les yeux. Tout ce qu'il faut savoir est ici : le contexte, le
> correctif, la raison de chaque changement, et la façon de vérifier que ça
> marche. Rien à demander à Selflow.

**Branche de travail attendue :** `claude/passerelle-selflow-deversement`
**Base :** `a5da35d` (« mon espace -liaison », état de `main` au 12 août 2026)
**Patch prêt à appliquer :** `0002-deversement-selflow-sur-a5da35d.patch`
(joint à ce fichier ; vérifié, il s'applique proprement sur `a5da35d`)

---

## 1. Ce qui s'est passé, et pourquoi on recommence

Le 9 août 2026, un correctif de la passerelle a été appliqué et poussé sur
`main` : commit **`b3bd59b`**.

Le 12 août, un `git push --force` sur `main` a réécrit l'historique. Le commit
`b3bd59b` n'en fait plus partie. Il n'a laissé aucune trace visible : le code
est simplement revenu à sa version d'avant, sans conflit, sans avertissement,
sans ligne de journal. C'est le mode de disparition propre au force-push — il
ne casse rien, il efface.

`git cherry-pick b3bd59b` échoue sur un poste de travail ordinaire
(`fatal: bad revision`) : l'objet n'a jamais été récupéré localement, et
GitHub finit par le collecter. **D'où ce patch** : il porte le même contenu,
déjà reporté sur `a5da35d`, avec le conflit résolu.

### Le conflit, et comment il a été tranché

Un seul conflit, dans `app/Http/Controllers/Api/ExternalSyncController.php`.
Entre le 9 et le 12 août, la résolution du compte avait été retouchée sur
`main`. Le correctif déplace cette résolution dans deux méthodes dédiées
(`compteGeneral()` et `tiers()`), au même endroit où `main` avait laissé
l'ancien bloc en ligne.

**La version du correctif l'emporte** : sa logique remplace l'ancienne, elle ne
s'y ajoute pas. L'ancien bloc en ligne est supprimé, ses deux comportements
(recherche dans `plan_tiers`, puis création à la volée dans `plan_comptables`)
sont repris — corrigés — dans les méthodes.

---

## 2. Le correctif, point par point

Cinq problèmes. Chacun se manifeste en production sans rien signaler, ce qui
est la raison pour laquelle ils sont passés inaperçus jusqu'ici.

### 2.1 Idempotence — la balance qui double

**Avant :**

```php
'n_saisie' => $refPiece ?: 'SELF_' . time() . '_' . $count,
```

Ni la référence de pièce ni `SELF_ . time()` ne distingue un renvoi d'une
écriture nouvelle. Une coupure réseau pendant un déversement, un retry de file
d'attente, un clic répété sur « synchroniser » : les écritures repassent, et
Comptaflow les crée une seconde fois. La balance double. Rien ne le dit.

**Après :** Selflow transmet un champ `cle_selflow` de la forme
`SELFLOW-{entreprise}-{écriture}`. Une nouvelle colonne du même nom porte une
**contrainte d'unicité par entreprise** (migration
`2026_08_12_000001_cle_idempotence_deversement_selflow.php`). Un second
déversement de la même écriture est reconnu, compté séparément, et ignoré.

C'est la contrainte de base qui fait le travail, pas le test applicatif : deux
requêtes simultanées passeraient toutes deux le `exists()` avant que l'une
n'écrive.

### 2.2 Le compte de tiers — le relevé client impossible

**Avant :** le compte de tiers n'était pas transmis. Comptaflow le cherchait
dans `plan_tiers` à partir du numéro de compte **général** (`411000`), ne le
trouvait évidemment pas, et rattachait l'écriture au seul compte collectif.
Résultat : toutes les ventes de tous les clients au même endroit. Établir le
relevé d'un client donné devient impossible — l'information n'existe plus.

**Après :** Selflow transmet `compte_tiers`. Comptaflow le cherche dans son
propre `plan_tiers` et rattache l'écriture.

**Il ne le crée pas.** C'est délibéré : le plan de tiers est la configuration
d'origine de Comptaflow. Y ajouter des fiches depuis Selflow ferait deux
référentiels concurrents, et le comptable ne saurait plus lequel fait foi. Un
tiers inconnu laisse l'écriture sur son compte collectif — moins précis, mais
juste — et le comptable la rattachera lui-même.

### 2.3 L'exercice — la pièce d'exercice clos

**Avant :**

```php
$exercice = ExerciceComptable::where('company_id', $company->id)
    ->where('is_active', true)->first()
    ?? ExerciceComptable::where('company_id', $company->id)->first();
```

L'exercice actif est pris sans regarder la date de la pièce. Une facture de
décembre déversée en janvier atterrit dans le nouvel exercice. Elle fausse deux
exercices d'un coup : celui qu'elle quitte et celui où elle arrive.

**Après :** Selflow transmet `exercice_debut` et `exercice_fin`. Ils sont
comparés à l'exercice ouvert chez Comptaflow. En cas de désaccord, la pièce est
**refusée avec son motif** — pas rangée au hasard.

### 2.4 Le journal inconnu

**Avant :**

```php
$codeJournal = CodeJournal::where(...)->where('code_journal', $cjCode)->first()
    ?? CodeJournal::where('company_id', $company->id)->first();
```

Un code de journal inconnu retombait sur **le premier journal de la liste**.
Une vente pouvait ainsi atterrir au journal de caisse, et personne ne s'en
apercevait avant la révision.

**Après :** le journal de Comptaflow fait foi — c'est sa configuration
d'origine — mais un code qu'il ne connaît pas est une erreur à signaler, pas à
rattraper au hasard. La ligne est refusée, avec son motif.

### 2.5 Le secret partagé, en clair dans le dépôt

**Avant**, à sept endroits :

```php
config('external_sync.external_sync_secret', 'selflow-comptaflow-secret-2026')
```

La valeur de repli était écrite dans le code. Le dépôt la publie. Quiconque
l'a lue pouvait déverser des écritures dans la comptabilité de n'importe quelle
entreprise liée, ou lire la liste de toutes les entreprises de la plateforme.

Et la comparaison se faisait avec `!==` : une comparaison de chaînes ordinaire
s'arrête au premier caractère différent. Le temps de réponse révèle alors
combien de caractères sont justes, et le secret se devine caractère par
caractère.

**Après :** aucune valeur de repli. Sans `EXTERNAL_SYNC_SECRET` en variable
d'environnement, les points d'entrée externes **refusent tout** — un secret non
configuré ne vaut pas « pas de contrôle ». Et la comparaison passe par
`hash_equals`, en temps constant.

> ⚠️ **À faire après application :** poser `EXTERNAL_SYNC_SECRET` dans le `.env`
> de Comptaflow **et** dans celui de Selflow, avec la même valeur. Sans cela la
> synchronisation refusera tout — ce qui est le comportement voulu, mais il faut
> le savoir avant de conclure à une régression.
>
> Générer une valeur : `php artisan tinker --execute="echo bin2hex(random_bytes(32));"`
>
> Et considérer l'ancienne valeur comme compromise : elle est dans l'historique
> public du dépôt, la retirer du code ne la retire pas de l'historique.

### 2.6 Le compte rendu

En prime : la réponse HTTP distingue désormais les écritures **déversées**,
celles **déjà présentes** (idempotence), et celles **refusées avec leur motif**.

```json
{ "success": true, "count": 12, "ignorees": 3, "refus": ["FA-0042 : journal « XXX » inconnu"] }
```

Une synchronisation qui annonce « succès » en ayant écarté la moitié des lignes
est pire qu'un échec : elle installe une confiance fausse.

---

## 3. Comment appliquer

```bash
cd /chemin/vers/COMPTAFLOW
git fetch origin
git checkout -b claude/passerelle-selflow-deversement origin/main

# Le patch est vérifié sur a5da35d. Si main a bougé depuis, -3 permet
# à git de faire une fusion à trois points plutôt que d'abandonner.
git apply --check 0002-deversement-selflow-sur-a5da35d.patch   # doit être silencieux
git am -3 0002-deversement-selflow-sur-a5da35d.patch

php artisan migrate
php -l app/Http/Controllers/Api/ExternalSyncController.php
```

Puis pousser la branche et la fusionner dans `main` **par pull request, pas par
force-push** — c'est un force-push qui a effacé la première version de ce
correctif.

### Fichiers touchés

| Fichier | Ce qui change |
|---|---|
| `app/Http/Controllers/Api/ExternalSyncController.php` | l'essentiel : idempotence, tiers, exercice, journal, secret, compte rendu |
| `app/Models/EcritureComptable.php` | `cle_selflow` dans `$fillable` |
| `app/Http/Controllers/Super/SuperAdminCompanyController.php` | le secret sans valeur de repli |
| `config/external_sync.php` | idem, et le commentaire qui dit pourquoi |
| `database/migrations/2026_08_12_000001_cle_idempotence_deversement_selflow.php` | la colonne et sa contrainte d'unicité |

---

## 4. Vérifier que ça marche

Sans Selflow, `curl` suffit. Remplacer `SECRET` et `1` par les vraies valeurs.

```bash
# 1. Un secret faux doit renvoyer 401 (Unauthorized — authentification refusée)
curl -s -o /dev/null -w '%{http_code}\n' -X POST \
  http://comptaflow.test/api/external/ecritures/deverser \
  -H 'X-Sync-Secret: mauvais' -H 'Content-Type: application/json' \
  -d '{"selflow_company_id":1,"ecritures":[]}'

# 2. Le même déversement deux fois : la seconde doit rendre ignorees=1, count=0
BODY='{"selflow_company_id":1,"ecritures":[{
  "cle_selflow":"SELFLOW-1-999","code_journal":"VTE","date_ecriture":"2026-08-15",
  "libelle":"Test idempotence","reference_document":"TEST-999",
  "compte_debit":"411000","compte_credit":"","compte_tiers":"411CLI001",
  "debit":1000,"credit":0,"exercice_debut":"2026-01-01","exercice_fin":"2026-12-31"}]}'

curl -s -X POST http://comptaflow.test/api/external/ecritures/deverser \
  -H "X-Sync-Secret: SECRET" -H 'Content-Type: application/json' -d "$BODY"
curl -s -X POST http://comptaflow.test/api/external/ecritures/deverser \
  -H "X-Sync-Secret: SECRET" -H 'Content-Type: application/json' -d "$BODY"

# 3. Un journal inconnu doit sortir dans "refus", pas atterrir ailleurs
#    (rejouer avec "code_journal":"ZZZ" et une cle_selflow neuve)

# 4. Une pièce hors exercice doit être refusée
#    (rejouer avec "exercice_debut":"2025-01-01","exercice_fin":"2025-12-31")
```

---

## 5. Le plan comptable — ce qu'il faut savoir avant de synchroniser

Comptaflow **possède bien** son référentiel SYSCOHADA : `config/syscohada_complet.php`,
**1 259 comptes**, chargeable par entreprise depuis l'écran de configuration
(`AdminConfigController::loadSyscohadaPlan`). Il est proposé dans trois formats :

| Format | Route | Ce que ça donne |
|---|---|---|
| SYSCOHADA (2-4) | `load_syscohada4` | `41`, `411`, `4111` — le numéro brut |
| **COMPTES SAGE (6)** | `load_syscohada6` | `410000`, `411000`, `411100` — complété à 6 par des zéros |
| DC-KNOWING (8) | `load_syscohada8` | `41000000`, `41100000` — complété à 8 |

**Selflow émet des numéros à 6 chiffres** : `411000` clients, `401000`
fournisseurs, `701000` ventes, `601000` achats, `443100` TVA collectée,
`445200` TVA déductible, `447000` autres taxes, `571000` caisse, `521000`
banque.

> **Conséquence pratique : chargez le format « COMPTES SAGE (6) ».** C'est le
> seul des trois où les comptes de Selflow tombent exactement sur des comptes
> existants du référentiel. En format 2-4, `701000` ne correspond à rien : le
> déversement le crée à la volée, avec pour intitulé le libellé de l'écriture.
> Le plan se remplit alors de comptes bâtards à côté des vrais.

Le correctif **ne complète plus** le numéro reçu à `account_digits` par des
zéros à droite, comme le faisait l'ancien code. Ce complément transformait
`411000` en `41100000` en format 8, ce qui pouvait tomber juste par accident et
faux le reste du temps. Le numéro transmis est désormais pris tel quel : c'est
Selflow qui décide de sa numérotation, et elle est stable.

### Un bug repéré au passage (hors correctif)

`routes/web.php:510` route `POST /load-syscohada` vers
`AdminConfigController::loadSyscohadaPlan`, **qui est déclarée `private`**.
Appeler cette route donne une 500 (Internal Server Error — erreur interne du
serveur). Les trois autres routes (`-4`, `-6`, `-8`) passent par des méthodes
publiques et fonctionnent. Soit supprimer la route 510, soit rendre la méthode
publique. Ce n'est pas dans le patch : ça ne concerne pas la passerelle.

---

## 6. Ce que Selflow envoie, pour référence

`POST {comptaflow}/api/external/ecritures/deverser`, en-tête `X-Sync-Secret`.

```json
{
  "selflow_company_id": 1,
  "ecritures": [{
    "cle_selflow":         "SELFLOW-1-4213",
    "code_journal":        "VTE",
    "date_ecriture":       "2026-08-15",
    "libelle":             "Vente de marchandises — FA-0042",
    "reference_document":  "FA-0042",
    "compte_debit":        "411000",
    "compte_credit":       "",
    "compte_tiers":        "411CLI001",
    "debit":               118000,
    "credit":              0,
    "point_de_vente":      "Boutique Plateau",
    "exercice_debut":      "2026-01-01",
    "exercice_fin":        "2026-12-31"
  }]
}
```

Côté Selflow l'envoi est asynchrone (`App\Jobs\DeverserEcritureComptaflow`,
3 tentatives, 10 s d'attente entre deux) : une indisponibilité de Comptaflow ne
bloque plus la saisie d'une vente, et la reprise est automatique. C'est
précisément ce qui rend l'idempotence indispensable.

---

## 7. Ce qui reste ouvert, et n'est pas dans ce patch

- **L'analytique.** Selflow ne transmet aucun axe analytique, et Comptaflow
  n'en attend aucun. Le champ `point_de_vente` est envoyé mais ignoré : c'est
  le candidat naturel pour une ventilation par point de vente, le jour où on
  décidera de la brancher.
- **Le sens de la synchronisation.** Elle est à sens unique : Selflow pousse,
  Comptaflow reçoit. Une correction faite dans Comptaflow ne remonte pas.
