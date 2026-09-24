@extends('layouts.app')

@section('title', 'Jadwal Penggunaan Laboratorium')
@section('header', 'Jadwal Penggunaan Laboratorium')

@section('content')
<div class="mb-6">
    <p class="text-sm text-gray-500">Lihat dan kelola jadwal penggunaan laboratorium per slot waktu</p>
</div>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div class="flex items-center gap-2">
        <button onclick="openImportModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Excel
        </button>
        <a href="{{ route('lab-schedules.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export Excel
        </a>
    </div>
    <p class="text-sm text-gray-500">Total: <span id="total-count" class="font-semibold text-gray-800">0</span> jadwal</p>
</div>

{{-- Tautan jadwal real-time (tampil di halaman jadwal publik) --}}
<div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
    <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-800">Tautan Jadwal Real-Time</h3>
            <p class="text-xs text-gray-500 mt-0.5">Ditampilkan di halaman <a href="{{ route('jadwal-lab.index') }}" target="_blank" class="text-green-700 font-medium hover:underline">jadwal publik</a> sebagai rujukan jadwal real-time. Kosongkan untuk menyembunyikan tautan.</p>
            @if(session('success'))
                <div class="mt-2 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-xs">{{ session('success') }}</div>
            @endif
            <form action="{{ route('settings.realtime-schedule-url') }}" method="POST" class="mt-3 flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="url" name="realtime_url" value="{{ $realtimeUrl }}" placeholder="https://contoh.ac.id/jadwal-realtime"
                    class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Simpan</button>
            </form>
            @if($errors->any())
                <p class="mt-2 text-xs text-red-600">{{ $errors->first() }}</p>
            @endif
        </div>
    </div>
</div>

<div id="day-tabs" class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
    <button onclick="filterDay('all')" data-day-filter="all" class="day-tab active px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors">Semua</button>
    @foreach($days as $dayKey => $dayLabel)
        <button onclick="filterDay('{{ $dayKey }}')" data-day-filter="{{ $dayKey }}" class="day-tab px-4 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-colors">{{ $dayLabel }}</button>
    @endforeach
</div>

