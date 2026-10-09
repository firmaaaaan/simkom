@php
    // Fallback untuk kode status yang tidak punya view sendiri (401, 405, 422, dst.).
    $status = ($exception ?? null) ? ($exception->getStatusCode() ?? 500) : 500;

    $meta = [
        401 => [
            'title' => 'Belum Masuk',
            'message' => 'Anda harus masuk terlebih dahulu untuk mengakses halaman ini.',
            'toneBg' => 'bg-amber-50',
            'toneText' => 'text-amber-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />',
        ],
        405 => [
            'title' => 'Metode Tidak Diizinkan',
            'message' => 'Jenis permintaan yang Anda gunakan tidak didukung oleh halaman ini.',
            'toneBg' => 'bg-sky-50',
            'toneText' => 'text-sky-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />',
        ],
        413 => [
            'title' => 'Ukuran File Terlalu Besar',
            'message' => 'File yang diunggah melebihi batas ukuran maksimum. Silakan perkecil file lalu coba lagi.',
            'toneBg' => 'bg-orange-50',
            'toneText' => 'text-orange-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M22.5 12.75c0-.619-.042-1.22-.125-1.817a2.029 2.029 0 00-1.976-1.635H3.6a2.03 2.03 0 00-1.976 1.635c-.083.597-.125 1.198-.125 1.817 0 2.071 1.617 3.75 3.6 3.75h1.8M12 6v6m0 0l-2.25-2.25M12 12l2.25-2.25M3 12h.01M21 12h.01" />',
        ],
        422 => [
            'title' => 'Data Tidak Valid',
            'message' => 'Permintaan tidak dapat diproses karena data yang dikirim tidak valid. Silakan periksa kembali isian Anda.',
            'toneBg' => 'bg-violet-50',
            'toneText' => 'text-violet-500',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
    ];

    $default = [
        'title' => 'Terjadi Kesalahan',
        'message' => 'Permintaan tidak dapat diproses. Silakan coba lagi.',
        'toneBg' => 'bg-amber-50',
        'toneText' => 'text-amber-500',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    ];

    $page = $meta[$status] ?? $default;
@endphp

@include('errors.layout', array_merge(['code' => $status], $page))
