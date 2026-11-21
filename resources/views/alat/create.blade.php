@extends('layouts.app')

@section('title', 'Tambah Alat')
@section('header', 'Tambah Alat Laboratorium')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start max-w-6xl">

    {{-- Kolom kiri: scan + input manual --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Scan cepat: pilih lab lalu scan, data otomatis tersimpan --}}
        <div class="bg-white rounded-xl border border-green-200 p-6 space-y-4">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Scan Barcode Alat</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pilih laboratorium tujuan terlebih dahulu, lalu scan barcode alat. Data langsung tersimpan ke laboratorium tersebut
                    &mdash; siap terintegrasi dengan SIMPTT Inventaris.
                </p>
            </div>

            <div>
                <label for="scan_laboratory_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Laboratorium Tujuan <span class="text-red-500">*</span>
                </label>
                <select id="scan_laboratory_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                    <option value="">Pilih laboratorium...</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" id="toggle-scan" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M8 8v8M12 8v8M16 8v8" />
                    </svg>
                    <span id="toggle-scan-label">Aktifkan Kamera</span>
                </button>
                <p class="text-xs text-gray-500">
                    Kamera tetap menyala untuk scan banyak alat sekaligus. Scanner pistol (keyboard wedge) juga didukung.
                </p>
            </div>

            <div id="scan-container" class="hidden">
                <div id="qr-reader" class="w-full max-w-sm mx-auto"></div>
                <p class="mt-2 text-xs text-center text-gray-500">Arahkan kamera ke barcode alat (Code128, Code39, QR, dll).</p>
            </div>

            <div id="scan-status" class="hidden p-3 rounded-lg text-xs"></div>
        </div>

        {{-- Input manual --}}
        <form action="{{ route('alat.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            @csrf
            <div>
                <h2 class="text-base font-semibold text-gray-800">Input Manual</h2>
                <p class="text-sm text-gray-500 mt-1">Lengkapi data alat secara manual bila tanpa barcode.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alat</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No Inventaris</label>
                <input type="text" name="code" value="{{ old('code') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm" placeholder="Kosongkan untuk otomatis">
                @error('code') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Merk/Type</label>
                <input type="text" name="merk_type" value="{{ old('merk_type') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium</label>
                <select name="laboratory_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
                    <option value="">-</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi/Ruangan</label>
                <input type="text" name="location" value="{{ old('location') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <input type="text" name="status" value="{{ old('status') }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" name="action" value="save" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Simpan</button>
                <button type="submit" name="action" value="save_another" class="px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-lg hover:bg-green-50">Simpan & Tambah Lagi</button>
                <a href="{{ route('alat.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>

    {{-- Kolom kanan: alat pada lab terpilih --}}
    <aside class="xl:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-4 xl:sticky xl:top-4">
            <div class="flex items-center justify-between mb-1">
                <h2 class="text-sm font-semibold text-gray-800">Alat di Laboratorium</h2>
                <span id="lab-alat-count" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">0</span>
            </div>
            <p id="lab-alat-name" class="text-xs text-gray-500 mb-3">Pilih laboratorium untuk melihat daftar alat.</p>

            <ul id="lab-alat-list" class="divide-y divide-gray-100 max-h-[30rem] overflow-y-auto text-sm"></ul>

            <p id="lab-alat-empty" class="hidden text-xs text-gray-400 text-center py-6">Belum ada alat di laboratorium ini.</p>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labSelect = document.getElementById('scan_laboratory_id');
    const toggleBtn = document.getElementById('toggle-scan');
    const toggleLabel = document.getElementById('toggle-scan-label');
    const container = document.getElementById('scan-container');
    const status = document.getElementById('scan-status');

    const listEl = document.getElementById('lab-alat-list');
    const countEl = document.getElementById('lab-alat-count');
    const nameEl = document.getElementById('lab-alat-name');
    const emptyEl = document.getElementById('lab-alat-empty');

    let scanner = null;
    let running = false;
    let lastText = '';
    let lastAt = 0;

    // Ingat pilihan laboratorium terakhir agar laboran tidak memilih ulang.
    const savedLab = localStorage.getItem('alat_scan_lab');
    if (savedLab) {
        labSelect.value = savedLab;
    }

    labSelect.addEventListener('change', function () {
        localStorage.setItem('alat_scan_lab', labSelect.value);
        loadLabAlat();
    });

    function showStatus(message, ok) {
        status.className = 'p-3 rounded-lg text-xs whitespace-pre-line ' + (ok
            ? 'bg-green-50 text-green-700 border border-green-200'
            : 'bg-red-50 text-red-700 border border-red-200');
        status.textContent = message;
        status.classList.remove('hidden');
    }

    function setCount(total) {
        countEl.textContent = total;
    }

    function renderItem(item, highlight) {
        const row = document.createElement('li');
        row.className = 'py-2 flex items-start justify-between gap-2';
        if (highlight) {
            row.classList.add('bg-green-50', '-mx-2', 'px-2', 'rounded-lg');
            setTimeout(function () {
                row.classList.remove('bg-green-50', '-mx-2', 'px-2', 'rounded-lg');
            }, 5000);
        }

        const info = document.createElement('div');
        info.className = 'min-w-0';

        const name = document.createElement('p');
        name.className = 'font-medium text-gray-800 truncate';
        name.textContent = item.name || item.code;

        const meta = document.createElement('p');
        meta.className = 'text-xs text-gray-500 font-mono';
        meta.textContent = item.code;

        info.appendChild(name);
        info.appendChild(meta);
        row.appendChild(info);

        if (item.status) {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 whitespace-nowrap';
            badge.textContent = item.status;
            row.appendChild(badge);
        }

        return row;
    }

    async function loadLabAlat() {
        const labId = labSelect.value;

        listEl.innerHTML = '';
        emptyEl.classList.add('hidden');
        setCount(0);

        if (!labId) {
            nameEl.textContent = 'Pilih laboratorium untuk melihat daftar alat.';
            return;
        }

        nameEl.textContent = 'Memuat daftar alat...';

        try {
            const url = new URL('{{ route('alat.lab-list') }}', window.location.origin);
            url.searchParams.set('laboratory_id', labId);

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!response.ok || !data.ok) {
                nameEl.textContent = 'Gagal memuat daftar alat.';
                return;
            }

            nameEl.textContent = data.laboratory.name;
            setCount(data.alat.length);

            if (data.alat.length === 0) {
                emptyEl.classList.remove('hidden');
                return;
            }

            data.alat.forEach(function (item) {
                listEl.appendChild(renderItem(item, false));
            });
        } catch (error) {
            nameEl.textContent = 'Gagal memuat daftar alat.';
        }
    }

    function addScannedItem(item) {
        emptyEl.classList.add('hidden');
        listEl.prepend(renderItem(item, true));
        setCount(parseInt(countEl.textContent, 10) + 1 || 1);
    }

    function parsePayload(text) {
        try {
            const data = JSON.parse(text);
            if (data && typeof data === 'object' && !Array.isArray(data)) {
                return {
                    code: data.code || data.kode || '',
                    name: data.name || data.nama || null,
                    merk_type: data.merk_type || data.merk || null,
                    location: data.location || data.lokasi || null,
                    status: data.status || null,
                    description: data.description || data.keterangan || null
                };
            }
        } catch (e) {}
        return { code: text };
    }

    async function saveScan(text) {
        const labId = labSelect.value;
        if (!labId) {
            showStatus('Pilih laboratorium tujuan terlebih dahulu.', false);
            labSelect.focus();
            return;
        }

        const payload = parsePayload(text);
        if (!payload.code) {
            showStatus('Barcode tidak berisi kode alat yang valid.', false);
            return;
        }

        payload.laboratory_id = labId;

        try {
            const response = await fetch('{{ route('alat.scan-store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.ok) {
                addScannedItem(data.alat);

                let message = 'Tersimpan: ' + data.message;
                if (data.inventaris) {
                    const inv = data.inventaris;
                    const lines = [];
                    if (inv.merk_type) lines.push('Merk/Type: ' + inv.merk_type);
                    if (inv.lokasi) lines.push('Lokasi: ' + inv.lokasi);
                    if (inv.status) lines.push('Status: ' + inv.status);
                    if (lines.length) message += '\n' + lines.join('\n');
                }

                showStatus(message, true);
            } else {
                showStatus(data.message || 'Gagal menyimpan hasil scan.', false);
            }
        } catch (error) {
            showStatus('Terjadi kesalahan saat menyimpan. Coba lagi.', false);
        }
    }

    async function stopCamera() {
        if (!running || !scanner) return;
        try {
            await scanner.stop();
            scanner.clear();
        } catch (e) {}
        running = false;
        container.classList.add('hidden');
        toggleLabel.textContent = 'Aktifkan Kamera';
    }

    toggleBtn.addEventListener('click', async function () {
        if (running) {
            await stopCamera();
            return;
        }

        if (!labSelect.value) {
            showStatus('Pilih laboratorium tujuan terlebih dahulu.', false);
            labSelect.focus();
            return;
        }

        container.classList.remove('hidden');

        if (!scanner) {
            scanner = new Html5Qrcode('qr-reader');
        }

        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 100 }, aspectRatio: 1.0 },
                function (decodedText) {
                    const text = decodedText.trim();
                    const now = Date.now();

                    // Abaikan pantulan scan kode yang sama dalam 3 detik.
                    if (text === lastText && now - lastAt < 3000) {
                        return;
                    }
                    lastText = text;
                    lastAt = now;

                    saveScan(text);
                },
                function () {}
            );
            running = true;
            toggleLabel.textContent = 'Matikan Kamera';
        } catch (err) {
            showStatus('Tidak dapat mengakses kamera. Gunakan scanner pistol atau input manual.', false);
            container.classList.add('hidden');
        }
    });

    loadLabAlat();
});
</script>
@endpush
