<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <title>@yield('title', 'Sales Mobile')</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Custom Premium Mobile CSS -->
    <style>
        :root {
            --bg-primary: #0b0f19;
            --bg-secondary: #161e31;
            --bg-card: rgba(26, 36, 57, 0.65);
            --border-color: rgba(255, 255, 255, 0.08);
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --accent-glow: 0 8px 25px rgba(99, 102, 241, 0.3);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --active-color: #818cf8;
        }

        /* PWA Install Banner Style */
        .pwa-banner {
            display: none;
            background: rgba(22, 30, 49, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 12px 16px;
            margin: 10px 16px 16px 16px;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            animation: pwaSlideDown 0.4s ease-out;
            z-index: 1001;
        }

        @keyframes pwaSlideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .pwa-logo-container {
            width: 40px;
            height: 40px;
            background: var(--accent-gradient);
            box-shadow: var(--accent-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .pwa-text {
            flex-grow: 1;
            padding: 0 12px;
        }

        .pwa-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-primary);
        }

        .pwa-subtitle {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .pwa-btn-action {
            background: var(--accent-gradient);
            border: none;
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: var(--accent-glow);
            transition: all 0.2s;
        }

        .pwa-btn-action:active {
            transform: scale(0.95);
        }

        .pwa-close {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.15rem;
            cursor: pointer;
            padding: 0 4px;
            margin-left: 8px;
            transition: color 0.2s;
            line-height: 1;
        }

        .pwa-close:hover {
            color: #f87171;
        }

        .pwa-ios-guide {
            font-size: 0.75rem;
            color: #a78bfa;
            font-weight: 500;
            margin: 0;
            line-height: 1.3;
        }

        body {
            font-family: 'Outfit', 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            margin: 0;
            padding-bottom: 75px;
            /* bottom nav height */
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            overflow-x: hidden;
        }

        /* Glassmorphism Cards */
        .mobile-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .mobile-card:active {
            transform: scale(0.98);
        }

        /* Bottom Nav */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 68px;
            background: rgba(22, 30, 49, 0.92);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -4px 25px rgba(0, 0, 0, 0.3);
            padding-bottom: env(safe-area-inset-bottom);
        }

        .nav-item-mobile {
            text-align: center;
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.72rem;
            font-weight: 500;
            flex: 1;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-item-mobile i {
            font-size: 1.35rem;
            margin-bottom: 4px;
            transition: transform 0.3s ease;
        }

        .nav-item-mobile.active {
            color: var(--active-color);
        }

        .nav-item-mobile.active i {
            transform: translateY(-2px);
            text-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }

        .nav-item-mobile.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: var(--active-color);
            box-shadow: 0 0 8px var(--active-color);
        }

        /* Top Header */
        .top-header {
            position: sticky;
            top: 0;
            z-index: 900;
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .badge-sales {
            background: var(--accent-gradient);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: var(--accent-glow);
        }

        /* Standard Input Styling */
        .form-control-mobile {
            background-color: #121824 !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
            border-radius: 12px !important;
            padding: 12px 14px !important;
            font-size: 0.9rem !important;
        }

        .form-control-mobile:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25) !important;
        }

        .btn-mobile {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .btn-mobile-primary {
            background: var(--accent-gradient);
            border: none;
            color: white;
            box-shadow: var(--accent-glow);
        }

        .btn-mobile-primary:active {
            transform: scale(0.97);
            opacity: 0.9;
        }

        /* Micro-animations */
        @keyframes pulse-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(99, 102, 241, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
            }
        }

        .pulse-active {
            animation: pulse-glow 2s infinite;
        }

        /* Select2 Dark Theme Styling */
        .select2-container {
            width: 100% !important;
            margin-bottom: 2px !important;
        }

        .select2-container--default .select2-selection--single {
            background-color: #121824 !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px !important;
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text-primary) !important;
            padding-left: 14px !important;
            font-size: 0.9rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 10px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--text-secondary) transparent transparent transparent !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent var(--text-secondary) transparent !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--text-secondary) !important;
            opacity: 0.6 !important;
        }

        .select2-dropdown {
            background-color: var(--bg-secondary) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
            overflow: hidden !important;
            z-index: 1050 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #121824 !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
        }

        .select2-container--default .select2-search--dropdown {
            padding: 10px !important;
        }

        .select2-container--default .select2-results__option {
            padding: 10px 14px !important;
            font-size: 0.88rem !important;
            color: var(--text-secondary) !important;
            background-color: transparent !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--accent-gradient) !important;
            color: #fff !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(99, 102, 241, 0.2) !important;
            color: var(--text-primary) !important;
        }

        .select2-container--default .select2-results__option--disabled {
            color: rgba(255, 255, 255, 0.2) !important;
        }
    </style>
    @stack('styles')
</head>

<body>

    @if (Auth::check())
        <!-- Top Header -->
        <div class="top-header">
            <div class="d-flex align-items-center">
                <div class="logo-icon d-flex align-items-center justify-content-center rounded-3 me-2"
                    style="width: 30px; height: 30px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
                    <i class="fa-solid fa-layer-group text-white" style="font-size: 0.9rem;"></i>
                </div>
                <h1>DIS Mobile</h1>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('mobile.profile') }}"
                    class="badge-sales me-2 text-decoration-none d-flex align-items-center text-white">
                    <i class="fa-solid fa-circle-user me-1"></i> {{ Auth::user()->name }}
                </a>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="confirmLogout(event)"
                    style="text-decoration: none;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 1.15rem;"></i>
                </button>
            </div>
        </div>
    @endif

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="pwa-banner">
        <div class="pwa-logo-container">
            <i class="fa-solid fa-layer-group text-white" style="font-size: 1.15rem;"></i>
        </div>
        <div class="pwa-text">
            <h4 class="pwa-title">DIS ERP App</h4>
            <div id="pwa-install-action-area">
                <p class="pwa-subtitle">Pasang ke layar utama HP Anda</p>
            </div>
            <div id="pwa-ios-instructions" style="display: none;">
                <p class="pwa-ios-guide"><i class="fa-solid fa-share-from-square me-1"></i> Tap <strong>Share</strong>
                    lalu pilih <strong>Add to Home Screen</strong></p>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <button id="pwa-btn-install" class="pwa-btn-action">Pasang</button>
            <button id="pwa-btn-dismiss" class="pwa-close" aria-label="Close">&times;</button>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="container-fluid px-3 py-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert"
                style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert"
                style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171;">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    @if (Auth::check())
        @php
            $userRole = strtolower(Auth::user()->role ?? '');
            $isOwner = in_array($userRole, ['owner', 'admin', 'super admin', 'superadmin']);
        @endphp
        <!-- Bottom Nav -->
        <div class="bottom-nav">
            @if ($isOwner)
                <a href="{{ route('mobile.owner.dashboard') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.owner.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('mobile.owner.order.index') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.owner.order.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Orderan</span>
                </a>
                <a href="{{ route('mobile.owner.laba-rugi') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.owner.laba-rugi') ? 'active' : '' }}">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Laba Rugi</span>
                </a>
            @else
                @php $isKanvas = Auth::user()->is_kanvas; @endphp

                {{-- Dashboard (selalu ada) --}}
                <a href="{{ route('mobile.dashboard') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>

                @if ($isKanvas)
                {{-- DPB (hanya sales canvas) --}}
                <a href="{{ route('mobile.order.canvas.dpb') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.order.canvas.dpb*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>DPB</span>
                </a>
                @endif

                @if (!$isKanvas)
                {{-- Pelanggan / Kunjungan — hanya sales regular --}}
                <a href="{{ route('mobile.kunjungan.index') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.kunjungan.*') || Request::routeIs('mobile.order.create') || Request::routeIs('mobile.order.store') || Request::routeIs('mobile.pelanggan.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-store"></i>
                    <span>Pelanggan</span>
                </a>

                {{-- Barang — hanya sales regular --}}
                <a href="{{ route('mobile.barang.index') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.barang.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Barang</span>
                </a>
                @endif

                {{-- Penjualan (selalu ada, aktif juga saat canvas order) --}}
                <a href="{{ route('mobile.order.index') }}"
                    class="nav-item-mobile {{ (Request::routeIs('mobile.order.index') || (Request::routeIs('mobile.order.canvas.*') && !Request::routeIs('mobile.order.canvas.dpb*'))) ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Penjualan</span>
                </a>

                @if (!$isKanvas)
                {{-- Ajuan Limit — hanya sales regular --}}
                <a href="{{ route('mobile.limit-kredit.index') }}"
                    class="nav-item-mobile {{ Request::routeIs('mobile.limit-kredit.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Ajuan Limit</span>
                </a>
                @endif
            @endif
            @if ($isOwner)
                <form id="logout-form" action="{{ route('mobile.owner.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @else
                <form id="logout-form" action="{{ route('mobile.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endif
        </div>
    @endif

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>

    <script>
        function confirmLogout(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan keluar dari sesi ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6366f1',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                background: '#161e31',
                color: '#f8fafc'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // Global Rupiah Formatter for Input Fields
        document.addEventListener('DOMContentLoaded', function() {
            function formatRupiah(angka) {
                let number_string = angka.replace(/[^,\d]/g, '').toString();
                let split = number_string.split(',');
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }

                rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                return rupiah;
            }

            function cleanRupiah(value) {
                if (!value) return '';
                return value.toString().replace(/\./g, '').replace(/,/g, '.');
            }

            window.initRupiahInput = function(input) {
                if (input.dataset.rupiahInitialized) return;
                input.dataset.rupiahInitialized = 'true';

                input.type = 'text';
                input.setAttribute('inputmode', 'numeric');

                if (input.value) {
                    let rawVal = input.value;
                    input.value = formatRupiah(rawVal.replace('.', ','));
                }

                input.addEventListener('input', function(e) {
                    let cursorPosition = this.selectionStart;
                    let originalLength = this.value.length;

                    let formatted = formatRupiah(this.value);
                    this.value = formatted;

                    let newLength = this.value.length;
                    cursorPosition = cursorPosition + (newLength - originalLength);
                    this.setSelectionRange(cursorPosition, cursorPosition);
                });

                // Check bounds on change (blur / hit enter)
                input.addEventListener('change', function() {
                    let rawVal = cleanRupiah(this.value);
                    let numVal = parseFloat(rawVal) || 0;
                    let maxVal = parseFloat(this.getAttribute('max'));

                    if (!isNaN(maxVal) && numVal > maxVal) {
                        this.value = formatRupiah(maxVal.toString());
                        this.dispatchEvent(new Event('input')); // Re-format

                        if (window.Swal) {
                            Swal.fire({
                                title: 'Melebihi Jumlah Bayar',
                                text: 'Jumlah bayar tidak boleh melebihi sisa piutang (Maksimal Rp ' +
                                    maxVal.toLocaleString('id-ID') + ')',
                                icon: 'warning',
                                background: '#161e31',
                                color: '#f8fafc',
                                confirmButtonColor: '#6366f1'
                            });
                        } else {
                            alert('Jumlah bayar tidak boleh melebihi sisa piutang (Maksimal Rp ' +
                                maxVal.toLocaleString('id-ID') + ')');
                        }
                    }
                });
            };

            document.querySelectorAll('.rupiah-input').forEach(initRupiahInput);

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                if (node.classList && node.classList.contains(
                                        'rupiah-input')) {
                                    initRupiahInput(node);
                                }
                                node.querySelectorAll('.rupiah-input').forEach(
                                    initRupiahInput);
                            }
                        });
                    }
                });
            });
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            document.addEventListener('submit', function(e) {
                const form = e.target;
                let isValid = true;

                form.querySelectorAll('.rupiah-input').forEach(function(input) {
                    let rawVal = cleanRupiah(input.value);
                    let numVal = parseFloat(rawVal) || 0;

                    let maxVal = parseFloat(input.getAttribute('max'));
                    let minVal = parseFloat(input.getAttribute('min'));

                    if (!isNaN(maxVal) && numVal > maxVal) {
                        isValid = false;
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Input Tidak Valid',
                                text: 'Jumlah tidak boleh melebihi Rp ' + maxVal
                                    .toLocaleString('id-ID'),
                                icon: 'warning',
                                background: '#161e31',
                                color: '#f8fafc',
                                confirmButtonColor: '#6366f1'
                            });
                        } else {
                            alert('Jumlah tidak boleh melebihi Rp ' + maxVal.toLocaleString(
                                'id-ID'));
                        }
                    }
                    if (!isNaN(minVal) && numVal < minVal) {
                        isValid = false;
                        if (window.Swal) {
                            Swal.fire({
                                title: 'Input Tidak Valid',
                                text: 'Jumlah tidak boleh kurang dari Rp ' + minVal
                                    .toLocaleString('id-ID'),
                                icon: 'warning',
                                background: '#161e31',
                                color: '#f8fafc',
                                confirmButtonColor: '#6366f1'
                            });
                        } else {
                            alert('Jumlah tidak boleh kurang dari Rp ' + minVal.toLocaleString(
                                'id-ID'));
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }

                form.querySelectorAll('.rupiah-input').forEach(function(input) {
                    input.value = cleanRupiah(input.value);
                });
            });
        });
    </script>
    <script src="{{ asset('js/pwa.js') }}"></script>
    @if(session('success'))
        <script>
            if (window.localStorage && '{{ Auth::user() ? Auth::user()->nik : "" }}') {
                localStorage.removeItem('mobile_order_cart_' + '{{ Auth::user() ? Auth::user()->nik : "" }}');
            }
        </script>
    @endif
    @stack('scripts')
</body>

</html>
