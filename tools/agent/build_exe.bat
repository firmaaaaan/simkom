@echo off
REM Build SmartLabsAgent.exe satu file (butuh Python + pip install pyinstaller).
cd /d "%~dp0"
pyinstaller --onefile --name SmartLabsAgent --distpath . --workpath build_tmp --specpath build_tmp agent.py
if errorlevel 1 (
    echo Gagal build. Pastikan pyinstaller terpasang: pip install pyinstaller
    pause
    exit /b 1
)
echo.
echo Build selesai: SmartLabsAgent.exe (siap dipasang di PC lab via install.bat)
pause
