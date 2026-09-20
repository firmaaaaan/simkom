@extends('layouts.app')

@section('title', 'Kelola Role')
@section('header', 'Kelola Role')

@section('content')
{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Role</p>
            <p class="text-2xl font-bold text-gray-800">{{ $roles->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Permission</p>
            <p class="text-2xl font-bold text-gray-800">{{ $roles->sum('permissions_count') }}</p>
        </div>
    </div>

    <a href="{{ route('users.index') }}" class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">User Terdaftar</p>
            <p class="text-2xl font-bold text-gray-800">{{ $roles->sum('users_count') }}</p>
        </div>
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-6 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- TABLE --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-gray-800">Daftar Role</h2>
            <p class="text-xs text-gray-500 mt-1">Permission menentukan menu dan aksi yang bisa diakses pemilik role.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-export-button route="roles.export" />
            <a href="{{ route('roles.create') }}" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
                Tambah Role
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm table-responsive-cards">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Role</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Permission</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">User</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors align-top">
                        <td data-label="Role" class="px-6 py-4">
                            <p class="font-medium text-gray-800">{{ $role->label }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 mt-1">{{ $role->name }}</span>
                        </td>
                        <td data-label="Permission" class="px-6 py-4">
                            @if($role->permissions_count === 0)
                                <span class="text-xs text-gray-400 italic">Belum ada permission</span>
                            @else
                                <div class="flex flex-wrap gap-1 max-w-md">
                                    @foreach($role->permissions->take(4) as $permission)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">{{ $permission->label }}</span>
                                    @endforeach
                                    @if($role->permissions_count > 4)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">+{{ $role->permissions_count - 4 }} lainnya</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td data-label="User" class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role->users_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $role->users_count }} user
                            </span>
                        </td>
                        <td data-label="Aksi" class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('roles.edit', $role) }}" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors" title="Edit">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </a>
                                @if($role->users_count > 0)
                                    <span class="p-1.5 cursor-not-allowed" title="Masih dipakai {{ $role->users_count }} user">
                                        <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </span>
                                @else
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus role {{ $role->label }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <p class="font-medium">Belum ada data role</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
