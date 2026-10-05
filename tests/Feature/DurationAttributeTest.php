<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxUsage;
use App\Models\Laboratory;
use App\Models\LabUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DurationAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_box_usage_duration_counts_total_hours_and_minutes_until_now(): void
    {
        $box = Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001', 'location' => 'Rak A']);

        $usage = BoxUsage::create([
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'status' => 'Using',
            'used_at' => now()->subMinutes(150),
        ]);

        $this->assertSame('2 jam 30 menit', $usage->duration);
    }

    public function test_box_duration_shows_full_total_hours_beyond_one_day(): void
    {
        $box = Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001', 'location' => 'Rak A']);

        $usage = BoxUsage::create([
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'status' => 'Returned',
            'used_at' => '2026-10-01 08:00',
            'returned_at' => '2026-10-02 09:30',
        ]);

        $this->assertSame('25 jam 30 menit', $usage->duration);
    }

    public function test_box_duration_within_same_day_shows_hours_and_minutes(): void
    {
        $box = Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-001', 'location' => 'Rak A']);

        $usage = BoxUsage::create([
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'status' => 'Returned',
            'used_at' => '2026-10-01 08:00',
            'returned_at' => '2026-10-01 12:15',
        ]);

        $this->assertSame('4 jam 15 menit', $usage->duration);
    }

    public function test_active_lab_usage_duration_counts_until_now(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        $usage = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name' => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose' => 'Praktikum Jaringan',
            'day' => 'Senin',
            'status' => 'In',
            'checked_in_at' => now()->subMinutes(90),
        ]);

        $this->assertSame('1 jam 30 menit', $usage->duration);
    }

    public function test_lab_usage_duration_shows_full_total_hours_beyond_one_day(): void
    {
        $lab = Laboratory::create([
            'name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40,
        ]);

        $usage = LabUsage::create([
            'laboratory_id' => $lab->id,
            'user_name' => 'Budi Santoso',
            'user_prodi' => 'Teknik Informatika',
            'purpose' => 'Praktikum Jaringan',
            'day' => 'Senin',
            'status' => 'Out',
            'checked_in_at' => '2026-10-01 08:00',
            'validated_at' => '2026-10-03 10:00',
        ]);

        $this->assertSame('50 jam 0 menit', $usage->duration);
    }
}
