@extends('layouts.mobile')

@section('title', 'Histori Pengajuan Limit')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Pengajuan Limit Kredit</h5>
        <a href="{{ route('mobile.limit-kredit.create') }}" class="btn btn-sm btn-mobile-primary px-3 py-1-5 fs-8">
            <i class="fa-solid fa-plus me-1"></i> Baru
        </a>
    </div>

    @if ($isSpv)
        <!-- Custom Premium Tabs for SPV Sales -->
        <ul class="nav nav-pills nav-justified mb-3 gap-2 p-1 rounded-3" id="spvLimitTabs" role="tablist" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05);">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-2 fs-8 fw-semibold rounded-3 text-white border-0" 
                    id="my-requests-tab" data-bs-toggle="pill" data-bs-target="#my-requests" type="button" role="tab" aria-controls="my-requests" aria-selected="true"
                    style="transition: all 0.25s ease;">
                    Pengajuan Saya
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2 fs-8 fw-semibold rounded-3 text-white border-0" 
                    id="pending-approvals-tab" data-bs-toggle="pill" data-bs-target="#pending-approvals" type="button" role="tab" aria-controls="pending-approvals" aria-selected="false"
                    style="transition: all 0.25s ease;">
                    Persetujuan Limit
                    @if($pendingAjuans->count() > 0)
                        <span class="badge bg-danger ms-1 font-monospace" style="font-size: 0.65rem;">{{ $pendingAjuans->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>

        <style>
            #spvLimitTabs .nav-link {
                background: transparent;
                color: var(--text-secondary) !important;
            }
            #spvLimitTabs .nav-link.active {
                background: var(--accent-gradient) !important;
                box-shadow: var(--accent-glow);
                color: #ffffff !important;
            }
        </style>
    @endif

    @if($isSpv)
        <div class="tab-content" id="spvLimitTabsContent">
            <div class="tab-pane fade show active" id="my-requests" role="tabpanel" aria-labelledby="my-requests-tab">
    @endif

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

    @if($isSpv)
            </div>
            <div class="tab-pane fade" id="pending-approvals" role="tabpanel" aria-labelledby="pending-approvals-tab">
                <!-- Pending Approvals List -->
                <div class="limit-requests-list">
                    @if ($pendingAjuans->isEmpty())
                        <div class="mobile-card text-center py-5">
                            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                                style="width: 65px; height: 65px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                                <i class="fa-solid fa-clipboard-check text-success" style="font-size: 1.8rem;"></i>
                            </div>
                            <h6 class="fw-bold text-white mb-1">Tidak Ada Antrean</h6>
                            <p class="text-secondary small mb-0">Semua pengajuan limit kredit telah diproses.</p>
                        </div>
                    @else
                        @foreach ($pendingAjuans as $ajuan)
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
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size: 0.65rem;">Pending</span>
                                    </div>
                                </div>

                                <!-- Limit value breakdown -->
                                <div class="row g-2 mb-2 pt-1" style="font-size: 0.75rem;">
                                    <div class="col-5">
                                        <span class="text-secondary d-block" style="font-size: 0.65rem;">Limit Lama</span>
                                        <span class="text-white-50">Rp {{ number_format($ajuan->limit_lama, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="col-2 d-flex align-items-center justify-content-center text-secondary">
                                        <i class="fa-solid fa-angles-right"></i>
                                    </div>
                                    <div class="col-5 text-end">
                                        <span class="text-secondary d-block" style="font-size: 0.65rem;">Limit Diajukan</span>
                                        <span class="text-info fw-bold">Rp {{ number_format($ajuan->limit_baru, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Requester and Reason -->
                                <div class="p-2 rounded-3 mb-2" style="background-color: rgba(255, 255, 255, 0.02); font-size: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.05);">
                                    <span class="text-secondary d-block" style="font-size: 0.65rem; font-weight: 500;">Diajukan Oleh:</span>
                                    <p class="text-white fw-semibold mb-1">{{ $ajuan->requester->name ?? '-' }} <span class="text-secondary fw-normal">({{ $ajuan->created_at->format('d/m/Y H:i') }})</span></p>
                                    <span class="text-secondary d-block mt-1" style="font-size: 0.65rem; font-weight: 500;">Alasan Pengajuan:</span>
                                    <p class="text-white-50 mb-0">"{{ $ajuan->alasan }}"</p>
                                </div>

                                <!-- Quick Action Buttons -->
                                <div class="row g-2 pt-2 border-top border-secondary border-opacity-10">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100 py-1-5 fw-semibold fs-8" 
                                                onclick="openRejectModal('{{ $ajuan->id }}')">
                                            <i class="fa-solid fa-xmark me-1"></i> Tolak
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <form action="{{ route('mobile.spv.limit-kredit.approve', $ajuan->id) }}" method="POST" onsubmit="return confirmApprove(event)">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success w-100 py-1-5 fw-semibold fs-8 border-0" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                                <i class="fa-solid fa-check me-1"></i> Setujui
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Rejection Modal Component -->
        <div class="modal fade" id="modalRejectLimit" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(5px); z-index: 1060;">
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
                            <textarea name="catatan_admin" class="form-control form-control-mobile" rows="3" placeholder="Masukkan alasan mengapa pengajuan ditolak..." required style="background-color: rgba(0,0,0,0.2); border-color: rgba(255,255,255,0.15); color: #fff;"></textarea>
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

                function openRejectModal(id) {
                    const actionUrl = `/m/spv/limit-kredit/${id}/reject`;
                    document.getElementById('formRejectLimit').action = actionUrl;
                    const modal = new bootstrap.Modal(document.getElementById('modalRejectLimit'));
                    modal.show();
                }
            </script>
        @endpush
    @endif
@endsection
