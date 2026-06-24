@extends('layouts.app')
@section('title', 'Detail DPB')
@section('content')
    <div class="row justify-content-start">
        <div class="col-lg-10 col-md-12">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div
                    class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                            style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-file-invoice fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Detail DPB: {{ $canvasSession->no_canvas }}</h5>
                            <small class="text-white-50 font-12">Informasi pengambilan barang (DPB), sinkronisasi penjualan,
                                dan sisa kembali</small>
                        </div>
                    </div>
                    <div>
                        @if ($canvasSession->status === 'loading')
                            <a href="{{ route('canvas.edit', $canvasSession->id) }}"
                                class="btn btn-warning btn-sm fw-bold hover-scale me-1 text-dark shadow-sm">
                                <i class="fa-solid fa-box-open"></i> Selesaikan & Bongkar
                            </a>
                        @endif
                        <a href="{{ route('canvas.print', $canvasSession->id) }}" target="_blank"
                            class="btn btn-light btn-sm fw-bold hover-scale me-1 text-primary shadow-sm"
                            style="background-color: rgba(255, 255, 255, 0.2); border-color: rgba(255, 255, 255, 0.35); color: white;">
                            <i class="fa-solid fa-print me-1"></i> Cetak Laporan
                        </a>
                        <a href="{{ route('canvas.index') }}"
                            class="btn btn-light btn-sm fw-bold hover-scale text-primary shadow-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    {{-- Session Header Details --}}
                    <div class="row bg-light rounded p-4 mb-4 border border-light-subtle g-3 shadow-sm">
                        <div class="col-md-3">
                            <span class="text-secondary small d-block mb-1 text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 0.5px;">Salesman Kanvas</span>
                            <strong
                                class="text-dark fs-6">{{ $canvasSession->sales->name ?? $canvasSession->kode_sales }}</strong>
                            <span class="text-muted d-block small mt-0.5">NIK: {{ $canvasSession->kode_sales }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-secondary small d-block mb-1 text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 0.5px;">Tanggal Loading</span>
                            <strong
                                class="text-dark fs-6">{{ \Carbon\Carbon::parse($canvasSession->tanggal)->format('d F Y') }}</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-secondary small d-block mb-1 text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 0.5px;">Status Session</span>
                            @if ($canvasSession->status === 'loading')
                                <button type="button"
                                    class="btn btn-sm btn-warning py-1 px-2.5 fw-bold text-dark shadow-sm mt-1" disabled
                                    style="opacity: 0.9; cursor: default; pointer-events: none; font-size: 11px;">
                                    <i class="fa-solid fa-truck-moving me-1"></i> Aktif di Lapangan
                                </button>
                            @else
                                <button type="button"
                                    class="btn btn-sm btn-success py-1 px-2.5 fw-bold text-white shadow-sm mt-1" disabled
                                    style="opacity: 0.9; cursor: default; pointer-events: none; font-size: 11px;">
                                    <i class="fa-solid fa-circle-check me-1"></i> Selesai & Rekonsiliasi
                                </button>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <span class="text-secondary small d-block mb-1 text-uppercase fw-bold"
                                style="font-size: 10px; letter-spacing: 0.5px;">Catatan</span>
                            <span class="text-dark small d-block mt-1">{{ $canvasSession->keterangan ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-box-open me-2 text-primary"></i> Rincian Barang
                        & Rekonsiliasi</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle shadow-sm">
                            <thead class="table-light text-secondary text-uppercase fs-7 font-11">
                                <tr>
                                    <th width="50" class="text-center py-2.5">No</th>
                                    <th class="py-2.5">Nama Barang</th>
                                    <th width="150" class="text-center py-2.5">Satuan</th>
                                    <th width="120" class="text-end pe-3 bg-primary-subtle text-primary fw-bold py-2.5">
                                        Ambil (Loading)</th>
                                    <th width="120" class="text-end pe-3 bg-info-subtle text-info fw-bold py-2.5">Terjual
                                        (Sales)</th>
                                    <th width="120" class="text-end pe-3 bg-success-subtle text-success fw-bold py-2.5">
                                        Kembali (Unload)</th>
                                    <th width="120" class="text-end pe-3 bg-danger-subtle text-danger fw-bold py-2.5">
                                        Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalAmbil = 0;
                                    $totalTerjual = 0;
                                    $totalKembali = 0;
                                    $totalSelisih = 0;
                                @endphp
                                @foreach ($canvasSession->details as $index => $detail)
                                    @php
                                        $totalAmbil += (float) $detail->qty_ambil;
                                        $totalTerjual += (float) $detail->qty_terjual;
                                        $totalKembali += (float) $detail->qty_kembali;
                                        $totalSelisih += (float) $detail->selisih;
                                    @endphp
                                    <tr>
                                        <td class="text-center fw-semibold text-secondary">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $detail->barang->nama_barang }}</div>
                                            <span class="text-secondary small font-11">Kode:
                                                {{ $detail->kode_barang }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-sm btn-light border py-0.5 px-2.5 fw-semibold" disabled
                                                style="opacity: 0.85; cursor: default; pointer-events: none; font-size: 11px;">
                                                {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                                            </button>
                                        </td>
                                        <td class="text-end pe-3 fw-bold bg-primary-subtle text-primary">
                                            {{ (float) $detail->qty_ambil }}
                                        </td>
                                        <td class="text-end pe-3 fw-bold bg-info-subtle text-info">
                                            {{ (float) $detail->qty_terjual }}
                                        </td>
                                        <td class="text-end pe-3 fw-bold bg-success-subtle text-success">
                                            {{ (float) $detail->qty_kembali }}
                                        </td>
                                        <td class="text-end pe-3 fw-bold bg-danger-subtle text-danger">
                                            {{ (float) $detail->selisih }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end pe-3 py-2">TOTAL:</td>
                                    <td class="text-end pe-3 text-primary bg-primary-subtle py-2">{{ $totalAmbil }}</td>
                                    <td class="text-end pe-3 text-info bg-info-subtle py-2">{{ $totalTerjual }}</td>
                                    <td class="text-end pe-3 text-success bg-success-subtle py-2">{{ $totalKembali }}</td>
                                    <td class="text-end pe-3 text-danger bg-danger-subtle py-2">{{ $totalSelisih }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Discrepancy warning banner --}}
                    @if ($canvasSession->status === 'completed' && $totalSelisih != 0)
                        <div class="alert alert-warning border border-warning-subtle mt-4 mb-0 shadow-sm" role="alert"
                            style="background-color: rgba(255, 193, 7, 0.05);">
                            <i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i>
                            <strong class="text-warning-emphasis">Catatan Selisih:</strong> Terdapat selisih sebanyak
                            **{{ $totalSelisih }}** barang dalam DPB ini. Pastikan untuk menindaklanjuti selisih ini
                            (misal: penyesuaian keuangan atau pertanggungjawaban fisik).
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
