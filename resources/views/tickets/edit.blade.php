@extends('layouts.app')

@section('title', 'Edit Tiket')
@section('header', 'Edit Tiket Kendala')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>

    {{-- Errors --}}
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Edit Tiket</h3>
        <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Laboratorium <span class="text-red-500">*</span></label>
                    <select name="laboratory_id" id="labSelect" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Laboratorium</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratory_id', $ticket->laboratory_id) == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                    <select name="academic_year_id" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($academicYears as $year)
                            @php $selected = old('academic_year_id', $ticket->academic_year_id) ? old('academic_year_id', $ticket->academic_year_id) == $year->id : $year->status == 'Aktif'; @endphp
                            <option value="{{ $year->id }}" {{ $selected ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Komputer</label>
                    <select name="computer_id" id="computerSelect" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Komputer (opsional)</option>
                        @foreach($computers as $comp)
                            <option value="{{ $comp->id }}" {{ old('computer_id', $ticket->computer_id) == $comp->id ? 'selected' : '' }}>{{ $comp->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="Komputer" {{ old('category', $ticket->category) == 'Komputer' ? 'selected' : '' }}>Komputer</option>
                        <option value="Hardware" {{ old('category', $ticket->category) == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                        <option value="Software" {{ old('category', $ticket->category) == 'Software' ? 'selected' : '' }}>Software</option>
                        <option value="Jaringan" {{ old('category', $ticket->category) == 'Jaringan' ? 'selected' : '' }}>Jaringan</option>
                        <option value="Listrik/UPS" {{ old('category', $ticket->category) == 'Listrik/UPS' ? 'selected' : '' }}>Listrik/UPS</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas <span class="text-red-500">*</span></label>
                    <select name="priority" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="Rendah" {{ old('priority', $ticket->priority) == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                        <option value="Sedang" {{ old('priority', $ticket->priority) == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                        <option value="Tinggi" {{ old('priority', $ticket->priority) == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                        <option value="Darurat" {{ old('priority', $ticket->priority) == 'Darurat' ? 'selected' : '' }}>Darurat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="Open" {{ old('status', $ticket->status) == 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="In Progress" {{ old('status', $ticket->status) == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ old('status', $ticket->status) == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="Closed" {{ old('status', $ticket->status) == 'Closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ditugaskan Kepada</label>
                    <select name="assigned_to" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Belum ditugaskan</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', $ticket->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $ticket->title) }}" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old('description', $ticket->description) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    Perbarui Tiket
                </button>
                <a href="{{ route('tickets.show', $ticket) }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('labSelect').addEventListener('change', function() {
        var labId = this.value;
        var computerSelect = document.getElementById('computerSelect');
        var currentId = '{{ $ticket->computer_id }}';
        computerSelect.innerHTML = '<option value="">Memuat...</option>';

        if (!labId) {
            computerSelect.innerHTML = '<option value="">Pilih Komputer (opsional)</option>';
            return;
        }

        fetch('/computers/list?laboratory_id=' + labId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var html = '<option value="">Pilih Komputer (opsional)</option>';
                data.forEach(function(c) {
                    var sel = currentId == c.id ? ' selected' : '';
                    html += '<option value="' + c.id + '"' + sel + '>' + c.code + '</option>';
                });
                computerSelect.innerHTML = html;
            })
            .catch(function() {
                computerSelect.innerHTML = '<option value="">Gagal memuat</option>';
            });
    });
</script>
@endpush
@endsection
