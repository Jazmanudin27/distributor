@extends('layouts.app')
@section('title', 'Retur Penjualan')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-rotate-right me-2"></i> Retur Penjualan
                </h5>
                <small class="text-white-50">Kelola transaksi penerimaan retur barang dari pelanggan</small>
            </div>
            @can('create-retur_penjualan')
                <a href="{{ route('retur-penjualan.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Retur Baru
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('retur-penjualan.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Transaksi</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="No Retur, No Faktur, Pelanggan..." value="{{ request('search') }}">
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
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Jenis Retur</label>
                        <select name="jenis_retur" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="Barang Rusak" {{ request('jenis_retur') === 'Barang Rusak' ? 'selected' : '' }}>Barang Rusak</option>
                            <option value="Salah Kirim" {{ request('jenis_retur') === 'Salah Kirim' ? 'selected' : '' }}>Salah Kirim</option>
                            <option value="Lainnya" {{ request('jenis_retur') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Filter Data">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('retur-penjualan.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Reset">
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
                            <th width="160">No Retur</th>
                            <th width="110">Tanggal</th>
                            <th>Pelanggan</th>
                            <th width="120">Sales</th>
                            <th width="160">No Faktur</th>
                            <th width="130">Jenis Retur</th>
                            <th class="text-end" width="150">Total Retur</th>
                            <th>Keterangan</th>
                            <th width="140" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2 py-1">
                                        {{ $item->no_retur }}
                                    </span>
                                </td>
                                <td class="small">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="fw-bold text-dark">
                                    <div>{{ $item->pelanggan->nama_pelanggan ?? '-' }}</div>
                                    <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                        <span class="font-monospace text-secondary">{{ $item->kode_pelanggan }}</span>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark">
                                    @if($item->sales)
                                        <div>{{ $item->sales->name }}</div>
                                        <div class="text-muted small fw-normal" style="font-size: 0.78rem;">
                                            <span class="font-monospace text-secondary">{{ $item->kode_sales }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fw-normal">{{ $item->kode_sales ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->no_faktur)
                                        <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                            {{ $item->no_faktur }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-8 fw-semibold">
                                        {{ $item->jenis_retur }}
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    Rp {{ number_format((float) $item->total, 0, ',', '.') }}
                                </td>
                                <td class="text-muted small">{{ Str::limit($item->keterangan, 40) }}</td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('retur-penjualan.print', $item->no_retur) }}" target="_blank"
                                            class="btn btn-sm btn-outline-info rounded" title="Cetak Faktur">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        <a href="{{ route('retur-penjualan.show', $item->no_retur) }}"
                                            class="btn btn-sm btn-outline-secondary rounded" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('edit-retur_penjualan')
                                            <a href="{{ route('retur-penjualan.edit', $item->no_retur) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-retur_penjualan')
                                            <form action="{{ route('retur-penjualan.destroy', $item->no_retur) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete rounded"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-show-logs rounded"
                                            data-no-faktur="{{ $item->no_retur }}" title="Riwayat Aktivitas">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-rotate-right d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data transaksi retur penjualan.
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
