@echo off
echo ========================================================
echo  Kravyo - 1-Click Database Export (for Git & Sync)
echo ========================================================
echo.
echo Exporting kravyo_db to database/kravyo_complete_dump.sql...

if exist "C:\xampp\mysql\bin\mysqldump.exe" (
    "C:\xampp\mysql\bin\mysqldump.exe" -u root kravyo_db > "database\kravyo_temp.sql"
    powershell -Command "Get-Content 'database\kravyo_temp.sql' | Set-Content -Encoding utf8 'database\kravyo_complete_dump.sql'; Remove-Item 'database\kravyo_temp.sql'"
    echo.
    echo [SUCCESS] Database successfully exported to database/kravyo_complete_dump.sql!
    echo You can now commit and push to Git.
) else (
    echo [ERROR] mysqldump not found at C:\xampp\mysql\bin\mysqldump.exe!
    echo Please make sure XAMPP is installed in C:\xampp.
)

echo.
pause
