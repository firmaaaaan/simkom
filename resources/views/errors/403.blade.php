@include('errors.layout', [
    'code' => 403,
    'title' => 'Akses Ditolak',
    'message' => 'Anda tidak memiliki izin untuk membuka halaman ini. Jika Anda merasa halaman ini seharusnya bisa diakses, hubungi admin untuk memberi Anda izin.',
    'toneBg' => 'bg-violet-50',
    'toneText' => 'text-violet-500',
    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />',
])
