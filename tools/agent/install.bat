@echo off
REM Pasang SimKomAgent sebagai start-otomatis saat user login (registry Run HKCU).
cd /d "%~dp0"

if exist "%~dp0SimKomAgent.exe" (
    reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /t REG_SZ /d "\"%~dp0SimKomAgent.exe\"" /f >nul
    echo SimKomAgent.exe terdaftar sebagai start-otomatis saat login.
) else (
    for /f "delims=" %%P in ('where pythonw.exe 2^>nul') do (
        reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /t REG_SZ /d "\"%%P\" \"%~dp0agent.py\"" /f >nul
        echo Agent Python terdaftar sebagai start-otomatis saat login (%%P).
        goto :done
    )
    echo Tidak menemukan SimKomAgent.exe maupun pythonw.exe.
    echo Build .exe terlebih dahulu (build_exe.bat) atau instal Python 3.
)
:done
pause
