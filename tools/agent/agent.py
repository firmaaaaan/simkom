#!/usr/bin/env python3
"""Agent telemetri, kendali, & inventaris PC Lab (SimKom).

Mengambil data hardware via psutil lalu mengirimkannya ke server Laravel:
POST /api/v1/telemetry dengan header X-API-KEY. Setiap siklus agent juga
mem-poll GET /api/v1/commands?hostname=... untuk menerima perintah dari
dashboard:
  - shutdown  : matikan PC (shutdown /s /t 15)
  - restart   : restart PC (shutdown /r /t 15)
  - lock      : kunci layar (LockWorkStation)
  - logout    : keluarkan user login aktif (logoff sesi console)
  - screenshot: kirim gambar layar (JPEG) ke POST /api/v1/screenshots
Selain itu agent mengirim inventaris software terinstall (registry
Uninstall Windows) ke POST /api/v1/software saat start + tiap
software_interval_seconds (default 1 jam), dan snapshot software yang
SEDANG berjalan (proses + judul jendela visible) ke
POST /api/v1/processes tiap process_interval_seconds (default 60 dtk).

CARA PAKAI
==========
A. Dengan Python  (butuh Python 3.9+):
   1. pip install -r requirements.txt
   2. Edit config.json:
        - server_url : alamat server SimKom, contoh http://192.168.1.5:8000
        - api_key    : samakan dengan MONITORING_API_KEY di .env server
        - interval_seconds (opsional): jeda laporan dalam detik, default 10
        - software_interval_seconds (opsional): jeda sync software, default 3600
   3. python agent.py     (atau pythonw agent.py tanpa jendela console)

B. Dengan .exe (tanpa Python di PC target):
   1. Build di mesin developer: build_exe.bat  (butuh pyinstaller)
   2. Salin ke PC lab: SimKomAgent.exe, config.json, install.bat,
      uninstall.bat, dan register_task.ps1
   3. Jalankan install.bat (prompt UAC "Yes") — agent mulai sejak PC hidup.

install.bat   : mendaftarkan scheduled task ONSTART (mulai sejak PC hidup,
                berjalan sebagai SYSTEM selama PC hidup; butuh UAC sekali).
uninstall.bat : menghapus task tersebut.

Log ditulis ke agent.log di folder yang sama dengan script/exe.
"""

import base64
import ctypes
import getpass
import io
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
from PIL import Image, ImageGrab

# winreg hanya ada di Windows — guard agar modul tetap bisa diimport
# di sistem lain (fungsi inventory sudah check os.name).
if os.name == "nt":
    import winreg
else:
    winreg = None

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


def get_active_user():
    """User yang sedang login di sesi console.

    Akurat walau agent berjalan sebagai SYSTEM (task ONSTART): psutil.users()
    mengembalikan sesi login interaktif. Sebelum ada user login (layar login
    Windows), kembalikan None sehingga dashboard menampilkan "—".
    """
    try:
        users = psutil.users()
        if users:
            return users[-1].name
    except Exception:
        pass

    user = getpass.getuser()
    if user.upper() in {"SYSTEM", "LOCAL SERVICE", "NETWORK SERVICE"}:
        return None
    return user


