@extends('layouts.app')
@section('title', 'Laporan Barang Kanvas')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Laporan Barang Kanvas</h5>
                <small class="text-white-50 font-12">Laporan pengambilan, penjualan, dan sisa barang kanvas salesman</small>
            </div>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold hover-scale d-print-none">
                <i class="fa-solid fa-print me-1 text-white"></i> Cetak Laporan
            </button>
        </div>
        <div class="card-body p-4">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('canvas.report') }}" class="mb-4 d-print-none" id="report-filter-form">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">Salesman</label>
                        <select name="kode_sales" class="form-select form-select-sm">
                            <option value="">— Semua Sales —</option>
                            @foreach ($salesmen as $s)
                                <option value="{{ $s->nik }}"
                                    {{ request('kode_sales') == $s->nik ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                            value="{{ $tanggalMulai }}">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">S/D Tanggal</label>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                            value="{{ $tanggalAkhir }}">
                    </div>
                    <div class="col-sm-auto d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('canvas.report') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Print Header --}}
            <div class="d-none d-print-block mb-4 text-center">
                <h4 class="fw-bold mb-1">LAPORAN MUTASI BARANG KANVAS</h4>
                <p class="mb-0 text-secondary">
                    Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d M Y') }}
                </p>
                @if ($kodeSales)
                    @php
                        $selectedSalesman = $salesmen->where('nik', $kodeSales)->first();
                    @endphp
                    <p class="fw-semibold mt-1">Salesman: {{ $selectedSalesman ? $selectedSalesman->name : $kodeSales }}</p>
                @endif
                <hr class="my-3">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7 font-11">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="120">Kode Barang</th>
                            <th>Nama Barang</th>
                            <th width="100" class="text-center">Satuan</th>
                            <th width="150" class="text-end pe-3 bg-primary-subtle text-primary">Diambil (Loading)</th>
                            <th width="150" class="text-end pe-3 bg-info-subtle text-info">Terjual (Sales)</th>
                            <th width="150" class="text-end pe-3 bg-success-subtle text-success">Kembali (Unload)</th>
                            <th width="150" class="text-end pe-3 bg-warning-subtle text-warning font-bold">Belum Kembali (Sisa)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $index => $item)
                            @php
                                $isi = (float)($item->barangSatuan->isi ?? 1);
                                $qtyAmbilSmallest = $item->total_ambil * $isi;
                                $qtyTerjualSmallest = $item->total_terjual * $isi;
                                $qtyKembaliSmallest = $item->total_kembali * $isi;
                                $qtySisaSmallest = $item->total_selisih * $isi;
                            @endphp
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">{{ $index + 1 }}</td>
                                <td class="font-monospace text-secondary small">{{ $item->kode_barang }}</td>
                                <td class="fw-bold text-dark">{{ $item->barang->nama_barang }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border fw-semibold font-11 py-1 px-2.5" style="opacity: 0.85;">
                                        {{ $item->barangSatuan->satuan ?? 'PCS' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3 bg-primary-subtle text-primary fw-bold">
                                    <div>{{ $item->barang->formatStok($qtyAmbilSmallest) }}</div>
                                    <small class="text-secondary font-11">({{ (float)$item->total_ambil }} {{ $item->barangSatuan->satuan ?? 'PCS' }})</small>
                                </td>
                                <td class="text-end pe-3 bg-info-subtle text-info fw-bold">
                                    <div>{{ $item->barang->formatStok($qtyTerjualSmallest) }}</div>
                                    <small class="text-secondary font-11">({{ (float)$item->total_terjual }} {{ $item->barangSatuan->satuan ?? 'PCS' }})</small>
                                </td>
                                <td class="text-end pe-3 bg-success-subtle text-success fw-bold">
                                    <div>{{ $item->barang->formatStok($qtyKembaliSmallest) }}</div>
                                    <small class="text-secondary font-11">({{ (float)$item->total_kembali }} {{ $item->barangSatuan->satuan ?? 'PCS' }})</small>
                                </td>
                                <td class="text-end pe-3 bg-warning-subtle text-warning fw-bold">
                                    <div>{{ $item->barang->formatStok($qtySisaSmallest) }}</div>
                                    <small class="text-secondary font-11">({{ (float)$item->total_selisih }} {{ $item->barangSatuan->satuan ?? 'PCS' }})</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-file-lines d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada data untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Print Style overrides --}}
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .card {
                box-shadow: none !important;
                border: 0 !important;
            }
            .card-body {
                padding: 0 !important;
            }
            .table-responsive {
                overflow: visible !important;
            }
            .table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            .table th, .table td {
                border: 1px solid #dee2e6 !important;
                background-color: transparent !important;
                color: black !important;
            }
        }
    </style>
@endsection
