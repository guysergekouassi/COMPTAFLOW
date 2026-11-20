# Cahier des charges technique — Connecteur RH-FLOW vers COMPTAFLOW

**Date :** 20 novembre 2026  
**Destinataire :** équipe chargée du développement RH-FLOW  
**Application centrale :** COMPTAFLOW  
**Objet :** réaliser le côté RH-FLOW de la liaison des entreprises et du déversement manuel des écritures de paie.

> Ce document est le contrat de réalisation à transmettre à l'équipe COMPTAFLOW/RH-FLOW. Il décrit ce que RH-FLOW doit développer et respecter. Il ne prétend pas que le code RH-FLOW est déjà réalisé.

---

## 1. Règle générale : deux opérations séparées

La connexion des applications et l'association des dossiers ne doivent pas être confondues.

1. **Connexion des applications :** l'administrateur crée la connexion dans COMPTAFLOW, choisit la durée du code, copie le code généré et le colle dans la configuration RH-FLOW.
2. **Association des entreprises :** une fois l'application reconnue, l'administrateur sélectionne un dossier COMPTAFLOW puis l'entreprise RH-FLOW correspondante. La correspondance est enregistrée des deux côtés.
3. **Déversement :** les écritures de paie ne partent que lorsque l'utilisateur clique explicitement sur le bouton **Déverser** dans RH-FLOW.

Enregistrer une connexion ou associer deux entreprises ne doit jamais déclencher un déversement.

## 2. Paramétrage de la connexion dans RH-FLOW

RH-FLOW doit fournir un écran de configuration simple comprenant :
- URL du serveur COMPTAFLOW ;
- code de liaison généré par COMPTAFLOW ;
- état de connexion : non configurée, à vérifier, connectée, expirée ou révoquée ;
- date d'expiration du code, si elle est communiquée ;
- action **Tester la connexion** ;
- action **Enregistrer / remplacer le code**.

Le code doit être stocké de manière protégée côté serveur, jamais dans le JavaScript, le HTML public, les journaux ou une réponse accessible à un utilisateur non autorisé.

Le code est fourni par COMPTAFLOW. Lors de sa génération, le Super Admin choisit sa durée : 3 mois (90 jours), 6 mois (180 jours), 1 an (365 jours, valeur par défaut) ou 2 ans (730 jours). La date d'expiration correspondante est affichée à l'administrateur. RH-FLOW doit accepter le code pendant sa période de validité et refuser un code expiré ou révoqué. RH-FLOW ne doit pas fixer sa propre durée, fabriquer le code, le modifier ni tenter de le deviner. Si COMPTAFLOW le renouvelle, l'ancien code doit être remplacé par le nouveau après validation.

## 3. Endpoint obligatoire à développer dans RH-FLOW : catalogue des entreprises

### Route attendue

`GET /api/integration/companies`

Cette route doit être accessible sur le domaine RH-FLOW configuré dans COMPTAFLOW.

### Authentification de cet endpoint

COMPTAFLOW appelle l'endpoint avec l'en-tête HTTP :

`X-Integration-Pairing-Code: <code_fourni_par_comptaflow>`

RH-FLOW doit :
- vérifier le code reçu avec une comparaison sûre ;
- refuser un code absent, invalide, expiré ou révoqué ;
- vérifier que le code appartient bien à l'intégration COMPTAFLOW attendue ;
- ne jamais accepter un code fourni dans un paramètre URL ;
- ne jamais retourner les entreprises d'une autre organisation ou d'un autre périmètre auquel l'intégration n'a pas accès.

### Format de réponse obligatoire

Réponse HTTP 200 :

```json
{
  "data": [
    {
      "id": "identifiant-stable-rhflow",
      "name": "Nom de l'entreprise RH-FLOW"
    }
  ]
}
```

Contraintes :
- `id` est l'identifiant stable et unique de l'entreprise dans RH-FLOW ; le transmettre sous forme de chaîne ;
- `name` est le nom lisible de l'entreprise ;
- une entreprise inactive, supprimée ou non autorisée ne doit pas être proposée ;
- ne pas renvoyer de mot de passe, donnée personnelle inutile, donnée de paie ou information financière dans ce catalogue ;
- ne pas renvoyer une liste globale si le code ne donne pas explicitement accès à toutes les entreprises de l'organisation.

Réponses d'erreur recommandées :
- 401 : code absent ou invalide ;
- 403 : code reconnu mais accès interdit/révoqué ;
- 429 : trop de requêtes ;
- 5xx : erreur interne, sans exposer de secret ni de trace technique.

