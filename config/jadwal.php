<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Jadwal Laboratorium Eksternal (SIMPTT)
    |--------------------------------------------------------------------------
    |
    | Endpoint publik GET /api/v1/jadwal/lab yang menyediakan jadwal
    | penggunaan laboratorium per jam. Query param: tanggal (Y-m-d),
    | kdruang (kode ruang, opsional).
    |
    */
    'api_url' => env('JADWAL_API_URL', 'http://157.66.9.230:3000/api/v1/jadwal/lab'),

    /*
    |--------------------------------------------------------------------------
    | API Jadwal Kelas Eksternal (SIMPTT)
    |--------------------------------------------------------------------------
    |
    | Endpoint terpisah untuk jadwal kelas. Format payload & query param
    | sama dengan endpoint laboratorium.
    |
    */
    'kelas_api_url' => env('JADWAL_KELAS_API_URL', 'http://157.66.9.230:3000/api/v1/jadwal/kelas'),

    'timeout' => (int) env('JADWAL_API_TIMEOUT', 10),

    // Lama cache response per tanggal (detik).
    'cache_ttl' => (int) env('JADWAL_API_CACHE_TTL', 300),
];
