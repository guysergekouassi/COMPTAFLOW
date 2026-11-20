# Cahier des charges général — Applications tierces ↔ COMPTAFLOW

**Version :** 1.0 — 20 novembre 2026  
**Destinataires :** éditeurs et développeurs d'applications externes non membres de la Suite FLOW.  
**Autorité d'intégration :** COMPTAFLOW.

> Ce document décrit le contrat d'intégration tiers. Une application externe ne bénéficie pas du parcours multi-dossiers des applications maison : COMPTAFLOW lui attribue un dossier dédié et décide de son périmètre.

## 1. Principes non négociables

- L'application externe utilise les endpoints COMPTAFLOW ; aucun accès SQL direct.
- Elle ne peut ni lister les entreprises COMPTAFLOW ni sélectionner une entreprise existante.
- Elle demande la création d'un dossier dédié ; le Super Admin COMPTAFLOW accepte ou rejette.
- Le dossier est créé et attribué côté COMPTAFLOW.
- Le destinataire de chaque dépôt est déterminé par le jeton Bearer authentifié côté serveur.
- Les payloads métier ne doivent pas contenir d'identifiant permettant de changer le dossier destinataire.
- Les permissions référentiel, écritures et pièces justificatives sont gérées côté COMPTAFLOW.
- Tous les échanges se font en HTTPS.

## 2. Enregistrement initial de l'application

Le Super Admin COMPTAFLOW enregistre l'application tierce :
- nom de l'application ;
- propriétaire/éditeur ;
- responsable ;
- e-mail de contact ;
- URL de l'application ;
- description et environnement ;
- permissions demandées/accordées.

COMPTAFLOW génère les éléments techniques :
- URL API COMPTAFLOW ;
- code d'application ;
- Client ID ;
- Client Secret ;
- permissions ;
- version du contrat ;
- durée/rotation du secret.

Le Client Secret est affiché une seule fois. L'application doit le stocker dans un coffre serveur ou un mécanisme de secrets, jamais dans le navigateur, le dépôt Git ou les journaux.

## 3. Demande de création d'entreprise dédiée

Une application externe ne choisit pas un dossier COMPTAFLOW existant. Elle soumet une demande de création.

### Endpoint

`POST /api/integration/external/company-requests`

La version actuellement implémentée accepte les identifiants applicatifs dans le corps JSON. En-têtes : `Accept: application/json`, `Content-Type: application/json`.

### Payload exact

```json
{
  "client_id": "cf_app_identifiant_fourni",
  "client_secret": "cf_secret_fourni",
  "request_id": "EXT-REQ-2026-000001",
  "company_name": "SOCIETE EXTERNE DEMO",
  "manager_name": "Nom du gérant",
  "manager_email": "gerant@example.test",
  "activity": "Commerce général",
  "legal_form": "SARL",
  "phone_number": "+2250102030405"
}
```

Champs obligatoires :
- `client_id` : identifiant de l'application enregistrée dans COMPTAFLOW ;
- `client_secret` : secret applicatif courant ;
- `request_id` : référence unique générée par l'application externe, stable lors des reprises ;
- `company_name` : raison sociale ;
- `manager_name` : nom complet du gérant ;
- `manager_email` : e-mail valide du gérant.

Champs facultatifs :
- `activity` ;
- `legal_form` ;
- `phone_number`.

Ne pas envoyer `company_id), `comptaflow_company_id`, `owner_id), un identifiant de dossier cible ou un mot de passe. COMPTAFLOW ne reçoit aucun mot de passe du gérant dans cette demande. Après validation, COMPTAFLOW crée le dossier, l'exercice et un mécanisme d'activation permettant au gérant de choisir lui-même son mot de passe.

### Réponse si la demande est mise en attente — HTTP 202

```json
{
  "success": true,
  "request_id": "EXT-REQ-2026-000001",
  "status": "pending",
  "message": "Demande reçue par COMPTAFLOW. Le dossier sera créé après validation."
}
```

Si la même application renvoie le même `request_id), COMPTAFLOW répond avec l'état existant sans créer un doublon. L'application doit donc réutiliser la même référence en cas de timeout ou de reprise.

Erreurs attendues :
- 401 : application inconnue, inactive ou secret invalide/expiré ;
- 409 : un dossier dédié existe déjà ou conflit de demande ;
- 422 : champs invalides ;
- 429 : trop de demandes.

## 4. Suivi de la demande

### Endpoint

`POST /api/integration/external/company-requests/{requestId}/status`

Payload :

```json
{
  "client_id": "cf_app_identifiant_fourni",
  "client_secret": "cf_secret_fourni"
}
```

Réponse si en attente :

```json
{
  "request_id": "EXT-REQ-2026-000001",
  "status": "pending",
  "company": null,
  "rejection_reason": null
}
```

Réponse après acceptation :

```json
{
  "request_id": "EXT-REQ-2026-000001",
  "status": "approved",
  "company": {
    "id": 168
  },
  "rejection_reason": null
}
```

Réponse après rejet :

```json
{
  "request_id": "EXT-REQ-2026-000001",
  "status": "rejected",
  "company": null,
  "rejection_reason": "Informations d'entreprise à compléter."
}
```

L'application ne peut consulter que ses propres demandes. Le `company.id` n'est communiqué qu'après acceptation ; il ne permet pas de choisir un autre dossier.

## 5. Obtention du jeton Bearer après création

Une fois le dossier dédié créé et lié, demander un jeton :

`POST /api/integration/token`

```json
{
  "client_id": "cf_app_identifiant_fourni",
  "client_secret": "cf_secret_fourni",
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
    "name": "SOCIETE EXTERNE DEMO"
  }
}
```

Le dossier doit être l'unique dossier dédié et actif de cette application externe. Le jeton expire après 3600 secondes. Ne jamais le journaliser. Pour les appels métier, utiliser `Authorization: Bearer <access_token>`.

## 6. Payload de référentiel

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
      "numero_de_tiers": "CLI001",
      "intitule": "CLIENT EXEMPLE",
      "compte_general": "411000",
      "type_de_tiers": "client",
      "numero_original": "CLI001",
      "email": "client@example.test",
      "telephone": "+225000000000",
      "adresse": "Abidjan"
    }
  ]
}
```

