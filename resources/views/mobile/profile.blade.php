@extends('layouts.mobile')

@section('title', 'Profil Sales')

@section('content')
    <h5 class="fw-bold mb-3" style="font-size: 1.1rem; letter-spacing: 0.5px;">Profil Saya</h5>

    <!-- Profile Info Card -->
    <div class="mobile-card text-center py-4">
        <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
            style="width: 75px; height: 75px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
            <i class="fa-solid fa-user-tie text-white" style="font-size: 2.2rem;"></i>
        </div>
        <h4 class="fw-bold text-white mb-1" style="font-size: 1.25rem;">{{ $user->name }}</h4>
        <span class="badge rounded-pill bg-dark border border-secondary px-3 py-1 mb-3 text-secondary font-monospace" style="font-size: 0.75rem;">
            NIK: {{ $user->nik ?? '-' }}
        </span>
        <div class="text-secondary small" style="font-size: 0.8rem;">
            <div class="mb-1"><i class="fa-solid fa-envelope me-2"></i>{{ $user->email }}</div>
            <div><i class="fa-solid fa-user-shield me-2"></i>Username: {{ $user->username }}</div>
        </div>
    </div>

    @php
        $userRole = strtolower(Auth::user()->role ?? '');
        $isOwner = in_array($userRole, ['owner', 'admin', 'super admin', 'superadmin']);
    @endphp

    @if (!$isOwner)
        <!-- Monthly Sales Status -->
        <div class="mobile-card">
            <h6 class="text-secondary mb-2" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Total Penjualan Bulan Ini</h6>
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="fw-bold mb-0 text-white" style="font-size: 1.3rem;">
                    Rp {{ number_format($achievedSales, 0, ',', '.') }}
                </h3>
                <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                    <i class="fa-solid fa-chart-line" style="font-size: 1.1rem; color: #818cf8;"></i>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="mobile-card m-0 p-3 text-center">
                    <div class="text-secondary mb-1" style="font-size: 0.75rem;">Total Kunjungan</div>
                    <h4 class="fw-bold text-white mb-0" style="font-size: 1.2rem;">{{ $totalVisitsCount }}</h4>
                </div>
            </div>
            <div class="col-6">
                <div class="mobile-card m-0 p-3 text-center">
                    <div class="text-secondary mb-1" style="font-size: 0.75rem;">Total Penjualan</div>
                    <h4 class="fw-bold text-white mb-0" style="font-size: 1.2rem;">{{ $totalOrdersCount }}</h4>
                </div>
            </div>
        </div>

        <!-- Quick Menu Links for Sales -->
        <div class="mobile-card p-2">
            <a href="{{ route('mobile.order.index') }}" class="d-flex justify-content-between align-items-center p-3 text-white text-decoration-none border-bottom border-secondary border-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-clock-rotate-left text-info me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Histori Penjualan</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
            <a href="{{ route('mobile.limit-kredit.index') }}" class="d-flex justify-content-between align-items-center p-3 text-white text-decoration-none border-bottom border-secondary border-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-file-invoice-dollar text-purple me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Pengajuan Limit Kredit</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
            <a href="#" onclick="confirmLogout(event)" class="d-flex justify-content-between align-items-center p-3 text-danger text-decoration-none">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-right-from-bracket me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Logout / Keluar</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
        </div>
    @else
        <!-- Quick Menu Links for Owner -->
        <div class="mobile-card p-2">
            <a href="{{ route('mobile.owner.dashboard') }}" class="d-flex justify-content-between align-items-center p-3 text-white text-decoration-none border-bottom border-secondary border-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-chart-pie text-info me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Dashboard Owner</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
            <a href="{{ route('mobile.owner.laba-rugi') }}" class="d-flex justify-content-between align-items-center p-3 text-white text-decoration-none border-bottom border-secondary border-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-calculator text-success me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Laporan Laba Rugi</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
            <a href="{{ route('mobile.owner.pending-approval') }}" class="d-flex justify-content-between align-items-center p-3 text-white text-decoration-none border-bottom border-secondary border-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-signature text-warning me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Persetujuan Limit Kredit</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
            <a href="{{ route('mobile.owner.low-stock') }}" class="d-flex justify-content-between align-items-center p-3 text-white text-decoration-none border-bottom border-secondary border-opacity-10">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-boxes-stacked text-danger me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Produk Stok Menipis</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
            <a href="#" onclick="confirmLogout(event)" class="d-flex justify-content-between align-items-center p-3 text-danger text-decoration-none">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-right-from-bracket me-3" style="font-size: 1.2rem; width: 20px;"></i>
                    <span style="font-size: 0.85rem; font-weight: 500;">Logout / Keluar</span>
                </div>
                <i class="fa-solid fa-chevron-right text-secondary fs-8"></i>
            </a>
        </div>
    @endif
@endsection
