@extends('layouts.mobile')

@section('title', 'Laporan Laba Rugi')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.owner.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Laporan Laba Rugi</h5>
    </div>

    <!-- Date Range Filter Form -->
    <div class="mobile-card p-3 mb-4">
        <form action="{{ route('mobile.owner.laba-rugi') }}" method="GET">
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

    <!-- P&L Table Summary Cards -->
    <div class="mobile-card p-3">
        <div class="pb-2 mb-3 border-bottom border-secondary border-opacity-10 text-center">
            <span class="text-secondary uppercase" style="font-size: 0.65rem; letter-spacing: 1.5px; font-weight: 600;">Laba
                Rugi Kotor</span>
            <h2 class="fw-bold text-white mb-0" style="font-size: 1.6rem; color: #818cf8 !important;">
                Rp {{ number_format($profit, 0, ',', '.') }}
            </h2>
            <span
                class="badge rounded-pill bg-purple bg-opacity-20 text-purple border border-purple border-opacity-30 px-2.5 py-1 mt-1.5"
                style="font-size: 0.72rem; color: #c084fc !important; border-color: rgba(192, 132, 252, 0.3) !important;">
                Margin: {{ number_format($marginPercent, 2, ',', '.') }}%
            </span>
        </div>

        <div style="font-size: 0.82rem;">
            <!-- PENDAPATAN -->
            <div class="text-secondary small fw-semibold uppercase mb-1.5"
                style="font-size: 0.65rem; letter-spacing: 0.5px;">I. Pendapatan Penjualan</div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-white-50">Penjualan Kotor</span>
                <span class="text-white font-monospace">Rp {{ number_format($salesGross, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-5">
                <span class="text-white-50">Retur Penjualan</span>
                <span class="text-danger font-monospace">({{ number_format($salesReturn, 0, ',', '.') }})</span>
            </div>
            <div class="d-flex justify-content-between mb-3 fw-bold">
                <span class="text-info">Penjualan Bersih</span>
                <span class="text-info font-monospace">Rp {{ number_format($salesNet, 0, ',', '.') }}</span>
            </div>

            <!-- HPP -->
            <div class="text-secondary small fw-semibold uppercase mt-3 mb-1.5"
                style="font-size: 0.65rem; letter-spacing: 0.5px;">II. Harga Pokok Penjualan</div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-white-50">HPP Penjualan</span>
                <span class="text-white font-monospace">Rp {{ number_format($hppGross, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-5">
                <span class="text-white-50">HPP Retur Penjualan</span>
                <span class="text-success font-monospace">({{ number_format($hppReturn, 0, ',', '.') }})</span>
            </div>
            <div class="d-flex justify-content-between mb-3 fw-bold">
                <span class="text-warning">HPP Bersih (Net COGS)</span>
                <span class="text-warning font-monospace">Rp {{ number_format($hppNet, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
@endsection
