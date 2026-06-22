@extends('layouts.mobile')

@section('title', 'Laporan Kunjungan Sales')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Kunjungan Sales</h5>
    </div>

    <!-- Filter Form -->
    <div class="mobile-card p-3 mb-4">
        <form action="{{ route('mobile.spv.sales-visits') }}" method="GET">
            <div class="row g-2 mb-2">
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
            <div class="mb-3">
                <label class="form-label text-secondary small fw-semibold mb-1">Filter Sales</label>
                <select name="kode_sales" class="form-control form-control-mobile py-2">
                    <option value="">-- Semua Sales --</option>
                    @foreach($salesmen as $sales)
                        <option value="{{ $sales->nik }}" {{ $selected_sales == $sales->nik ? 'selected' : '' }}>
                            {{ $sales->name }} ({{ $sales->nik }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-mobile btn-mobile-primary w-100 py-2 fw-semibold fs-8.5">
                <i class="fa-solid fa-filter me-1.5"></i> Terapkan Filter
            </button>
        </form>
    </div>

    <!-- Visits List -->
    @if($visits->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                <i class="fa-solid fa-map-location-dot text-secondary" style="font-size: 1.7rem;"></i>
            </div>
            <h6 class="fw-bold text-white-50">Tidak Ada Kunjungan</h6>
            <p class="text-secondary small">Tidak ditemukan riwayat kunjungan sales untuk filter ini.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($visits as $visit)
                <div class="mobile-card p-3 mb-0">
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">
                                {{ $visit->pelanggan->nama_pelanggan ?? 'Pelanggan Terhapus' }}
                            </h6>
                            <span class="text-secondary small" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-map-marker-alt me-1 text-danger"></i>
                                {{ $visit->pelanggan->wilayah->nama_wilayah ?? '-' }}
                            </span>
                        </div>
                        <span class="badge rounded-pill bg-dark border border-secondary border-opacity-25 px-2.5 py-1 text-white-50" style="font-size: 0.68rem;">
                            {{ $visit->tanggal->format('d M Y') }}
                        </span>
                    </div>

                    <div style="font-size: 0.8rem;" class="mb-2">
                        <div class="mb-1 text-white-50">
                            Sales: <strong class="text-white">{{ $visit->sales->name ?? 'Sales Terhapus' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Check-in: <strong class="text-info font-monospace">{{ $visit->checkin->format('H:i') }}</strong></span>
                            <span>
                                Check-out: 
                                @if($visit->checkout)
                                    <strong class="text-success font-monospace">{{ $visit->checkout->format('H:i') }}</strong>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-1.5 py-0.5" style="font-size: 0.65rem;">
                                        Aktif
                                    </span>
                                @endif
                            </span>
                        </div>
                        @if($visit->checkout)
                            @php
                                $diffMin = $visit->checkin->diffInMinutes($visit->checkout);
                            @endphp
                            <div class="text-white-50">
                                Durasi Kunjungan: <strong class="text-white font-monospace">{{ $diffMin }} menit</strong>
                            </div>
                        @endif
                    </div>

                    @if($visit->catatan)
                        <div class="p-2 rounded mt-2 border border-secondary border-opacity-10" style="background: rgba(255, 255, 255, 0.02); font-size: 0.75rem;">
                            <span class="text-secondary d-block fw-semibold" style="font-size: 0.65rem; text-transform: uppercase; margin-bottom: 2px;">Catatan:</span>
                            <span class="text-white-50">{{ $visit->catatan }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination Links -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $visits->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
