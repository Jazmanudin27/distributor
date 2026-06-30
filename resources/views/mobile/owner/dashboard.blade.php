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

        /* Leaderboard styling */
        .leaderboard-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .leaderboard-item:last-child {
            border-bottom: none;
        }
        .leaderboard-rank {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .rank-1 {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #0f172a;
            box-shadow: 0 0 12px rgba(245, 158, 11, 0.4);
        }
        .rank-2 {
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            color: #0f172a;
            box-shadow: 0 0 12px rgba(203, 213, 225, 0.3);
        }
        .rank-3 {
            background: linear-gradient(135deg, #cd7f32 0%, #a0522d 100%);
            color: #ffffff;
            box-shadow: 0 0 12px rgba(160, 82, 45, 0.3);
        }
        .rank-other {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
            border: 1px solid rgba(255, 255, 255, 0.05);
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

    <!-- Filter Kategori Sales -->
    <div class="metric-card p-3 mb-3" style="background: rgba(255, 255, 255, 0.05); border-radius: 16px;">
        <form method="GET" action="{{ url()->current() }}" id="filter-form">
            <div class="d-flex align-items-center justify-content-between">
                <label class="text-secondary mb-0 fw-semibold" style="font-size: 0.8rem;"><i class="fa-solid fa-filter me-1"></i> Kategori Sales:</label>
                <select name="kategori_sales" class="form-select form-select-sm border-0 text-white font-monospace" style="width: auto; background-color: rgba(255,255,255,0.08); font-size: 0.8rem; border-radius: 8px;" onchange="this.form.submit()">
                    <option value="non_canvas" {{ request('kategori_sales', 'non_canvas') === 'non_canvas' ? 'selected' : '' }}>Non-Kanvas</option>
                    <option value="canvas" {{ request('kategori_sales') === 'canvas' ? 'selected' : '' }}>Kanvas</option>
                    <option value="all" {{ request('kategori_sales') === 'all' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Alert / Task Section -->
    <div class="row g-2 mb-4">
        <div class="col-4">
            <a href="{{ route('mobile.owner.pending-approval') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-2 text-center border-opacity-30 {{ $pendingApprovalsCount > 0 ? 'pulse-badge-yellow border-warning' : '' }}"
                    style="background: rgba(245, 158, 11, 0.05); min-height: 95px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="text-warning mb-1"
                        style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
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
                    <div class="text-info mb-1"
                        style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
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
                    <div class="text-danger mb-1"
                        style="font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
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
        <div class="col-4">
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
        <div class="col-4">
            <a href="{{ route('mobile.owner.sales-visits') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-3 text-center border-opacity-30 border-info"
                    style="background: rgba(0, 191, 255, 0.05); min-height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="accent-icon mb-2 rounded-circle"
                        style="width: 38px; height: 38px; background: rgba(0, 191, 255, 0.1); border: 1px solid rgba(0, 191, 255, 0.2);">
                        <i class="fa-solid fa-map-location-dot text-info" style="font-size: 1rem;"></i>
                    </div>
                    <div class="text-info" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">
                        Kunjungan</div>
                    <span class="text-secondary" style="font-size: 0.65rem;">Lokasi</span>
                </div>
            </a>
        </div>
        <!-- 3. Daftar Orderan -->
        <div class="col-4">
            <a href="{{ route('mobile.owner.order.index') }}" class="text-decoration-none">
                <div class="metric-card m-0 p-3 text-center border-opacity-30 border-primary"
                    style="background: rgba(99, 102, 241, 0.05); min-height: 110px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="accent-icon mb-2 rounded-circle"
                        style="width: 38px; height: 38px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                        <i class="fa-solid fa-receipt text-indigo" style="font-size: 1rem; color: #818cf8;"></i>
                    </div>
                    <div class="text-indigo" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; color: #818cf8;">
                        Orderan</div>
                    <span class="text-secondary" style="font-size: 0.65rem;">Daftar Order</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Top Pencapaian Sales -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 0.95rem; letter-spacing: 0.5px; color: var(--text-primary);">Top Pencapaian Sales (Bulan Ini)</h5>
        <a href="{{ route('mobile.owner.sales-achievement') }}" class="text-decoration-none" style="font-size: 0.75rem; color: var(--active-color); font-weight: 600;">Lihat Semua <i class="fa-solid fa-angle-right ms-0.5"></i></a>
    </div>

    <div class="metric-card p-3 mb-4">
        @if (count($topSales) === 0)
            <div class="text-center py-3">
                <p class="text-secondary mb-0" style="font-size: 0.8rem;">Belum ada data penjualan sales bulan ini.</p>
            </div>
        @else
            <div class="d-flex flex-column animate-list">
                @foreach ($topSales as $index => $sales)
                    @php
                        $rank = $index + 1;
                        $rankClass = 'rank-other';
                        if ($rank === 1) $rankClass = 'rank-1';
                        elseif ($rank === 2) $rankClass = 'rank-2';
                        elseif ($rank === 3) $rankClass = 'rank-3';
                    @endphp
                    <div class="leaderboard-item">
                        <div class="d-flex align-items-center">
                            <div class="leaderboard-rank {{ $rankClass }}">
                                @if ($rank === 1)
                                    <i class="fa-solid fa-trophy" style="font-size: 0.75rem;"></i>
                                @else
                                    {{ $rank }}
                                @endif
                            </div>
                            <div>
                                <div class="fw-bold text-white" style="font-size: 0.88rem; line-height: 1.2;">{{ $sales['name'] }}</div>
                                <span class="text-secondary" style="font-size: 0.7rem;">NIK: {{ $sales['nik'] }}</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success" style="font-size: 0.95rem; font-family: monospace;">
                                Rp {{ number_format($sales['total_sales'], 0, ',', '.') }}
                            </div>
                            <span class="text-secondary" style="font-size: 0.7rem;">
                                {{ $sales['invoice_count'] }} Faktur &bull; {{ $sales['visit_count'] }} Kunjungan
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
