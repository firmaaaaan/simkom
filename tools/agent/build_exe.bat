@echo off
REM Build SimKomAgent.exe satu file (butuh Python + pip install pyinstaller).
cd /d "%~dp0"
pyinstaller --onefile --name SimKomAgent --distpath . --workpath build_tmp --specpath build_tmp agent.py
if errorlevel 1 (
    echo Gagal build. Pastikan pyinstaller terpasang: pip install pyinstaller
    pause
    exit /b 1
)
echo.
echo Build selesai: SimKomAgent.exe (siap dipasang di PC lab via install.bat)
pause
