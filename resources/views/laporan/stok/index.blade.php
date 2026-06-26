@extends('layouts.app')
@section('title', 'Laporan & Buku Stok Barang')
@section('content')
    <div class="row justify-content-start py-4">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div
                    class="card-header card-premium-header text-white text-center py-4 border-0 d-flex flex-column align-items-center">
                    <h5 class="mb-1 fw-bold text-white">Laporan & Buku Stok</h5>
                    <p class="text-white-50 small mb-0" style="font-size: 11px;">Cetak rekap stok saat ini atau kartu riwayat
                        mutasi barang</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('laporan.stok.cetak') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Jenis Laporan</label>
                            <select name="jenis_laporan" id="jenis_laporan" class="form-select form-select-sm">
                                <option value="rekap" {{ request('jenis_laporan') === 'rekap' ? 'selected' : '' }}>Rekap Stok Saat Ini</option>
                                <option value="rekap_persediaan" {{ request('jenis_laporan') === 'rekap_persediaan' ? 'selected' : '' }}>Rekap Persediaan Stok (Good Stok)</option>
                                <option value="margin" {{ request('jenis_laporan') === 'margin' ? 'selected' : '' }}>Laporan Margin Barang (Stok Saat Ini)</option>
                                <option value="detail" {{ request('jenis_laporan') === 'detail' ? 'selected' : '' }}>Buku / Kartu Stok (Detail)</option>
                            </select>
                        </div>

                        {{-- SUPPLIER FILTER --}}
                        <div class="mb-3 filter-supplier d-none">
                            <label class="form-label fw-semibold text-secondary mb-1">Pilih Supplier</label>
                            <select name="kode_supplier" class="form-select form-select-sm select2-init" id="kode_supplier">
                                <option value="">-- Semua Supplier --</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->kode_supplier }}">
                                        {{ $s->nama_supplier }} ({{ $s->kode_supplier }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- REKAP FILTERS --}}
                        <div class="mb-3 filter-rekap">
                            <label class="form-label fw-semibold text-secondary mb-1">Kategori</label>
                            <select name="kategori" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($kategoris as $k)
                                    <option value="{{ $k->nama_kategori }}">
                                        {{ $k->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 filter-rekap">
                            <label class="form-label fw-semibold text-secondary mb-1">Merk</label>
                            <select name="merk" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Merk --</option>
                                @foreach ($merks as $m)
                                    <option value="{{ $m->nama_merk }}">
                                        {{ $m->nama_merk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 filter-rekap">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tampilkan_stok_kosong" id="tampilkan_stok_kosong" value="1" {{ request('tampilkan_stok_kosong') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-secondary" for="tampilkan_stok_kosong">
                                    Tampilkan Stok Kosong
                                </label>
                            </div>
                        </div>

                        {{-- DETAIL FILTERS --}}
                        <div class="mb-3 filter-barang d-none">
                            <label class="form-label fw-semibold text-secondary mb-1">Pilih Barang <span
                                    class="text-danger">*</span></label>
                            <select name="kode_barang" class="form-select form-select-sm select2-init" id="kode_barang">
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barangsList as $b)
                                    <option value="{{ $b->kode_barang }}">
                                        {{ $b->nama_barang }} ({{ $b->kode_barang }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DATES FILTER --}}
                        <div class="row g-2 filter-dates d-none mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Dari Tanggal</label>
                                <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                                    value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Sampai Tanggal</label>
                                <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.stok.cetak') }}'; this.form.target='_blank';"
                                    class="btn btn-primary w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-2"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-print"></i> Cetak
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.stok.excel') }}'; this.form.target='_self';"
                                    class="btn btn-success w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-2"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize select2
            $('.select2-init').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Toggle filter visibility based on report type select
            function toggleFilters() {
                const val = $('#jenis_laporan').val();
                if (val === 'rekap') {
                    $('.filter-rekap').removeClass('d-none');
                    $('.filter-barang').addClass('d-none');
                    $('.filter-supplier').addClass('d-none');
                    $('.filter-dates').addClass('d-none');
                    $('#kode_barang').prop('required', false);
                } else if (val === 'detail') {
                    $('.filter-rekap').addClass('d-none');
                    $('.filter-barang').removeClass('d-none');
                    $('.filter-supplier').addClass('d-none');
                    $('.filter-dates').removeClass('d-none');
                    $('#kode_barang').prop('required', true);
                } else if (val === 'rekap_persediaan') {
                    $('.filter-rekap').removeClass('d-none');
                    $('.filter-barang').addClass('d-none');
                    $('.filter-supplier').removeClass('d-none');
                    $('.filter-dates').removeClass('d-none');
                    $('#kode_barang').prop('required', false);
                } else if (val === 'margin') {
                    $('.filter-rekap').removeClass('d-none');
                    $('.filter-barang').addClass('d-none');
                    $('.filter-supplier').removeClass('d-none');
                    $('.filter-dates').addClass('d-none');
                    $('#kode_barang').prop('required', false);
                }
            }

            $('#jenis_laporan').on('change', toggleFilters);
            toggleFilters(); // run on load
        });
    </script>
@endpush
