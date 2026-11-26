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

## 6. État fonctionnel livré

### Console Super Admin COMPTA-APK
- [x] Rubrique principale « Liaison COMPTA-APK » dans le menu Super Admin.
- [x] Entrée « APPLICATION MAISON — LA SUITE FLOW ».
- [x] Entrée « APPLICATION EXTERNE ».
- [x] Registre des applications : code, nom, type, propriétaire, statut et mode d'authentification.
- [x] Liaisons application ↔ entreprise et permissions par référentiel, écritures et pièces justificatives.
- [x] Action de liaison à une entreprise existante.
- [x] Création d'une entreprise dédiée à une application externe, avec compte technique, catégories de trésorerie et exercice comptable dont les dates sont saisies par le Super Admin.
- [x] Client ID, secret affiché une seule fois, empreinte du secret, expiration, rotation et révocation.
- [x] Journal des connexions, dépôts, révocations et opérations applicatives.
- [x] La page historique `/superadmin/liaisons` reste disponible pour le parcours SELFLOW existant.

### Routes de la nouvelle interface
- `/superadmin/liaison-compta-apk` : entrée principale, redirection vers APPLICATION MAISON.
- `/superadmin/liaison-compta-apk/maison` : applications internes et leurs liaisons.
- `/superadmin/liaison-compta-apk/externe` : applications tierces et dossiers dédiés.

### Sécurité et endpoints
- [x] Émission de jetons Bearer d'une heure.
- [x] Résolution du dossier côté serveur à partir du jeton et de la liaison active.
- [x] Une application maison ne peut utiliser que les dossiers explicitement liés.
- [x] Une application externe ne peut avoir qu'une seule liaison active vers son dossier dédié.
- [x] Aucun endpoint externe ne fournit la liste globale des entreprises.
- [x] Un `company_id` fourni dans un payload ne peut pas changer le dossier autorisé.
- [x] Permissions séparées : `referentiel`, `ecritures`, `pieces_justificatives`.
- [x] Horodatage, nonce anti-rejeu, limitation de débit, idempotence et journalisation.
- [x] Révocation d'une liaison SELFLOW synchronisée avec le registre central et les jetons.
- [x] Le mode historique `X-Company-Key` est maintenu en repli transitoire, mais il est aussi soumis au registre central et à ses permissions pour SELFLOW.
- [x] Le secret global n'est plus envoyé dans les payloads métier de référentiel, écritures ou pièces. Il reste réservé au provisionnement et à certaines opérations de contrôle historiques.

### Client SELFLOW
- [x] Formulaire Super Admin sur la page historique SELFLOW pour enregistrer le Client ID et le Client Secret COMPTAFLOW.
- [x] Secret applicatif chiffré avec `APP_KEY` dans les paramètres plateforme ; jeton en cache chiffré.
- [x] Appels métier en Bearer avec horodatage et nonce.
- [x] Renouvellement transparent du jeton après HTTP 401, avec repli historique si les identifiants ne sont pas encore actualisés.
- [x] Raccordement du référentiel, des écritures, du regroupement, des pièces justificatives et de la propagation des comptes au client HTTP commun.
- [x] Les opérations de rotation/révocation des anciennes clés par dossier conservent leur mécanisme de contrôle historique.

## 7. Résultats des contrôles automatisés

<badge color="success">COMPTAFLOW — CI réussie</badge>

Workflow GitHub Actions `44586071484` :
- Installation Composer : réussie.
- Tests de sécurité Bearer `IntegrationBearerSecurityTest` : réussis, y compris le refus d'accès à la liste globale et à `company-info` avec un simple jeton Bearer.
- Test isolé `IntegrationRegistryMigrationTest` ajouté pour exécuter la nouvelle migration et vérifier les tables/champs du registre.
- Compilation des vues Blade : réussie.
- Vérification des routes Super Admin et API : réussie.

<badge color="success">SEFlow — CI réussie</badge>

