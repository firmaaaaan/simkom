@extends('layouts.app')

@section('title', 'Input Pengecekan Perangkat')
@section('header', 'Input Pengecekan Perangkat')

@section('content')
<div class="mb-6">
    <a href="{{ route('device-checks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Kembali
    </a>
</div>

@include('device-checks.partials.form', ['isEdit' => false])
@endsection

@push('scripts')
<script>
    // Ganti lab / tahun ajaran langsung memuat ulang matriksnya.
    ['laboratory_id', 'academic_year_id'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;

        el.addEventListener('change', function () {
            const params = new URLSearchParams(window.location.search);
            params.set('laboratory_id', document.getElementById('laboratory_id').value);
            params.set('academic_year_id', document.getElementById('academic_year_id').value);
            window.location.href = '{{ route('device-checks.create') }}?' + params.toString();
        });
    });

    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.check-item').forEach(function (cb) {
                cb.checked = checkAll.checked;
            });
        });
    }
</script>
@endpush
