@extends('layouts.app')

@section('title', 'Bulk Assign Hardware & Software')
@section('header', 'Bulk Assign Hardware & Software')

@section('content')
<div x-data="bulkAssign()">
    <div class="mb-6">
        <a href="{{ route('computers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-green-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('computers.store-bulk-assign') }}" method="POST">
        @csrf

        {{-- Section 1: Pilih Komputer --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800">1. Pilih Komputer</h2>
                <span class="text-sm text-gray-500"><span x-text="selectedComputers.length"></span> dipilih</span>
            </div>

            <div class="flex flex-wrap items-center gap-3 mb-4">
                <select onchange="window.location.href='{{ url()->current() }}?laboratory_id=' + this.value"
                    class="text-sm border border-gray-200 rounded-lg py-2 focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Lab</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" @selected(request('laboratory_id') === $lab->id)>{{ $lab->name }}</option>
                    @endforeach
                </select>
                <input type="text" placeholder="Cari kode komputer..." x-model="searchQuery"
                    class="text-sm border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-green-500 w-52">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" :checked="selectedComputers.length === filteredComputers.length && filteredComputers.length > 0"
                        @change="toggleAllComputers($event)"
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    Pilih Semua
                </label>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 max-h-64 overflow-y-auto">
                @forelse($computers as $comp)
                    <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition-colors"
                        :class="selectedComputers.includes('{{ $comp->id }}') ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:bg-gray-50'"
                        x-show="searchQuery === '' || '{{ $comp->code }}'.toLowerCase().includes(searchQuery.toLowerCase())">
                        <input type="checkbox" value="{{ $comp->id }}" x-model="selectedComputers"
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <div class="min-w-0">
                            <span class="text-sm font-medium text-gray-800 block truncate">{{ $comp->code }}</span>
                            <span class="text-xs text-gray-400 block truncate">{{ $comp->laboratory?->name ?? '-' }}</span>
                        </div>
                    </label>
                @empty
                    <p class="text-sm text-gray-400 col-span-full">Tidak ada komputer ditemukan</p>
                @endforelse
            </div>
        </div>

        {{-- Section 2: Pilih Hardware --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">2. Pilih Hardware</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @forelse($hardware as $item)
                    <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition-colors"
                        :class="selectedHardware.includes('{{ $item->id }}') ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="checkbox" name="hardware_ids[]" value="{{ $item->id }}" x-model="selectedHardware"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 truncate">{{ $item->name }}</span>
                    </label>
                @empty
                    <p class="text-sm text-gray-400 col-span-full">Belum ada data hardware</p>
                @endforelse
            </div>
        </div>

        {{-- Section 3: Pilih Software --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">3. Pilih Software</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @forelse($software as $item)
                    <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition-colors"
                        :class="selectedSoftware.includes('{{ $item->id }}') ? 'border-purple-400 bg-purple-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="checkbox" name="software_ids[]" value="{{ $item->id }}" x-model="selectedSoftware"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700 truncate">{{ $item->name }}</span>
                    </label>
                @empty
                    <p class="text-sm text-gray-400 col-span-full">Belum ada data software</p>
                @endforelse
            </div>
        </div>

        {{-- Hidden inputs for selected computers --}}
        <template x-for="id in selectedComputers" :key="id">
            <input type="hidden" name="computer_ids[]" :value="id">
        </template>

        {{-- Submit --}}
        <div class="flex items-center justify-between bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">
                Akan ditugaskan ke <span class="font-semibold text-gray-800" x-text="selectedComputers.length"></span> komputer
            </p>
            <button type="submit"
                :disabled="selectedComputers.length === 0 || (selectedHardware.length === 0 && selectedSoftware.length === 0)"
                class="px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Terapkan
            </button>
        </div>
    </form>
</div>

<script>
function bulkAssign() {
    return {
        selectedComputers: [],
        selectedHardware: [],
        selectedSoftware: [],
        searchQuery: '',
        filteredComputers: @json($computers),
        toggleAllComputers(e) {
            if (e.target.checked) {
                this.selectedComputers = this.filteredComputers.map(c => c.id);
            } else {
                this.selectedComputers = [];
            }
        }
    };
}
</script>
@endsection
