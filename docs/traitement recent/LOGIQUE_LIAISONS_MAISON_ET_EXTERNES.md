# Logique simplifiée des liaisons COMPTAFLOW — Applications maison et externes

**Date :** 20 novembre 2026  
**Statut :** spécification fonctionnelle à appliquer  
**Périmètre :** console Super Admin COMPTAFLOW, échanges avec RH-FLOW / Suite FLOW et applications tierces.

> Ce document décrit la logique cible demandée. Il ne signifie pas que tous les écrans, endpoints ou échanges décrits sont déjà développés ou activés.

---

## 1. Principe général : séparer connexion et liaison des dossiers

La page COMPTA-APK doit être simplifiée. Elle ne doit pas obliger l'administrateur à gérer des paramètres techniques inutiles au quotidien.

Le parcours doit être divisé en deux opérations indépendantes :

1. **Établir la connexion entre deux applications** : les applications se reconnaissent et disposent d'un identifiant/code de liaison.
2. **Associer les dossiers/entreprises** : après connexion, l'utilisateur choisit le dossier COMPTAFLOW et le dossier de l'application partenaire à faire correspondre pour les transferts.

Une connexion applicative ne signifie pas que tous les dossiers des deux applications sont automatiquement associés. Chaque association de dossiers est explicite, révocable et contrôlée côté serveur.

Le bouton **Déverser** déclenche le transfert métier. La simple création d'une liaison ne doit jamais envoyer des écritures.

## 2. Applications maison — Suite FLOW (RH-FLOW, SELFLOW, etc.)

### 2.1 Établir la connexion entre applications

Dans COMPTAFLOW, afficher un formulaire court :

- Nom de l'application maison.
- URL de base de l'application partenaire.
- Éventuellement une description ou un environnement (production/test).

Après validation, COMPTAFLOW :

1. Vérifie que l'application est autorisée comme application maison.
2. Génère un code/identifiant de liaison unique, propre à cette connexion.
3. Affiche le code une seule fois avec un bouton **Copier**.
4. Enregistre l'application, son URL, son statut et les dates de création/renouvellement.
5. Permet à l'administrateur de copier ce code et de le coller dans l'écran de configuration de l'application partenaire.

L'application partenaire doit pouvoir valider le code auprès de COMPTAFLOW. Le code ne doit pas être un simple identifiant prévisible : il doit être aléatoire, suffisamment robuste, stocké sous forme de hash côté serveur lorsque c'est un secret, et transmis exclusivement sur HTTPS.

### 2.2 Renouvellement du code de liaison

Prévoir deux modes :

- **Renouvellement manuel** : bouton « Renouveler le code », avec confirmation et journalisation.
- **Renouvellement automatique** : expiration configurable et possibilité de renouveler avant échéance selon une procédure authentifiée entre les applications.

Le renouvellement doit révoquer l'ancien secret/code après une courte période de transition contrôlée, ou immédiatement si l'administrateur le demande. Ne jamais laisser deux codes actifs sans échéance ni raison. Les liaisons de dossiers existantes ne doivent pas être supprimées par le simple renouvellement d'un secret ; elles restent soumises à une réauthentification si nécessaire.

### 2.3 Associer les dossiers après connexion

Une fois l'application connectée, la fiche de cette application affiche une section **Liaison des dossiers** :

1. Une première liste déroulante affiche les entreprises/dossiers COMPTAFLOW accessibles à l'administrateur.
2. Lorsque l'utilisateur sélectionne un dossier COMPTAFLOW, une fenêtre (modal) s'ouvre.
3. Cette fenêtre charge, depuis l'API authentifiée de l'application partenaire, la liste des entreprises/dossiers de cette application.
4. L'utilisateur choisit le dossier partenaire correspondant.
5. Il confirme l'association.

Exemple de correspondance :

| COMPTAFLOW | RH-FLOW |
|---|---|
| Dossier COMPTAFLOW A | Entreprise RH-FLOW A |
| Dossier COMPTAFLOW B | Entreprise RH-FLOW B |

