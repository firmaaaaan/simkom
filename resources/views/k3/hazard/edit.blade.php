@extends('layouts.app')
@section('title','Edit Identifikasi Bahaya')
@section('header','Edit Identifikasi Bahaya')
@section('content')
<form method="POST" action="{{ route('k3.hazard.update',$hazard) }}" class="bg-white rounded-xl border p-6 space-y-4">
@csrf @method('PUT')
<div><label class="block text-sm font-medium">Laboratorium</label><select name="laboratory_id" class="w-full border rounded-lg p-2"><option value="">-</option>@foreach($laboratories as $lab)<option value="{{ $lab->id }}" @selected($hazard->laboratory_id==$lab->id)>{{ $lab->name }}</option>@endforeach</select></div>
<div><label class="block text-sm font-medium">Area/Aktivitas</label><input name="area_activity" value="{{ $hazard->area_activity }}" class="w-full border rounded-lg p-2" required></div>
<div><label class="block text-sm font-medium">Potensi Bahaya</label><textarea name="hazard" class="w-full border rounded-lg p-2">{{ $hazard->hazard }}</textarea></div>
<div><label class="block text-sm font-medium">Risiko</label><textarea name="risk" class="w-full border rounded-lg p-2">{{ $hazard->risk }}</textarea></div>
<div class="grid grid-cols-3 gap-3">
<div><label class="block text-sm font-medium">Peluang Sebelum</label><input name="likelihood_before" value="{{ $hazard->likelihood_before }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Akibat Sebelum</label><input name="impact_before" value="{{ $hazard->impact_before }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Level Sebelum</label><input name="risk_level_before" value="{{ $hazard->risk_level_before }}" class="w-full border rounded-lg p-2"></div>
</div>
<div><label class="block text-sm font-medium">Pengendalian Sebelum</label><textarea name="control_before" class="w-full border rounded-lg p-2">{{ $hazard->control_before }}</textarea></div>
<div><label class="block text-sm font-medium">Jadwal</label><input name="schedule" value="{{ $hazard->schedule }}" class="w-full border rounded-lg p-2"></div>
<div class="grid grid-cols-3 gap-3">
<div><label class="block text-sm font-medium">Peluang Sesudah</label><input name="likelihood_after" value="{{ $hazard->likelihood_after }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Akibat Sesudah</label><input name="impact_after" value="{{ $hazard->impact_after }}" class="w-full border rounded-lg p-2"></div>
<div><label class="block text-sm font-medium">Level Sesudah</label><input name="risk_level_after" value="{{ $hazard->risk_level_after }}" class="w-full border rounded-lg p-2"></div>
</div>
<div><label class="block text-sm font-medium">Pengendalian Sesudah</label><textarea name="control_after" class="w-full border rounded-lg p-2">{{ $hazard->control_after }}</textarea></div>
<div><label class="block text-sm font-medium">Rekomendasi</label><textarea name="recommendation" class="w-full border rounded-lg p-2">{{ $hazard->recommendation }}</textarea></div>
<div><label class="block text-sm font-medium">Wewenang</label><input name="authority" value="{{ $hazard->authority }}" class="w-full border rounded-lg p-2"></div>
<div class="flex gap-2"><button class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button><a href="{{ route('k3.hazard.index') }}" class="px-4 py-2 border rounded-lg">Batal</a></div>
</form>
@endsection