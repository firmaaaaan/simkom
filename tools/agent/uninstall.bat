@echo off
REM Hapus SimKomAgent dari scheduled task (dan registry Run lama bila ada).
setlocal

REM Penghapusan task butuh hak admin — minta UAC bila belum.
net session >nul 2>&1
if errorlevel 1 (
    echo Meminta persetujuan administrator ^(UAC^)...
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

schtasks /End /TN "SimKomAgent" >nul 2>&1
schtasks /Delete /TN "SimKomAgent" /F >nul 2>&1
if errorlevel 1 (
    echo Task SimKomAgent tidak ditemukan.
) else (
    echo Task SimKomAgent dihentikan dan dihapus.
)

REM Sisa pendaftaran lama ^(registry Run^), kalau masih ada.
reg delete "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v SimKomAgent /f >nul 2>&1

echo Hapus folder agent ini bila tidak dipakai lagi.
pause
exit /b 0
