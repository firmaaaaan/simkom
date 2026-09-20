@extends('layouts.app')

@section('title', 'Peminjaman Komputer')
@section('header', 'Peminjaman Komputer')

@section('content')
{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <a href="{{ route('borrowings.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Peminjaman</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>
    </a>

    <a href="{{ route('borrowings.index', ['status' => 'Pending']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
    </a>

    <a href="{{ route('borrowings.index', ['status' => 'Approved']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Disetujui</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
    </a>

    <a href="{{ route('borrowings.index', ['status' => 'Returned']) }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Dikembalikan</p>
            <p class="text-2xl font-bold text-gray-600">{{ $stats['returned'] }}</p>
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
        <p class="font-medium">Perubahan status gagal disimpan:</p>
        <ul class="list-disc list-inside mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FILTER & SEARCH --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-base font-semibold text-gray-800">Daftar Peminjaman</h2>
        <div class="flex items-center gap-2">
        <x-export-button route="borrowings.export" :params="request()->query()" />
        <form action="{{ route('borrowings.index') }}" method="GET" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, NIM..." class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent w-64">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Cari
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('borrowings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            @endif
        </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Kode Tracking</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Peminjam</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Komputer</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Laboratorium</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Tanggal</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Jam</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Catatan Admin</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($borrowings as $borrowing)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td data-label="Kode Tracking" class="px-6 py-4">
                            <span class="font-mono font-semibold text-blue-600">{{ $borrowing->tracking_code }}</span>
                        </td>
                        <td data-label="Peminjam" class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-800">{{ $borrowing->borrower_name }}</p>
                                <p class="text-xs text-gray-500">{{ $borrowing->borrower_nim }}</p>
                            </div>
                        </td>
                        <td data-label="Komputer" class="px-6 py-4 font-medium text-gray-800">{{ $borrowing->computer->code ?? '-' }}</td>
                        <td data-label="Laboratorium" class="px-6 py-4 text-gray-600">{{ $borrowing->laboratory->name ?? '-' }}</td>
                        <td data-label="Tanggal" class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($borrowing->borrow_date)->format('d M Y') }}</td>
                        <td data-label="Jam" class="px-6 py-4 text-gray-600">{{ $borrowing->borrow_time_start }} - {{ $borrowing->borrow_time_end }}</td>
                        <td data-label="Status" class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'Pending' => 'bg-yellow-100 text-yellow-700',
                                    'Approved' => 'bg-green-100 text-green-700',
                                    'Rejected' => 'bg-red-100 text-red-700',
                                    'Returned' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$borrowing->status] ?? '' }}">
                                {{ $borrowing->status }}
                            </span>
                        </td>
                        <td data-label="Catatan Admin" class="px-6 py-4">
                            @if(filled($borrowing->admin_notes))
                                <p class="text-gray-700 max-w-xs" title="{{ $borrowing->admin_notes }}">
                                    {{ \Illuminate\Support\Str::limit($borrowing->admin_notes, 80) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $borrowing->status === 'Rejected' ? 'Alasan penolakan' : 'Catatan persetujuan' }}
                                </p>
                            @else
                                <span class="text-xs text-gray-400">&ndash;</span>
                            @endif
                        </td>
                        <td data-label="Aksi" class="px-6 py-4">
                            @if($borrowing->status === 'Pending')
                                <div class="flex items-center gap-1">
                                    <button onclick="openApproveModal('{{ $borrowing->tracking_code }}', {{ $borrowing->id }}, 'Approved')" class="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors" title="Setujui">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </button>
                                    <button onclick="openApproveModal('{{ $borrowing->tracking_code }}', {{ $borrowing->id }}, 'Rejected')" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Tolak">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <p class="font-medium">Tidak ada data peminjaman</p>
                            <p class="text-sm mt-1">Belum ada pengajuan peminjaman komputer</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($borrowings->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $borrowings->links() }}
        </div>
    @endif
</div>

{{-- APPROVE/REJECT MODAL --}}
<div id="approveModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Konfirmasi</h3>
        </div>
        <form id="statusForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="px-6 py-4 space-y-4">
                <p class="text-sm text-gray-600">
                    Anda yakin ingin <span id="modalAction" class="font-semibold"></span> peminjaman <span id="modalTracking" class="font-mono font-semibold text-blue-600"></span>?
                </p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="notesLabel">Catatan Admin (opsional)</label>
                    <textarea name="admin_notes" id="adminNotes" rows="3" placeholder="Tambahkan catatan..." class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    <p class="text-xs text-red-500 mt-1 hidden" id="notesError">Alasan penolakan wajib diisi</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" id="modalSubmitBtn" class="px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors">
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openApproveModal(trackingCode, id, status) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('statusForm');
        const title = document.getElementById('modalTitle');
        const action = document.getElementById('modalAction');
        const tracking = document.getElementById('modalTracking');
        const submitBtn = document.getElementById('modalSubmitBtn');
        const notesLabel = document.getElementById('notesLabel');
        const adminNotes = document.getElementById('adminNotes');
        const notesError = document.getElementById('notesError');

        form.action = `/borrowings/${id}/status`;
        tracking.textContent = trackingCode;
        adminNotes.value = '';
        notesError.classList.add('hidden');
        adminNotes.classList.remove('border-red-500');

        if (status === 'Approved') {
            title.textContent = 'Setujui Peminjaman';
            action.textContent = 'menyetujui';
            action.className = 'font-semibold text-green-600';
            submitBtn.className = 'px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors';
            submitBtn.textContent = 'Setujui';
            notesLabel.textContent = 'Catatan Admin (opsional)';
            adminNotes.required = false;
            adminNotes.placeholder = 'Tambahkan catatan...';
        } else {
            title.textContent = 'Tolak Peminjaman';
            action.textContent = 'menolak';
            action.className = 'font-semibold text-red-600';
            submitBtn.className = 'px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors';
            submitBtn.textContent = 'Tolak';
            notesLabel.innerHTML = 'Alasan Penolakan <span class="text-red-500">*</span>';
            adminNotes.required = true;
            adminNotes.placeholder = 'Masukkan alasan penolakan...';
        }

        // Add hidden input for status
        let statusInput = form.querySelector('input[name="status"]');
        if (!statusInput) {
            statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            form.appendChild(statusInput);
        }
        statusInput.value = status;

        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    document.getElementById('approveModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
@endpush
@endsection
