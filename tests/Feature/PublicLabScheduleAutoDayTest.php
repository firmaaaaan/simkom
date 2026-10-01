<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\LabSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLabScheduleAutoDayTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function lab(): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab Auto',
            'code' => 'LA1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);
    }

    private function schedule(Laboratory $lab, string $day, string $course): LabSchedule
    {
        return LabSchedule::create([
            'laboratory_id' => $lab->id,
            'day' => $day,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'course_name' => $course,
            'study_program' => 'Teknik Informatika',
            'semester' => '5',
            'instructor' => 'Budi',
            'class_group' => 'A',
            'created_by' => User::factory()->create()->id,
        ]);
    }

    public function test_default_view_shows_only_today_on_weekday(): void
    {
        Carbon::setTestNow('2026-09-30 10:00:00'); // Rabu.
        $lab = $this->lab();
        $this->schedule($lab, 'Wednesday', 'Kuliah Rabu');
        $this->schedule($lab, 'Monday', 'Kuliah Senin');

        $this->get(route('jadwal-lab.index'))
            ->assertOk()
            ->assertSee('Kuliah Rabu')
            ->assertDontSee('Kuliah Senin');
    }

    public function test_weekend_falls_back_to_all_days(): void
    {
        Carbon::setTestNow('2026-10-03 10:00:00'); // Sabtu.
        $lab = $this->lab();
        $this->schedule($lab, 'Monday', 'Kuliah Senin');
        $this->schedule($lab, 'Wednesday', 'Kuliah Rabu');

        $this->get(route('jadwal-lab.index'))
            ->assertOk()
            ->assertSee('Kuliah Senin')
            ->assertSee('Kuliah Rabu');
    }

    public function test_all_day_param_shows_every_day(): void
    {
        Carbon::setTestNow('2026-09-30 10:00:00'); // Rabu.
        $lab = $this->lab();
        $this->schedule($lab, 'Monday', 'Kuliah Senin');
        $this->schedule($lab, 'Wednesday', 'Kuliah Rabu');

        $this->get(route('jadwal-lab.index', ['day' => 'all']))
            ->assertOk()
            ->assertSee('Kuliah Senin')
            ->assertSee('Kuliah Rabu');
    }

    public function test_explicit_day_overrides_today(): void
    {
        Carbon::setTestNow('2026-09-30 10:00:00'); // Rabu.
        $lab = $this->lab();
        $this->schedule($lab, 'Monday', 'Kuliah Senin');
        $this->schedule($lab, 'Wednesday', 'Kuliah Rabu');

        $this->get(route('jadwal-lab.index', ['day' => 'Monday']))
            ->assertOk()
            ->assertSee('Kuliah Senin')
            ->assertDontSee('Kuliah Rabu');
    }

    public function test_invalid_day_returns_404(): void
    {
        $this->lab();

        $this->get(route('jadwal-lab.index', ['day' => 'Sunday']))
            ->assertNotFound();
    }

    public function test_day_all_is_not_treated_as_invalid(): void
    {
        $this->lab();

        $this->get(route('jadwal-lab.index', ['day' => 'all']))
            ->assertOk();
    }
}
