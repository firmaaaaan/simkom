@extends('layouts.app')
@section('title','Identifikasi Bahaya')
@section('header','Identifikasi Bahaya, Penilaian & Pengendalian Risiko')
@section('content')
<div class="mb-4"><a href="{{ route('k3.hazard.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">Tambah</a></div>
<div class="bg-white rounded-xl border overflow-hidden">
<table class="w-full text-sm">
<thead><tr><th class="p-3">Area/Aktivitas</th><th class="p-3">Bahaya</th><th class="p-3">Risiko</th><th class="p-3">Aksi</th></tr></thead>
<tbody>
@forelse($items as $item)
<tr class="border-t">
<td class="p-3">{{ $item->area_activity }}</td>
<td class="p-3">{{ $item->hazard }}</td>
<td class="p-3">{{ $item->risk }}</td>
<td class="p-3">
<a href="{{ route('k3.hazard.edit',$item) }}" class="text-blue-600">Edit</a>
<form action="{{ route('k3.hazard.destroy',$item) }}" method="POST" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 ml-2">Hapus</button></form>
</td>
</tr>
@empty
<tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada data</td></tr>
@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection