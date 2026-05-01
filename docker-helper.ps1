# NullSaldo Docker Helper Script for PowerShell
# Usage: .\docker-helper.ps1 [command] [args]

param(
    [string]$Command = "help",
    [string[]]$Args = @()
)

$dockerCmd = "docker-compose"
$appContainer = "nullsaldo-app"
$mysqlContainer = "nullsaldo-mysql"

function Show-Help {
    Write-Host "NullSaldo Docker Helper" -ForegroundColor Blue
    Write-Host ""
    Write-Host "Usage: .\docker-helper.ps1 [command] [args]" -ForegroundColor Gray
    Write-Host ""
    
    Write-Host "Container Management:" -ForegroundColor Green
    Write-Host "  up              Start all containers"
    Write-Host "  down            Stop all containers"
    Write-Host "  restart         Restart all containers"
    Write-Host "  ps              Show container status"
    Write-Host "  logs            Show all logs"
    Write-Host "  clean           Remove containers and volumes"
    Write-Host ""
    
    Write-Host "Laravel Commands:" -ForegroundColor Green
    Write-Host "  artisan cmd     Run artisan command"
    Write-Host "  tinker          Start tinker shell"
    Write-Host "  test            Run tests"
    Write-Host "  migrate         Run migrations"
    Write-Host "  seed            Run seeders"
    Write-Host "  fresh           Fresh migration"
    Write-Host ""
    
    Write-Host "Composer & NPM:" -ForegroundColor Green
    Write-Host "  composer cmd    Run composer command"
    Write-Host "  npm cmd         Run npm command"
    Write-Host "  build           Build assets"
    Write-Host ""
    
    Write-Host "Database:" -ForegroundColor Green
    Write-Host "  db-shell        MySQL shell"
    Write-Host "  db-backup       Backup database"
    Write-Host "  db-restore      Restore database"
    Write-Host ""
    
    Write-Host "Development:" -ForegroundColor Green
    Write-Host "  shell           Access app shell"
    Write-Host "  cache-clear     Clear caches"
    Write-Host "  cache-build     Build caches"
    Write-Host "  fresh-build     Clean rebuild"
    Write-Host ""
    
    Write-Host "Utils:" -ForegroundColor Green
    Write-Host "  help            Show this help"
    Write-Host "  version         Show versions"
    Write-Host ""
}

function Run-Artisan {
    & $dockerCmd exec $appContainer php artisan @args
}

function Run-Composer {
    & $dockerCmd exec $appContainer composer @args
}

function Run-Npm {
    & $dockerCmd exec $appContainer npm @args
}

switch ($Command) {
    "up" {
        Write-Host "Starting containers..." -ForegroundColor Blue
        & $dockerCmd up -d
        Start-Sleep -Seconds 3
        Write-Host "✓ Containers started!" -ForegroundColor Green
        & $dockerCmd ps
    }
    "down" {
        Write-Host "Stopping containers..." -ForegroundColor Blue
        & $dockerCmd down
        Write-Host "✓ Containers stopped!" -ForegroundColor Green
    }
    "restart" {
        Write-Host "Restarting containers..." -ForegroundColor Blue
        & $dockerCmd restart
        Write-Host "✓ Containers restarted!" -ForegroundColor Green
    }
    "ps" {
        & $dockerCmd ps
    }
    "logs" {
        & $dockerCmd logs -f @args
    }
    "clean" {
        Write-Host "This will remove all containers and volumes!" -ForegroundColor Yellow
        $response = Read-Host "Continue? (y/N)"
        if ($response -eq "y" -or $response -eq "Y") {
            & $dockerCmd down -v
            Write-Host "✓ Cleaned up!" -ForegroundColor Green
        }
    }
    
    # Laravel Commands
    "artisan" {
        Run-Artisan @args
    }
    "tinker" {
        & $dockerCmd exec -it $appContainer php artisan tinker
    }
    "test" {
        Run-Artisan test
    }
    "migrate" {
        Run-Artisan migrate @args
    }
    "seed" {
        Run-Artisan db:seed
    }
    "fresh" {
        Run-Artisan migrate:refresh --seed
    }
    
    # Composer & NPM
    "composer" {
        Run-Composer @args
    }
    "npm" {
        Run-Npm @args
    }
    "build" {
        Write-Host "Building assets..." -ForegroundColor Blue
        Run-Npm run build
        Write-Host "✓ Assets built!" -ForegroundColor Green
    }
    
    # Database
    "db-shell" {
        & $dockerCmd exec -it $mysqlContainer mysql -u nullsaldo -ppassword nullsaldo
    }
    "db-backup" {
        Write-Host "Backing up database..." -ForegroundColor Blue
        & $dockerCmd exec $mysqlContainer mysqldump -u nullsaldo -ppassword nullsaldo | Out-File -FilePath "backup.sql"
        Write-Host "✓ Database backed up to backup.sql" -ForegroundColor Green
    }
    "db-restore" {
        if (-not (Test-Path "backup.sql")) {
            Write-Host "✗ backup.sql not found!" -ForegroundColor Red
            exit 1
        }
        Write-Host "Restoring database..." -ForegroundColor Blue
        Get-Content "backup.sql" | & $dockerCmd exec -i $mysqlContainer mysql -u nullsaldo -ppassword nullsaldo
        Write-Host "✓ Database restored!" -ForegroundColor Green
    }
    
    # Development
    "shell" {
        & $dockerCmd exec -it $appContainer /bin/bash
    }
    "cache-clear" {
        Write-Host "Clearing caches..." -ForegroundColor Blue
        Run-Artisan cache:clear
        Run-Artisan config:clear
        Run-Artisan view:clear
        Write-Host "✓ Caches cleared!" -ForegroundColor Green
    }
    "cache-build" {
        Write-Host "Building caches..." -ForegroundColor Blue
        Run-Artisan config:cache
        Run-Artisan route:cache
        Run-Artisan view:cache
        Write-Host "✓ Caches built!" -ForegroundColor Green
    }
    "fresh-build" {
        Write-Host "Fresh rebuild..." -ForegroundColor Blue
        & $dockerCmd down -v
        & $dockerCmd up -d --build
        Start-Sleep -Seconds 5
        Write-Host "✓ Fresh build complete!" -ForegroundColor Green
    }
    
    # Utils
    "help" {
        Show-Help
    }
    "version" {
        Write-Host "Version Information:" -ForegroundColor Blue
        docker --version
        docker-compose --version
        & $dockerCmd exec $appContainer php -v | Select-Object -First 1
        & $dockerCmd exec $appContainer php artisan --version
    }
    
    default {
        Write-Host "Unknown command: $Command" -ForegroundColor Red
        Write-Host "Use '.\docker-helper.ps1 help' for usage information"
        exit 1
    }
}