Champs :
- plan comptable : `numero_de_compte`, `intitule`, `numero_original` ;
- journal : `code_journal`, `intitule`, `type`, `compte_numero`, `numero_original` ;
- tiers : `numero_de_tiers`, `intitule`, `compte_general`, `type_de_tiers`, `numero_original`, plus champs complémentaires éventuels.

COMPTAFLOW conserve les numéros source comme références d'origine et applique ses règles internes de numérotation. L'application tierce ne doit pas envoyer de numéro de dossier ou de société pour router l'import.

## 7. Payload d'écritures comptables

Route :

`POST /api/external/ecritures/deverser`

```json
{
  "operation": "EXT-VENTE-2026-000125",
  "atomique": true,
  "exercice_debut": "2026-01-01",
  "exercice_fin": "2026-12-31",
  "ecritures": [
    {
      "cle_source": "EXT-VENTE-2026-000125-L1",
      "operation_source": "EXT-VENTE-2026-000125",
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
      "cle_source": "EXT-VENTE-2026-000125-L2",
      "operation_source": "EXT-VENTE-2026-000125",
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
      "cle_source": "EXT-VENTE-2026-000125-L3",
      "operation_source": "EXT-VENTE-2026-000125",
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

L'enveloppe accepte `ecritures` (obligatoire), `atomique` (booléen facultatif), `operation` (facultatif), `exercice_debut` et `exercice_fin`.

Chaque ligne utilise les champs `date_ecriture`, `reference_document`, `libelle`, `debit`, `credit`, `code_journal`, `compte_debit` ou `compte_credit`, `compte_tiers`, `cle_source` et/ou `operation_source`.

Règles :
- référentiel synchronisé avant les écritures ;
- débit = crédit pour chaque opération ;
- `atomique: true` recommandé : tout le lot est annulé si une ligne est refusée ;
- clés source stables et uniques pour éviter les doublons ;
- exercice source cohérent avec l'exercice COMPTAFLOW ;
- aucun identifiant de société/dossier dans le payload métier ;
- le dossier cible est celui du jeton Bearer.

## 8. Pièces justificatives PDF

Route :

`POST /api/external/pieces-justificatives/deverser`

```json
{
  "operation_source": "EXT-VENTE-2026-000125",
  "nom_fichier": "FAC-2026-000125.pdf",
  "sha256": "64_caracteres_hexadecimaux_sha256",
  "contenu_base64": "JVBERi0xLjQK..."
}
```

Le PDF doit être valide, inférieur ou égal à 10 Mo, son empreinte SHA-256 doit correspondre et `operation_source` doit désigner une opération déjà enregistrée dans ce dossier. Un dépôt identique est idempotent.

## 9. Permissions et périmètre

Le Super Admin attribue séparément les droits :
- `referentiel` ;
- `ecritures` ;
- `pieces_justificatives`.

L'application ne doit appeler que les familles qui lui sont accordées. Toute tentative hors permission est refusée. La révocation du Client Secret, de l'application ou de la liaison doit bloquer les appels suivants.

## 10. Traçabilité, reprise et sécurité

Pour chaque appel, conserver côté application :
- utilisateur ou processus à l'origine ;
- identifiant de demande/opération ;
- dossier dédié ;
- date/heure ;
- endpoint et méthode ;
- identifiant de requête ;
- nombre de lignes et totaux ;
- code HTTP, réponse et message.

Ne jamais journaliser Client Secret, jeton Bearer, mot de passe ou en-tête Authorization.

Gestion des erreurs :
- 401 : credentials ou token invalide/expiré ;
- 403 : application ou permission révoquée ;
- 409 : dossier déjà créé, exercice en conflit ou opération conflictuelle ;
- 422 : payload invalide ;
- 429 : respecter la limitation ;
- timeout/5xx : consulter l'état ou vérifier l'idempotence avant toute reprise.

## 11. Tests de réception

1. Une application tierce inactive ou avec mauvais secret est refusée.
2. Une demande avec les mêmes `request_id` ne crée jamais deux dossiers.
3. Une demande n'accepte aucun `company_id) fourni par l'appelant.
4. Le Super Admin peut accepter/rejeter et les changements d'état sont traçables.
5. Le dossier dédié est unique et le jeton est limité à ce dossier.
6. Aucune API ne permet de lister les autres entreprises COMPTAFLOW.
7. Les permissions séparent référentiel, écritures et pièces.
8. Le référentiel et les écritures respectent les payloads ci-dessus.
9. Une clé source répétée ne duplique pas les écritures.
10. Un PDF non valide, trop volumineux ou de hash incorrect est rejeté.
11. Les événements sont retraçables sans secrets en clair.
12. La révocation bloque immédiatement les appels ultérieurs.
