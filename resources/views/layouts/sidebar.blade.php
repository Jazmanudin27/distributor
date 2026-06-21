@php
    $user = Auth::user();

    $pendingPelangganCount = \App\Models\Pelanggan::where(function ($q) {
        $q->whereNull('approve')->orWhere('approve', 0);
    })->count();

    $pendingLimitCount = \App\Models\AjuanLimitKredit::where('status', 'pending')->count();

    $pendingPembayaranCount =
        \App\Models\PenjualanPembayaran::where('status', 'pending')->count() +
        \Illuminate\Support\Facades\DB::table('penjualan_pembayaran_transfer')->where('status', 'pending')->count() +
        \Illuminate\Support\Facades\DB::table('penjualan_pembayaran_giro')->where('status', 'pending')->count();

    $totalTransaksiPending = 0;
    if ($user) {
        if ($user->can('view-ajuan_limit_kredit')) {
            $totalTransaksiPending += $pendingLimitCount;
        }
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            $totalTransaksiPending += $pendingPembayaranCount;
        }
    }

    // Active state checks for accordion expansion
    $isMasterActive = request()->routeIs('kategori.*') || 
                      request()->routeIs('merk.*') || 
                      request()->routeIs('supplier.*') || 
                      request()->routeIs('barang.*') || 
                      request()->routeIs('barang_satuan.*') || 
                      request()->routeIs('pelanggan.*') || 
                      request()->routeIs('diskon-strata.*');

    $isTransaksiActive = request()->routeIs('penjualan.*') || 
                         request()->routeIs('retur-penjualan.*') || 
                         request()->routeIs('penjualan-kiriman.*') || 
                         request()->routeIs('pembelian.*') || 
                         request()->routeIs('retur-pembelian.*') || 
                         request()->routeIs('stok-opname.*') || 
                         request()->routeIs('ajuan-limit-kredit.*') || 
                         request()->routeIs('pembayaran.pending.*') || 
                         request()->routeIs('kas-bank.*');

    $isSalesActive = request()->routeIs('sales-tracking.*');

    $isLaporanActive = request()->routeIs('laporan.*');

    $isSettingActive = request()->routeIs('users.*') || 
                       request()->routeIs('roles.*');
@endphp

