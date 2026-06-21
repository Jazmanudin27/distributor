@extends('layouts.app')
@section('title', 'Kasir Penjualan')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i> Kasir Penjualan
                </h5>
                <small class="text-white-50">Kelola transaksi penjualan dan piutang pelanggan</small>
            </div>
            @can('create-penjualan')
                <a href="{{ route('penjualan.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Transaksi Baru
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('penjualan.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Transaksi</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="No Faktur, Nama Pelanggan..." value="{{ request('search') }}">
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
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Status Pembayaran</label>
                        <select name="status_pembayaran" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="lunas" {{ request('status_pembayaran') === 'lunas' ? 'selected' : '' }}>Lunas
                            </option>
                            <option value="belum_lunas"
                                {{ request('status_pembayaran') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Wilayah</label>
                        <select name="kode_wilayah" class="form-select form-select-sm">
                            <option value="">Semua Wilayah</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->kode_wilayah }}"
                                    {{ request('kode_wilayah') == $w->kode_wilayah ? 'selected' : '' }}>
                                    {{ $w->nama_wilayah }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Salesman</label>
                        <select name="kode_sales" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($salesmen as $s)
                                <option value="{{ $s->nik }}"
                                    {{ request('kode_sales') === $s->nik ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-transparent mb-1">Aksi</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill fw-bold" title="Filter Data">
                                <i class="fa-solid fa-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('penjualan.index') }}"
                                class="btn btn-outline-secondary btn-sm flex-fill fw-bold" title="Reset">
                                <i class="fa-solid fa-rotate-right me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="160">No Faktur</th>
                            <th width="110">Tanggal</th>
                            <th>Pelanggan</th>
                            <th width="140">Wilayah</th>
                            <th width="120">Sales</th>
                            <th width="80" class="text-center">Jenis</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Terbayar</th>
                            <th class="text-end">Sisa</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="140" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            @php
                                $totalBayar = $item->pembayarans->sum('jumlah');
                                $sisaPiutang = $item->grand_total - $totalBayar;
                                $isLunas = $sisaPiutang <= 0;
                            @endphp
                            <tr>
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2 py-1">
                                        {{ $item->no_faktur }}
                                    </span>
                                </td>
                                <td class="small">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="fw-bold text-dark">
                                    <div>{{ $item->pelanggan->nama_pelanggan ?? '-' }}</div>
                                    <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                        <span class="font-monospace text-secondary">{{ $item->kode_pelanggan }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($item->pelanggan && $item->pelanggan->wilayah)
                                        <div class="fw-semibold text-dark">{{ $item->pelanggan->wilayah->nama_wilayah }}
                                        </div>
                                        <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                            <span
                                                class="font-monospace text-secondary">{{ $item->pelanggan->kode_wilayah }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">
                                    @if ($item->sales)
                                        <div>{{ $item->sales->name }}</div>
                                        <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                            <span class="font-monospace text-secondary">{{ $item->kode_sales }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fw-normal">{{ $item->kode_sales ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge {{ $item->jenis_transaksi === 'T' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-1 fw-bold fs-8">
                                        {{ $item->jenis_transaksi }}
                                    </span>
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
                                        class="badge bg-{{ $isLunas ? 'success' : 'danger' }}-subtle text-{{ $isLunas ? 'success' : 'danger' }} border border-{{ $isLunas ? 'success' : 'danger' }}-subtle px-2 py-1 fw-bold fs-8">
                                        {{ $isLunas ? 'L' : 'BL' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('penjualan.print', $item->no_faktur) }}"
                                            class="btn btn-sm btn-outline-info rounded position-relative btn-print-faktur"
                                            data-no-faktur="{{ $item->no_faktur }}" data-cetak="{{ $item->cetak ?? 0 }}"
                                            title="Cetak Faktur">
                                            <i class="fa-solid fa-print"></i>
                                            @if (($item->cetak ?? 0) > 0)
                                                <span
                                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                    style="font-size: 0.65rem; padding: 0.2em 0.35em;">
                                                    {{ $item->cetak }}
                                                </span>
                                            @endif
                                        </a>
                                        <a href="{{ route('penjualan.show', $item->no_faktur) }}"
                                            class="btn btn-sm btn-outline-secondary rounded" title="Lihat & Bayar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('edit-penjualan')
                                            <a href="{{ route('penjualan.edit', $item->no_faktur) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-penjualan')
                                            <form action="{{ route('penjualan.destroy', $item->no_faktur) }}" method="POST"
                                                class="d-inline">
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
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-file-invoice-dollar d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data transaksi penjualan.
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
