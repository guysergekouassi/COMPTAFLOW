# COMPTAFLOW BACKEND STARTER (PowerShell)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   COMPTAFLOW BACKEND STARTER" -ForegroundColor Cyan  
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérification PHP
Write-Host "Verification de PHP..." -ForegroundColor Yellow
try {
    $phpVersion = php --version
    Write-Host $phpVersion -ForegroundColor Green
} catch {
    Write-Host "ERROR: PHP n'est pas installé" -ForegroundColor Red
    Read-Host "Appuyez sur Entrée pour quitter"
    exit 1
}

# Vérification Composer
Write-Host "Verification de Composer..." -ForegroundColor Yellow
try {
    $composerVersion = composer --version
    Write-Host $composerVersion -ForegroundColor Green
} catch {
    Write-Host "ERROR: Composer n'est pas installé" -ForegroundColor Red
    Read-Host "Appuyez sur Entrée pour quitter"
    exit 1
}

# Installation dépendances PHP
Write-Host "Installation des dépendances PHP..." -ForegroundColor Yellow
if (Test-Path "vendor") {
    Write-Host "Les dépendances PHP sont déjà installées" -ForegroundColor Green
} else {
    try {
        composer install --no-interaction
        Write-Host "Dépendances PHP installées avec succès" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Erreur lors de l'installation des dépendances PHP" -ForegroundColor Red
        Read-Host "Appuyez sur Entrée pour quitter"
        exit 1
    }
}

# Installation dépendances Node.js
Write-Host "Installation des dépendances Node.js..." -ForegroundColor Yellow
if (Test-Path "node_modules") {
    Write-Host "Les dépendances Node.js sont déjà installées" -ForegroundColor Green
} else {
    try {
        npm install --no-audit --no-fund
        Write-Host "Dépendances Node.js installées avec succès" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Erreur lors de l'installation des dépendances Node.js" -ForegroundColor Red
        Read-Host "Appuyez sur Entrée pour quitter"
        exit 1
    }
}

# Configuration .env
Write-Host "Configuration du fichier .env..." -ForegroundColor Yellow
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Host "Fichier .env créé à partir de .env.example" -ForegroundColor Green
    } else {
        Write-Host "Création d'un fichier .env par défaut..." -ForegroundColor Yellow
        @"
APP_NAME=COMPTAFLOW
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=comptaflow
DB_USERNAME=root
DB_PASSWORD=
"@ | Out-File -FilePath ".env" -Encoding UTF8
        Write-Host "Fichier .env créé avec configuration par défaut" -ForegroundColor Green
    }
} else {
    Write-Host "Fichier .env déjà existant" -ForegroundColor Green
}

# Démarrage du serveur
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Démarrage du serveur Laravel..." -ForegroundColor Cyan
Write-Host "Serveur accessible sur: http://localhost:8000" -ForegroundColor Green
Write-Host "Appuyez sur Ctrl+C pour arrêter le serveur" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

try {
    php artisan serve --host=0.0.0.0 --port=8000
} catch {
    Write-Host "ERROR: Impossible de démarrer le serveur Laravel" -ForegroundColor Red
    Read-Host "Appuyez sur Entrée pour quitter"
    exit 1
}
