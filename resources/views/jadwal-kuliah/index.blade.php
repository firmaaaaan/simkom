@extends('layouts.public')

@section('title', 'Jadwal Kuliah')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-primary-600 px-6 py-5">
            <h1 class="text-xl font-bold text-white">Jadwal Kuliah</h1>
            <p class="text-primary-100 text-sm mt-1">Jadwal kuliah dan non-kuliah laboratorium</p>
        </div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <label for="laboratorium_id" class="block text-sm font-medium text-slate-700 mb-1">Filter Laboratorium</label>
                    <select id="laboratorium_id" class="w-full sm:w-64 rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500 px-3 py-2 border">
                        <option value="">Semua Laboratorium</option>
                        @foreach($laboratoriums as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->nama_laboratorium }} ({{ $lab->kode_laboratorium }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-sm text-slate-500">
                    <span id="last-sync-info"></span>
                </div>
            </div>
            <div id="calendar"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var laboratoriumSelect = document.getElementById('laboratorium_id');

        function getEventsUrl() {
            var url = '{{ route("jadwal-kuliah.api.fetch") }}';
            var laboratoriumId = laboratoriumSelect.value;

            if (laboratoriumId) {
                url += '?laboratorium_id=' + laboratoriumId;
            }

            return url;
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: getEventsUrl(),
            loading: function(isLoading) {
                if (isLoading) {
                    calendarEl.style.opacity = '0.5';
                } else {
                    calendarEl.style.opacity = '1';
                }
            },
            eventClick: function(info) {
                if (info.event.url) {
                    window.open(info.event.url, '_blank');
                }
            }
        });

        calendar.render();

        if (laboratoriumSelect) {
            laboratoriumSelect.addEventListener('change', function() {
                calendar.removeAllEvents();
                calendar.addEventSource(getEventsUrl());
            });
        }
    });
</script>
@endpush