# Cahier des charges général — Applications maison de la Suite FLOW ↔ COMPTAFLOW

**Version :** 1.0 — 20 novembre 2026  
**Destinataires :** équipes de développement des applications maison (RH-FLOW, SELFLOW et futures applications Suite FLOW).  
**Référence centrale :** COMPTAFLOW.

> Ce document définit le contrat commun à respecter par toute application maison. Les routes explicitement signalées « à développer/valider » sont des exigences d'interface et ne doivent pas être présentées comme déjà disponibles.

## 1. Objectif et principes

Chaque application maison doit pouvoir :
- établir une connexion applicative avec COMPTAFLOW au moyen d'un code généré par COMPTAFLOW ;
- associer ses propres entreprises aux dossiers COMPTAFLOW ;
- initier cette association depuis son interface ou la laisser être initiée depuis COMPTAFLOW ;
- synchroniser les référentiels autorisés ;
- transmettre des écritures et pièces justificatives selon les payloads définis ;
- consulter le résultat et tracer chaque opération.

Connexion applicative, association des entreprises et déversement comptable sont trois opérations distinctes. La création ou le renouvellement d'une liaison ne déclenche jamais un déversement.

## 2. Établissement de la connexion

Dans COMPTAFLOW, le Super Admin enregistre l'application maison : code applicatif, nom, URL partenaire, propriétaire et statut. COMPTAFLOW génère un code aléatoire et choisit sa durée parmi les options proposées dans son interface (3 mois, 6 mois, 1 an ou 2 ans selon la configuration actuelle).

L'administrateur copie le code et le colle dans l'écran de configuration de l'application maison. Cette dernière échange le code contre les identifiants applicatifs.

### Échange du code

`POST /api/integration/pairing/redeem`

```json
{
  "pairing_code": "CODE_COPIE_DEPUIS_COMPTAFLOW",
  "application_code": "rh-flow"
}
```

Réponse de succès :

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

Le code de liaison est à usage unique. Le Client Secret est affiché une seule fois, puis stocké chiffré dans l'application maison. Il ne doit jamais être placé dans le navigateur, le dépôt Git, un fichier de log ou un payload métier. La rotation du code de liaison et celle du Client Secret sont deux opérations différentes.

## 3. Association des entreprises dans les deux sens

Les deux parcours doivent être disponibles et créer la même relation centrale.

### 3.1 Parcours initié depuis COMPTAFLOW

1. Choisir un dossier COMPTAFLOW.
2. COMPTAFLOW appelle le catalogue de l'application maison.
3. Afficher les entreprises maison disponibles.
4. Choisir l'entreprise maison correspondante.
5. COMPTAFLOW revalide l'identifiant auprès de l'application et enregistre la correspondance.
6. L'application maison synchronise l'état de la liaison.

Catalogue partenaire attendu :

`GET /api/integration/companies`

En-tête :

`X-Integration-Pairing-Code: <code_fourni_par_comptaflow>`

Réponse :

```json
{
  "data": [
    {
      "id": "rh_company_104",
      "name": "3D PRESTA"
    }
  ]
}
```

Cette route doit retourner uniquement les entreprises actives auxquelles l'application authentifiée a droit. Ne jamais exposer des données de paie, mots de passe ou informations personnelles inutiles.

### 3.2 Parcours initié depuis l'application maison

1. L'administrateur choisit son entreprise dans RH-FLOW/SELOW.
2. L'application demande à COMPTAFLOW les dossiers autorisés pour cette application.
3. COMPTAFLOW retourne uniquement les dossiers explicitement autorisés par le Super Admin ; jamais la liste globale des clients.
4. L'administrateur choisit le dossier COMPTAFLOW.
5. L'application envoie une demande de liaison.
6. COMPTAFLOW vérifie l'application, l'entreprise source, le dossier cible, les autorisations et les doublons.
7. COMPTAFLOW crée la liaison centrale et retourne son identifiant et son statut.
8. L'application maison enregistre la même référence.

Les endpoints COMPTAFLOW du parcours inverse ci-dessous sont un contrat à développer/valider si absents du serveur :

**Catalogue des dossiers COMPTAFLOW autorisés**

`POST /api/integration/companies/catalog`

```json
{
  "client_id": "cf_app_xxxxxxxxx",
  "client_secret": "cf_secret_xxxxxxxxx",
  "partner_company_id": "rh_company_104"
}
```

Réponse :

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

