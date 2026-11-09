{{-- Shared import page layout used by K3-related import views. --}}
{{-- Expected vars:
    - $title       : Judul halaman
    - $body (slot): Konten spesifik halaman import
--}}

@section('title', $title ?? 'Import Data')
@section('header', $title ?? 'Import Data')

@section('content')
<div class="max-w-3xl">
    @yield('import-header')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        @yield('import-body')
    </div>
</div>
@endsection
