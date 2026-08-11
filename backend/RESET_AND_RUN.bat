@echo off
REM Reset and run Laravel backend

cd /d "%~dp0"

echo.
echo ==================================================
echo  AgriTech Backend - Reset & Run Script
echo ==================================================
echo.

REM Check if backend folder exists
if not exist "composer.json" (
    echo ERROR: Not in Laravel project directory!
    exit /b 1
)

echo [1/6] Clearing cache...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo ✓ Cache cleared

echo.
echo [2/6] Clearing optimized autoloader...
php artisan optimize:clear
echo ✓ Optimized cache cleared

echo.
echo [3/6] Dumping autoloader...
composer dump-autoload -o
echo ✓ Autoloader updated

echo.
echo [4/6] Caching routes...
php artisan route:cache
echo ✓ Routes cached

echo.
echo [5/6] Checking database connection...
php artisan db:show >nul 2>&1
if %errorlevel% neq 0 (
    echo ✗ Database connection failed!
    echo   Check your .env file and PostgreSQL is running
    pause
    exit /b 1
)
echo ✓ Database connected

echo.
echo [6/6] Running pending migrations...
php artisan migrate
echo ✓ Migrations completed

echo.
echo ==================================================
echo  Backend ready! Starting server...
echo ==================================================
echo.

REM Start the development server
php artisan serve

pause
