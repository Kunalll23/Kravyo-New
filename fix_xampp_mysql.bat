@echo off
echo Stopping MySQL if it is running...
powershell -Command "Stop-Process -Name 'mysqld' -Force -ErrorAction SilentlyContinue"

echo.
echo Backing up and restoring MySQL data folder...
powershell -Command "$mysqlDir = 'C:\xampp\mysql'; $dataDir = '$mysqlDir\data'; $oldDataDir = '$mysqlDir\data_old'; $backupDir = '$mysqlDir\backup'; if (Test-Path $oldDataDir) { Remove-Item -Recurse -Force $oldDataDir }; Rename-Item -Path $dataDir -NewName 'data_old'; New-Item -ItemType Directory -Path $dataDir | Out-Null; Copy-Item -Path '$backupDir\*' -Destination $dataDir -Recurse -Force; $excludeFolders = @('mysql', 'performance_schema', 'phpmyadmin', 'test'); Get-ChildItem -Path $oldDataDir -Directory | Where-Object { $_.Name -notin $excludeFolders } | ForEach-Object { Copy-Item -Path $_.FullName -Destination $dataDir -Recurse -Force }; Copy-Item -Path '$oldDataDir\ibdata1' -Destination $dataDir -Force;"

echo.
echo Done! Your database has been repaired.
echo You can now start MySQL from the XAMPP Control Panel.
pause
