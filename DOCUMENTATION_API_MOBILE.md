# Documentation Complète des API et Routes (ComptaFlow)

Ce document liste de manière exhaustive toutes les routes (APIs et Web) exposées par l'application, classées par section logique. L'application mélange des routes d'API dédiées (`/api/v1/*`) pour un usage mobile ou SPA, et des routes web classiques pour le back-office.

---

## 1. APIs Mobiles Dédiées (`/api/v1/*`)
Ces routes retournent exclusivement du JSON et utilisent des tokens Sanctum pour l'authentification.

### Authentification & Utilisateur
* `POST /api/v1/login` : Authentification
* `POST /api/v1/logout` : Déconnexion
* `GET /api/v1/user` : Infos utilisateur connecté
* `GET /api/v1/dashboard` : Données du tableau de bord

### Notifications & Tâches
* `GET /api/v1/notifications` | `POST /api/v1/notifications`
* `GET /api/v1/notifications/unread-count`
* `POST /api/v1/notifications/{id}/mark-as-read`
* `GET /api/v1/tasks` | `POST /api/v1/tasks` | `DELETE /api/v1/tasks/{id}`
* `GET /api/v1/tasks/daily`
* `POST /api/v1/tasks/{id}/complete` : Marquer une tâche comme terminée

### Exercices & Approbations
* `GET /api/v1/exercices` | `GET /api/v1/exercices/active`
* `GET /api/v1/exercices/by-email/{email}`
* `GET /api/v1/approvals` | `POST /api/v1/approvals/{id}/handle`

### Saisie & Écritures
* `GET /api/v1/entries` | `POST /api/v1/entries`
* `GET /api/v1/entries/drafts` | `GET /api/v1/entries/rejected`
* `POST /api/v1/entries/multiple`
* `GET /api/v1/entries/{n_saisie}` | `DELETE /api/v1/entries/{n_saisie}`
* `POST /api/v1/scan` : Reconnaissance IA (Factures)

### Paramétrage Comptable
* `GET / POST /api/v1/accounting/plan-comptable` : Comptes généraux
* `GET / POST /api/v1/accounting/plan-tiers` : Tiers
* `GET / POST /api/v1/accounting/journals` : Journaux
* `GET / POST /api/v1/accounting/treasury-categories` : Catégories trésorerie
* `GET / POST /api/v1/accounting/treasury-posts` : Postes de trésorerie
* `DELETE /api/v1/accounting/{type}/{id}` : Suppression générique

### Analytique & Immobilisations
* Axes, Sections, Règles, Ventilations : `GET / POST /api/v1/analytique/*`
* Immobilisations : `GET /api/v1/immobilisations` | `GET /api/v1/immobilisations/{id}` | `GET /api/v1/immobilisations/{id}/amortissements`
* Lettrage : `GET / POST /api/v1/lettrage`

### Rapports Financiers
* Balance, Grand-livre, Bilan, Résultat, TFT : `GET /api/v1/reports/*`
* Rapports analytiques : `GET /api/v1/reports/analytique/*`

---

## 2. Authentification Web & Sessions (Back-office)
Ces routes gèrent l'accès standard sur navigateur.
* `GET /` | `GET /index` : Page d'accueil / redirection
* `GET /login` | `POST /login` : Connexion Web
* `POST /logout` : Déconnexion Web
* `GET /sanctum/csrf-cookie` : Initialisation CSRF (SPA)

---

## 3. Paramètres Utilisateur & Profil
* `GET /profile` : Profil Utilisateur
* `GET /settings` : Paramètres Utilisateur
* `PUT /settings/account` : Mise à jour du compte
* `PUT /settings/password` : Modification mot de passe
* `POST /settings/avatar` : Modification avatar

---

## 4. Gestion des Exercices Comptables
* `GET / POST /exercice_comptable` : Liste et création des exercices
* `GET / PUT / DELETE /exercice_comptable/{id}` : Actions spécifiques sur un exercice
* `PATCH /exercice_comptable/{id}/cloturer` : Clôture de l'exercice
* `PATCH /exercice_comptable/{id}/reouvrir` : Réouverture
* `POST /exercice_comptable/{id}/activate` | `GET /exercice_comptable/{id}/switch` : Changement d'exercice actif

---

## 5. Plan Comptable & Journaux
* `GET / POST /plan_comptable` | `GET /plan_comptable/datatable` : Gestion du plan comptable général
* `PUT / DELETE /plan_comptable/{id}`
* `POST /plan_comptable/use-default` : Charger le plan Syscohada par défaut
* `GET / POST / PUT / DELETE /accounting_journals` : Paramétrages des Codes Journaux
* `GET / POST / PUT / DELETE /plan_tiers` : Paramétrages du plan des Tiers
* `GET /plan_tiers/{racine}` : Trouver le dernier numéro

---

## 6. Saisie des Écritures & Consultation
* `GET / POST / PUT / DELETE /ecriture` : Écritures simples
* `GET /ecriture/{id}` | `GET /ecriture/{ecriture}/edit`
* `POST /ecritures-comptables/store` : Sauvegarde multiple d'écritures
* `DELETE /ecritures/saisie/{n_saisie}` : Suppression par numéro de pièce
* `GET /ecriture-scan` | `GET /bulk-scan` | `POST /ia-traitement` : Saisie assistée par l'Intelligence Artificielle
* `GET /accounting_entry_real` : Livre journal - Vue détaillée

---

## 7. Trésorerie & Opérations Bancaires
* `GET / POST / PUT / DELETE /tresorerie` : Journaux de trésorerie
* `GET / POST / PUT / DELETE /gestion_tresorerie` : Gestion des flux de trésorerie
* `GET / POST / PUT / DELETE /poste` : Paramétrage des postes bancaires / caisse
* `GET / POST /mouvement/store` : Saisie des mouvements de trésorerie

---

## 8. Immobilisations & Amortissements
* `GET / POST / PUT / DELETE /immobilisations` : Fiches d'immobilisations
* `POST /immobilisations/generer-dotations` : Lancer le calcul d'amortissement
* `POST /immobilisations/{id}/ceder` : Sortie d'actif
* `GET /immobilisations/{id}/tableau` : Export du tableau d'amortissement

---

## 9. Comptabilité Analytique
* `GET / POST / PUT / DELETE /analytique/axes` : Gestion des axes
* `GET / POST / PUT / DELETE /analytique/sections` : Gestion des centres/sections
* `GET / POST /analytique/regles` : Définitions d'affectation d'une clé de répartition

---

## 10. Lettrage des Comptes
* `GET / POST / DELETE /comptabilite/lettrage` : Module de Lettrage automatique ou manuel pour rapprocher factures et paiements

---

## 11. Rapports, États Financiers et Liasse Fiscale
* **Balance** : `GET /accounting_balance` (Générale), `GET /accounting_balance_tiers` (Auxiliaire)
* **Grand-livre** : `GET /accounting_ledger` (Général), `GET /accounting_ledger_tiers` (Auxiliaire)
* **Compte de Résultat** : `GET /reporting/resultat`, `GET /reporting/monthly-resultat`
* **Bilan** : `GET /reporting/bilan`
* **TFT (Tableau de Flux de Trésorerie)** : `GET /reporting/tft`, `GET /reporting/tft-personalized`
* **Liasse Fiscale Complète** : `GET /reporting/liasse`, `GET /reporting/liasse/{regime}`
* Liasse Exports (Excel/PDF) : `GET /reporting/liasse/{regime}/export/{format}`

---

## 12. Mode Administrateur Principal (Cabinet / Admin Hub)
Les routes sous `/admin/*` permettent la configuration globale pour une entreprise donnée.
* `GET /admin/dashboard` : Tableau de bord administratif
* `GET /admin/users` | `GET /admin/habilitations` : Contrôle d'accès et droits (RBAC)
* `GET / POST /admin/config/*` : Configuration lourde (Import de plans standardisés, resets, etc.)
* **Imports massifs (Hub)** : `GET / POST /admin/import/*` pour envoyer des fichiers CSV/Excel de journaux, tiers, plans de comptes.
* `POST /admin/companies/*` : Gestion entités clientes

---

## 13. Mode Super Administrateur (Plateforme Globale)
Les routes sous `/superadmin/*` gèrent plusieurs cabinets ou entreprises globales, ainsi que les paramétrages lourds et facturations.
* `GET /superadmin/dashboard`
* `GET / POST / PUT / DELETE /superadmin/companies` : Toutes les entreprises de la plateforme
* `GET / POST / PUT / DELETE /superadmin/users` | `/superadmin/admins`
* `POST /superadmin/switch/company/{id}` : Prise de contrôle direct d'une entreprise (Bypasse)
* `GET /admin/impersonate/{user}` : Se connecter ("Assumer l'identité") en tant qu'un utilisateur spécifique.

---

> Ce document liste la fonctionnalité globale de l'API. Chaque section correspond à un contrôleur spécifique dans l'architecture MVC Laravel du backend. Pour interfacer l'application mobile, vous devriez vous référer  prioritairement à la section **1. APIs Mobiles Dédiées**. Les autres routes concernent l'interface web (SSR/Views).
