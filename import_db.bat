@echo off
echo ========================================================
echo  Kravyo - 1-Click Database Import
echo ========================================================
echo.
echo Importing database/kravyo_complete_dump.sql into kravyo_db...

if exist "C:\xampp\mysql\bin\mysql.exe" (
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS kravyo_db;"
    powershell -Command "Get-Content 'database\kravyo_complete_dump.sql' | C:\xampp\mysql\bin\mysql.exe -u root kravyo_db"
    echo.
    echo [SUCCESS] Database kravyo_db updated with all latest tables and records!
) else (
    echo [ERROR] mysql.exe not found at C:\xampp\mysql\bin\mysql.exe!
    echo Please make sure XAMPP is installed in C:\xampp and MySQL is running.
)

echo.
pause
