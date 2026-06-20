@extends('layouts.app')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-box-archive me-2"></i> Master Barang
                </h5>
                <small class="text-white-50">Daftar produk, jenis, kategori, dan status stok saat ini</small>
            </div>
            @can('create-barang')
                <a href="{{ route('barang.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Tambah Barang
                </a>
            @endcan
        </div>
        {{-- CARD BODY --}}
        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('barang.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Barang</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Nama atau Kode..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Kategori</label>
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($kategoris as $k)
                                <option value="{{ $k->nama_kategori }}"
                                    {{ request('kategori') == $k->nama_kategori ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Merk</label>
                        <select name="merk" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($merks as $m)
                                <option value="{{ $m->nama_merk }}"
                                    {{ request('merk') == $m->nama_merk ? 'selected' : '' }}>
                                    {{ $m->nama_merk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Supplier</label>
                        <select name="kode_supplier" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->kode_supplier }}"
                                    {{ request('kode_supplier') == $s->kode_supplier ? 'selected' : '' }}>
                                    {{ $s->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
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
                    <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="120">Kode</th>
                            <th width="120">Kode Item</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Merk</th>
                            <th width="100" class="text-end pe-3">Stok</th>
                            <th width="100">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($items as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace px-2.5 py-1">
                                        {{ $item->kode_barang }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace px-2.5 py-1 border">
                                        {{ $item->kode_item ?? '-' }}
                                    </span>
                                </td>

                                <td class="fw-bold text-dark">
                                    <div>{{ $item->nama_barang }}</div>
                                    <div class="text-muted small fw-normal mt-1" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-truck me-1 text-secondary opacity-75"></i>
                                        <span
                                            class="text-primary fw-semibold">{{ $item->supplier->nama_supplier ?? '-' }}</span>
                                    </div>
                                </td>

                                <td class="text-secondary small fw-semibold">
                                    {{ $item->kategori ?? '-' }}
                                </td>

                                <td class="text-secondary small fw-semibold">
                                    {{ $item->merk ?? '-' }}
                                </td>

                                <td class="text-end pe-3 fw-bold font-monospace text-dark">
                                    {{ number_format((float) ($item->stok ?? 0), 2, ',', '.') }}
                                </td>

                                <td>
                                    <span
                                        class="badge bg-{{ $item->status ? 'success' : 'secondary' }}-subtle text-{{ $item->status ? 'success' : 'secondary' }} border border-{{ $item->status ? 'success' : 'secondary' }}-subtle px-2 py-1 fw-bold fs-8">
                                        {{ $item->status ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('barang.show', $item) }}"
                                            class="btn btn-sm btn-outline-secondary rounded" title="Detail / Kelola Satuan">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('edit-barang')
                                            <a href="{{ route('barang.edit', $item) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-barang')
                                            <form action="{{ route('barang.destroy', $item) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete rounded"
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
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-box-open d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data barang.
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
