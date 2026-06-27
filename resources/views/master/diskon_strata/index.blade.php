@extends('layouts.app')
@section('title', 'Master Diskon Strata')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-percent me-2"></i> Master Diskon Strata
                </h5>
                <small class="text-white-50">Atur diskon berjenjang (strata) berdasarkan Qty, Kategori, Merk, atau
                    Supplier</small>
            </div>
            <a href="{{ route('diskon-strata.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-circle-plus me-1 text-white"></i> Tambah Diskon Strata
            </a>
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('diskon-strata.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Nama Diskon</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Nama promo..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Filter Tipe</label>
                        <select name="tipe" class="form-select form-select-sm">
                            <option value="">-- Semua Tipe --</option>
                            <option value="barang" {{ request('tipe') === 'barang' ? 'selected' : '' }}>Per Barang</option>
                            <option value="beberapa_barang" {{ request('tipe') === 'beberapa_barang' ? 'selected' : '' }}>
                                Per Beberapa Barang</option>
                            <option value="kategori" {{ request('tipe') === 'kategori' ? 'selected' : '' }}>Per Kategori
                            </option>
                            <option value="merk" {{ request('tipe') === 'merk' ? 'selected' : '' }}>Per Merk</option>
                            <option value="supplier" {{ request('tipe') === 'supplier' ? 'selected' : '' }}>Per Supplier
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100" title="Filter Data">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Nama Diskon</th>
                            <th width="150" class="text-center">Tipe</th>
                            <th>Target Scope</th>
                            <th width="200" class="text-center">Periode Berlaku</th>
                            <th width="100" class="text-center">Status</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->nama_diskon }}</div>
                                    <small class="text-muted fs-8 font-monospace">ID: #{{ $item->id }}</small>
                                </td>
                                <td class="text-center">
                                    @php
                                        $badges = [
                                            'barang' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'beberapa_barang' => 'bg-info-subtle text-info border border-info-subtle',
                                            'kategori' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            'merk' => 'bg-purple-subtle text-purple border border-purple-subtle',
                                            'supplier' => 'bg-success-subtle text-success border border-success-subtle',
                                        ];
                                        $label = [
                                            'barang' => 'Per Barang',
                                            'beberapa_barang' => 'Per Beberapa Barang',
                                            'kategori' => 'Per Kategori',
                                            'merk' => 'Per Merk',
                                            'supplier' => 'Per Supplier',
                                        ];
                                    @endphp
                                    <span class="badge {{ $badges[$item->tipe] ?? 'bg-secondary' }} px-2 py-1 fs-8">
                                        {{ $label[$item->tipe] ?? $item->tipe }}
                                    </span>
                                </td>
                                <td>
                                    @if ($item->tipe === 'barang')
                                        <span class="text-secondary small">Barang:</span>
                                        <strong>{{ $item->barangs->first()->nama_barang ?? '-' }}</strong>
                                    @elseif ($item->tipe === 'beberapa_barang')
                                        <span class="text-secondary small">Barang:</span>
                                        <strong>{{ $item->barangs->count() }} item terpilih</strong>
                                    @elseif ($item->tipe === 'kategori')
                                        <span class="text-secondary small">Kategori:</span>
                                        <strong>{{ $item->kategori->nama_kategori ?? '-' }}</strong>
                                    @elseif ($item->tipe === 'merk')
                                        <span class="text-secondary small">Merk:</span>
                                        <strong>{{ $item->merk->nama_merk ?? '-' }}</strong>
                                    @elseif ($item->tipe === 'supplier')
                                        <span class="text-secondary small">Supplier:</span>
                                        <strong>{{ $item->supplier->nama_supplier ?? '-' }}</strong>
                                    @endif
                                </td>
                                <td class="text-center small">
                                    @if ($item->berlaku_dari || $item->berlaku_sampai)
                                        <span class="font-monospace text-secondary">
                                            {{ $item->berlaku_dari ? $item->berlaku_dari->format('d/m/Y') : '∞' }} -
                                            {{ $item->berlaku_sampai ? $item->berlaku_sampai->format('d/m/Y') : '∞' }}
                                        </span>
                                    @else
                                        <span class="text-muted">Selamanya</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('diskon-strata.toggle-status', $item->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @if ($item->is_active)
                                            <button type="submit"
                                                class="badge bg-success px-2 py-1 fs-8 border-0 hover-scale text-decoration-none"
                                                style="cursor: pointer;" title="Klik untuk menonaktifkan">
                                                <i class="fa-solid fa-toggle-on me-1"></i> Aktif
                                            </button>
                                        @else
                                            <button type="submit"
                                                class="badge bg-danger px-2 py-1 fs-8 border-0 hover-scale text-decoration-none"
                                                style="cursor: pointer;" title="Klik untuk mengaktifkan">
                                                <i class="fa-solid fa-toggle-off me-1"></i> Non-Aktif
                                            </button>
                                        @endif
                                    </form>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('diskon-strata.show', $item->id) }}"
                                            class="btn btn-sm btn-outline-info rounded" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('diskon-strata.edit', $item->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('diskon-strata.destroy', $item->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete rounded"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-percent d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data diskon strata.
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
