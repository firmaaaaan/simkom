@include('errors.layout', [
    'code' => 429,
    'title' => 'Terlalu Banyak Permintaan',
    'message' => 'Anda mengirim permintaan terlalu cepat. Mohon tunggu beberapa saat sebelum mencoba lagi.',
    'toneBg' => 'bg-orange-50',
    'toneText' => 'text-orange-500',
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />',
])