La liste RH-FLOW ne doit apparaître qu'après sélection du dossier COMPTAFLOW et appel réussi à l'API partenaire. Afficher les états de chargement, absence de résultat et erreur de connexion.

La liaison enregistrée doit contenir au minimum les identifiants internes des deux dossiers, l'application partenaire, le statut, la date de création, l'auteur et les éventuelles permissions de transfert.

**Sécurité et périmètre :**
- L'API partenaire ne renvoie que les entreprises accessibles à l'application authentifiée.
- COMPTAFLOW vérifie que le dossier local appartient au périmètre de l'administrateur.
- L'association est unique selon les règles métier définies (pas de doublon actif involontaire).
- Les identifiants transmis par le navigateur ne suffisent jamais à autoriser un accès : le serveur revalide chaque relation.
- Une application maison peut avoir plusieurs associations de dossiers, mais un transfert doit toujours cibler une association précise.

### 2.4 Déversement des données

Après association des dossiers, afficher un bouton **Déverser** sur la fiche de liaison ou dans l'écran métier concerné.

Le clic :
1. Identifie l'association de dossiers sélectionnée.
2. Vérifie que la liaison applicative et la liaison des dossiers sont actives.
3. Vérifie les droits nécessaires (référentiel, écritures, pièces justificatives, selon l'opération).
4. Prépare le payload conforme au contrat COMPTAFLOW.
5. Envoie la requête vers l'endpoint prévu.
6. Affiche le résultat : accepté, rejeté avec motif, ou échec technique.
7. Enregistre une trace complète de l'opération.

Aucun déversement comptable ne doit être déclenché simplement parce que la connexion ou l'association des dossiers vient d'être créée. Le mode normal demandé est un déversement déclenché explicitement par le bouton.

---

## 3. Applications externes — intégration tierce

Les applications qui ne font pas partie de la Suite FLOW suivent un parcours différent. Elles ne doivent pas pouvoir choisir librement un dossier COMPTAFLOW existant.

### 3.1 Création et configuration d'une application externe

Dans COMPTAFLOW, l'administrateur enregistre l'application externe avec :

- Nom de l'application.
- Nom de l'organisation/éditeur propriétaire.
- Nom du responsable ou gérant.
- Adresse e-mail du responsable.
- Description et coordonnées utiles.
- URL de l'application et environnement, si requis par le contrat d'intégration.

COMPTAFLOW génère les éléments techniques de connexion nécessaires :

- URL de l'API COMPTAFLOW à utiliser.
- Identifiant/code de liaison de l'application.
- Identifiant unique du dossier COMPTAFLOW dédié à cette application/entreprise.
- Identifiants API (Client ID et secret ou mécanisme équivalent) et procédure d'obtention d'un jeton Bearer.
- Version du contrat de payload et liste des opérations autorisées.

Les secrets ne sont montrés qu'au moment de leur génération ou rotation. COMPTAFLOW ne doit pas stocker un secret en clair ni le réafficher ultérieurement.

### 3.2 Demande de création d'entreprise envoyée par l'application externe

L'application externe ne demande pas à se rattacher à une entreprise COMPTAFLOW déjà existante. Elle soumet une **demande de création**.

La demande doit contenir les informations métier nécessaires, au minimum :

- Nom/raison sociale de l'entreprise.
- Nom du gérant/responsable.
- Adresse e-mail du responsable.
- Les autres champs obligatoires du formulaire de création d'entreprise COMPTAFLOW, selon le schéma officiel.

Le mot de passe initial ne doit pas être une valeur fixe partagée entre les intégrations. COMPTAFLOW doit générer un mot de passe temporaire aléatoire ou un lien d'activation à durée limitée, le communiquer de manière sécurisée au destinataire autorisé et imposer son changement à la première connexion. Ne jamais écrire ce mot de passe dans les journaux d'API.

La demande reçue passe par une validation côté COMPTAFLOW :
1. Authentifier l'application émettrice.
2. Vérifier que son identifiant/code de liaison est valide, actif et non expiré.
3. Vérifier que l'identifiant de dossier fourni correspond au dossier dédié attribué à cette application, ou qu'il s'agit d'une demande de création sans dossier préexistant.
4. Valider les champs obligatoires et les formats (nom, e-mail, responsable, etc.).
5. Vérifier les autorisations de l'application et la version du payload.
6. Détecter les doublons et les demandes répétées.
7. Enregistrer la demande avec son statut (reçue, à vérifier, acceptée, rejetée).
8. Après acceptation, créer le dossier COMPTAFLOW dédié et retourner l'identifiant officiel généré par COMPTAFLOW.

L'application externe reçoit ensuite les valeurs de configuration qui lui sont destinées : URL API, identifiant de liaison, identifiant de dossier attribué, Client ID et procédure de jeton. Elle ne reçoit jamais la liste globale des entreprises COMPTAFLOW.

### 3.3 Contrôle strict des payloads

Avant toute écriture dans COMPTAFLOW, chaque requête doit être contrôlée :

- Application authentifiée et active.
- Code/identifiant de liaison valide.
- Dossier cible attribué à cette application et autorisé.
- Opération permise par les habilitations.
- Payload conforme au schéma attendu et à sa version.
- Champs obligatoires présents et typés correctement.
- Références comptables (comptes, tiers, journaux) valides dans le dossier ciblé.
- Montants, dates, sens débit/crédit et pièces justificatives conformes aux règles métier.
- Identifiant d'idempotence pour empêcher le double traitement d'un même dépôt.
- Protection contre le rejeu, limitation de débit et taille maximale des requêtes/fichiers.

Un champ `company_id` transmis par le client ne doit jamais permettre de sélectionner librement une autre entreprise. Le dossier cible est résolu côté serveur à partir de l'application authentifiée et de son attribution active. Si l'identifiant fourni est conservé dans le contrat pour contrôle, il doit être comparé à l'identifiant attribué et toute différence doit provoquer un rejet.

Les endpoints et payloads doivent respecter les formats COMPTAFLOW existants pour le plan comptable, les tiers, les codes journaux, les écritures et les pièces justificatives. L'application externe ne doit pas écrire directement dans les tables COMPTAFLOW.

---

## 4. Traçabilité détaillée dans COMPTAFLOW

La page doit présenter un tableau d'historique lisible, filtrable et consultable. Les captures fournies montrent déjà une table avec Date, Application, Dossier, Événement, Route, HTTP et IP ; cette base doit être enrichie pour répondre au besoin de traçabilité détaillée.

### 4.1 Colonnes attendues

| Colonne | Contenu |
|---|---|
| Date et heure | Horodatage précis avec fuseau clairement défini |
| Application | Nom et identifiant de l'application appelante |
| Propriétaire | Organisation ou éditeur responsable |
| Dossier | Dossier COMPTAFLOW ciblé et, si maison, dossier partenaire associé |
| Qui | Utilisateur ou compte technique à l'origine de l'action |
| Quoi | Nature de l'action : demande, création, liaison, test, déversement, rotation, révocation, rejet |
| D'où | Application, URL/origine et adresse IP source |
| Méthode HTTP | GET, POST, PUT, PATCH, DELETE |
| Route / endpoint | Chemin exact appelé |
| Payload envoyé | Corps reçu, consultable dans un panneau de détail |
| Réponse | Résultat métier et corps de réponse expurgé des secrets |
| HTTP | Code HTTP retourné |
| Statut | Accepté, rejeté, en attente, erreur technique |
| Durée | Temps de traitement en millisecondes |
| Corrélation | Identifiant de requête / trace |
| Idempotence | Clé ou référence de dépôt permettant de retrouver une répétition |

Le tableau principal peut rester compact (date, application, dossier, qui, action, méthode, route, HTTP, statut, IP). Un clic sur une ligne ouvre un panneau de détail avec le payload et la réponse.

### 4.2 Protection des informations sensibles

La traçabilité doit être complète sans devenir une fuite de secrets :

- Ne jamais journaliser les mots de passe, Client Secret, jetons Bearer, clés privées ou en-têtes d'authentification.
- Masquer les champs sensibles des payloads et réponses.
- Conserver les payloads métier utiles à l'audit avec une politique de rétention définie et un accès réservé.
- Limiter l'accès aux journaux aux Super Admins et aux rôles explicitement autorisés.
- Journaliser également les consultations/exportations des journaux sensibles.
- Prévoir une politique de purge/archivage conforme aux obligations de conservation.

---

## 5. Interface COMPTAFLOW cible : simple et en deux parties

La page **Liaison COMPTA-APK** doit être organisée sans multiplier les formulaires techniques.

### Partie A — Connexions applicatives

Deux sections ou onglets :
- **Applications maison**
- **Applications externes**

Pour une application maison : formulaire URL + application, génération du code, bouton Copier, état de connexion, renouvellement manuel/automatique.

Pour une application externe : formulaire d'enregistrement du partenaire, génération des identifiants, URL API, identifiant de liaison, identifiant du dossier dédié et état de validation.

### Partie B — Liaisons des dossiers

Visible pour les applications maison déjà connectées :
- Liste déroulante des dossiers COMPTAFLOW.
- Après sélection, ouverture d'une fenêtre listant les dossiers de l'application partenaire.
- Confirmation de l'association.
- Liste des associations existantes avec état actif/révoqué.
- Bouton **Déverser** pour l'opération métier, avec retour de résultat.

Pour les applications externes, la partie correspondante est la demande de création de dossier dédié et son suivi ; pas de rattachement à un dossier existant.

### États visuels indispensables

- Non configurée.
- Code généré / à copier.
- En attente de validation partenaire.
- Connectée.
- Connexion expirée.
- Code révoqué.
- Dossiers non associés.
- Dossiers associés.
- Déversement en cours.
- Déversement accepté.
- Déversement rejeté (motif visible).

---

## 6. Règles de gestion non négociables

1. Une liaison d'application et une liaison de dossiers sont deux objets distincts.
2. Les applications maison peuvent associer plusieurs dossiers, mais chaque déversement est limité à une association explicitement sélectionnée.
3. La liste des dossiers partenaires est récupérée par API après sélection du dossier COMPTAFLOW ; elle n'est pas saisie librement.
4. Une application externe ne peut jamais parcourir ou sélectionner la liste globale des entreprises COMPTAFLOW.
5. Une application externe reçoit un dossier dédié généré/attribué par COMPTAFLOW après validation de sa demande de création.
6. Aucun payload n'est accepté sans authentification, autorisation, validation du dossier et validation du schéma.
7. Aucun déversement comptable automatique ne découle de la seule création d'une liaison ; le bouton Déverser déclenche le transfert.
8. Les opérations doivent être idempotentes et traçables.
9. Les secrets sont générés de manière robuste, stockés sous forme non réversible lorsqu'il s'agit de secrets de vérification, renouvelables et révocables.
10. Les intégrations historiques (notamment SELFLOW et GESTION-DE-SITES) doivent rester compatibles pendant une migration contrôlée ; aucune ancienne clé ne doit être supprimée sans vérifier les clients encore actifs.

---

## 7. Parcours de validation avant mise en production

### Applications maison
- [ ] Générer le code de liaison depuis COMPTAFLOW.
- [ ] Copier/coller le code dans RH-FLOW.
- [ ] Vérifier l'authentification mutuelle.
- [ ] Sélectionner un dossier COMPTAFLOW.
- [ ] Charger la liste des dossiers RH-FLOW dans la fenêtre.
- [ ] Associer les deux dossiers et vérifier la persistance.
- [ ] Vérifier qu'un dossier non autorisé ne peut pas être listé ou ciblé.
- [ ] Déverser un jeu d'essai via le bouton.
- [ ] Vérifier le résultat, l'idempotence et le journal détaillé.
- [ ] Tester expiration, renouvellement et révocation du code.

### Applications externes
- [ ] Enregistrer un partenaire externe.
- [ ] Générer URL API, code de liaison et identifiants.
- [ ] Soumettre une demande de création d'entreprise.
- [ ] Vérifier les contrôles des champs et la validation Super Admin.
- [ ] Vérifier la création d'un dossier dédié et l'attribution de son identifiant.
- [ ] Vérifier le refus d'un autre identifiant de dossier.
- [ ] Tester un payload valide puis des payloads invalides (schéma, droits, compte, journal, tiers).
- [ ] Tester les doublons, le rejeu et la révocation.
- [ ] Vérifier que les journaux masquent tous les secrets et conservent les données d'audit utiles.

---

## 8. Éléments restant à décider ou à confirmer

Les points suivants nécessitent une décision métier/technique avant codage complet ; ils ne doivent pas être inventés :

- Liste définitive des applications maison actives et URL de chaque environnement.
- Format exact du code de liaison et durée de vie par défaut.
- Mode de renouvellement automatique et fenêtre de transition.
- Règle d'unicité des associations de dossiers (un dossier partenaire peut-il correspondre à plusieurs dossiers COMPTAFLOW ?).
- Champs supplémentaires obligatoires pour créer une entreprise COMPTAFLOW à partir d'une demande externe.
- Qui approuve une demande externe de création : création immédiate après validation automatique ou approbation Super Admin.
- Règles de génération et de remise du mot de passe temporaire ou du lien d'activation.
- Schémas JSON et versions de payload officiels pour chaque famille de données.
- Durée de conservation des payloads et journaux d'audit.

**Décision de sécurité recommandée pour le mot de passe initial :** lien d'activation à usage unique et durée limitée plutôt qu'un mot de passe par défaut commun.

---

## 9. Captures de référence fournies

Les captures transmises le 20 novembre 2026 servent de référence pour :
- L'écran RH-FLOW de configuration de la passerelle COMPTAFLOW (URL serveur, clé de dossier, code journal et test de connexion).
- Le tableau actuel d'historique des connexions et dépôts, avec Date, Application, Dossier, Événement, Route, HTTP et IP.

Ces captures illustrent l'existant observé ; elles ne prouvent pas que le mécanisme de clé affiché est déjà compatible avec le nouveau flux de code de liaison/Bearer décrit dans ce document. La transition entre les deux doit être conçue et testée explicitement.


## 10. Réalisation COMPTAFLOW publiée sur main — 20/11/2026

### Éléments développés côté COMPTAFLOW
- Ajout d'une URL d'application et d'un code de liaison aléatoire par application maison ; code stocké sous forme de hash pour la validation et chiffré pour l'appel sortant au catalogue partenaire.
- Échange du code maison via `POST /api/integration/pairing/redeem`. Le code ne donne aucun accès à un dossier ; il sert à activer l'identité applicative et remettre des identifiants API une seule fois.
- Bouton de génération/renouvellement du code pour les applications maison déjà référencées, y compris RH-FLOW initialement en statut prévu/en attente.
- Écran en deux étapes : choix du dossier COMPTAFLOW, puis fenêtre de choix du dossier partenaire chargée par appel serveur.
- Le serveur recontrôle le dossier partenaire dans le catalogue distant avant d'enregistrer l'association ; les identifiants source et nom du dossier partenaire sont conservés dans la liaison.
- Le proxy du catalogue attend le contrat partenaire `GET /api/integration/companies`, en HTTPS, avec l'en-tête `X-Integration-Pairing-Code` et une réponse `{ "data": [{ "id": "...", "name": "..." }] }`.
- Ajout d'une file de demandes de création d'entreprise pour les applications externes : `POST /api/integration/external/company-requests`, puis consultation de sa propre demande via `POST /api/integration/external/company-requests/{requestId}/status`.
- Les demandes externes sont approuvées par un Super Admin ; COMPTAFLOW crée alors le dossier dédié, son exercice comptable et la liaison, puis envoie au gérant un lien d'activation temporaire pour qu'il choisisse son propre mot de passe. Aucun mot de passe par défaut commun n'est créé.
- Traçabilité API enrichie côté middleware : méthode, durée, identifiant de requête, payload et réponse expurgés des champs sensibles.
- Les anciennes routes SELFLOW et GESTION-DE-SITES sont conservées.

### Contrat restant côté application maison partenaire
Le dépôt COMPTAFLOW ne contient pas le code RH-FLOW. Pour que le sélecteur des entreprises RH-FLOW fonctionne en réel, RH-FLOW doit exposer :
- `GET /api/integration/companies`
- vérifier le code reçu dans `X-Integration-Pairing-Code` ;
- renvoyer uniquement les entreprises accessibles à l'application, sous `data: [{id,name}]` ;
- enregistrer le code collé par l'administrateur et utiliser le mécanisme d'échange décrit ci-dessus.

Sans cet endpoint partenaire, COMPTAFLOW affichera une erreur de catalogue et ne permettra pas d'enregistrer une liaison distante fictive.

### Déversement
Le transfert d'écritures reste déclenché par l'application source via son bouton **Déverser**, qui appelle les endpoints COMPTAFLOW déjà documentés. COMPTAFLOW ne doit pas inventer ni envoyer des écritures de paie qu'il ne possède pas. Le bouton dans RH-FLOW nécessite donc le raccordement de son écran au endpoint de dépôt avec les identifiants et payloads validés.

### Validation et limites
- Tests unitaires ajoutés pour l'échange du code maison à usage unique et la demande externe idempotente.
- Les tests CI doivent encore être confirmés après la dernière correction des fixtures.
- Le parcours partenaire RH-FLOW et le bouton Déverser côté RH-FLOW ne sont pas déclarés opérationnels tant que le dépôt RH-FLOW et son endpoint ne sont pas intégrés et testés.


### Validation CI — 20/11/2026
- Workflow `44736719640` sur `cc2a25f5045bee4cdf7f8d086e306a4686cd1c89` : **completed / success**.
- Tests ciblés du registre, du code de liaison à usage unique et de la demande externe idempotente : réussis.
- Compilation Blade : réussie.
- Vérification des routes Super Admin et API COMPTA-APK : réussie.
- La CI valide COMPTAFLOW uniquement. Le catalogue d'entreprises RH-FLOW et le bouton Déverser côté RH-FLOW restent dépendants du dépôt partenaire et de son API.


## 12. Correction de la durée du code de liaison — 20/11/2026
La durée de validité n'est plus imposée à 30 jours. Au moment de générer ou renouveler le code, le Super Admin choisit :
- 3 mois (90 jours) ;
- 6 mois (180 jours) ;
- 1 an (365 jours, valeur par défaut) ;
- 2 ans (730 jours).

La durée choisie est enregistrée côté COMPTAFLOW et affichée avec la date d'expiration. Le code reste à usage unique pour l'échange d'identifiants. Cette durée concerne le code de liaison initial, pas le jeton Bearer temporaire ni le secret applicatif.

Le cahier des charges à transmettre à l'équipe RH-FLOW est désormais :
`docs/traitement recent/CAHIER_DES_CHARGES_RH_FLOW_LIAISON_COMPTAFLOW.md`.


## Complément — liaison bidirectionnelle des applications maison
La liaison des dossiers doit pouvoir être initiée depuis les deux interfaces : COMPTAFLOW choisit un dossier local puis une entreprise partenaire ; l'application maison choisit son entreprise locale puis un dossier COMPTAFLOW autorisé. Les deux actions aboutissent à une seule relation centrale et au même statut des deux côtés.

Pour le parcours initié depuis l'application maison, COMPTAFLOW doit exposer un catalogue limité aux dossiers explicitement autorisés pour cette application, puis une action de demande de liaison. Ces routes inverses sont documentées dans les cahiers des charges comme contrat à développer/valider ; elles ne sont pas déclarées existantes sur la seule base de l'audit des routes actuelles.

Documents généraux créés :
- `CAHIER_DES_CHARGES_APPLICATIONS_MAISON_SUITE_FLOW.md`
- `CAHIER_DES_CHARGES_APPLICATIONS_TIERS_COMPTAFLOW.md`

Le cahier RH-FLOW a également été mis à jour pour inclure les deux sens de liaison et les payloads des échanges de référentiel, écritures et PDF.
