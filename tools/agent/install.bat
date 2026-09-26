@echo off
REM ============================================================
REM  Instalasi SimKom Agent - Monitoring PC Lab Komputer
REM  Tanpa admin: menyalin ke %LOCALAPPDATA%\SimKomAgent lalu
REM  mendaftarkan auto-start saat user login (registry HKCU Run).
REM ============================================================

setlocal
set "SRC=%~dp0"
set "TARGET=%LOCALAPPDATA%\SimKomAgent"

echo.
echo ============================================
echo    Instalasi SimKom Agent (Monitoring PC)
echo ============================================
echo.

if not exist "%SRC%SimKomAgent.exe" (
    echo [ERROR] SimKomAgent.exe tidak ditemukan di:
    echo         %SRC%
    pause
    exit /b 1
)

REM --- Matikan instance lama DULU agar file tidak terkunci ---
taskkill /IM SimKomAgent.exe /F >nul 2>&1
timeout /t 1 /nobreak >nul

mkdir "%TARGET%" 2>nul
copy /Y "%SRC%SimKomAgent.exe" "%TARGET%\SimKomAgent.exe" >nul
if errorlevel 1 (
    echo [ERROR] Gagal menyalin exe ke %TARGET%
    echo         Pastikan SimKomAgent.exe tidak sedang berjalan:
    echo         taskkill /IM SimKomAgent.exe /F
    echo         lalu jalankan install.bat ini lagi.
    pause
    exit /b 1
)

if exist "%TARGET%\config.json" (
    echo [OK] config.json lama dipertahankan: %TARGET%\config.json
) else (
    copy /Y "%SRC%config.json" "%TARGET%\config.json" >nul
    echo [OK] config.json disalin ke: %TARGET%\config.json
)

REM --- Auto-start saat login (HKCU, tidak perlu hak admin) ---
reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /t REG_SZ /d "\"%TARGET%\SimKomAgent.exe\"" /f >nul

REM --- Jalankan versi baru ---
start "" "%TARGET%\SimKomAgent.exe"

echo.
echo [OK] Agent terpasang di : %TARGET%
echo      Config             : %TARGET%\config.json  (isi server_url + api_key)
echo      Log                : %TARGET%\agent.log
echo      Auto-start         : aktif (registry HKCU Run)
echo.
echo      Catatan: isi api_key di config.json dengan nilai
echo      MONITORING_API_KEY dari .env server, lalu jalankan
echo      ulang install.bat ini atau buka SimKomAgent.exe.
echo.
pause