Workflow GitHub Actions `44586186577` :
- Installation Composer : réussie.
- Tests ciblés `LiaisonComptaflowTest` et `PasserelleComptaflowTest` : réussis.
- Compilation des vues Blade : réussie.
- Vérification des routes Super Admin SELFLOW : réussie.

La suite SELFLOW signale encore des dépréciations PHPUnit héritées ; elles ne bloquent pas le workflow. Les assertions des suites ciblées passent.

Ces contrôles sont automatisés sur GitHub. Ils ne remplacent pas les tests de bout en bout sur les serveurs de production.

## 8. Ce qui reste réellement à faire

### Avant activation en production
- [ ] Déployer les deux branches `main` sur les serveurs COMPTAFLOW et SELFLOW.
- [ ] Exécuter les migrations COMPTAFLOW en production avec sauvegarde préalable.
- [ ] Ouvrir `/superadmin/liaison-compta-apk` avec un compte Super Admin et vérifier le rendu réel.
- [ ] Dans COMPTAFLOW, générer/renouveler les identifiants de l'application maison SELFLOW.
- [ ] Copier le Client ID et le Client Secret affiché une seule fois.
- [ ] Dans SELFLOW → Super Admin → Liaisons SELFLOW, enregistrer ces identifiants dans le nouveau panneau « Authentification centralisée COMPTA-APK ».
- [ ] Vérifier une liaison réelle, un déversement de référentiel, une écriture et une pièce justificative.
- [ ] Vérifier la rotation et la révocation sur les deux applications en environnement réel.

### Périmètre non livré
- [ ] RH-FLOW est inscrit comme application maison en attente, mais son connecteur, ses payloads et ses tests ne sont pas développés dans ce lot.
- [ ] Les nouveaux connecteurs d'applications externes devront être testés avec leurs vrais payloads et leurs permissions dédiées.
- [ ] Le retrait définitif de `X-Company-Key` et du secret global n'est pas encore effectué : il doit attendre la migration de tous les clients actifs, notamment GESTION-DE-SITES.
- [ ] Les tests navigateur et le déploiement effectif sur les sites de production restent à réaliser.

## 9. Références des livraisons

- COMPTAFLOW, branche `main` : `8c408a97ea7f0483f200db4bb8a0176d0ef460dc`.
- SELFLOW, branche `main` : code Bearer initial `6c716b8ee055ec2b5e4cd53f54fffa02b1769fb3`, puis mises à jour de tests et de CI jusqu'au commit `c6226eb4ab6510264dd9c693603b27ce7392daca`.
- La correction initiale de la page de contrôle Super Admin reste référencée par `b0277ea32f7dfe4eae29c65567fb875d40a9b080`.
- Dernière consolidation documentaire (récapitulatif, journal et README) : `350193e4fb3ca1787e4a8bf45a220e16d54e9db1`.

## 10. État global à retenir

**Le code de la console COMPTA-APK, le registre, les contrôles d'accès et le client SELFLOW sont publiés sur les branches `main` et les workflows ciblés passent. La fonctionnalité n'est pas encore déclarée opérationnelle sur les sites de production : les migrations, le déploiement et la saisie des identifiants réels restent obligatoires.**

Ne pas confondre :
- l'ancienne page SELFLOW : `/superadmin/liaisons` ;
- la nouvelle console centrale : `/superadmin/liaison-compta-apk`.

## 11. Règle de mise à jour continue

À chaque nouveau traitement :
1. Lire ce récapitulatif et le journal avant modification.
2. Vérifier l'état réel de `main` dans les dépôts concernés.
3. Mettre à jour le journal daté dans `docs/traitement recent/JOURNAL.md`.
4. Mettre à jour ce récapitulatif avec les résultats réels.
5. Pousser directement sur `main`, conformément à la consigne actuelle.
6. Reporter les SHA de commit et les résultats CI.
7. Distinguer explicitement ce qui est codé, testé, déployé et vérifié en production.


## 12. Vérification de l'état réel du dépôt — 19/11/2026 (mise à jour)

Cette section fait foi lorsqu'une ancienne section ci-dessus présente encore un état intermédiaire.

### Code vérifié sur `main`
- Le menu Super Admin contient bien « Liaison COMPTA-APK », « APPLICATION MAISON — LA SUITE FLOW », « APPLICATION EXTERNE » et conserve l'accès aux anciennes liaisons SELFLOW.
- Les routes Web de la console sont présentes dans le groupe `auth + superadmin` :
  - `/superadmin/liaison-compta-apk`
  - `/superadmin/liaison-compta-apk/maison`
  - `/superadmin/liaison-compta-apk/externe`
  - routes POST/PUT/DELETE de création, modification, liaison, révocation de liaison, rotation et révocation d'application.
- La vue `resources/views/superadmin/liaison_compta_apk.blade.php` est présente et utilise le composant `components.sidebar`, lequel bascule vers `components.superadmin_sidebar` lorsque le compte est Super Admin.
- Le contrôleur `SuperAdminIntegrationController`, la migration du registre, le middleware `VerifieApplicationOuCleEntreprise` et `IntegrationTokenController` sont présents.
- SELFLOW est repris en mode historique sans réactiver les liaisons révoquées ; RH-FLOW est enregistré en attente, sans prétendre que son connecteur est livré.
- Les routes de dépôt API continuent d'utiliser le middleware central ; les anciens mécanismes de clé restent disponibles pendant la migration contrôlée.
- Le token d'une application maison ne peut être émis que pour un dossier appartenant à ses liaisons actives. Pour une application externe, une seule liaison active est exigée.
- Les identifiants de dossier éventuellement présents dans le corps d'une requête Bearer sont comparés au dossier du jeton et ne peuvent pas changer le dossier cible.

### CI vérifiée
- COMPTAFLOW : dernier commit `fef72c2c620b4fa12c612e947bf9b362be6a5839`.
- Workflow GitHub Actions `44587278019` : **success**. Étapes réussies : installation Composer, préparation de la clé d'application, test isolé de la migration du registre et tests Bearer, compilation Blade et vérification des routes.
- La CI exécute les tests ciblés `IntegrationRegistryMigrationTest` et `IntegrationBearerSecurityTest`, pas l'intégralité de la suite du projet.
- SELFLOW : branche `main` au commit `c6226eb4ab6510264dd9c693603b27ce7392daca`.
- Workflow SELFLOW `44586186577` : **success**. Les suites ciblées de liaison/passerelle, la compilation Blade et la vérification des routes ont réussi.

### État final exact
- **Codé et poussé sur main :** console COMPTA-APK, registre, routes Super Admin, liaison des dossiers, gestion des secrets, jetons Bearer, permissions, anti-rejeu, journalisation, maintien de la compatibilité SELFLOW, client SELFLOW et tests ciblés.
- **Validé en CI :** les étapes ci-dessus pour les deux dépôts.
- **Non déployé / non vérifié en production dans cette session :** migrations sur les bases réelles, déploiement des deux applications, ouverture de l'interface avec un compte Super Admin de production, configuration effective du Client ID/Secret dans SELFLOW et essais de dépôts réseau réels.
- **Non livré :** connecteur RH-FLOW ; les nouvelles intégrations externes doivent chacune faire l'objet d'un test avec leurs payloads réels.
- **Compatibilité non supprimée :** ne pas retirer `X-Company-Key` ni `EXTERNAL_SYNC_SECRET` avant la migration effective de tous les clients actifs, notamment GESTION-DE-SITES.

Dernier HEAD COMPTAFLOW vérifié : `fef72c2c620b4fa12c612e947bf9b362be6a5839`.


## 13. Durcissement final — dossier dédié obligatoire pour les applications externes

- Le rattachement générique à une entreprise existante est désormais réservé aux applications maison.
- Le contrôleur refuse en HTTP 403 toute tentative d'utiliser cette action avec une application externe, même si l'appel est forgé directement sans passer par l'interface.
- Le formulaire « Lier à une entreprise existante » n'est plus affiché pour les applications externes.
- Une application externe doit utiliser exclusivement le parcours « Créer une entreprise COMPTAFLOW dédiée et la lier ». Le contrôleur conserve aussi la règle d'une seule liaison active.
- Deux tests Feature ont été ajoutés : refus du rattachement générique pour une application externe et refus d'émission d'un jeton maison pour un dossier non lié.
- COMPTAFLOW : commit de correction/test `45a033138022579f9ae84f8daadac8ae2e6bf7bb`.
- GitHub Actions COMPTAFLOW : workflow `44589011690` terminé avec succès ; installation, tests migration/Bearer, compilation Blade et contrôle des routes réussis.

Cette correction renforce la règle métier : une application externe n'est pas autorisée à se rattacher à un dossier client préexistant. Elle possède un dossier dédié créé par le parcours Super Admin.


## 14. Clôture du lot de code et contrôle CI final — 19/11/2026

### Commits du dernier durcissement
- `98c620fc3f922665a9f6c02e9ab5cd0da93200b7` — refus serveur du rattachement générique pour une application externe.
- `dafae855b9de43144c9c9ebcb174e7d868dd0281` — masquage du formulaire de rattachement existant dans la section externe.
- `ad8ba236f2f924394382c291dcf35c52a53f6f50` — ajout des tests de refus et d'isolation.
- `45a033138022579f9ae84f8daadac8ae2e6bf7bb` — imports de test et validation complète du commit de code.

### Résultat final CI
- Workflow COMPTAFLOW `44589135771` : **completed / success**.
- Étapes confirmées : Composer, migration ciblée et tests Bearer, compilation Blade, vérification des routes.
- Le workflow est exécuté sur le commit documentaire `d63b80310402bafb1393f7ec9057edf919f2ddde`, dont le code applicatif inclut le correctif testé `45a0331...`.
- Les changements de suivi suivants n'ont modifié que les fichiers Markdown.

### État de clôture
La réalisation applicative COMPTA-APK demandée dans ce lot est terminée et publiée sur `main`, avec contrôles CI réussis. La mise en service sur les serveurs n'est pas déclarée terminée : elle nécessite accès à l'hébergement, sauvegarde, migration réelle, déploiement COMPTAFLOW + SELFLOW et essais de bout en bout avec les identifiants réels.


## 15. Correctifs suite au retour de production — 19/11/2026

### Présentation « Visibilité des pages & clés IA »
- Le rendu signalé venait de la vue `resources/views/superadmin/page-control/index.blade.php` : elle incluait directement le sidebar Super Admin et ajoutait un CSS autonome qui forçait une sidebar bleu nuit de 260 px, un layout flex et une largeur calculée pour le contenu.
- La vue utilise maintenant `components.sidebar`, comme le tableau de bord Super Admin. Ce composant restitue la barre horizontale et choisit le sidebar Super Admin par son mécanisme normal.
- Suppression des règles CSS qui forçaient le thème sombre et le positionnement latéral. Le style reste limité aux cartes, formulaires et éléments propres au catalogue.
- Le contenu conserve les informations du compte Super Admin, les onglets Pages / IA / Quotas et toutes les actions existantes.

### Échec MySQL de la migration
- Erreur constatée sur le serveur : nom de contrainte généré `integration_application_companies_integration_application_id_foreign` trop long pour MySQL (limite 64 caractères).
- Migration corrigée avec des noms de contraintes explicites courts : `ia_companies_app_fk`, `ia_companies_company_fk`, `ia_tokens_app_fk`, `ia_tokens_company_fk`, `ia_nonces_app_fk`, `ia_events_app_fk`, `ia_events_company_fk`.
- La migration vérifie l'existence des tables avant de les créer et répare les index et clés étrangères manquants. Elle est conçue pour reprendre après le cas MySQL où la table existe déjà mais où l'ajout de la première clé étrangère a échoué.
- Aucun `DROP TABLE` correctif ni suppression de données n'est demandé.

### Tests ajoutés
- Exécution de la migration deux fois pour vérifier son idempotence.
- Simulation du cas réel : tables `integration_applications` et `integration_application_companies` déjà créées, mais clés étrangères absentes ; la migration doit compléter les contraintes et index.
- Résultat du nouveau workflow CI à confirmer après son exécution.

