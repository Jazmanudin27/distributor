@extends('layouts.app')
@section('title', 'Transaksi Pembelian')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-cart-shopping me-2"></i> Transaksi Pembelian
                </h5>
                <small class="text-white-50">Kelola faktur pembelian dari supplier dan pembayaran tempo</small>
            </div>
            @can('create-pembelian')
                <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-white"></i> Transaksi Baru
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('pembelian.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Transaksi</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="No Faktur, PO, Supplier..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                            value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                            value="{{ request('tanggal_akhir') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Status Pembayaran</label>
                        <select name="status_pembayaran" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas
                            </option>
                            <option value="belum_lunas"
                                {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Filter Data">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="150">No Faktur</th>
                            <th width="120">Tanggal</th>
                            <th>Supplier</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th width="120" class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            @php
                                $totalBayar = $item->pembayarans->sum('jumlah');
                                $sisaPiutang = $item->grand_total - $totalBayar;
                                $isLunas = $sisaPiutang <= 0;
                            @endphp
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2.5 py-1">
                                        {{ $item->no_faktur }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="fw-bold text-dark">
                                    <div>{{ $item->supplier->nama_supplier ?? '-' }}</div>
                                    <div class="text-muted small fw-normal mt-1" style="font-size: 0.78rem;">
                                        PO: <span class="font-monospace text-primary">{{ $item->no_po ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    Rp {{ number_format((float) $item->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    Rp {{ number_format((float) $totalBayar, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    Rp {{ number_format((float) max(0, $sisaPiutang), 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ $isLunas ? 'success' : 'danger' }}-subtle text-{{ $isLunas ? 'success' : 'warning-emphasis' }} border border-{{ $isLunas ? 'success' : 'warning-subtle' }} px-2 py-1 fw-bold fs-8">
                                        {{ $isLunas ? 'L' : 'BL' }}
                                    </span>
                                    <div class="mt-1">
                                        @if($item->tanggal_approve)
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-0.5 fw-bold fs-9" title="Disetujui pada {{ \Carbon\Carbon::parse($item->tanggal_approve)->format('d-m-Y') }}">
                                                Approved
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-0.5 fw-bold fs-9">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('pembelian.show', $item->no_faktur) }}"
                                            class="btn btn-sm btn-outline-secondary rounded" title="Lihat & Bayar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @if(!$item->tanggal_approve && auth()->user()->can('approve-pembelian'))
                                            <form action="{{ route('pembelian.approve', $item->no_faktur) }}" method="POST"
                                                class="d-inline approve-form">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-success approve-btn rounded"
                                                    title="Setujui Pembelian">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @can('edit-pembelian')
                                            <a href="{{ route('pembelian.edit', $item->no_faktur) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-pembelian')
                                            <form action="{{ route('pembelian.destroy', $item->no_faktur) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete rounded"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary btn-show-logs rounded"
                                            data-no-faktur="{{ $item->no_faktur }}" title="Riwayat Aktivitas">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-cart-flatbed-suitcases d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data transaksi pembelian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.approve-btn', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Setujui Pembelian?',
                    text: "Apakah Anda yakin ingin menyetujui transaksi pembelian ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    background: '#161e31',
                    color: '#f8fafc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
