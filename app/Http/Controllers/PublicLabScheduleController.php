<?php

namespace App\Http\Controllers;

use App\Models\LabSchedule;
use App\Models\Laboratory;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicLabScheduleController extends Controller
{
    /**
     * Jadwal penggunaan laboratorium versi publik (tanpa login), read-only.
     * Tanpa ?day= → mode "Semua Hari". ?day=Monday..Friday → satu hari.
     * Bisa disaring per lab lewat ?laboratory_id=.
     */
    public function index(Request $request)
    {
        $days = LabSchedule::days();

        $day = $request->query('day');
        if ($day !== null && $day !== '' && ! array_key_exists($day, $days)) {
            abort(404);
        }

        $allDays = ($day === null || $day === '');
        $today = now()->format('l');
        $isToday = ! $allDays && $day === $today;
        $nowTime = now()->format('H:i');

        $laboratories = Laboratory::where('status', 'Aktif')
            ->orderBy('name')
            ->get();

        $selectedLab = null;
        $labFilter = $request->query('laboratory_id');
        if ($labFilter !== null && $labFilter !== '') {
            $selectedLab = $laboratories->firstWhere('id', (int) $labFilter);
        }

        $query = LabSchedule::with('laboratory');
        if (! $allDays) {
            $query->where('day', $day);
        }
        if ($selectedLab) {
            $query->where('laboratory_id', $selectedLab->id);
        }

        // Kunci sel: day_start(H:i)_lab — cocok dengan loop slot di blade.
        $schedules = $query->get()
            ->groupBy(fn ($item) => $item->day . '_' . substr($item->start_time, 0, 5) . '_' . $item->laboratory_id)
            ->map(fn ($group) => $group->first());

        // Highlight slot yang sedang berlangsung: hanya pada hari ini,
        // dan jam sekarang berada di dalam rentang slot.
        $timeSlots = array_map(function ($slot) use ($isToday, $nowTime) {
            $slot['is_now'] = $isToday && $nowTime >= $slot['start'] && $nowTime < $slot['end'];

            return $slot;
        }, LabSchedule::timeSlots());

        // URL jadwal real-time (diatur admin lewat Settings); null = tautan disembunyikan.
        $realtimeUrl = Setting::realtimeScheduleUrl();

        return view('public.lab-schedules', compact(
            'laboratories',
            'selectedLab',
            'schedules',
            'days',
            'day',
            'allDays',
            'today',
            'isToday',
            'timeSlots',
            'realtimeUrl'
        ));
    }
}