Le format JSON doit rester stable. COMPTAFLOW attend la propriété racine `data` contenant les objets `id` et `name`.

## 4. Association des entreprises : deux sens possibles et une seule correspondance

La liaison doit pouvoir être initiée depuis COMPTAFLOW **ou** depuis RH-FLOW. Les deux parcours sont équivalents et doivent aboutir à la même relation, sans créer de doublon.

### 4.1 Parcours initié depuis COMPTAFLOW

1. L'administrateur sélectionne un dossier COMPTAFLOW.
2. COMPTAFLOW appelle le catalogue des entreprises RH-FLOW avec le code de liaison.
3. Une fenêtre affiche uniquement les entreprises RH-FLOW autorisées.
4. L'administrateur sélectionne l'entreprise RH-FLOW correspondante.
5. COMPTAFLOW revalide côté serveur l'identifiant distant et l'accès avant d'enregistrer l'association.
6. COMPTAFLOW confirme la relation à RH-FLOW ; RH-FLOW enregistre la même correspondance.

### 4.2 Parcours initié depuis RH-FLOW — obligatoire également

1. L'administrateur RH-FLOW sélectionne une entreprise RH-FLOW.
2. RH-FLOW demande à COMPTAFLOW le catalogue des dossiers COMPTAFLOW autorisés pour l'application RH-FLOW.
3. COMPTAFLOW ne retourne que les dossiers explicitement autorisés à cette application par le Super Admin ou par une règle d'autorisation validée. Il ne retourne jamais le catalogue global.
4. RH-FLOW affiche ces dossiers dans une fenêtre ou liste déroulante.
5. L'administrateur RH-FLOW choisit le dossier COMPTAFLOW correspondant.
6. RH-FLOW envoie une demande de création de correspondance à COMPTAFLOW.
7. COMPTAFLOW vérifie côté serveur que :
   - l'application RH-FLOW est active et authentifiée ;
   - l'entreprise RH-FLOW appartient bien à cette application ;
   - le dossier COMPTAFLOW est autorisé pour cette application ;
   - aucun lien actif contradictoire ou doublon n'existe ;
   - les deux dossiers sont actifs et accessibles.
8. COMPTAFLOW crée la liaison comme source centrale de vérité et renvoie son résultat.
9. RH-FLOW enregistre l'identifiant de liaison retourné et affiche le statut confirmé.

### 4.3 Endpoints COMPTAFLOW à fournir pour le parcours inverse

Les routes ci-dessous constituent le contrat à développer/valider pour permettre à RH-FLOW d'initier la liaison. Elles ne doivent pas être considérées comme déjà disponibles tant qu'elles n'ont pas été ajoutées aux routes et contrôleurs COMPTAFLOW et testées.

**Lister les dossiers COMPTAFLOW autorisés :**

`POST /api/integration/companies/catalog`

Requête JSON :

```json
{
  "client_id": "cf_app_identifiant_fourni",
  "client_secret": "secret_applicatif",
  "partner_company_id": "rh_company_104"
}
```

Réponse HTTP 200 :

```json
{
  "success": true,
  "data": [
    {
      "id": 168,
      "name": "3D PRESTA"
    }
  ]
}
```

La réponse doit être limitée aux dossiers explicitement autorisés à l'application RH-FLOW. Le champ `partner_company_id` sert à vérifier le contexte de l'entreprise source et ne sélectionne pas le dossier COMPTAFLOW.

**Demander l'association :**

`POST /api/integration/companies/link`

Requête JSON :

```json
{
  "client_id": "cf_app_identifiant_fourni",
  "client_secret": "secret_applicatif",
  "partner_company_id": "rh_company_104",
  "company_id": 168
}
```

Réponse HTTP 201 :

```json
{
  "success": true,
  "link": {
    "id": "link_opaque_id",
    "partner_company_id": "rh_company_104",
    "company_id": 168,
    "status": "active"
  },
  "message": "Les deux entreprises sont maintenant liées."
}
```

**Important :** ces deux payloads décrivent le contrat fonctionnel à implémenter pour le parcours inverse. Ils ne constituent pas une permission d'accès : COMPTAFLOW doit vérifier chaque champ en base et rejeter tout identifiant hors périmètre. Le secret applicatif doit transiter exclusivement en HTTPS, être conservé côté serveur et ne jamais être journalisé. À terme, préférer le jeton Bearer court plutôt que répéter le secret dans chaque requête ; si le endpoint utilise Bearer, supprimer `client_secret` du JSON et exiger `Authorization: Bearer <access_token>`.

