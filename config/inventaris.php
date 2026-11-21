<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Scan Barcode Inventaris Eksternal (SIMPTT)
    |--------------------------------------------------------------------------
    |
    | Endpoint GET /api/v1/inventaris/scan?kode= yang mengambil detail
    | inventaris (nama alat, no inventaris, merk/type, lab, lokasi, status,
    | keterangan) dari kode barcode. Read-only.
    |
    | Respons 200: {success, message, data:{sumber, no_inventaris, nama_alat,
    | merk_type, laboratorium, kdruang, lokasi, status, keterangan}}
    | Respons 404: {success:false, message, data:null}
    |
    */
    'scan_api_url' => env('INVENTARIS_SCAN_API_URL', 'http://157.66.9.230:3000/api/v1/inventaris/scan'),

    'timeout' => (int) env('INVENTARIS_API_TIMEOUT', 10),
];
