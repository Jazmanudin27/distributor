@extends('layouts.app')
@section('title', 'Master Merk')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-tag me-2"></i> Master Merk
                </h5>
                <small class="text-white-50">Daftar merk / brand produk barang</small>
            </div>
            @can('create-merk')
                <a href="{{ route('merk.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Tambah Merk
                </a>
            @endcan
        </div>
        
        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('merk.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Merk</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Nama Merk..." value="{{ request('search') }}">
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
                            <th>Nama Merk</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($merks as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $merks->firstItem() + $index }}</td>
                                <td class="fw-bold text-dark">{{ $item->nama_merk }}</td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        @can('edit-merk')
                                            <a href="{{ route('merk.edit', $item->id) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-merk')
                                            <form action="{{ route('merk.destroy', $item->id) }}" method="POST"
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
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-tag d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data merk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($merks->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $merks->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
