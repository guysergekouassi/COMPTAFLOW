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

## 4. Association des entreprises : correspondance à conserver

Dans COMPTAFLOW, l'administrateur :
1. sélectionne un dossier COMPTAFLOW ;
2. COMPTAFLOW appelle le catalogue RH-FLOW ;
3. une fenêtre affiche les entreprises retournées ;
4. l'administrateur choisit l'entreprise RH-FLOW ;
5. COMPTAFLOW vérifie à nouveau l'existence de l'identifiant choisi avant d'enregistrer l'association.

RH-FLOW doit conserver une correspondance fiable entre :
- son identifiant d'entreprise source ;
- l'identifiant du dossier COMPTAFLOW associé, communiqué après la confirmation de la liaison ;
- l'état de la liaison.

Cette correspondance doit être unique et ne doit pas être déduite du nom de l'entreprise. Les noms peuvent changer ; les identifiants source doivent rester stables.

Une entreprise RH-FLOW ne doit pas être associée par erreur à deux dossiers COMPTAFLOW différents pour la même intégration. Si la liaison est révoquée, RH-FLOW doit empêcher tout nouveau déversement jusqu'à réassociation valide.

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
