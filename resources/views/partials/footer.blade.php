<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
        <div class="mb-2 mb-md-0">
            ©
            <script>
                document.write(new Date().getFullYear());
            </script>
            , SimLabKom - Sistem Inventaris Laboratorium
        </div>
        <div>
            <a href="{{ url('/') }}" class="footer-link me-4">Beranda</a>
            <a href="{{ route('peminjaman-komputer.index') }}" class="footer-link me-4">Peminjaman</a>
            <a href="{{ route('laporan-kendala-komputer.create') }}" class="footer-link me-4">Lapor Kendala</a>
            <a href="{{ route('laporan-kendala-komputer.track') }}" class="footer-link">Lacak</a>
        </div>
    </div>
</footer>
