<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AkademikJadwalApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function user(): User
    {
        return User::create([
            'name' => 'Mahasiswa',
            'email' => 'mhs@akademik.test',
            'password' => 'rahasia123',
        ]);
    }

    private function apiPayload(): array
    {
        return [
            'success' => true,
            'message' => 'Berhasil mengambil data penggunaan laboratorium',
            'data' => [
                [
                    'tanggal' => '2026-10-12', 'jam' => '10:00', 'kode' => 'TIN2001',
                    'nama' => 'JARINGAN KOMPUTER', 'pengguna' => 'Dr. BUDI, S.T., M.T.',
                    'rel' => 0, 'jenis' => 'Skill Lab', 'kls' => 'B1',
                    'ruang' => 'SB.1.02 / Ruang Laboratorium Komputer 2 (52)',
                    'kdruang' => 1336, 'stat' => 'P', 'terisi' => true, 'keterangan' => null,
                ],
                [
                    'tanggal' => '2026-10-12', 'jam' => '16:00', 'kode' => 'TIO3012',
                    'nama' => 'FORENSIKA DIGITAL', 'pengguna' => 'Dr. ARIZONA, S.Kom., M.Kom.',
                    'rel' => 0, 'jenis' => 'Skill Lab', 'kls' => 'A2',
                    'ruang' => 'SM.1.20 / Ruang Laboratorium Komputer 3 (52)',
                    'kdruang' => 1421, 'stat' => 'P', 'terisi' => true, 'keterangan' => null,
                ],
                [
                    'tanggal' => '2026-10-12', 'jam' => '17:00', 'kode' => 'TIO3012',
                    'nama' => 'FORENSIKA DIGITAL', 'pengguna' => 'Dr. ARIZONA, S.Kom., M.Kom.',
                    'rel' => 0, 'jenis' => 'Skill Lab', 'kls' => 'A2',
                    'ruang' => 'SM.1.20 / Ruang Laboratorium Komputer 3 (52)',
                    'kdruang' => 1421, 'stat' => 'P', 'terisi' => true, 'keterangan' => null,
                ],
                [
                    'tanggal' => '2026-10-12', 'jam' => '08:00', 'kode' => '-',
                    'nama' => 'SLOT-KOSONG-TIDAK-HARUS-TAMPIL', 'pengguna' => '-',
                    'rel' => '-', 'jenis' => '-', 'kls' => '-',
                    'ruang' => 'SM.1.20 / Ruang Laboratorium Komputer 3 (52)',
                    'kdruang' => 1421, 'stat' => 'P', 'terisi' => false, 'keterangan' => null,
                ],
            ],
        ];
    }

    /**
     * Fake API: payload yang tanggalnya mengikuti parameter tanggal request,
     * sehingga setiap hari dalam minggu mendapat data yang benar.
     */
    private function fakeApi(): void
    {
        Http::fake(function ($request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $tanggal = $query['tanggal'] ?? now()->toDateString();

            $payload = $this->apiPayload();
            foreach ($payload['data'] as &$row) {
                $row['tanggal'] = $tanggal;
            }
            unset($row);

            return Http::response($payload);
        });
    }

    public function test_guest_is_redirected_to_login_on_admin_page(): void
    {
        $this->get(route('akademik.jadwal'))
            ->assertRedirect(route('login'));
    }

    public function test_page_is_public_for_guests(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->get(route('jadwal.index'))
            ->assertOk()
            ->assertSee('Jadwal Perkuliahan Laboratorium')
            ->assertSee('Klik jadwal untuk detail')
            ->assertSee('Kembali ke Denah')
            ->assertSee(url('/#denah'), false)
            ->assertSee('16:00–18:00');
    }

    public function test_prev_week_is_hidden_on_current_week(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->get(route('jadwal.index'))
            ->assertOk()
            ->assertDontSee('Minggu sebelumnya', false)
            ->assertDontSee('tanggal=2026-10-05', false);
    }

    public function test_past_week_is_hidden_and_skips_api(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        // Senin 21 Sep 2026 → minggu lampau: tampil kosong, API tidak dipanggil.
        $this->get(route('jadwal.index', ['tanggal' => '2026-09-21']))
            ->assertOk()
            ->assertSee('Minggu sudah berlalu')
            ->assertDontSee('16:00–18:00')
            ->assertDontSee('Minggu sebelumnya', false);

        Http::assertNothingSent();
    }

    public function test_weekly_grid_shows_filled_schedule_and_hides_empty_slots(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('12 – 18 Oktober 2026')
            ->assertSee('FORENSIKA DIGITAL')
            ->assertSee('JARINGAN KOMPUTER')
            ->assertSee('Dr. ARIZONA, S.Kom., M.Kom.')
            ->assertDontSee('SLOT-KOSONG-TIDAK-HARUS-TAMPIL')
            ->assertSee('Jadwal Perkuliahan');
    }

    public function test_consecutive_slots_are_merged_into_time_range(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        // Slot jam 16:00 + 17:00 milik matkul yang sama digabung → 16:00–18:00
        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('16:00–18:00')
            ->assertDontSee('17:00–18:00');
    }

    public function test_week_navigation_fetches_requested_week(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal', ['tanggal' => '2026-10-19']))
            ->assertOk()
            ->assertSee('19 – 25 Oktober 2026')
            ->assertSee('tanggal=2026-10-12', false)
            ->assertSee('tanggal=2026-10-26', false);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'tanggal=2026-10-19'));
    }

    public function test_cross_month_week_label_is_formatted(): void
    {
        Carbon::setTestNow('2026-09-30 09:00:00');
        $this->fakeApi();

        // Rabu 30 Sep 2026 → minggu Sen 28 Sep – Min 4 Okt 2026
        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('28 September – 4 Oktober 2026');
    }

    public function test_hours_are_grouped_into_slots_with_break(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('06.00 – 08.00')
            ->assertSee('10.00 – 12.00')
            ->assertSee('12.00 – 13.00')
            ->assertSee('ISTIRAHAT')
            ->assertSee('13.00 – 15.00')
            ->assertSee('19.00 – 21.00')
            ->assertDontSee('21.00 – 23.00')
            ->assertDontSee('07:00');
    }

    public function test_room_filter_apply(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal', ['kdruang' => 1421]))
            ->assertOk()
            ->assertSee('FORENSIKA DIGITAL')
            ->assertDontSee('JARINGAN KOMPUTER')
            ->assertSee('Filter ruang aktif');
    }

    public function test_invalid_date_is_rejected(): void
    {
        $this->actingAs($this->user())
            ->get(route('akademik.jadwal', ['tanggal' => 'bukan-tanggal']))
            ->assertSessionHasErrors('tanggal');
    }

    public function test_api_failure_shows_friendly_error(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        Http::fake([config('jadwal.api_url').'*' => Http::response('', 500)]);

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('Gagal mengambil data dari API jadwal')
            ->assertSee('Data tidak tersedia karena API tidak dapat dijangkau.');
    }

    public function test_detail_modal_markup_is_present(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('Detail Jadwal', false)
            ->assertSee('openDay', false)
            ->assertSee('openIdx', false)
            // JSON untuk Alpine harus lewat Js::from (JSON.parse), bukan @json mentah,
            // agar tanda kutipnya tidak memutus atribut x-data="...".
            ->assertSee('cal: JSON.parse(', false)
            ->assertDontSee('cal: {', false);
    }

    public function test_curl_example_block_is_shown(): void
    {
        Carbon::setTestNow('2026-10-12 09:00:00');
        $this->fakeApi();

        $this->actingAs($this->user())
            ->get(route('akademik.jadwal'))
            ->assertOk()
            ->assertSee('Contoh pemakaian API (curl)')
            ->assertSee(config('jadwal.api_url'), false)
            ->assertSee('curl -s "', false);
    }
}