### 4.4 Correspondance unique et cohérence des deux côtés

La liaison centrale doit contenir au minimum :
- identifiant de l'application ;
- identifiant du dossier COMPTAFLOW ;
- identifiant stable du dossier RH-FLOW ;
- nom d'affichage des deux dossiers (copie informative, pas clé d'identité) ;
- statut actif/révoqué ;
- date de création et de mise à jour ;
- utilisateur ayant initié la demande ;
- origine de la demande : COMPTAFLOW ou RH-FLOW.

Les identifiants stables priment sur les noms. Un changement de nom ne doit pas casser la liaison.

Une entreprise RH-FLOW ne peut pas être liée simultanément à plusieurs dossiers COMPTAFLOW pour la même application, sauf règle métier explicite validée par le propriétaire. Une même entreprise COMPTAFLOW ne peut pas être liée à plusieurs entreprises RH-FLOW dans le même périmètre si cela crée une ambiguïté de déversement.

Les deux interfaces doivent afficher le même statut central. En cas d'échec de confirmation réseau, RH-FLOW doit relire l'état auprès de COMPTAFLOW avant de réessayer : ne pas créer une seconde liaison à l'aveugle.

## 5. Authentification pour les appels métier COMPTAFLOW

Après l'établissement de la liaison, RH-FLOW utilise les identifiants applicatifs remis par COMPTAFLOW :
- `client_id` ;
- `client_secret`.

Ces identifiants sont des secrets serveur-à-serveur. Ils ne doivent jamais être intégrés dans le navigateur ou dans une application mobile distribuée.

### Obtention d'un jeton

Route COMPTAFLOW :

`POST /api/integration/token`

Corps JSON :

```json
{
  "client_id": "client_id_fourni",
  "client_secret": "secret_fourni",
  "company_id": 123
}
```

Ici, `company_id` désigne le dossier COMPTAFLOW que l'administrateur a explicitement associé. Il sert uniquement à sélectionner une liaison déjà autorisée : il ne permet pas de choisir arbitrairement une entreprise. COMPTAFLOW doit refuser tout identifiant qui ne figure pas dans les liaisons actives de cette application.

Réponse attendue en cas de succès :

```json
{
  "access_token": "jeton_temporaire",
  "token_type": "Bearer",
  "expires_in": 3600,
  "company": {
    "id": 123,
    "name": "Nom du dossier COMPTAFLOW"
  }
}
```

Le jeton est temporaire (une heure selon le contrat actuel). RH-FLOW doit le conserver côté serveur, demander un nouveau jeton à son expiration et ne jamais le journaliser.

Pour les appels métier, envoyer :

`Authorization: Bearer <access_token>`

Ne pas envoyer `client_secret`, le code de liaison ou un mot de passe dans le payload métier.

## 6. Déversement des écritures de paie — bouton obligatoire

RH-FLOW doit ajouter un bouton explicite **Déverser vers COMPTAFLOW** dans l'écran de validation/consultation des écritures de paie.

Comportement exigé :
1. l'utilisateur choisit et valide la période de paie ;
2. RH-FLOW prépare le lot d'écritures selon le payload COMPTAFLOW ;
3. RH-FLOW vérifie qu'une liaison active existe pour l'entreprise ;
4. RH-FLOW obtient ou renouvelle le jeton Bearer ;
5. RH-FLOW demande confirmation à l'utilisateur et envoie le lot ;
6. RH-FLOW affiche le résultat détaillé : accepté, refusé, partiellement invalide ou à reprendre ;
7. RH-FLOW conserve la référence de dépôt et le résultat pour éviter un double envoi.

### Endpoint COMPTAFLOW

`POST /api/external/ecritures/deverser`

En-têtes :
- `Accept: application/json`
- `Content-Type: application/json`
- `Authorization: Bearer <access_token>`

Structure minimale confirmée par le contrôleur COMPTAFLOW :

```json
{
  "operation": "PAIE-2026-11",
  "atomique": true,
  "exercice_debut": "2026-01-01",
  "exercice_fin": "2026-12-31",
  "ecritures": [
    {
      "...": "Lignes conformes au payload comptable COMPTAFLOW déjà défini"
    }
  ]
}
```

