@extends('layouts.public')

@section('title', 'Verifikasi Peminjaman')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Peminjaman Berhasil Diajukan</h1>
            <p class="text-slate-600 mb-6">Peminjaman akan segera diverifikasi oleh admin. Harap simpan kode tracker berikut:</p>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 text-left mb-6">
                <h2 class="font-semibold text-slate-900 mb-2">Kode Tracker</h2>
                <p class="text-sm text-slate-600 mb-1">Simpan kode berikut untuk mengecek status peminjaman:</p>
                <div class="flex items-center justify-between gap-2">
                    <code class="flex-1 bg-white border border-slate-200 rounded px-3 py-2 text-sm font-mono text-primary-700 text-center">{{ $kode_tracker }}</code>
                     <button type="button" class="bg-primary-600 text-white px-3 py-2 rounded-lg text-sm" id="copyBtnPeminjaman" data-kode="{{ $kode_tracker }}">Copy</button>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6 text-left">
                <p class="font-medium">Perhatian:</p>
                <p class="text-sm">Peminjaman akan dibatalkan jika terjadi reschedule perkuliahan di laboratorium terkait.</p>
            </div>

            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('peminjaman-komputer.index') }}" class="bg-primary-600 text-white px-6 py-2.5 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                    Kembali ke Daftar Komputer
                </a>
                <a href="{{ route('laporan-kendala-komputer.track') }}" class="bg-white text-primary-600 border border-primary-200 px-6 py-2.5 rounded-lg hover:bg-primary-50 transition-colors font-medium">
                    Lacak Status
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="copyToast" class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    Kode tracker disalin!
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var copyBtn = document.getElementById('copyBtnPeminjaman');
        var toast = document.getElementById('copyToast');

        if (copyBtn && toast) {
            copyBtn.addEventListener('click', function() {
                var kode = copyBtn.getAttribute('data-kode');
                
                // Fallback copy method
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(kode).then(function() {
                        showToast();
                    }).catch(function() {
                        fallbackCopy(kode);
                        showToast();
                    });
                } else {
                    fallbackCopy(kode);
                    showToast();
                }
            });
        }

        function fallbackCopy(text) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
            } catch (e) {
                console.error('Copy failed', e);
            }
            document.body.removeChild(textarea);
        }

        function showToast() {
            toast.classList.remove('translate-x-full');
            setTimeout(function() {
                toast.classList.add('translate-x-full');
            }, 2000);
        }
    });
</script>
@endsection