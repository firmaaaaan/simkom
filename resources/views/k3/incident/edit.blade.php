@extends('layouts.app')
@section('title','Edit Laporan Insiden')
@section('header','Edit Laporan Insiden')
@section('content')
<form method="POST" action="{{ route('k3.incident.update',$incident) }}" class="bg-white rounded-xl border p-6 space-y-4">
@csrf @method('PUT')
<div><label class="block text-sm font-medium">Laboratorium</label><select name="laboratory_id" class="w-full border rounded-lg p-2"><option value="">-</option>@foreach($laboratories as $lab)<option value="{{ $lab->id }}" @selected($incident->laboratory_id==$lab->id)>{{ $lab->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-medium">Tanggal</label><input type="date" name="incident_date" value="{{ $incident->incident_date?->format('Y-m-d') }}" class="w-full border rounded-lg p-2" required></div>
<div><label class="block text-sm font-medium">Pelapor</label><input name="reporter_name" value="{{ $incident->reporter_name }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Jenis Insiden</label><input name="incident_type" value="{{ $incident->incident_type }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Deskripsi</label><textarea name="description" class="w-full border rounded-lg p-2">{{ $incident->description }}</textarea></div>
<div><label class="block text-sm font-medium">Dampak</label><textarea name="impact" class="w-full border rounded-lg p-2">{{ $incident->impact }}</textarea></div>
<div><label class="block text-sm font-medium">Tindakan Segera</label><textarea name="immediate_action" class="w-full border rounded-lg p-2">{{ $incident->immediate_action }}</textarea></div>
<div><label class="block text-sm font-medium">Tindak Lanjut</label><textarea name="follow_up" class="w-full border rounded-lg p-2">{{ $incident->follow_up }}</textarea></div>
<div><label class="block text-sm font-medium">Status</label><input name="status" value="{{ $incident->status }}" class="w-full border rounded-lg p-2"></div>
<div class="flex gap-2"><button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button><a href="{{ route('k3.incident.index') }}" class="px-4 py-2 border rounded-lg">Batal</a></div>
</form>
@endsection