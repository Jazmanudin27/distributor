@extends('layouts.app')
@section('title', 'Detail Retur Penjualan')
@section('content')
    <div class="row g-4 justify-content-start">
        {{-- MAIN CARD --}}
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
                {{-- Header --}}
                <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center border-0"
                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-20 rounded-circle p-2 d-flex align-items-center justify-content-center border border-white border-opacity-10"
                            style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-rotate-right fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Detail Retur Penjualan</h5>
                            <small class="text-white text-opacity-75">Detail rincian penerimaan barang retur dari pelanggan</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('retur-penjualan.print', $item->no_retur) }}" target="_blank"
                            class="btn btn-white btn-sm fw-bold hover-scale text-primary bg-white border">
                            <i class="fa-solid fa-print me-1"></i> Cetak Faktur
                        </a>
                        @can('edit-retur_penjualan')
                            <a href="{{ route('retur-penjualan.edit', $item->no_retur) }}"
                                class="btn btn-white btn-sm fw-bold hover-scale text-success bg-white border">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Retur
                            </a>
                        @endcan
                        <a href="{{ route('retur-penjualan.index') }}"
                            class="btn btn-light btn-sm fw-bold hover-scale border border-white border-opacity-10">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 position-relative">
                    {{-- STATUS STAMP --}}
                    <div class="position-absolute"
                        style="right: 40px; top: 25px; z-index: 2; pointer-events: none; transform: rotate(8deg);">
                        <div class="border border-4 border-success rounded-3 px-4 py-2 text-success fw-bold text-uppercase shadow-sm bg-white bg-opacity-90 d-flex flex-column align-items-center"
                            style="font-size: 1.3rem; letter-spacing: 3px; border-style: double !important; border-width: 6px !important; opacity: 0.85;">
                            <span>{{ strtoupper($item->jenis_retur) }}</span>
                            <small style="font-size: 0.55rem; letter-spacing: 1.5px;" class="fw-semibold mt-1">SALES RETURN</small>
                        </div>
                    </div>

                    {{-- METADATA ROW --}}
                    <div class="row g-3 border-bottom pb-4 mb-4">
                        {{-- Return Info --}}
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i> Data Retur
                            </h6>
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tr>
                                    <td class="text-secondary fw-semibold py-1" width="160">No Retur</td>
                                    <td class="py-1">: <span
                                            class="badge bg-secondary font-monospace px-2 py-1 ms-2">{{ $item->no_retur }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Tanggal</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2 fw-semibold">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Jenis Retur</td>
                                    <td class="py-1">:
                                        <span class="badge ms-2 bg-info-subtle text-info border border-info-subtle px-2 py-1 fw-bold fs-8">
                                            {{ $item->jenis_retur }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Faktur Penjualan</td>
                                    <td class="py-1">: 
                                        @if($item->no_faktur)
                                            <span class="badge bg-light text-secondary border px-2 py-1 ms-2 font-monospace">{{ $item->no_faktur }}</span>
                                        @else
                                            <span class="text-muted ms-2 small">- (Retur Umum)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Operator</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2">{{ $item->user->name ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Keterangan</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2 small">{{ $item->keterangan ?? '-' }}</span></td>
                                </tr>
                            </table>
                        </div>

                        {{-- Pelanggan Info --}}
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-address-book me-1 text-success"></i> Data Pelanggan
                            </h6>
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tr>
                                    <td class="text-secondary fw-semibold py-1" width="140">Nama Pelanggan</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2 fw-bold">{{ $item->pelanggan->nama_pelanggan ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Kode Pelanggan</td>
                                    <td class="py-1">: <span
                                            class="badge bg-light text-secondary border px-2 py-1 ms-2 font-monospace">{{ $item->kode_pelanggan }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Alamat</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2 small">{{ $item->pelanggan->alamat_pelanggan ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">No HP</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2">{{ $item->pelanggan->no_hp_pelanggan ?? '-' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                            <i class="fa-solid fa-boxes-stacked me-1 text-primary"></i> Daftar Item Barang Retur
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                    <tr>
                                        <th width="50" class="text-center py-2">No</th>
                                        <th width="130" class="py-2">Kode</th>
                                        <th class="py-2">Nama Barang</th>
                                        <th width="110" class="text-center py-2">Satuan</th>
                                        <th width="100" class="text-center py-2">Kondisi</th>
                                        <th width="90" class="text-end py-2">Qty</th>
                                        <th width="140" class="text-end py-2">Harga Retur</th>
                                        <th width="120" class="text-center py-2">Diskon %</th>
                                        <th width="120" class="text-end py-2">Potongan</th>
                                        <th width="150" class="text-end py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotalRaw = 0; @endphp
                                    @foreach ($item->details as $index => $detail)
                                        @php
                                            $rowSub = $detail->qty * $detail->harga_retur;
                                            $rowNett = $rowSub - $detail->total_diskon_rupiah;
                                            $subtotalRaw += $rowSub;

                                            $disks = [];
                                            if (($detail->diskon1_persen ?? 0) > 0) $disks[] = floatval($detail->diskon1_persen) . '%';
                                            if (($detail->diskon2_persen ?? 0) > 0) $disks[] = floatval($detail->diskon2_persen) . '%';
                                            if (($detail->diskon3_persen ?? 0) > 0) $disks[] = floatval($detail->diskon3_persen) . '%';
                                            $diskStr = count($disks) > 0 ? implode(' + ', $disks) : '-';
                                        @endphp
                                        <tr>
                                            <td class="text-center text-secondary small fw-bold">{{ $index + 1 }}</td>
                                            <td class="font-monospace text-secondary small">{{ $detail->kode_barang }}</td>
                                            <td>
                                                <span class="fw-bold text-dark d-block">{{ $detail->barang->nama_barang ?? 'Barang Terhapus' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-1 fs-8">
                                                    {{ $detail->barangSatuan->satuan ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ ($detail->kondisi ?? 'Bagus') == 'Bagus' ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} border px-2 py-1 fs-8">
                                                    {{ $detail->kondisi ?? 'Bagus' }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">{{ floatval($detail->qty) }}</td>
                                            <td class="text-end text-dark">Rp {{ number_format((float) $detail->harga_retur, 0, ',', '.') }}</td>
                                            <td class="text-center text-secondary font-monospace small">{{ $diskStr }}</td>
                                            <td class="text-end text-danger">-Rp {{ number_format((float) $detail->total_diskon_rupiah, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold text-dark">Rp {{ number_format((float) $rowNett, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- SUMMARY PANEL --}}
                    <div class="row justify-content-end mt-4 pt-3 border-top">
                        <div class="col-md-6 col-lg-4">
                            <div class="card bg-light border-0 p-3 rounded-3 shadow-sm mb-0">
                                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 fs-7">Rincian Perhitungan</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Subtotal (Sebelum Diskon)</span>
                                    <span class="fw-semibold text-dark">Rp {{ number_format((float) $subtotalRaw, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Total Diskon Item</span>
                                    <span class="fw-semibold text-danger">-Rp {{ number_format((float) $item->details->sum('total_diskon_rupiah'), 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span class="fw-bold text-success">Total Nilai Retur</span>
                                    <span class="fw-bold text-success fs-5">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
