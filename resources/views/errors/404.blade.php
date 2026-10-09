@include('errors.layout', [
    'code' => 404,
    'title' => 'Halaman Tidak Ditemukan',
    'message' => 'Halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau alamat yang dimasukkan salah. Silakan periksa kembali URL-nya.',
    'toneBg' => 'bg-sky-50',
    'toneText' => 'text-sky-500',
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v3m0 0v3m0-3h3m-3 0H7.5" />',
])
