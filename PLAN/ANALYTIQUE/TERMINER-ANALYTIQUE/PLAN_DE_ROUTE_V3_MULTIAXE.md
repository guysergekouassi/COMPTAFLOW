# PLAN DE ROUTE V3 — IMPORT ANALYTIQUE MULTI-AXE (version définitive)
**Date** : 20/07/2026 — **Mise à jour : ÉTAPES 1 et 2 APPLIQUÉES EN RÉEL** ✅
**⚠️ CE PLAN REMPLACE `PLAN_DE_ROUTE_FINAL_ANALYTIQUE.md`** (qui posait encore la question "mono-axe ou multi-axe ?" comme non tranchée — c'est maintenant tranché : **multi-axe confirmé et déjà géré côté saisie manuelle**).

Sources analysées : `plan_module_analytique_flowcompta.md`, `AdminConfigController (2)/(4).php`, `ImportCommitJob (2)/(4).php`, + vérification du code réel de l'application (`app/...`).

## STATUT D'AVANCEMENT (mis à jour)

| Étape | Statut |
|---|---|
| Étape 1 — `AdminConfigController.php` réel mis à jour (dictionnaire multi-axe, `$processedRow`, `$sectionsByCode`, aperçu staging) | ✅ **FAIT** |
| Étape 2 — `ImportCommitJob.php` réel mis à jour (résidus A/G supprimés, `$sectionsByCode`, résolution `section_ids[]`, insertion hybride) | ✅ **FAIT** |
| Vue de mapping (blade) | ✅ **Rien à faire** — `import_mapper.blade.php` boucle dynamiquement sur `$fields`, les 6 nouveaux champs analytiques s'affichent automatiquement sans modification |
| Étape 3 — Vérifications post-application | ✅ confirmées (relation `ventilations()` déjà correcte) |
| Étape 4 — Décision clé de résolution section (unicité code entre axes) | ✅ **FAIT** — aucun conflit trouvé, résolution par code seul confirmée sûre |
| Étape 5 — Tests avec fichier réel (4 scénarios) | ✅ **FAIT** — tous validés avec succès |

---

## 1. CE QUI A CHANGÉ PAR RAPPORT AU PLAN PRÉCÉDENT

1. **Décision tranchée** : le modal de saisie manuelle gère déjà le **multi-axe** — chaque axe est validé indépendamment à 100% (pas un total global). L'import doit rester cohérent avec ça.
2. **Conséquence sur l'import** : une même ligne générale peut porter **jusqu'à 3 paires** `axe_analytique(_2/_3)` / `section_analytique(_2/_3)`. Chaque paire renseignée crée **sa propre `VentilationAnalytique` indépendante**, toujours à 100% du montant de la ligne (pas un partage entre axes).
3. Le format Sage 100 standard n'expose qu'**une seule paire** par ligne — les paires 2 et 3 ne servent que si la source de données est enrichie. Aucune régression si un seul axe est utilisé.

---

## 2. COMPARAISON — ÉTAT RÉEL DE L'APP vs FICHIERS FOURNIS (2)

### 2.1 Bonnes surprises : déjà fait en réel, alors que `plan_module_analytique_flowcompta.md` le listait comme "à vérifier/manquant"

| Point listé comme "à faire" dans le plan .md | Statut réel vérifié |
|---|---|
| Fix `RegleVentilation` import manquant dans `VentilationController.php` | ✅ **Déjà présent** (`use App\Models\RegleVentilation;` ligne 9) |
| Relation `EcritureComptable::ventilations()` | ✅ **Déjà présente** (ligne 107, `hasMany(VentilationAnalytique::class, 'ecriture_id')`) |
| Validation serveur "somme ventilations = 100%" à l'écriture manuelle | ✅ **Déjà présente** : `EcritureComptableController::validateVentilations()` (ligne 1261), appelée aux lignes 448 et 646. Gère déjà le **multi-axe** (`groupedByAxe`, contrôle 100% par axe) |
| Protection suppression Axe si sections liées | ✅ **Déjà présente** (`AxeAnalytiqueController::destroy()`) |
| Protection suppression Section si ventilations/règles liées | ✅ **Déjà présente** (`SectionAnalytiqueController::destroy()`) |

➡️ **Rien à faire sur ces 5 points.** Le document `.md` fourni était donc partiellement obsolète/pessimiste sur l'état réel — bon réflexe de l'avoir vérifié plutôt que de refaire ce qui existe déjà.

### 2.2 Ce qui n'est PAS encore appliqué en réel (confirmé par recherche vide dans `app/...`)

| Élément | Fichier réel (`app/...`) | Fichiers fournis `(2)` | Action requise |
|---|---|---|---|
| Dictionnaire `courant` avec `axe_analytique`/`section_analytique` (+ `_2`/`_3`) | ❌ contient encore `type_ecriture` (A/G) | ✅ présent, 3 paires | **À appliquer** |
| Préchargement `$sectionsByCode` dans `AdminConfigController` (staging) | ❌ absent | ✅ présent | **À appliquer** |
| Résolution section en aperçu (staging), message "Ventilée sur : ..." | ❌ absent (message "Ignorée" trompeur) | ✅ présent, gère les 3 paires | **À appliquer** |
| Préchargement `$sectionsByCode` dans `ImportCommitJob` | ❌ absent | ✅ présent | **À appliquer** |
| Résolution `section_ids[]` (jusqu'à 3) en Phase 1 du job | ❌ absent | ✅ présent | **À appliquer** |
| Insertion hybride Phase 5 (masse / unitaire + `ventilations()->create()` par section) | ❌ absent | ✅ présent | **À appliquer** |

### 2.3 ⚠️ Incohérence encore présente dans `ImportCommitJob (2).php` (fichier fourni) — à corriger avant d'appliquer

Le fichier `(2)` contient **toujours** les 2 blocs résiduels de l'ancienne logique A/G, qui contredisent la conclusion multi-axe/pas-de-flag :

```php
// ── Type A (Analytique) → ignorer ──
if (isset($rowMapped['type_ecriture']) && strtoupper(trim($rowMapped['type_ecriture'])) === 'A') {
    $report['filtered_a']++;
    continue;
}

// ── Détection des lignes analytiques cachées (colonne hors mapping) ──
$isHiddenA        = false;
$mappedColIndexes = array_filter(array_values($mapping), fn($v) => is_numeric($v));
foreach ($rowOrig as $colIdx => $cellVal) {
    if (in_array($colIdx, $mappedColIndexes)) continue;
    $v = strtoupper(trim($cellVal ?? ''));
    if ($v === 'A' || $v === 'ANALYTIQUE') { $isHiddenA = true; break; }
}
if ($isHiddenA) { $report['filtered_a']++; continue; }
```

Ces deux blocs doivent être **supprimés** au moment de l'application (le champ `type_ecriture` n'existe plus dans le dictionnaire de mapping `(2)`, donc `$rowMapped['type_ecriture']` sera systématiquement vide — le bloc est mort mais reste un résidu trompeur et le second bloc reste dangereux pour de vraies lignes générales avec une colonne parasite valant "A").

De même, dans `AdminConfigController (2).php`, `$processedRow` initialise encore `'type_ecriture' => null,` (ligne ~2459, résidu sans effet mais à nettoyer par cohérence).

---

## 3. PLAN D'ACTION — ÉTAPES DANS L'ORDRE

### ÉTAPE 1 — ✅ FAIT : Appliqué dans le fichier réel `app/Http/Controllers/Admin/AdminConfigController.php`
- [x] Remplacé le bloc `'type_ecriture' => [...]` du dictionnaire `courant` par les **6 champs** (`axe_analytique`, `section_analytique`, `axe_analytique_2`, `section_analytique_2`, `axe_analytique_3`, `section_analytique_3`).
- [x] Remplacé `'type_ecriture' => null,` dans `$processedRow` par les 6 clés équivalentes.
- [x] Ajouté le préchargement `$sectionsByCode` (juste avant `// --- DICTIONNAIRES DE CORRESPONDANCE (AUTO-LOOKUP) ---`).
- [x] Remplacé la logique d'aperçu (staging) : boucle sur `['', '_2', '_3']`, résolution de chaque `section_analytique{suffixe}`, erreur bloquante si code inconnu, sinon accumulation dans `section_resolue_info`. L'ancien bloc "Ignorée (analytique - type A)" a été supprimé.
- [x] Vue de mapping (blade) : **aucune modification nécessaire** — `import_mapper.blade.php` génère les champs dynamiquement via `@foreach($fields as $key => $field)`, les 6 nouveaux champs apparaissent automatiquement.

### ÉTAPE 2 — ✅ FAIT : Appliqué dans le fichier réel `app/Jobs/ImportCommitJob.php`, résidus supprimés
- [x] Ajouté `use App\Models\SectionAnalytique;`.
- [x] Préchargé `$sectionsByCode` (juste après `$journalIdToCode`).
- [x] **Supprimé** les 2 blocs résiduels A/G (`type_ecriture === 'A'` et `isHiddenA`) qui existaient encore dans le fichier réel.
- [x] Ajouté le bloc de résolution multi-section (`foreach (['', '_2', '_3'] as $suffixe) ...`) juste avant `$mappedRows[]`, avec `'section_ids' => $sectionsIdsResolues,`.
- [x] Remplacé la Phase 5 (accumulation batch) par la version hybride : test `$groupeAAnalytique` sur `!empty($r['section_ids'])` ; chemin rapide `insert()` en masse conservé si aucune section ; sinon `create()` unitaire + boucle `foreach ($r['section_ids'] as $sectionIdA) { $ecriture->ventilations()->create([...]); }`.
- [x] Vérification syntaxique (`get_errors`) : aucune erreur sur les deux fichiers.

### ÉTAPE 3 — Vérifications post-application (rien à créer, juste confirmer)
- [ ] Confirmer que `EcritureComptable::ventilations()` reste inchangée (déjà correcte).
- [ ] Confirmer que `validateVentilations()` (saisie manuelle) n'a pas besoin d'adaptation — l'import ne passe pas par ce contrôleur, donc pas d'interférence, mais bonne pratique de vérifier la cohérence des rapports après import (étape 5).

### ÉTAPE 4 — ✅ FAIT : Décision sur la clé de résolution des sections
Vérification exécutée en base réelle (script temporaire, supprimé après usage) :
```
Total sections analytiques : 1
Groupes (company+code) avec plusieurs axes différents : 0
=> AUCUN CONFLIT : chaque code de section est unique par société, tous axes confondus.
```
**Conclusion retenue** : la résolution par code seul (`$sectionsByCode[$sectionCode]`, sans croiser l'axe) reste sûre en l'état actuel des données. À ré-exécuter cette vérification si le volume de sections augmente significativement ou avant une mise en production à grande échelle.

### ÉTAPE 5 — ✅ FAIT : Tests de validation (4 scénarios réels via `ImportCommitJob::handle()`)
Un script de test a créé de vraies lignes de `ImportStaging`, exécuté le job réel, vérifié le résultat en base, puis tout nettoyé.

| Scénario | Contenu | Résultat |
|---|---|---|
| **A** — Général pur | 2 lignes, aucune section | ✅ `committed`, 2 écritures créées, **aucune** `VentilationAnalytique` (chemin rapide `insert()` en masse confirmé) |
| **B** — 1 axe renseigné | Ligne charge avec `section_analytique=CO1` | ✅ `committed`, écriture avec `plan_analytique=1` + 1 `VentilationAnalytique` (section_id=1, montant=200000, 100%) |
| **C** — 2 axes en parallèle | Ligne charge avec `section_analytique=CO1` **et** `section_analytique_2=PRJ_A` (axe différent) | ✅ `committed`, écriture avec **2** `VentilationAnalytique` indépendantes, chacune à 100% du même montant (300000) — confirme le principe "pas de partage entre axes" |
| **D** — Code section inconnu | `section_analytique=ZZZZZ` | ✅ `status=error`, message bloquant explicite `"Section analytique 'ZZZZZ' (axe 1) introuvable..."`, **aucune écriture créée** (transaction annulée) |

**Conclusion** : les 4 comportements attendus sont validés à l'identique de ce que prévoyait le plan. Reste, en conditions réelles de production (hors périmètre de ce test local) :
- [ ] Test de volumétrie (plusieurs milliers de lignes) pour confirmer l'absence de régression de perf sur le chemin `insert()` en masse.
- [ ] Test avec un véritable fichier d'export Sage 100 (colonnes réelles `N° plan analytique` / `N° section`) au moment où un tel fichier sera disponible.
- [ ] Vérifier visuellement les rapports analytiques (Balance / Grand Livre / Résultat par section) après un import réel en conditions utilisateur (via l'interface, pas seulement en script).

---

## 4. HORS SCOPE (confirmé, pour plus tard)

- **Import "ventilation seule"** (fichier contenant uniquement des lignes analytiques venant ventiler des écritures déjà en base) — section 7 du `.md`, type d'import distinct (`type = 'ventilation_analytique'`). À ne démarrer qu'une fois la section 6 (import combiné) stabilisée et testée en conditions réelles avec un vrai export Sage.

---

## 5. RÉCAPITULATIF — CHECKLIST GLOBALE DE MISE EN PRODUCTION

1. [x] Fix `RegleVentilation` (déjà en place, vérifié)
2. [x] Relation `EcritureComptable::ventilations()` (déjà en place, vérifié)
3. [x] Validation serveur multi-axe pour la saisie manuelle (déjà en place, vérifié)
4. [x] Protection suppression Axes/Sections (déjà en place, vérifié)
5. [x] Appliquer le dictionnaire de mapping multi-axe dans `AdminConfigController.php` réel (Étape 1) — **FAIT**
6. [x] Appliquer la résolution + insertion hybride multi-axe dans `ImportCommitJob.php` réel, **sans** les résidus A/G (Étape 2) — **FAIT**
7. [x] Vérifier l'unicité des codes de section entre axes en base réelle (Étape 4) — **FAIT, aucun conflit**
8. [x] Vue blade de mapping — **rien à faire**, génération dynamique confirmée
9. [x] Dérouler les tests de l'Étape 5 (4 scénarios : général seul, 1 axe, 2 axes en parallèle, section inconnue) — **FAIT, tous validés**
10. [ ] Test de volumétrie + test avec un vrai fichier d'export Sage 100 (à faire dès qu'un fichier réel est disponible)
11. [ ] Une fois stabilisé : envisager l'import "ventilation seule" (section 4 du document `.md`) si le besoin se confirme

## ✅ STATUT GLOBAL : IMPORT ANALYTIQUE COMBINÉ TERMINÉ ET VALIDÉ

Le cœur du chantier (import général + analytique multi-axe simultané) est **implémenté dans le vrai code, testé et fonctionnel**. Il ne reste que des vérifications de robustesse en conditions réelles (volumétrie, vrai fichier Sage) avant mise en production complète.

**Fichiers à modifier concrètement** :
- `app/Http/Controllers/Admin/AdminConfigController.php`
- `app/Jobs/ImportCommitJob.php`
- Vue blade de mapping d'import (à identifier : `resources/views/admin/config/*mapping*.blade.php`)

**Aucune migration, aucun nouveau modèle** : tout le socle de données (axes, sections, ventilations, règles) est déjà en place et correct.
