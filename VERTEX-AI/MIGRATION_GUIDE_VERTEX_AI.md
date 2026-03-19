# 📋 GUIDE MIGRATION : Gemini API → Vertex AI

**Objectif** : Remplacer l'API Gemini directe par Vertex AI + ADC  
**Impact** : Aucun changement frontend - Transparente pour l'utilisateur  
**Durée estimation** : 30-45 min  

---

## 🔄 RÉSUMÉ DES CHANGEMENTS

| Aspect | Avant (Vision/Gemini API) | Après (Vertex AI) |
|--------|--------------------------|-------------------|
| **Auth** | Clé API en .env | ADC (gcloud) |
| **Endpoint** | `generativelanguage.googleapis.com` | `europe-west2-aiplatform.googleapis.com` |
| **Service** | Appel direct curl | VertexAiService wrapper |
| **Retry** | Manuel (5 modèles) | Automatique (Vertex gère) |
| **Monitoring** | Logs texte | Google Cloud Logging |
| **Coût** | Pay-per-token | Billing activé GCP |

---

## ✅ ÉTAPE 1 : PRÉREQUIS GCP

### 1.1 Vérifier Project ID

```bash
gcloud config get-value project
# Output : scan1-comptaflow ✅

# Ou définir si différent :
gcloud config set project scan1-comptaflow
```

### 1.2 Activer les APIs

```bash
gcloud services enable aiplatform.googleapis.com
gcloud services enable storage-api.googleapis.com

# Vérifier
gcloud services list --enabled | grep -i aiplatform
```

### 1.3 Configurer ADC (Application Default Credentials)

```bash
# Sur le serveur de prod (Cloud Shell, VM GCP, etc)
gcloud auth application-default login

# Sur développement (local)
gcloud auth application-default login

# Vérifier
gcloud auth application-default print-access-token | head -c 50
# Output : ya29.a0ATkoCc5E... ✅
```

---

## 📁 ÉTAPE 2 : FICHIERS À METTRE À JOUR

### 2.1 Ajouter le Service Vertex AI

**Fichier** : `app/Services/VertexAiService.php` (voir fichier fourni)

```bash
# Copier le fichier
cp VertexAiService.php app/Services/

# Vérifier
php artisan tinker
> App\Services\VertexAiService::testConnection()
# Output : ['status' => 'ok', ...]
```

### 2.2 Remplacer le Contrôleur

**Fichier** : `app/Http/Controllers/IaController.php`

**Changements clés** :

```php
// AVANT
private function callGeminiApi(string $url, array $payload, string $api_key): array
{
    // Appel direct curl avec retry sur modèles
    ...
}

// APRÈS
public function traiterFacture(Request $request)
{
    ...
    // Utiliser VertexAiService
    $result = $this->vertexAiService->analyzeInvoice($image_data, $mime_type, $prompt);
    ...
}
```

**Comment faire** :
1. Copier `IaController_VERTEX_AI.php` → `app/Http/Controllers/IaController.php`
2. Ou merger les changements :
   - Ajouter `use App\Services\VertexAiService;` en haut
   - Remplacer la méthode `traiterFacture()`
   - Supprimer `callGeminiApi()` (plus besoin)
   - Garder `buildPrompt()` et autres helper

### 2.3 Mettre à jour `.env`

**Fichier** : `.env`

```bash
# SUPPRIMER
# GEMINI_API_KEY=...
# VISION_API_KEY=...

# AJOUTER (voir .env.VERTEX_AI fourni)
GOOGLE_CLOUD_PROJECT_ID=scan1-comptaflow
GOOGLE_CLOUD_PROJECT_NUMBER=288805151479
VERTEX_AI_LOCATION=europe-west2
VERTEX_AI_MODEL=gemini-2.5-flash
VERTEX_AI_TEMPERATURE=0.2
VERTEX_AI_MAX_TOKENS=4096
VERTEX_AI_TIMEOUT_SECONDS=120

COMPTAFLOW_IA_MODE=vertex_ai
```

---

## 🧪 ÉTAPE 3 : TESTS

### 3.1 Test Connectivité

```bash
# Via artisan
php artisan tinker

> App\Services\VertexAiService::testConnection()

# Output attendu :
# => [
#      "status" => "ok",
#      "message" => "Connexion Vertex AI réussie",
#      "project_id" => "scan1-comptaflow",
#      "location" => "europe-west2",
#      "model" => "gemini-2.5-flash"
#    ]
```

### 3.2 Test via API

```bash
# 1. Obtenir le token auth
php artisan tinker
> $user = User::first();
> $token = $user->createToken('test')->plainTextToken;
> echo $token;

# 2. Upload une facture test
curl -X POST \
  http://localhost:8000/api/comptaflow/factures/scan \
  -H "Authorization: Bearer $TOKEN" \
  -F "facture=@facture_test.jpg"

# Output attendu :
# {
#   "est_facture": true,
#   "montant_ttc": 1180000,
#   "ecriture": [...],
#   "confiance": 0.95
# }
```

