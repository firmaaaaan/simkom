@extends('layouts.app')
@section('title','Tanggap Darurat K3')
@section('header','Tanggap Darurat K3')
@section('content')
<div class="mb-4"><a href="{{ route('k3.emergency.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">Tambah</a></div>
<div class="bg-white rounded-xl border overflow-hidden">
<table class="w-full text-sm">
<thead><tr><th class="p-3">Skenario</th><th class="p-3">Penanggung Jawab</th><th class="p-3">Kontak</th><th class="p-3">Latihan</th><th class="p-3">Aksi</th></tr></thead>
<tbody>
@forelse($items as $item)
<tr class="border-t">
<td class="p-3">{{ $item->scenario }}</td>
<td class="p-3">{{ $item->responsible_person }}</td>
<td class="p-3">{{ $item->contact }}</td>
<td class="p-3">{{ $item->drill_date?->format('d/m/Y') }}</td>
<td class="p-3">
<a href="{{ route('k3.emergency.edit',$item) }}" class="text-blue-600">Edit</a>
<form action="{{ route('k3.emergency.destroy',$item) }}" method="POST" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 ml-2">Hapus</button></form>
</td>
</tr>
@empty
<tr><td colspan="5" class="p-6 text-center text-gray-500">Belum ada data</td></tr>
@endforelse
</tbody>
</table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection