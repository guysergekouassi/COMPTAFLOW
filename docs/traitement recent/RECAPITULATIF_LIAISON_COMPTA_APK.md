# Liaison COMPTA-APK — récapitulatif de réalisation

Dernière mise à jour : 19 novembre 2026  
Dépôt : `IT-dcknowing/COMPTAFLOW` — branche `main`

## 1. Objectif confirmé

Mettre en place dans le Super Admin de COMPTAFLOW une gestion centralisée des applications qui échangent des données comptables avec COMPTAFLOW. L'interface doit être une vraie fonctionnalité métier, et non un simple renommage de l'ancienne page SELFLOW.

Le menu attendu est une rubrique principale **Liaison COMPTA-APK**, avec deux entrées :

### APPLICATION MAISON — LA SUITE FLOW
- Registre des applications internes autorisées (SELow, RH-FLOW, etc. selon les applications effectivement intégrées).
- Consultation des liaisons de chaque application.
- Liste limitée aux entreprises COMPTAFLOW disponibles pour cette application.
- Action pour lier une entreprise existante.
- Action pour créer une entreprise et la lier lorsque le parcours métier le permet.
- Contrôle des droits et du périmètre exclusivement côté serveur.

### APPLICATION EXTERNE
- Registre des applications tierces : nom, propriétaire, statut.
- Création et modification d'une application externe.
- Création d'une entreprise COMPTAFLOW dédiée à l'intégration.
- Gestion des identifiants : génération, date d'expiration, renouvellement/rotation et révocation.
- Historique des connexions et des dépôts.
- Une application externe ne doit jamais recevoir la liste globale des entreprises COMPTAFLOW ni choisir librement un `company_id`.

## 2. Architecture et règles de sécurité obligatoires

- Les endpoints COMPTAFLOW sont les seules portes d'entrée. Aucun accès SQL direct ni écriture directe dans les tables COMPTAFLOW depuis une application cliente.
- Le contexte entreprise est résolu côté serveur à partir de l'application authentifiée et de ses liaisons actives. Aucun identifiant d'entreprise fourni dans un payload métier ne doit sélectionner le dossier destinataire.
- Chaque application possède un `client_id` et un secret généré aléatoirement par COMPTAFLOW.
- Le secret est affiché une seule fois à sa création/rotation ; seule une empreinte vérifiable est conservée côté COMPTAFLOW.
- Le secret ne doit jamais être commité, écrit en dur, placé dans un JSON métier ou journalisé.
- Authentification cible : `Authorization: Bearer <jeton applicatif>`.
- Les permissions doivent être distinctes pour le référentiel, les écritures comptables et les pièces justificatives.
- Prévoir révocation immédiate, rotation, expiration, limitation de débit, journalisation, protection anti-rejeu et idempotence.
- Les paramètres nécessaires aux clients doivent être configurables dans leur interface sécurisée, avec stockage chiffré côté client ; pas de modification du code ni édition manuelle du `.env` pour chaque nouvelle liaison.
- `X-Company-Key` et l'ancien `EXTERNAL_SYNC_SECRET` sont des mécanismes de transition/compatibilité. Ne pas retirer l'ancien mécanisme avant migration de tous les clients actifs et tests concluants.
- Le contrat final doit retenir un mécanisme d'authentification cohérent ; le secret applicatif ne doit pas être envoyé dans les payloads métier.

## 3. Déversements et contrats de données

Familles de données :
1. Référentiel : plan comptable, tiers, codes journaux.
2. Écritures : ventes, achats, OD, paie, stock et autres opérations.
3. Pièces justificatives : dépôt PDF lié à une opération précise.

Règles de numérotation :
- L'application source transmet son numéro métier courant.
- COMPTAFLOW conserve ce numéro comme `numero_original` et génère son propre numéro selon les paramètres du dossier.
- La source ne transmet pas un second champ `numero_original`.
- Même principe pour les comptes, tiers et codes journaux.
- Les clés source utiles à l'idempotence peuvent être transmises, mais ne déterminent jamais le dossier destinataire.

## 4. Éléments déjà présents dans le dépôt (vérifiés sur main)

Référence de base vérifiée : commit `b0277ea32f7dfe4eae29c65567fb875d40a9b080`.

### Liaison SELFLOW historique
- Route GET `/superadmin/liaisons` et actions associées déclarées dans `routes/web.php`.
- Contrôleur existant : `app/Http/Controllers/Super/SuperAdminLiaisonController.php`.
- Cette page est limitée au parcours historique SELFLOW ↔ COMPTAFLOW. Elle ne constitue pas la nouvelle console COMPTA-APK.

### Endpoints externes existants
L'inventaire de `routes/api.php` montre notamment :
- provision et révocation de dossiers externes ;
- rotation de clé et vérification ;
- déversement des écritures et regroupement ;
- propagation des comptes ;
- déversement du référentiel ;
- endpoints spécifiques GESTION-DE-SITES ;
- dépôt des pièces justificatives (présent dans le fichier de routes).

Les routes de déversement examinées sont protégées par le middleware historique `cle.entreprise`. Ce dispositif lie la clé à un dossier, mais ne constitue pas encore le registre générique et l'authentification applicative Bearer décrits dans la cible.

### Éléments de synchronisation déjà documentés
Selon la spécification fournie dans la session :
- `POST /api/external/ecritures/comptes/propager` est disponible ; il réaffecte les anciennes lignes au nouvel identifiant de plan comptable et actualise les comptes généraux des tiers, sans recalcul des montants.
- `POST /api/external/pieces-justificatives/deverser` est disponible ; contrôle de la clé du dossier, du PDF, de la taille, du SHA-256, de l'opération exacte et de l'idempotence ; stockage privé et contrôle du dossier propriétaire lors du téléchargement.
- SELFLOW transmet déjà les payloads de référentiel et d'écritures sans secret global, sans identifiants de routage et sans champ `numero_original` séparé.
- Le dépôt PDF SELFLOW est intégré au job de déversement des opérations.
- SELFLOW : comptabilisation du stock activée par défaut tous les trois mois ; quotidien optionnel ; date de fin facultative.

Ces éléments sont rapportés comme existants dans la spécification de session ; ils ne valent pas preuve d'un test de bout en bout effectué dans ce traitement.

## 5. Historique des commits connus

| Dépôt | Commit | Portée connue |
|---|---|---|
| COMPTAFLOW — IT | `b0277ea32f7dfe4eae29c65567fb875d40a9b080` | Correction de la page Super Admin/contrôle des pages : largeur de sidebar, disposition responsive, informations du Super Admin et accès vers la page historique de liaison. |
| COMPTAFLOW — IT | `8101a46f639ec24420baf800204775199a51f4f6` | Modifications antérieures sur le résultat/clôture comptable, décrites dans l'historique de session. |
| SELFLOW — IT | `612000ef116f486778a1f4eb6f557c64ffa70d2e` | Stock actif par défaut par période de trois mois et transmission automatique du PDF après acceptation, selon l'historique de session. |
| COMPTAFLOW — IT | `128f5e55df8e10d79551d5ae6289b50e862000cb` | Correction PHP dans le connecteur, syntaxe contrôlée par `php -l` selon l'historique de session. |

Le commit `b0277ea...` n'a pas créé l'interface COMPTA-APK. Aucun commit de réalisation de cette nouvelle interface n'est enregistré à la date de ce récapitulatif. Les deux fichiers de suivi ont été ajoutés sur main : récapitulatif initial `47b64fe8518ec66f6c2a21fa91e75ed154bad6ae`, journal `5537cfd52ac32383b403b3dda841ea41672e13d5`.

## 6. Travail restant — non livré à ce jour

### À développer
- [ ] Rubrique principale Super Admin « Liaison COMPTA-APK ».
- [ ] Sous-menu « APPLICATION MAISON — LA SUITE FLOW ».
- [ ] Sous-menu « APPLICATION EXTERNE ».
- [ ] Tables/migrations et modèles du registre générique des applications.
- [ ] Modèle des liaisons application ↔ entreprise, avec contraintes propres aux applications maison et externes.
- [ ] Gestion centralisée des permissions par famille de données.
- [ ] Génération de `client_id`, secret aléatoire, empreinte, expiration, rotation et révocation.
- [ ] Émission et validation de jetons Bearer.
- [ ] Middleware d'authentification applicative et résolution du dossier côté serveur.
- [ ] Historique des authentifications, connexions, dépôts, refus et révocations.
- [ ] Écrans de configuration sécurisée côté applications clientes.
- [ ] Migration progressive des clients actifs depuis `EXTERNAL_SYNC_SECRET` / `X-Company-Key`.
- [ ] Retrait de l'ancien secret global seulement après migration et validation.

### À tester
- [ ] Application maison autorisée sur plusieurs dossiers explicitement liés.
- [ ] Application externe isolée à son entreprise dédiée.
- [ ] Tentative d'accès à une entreprise non liée refusée.
- [ ] Aucun endpoint externe ne liste les entreprises globales.
- [ ] Aucun `company_id` libre dans les payloads ne peut changer le dossier cible.
- [ ] Secret invalide, jeton expiré, révocation et rotation.
- [ ] Permissions référentiel / écritures / pièces justificatives.
- [ ] Rejeu idempotent et anti-rejeu.
- [ ] Dépôt PDF dans le mauvais dossier refusé.
- [ ] Compatibilité des connecteurs existants et absence de régression sur SELFLOW et GESTION-DE-SITES.
- [ ] Tests PHP, tests applicatifs, vérification des routes et rendu navigateur après déploiement.

## 7. Périmètre et exclusions à respecter

- Le travail de développement de cette demande porte sur COMPTAFLOW et la liaison avec SELFLOW.
- RH-FLOW est cité comme application maison future/contractuelle, mais son dépôt et son code ne doivent pas être modifiés dans ce lot sans demande explicite distincte.
- GESTION-DE-SITES possède déjà des endpoints dédiés ; ne pas casser ni détourner cette intégration.
- Les changements doivent être poussés directement sur `main` du dépôt IT `IT-dcknowing/COMPTAFLOW`, sans Pull Request, conformément à la consigne actuelle.
- Ne jamais déclarer une fonctionnalité terminée sur la seule présence d'une route ou d'une vue : vérifier contrôleur, autorisations, données, tests et parcours réel.

## 8. État de ce traitement

**État : documentation de suivi en cours ; interface COMPTA-APK non livrée.**

Ce récapitulatif distingue les éléments vérifiés dans le dépôt, ceux rapportés par la spécification de session et les travaux réellement non réalisés. Chaque nouveau traitement doit mettre à jour ce fichier et le journal de traitement avant de conclure, avec commit, fichiers modifiés, contrôles effectués, limites et tâches restantes.

## 9. Règle de mise à jour continue

À chaque traitement :
1. Lire ce récapitulatif avant modification.
2. Vérifier l'état réel de `main` et des fichiers concernés.
3. Mettre à jour le journal daté dans `docs/traitement recent/`.
4. Mettre à jour ce récapitulatif avec les résultats réels.
5. Pousser les modifications sur `main`.
6. Reporter le SHA du commit et distinguer explicitement tests réussis, tests non exécutés et déploiement non vérifié.


## 10. Livraison serveur COMPTA-APK — 19/11/2026

### Code ajouté dans COMPTAFLOW
- Migration `2026_11_19_000001_create_integration_registry_tables.php` : registre des applications, liaisons et permissions, jetons hashés, événements d'audit et nonces anti-rejeu.
- Contrôleur `SuperAdminIntegrationController` : création/modification/suspension/révocation d'applications, liaisons, création de dossier dédié externe, rotation des secrets et journalisation.
- Contrôleur API `IntegrationTokenController` : émission de jetons Bearer d'une heure après validation du client et de son périmètre.
- Middleware `VerifieApplicationOuCleEntreprise` : contrôle du jeton, du dossier lié, du type maison/externe, des permissions, de l'expiration et des en-têtes anti-rejeu. Repli conservé vers `VerifieCleEntreprise` pour la compatibilité historique.
- Vue `resources/views/superadmin/liaison_compta_apk.blade.php` et routes Super Admin dédiées.
- Nouvelle rubrique de menu avec les deux entrées demandées ; l'ancienne page SELFLOW reste accessible.
- Le registre affiche SELFLOW en mode historique et RH-FLOW en attente (référence uniquement ; aucun dépôt RH-FLOW modifié).
- Le préfixe source des écritures est désormais issu du code d'application pour les applications Bearer autres que SELFLOW/GESTION-DE-SITES.

### Contrat API
Voir `CONTRAT_API_BEARER.md` dans ce dossier. Les applications maison peuvent obtenir un jeton pour une liaison parmi celles qui leur sont explicitement accordées. Une application externe doit posséder une seule liaison active.

### État à ne pas surévaluer
Le code serveur est ajouté dans ce commit, mais migration SQL, tests PHP/Feature, rendu navigateur, déploiement serveur et tests bout en bout restent à exécuter. Le connecteur SELFLOW n'a pas encore été basculé côté client vers Bearer ; il reste accepté via `X-Company-Key`. RH-FLOW reste hors développement applicatif dans ce lot.


### Correctifs complémentaires après la première livraison
- Les routes de déversement sont maintenant soumises à des limites de débit explicites.
- Les journaux distinguent les appels API génériques des dépôts référentiel, écritures et pièces justificatives.
- Les applications autres que SELFLOW/GESTION-DE-SITES utilisent leur propre code comme identité source et comme préfixe de déversement ; elles ne récupèrent pas par erreur l'identifiant Selflow d'un dossier également lié à SELFLOW.
- Une suite Feature `IntegrationBearerSecurityTest` couvre la résolution de dossier, le refus d'un company_id contradictoire, le rejeu de nonce, les permissions et l'unicité du dossier externe. Ces tests sont ajoutés mais ne sont pas encore exécutés.
