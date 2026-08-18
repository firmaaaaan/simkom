@extends('layouts.public')

@section('title', 'Peminjaman Komputer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="bg-primary-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Peminjaman Komputer</h1>
            <p class="text-primary-100 text-sm mt-1">Pilih komputer yang tersedia untuk meminjam</p>
        </div>
        <div class="p-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Filter Pencarian</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-5">
                        <label for="laboratorium_id" class="block text-sm font-medium text-gray-700 mb-1.5">Laboratorium</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <select name="laboratorium_id" id="laboratorium_id" class="block w-full pl-10 pr-10 py-2.5 text-sm border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white shadow-sm transition-colors">
                                <option value="">Semua Laboratorium</option>
                                @foreach($laboratoriums as $lab)
                                    <option value="{{ $lab->id }}" {{ request('laboratorium_id') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="md:col-span-7">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1.5">Cari Komputer</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" class="block w-full pl-10 pr-4 py-2.5 text-sm border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white shadow-sm transition-colors" placeholder="Ketik nama atau kode komputer...">
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <button type="button" id="resetFilter" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset Filter
                    </button>
                    <span id="filterInfo" class="text-xs text-gray-500 hidden"></span>
                </div>
            </div>

            <div id="komputerGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @include('peminjaman-komputer.partials.komputer-grid')
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var laboratoriumSelect = document.getElementById('laboratorium_id');
    var searchInput = document.getElementById('search');
    var komputerGrid = document.getElementById('komputerGrid');
    var resetButton = document.getElementById('resetFilter');
    var filterInfo = document.getElementById('filterInfo');
    var debounceTimer = null;

    function getActiveFilterLabel() {
        var labText = laboratoriumSelect.options[laboratoriumSelect.selectedIndex].text;
        if (laboratoriumSelect.value && searchInput.value.trim()) {
            return 'Filter: ' + labText + ' & "' + searchInput.value.trim() + '"';
        } else if (laboratoriumSelect.value) {
            return 'Filter: ' + labText;
        } else if (searchInput.value.trim()) {
            return 'Pencarian: "' + searchInput.value.trim() + '"';
        }
        return '';
    }

    function updateFilterInfo() {
        var label = getActiveFilterLabel();
        if (label) {
            filterInfo.textContent = label;
            filterInfo.classList.remove('hidden');
        } else {
            filterInfo.classList.add('hidden');
        }
    }

    function buildUrl() {
        var url = new URL('{{ route('peminjaman-komputer.index') }}', window.location.origin);
        if (laboratoriumSelect.value) {
            url.searchParams.set('laboratorium_id', laboratoriumSelect.value);
        }
        if (searchInput.value.trim()) {
            url.searchParams.set('search', searchInput.value.trim());
        }
        return url.toString();
    }

    function fetchKomputers() {
        var url = buildUrl();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            komputerGrid.innerHTML = html;
            updateFilterInfo();
        })
        .catch(error => {
            console.error('Error fetching computers:', error);
        });
    }

    laboratoriumSelect.addEventListener('change', function () {
        fetchKomputers();
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchKomputers, 300);
    });

    resetButton.addEventListener('click', function () {
        laboratoriumSelect.value = '';
        searchInput.value = '';
        fetchKomputers();
    });

    updateFilterInfo();
});
</script>
@endpush

@endsection