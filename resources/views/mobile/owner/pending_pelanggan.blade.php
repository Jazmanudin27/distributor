@extends('layouts.mobile')

@section('title', 'Persetujuan Pelanggan Baru')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.owner.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Persetujuan Pelanggan Baru</h5>
    </div>

    <!-- Pending Customer List -->
    @if ($pendingCustomers->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                <i class="fa-solid fa-user-check text-secondary" style="font-size: 1.7rem;"></i>
            </div>
            <h6 class="fw-bold text-white-50">Semua Terproses</h6>
            <p class="text-secondary small">Tidak ada pengajuan pelanggan baru yang perlu disetujui saat ini.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3 pb-5">
            @foreach ($pendingCustomers as $p)
                <div class="mobile-card p-3 mb-0">
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">{{ $p->nama_pelanggan }}</h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.7rem;">KODE: {{ $p->kode_pelanggan }}</span>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1" style="font-size: 0.68rem; font-weight: 600;">
                            Menunggu Owner
                        </span>
                    </div>

                    <!-- Details -->
                    <div style="font-size: 0.8rem;" class="text-secondary mb-3">
                        <div class="mb-1"><i class="fa-solid fa-phone me-1.5 text-secondary"></i> No HP: <strong class="text-white">{{ $p->no_hp_pelanggan }}</strong></div>
                        <div class="mb-1"><i class="fa-solid fa-map-pin me-1.5 text-danger"></i> Alamat: <strong class="text-white">{{ $p->alamat_pelanggan }}</strong></div>
                        @if($p->alamat_toko && $p->alamat_toko !== $p->alamat_pelanggan)
                            <div class="mb-1"><i class="fa-solid fa-truck me-1.5 text-secondary"></i> Alamat Toko: <strong class="text-white">{{ $p->alamat_toko }}</strong></div>
                        @endif
                        <div class="mb-1"><i class="fa-solid fa-map me-1.5 text-secondary"></i> Wilayah: <strong class="text-white">{{ $p->wilayah->nama_wilayah ?? '-' }} / {{ $p->subWilayah->nama_wilayah ?? '-' }}</strong></div>
                        <div class="mb-1"><i class="fa-solid fa-wallet me-1.5 text-indigo"></i> Pembayaran: <strong class="text-white">{{ $p->metode_bayar }} (Limit: Rp {{ number_format($p->limit_pelanggan, 0, ',', '.') }}, LJT: {{ $p->ljt }} hari)</strong></div>
                        
                        @if ($p->latitude && $p->longitude)
                            <div class="mt-2">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $p->latitude }},{{ $p->longitude }}" target="_blank" class="btn btn-xs btn-outline-info text-info border-info border-opacity-50 py-1 px-2.5" style="font-size: 0.7rem; border-radius: 6px; text-decoration: none;">
                                    <i class="fa-solid fa-location-arrow me-1"></i> Buka Peta Lokasi
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Attachments -->
                    @if ($p->foto || $p->foto_ktp)
                        <div class="row g-2 mb-3 pt-2 border-top border-secondary border-opacity-10">
                            @if ($p->foto)
                                <div class="col-6">
                                    <span class="text-secondary d-block mb-1" style="font-size: 0.65rem;">FOTO TOKO:</span>
                                    <a href="{{ asset($p->foto) }}" target="_blank">
                                        <img src="{{ asset($p->foto) }}" class="img-fluid rounded-3 border border-secondary border-opacity-10" style="max-height: 80px; width: 100%; object-fit: cover;">
                                    </a>
                                </div>
                            @endif
                            @if ($p->foto_ktp)
                                <div class="col-6">
                                    <span class="text-secondary d-block mb-1" style="font-size: 0.65rem;">FOTO KTP:</span>
                                    <a href="{{ asset($p->foto_ktp) }}" target="_blank">
                                        <img src="{{ asset($p->foto_ktp) }}" class="img-fluid rounded-3 border border-secondary border-opacity-10" style="max-height: 80px; width: 100%; object-fit: cover;">
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Action buttons -->
                    <div class="d-flex gap-2 border-top border-secondary border-opacity-10 pt-3">
                        <form action="{{ route('mobile.owner.reject-pelanggan', $p->kode_pelanggan) }}" method="POST" class="flex-grow-1" onsubmit="return confirmReject(event)">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 py-2 fw-semibold" style="border-radius: 8px; font-size: 0.78rem;">
                                <i class="fa-solid fa-xmark me-1"></i> Tolak
                            </button>
                        </form>
                        <form action="{{ route('mobile.owner.approve-pelanggan', $p->kode_pelanggan) }}" method="POST" class="flex-grow-1" onsubmit="return confirmApprove(event)">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100 py-2 fw-semibold border-0 text-white" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; font-size: 0.78rem;">
                                <i class="fa-solid fa-check me-1"></i> Setujui
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @push('scripts')
            <script>
                function confirmApprove(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Setujui Pelanggan Baru?',
                        text: "Pelanggan akan segera aktif dan dapat menerima check-in serta transaksi.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Setujui',
                        cancelButtonText: 'Batal',
                        background: '#161e31',
                        color: '#f8fafc'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            e.target.submit();
                        }
                    });
                    return false;
                }

                function confirmReject(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Tolak Pelanggan Baru?',
                        text: "Pendaftaran pelanggan ini akan ditolak.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Tolak',
                        cancelButtonText: 'Batal',
                        background: '#161e31',
                        color: '#f8fafc'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            e.target.submit();
                        }
                    });
                    return false;
                }
            </script>
        @endpush
    @endif
@endsection
