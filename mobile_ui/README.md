# COMPTAFLOW Mobile UI - Mappage API

Ce dossier contient les interfaces Flutter correspondant **uniquement** aux modules API implémentés pour la version mobile.

## 🔗 Mappage des Écrans aux Endpoints API

| Écran Flutter | Module Système | Endpoint API Consommé |
| :--- | :--- | :--- |
| `dashboard_page.dart` | Pilotage | `GET /api/v1/dashboard` |
| `notifications_page.dart` | Pilotage | `GET /api/v1/notifications` |
| `approvals_page.dart` | Pilotage | `GET /api/v1/approvals` |
| `tasks_page.dart` | Pilotage | `GET /api/v1/tasks` |
| `entries_list_page.dart` | Traitement | `GET /api/v1/entries` (Toutes, Rejets, Brouillons) |
| `new_entry_page.dart` | Traitement | `POST /api/v1/entries` & `POST /api/v1/scan` |
| `reports_pages.dart` | États Financiers | `GET /api/v1/reports/bilan`, `/resultat`, `/tft`, `/balance` |
| `immo_pages.dart` | Immobilisations | `GET /api/v1/immobilisations` |
| `analytique_pages.dart` | Analytique | `GET /api/v1/reports/analytique/*` & `/api/v1/analytique/rules` |
| `accounting_config_pages.dart`| Paramétrage | `GET /api/v1/accounting/plan-comptable`, `plan-tiers`, `journals`, `treasury-posts` |

## 🚫 Écrans Exclus (Web Uniquement)
- **Dossier de Configuration** : Les réglages structurels avancés de l'entreprise s'effectuent sur le Web.
- **Gestion des Utilisateurs** : La création/blocage des comptes reste une action administrative Web.
- **Imports/Exports massifs** : Les traitements de fichiers Excel s'effectuent via le portail Web.