### Procédure serveur après récupération de main
1. Sauvegarder la base COMPTAFLOW.
2. Récupérer le commit de correction sur le serveur.
3. Exécuter `php artisan migrate --force` (ou `php artisan migrate` après confirmation interactive).
4. Si une erreur subsiste, arrêter et transmettre le message exact ; ne pas supprimer manuellement les tables du registre.

La capture confirme uniquement l'échec de l'ajout de la contrainte ; elle ne permet pas de lire l'état exact des tables en production. La migration révisée vérifie et complète cet état sans présumer qu'il est vide.


## 16. Résultat de validation après corrections de test — 19/11/2026

- Le premier test de reprise avait une assertion trop stricte sur les métadonnées de clés étrangères SQLite ; le contrôle a été adapté pour vérifier les tables/index sur SQLite et les noms de contraintes sur MySQL.
- Un index simple sur le statut des liaisons a été ajouté en plus de l'index composite entreprise/statut, afin de restaurer les deux index que la migration initiale n'avait pas encore atteints avant son arrêt.
- Workflow `44600112707` : succès après adaptation des assertions de métadonnées.
- Workflow `44600200330` : succès après ajustement de la portée des assertions MySQL.
- Workflow `44600248946` : lancé sur le commit `49ed888f97d3b9962f0929fa0cfff6cdfb105e56` ; résultat à confirmer.


### Confirmation finale CI — 19/11/2026
- Workflow `44600248946` sur le commit applicatif `49ed888f97d3b9962f0929fa0cfff6cdfb105e56` : **completed / success**.
- Étapes réussies : installation Composer, tests ciblés migration et Bearer, compilation Blade, vérification des routes.
- La vue IA est validée à la compilation. La migration est validée sur SQLite en mode création et reprise d'un schéma partiellement créé ; la CI ne simule pas un serveur MySQL réel.
- La correction de MySQL 1059 est basée sur des noms de contraintes courts et la migration de reprise. Le résultat MySQL réel doit encore être confirmé par la commande sur l'hébergement après récupération du code.


## État au 20/11/2026 — implémentation COMPTA-APK
- Console maison : code de liaison, renouvellement manuel, sélection du dossier COMPTAFLOW puis appel au catalogue partenaire et mémorisation de la correspondance distante.
- Applications externes : demande API de création, file d'approbation Super Admin, création du dossier dédié, exercice comptable et lien d'activation temporaire du gérant.
- Audit : méthode HTTP, route, IP, statut, durée, identifiant de requête et détails payload/réponse avec masquage des secrets.
- Migrations complémentaires : ajout des champs de liaison et table des demandes externes.
- Tests CI réussis sur `cc2a25f5045bee4cdf7f8d086e306a4686cd1c89` (workflow `44736719640`).
- **Non livré côté partenaire** : le dépôt RH-FLOW n'est pas disponible dans les dépôts GitHub installés de cette session. Le endpoint `GET /api/integration/companies` et le bouton `Déverser` doivent encore être ajoutés/testés dans RH-FLOW.
- **Non déployé en production** : le serveur doit récupérer main, sauvegarder la base puis exécuter les migrations. Aucun résultat MySQL de production n'est revendiqué.


## Correction du 20/11/2026
- La durée du code de liaison maison est désormais sélectionnable : 3 mois, 6 mois, 1 an ou 2 ans ; 1 an par défaut. Il n'y a plus d'expiration fixe à 30 jours.
- Nouveau cahier des charges destiné à l'équipe RH-FLOW : `CAHIER_DES_CHARGES_RH_FLOW_LIAISON_COMPTAFLOW.md`.
- Ce document détaille le endpoint catalogue, la sélection/correspondance des dossiers, l'authentification Bearer, le bouton Déverser manuel, le payload, les contrôles et les tests de réception.