### 3.3 Test avec Postman

**Request** :
```
POST /api/comptaflow/factures/scan
Authorization: Bearer [TOKEN]
Content-Type: multipart/form-data

Body:
  facture: [fichier JPG/PNG/PDF]
```

**Response** :
```json
{
  "est_facture": true,
  "statut_lecture": "lisible",
  "type_document": "Facture",
  "tiers": "Fournisseur ABC",
  "date": "2026-03-19",
  "reference": "INV-001",
  "montant_ht": 1000000,
  "montant_tva": 180000,
  "montant_ttc": 1180000,
  "devise": "XOF",
  "ecriture": [
    {"compte": "601100", "intitule": "Achats", "debit": 1000000, "credit": 0},
    {"compte": "401100", "intitule": "FOURNISSEUR ABC", "debit": 0, "credit": 1180000}
  ],
  "confiance": 0.95,
  "analyse": "Facture claire et bien structurée"
}
```

---

## 🐛 TROUBLESHOOTING

### Erreur : "Impossible d'obtenir le token ADC"

**Cause** : ADC non configuré

**Solution** :
```bash
gcloud auth application-default login
# ou sur VM GCP
gcloud compute ssh INSTANCE --zone ZONE -- \
  gcloud auth application-default login
```

### Erreur : "HTTP 403 - Permission denied"

**Cause** : Service account sans permissions

**Solution** :
```bash
# Sur GCP Console :
# 1. IAM & Admin → Service Accounts
# 2. Trouver la service account du projet
# 3. Ajouter roles:
#    - Vertex AI User
#    - Vertex AI Service Agent
```

### Erreur : "Quota exceeded (429)"

**Cause** : Trop d'appels API simultanés

**Solution** :
```bash
# Augmenter le timeout .env
VERTEX_AI_TIMEOUT_SECONDS=180

# Ou ajouter delay entre appels
sleep(2)
```

### Erreur : "JSON invalide"

**Cause** : Réponse tronquée ou malformée

**Solution** :
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log | grep "Vertex"

# Augmenter max_tokens si nécessaire
VERTEX_AI_MAX_TOKENS=8192
```

---

## 📊 ÉTAPE 4 : VALIDATION PRODUCTION

### Checklist pre-deployment

- [ ] VertexAiService.php copié dans `app/Services/`
- [ ] IaController.php remplacé
- [ ] `.env` mise à jour (VERTEX_AI_* variables)
- [ ] ADC configuré (`gcloud auth application-default login`)
- [ ] Test connectivité réussi (`testConnection()`)
- [ ] Test upload facture réussi (via Postman)
- [ ] Logs sans erreur (`tail -f storage/logs/laravel.log`)
- [ ] GEMINI_API_KEY supprimé du `.env`
- [ ] Migrations exécutées (si nécessaire)

### Rollback plan

Si problème :

```bash
# Revenir à l'ancienne version
git checkout HEAD~1 app/Http/Controllers/IaController.php

# Restaurer .env
git checkout HEAD .env

# Redémarrer
php artisan cache:clear
php artisan config:clear
```

---

## 📈 MONITORING

### Via Logs

```bash
# Logs Vertex AI en live
tail -f storage/logs/laravel.log | grep "Vertex"

# Résumé erreurs
grep "error" storage/logs/laravel.log | tail -20
```

### Via GCP Console

1. **Cloud Logging** : https://console.cloud.google.com/logs
   - Filtre : `resource.type="api"` AND `protoPayload.methodName="aiplatform"`

2. **Vertex AI Dashboard** : https://console.cloud.google.com/vertex-ai/
   - Monitoring → Models
   - Voir les appels aux modèles Gemini

3. **Quotas** : https://console.cloud.google.com/iam-admin/quotas
   - Vérifier Vertex AI quotas restants

---

## 💰 NOTES SUR LES COÛTS

| API | Coût |
|-----|------|
| **Gemini 2.5 Flash** (Vertex AI) | ~$0.075 / million input tokens |
| **Gemini 1.5 Pro** (Vertex AI) | ~$1.50 / million input tokens |
| **Ancien Vision API** | Remplacé ✅ |

→ **Billing doit être activé** sur le projet GCP pour utiliser Vertex AI

---

## 🎯 RÉSULTAT ATTENDU

✅ **Après migration** :
- API fonctionne identiquement pour l'utilisateur
- Logs plus détaillés via Google Cloud
- Monitoring amélioré
- Pas de clé API en dur
- ADC plus sûr et flexible

---

## 📚 RESSOURCES

- [Vertex AI Docs](https://cloud.google.com/vertex-ai/docs)
- [Gemini Vision](https://cloud.google.com/vertex-ai/docs/generative-ai/image/overview)
- [ADC Guide](https://cloud.google.com/docs/authentication/application-default-credentials)
- [gcloud CLI Reference](https://cloud.google.com/sdk/gcloud/reference)

---

**Questions ? Support GCP : https://cloud.google.com/support**
