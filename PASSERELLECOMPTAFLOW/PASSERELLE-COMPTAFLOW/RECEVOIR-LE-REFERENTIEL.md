# Comptaflow doit recevoir le référentiel de Selflow

Ce document ne contient rien qui s'exécute dans Selflow. Il décrit **la moitié
Comptaflow** d'un changement dont la moitié Selflow est déjà écrite et
éprouvée : `app/Modules/Admin/Services/DeversementReferentielService.php`, et
ses treize épreuves dans `tests/Feature/DeversementReferentielTest.php`.

Tant que l'endpoint décrit ici n'existe pas chez Comptaflow, le déversement du
référentiel échoue proprement et le dit à l'utilisateur — il ne casse rien.

---

## Ce qui change, et pourquoi

**Selflow déverse ; Comptaflow reçoit.** Chaque entreprise de Selflow verse ses
données dans Comptaflow comme on y verserait un fichier d'import.

Le code faisait exactement l'inverse. `ComptabiliteService::synchroniserDepuisComptaflow()`
appelait `link-company`, **recevait** le plan comptable, les codes journaux et
les tiers de Comptaflow, les recopiait dans Selflow — puis **supprimait** toute
ligne Selflow marquée `source = comptaflow` qui ne figurait pas dans la réponse.

Deux conséquences, toutes deux silencieuses :

- une entreprise dont le comptable n'avait pas encore rempli son plan chez
  Comptaflow **se retrouvait dépouillée du sien** ;
- Selflow se construisait sur Comptaflow, alors qu'une entreprise sans
  abonnement comptable doit pouvoir travailler seule. Le trousseau
  (`TrousseauEntrepriseService`) lui donne dès sa création 38 comptes, 10
  journaux et ses deux tiers de passage — c'est ce patrimoine-là qui doit
  partir vers Comptaflow, pas l'inverse.

Cette méthode est retirée. Le déversement la remplace.

---

## Deux appels, dans cet ordre

### 1. `POST /api/external/link-company` — inchangé

Selflow l'appelle toujours, et **n'en lit plus qu'une chose** : `company_id`.
Le `plan_comptable`, les `codes_journaux` et les `tiers` que la réponse
contient encore sont ignorés. Rien n'est à changer de ce côté ; les champs
peuvent rester, ils ne servent simplement plus.

Les tableaux `clients` et `fournisseurs` que Selflow envoyait dans la requête
ne sont plus transmis : ils partent maintenant par le second appel, avec leur
numéro de tiers et leur compte de rattachement.

### 2. `POST /api/external/referentiel/deverser` — **à écrire**

```json
{
  "secret": "<EXTERNAL_SYNC_SECRET>",
  "selflow_company_id": 7,
  "comptaflow_company_id": 42,

  "plan_comptable": [
    { "numero_de_compte": "401000", "intitule": "Fournisseurs", "numero_original": null },
    { "numero_de_compte": "411000", "intitule": "Clients",      "numero_original": null }
  ],

  "codes_journaux": [
    { "code_journal": "VTE", "intitule": "Ventes",            "type": "Ventes",
      "compte_numero": null,     "numero_original": null },
    { "code_journal": "MTN", "intitule": "MTN Mobile Money",  "type": "Trésorerie",
      "compte_numero": "521500", "numero_original": null }
  ],

  "tiers": [
    { "numero_de_tiers": "410000", "intitule": "Client divers", "type_de_tiers": "client",
      "compte_general": "411000", "informations": {}, "numero_original": "3" },
    { "numero_de_tiers": "410007", "intitule": "Konan Yao",     "type_de_tiers": "client",
      "compte_general": "411000",
      "informations": { "telephone": "+225 07 00 00 00" },
      "numero_original": "11" }
  ]
}
```

Réponse attendue :

```json
{ "success": true, "comptes": 38, "journaux": 10, "tiers": 12 }
```

En cas de refus, `{"success": false, "message": "…"}` : Selflow rapporte le
message tel quel à l'utilisateur.

---

## Comment le traiter, côté Comptaflow

### Le secret, d'abord

