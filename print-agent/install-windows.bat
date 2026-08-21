@echo off
:: ============================================================
:: Raabiha Print Agent — Windows Auto-Start Installer
:: Jalankan sebagai Administrator (klik kanan → Run as administrator)
:: Hanya perlu dilakukan SEKALI per komputer kasir
:: ============================================================

setlocal enabledelayedexpansion
set AGENT_DIR=%~dp0
set SERVICE_NAME=RaabihaAgent

echo ============================================================
echo   Raabiha Print Agent - Setup Otomatis
echo ============================================================
echo.

:: Cek apakah jalan sebagai Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Harap jalankan file ini sebagai Administrator!
    echo         Klik kanan pada file ini, pilih "Run as administrator"
    echo.
    pause
    exit /b 1
)

:: Cek Node.js
echo [1/4] Memeriksa Node.js...
where node >nul 2>&1
if %errorLevel% neq 0 (
    echo [INFO] Node.js belum terinstall. Membuka halaman download...
    start https://nodejs.org/en/download/
    echo.
    echo Silakan install Node.js terlebih dahulu, lalu jalankan file ini lagi.
    pause
    exit /b 1
)
for /f "tokens=*" %%v in ('node --version') do set NODE_VER=%%v
echo [OK] Node.js ditemukan: %NODE_VER%

:: Install dependencies
echo.
echo [2/4] Menginstall dependencies...
cd /d "%AGENT_DIR%"
call npm install --omit=dev --silent
if %errorLevel% neq 0 (
    echo [ERROR] Gagal install dependencies!
    pause
    exit /b 1
)
echo [OK] Dependencies terinstall.

:: Hapus service lama jika ada
echo.
echo [3/4] Mendaftarkan Print Agent sebagai Windows Service...
sc query %SERVICE_NAME% >nul 2>&1
if %errorLevel% equ 0 (
    echo [INFO] Service lama ditemukan, menghapus...
    sc stop %SERVICE_NAME% >nul 2>&1
    sc delete %SERVICE_NAME% >nul 2>&1
    timeout /t 2 /nobreak >nul
)

:: Cari path node.exe
for /f "tokens=*" %%n in ('where node') do set NODE_PATH=%%n

:: Daftarkan sebagai Windows Service menggunakan sc create
:: binPath harus menggunakan cmd /c karena node bukan service native
set "SCRIPT_PATH=%AGENT_DIR%src\index.js"
sc create %SERVICE_NAME% ^
    binPath= "cmd /c node \"%SCRIPT_PATH%\"" ^
    DisplayName= "Raabiha Print Agent" ^
    start= auto ^
    obj= LocalSystem >nul 2>&1

if %errorLevel% neq 0 (
    echo [WARNING] sc create kurang berhasil, mencoba alternatif dengan NSSM...
    goto :try_nssm
)

:: Set deskripsi service
sc description %SERVICE_NAME% "Layanan cetak struk untuk Raabiha POS. Menghubungkan browser ke printer Bluetooth." >nul 2>&1

:: Jalankan service
sc start %SERVICE_NAME% >nul 2>&1
echo [OK] Service berhasil didaftarkan dan dijalankan.
goto :success

:try_nssm
:: Alternatif: gunakan Task Scheduler (lebih kompatibel tanpa NSSM)
echo [INFO] Mendaftarkan via Task Scheduler...
set "START_BAT=%AGENT_DIR%start.bat"
schtasks /delete /tn "%SERVICE_NAME%" /f >nul 2>&1
schtasks /create ^
    /tn "%SERVICE_NAME%" ^
    /tr "\"%START_BAT%\"" ^
    /sc onlogon ^
    /rl highest ^
    /f >nul 2>&1

if %errorLevel% neq 0 (
    echo [ERROR] Gagal mendaftarkan ke Task Scheduler.
    pause
    exit /b 1
)
echo [OK] Berhasil didaftarkan via Task Scheduler.

:: Jalankan sekarang juga
echo [INFO] Menjalankan Print Agent sekarang...
start /b "" cmd /c node "%AGENT_DIR%src\index.js"

:success
echo.
echo [4/4] Selesai!
echo.
echo ============================================================
echo   Setup berhasil!
echo.
echo   Print Agent akan otomatis berjalan setiap kali
echo   komputer dinyalakan.
echo.
echo   Kasir cukup:
echo   1. Pair printer di Bluetooth Settings Windows
echo      (seperti pair headphone)
echo   2. Buka raabiha.com/pos di Chrome
echo   3. Klik "Sambungkan via Bluetooth"
echo   4. Selesai!
echo ============================================================
echo.
pause