**Important :** l'exemple ci-dessus illustre uniquement la structure supérieure du lot. Les champs de chaque ligne doivent reprendre strictement le payload d'écritures déjà utilisé et documenté pour COMPTAFLOW/SELOW. Ne pas inventer de nouveaux noms de champs, ne pas modifier les règles débit/crédit et ne pas convertir les montants sans spécification validée.

Règles impératives :
- `ecritures` est obligatoire et doit être un tableau ;
- `atomique` doit être à `true` pour un lot de paie : si une ligne est refusée, l'opération entière doit être refusée afin d'éviter une écriture déséquilibrée ;
- `operation` doit identifier de manière stable l'opération source ;
- les dates d'exercice doivent correspondre à l'exercice du dossier COMPTAFLOW ; un désaccord doit être traité comme une erreur à corriger, pas contourné ;
- ne pas inclure de `company_id`, `comptaflow_company_id`, `selflow_company_id` ou identifiant de routage dans le payload métier ;
- ne jamais envoyer une écriture d'une entreprise RH-FLOW avec le jeton d'une autre entreprise ;
- ne jamais déclencher le dépôt automatiquement à la validation de la liaison ou à l'enregistrement des paramètres.

Le dossier destinataire est déterminé par le jeton Bearer, lié côté COMPTAFLOW au dossier sélectionné. Le JSON métier ne peut pas changer ce destinataire.

## 7. Mode manuel par défaut

Le mode de transfert doit rester **MANUEL par défaut**.

- Aucun envoi automatique à la création d'une liaison.
- Aucun envoi automatique à la simple validation d'une période, sauf si l'utilisateur a explicitement activé ultérieurement un mode automatique dans un paramètre dédié.
- Le bouton **Déverser** doit rester le déclencheur de référence.
- Avant envoi, présenter l'entreprise, la période, le nombre d'écritures et le total débit/crédit.
- Bloquer l'envoi si le total débit est différent du total crédit.
- Après succès, identifier le lot comme déjà déversé ; demander une confirmation renforcée en cas de nouvel envoi volontaire.

## 8. Traçabilité à conserver dans RH-FLOW

Pour chaque tentative, enregistrer :
- utilisateur qui a cliqué sur Déverser ;
- entreprise RH-FLOW ;
- dossier COMPTAFLOW associé ;
- période de paie ;
- date et heure de la demande ;
- endpoint appelé et méthode HTTP ;
- identifiant unique de requête ;
- identifiant stable de l'opération source ;
- nombre de lignes et totaux débit/crédit ;
- code HTTP et résultat ;
- message de refus ou référence retournée par COMPTAFLOW.

Ne jamais enregistrer en clair :
- client_secret ;
- access_token ;
- code de liaison ;
- mot de passe ;
- en-tête Authorization.

La traçabilité COMPTAFLOW doit également être consultée pour rapprocher les appels reçus avec les dépôts RH-FLOW.

## 9. Gestion des erreurs et sécurité

RH-FLOW doit gérer au minimum :
- 401 : renouveler le jeton une fois si expiré ; si les identifiants sont invalides, demander une nouvelle configuration ;
- 403 : liaison révoquée ou permission insuffisante ; bloquer le dépôt ;
- 409 : conflit d'exercice ou opération déjà traitée ; afficher le motif et ne pas contourner ;
- 422 : payload invalide ou dossier non configuré ; afficher les erreurs de validation ;
- 429 : respecter le délai de reprise ;
- 5xx / timeout : conserver la tentative en état incertain et vérifier le statut avant de renvoyer pour éviter un doublon.

Autres exigences :
- HTTPS obligatoire ;
- validation stricte des réponses JSON ;
- timeout réseau défini ;
- protection contre les doubles clics ;
- idempotence à partir d'un identifiant d'opération source stable ;
- aucune écriture SQL directe dans la base COMPTAFLOW ;
- aucun accès à une autre entreprise par modification d'un identifiant côté client.

## 10. Tests de réception obligatoires

Le connecteur ne sera considéré terminé qu'après validation des cas suivants :

