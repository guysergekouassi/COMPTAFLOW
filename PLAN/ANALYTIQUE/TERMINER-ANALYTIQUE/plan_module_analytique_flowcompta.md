# Plan d'implémentation — Module Analytique (Flow Compta)

> Document de référence à donner à une IA (Claude Code ou autre) ou à un développeur pour reprendre le travail sur le module analytique. Reflète l'état réel du code au [date de ce document] et les décisions déjà prises.

---

## 1. Contexte

Flow Compta est une application Laravel de comptabilité générale + analytique, calquée sur les conventions Sage 100. La **compta générale fonctionne et est en production**. La **compta analytique est en cours d'implémentation** — architecture posée, mais incomplète et partiellement buguée.

Deux axes de travail sont concernés :
1. La **gestion analytique manuelle** (saisie, règles de ventilation automatique, rapports).
2. **L'import de données analytiques** depuis Sage (ou autre source externe), en parallèle de l'import de compta générale déjà fonctionnel.

---

## 2. Modèle de données (existant, validé)

```
axes_analytiques (id, code, libelle, company_id)
        │ 1—N
        ▼
sections_analytiques (id, axe_id, code, libelle, company_id)
        │ 1—N
        ▼
ventilations_analytiques (id, ecriture_id, section_id, montant, pourcentage)
        │ N—1
        ▼
ecriture_comptables (ligne générale : compte, débit, crédit, journal, date...)

regles_ventilation (compte_id, section_id, pourcentage_defaut, company_id)
   → applique automatiquement une clé de répartition à la saisie manuelle
   pour les comptes de charges indirectes (ex: charges communes à répartir)
```

**Principe non négociable** : une ventilation analytique ne peut jamais exister sans la ligne générale (`ecriture_id`) à laquelle elle est rattachée. Aucune donnée analytique "flottante".

---

## 3. Bugs déjà identifiés (à vérifier/corriger si pas encore fait)

- [ ] **`VentilationController::storeRule()`** — utilisait `RegleVentilation::` sans l'import `use App\Models\RegleVentilation;` en haut du fichier `app/Http/Controllers/Analytique/VentilationController.php`. Provoquait une erreur fatale à la création de toute règle de ventilation. **À reconfirmer que le correctif est bien appliqué.**

---

## 4. Décision architecturale : multi-axe supporté (corrigé)

**Mise à jour** : le modal de ventilation manuelle gère **déjà correctement** le multi-axe — chaque axe est validé indépendamment à 100% (pas un total global toutes sections confondues). L'hypothèse "mono-axe pour l'instant" posée initialement dans ce document n'est donc plus d'actualité et **l'import a été adapté en conséquence** pour rester cohérent avec la saisie manuelle.

### Principe retenu pour l'import (identique à la logique du modal)

Une même ligne générale peut porter une ventilation sur **plusieurs axes en parallèle**. Chaque axe est ventilé **indépendamment à 100% du montant de la ligne** — ce n'est pas un partage du montant entre les axes, mais bien N ventilations complètes et distinctes (une par axe), toutes rattachées à la même ligne générale.

Exemple : une charge de 1 200 000 peut donner lieu à :
- une `VentilationAnalytique` de 1 200 000 sur la section "Cocody" (axe Point de vente)
- **et en même temps** une `VentilationAnalytique` de 1 200 000 sur la section "Projet A" (axe Projet)

### Limite réelle côté import (pas côté code)