def collect(config):
    return {
        "hostname": socket.gethostname(),
        "ip_address": get_ip_address(),
        "mac_address": get_mac_address(),
        "cpu_usage": float(psutil.cpu_percent(interval=0.5)),
        "ram_usage": float(psutil.virtual_memory().percent),
        "disk_usage": float(psutil.disk_usage(get_disk_path(config)).percent),
        "active_user": get_active_user(),
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


def ack_command(config, command_id, status, message=None):
    """Laporkan hasil eksekusi perintah ke server (completed | failed)."""
    url = config["server_url"].rstrip("/") + "/api/v1/commands/{0}/ack".format(command_id)
    body = {"status": status}
    if message:
        body["message"] = str(message)[:500]
    response = requests.post(url, json=body, headers=api_headers(config), timeout=10)
    response.raise_for_status()
    return response


# ---------------------------------------------------------------------------
# Kendali PC
# ---------------------------------------------------------------------------

def run_shutdown():
    """Jadwalkan PC mati dalam 15 detik (Windows) / 1 menit (lainnya)."""
    if os.name == "nt":
        subprocess.Popen(["shutdown", "/s", "/t", "15"])
    else:
        subprocess.Popen(["shutdown", "-h", "+1"])


def run_restart():
    """Jadwalkan PC restart dalam 15 detik (Windows) / 1 menit (lainnya)."""
    if os.name == "nt":
        subprocess.Popen(["shutdown", "/r", "/t", "15"])
    else:
        subprocess.Popen(["shutdown", "-r", "+1"])


def run_lock():
    """Kunci layar sesi aktif (Windows only)."""
    if os.name != "nt":
        raise RuntimeError("kunci layar hanya didukung di Windows")
    subprocess.Popen(["rundll32.exe", "user32.dll,LockWorkStation"])


def run_logout():
    """Keluar-kan user login (logoff sesi console aktif). False bila tak ada."""
    if os.name != "nt":
        raise RuntimeError("logout hanya didukung di Windows")

    try:
        users = psutil.users()
    except Exception:
        users = []

    for entry in users:
        pid = getattr(entry, "pid", None)
        if not pid:
            continue
        try:
            session_id = psutil.Process(pid).session_id()
            subprocess.Popen(["logoff", str(session_id)])
            return True
        except Exception:
            continue

    return False


def capture_screenshot_base64(max_width=1280, quality=60):
    """Ambil layar → JPEG (maks lebar max_width) → base64 string."""
    image = ImageGrab.grab()
    if image.width > max_width:
        ratio = max_width / float(image.width)
        image = image.resize((max_width, max(1, int(image.height * ratio))))

    buffer = io.BytesIO()
    image.save(buffer, format="JPEG", quality=quality)
    return base64.b64encode(buffer.getvalue()).decode("ascii")


def send_screenshot(config, hostname):
    url = config["server_url"].rstrip("/") + "/api/v1/screenshots"
    payload = {
        "hostname": hostname,
        "image_base64": capture_screenshot_base64(),
    }
    response = requests.post(url, json=payload, headers=api_headers(config), timeout=30)
    response.raise_for_status()
    return response


# ---------------------------------------------------------------------------
# Inventaris software (registry Uninstall Windows)
# ---------------------------------------------------------------------------

def _read_uninstall_entry(root, key_path):
    try:
        with winreg.OpenKey(root, key_path) as sub:
            values = {}
            for field in ("DisplayName", "DisplayVersion", "Publisher", "InstallDate", "EstimatedSize"):
                try:
                    values[field] = winreg.QueryValueEx(sub, field)[0]
                except OSError:
                    values[field] = None
    except OSError:
        return None

    name = values.get("DisplayName")
    if not name:
        return None

    size = values.get("EstimatedSize")

    def text(value, limit):
        return str(value)[:limit] if value not in (None, "") else None

    return {
        "name": text(name, 300) or "",
        "version": text(values.get("DisplayVersion"), 100),
        "publisher": text(values.get("Publisher"), 300),
        "install_date": text(values.get("InstallDate"), 50),
        "size_kb": int(size) if isinstance(size, (int, float)) and size >= 0 else None,
    }


def collect_installed_software():
    """Daftar software terinstall dari registry Uninstall (32/64-bit + HKCU)."""
    if os.name != "nt":
        return []

    roots = [
        (winreg.HKEY_LOCAL_MACHINE, r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall"),
        (winreg.HKEY_LOCAL_MACHINE, r"SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall"),
        (winreg.HKEY_CURRENT_USER, r"SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall"),
    ]

    seen = set()
    items = []
    for root, path in roots:
        try:
            with winreg.OpenKey(root, path) as key:
                count = winreg.QueryInfoKey(key)[0]
        except OSError:
            continue

        for index in range(count):
            try:
                with winreg.OpenKey(root, path) as key:
                    sub_name = winreg.EnumKey(key, index)
            except OSError:
                continue

            entry = _read_uninstall_entry(root, path + "\\" + sub_name)
            if entry and entry["name"] and entry["name"] not in seen:
                seen.add(entry["name"])
                items.append(entry)

    items.sort(key=lambda item: item["name"].lower())
    return items


def send_software(config, hostname):
    items = collect_installed_software()
    url = config["server_url"].rstrip("/") + "/api/v1/software"
    response = requests.post(
        url,
        json={"hostname": hostname, "items": items},
        headers=api_headers(config),
        timeout=30,
    )
    response.raise_for_status()
    log.info("Inventaris software terkirim: %d aplikasi", len(items))
    return response


# ---------------------------------------------------------------------------
# Snapshot software yang SEDANG berjalan (proses + judul jendela)
# ---------------------------------------------------------------------------

def _visible_window_titles():
    """Map pid -> judul jendela visible pertama (hanya Windows)."""
    if os.name != "nt":
        return {}

    titles = {}
    user32 = ctypes.windll.user32
    kernel32 = ctypes.windll.kernel32

    def _handler(hwnd, _):
        if not user32.IsWindowVisible(hwnd):
            return True

        length = user32.GetWindowTextLengthW(hwnd)
        if length <= 0:
            return True

        buffer = ctypes.create_unicode_buffer(length + 1)
        user32.GetWindowTextW(hwnd, buffer, length + 1)
        title = buffer.value.strip()
        if not title:
            return True

        pid = ctypes.c_ulong()
        user32.GetWindowThreadProcessId(hwnd, ctypes.byref(pid))
        if pid.value:
            titles.setdefault(pid.value, title)
        return True

    WNDENUMPROC = ctypes.WINFUNCTYPE(ctypes.c_int, ctypes.c_void_p, ctypes.c_void_p)
    user32.EnumWindows(WNDENUMPROC(_handler), 0)
    return titles


def collect_running_processes(max_items=50):
    """Daftar proses yang sedang berjalan (punya jendela visible).

    Mengembalikan list dict {name, window_title}. Proses background tanpa
    jendela tidak disertakan sehingga daftar relevan dengan aplikasi yang
    benar-benar dibuka user.
    """
    titles = _visible_window_titles()
    if not titles:
        return []

    items = []
    seen = set()
    for proc in psutil.process_iter(["pid", "name"]):
        try:
            pid = proc.info["pid"]
            name = proc.info["name"]
        except (psutil.NoSuchProcess, psutil.AccessDenied):
            continue

        title = titles.get(pid)
        if not name or not title:
            continue

        key = (name.lower(), title.lower())
        if key in seen:
            continue
        seen.add(key)

        items.append({
            "name": str(name)[:200],
            "window_title": str(title)[:500],
        })

    items.sort(key=lambda item: (item["name"].lower(), item["window_title"].lower()))
    return items[:max_items]


def send_processes(config, hostname):
    items = collect_running_processes()
    url = config["server_url"].rstrip("/") + "/api/v1/processes"
    response = requests.post(
        url,
        json={"hostname": hostname, "items": items},
        headers=api_headers(config),
        timeout=30,
    )
    response.raise_for_status()
    log.info("Snapshot proses berjalan terkirim: %d jendela", len(items))
    return response


# ---------------------------------------------------------------------------
# Perintah dari dashboard
# ---------------------------------------------------------------------------

def execute_command(config, hostname, command):
    command_id = command.get("id")
    action = command.get("action")
    log.info("Menerima perintah %s (%s)", action, command_id)

    def ack(status, message=None):
        try:
            ack_command(config, command_id, status, message)
        except requests.RequestException as exc:
            log.warning("Gagal meng-ack perintah %s: %s", command_id, exc)

    if action == "shutdown":
        ack("completed", "shutdown dijalankan")
        log.warning("PC dijadwalkan mati dalam 15 detik.")
        run_shutdown()
    elif action == "restart":
        ack("completed", "restart dijalankan")
        log.warning("PC dijadwalkan restart dalam 15 detik.")
        run_restart()
    elif action == "lock":
        try:
            run_lock()
            ack("completed", "layar dikunci")
        except Exception as exc:
            ack("failed", str(exc))
    elif action == "logout":
        try:
            if run_logout():
                ack("completed", "user dikeluarkan")
                log.warning("User dikeluarkan dari sesi console.")
            else:
                ack("failed", "tidak ada user login")
        except Exception as exc:
            ack("failed", str(exc))
    elif action == "screenshot":
        try:
            send_screenshot(config, hostname)
            ack("completed", "screenshot terkirim")
        except Exception as exc:
            ack("failed", "gagal screenshot: {0}".format(exc))
    else:
        log.warning("Aksi tidak dikenal: %s", action)
        ack("failed", "aksi tidak dikenal: {0}".format(action))


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
        execute_command(config, hostname, command)
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
    software_interval = max(60, int(config.get("software_interval_seconds", 3600)))
    process_interval = max(15, int(config.get("process_interval_seconds", 60)))
    hostname = socket.gethostname()
    log.info("Agent dimulai. Server=%s interval=%ss hostname=%s",
             config.get("server_url"), interval, hostname)

    last_software_sync = 0.0  # 0 → sync pertama langsung saat start
    last_process_sync = 0.0

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

        # Inventaris software: saat start lalu berkala.
        if time.time() - last_software_sync >= software_interval:
            try:
                send_software(config, hostname)
                last_software_sync = time.time()
            except requests.RequestException as exc:
                log.warning("Gagal mengirim inventaris software: %s", exc)
            except Exception as exc:
                log.error("Kesalahan saat mengumpulkan software: %s", exc)

        # Snapshot software yang sedang berjalan (proses + judul jendela).
        if time.time() - last_process_sync >= process_interval:
            try:
                send_processes(config, hostname)
                last_process_sync = time.time()
            except requests.RequestException as exc:
                log.warning("Gagal mengirim snapshot proses: %s", exc)
            except Exception as exc:
                log.error("Kesalahan saat mengumpulkan proses berjalan: %s", exc)

        time.sleep(interval)


if __name__ == "__main__":
    main()
