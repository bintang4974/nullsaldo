# 🐳 Quick Start Docker

## ⚡ Fastest Setup (3 Steps)

### 1️⃣ Copy environment file
```powershell
cp .env.docker .env
```

### 2️⃣ Start Docker
```powershell
# Windows PowerShell
.\docker-helper.ps1 up

# Or Linux/Mac bash
./docker-helper.sh up
```

### 3️⃣ Access aplikasi
- **App**: http://localhost
- **PHPMyAdmin**: http://localhost:8081

---

## 🎯 Common Commands

```powershell
# Windows PowerShell
.\docker-helper.ps1 up           # Start
.\docker-helper.ps1 down         # Stop
.\docker-helper.ps1 artisan migrate  # Run migration
.\docker-helper.ps1 npm install  # Install npm
```

```bash
# Linux/Mac
./docker-helper.sh up
./docker-helper.sh down
./docker-helper.sh artisan migrate
./docker-helper.sh npm install
```

---

## 📚 Complete Documentation

Lihat [DOCKER.md](DOCKER.md) untuk dokumentasi lengkap dengan semua command dan troubleshooting.

---

## 🔧 Environment Variables

Edit `.env`:
```env
DB_DATABASE=nullsaldo
DB_USERNAME=nullsaldo
DB_PASSWORD=password
DB_ROOT_PASSWORD=root
APP_PORT=80
PHPMYADMIN_PORT=8081
```

---

## 🚀 What's Included

✅ **PHP 8.3** with Nginx  
✅ **MySQL 8.0** database  
✅ **PHPMyAdmin** for database management  
✅ **Auto migrations** on startup  
✅ **Auto composer install**  
✅ **Health checks** for all services  
✅ **Production-ready** configuration  

---

**Happy coding! 🎉**
