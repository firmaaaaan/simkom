@extends('layouts.app')

@section('title', 'Detail Software')
@section('header', 'Detail Software')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('software.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-bold text-gray-800">Detail Software</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('software.edit', $software) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                    Edit
                </a>
                <form action="{{ route('software.destroy', $software) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus software ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ $software->code }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $software->status === 'Aktif' ? 'bg-green-100 text-green-800' : ($software->status === 'Expired' ? 'bg-red-100 text-red-800' : ($software->status === 'Trial' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600')) }}">
                    {{ $software->status }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Nama</p>
                    <p class="text-sm text-gray-800 font-medium">{{ $software->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Versi</p>
                    <p class="text-sm text-gray-800">{{ $software->version ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Kategori</p>
                    <p class="text-sm text-gray-800">{{ $software->category }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Jenis Lisensi</p>
                    <p class="text-sm text-gray-800">{{ $software->license_type ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Penanggung Jawab</p>
                    <p class="text-sm text-gray-800">{{ $software->pic_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Email PJ</p>
                    @if($software->pic_email)
                        <a href="mailto:{{ $software->pic_email }}" class="text-sm text-green-700 hover:underline">{{ $software->pic_email }}</a>
                    @else
                        <p class="text-sm text-gray-800">-</p>
                    @endif
                </div>
            </div>

            @if($software->description)
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Keterangan</p>
                    <p class="text-sm text-gray-800">{{ $software->description }}</p>
                </div>
            @endif

            <div class="pt-4 border-t border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Daftar Lisensi</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Total {{ $software->licenses->count() }} ·
                            Terpakai {{ $software->assigned_licenses_count }} ·
                            Tersedia {{ $software->licenses->count() - $software->assigned_licenses_count }}
                        </p>
                    </div>
                    @can('edit-software')
                        <a href="{{ route('software.edit', $software) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            Kelola Lisensi
                        </a>
                    @endcan
                </div>

                @if($software->licenses->isEmpty())
                    <p class="px-4 py-6 text-center text-sm text-gray-400 border border-dashed border-gray-200 rounded-lg">
                        Belum ada lisensi untuk software ini.
                    </p>
                @else
                    <div class="overflow-x-auto border border-gray-100 rounded-lg">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/60">
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">No</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">Label</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">Product Key</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">PC Terpasang</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">Beli</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">Expired</th>
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($software->licenses as $index => $license)
                                    <tr class="border-b border-gray-50 last:border-0">
                                        <td class="px-4 py-3 text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $license->label ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-xs text-gray-700" data-license-key
                                                    data-masked="{{ $license->maskedKey() }}"
                                                    data-url="{{ route('software.license-key', [$software, $license]) }}"
                                                    data-revealed="0">{{ $license->maskedKey() }}</span>
                                                @if($canRevealKey && $license->license_key)
                                                    <button type="button" onclick="toggleLicenseKey(this)"
                                                        class="text-gray-400 hover:text-green-600 transition-colors" title="Lihat / sembunyikan key">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            @if($license->computer)
                                                {{ $license->computer->code }}
                                                @if($license->computer->laboratory)
                                                    <span class="text-xs text-gray-400">· {{ $license->computer->laboratory->name }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">Belum dipasang</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $license->purchase_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $license->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            @php $licenseStatus = $license->statusLabel(); @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $licenseStatus === 'Expired' ? 'bg-red-100 text-red-700' : ($licenseStatus === 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600') }}">
                                                {{ $licenseStatus }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function toggleLicenseKey(button) {
        const span = button.parentElement.querySelector('[data-license-key]');
        if (!span) return;

        if (span.dataset.revealed === '1') {
            span.textContent = span.dataset.masked;
            span.dataset.revealed = '0';
            return;
        }

        try {
            const res = await fetch(span.dataset.url, {
                headers: { 'Accept': 'application/json' },
            });

            if (!res.ok) {
                alert('Tidak berhak melihat product key.');
                return;
            }

            const data = await res.json();
            if (!data.key) {
                alert('Product key kosong.');
                return;
            }

            span.textContent = data.key;
            span.dataset.revealed = '1';
        } catch (e) {
            alert('Gagal memuat product key.');
        }
    }
</script>
@endpush
