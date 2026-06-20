@extends('layouts.mobile')

@section('title', 'Persetujuan Limit Kredit')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('mobile.owner.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Persetujuan Limit Kredit</h5>
    </div>

    <!-- Approvals List -->
    @if($ajuans->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fa-solid fa-clipboard-check text-success" style="font-size: 1.8rem;"></i>
            </div>
            <h6 class="fw-bold text-white mb-1">Tidak Ada Antrean</h6>
            <p class="text-secondary small mb-0">Semua pengajuan limit kredit telah diproses.</p>
        </div>
    @else
        @foreach($ajuans as $ajuan)
            <div class="mobile-card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold text-white mb-0" style="font-size: 0.92rem;">{{ $ajuan->pelanggan->nama_pelanggan }}</h6>
                        <span class="text-secondary font-monospace" style="font-size: 0.72rem;">{{ $ajuan->kode_pelanggan }}</span>
                    </div>
                    <span class="badge rounded-pill bg-warning text-dark py-1 px-2.5 fw-semibold" style="font-size: 0.68rem;">
                        PENDING
                    </span>
                </div>

                <!-- Limits comparison -->
                <div class="row g-2 text-center my-3 py-2.5 rounded-3" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color);">
                    <div class="col-5">
                        <div class="text-secondary mb-0.5" style="font-size: 0.65rem;">LIMIT LAMA</div>
                        <div class="text-white fw-bold" style="font-size: 0.82rem;">Rp {{ number_format($ajuan->limit_lama, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-2 d-flex align-items-center justify-content-center text-secondary">
                        <i class="fa-solid fa-angles-right"></i>
                    </div>
                    <div class="col-5">
                        <div class="text-info mb-0.5" style="font-size: 0.65rem; font-weight: 600;">LIMIT BARU</div>
                        <div class="text-info fw-bold" style="font-size: 0.85rem;">Rp {{ number_format($ajuan->limit_baru, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Requester and reason -->
                <div style="font-size: 0.78rem;" class="mb-3 text-secondary">
                    <div class="mb-1">Diajukan Oleh: <strong class="text-white">{{ $ajuan->requester->name ?? '-' }}</strong></div>
                    <div class="mb-1">Tanggal: <strong class="text-white">{{ \Carbon\Carbon::parse($ajuan->created_at)->format('d-m-Y H:i') }}</strong></div>
                    <div>Alasan: <i class="text-white-50">"{{ $ajuan->alasan }}"</i></div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="row g-2">
                    <div class="col-6">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 py-2 fw-semibold" 
                                onclick="openRejectModal('{{ $ajuan->id }}')">
                            <i class="fa-solid fa-xmark me-1.5"></i> Tolak
                        </button>
                    </div>
                    <div class="col-6">
                        <form action="{{ route('mobile.owner.approve-limit', $ajuan->id) }}" method="POST" onsubmit="return confirmApprove(event)">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100 py-2 fw-semibold">
                                <i class="fa-solid fa-check me-1.5"></i> Setujui
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Rejection Modal Component -->
        <div class="modal fade" id="modalRejectLimit" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(5px);">
            <div class="modal-dialog modal-dialog-centered px-3">
                <div class="modal-content" style="background-color: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 20px;">
                    <form id="formRejectLimit" action="" method="POST">
                        @csrf
                        <div class="modal-header border-bottom border-secondary border-opacity-10 text-white py-3">
                            <h6 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Tolak Pengajuan Limit</h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <label class="form-label text-secondary small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="catatan_admin" class="form-control form-control-mobile" rows="3" placeholder="Masukkan alasan mengapa pengajuan ditolak..." required></textarea>
                        </div>
                        <div class="modal-footer border-top border-secondary border-opacity-10 py-2">
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-sm btn-danger px-3 py-2 fw-semibold rounded-3">Kirim Penolakan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                function confirmApprove(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Setujui Pengajuan?',
                        text: "Limit kredit pelanggan akan segera diperbarui.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
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

                function openRejectModal(id) {
                    const actionUrl = `/m/owner/reject-limit/${id}`;
                    document.getElementById('formRejectLimit').action = actionUrl;
                    const modal = new bootstrap.Modal(document.getElementById('modalRejectLimit'));
                    modal.show();
                }
            </script>
        @endpush
    @endif
@endsection
