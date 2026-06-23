@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('content')
    <div class="row justify-content-start py-4">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div
                    class="card-header card-premium-header text-white text-center py-4 border-0 d-flex flex-column align-items-center">
                    <h5 class="mb-1 fw-bold text-white">Laporan Penjualan</h5>
                    <p class="text-white-50 small mb-0" style="font-size: 11px;">Cetak rekapitulasi atau detail transaksi
                        penjualan</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('laporan.penjualan.cetak') }}" method="GET" target="_blank">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                                    value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Tanggal Akhir</label>
                                <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Salesman</label>
                            <select name="kode_sales" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Salesman --</option>
                                @foreach ($salesmen as $s)
                                    <option value="{{ $s->nik }}">
                                        {{ $s->name }} ({{ $s->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Pelanggan (Toko)</label>
                            <select name="kode_pelanggan" id="kode_pelanggan"
                                class="form-select form-select-sm select2-pelanggan-ajax">
                                <option value="">-- Semua Pelanggan --</option>
                                @foreach ($pelanggans as $p)
                                    <option value="{{ $p->kode_pelanggan }}" selected>
                                        {{ $p->nama_pelanggan }} ({{ $p->kode_pelanggan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Supplier</label>
                            <select name="kode_supplier" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Supplier --</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->kode_supplier }}">
                                        {{ $sup->nama_supplier }} ({{ $sup->kode_supplier }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Pilih Jenis Laporan</label>
                            <select name="jenis_laporan" class="form-select form-select-sm">
                                <option value="rekap">Format 1: Rekap (Per Invoice)</option>
                                <option value="detail">Format 2: Detail (Per Barang - Kolom Lengkap)</option>
                                <option value="detail_simple">Format 3: Detail Simple (Sesuai Layout)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Jenis Transaksi</label>
                            <select name="jenis_transaksi" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Jenis Transaksi --</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Kredit">Kredit</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary mb-1">Status Faktur</label>
                            <select name="status_faktur" class="form-select form-select-sm select2-init">
                                <option value="aktif">Faktur Aktif</option>
                                <option value="batal">Faktur Batal</option>
                                <option value="semua">Semua Faktur</option>
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit"
                                    onclick="this.form.action='{{ route('laporan.penjualan.cetak') }}'; this.form.target='_blank';"
                                    class="btn btn-primary w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-2"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-print"></i> Cetak
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit"
                                    onclick="this.form.action='{{ route('laporan.penjualan.excel') }}'; this.form.target='_self';"
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
            $('.select2-init').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            $('.select2-pelanggan-ajax').select2({
                theme: 'bootstrap-5',
                width: '100%',
                ajax: {
                    url: '{{ route('pelanggan.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0
            });
        });
    </script>
@endpush
