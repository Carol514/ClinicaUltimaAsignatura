@echo off
setlocal

echo [DB] Subiendo MySQL y phpMyAdmin...
docker-compose up -d

echo [APP] Regenerando autoload...
composer dump-autoload

echo [DB] Migrando desde cero y sembrando datos...
php artisan migrate:fresh --seed

echo [OK] Base de datos lista.
pause
