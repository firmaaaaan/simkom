<?php

namespace App\Services;

use App\Models\ApiRuang;
use App\Models\Laboratory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SimpttRuangSyncService
{
    /**
     * Ambil master ruangan dari API SIMPTT dan simpan hanya kode SB.
     * Upsert berdasarkan kdruang; kolom manual (catatan) tidak ditimpa.
     *
     * @return array{ditemukan:int, baru:int, diperbarui:int, dilewati:int}
     */
    public function sync(): array
    {
        $response = Http::timeout(config('jadwal.timeout'))
            ->get(config('jadwal.ruang_api_url'));

        if (! $response->successful()) {
            throw new RuntimeException('API ruang mengembalikan HTTP '.$response->status());
        }

        $data = $response->json('data');

        if (! is_array($data)) {
            throw new RuntimeException('Format respons API ruang tidak valid.');
        }

        $stats = ['ditemukan' => count($data), 'baru' => 0, 'diperbarui' => 0, 'dilewati' => 0];

        foreach ($data as $item) {
            $ruang = isset($item['ruang']) ? trim((string) $item['ruang']) : '';
            $kdruang = isset($item['kdruang']) ? (int) $item['kdruang'] : 0;

            if ($kdruang <= 0 || ! preg_match('/^SB(?=[\s.\/]|$)/i', $ruang)) {
                $stats['dilewati']++;

                continue;
            }

            $parsed = $this->parse($ruang);
            $exists = ApiRuang::query()->where('kdruang', $kdruang)->exists();

            ApiRuang::query()->updateOrCreate(
                ['kdruang' => $kdruang],
                [
                    'kode' => $parsed['kode'],
                    'nama' => $parsed['nama'],
                    'kapasitas' => $parsed['kapasitas'],
                    'ruang_asli' => $ruang,
                ]
            );

            $this->mirrorToLaboratory($parsed['kode'], $parsed['nama'], $parsed['kapasitas']);

            $stats[$exists ? 'diperbarui' : 'baru']++;
        }

        return $stats;
    }

    /**
     * Cerminkan ruangan SB ke master laboratories yang dipakai modul
     * monitoring suhu & kartu kendali alat. Field manual (ambang suhu,
     * lokasi yang sudah diubah, status, deskripsi) tidak ditimpa.
     */
    private function mirrorToLaboratory(string $kode, string $nama, int $kapasitas): void
    {
        $laboratory = Laboratory::query()->firstOrNew(['code' => $kode]);

        if (! $laboratory->exists) {
            $laboratory->location = 'Gedung SB';
        }

        $laboratory->name = $nama;
        $laboratory->capacity = $kapasitas;
        $laboratory->save();
    }

    /**
     * Uraikan "SB.1.02 / Ruang Laboratorium Komputer 2 (52)" menjadi
     * kode SB.1.02, nama Ruang Laboratorium Komputer 2, kapasitas 52.
     *
     * @return array{kode:string, nama:string, kapasitas:int}
     */
    private function parse(string $ruang): array
    {
        $ruang = preg_replace('/\s+/u', ' ', $ruang);
        $kapasitas = 0;
        $tanpaKapasitas = $ruang;

        if (preg_match('/\((\d+)\)\s*$/u', $ruang, $match)) {
            $kapasitas = (int) $match[1];
            $tanpaKapasitas = rtrim(mb_substr($ruang, 0, mb_strlen($ruang) - mb_strlen($match[0])));
        }

        $parts = explode('/', $tanpaKapasitas, 2);
        $kode = strtoupper(trim($parts[0]));
        $nama = trim($parts[1] ?? '');

        return [
            'kode' => $kode,
            'nama' => $nama !== '' ? $nama : $kode,
            'kapasitas' => $kapasitas,
        ];
    }
}
