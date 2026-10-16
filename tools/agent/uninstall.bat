@echo off
REM Lepas start-otomatis SimKomAgent dari registry Run HKCU.
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /f >nul 2>&1
if errorlevel 1 (
    echo Pendaftaran SimKomAgent tidak ditemukan.
) else (
    echo SimKomAgent dilepas dari start-otomatis. Hapus folder agent ini bila tidak dipakai.
)
pause
