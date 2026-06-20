@extends('layouts.app')
@section('title', 'Persetujuan Pembayaran')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3"
            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-circle-check me-2"></i> Persetujuan Pembayaran
                </h5>
                <small class="text-white-50">Kelola dan verifikasi pembayaran masuk (Tunai, Transfer, Giro, dll.)</small>
            </div>
        </div>

        <div class="card-body p-4">
            {{-- Tab Navigation --}}
            <ul class="nav nav-pills mb-4">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}"
                        href="{{ route('pembayaran.pending.index', ['tab' => 'pending']) }}">
                        <i class="fa-solid fa-clock me-1"></i> Menunggu Persetujuan
                        @if (isset($pendingPembayaranCount) && $pendingPembayaranCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill ms-1"
                                style="font-size: 0.7rem; padding: 0.25em 0.55em;">{{ $pendingPembayaranCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'history' ? 'active' : '' }}"
                        href="{{ route('pembayaran.pending.index', ['tab' => 'history']) }}">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Riwayat Persetujuan
                    </a>
                </li>
            </ul>

            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border border-secondary border-opacity-10">
                <form action="{{ route('pembayaran.pending.index') }}" method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Metode Pembayaran</label>
                        <select name="jenis_bayar" class="form-select form-select-sm">
                            <option value="">Semua Metode</option>
                            <option value="tunai"
                                {{ request('jenis_bayar') === 'tunai' || request('jenis_bayar') === 'Cash' ? 'selected' : '' }}>
                                Tunai / Cash</option>
                            <option value="transfer"
                                {{ request('jenis_bayar') === 'transfer' || request('jenis_bayar') === 'Transfer' ? 'selected' : '' }}>
                                Transfer</option>
                            <option value="giro"
                                {{ request('jenis_bayar') === 'giro' || request('jenis_bayar') === 'Giro' ? 'selected' : '' }}>
                                Giro</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                            value="{{ request('tanggal_mulai') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                            value="{{ request('tanggal_akhir') }}">
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-1.5" title="Filter Data">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('pembayaran.pending.index', ['tab' => $tab]) }}"
                            class="btn btn-outline-secondary btn-sm w-100 py-1.5" title="Reset">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="160">No Bukti / Tgl</th>
                            <th width="160">No Faktur</th>
                            <th>Pelanggan</th>
                            <th width="150">Salesman</th>
                            <th width="100" class="text-center">Metode</th>
                            <th class="text-end">Jumlah</th>
                            <th>Keterangan</th>
                            <th width="120" class="text-center">Status</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $index => $payment)
                            <tr>
                                <td class="text-center text-secondary small fw-bold">{{ $payments->firstItem() + $index }}
                                </td>
                                <td>
                                    <span
                                        class="fw-bold text-dark d-block font-monospace small">{{ $payment->no_bukti }}</span>
                                    <small
                                        class="text-muted font-monospace">{{ \Carbon\Carbon::parse($payment->tanggal)->format('d-m-Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('penjualan.show', $payment->no_faktur) }}"
                                        class="badge bg-secondary font-monospace px-2 py-1 text-decoration-none">
                                        {{ $payment->no_faktur }}
                                    </a>
                                </td>
                                <td class="fw-bold text-dark">
                                    <div>{{ $payment->pelanggan->nama_pelanggan ?? '-' }}</div>
                                    <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                        <span class="font-monospace text-secondary">{{ $payment->kode_pelanggan }}</span>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark">
                                    @if ($payment->penjualan && $payment->penjualan->sales)
                                        <div>{{ $payment->penjualan->sales->name }}</div>
                                        <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                            <span
                                                class="font-monospace text-secondary">{{ $payment->penjualan->kode_sales }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fw-normal">{{ $payment->kode_sales ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $badgeClass =
                                            'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle';
                                        if ($payment->jenis_bayar === 'Cash') {
                                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                        } elseif ($payment->jenis_bayar === 'Transfer') {
                                            $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                        } elseif ($payment->jenis_bayar === 'Giro') {
                                            $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-2 py-1 fw-bold fs-8">
                                        {{ $payment->jenis_bayar }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    Rp {{ number_format((float) $payment->jumlah, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $payment->keterangan ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($payment->status === 'pending')
                                        <span class="badge bg-warning text-dark px-2 py-1 fs-8">Pending</span>
                                    @elseif($payment->status === 'disetujui')
                                        <span class="badge bg-success px-2 py-1 fs-8">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger px-2 py-1 fs-8">Ditolak</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($payment->status === 'pending')
                                            <form
                                                action="{{ route('pembayaran.approve', [$payment->id, 'source' => $payment->source_table]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pembayaran ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm fw-semibold">
                                                    <i class="fa-solid fa-check me-1"></i> Setuju
                                                </button>
                                            </form>
                                            <form
                                                action="{{ route('pembayaran.reject', [$payment->id, 'source' => $payment->source_table]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menolak pembayaran ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm fw-semibold">
                                                    <i class="fa-solid fa-xmark me-1"></i> Tolak
                                                </button>
                                            </form>
                                        @else
                                            @if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                                                <form
                                                    action="{{ route('pembayaran.cancel-approval', [$payment->id, 'source' => $payment->source_table]) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan persetujuan/penolakan pembayaran ini?')">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-warning btn-sm fw-semibold text-white d-flex align-items-center gap-1">
                                                        <i class="fa-solid fa-arrow-rotate-left"></i> Batal
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-circle-check d-block fs-3 mb-2 opacity-50 text-secondary"></i>
                                    Tidak ada data pembayaran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endsection
