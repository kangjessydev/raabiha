@echo off
title Raabiha Print Agent
echo ======================================
echo   Raabiha Print Agent
echo ======================================
echo.

:: Cek Node.js
node --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Node.js tidak terinstall!
    echo         Download di: https://nodejs.org
    pause
    exit /b 1
)

:: Cek dependencies
if not exist "%~dp0node_modules" (
    echo [INFO] Install dependencies...
    cd /d "%~dp0"
    npm install --omit=dev
)

:: Jalankan agent
echo [INFO] Menjalankan Print Agent...
echo [INFO] Jangan tutup window ini selagi POS dipakai
echo.

cd /d "%~dp0"
node src/index.js

pause