Même contrôle que sur les autres routes `external` : `hash_equals` contre
`EXTERNAL_SYNC_SECRET`, et refus si la variable est absente. Une comparaison
`!==` s'arrête au premier caractère différent, et le temps de réponse révèle
alors combien de caractères sont justes.

### La liaison doit exister

**Comptaflow ne reçoit que si la liaison existe.** Si aucune `company` ne
correspond à `comptaflow_company_id` / `selflow_company_id`, la requête est
refusée — elle n'en crée pas une au passage.

### Emprunter la logique d'import déjà écrite

Les champs reprennent **exactement les colonnes des modèles d'import** :

| Jeu | Colonnes du modèle | Champs reçus |
|---|---|---|
| `modele_plan_comptable.xlsx` | `N° compte` ; `Intitulé du compte` | `numero_de_compte` ; `intitule` |
| `modele_codes_journaux.xlsx` | `Code` ; `Intitulé` ; `Type` | `code_journal` ; `intitule` ; `type` |
| `modele_plan_tiers.xlsx` | `N° tiers` ; `Intitulé du tiers` ; `Type` | `numero_de_tiers` ; `intitule` ; `type_de_tiers` |

C'est délibéré : le déversement doit passer par la logique d'import de
Comptaflow, pas par une seconde voie à maintenir en parallèle.

**`informations` ne passe aucun contrôle.** Téléphone, adresse, courriel,
NCC, RCCM, régime d'imposition : rien de comptable n'en dépend. Ces champs se
déversent tels quels et se donnent à consulter. Un champ vide n'est pas
transmis — il écraserait ce que Comptaflow détient peut-être déjà.

### Amorcer quand Comptaflow est vide

**C'est le point central de la demande.** Si l'entreprise n'a chez Comptaflow
ni plan comptable, ni codes journaux, ni plan de tiers, ces données sont
**créées à partir de celles de Selflow**. L'entreprise ne doit pas avoir à
ressaisir un plan qu'elle possède déjà.

### Ne rien écraser quand Comptaflow n'est pas vide

Symétriquement, ce que le comptable a saisi ou modifié chez Comptaflow lui
appartient. Un `updateOrCreate` sur la clé naturelle — le numéro de compte, le
code journal, le numéro de tiers, tous cadrés par l'entreprise — suffit :

- ce qui manque est créé ;
- ce qui existe est mis à jour ;
- **rien n'est supprimé.** C'est la faute de l'ancien sens, à ne pas reproduire
  en miroir : un compte absent du déversement peut avoir été créé par le
  comptable, ou porter des écritures.

### L'ordre compte

Le plan comptable, puis les journaux, puis les tiers. Un tiers renvoie à son
compte général par une clé étrangère : il doit exister avant lui.

---

## Le point qui bloque encore, et qu'il faut corriger d'abord

`plan_tiers.compte_general` est `NOT NULL` avec une clé étrangère
(`2025_06_25_215544_create_plan_tiers_table.php:18`), et
`MasterTiersImport::model()` ne le renseigne jamais. **L'import des tiers échoue
donc sur une violation d'intégrité, même avec un fichier correct** — et le
déversement échouera pour la même raison s'il emprunte le même chemin.

Selflow transmet `compte_general` sur chaque tiers, précisément pour cela. Il
reste à le poser dans le modèle.

Deux autres points déjà signalés, toujours ouverts :

- `AdminConfigController:520` construit `MasterPlanImport` sans le chemin du
  fichier, si bien que `detectDelimiter()` renvoie toujours `;` quel que soit
  le séparateur réel ;
- `companies.tier_digits` doit valoir **6** pour chaque entreprise. Une
  migration le pose à 6, une autre à 8. À huit, les numéros de tiers de Selflow
  — six caractères — ne correspondront à rien, et chaque écriture retombera sur
  le compte collectif.

---

## Vérifier

Une fois l'endpoint écrit :

1. poser `EXTERNAL_SYNC_SECRET`, **identique dans les deux `.env`** ;
2. dans Selflow, ouvrir les paramètres de l'entreprise et lancer la
   synchronisation Comptaflow ;
3. le message doit annoncer le nombre de comptes, journaux et tiers déversés ;
4. relancer : rien ne doit doubler, et rien ne doit disparaître.
