@echo off
REM ============================================================
REM  Build ulang SimKomAgent.exe dari agent.py
REM  Butuh Python 3.10+ terinstal (https://www.python.org/downloads/)
REM ============================================================

setlocal
cd /d "%~dp0"

set "PY="
where py >nul 2>nul && set "PY=py -3"
if not defined PY (
    where python >nul 2>nul && set "PY=python"
)
if not defined PY (
    echo [ERROR] Python tidak ditemukan.
    echo         Instal Python 3.10+ dulu, lalu jalankan ulang script ini.
    pause
    exit /b 1
)

echo [1/3] Menyiapkan dependency build (pyinstaller, psutil, requests)...
%PY% -m pip install --quiet --upgrade pyinstaller psutil requests
if errorlevel 1 (
    echo [ERROR] pip gagal - cek koneksi internet Anda.
    pause
    exit /b 1
)

echo [2/3] Membangun SimKomAgent.exe (onefile, tanpa jendela console)...
%PY% -m PyInstaller --noconfirm --onefile --noconsole --name SimKomAgent agent.py
if errorlevel 1 (
    echo [ERROR] Build gagal.
    pause
    exit /b 1
)

echo [3/3] Menyalin hasil & membersihkan artefak...
copy /Y "dist\SimKomAgent.exe" "SimKomAgent.exe" >nul
rmdir /S /Q dist build 2>nul
del /Q SimKomAgent.spec 2>nul

echo.
echo [OK] SimKomAgent.exe siap: %cd%\SimKomAgent.exe
echo      Jalankan install.bat untuk memasangnya.
echo.
pause
