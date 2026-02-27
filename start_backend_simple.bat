@echo off
echo ========================================
echo    COMPTAFLOW BACKEND STARTER
echo ========================================
echo.

echo Verification de PHP...
php --version
if %errorlevel% neq 0 (
    echo ERROR: PHP n'est pas installe
    pause
    exit /b 1
)

echo.
echo Verification de Composer...
composer --version
if %errorlevel% neq 0 (
    echo ERROR: Composer n'est pas installe
    pause
    exit /b 1
)

echo.
echo Installation des dependances PHP...
composer install --no-interaction

echo.
echo Installation des dependances Node.js...
npm install --no-audit --no-fund

echo.
echo Configuration du .env...
if not exist .env (
    if exist .env.example (
        copy .env.example .env
        echo Fichier .env cree
    ) else (
        echo Creation du fichier .env par defaut...
        echo APP_NAME=COMPTAFLOW > .env
        echo APP_ENV=local >> .env
        echo APP_DEBUG=true >> .env
        echo APP_URL=http://localhost:8000 >> .env
    )
)

echo.
echo Demarrage du serveur Laravel...
echo ========================================
echo Serveur accessible sur: http://localhost:8000
echo Appuyez sur Ctrl+C pour arreter
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
