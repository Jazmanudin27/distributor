@extends('layouts.mobile')

@section('title', 'Dashboard Sales')

@section('content')
    <!-- Welcome/Profile Section -->
    <div class="d-flex align-items-center mb-4">
        <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center me-3"
            style="width: 50px; height: 50px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
            <i class="fa-solid fa-user-tie text-white" style="font-size: 1.4rem;"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">{{ Auth::user()->name }}</h4>
            <span class="text-secondary" style="font-size: 0.8rem; font-weight: 500;">Sales NIK:
                {{ Auth::user()->nik ?? '-' }}</span>
        </div>
    </div>

    @if(strtolower(Auth::user()->role) === 'spv sales' && isset($pendingCustomersCount) && $pendingCustomersCount > 0)
        <div class="alert alert-warning rounded-4 mb-3 d-flex align-items-center justify-content-between"
            style="background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24;">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-user-check me-3" style="font-size: 1.25rem;"></i>
                <div>
                    <span style="font-size: 0.8rem; font-weight: 600;">Persetujuan Pelanggan Baru</span>
                    <small class="d-block text-secondary" style="font-size: 0.7rem; color: #fbbf24 !important; opacity: 0.85;">Ada {{ $pendingCustomersCount }} pelanggan baru menunggu persetujuan</small>
                </div>
            </div>
            <a href="{{ route('mobile.spv.pelanggan.pending') }}" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-1" style="font-size: 0.75rem; border-radius: 8px; text-decoration: none;">
                Tinjau <i class="fa-solid fa-arrow-right ms-0.5"></i>
            </a>
        </div>
    @endif

    <!-- Pencapaian Bulan Ini Card -->
    <div class="mobile-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-secondary mb-1"
                    style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Total Penjualan Bulan Ini</h6>
                <h3 class="fw-bold mb-0" style="font-size: 1.4rem; color: #818cf8;">Rp
                    {{ number_format($achievedSales, 0, ',', '.') }}</h3>
            </div>
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center"
                style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                <i class="fa-solid fa-chart-line" style="font-size: 1.25rem; color: #818cf8;"></i>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="mobile-card m-0 p-3"
                style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(168, 85, 247, 0.12) 100%); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);">
                <div class="d-flex align-items-center">
                    <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 52px; height: 52px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);">
                        <i class="fa-solid fa-store text-white" style="font-size: 1.4rem;"></i>
                    </div>
                    <div>
                        <h6 class="text-secondary mb-1"
                            style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase;">
                            Kunjungan Hari Ini</h6>
                        <h3 class="fw-bold mb-0 text-white" style="font-size: 1.4rem;">
                            {{ $todayVisitsCount }} <span
                                style="font-size: 0.85rem; font-weight: 500; color: var(--text-secondary);">Outlet</span>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Panel -->
    <h5 class="fw-bold mb-3" style="font-size: 0.95rem; letter-spacing: 0.5px;">Menu Utama</h5>
    @if ($activeCheckin)
        <div class="row g-3 mb-3">
            <div class="col-6">
                <a href="{{ route('mobile.kunjungan.index') }}"
                    class="btn btn-mobile w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100"
                    style="background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <i class="fa-solid fa-store mb-2 text-success" style="font-size: 1.6rem;"></i>
                    <span style="font-size: 0.85rem;">Menu Pelanggan</span>
                </a>
            </div>
            <div class="col-6">
                <a href="{{ route('mobile.order.create') }}"
                    class="btn btn-mobile btn-mobile-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                    <i class="fa-solid fa-cart-plus mb-2" style="font-size: 1.6rem;"></i>
                    <span style="font-size: 0.85rem;">Input Penjualan</span>
                </a>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-12">
                <a href="{{ route('mobile.pelanggan.create') }}"
                    class="btn btn-mobile w-100 py-2.5 d-flex align-items-center justify-content-center"
                    style="background-color: rgba(0, 191, 255, 0.05); border: 1px solid rgba(0, 191, 255, 0.2); color: var(--text-primary);">
                    <i class="fa-solid fa-user-plus me-2 text-info" style="font-size: 1.2rem;"></i>
                    <span style="font-size: 0.85rem;">Pendaftaran Pelanggan Baru</span>
                </a>
            </div>
        </div>

        <div class="alert alert-info rounded-4 mb-4 d-flex align-items-center"
            style="background-color: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); color: #818cf8; margin-top: -8px;">
            <i class="fa-solid fa-circle-info me-3" style="font-size: 1.25rem;"></i>
            <div>
                <span style="font-size: 0.75rem; font-weight: 500; opacity: 0.8;" class="d-block">Kunjungan Aktif:</span>
                <strong style="font-size: 0.85rem;">{{ $activeCheckin->pelanggan->nama_pelanggan }}</strong>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            <div class="col-6">
                <a href="{{ route('mobile.kunjungan.index') }}"
                    class="btn btn-mobile btn-mobile-primary w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100">
                    <i class="fa-solid fa-store mb-2" style="font-size: 1.6rem;"></i>
                    <span style="font-size: 0.85rem;">Menu Pelanggan</span>
                </a>
            </div>
            <div class="col-6">
                <a href="{{ route('mobile.pelanggan.create') }}"
                    class="btn btn-mobile w-100 py-3 d-flex flex-column align-items-center justify-content-center h-100"
                    style="background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <i class="fa-solid fa-user-plus mb-2 text-info" style="font-size: 1.6rem;"></i>
                    <span style="font-size: 0.85rem;">Pelanggan Baru</span>
                </a>
            </div>
        </div>

        <div class="alert alert-warning rounded-4 mb-4 d-flex align-items-center"
            style="background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; margin-top: -8px;">
            <i class="fa-solid fa-triangle-exclamation me-3" style="font-size: 1.25rem;"></i>
            <span style="font-size: 0.80rem; font-weight: 500;">Anda belum check-in. Silakan pilih pelanggan terlebih dahulu
                untuk melakukan penjualan.</span>
        </div>
    @endif

    <!-- Recent Orders Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 0.95rem; letter-spacing: 0.5px;">Pesanan Terbaru Anda</h5>
    </div>

    @if ($recentOrders->isEmpty())
        <div class="mobile-card text-center py-4">
            <i class="fa-solid fa-box-open text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
            <p class="text-secondary mb-0" style="font-size: 0.8rem;">Belum ada pesanan yang diinput bulan ini.</p>
        </div>
    @else
        @foreach ($recentOrders as $order)
            <div class="mobile-card p-3 mb-2"
                style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);">
                <!-- Header: Customer and Transaction Type -->
                <div
                    class="d-flex justify-content-between align-items-start mb-2 border-bottom border-secondary border-opacity-10 pb-2">
                    <div>
                        <h6 class="fw-bold text-white mb-0" style="font-size: 0.9rem;">
                            {{ $order->pelanggan->nama_pelanggan }}
                        </h6>
                        <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                            <i class="fa-solid fa-file-invoice me-1"></i>{{ $order->no_faktur }}
                        </span>
                    </div>
                    <div class="text-end">
                        <span
                            class="badge rounded-pill {{ $order->jenis_transaksi === 'Tunai' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} px-2 py-1"
                            style="font-size: 0.65rem; font-weight: 600;">
                            {{ $order->jenis_transaksi }}
                        </span>
                        <div class="text-secondary mt-1" style="font-size: 0.65rem; font-weight: 500;">
                            <i class="fa-regular fa-calendar me-1"></i>{{ $order->tanggal->format('d M Y') }}
                        </div>
                    </div>
                </div>

                <!-- Info Details: Sales, Region, Address -->
                <div class="mb-2" style="font-size: 0.75rem; line-height: 1.5;">
                    <div class="row g-2 mb-1">
                        <div class="col-4 text-secondary">Sales/User:</div>
                        <div class="col-8 text-end text-white-50">
                            {{ $order->sales ? $order->sales->name : ($order->user ? $order->user->name : Auth::user()->name) }}
                            <span
                                class="text-secondary font-monospace">({{ $order->kode_sales ?? Auth::user()->nik }})</span>
                        </div>
                    </div>
                    <div class="row g-2 mb-1">
                        <div class="col-4 text-secondary">Wilayah:</div>
                        <div class="col-8 text-end text-white-50">
                            {{ $order->pelanggan->wilayah ? $order->pelanggan->wilayah->nama_wilayah : '-' }}
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-3 text-secondary">Alamat:</div>
                        <div class="col-9 text-end text-white-50" style="word-break: break-word;">
                            {{ $order->pelanggan->alamat_pelanggan }}
                        </div>
                    </div>
                </div>

                <!-- Footer: Total Grand Total -->
                <div
                    class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-10 mt-2">
                    <span class="text-secondary" style="font-size: 0.75rem; font-weight: 500;">Grand Total:</span>
                    <span class="fw-bold text-info" style="font-size: 0.9rem;">Rp
                        {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    @endif
@endsection
