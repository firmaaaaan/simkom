<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramNotificationService
{
    /**
     * Teruskan satu notifikasi lonceng (in-app) ke chat Telegram.
     *
     * Koneksi ke api.telegram.org bisa lambat/tidak stabil, jadi kirimannya
     * diulang sampai batas max_attempts. Gagal total (token/chat_id salah,
     * jaringan mati) hanya dicatat ke log dan tidak pernah dilempar, agar
     * penyimpanan notifikasi in-app serta request utama tetap berjalan.
     */
    public function send(string $title, string $message, ?string $url = null): void
    {
        $token = (string) config('telegram.bot_token');
        $chatId = (string) config('telegram.chat_id');

        if ($token === '' || $chatId === '') {
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $this->format($title, $message, $url),
            'parse_mode' => (string) config('telegram.parse_mode', 'HTML'),
            'disable_web_page_preview' => true,
        ];

        $attempts = max(1, (int) config('telegram.max_attempts', 2));
        $timeout = (int) config('telegram.timeout', 15);

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $started = microtime(true);
                $response = Http::timeout($timeout)
                    ->post('https://api.telegram.org/bot'.$token.'/sendMessage', $payload);
                $elapsedMs = (int) round((microtime(true) - $started) * 1000);

                if ($response->successful()) {
                    Log::info('Notifikasi Telegram terkirim.', [
                        'status' => $response->status(),
                        'ms' => $elapsedMs,
                        'attempt' => $attempt,
                    ]);

                    return;
                }

                $status = $response->status();
                Log::warning('Pengiriman notifikasi Telegram ditolak.', [
                    'status' => $status,
                    'description' => $response->json('description'),
                    'ms' => $elapsedMs,
                    'attempt' => $attempt,
                ]);

                // 4xx selain 429 = permintaan salah (token/chat_id) — percobaan
                // ulang percuma, hentikan sekarang.
                if ($status !== 429 && $status < 500) {
                    return;
                }
            } catch (Throwable $e) {
                // Exception HTTP bisa memuat URL lengkap berisi token — redact dulu.
                Log::warning('Pengiriman notifikasi Telegram gagal pada percobaan '.$attempt.' dari '.$attempts.': '
                    .str_replace($token, '***', $e->getMessage()));
            }

            if ($attempt < $attempts) {
                // Jeda singkat sebelum percobaan ulang (koneksi lambat sering
                // pulih pada attempt berikutnya).
                usleep(max(0, (int) config('telegram.retry_delay_ms', 500)) * 1000);
            }
        }
    }

    /**
     * Susun isi pesan: judul tebal, isi, lalu tautan ke halaman terkait.
     * Semua konten di-escape karena title/message berasal dari input publik.
     */
    private function format(string $title, string $message, ?string $url): string
    {
        $text = '<b>'.e($title).'</b>'."\n".e($message);

        if ($url !== null && $url !== '') {
            $text .= "\n\n".'<a href="'.e($url).'">Lihat detail</a>';
        }

        return $text;
    }
}
