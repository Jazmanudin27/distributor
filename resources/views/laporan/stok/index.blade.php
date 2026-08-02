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
                            <div class="col-4">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.stok') }}'; this.form.target='_self';"
                                    class="btn btn-primary w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-1"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-eye"></i> Tampilkan
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.stok.cetak') }}'; this.form.target='_blank';"
                                    class="btn btn-outline-primary w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-1"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-print"></i> Cetak
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.stok.excel') }}'; this.form.target='_self';"
                                    class="btn btn-success w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-1"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('laporan.stok.saldo-awal.index') }}" 
                               class="btn btn-outline-secondary w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-2"
                               style="height: 38px;">
                                <i class="fa-solid fa-calculator"></i> Setting / Generate Saldo Awal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if (isset($barang) && $barang)
            <div class="col-12 mt-4">
                <div class="card shadow border-0 rounded-4 overflow-hidden">
                    <div class="card-header card-premium-header text-white py-3 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold text-white">
                                <i class="fa-solid fa-clock-rotate-left me-2"></i> Kartu Mutasi Stok: {{ $barang->nama_barang }}
                            </h5>
                            <small class="text-white-50">Kode: <span class="font-monospace fw-bold text-white">{{ $barang->kode_barang }}</span> | Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-white text-dark py-2 px-3 fs-7">
                                Saldo Awal: <strong>{{ $barang->formatStok($stokAwal) }}</strong>
                            </span>
                            <span class="badge bg-success py-2 px-3 fs-7">
                                Saldo Akhir: <strong>{{ $barang->formatStok($stokAkhir) }}</strong>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover align-middle mb-0 text-sm">
                                <thead class="table-dark text-center align-middle">
                                    <tr>
                                        <th rowspan="2" width="40">No</th>
                                        <th rowspan="2" width="100">Tanggal</th>
                                        <th rowspan="2" width="130">No. Referensi</th>
                                        <th rowspan="2">Keterangan / Pelanggan / Sales</th>
                                        <th colspan="4" class="bg-success text-white py-1">PENERIMAAN (MASUK)</th>
                                        <th colspan="3" class="bg-danger text-white py-1">PENGELUARAN (KELUAR)</th>
                                        <th rowspan="2" width="130">Saldo Running</th>
                                    </tr>
                                    <tr class="table-secondary text-dark small text-center">
                                        <th width="80" class="bg-success bg-opacity-25">Pembelian</th>
                                        <th width="80" class="bg-success bg-opacity-25">Retur Jual</th>
                                        <th width="80" class="bg-success bg-opacity-25">Batal Jual</th>
                                        <th width="80" class="bg-success bg-opacity-25">Opname (+)</th>
                                        <th width="80" class="bg-danger bg-opacity-25">Penjualan</th>
                                        <th width="80" class="bg-danger bg-opacity-25">Retur Beli</th>
                                        <th width="80" class="bg-danger bg-opacity-25">Opname (-)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($movements as $index => $m)
                                        <tr>
                                            <td class="text-center font-monospace">{{ $index + 1 }}</td>
                                            <td class="text-center font-monospace small">
                                                {{ \Carbon\Carbon::parse($m['tanggal'])->format('d/m/Y') }}
                                            </td>
                                            <td class="font-monospace fw-bold text-primary">{{ $m['no_referensi'] }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $m['keterangan'] }}</div>
                                                @if (isset($m['pelanggan']) && $m['pelanggan'] && $m['pelanggan'] !== '-')
                                                    <div class="small text-muted"><i class="fa-solid fa-user me-1"></i> {{ $m['pelanggan'] }} ({{ $m['wilayah'] ?? '-' }})</div>
                                                @endif
                                                @if (isset($m['nama_sales']) && $m['nama_sales'] && $m['nama_sales'] !== '-')
                                                    <div class="small text-muted"><i class="fa-solid fa-id-badge me-1"></i> Sales: {{ $m['nama_sales'] }}</div>
                                                @endif
                                            </td>

                                            {{-- MASUK --}}
                                            <td class="text-end font-monospace text-success fw-bold">
                                                {{ $m['pembelian_masuk'] > 0 ? $barang->formatStok($m['pembelian_masuk']) : '-' }}
                                            </td>
                                            <td class="text-end font-monospace text-success fw-bold">
                                                {{ $m['retur_jual'] > 0 ? $barang->formatStok($m['retur_jual']) : '-' }}
                                            </td>
                                            <td class="text-end font-monospace text-success fw-bold">
                                                {{ isset($m['batal_jual']) && $m['batal_jual'] > 0 ? $barang->formatStok($m['batal_jual']) : '-' }}
                                            </td>
                                            <td class="text-end font-monospace text-success fw-bold">
                                                {{ $m['opname_masuk'] > 0 ? $barang->formatStok($m['opname_masuk']) : '-' }}
                                            </td>

                                            {{-- KELUAR --}}
                                            <td class="text-end font-monospace text-danger fw-bold">
                                                {{ $m['penjualan_keluar'] > 0 ? $barang->formatStok($m['penjualan_keluar']) : '-' }}
                                            </td>
                                            <td class="text-end font-monospace text-danger fw-bold">
                                                {{ $m['retur_beli'] > 0 ? $barang->formatStok($m['retur_beli']) : '-' }}
                                            </td>
                                            <td class="text-end font-monospace text-danger fw-bold">
                                                {{ $m['opname_keluar'] > 0 ? $barang->formatStok($m['opname_keluar']) : '-' }}
                                            </td>

                                            {{-- SALDO --}}
                                            <td class="text-end font-monospace fw-bold text-dark">
                                                {{ $barang->formatStok($m['saldo']) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-4 text-muted">
                                                <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                                Tidak ada riwayat mutasi stok untuk barang ini dalam periode tanggal tersebut.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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

                // Dynamic required attribute, label asterisk, and placeholder for kode_barang
                if (val === 'detail') {
                    $('.filter-barang label').html('Pilih Barang <span class="text-danger">*</span>');
                    $('#kode_barang option[value=""]').text('-- Pilih Barang --');
                    $('#kode_barang').prop('required', true);
                } else {
                    $('.filter-barang label').html('Pilih Barang');
                    $('#kode_barang option[value=""]').text('-- Semua Barang --');
                    $('#kode_barang').prop('required', false);
                }

                // Refresh select2 to display updated option text
                if ($('#kode_barang').hasClass('select2-hidden-accessible')) {
                    $('#kode_barang').select2('destroy');
                }
                $('#kode_barang').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });

                if (val === 'rekap') {
                    $('.filter-rekap').removeClass('d-none');
                    $('.filter-barang').addClass('d-none');
                    $('.filter-supplier').addClass('d-none');
                    $('.filter-dates').addClass('d-none');
                } else if (val === 'detail') {
                    $('.filter-rekap').addClass('d-none');
                    $('.filter-barang').removeClass('d-none');
                    $('.filter-supplier').addClass('d-none');
                    $('.filter-dates').removeClass('d-none');
                } else if (val === 'rekap_persediaan') {
                    $('.filter-rekap').removeClass('d-none');
                    $('.filter-barang').removeClass('d-none');
                    $('.filter-supplier').removeClass('d-none');
                    $('.filter-dates').removeClass('d-none');
                } else if (val === 'margin') {
                    $('.filter-rekap').removeClass('d-none');
                    $('.filter-barang').addClass('d-none');
                    $('.filter-supplier').removeClass('d-none');
                    $('.filter-dates').addClass('d-none');
                }
            }

            $('#jenis_laporan').on('change', toggleFilters);
            toggleFilters(); // run on load
        });
    </script>
@endpush
