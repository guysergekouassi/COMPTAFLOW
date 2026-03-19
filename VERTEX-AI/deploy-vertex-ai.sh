#!/bin/bash

##############################################################################
# DEPLOYMENT SCRIPT : VERTEX AI MIGRATION
# 
# Utilisation : bash deploy-vertex-ai.sh
# Durée : ~2 min
##############################################################################

set -e  # Exit on error

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   DEPLOYMENT : VISION API → VERTEX AI                         ║${NC}"
echo -e "${BLUE}║   Project : FLOW AI - ComptaFlow                              ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

##############################################################################
# CONFIGURATION
##############################################################################

PROJECT_DIR=$(pwd)
SERVICE_FILE="VertexAiService.php"
CONTROLLER_FILE="IaController_VERTEX_AI.php"
ENV_FILE=".env.VERTEX_AI"

##############################################################################
# FUNCTIONS
##############################################################################

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

##############################################################################
# MAIN DEPLOYMENT
##############################################################################

main() {
    
    # 1. Vérifier présence des fichiers
    echo ""
    log_info "Vérification des fichiers source..."
    
    if [ ! -f "$SERVICE_FILE" ]; then
        log_error "Fichier manquant : $SERVICE_FILE"
        exit 1
    fi
    log_success "$SERVICE_FILE présent"
    
    if [ ! -f "$CONTROLLER_FILE" ]; then
        log_error "Fichier manquant : $CONTROLLER_FILE"
        exit 1
    fi
    log_success "$CONTROLLER_FILE présent"
    
    if [ ! -f "$ENV_FILE" ]; then
        log_warning "$ENV_FILE manquant (optionnel - pour référence)"
    else
        log_success "$ENV_FILE présent"
    fi
    
    # 2. Vérifier PHP syntax
    echo ""
    log_info "Vérification syntaxe PHP..."
    
    if php -l "$SERVICE_FILE" > /dev/null 2>&1; then
        log_success "$SERVICE_FILE syntaxe OK"
    else
        log_error "Erreur syntaxe : $SERVICE_FILE"
        php -l "$SERVICE_FILE"
        exit 1
    fi
    
    if php -l "$CONTROLLER_FILE" > /dev/null 2>&1; then
        log_success "$CONTROLLER_FILE syntaxe OK"
    else
        log_error "Erreur syntaxe : $CONTROLLER_FILE"
        php -l "$CONTROLLER_FILE"
        exit 1
    fi
    
    # 3. Créer backup
    echo ""
    log_info "Création backups..."
    
    BACKUP_DATE=$(date +%Y%m%d_%H%M%S)
    
    if [ -f "app/Services/VertexAiService.php" ]; then
        cp app/Services/VertexAiService.php "app/Services/VertexAiService.php.backup.$BACKUP_DATE"
        log_success "Backup VertexAiService existant"
    fi
    
    if [ -f "app/Http/Controllers/IaController.php" ]; then
        cp app/Http/Controllers/IaController.php "app/Http/Controllers/IaController.php.backup.$BACKUP_DATE"
        log_success "Backup IaController créé"
    fi
    
    # 4. Copier les fichiers
    echo ""
    log_info "Installation des fichiers..."
    
    cp "$SERVICE_FILE" app/Services/VertexAiService.php
    log_success "VertexAiService.php installé"
    
    cp "$CONTROLLER_FILE" app/Http/Controllers/IaController.php
    log_success "IaController.php installé"
    
    # 5. Permissions
    echo ""
    log_info "Définition des permissions..."
    
    chmod 644 app/Services/VertexAiService.php
    chmod 644 app/Http/Controllers/IaController.php
    log_success "Permissions OK"
    
    # 6. Laravel cache clear
    echo ""
    log_info "Nettoyage cache Laravel..."
    
    php artisan cache:clear
    log_success "Cache vidé"
    
    php artisan config:clear
    log_success "Config cachée vidée"
    
    php artisan route:clear
    log_success "Routes cache vidée"
    
    # 7. Test connectivité Vertex AI
    echo ""
    log_info "Test connectivité Vertex AI..."
    
    TEST_OUTPUT=$(php artisan tinker <<'EOT'
echo json_encode(App\Services\VertexAiService::testConnection());
EOT
)
    
    if echo "$TEST_OUTPUT" | grep -q "ok"; then
        log_success "Vertex AI connecté ✓"
        echo "$TEST_OUTPUT" | jq '.'
    else
        log_warning "Vertex AI non connecté"
        log_warning "Vérifiez : gcloud auth application-default login"
        echo "$TEST_OUTPUT"
    fi
    
    # 8. Résumé
    echo ""
    echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}✅ DEPLOYMENT RÉUSSI${NC}"
    echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
    echo ""
    
    echo "Fichiers installés :"
    echo "  ✓ app/Services/VertexAiService.php"
    echo "  ✓ app/Http/Controllers/IaController.php"
    echo ""
    
    echo "Backups (si rollback nécessaire) :"
    [ -f "app/Services/VertexAiService.php.backup.$BACKUP_DATE" ] && echo "  - app/Services/VertexAiService.php.backup.$BACKUP_DATE"
    [ -f "app/Http/Controllers/IaController.php.backup.$BACKUP_DATE" ] && echo "  - app/Http/Controllers/IaController.php.backup.$BACKUP_DATE"
    echo ""
    
    echo "Prochaines étapes :"
    echo "  1. Mettre à jour .env (copier variables de $ENV_FILE)"
    echo "  2. Vérifier : gcloud auth application-default login"
    echo "  3. Tester API : curl -X POST https://votre-app/api/comptaflow/factures/scan ..."
    echo "  4. Monitorer logs : tail -f storage/logs/laravel.log"
    echo ""
    
    echo -e "${BLUE}Documentation : MIGRATION_GUIDE_VERTEX_AI.md${NC}"
    echo -e "${BLUE}Checklist : DEPLOYMENT_CHECKLIST.md${NC}"
    
}

##############################################################################
# ERROR HANDLING
##############################################################################

trap 'log_error "Deployment échoué"; exit 1' ERR

##############################################################################
# EXECUTE
##############################################################################

main "$@"
