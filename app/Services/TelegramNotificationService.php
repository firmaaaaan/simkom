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
     * Gagal kirim (token/chat_id salah, jaringan, Telegram down) hanya
     * dicatat ke log dan tidak pernah dilempar, agar penyimpanan
     * notifikasi in-app serta request utama tetap berjalan normal.
     */
    public function send(string $title, string $message, ?string $url = null): void
    {
        $token = (string) config('telegram.bot_token');
        $chatId = (string) config('telegram.chat_id');

        if ($token === '' || $chatId === '') {
            return;
        }

        try {
            $response = Http::timeout((int) config('telegram.timeout', 5))
                ->post('https://api.telegram.org/bot'.$token.'/sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $this->format($title, $message, $url),
                    'parse_mode' => (string) config('telegram.parse_mode', 'HTML'),
                    'disable_web_page_preview' => true,
                ]);

            if (! $response->successful()) {
                Log::warning('Pengiriman notifikasi Telegram ditolak.', [
                    'status' => $response->status(),
                    'description' => $response->json('description'),
                ]);
            }
        } catch (Throwable $e) {
            // Exception HTTP bisa memuat URL lengkap berisi token — redact dulu.
            Log::warning('Pengiriman notifikasi Telegram gagal: '.str_replace($token, '***', $e->getMessage()));
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