1. Un code de liaison valide permet à COMPTAFLOW de charger le catalogue RH-FLOW.
2. Un code invalide, expiré ou révoqué retourne une erreur et ne révèle aucun catalogue.
3. Le catalogue ne montre que les entreprises autorisées.
4. L'administrateur peut associer le bon dossier COMPTAFLOW à la bonne entreprise RH-FLOW.
5. Une entreprise non liée ne peut pas déverser.
6. Un jeton obtenu pour le dossier A ne peut jamais écrire dans le dossier B.
7. Une écriture de paie équilibrée et conforme est reçue dans le bon dossier.
8. Un lot déséquilibré ou non conforme est refusé intégralement.
9. Une période d'exercice incohérente est refusée et clairement signalée.
10. Un double clic ou une répétition réseau ne crée pas deux fois la même opération.
11. Une révocation de liaison bloque les dépôts suivants.
12. L'historique RH-FLOW et l'historique COMPTAFLOW permettent d'identifier qui a envoyé quoi, quand, depuis quelle entreprise et avec quel résultat.
13. La validation de la connexion et l'association des entreprises ne déclenchent aucun dépôt.
14. Le bouton Déverser fonctionne en mode manuel, qui est le mode initial.

Fournir les résultats de tests et les références de commits avant de déclarer le connecteur en production.

---

## 11. Éléments à remettre par l'équipe RH-FLOW

- Le commit du endpoint `GET /api/integration/companies`.
- Le commit de l'écran de configuration du code de liaison.
- Le commit de la correspondance des entreprises.
- Le commit du bouton manuel **Déverser vers COMPTAFLOW**.
- Les tests automatisés et leurs résultats.
- Un exemple anonymisé de réponse du catalogue.
- Un exemple anonymisé de dépôt accepté et un exemple de dépôt refusé.
- La confirmation que les secrets ne sont jamais exposés au navigateur ou aux journaux.

**Critère final :** la liaison est établie, les entreprises sont correctement associées, et un utilisateur RH-FLOW peut déclencher manuellement le transfert d'une paie équilibrée vers le seul dossier COMPTAFLOW autorisé, avec une traçabilité complète.


---

## 12. Payloads de référence à respecter

Les exemples ci-dessous reprennent les noms de champs effectivement utilisés par les contrôleurs COMPTAFLOW consultés dans le dépôt. Ils doivent être traités comme contrats d'intégration. Les identifiants de routage du dossier ne doivent jamais être ajoutés aux payloads métier.

### 12.1 Échange initial du code de liaison maison

Route COMPTAFLOW :

`POST /api/integration/pairing/redeem`

```json
{
  "pairing_code": "code_copie_depuis_comptaflow",
  "application_code": "rh-flow"
}
```

Réponse de succès (secret affiché une seule fois) :

```json
{
  "success": true,
  "application": {
    "code": "rh-flow",
    "name": "RH-FLOW"
  },
  "api_base_url": "https://comptaflow.example",
  "client_id": "cf_app_xxxxxxxxx",
  "client_secret": "cf_secret_xxxxxxxxx",
  "secret_expires_at": "2027-11-20T10:00:00Z",
  "next_step": "Associez au moins un dossier autorisé avant de demander un jeton Bearer."
}
```

Ne jamais afficher ou journaliser le secret après cet échange. Le code est à usage unique et sa durée est celle choisie lors de sa génération dans COMPTAFLOW.

### 12.2 Obtention d'un jeton Bearer

Route :

`POST /api/integration/token`

```json
{
  "client_id": "cf_app_xxxxxxxxx",
  "client_secret": "cf_secret_xxxxxxxxx",
  "company_id": 168
}
```

Réponse :

```json
{
  "access_token": "cf_tok_xxxxxxxxx",
  "token_type": "Bearer",
  "expires_in": 3600,
  "company": {
    "id": 168,
    "name": "3D PRESTA"
  }
}
```

Le `company_id` ne peut désigner qu'un dossier déjà lié et autorisé pour l'application. Le jeton émis est limité à ce seul dossier.

### 12.3 Déversement du référentiel

Route :

`POST /api/external/referentiel/deverser`

En-têtes : `Authorization: Bearer <access_token>`, `Accept: application/json`, `Content-Type: application/json`.

```json
{
  "plan_comptable": [
    {
      "numero_de_compte": "601000",
      "intitule": "Achats de marchandises",
      "numero_original": "601000"
    }
  ],
  "codes_journaux": [
    {
      "code_journal": "OD",
      "intitule": "Opérations diverses",
      "type": "od",
      "compte_numero": "",
      "numero_original": "OD"
    }
  ],
  "tiers": [
    {
      "numero_de_tiers": "FOU001",
      "intitule": "FOURNISSEUR EXEMPLE",
      "compte_general": "401000",
      "type_de_tiers": "fournisseur",
      "numero_original": "FOU001",
      "email": "contact@example.test",
      "telephone": "+225000000000",
      "adresse": "Abidjan"
    }
  ]
}
```

