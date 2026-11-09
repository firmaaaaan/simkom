<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the reset password mail message.
     */
    protected function buildMailMessage($url): MailMessage
    {
        $appName = (string) config('app.name', 'SimKom');
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi Akun '.$appName)
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun '.$appName.' Anda.')
            ->line('Klik tombol di bawah ini untuk membuat kata sandi baru. Demi keamanan, Anda akan diminta memasukkan kata sandi baru pada halaman yang terbuka.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan pengaturan ulang kata sandi ini berlaku selama '.$expire.' menit sejak email ini dikirim dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak merasa meminta pengaturan ulang kata sandi, abaikan email ini. Kata sandi Anda tidak akan berubah.')
            ->line('Jika tombol di atas tidak berfungsi, salin dan buka tautan berikut di peramban Anda:')
            ->line($url);
    }
}
