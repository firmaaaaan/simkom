@extends('layouts.app')

@section('title', 'Tambah Alat')
@section('header', 'Tambah Alat Laboratorium')

@section('content')
<div class="max-w-2xl space-y-6">

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

        <div id="scan-results" class="hidden">
            <p class="text-sm font-medium text-gray-700 mb-2">
                Tersimpan: <span id="scan-count" class="font-bold text-green-700">0</span> alat
            </p>
            <ul id="scan-list" class="divide-y divide-gray-100 text-sm"></ul>
        </div>
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
    const results = document.getElementById('scan-results');
    const list = document.getElementById('scan-list');
    const counter = document.getElementById('scan-count');

    let scanner = null;
    let running = false;
    let savedCount = 0;
    let lastText = '';
    let lastAt = 0;

    // Ingat pilihan laboratorium terakhir agar laboran tidak memilih ulang.
    const savedLab = localStorage.getItem('alat_scan_lab');
    if (savedLab) {
        labSelect.value = savedLab;
    }
    labSelect.addEventListener('change', function () {
        localStorage.setItem('alat_scan_lab', labSelect.value);
    });

    function showStatus(message, ok) {
        status.className = 'p-3 rounded-lg text-xs ' + (ok
            ? 'bg-green-50 text-green-700 border border-green-200'
            : 'bg-red-50 text-red-700 border border-red-200');
        status.textContent = message;
        status.classList.remove('hidden');
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

    function addResult(item) {
        savedCount++;
        counter.textContent = savedCount;
        results.classList.remove('hidden');

        const row = document.createElement('li');
        row.className = 'py-2 flex items-start justify-between gap-2';

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

        const badge = document.createElement('span');
        badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700';
        badge.textContent = 'Tersimpan';

        row.appendChild(info);
        row.appendChild(badge);
        list.prepend(row);
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
                addResult(data.alat);
                showStatus('Tersimpan: ' + data.message, true);
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
});
</script>
@endpush
