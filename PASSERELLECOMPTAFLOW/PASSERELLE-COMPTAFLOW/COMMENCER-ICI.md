# Commencer ici — travail à faire sur Comptaflow

Ce dossier vient du dépôt **Selflow**. Il porte tout ce qu'il faut pour faire la
moitié Comptaflow d'une passerelle dont la moitié Selflow est déjà écrite,
éprouvée et poussée. Rien ici ne s'exécute dans Selflow.

Une session qui reçoit ce dossier n'a besoin d'aucun autre contexte. Lisez cette
page, puis les deux documents qu'elle désigne, dans cet ordre.

---

## Le principe, en une phrase

**Selflow déverse ; Comptaflow reçoit.** Chaque entreprise de Selflow verse son
référentiel et ses écritures dans Comptaflow comme on y verserait un fichier
d'import. Jamais l'inverse : une entreprise sans abonnement comptable doit
pouvoir travailler seule, et Selflow lui livre déjà son plan comptable, ses
journaux et ses tiers de passage à sa création.

Corollaire qui gouverne tout le reste : **les données d'origine de Comptaflow
font foi.** Un code journal, un compte ou un tiers déjà en place n'est jamais
réécrit par Selflow. Ce qui manque est créé en dessous ; ce qui est inconnu est
signalé, jamais rattrapé au hasard.

---

## Les quatre travaux, par ordre d'urgence

### 1. Écrire `POST /api/external/referentiel/deverser` — **le seul point bloquant**

Tant qu'il n'existe pas, le déversement du référentiel échoue proprement et le
dit à l'utilisateur : rien ne casse, mais **une entreprise nouvelle doit voir
son plan comptable ressaisi à la main chez Comptaflow**.

→ **`RECEVOIR-LE-REFERENTIEL.md`** porte la spécification complète : la charge
utile champ par champ, les règles de création, les codes de réponse attendus, et
ce que Selflow fait de chaque réponse.

Côté Selflow, l'appelant est déjà écrit et couvert :
`app/Modules/Admin/Services/DeversementReferentielService.php`, et ses treize
épreuves dans `tests/Feature/DeversementReferentielTest.php`.

### 2. Fusionner le correctif de la passerelle dans `main`

Il vit sur la branche `claude/passerelle-selflow-deversement` du dépôt
Comptaflow, commit `0407335`. **La fusion n'est pas faite** — `main` est resté
sur `a5da35d`.

Si la branche a disparu, le correctif est ici sous deux formes :
`0002-deversement-selflow-sur-a5da35d.patch`, vérifié (`git apply --check`
passe sur `a5da35d`).

→ **`CONSIGNES-POUR-COMPTAFLOW.md`** est le dossier complet et autoportant :
contexte, raison de chaque changement, application, vérification au `curl`, plan
comptable.

Ce correctif porte six choses, dont trois touchent l'argent ou la sécurité :

| Ce qu'il corrige | Ce qui se passait sans lui |
|---|---|
| Le secret partagé n'a plus de valeur de repli, et se compare en temps constant | `selflow-comptaflow-secret-2026` était en clair dans le dépôt, à sept endroits. Quiconque l'a lu pouvait déverser des écritures dans n'importe quelle comptabilité liée |
| Le déversement devient idempotent (`cle_selflow`) | Rejouer une synchronisation **dupliquait tout, et la balance doublait** en silence |
| Le compte de tiers arrive | Tout retombait sur le compte collectif `411000` : le relevé d'un client était impossible |
| Les exercices sont comparés | Une pièce d'un exercice clos se rangeait dans l'exercice courant |
| Un journal inconnu est signalé | Une vente pouvait atterrir au journal de caisse |
| `type_de_compte` suit la classe SYSCOHADA | Un compte `701000` arrivait à l'actif du bilan |

### 3. Deux défauts d'import, indépendants de la passerelle

- **`plan_tiers.compte_general` est `NOT NULL`, et `MasterTiersImport` ne le
  renseigne pas.** Toute importation de tiers viole la contrainte d'intégrité,
  même avec un fichier parfait.
- **`AdminConfigController:520` construit `MasterPlanImport` sans le chemin du
  fichier**, donc `detectDelimiter()` retombe toujours sur `;`.

### 4. Les quatre modèles d'import à remplacer

Les fichiers de `public/templates/import/` **ne sont pas des modèles** : ce sont
des exports Sage bruts d'ELIKET MARKET, tirage du 22/01/2026, bandeau de titre
et cellules fusionnées compris. Vérifié fichier par fichier en rejouant la
logique des importeurs :

| Fichier | Contient | Ce que l'import en tire |
|---|---|---|
| `modele_plan_comptable.xlsx` | 26 comptes | **0** — `row[0]` vaut « Détail » |
| `modele_plan_tiers.xlsx` | 2 tiers | **2 lignes inversées** |
| `modele_codes_journaux.xlsx` | 1 journal | 1 ligne sans son type |
| `modele_ecritures.xlsx` | 6 feuilles d'impression | rien — aucune route ne l'importe |

Et le dépôt **publie ainsi la comptabilité réelle d'un client**.

Les quatre classeurs refaits sont dans `modeles-import/` — une feuille, une
en-tête, l'ordre que les importeurs lisent, exemples SYSCOHADA neutres.
Vérifiés : 7 comptes, 4 tiers, 5 journaux retenus, seule l'en-tête écartée. La
marche à suivre est en section 7 de `CONSIGNES-POUR-COMPTAFLOW.md`.

---

## À faire en même temps que la fusion

Poser `EXTERNAL_SYNC_SECRET` dans les **deux** `.env`, Selflow et Comptaflow,
avec la même valeur tirée au hasard. Sans elle la synchronisation refuse tout —
c'est le comportement voulu, mais il faut le savoir avant de conclure à une
régression.

Et considérer `selflow-comptaflow-secret-2026` comme **compromis** : il est dans
l'historique public.

Vérifier enfin que `companies.tier_digits` vaut **6** : c'est la convention de
numérotation des tiers que Selflow applique de son côté.

---

## Ce que ce dossier contient

| Fichier | Rôle |
|---|---|
| `COMMENCER-ICI.md` | cette page |
| `RECEVOIR-LE-REFERENTIEL.md` | la spécification du point d'entrée à écrire — **travail n° 1** |
| `CONSIGNES-POUR-COMPTAFLOW.md` | le dossier complet du correctif — **travail n° 2** |
| `0002-deversement-selflow-sur-a5da35d.patch` | le correctif, vérifié sur `a5da35d` |
| `0001-deversement-selflow.patch` | la première version du correctif, conservée pour mémoire |
| `LISEZ-MOI.md` | le résumé d'origine du correctif |
| `modeles-import/` | les quatre classeurs refaits — **travail n° 4** |

---

## Ce qu'il ne faut pas faire

- **Ne pas remettre de valeur de repli au secret.** Un déversement qui refuse
  tout parce que la variable manque est le comportement voulu.
- **Ne pas créer les tiers inconnus.** Le plan de tiers de Comptaflow est sa
  configuration d'origine ; deux référentiels concurrents feraient qu'aucun ne
  ferait foi. L'écriture reste sur son compte collectif, et le comptable la
  rattache lui-même.
- **Ne pas rattraper un code journal inconnu** en prenant le premier de la
  liste. C'est une erreur à signaler.
- **Ne rien renvoyer vers Selflow.** La passerelle est à sens unique. La
  méthode qui faisait l'inverse a été retirée de Selflow après avoir dépouillé
  de son plan comptable une entreprise dont le comptable n'avait pas encore
  rempli le sien.
