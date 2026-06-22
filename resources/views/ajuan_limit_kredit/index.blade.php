@extends('layouts.app')
@section('title', 'Ajuan Limit Kredit')

@push('styles')
    <style>
        .stat-card {
            border: none;
            border-radius: 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .stat-card .icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .badge-status {
            font-size: 0.78rem;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .filter-tab {
            border: none;
            background: transparent;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--bs-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-tab.active,
        .filter-tab:hover {
            background: var(--bs-primary);
            color: #fff;
        }

        .table-row-hover:hover {
            background: rgba(99, 102, 241, 0.07);
        }

        .limit-arrow {
            color: var(--bs-secondary);
            font-size: 0.9rem;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column gap-4">

        {{-- HEADER --}}
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 fw-bold">
                        <i class="fa-solid fa-hand-holding-dollar me-2"></i> Ajuan Limit Kredit
                    </h5>
                    <small class="text-white-50">Manajemen pengajuan perubahan limit kredit pelanggan</small>
                </div>
                @can('create-ajuan_limit_kredit')
                    <a href="{{ route('ajuan-limit-kredit.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-circle-plus me-1 text-white"></i> Buat Ajuan
                    </a>
                @endcan
            </div>

            <div class="card-body p-4">

                {{-- STAT CARDS --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card stat-card shadow-sm h-100"
                            style="background: linear-gradient(135deg,#f8faff,#eef2ff);">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="icon-wrap bg-primary bg-opacity-15">
                                    <i class="fa-solid fa-list-check text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-4 lh-1">{{ $statusCounts['all'] }}</div>
                                    <small class="text-secondary">Total Ajuan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card shadow-sm h-100"
                            style="background: linear-gradient(135deg,#fffbeb,#fef9c3);">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="icon-wrap bg-warning bg-opacity-15">
                                    <i class="fa-solid fa-hourglass-half text-warning"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-4 lh-1">{{ $statusCounts['pending'] }}</div>
                                    <small class="text-secondary">Menunggu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card shadow-sm h-100"
                            style="background: linear-gradient(135deg,#f0fdf4,#dcfce7);">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="icon-wrap bg-success bg-opacity-15">
                                    <i class="fa-solid fa-circle-check text-success"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-4 lh-1">{{ $statusCounts['approved'] }}</div>
                                    <small class="text-secondary">Disetujui</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card stat-card shadow-sm h-100"
                            style="background: linear-gradient(135deg,#fff1f2,#ffe4e6);">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="icon-wrap bg-danger bg-opacity-15">
                                    <i class="fa-solid fa-circle-xmark text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-4 lh-1">{{ $statusCounts['rejected'] }}</div>
                                    <small class="text-secondary">Ditolak</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILTER --}}
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <form action="{{ route('ajuan-limit-kredit.index') }}" method="GET"
                        class="d-flex gap-2 flex-wrap align-items-center w-100">
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="submit" name="status" value=""
                                class="filter-tab {{ !request('status') ? 'active' : '' }}">
                                Semua
                            </button>
                            <button type="submit" name="status" value="pending"
                                class="filter-tab {{ request('status') === 'pending' ? 'active' : '' }}">
                                <i class="fa-solid fa-hourglass-half me-1"></i> Pending
                                @if (isset($statusCounts['pending']) && $statusCounts['pending'] > 0)
                                    <span class="badge bg-warning text-dark rounded-pill ms-1"
                                        style="font-size: 0.7rem; padding: 0.2em 0.5em;">{{ $statusCounts['pending'] }}</span>
                                @endif
                            </button>
                            <button type="submit" name="status" value="approved"
                                class="filter-tab {{ request('status') === 'approved' ? 'active' : '' }}">
                                <i class="fa-solid fa-check me-1"></i> Disetujui
                            </button>
                            <button type="submit" name="status" value="rejected"
                                class="filter-tab {{ request('status') === 'rejected' ? 'active' : '' }}">
                                <i class="fa-solid fa-xmark me-1"></i> Ditolak
                            </button>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm" style="width:220px"
                                placeholder="Cari pelanggan..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            @if (request()->hasAny(['status', 'search']))
                                <a href="{{ route('ajuan-limit-kredit.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Pelanggan</th>
                                <th class="text-end">Limit Lama</th>
                                <th class="text-center"></th>
                                <th class="text-end">Limit Baru</th>
                                <th>Diajukan Oleh</th>
                                <th>Tanggal Ajuan</th>
                                <th class="text-center">Status</th>
                                <th width="100" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ajuans as $index => $ajuan)
                                <tr class="table-row-hover">
                                    <td class="text-center text-secondary small fw-bold">
                                        {{ $ajuans->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $ajuan->pelanggan->nama_pelanggan ?? '-' }}</div>
                                        <small class="text-muted font-monospace">{{ $ajuan->kode_pelanggan }}</small>
                                    </td>
                                    <td class="text-end text-secondary">
                                        Rp {{ number_format($ajuan->limit_lama, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if ($ajuan->limit_baru > $ajuan->limit_lama)
                                            <i class="fa-solid fa-arrow-up text-success"></i>
                                        @elseif($ajuan->limit_baru < $ajuan->limit_lama)
                                            <i class="fa-solid fa-arrow-down text-danger"></i>
                                        @else
                                            <i class="fa-solid fa-equals text-secondary"></i>
                                        @endif
                                    </td>
                                    <td
                                        class="text-end fw-semibold text-{{ $ajuan->limit_baru > $ajuan->limit_lama ? 'success' : ($ajuan->limit_baru < $ajuan->limit_lama ? 'danger' : 'secondary') }}">
                                        Rp {{ number_format($ajuan->limit_baru, 0, ',', '.') }}
                                    </td>
                                    <td class="small">
                                        <div>{{ $ajuan->requester->name ?? '-' }}</div>
                                    </td>
                                    <td class="small text-secondary">
                                        {{ $ajuan->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge-status badge bg-{{ $ajuan->status_color }}-subtle text-{{ $ajuan->status_color }} border border-{{ $ajuan->status_color }}-subtle">
                                            @if ($ajuan->status === 'pending')
                                                <i class="fa-solid fa-hourglass-half me-1"></i>
                                            @elseif($ajuan->status === 'approved')
                                                <i class="fa-solid fa-check me-1"></i>
                                            @else
                                                <i class="fa-solid fa-xmark me-1"></i>
                                            @endif
                                            {{ $ajuan->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('ajuan-limit-kredit.show', $ajuan->id) }}"
                                                class="btn btn-sm btn-outline-secondary rounded" title="Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            @if ($ajuan->isPending())
                                                @can('approve-ajuan_limit_kredit')
                                                    <button type="button" class="btn btn-sm btn-success rounded btn-approve"
                                                        title="Setujui" data-id="{{ $ajuan->id }}"
                                                        data-pelanggan="{{ $ajuan->pelanggan->nama_pelanggan ?? '' }}"
                                                        data-limit-baru="{{ number_format($ajuan->limit_baru, 0, ',', '.') }}">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger rounded btn-reject"
                                                        title="Tolak" data-id="{{ $ajuan->id }}"
                                                        data-pelanggan="{{ $ajuan->pelanggan->nama_pelanggan ?? '' }}">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-hand-holding-dollar d-block fs-2 mb-2 opacity-30"></i>
                                        Tidak ada data ajuan limit kredit.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($ajuans->hasPages())
                    <div class="d-flex justify-content-end mt-4">
                        {{ $ajuans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Approve --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="approveModalLabel">
                        <i class="fa-solid fa-circle-check me-2"></i> Setujui Ajuan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="mb-3">Anda akan menyetujui ajuan limit kredit untuk:</p>
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 mb-3 border border-success-subtle">
                            <div class="fw-bold" id="approve-pelanggan-name"></div>
                            <div class="text-success fw-semibold mt-1">Limit Baru: Rp <span
                                    id="approve-limit-baru"></span></div>
                        </div>
                        <p class="text-success fw-semibold small"><i class="fa-solid fa-triangle-exclamation me-1"></i>
                            Limit pelanggan akan otomatis diperbarui setelah disetujui.
                        </p>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Catatan (opsional)</label>
                            <textarea name="catatan_admin" class="form-control form-control-sm" rows="2"
                                placeholder="Tambahkan catatan persetujuan..."></textarea>
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
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="rejectModalLabel">
                        <i class="fa-solid fa-circle-xmark me-2"></i> Tolak Ajuan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="mb-3">Anda akan menolak ajuan limit kredit untuk:</p>
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3 mb-3 border border-danger-subtle">
                            <div class="fw-bold" id="reject-pelanggan-name"></div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold small">Alasan Penolakan <span
                                    class="text-danger">*</span></label>
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
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const pelanggan = this.dataset.pelanggan;
                const limitBaru = this.dataset.limitBaru;
                document.getElementById('approve-pelanggan-name').textContent = pelanggan;
                document.getElementById('approve-limit-baru').textContent = limitBaru;
                document.getElementById('approveForm').action = `/ajuan-limit-kredit/${id}/approve`;
                new bootstrap.Modal(document.getElementById('approveModal')).show();
            });
        });

        document.querySelectorAll('.btn-reject').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const pelanggan = this.dataset.pelanggan;
                document.getElementById('reject-pelanggan-name').textContent = pelanggan;
                document.getElementById('rejectForm').action = `/ajuan-limit-kredit/${id}/reject`;
                new bootstrap.Modal(document.getElementById('rejectModal')).show();
            });
        });
    </script>
@endpush
