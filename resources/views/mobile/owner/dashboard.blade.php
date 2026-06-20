@extends('layouts.mobile')

@section('title', 'Dashboard Owner')

@push('styles')
    <style>
        .metric-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }

        .metric-card:active {
            transform: scale(0.98);
        }

        .metric-title {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .metric-value {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .metric-sub {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .metric-sub strong {
            color: var(--text-primary);
        }

        .accent-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pulse-badge {
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .pulse-badge-yellow {
            animation: pulse-glow-yellow 2s infinite;
        }

        @keyframes pulse-glow-yellow {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.5);
            }

            70% {
                box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            }
        }
    </style>
@endpush

@section('content')
    <!-- Welcome Section -->
    <div class="d-flex align-items-center mb-4">
        <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center me-3"
            style="width: 50px; height: 50px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
            <i class="fa-solid fa-crown text-white" style="font-size: 1.3rem;"></i>
        </div>
        <div>
            <h4 class="mb-0 fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">{{ Auth::user()->name }}</h4>
            <span class="text-secondary" style="font-size: 0.8rem; font-weight: 500;">Role: Owner Panel</span>
        </div>
    </div>

    <!-- Alert / Task Section -->
    <div class="row g-2 mb-4">
        <div class="col-4">
            <a href="{{ route('mobile.owner.pending-approval') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-2 text-center border-opacity-30 {{ $pendingApprovalsCount > 0 ? 'pulse-badge-yellow border-warning' : '' }}"
                    style="background: rgba(245, 158, 11, 0.05); min-height: 95px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="text-warning mb-1" style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        Limit Kredit</div>
                    <h3 class="fw-bold text-white mb-0" style="font-size: 1.15rem;">{{ $pendingApprovalsCount }}</h3>
                    <span class="text-secondary" style="font-size: 0.6rem;">Persetujuan</span>
                </div>
            </a>
        </div>
        <div class="col-4">
            <a href="{{ route('mobile.owner.pending-pelanggan') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-2 text-center border-opacity-30 {{ $pendingPelangganCount > 0 ? 'pulse-badge-yellow border-info' : '' }}"
                    style="background: rgba(0, 191, 255, 0.05); min-height: 95px; display: flex; flex-direction: column; justify-content: center; align-items: center; border-color: rgba(0, 191, 255, 0.3) !important;">
                    <div class="text-info mb-1" style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        Pelanggan Baru</div>
                    <h3 class="fw-bold text-white mb-0" style="font-size: 1.15rem;">{{ $pendingPelangganCount }}</h3>
                    <span class="text-secondary" style="font-size: 0.6rem;">Persetujuan</span>
                </div>
            </a>
        </div>
        <div class="col-4">
            <a href="{{ route('mobile.owner.low-stock') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-2 text-center border-opacity-30 {{ $lowStockCount > 0 ? 'pulse-badge border-danger' : '' }}"
                    style="background: rgba(239, 68, 68, 0.05); min-height: 95px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="text-danger mb-1" style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                        Stok Menipis</div>
                    <h3 class="fw-bold text-white mb-0" style="font-size: 1.15rem;">{{ $lowStockCount }}</h3>
                    <span class="text-secondary" style="font-size: 0.6rem;">Item Produk</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Kinerja & Aktivitas Sales -->
    <h5 class="fw-bold mt-4 mb-3" style="font-size: 0.95rem; letter-spacing: 0.5px; color: var(--text-primary);">Kinerja &
        Aktivitas Sales</h5>

    <div class="row g-3 mb-3">
        <!-- 1. Pencapaian Sales -->
        <div class="col-6">
            <a href="{{ route('mobile.owner.sales-achievement') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-3 text-center border-opacity-30 border-success"
                    style="background: rgba(16, 185, 129, 0.05); min-height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="accent-icon mb-2 rounded-circle"
                        style="width: 38px; height: 38px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                        <i class="fa-solid fa-trophy text-success" style="font-size: 1rem;"></i>
                    </div>
                    <div class="text-success" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">
                        Pencapaian</div>
                    <span class="text-secondary" style="font-size: 0.65rem;">Omzet Sales</span>
                </div>
            </a>
        </div>
        <!-- 2. Kunjungan Sales -->
        <div class="col-6">
            <a href="{{ route('mobile.owner.sales-visits') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-3 text-center border-opacity-30 border-info"
                    style="background: rgba(0, 191, 255, 0.05); min-height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="accent-icon mb-2 rounded-circle"
                        style="width: 38px; height: 38px; background: rgba(0, 191, 255, 0.1); border: 1px solid rgba(0, 191, 255, 0.2);">
                        <i class="fa-solid fa-map-location-dot text-info" style="font-size: 1rem;"></i>
                    </div>
                    <div class="text-info" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">
                        Kunjungan</div>
                    <span class="text-secondary" style="font-size: 0.65rem;">Lokasi & Catatan</span>
                </div>
            </a>
        </div>
    </div>
    <!-- Metrik Keuangan -->
    <h5 class="fw-bold mb-3" style="font-size: 0.95rem; letter-spacing: 0.5px; color: var(--text-primary);">Ringkasan
        Keuangan</h5>

    <!-- 1. Penjualan -->
    <div class="metric-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="metric-title">Penjualan Bersih (Sales)</span>
                <div class="metric-value text-info">Rp {{ number_format($salesMonth, 0, ',', '.') }}</div>
                <div class="metric-sub">Hari Ini: <strong>Rp {{ number_format($salesToday, 0, ',', '.') }}</strong></div>
            </div>
            <div class="accent-icon" style="background: rgba(0, 191, 255, 0.1); border: 1px solid rgba(0, 191, 255, 0.2);">
                <i class="fa-solid fa-file-invoice-dollar text-info" style="font-size: 1.3rem;"></i>
            </div>
        </div>
    </div>

    <!-- 2. Pembelian -->
    <div class="metric-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="metric-title">Pembelian Stok (Purchases)</span>
                <div class="metric-value text-warning">Rp {{ number_format($purchaseMonth, 0, ',', '.') }}</div>
                <div class="metric-sub">Hari Ini: <strong>Rp {{ number_format($purchaseToday, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="accent-icon"
                style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">
                <i class="fa-solid fa-cart-shopping text-warning" style="font-size: 1.3rem;"></i>
            </div>
        </div>
    </div>

    <!-- 3. Setoran Pembayaran Masuk -->
    <div class="metric-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="metric-title">Setoran Tunai/Transfer/Giro</span>
                <div class="metric-value text-success">Rp {{ number_format($paymentsMonth, 0, ',', '.') }}</div>
                <div class="metric-sub">Hari Ini: <strong>Rp {{ number_format($paymentsToday, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="accent-icon"
                style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fa-solid fa-wallet text-success" style="font-size: 1.3rem;"></i>
            </div>
        </div>
    </div>

    <!-- 4. Estimasi Laba Kotor Bulan Ini -->
    <a href="{{ route('mobile.owner.laba-rugi') }}" class="text-decoration-none">
        <div class="metric-card"
            style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(99, 102, 241, 0.15) 100%); border: 1px solid rgba(139, 92, 246, 0.2);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="metric-title text-purple" style="color: #c084fc !important;">Estimasi Laba Kotor Bulan
                        Ini</span>
                    <div class="metric-value text-purple" style="color: #c084fc;">Rp
                        {{ number_format($profitMonth, 0, ',', '.') }}</div>
                    <div class="metric-sub text-white-50">Ketuk untuk analisis laba rugi terperinci <i
                            class="fa-solid fa-arrow-right ms-1"></i></div>
                </div>
                <div class="accent-icon"
                    style="background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.3);">
                    <i class="fa-solid fa-calculator text-purple" style="font-size: 1.3rem; color: #c084fc;"></i>
                </div>
            </div>
        </div>
    </a>

@endsection
