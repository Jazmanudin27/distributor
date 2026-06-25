@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('content')
<div class="container-fluid p-0 py-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-chart-line"></i> Laporan Laba Rugi</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('cetakLabaRugi') }}" method="POST" target="_blank" autocomplete="off">
                        @csrf

                        {{-- Periode --}}
                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Tanggal Dari</label>
                                <input type="date" class="form-control form-control-sm" id="tanggal_dari"
                                    name="tanggal_dari" value="{{ request('tanggal_dari', date('Y-m-01')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Tanggal Sampai</label>
                                <input type="date" class="form-control form-control-sm" id="tanggal_sampai"
                                    name="tanggal_sampai" value="{{ request('tanggal_sampai', date('Y-m-d')) }}" required>
                            </div>
                        </div>

                        {{-- Supplier --}}
                        <div class="row mt-3" hidden>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-secondary mb-1">Supplier</label>
                                <select name="supplier" class="form-select2 form-select-sm">
                                    <option value="">-- Semua Supplier --</option>
                                    @foreach ($suppliers as $s)
                                        <option value="{{ $s->kode_supplier }}"
                                            {{ request('supplier') == $s->kode_supplier ? 'selected' : '' }}>
                                            {{ $s->nama_supplier }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold text-secondary mb-1">Jenis Laporan</label>
                                <select name="jenis_laporan" class="form-select2 form-select-sm" required>
                                    <option value="">-- Pilih Jenis Laporan --</option>
                                    <option value="1" {{ request('jenis_laporan') == '1' ? 'selected' : '' }}>
                                        Detail
                                    </option>
                                    <option value="2" {{ request('jenis_laporan') == '2' ? 'selected' : '' }}>
                                        Per Supplier
                                    </option>
                                    <option value="3" {{ request('jenis_laporan') == '3' ? 'selected' : '' }}>
                                        Per Tanggal
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row text-center mt-4">
                            <div class="col-md-6 mb-2">
                                <button type="submit" name="cetak" class="btn btn-sm btn-primary w-100 py-2 fw-bold shadow-sm">
                                    <i class="fa fa-print me-1"></i> Cetak Laporan
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="submit" name="export" class="btn btn-sm btn-success w-100 py-2 fw-bold shadow-sm">
                                    <i class="fa fa-file-excel me-1"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.form-select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
@endpush