{{-- Banner mode salin/tempel --}}
<div id="paste-banner" class="hidden mb-4 items-center justify-between gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
    <div class="flex items-center gap-2 min-w-0">
        <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" /></svg>
        <p class="text-blue-800 truncate">Menyalin: <strong id="paste-banner-name"></strong> — klik <strong>sel kosong</strong> untuk tempel. Esc untuk batal.</p>
    </div>
    <button type="button" onclick="clearClipboard()" class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors flex-shrink-0">
        Batal Salin
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse min-w-[600px]">
            <thead>
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600 border border-gray-200 w-[120px]">Waktu</th>
                    @foreach($laboratories as $lab)
                        <th class="bg-green-600 text-white text-center px-4 py-3 font-semibold border border-green-500">
                            <div class="text-sm">{{ $lab->code }}</div>
                            <div class="text-[10px] font-normal opacity-90 mt-0.5">{{ $lab->name }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($days as $dayKey => $dayLabel)
                    @php
                        $dayColors = [
                            'Monday' => 'bg-blue-50 text-blue-800',
                            'Tuesday' => 'bg-emerald-50 text-emerald-800',
                            'Wednesday' => 'bg-orange-50 text-orange-800',
                            'Thursday' => 'bg-amber-50 text-amber-800',
                            'Friday' => 'bg-sky-50 text-sky-800',
                        ];
                        $dayClass = $dayColors[$dayKey] ?? 'bg-gray-50 text-gray-800';
                    @endphp
                    <tr data-day-row="{{ $dayKey }}">
                        <td colspan="{{ $laboratories->count() + 1 }}" class="{{ $dayClass }} px-4 py-2 font-bold text-sm border border-gray-200">
                            {{ $dayLabel }}
                        </td>
                    </tr>
                    @foreach($timeSlots as $slot)
                        @if($slot['is_break'] ?? false)
                            <tr data-day-row="{{ $dayKey }}" class="bg-amber-50/50">
                                <td class="px-4 py-2.5 font-semibold text-amber-700 text-xs border border-gray-200">{{ $slot['label'] }}</td>
                                <td colspan="{{ $laboratories->count() }}" class="text-center text-amber-600 text-xs font-medium italic border border-gray-200">
                                    <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    ISTIRAHAT
                                </td>
                            </tr>
                        @else
                            <tr data-day-row="{{ $dayKey }}">
                                <td class="px-4 py-2.5 font-semibold text-gray-700 text-xs border border-gray-200 bg-gray-50/50">{{ $slot['label'] }}</td>
                                @foreach($laboratories as $lab)
                                    @php
                                        $cellKey = $dayKey . '_' . $slot['start'] . '_' . $lab->id;
                                        $schedule = $schedules->get($cellKey);
                                    @endphp
                                    @if($schedule)
                                        <td class="group border border-gray-200 bg-green-50/40 text-left p-2.5 align-top cursor-grab active:cursor-grabbing" id="cell-{{ $cellKey }}"
                                            draggable="true"
                                            ondragstart="handleDragStart(event, '{{ $schedule->id }}')"
                                            ondragend="dragReset()"
                                            data-source-id="{{ $schedule->id }}"
                                            data-label="{{ $schedule->course_name }}"
                                            data-day="{{ $dayKey }}"
                                            data-start="{{ $slot['start'] }}"
                                            data-end="{{ $slot['end'] }}"
                                            data-lab-id="{{ $lab->id }}"
                                            data-lab-name="{{ $lab->name }}"
                                            data-lab-code="{{ $lab->code }}">
                                            <div class="space-y-0.5">
                                                <p class="font-bold text-blue-800 text-xs leading-tight">{{ $schedule->course_name }}</p>
                                                <p class="text-emerald-700 text-[10px] font-medium">{{ $schedule->study_program }}</p>
                                                @if($schedule->semester || $schedule->class_group)
                                                    <p class="text-gray-500 text-[10px]">
                                                        @if($schedule->semester)Sem {{ $schedule->semester }}@endif
                                                        @if($schedule->semester && $schedule->class_group) &middot; @endif
                                                        @if($schedule->class_group)Kelas {{ $schedule->class_group }}@endif
                                                    </p>
                                                @endif
                                                @if($schedule->instructor)
                                                    <p class="text-gray-400 text-[10px] truncate" title="{{ $schedule->instructor }}">{{ $schedule->instructor }}</p>
                                                @endif
                                            </div>
                                            <div class="flex gap-1 mt-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button type="button" onclick="editSchedule('{{ $schedule->id }}')" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-500 text-white text-[10px] font-medium rounded hover:bg-blue-600 transition-colors">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                                    Edit
                                                </button>
                                                <button type="button"
                                                    class="js-copy-schedule inline-flex items-center gap-1 px-2 py-0.5 bg-purple-500 text-white text-[10px] font-medium rounded hover:bg-purple-600 transition-colors"
                                                    title="Salin jadwal ini"
                                                    data-course-name="{{ $schedule->course_name }}"
                                                    data-study-program="{{ $schedule->study_program }}"
                                                    data-semester="{{ $schedule->semester ?? '' }}"
                                                    data-instructor="{{ $schedule->instructor ?? '' }}"
                                                    data-class-group="{{ $schedule->class_group ?? '' }}">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" /></svg>
                                                    Salin
                                                </button>
                                                <button type="button" onclick="deleteSchedule('{{ $schedule->id }}', @js($schedule->course_name.' ('.$schedule->time_label.')'))" class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-500 text-white text-[10px] font-medium rounded hover:bg-red-600 transition-colors">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    @else
                                        <td class="group border border-gray-200 hover:bg-green-50 cursor-pointer transition-colors text-center" id="cell-{{ $cellKey }}"
                                            onclick="openCellModal(this)"
                                            ondragover="handleDragOver(event)"
                                            ondragleave="handleDragLeave(event)"
                                            ondrop="handleDrop(event)"
                                            data-day="{{ $dayKey }}"
                                            data-start="{{ $slot['start'] }}"
                                            data-end="{{ $slot['end'] }}"
                                            data-lab-id="{{ $lab->id }}"
                                            data-lab-name="{{ $lab->name }}"
                                            data-lab-code="{{ $lab->code }}"
                                            data-slot-label="{{ $slot['label'] }}">
                                            <svg class="w-4 h-4 mx-auto text-gray-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Schedule Modal -->
<div id="schedule-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="schedule-form" onsubmit="submitSchedule(event)">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <input type="hidden" name="schedule_id" id="schedule-id" value="">
                <input type="hidden" name="day" id="modal-day" value="">
                <input type="hidden" name="start_time" id="modal-start-time" value="">
                <input type="hidden" name="end_time" id="modal-end-time" value="">
                <input type="hidden" name="laboratory_id" id="modal-lab-id" value="">

                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white" id="modal-title">Tambah Jadwal Baru</h3>
                    <p class="text-sm text-green-100 mt-0.5" id="modal-subtitle"></p>
                </div>

                <div class="bg-white px-6 py-5">
                    <div class="space-y-4">
                        <div>
                            <label for="course_name" class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah <span class="text-red-500">*</span></label>
                            <input type="text" name="course_name" id="course_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Masukkan nama mata kuliah">
                        </div>
                        <div>
                            <label for="study_program" class="block text-sm font-medium text-gray-700 mb-1">Program Studi <span class="text-red-500">*</span></label>
                            <input type="text" name="study_program" id="study_program" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Masukkan program studi">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="number" name="semester" id="semester" min="1" max="14" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Semester">
                            </div>
                            <div>
                                <label for="class_group" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                                <input type="text" name="class_group" id="class_group" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Contoh: A, B, C">
                            </div>
                        </div>
                        <div>
                            <label for="instructor" class="block text-sm font-medium text-gray-700 mb-1">Dosen Pengampu</label>
                            <input type="text" name="instructor" id="instructor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Masukkan nama dosen">
                        </div>
                    </div>

                    <div id="conflict-warning" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Konflik Jadwal Terdeteksi</p>
                                <p class="text-sm text-red-700 mt-1" id="conflict-message"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="button" onclick="deleteScheduleFromModal()" id="btn-delete-modal" class="hidden px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Hapus</button>
                    <button type="submit" id="btn-submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="closeImportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            <form id="import-form" onsubmit="submitImport(event)">
                @csrf
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">Import Jadwal dari Excel</h3>
                    <p class="text-sm text-green-100 mt-0.5">Unggah file Excel untuk mengimpor jadwal laboratorium</p>
                </div>

                <div class="bg-white px-6 py-5">
                    <div class="space-y-4">
                        <div>
                            <label for="import-file" class="block text-sm font-medium text-gray-700 mb-1">Pilih File Excel</label>
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-green-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"/></svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="import-file" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500">
                                            <span>Upload file</span>
                                            <input id="import-file" name="file" type="file" accept=".xlsx,.xls,.csv" class="sr-only" required onchange="handleFileSelect(this)">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">XLSX, XLS, CSV (Maks. 10MB)</p>
                                    <p id="file-name-display" class="text-sm text-green-600 font-medium hidden"></p>
                                </div>
                            </div>
                        </div>

                        <div id="import-error" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700" id="import-error-message"></p>
                        </div>
                        <div id="import-success" class="hidden p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-700" id="import-success-message"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                    <button type="submit" id="btn-import-submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full">
            <div class="px-6 pt-6 pb-4 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </div>
                <h3 class="mt-3 text-lg font-bold text-gray-900">Hapus Jadwal?</h3>
                <p class="mt-1 text-sm text-gray-500">Jadwal <span id="delete-label" class="font-semibold text-gray-800"></span> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                <button type="button" id="btn-confirm-delete" onclick="confirmDelete()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Swap/Move Confirm Modal -->
<div id="swap-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" onclick="closeSwapModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full">
            <div class="px-6 pt-6 pb-4 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                </div>
                <h3 class="mt-3 text-lg font-bold text-gray-900" id="swap-title">Tukar Slot Jadwal?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <span id="swap-source-label" class="font-semibold text-gray-800"></span>
                    <span id="swap-verb">ditukar dengan</span>
                    <span id="swap-target-label" class="font-semibold text-gray-800"></span>.
                    <span class="block mt-1">Lanjutkan?</span>
                </p>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="closeSwapModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                <button type="button" id="btn-confirm-swap" onclick="confirmSwap()" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

@endsection

@push('scripts')
<style>
    .day-tab { background-color: #f3f4f6; color: #4b5563; }
    .day-tab.active { background-color: #16a34a; color: white; }
    .day-tab:not(.active):hover { background-color: #e5e7eb; }

    /* Drag & drop jadwal */
    td[draggable="true"]:hover { box-shadow: inset 0 0 0 2px #86efac; }
    td.dragging { opacity: 0.45; }
    td.drop-target { background-color: #dcfce7 !important; box-shadow: inset 0 0 0 2px #16a34a; }

    /* Mode salin/tempel: highlight sel kosong yang bisa dituju */
    body.paste-active td[data-lab-id]:not([draggable="true"]) {
        background-color: #eff6ff !important;
        box-shadow: inset 0 0 0 2px #3b82f6;
        cursor: copy;
    }
    body.paste-active td[data-lab-id]:not([draggable="true"]):hover {
        background-color: #dbeafe !important;
        box-shadow: inset 0 0 0 2px #2563eb;
    }
</style>
<script>
    const BASE_URL = '{{ route("lab-schedules.index") }}';

    const dayLabels = { 'Monday': 'SENIN', 'Tuesday': 'SELASA', 'Wednesday': 'RABU', 'Thursday': 'KAMIS', 'Friday': 'JUMAT' };
    const dayLabelsReverse = { 'SENIN': 'Monday', 'SELASA': 'Tuesday', 'RABU': 'Wednesday', 'KAMIS': 'Thursday', 'JUMAT': 'Friday' };

    let isEditMode = false;
    let currentScheduleId = null;

    const CLIPBOARD_KEY = 'labScheduleClipboard';

    function getClipboard() {
        try {
            const raw = sessionStorage.getItem(CLIPBOARD_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function setClipboard(data) {
        scheduleClipboard = data;
        try {
            if (data) sessionStorage.setItem(CLIPBOARD_KEY, JSON.stringify(data));
            else sessionStorage.removeItem(CLIPBOARD_KEY);
        } catch (e) {}
        updatePasteBanner();
    }

    let scheduleClipboard = null;

    function updatePasteBanner() {
        const banner = document.getElementById('paste-banner');
        if (!banner) return;
        if (scheduleClipboard && scheduleClipboard.course_name) {
            document.getElementById('paste-banner-name').textContent = scheduleClipboard.course_name;
            banner.classList.remove('hidden');
            banner.classList.add('flex');
            document.body.classList.add('paste-active');
        } else {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
            document.body.classList.remove('paste-active');
        }
    }

    function clearClipboard() {
        setClipboard(null);
        showToast('info', 'Mode salin dibatalkan');
    }

    function copySchedule(btn) {
        setClipboard({
            course_name: btn.dataset.courseName || '',
            study_program: btn.dataset.studyProgram || '',
            semester: btn.dataset.semester || '',
            instructor: btn.dataset.instructor || '',
            class_group: btn.dataset.classGroup || '',
        });
        showToast('success', 'Tersalin — klik sel kosong untuk tempel');
    }

    function filterDay(day) {
        document.querySelectorAll('.day-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('[data-day-filter="' + day + '"]').classList.add('active');
        document.querySelectorAll('[data-day-row]').forEach(row => {
            row.style.display = (day === 'all' || row.getAttribute('data-day-row') === day) ? '' : 'none';
        });
    }

    function openCellModal(td) {
        isEditMode = false;
        currentScheduleId = null;
        const day = td.getAttribute('data-day');
        const startTime = td.getAttribute('data-start');
        const endTime = td.getAttribute('data-end');
        const labId = td.getAttribute('data-lab-id');
        const labName = td.getAttribute('data-lab-name');
        const labCode = td.getAttribute('data-lab-code');
        const dayLabel = dayLabels[day] || day;

        document.getElementById('modal-day').value = day;
        document.getElementById('modal-start-time').value = startTime;
        document.getElementById('modal-end-time').value = endTime;
        document.getElementById('modal-lab-id').value = labId;
        document.getElementById('schedule-id').value = '';

        const clip = scheduleClipboard;
        const slotInfo = dayLabel + ' | ' + startTime + ' - ' + endTime + ' | ' + labCode + ' - ' + labName;

        if (clip && clip.course_name) {
            document.getElementById('modal-title').textContent = 'Tempel Jadwal';
            document.getElementById('modal-subtitle').textContent = slotInfo;
            document.getElementById('course_name').value = clip.course_name || '';
            document.getElementById('study_program').value = clip.study_program || '';
            document.getElementById('semester').value = clip.semester || '';
            document.getElementById('instructor').value = clip.instructor || '';
            document.getElementById('class_group').value = clip.class_group || '';
        } else {
            document.getElementById('modal-title').textContent = 'Tambah Jadwal Baru';
            document.getElementById('modal-subtitle').textContent = slotInfo;
            document.getElementById('course_name').value = '';
            document.getElementById('study_program').value = '';
            document.getElementById('semester').value = '';
            document.getElementById('instructor').value = '';
            document.getElementById('class_group').value = '';
        }

        document.getElementById('conflict-warning').classList.add('hidden');
        document.getElementById('btn-delete-modal').classList.add('hidden');
        document.getElementById('form-method').value = 'POST';
        document.getElementById('btn-submit').textContent = 'Simpan Jadwal';
        document.getElementById('schedule-modal').classList.remove('hidden');
    }

    function editSchedule(id) {
        isEditMode = true;
        currentScheduleId = id;
        document.getElementById('conflict-warning').classList.add('hidden');
        document.getElementById('btn-delete-modal').classList.remove('hidden');
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('schedule-id').value = id;
        document.getElementById('modal-title').textContent = 'Edit Jadwal';
        document.getElementById('btn-submit').textContent = 'Update Jadwal';

        fetch(BASE_URL + '/' + id, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(response => { if (!response.ok) throw new Error('Gagal mengambil data jadwal'); return response.json(); })
        .then(data => {
            const s = data.schedule || data;
            document.getElementById('modal-day').value = s.day;
            document.getElementById('modal-start-time').value = s.start_time;
            document.getElementById('modal-end-time').value = s.end_time;
            document.getElementById('modal-lab-id').value = s.laboratory_id;
            document.getElementById('modal-subtitle').textContent = (dayLabels[s.day] || s.day) + ' | ' + s.start_time + ' - ' + s.end_time;
            document.getElementById('course_name').value = s.course_name || '';
            document.getElementById('study_program').value = s.study_program || '';
            document.getElementById('semester').value = s.semester || '';
            document.getElementById('instructor').value = s.instructor || '';
            document.getElementById('class_group').value = s.class_group || '';
            document.getElementById('schedule-modal').classList.remove('hidden');
        })
        .catch(error => { showToast('error', 'Gagal memuat data: ' + error.message); });
    }

    function submitSchedule(e) {
        e.preventDefault();
        const form = document.getElementById('schedule-form');
        const formData = new FormData(form);
        const btnSubmit = document.getElementById('btn-submit');
        const originalText = btnSubmit.textContent;
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Menyimpan...';

        let url = BASE_URL;
        // Selalu kirim sebagai POST: PHP tidak mem-parsing multipart/form-data
        // untuk method PUT asli, sehingga field akan terbaca kosong saat validasi.
        // Laravel membaca _method=PUT dari body (method spoofing) sebagai PUT.
        let method = 'POST';
        if (isEditMode && currentScheduleId) {
            url = BASE_URL + '/' + currentScheduleId;
            formData.append('_method', 'PUT');
        }

        fetch(url, { method, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: formData })
        .then(response => response.json().then(data => { if (!response.ok) return { success: false, errors: data.errors, message: data.message, ...data }; return data; }))
        .then(data => {
            if (data.success === false && data.conflict) {
                document.getElementById('conflict-message').textContent = data.message || 'Terjadi konflik jadwal pada slot waktu tersebut';
                document.getElementById('conflict-warning').classList.remove('hidden');
                return;
            }
            if (data.success === false && data.errors) {
                const firstError = Object.values(data.errors)[0];
                showToast('error', Array.isArray(firstError) ? firstError[0] : firstError);
                return;
            }
            if (data.success) {
                showToast('success', data.message || 'Jadwal berhasil disimpan');
                closeModal();
                setTimeout(() => location.reload(), 800);
            } else {
                showToast('error', data.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => { showToast('error', 'Terjadi kesalahan jaringan: ' + error.message); })
        .finally(() => { btnSubmit.disabled = false; btnSubmit.textContent = originalText; });
    }

    let pendingDeleteId = null;

    function deleteSchedule(id, label) {
        openDeleteModal(id, label);
    }

    function deleteScheduleFromModal() {
        if (!currentScheduleId) return;
        const id = currentScheduleId;
        const label = document.getElementById('course_name').value;
        const start = document.getElementById('modal-start-time').value;
        const end = document.getElementById('modal-end-time').value;
        closeModal();
        openDeleteModal(id, [label, start && end ? start + ' - ' + end : ''].filter(Boolean).join(' | '));
    }

    function openDeleteModal(id, label) {
        pendingDeleteId = id;
        document.getElementById('delete-label').textContent = label || 'ini';
        const btn = document.getElementById('btn-confirm-delete');
        btn.disabled = false;
        btn.textContent = 'Ya, Hapus';
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        pendingDeleteId = null;
    }

    function confirmDelete() {
        if (!pendingDeleteId) return;
        const btn = document.getElementById('btn-confirm-delete');
        btn.disabled = true;
        btn.textContent = 'Menghapus...';

        fetch(BASE_URL + '/' + pendingDeleteId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(response => response.json())
        .then(data => {
            closeDeleteModal();
            if (data.success) { showToast('success', data.message || 'Jadwal berhasil dihapus'); setTimeout(() => location.reload(), 800); }
            else { showToast('error', data.message || 'Gagal menghapus jadwal'); }
        })
        .catch(error => { closeDeleteModal(); showToast('error', 'Terjadi kesalahan: ' + error.message); })
        .finally(() => { btn.disabled = false; btn.textContent = 'Ya, Hapus'; });
    }

    function closeModal() {
        document.getElementById('schedule-modal').classList.add('hidden');
        isEditMode = false;
        currentScheduleId = null;
    }

    function openImportModal() {
        document.getElementById('import-form').reset();
        document.getElementById('file-name-display').classList.add('hidden');
        document.getElementById('file-name-display').textContent = '';
        document.getElementById('import-error').classList.add('hidden');
        document.getElementById('import-success').classList.add('hidden');
        document.getElementById('import-modal').classList.remove('hidden');
    }

    function closeImportModal() { document.getElementById('import-modal').classList.add('hidden'); }

    function handleFileSelect(input) {
        const d = document.getElementById('file-name-display');
        if (input.files && input.files[0]) { d.textContent = input.files[0].name; d.classList.remove('hidden'); }
        else { d.classList.add('hidden'); }
    }

    function submitImport(e) {
        e.preventDefault();
        const formData = new FormData(document.getElementById('import-form'));
        const btnImport = document.getElementById('btn-import-submit');
        const originalHTML = btnImport.innerHTML;
        btnImport.disabled = true;
        btnImport.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Mengimport...';
        document.getElementById('import-error').classList.add('hidden');
        document.getElementById('import-success').classList.add('hidden');

        fetch(BASE_URL + '/import', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('import-success-message').textContent = data.message || 'Import berhasil';
                document.getElementById('import-success').classList.remove('hidden');
                showToast('success', data.message || 'Import berhasil');
                setTimeout(() => location.reload(), 1500);
            } else {
                document.getElementById('import-error-message').textContent = data.message || 'Import gagal';
                document.getElementById('import-error').classList.remove('hidden');
            }
        })
        .catch(error => {
            document.getElementById('import-error-message').textContent = 'Terjadi kesalahan: ' + error.message;
            document.getElementById('import-error').classList.remove('hidden');
        })
        .finally(() => { btnImport.disabled = false; btnImport.innerHTML = originalHTML; });
    }

    // ---------- Drag & Drop: pindah / tukar slot ----------
    let dragSource = null;
    let dropTarget = null;
    let pendingDrop = null;

    function handleDragStart(event, id) {
        const td = event.currentTarget;
        dragSource = {
            id: id,
            label: td.getAttribute('data-label'),
            day: td.getAttribute('data-day'),
            start: td.getAttribute('data-start'),
            end: td.getAttribute('data-end'),
            labId: td.getAttribute('data-lab-id'),
            labName: td.getAttribute('data-lab-name')
        };
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            try { event.dataTransfer.setData('text/plain', String(id)); } catch (e) {}
        }
        td.classList.add('dragging');
    }

    function handleDragOver(event) {
        if (!dragSource) return;
        const td = event.currentTarget;
        if (td.getAttribute('data-source-id') === String(dragSource.id)) return;
        event.preventDefault();
        if (event.dataTransfer) event.dataTransfer.dropEffect = 'move';
        if (dropTarget && dropTarget !== td) dropTarget.classList.remove('drop-target');
        dropTarget = td;
        td.classList.add('drop-target');
    }

    function handleDragLeave(event) {
        if (event.currentTarget === dropTarget) {
            dropTarget.classList.remove('drop-target');
            dropTarget = null;
        }
    }

    function handleDrop(event) {
        event.preventDefault();
        const td = event.currentTarget;
        td.classList.remove('drop-target');
        if (!dragSource || td.getAttribute('data-source-id') === String(dragSource.id)) {
            dragReset();
            return;
        }
        const target = {
            id: td.getAttribute('data-source-id') || null,
            label: td.getAttribute('data-label') || td.getAttribute('data-slot-label'),
            day: td.getAttribute('data-day'),
            start: td.getAttribute('data-start'),
            end: td.getAttribute('data-end'),
            labId: td.getAttribute('data-lab-id'),
            labName: td.getAttribute('data-lab-name')
        };
        openSwapModal(dragSource, target);
    }

    function dragReset() {
        document.querySelectorAll('.dragging').forEach(el => el.classList.remove('dragging'));
        document.querySelectorAll('.drop-target').forEach(el => el.classList.remove('drop-target'));
        dragSource = null;
        dropTarget = null;
    }

    function slotText(item) {
        return (dayLabels[item.day] || item.day) + ' ' + item.start + ' - ' + item.end + ' di ' + item.labName;
    }

    function openSwapModal(source, target) {
        pendingDrop = { source: source, target: target };
        document.getElementById('swap-source-label').textContent = source.label + ' (' + slotText(source) + ')';

        if (target.id) {
            document.getElementById('swap-title').textContent = 'Tukar Slot Jadwal?';
            document.getElementById('swap-verb').textContent = 'ditukar dengan';
            document.getElementById('swap-target-label').textContent = target.label + ' (' + slotText(target) + ')';
        } else {
            document.getElementById('swap-title').textContent = 'Pindahkan Jadwal?';
            document.getElementById('swap-verb').textContent = 'dipindahkan ke';
            document.getElementById('swap-target-label').textContent = slotText(target);
        }

        const btn = document.getElementById('btn-confirm-swap');
        btn.disabled = false;
        btn.textContent = 'Ya, Lanjutkan';
        document.getElementById('swap-modal').classList.remove('hidden');
    }

    function closeSwapModal() {
        document.getElementById('swap-modal').classList.add('hidden');
        pendingDrop = null;
        dragReset();
    }

    function confirmSwap() {
        if (!pendingDrop) return;
        const { source, target } = pendingDrop;
        const btn = document.getElementById('btn-confirm-swap');
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        const formData = new FormData();
        formData.append('laboratory_id', target.labId);
        formData.append('day', target.day);
        formData.append('start_time', target.start);
        formData.append('end_time', target.end);
        if (target.id) formData.append('target_id', target.id);

        fetch(BASE_URL + '/' + source.id + '/move', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
            closeSwapModal();
            if (!ok) {
                showToast('error', data.message || 'Gagal memproses perubahan jadwal');
                return;
            }
            showToast('success', data.message || 'Jadwal berhasil diperbarui');
            setTimeout(() => location.reload(), 800);
        })
        .catch(error => {
            closeSwapModal();
            showToast('error', 'Terjadi kesalahan: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Ya, Lanjutkan';
        });
    }

    function showToast(type, message) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'flex items-center w-full max-w-xs p-4 rounded-lg shadow-lg text-sm font-medium transition-all transform translate-x-full opacity-0';
        if (type === 'success') { toast.classList.add('bg-green-500', 'text-white'); toast.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' + message; }
        else if (type === 'error') { toast.classList.add('bg-red-500', 'text-white'); toast.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' + message; }
        else { toast.classList.add('bg-blue-500', 'text-white'); toast.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' + message; }
        container.appendChild(toast);
        setTimeout(() => { toast.classList.remove('translate-x-full', 'opacity-0'); toast.classList.add('translate-x-0', 'opacity-100'); }, 50);
        setTimeout(() => { toast.classList.remove('translate-x-0', 'opacity-100'); toast.classList.add('translate-x-full', 'opacity-0'); setTimeout(() => toast.remove(), 300); }, 4000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        filterDay('all');
        dragReset();
        document.getElementById('schedule-modal').addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
        document.getElementById('import-modal').addEventListener('keydown', e => { if (e.key === 'Escape') closeImportModal(); });
        document.getElementById('delete-modal').addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
        document.getElementById('swap-modal').addEventListener('keydown', e => { if (e.key === 'Escape') closeSwapModal(); });

        document.querySelectorAll('.js-copy-schedule').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                copySchedule(this);
            });
        });

        // Pulihkan clipboard setelah reload (tempel ke banyak slot)
        scheduleClipboard = getClipboard();
        updatePasteBanner();

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            const modalOpen = !document.getElementById('schedule-modal').classList.contains('hidden')
                || !document.getElementById('import-modal').classList.contains('hidden')
                || !document.getElementById('delete-modal').classList.contains('hidden')
                || !document.getElementById('swap-modal').classList.contains('hidden');
            if (!modalOpen && scheduleClipboard) {
                clearClipboard();
            }
        });
    });
</script>
@endpush