## Mise à jour — liaison bidirectionnelle et contrats de payload (20/11/2026)
- Le cahier RH-FLOW comprend maintenant la liaison initiée depuis RH-FLOW aussi bien que depuis COMPTAFLOW.
- Les payloads de pairing, token, référentiel, écritures et justificatifs ont été ajoutés à partir des contrôleurs COMPTAFLOW consultés.
- Deux cahiers des charges généraux ont été créés : applications maison Suite FLOW et applications tierces.
- Le catalogue COMPTAFLOW côté partenaire et l'endpoint de demande de liaison inverse sont identifiés comme endpoints à développer/valider. Le parcours bidirectionnel est donc spécifié, mais pas encore déclaré opérationnel de bout en bout.
- Les tests de paie avec un vrai payload RH-FLOW et la validation de production restent à effectuer.


## Bibliothèque documentaire Super Admin — 20/11/2026
- Les trois cahiers des charges sont désormais prévus dans l'interface Super Admin, sous la rubrique « Liaison COMPTA-APK » via « Documentation des intégrations ».
- Consultation du texte Markdown et téléchargement du fichier sont protégés par les middlewares `auth` et `superadmin`.
- Les fichiers sont servis depuis le répertoire du dépôt `docs/traitement recent/`, avec une liste blanche de noms fixes (aucun chemin de fichier fourni par l'utilisateur).
- Fichiers UI/serveur ajoutés : contrôleur documentaire, page de catalogue, page de lecture.
- Routes ajoutées : `/superadmin/documentation-integrations`, `/{slug}`, `/{slug}/telecharger` sous le groupe Super Admin.
- À vérifier après déploiement : menu visible, consultation des trois cahiers, téléchargement des trois fichiers. Aucun test runtime n'est revendiqué depuis GitHub seul.
- Aucun `php artisan migrate` requis pour cette fonctionnalité.


### Correctif avant déploiement — 20/11/2026
- Une relecture des fichiers ajoutés a révélé des séparateurs de namespace PHP doublés dans le contrôleur documentaire et les routes.
- Les séparateurs ont été corrigés dans les fichiers source.
- La compilation PHP/Blade et le chargement réel des routes doivent encore être confirmés sur un environnement PHP ou le serveur après récupération du commit.
 

## Mise à jour — interface Suite FLOW et erreur validityDays (20/11/2026)
- L'écran maison est organisé en deux niveaux : liste d'applications sous forme de boutons, puis espace détaillé de l'application sélectionnée.
- Les dossiers liés et l'historique sont filtrés par application ; les autres applications ne mélangent plus leurs dossiers sur le même écran.
- Le formulaire d'ajout est replié dans une action dédiée.
- La durée du code de liaison est choisie à la création et au renouvellement (90, 180, 365 ou 730 jours).
- Correction du défaut `$validityDays` non défini dans `store()` ; expiration, message flash et date affichée utilisent désormais la même durée.
- Contrôle effectué : fichiers et sections source relus sur GitHub. Tests PHP/Blade et vérification visuelle sur l'instance dev restent à exécuter après pull.


## Traitement du 26/11/2026 — Correctifs de comptabilité

Branche : `correction-cloture-resultat-dates-exercice-ecritures-acces-comptable`

Cette série concerne le passage du résultat et la clôture idempotente, la stratégie `auto` du compte 1301/1309, le nom de période sur deux années, la saisie masquée JJ/MM/AAAA, le partage des écritures et balances au niveau du dossier et le test de clôture sans résultat préalablement passé.

Les corrections de l'écran d'import et du message de scan restent à valider par analyse approfondie de leur contexte d'exercice. Les tests serveur n'ont pas été exécutés.


### Complément — import et scan (26/11/2026)
- Le formulaire d'import de journaux préselectionne l'exercice courant, affiche ses dates réelles et la validation du lot s'appuie sur l'exercice lié au dossier du lot, sans dépendre d'un contexte de société changé entre temps.
- La clôture est testée par un nouveau test feature qui passe directement à la clôture et vérifie qu'un résultat 1301/1309 est créé une seule fois.
- Le scan groupé reprend l'exercice de session côté serveur, même si l'onglet est resté ouvert avec un ancien identifiant ; les dates de facture restent contrôlées dans la période du nouvel exercice.
