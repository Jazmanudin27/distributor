@extends('layouts.app')
@section('title', 'Master Supplier')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-truck me-2"></i> Master Supplier
                </h5>
                <small class="text-white-50">Daftar partner supplier / penyalur barang</small>
            </div>
            @can('create-supplier')
                <a href="{{ route('supplier.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Tambah Supplier
                </a>
            @endcan
        </div>
        
        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('supplier.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Supplier</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Nama, Kode, atau Email..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary" title="Filter Data">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="150">Kode Supplier</th>
                            <th>Nama Supplier</th>
                            <th>Alamat</th>
                            <th>No HP</th>
                            <th>Email</th>
                            <th width="100">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $suppliers->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2.5 py-1">
                                        {{ $item->kode_supplier }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">{{ $item->nama_supplier }}</td>
                                <td class="text-secondary small">{{ Str::limit($item->alamat, 50) }}</td>
                                <td class="text-secondary small font-monospace">{{ $item->no_hp }}</td>
                                <td class="text-secondary small">{{ $item->email }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $item->status == 1 ? 'success' : 'secondary' }}-subtle text-{{ $item->status == 1 ? 'success' : 'secondary' }} border border-{{ $item->status == 1 ? 'success' : 'secondary' }}-subtle px-2 py-1 fw-bold fs-8">
                                        {{ $item->status == 1 ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        @can('edit-supplier')
                                            <a href="{{ route('supplier.edit', $item->kode_supplier) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-supplier')
                                            <form action="{{ route('supplier.destroy', $item->kode_supplier) }}" method="POST"
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-truck d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data supplier.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($suppliers->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
