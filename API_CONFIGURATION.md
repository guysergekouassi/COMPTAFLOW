# COMPTAFLOW API Configuration

## Instructions de connexion au backend

### 1. Demarrer le backend Laravel

Depuis le dossier COMPTAFLOW sur votre bureau:

```bash
# Methode 1: Avec le script automatique
start_backend.bat

# Methode 2: Manuellement
composer install
npm install
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Configuration de l'application mobile

L'application mobile est configuree pour se connecter a:
- **URL**: http://localhost:8000/api
- **Port**: 8000

### 3. Verification de la connexion

1. Demarrez le backend Laravel
2. Lancez l'application mobile avec: `flutter run -d chrome`
3. Testez la connexion

### 4. URLs de test

- **Backend**: http://localhost:8000
- **API**: http://localhost:8000/api
- **Documentation API**: http://localhost:8000/api/documentation

### 5. Configuration alternative

Si vous utilisez un emulateur Android, modifiez la ligne 13 dans:
`lib/services/api_service.dart`

Remplacez:
```dart
static const String baseUrl = 'http://localhost:8000/api';
```

Par:
```dart
static const String baseUrl = 'http://10.0.2.2:8000/api';
```

### 6. Depannage

- Verifiez que PHP 8.1+ est installe
- Verifiez que Composer est installe
- Verifiez que le port 8000 n'est pas utilise
- Verifiez votre firewall Windows
