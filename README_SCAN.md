# Instructions d'utilisation du système SCAN IA SYSCOHADA CI

## 🎯 Fichiers créés

### 1. **ia_traitement.php** 
- Backend PHP qui communique avec l'API Gemini
- Prompt expert SYSCOHADA Côte d'Ivoire
- Gestion intelligente des erreurs et retries

### 2. **compta_scan.js**
- Frontend JavaScript moderne
- Interface de scan intuitive
- Validation et prévisualisation des fichiers

### 3. **scan_interface.html**
- Interface HTML complète et responsive
- Design moderne avec Bootstrap 5
- Intégration parfaite avec le système

## 🚀 Utilisation

### Option 1 : Interface autonome
```bash
# Ouvrir l'interface de scan
http://localhost/scan_interface.html
```

### Option 2 : Intégration dans Laravel
1. Copiez `ia_traitement.php` dans votre projet Laravel
2. Ajoutez la route dans `web.php` :
   ```php
   Route::post('/ia-traitement', 'IaController@traiterFacture');
   ```
3. Intégrez `compta_scan.js` dans vos vues Blade

## 🧠 Fonctionnalités IA

### Comptes SYSCOHADA CI reconnus :
- **401000** : Fournisseurs d'exploitation
- **411000** : Clients  
- **421000** : Personnel
- **431000** : CNPS
- **442000** : Impôts et taxes
- **445000** : TVA (445100 déductible, 445200 collectée)
- **501000** : Caisse
- **521000** : Banques
- **571000** : Caisse principale
- **601000** : Achats de marchandises
- **603000** : Achats de matières premières
- **611000** : Transports
- **613000** : Locations
- **614000** : Entretien et réparations
- **622000** : Rémunérations d'intermédiaires
- **631000** : Impôts et taxes
- **641000** : Charges de personnel
- **701000** : Ventes de marchandises
- **706000** : Services vendus

## 🔧 Configuration

### Clé API Gemini
```php
$api_key = "AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU";
```

### Modèle utilisé
- `gemini-1.5-flash` : Rapide et performant
- Température : 0.2 (réponses précises)
- Max tokens : 2000

## 📋 Processus d'analyse

1. **Upload** : Glissez ou sélectionnez votre facture
2. **Validation** : Vérification format et taille
3. **Compression** : Optimisation de l'image
4. **Analyse IA** : Extraction intelligente des données
5. **Remplissage** : Formulaire auto-rempli
6. **Validation** : Vérification équilibre Débit/Crédit
7. **Enregistrement** : Sauvegarde en base de données

## 🎨 Interface utilisateur

- **Design moderne** : Gradient et animations fluides
- **Responsive** : Fonctionne sur mobile et desktop
- **Intuitif** : Drag & drop, prévisualisation
- **Feedback** : Alertes et indicateurs visuels
- **Accessible** : Compatible lecteurs d'écran

## 🛡️ Sécurité

- **Validation fichiers** : Types et tailles contrôlés
- **Sanitization** : Nettoyage des entrées utilisateur
- **Gestion erreurs** : Pas d'informations sensibles exposées
- **Rate limiting** : Protection contre abus

## 📊 Performance

- **Compression images** : Réduction taille avant envoi
- **Cache intelligent** : Optimisation des requêtes
- **Retry exponentiel** : Gestion quota API
- **Loading states** : Feedback utilisateur constant

## 🔍 Tests

```bash
# Tester l'interface
http://localhost/scan_interface.html

# Tester le backend PHP
curl -X POST -F "facture=@test.jpg" http://localhost/ia_traitement.php
```

Le système est prêt à être utilisé ! 🎉
