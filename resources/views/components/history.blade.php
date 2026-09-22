@php
    $statusFilter = request('usage_status');
    $search = request('usage_search');
    $boxFilter = request('usage_box');
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Riwayat Penggunaan Box</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau penggunaan box oleh mahasiswa</p>
    </div>

    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <a href="{{ route('components.index', ['tab' => 'history']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ !$statusFilter ? 'ring-2 ring-green-400 border-green-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gray-900">{{ $usageStats['total'] }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('components.index', ['tab' => 'history', 'usage_status' => 'Using']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ $statusFilter === 'Using' ? 'ring-2 ring-yellow-400 border-yellow-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-yellow-600">{{ $usageStats['using'] }}</p>
                        <p class="text-xs text-gray-500">Dipakai</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('components.index', ['tab' => 'history', 'usage_status' => 'Returned']) }}"
               class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-shadow {{ $statusFilter === 'Returned' ? 'ring-2 ring-green-400 border-green-400' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-green-600">{{ $usageStats['returned'] }}</p>
                        <p class="text-xs text-gray-500">Dikembalikan</p>
                    </div>
                </div>
            </a>
        </div>

        <form action="{{ route('components.index') }}" method="GET" class="flex items-end gap-3">
            <input type="hidden" name="tab" value="history">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Pengguna</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="usage_search" value="{{ $search ?? '' }}" placeholder="Nama atau NIM..."
                        class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Box</label>
                <select name="usage_box" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Box</option>
                    @foreach($boxes as $box)
                        <option value="{{ $box->id }}" {{ ($boxFilter ?? '') == $box->id ? 'selected' : '' }}>{{ $box->code }} - {{ $box->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="usage_status" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="Using" {{ ($statusFilter ?? '') === 'Using' ? 'selected' : '' }}>Sedang Dipakai</option>
                    <option value="Returned" {{ ($statusFilter ?? '') === 'Returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                Filter
            </button>
            @if($search || $statusFilter || $boxFilter)
                <a href="{{ route('components.index', ['tab' => 'history']) }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="p-6">
        @if($usages->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 text-sm">Belum ada riwayat penggunaan</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-responsive-cards">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Waktu Pakai</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Box</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Pengguna</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">NIM</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Kelas</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-500">Durasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                <td data-label="Waktu Pakai" class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $usage->used_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $usage->used_at->format('H:i') }}</div>
                                </td>
                                <td data-label="Box" class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-700">
                                        {{ $usage->box->code }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $usage->box->name }}</div>
                                </td>
                                <td data-label="Pengguna" class="px-4 py-3 font-medium text-gray-900">{{ $usage->user_name }}</td>
                                <td data-label="NIM" class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $usage->user_nim }}</td>
                                <td data-label="Kelas" class="px-4 py-3 text-gray-600 text-sm">{{ $usage->user_kelas ?? '-' }}</td>
                                <td data-label="Status" class="px-4 py-3 text-center">
                                    @if($usage->status === 'Using')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                            Sedang Dipakai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td data-label="Durasi" class="px-4 py-3 text-center text-sm text-gray-600">
                                    {{ $usage->duration }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    Tidak ada data ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($usages->hasPages())
                <div class="mt-4 pt-4 border-t border-gray-100">
                    {{ $usages->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
