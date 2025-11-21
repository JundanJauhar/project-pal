@echo off
echo Optimizing PT PAL Project...
echo.

echo [1/6] Clearing cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo.
echo [2/6] Optimizing configuration...
php artisan config:cache

echo.
echo [3/6] Optimizing routes...
php artisan route:cache

echo.
echo [4/6] Optimizing views...
php artisan view:cache

echo.
echo [5/6] Optimizing autoloader...
composer dump-autoload -o

echo.
echo [6/6] Running full optimization...
php artisan optimize

echo.
echo ================================
echo Optimization Complete!
echo ================================
pause
