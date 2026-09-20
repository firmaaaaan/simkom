@extends('layouts.app')

@section('title', 'Tambah Role')
@section('header', 'Tambah Role')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-green-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Daftar Role
    </a>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Form Tambah Role</h3>
        <p class="text-sm text-gray-500 mb-6">Tentukan nama role dan permission yang dimilikinya.</p>

        <form action="{{ route('roles.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Role <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="50"
                        placeholder="mis. laboran"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="text-xs text-gray-400 mt-1">Dipakai sistem sebagai kode role (huruf, angka, - dan _).</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Label <span class="text-red-500">*</span></label>
                    <input type="text" name="label" value="{{ old('label') }}" required maxlength="255"
                        placeholder="mis. Laboran"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Permission</label>
                <p class="text-xs text-gray-500 mb-3">Centang menu dan aksi yang boleh diakses role ini.</p>

                @include('roles.partials.permission-checkboxes', [
                    'permissions' => $permissions,
                    'selectedIds' => array_map('intval', old('permissions', [])),
                ])
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    Simpan Role
                </button>
                <a href="{{ route('roles.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
