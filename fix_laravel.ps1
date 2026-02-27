# COMPTAFLOW LARAVEL FIX SCRIPT

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   COMPTAFLOW LARAVEL FIX" -ForegroundColor Cyan  
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Création des dossiers manquants
Write-Host "Création des dossiers de stockage..." -ForegroundColor Yellow

$dossiers = @(
    "storage\framework\sessions",
    "storage\framework\cache", 
    "storage\framework\views",
    "storage\logs",
    "bootstrap\cache"
)

foreach ($dossier in $dossiers) {
    if (-not (Test-Path $dossier)) {
        New-Item -ItemType Directory -Path $dossier -Force
        Write-Host "Créé: $dossier" -ForegroundColor Green
    } else {
        Write-Host "Existe déjà: $dossier" -ForegroundColor Gray
    }
}

# Nettoyage des caches Laravel
Write-Host ""
Write-Host "Nettoyage des caches Laravel..." -ForegroundColor Yellow

try {
    php artisan cache:clear
    Write-Host "Cache application vidé" -ForegroundColor Green
} catch {
    Write-Host "Erreur cache:clear" -ForegroundColor Red
}

try {
    php artisan config:clear  
    Write-Host "Cache configuration vidé" -ForegroundColor Green
} catch {
    Write-Host "Erreur config:clear" -ForegroundColor Red
}

try {
    php artisan view:clear
    Write-Host "Cache views vidé" -ForegroundColor Green
} catch {
    Write-Host "Erreur view:clear" -ForegroundColor Red
}

try {
    php artisan route:clear
    Write-Host "Cache routes vidé" -ForegroundColor Green
} catch {
    Write-Host "Erreur route:clear" -ForegroundColor Red
}

# Lien de stockage
Write-Host ""
Write-Host "Création du lien de stockage..." -ForegroundColor Yellow
try {
    php artisan storage:link
    Write-Host "Lien storage créé" -ForegroundColor Green
} catch {
    Write-Host "Erreur storage:link" -ForegroundColor Red
}

# Génération de clé
Write-Host ""
Write-Host "Vérification de la clé d'application..." -ForegroundColor Yellow
try {
    php artisan key:generate --force
    Write-Host "Clé d'application générée" -ForegroundColor Green
} catch {
    Write-Host "Erreur key:generate" -ForegroundColor Red
}

# Permissions Windows
Write-Host ""
Write-Host "Configuration des permissions..." -ForegroundColor Yellow
try {
    icacls storage /grant "Everyone:(OI)(CI)F" /T
    icacls bootstrap\cache /grant "Everyone:(OI)(CI)F" /T
    Write-Host "Permissions configurées" -ForegroundColor Green
} catch {
    Write-Host "Erreur permissions (normal si pas admin)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "RÉPARATION TERMINÉE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Redémarrez le serveur avec:" -ForegroundColor Yellow
Write-Host "php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Accédez à: http://localhost:8000" -ForegroundColor Green
