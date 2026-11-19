# Contrat d'authentification des applications COMPTA-APK

Version initiale — 19 novembre 2026

## 1. Enregistrement

Le Super Admin enregistre l'application dans COMPTAFLOW. Le système génère :
- un `client_id` public ;
- un `client_secret` aléatoire affiché une seule fois ;
- une date d'expiration du secret (12 mois par défaut).

COMPTAFLOW conserve uniquement le haché du secret. Le secret ne doit jamais être inclus dans un payload métier, un dépôt Git ou un journal.

## 2. Obtention d'un jeton

Endpoint : `POST /api/integration/token`

Exemple de corps JSON :
```json
{
  "client_id": "IDENTIFIANT_FOURNI_PAR_COMPTAFLOW",
  "client_secret": "SECRET_FOURNI_UNE_SEULE_FOIS",
  "company_id": 123
}
```

Pour une application maison liée à plusieurs dossiers, `company_id` est obligatoire à cette étape et doit correspondre à une liaison active déjà autorisée dans COMPTAFLOW. Pour une application externe, une seule liaison active est permise ; le champ peut être omis. Il ne s'agit pas d'un identifiant de routage dans les payloads métier.

Réponse : jeton opaque `Bearer`, durée de vie d'une heure et dossier autorisé associé. Le jeton en clair n'est jamais stocké ; seul son SHA-256 est conservé.

## 3. Appels métier authentifiés

Chaque endpoint de synchronisation protégé accepte :
- `Authorization: Bearer <access_token>`
- `X-Request-Timestamp: <timestamp Unix en secondes>`
- `X-Request-Nonce: <valeur aléatoire unique de 16 à 128 caractères>`
- `Content-Type: application/json` (ou multipart/form-data pour les pièces PDF).

L'horodatage doit se trouver dans une fenêtre de cinq minutes. Le nonce ne peut être utilisé qu'une fois par application. Un rejeu retourne HTTP 409 ; un horodatage absent ou hors fenêtre retourne HTTP 401.

Familles de permissions contrôlées par liaison :
- `referentiel` : plan comptable, tiers, codes journaux ;
- `ecritures` : déversements et propagation des comptes ;
- `pieces_justificatives` : dépôt et consultation des pièces autorisées.

## 4. Résolution du dossier

Le dossier destinataire provient du jeton et de la liaison active en base. Un `company_id` dans le payload métier ne peut jamais changer le dossier cible ; une valeur contradictoire est refusée. Une application externe doit avoir exactement un dossier actif lié.

Les endpoints existants restent l'unique porte d'entrée : aucun accès SQL direct depuis les applications clientes.

## 5. Compatibilité

Le middleware centralisé accepte encore l'ancien `X-Company-Key` via le middleware historique pour les connecteurs déjà déployés. Cette compatibilité est transitoire. Ne pas supprimer les anciennes clés avant migration et validation de tous les clients actifs.

Les routes de provisionnement initial restent distinctes : elles ne sont pas remplacées par le jeton de déversement.

## 6. Rotation et révocation

La rotation du secret :
- affiche le nouveau secret une seule fois ;
- renouvelle l'expiration ;
- révoque immédiatement les jetons déjà émis.

La révocation de l'application ou d'une liaison révoque également les jetons concernés. Les dépôts déjà enregistrés restent conservés.

## 7. Limites de la livraison serveur

Le serveur COMPTAFLOW fournit le registre, les liaisons, l'émission/validation Bearer et les contrôles anti-rejeu. Le paramétrage client SELFLOW pour obtenir et renouveler automatiquement ces jetons doit être déployé séparément avant de basculer ses appels en Bearer. Jusqu'à cette migration, le connecteur SELFLOW reste compatible avec `X-Company-Key`.
