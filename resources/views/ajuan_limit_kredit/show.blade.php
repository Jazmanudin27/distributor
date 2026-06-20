@extends('layouts.app')
@section('title', 'Detail Ajuan Limit Kredit')

@push('styles')
<style>
    .detail-label { font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: var(--bs-secondary); }
    .detail-value { font-size: 1rem; font-weight: 500; margin-top: 2px; }
    .timeline-dot {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; flex-shrink: 0;
    }
    .limit-change-card {
        border-radius: 14px;
        border: 1px solid rgba(0,0,0,0.07);
        background: rgba(99,102,241,0.03);
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        {{-- Header Card --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header card-premium-header text-white d-flex align-items-center gap-3 py-3">
                <a href="{{ route('ajuan-limit-kredit.index') }}" class="btn btn-sm btn-light btn-outline-light opacity-75">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="flex-grow-1">
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-file-lines me-2"></i> Detail Ajuan Limit Kredit
                    </h5>
                    <small class="text-white-50">ID Ajuan #{{ $ajuan->id }}</small>
                </div>
                <span class="badge fs-6 px-3 py-2 bg-{{ $ajuan->status_color }}-subtle text-{{ $ajuan->status_color }} border border-{{ $ajuan->status_color }}-subtle">
                    @if($ajuan->status === 'pending')
                        <i class="fa-solid fa-hourglass-half me-1"></i>
                    @elseif($ajuan->status === 'approved')
                        <i class="fa-solid fa-check me-1"></i>
                    @else
                        <i class="fa-solid fa-xmark me-1"></i>
                    @endif
                    {{ $ajuan->status_label }}
                </span>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    {{-- Info Pelanggan --}}
                    <div class="col-md-6">
                        <p class="form-section-label mb-3" style="font-size:0.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--bs-secondary)">
                            <i class="fa-solid fa-user me-1"></i> Informasi Pelanggan
                        </p>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                style="width:52px;height:52px;flex-shrink:0">
                                <i class="fa-solid fa-user text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-bold fs-5">{{ $ajuan->pelanggan->nama_pelanggan ?? '-' }}</div>
                                <span class="badge bg-secondary font-monospace">{{ $ajuan->kode_pelanggan }}</span>
                            </div>
                        </div>
                        <div class="text-secondary small">
                            <i class="fa-solid fa-location-dot me-1"></i>
                            {{ $ajuan->pelanggan->alamat_pelanggan ?? '-' }}
                        </div>
                    </div>

                    {{-- Info Ajuan --}}
                    <div class="col-md-6">
                        <p class="form-section-label mb-3" style="font-size:0.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--bs-secondary)">
                            <i class="fa-solid fa-clock me-1"></i> Informasi Ajuan
                        </p>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="detail-label">Diajukan Oleh</div>
                                <div class="detail-value">{{ $ajuan->requester->name ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="detail-label">Tanggal Ajuan</div>
                                <div class="detail-value">{{ $ajuan->created_at->format('d M Y, H:i') }}</div>
                            </div>
                            @if($ajuan->approved_at)
                                <div class="col-6">
                                    <div class="detail-label">Diproses Oleh</div>
                                    <div class="detail-value">{{ $ajuan->approver->name ?? '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-label">Tanggal Proses</div>
                                    <div class="detail-value">{{ $ajuan->approved_at->format('d M Y, H:i') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Perubahan Limit --}}
                    <div class="col-12">
                        <div class="limit-change-card p-4">
                            <p class="form-section-label mb-3" style="font-size:0.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--bs-secondary)">
                                <i class="fa-solid fa-sliders me-1"></i> Perubahan Limit Kredit
                            </p>
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <div class="text-center">
                                    <div class="text-secondary small mb-1">Limit Lama</div>
                                    <div class="fw-bold fs-4 text-secondary">
                                        Rp {{ number_format($ajuan->limit_lama, 0, ',', '.') }}
                                    </div>
                                </div>
                                <i class="fa-solid fa-arrow-right text-primary fs-4"></i>
                                <div class="text-center">
                                    <div class="text-secondary small mb-1">Limit Baru</div>
                                    <div class="fw-bold fs-4 text-primary">
                                        Rp {{ number_format($ajuan->limit_baru, 0, ',', '.') }}
                                    </div>
                                </div>
                                @php
                                    $diff = $ajuan->limit_baru - $ajuan->limit_lama;
                                    $pct = $ajuan->limit_lama > 0 ? round(($diff / $ajuan->limit_lama) * 100, 1) : 0;
                                @endphp
                                <div class="ms-2">
                                    @if($diff > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                                            <i class="fa-solid fa-arrow-up me-1"></i>
                                            +Rp {{ number_format($diff, 0, ',', '.') }}
                                            <small>(+{{ $pct }}%)</small>
                                        </span>
                                    @elseif($diff < 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                                            <i class="fa-solid fa-arrow-down me-1"></i>
                                            Rp {{ number_format($diff, 0, ',', '.') }}
                                            <small>({{ $pct }}%)</small>
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 fs-6">
                                            <i class="fa-solid fa-equals me-1"></i> Tidak Berubah
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alasan --}}
                    <div class="col-md-6">
                        <div class="detail-label mb-1">Alasan Pengajuan</div>
                        <div class="p-3 bg-light rounded-3 small">{{ $ajuan->alasan ?? '-' }}</div>
                    </div>

                    {{-- Catatan Admin --}}
                    @if($ajuan->catatan_admin)
                        <div class="col-md-6">
                            <div class="detail-label mb-1">
                                @if($ajuan->isApproved())
                                    <span class="text-success">Catatan Persetujuan</span>
                                @else
                                    <span class="text-danger">Alasan Penolakan</span>
                                @endif
                            </div>
                            <div class="p-3 bg-{{ $ajuan->isApproved() ? 'success' : 'danger' }}-subtle rounded-3 small border border-{{ $ajuan->isApproved() ? 'success' : 'danger' }}-subtle">
                                {{ $ajuan->catatan_admin }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($ajuan->isPending())
                @can('approve-ajuan_limit_kredit')
                    <div class="card-footer border-0 bg-transparent p-4 pt-0 d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-danger btn-reject fw-bold px-4"
                            data-id="{{ $ajuan->id }}"
                            data-pelanggan="{{ $ajuan->pelanggan->nama_pelanggan ?? '' }}">
                            <i class="fa-solid fa-xmark me-1"></i> Tolak Ajuan
                        </button>
                        <button type="button" class="btn btn-success btn-approve fw-bold px-4"
                            data-id="{{ $ajuan->id }}"
                            data-pelanggan="{{ $ajuan->pelanggan->nama_pelanggan ?? '' }}"
                            data-limit-baru="{{ number_format($ajuan->limit_baru, 0, ',', '.') }}">
                            <i class="fa-solid fa-check me-1"></i> Setujui Ajuan
                        </button>
                    </div>
                @endcan
            @endif
        </div>

    </div>
</div>

{{-- Modal Approve --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-check me-2"></i> Setujui Ajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="bg-success bg-opacity-10 rounded-3 p-3 mb-3 border border-success-subtle">
                        <div class="fw-bold" id="approve-pelanggan-name"></div>
                        <div class="text-success fw-semibold mt-1">Limit Baru: Rp <span id="approve-limit-baru"></span></div>
                    </div>
                    <p class="text-success fw-semibold small mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Limit pelanggan akan otomatis diperbarui setelah disetujui.
                    </p>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Catatan (opsional)</label>
                        <textarea name="catatan_admin" class="form-control form-control-sm" rows="2"
                            placeholder="Tambahkan catatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="fa-solid fa-check me-1"></i> Ya, Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-xmark me-2"></i> Tolak Ajuan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="bg-danger bg-opacity-10 rounded-3 p-3 mb-3 border border-danger-subtle">
                        <div class="fw-bold" id="reject-pelanggan-name"></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_admin" class="form-control form-control-sm" rows="3"
                            placeholder="Tuliskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="fa-solid fa-xmark me-1"></i> Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('approve-pelanggan-name').textContent = this.dataset.pelanggan;
            document.getElementById('approve-limit-baru').textContent = this.dataset.limitBaru;
            document.getElementById('approveForm').action = `/ajuan-limit-kredit/${this.dataset.id}/approve`;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        });
    });
    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('reject-pelanggan-name').textContent = this.dataset.pelanggan;
            document.getElementById('rejectForm').action = `/ajuan-limit-kredit/${this.dataset.id}/reject`;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        });
    });
</script>
@endpush
