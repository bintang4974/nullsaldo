# Docker Setup untuk NullSaldo

Setup Docker lengkap untuk aplikasi Laravel NullSaldo dengan Nginx, MySQL, dan PHPMyAdmin.

## 📋 Persyaratan

- Docker Desktop (v24+) - [Download](https://www.docker.com/products/docker-desktop)
- Docker Compose (v2.0+) - Biasanya sudah termasuk dalam Docker Desktop

## 🚀 Quick Start

### 1. Persiapan File Environment

```bash
# Copy environment file
cp .env.docker .env
```

Atau edit `.env` sesuai kebutuhan Anda:
```env
DB_DATABASE=nullsaldo
DB_USERNAME=nullsaldo
DB_PASSWORD=password
DB_ROOT_PASSWORD=root
```

### 2. Build dan Start Containers

```bash
# Build image dan start services
docker-compose up -d --build

# Atau jika sudah pernah build sebelumnya
docker-compose up -d
```

### 3. Verifikasi Setup

```bash
# Cek status services
docker-compose ps

# Lihat logs aplikasi
docker-compose logs -f app

# Atau cek mysql
docker-compose logs mysql
```

## 🌐 Akses Aplikasi

| Service | URL | Keterangan |
|---------|-----|-----------|
| **Aplikasi** | http://localhost | Aplikasi Laravel |
| **PHPMyAdmin** | http://localhost:8081 | Database Management |
| **MySQL** | localhost:3306 | Host: `mysql`, User: `nullsaldo` |

## 🔧 Konfigurasi

### Ubah Port (jika diperlukan)

Edit `.env`:
```env
# Port aplikasi (default: 80)
APP_PORT=8080

# Port MySQL (default: 3306)
MYSQL_PORT=3307

# Port PHPMyAdmin (default: 8081)
PHPMYADMIN_PORT=8082
```

Lalu restart:
```bash
docker-compose down
docker-compose up -d
```

### Database Credentials

PHPMyAdmin:
- **Server**: mysql
- **Username**: nullsaldo (atau root)
- **Password**: password (atau root untuk root user)

## 📝 Command Useful

### Artisan Commands

```bash
# Jalankan artisan command
docker-compose exec app php artisan migrate:refresh --seed

# Tinker (interactive shell)
docker-compose exec app php artisan tinker

# Jalankan tests
docker-compose exec app php artisan test

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Composer & NPM

```bash
# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Build assets
docker-compose exec app npm run build

# Development mode
docker-compose exec app npm run dev
```

### Database

```bash
# Backup database
docker-compose exec mysql mysqldump -u nullsaldo -ppassword nullsaldo > backup.sql

# Restore database
docker exec nullsaldo-mysql mysql -u nullsaldo -ppassword nullsaldo < backup.sql

# Shell MySQL
docker-compose exec mysql mysql -u nullsaldo -ppassword nullsaldo
```

### Logs

```bash
# Lihat semua logs
docker-compose logs -f

# Hanya logs app
docker-compose logs -f app

# Hanya logs MySQL
docker-compose logs -f mysql

# Last 50 lines
docker-compose logs --tail=50
```

## 🧹 Cleanup

```bash
# Stop containers
docker-compose down

# Remove containers dan volumes
docker-compose down -v

# Remove images juga
docker-compose down -v --rmi all

# Rebuild cache (jika ada issue)
docker-compose build --no-cache
```

## 🔄 Auto-Initialization

Container akan otomatis:
1. ✅ Menunggu MySQL siap
2. ✅ Generate APP_KEY (jika belum ada)
3. ✅ Jalankan migrations
4. ✅ Cache configuration & routes
5. ✅ Start Nginx + PHP-FPM

## 🐛 Troubleshooting

### "Connection refused" saat startup

```bash
# Cek status MySQL
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql

# Tunggu beberapa saat, lalu cek lagi
docker-compose logs app
```

### Permission denied errors

```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /app/storage
docker-compose exec app chown -R www-data:www-data /app/bootstrap/cache
```

### Cache/Session issues

```bash
# Clear semua cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Storage symlink error

```bash
docker-compose exec app php artisan storage:link
```

## 📊 Resources

### Container Limits (dapat disesuaikan di docker-compose.yml)

- **PHP-FPM**: 5 processes, 256M memory per process
- **Nginx**: Konfigurasi di `docker/nginx.conf`
- **MySQL**: 1GB max allocation default

### Untuk Production

Tambahkan di `docker-compose.yml`:
```yaml
services:
  app:
    resources:
      limits:
        cpus: '1'
        memory: 512M
      reservations:
        cpus: '0.5'
        memory: 256M
```

## 📚 File Structure

```
.
├── Dockerfile           # Multi-stage build
├── docker-compose.yml   # Services configuration
├── .dockerignore        # Files to exclude
├── docker/
│   ├── entrypoint.sh    # Startup script
│   ├── nginx.conf       # Nginx configuration
│   ├── php-fpm.conf     # PHP-FPM settings
│   ├── supervisord.conf # Process management
│   └── mysql.cnf        # MySQL configuration
└── .env.docker          # Environment template
```

## ✨ Features

✅ Multi-stage Docker build (optimized size)  
✅ Health checks untuk semua services  
✅ Automatic database migrations  
✅ PHP-FPM + Nginx (production-ready)  
✅ Supervisor untuk process management  
✅ Gzip compression enabled  
✅ Security headers configured  
✅ Environment-based configuration  
✅ Volume binding untuk development  
✅ PHPMyAdmin untuk database management  

## 🆘 Support

Untuk masalah atau pertanyaan, cek:
- Docker logs: `docker-compose logs`
- MySQL: `docker-compose exec mysql mysql -u root -proot -e "show processlist;"`
- PHP errors: `docker-compose exec app tail -f storage/logs/laravel.log`

---

**Happy Coding! 🚀**
