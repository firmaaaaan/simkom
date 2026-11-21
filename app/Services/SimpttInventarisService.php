<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SimpttInventarisService
{
    /**
     * Ambil detail inventaris dari API SIMPTT berdas kode barcode.
     *
     * @param  string  $kode  Kode barcode / no inventaris (maks. 45 karakter)
     * @return array<string, mixed>|null Data inventaris, atau null bila tidak ditemukan / kode tidak valid.
     *
     * @throws RuntimeException Bila API tidak dapat dihubungi atau mengembalikan error server.
     */
    public function scan(string $kode): ?array
    {
        $kode = trim($kode);

        if ($kode === '') {
            return null;
        }

        try {
            $response = Http::timeout(config('inventaris.timeout'))
                ->acceptJson()
                ->get(config('inventaris.scan_api_url'), ['kode' => $kode]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('API inventaris tidak dapat dihubungi.', 0, $e);
        }

        // 404 = tidak ditemukan, 400 = kode tidak valid (mis. terlalu panjang).
        if ($response->status() === 404 || $response->status() === 400) {
            return null;
        }

        if (! $response->successful()) {
            throw new RuntimeException('API inventaris mengembalikan HTTP '.$response->status());
        }

        $data = $response->json('data');

        if (! is_array($data) || $response->json('success') !== true) {
            return null;
        }

        return $data;
    }
}
