<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\BoxUsage;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoxUsageNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function box(string $code = 'BOX-RAM-001'): Box
    {
        return Box::create(['name' => 'Box RAM', 'code' => $code, 'location' => 'Rak A']);
    }

    public function test_qr_scan_borrowing_creates_notification(): void
    {
        $box = $this->box();

        $this->postJson(route('box-scan.use', $box->code), [
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'user_kelas' => 'TI-4A',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, Notification::count());

        $notification = Notification::first();
        $this->assertSame('box_usage', $notification->type);
        $this->assertSame('Peminjaman Box Baru', $notification->title);
        $this->assertStringContainsString('Budi Santoso', $notification->message);
        $this->assertStringContainsString('2210512001', $notification->message);
        $this->assertStringContainsString('BOX-RAM-001', $notification->message);
        $this->assertSame(route('boxes.index'), $notification->url);
        $this->assertNull($notification->read_at);
    }

    public function test_manual_admin_input_does_not_create_notification(): void
    {
        $box = $this->box();

        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $role->permissions()->attach(
            Permission::firstOrCreate(['name' => 'create-components'], ['label' => 'Input Komponen'])
        );
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@boxnotif.test', 'password' => 'rahasia123']);
        $admin->roles()->attach($role);

        $this->actingAs($admin)->post(route('box-usages.store'), [
            'box_id' => $box->id,
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'user_kelas' => 'TI-4A',
            'status' => 'Using',
            'used_at' => '2026-09-29 08:00',
        ])->assertSessionHas('success');

        $this->assertSame(1, BoxUsage::count());
        $this->assertSame(0, Notification::count());
    }
}
