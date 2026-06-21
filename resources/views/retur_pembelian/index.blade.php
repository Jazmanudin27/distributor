@extends('layouts.app')
@section('title', 'Retur Pembelian')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-rotate-left me-2"></i> Retur Pembelian
                </h5>
                <small class="text-white-50">Kelola pengembalian barang ke supplier</small>
            </div>
            @can('create-retur_pembelian')
                <a href="{{ route('retur-pembelian.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Retur Baru
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('retur-pembelian.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Transaksi</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="No Retur, Faktur, Supplier..." value="{{ request('search') }}">
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
                            <option value="PF"
                                {{ request('jenis_retur') === 'PF' ? 'selected' : '' }}>PF (Potong Faktur)</option>
                            <option value="Tukar Barang" {{ request('jenis_retur') === 'Tukar Barang' ? 'selected' : '' }}>
                                Tukar Barang</option>
                            <option value="Cash" {{ request('jenis_retur') === 'Cash' ? 'selected' : '' }}>Cash / Refund
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Kondisi Barang</label>
                        <select name="kondisi" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="Baik" {{ request('kondisi') === 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak" {{ request('kondisi') === 'Rusak' ? 'selected' : '' }}>Rusak / BS
                            </option>
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
                            <th width="150">No Retur</th>
                            <th width="120">Tanggal</th>
                            <th width="150">No Faktur Asal</th>
                            <th>Supplier</th>
                            <th class="text-end">Total Retur</th>
                            <th width="120" class="text-center">Jenis</th>
                            <th width="120" class="text-center">Kondisi</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2.5 py-1">
                                        {{ $item->no_retur }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>
                                    @if ($item->no_faktur)
                                        <a href="{{ route('pembelian.show', $item->no_faktur) }}"
                                            class="font-monospace text-primary fw-bold text-decoration-none">
                                            {{ $item->no_faktur }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ $item->supplier->nama_supplier ?? '-' }}
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    Rp {{ number_format((float) $item->total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                        {{ $item->jenis_retur }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ $item->kondisi == 'Baik' ? 'success' : 'danger' }}-subtle text-{{ $item->kondisi == 'Baik' ? 'success' : 'danger' }}-emphasis border border-{{ $item->kondisi == 'Baik' ? 'success' : 'danger' }}-subtle px-2 py-1">
                                        {{ $item->kondisi }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('retur-pembelian.show', $item->no_retur) }}"
                                            class="btn btn-sm btn-outline-secondary rounded" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('edit-retur_pembelian')
                                            <a href="{{ route('retur-pembelian.edit', $item->no_retur) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-retur_pembelian')
                                            <form action="{{ route('retur-pembelian.destroy', $item->no_retur) }}"
                                                method="POST" class="d-inline delete-form">
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
                                            data-no-faktur="{{ $item->no_retur }}" title="Riwayat Aktivitas">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-rotate-left d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data transaksi retur pembelian.
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
