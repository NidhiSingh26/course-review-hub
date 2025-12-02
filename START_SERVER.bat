@echo off
echo Starting PHP Development Server...
echo.

REM Try common PHP paths
if exist "C:\xampp\php\php.exe" (
    echo Found PHP at C:\xampp\php\php.exe
    C:\xampp\php\php.exe -S localhost:8000 -t public
    goto end
)

if exist "C:\wamp64\bin\php\php8.2.0\php.exe" (
    echo Found PHP at C:\wamp64\bin\php\php8.2.0\php.exe
    C:\wamp64\bin\php\php8.2.0\php.exe -S localhost:8000 -t public
    goto end
)

if exist "C:\php\php.exe" (
    echo Found PHP at C:\php\php.exe
    C:\php\php.exe -S localhost:8000 -t public
    goto end
)

REM If PHP is in PATH
php -S localhost:8000 -t public 2>nul
if %errorlevel% equ 0 goto end

echo.
echo ERROR: PHP not found!
echo.
echo Please install PHP from one of these options:
echo 1. Download from https://windows.php.net/download/
echo 2. Install XAMPP from https://www.apachefriends.org/
echo 3. Install WAMP from https://www.wampserver.com/
echo.
echo After installing, either:
echo - Add PHP to your system PATH, OR
echo - Update this batch file with your PHP path
echo.
pause
:end