Le format paramétrable Sage 100 (confirmé par capture d'écran de la liste exhaustive des champs) n'expose qu'**une seule paire** `N° plan analytique` / `N° section` par ligne dans son format standard. Pour permettre malgré tout plusieurs axes ventilés simultanément si la source de données le permet (fichier enrichi, autre export), le dictionnaire de mapping expose **3 paires optionnelles** : Axe 1 (`axe_analytique` / `section_analytique`), Axe 2 (`axe_analytique_2` / `section_analytique_2`), Axe 3 (`axe_analytique_3` / `section_analytique_3`). Seules les paires effectivement mappées et renseignées génèrent une ventilation — si l'export Sage réel n'a qu'une seule paire de colonnes, le comportement reste strictement identique à un import mono-axe.

**Reste à évaluer si besoin** : croiser `axe_analytique` avec `section_analytique` lors de la résolution (au lieu du seul code section) si deux sections d'axes différents partagent un jour le même code — non fait à ce stade car non nécessaire tant que les codes de section restent uniques tous axes confondus dans la base.

---

## 5. Manques identifiés côté saisie manuelle et gestion (hors import)

- [ ] **Validation serveur manquante** : la cohérence "somme des ventilations = montant de l'écriture" repose uniquement sur le JS front (`validerVentilation()`). Un appel API direct pourrait enregistrer des ventilations incohérentes. → Ajouter un contrôle serveur dans `EcritureComptableController::store()` / `storeMultiple()`.
- [ ] **Suppression non protégée** : `AxeAnalytiqueController::destroy()` et `SectionAnalytiqueController::destroy()` suppriment sans vérifier l'existence de `VentilationAnalytique` ou `RegleVentilation` liées. Risque d'écritures analytiques orphelines. → Ajouter un contrôle bloquant, sur le modèle de ce qui existe déjà dans `PlanComptableController::destroy()` pour les comptes utilisés.
- [ ] **Validation multi-axe du modal** : si le multi-axe est confirmé (section 4), scoper la validation "100%" par axe, pas globalement.

---

## 6. Import analytique combiné (générale + analytique dans le même fichier)

### 6.1 Format réel confirmé (Sage 100, format paramétrable)

**Découverte clé** : Sage ne distingue pas les lignes par un flag "Type A/G". La ventilation analytique est portée par **deux colonnes optionnelles directement sur la ligne générale elle-même** :
- `N° plan analytique` → code de l'axe
- `N° section` → code de la section

Quand une écriture doit être répartie sur plusieurs sections, **Sage a déjà éclaté la ligne en plusieurs lignes physiques** au moment de la saisie (une ligne par section, chacune avec son propre montant). Il n'y a donc **jamais de pourcentage à calculer côté import** pour ce format : chaque ligne porte son montant final et, éventuellement, sa section.

Exemple représentatif :
```
Date;NPièce;Journal;Compte;Libellé;Débit;Crédit;NPlanAnalytique;NSection
15/01/25;298;ACH;622000;Loyer;600000;0;PDV;COCODY
15/01/25;298;ACH;622000;Loyer;360000;0;PDV;MARCORY
15/01/25;298;ACH;622000;Loyer;240000;0;PDV;PLATEAU
15/01/25;298;ACH;481000;Loyer;0;1200000;;
```

Conséquence pratique : **pas de flag A/G, pas de regroupement de lignes, pas de recherche croisée**. La ligne générale et l'information analytique sont la même ligne.

### 6.2 Champs de mapping (dictionnaire `courant`)

Champs déjà en place (comptes, journal, débit/crédit, tiers...) + ceux ajoutés pour l'analytique :

| Champ interne | Label | Obligatoire | Rôle |
|---|---|---|---|
| `axe_analytique` | N° Plan Analytique (Axe 1) | Non | Info/validation croisée si besoin |
| `section_analytique` | N° Section (Axe 1) | Non | Résout `section_id` ; si renseigné, ventilation 100% de la ligne sur cet axe |
| `axe_analytique_2` | N° Plan Analytique (Axe 2) | Non | Idem, pour un 2ème axe ventilé en parallèle |
| `section_analytique_2` | N° Section (Axe 2) | Non | Crée une ventilation supplémentaire indépendante de l'Axe 1 |
| `axe_analytique_3` | N° Plan Analytique (Axe 3) | Non | Idem, pour un 3ème axe |
| `section_analytique_3` | N° Section (Axe 3) | Non | Crée une ventilation supplémentaire indépendante des Axes 1 et 2 |

Le champ `type_ecriture` (A/G) **a été retiré** du dictionnaire — n'a pas d'équivalent réel dans Sage. Seules les paires effectivement mappées ET renseignées sur une ligne donnée génèrent une ventilation ; les paires 2 et 3 ne sont utiles que si la source de données expose plus d'une paire axe/section par ligne (le format standard Sage 100 n'en expose qu'une).

### 6.3 Pipeline de traitement (`ImportCommitJob.php`)

1. **Préchargement** `$sectionsByCode` (comme pour comptes/tiers/journaux), indexé par code en majuscules.
2. **Phase 1 (lecture des lignes)** : pour chaque ligne, résoudre jusqu'à 3 sections (une par paire axe/section mappée et renseignée) → `section_ids` (tableau). Si un code section renseigné n'existe pas en base → erreur bloquante explicite.
3. **Phase 5 (insertion)** : par groupe de pièce,
   - si **aucune** ligne du groupe n'a de `section_ids` → chemin rapide inchangé, `EcritureComptable::insert()` en masse.
   - si **au moins une** ligne a des `section_ids` → bascule en `EcritureComptable::create()` ligne par ligne (nécessaire pour récupérer l'`id` généré), puis création d'**une `VentilationAnalytique` par section résolue** sur cette ligne — chacune à 100% du montant de la ligne, indépendamment des autres (même principe que le modal manuel).

### 6.4 Dépendance technique à vérifier

Le code appelle `$ecriture->ventilations()->create(...)`. Cela suppose que **`EcritureComptable` a une relation `hasMany` vers `VentilationAnalytique`** :

```php
public function ventilations()
{
    return $this->hasMany(VentilationAnalytique::class, 'ecriture_id');
}
```

**À vérifier dans `app/Models/EcritureComptable.php` — si absente, l'ajouter, sinon le job échoue à l'exécution.**

### 6.5 État du code

✅ **Déjà implémenté** (fichiers livrés) :
- `AdminConfigController.php` : dictionnaire mis à jour (retrait `type_ecriture`, ajout `axe_analytique` + `section_analytique`), préchargement `$sectionsByCode`, validation en aperçu (staging) avec vraie erreur si section inconnue.
- `ImportCommitJob.php` : résolution `section_id` par ligne en Phase 1, insertion hybride (masse / unitaire) en Phase 5, ventilation créée immédiatement après chaque `create()`.

⚠️ **Pas encore fait** :
- Vérification/ajout de la relation `ventilations()` sur `EcritureComptable` (section 6.4).
- Tests avec un vrai fichier d'export Sage contenant les colonnes `N° plan analytique` / `N° section`.
- Décision définitive mono-axe vs multi-axe pour la résolution de section (section 4).

---

## 7. Import analytique seul (ventilation a posteriori sur écritures déjà en base)

**Statut** : cas d'usage confirmé légitime, mais **non implémenté** — à traiter comme un **type d'import distinct** (`type = 'ventilation_analytique'`), séparé de l'import combiné (section 6).

### Différence clé avec l'import combiné
- L'import combiné crée les écritures générales ET les ventilations dans le même job.
- L'import "ventilation seule" ne crée **aucune** écriture : il recherche des `EcritureComptable` déjà existantes et vient seulement leur attacher une `VentilationAnalytique`.

### Logique à prévoir
```php
$ecriture = EcritureComptable::where('company_id', $companyId)
    ->where('n_saisie', $row['n_saisie'])
    ->where('plan_comptable_id', $compteId)
    ->first();

if (!$ecriture) {
    $errors[] = "Ligne {$index}: Aucune écriture trouvée pour N°Saisie {$row['n_saisie']} / Compte {$row['compte']}.";
    continue;
}
// puis create() de la VentilationAnalytique liée
```

**Point de fragilité à documenter pour l'utilisateur final** : la clé de rapprochement (n° saisie + compte) peut être ambiguë si une même pièce a plusieurs lignes sur le même compte (contre-passations, régularisations). Prévoir un message d'erreur explicite dans ce cas plutôt qu'un rattachement silencieux au hasard.

**Ne pas commencer ce chantier avant que la section 6 soit stabilisée et testée en conditions réelles.**

---

## 8. Checklist de mise en production (ordre recommandé)

1. [ ] Confirmer que le fix `RegleVentilation` (section 3) est bien appliqué.
2. [ ] Confirmer/ajouter la relation `ventilations()` sur `EcritureComptable` (section 6.4).
3. [ ] Ajouter les contrôles serveur manquants côté saisie manuelle (section 5).
4. [ ] Protéger la suppression des axes/sections (section 5).
5. [ ] Tester l'import combiné (section 6) avec un export Sage réel contenant `N° plan analytique` / `N° section` (et un cas multi-axe si la source le permet).
6. [ ] Vérifier que les rapports analytiques (Balance / Grand Livre / Résultat par section) reflètent correctement les écritures importées, y compris sur plusieurs axes.
7. [ ] Une fois stabilisé : construire l'import "ventilation seule" (section 7) si le besoin se confirme.

---

## 9. Fichiers concernés (référence rapide)

| Fichier | Rôle |
|---|---|
| `app/Http/Controllers/Analytique/AxeAnalytiqueController.php` | CRUD axes |
| `app/Http/Controllers/Analytique/SectionAnalytiqueController.php` | CRUD sections |
| `app/Http/Controllers/Analytique/VentilationController.php` | Règles de ventilation auto (bug import corrigé) |
| `app/Models/AxeAnalytique.php` / `SectionAnalytique.php` / `VentilationAnalytique.php` / `RegleVentilation.php` | Modèles |
| `app/Http/Controllers/Admin/AdminConfigController.php` | Mapping d'import (dictionnaire `courant` mis à jour) |
| `app/Jobs/ImportCommitJob.php` | Job de commit d'import (résolution section + insertion hybride ajoutées) |
| `app/Services/Analytique/AnalyticalReportingService.php` | Rapports Balance/Grand Livre/Résultat analytiques |
| `app/Models/EcritureComptable.php` | **À vérifier** : relation `ventilations()` |
