@extends('layouts.mobile')

@section('title', 'Laporan Pencapaian Sales')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Pencapaian Sales</h5>
    </div>

    <!-- Date Range Filter Form -->
    <div class="mobile-card p-3 mb-4">
        <form action="{{ route('mobile.spv.sales-achievement') }}" method="GET">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label text-secondary small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" class="form-control form-control-mobile py-2"
                        value="{{ $tanggal_mulai }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" class="form-control form-control-mobile py-2"
                        value="{{ $tanggal_akhir }}" required>
                </div>
            </div>
            <button type="submit" class="btn btn-mobile btn-mobile-primary w-100 py-2 fw-semibold fs-8.5">
                <i class="fa-solid fa-filter me-1.5"></i> Terapkan Filter
            </button>
        </form>
    </div>

    <!-- Sales Leaderboard/Achievement List -->
    @if (count($achievements) === 0)
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                <i class="fa-solid fa-users-slash text-secondary" style="font-size: 1.7rem;"></i>
            </div>
            <h6 class="fw-bold text-white-50">Tidak Ada Data Sales</h6>
            <p class="text-secondary small">Tidak ada akun sales aktif yang ditemukan.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach ($achievements as $index => $row)
                @php
                    $rank = $index + 1;
                    $rankClass = 'bg-secondary text-white';
                    $rankGlow = 'rgba(255, 255, 255, 0.05)';
                    if ($rank === 1) {
                        $rankClass = 'bg-warning text-dark';
                        $rankGlow = 'rgba(245, 158, 11, 0.2)';
                    } elseif ($rank === 2) {
                        $rankClass = 'bg-light text-dark';
                        $rankGlow = 'rgba(241, 245, 249, 0.2)';
                    } elseif ($rank === 3) {
                        $rankClass = 'bg-danger text-white';
                        $rankGlow = 'rgba(239, 68, 68, 0.2)';
                    }
                @endphp

                <div class="mobile-card p-3 mb-0">
                    <div
                        class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div class="d-flex align-items-center">
                            <!-- Rank Badge -->
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-2.5"
                                style="width: 26px; height: 26px; font-size: 0.8rem; background: {{ $rankGlow }}; border: 1px solid rgba(255, 255, 255, 0.1);">
                                <span
                                    class="badge rounded-circle {{ $rankClass }} p-0 d-flex align-items-center justify-content-center"
                                    style="width: 20px; height: 20px;">
                                    {{ $rank }}
                                </span>
                            </div>
                            <div>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">{{ $row['name'] }}</h6>
                                <span class="text-secondary font-monospace" style="font-size: 0.7rem;">NIK:
                                    {{ $row['nik'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Achievement metrics -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary uppercase d-block mb-0.5"
                                style="font-size: 0.62rem; letter-spacing: 0.5px; font-weight: 500;">Pencapaian
                                Penjualan</span>
                            <h5 class="fw-bold text-success mb-0 font-monospace" style="font-size: 1.15rem;">
                                Rp {{ number_format($row['total_sales'], 0, ',', '.') }}
                            </h5>
                        </div>
                        <div class="text-end">
                            <span class="text-secondary d-block" style="font-size: 0.75rem;">
                                Order: <strong class="text-white">{{ $row['invoice_count'] }} Faktur</strong>
                            </span>
                            <span class="text-secondary d-block" style="font-size: 0.75rem;">
                                Kunjungan: <strong class="text-white">{{ $row['visit_count'] }} Kali</strong>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
