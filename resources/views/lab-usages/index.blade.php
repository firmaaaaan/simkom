@extends('layouts.app')

@section('title', 'Penggunaan Lab')
@section('header', 'Penggunaan Lab')

@section('content')
{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <a href="{{ route('lab-usages.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Penggunaan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
    </a>

    <a href="{{ route('lab-usages.index', ['status' => 'In']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Masih di Lab (In)</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['in'] }}</p>
        </div>
    </a>

    <a href="{{ route('lab-usages.index', ['status' => 'Out']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Tervalidasi Keluar (Out)</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['out'] }}</p>
        </div>
    </a>

    <a href="{{ route('lab-usages.index', ['date' => now()->format('Y-m-d')]) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Check-in Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['today'] }}</p>
        </div>
    </a>
</div>

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

{{-- VALIDATION MESSAGE --}}
@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FILTER & SEARCH --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Riwayat Penggunaan Lab</h2>
            <p class="text-xs text-gray-500 mt-0.5">Check-in via QR oleh mahasiswa, validasi keluar oleh admin/laboran</p>
        </div>
        <a href="{{ route('lab-usages.qr-stiker') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            QR Stiker Lab
        </a>
    </div>

    <div class="px-6 py-4 border-b border-gray-100">
        <form action="{{ route('lab-usages.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Laboratorium</label>
                <select name="laboratory_id" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">Semua</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>
                            {{ $lab->name }} ({{ $lab->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">Semua</option>
                    <option value="In" {{ request('status') === 'In' ? 'selected' : '' }}>In (masih di lab)</option>
                    <option value="Out" {{ request('status') === 'Out' ? 'selected' : '' }}>Out (sudah keluar)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, prodi, keperluan..."
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-56">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Terapkan
            </button>
            @if(request()->hasAny(['search', 'status', 'laboratory_id', 'date']))
                <a href="{{ route('lab-usages.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Nama</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Prodi</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Keperluan</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Laboratorium</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Hari</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Check-in</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Durasi</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Divalidasi</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($labUsages as $usage)
                    @php
                        $previewPayload = [
                            'user_name'     => $usage->user_name,
                            'user_prodi'    => $usage->user_prodi,
                            'purpose'       => $usage->purpose,
                            'day'           => $usage->day,
                            'status'        => $usage->status,
                            'checked_in_at' => optional($usage->checked_in_at)->toIso8601String(),
                            'validated_at'  => optional($usage->validated_at)->toIso8601String(),
                            'exit_note'     => $usage->exit_note,
                            'lab'           => $usage->laboratory->name ?? '-',
                            'lab_code'      => $usage->laboratory->code ?? '-',
                            'validator'     => $usage->validatedBy->name ?? null,
                            'duration'      => $usage->duration,
                        ];
                        $previewJson = json_encode($previewPayload, JSON_HEX_APOS | JSON_HEX_QUOT);
                    @endphp
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td data-label="Nama" class="px-6 py-4 font-medium">
                            <button type="button"
                                data-usage="{{ $previewJson }}"
                                onclick="openPreviewModal(this)"
                                class="text-gray-800 hover:text-green-600 hover:underline text-left transition-colors" title="Preview detail">
                                {{ $usage->user_name }}
                            </button>
                        </td>
                        <td data-label="Prodi" class="px-6 py-4 text-gray-600">{{ $usage->user_prodi }}</td>
                        <td data-label="Keperluan" class="px-6 py-4 text-gray-600 max-w-[180px]">
                            <span title="{{ $usage->purpose }}">{{ \Illuminate\Support\Str::limit($usage->purpose, 50) }}</span>
                        </td>
                        <td data-label="Laboratorium" class="px-6 py-4 text-gray-600">{{ $usage->laboratory->name ?? '-' }}</td>
                        <td data-label="Hari" class="px-6 py-4 text-gray-600">{{ $usage->day }}</td>
                        <td data-label="Check-in" class="px-6 py-4 text-gray-600">{{ $usage->checked_in_at->format('d M Y H:i') }}</td>
                        <td data-label="Durasi" class="px-6 py-4 text-gray-600">{{ $usage->duration }}</td>
                        <td data-label="Status" class="px-6 py-4">
                            @if($usage->status === 'In')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">In</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Out</span>
                            @endif
                        </td>
                        <td data-label="Divalidasi" class="px-6 py-4 text-gray-600">
                            @if($usage->validated_at)
                                <p>{{ $usage->validated_at->format('d M Y H:i') }}</p>
                                <p class="text-xs text-gray-400">oleh {{ $usage->validatedBy->name ?? '-' }}</p>
                                @if(filled($usage->exit_note))
                                    <p class="text-xs text-gray-500 mt-0.5" title="{{ $usage->exit_note }}">{{ \Illuminate\Support\Str::limit($usage->exit_note, 40) }}</p>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">&ndash;</span>
                            @endif
                        </td>
                        <td data-label="Aksi" class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <button type="button"
                                    data-usage="{{ $previewJson }}"
                                    onclick="openPreviewModal(this)"
                                    class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Preview detail">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                                @if($usage->status === 'In')
                                    <button onclick="openValidateModal('{{ $usage->id }}', '{{ e($usage->user_name) }}')"
                                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition-colors">
                                        Validasi Keluar
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <p class="font-medium">Tidak ada data penggunaan lab</p>
                            <p class="text-sm mt-1">Belum ada mahasiswa yang check-in via QR</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($labUsages->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $labUsages->links() }}
        </div>
    @endif
</div>

{{-- PREVIEW DETAIL MODAL --}}
<div id="previewModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" style="display:none">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Preview Penggunaan Lab</h3>
                    <p class="text-xs text-gray-500">Detail check-in via QR</p>
                </div>
            </div>
            <button onclick="closePreviewModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4 overflow-y-auto max-h-[70vh]">
            <div class="flex items-center justify-between">
                <div>
                    <p id="pvName" class="text-lg font-bold text-gray-900"></p>
                    <p id="pvProdi" class="text-sm text-gray-500"></p>
                </div>
                <span id="pvStatus" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"></span>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Laboratorium</span>
                    <span id="pvLab" class="font-medium text-gray-900 text-right"></span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Keperluan</span>
                    <span id="pvPurpose" class="font-medium text-gray-900 text-right"></span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Hari</span>
                    <span id="pvDay" class="font-medium text-gray-900"></span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Check-in</span>
                    <span id="pvCheckIn" class="font-medium text-gray-900"></span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-gray-500">Durasi</span>
                    <span id="pvDuration" class="font-medium text-gray-900"></span>
                </div>
            </div>

            <div id="pvValidatedBox" class="bg-green-50 border border-green-200 rounded-xl p-4 space-y-2 text-sm hidden">
                <p class="text-xs font-semibold text-green-800 uppercase tracking-wider">Validasi Keluar</p>
                <div class="flex justify-between gap-4">
                    <span class="text-green-700">Waktu</span>
                    <span id="pvValidatedAt" class="font-medium text-green-900"></span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-green-700">Oleh</span>
                    <span id="pvValidator" class="font-medium text-green-900"></span>
                </div>
                <div id="pvNoteWrap" class="hidden">
                    <span class="text-green-700 block mb-0.5">Catatan</span>
                    <p id="pvNote" class="text-green-900"></p>
                </div>
            </div>

            <div id="pvPendingBox" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm">
                <p class="text-yellow-800 font-medium">Menunggu validasi keluar oleh admin</p>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button type="button" onclick="closePreviewModal()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

{{-- VALIDATE OUT MODAL --}}
<div id="validateModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Validasi Keluar</h3>
        </div>
        <form id="validateForm" method="POST">
            @csrf
            <div class="px-6 py-4 space-y-4">
                <p class="text-sm text-gray-600">
                    Anda yakin ingin memvalidasi keluar <span id="modalName" class="font-semibold text-gray-900"></span>?
                </p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="exit_note" rows="3" placeholder="Catatan saat pengembalian, jika ada..."
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeValidateModal()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Validasi Keluar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function formatDateTime(value) {
        if (!value) return '-';
        const d = new Date(value.replace(/-/g, '/').replace('T', ' '));
        if (isNaN(d)) return value;
        const pad = n => String(n).padStart(2, '0');
        return `${pad(d.getDate())} ${['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][d.getMonth()]} ${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    function openPreviewModal(el) {
        const usage = JSON.parse(el.dataset.usage);
        const extra = {
            lab: usage.lab,
            lab_code: usage.lab_code,
            validator: usage.validator,
            duration: usage.duration,
        };

        document.getElementById('pvName').textContent = usage.user_name || '-';
        document.getElementById('pvProdi').textContent = usage.user_prodi || '-';
        document.getElementById('pvLab').textContent = `${extra.lab} (${extra.lab_code})`;
        document.getElementById('pvPurpose').textContent = usage.purpose || '-';
        document.getElementById('pvDay').textContent = usage.day || '-';
        document.getElementById('pvCheckIn').textContent = formatDateTime(usage.checked_in_at);
        document.getElementById('pvDuration').textContent = extra.duration || '-';

        const statusEl = document.getElementById('pvStatus');
        if (usage.status === 'In') {
            statusEl.textContent = 'In (di lab)';
            statusEl.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700';
        } else {
            statusEl.textContent = 'Out (sudah keluar)';
            statusEl.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700';
        }

        const validated = !!usage.validated_at;
        document.getElementById('pvValidatedBox').classList.toggle('hidden', !validated);
        document.getElementById('pvPendingBox').classList.toggle('hidden', validated);
        if (validated) {
            document.getElementById('pvValidatedAt').textContent = formatDateTime(usage.validated_at);
            document.getElementById('pvValidator').textContent = extra.validator || '-';
            const noteWrap = document.getElementById('pvNoteWrap');
            if (usage.exit_note) {
                noteWrap.classList.remove('hidden');
                document.getElementById('pvNote').textContent = usage.exit_note;
            } else {
                noteWrap.classList.add('hidden');
            }
        }

        const modal = document.getElementById('previewModal');
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
    }

    function closePreviewModal() {
        const modal = document.getElementById('previewModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    document.getElementById('previewModal').addEventListener('click', function (e) {
        if (e.target === this) closePreviewModal();
    });

    function openValidateModal(id, name) {
        document.getElementById('validateForm').action = `/lab-usages/${id}/validate-out`;
        document.getElementById('modalName').textContent = name;
        document.querySelector('#validateForm textarea[name="exit_note"]').value = '';
        document.getElementById('validateModal').classList.remove('hidden');
    }

    function closeValidateModal() {
        document.getElementById('validateModal').classList.add('hidden');
    }

    document.getElementById('validateModal').addEventListener('click', function (e) {
        if (e.target === this) closeValidateModal();
    });
</script>
@endpush
@endsection
