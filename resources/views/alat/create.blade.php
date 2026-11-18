@extends('layouts.app')

@section('title', 'Tambah Alat')
@section('header', 'Tambah Alat Laboratorium')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('alat.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alat</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm">
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No Inventaris</label>
            <div class="flex gap-2">
                <input type="text" name="code" id="code_input" value="{{ old('code') }}" class="flex-1 rounded-lg border-gray-300 border px-3 py-2 text-sm font-mono" placeholder="Kosongkan untuk otomatis" autocomplete="off">
                <button type="button" id="toggle-scan" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium border border-green-600 text-green-700 rounded-lg hover:bg-green-50 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M8 8v8M12 8v8M16 8v8" />
                    </svg>
                    <span id="toggle-scan-label">Scan</span>
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Hasil scan kamera atau scanner pistol (keyboard wedge) langsung mengisi No Inventaris.</p>
            @error('code') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            <div id="scan-container" class="hidden mt-3">
                <div id="qr-reader" class="w-full max-w-sm mx-auto"></div>
                <p class="mt-2 text-xs text-center text-gray-500">Arahkan kamera ke barcode alat (Code128, Code39, QR, dll).</p>
            </div>
            <div id="scan-status" class="hidden mt-2 p-2 rounded-lg text-xs"></div>
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
    const codeInput = document.getElementById('code_input');
    const toggleBtn = document.getElementById('toggle-scan');
    const toggleLabel = document.getElementById('toggle-scan-label');
    const container = document.getElementById('scan-container');
    const status = document.getElementById('scan-status');

    let scanner = null;
    let running = false;

    function showStatus(message, ok) {
        status.className = 'mt-2 p-2 rounded-lg text-xs ' + (ok
            ? 'bg-green-50 text-green-700 border border-green-200'
            : 'bg-red-50 text-red-700 border border-red-200');
        status.textContent = message;
        status.classList.remove('hidden');
        if (ok) {
            setTimeout(function () { status.classList.add('hidden'); }, 5000);
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
        toggleLabel.textContent = 'Scan';
    }

    toggleBtn.addEventListener('click', async function () {
        if (running) {
            await stopCamera();
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
                    codeInput.value = decodedText.trim();
                    showStatus('Barcode terdeteksi: ' + decodedText.trim(), true);
                    stopCamera();
                },
                function () {}
            );
            running = true;
            toggleLabel.textContent = 'Matikan';
        } catch (err) {
            showStatus('Tidak dapat mengakses kamera. Gunakan scanner pistol atau isi manual.', false);
            container.classList.add('hidden');
        }
    });
});
</script>
@endpush
