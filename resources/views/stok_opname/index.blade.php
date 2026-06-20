@extends('layouts.app')
@section('title', 'Stok Opname')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-clipboard-list me-2"></i> Stok Opname
                </h5>
                <small class="text-white-50">Sesuaikan data stok sistem dengan jumlah fisik di gudang</small>
            </div>
            @can('create-stok_opname')
                <a href="{{ route('stok-opname.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Opname Baru
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('stok-opname.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Transaksi</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="No Opname, keterangan..." value="{{ request('search') }}">
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
                            <th width="150">No Opname</th>
                            <th width="120">Tanggal</th>
                            <th width="150" class="text-center">Jumlah Item</th>
                            <th>Keterangan</th>
                            <th width="150">Operator</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2.5 py-1">
                                        {{ $item->no_opname }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td class="text-center fw-bold">
                                    {{ $item->details->count() }} item
                                </td>
                                <td class="text-muted small">
                                    {{ $item->keterangan ? Str::limit($item->keterangan, 50) : '-' }}
                                </td>
                                <td class="fw-semibold text-dark">
                                    {{ $item->user->name ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('stok-opname.show', $item->no_opname) }}"
                                            class="btn btn-sm btn-outline-secondary rounded" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('edit-stok_opname')
                                            <a href="{{ route('stok-opname.edit', $item->no_opname) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-stok_opname')
                                            <form action="{{ route('stok-opname.destroy', $item->no_opname) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete rounded"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-clipboard-list d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data transaksi stok opname.
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