@if ($user)
    <div class="sidebar-menu-accordion accordion" id="sidebarMenuAccordion">
        {{-- DASHBOARD --}}
        <div class="accordion-item">
            <h2 class="accordion-header">
                <a href="{{ url('/') }}" class="accordion-button no-chevron text-decoration-none sidebar-main-item {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
                    <span><i class="fa-solid fa-gauge-high me-2 text-primary"></i> Dashboard</span>
                </a>
            </h2>
        </div>

        {{-- DATA MASTER --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingMaster">
                <button class="accordion-button {{ $isMasterActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseMaster" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}" aria-controls="collapseMaster">
                    <span>
                        <i class="fa-solid fa-folder me-2 text-primary"></i> Data Master
                        @if ($pendingPelangganCount > 0 && $user->can('view-pelanggan'))
                            <span class="badge bg-warning text-dark rounded-pill ms-2"
                                style="font-size: 0.65rem; padding: 0.25em 0.55em;">{{ $pendingPelangganCount }}</span>
                        @endif
                    </span>
                </button>
            </h2>
            <div id="collapseMaster" class="accordion-collapse collapse {{ $isMasterActive ? 'show' : '' }}" aria-labelledby="headingMaster"
                data-bs-parent="#sidebarMenuAccordion">
                <div class="accordion-body">
                    <div class="sidebar-submenu-list">
                        @can('view-kategori')
                            <a href="{{ route('kategori.index') }}" class="sidebar-submenu-item {{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-tags text-primary"></i> Data Kategori
                            </a>
                        @endcan
                        @can('view-merk')
                            <a href="{{ route('merk.index') }}" class="sidebar-submenu-item {{ request()->routeIs('merk.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-copyright text-info"></i> Data Merk
                            </a>
                        @endcan
                        @can('view-supplier')
                            <a href="{{ route('supplier.index') }}" class="sidebar-submenu-item {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-truck text-success"></i> Data Supplier
                            </a>
                        @endcan
                        @can('view-barang')
                            <a href="{{ route('barang.index') }}" class="sidebar-submenu-item {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-boxes-stacked text-warning"></i> Data Barang
                            </a>
                        @endcan
                        @can('view-satuan')
                            <a href="{{ route('barang_satuan.index') }}" class="sidebar-submenu-item {{ request()->routeIs('barang_satuan.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-balance-scale text-primary"></i> Barang Satuan
                            </a>
                        @endcan
                        @can('view-pelanggan')
                            <a href="{{ route('pelanggan.index') }}"
                                class="sidebar-submenu-item d-flex justify-content-between align-items-center {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                                <span>
                                    <i class="fa-solid fa-address-book text-success"></i> Data Pelanggan
                                </span>
                                @if ($pendingPelangganCount > 0)
                                    <span class="badge bg-warning text-dark rounded-pill"
                                        style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingPelangganCount }}
                                        pending</span>
                                @endif
                            </a>
                        @endcan
                        @can('view-diskon_strata')
                            <a href="{{ route('diskon-strata.index') }}" class="sidebar-submenu-item {{ request()->routeIs('diskon-strata.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-percent text-primary"></i> Diskon Strata
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- TRANSAKSI --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTransaksi">
                <button class="accordion-button {{ $isTransaksiActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseTransaksi" aria-expanded="{{ $isTransaksiActive ? 'true' : 'false' }}" aria-controls="collapseTransaksi">
                    <span>
                        <i class="fa-solid fa-receipt me-2 text-primary"></i> Transaksi
                        @if ($totalTransaksiPending > 0)
                            <span class="badge bg-danger text-light rounded-pill ms-2"
                                style="font-size: 0.65rem; padding: 0.25em 0.55em;">{{ $totalTransaksiPending }}</span>
                        @endif
                    </span>
                </button>
            </h2>
            <div id="collapseTransaksi" class="accordion-collapse collapse {{ $isTransaksiActive ? 'show' : '' }}" aria-labelledby="headingTransaksi"
                data-bs-parent="#sidebarMenuAccordion">
                <div class="accordion-body">
                    <div class="sidebar-submenu-list">
                        @can('view-penjualan')
                            <a href="{{ route('penjualan.index') }}" class="sidebar-submenu-item {{ request()->routeIs('penjualan.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-file-invoice-dollar text-success"></i> Penjualan
                            </a>
                        @endcan
                        @can('view-retur_penjualan')
                            <a href="{{ route('retur-penjualan.index') }}" class="sidebar-submenu-item {{ request()->routeIs('retur-penjualan.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-rotate-right text-success"></i> Retur Penjualan
                            </a>
                        @endcan
                        @can('view-penjualan_kiriman')
                            <a href="{{ route('penjualan-kiriman.index') }}" class="sidebar-submenu-item {{ request()->routeIs('penjualan-kiriman.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-truck-ramp-box text-info"></i> Kiriman Penjualan
                            </a>
                        @endcan
                        @can('view-pembelian')
                            <a href="{{ route('pembelian.index') }}" class="sidebar-submenu-item {{ request()->routeIs('pembelian.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-cart-shopping text-warning"></i> Pembelian
                            </a>
                        @endcan
                        @can('view-retur_pembelian')
                            <a href="{{ route('retur-pembelian.index') }}" class="sidebar-submenu-item {{ request()->routeIs('retur-pembelian.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-rotate-left text-danger"></i> Retur Pembelian
                            </a>
                        @endcan
                        @can('view-stok_opname')
                            <a href="{{ route('stok-opname.index') }}" class="sidebar-submenu-item {{ request()->routeIs('stok-opname.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-clipboard-list text-info"></i> Stok Opname
                            </a>
                        @endcan
                        @can('view-ajuan_limit_kredit')
                            <a href="{{ route('ajuan-limit-kredit.index') }}"
                                class="sidebar-submenu-item d-flex justify-content-between align-items-center {{ request()->routeIs('ajuan-limit-kredit.*') ? 'active' : '' }}">
                                <span>
                                    <i class="fa-solid fa-hand-holding-dollar text-warning"></i> Ajuan Limit Kredit
                                </span>
                                @if ($pendingLimitCount > 0)
                                    <span class="badge bg-danger text-light rounded-pill"
                                        style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingLimitCount }}</span>
                                @endif
                            </a>
                        @endcan
                        @if ($user->hasRole('Super Admin') || $user->hasRole('Admin'))
                            <a href="{{ route('pembayaran.pending.index') }}"
                                class="sidebar-submenu-item d-flex justify-content-between align-items-center {{ request()->routeIs('pembayaran.pending.*') ? 'active' : '' }}">
                                <span>
                                    <i class="fa-solid fa-circle-check text-warning"></i> Pembayaran
                                </span>
                                @if ($pendingPembayaranCount > 0)
                                    <span class="badge bg-danger text-light rounded-pill"
                                        style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingPembayaranCount }}
                                    </span>
                                @endif
                            </a>
                        @endif
                        @if ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('view-kas_bank'))
                            <a href="{{ route('kas-bank.index') }}" class="sidebar-submenu-item {{ request()->routeIs('kas-bank.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-wallet text-warning"></i> Kas & Bank
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- AKTIVITAS SALES --}}
        @if ($user->hasRole('Super Admin') || $user->can('view-penjualan'))
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSales">
                    <button class="accordion-button {{ $isSalesActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseSales" aria-expanded="{{ $isSalesActive ? 'true' : 'false' }}" aria-controls="collapseSales">
                        <span><i class="fa-solid fa-person-running me-2 text-primary"></i> Aktivitas Sales</span>
                    </button>
                </h2>
                <div id="collapseSales" class="accordion-collapse collapse {{ $isSalesActive ? 'show' : '' }}" aria-labelledby="headingSales"
                    data-bs-parent="#sidebarMenuAccordion">
                    <div class="accordion-body">
                        <div class="sidebar-submenu-list">
                            <a href="{{ route('sales-tracking.index') }}" class="sidebar-submenu-item {{ request()->routeIs('sales-tracking.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-map-location-dot text-danger"></i> Tracking Sales (Peta)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- LAPORAN --}}
        @if (
            $user->can('view-laporan_pembelian') ||
                $user->can('view-laporan_retur_pembelian') ||
                $user->can('view-laporan_stok') ||
                $user->can('view-laporan_penjualan') ||
                $user->can('view-laporan_retur_penjualan') ||
                $user->can('view-laporan_piutang'))
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingLaporan">
                    <button class="accordion-button {{ $isLaporanActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseLaporan" aria-expanded="{{ $isLaporanActive ? 'true' : 'false' }}" aria-controls="collapseLaporan">
                        <span><i class="fa-solid fa-chart-line me-2 text-primary"></i> Laporan</span>
                    </button>
                </h2>
                <div id="collapseLaporan" class="accordion-collapse collapse {{ $isLaporanActive ? 'show' : '' }}" aria-labelledby="headingLaporan"
                    data-bs-parent="#sidebarMenuAccordion">
                    <div class="accordion-body">
                        <div class="sidebar-submenu-list">
                            @can('view-laporan_pembelian')
                                <a href="{{ route('laporan.pembelian') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.pembelian') ? 'active' : '' }}">
                                    <i class="fa-solid fa-file-invoice-dollar text-warning"></i> Pembelian
                                </a>
                            @endcan
                            @can('view-laporan_retur_pembelian')
                                <a href="{{ route('laporan.retur-pembelian') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.retur-pembelian') ? 'active' : '' }}">
                                    <i class="fa-solid fa-file-invoice text-danger"></i> Retur Pembelian
                                </a>
                            @endcan
                            @can('view-laporan_stok')
                                <a href="{{ route('laporan.stok') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
                                    <i class="fa-solid fa-boxes-stacked text-info"></i> Stok Barang
                                </a>
                            @endcan
                            @can('view-laporan_penjualan')
                                <a href="{{ route('laporan.penjualan') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">
                                    <i class="fa-solid fa-file-invoice-dollar text-success"></i> Penjualan
                                </a>
                            @endcan
                            @can('view-laporan_retur_penjualan')
                                <a href="{{ route('laporan.retur-penjualan') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.retur-penjualan') ? 'active' : '' }}">
                                    <i class="fa-solid fa-file-invoice text-danger"></i> Retur Penjualan
                                </a>
                            @endcan
                            @can('view-laporan_piutang')
                                <a href="{{ route('laporan.piutang') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.piutang') ? 'active' : '' }}">
                                    <i class="fa-solid fa-address-card text-info"></i> Piutang Pelanggan
                                </a>
                            @endcan
                            @can('view-laporan_laba_rugi')
                                <a href="{{ route('laporan.laba-rugi') }}" class="sidebar-submenu-item {{ request()->routeIs('laporan.laba-rugi') ? 'active' : '' }}">
                                    <i class="fa-solid fa-calculator text-success"></i> Laba Rugi
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- SETTING --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSetting">
                <button class="accordion-button {{ $isSettingActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseSetting" aria-expanded="{{ $isSettingActive ? 'true' : 'false' }}" aria-controls="collapseSetting">
                    <span><i class="fa-solid fa-gear me-2 text-secondary"></i> Setting</span>
                </button>
            </h2>
            <div id="collapseSetting" class="accordion-collapse collapse {{ $isSettingActive ? 'show' : '' }}" aria-labelledby="headingSetting"
                data-bs-parent="#sidebarMenuAccordion">
                <div class="accordion-body">
                    <div class="sidebar-submenu-list">
                        @can('view-users')
                            <a href="{{ route('users.index') }}" class="sidebar-submenu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-users text-info"></i> Data Users
                            </a>
                        @endcan
                        @can('view-roles')
                            <a href="{{ route('roles.index') }}" class="sidebar-submenu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-user-shield text-warning"></i> Hak Akses Menu
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- LOGOUT BUTTON --}}
        <div class="px-3 py-3 border-top border-white-10 mt-3 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger w-100 py-2 rounded-3">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
@endif
