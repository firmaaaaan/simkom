<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Jadwal Perkuliahan Eksternal (SIMPTT)
    |--------------------------------------------------------------------------
    |
    | Endpoint publik GET /api/v1/jadwal/lab yang menyediakan jadwal
    | penggunaan laboratorium per jam. Query param: tanggal (Y-m-d),
    | kdruang (kode ruang, opsional).
    |
    */
    'api_url' => env('JADWAL_API_URL', 'http://157.66.9.230:3000/api/v1/jadwal/lab'),

    'timeout' => (int) env('JADWAL_API_TIMEOUT', 10),

    // Lama cache response per tanggal (detik).
    'cache_ttl' => (int) env('JADWAL_API_CACHE_TTL', 300),
];