**Demande de liaison**

`POST /api/integration/companies/link`

```json
{
  "client_id": "cf_app_xxxxxxxxx",
  "client_secret": "cf_secret_xxxxxxxxx",
  "partner_company_id": "rh_company_104",
  "company_id": 168
}
```

Réponse :

```json
{
  "success": true,
  "link": {
    "id": "link_opaque_id",
    "partner_company_id": "rh_company_104",
    "company_id": 168,
    "status": "active"
  }
}
```

Ces deux exemples documentent le contrat fonctionnel proposé, pas une route déjà certifiée. Le secret doit transiter sur HTTPS et rester côté serveur. Préférer une authentification Bearer courte pour ces routes, après émission d'un jeton limité au dossier ou au périmètre de liaison.

### 3.3 Règles d'unicité et de sécurité

- Les identifiants stables sont les clés de correspondance ; les noms ne servent qu'à l'affichage.
- Une liaison est enregistrée une seule fois et doit être identique dans les deux applications.
- Un dossier COMPTAFLOW ne peut être proposé que s'il est autorisé pour l'application.
- Aucun identifiant envoyé par le navigateur ne suffit à autoriser une liaison : tout est revalidé côté serveur.
- Une application maison peut être liée à plusieurs dossiers, mais chaque jeton Bearer doit être limité à un seul dossier autorisé.
- La révocation bloque les nouveaux déversements.
- Une indisponibilité réseau après demande de liaison impose de relire l'état avant toute nouvelle création.

## 4. Obtenir le jeton d'accès métier

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

Le dossier demandé doit déjà être lié à l'application. Le jeton est temporaire (3600 secondes) et lié à ce seul dossier. Le Client Secret ne doit plus être envoyé après l'obtention du jeton.

Tous les appels métier utilisent :
- `Authorization: Bearer <access_token>`
- `Accept: application/json`
- `Content-Type: application/json`

## 5. Payload de synchronisation du référentiel

Route :

`POST /api/external/referentiel/deverser`

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

Champs lus par le mapping COMPTAFLOW :
- `plan_comptable[]` : `numero_de_compte`, `intitule`, `numero_original`.
- `codes_journaux[]` : `code_journal`, `intitule`, `type`, `compte_numero`, `numero_original`.
- `tiers[]` : `numero_de_tiers`, `intitule`, `compte_general`, `type_de_tiers`, `numero_original`. Les champs complémentaires du tiers peuvent renseigner sa fiche.

Les tableaux sont facultatifs, mais doivent être des tableaux lorsqu'ils sont fournis. Transmettre le référentiel avant les écritures qui l'utilisent. COMPTAFLOW normalise les numéros et conserve le numéro source ; ne pas inventer un deuxième champ de routage.

## 6. Payload de déversement des écritures

Route :

`POST /api/external/ecritures/deverser`

```json
{
  "operation": "VENTE-2026-000125",
  "atomique": true,
  "exercice_debut": "2026-01-01",
  "exercice_fin": "2026-12-31",
  "ecritures": [
    {
      "cle_source": "VENTE-2026-000125-L1",
      "operation_source": "VENTE-2026-000125",
      "date_ecriture": "2026-11-20",
      "reference_document": "FAC-2026-000125",
      "code_journal": "VTE",
      "libelle": "Vente marchandises",
      "compte_debit": "411000",
      "compte_credit": null,
      "compte_tiers": "CLI001",
      "debit": 118000,
      "credit": 0
    },
    {
      "cle_source": "VENTE-2026-000125-L2",
      "operation_source": "VENTE-2026-000125",
      "date_ecriture": "2026-11-20",
      "reference_document": "FAC-2026-000125",
      "code_journal": "VTE",
      "libelle": "Vente marchandises",
      "compte_debit": null,
      "compte_credit": "701000",
      "compte_tiers": null,
      "debit": 0,
      "credit": 100000
    },
    {
      "cle_source": "VENTE-2026-000125-L3",
      "operation_source": "VENTE-2026-000125",
      "date_ecriture": "2026-11-20",
      "reference_document": "FAC-2026-000125",
      "code_journal": "VTE",
      "libelle": "TVA collectée",
      "compte_debit": null,
      "compte_credit": "443000",
      "compte_tiers": null,
      "debit": 0,
      "credit": 18000
    }
  ]
}
```

