@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('content')
    <div class="row justify-content-start py-4">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div
                    class="card-header card-premium-header text-white text-center py-4 border-0 d-flex flex-column align-items-center">
                    <h5 class="mb-1 fw-bold text-white">Laporan Laba Rugi</h5>
                    <p class="text-white-50 small mb-0" style="font-size: 11px;">Cetak ringkasan laba kotor penjualan berdasarkan HPP barang</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('laporan.laba-rugi.cetak') }}" method="GET" target="_blank">
                        <div class="row g-2 mb-4">
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

                        <div class="row g-2">
                            <div class="col-6">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.laba-rugi.cetak') }}'; this.form.target='_blank';"
                                    class="btn btn-primary w-100 py-2 fw-bold hover-scale shadow-sm d-flex align-items-center justify-content-center gap-2"
                                    style="height: 38px;">
                                    <i class="fa-solid fa-print"></i> Cetak
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" onclick="this.form.action='{{ route('laporan.laba-rugi.excel') }}'; this.form.target='_self';"
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
