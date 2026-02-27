@echo off
echo ========================================
echo    COMPTAFLOW BACKEND STARTER
echo ========================================
echo.

echo Verification de PHP...
php --version
if %errorlevel% neq 0 (
    echo ERROR: PHP n'est pas installe ou pas dans le PATH
    echo Veuillez installer PHP 8.1 ou superieur
    pause
    exit /b 1
)

echo.
echo Verification de Composer...
composer --version
if %errorlevel% neq 0 (
    echo ERROR: Composer n'est pas installe ou pas dans le PATH
    pause
    exit /b 1
)

echo.
echo Verification de Node.js...
node --version
if %errorlevel% neq 0 (
    echo ERROR: Node.js n'est pas installe
    pause
    exit /b 1
)

echo.
echo Installation des dependances PHP...
if exist vendor (
    echo Les dependances PHP sont deja installees
) else (
    composer install
    if %errorlevel% neq 0 (
        echo ERROR: Erreur lors de l'installation des dependances PHP
        pause
        exit /b 1
    )
)

echo.
echo Installation des dependances Node.js...
if exist node_modules (
    echo Les dependances Node.js sont deja installees
) else (
    npm install
    if %errorlevel% neq 0 (
        echo ERROR: Erreur lors de l'installation des dependances Node.js
        pause
        exit /b 1
    )
)

echo.
echo Creation du fichier .env s'il n'existe pas...
if not exist .env (
    if exist .env.example (
        copy .env.example .env
        echo Fichier .env cree a partir de .env.example
        echo Veuillez configurer votre base de donnees dans .env
    ) else (
        echo WARNING: Fichier .env.example non trouve
        echo Creation d'un fichier .env de base...
        echo APP_NAME=COMPTAFLOW > .env
        echo APP_ENV=local >> .env
        echo APP_KEY=base64:YOUR_APP_KEY_HERE >> .env
        echo APP_DEBUG=true >> .env
        echo APP_URL=http://localhost:8000 >> .env
        echo DB_CONNECTION=mysql >> .env
        echo DB_HOST=127.0.0.1 >> .env
        echo DB_PORT=3306 >> .env
        echo DB_DATABASE=comptaflow >> .env
        echo DB_USERNAME=root >> .env
        echo DB_PASSWORD= >> .env
    )
)

echo.
echo Verification des fichiers Laravel...
if not exist artisan (
    echo ERROR: Fichier artisan non trouve. Ce n'est pas un projet Laravel!
    pause
    exit /b 1
)

if not exist composer.json (
    echo ERROR: Fichier composer.json non trouve. Ce n'est pas un projet PHP!
    pause
    exit /b 1
)

echo.
echo Demarrage du serveur Laravel...
echo Le serveur sera accessible sur: http://localhost:8000
echo Appuyez sur Ctrl+C pour arreter le serveur
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause
