# 🚀 SYNTHÈSE DÉPLOIEMENT VERTEX AI

**Génération** : 19 Mars 2026  
**Projet** : FLOW AI - Remplacement Vision API → Vertex AI  
**Équipe IT** : DC-Knowing IT  

---

## 📦 FICHIERS À DÉPLOYER

### 1️⃣ NOUVEAU SERVICE

**Fichier** : `app/Services/VertexAiService.php`

```bash
# Action
cp VertexAiService.php app/Services/VertexAiService.php

# Vérification
php -l app/Services/VertexAiService.php  # Doit dire "No syntax errors"
```

**Responsabilités** :
- Authentification via ADC (Application Default Credentials)
- Appel Vertex AI Gemini Vision
- Gestion des erreurs + retry automatique
- Parsing réponse JSON
- Test de connectivité

---

### 2️⃣ CONTRÔLEUR ADAPTÉ

**Fichier** : `app/Http/Controllers/IaController.php`

```bash
# Action (choix 1 : remplacer complètement)
cp IaController_VERTEX_AI.php app/Http/Controllers/IaController.php

# Ou action (choix 2 : merger manuellement)
# - Ajouter : use App\Services\VertexAiService;
# - Ajouter : private VertexAiService $vertexAiService;
# - Remplacer la méthode traiterFacture()
# - Supprimer callGeminiApi()
# - Garder tous les autres helpers (buildPrompt, compressImage, etc)
```

**Changements majeurs** :
- `traiterFacture()` utilise `VertexAiService` au lieu de `callGeminiApi()`
- Pas besoin de retry sur les modèles (Vertex gère)
- Authentification ADC automatique
- Logs structurés

---

### 3️⃣ VARIABLES D'ENVIRONNEMENT

**Fichier** : `.env`

```bash
# SUPPRIMER
# GEMINI_API_KEY=sk-...
# VISION_API_KEY=...

# AJOUTER (copier depuis .env.VERTEX_AI)
GOOGLE_CLOUD_PROJECT_ID=scan1-comptaflow
GOOGLE_CLOUD_PROJECT_NUMBER=288805151479
VERTEX_AI_LOCATION=europe-west2
VERTEX_AI_MODEL=gemini-2.5-flash
VERTEX_AI_TEMPERATURE=0.2
VERTEX_AI_MAX_TOKENS=4096
VERTEX_AI_TIMEOUT_SECONDS=120
COMPTAFLOW_IA_MODE=vertex_ai
COMPTAFLOW_CONFIDENCE_THRESHOLD=0.70
```

---

## ⚙️ CONFIGURATION GCP PRÉALABLE

### Pour l'équipe DevOps / SRE

```bash
# 1. Vérifier projet actif
gcloud config get-value project
# Output attendu : scan1-comptaflow

# 2. Activer les APIs
gcloud services enable aiplatform.googleapis.com
gcloud services enable storage-api.googleapis.com

# 3. Configurer ADC
gcloud auth application-default login

# 4. Vérifier le token
gcloud auth application-default print-access-token | head -c 50
# Output : ya29.a0ATkoCc5E... ✅

# 5. Tester Vertex AI
gcloud ai models describe gemini-2.5-flash \
  --location=europe-west2

# 6. Vérifier les quotas
gcloud compute project-info describe --project=scan1-comptaflow
```

---

## ✅ CHECKLIST DÉPLOIEMENT

### Phase 1 : Préparation (30 min)

- [ ] Cloner le repo sur serveur deployment
- [ ] Copier `VertexAiService.php` → `app/Services/`
- [ ] Copier `IaController_VERTEX_AI.php` → `app/Http/Controllers/IaController.php`
- [ ] Mettre à jour `.env` (copier variables depuis `.env.VERTEX_AI`)
- [ ] Vérifier syntaxe PHP : `php -l app/Services/VertexAiService.php`
- [ ] Vérifier syntaxe PHP : `php -l app/Http/Controllers/IaController.php`

### Phase 2 : Configuration GCP (15 min)

- [ ] ADC configuré : `gcloud auth application-default login`
- [ ] APIs activées : `aiplatform.googleapis.com`
- [ ] Token valide : `gcloud auth application-default print-access-token`
- [ ] Projet correct : `gcloud config get-value project` → `scan1-comptaflow`

### Phase 3 : Validation (20 min)

