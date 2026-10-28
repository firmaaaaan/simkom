@extends('layouts.app')

@section('title', 'Pindai Barcode Inventaris')
@section('header', 'Pindai Barcode Inventaris')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('computers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Daftar Komputer
        </a>
        <a href="{{ route('computers.edit', $computers->first() ?? '#') }}" class="text-sm text-gray-500 hover:text-green-600" onclick="event.preventDefault(); if(window.showAlert){ window.showAlert({title:'Informasi', message:'Pilih komputer terlebih dahulu untuk mengedit secara manual.'}); } else { alert('Pilih komputer terlebih dahulu untuk mengedit secara manual.'); }">
            Edit Manual
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">Pilih Komputer</h2>
                    <p class="text-xs text-gray-500 mt-1">Pilih komputer yang akan diberi kode barcode inventaris lama</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Progres</p>
                        <p class="text-sm font-medium text-gray-800">{{ $filledCount }} / {{ $computers->count() }}</p>
                    </div>
                    <div class="w-24 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $computers->count() > 0 ? ($filledCount / $computers->count()) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <form method="GET" action="{{ route('computers.barcode-scan') }}" class="flex items-center gap-2">
                    <select name="laboratory_id" onchange="this.form.submit()" class="block w-full sm:w-auto px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="all" {{ $selectedLabId === 'all' ? 'selected' : '' }}>Semua Laboratorium</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ (string)$lab->id === $selectedLabId ? 'selected' : '' }}>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="px-6 py-5">
            <div class="mb-5">
                <label for="computer_id" class="block text-sm font-medium text-gray-700 mb-1.5">Komputer Target</label>
                <select id="computer_id" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih Komputer --</option>
                    @foreach($computers as $computer)
                        <option value="{{ $computer->id }}" data-code="{{ $computer->code }}" data-has-barcode="{{ $computer->barcode ? '1' : '0' }}">
                            {{ $computer->code }} @if($computer->laboratory) — {{ $computer->laboratory->name }} @endif @if($computer->barcode) [Sudah: {{ $computer->barcode }}] @endif
                        </option>
                    @endforeach
                </select>
                <p id="current-computer-info" class="mt-1.5 text-xs text-gray-500"></p>
            </div>

            <div class="mb-6">
                <label for="barcode_input" class="block text-sm font-medium text-gray-700 mb-1.5">Kode Barcode</label>
                <div class="flex gap-2">
                    <input type="text" id="barcode_input" class="flex-1 px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono" placeholder="Pindai dengan kamera atau ketik hasil scan pistol..." autocomplete="off">
                    <button id="save-barcode" class="px-4 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan
                    </button>
                </div>
                <p class="mt-1.5 text-xs text-gray-500">Scanner pistol (keyboard wedge) bisa langsung scan tanpa mengaktifkan kamera. Fokuskan cursor ke kolom ini.</p>
            </div>

            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-700">Pindai via Kamera</p>
                    <button id="toggle-camera" class="text-sm text-green-600 hover:text-green-700 font-medium">Aktifkan Kamera</button>
                </div>
                <div id="camera-container" class="hidden">
                    <div id="qr-reader" class="w-full max-w-md mx-auto"></div>
                    <p class="mt-2 text-xs text-center text-gray-500">Arahkan kamera ke barcode yang tertempel di CPU (mendukung Code128, Code39, dll)</p>
                </div>
            </div>

            <div id="scan-status" class="hidden p-4 rounded-lg text-sm"></div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const computerSelect = document.getElementById('computer_id');
    const barcodeInput = document.getElementById('barcode_input');
    const saveBtn = document.getElementById('save-barcode');
    const toggleCameraBtn = document.getElementById('toggle-camera');
    const cameraContainer = document.getElementById('camera-container');
    const scanStatus = document.getElementById('scan-status');
    const currentInfo = document.getElementById('current-computer-info');
    
    let html5QrCode = null;
    let cameraRunning = false;
    
    function updateComputerInfo() {
        const selected = computerSelect.options[computerSelect.selectedIndex];
        if (selected && selected.value) {
            if (selected.dataset.hasBarcode === '1') {
                currentInfo.innerHTML = '<span class="text-amber-600">⚠ Komputer ini sudah punya barcode. Memindai ulang akan menggantinya.</span>';
            } else {
                currentInfo.textContent = 'Siap memindai barcode untuk ' + selected.dataset.code;
            }
            barcodeInput.focus();
        } else {
            currentInfo.textContent = '';
        }
    }
    
    computerSelect.addEventListener('change', updateComputerInfo);
    
    function showStatus(message, type) {
        scanStatus.className = 'p-4 rounded-lg text-sm ' + (type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200');
        scanStatus.textContent = message;
        scanStatus.classList.remove('hidden');
        setTimeout(() => scanStatus.classList.add('hidden'), 5000);
    }
    
    async function saveBarcode() {
        const computerId = computerSelect.value;
        const barcode = barcodeInput.value.trim();
        
        if (!computerId) {
            showStatus('Pilih komputer terlebih dahulu', 'error');
            computerSelect.focus();
            return;
        }
        if (!barcode) {
            showStatus('Barcode tidak boleh kosong', 'error');
            barcodeInput.focus();
            return;
        }
        
        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan...';
        
        try {
            const response = await fetch('{{ route('computers.barcode-store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ computer_id: computerId, barcode: barcode })
            });
            
            const data = await response.json();
            
            if (response.ok && data.ok) {
                showStatus(data.message, 'success');
                barcodeInput.value = '';
                
                // Update option to show barcode
                const selected = computerSelect.options[computerSelect.selectedIndex];
                selected.dataset.hasBarcode = '1';
                selected.text = selected.dataset.code + ' @if($computers->first()?->laboratory) — ' + (selected.text.includes('—') ? selected.text.split('—')[1].trim().replace(/\[.*\]/, '').trim() : '') + ' @endif [Sudah: ' + barcode + ']';
                
                // Move to next unfilled if exists
                let nextIndex = computerSelect.selectedIndex + 1;
                while (nextIndex < computerSelect.options.length) {
                    if (computerSelect.options[nextIndex].value && computerSelect.options[nextIndex].dataset.hasBarcode === '0') {
                        computerSelect.selectedIndex = nextIndex;
                        updateComputerInfo();
                        break;
                    }
                    nextIndex++;
                }
            } else {
                showStatus(data.message || 'Gagal menyimpan barcode', 'error');
            }
        } catch (error) {
            showStatus('Terjadi kesalahan. Coba lagi.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan';
            barcodeInput.focus();
        }
    }
    
    saveBtn.addEventListener('click', saveBarcode);
    
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveBarcode();
        }
    });
    
    toggleCameraBtn.addEventListener('click', async function() {
        if (cameraRunning) {
            await html5QrCode.stop();
            cameraRunning = false;
            cameraContainer.classList.add('hidden');
            toggleCameraBtn.textContent = 'Aktifkan Kamera';
        } else {
            cameraContainer.classList.remove('hidden');
            html5QrCode = new Html5Qrcode("qr-reader");
            try {
                await html5QrCode.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 250, height: 100 }, aspectRatio: 1.0 },
                    (decodedText) => {
                        barcodeInput.value = decodedText.trim();
                        showStatus('Barcode terdeteksi: ' + decodedText, 'success');
                    },
                    (errorMessage) => {
                        // Ignore scan errors
                    }
                );
                cameraRunning = true;
                toggleCameraBtn.textContent = 'Matikan Kamera';
                barcodeInput.focus();
            } catch (err) {
                showStatus('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan atau gunakan scanner pistol.', 'error');
                cameraContainer.classList.add('hidden');
            }
        }
    });
    
    barcodeInput.focus();
});
</script>
@endpush
@endsection