Champs exploités par le contrôleur :
- enveloppe : `ecritures` obligatoire, `atomique` booléen facultatif, `operation` facultatif ;
- période : `exercice_debut` et `exercice_fin` sont comparées à l'exercice COMPTAFLOW ;
- ligne : `date_ecriture`, `reference_document`, `libelle`, `debit`, `credit`, `code_journal`, `compte_debit` ou `compte_credit`, `compte_tiers`, `cle_source` et/ou `operation_source`.

Règles :
- un numéro de journal inconnu provoque un refus ; synchroniser le référentiel avant les écritures ;
- les comptes sont identifiés par leur numéro source et doivent correspondre au référentiel ;
- le tiers reste facultatif mais doit être transmis lorsqu'il existe ;
- `cle_source` doit être stable et unique par ligne pour l'idempotence ;
- `operation_source` doit identifier la même opération sur toutes ses lignes ;
- `atomique: true` annule tout le lot si une ligne est refusée ;
- vérifier l'équilibre débit/crédit avant envoi ;
- ne jamais transmettre `company_id`, `comptaflow_company_id` ou `selflow_company_id` pour choisir le dossier. Le jeton détermine le destinataire.

## 7. Payload de pièce justificative PDF

Route :

`POST /api/external/pieces-justificatives/deverser`

```json
{
  "operation_source": "VENTE-2026-000125",
  "nom_fichier": "FAC-2026-000125.pdf",
  "sha256": "64_caracteres_hexadecimaux_sha256",
  "contenu_base64": "JVBERi0xLjQK..."
}
```

Le PDF doit être valide, inférieur ou égal à 10 Mo et correspondre à l'empreinte SHA-256. L'opération doit déjà exister dans le dossier lié. Un dépôt identique est idempotent ; un autre PDF déjà attaché entraîne un conflit.

## 8. Bouton Déverser et mode de transfert

Chaque application maison affiche un bouton **Déverser vers COMPTAFLOW** dans le contexte du dossier et de la période/opération.

Avant envoi, présenter :
- entreprise source et dossier COMPTAFLOW cible ;
- période et référence d'opération ;
- nombre de lignes ;
- total débit et crédit ;
- éventuelles pièces jointes.

Le mode est **manuel par défaut**. L'utilisateur confirme. Aucun transfert automatique à la création d'une liaison ou à la validation de la connexion. Si un mode automatique est ajouté ultérieurement, il doit être explicitement activé dans les paramètres et journalisé.

## 9. Traçabilité et erreurs

Conserver : utilisateur déclencheur, application, entreprise source, dossier cible, opération, période, date/heure, route, méthode, identifiant de requête, code HTTP, nombre de lignes, totaux, résultat et message d'erreur.

Ne jamais journaliser code de liaison, Client Secret, jeton Bearer ou mot de passe.

Gérer au minimum :
- 401 : identifiants/jeton invalides ou expirés ;
- 403 : permission ou liaison révoquée ;
- 409 : conflit d'exercice ou d'association ;
- 422 : payload invalide, journal inconnu ou ligne refusée ;
- 429 : limitation de débit ;
- 5xx/timeout : résultat incertain, vérifier l'idempotence avant renvoi.

## 10. Tests de réception

1. Le code de liaison valide est accepté et un code utilisé/expiré/révoqué est refusé.
2. Le catalogue source ne divulgue que les entreprises autorisées.
3. Les deux parcours de liaison (COMPTAFLOW vers l'application et application vers COMPTAFLOW) créent exactement la même association.
4. Un dossier non autorisé n'apparaît pas dans le catalogue.
5. Un jeton d'un dossier ne permet pas d'écrire dans un autre.
6. Le référentiel est importé avant les écritures.
7. Une opération équilibrée est reçue dans le bon dossier.
8. Un lot atomique avec une ligne invalide est annulé en entier.
9. Une répétition de la même clé source ne duplique pas les écritures.
10. Le PDF est contrôlé, attaché à l'opération et non accessible depuis un autre dossier.
11. La révocation bloque les appels suivants.
12. L'historique permet de retrouver qui, quoi, quand, où et le résultat.

## 11. Adaptation par application

Le présent document est le contrat commun. Chaque application ajoute uniquement ses données métier propres (paie pour RH-FLOW, ventes/achats/stock pour SELFLOW, etc.) en respectant le format commun des référentiels, écritures et pièces justificatives. Aucun connecteur ne doit inventer son propre format comptable sans versionner et faire valider un nouveau contrat.
