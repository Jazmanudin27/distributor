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
            <div class="d-flex gap-2">
                @can('edit-barang')
                    <button type="button" id="btn-bulk-deactivate" class="btn btn-danger btn-sm fw-bold hover-scale d-none"
                        onclick="submitBulkDeactivate()">
                        <i class="fa-solid fa-thumbs-down me-1"></i> Nonaktifkan Masal
                    </button>
                @endcan
                @can('create-barang')
                    <a href="{{ route('barang.create') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-circle-plus me-1 text-primary"></i> Tambah Barang
                    </a>
                @endcan
            </div>
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
                            @can('edit-barang')
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="select-all-barang" class="form-check-input">
                                </th>
                            @endcan
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
                                @can('edit-barang')
                                    <td class="text-center">
                                        <input type="checkbox" value="{{ $item->kode_barang }}"
                                            class="form-check-input barang-checkbox">
                                    </td>
                                @endcan
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
                                            <form action="{{ route('barang.toggle-status', $item->kode_barang) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-outline-{{ $item->status ? 'success' : 'secondary' }} rounded"
                                                    title="{{ $item->status ? 'Non-aktifkan' : 'Aktifkan' }}">
                                                    <i class="fa-{{ $item->status ? 'solid' : 'regular' }} fa-thumbs-up"></i>
                                                </button>
                                            </form>
                                        @endcan
                                        @can('delete-barang')
                                            <form action="{{ route('barang.destroy', $item) }}" method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-delete rounded" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->can('edit-barang') ? 10 : 9 }}"
                                    class="text-center py-4 text-muted">
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

    @can('edit-barang')
        <form id="bulk-deactivate-form" action="{{ route('barang.bulk-deactivate') }}" method="POST"
            style="display: none;">
            @csrf
            <div id="bulk-deactivate-inputs"></div>
        </form>
    @endcan
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const selectAll = $('#select-all-barang');
            const checkboxes = $('.barang-checkbox');
            const btnBulk = $('#btn-bulk-deactivate');

            function toggleBulkButton() {
                const checkedCount = $('.barang-checkbox:checked').length;
                if (checkedCount > 0) {
                    btnBulk.removeClass('d-none');
                } else {
                    btnBulk.addClass('d-none');
                }
            }

            selectAll.on('change', function() {
                checkboxes.prop('checked', this.checked);
                toggleBulkButton();
            });

            $(document).on('change', '.barang-checkbox', function() {
                const checkedCount = $('.barang-checkbox:checked').length;
                const totalCount = $('.barang-checkbox').length;

                if (checkedCount === 0) {
                    selectAll.prop('checked', false);
                } else if (checkedCount === totalCount) {
                    selectAll.prop('checked', true);
                } else {
                    selectAll.prop('checked', false);
                }
                toggleBulkButton();
            });

            window.submitBulkDeactivate = function() {
                const checked = $('.barang-checkbox:checked');
                if (checked.length === 0) return;

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda akan menonaktifkan ${checked.length} barang secara masal!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Nonaktifkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const container = $('#bulk-deactivate-inputs');
                        container.empty();
                        checked.each(function() {
                            container.append(
                                `<input type="hidden" name="selected_ids[]" value="${$(this).val()}">`
                            );
                        });
                        $('#bulk-deactivate-form').submit();
                    }
                });
            };
        });
    </script>
@endpush
