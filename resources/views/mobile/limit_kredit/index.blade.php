@extends('layouts.mobile')

@section('title', 'Histori Pengajuan Limit')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Pengajuan Limit Kredit</h5>
        <a href="{{ route('mobile.limit-kredit.create') }}" class="btn btn-sm btn-mobile-primary px-3 py-1-5 fs-8">
            <i class="fa-solid fa-plus me-1"></i> Baru
        </a>
    </div>

    <!-- Limit Requests List -->
    <div class="limit-requests-list">
        @if ($ajuans->isEmpty())
            <div class="mobile-card text-center py-5">
                <i class="fa-solid fa-file-invoice-dollar text-secondary mb-3" style="font-size: 2.5rem; opacity: 0.4;"></i>
                <p class="text-secondary mb-0">Belum ada pengajuan limit kredit.</p>
            </div>
        @else
            @foreach ($ajuans as $ajuan)
                <div class="mobile-card p-3 mb-2" style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between align-items-start mb-2 border-bottom border-secondary border-opacity-10 pb-2">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.9rem;">
                                {{ $ajuan->pelanggan->nama_pelanggan }}
                            </h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                Kode: {{ $ajuan->kode_pelanggan }}
                            </span>
                        </div>
                        <div>
                            @if ($ajuan->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size: 0.65rem;">Pending</span>
                            @elseif ($ajuan->status === 'approved')
                                <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size: 0.65rem;">Disetujui</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size: 0.65rem;">Ditolak</span>
                            @endif
                        </div>
                    </div>

                    <!-- Limit value breakdown -->
                    <div class="row g-2 mb-2 pt-1" style="font-size: 0.75rem;">
                        <div class="col-6">
                            <span class="text-secondary d-block">Limit Lama</span>
                            <span class="text-white-50">Rp {{ number_format($ajuan->limit_lama, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="text-secondary d-block">Limit Diajukan</span>
                            <span class="text-info fw-bold">Rp {{ number_format($ajuan->limit_baru, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="p-2 rounded-3 mb-1" style="background-color: rgba(255, 255, 255, 0.02); font-size: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.05);">
                        <span class="text-secondary d-block" style="font-size: 0.65rem; font-weight: 500;">Alasan Pengajuan:</span>
                        <p class="text-white-50 mb-0 mt-0-5">"{{ $ajuan->alasan }}"</p>
                    </div>

                    <!-- Catatan Admin (if rejected or approved with comment) -->
                    @if ($ajuan->catatan_admin)
                        <div class="p-2 rounded-3 mb-1 mt-2 border border-danger border-opacity-20" style="background-color: rgba(239, 68, 68, 0.03); font-size: 0.75rem;">
                            <span class="text-danger d-block" style="font-size: 0.65rem; font-weight: 600;">Catatan Admin:</span>
                            <p class="text-white-50 mb-0 mt-0-5">"{{ $ajuan->catatan_admin }}"</p>
                        </div>
                    @endif

                    <div class="text-end text-secondary mt-2 pt-1 border-top border-secondary border-opacity-10" style="font-size: 0.65rem;">
                        Diajukan: {{ $ajuan->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            @endforeach

            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-3">
                {{ $ajuans->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
