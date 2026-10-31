@echo off
title Kravyo AI Recommendation Server
echo ========================================================
echo  Kravyo - Python AI Recommendation Microservice
echo  Server URL: http://127.0.0.1:5000/
echo ========================================================
echo.

cd /d "c:\xampp\htdocs\Kravyo\python_ai"

echo Starting AI Microservice (Flask + Scikit-learn + Pandas)...
echo Keep this window OPEN while running Kravyo.
echo.

python app.py

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Failed to start Python AI Server.
    echo Please make sure Python is installed and requirements are installed:
    echo pip install -r requirements.txt
    echo.
    pause
)
