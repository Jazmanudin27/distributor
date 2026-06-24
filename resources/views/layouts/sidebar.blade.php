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

    $isTxPenjualanActive =
        request()->routeIs('penjualan.*') ||
        request()->routeIs('penjualan-kiriman.*') ||
        request()->routeIs('retur-penjualan.*');

    $isTxPembelianActive = request()->routeIs('pembelian.*') || request()->routeIs('retur-pembelian.*');

    $isTxGudangActive = request()->routeIs('stok-opname.*') || request()->routeIs('canvas.*');

    $isTxKeuanganActive =
        request()->routeIs('ajuan-limit-kredit.*') ||
        request()->routeIs('pembayaran.pending.*') ||
        request()->routeIs('kas-bank.*');

    $isSalesActive = request()->routeIs('sales-tracking.*');

    $isLapPenjualanActive =
        request()->routeIs('laporan.penjualan') ||
        request()->routeIs('laporan.retur-penjualan') ||
        request()->routeIs('laporan.setoran');

    $isLapPembelianActive = request()->routeIs('laporan.pembelian') || request()->routeIs('laporan.retur-pembelian');

    $isLapGudangActive = request()->routeIs('laporan.stok');

    $isLapKeuanganActive =
        request()->routeIs('laporan.piutang') ||
        request()->routeIs('laporan.rekap-sisa-piutang') ||
        request()->routeIs('laporan.pembayaran_piutang') ||
        request()->routeIs('laporan.laba-rugi');

    $isSettingActive = request()->routeIs('users.*') || request()->routeIs('roles.*');
@endphp

