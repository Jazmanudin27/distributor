@extends('layouts.app')

@section('title', 'Detail Barang')

@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-12 col-md-12">
            <!-- Card Detail Barang -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <!-- Header Card dengan Gradien -->
                <div
                    class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-box fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Detail Master Barang</h5>
                            <small class="text-white-50">Lihat spesifikasi produk dan konfigurasi satuan harga</small>
                        </div>
                    </div>
                    <a href="{{ route('barang.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                        <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Informasi Utama (Kiri) -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-info-circle me-1 text-primary"></i> Informasi Produk
                            </h6>

                            <table class="table table-borderless align-middle my-2">
                                <tr>
                                    <td class="text-secondary fw-semibold py-2" width="150">Kode Barang (Sistem)</td>
                                    <td class="py-2">: <span
                                            class="badge bg-secondary font-monospace px-2.5 py-1.5 ms-2">{{ $item->kode_barang }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Kode Item (Manual)</td>
                                    <td class="py-2">: <span
                                            class="badge bg-light text-dark font-monospace px-2.5 py-1.5 ms-2 border">{{ $item->kode_item ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Nama Barang</td>
                                    <td class="py-2">: <span
                                            class="fw-bold text-dark ms-2">{{ $item->nama_barang }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Supplier Utama</td>
                                    <td class="py-2">: <span
                                            class="text-dark ms-2 fw-semibold">{{ $item->supplier->nama_supplier ?? '-' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Klasifikasi & Status (Kanan) -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-tags me-1 text-success"></i> Klasifikasi & Stok
                            </h6>

                            <table class="table table-borderless align-middle my-2">
                                <tr>
                                    <td class="text-secondary fw-semibold py-2" width="150">Kategori</td>
                                    <td class="py-2">: <span
                                            class="badge bg-success-subtle text-success px-2.5 py-1.5 ms-2 border border-success-subtle">{{ $item->kategori ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Merk</td>
                                    <td class="py-2">: <span
                                            class="badge bg-primary-subtle text-primary px-2.5 py-1.5 ms-2 border border-primary-subtle">{{ $item->merk ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Stok Saat Ini</td>
                                    <td class="py-2">: <span
                                            class="badge bg-info-subtle text-info-emphasis px-2.5 py-1.5 ms-2 border border-info-subtle fw-extrabold fs-7 font-monospace">{{ number_format((float) ($item->stok ?? 0), 2, ',', '.') }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Stok Minimal</td>
                                    <td class="py-2">: <span
                                            class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1.5 ms-2 border border-warning-subtle fw-bold">{{ $item->stok_min ?? 0 }}
                                            item</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-2">Status</td>
                                    <td class="py-2">:
                                        <span
                                            class="badge bg-{{ $item->status ? 'success' : 'secondary' }}-subtle text-{{ $item->status ? 'success' : 'secondary' }} border border-{{ $item->status ? 'success' : 'secondary' }}-subtle px-2.5 py-1.5 fw-bold ms-2">
                                            {{ $item->status ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Keterangan (Lebar Penuh) -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-2">
                                <i class="fa-solid fa-comment-dots me-1 text-info"></i> Keterangan / Catatan
                            </h6>
                            <p class="text-muted bg-light p-3 rounded border fs-7 mb-0">
                                {{ $item->keterangan ? $item->keterangan : 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Kelola Satuan (Unit Conversions) -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-scale-balanced me-2 text-primary"></i> Daftar Satuan & Konversi Harga
                        </h6>
                        <small class="text-muted">Konfigurasi harga jual-pokok per jenis satuan produk</small>
                    </div>
                    @can('create-barang_satuan')
                        <button type="button" class="btn btn-primary btn-sm fw-bold hover-scale" id="btn-tambah-satuan">
                            <i class="fa-solid fa-plus me-1"></i> Tambah Satuan
                        </button>
                    @endcan
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                                <tr>
                                    <th class="py-2 px-3">Satuan</th>
                                    <th class="py-2 px-3">Isi / Konversi</th>
                                    <th class="py-2 px-3">Harga Pokok</th>
                                    <th class="py-2 px-3">Harga Jual</th>
                                    @if (Auth::user()->can('edit-barang_satuan') || Auth::user()->can('delete-barang_satuan'))
                                        <th class="py-2 px-3 text-center" width="120">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->satuans as $satuan)
                                    <tr>
                                        <td class="px-3">
                                            <span
                                                class="badge bg-info-subtle text-info px-2.5 py-1.5 border border-info-subtle font-monospace fw-bold">
                                                {{ $satuan->satuan }}
                                            </span>
                                        </td>
                                        <td class="px-3 text-dark fw-semibold">
                                            Isi {{ $satuan->isi }} item
                                        </td>
                                        <td class="px-3 text-success fw-semibold">
                                            Rp {{ number_format((float) $satuan->harga_pokok, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 text-primary fw-bold">
                                            Rp {{ number_format((float) $satuan->harga_jual, 0, ',', '.') }}
                                        </td>
                                        @if (Auth::user()->can('edit-barang_satuan') || Auth::user()->can('delete-barang_satuan'))
                                            <td class="px-3 text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    @can('edit-barang_satuan')
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm rounded-circle shadow-sm btn-edit-satuan"
                                                            data-id="{{ $satuan->id }}" data-satuan="{{ $satuan->satuan }}"
                                                            data-isi="{{ $satuan->isi }}"
                                                            data-harga_pokok="{{ (int) $satuan->harga_pokok }}"
                                                            data-harga_jual="{{ (int) $satuan->harga_jual }}" title="Edit"
                                                            style="width: 30px; height: 30px; padding: 4px;">
                                                            <i class="fa-regular fa-pen-to-square"></i>
                                                        </button>
                                                    @endcan
                                                    @can('delete-barang_satuan')
                                                        <form action="{{ route('barang_satuan.destroy', $satuan->id) }}"
                                                            method="POST" class="d-inline delete-form delete">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button"
                                                                class="btn btn-outline-danger btn-sm rounded-circle shadow-sm btn-delete"
                                                                title="Hapus" style="width: 30px; height: 30px; padding: 4px;">
                                                                <i class="fa-regular fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-scale-unbalanced d-block fs-3 mb-2 opacity-50"></i>
                                            Belum ada data satuan / harga konversi yang diatur untuk produk ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Satuan (Dinamis untuk Tambah & Edit) -->
    @if (Auth::user()->can('create-barang_satuan') || Auth::user()->can('edit-barang_satuan'))
        <div class="modal fade" id="satuanModal" tabindex="-1" aria-labelledby="satuanModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-3 border-0 shadow-lg">
                    <div class="modal-header card-premium-header text-white py-3">
                        <h5 class="modal-title fw-bold" id="satuanModalLabel">
                            <i class="fa-solid fa-scale-balanced me-2"></i> <span id="satuanModalTitle">Tambah Satuan &
                                Harga</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form id="satuanForm" action="" method="POST">
                        @csrf
                        <div id="method-field-container"></div>
                        <input type="hidden" name="kode_barang" value="{{ $item->kode_barang }}">
                        <input type="hidden" name="redirect_to" value="{{ route('barang.show', $item->kode_barang) }}">

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <!-- Kode Barang (Readonly) -->
                                <div class="col-12">
                                    <label class="form-label fs-7 fw-bold text-secondary mb-1">Barang</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        value="{{ $item->nama_barang }} ({{ $item->kode_barang }})" readonly>
                                </div>

                                <!-- Nama Satuan -->
                                <div class="col-md-6">
                                    <label for="modal_satuan" class="form-label fs-7 fw-bold text-secondary mb-1">Nama
                                        Satuan <span class="text-danger">*</span></label>
                                    <input type="text" name="satuan" id="modal_satuan"
                                        class="form-control form-control-sm" placeholder="Contoh: DUS, PACK, PCS"
                                        required>
                                </div>

                                <!-- Isi / Konversi -->
                                <div class="col-md-6">
                                    <label for="modal_isi" class="form-label fs-7 fw-bold text-secondary mb-1">Isi /
                                        Konversi <span class="text-danger">*</span></label>
                                    <input type="number" name="isi" id="modal_isi"
                                        class="form-control form-control-sm" min="1" placeholder="Jumlah isi..."
                                        required>
                                </div>

                                <!-- Harga Pokok -->
                                <div class="col-md-6">
                                    <label for="modal_harga_pokok"
                                        class="form-label fs-7 fw-bold text-secondary mb-1">Harga Pokok <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="harga_pokok" id="modal_harga_pokok"
                                            class="form-control form-control-sm" placeholder="0" required>
                                    </div>
                                </div>

                                <!-- Harga Jual -->
                                <div class="col-md-6">
                                    <label for="modal_harga_jual"
                                        class="form-label fs-7 fw-bold text-secondary mb-1">Harga Jual <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="harga_jual" id="modal_harga_jual"
                                            class="form-control form-control-sm" placeholder="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer p-3 bg-light border-top">
                            <button type="button" class="btn btn-light btn-sm px-3 border"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Event klik Tambah Satuan
            $('#btn-tambah-satuan').on('click', function() {
                $('#satuanForm').attr('action', "{{ route('barang_satuan.store') }}");
                $('#method-field-container').html(''); // Kosongkan method PUT
                $('#satuanModalTitle').text('Tambah Satuan & Harga');

                // Reset field modal
                $('#modal_satuan').val('');
                $('#modal_isi').val('');
                $('#modal_harga_pokok').val('');
                $('#modal_harga_jual').val('');

                $('#satuanModal').modal('show');
            });

            // Event klik Edit Satuan
            $('.btn-edit-satuan').on('click', function() {
                let id = $(this).data('id');
                let satuan = $(this).data('satuan');
                let isi = $(this).data('isi');
                let harga_pokok = $(this).data('harga_pokok');
                let harga_jual = $(this).data('harga_jual');

                // Arahkan url action ke route update
                let updateUrl = "{{ route('barang_satuan.update', ':id') }}".replace(':id', id);
                $('#satuanForm').attr('action', updateUrl);

                // Tambahkan input hidden method PUT
                $('#method-field-container').html('<input type="hidden" name="_method" value="PUT">');
                $('#satuanModalTitle').text('Edit Satuan & Harga');

                // Isi nilai ke input modal
                $('#modal_satuan').val(satuan);
                $('#modal_isi').val(isi);
                $('#modal_harga_pokok').val(harga_pokok);
                $('#modal_harga_jual').val(harga_jual);

                $('#satuanModal').modal('show');
            });
        });
    </script>
@endpush
