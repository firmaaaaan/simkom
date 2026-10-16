<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function configureTelegram(): void
    {
        config()->set('telegram.bot_token', 'test-token');
        config()->set('telegram.chat_id', '1166966295');
        config()->set('telegram.retry_delay_ms', 0);
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

    public function test_notification_is_forwarded_to_telegram(): void
    {
        $this->configureTelegram();
        Http::fake();

        $this->notify(['title' => 'Laporan Kendala Baru', 'message' => 'Tiket TKT-123 - PC mati']);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/bottest-token/sendMessage')
                && $request['chat_id'] === '1166966295'
                && $request['parse_mode'] === 'HTML'
                && $request['disable_web_page_preview'] === true
                && str_contains($request['text'], '<b>Laporan Kendala Baru</b>')
                && str_contains($request['text'], 'Tiket TKT-123 - PC mati')
                && str_contains($request['text'], '<a href="'.e(route('dashboard')).'">Lihat detail</a>');
        });
    }

    public function test_no_telegram_request_when_not_configured(): void
    {
        Http::fake();

        $this->notify();

        Http::assertNothingSent();
    }

    public function test_telegram_http_error_does_not_break_notification(): void
    {
        $this->configureTelegram();
        Http::fake(['api.telegram.org/*' => Http::response('Bad Request', 400)]);

        $this->notify();

        $this->assertSame(1, Notification::count());
        Http::assertSentCount(1);
    }

    public function test_telegram_connection_failure_does_not_break_notification(): void
    {
        $this->configureTelegram();
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;

            throw new ConnectionException('cURL error 28: Operation timed out');
        });

        $this->notify();

        // Gagal di semua percobaan tetap tidak melempar dan notifikasi tersimpan.
        $this->assertSame(2, $calls);
        $this->assertSame(1, Notification::count());
    }

    public function test_retries_after_connection_failure_then_succeeds(): void
    {
        $this->configureTelegram();
        $calls = 0;
        Http::fake(function () use (&$calls) {
            $calls++;
            if ($calls === 1) {
                throw new ConnectionException('cURL error 28: Connection timed out after 5012 milliseconds');
            }

            return Http::response(['ok' => true], 200);
        });

        $this->notify();

        $this->assertSame(2, $calls);
        $this->assertSame(1, Notification::count());
        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'Notifikasi Uji'));
    }

    public function test_retries_after_server_error(): void
    {
        $this->configureTelegram();
        Http::fake([
            'api.telegram.org/*' => Http::sequence()->pushStatus(500)->pushStatus(200, ['ok' => true]),
        ]);

        $this->notify();

        $this->assertSame(1, Notification::count());
        Http::assertSentCount(2);
    }

    public function test_qr_box_scan_forwards_to_telegram(): void
    {
        $this->configureTelegram();
        Http::fake();

        $box = Box::create(['name' => 'Box RAM', 'code' => 'BOX-RAM-TG', 'location' => 'Rak A']);

        $this->postJson(route('box-scan.use', $box->code), [
            'user_name' => 'Budi Santoso',
            'user_nim' => '2210512001',
            'user_kelas' => 'TI-4A',
        ])->assertOk()->assertJson(['success' => true]);

        Http::assertSent(function (Request $request) {
            return str_contains($request['text'], '<b>Peminjaman Box Baru</b>')
                && str_contains($request['text'], 'Budi Santoso')
                && str_contains($request['text'], 'BOX-RAM-TG');
        });
    }
}
