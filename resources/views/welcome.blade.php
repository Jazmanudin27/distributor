@extends('layouts.app')

@section('title', 'Dashboard - Distributor')

@push('styles')
    <style>
        .stat-card {
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        .stat-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .chart-card {
            background: #1A1D27 !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
            border-radius: 16px !important;
        }

        .list-card {
            background: #1A1D27 !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
            border-radius: 16px !important;
            min-height: 380px;
        }

        .avatar-initial {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(108, 99, 255, 0.15);
            color: #a78bfa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-pulse {
            position: relative;
        }

        .badge-pulse::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 6px;
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid p-0">
        <!-- Welcome Row -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-white">Ringkasan Bisnis</h4>
                <p class="text-secondary small mb-0">Selamat datang kembali, <strong
                        class="text-white">{{ Auth::user()->name }}</strong>. Berikut adalah kinerja distributor Anda hari
                    ini.</p>
            </div>
            <div class="text-end text-secondary small">
                <i class="fa-regular fa-calendar-days me-1"></i> {{ date('d M Y') }}
            </div>
        </div>

        <!-- Stat Cards Row -->
        <div class="row g-4 mb-4">
            <!-- Card 1: Penjualan Hari Ini -->
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card stat-card shadow-sm h-100 p-3 bg-gradient"
                    style="background: linear-gradient(135deg, rgba(26, 29, 39, 0.9) 0%, rgba(16, 185, 129, 0.05) 100%);">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <span class="text-secondary small fw-semibold tracking-wider text-uppercase"
                                style="font-size: 0.7rem;">Penjualan Hari Ini</span>
                            <h3 class="fw-bold text-white mt-2 mb-0">Rp
                                {{ number_format($totalPenjualanHariIni, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon-box" style="background-color: rgba(16, 185, 129, 0.15); color: #10b981;">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Penjualan Bulan Ini -->
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card stat-card shadow-sm h-100 p-3"
                    style="background: linear-gradient(135deg, rgba(26, 29, 39, 0.9) 0%, rgba(99, 102, 241, 0.05) 100%);">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <span class="text-secondary small fw-semibold tracking-wider text-uppercase"
                                style="font-size: 0.7rem;">Penjualan Bulan Ini</span>
                            <h3 class="fw-bold text-white mt-2 mb-0">Rp
                                {{ number_format($totalPenjualanBulanIni, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon-box" style="background-color: rgba(99, 102, 241, 0.15); color: #6366f1;">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Card 4: Pembelian Hari Ini -->
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card stat-card shadow-sm h-100 p-3"
                    style="background: linear-gradient(135deg, rgba(26, 29, 39, 0.9) 0%, rgba(245, 158, 11, 0.05) 100%);">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <span class="text-secondary small fw-semibold tracking-wider text-uppercase"
                                style="font-size: 0.7rem;">Pembelian Hari Ini</span>
                            <h3 class="fw-bold text-white mt-2 mb-0">Rp
                                {{ number_format($totalPembelianHariIni, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon-box" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Piutang Outstanding -->
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card stat-card shadow-sm h-100 p-3"
                    style="background: linear-gradient(135deg, rgba(26, 29, 39, 0.9) 0%, rgba(239, 68, 68, 0.05) 100%);">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <span class="text-secondary small fw-semibold tracking-wider text-uppercase"
                                style="font-size: 0.7rem;">Total Outstanding Piutang</span>
                            <h3 class="fw-bold text-white mt-2 mb-0">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon-box" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444;">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-xl-12 col-md-12 col-12">
                <div class="card stat-card shadow-sm h-100 p-3"
                    style="background: linear-gradient(135deg, rgba(26, 29, 39, 0.9) 0%, rgba(236, 72, 153, 0.05) 100%);">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-secondary small fw-semibold tracking-wider text-uppercase"
                                style="font-size: 0.7rem;">Target Penjualan Bulan Ini</span>
                            <h3 class="fw-bold text-white mt-1 mb-0">
                                Rp {{ number_format($targetPenjualan, 0, ',', '.') }}
                                <a href="#" class="ms-1" data-bs-toggle="modal" data-bs-target="#editTargetModal"
                                    title="Ubah Target" style="color: #ec4899;">
                                    <i class="fa-solid fa-pen-to-square" style="font-size: 0.85rem;"></i>
                                </a>
                            </h3>
                        </div>
                        <div class="stat-icon-box" style="background-color: rgba(236, 72, 153, 0.15); color: #ec4899;">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                    </div>
                    <div class="mt-2 border-top border-white-10 pt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Progres Pencapaian</span>
                            <span class="fw-semibold small"
                                style="color: #ec4899;">{{ number_format($progressPenjualan, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 6px; background-color: rgba(255, 255, 255, 0.1);">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                style="width: {{ min(100, $progressPenjualan) }}%; background-color: #ec4899; transition: width 0.8s ease;"
                                aria-valuenow="{{ $progressPenjualan }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <!-- Monthly Sales Chart -->
            <div class="col-lg-8">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center bg-transparent py-3">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="fa-solid fa-chart-column text-primary me-2"></i>Tren Penjualan 6 Bulan Terakhir
                        </h6>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px; position: relative;">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Salesmen Chart -->
            <div class="col-lg-4">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center bg-transparent py-3">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="fa-solid fa-crown text-warning me-2"></i>Top Sales Bulan Ini
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div style="height: 220px; position: relative;" class="mb-3">
                            @if ($topSalesmen->isEmpty())
                                <div class="h-100 d-flex align-items-center justify-content-center text-secondary small">
                                    Belum ada penjualan di bulan ini.
                                </div>
                            @else
                                <canvas id="topSalesmenChart"></canvas>
                            @endif
                        </div>
                        <div class="small text-secondary text-center">
                            Penjualan dihitung berdasarkan faktur aktif yang lunas maupun kredit.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="row g-4">
            <!-- Salesmen Checked-in Today -->
            <div class="col-xl-4 col-lg-6">
                <div class="card list-card shadow-sm">
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="fa-solid fa-location-dot text-danger me-2"></i>Sales Aktif Hari Ini
                        </h6>
                        <span class="badge bg-success-subtle text-success badge-pulse">{{ $activeCheckins->count() }}
                            Online</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                        @if ($activeCheckins->isEmpty())
                            <div class="p-4 text-center text-secondary small">
                                <i class="fa-solid fa-map-location-dot d-block fs-3 mb-2 opacity-50"></i>
                                Belum ada sales yang check-in hari ini.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($activeCheckins as $checkin)
                                    <div class="list-group-item bg-transparent border-white-10 py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial me-3">
                                                {{ strtoupper(substr($checkin->sales->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between">
                                                    <span
                                                        class="fw-bold text-white small">{{ $checkin->sales->name ?? 'Sales' }}</span>
                                                    <span class="text-secondary font-monospace"
                                                        style="font-size: 0.7rem;">
                                                        {{ date('H:i', strtotime($checkin->checkin)) }}
                                                    </span>
                                                </div>
                                                <p class="mb-0 text-secondary" style="font-size: 0.75rem;">
                                                    Kunjungan: <strong
                                                        class="text-light-indigo">{{ $checkin->pelanggan->nama_pelanggan ?? 'Toko' }}</strong>
                                                </p>
                                                @if ($checkin->catatan)
                                                    <span class="text-muted italic"
                                                        style="font-size: 0.7rem;">"{{ Str::limit($checkin->catatan, 35) }}"</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-xl-4 col-lg-6">
                <div class="card list-card shadow-sm">
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Stok Menipis (Kritis)
                        </h6>
                        @if ($lowStockCount > 0)
                            <span class="badge bg-danger-subtle text-danger">{{ $lowStockCount }} Item</span>
                        @endif
                    </div>
                    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                        @if ($lowStockItems->isEmpty())
                            <div class="p-4 text-center text-secondary small">
                                <i class="fa-solid fa-circle-check d-block fs-3 mb-2 text-success opacity-75"></i>
                                Semua stok barang dalam kondisi aman.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($lowStockItems as $item)
                                    @php
                                        $satuan = $item->satuans->sortBy('isi')->first();
                                        $satuanName = $satuan ? $satuan->satuan : 'PCS';
                                    @endphp
                                    <div
                                        class="list-group-item bg-transparent border-white-10 py-3 px-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <span
                                                class="fw-semibold text-white small d-block">{{ $item->nama_barang }}</span>
                                            <small class="text-secondary">{{ $item->kode_barang }}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-danger text-white fw-bold">{{ $item->stok }}
                                                {{ $satuanName }}</span>
                                            <small class="d-block text-secondary small"
                                                style="font-size: 0.7rem;">Kritis</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Latest Credit Limit Requests -->
            <div class="col-xl-4 col-lg-12">
                <div class="card list-card shadow-sm">
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-white mb-0">
                            <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Pengajuan Limit Kredit
                        </h6>
                        @if ($pendingApprovalKredit > 0)
                            <span class="badge bg-warning-subtle text-warning">{{ $pendingApprovalKredit }} Butuh
                                Approval</span>
                        @endif
                    </div>
                    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                        @if ($latestLimitRequests->isEmpty())
                            <div class="p-4 text-center text-secondary small">
                                <i class="fa-solid fa-circle-check d-block fs-3 mb-2 text-success opacity-75"></i>
                                Belum ada pengajuan limit kredit.
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($latestLimitRequests as $req)
                                    <div class="list-group-item bg-transparent border-white-10 py-3 px-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span
                                                    class="fw-semibold text-white small d-block">{{ $req->pelanggan->nama_pelanggan ?? 'Toko' }}</span>
                                                <small class="text-secondary">Diajukan oleh:
                                                    {{ $req->requester->name ?? 'Sales' }}</small>
                                                <div class="mt-1" style="font-size: 0.75rem;">
                                                    <span class="text-secondary">Rp
                                                        {{ number_format($req->limit_lama, 0, ',', '.') }}</span>
                                                    <i class="fa-solid fa-arrow-right mx-1 text-muted"></i>
                                                    <strong class="text-success">Rp
                                                        {{ number_format($req->limit_baru, 0, ',', '.') }}</strong>
                                                </div>
                                            </div>
                                            <span
                                                class="badge bg-{{ $req->status_color }}-subtle text-{{ $req->status_color }} fs-8">
                                                {{ $req->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Target -->
    <div class="modal fade" id="editTargetModal" tabindex="-1" aria-labelledby="editTargetModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-white rounded-4 shadow-lg"
                style="background: #1A1D27; border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="modal-header py-3" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <h5 class="modal-title fw-bold" id="editTargetModalLabel">
                        <i class="fa-solid fa-bullseye text-primary me-2"></i>Ubah Target Penjualan Bulan Ini
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('dashboard.set-target') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small">Nominal Target Penjualan
                                (Rupiah)</label>
                            <div class="input-group">
                                <span class="input-group-text text-secondary"
                                    style="background: #151821; border: 1px solid rgba(255, 255, 255, 0.1);">Rp</span>
                                <input type="number" name="target_penjualan" class="form-control text-white"
                                    style="background: #151821; border: 1px solid rgba(255, 255, 255, 0.1);"
                                    value="{{ (int) $targetPenjualan }}" min="0" required
                                    placeholder="Contoh: 5000000000">
                            </div>
                            <div class="form-text text-secondary mt-2" style="font-size: 0.75rem;">
                                Target saat ini: <strong class="text-white">Rp
                                    {{ number_format($targetPenjualan, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-3" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-3"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3">Simpan Target</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // 1. Monthly Sales Chart
            const ctxMonthly = document.getElementById('monthlySalesChart');
            if (ctxMonthly) {
                const months = @json($monthlySales->pluck('month_name'));
                const salesValues = @json($monthlySales->pluck('total'));

                new Chart(ctxMonthly, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Total Penjualan (Rp)',
                            data: salesValues,
                            borderColor: '#6C63FF',
                            backgroundColor: 'rgba(108, 99, 255, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#6C63FF',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let value = context.raw;
                                        return ' Penjualan: Rp ' + Number(value).toLocaleString(
                                            'id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.05)'
                                },
                                ticks: {
                                    color: '#94A3B8',
                                    font: {
                                        size: 10
                                    },
                                    callback: function(value) {
                                        if (value >= 1e6) return (value / 1e6) + ' Jt';
                                        return value;
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#94A3B8',
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Top Salesmen Chart
            const ctxSalesmen = document.getElementById('topSalesmenChart');
            if (ctxSalesmen) {
                const names = @json($topSalesmen->pluck('name'));
                const totalValues = @json($topSalesmen->pluck('total'));

                new Chart(ctxSalesmen, {
                    type: 'doughnut',
                    data: {
                        labels: names,
                        datasets: [{
                            data: totalValues,
                            backgroundColor: [
                                '#6C63FF', // Indigo
                                '#10B981', // Emerald
                                '#F59E0B', // Amber
                                '#EF4444', // Red
                                '#06B6D4' // Cyan
                            ],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#94A3B8',
                                    font: {
                                        size: 10
                                    },
                                    boxWidth: 10
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        let value = context.raw;
                                        return ' ' + label + ': Rp ' + Number(value).toLocaleString(
                                            'id-ID');
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        });
    </script>
@endpush