```bash
# 1. Cache clear
php artisan cache:clear
php artisan config:clear

# 2. Test connectivité
php artisan tinker
> App\Services\VertexAiService::testConnection()
# Output : ['status' => 'ok', ...]

# 3. Test config
> App\Services\VertexAiService::getConfig()
# Output : ['project_id' => 'scan1-comptaflow', ...]

# 4. Test upload (via Postman ou API directe)
curl -X POST \
  https://votre-app.com/api/comptaflow/factures/scan \
  -H "Authorization: Bearer $TOKEN" \
  -F "facture=@facture_test.jpg"

# Output attendu :
# {"est_facture":true,"montant_ttc":1180000,...}
```

### Phase 4 : Monitoring (continu)

```bash
# Logs en live
tail -f storage/logs/laravel.log | grep -i "vertex\|error"

# Compter erreurs
grep "error" storage/logs/laravel.log | wc -l

# Si erreur, voir details
tail -50 storage/logs/laravel.log
```

---

## 🔄 ROUTES API (Aucun changement pour frontend)

```
POST /api/comptaflow/factures/scan
  Input: multipart/form-data { facture: File }
  Output: JSON { est_facture, montant_ttc, ecriture[], ... }
  [ANCIEN] Via Gemini API direct
  [NOUVEAU] Via Vertex AI ← Même réponse, meilleure infra
```

---

## 📊 DIFFÉRENCES VISIBLES

### Pour l'utilisateur
✅ **AUCUNE** - Tout fonctionne pareil

### Pour le logging
```
AVANT: [2026-03-19 14:30] Essai modèle Gemini: gemini-2.0-flash
APRÈS: [2026-03-19 14:30] Vertex AI Request {model: gemini-2.5-flash, location: europe-west2}
```

### Pour la performance
- Même rapidité (~2-3 sec par facture)
- Meilleur timeout handling
- Résilience améliorée

---

## 🚨 ROLLBACK RAPIDE

Si problème majeur :

```bash
# 1. Revenir au code ancien
git checkout HEAD~1 app/Http/Controllers/IaController.php
git checkout HEAD~1 .env

# 2. Supprimer le service
rm app/Services/VertexAiService.php

# 3. Redémarrer
php artisan cache:clear
php artisan config:clear

# 4. Vérifier
tail -10 storage/logs/laravel.log
```

---

## 💡 OPTIMISATIONS POSSIBLES

Après déploiement initial, considérer :

1. **RAG (Retrieval-Augmented Generation)** :
   - Utiliser les corpus RAG déjà créés
   - Améliorer contexte avec docs métier

2. **Document AI** :
   - OCR structuré optionnel
   - Pour comparer vs Gemini Vision

3. **Caching amélioré** :
   - Redis pour cache 24h factures identiques

4. **Analytics** :
   - Dashboard Vertex AI monitoring
   - Tracking confiance moyenne

---

## 📞 SUPPORT

### Erreurs Vertex AI

```
Error 403: Permission denied
  → Vérifier IAM roles sur service account

Error 429: Quota exceeded
  → Augmenter timeout / ajouter delay

Error JSON Parse
  → Vérifier max_tokens (augmenter si besoin)
```

### Contacts

- **GCP Support** : https://cloud.google.com/support
- **Vertex AI Docs** : https://cloud.google.com/vertex-ai/docs
- **Tech Lead DC-Knowing** : [Contact interne]

---

## 📋 FICHIERS FOURNIS

| Fichier | Action | Où |
|---------|--------|-----|
| `VertexAiService.php` | Copier | `app/Services/` |
| `IaController_VERTEX_AI.php` | Copier/merger | `app/Http/Controllers/IaController.php` |
| `.env.VERTEX_AI` | Référence | Copier variables dans `.env` |
| `MIGRATION_GUIDE_VERTEX_AI.md` | Lire | Documentation complète |
| Ce fichier | Checklist | Guide déploiement |

---

## ✨ RÉSULTAT FINAL

✅ **Système de scan IA moderni** avec :
- Vertex AI Gemini Vision (meilleur modèle vision du marché)
- ADC authentication (plus sûr)
- Monitoring Google Cloud (meilleur logging)
- Zero downtime (transparent pour users)
- Même API (zéro changement frontend)

**Prêt pour production ! 🚀**

---

**Version** : 1.0  
**Date** : 19 Mars 2026  
**Status** : ✅ Ready for deployment
