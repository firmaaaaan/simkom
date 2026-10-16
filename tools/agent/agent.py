#!/usr/bin/env python3
"""Agent telemetri & kontrol PC Lab (SimKom).

Mengambil data hardware via psutil lalu mengirimkannya ke server Laravel:
POST /api/v1/telemetry dengan header X-API-KEY. Setiap siklus agent juga
mem-poll GET /api/v1/commands?hostname=... untuk menerima perintah dari
dashboard — saat ini "shutdown" (mematikan PC dari halaman Monitoring PC),
yang dieksekusi via `shutdown /s /t 15` (Windows) atau `shutdown -h +1`.

CARA PAKAI
==========
A. Dengan Python  (butuh Python 3.9+):
   1. pip install -r requirements.txt
   2. Edit config.json:
        - server_url : alamat server SimKom, contoh http://192.168.1.5:8000
        - api_key    : samakan dengan MONITORING_API_KEY di .env server
        - interval_seconds (opsional): jeda laporan dalam detik, default 10
   3. python agent.py     (atau pythonw agent.py tanpa jendela console)

B. Dengan .exe (tanpa Python di PC target):
   1. Build di mesin developer: build_exe.bat  (butuh pyinstaller)
   2. Salin SimKomAgent.exe + config.json ke PC lab
   3. Jalankan SimKomAgent.exe, lalu install.bat agar start otomatis saat login

install.bat   : mendaftarkan agent ke registry Run (start saat login user).
uninstall.bat : melepas pendaftaran tersebut.

Log ditulis ke agent.log di folder yang sama dengan script/exe.
"""

import getpass
import json
import logging
import os
import platform
import socket
import subprocess
import sys
import time

import psutil
import requests

# Saat di-build jadi .exe (PyInstaller), __file__ berada di folder sementara,
# jadi config.json & agent.log ditaruh di folder tempat exe berada.
FROZEN = getattr(sys, "frozen", False)
BASE_DIR = os.path.dirname(sys.executable) if FROZEN else os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(BASE_DIR, "config.json")
LOG_PATH = os.path.join(BASE_DIR, "agent.log")

log = logging.getLogger("simlabkom-agent")
log.setLevel(logging.INFO)
_formatter = logging.Formatter("%(asctime)s [%(levelname)s] %(message)s")

_console = logging.StreamHandler()
_console.setFormatter(_formatter)
log.addHandler(_console)

try:
    _file_handler = logging.FileHandler(LOG_PATH, encoding="utf-8")
    _file_handler.setFormatter(_formatter)
    log.addHandler(_file_handler)
except OSError:
    pass  # folder tidak bisa ditulis — tetap jalan dengan log console


def load_config():
    with open(CONFIG_PATH, "r", encoding="utf-8") as handle:
        return json.load(handle)


def get_ip_address():
    """IP lokal menuju jaringan (tanpa benar-benar mengirim paket)."""
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        try:
            sock.connect(("8.8.8.8", 80))
            return sock.getsockname()[0]
        finally:
            sock.close()
    except OSError:
        return "127.0.0.1"


def get_mac_address():
    try:
        for _name, addrs in psutil.net_if_addrs().items():
            for addr in addrs:
                if addr.family == psutil.AF_LINK and addr.address:
                    mac = addr.address
                    if mac and mac != "00:00:00:00:00:00":
                        return mac
    except Exception:
        pass
    return None


def get_disk_path(config):
    path = config.get("disk_path")
    if path:
        return path
    return "C:/" if os.name == "nt" else "/"


def collect(config):
    return {
        "hostname": socket.gethostname(),
        "ip_address": get_ip_address(),
        "mac_address": get_mac_address(),
        "cpu_usage": float(psutil.cpu_percent(interval=0.5)),
        "ram_usage": float(psutil.virtual_memory().percent),
        "disk_usage": float(psutil.disk_usage(get_disk_path(config)).percent),
        "active_user": getpass.getuser(),
    }


def api_headers(config):
    return {
        "X-API-KEY": config.get("api_key", ""),
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "simlabkom-agent/1.0 ({0} {1})".format(
            platform.system(), platform.release()
        ),
    }


def send(config, payload):
    url = config["server_url"].rstrip("/") + "/api/v1/telemetry"
    response = requests.post(url, json=payload, headers=api_headers(config), timeout=10)
    response.raise_for_status()
    return response


def ack_command(config, command_id, status):
    """Laporkan hasil eksekusi perintah ke server (completed | failed)."""
    url = config["server_url"].rstrip("/") + "/api/v1/commands/{0}/ack".format(command_id)
    response = requests.post(
        url, json={"status": status}, headers=api_headers(config), timeout=10
    )
    response.raise_for_status()
    return response


def run_shutdown():
    """Jadwalkan PC mati dalam 15 detik (Windows) / 1 menit (lainnya)."""
    if os.name == "nt":
        subprocess.Popen(["shutdown", "/s", "/t", "15"])
    else:
        subprocess.Popen(["shutdown", "-h", "+1"])


def execute_command(config, command):
    command_id = command.get("id")
    action = command.get("action")
    log.info("Menerima perintah %s (%s)", action, command_id)

    if action == "shutdown":
        try:
            ack_command(config, command_id, "completed")
        except requests.RequestException as exc:
            log.warning("Gagal meng-ack perintah %s: %s", command_id, exc)
        log.warning("PC dijadwalkan mati dalam 15 detik.")
        run_shutdown()
    else:
        log.warning("Aksi tidak dikenal: %s", action)
        try:
            ack_command(config, command_id, "failed")
        except requests.RequestException as exc:
            log.warning("Gagal meng-ack perintah %s: %s", command_id, exc)


def poll_commands(config, hostname):
    """Ambil perintah pending dari server lalu eksekusi."""
    url = config["server_url"].rstrip("/") + "/api/v1/commands"
    response = requests.get(
        url,
        headers=api_headers(config),
        params={"hostname": hostname},
        timeout=10,
    )
    response.raise_for_status()
    commands = response.json().get("commands", [])
    for command in commands:
        execute_command(config, command)
    return len(commands)


def main():
    try:
        config = load_config()
    except (OSError, json.JSONDecodeError) as exc:
        log.error("Gagal membaca config.json: %s", exc)
        sys.exit(1)

    if not config.get("api_key") or str(config["api_key"]).startswith("GANTI_DENGAN"):
        log.error("api_key belum diisi di config.json (samakan dengan MONITORING_API_KEY di .env server).")
        sys.exit(1)

    interval = max(1, int(config.get("interval_seconds", 10)))
    hostname = socket.gethostname()
    log.info("Agent dimulai. Server=%s interval=%ss hostname=%s",
             config.get("server_url"), interval, hostname)

    while True:
        try:
            payload = collect(config)
            send(config, payload)
            log.info(
                "Terkirim %s | CPU=%.1f%% RAM=%.1f%% Disk=%.1f%%",
                payload["hostname"],
                payload["cpu_usage"],
                payload["ram_usage"],
                payload["disk_usage"],
            )
        except requests.RequestException as exc:
            log.warning("Gagal mengirim telemetri: %s", exc)
        except Exception as exc:  # agent tidak boleh mati
            log.error("Kesalahan tak terduga: %s", exc)

        # Terima perintah dari dashboard (mis. mematikan PC).
        try:
            poll_commands(config, hostname)
        except requests.RequestException as exc:
            log.warning("Gagal memeriksa perintah: %s", exc)
        except Exception as exc:
            log.error("Kesalahan saat memeriksa perintah: %s", exc)

        time.sleep(interval)


if __name__ == "__main__":
    main()
