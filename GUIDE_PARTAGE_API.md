# GUIDE DE PARTAGE API GEMINI

## 🎯 OBJECTIF
Partager l'API Gemini entre vous et votre responsable pour avoir les mêmes résultats de scan.

## 🔑 ÉTAPES À SUIVRE

### 1. CRÉER UNE CLÉ API POUR LE RESPONSABLE
- Allez sur : https://makersuite.google.com/app/apikey
- Créez une nouvelle clé API
- Nommez-la : "COMPTAFLOW-PROD-RESPONSABLE"

### 2. CONFIGURER LES ENVIRONNEMENTS

#### VOTRE ENVIRONNEMENT (local)
```bash
# Dans votre .env
GEMINI_API_KEY=AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU
GEMINI_MODEL=gemini-flash-latest
```

#### ENVIRONNEMENT PRODUCTION (responsable)
```bash
# Dans le .env de production
GEMINI_API_KEY=NOUVELLE_CLÉ_API_DU_RESPONSABLE
GEMINI_MODEL=gemini-flash-latest
```

### 3. SYNCHRONISER LES FICHIERS

#### Fichiers à push sur Git :
```bash
git add ia_traitement_standalone.php
git add resources/views/accounting/scan.blade.php
git add routes/web.php
git add .env.example
git commit -m "Configuration API Gemini partagée"
git push origin main
```

#### Fichiers que le responsable doit pull :
```bash
git pull origin main
```

### 4. CONFIGURATION PRODUCTION

Le responsable doit :
1. Pull les changements
2. Copier `.env.example` vers `.env`
3. Ajouter sa clé API dans le `.env`
4. Configurer l'URL de production

## 📁 FICHIERS MODIFIÉS

### ✅ `ia_traitement_standalone.php`
- Utilise maintenant `$_ENV['GEMINI_API_KEY']`
- Utilise `$_ENV['GEMINI_MODEL']`
- Compatible avec les deux environnements

### ✅ `.env.example`
- Contient la configuration API
- Partagé via Git
- Template pour les deux environnements

### ✅ `resources/views/accounting/scan.blade.php`
- Interface de scan synchronisée
- Mapping SYSCOHADA complet
- Gestion TVA automatique

### ✅ `routes/web.php`
- Route `/ia_traitement_standalone.php` ajoutée
- Compatible production/local

## 🚀 TEST DE FONCTIONNEMENT

### Test local (vous) :
```bash
http://127.0.0.1:8000/ecriture-scan
```

### Test production (responsable) :
```bash
https://votresite.com/ecriture-scan
```

## ✅ RÉSULTAT ATTENDU

- **Même API** : Gemini flash-latest
- **Même mapping** : SYSCOHADA CI → 8 chiffres
- **Mêmes résultats** : Comptes PPPPNNNN00
- **Système synchronisé** : Push/Pull fonctionne

## 🔧 DÉPANNAGE

### Si erreur 429 (quota dépassé) :
- Utiliser des clés API différentes
- Attendre quelques minutes
- Créer une nouvelle clé

### Si erreur de configuration :
- Vérifier le `.env`
- Redémarrer le serveur
- Vérifier les permissions

## 📞 CONTACT

Pour toute question sur la configuration :
- Vous : clé API locale
- Responsable : clé API production
- Support : documentation partagée

---

**Le système est maintenant prêt pour le travail collaboratif !** 🎉