@if ($user)
    <!-- Tenant Badge (Avatar and Info) -->
    <div class="tenant-badge-custom">
        <a href="{{ route('profile.edit') }}" style="text-decoration: none; display: block;">
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
        </a>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">

        <!-- UTAMA -->
        <div class="section-title">Utama</div>

        <a href="{{ url('/') }}" class="{{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-table-cells-large"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            <i class="fa-solid fa-user-gear"></i>
            <span>Profil Saya</span>
        </a>

        <!-- MASTER -->
        @if (
            $user->can('view-kategori') ||
                $user->can('view-merk') ||
                $user->can('view-supplier') ||
                $user->can('view-barang') ||
                $user->can('view-satuan') ||
                $user->can('view-pelanggan') ||
                $user->can('view-diskon_strata'))
            <div class="section-title">Master</div>
            <div>
                <div class="dropdown-trigger {{ $isMasterActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                    data-bs-target="#collapseMaster" role="button"
                    aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}" aria-controls="collapseMaster">
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
                                class="{{ request()->routeIs('pelanggan.*') && !request()->routeIs('pelanggan.map') ? 'active' : '' }} d-flex justify-content-between align-items-center">
                                <span>Data Pelanggan</span>
                                @if ($pendingPelangganCount > 0)
                                    <span class="badge bg-danger text-light rounded-pill"
                                        style="font-size: 0.7rem; padding: 0.25em 0.6em;">{{ $pendingPelangganCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('pelanggan.map') }}"
                                class="{{ request()->routeIs('pelanggan.map') ? 'active' : '' }}">Mapping Pelanggan</a>
                        @endcan
                        @can('view-diskon_strata')
                            <a href="{{ route('diskon-strata.index') }}"
                                class="{{ request()->routeIs('diskon-strata.*') ? 'active' : '' }}">Diskon Strata</a>
                        @endcan
                    </div>
                </div>
            </div>
        @endif

        <!-- TRANSAKSI -->
        @if (
            $user->can('view-penjualan') ||
                $user->can('view-retur_penjualan') ||
                $user->can('view-penjualan_kiriman') ||
                $user->can('view-pembelian') ||
                $user->can('view-retur_pembelian') ||
                $user->can('view-stok_opname') ||
                $user->can('view-ajuan_limit_kredit') ||
                $user->hasRole('Super Admin') ||
                $user->hasRole('Admin') ||
                $user->can('view-kas_bank'))
            <div class="section-title">Transaksi</div>

            <!-- PENJUALAN -->
            @if (
                $user->can('view-penjualan') ||
                    $user->can('view-retur_penjualan') ||
                    $user->can('view-penjualan_kiriman') ||
                    $user->hasRole('Super Admin') ||
                    $user->hasRole('Admin'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isTxPenjualanActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#collapseTxPenjualan" role="button"
                        aria-expanded="{{ $isTxPenjualanActive ? 'true' : 'false' }}"
                        aria-controls="collapseTxPenjualan">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Penjualan</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isTxPenjualanActive ? 'show' : '' }}" id="collapseTxPenjualan">
                        <div class="submenu-container">
                            @can('view-penjualan')
                                <a href="{{ route('penjualan.index') }}"
                                    class="{{ request()->routeIs('penjualan.*') ? 'active' : '' }}">Penjualan</a>
                            @endcan
                            @can('view-penjualan_kiriman')
                                <a href="{{ route('penjualan-kiriman.index') }}"
                                    class="{{ request()->routeIs('penjualan-kiriman.*') ? 'active' : '' }}">Kiriman
                                    Penjualan</a>
                            @endcan
                            @can('view-retur_penjualan')
                                <a href="{{ route('retur-penjualan.index') }}"
                                    class="{{ request()->routeIs('retur-penjualan.*') ? 'active' : '' }}">Retur
                                    Penjualan</a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif

            <!-- PEMBELIAN -->
            @if ($user->can('view-pembelian') || $user->can('view-retur_pembelian'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isTxPembelianActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#collapseTxPembelian" role="button"
                        aria-expanded="{{ $isTxPembelianActive ? 'true' : 'false' }}"
                        aria-controls="collapseTxPembelian">
                        <i class="fa-solid fa-truck"></i>
                        <span>Pembelian</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isTxPembelianActive ? 'show' : '' }}" id="collapseTxPembelian">
                        <div class="submenu-container">
                            @can('view-pembelian')
                                <a href="{{ route('pembelian.index') }}"
                                    class="{{ request()->routeIs('pembelian.*') ? 'active' : '' }}">Pembelian</a>
                            @endcan
                            @can('view-retur_pembelian')
                                <a href="{{ route('retur-pembelian.index') }}"
                                    class="{{ request()->routeIs('retur-pembelian.*') ? 'active' : '' }}">Retur
                                    Pembelian</a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif

            <!-- GUDANG / STOK -->
            @if (
                $user->can('view-stok_opname') ||
                    $user->hasRole('Super Admin') ||
                    $user->hasRole('Admin') ||
                    $user->can('view-canvas'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isTxGudangActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        data-bs-target="#collapseTxGudang" role="button"
                        aria-expanded="{{ $isTxGudangActive ? 'true' : 'false' }}" aria-controls="collapseTxGudang">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Gudang / Stok</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isTxGudangActive ? 'show' : '' }}" id="collapseTxGudang">
                        <div class="submenu-container">
                            @can('view-stok_opname')
                                <a href="{{ route('stok-opname.index') }}"
                                    class="{{ request()->routeIs('stok-opname.*') ? 'active' : '' }}">Stok Opname</a>
                            @endcan
                            @if ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('view-canvas'))
                                <a href="{{ route('canvas.index') }}"
                                    class="{{ request()->routeIs('canvas.*') ? 'active' : '' }}">DPB </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- KEUANGAN -->
            @if (
                $user->can('view-ajuan_limit_kredit') ||
                    $user->hasRole('Super Admin') ||
                    $user->hasRole('Admin') ||
                    $user->can('view-kas_bank'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isTxKeuanganActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#collapseTxKeuangan" role="button"
                        aria-expanded="{{ $isTxKeuanganActive ? 'true' : 'false' }}"
                        aria-controls="collapseTxKeuangan">
                        <i class="fa-solid fa-wallet"></i>
                        <span>Keuangan</span>
                        @if ($totalTransaksiPending > 0)
                            <span class="badge bg-danger text-light rounded-pill ms-2"
                                style="font-size: 0.65rem; padding: 0.25em 0.55em;">{{ $totalTransaksiPending }}</span>
                        @endif
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isTxKeuanganActive ? 'show' : '' }}" id="collapseTxKeuangan">
                        <div class="submenu-container">
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
                                    <span>Pembayaran Pending</span>
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
            @endif
        @endif

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

        <!-- LAPORAN -->
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

            <!-- LAPORAN PENJUALAN -->
            @if (
                $user->can('view-laporan_penjualan') ||
                    $user->can('view-laporan_retur_penjualan') ||
                    $user->can('view-laporan_setoran'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isLapPenjualanActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#collapseLapPenjualan" role="button"
                        aria-expanded="{{ $isLapPenjualanActive ? 'true' : 'false' }}"
                        aria-controls="collapseLapPenjualan">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Penjualan</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isLapPenjualanActive ? 'show' : '' }}" id="collapseLapPenjualan">
                        <div class="submenu-container">
                            @can('view-laporan_penjualan')
                                <a href="{{ route('laporan.penjualan') }}"
                                    class="{{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">Penjualan</a>
                            @endcan
                            @can('view-laporan_retur_penjualan')
                                <a href="{{ route('laporan.retur-penjualan') }}"
                                    class="{{ request()->routeIs('laporan.retur-penjualan') ? 'active' : '' }}">Retur
                                    Penjualan</a>
                            @endcan
                            @can('view-laporan_setoran')
                                <a href="{{ route('laporan.setoran') }}"
                                    class="{{ request()->routeIs('laporan.setoran') ? 'active' : '' }}">Setoran Sales</a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif

            <!-- LAPORAN PEMBELIAN -->
            @if ($user->can('view-laporan_pembelian') || $user->can('view-laporan_retur_pembelian'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isLapPembelianActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#collapseLapPembelian" role="button"
                        aria-expanded="{{ $isLapPembelianActive ? 'true' : 'false' }}"
                        aria-controls="collapseLapPembelian">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Pembelian</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isLapPembelianActive ? 'show' : '' }}" id="collapseLapPembelian">
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
                        </div>
                    </div>
                </div>
            @endif

            <!-- LAPORAN GUDANG -->
            @can('view-laporan_stok')
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isLapGudangActive ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        data-bs-target="#collapseLapGudang" role="button"
                        aria-expanded="{{ $isLapGudangActive ? 'true' : 'false' }}" aria-controls="collapseLapGudang">
                        <i class="fa-solid fa-warehouse"></i>
                        <span>Gudang / Stok</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isLapGudangActive ? 'show' : '' }}" id="collapseLapGudang">
                        <div class="submenu-container">
                            <a href="{{ route('laporan.stok') }}"
                                class="{{ request()->routeIs('laporan.stok') ? 'active' : '' }}">Stok Barang</a>
                        </div>
                    </div>
                </div>
            @endcan

            <!-- LAPORAN KEUANGAN -->
            @if ($user->can('view-laporan_piutang') || $user->can('view-laporan_laba_rugi'))
                <div class="mb-2">
                    <div class="dropdown-trigger {{ $isLapKeuanganActive ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#collapseLapKeuangan" role="button"
                        aria-expanded="{{ $isLapKeuanganActive ? 'true' : 'false' }}"
                        aria-controls="collapseLapKeuangan">
                        <i class="fa-solid fa-coins"></i>
                        <span>Keuangan</span>
                        <i class="fa-solid fa-chevron-down ms-auto chevron" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="collapse {{ $isLapKeuanganActive ? 'show' : '' }}" id="collapseLapKeuangan">
                        <div class="submenu-container">
                            @can('view-laporan_piutang')
                                <a href="{{ route('laporan.piutang') }}"
                                    class="{{ request()->routeIs('laporan.piutang') ? 'active' : '' }}">Piutang
                                    Pelanggan</a>
                                <a href="{{ route('laporan.rekap-sisa-piutang') }}"
                                    class="{{ request()->routeIs('laporan.rekap-sisa-piutang') ? 'active' : '' }}">Rekap
                                    Tagihan</a>
                                <a href="{{ route('laporan.pembayaran_piutang') }}"
                                    class="{{ request()->routeIs('laporan.pembayaran_piutang') ? 'active' : '' }}">Pembayaran
                                    Piutang</a>
                            @endcan
                            @can('view-laporan_laba_rugi')
                                <a href="{{ route('laporan.laba-rugi') }}"
                                    class="{{ request()->routeIs('laporan.laba-rugi') ? 'active' : '' }}">Laba Rugi</a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- SETTING --}}
        @if ($user->can('view-users') || $user->can('view-roles'))
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
        @endif

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
