@extends('layouts.app')
@section('title', 'Master Wilayah')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-map-location-dot me-2"></i> Master Wilayah
                </h5>
                <small class="text-white-50">Daftar wilayah pemasaran dan area pengiriman</small>
            </div>
            @can('create-wilayah')
                <a href="{{ route('wilayah.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Tambah Wilayah
                </a>
            @endcan
        </div>
        
        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('wilayah.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Wilayah</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Kode atau Nama Wilayah..." value="{{ request('search') }}">
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
                            <th width="140" class="text-center">Kode Wilayah</th>
                            <th>Nama Wilayah</th>
                            <th width="140" class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wilayahs as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $wilayahs->firstItem() + $index }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border fw-mono fs-7">{{ $item->kode_wilayah }}</span></td>
                                <td class="fw-bold text-dark">{{ $item->nama_wilayah }}</td>
                                <td class="text-center">
                                    <form action="{{ route('wilayah.toggle-status', $item->kode_wilayah) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if (($item->status ?? 1) == 1)
                                            <button type="submit" class="btn btn-sm btn-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold fs-7 hover-scale"
                                                style="cursor: pointer;" title="Klik untuk menonaktifkan">
                                                <i class="fa-solid fa-thumbs-up me-1"></i> Aktif
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold fs-7 hover-scale"
                                                style="cursor: pointer;" title="Klik untuk mengaktifkan">
                                                <i class="fa-solid fa-thumbs-down me-1"></i> Non-Aktif
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        @can('edit-wilayah')
                                            <a href="{{ route('wilayah.edit', $item->kode_wilayah) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-wilayah')
                                            <form action="{{ route('wilayah.destroy', $item->kode_wilayah) }}" method="POST"
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
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-map-location-dot d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data wilayah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($wilayahs->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $wilayahs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
