@echo off
REM ============================================================
REM  Uninstall SimKom Agent - Monitoring PC Lab Komputer
REM ============================================================

setlocal
set "TARGET=%LOCALAPPDATA%\SimKomAgent"

echo.
echo Menghapus SimKom Agent...

REM --- Matikan proses yang sedang berjalan ---
taskkill /IM SimKomAgent.exe /F >nul 2>&1
timeout /t 2 /nobreak >nul

REM --- Hapus auto-start ---
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /f >nul 2>&1

REM --- Hapus folder instalasi ---
rmdir /S /Q "%TARGET%" >nul 2>&1

if exist "%TARGET%\SimKomAgent.exe" (
    echo [ERROR] Gagal menghapus %TARGET%
) else (
    echo [OK] SimKom Agent berhasil dihapus.
)
echo.
pause
