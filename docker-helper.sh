#!/bin/bash

# NullSaldo Docker Helper Script
# Usage: ./docker-helper.sh [command]

set -e

DOCKER_CMD="docker-compose"
APP_CONTAINER="nullsaldo-app"
MYSQL_CONTAINER="nullsaldo-mysql"

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Help function
show_help() {
    cat << EOF
${BLUE}NullSaldo Docker Helper${NC}

Usage: ./docker-helper.sh [command]

${GREEN}Container Management:${NC}
  up              Start all containers
  down            Stop all containers
  restart         Restart all containers
  ps              Show container status
  logs            Show all logs (use -f for follow)
  clean           Remove containers and volumes

${GREEN}Laravel Commands:${NC}
  artisan [cmd]   Run artisan command (e.g., ./docker-helper.sh artisan migrate)
  tinker          Start tinker shell
  test            Run tests
  migrate         Run migrations
  seed            Run seeders
  fresh           Fresh migration (reset & seed)

${GREEN}Composer & NPM:${NC}
  composer [cmd]  Run composer (e.g., ./docker-helper.sh composer install)
  npm [cmd]       Run npm (e.g., ./docker-helper.sh npm install)
  build           Build assets (npm run build)

${GREEN}Database:${NC}
  db-shell        MySQL interactive shell
  db-backup       Backup database to backup.sql
  db-restore      Restore database from backup.sql

${GREEN}Development:${NC}
  shell           Access app container shell
  cache-clear     Clear all caches
  cache-build     Build caches
  fresh-build     Clean rebuild

${GREEN}Utils:${NC}
  help            Show this help message
  version         Show versions

EOF
}

# Utility functions
run_artisan() {
    $DOCKER_CMD exec $APP_CONTAINER php artisan "$@"
}

run_composer() {
    $DOCKER_CMD exec $APP_CONTAINER composer "$@"
}

run_npm() {
    $DOCKER_CMD exec $APP_CONTAINER npm "$@"
}

run_shell() {
    $DOCKER_CMD exec -it $APP_CONTAINER /bin/bash
}

run_tinker() {
    $DOCKER_CMD exec -it $APP_CONTAINER php artisan tinker
}

# Main command handling
case "$1" in
    # Container Management
    up)
        echo -e "${BLUE}Starting containers...${NC}"
        $DOCKER_CMD up -d
        sleep 3
        echo -e "${GREEN}✓ Containers started!${NC}"
        $DOCKER_CMD ps
        ;;
    down)
        echo -e "${BLUE}Stopping containers...${NC}"
        $DOCKER_CMD down
        echo -e "${GREEN}✓ Containers stopped!${NC}"
        ;;
    restart)
        echo -e "${BLUE}Restarting containers...${NC}"
        $DOCKER_CMD restart
        echo -e "${GREEN}✓ Containers restarted!${NC}"
        ;;
    ps)
        $DOCKER_CMD ps
        ;;
    logs)
        shift
        $DOCKER_CMD logs "$@"
        ;;
    clean)
        echo -e "${YELLOW}This will remove all containers and volumes!${NC}"
        read -p "Continue? (y/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            $DOCKER_CMD down -v
            echo -e "${GREEN}✓ Cleaned up!${NC}"
        fi
        ;;

    # Laravel Commands
    artisan)
        shift
        run_artisan "$@"
        ;;
    tinker)
        run_tinker
        ;;
    test)
        run_artisan test
        ;;
    migrate)
        shift
        run_artisan migrate "$@"
        ;;
    seed)
        run_artisan db:seed
        ;;
    fresh)
        run_artisan migrate:refresh --seed
        ;;

    # Composer & NPM
    composer)
        shift
        run_composer "$@"
        ;;
    npm)
        shift
        run_npm "$@"
        ;;
    build)
        echo -e "${BLUE}Building assets...${NC}"
        run_npm run build
        echo -e "${GREEN}✓ Assets built!${NC}"
        ;;

    # Database
    db-shell)
        $DOCKER_CMD exec -it $MYSQL_CONTAINER mysql -u nullsaldo -ppassword nullsaldo
        ;;
    db-backup)
        echo -e "${BLUE}Backing up database...${NC}"
        $DOCKER_CMD exec $MYSQL_CONTAINER mysqldump -u nullsaldo -ppassword nullsaldo > backup.sql
        echo -e "${GREEN}✓ Database backed up to backup.sql${NC}"
        ;;
    db-restore)
        if [ ! -f "backup.sql" ]; then
            echo -e "${RED}✗ backup.sql not found!${NC}"
            exit 1
        fi
        echo -e "${BLUE}Restoring database...${NC}"
        $DOCKER_CMD exec $MYSQL_CONTAINER mysql -u nullsaldo -ppassword nullsaldo < backup.sql
        echo -e "${GREEN}✓ Database restored!${NC}"
        ;;

    # Development
    shell)
        run_shell
        ;;
    cache-clear)
        echo -e "${BLUE}Clearing caches...${NC}"
        run_artisan cache:clear
        run_artisan config:clear
        run_artisan view:clear
        echo -e "${GREEN}✓ Caches cleared!${NC}"
        ;;
    cache-build)
        echo -e "${BLUE}Building caches...${NC}"
        run_artisan config:cache
        run_artisan route:cache
        run_artisan view:cache
        echo -e "${GREEN}✓ Caches built!${NC}"
        ;;
    fresh-build)
        echo -e "${BLUE}Fresh rebuild...${NC}"
        $DOCKER_CMD down -v
        $DOCKER_CMD up -d --build
        sleep 5
        echo -e "${GREEN}✓ Fresh build complete!${NC}"
        ;;

    # Utils
    help|--help|-h)
        show_help
        ;;
    version)
        echo -e "${BLUE}Version Information:${NC}"
        echo "Docker: $(docker --version)"
        echo "Docker Compose: $(docker-compose --version)"
        echo "PHP: $($DOCKER_CMD exec $APP_CONTAINER php -v | head -n 1)"
        echo "Laravel: $($DOCKER_CMD exec $APP_CONTAINER php artisan --version)"
        ;;
    *)
        echo -e "${RED}Unknown command: $1${NC}"
        echo "Use './docker-helper.sh help' for usage information"
        exit 1
        ;;
esac
