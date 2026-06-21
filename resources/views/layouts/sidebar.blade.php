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
    $isMasterActive =
        request()->routeIs('kategori.*') ||
        request()->routeIs('merk.*') ||
        request()->routeIs('supplier.*') ||
        request()->routeIs('barang.*') ||
        request()->routeIs('barang_satuan.*') ||
        request()->routeIs('pelanggan.*') ||
        request()->routeIs('diskon-strata.*');

    $isTransaksiActive =
        request()->routeIs('penjualan.*') ||
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

    $isSettingActive = request()->routeIs('users.*') || request()->routeIs('roles.*');
@endphp

@if ($user)
    <!-- Tenant Badge (Avatar and Info) -->
    <div class="tenant-badge-custom">
        <div class="tenant-card">
            <div class="tenant-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="tenant-info">
                <div class="tenant-name" title="{{ $user->name }}">{{ $user->name }}</div>
                <div class="tenant-role">
                    {{ $user->roles->first() ? ucfirst($user->roles->first()->name) : 'Staff' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">

        <!-- UTAMA -->
        <div class="section-title">Utama</div>

        <a href="{{ url('/') }}" class="{{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-table-cells-large"></i>
            <span>Dashboard</span>
        </a>

        <!-- MASTER -->
        <div class="section-title">Master</div>
        <div>
            <div class="dropdown-trigger {{ $isMasterActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                data-bs-target="#collapseMaster" role="button" aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}"
                aria-controls="collapseMaster">
                <i class="fa-solid fa-database"></i>
                <span>Data Master</span>
                @if ($pendingPelangganCount > 0 && $user->can('view-pelanggan'))
                    <span class="badge bg-danger text-light rounded-pill ms-2"
                        style="font-size: 0.65rem; padding: 0.25em 0.55em;">{{ $pendingPelangganCount }}</span>
                @endif
                <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
            </div>
            <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="collapseMaster">
                <div class="submenu-container">
                    @can('view-kategori')
                        <a href="{{ route('kategori.index') }}"
                            class="{{ request()->routeIs('kategori.*') ? 'active' : '' }}">Data Kategori</a>
                    @endcan
                    @can('view-merk')
                        <a href="{{ route('merk.index') }}"
                            class="{{ request()->routeIs('merk.*') ? 'active' : '' }}">Data Merk</a>
                    @endcan
                    @can('view-supplier')
                        <a href="{{ route('supplier.index') }}"
                            class="{{ request()->routeIs('supplier.*') ? 'active' : '' }}">Data Supplier</a>
                    @endcan
                    @can('view-barang')
                        <a href="{{ route('barang.index') }}"
                            class="{{ request()->routeIs('barang.*') ? 'active' : '' }}">Data Barang</a>
                    @endcan
                    @can('view-satuan')
                        <a href="{{ route('barang_satuan.index') }}"
                            class="{{ request()->routeIs('barang_satuan.*') ? 'active' : '' }}">Barang Satuan</a>
                    @endcan
                    @can('view-pelanggan')
                        <a href="{{ route('pelanggan.index') }}"
                            class="{{ request()->routeIs('pelanggan.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <span>Data Pelanggan</span>
                            @if ($pendingPelangganCount > 0)
                                <span class="badge bg-danger text-light rounded-pill"
                                    style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingPelangganCount }}</span>
                            @endif
                        </a>
                    @endcan
                    @can('view-diskon_strata')
                        <a href="{{ route('diskon-strata.index') }}"
                            class="{{ request()->routeIs('diskon-strata.*') ? 'active' : '' }}">Diskon Strata</a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- TRANSAKSI -->
        <div class="section-title">Transaksi</div>
        <div>
            <div class="dropdown-trigger {{ $isTransaksiActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                data-bs-target="#collapseTransaksi" role="button"
                aria-expanded="{{ $isTransaksiActive ? 'true' : 'false' }}" aria-controls="collapseTransaksi">
                <i class="fa-solid fa-receipt"></i>
                <span>Transaksi</span>
                @if ($totalTransaksiPending > 0)
                    <span class="badge bg-danger text-light rounded-pill ms-2"
                        style="font-size: 0.65rem; padding: 0.25em 0.55em;">{{ $totalTransaksiPending }}</span>
                @endif
                <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
            </div>
            <div class="collapse {{ $isTransaksiActive ? 'show' : '' }}" id="collapseTransaksi">
                <div class="submenu-container">
                    @can('view-penjualan')
                        <a href="{{ route('penjualan.index') }}"
                            class="{{ request()->routeIs('penjualan.*') ? 'active' : '' }}">Penjualan</a>
                    @endcan
                    @can('view-retur_penjualan')
                        <a href="{{ route('retur-penjualan.index') }}"
                            class="{{ request()->routeIs('retur-penjualan.*') ? 'active' : '' }}">Retur Penjualan</a>
                    @endcan
                    @can('view-penjualan_kiriman')
                        <a href="{{ route('penjualan-kiriman.index') }}"
                            class="{{ request()->routeIs('penjualan-kiriman.*') ? 'active' : '' }}">Kiriman Penjualan</a>
                    @endcan
                    @can('view-pembelian')
                        <a href="{{ route('pembelian.index') }}"
                            class="{{ request()->routeIs('pembelian.*') ? 'active' : '' }}">Pembelian</a>
                    @endcan
                    @can('view-retur_pembelian')
                        <a href="{{ route('retur-pembelian.index') }}"
                            class="{{ request()->routeIs('retur-pembelian.*') ? 'active' : '' }}">Retur Pembelian</a>
                    @endcan
                    @can('view-stok_opname')
                        <a href="{{ route('stok-opname.index') }}"
                            class="{{ request()->routeIs('stok-opname.*') ? 'active' : '' }}">Stok Opname</a>
                    @endcan
                    @can('view-ajuan_limit_kredit')
                        <a href="{{ route('ajuan-limit-kredit.index') }}"
                            class="{{ request()->routeIs('ajuan-limit-kredit.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <span>Ajuan Limit Kredit</span>
                            @if ($pendingLimitCount > 0)
                                <span class="badge bg-danger text-light rounded-pill"
                                    style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingLimitCount }}</span>
                            @endif
                        </a>
                    @endcan
                    @if ($user->hasRole('Super Admin') || $user->hasRole('Admin'))
                        <a href="{{ route('pembayaran.pending.index') }}"
                            class="{{ request()->routeIs('pembayaran.pending.*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <span>Pembayaran</span>
                            @if ($pendingPembayaranCount > 0)
                                <span class="badge bg-danger text-light rounded-pill"
                                    style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingPembayaranCount }}</span>
                            @endif
                        </a>
                    @endif
                    @if ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('view-kas_bank'))
                        <a href="{{ route('kas-bank.index') }}"
                            class="{{ request()->routeIs('kas-bank.*') ? 'active' : '' }}">Kas & Bank</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- AKTIVITAS SALES --}}
        @if ($user->hasRole('Super Admin') || $user->can('view-penjualan'))
            <div class="section-title">Aktivitas Sales</div>
            <div>
                <div class="dropdown-trigger {{ $isSalesActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                    data-bs-target="#collapseSales" role="button"
                    aria-expanded="{{ $isSalesActive ? 'true' : 'false' }}" aria-controls="collapseSales">
                    <i class="fa-solid fa-person-running"></i>
                    <span>Aktivitas Sales</span>
                    <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                </div>
                <div class="collapse {{ $isSalesActive ? 'show' : '' }}" id="collapseSales">
                    <div class="submenu-container">
                        <a href="{{ route('sales-tracking.index') }}"
                            class="{{ request()->routeIs('sales-tracking.*') ? 'active' : '' }}">Tracking Sales
                            (Peta)</a>
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
                $user->can('view-laporan_piutang') ||
                $user->can('view-laporan_laba_rugi') ||
                $user->can('view-laporan_setoran'))
            <div class="section-title">Laporan</div>
            <div>
                <div class="dropdown-trigger {{ $isLaporanActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                    data-bs-target="#collapseLaporan" role="button"
                    aria-expanded="{{ $isLaporanActive ? 'true' : 'false' }}" aria-controls="collapseLaporan">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Laporan</span>
                    <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                </div>
                <div class="collapse {{ $isLaporanActive ? 'show' : '' }}" id="collapseLaporan">
                    <div class="submenu-container">
                        @can('view-laporan_pembelian')
                            <a href="{{ route('laporan.pembelian') }}"
                                class="{{ request()->routeIs('laporan.pembelian') ? 'active' : '' }}">Pembelian</a>
                        @endcan
                        @can('view-laporan_retur_pembelian')
                            <a href="{{ route('laporan.retur-pembelian') }}"
                                class="{{ request()->routeIs('laporan.retur-pembelian') ? 'active' : '' }}">Retur
                                Pembelian</a>
                        @endcan
                        @can('view-laporan_stok')
                            <a href="{{ route('laporan.stok') }}"
                                class="{{ request()->routeIs('laporan.stok') ? 'active' : '' }}">Stok Barang</a>
                        @endcan
                        @can('view-laporan_penjualan')
                            <a href="{{ route('laporan.penjualan') }}"
                                class="{{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">Penjualan</a>
                        @endcan
                        @can('view-laporan_retur_penjualan')
                            <a href="{{ route('laporan.retur-penjualan') }}"
                                class="{{ request()->routeIs('laporan.retur-penjualan') ? 'active' : '' }}">Retur
                                Penjualan</a>
                        @endcan
                        @can('view-laporan_piutang')
                            <a href="{{ route('laporan.piutang') }}"
                                class="{{ request()->routeIs('laporan.piutang') ? 'active' : '' }}">Piutang Pelanggan</a>
                        @endcan
                        @can('view-laporan_laba_rugi')
                            <a href="{{ route('laporan.laba-rugi') }}"
                                class="{{ request()->routeIs('laporan.laba-rugi') ? 'active' : '' }}">Laba Rugi</a>
                        @endcan
                        @can('view-laporan_setoran')
                            <a href="{{ route('laporan.setoran') }}"
                                class="{{ request()->routeIs('laporan.setoran') ? 'active' : '' }}">Setoran Sales</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endif

        {{-- SETTING --}}
        <div class="section-title">Setting</div>
        <div>
            <div class="dropdown-trigger {{ $isSettingActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                data-bs-target="#collapseSetting" role="button"
                aria-expanded="{{ $isSettingActive ? 'true' : 'false' }}" aria-controls="collapseSetting">
                <i class="fa-solid fa-gear"></i>
                <span>Setting</span>
                <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
            </div>
            <div class="collapse {{ $isSettingActive ? 'show' : '' }}" id="collapseSetting">
                <div class="submenu-container">
                    @can('view-users')
                        <a href="{{ route('users.index') }}"
                            class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Data Users</a>
                    @endcan
                    @can('view-roles')
                        <a href="{{ route('roles.index') }}"
                            class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">Hak Akses Menu</a>
                    @endcan
                </div>
            </div>
        </div>

    </div>

    <!-- Footer/Logout -->
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
@endif
