            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                id="layout-navbar" style="position: sticky; top: 0; z-index: 1030;">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                        <i class="bx bx-menu bx-sm"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <!-- Notification -->
                        <li class="nav-item navbar-dropdown dropdown-notifications dropdown mr-2">
                            <div class="navbar-nav align-items-center">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <i class="bx bx-bell bx-sm"></i>
                                    @php
                                        $pendingLaporan = \App\Models\LaporanKendalaKomputer::where('status_kendala', 'menunggu')->count();
                                        $pendingPeminjaman = \App\Models\PeminjamanKomputer::where('status_peminjaman', 'menunggu')->count();
                                        $totalNotification = $pendingLaporan + $pendingPeminjaman;
                                    @endphp
                                    <span class="badge bg-danger rounded-pill badge-notifications {{ $totalNotification > 0 ? '' : 'd-none' }}">{{ $totalNotification }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <h6 class="dropdown-header">Notifikasi</h6>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.laporan-kendala-komputer.index') }}" class="dropdown-item">
                                            <i class="bx bx-error-circle me-2 text-warning"></i>
                                            <span>Laporan Kendala Menunggu</span>
                                            <span class="badge bg-warning rounded-pill ms-auto notification-laporan-count">{{ $pendingLaporan }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.peminjaman-komputer.index') }}" class="dropdown-item">
                                            <i class="bx bx-desktop me-2 text-info"></i>
                                            <span>Peminjaman Komputer Menunggu</span>
                                            <span class="badge bg-info rounded-pill ms-auto notification-peminjaman-count">{{ $pendingPeminjaman }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item text-primary">
                                            Lihat Semua
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- /Notification -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                data-bs-toggle="dropdown">
                                <span class="fw-semibold">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                        <i class="bx bx-cog me-2"></i>
                                        <span class="align-middle">Pengaturan</span>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Keluar</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <!--/ User -->
                    </ul>
                </div>
            </nav>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var notificationBadge = document.querySelector('.badge-notifications');
                    var notificationDropdown = document.querySelector('.dropdown-toggle[data-bs-toggle="dropdown"]');

                    function updateNotifications() {
                        fetch('{{ route('admin.api.notifications') }}', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            var total = data.total;
                            var laporanBadge = document.querySelector('.notification-laporan-count');
                            var peminjamanBadge = document.querySelector('.notification-peminjaman-count');

                            if (laporanBadge) laporanBadge.textContent = data.pending_laporan;
                            if (peminjamanBadge) peminjamanBadge.textContent = data.pending_peminjaman;

                            if (total > 0) {
                                if (notificationBadge) {
                                    notificationBadge.textContent = total;
                                    notificationBadge.style.display = 'inline';
                                }
                            } else {
                                if (notificationBadge) {
                                    notificationBadge.style.display = 'none';
                                }
                            }
                        })
                        .catch(error => console.error('Error fetching notifications:', error));
                    }

                    // Update notifications every 30 seconds
                    setInterval(updateNotifications, 30000);

                    // Initial update
                    updateNotifications();
                });
            </script>
