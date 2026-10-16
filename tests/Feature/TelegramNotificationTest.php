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
        Http::fake(function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        });

        $this->notify();

        $this->assertSame(1, Notification::count());
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
