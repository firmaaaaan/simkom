@echo off
REM Pasang SimKomAgent sebagai scheduled task ONSTART:
REM - start otomatis sejak PC hidup (tanpa menunggu user login),
REM - berjalan sebagai SYSTEM dan berlangsung selama PC hidup,
REM - otomatis restart bila crash (via RestartCount).
setlocal

REM Jadwalkan task butuh hak admin — minta UAC bila belum.
net session >nul 2>&1
if errorlevel 1 (
    echo Meminta persetujuan administrator ^(UAC^)...
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

cd /d "%~dp0"
set "TASK_NAME=SimKomAgent"

if not exist "%~dp0register_task.ps1" (
    echo [GAGAL] register_task.ps1 tidak ditemukan di folder ini.
    echo         Salin ulang seluruh isi folder agent ke PC ini.
    pause
    exit /b 1
)

REM Tentukan executable: .exe bila ada, kalau tidak pakai pythonw.
set "RUN_EXE="
set "RUN_ARG="
if exist "%~dp0SimKomAgent.exe" (
    set "RUN_EXE=%~dp0SimKomAgent.exe"
) else (
    for /f "delims=" %%P in ('where pythonw.exe 2^>nul') do if not defined RUN_EXE (
        set "RUN_EXE=%%P"
        set "RUN_ARG=%~dp0agent.py"
    )
)

if not defined RUN_EXE (
    echo [GAGAL] Tidak menemukan SimKomAgent.exe maupun pythonw.exe.
    echo         Build .exe terlebih dahulu ^(build_exe.bat^) atau instal Python 3.
    pause
    exit /b 1
)

echo Mendaftarkan task "%TASK_NAME%" ^(ONSTART, SYSTEM, tanpa batas waktu^)...
if defined RUN_ARG (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0register_task.ps1" -TaskName "%TASK_NAME%" -ExePath "%RUN_EXE%" -ScriptPath "%RUN_ARG%"
) else (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0register_task.ps1" -TaskName "%TASK_NAME%" -ExePath "%RUN_EXE%"
)
if errorlevel 1 (
    echo [GAGAL] Gagal membuat scheduled task. ^(PowerShell di atas^)
    pause
    exit /b 1
)

REM Bersihkan pendaftaran lama ^(registry Run, start saat login^) bila ada.
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /f >nul 2>&1

REM Jalankan langsung agar agent mulai sekarang tanpa reboot.
schtasks /Run /TN "%TASK_NAME%" >nul 2>&1
if errorlevel 1 (
    echo [OK] Task "%TASK_NAME%" terdaftar. Agent mulai otomatis saat boot berikutnya.
) else (
    echo [OK] Task "%TASK_NAME%" terdaftar dan agent sedang dijalankan sekarang.
)
pause
exit /b 0
