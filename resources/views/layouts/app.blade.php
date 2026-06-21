<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DIS')</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>

<body style="zoom:90%">

    <div class="wrapper">
        <!-- Sidebar Offcanvas -->
        <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="sidebarOffcanvas"
            aria-labelledby="sidebarOffcanvasLabel">
            <div class="sidebar-header">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="logo-box">
                        <i class="fa-solid fa-layer-group logo-icon"></i>
                    </div>
                    <div class="lh-sm text-start" style="line-height: 1.3;">
                        <h4 class="mb-0 brand-title">ASPARTECH</h4>
                        <small class="brand-subtitle">ERP Distributor</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"
                    style="filter: invert(1) grayscale(100%) brightness(200%); opacity: 0.6; transition: all 0.3s ease;"></button>
            </div>
            <div class="offcanvas-body p-0">
                @include('layouts.sidebar')
            </div>
        </div>

        <!-- Page Content -->
        <div id="content">
            <!-- Topbar -->
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container-fluid">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="d-flex align-items-center ms-auto">
                        <span class="me-3 text-secondary">Halo, {{ Auth::user()->name ?? 'Guest' }}</span>
                        @if (Auth::check())
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="content-area">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Aktivitas -->
    <div class="modal fade" id="activityLogModal" tabindex="-1" aria-labelledby="activityLogModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow border-0 rounded-4">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="activityLogModalLabel">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i> Riwayat Aktivitas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="activityLogTable">
                            <thead class="table-light text-secondary text-uppercase fs-8 fw-semibold">
                                <tr>
                                    <th>Aksi</th>
                                    <th>User</th>
                                    <th>Keterangan</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- AJAX loaded content -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {

            // Initialize Select2 globally, keeping small size if .form-select-sm is used
            $('.form-select').each(function() {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    selectionCssClass: $(this).hasClass('form-select-sm') ? 'select2--small' : '',
                    dropdownCssClass: $(this).hasClass('form-select-sm') ? 'select2--small' : ''
                });
            });

            // Handle SweetAlert Confirmations globally
            $(document).on('click', '.delete', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Handle activity logs modal globally
            $(document).on('click', '.btn-show-logs', function(e) {
                e.preventDefault();
                const noFaktur = $(this).data('no-faktur');
                if (!noFaktur) return;

                const $tbody = $('#activityLogTable tbody');
                $tbody.html(
                    '<tr><td colspan="4" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>'
                );
                $('#activityLogModal').modal('show');

                $.ajax({
                    url: '/activity-logs/' + encodeURIComponent(noFaktur),
                    method: 'GET',
                    success: function(data) {
                        $tbody.empty();
                        if (data.length === 0) {
                            $tbody.append(
                                '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat aktivitas untuk transaksi ini.</td></tr>'
                            );
                            return;
                        }
                        data.forEach(function(log) {
                            const date = new Date(log.created_at);
                            const day = date.getDate();
                            const month = date.getMonth() + 1;
                            const year = date.getFullYear();
                            const hour = String(date.getHours()).padStart(2, '0');
                            const minute = String(date.getMinutes()).padStart(2, '0');
                            const second = String(date.getSeconds()).padStart(2, '0');
                            const formattedDate =
                                `${day}/${month}/${year}, ${hour}.${minute}.${second}`;

                            $tbody.append(`
                                <tr>
                                    <td><span class="badge bg-primary px-2.5 py-1.5 fw-semibold fs-8" style="font-size: 0.75rem;">${log.action}</span></td>
                                    <td class="fw-semibold text-dark">${log.user ? log.user.name : '-'}</td>
                                    <td class="text-secondary small">${log.description || '-'}</td>
                                    <td class="font-monospace text-secondary small">${formattedDate}</td>
                                </tr>
                            `);
                        });
                    },
                    error: function() {
                        $tbody.html(
                            '<tr><td colspan="4" class="text-center py-4 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Gagal memuat data. Silakan coba lagi.</td></tr>'
                        );
                    }
                });
            });

            // Handle invoice reprinting with reasons
            $(document).on('click', '.btn-print-faktur', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const cetak = parseInt($(this).data('cetak')) || 0;

                if (cetak >= 1) {
                    Swal.fire({
                        title: 'Konfirmasi Cetak Ulang',
                        text: 'Faktur ini sudah pernah dicetak. Silakan masukkan alasan cetak ulang:',
                        input: 'text',
                        inputPlaceholder: 'Contoh: Faktur hilang, printer macet...',
                        showCancelButton: true,
                        confirmButtonText: 'Cetak',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6c757d',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Alasan cetak ulang harus diisi!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const alasanEncoded = encodeURIComponent(result.value);
                            const printUrl = `${href}?alasan=${alasanEncoded}`;
                            window.open(printUrl, '_blank');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    });
                } else {
                    window.open(href, '_blank');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            });

            // Show SweetAlert for session success globally if needed
            @if (session('success_alert'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success_alert') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>

    @stack('scripts')
</body>

</html>
