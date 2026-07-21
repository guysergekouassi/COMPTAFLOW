# PLAN DE ROUTE — REFONTE SAISIE D'ÉCRITURES + NETTOYAGE (MISE A JOUR COMPTAFLOW)
**Date** : 21/07/2026

Sources analysées : `mise a jour-comptaflow.txt` (demandes initiales), `EXPLICATION-implementation (1).md` (résumé de la conception), `nouvelle-saisie-page.blade (1)/(2).php`, `saisie-grille (1)/(2).js`, `SCANNER.txt` (ajustement scanner), `page_saisie_et_liste_integree (2).html` (prototype autonome), `visuel_page_avec_bouton_scanner.html` (visuel en-tête), + vérification du code réel de l'application (`app/...`, `resources/views/...`, `routes/web.php`).

---

## 1. LES 4 DEMANDES INITIALES (fichier `mise a jour-comptaflow.txt`)

1. **Édition inline dans la liste des écritures** : cliquer "Modifier" sur une ligne doit permettre d'éditer chaque champ directement dans le tableau, sans ouvrir de formulaire séparé.
2. **Nouvelle saisie trop longue/complexe** : remplacer le formulaire vertical `accounting_entry_real.blade.php` par une vraie **grille façon tableur** (comme Sage), intégrée directement dans la page de liste — pas de changement de page.
3. **Lenteur** : le parcours actuel (modal → clic "Continuer" → attente → choix Manuel/Scanner → nouvelle page qui recharge) est lent et doit devenir quasi instantané, sans rechargements de page intermédiaires.
4. **Nettoyage** : supprimer complètement la page **"Factures Produites"** (vue, contrôleur, routes, entrées de menu).

La demande a évolué en cours de route (fichier `SCANNER.txt`) : garder un accès rapide au **scan de facture** (fonctionnalité existante et à conserver), simplement re-brancher son point d'entrée sur la nouvelle page combinée au lieu du modal `saisieRedirectModal`.

---

## 2. CE QUI A ÉTÉ CONÇU (résumé de `EXPLICATION-implementation (1).md`)

### Solution retenue
Une page combinée unique = **grille de saisie illimitée** (au-dessus) + **liste des écritures filtrée en JS instantanément par journal** (en dessous), sur la même page, remplaçant `accounting_entry_real.blade.php`.

### Règles métier réelles déjà respectées dans la conception (vérifiées dans le vrai code)
- `PlanTiers::compte()` → tiers filtré par compte général sélectionné (`belongsTo(PlanComptable::class, 'compte_general')`) — confirmé réel.
- Poste trésorerie activé seulement si le compte commence par `5` (classe 5) — logique reprise dans la grille JS (`estTresorerie`).
- `storeMultiple()` (endpoint réel `POST /api/ecritures/multiple` → `EcritureComptableController::storeMultiple`) groupe par `n_saisie` et valide l'équilibre **par groupe**, pas ligne à ligne — confirmé réel (lignes 613-628 du contrôleur), grille illimitée donc cohérente.
- `CodeJournal` porte `compte_de_contrepartie` / `compte_de_tresorerie` → utilisés pour la suggestion automatique de contrepartie.
- **Décision actée** : le blocage serveur 422 en cas de déséquilibre est **conservé tel quel** — rien à changer dans `storeMultiple()`. Toute la protection UX (carte d'avertissement, blocage fermeture panneau, `beforeunload`) se fait **côté front uniquement**.

### Fonctionnalités livrées dans les fichiers `(2)` (version la plus avancée)
- Grille illimitée, ajout auto de ligne (Tab/Entrée sur Crédit).
- Contrepartie automatique suggérée (mémoire locale `localStorage` + `compte_de_contrepartie` du journal).
- Boutons "+" (compte, tiers, poste) réutilisant les modales existantes (`modalCenterCreate`, `createTiersModal`, `modalCreatePoste`) via `window.fcInjecterElementCree()`.
- Exercice non modifiable (badge cadenas, celui de la session/sidebar).
- Date remplacée par `Mois` + `Jour` (année = celle de l'exercice actif).
- Liste des écritures **filtrée en JS pur** par journal sélectionné, sans requête réseau (`window.SAISIE_DATA.ecritures` injecté en JSON par le contrôleur).
- Écritures déséquilibrées visibles (point orange, ligne teintée, montants en orange) + chip filtre "Déséquilibrées uniquement".
- Pièce jointe déplacée sur la ligne libellé/référence, bouton stylé, explicitement facultative.
- **Bouton "Scanner facture"** (ajout du fichier `SCANNER.txt` / version `(2)`) : reproduit exactement la résolution `journaux_saisis.find` que faisait l'ancien modal `saisieRedirectModal`, puis redirige vers `ecriture.scan` avec le **même contrat de paramètres** qu'avant (`id_exercice`, `id_journal`, `annee`, `mois`, `code`, `type`, `intitule`, `id_code`) — **la page de scan existante n'a besoin d'aucune modification**.

---

## 3. COMPARAISON — CE QUI EST DÉJÀ EN PLACE EN RÉEL vs CE QUI RESTE À FAIRE

### 3.1 Contrôleur `EcritureComptableController::index()` (réel, vérifié)

| Élément attendu par la page combinée | État réel |
|---|---|
| `$plansComptables`, `$plansTiers`, `$comptesTresorerie`, `$exerciceActif`(implicite via `$data`), `$nextSaisieNumber`, `$ecritures` | ✅ Déjà fournis (avec `->with([...])` déjà en place : `planComptable`, `planTiers`, `compteTresorerie`, `posteTresorerie` — donc **pas de N+1**, contrairement à ce que signalait le point 10.2 du `.md`) |
| `$codeJournaux` (liste des `CodeJournal` de la company) | ❌ **absent** de `index()` — à ajouter |
| `$exerciceActif` explicite (objet, pas juste `$data['id_exercice']`) | ❌ absent — actuellement seule `$data['id_exercice']` existe, pas d'objet `ExerciceComptable` chargé et passé à la vue |
| `$modelesSaisie` (optionnel, "Appeler un modèle") | ❌ n'existe pas — aucune table/migration dédiée. Fonctionnalité **non prioritaire**, câblée seulement côté front (`appliquerModele()`), à considérer comme un "nice to have" plus tard |
| Vue retournée | `accounting_entry_real` (formulaire vertical actuel, **à remplacer**) |

### 3.2 Routes concernées (réelles)

| Route | Usage actuel | Action |
|---|---|---|
| `GET /accounting_entry_real` → `EcritureComptableController::index` | Affiche le formulaire vertical actuel | Vue à remplacer par la page combinée ; contrôleur à enrichir (`$codeJournaux`, `$exerciceActif`) |
| `POST /accounting_entry_real` → `storeMultiple` (name `storeMultiple.storeMultiple`) | Enregistrement classique | Inchangé |
| `POST /api/ecritures/multiple` → `storeMultiple` (name `api.ecriture.storeMultiple`) | Utilisé par la grille JS (`fetch`) | ✅ Déjà existant, correspond exactement à ce qu'utilise `saisie-grille.js` |
| `GET /ecriture-scan` (`ecriture.scan`) | Page de scan facture | Inchangée — juste le point d'entrée change |
| `GET /journaux_saisis/find` (`journaux_saisis.find`) | Résolution id `JournauxSaisis` | Inchangée, réutilisée par `ouvrirScanner()` |
| `GET /saisie-directe-modal` (`modal_saisie_direct`) + modal `#saisieRedirectModal` (sidebar) | Ancien point d'entrée "Nouvelle saisie" | **À retirer** une fois la page combinée en place (confirmé par l'échange : "le modal peut être retiré, tu as confirmé qu'il ne sert qu'à ça") |
| `factures-produites.*` (`index`, `store`, `show`, `destroy`, `download`) | Page "Factures Produites" | **À supprimer intégralement** (routes, contrôleur, modèle, migration, vues, entrées de menu) |

### 3.3 Sidebar (réelle, vérifiée)

- Lien **"Nouvelle saisie"** (`data-bs-target="#saisieRedirectModal"`) → à changer en lien direct vers `accounting_entry_real` (la nouvelle page combinée gère elle-même Journal/Mois/Scanner, plus besoin du modal en amont).
- Lien **"Factures Produites"** (route `factures_produites.index`) → **à supprimer**.
- Le modal `@include('components.modal_saisie_direct', [...])` (ligne 289) → **à retirer** de la sidebar une fois la bascule confirmée.
- Autres liens de la section "Traitement" (Centre de Scan, Analyse IA, Hub des Journaux, etc.) → inchangés.

### 3.4 Fichier "Factures Produites" — périmètre exact de suppression (déjà localisé)

| Fichier | Action |
|---|---|
| `app/Http/Controllers/FactureProduiteController.php` | Supprimer entièrement |
| `app/Models/FactureProduite.php` | Supprimer entièrement |
| `database/migrations/2026_04_13_011316_create_factures_produites_table.php` | Ajouter une migration `down()` de suppression de table (ou supprimer proprement selon la politique du projet — **à confirmer**, voir section 6) |
| `resources/views/factures_produites.blade.php` | Supprimer |
| `resources/views/components/sidebar.blade.php` (lien ligne ~760) | Retirer l'entrée de menu |
| `resources/views/excel_ia_projets.blade.php` (ligne ~122) et `resources/views/excel_ia.blade.php` (ligne ~698) | Retirer l'onglet "Factures Produites" (ces deux vues y font référence en dehors de la sidebar — **découverte importante, pas mentionnée dans la demande initiale**) |
| `routes/web.php` (bloc `Route::prefix('factures-produites')...`, lignes 155-161) | Supprimer le groupe de routes |

### 3.5 Édition inline dans la liste (demande n°1)

Cette demande initiale est en réalité **résolue différemment** par la solution finale retenue : au lieu de rendre chaque cellule de la liste éditable individuellement, le clic sur une ligne (ou l'icône crayon) **sélectionne tout le groupe (`n_saisie`)** et **rouvre le panneau de saisie en grille, prérempli avec les lignes du groupe** (point 6 du `.md`, comportement du prototype `page_saisie_et_liste_integree (2).html`). C'est plus cohérent avec la contrainte d'équilibre par groupe que de l'édition cellule-par-cellule isolée. **Ce comportement de préremplissage/édition de groupe existant dans le prototype n'est PAS encore présent dans `saisie-grille (2).js` réel** — c'est un point à combler (voir section 4, étape 3).

---

## 4. PLAN D'ACTION — ÉTAPES DANS L'ORDRE

### ÉTAPE 1 — Préparer le contrôleur réel `EcritureComptableController::index()`
- [ ] Ajouter `$codeJournaux = CodeJournal::where('company_id', $activeCompanyId)->get();`
- [ ] Résoudre explicitement un objet `$exerciceActif` (réutiliser la logique déjà présente lignes 51-64, mais conserver l'objet au lieu de ne garder que l'id dans `$data`).
- [ ] Ajouter ces deux variables (+ `$modelesSaisie` optionnel, à `null`/`[]` en attendant une vraie table) au `compact(...)` existant.
- [ ] Ne rien retirer de l'existant (`$plansComptables`, `$plansTiers`, `$ecritures` avec relations, etc. restent utiles à la nouvelle page).

### ÉTAPE 2 — Remplacer la vue `accounting_entry_real.blade.php`
- [ ] Remplacer le contenu par la fusion de `nouvelle-saisie-page.blade (2).php` (en-tête + panneau grille + liste), adapté aux vraies variables du contrôleur (`$plansComptables`, `$plansTiers`, `$comptesTresorerie`, `$exerciceActif`, `$nextSaisieNumber`, `$ecritures`, `$codeJournaux`).
- [ ] Conserver le style/charte graphique déjà utilisé dans les fichiers `(2)` (dégradé bleu, cartes arrondies) — cohérent avec la demande explicite "reste dans le style de ComptaFlow" et avec le style de tableau déjà approuvé ("j'aime bien le style de notre tableau... ne change pas ses badges").
- [ ] **Point d'attention relevé dans le `.md` (section 10.4, non traité)** : le callback de succès des modales existantes (`modalCenterCreate`, `createTiersModal`, `modalCreatePoste`) doit appeler `window.fcInjecterElementCree(type, item)` — à vérifier/ajouter dans le JS de ces modales (hors des fichiers fournis ici, à localiser dans l'app réelle).
- [ ] Reprendre EXACTEMENT les colonnes et badges de statut déjà utilisés dans `accounting_entry_list.blade.php` (`$badgeClass = match($status) ...`) pour la portion "liste" — ne pas réinventer un nouveau design de badge (consigne explicite de l'utilisateur).

### ÉTAPE 3 — Adapter/compléter `saisie-grille.js`
- [ ] Reprendre `saisie-grille (2).js` tel quel comme base (version la plus avancée : contrepartie auto, scanner, filtrage instantané, blocage déséquilibre).
- [ ] **Combler le trou identifié en 3.5** : implémenter la sélection de groupe + réouverture prérempli du panneau de saisie en cliquant une ligne/l'icône crayon de la liste (comportement démontré dans `page_saisie_et_liste_integree (2).html` mais absent du JS réel `(2)`) :
  - Au clic sur une ligne de `#listeEcrituresBody` (ou son icône éditer), retrouver toutes les lignes du même `n_saisie` dans `window.SAISIE_DATA.ecritures`, vider `#grilleBody`, appeler `ajouterLigne(prefill)` pour chacune, ouvrir le panneau, mettre à jour `#saisieTitre` (ex: "Modifier l'écriture ECR_...").
  - Décider du mode d'enregistrement en édition : soit un `update` par ligne (voir `EcritureComptableGroupesController::update`/`miseAJourMassive`, déjà existants et fonctionnels pour la mise à jour groupée), soit ré-utiliser `storeMultiple` avec suppression+recréation du groupe — **à trancher avant codage** (voir section 6, question ouverte).
- [ ] Vérifier que `window.SAISIE_DATA` (injecté par la vue) contient bien tous les champs utilisés par le JS : `plansComptables`, `plansTiers`, `comptesTresorerie`, `idExercice`, `csrfToken`, `storeMultipleUrl`, `journauxSaisisFindUrl`, `ecritureScanUrl`, `ecritures`.

### ÉTAPE 4 — Retirer le modal `saisieRedirectModal` et rebrancher la sidebar
- [ ] Dans `resources/views/components/sidebar.blade.php` : remplacer le lien `data-bs-toggle="modal" data-bs-target="#saisieRedirectModal"` par un lien direct `href="{{ route('accounting_entry_real') }}"`.
- [ ] Retirer l'`@include('components.modal_saisie_direct', [...])` de la sidebar (ligne ~289).
- [ ] **Ne pas supprimer le fichier `modal_saisie_direct.blade.php` immédiatement** — le garder de côté un cycle de validation, au cas où un rollback rapide soit nécessaire (à supprimer définitivement une fois la nouvelle page validée en usage réel).

### ÉTAPE 5 — Nettoyage complet "Factures Produites"
- [ ] Supprimer `app/Http/Controllers/FactureProduiteController.php`.
- [ ] Supprimer `app/Models/FactureProduite.php`.
- [ ] Retirer le groupe de routes `factures-produites` dans `routes/web.php`.
- [ ] Retirer l'entrée de menu dans `resources/views/components/sidebar.blade.php`.
- [ ] Retirer les onglets "Factures Produites" dans `resources/views/excel_ia_projets.blade.php` et `resources/views/excel_ia.blade.php` (découverte de l'analyse, non mentionnée initialement).
- [ ] Supprimer `resources/views/factures_produites.blade.php`.
- [ ] Traiter la migration `database/migrations/2026_04_13_011316_create_factures_produites_table.php` selon la politique retenue (voir question ouverte en section 6) — a minima, ne pas la supprimer sans avoir décidé si la table doit être droppée en prod ou juste laissée orpheline sans code applicatif.

### ÉTAPE 6 — Tests de validation
- [ ] Parcours "Nouvelle saisie" complet : ouverture panneau instantanée (plus de rechargement de page), ajout de lignes, contrepartie auto, équilibrage, `storeMultiple` OK.
- [ ] Parcours "Scanner facture" : vérifier que `journaux_saisis.find` renvoie bien l'id `JournauxSaisis` attendu et que la redirection vers `ecriture.scan` reçoit exactement les mêmes paramètres qu'avec l'ancien modal (comparer les deux payloads).
- [ ] Parcours "modifier une écriture existante" (le point comblé à l'étape 3) : sélection d'un groupe dans la liste → préremplissage correct → enregistrement → pas de duplication de lignes.
- [ ] Filtrage par journal dans la liste : bien instantané, sans requête réseau (vérifier absence d'appel XHR au changement de journal).
- [ ] Blocage déséquilibre : impossible de fermer le panneau ou d'enregistrer si débit ≠ crédit ; message clair ; `beforeunload` déclenché si on tente de quitter la page.
- [ ] Vérifier qu'aucune référence à "Factures Produites" ne subsiste nulle part (recherche globale après suppression).
- [ ] Vérifier que le lien direct "Nouvelle saisie" dans la sidebar fonctionne sans passer par le modal supprimé.

---

## 5. RÉCAPITULATIF DES FICHIERS CONCERNÉS

| Fichier | Nature du changement |
|---|---|
| `app/Http/Controllers/EcritureComptableController.php` (`index`) | Enrichir (`$codeJournaux`, `$exerciceActif` objet) |
| `resources/views/accounting_entry_real.blade.php` | Remplacer entièrement par la page combinée |
| `public/js/saisie-grille.js` (nouveau, ou emplacement JS existant) | Créer/déployer à partir de `saisie-grille (2).js`, complété (étape 3) |
| `resources/views/components/sidebar.blade.php` | Modifier lien "Nouvelle saisie", retirer include modal, retirer lien "Factures Produites" |
| `resources/views/components/modal_saisie_direct.blade.php` | À terme supprimable (garder en attente de validation) |
| `app/Http/Controllers/FactureProduiteController.php`, `app/Models/FactureProduite.php`, `resources/views/factures_produites.blade.php` | Supprimer |
| `routes/web.php` | Retirer bloc `factures-produites` |
| `resources/views/excel_ia.blade.php`, `resources/views/excel_ia_projets.blade.php` | Retirer onglet "Factures Produites" |
| `database/migrations/..._create_factures_produites_table.php` | Décision à prendre (voir section 6) |
| `app/Http/Controllers/EcritureComptableGroupesController.php` | Potentiellement réutilisé pour l'édition de groupe existant (étape 3) — à confirmer selon le choix retenu |

---

## 6. QUESTIONS OUVERTES À TRANCHER AVANT DE CODER

1. **Édition d'un groupe existant** (étape 3) : réutiliser `EcritureComptableGroupesController::update`/`miseAJourMassive` (déjà présents et fonctionnels), ou repasser par `storeMultiple` avec suppression + recréation du groupe ? Le premier semble plus sûr (pas de perte d'id, pas de recréation de `JournalSaisi`), mais son contrat de champs est à vérifier avant de brancher le front.
2. **Migration `factures_produites`** : faut-il la conserver (au cas où) et juste retirer le code applicatif, ou aller jusqu'au `Schema::dropIfExists` en production ? Impacte aussi les fichiers physiquement uploadés dans `storage/app/.../factures_produites/{company}/{annee}/{mois}` — à décider si suppression des fichiers ou conservation.
3. **Cas "exercice non verrouillé en session"** (`$isContextLocked = false` du modal d'origine) : la page combinée affiche toujours l'exercice en badge grisé/non modifiable. Ce cas (choix libre de l'exercice) existe-t-il encore réellement dans l'usage actuel de l'app, ou l'exercice est-il désormais toujours verrouillé par le contexte de session ? Cette question posée dans l'échange précédent (fin de `SCANNER.txt`) n'a pas encore de réponse actée.
4. **Table "Modèles de saisie"** (`ecriture_modeles`) : fonctionnalité "Appeler un modèle" câblée côté front mais sans support serveur — à confirmer si utile pour une V1, ou repoussée à plus tard.

---

## 7. STATUT

Aucune modification n'a encore été appliquée dans le vrai code de l'application à ce stade — ce document est la **feuille de route de départ** pour ce chantier, à l'image de ce qui a été fait pour `PLAN/ANALYTIQUE/TERMINER-ANALYTIQUE`. Prochaine action recommandée : trancher les questions de la section 6, puis démarrer l'Étape 1.