Chaque famille est facultative, mais lorsqu'elle est fournie elle doit être un tableau. Les champs principaux de mapping sont :
- plan comptable : `numero_de_compte`, `intitule`, `numero_original` ;
- journaux : `code_journal`, `intitule`, `type`, `compte_numero`, `numero_original` ;
- tiers : `numero_de_tiers`, `intitule`, `compte_general`, `type_de_tiers`, `numero_original`. Les autres champs de fiche peuvent compléter les informations du tiers.

Le numéro source est la référence d'origine. COMPTAFLOW applique ses propres règles de numérotation et conserve le numéro source pour la correspondance. Ne pas envoyer un second champ de routage entreprise.

### 12.4 Déversement des écritures de paie

Route :

`POST /api/external/ecritures/deverser`

En-têtes : `Authorization: Bearer <access_token>`, `Accept: application/json`, `Content-Type: application/json`.

```json
{
  "operation": "PAIE-2026-11",
  "atomique": true,
  "exercice_debut": "2026-01-01",
  "exercice_fin": "2026-12-31",
  "ecritures": [
    {
      "cle_source": "PAIE-2026-11-SALAIRE-001",
      "operation_source": "PAIE-2026-11",
      "date_ecriture": "2026-11-30",
      "reference_document": "BULLETIN-001",
      "code_journal": "OD",
      "libelle": "Salaires de novembre 2026",
      "compte_debit": "661000",
      "compte_credit": null,
      "compte_tiers": null,
      "debit": 250000,
      "credit": 0
    },
    {
      "cle_source": "PAIE-2026-11-SALAIRE-002",
      "operation_source": "PAIE-2026-11",
      "date_ecriture": "2026-11-30",
      "reference_document": "BULLETIN-001",
      "code_journal": "OD",
      "libelle": "Net à payer",
      "compte_debit": null,
      "compte_credit": "421000",
      "compte_tiers": "SAL001",
      "debit": 0,
      "credit": 250000
    }
  ]
}
```

Champs exploités par le contrôleur d'écritures : `date_ecriture`, `reference_document`, `libelle`, `debit`, `credit`, `code_journal`, `compte_debit` ou `compte_credit`, `compte_tiers`, et les clés d'idempotence `cle_source` et/ou `operation_source`. Le lot accepte également `operation`, `atomique`, `exercice_debut` et `exercice_fin`.

Règles :
- envoyer l'opération complète dans un seul lot ;
- `atomique: true) pour la paie : si une ligne est refusée, tout le lot est annulé ;
- les débits et crédits doivent être équilibrés avant envoi ;
- le code journal et les comptes doivent exister ou être préalablement synchronisés ;
- `operation_source` doit être stable et identique sur les lignes de la même opération ;
- ne pas envoyer `company_id`, `comptaflow_company_id` ou un identifiant de dossier pour choisir le destinataire ;
- le destinataire est celui du jeton Bearer.

### 12.5 Dépôt d'une pièce justificative PDF

Route :

`POST /api/external/pieces-justificatives/deverser`

```json
{
  "operation_source": "PAIE-2026-11",
  "nom_fichier": "journal-paie-novembre-2026.pdf",
  "sha256": "64_caracteres_hexadecimaux_sha256",
  "contenu_base64": "JVBERi0xLjQK..."
}
```

Le PDF doit être valide, commencer par la signature PDF attendue, faire au maximum 10 Mo et correspondre au SHA-256 fourni. `operation_source` doit correspondre à une opération déjà reçue dans le dossier. Un dépôt identique est idempotent ; une autre pièce déjà attachée provoque un conflit.

### 12.6 Réponse de dépôt et erreurs

Les endpoints retournent un JSON avec `success`, un message et, selon l'opération, des compteurs ou détails. RH-FLOW doit conserver le code HTTP et le corps de réponse dans son historique. Les statuts 401/403 signalent un problème d'authentification/droit, 409 un conflit (notamment exercice), 422 un payload ou référentiel invalide, 429 une limitation de débit et 5xx une erreur serveur.

**Attention sur le périmètre de validation :** ces payloads sont dérivés des champs consommés par les contrôleurs COMPTAFLOW consultés. L'exemple de paie illustre les clés réellement lues, mais doit être confirmé avec un lot RH-FLOW réel et un test d'intégration avant mise en production. Les endpoints du parcours inverse de liaison décrits en section 4.3 restent à développer/valider côté COMPTAFLOW.
