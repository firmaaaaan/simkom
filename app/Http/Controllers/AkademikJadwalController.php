<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AkademikJadwalController extends Controller
{
    private const DAY_NAMES = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu',
    ];

    private const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Susunan baris grid jam: 2 jam per baris, kecuali istirahat 12.00–13.00
     * dan jeda 15.00–16.00 / 18.00–19.00 (baris kosong). Slot 'break' ditandai
     * ISTIRAHAT di tampilan; baris lain yang kosong dibiarkan kosong.
     */
    private const GRID_SLOTS = [
        ['start' => 6, 'end' => 8],
        ['start' => 8, 'end' => 10],
        ['start' => 10, 'end' => 12],
        ['start' => 12, 'end' => 13, 'break' => true],
        ['start' => 13, 'end' => 15],
        ['start' => 15, 'end' => 16],
        ['start' => 16, 'end' => 18],
        ['start' => 18, 'end' => 19],
        ['start' => 19, 'end' => 21],
    ];

    /**
     * Halaman admin (login) Jadwal Laboratorium — layout aplikasi + sidebar.
     * Data dari API SIMPTT sumber 'lab'.
     */
    public function index(Request $request)
    {
        return view('akademik.jadwal', array_merge($this->buildData($request, 'lab'), [
            'pageTitle' => 'Jadwal Laboratorium',
            'pageSubtitle' => 'Kalender mingguan jadwal penggunaan laboratorium — diambil dari API SIMPTT',
            'pageRoute' => 'akademik.jadwal',
            'publicUrl' => route('jadwal.index'),
        ]));
    }

    /**
     * Halaman admin (login) Jadwal Kelas — UI sama dengan jadwal laboratorium,
     * data dari endpoint SIMPTT terpisah untuk jadwal kelas.
     */
    public function indexKelas(Request $request)
    {
        return view('akademik.jadwal', array_merge($this->buildData($request, 'kelas'), [
            'pageTitle' => 'Jadwal Kelas',
            'pageSubtitle' => 'Kalender mingguan jadwal kelas — diambil dari API SIMPTT',
            'pageRoute' => 'akademik.jadwal-kelas',
            'publicUrl' => null,
        ]));
    }

    /**
     * Halaman publik (tanpa login) Jadwal Laboratorium — layout mandiri
     * ala halaman publik lain. Data & perilaku sama dengan halaman admin.
     */
    public function publik(Request $request)
    {
        return view('public.jadwal', $this->buildData($request, 'lab'));
    }

    /**
     * Siapkan data kalender mingguan dari API eksternal SIMPTT — sumber
     * 'lab' (/api/v1/jadwal/lab) atau 'kelas' (/api/v1/jadwal/kelas), satu
     * request API per tanggal minggu tersebut (di-pool paralel + cache
     * terpisah per sumber). Minggu lampau disembunyikan: navigasi ‹ tidak
     * bisa mundur melewati minggu berjalan, dan minggu lampau yang dibuka
     * lewat URL manual tampil kosong tanpa memanggil API.
     *
     * @return array<string, mixed>
     */
    private function buildData(Request $request, string $source = 'lab'): array
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'kdruang' => 'nullable|integer',
        ]);

        $anchor = $validated['tanggal'] ?? now()->toDateString();
        $kdruang = isset($validated['kdruang']) ? (int) $validated['kdruang'] : null;
        $endpoint = $source === 'kelas'
            ? config('jadwal.kelas_api_url')
            : config('jadwal.api_url');

        $monday = Carbon::parse($anchor)->startOfWeek(Carbon::MONDAY);
        $sunday = $monday->copy()->addDays(6);
        $today = now()->toDateString();
        $currentMonday = now()->copy()->startOfWeek(Carbon::MONDAY);
        $isPastWeek = $monday->lt($currentMonday);

        $dates = [];
        $dayLabels = [];
        $days = [];
        $gridSlots = [];

        foreach (self::GRID_SLOTS as $slot) {
            $gridSlots[] = [
                'label' => sprintf('%02d.00', $slot['start']).' – '.sprintf('%02d.00', $slot['end']),
                'start' => $slot['start'],
                'end' => $slot['end'],
                'isBreak' => ! empty($slot['break']),
            ];
        }

        for ($i = 0; $i < 7; $i++) {
            $date = $monday->copy()->addDays($i);
            $iso = $date->format('l');
            $dates[] = $date->toDateString();
            $dayLabels[$date->toDateString()] = self::DAY_NAMES[$iso].', '
                .$date->format('j').' '.self::MONTH_NAMES[(int) $date->format('n')].' '.$date->format('Y');
            $days[] = [
                'date' => $date->toDateString(),
                'short' => mb_substr(self::DAY_NAMES[$iso], 0, 3),
                'num' => (int) $date->format('j'),
                'isToday' => $date->toDateString() === $today,
                'isWeekend' => $date->isWeekend(),
            ];
        }

        // Minggu lampau tidak diambil dari API — disembunyikan, tampil kosong.
        [$payloads, $error] = $isPastWeek ? [[], null] : $this->fetchDates($dates, $today, $endpoint, $source);

        // Susun entri per tanggal (slot terisi, difilter per ruang) + sel per slot 2 jam.
        $roomMap = [];
        $entriesByDay = [];
        $slotCells = [];
        $totalSlots = 0;

        foreach ($dates as $date) {
            $slots = collect($payloads[$date]['data'] ?? []);
            $totalSlots += $slots->count();

            foreach ($slots as $slot) {
                if (isset($slot['kdruang'], $slot['ruang'])) {
                    $roomMap[(int) $slot['kdruang']] = $slot['ruang'];
                }
            }

            $entries = $this->prepareEntries($slots
                ->filter(fn ($slot) => ! empty($slot['terisi']))
                ->when($kdruang !== null, fn ($collection) => $collection
                    ->filter(fn ($slot) => (int) ($slot['kdruang'] ?? 0) === $kdruang)));

            $entriesByDay[$date] = $entries;
            $seen = [];

            foreach ($entries as $index => $entry) {
                $slotIdx = $this->slotIndexForHour($entry['hour'], $gridSlots);

                // Satu jadwal yang slotnya mentok dua baris tidak boleh dobel.
                $key = implode('|', [$entry['jam'], $entry['kode'], $entry['ruang'], $entry['nama']]);

                if (isset($seen[$slotIdx][$key])) {
                    continue;
                }

                $seen[$slotIdx][$key] = true;
                $slotCells[$date][$slotIdx][] = ['idx' => $index, 'entry' => $entry];
            }
        }

        $rooms = collect($roomMap)
            ->map(fn ($ruang, $kdruangId) => ['kdruang' => $kdruangId, 'ruang' => $ruang])
            ->sortBy('ruang')
            ->values();

        $totalEntries = array_sum(array_map('count', $entriesByDay));

        // Label rentang minggu, mis. "12 – 18 Oktober 2026" atau
        // "28 September – 4 Oktober 2026" (lintas bulan).
        if ($monday->format('Y-m') === $sunday->format('Y-m')) {
            $weekLabel = $monday->format('j').' – '.$sunday->format('j').' '
                .self::MONTH_NAMES[(int) $sunday->format('n')].' '.$sunday->format('Y');
        } else {
            $weekLabel = $monday->format('j').' '.self::MONTH_NAMES[(int) $monday->format('n')]
                .($monday->format('Y') !== $sunday->format('Y') ? ' '.$monday->format('Y') : '')
                .' – '.$sunday->format('j').' '.self::MONTH_NAMES[(int) $sunday->format('n')]
                .' '.$sunday->format('Y');
        }

        $commonParams = array_filter(['kdruang' => $kdruang]);

        // Navigasi minggu mengikuti halaman yang sedang dibuka (admin/publik).
        $routeName = $request->route()?->getName() ?? 'jadwal.index';

        return [
            'tanggal' => $anchor,
            'weekLabel' => $weekLabel,
            'kdruang' => $kdruang,
            'endpoint' => $endpoint,
            'rooms' => $rooms,
            'days' => $days,
            'slots' => $gridSlots,
            'cells' => $slotCells,
            'entriesByDay' => $entriesByDay,
            'dayLabels' => $dayLabels,
            'totalEntries' => $totalEntries,
            'totalSlots' => $totalSlots,
            'isPastWeek' => $isPastWeek,
            // Minggu berjalan/mendatang boleh mundur; minggu lampau disembunyikan.
            'prevUrl' => $monday->gt($currentMonday)
                ? route($routeName, array_merge($commonParams, ['tanggal' => $monday->copy()->subDays(7)->toDateString()]))
                : null,
            'nextUrl' => route($routeName, array_merge($commonParams, ['tanggal' => $monday->copy()->addDays(7)->toDateString()])),
            'todayUrl' => route($routeName, $commonParams),
            'sampleDate' => $today >= $monday->toDateString() && $today <= $sunday->toDateString() ? $today : $monday->toDateString(),
            'error' => $error,
        ];
    }

    /**
     * Ambil payload API untuk daftar tanggal: cek cache per tanggal dulu,
     * sisanya di-pool paralel per batch. Cache disimpan terpisah per sumber
     * (lab/kelas) agar kedua halaman tidak saling bentrok. Tanggal lampau
     * di-cache lebih lama (data historis tidak berubah), tanggal
     * berjalan/hari depan pakai TTL pendek. Kegagalan di-negative-cache
     * singkat supaya halaman tidak menunggu timeout berulang saat API
     * sedang mati.
     *
     * @return array{0: array<string, array>, 1: string|null}
     */
    private function fetchDates(array $dates, string $today, string $endpoint, string $source): array
    {
        $payloads = [];
        $missing = [];
        $failed = 0;

        foreach ($dates as $date) {
            $cached = Cache::get($this->cacheKey($date, $source));

            if (is_array($cached)) {
                $payloads[$date] = $cached;
            } elseif (Cache::get($this->failKey($date, $source))) {
                // Baru saja gagal — jangan langsung request ulang.
                $failed++;
            } else {
                $missing[] = $date;
            }
        }

        $chunkSize = 7;
        $abort = false;

        foreach (array_chunk($missing, $chunkSize) as $chunk) {
            if ($abort) {
                foreach ($chunk as $date) {
                    $failed++;
                    $this->markFailed($date, $source);
                }

                continue;
            }

            try {
                $results = Http::pool(function ($pool) use ($chunk, $endpoint) {
                    foreach ($chunk as $date) {
                        $pool->timeout(config('jadwal.timeout'))
                            ->acceptJson()
                            ->get($endpoint, ['tanggal' => $date]);
                    }
                }, $chunkSize);
            } catch (\Throwable $e) {
                $results = [];
            }

            $chunkFailed = 0;

            foreach (array_values($chunk) as $i => $date) {
                $result = $results[$i] ?? null;
                $payload = null;

                if ($result instanceof ClientResponse && ! $result->failed()) {
                    $json = $result->json();
                    $payload = is_array($json) && is_array($json['data'] ?? null) ? $json : null;
                }

                if ($payload === null) {
                    $chunkFailed++;
                    $failed++;
                    $this->markFailed($date, $source);

                    continue;
                }

                $payloads[$date] = $payload;
                Cache::forget($this->failKey($date, $source));
                $ttl = $date < $today ? 86400 : (int) config('jadwal.cache_ttl');
                Cache::put($this->cacheKey($date, $source), $payload, $ttl);
            }

            // Seluruh batch gagal → kemungkinan host sedang mati;
            // hentikan batch berikutnya agar halaman tidak menunggu lama.
            $abort = $chunkFailed === count($chunk);
        }

        if ($payloads === []) {
            return [$payloads, 'Gagal mengambil data dari API jadwal. Pastikan server API dapat dijangkau lalu coba lagi.'];
        }

        if ($failed > 0) {
            return [$payloads, 'Sebagian hari gagal dimuat dari API ('.$failed.' tanggal). Data pada tanggal tersebut mungkin tidak lengkap.'];
        }

        return [$payloads, null];
    }

    /**
     * Normalisasi slot terisi menjadi entri per jam untuk grid mingguan.
     * Slot jam berurutan milik jadwal yang sama (matkul, dosen, ruang, kelas,
     * jenis identik) mendapat rentang tampil, mis. baris 16:00 & 17:00 sama-sama
     * menampilkan "16:00–18:00". Duplikat jam identik dibuang.
     *
     * @param  Collection<int, array>  $slots
     * @return array<int, array>
     */
    private function prepareEntries($slots): array
    {
        $sorted = $slots->sortBy(['jam', 'ruang'])->values()->all();

        $groupOf = [];
        $groups = [];
        $skip = [];
        $open = [];

        foreach ($sorted as $i => $slot) {
            $key = implode('|', [
                $slot['kode'] ?? '-',
                $slot['nama'] ?? '-',
                $slot['pengguna'] ?? '-',
                $slot['ruang'] ?? '-',
                $slot['kls'] ?? '-',
                $slot['jenis'] ?? '-',
            ]);
            $hour = (int) substr($slot['jam'] ?? '00:00', 0, 2);

            if (isset($open[$key])) {
                $gid = $open[$key];

                if ($hour === $groups[$gid]['last'] + 1) {
                    $groups[$gid]['last'] = $hour;
                    $groupOf[$i] = $gid;

                    continue;
                }

                if ($hour === $groups[$gid]['last']) {
                    $skip[$i] = true;

                    continue;
                }
            }

            $groups[] = ['first' => $hour, 'last' => $hour];
            $gid = count($groups) - 1;
            $open[$key] = $gid;
            $groupOf[$i] = $gid;
        }

        $entries = [];

        foreach ($sorted as $i => $slot) {
            if (isset($skip[$i])) {
                continue;
            }

            $group = $groups[$groupOf[$i]];

            $entries[] = [
                'hour' => (int) substr($slot['jam'], 0, 2),
                'jam' => sprintf('%02d:00', $group['first']).'–'.sprintf('%02d:00', $group['last'] + 1),
                'kode' => $slot['kode'] ?? '-',
                'nama' => $slot['nama'] ?? '-',
                'pengguna' => $slot['pengguna'] ?? '-',
                'kls' => $slot['kls'] ?? '-',
                'jenis' => $slot['jenis'] ?? '-',
                'ruang' => $slot['ruang'] ?? '-',
                'keterangan' => $slot['keterangan'] ?? null,
            ];
        }

        return $entries;
    }

    /**
     * Cari indeks baris grid yang memuat jam tertentu (rentang [start, end)).
     * Jam di luar rentang (sebelum 06.00 / sesudah 23.00) di-clamp ke baris
     * pertama/terakhir agar tetap aman bila API mengirim jam di luar dugaan.
     */
    private function slotIndexForHour(int $hour, array $gridSlots): int
    {
        foreach ($gridSlots as $idx => $slot) {
            if ($hour >= $slot['start'] && $hour < $slot['end']) {
                return $idx;
            }
        }

        return $hour < $gridSlots[0]['start'] ? 0 : count($gridSlots) - 1;
    }

    private function markFailed(string $date, string $source): void
    {
        Cache::put($this->failKey($date, $source), 1, 60);
    }

    private function failKey(string $date, string $source): string
    {
        return 'akademik-jadwal:'.$source.':fail:'.$date;
    }

    private function cacheKey(string $date, string $source): string
    {
        return 'akademik-jadwal:'.$source.':'.$date;
    }
}
