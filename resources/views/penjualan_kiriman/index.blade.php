@extends('layouts.app')
@section('title', 'Kiriman Penjualan')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-truck-ramp-box me-2"></i> Kiriman Penjualan
                </h5>
                <small class="text-white-50">Kelola dan cetak rekapitulasi pengiriman sales per wilayah</small>
            </div>
            @can('create-penjualan_kiriman')
                <a href="{{ route('penjualan-kiriman.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-circle-plus me-1 text-white"></i> Tambah Kiriman
                </a>
            @endcan
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border">
                <form action="{{ route('penjualan-kiriman.index') }}" method="GET" class="row g-2 align-items-end">
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
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="Filter Data">
                            <i class="fa-solid fa-filter"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('penjualan-kiriman.index') }}" class="btn btn-outline-secondary btn-sm w-100"
                            title="Reset">
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
                            <th width="150">Tanggal Kirim</th>
                            <th>Wilayah</th>
                            <th width="150" class="text-center">Jumlah Faktur</th>
                            <th class="text-end" width="200">Total Nominal</th>
                            <th>Driver</th>
                            <th width="100" class="text-center">Status</th>
                            <th>Keterangan</th>
                            <th width="240" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td class="text-center text-secondary small fw-bold">{{ $items->firstItem() + $index }}</td>
                                <td class="small">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-M-Y') }}</td>
                                <td class="fw-bold text-dark">
                                    {{ $item->nama_wilayah }}
                                    <span
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1 fw-semibold fs-8 px-2">
                                        Kirim Ke-{{ $item->kirimanke }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-2 py-1">
                                        {{ $item->total_faktur }} Faktur
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    Rp {{ number_format((float) $item->total_nominal, 0, ',', '.') }}
                                </td>
                                <td class="small">
                                    <i class="fa-solid fa-user-tag text-secondary me-1"></i>{{ $item->driver_name ?? '-' }}
                                </td>
                                <td class="text-center">
                                    @if ($item->status == 'proses')
                                        <span class="badge bg-warning text-dark px-2 py-1">Proses</span>
                                    @elseif($item->status == 'kirim')
                                        <span class="badge bg-info text-white px-2 py-1">Kirim</span>
                                    @elseif($item->status == 'selesai')
                                        <span class="badge bg-success text-white px-2 py-1">Selesai</span>
                                    @elseif($item->status == 'batal')
                                        <span class="badge bg-danger text-white px-2 py-1">Batal</span>
                                    @else
                                        <span
                                            class="badge bg-secondary text-white px-2 py-1">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $item->keterangan ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('penjualan-kiriman.cetak-rekap', ['tanggal' => $item->tanggal, 'kode_wilayah' => $item->kode_wilayah, 'kirimanke' => $item->kirimanke]) }}"
                                            target="_blank" class="btn btn-sm btn-outline-info rounded"
                                            title="Cetak Rekap Kiriman">
                                            <i class="fa-solid fa-print"></i> Rekap
                                        </a>
                                        <a href="{{ route('penjualan-kiriman.cetak-barang', ['tanggal' => $item->tanggal, 'kode_wilayah' => $item->kode_wilayah, 'kirimanke' => $item->kirimanke]) }}"
                                            target="_blank" class="btn btn-sm btn-outline-warning rounded"
                                            title="Cetak Rekap Barang">
                                            <i class="fa-solid fa-boxes-stacked"></i> Barang
                                        </a>
                                        @can('edit-penjualan_kiriman')
                                            <a href="{{ route('penjualan-kiriman.edit', ['tanggal' => $item->tanggal, 'kode_wilayah' => $item->kode_wilayah, 'kirimanke' => $item->kirimanke]) }}"
                                                class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endcan
                                        @can('delete-penjualan_kiriman')
                                            <form action="{{ route('penjualan-kiriman.destroy') }}" method="POST"
                                                class="d-inline confirm-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="tanggal" value="{{ $item->tanggal }}">
                                                <input type="hidden" name="kode_wilayah" value="{{ $item->kode_wilayah }}">
                                                <input type="hidden" name="kirimanke" value="{{ $item->kirimanke }}">
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn rounded"
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
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-truck-ramp-box d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data kiriman penjualan.
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

@push('scripts')
    <script>
        $(document).ready(function() {
            // Delete confirmation with SweetAlert2
            $('.delete-btn').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Seluruh data rekap pengiriman untuk tanggal dan wilayah ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
