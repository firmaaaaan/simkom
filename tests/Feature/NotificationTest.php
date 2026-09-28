<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'User Biasa',
            'email' => 'user@notif.test',
            'password' => 'rahasia123',
        ]);
    }

    private function notify(array $attributes = []): Notification
    {
        return Notification::create(array_merge([
            'title' => 'Notifikasi Uji',
            'message' => 'Pesan notifikasi uji',
            'type' => 'info',
            'url' => route('dashboard'),
        ], $attributes));
    }

    public function test_index_lists_all_notifications(): void
    {
        $this->notify(['title' => 'Tiket Baru', 'type' => 'ticket']);
        $this->notify(['title' => 'Check-in Lab', 'type' => 'lab_usage']);
        $this->notify(['title' => 'Peminjaman Baru', 'type' => 'borrowing']);

        $this->actingAs($this->user())
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Tiket Baru')
            ->assertSee('Check-in Lab')
            ->assertSee('Peminjaman Baru')
            ->assertSee('Penggunaan Lab');
    }

    public function test_delete_single_notification(): void
    {
        $keep = $this->notify(['title' => 'Tetap Ada']);
        $remove = $this->notify(['title' => 'Akan Dihapus']);

        $this->actingAs($this->user())
            ->delete(route('notifications.destroy', $remove))
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', ['id' => $keep->id]);
        $this->assertDatabaseMissing('notifications', ['id' => $remove->id]);
    }

    public function test_delete_all_notifications(): void
    {
        $this->notify();
        $this->notify();
        $this->notify();

        $this->actingAs($this->user())
            ->delete(route('notifications.destroy-all'))
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_mark_all_read_form_redirects_and_bell_json_still_works(): void
    {
        $user = $this->user();
        $this->notify();

        // Form di halaman: redirect + read_at terisi.
        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));
        $this->assertNotNull(Notification::first()->read_at);

        // Lonceng (Accept: application/json): respons JSON unread_count 0.
        $this->notify(['title' => 'Belum Dibaca']);
        $this->actingAs($user)
            ->postJson(route('notifications.read-all'))
            ->assertOk()
            ->assertJson(['unread_count' => 0]);
    }

    public function test_unread_notification_shows_new_badge_and_read_shows_read(): void
    {
        $user = $this->user();

        $this->notify(['title' => 'Belum Dibaca']);
        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Baru');

        Notification::first()->update(['read_at' => now()]);
        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Dibaca');
    }

    public function test_page_requires_login(): void
    {
        $this->notify();

        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->delete(route('notifications.destroy-all'))->assertRedirect(route('login'));
    }
}
