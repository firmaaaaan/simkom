#!/usr/bin/env python3
"""Agent telemetri PC Lab Komputer (SimKom).

Mengambil data hardware via psutil tiap beberapa detik lalu mengirimkannya
ke server Laravel: POST /api/v1/telemetry dengan header X-API-KEY.

Cara pakai:
    A. Dengan Python : pip install -r requirements.txt, isi config.json,
                       lalu python agent.py
    B. Dengan .exe   : isi config.json di folder yang sama, jalankan
                       SimKomAgent.exe (build ulang: build_exe.bat;
                       auto-start saat login: install.bat)

Log ditulis ke agent.log di folder yang sama dengan script/exe.
"""

import getpass
import json
import logging
import os
import platform
import socket
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


def send(config, payload):
    url = config["server_url"].rstrip("/") + "/api/v1/telemetry"
    headers = {
        "X-API-KEY": config.get("api_key", ""),
        "Content-Type": "application/json",
        "Accept": "application/json",
        "User-Agent": "simlabkom-agent/1.0 ({0} {1})".format(
            platform.system(), platform.release()
        ),
    }
    response = requests.post(url, json=payload, headers=headers, timeout=10)
    response.raise_for_status()
    return response


def main():
    try:
        config = load_config()
    except (OSError, json.JSONDecodeError) as exc:
        log.error("Gagal membaca config.json: %s", exc)
        sys.exit(1)

    if not config.get("api_key") or config["api_key"] == "GANTI_DENGAN_API_KEY_DI_ENV":
        log.error("api_key belum diisi di config.json (samakan dengan MONITORING_API_KEY di .env server).")
        sys.exit(1)

    interval = max(1, int(config.get("interval_seconds", 5)))
    log.info("Agent dimulai. Server=%s interval=%ss", config.get("server_url"), interval)

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

        time.sleep(interval)


if __name__ == "__main__":
    main()
