@extends('layouts.app')
@section('title', 'Detail Faktur Penjualan')
@section('content')
    <div class="row g-4 justify-content-start">
        {{-- INVOICE MAIN CARD --}}
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
                {{-- Header --}}
                <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center border-0"
                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-20 rounded-circle p-2 d-flex align-items-center justify-content-center border border-white border-opacity-10"
                            style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-file-invoice-dollar fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Faktur Penjualan</h5>
                            <small class="text-white text-opacity-75">Detail rincian transaksi penjualan barang ke
                                pelanggan</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('penjualan.print', $item->no_faktur) }}"
                            class="btn btn-white btn-sm fw-bold hover-scale text-primary bg-white border btn-print-faktur"
                            data-no-faktur="{{ $item->no_faktur }}" data-cetak="{{ $item->cetak ?? 0 }}">
                            <i class="fa-solid fa-print me-1"></i> Cetak Faktur
                        </a>
                        @can('edit-penjualan')
                            <a href="{{ route('penjualan.edit', $item->no_faktur) }}"
                                class="btn btn-white btn-sm fw-bold hover-scale text-success bg-white border">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Faktur
                            </a>
                        @endcan
                        <a href="{{ route('penjualan.index') }}"
                            class="btn btn-light btn-sm fw-bold hover-scale border border-white border-opacity-10">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 position-relative">
                    @php
                        $isPaid = $sisaBayar <= 0;
                        $percentPaid = $item->grand_total > 0 ? min(100, round((($totalBayar + $totalRetur) / $item->grand_total) * 100)) : 0;
                    @endphp

                    {{-- STATUS STAMP --}}
                    <div class="position-absolute"
                        style="right: 40px; top: 25px; z-index: 2; pointer-events: none; transform: rotate(8deg);">
                        @if ($isPaid)
                            <div class="border border-4 border-success rounded-3 px-4 py-2 text-success fw-bold text-uppercase shadow-sm bg-white bg-opacity-90 d-flex flex-column align-items-center"
                                style="font-size: 1.5rem; letter-spacing: 4px; border-style: double !important; border-width: 6px !important; opacity: 0.85;">
                                <span>LUNAS</span>
                                <small style="font-size: 0.62rem; letter-spacing: 1.5px;" class="fw-semibold mt-1">PAID
                                    OFF</small>
                            </div>
                        @else
                            <div class="border border-4 border-warning rounded-3 px-4 py-2 text-warning fw-bold text-uppercase shadow-sm bg-white bg-opacity-90 d-flex flex-column align-items-center"
                                style="font-size: 1.5rem; letter-spacing: 4px; border-style: double !important; border-width: 6px !important; opacity: 0.85;">
                                <span>PIUTANG</span>
                                <small style="font-size: 0.62rem; letter-spacing: 1.5px;"
                                    class="fw-semibold mt-1">CREDIT</small>
                            </div>
                        @endif
                    </div>

                    {{-- SESSION MESSAGES --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (isset($totalPending) && $totalPending > 0)
                        <div class="alert alert-warning d-flex gap-2 align-items-center mb-4 py-2 border-warning">
                            <i class="fa-solid fa-circle-info mt-1 flex-shrink-0 text-warning"></i>
                            <span>Terdapat pembayaran <strong>Transfer/Giro</strong> senilai <strong>Rp
                                    {{ number_format($totalPending, 0, ',', '.') }}</strong> yang sedang menunggu
                                persetujuan dari bagian keuangan.</span>
                        </div>
                    @endif

                    {{-- METADATA ROW --}}
                    <div class="row g-3 border-bottom pb-4 mb-4">
                        {{-- Invoice Info --}}
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i> Data Faktur
                            </h6>
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tr>
                                    <td class="text-secondary fw-semibold py-1" width="160">No Faktur</td>
                                    <td class="py-1">: <span
                                            class="badge bg-secondary font-monospace px-2 py-1 ms-2">{{ $item->no_faktur }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Tanggal</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2 fw-semibold">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Tanggal Kirim</td>
                                    <td class="py-1">: <span
                                            class="text-dark ms-2 fw-semibold">{{ $item->tanggal_kirim ? \Carbon\Carbon::parse($item->tanggal_kirim)->format('d-m-Y') : '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1">Jenis Transaksi</td>
                                    <td class="py-1">:
                                        <span
                                            class="badge ms-2 {{ $item->jenis_transaksi === 'Tunai' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-1 fw-bold">
                                            {{ $item->jenis_transaksi }}
                                        </span>
                                    </td>
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
                                    <td class="text-secondary fw-semibold py-1">Kode</td>
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
                            <i class="fa-solid fa-boxes-stacked me-1 text-primary"></i> Daftar Item Penjualan
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                    <tr>
                                        <th width="50" class="text-center py-2">No</th>
                                        <th width="130" class="py-2">Kode</th>
                                        <th class="py-2">Nama Barang</th>
                                        <th width="110" class="text-center py-2">Satuan</th>
                                        <th width="90" class="text-end py-2">Qty</th>
                                        <th width="140" class="text-end py-2">Harga Jual</th>
                                        <th width="120" class="text-center py-2">Diskon %</th>
                                        <th width="120" class="text-end py-2">Potongan</th>
                                        <th width="150" class="text-end py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotalRaw = 0; @endphp
                                    @foreach ($item->details as $index => $detail)
                                        @php
                                            $rowSub = $detail->qty * $detail->harga;
                                            $rowNett = $rowSub - $detail->total_diskon;
                                            $subtotalRaw += $rowSub;

                                            $disks = [];
                                            if (($detail->diskon1_persen ?? 0) > 0) {
                                                $disks[] = floatval($detail->diskon1_persen) . '%';
                                            }
                                            if (($detail->diskon2_persen ?? 0) > 0) {
                                                $disks[] = floatval($detail->diskon2_persen) . '%';
                                            }
                                            if (($detail->diskon3_persen ?? 0) > 0) {
                                                $disks[] = floatval($detail->diskon3_persen) . '%';
                                            }
                                            $diskStr = count($disks) > 0 ? implode(' + ', $disks) : '-';
                                        @endphp
                                        <tr>
                                            <td class="text-center text-secondary small fw-bold">{{ $index + 1 }}</td>
                                            <td class="font-monospace text-secondary small">{{ $detail->kode_barang }}
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold text-dark d-block">{{ $detail->barang->nama_barang ?? 'Barang Terhapus' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-1 fs-8">
                                                    {{ $detail->barangSatuan->satuan ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">{{ $detail->qty }}</td>
                                            <td class="text-end text-dark">Rp
                                                {{ number_format((float) $detail->harga, 0, ',', '.') }}</td>
                                            <td class="text-center text-secondary font-monospace small">
                                                {{ $diskStr }}</td>
                                            <td class="text-end text-danger">-Rp
                                                {{ number_format((float) $detail->total_diskon, 0, ',', '.') }}</td>
                                            <td class="text-end fw-bold text-dark">Rp
                                                {{ number_format((float) $rowNett, 0, ',', '.') }}</td>
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
                                    <span class="fw-semibold text-dark">Rp
                                        {{ number_format((float) $subtotalRaw, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary small">Total Diskon Item</span>
                                    <span class="fw-semibold text-danger">-Rp
                                        {{ number_format((float) $item->details->sum('total_diskon'), 0, ',', '.') }}</span>
                                </div>
                                @php $diskonGlobal = $item->diskon - $item->details->sum('total_diskon'); @endphp
                                @if ($diskonGlobal > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary small">Diskon Global</span>
                                        <span class="fw-semibold text-danger">-Rp
                                            {{ number_format((float) $diskonGlobal, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between border-top pt-2 mb-2">
                                    <span class="fw-bold text-dark">Grand Total</span>
                                    <span class="fw-bold text-dark">Rp
                                        {{ number_format((float) $item->grand_total, 0, ',', '.') }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span class="small">Total Terbayar</span>
                                    <span class="fw-semibold">Rp
                                        {{ number_format((float) $totalBayar, 0, ',', '.') }}</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2 text-warning">
                                    <span class="small">Total Retur (PF)</span>
                                    <span class="fw-semibold">-Rp
                                        {{ number_format((float) $totalRetur, 0, ',', '.') }}</span>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-2 mb-3 text-danger">
                                    <span class="fw-bold fs-6">Sisa Piutang</span>
                                    <span class="fw-bold fs-5">Rp
                                        {{ number_format((float) max(0, $sisaBayar), 0, ',', '.') }}</span>
                                </div>

                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentPaid }}%">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>Pelunasan</span>
                                    <strong>{{ $percentPaid }}%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM PANELS: STATUS TAGIHAN + BAYAR --}}
        <div class="col-lg-6 col-md-12">
            {{-- FORM TAMBAH PEMBAYARAN --}}
            @if (!$isPaid)
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-cash-register me-2 text-success"></i> Catat Pembayaran Baru
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('penjualan.payment', $item->no_faktur) }}" method="POST"
                            id="formPembayaran">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold text-secondary">Tanggal <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control form-control-sm"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold text-secondary">No Bukti / BKK</label>
                                    <input type="text" name="no_bukti"
                                        class="form-control form-control-sm font-monospace"
                                        placeholder="Auto jika kosong">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold text-secondary">Jenis Pembayaran <span
                                            class="text-danger">*</span></label>
                                    <select name="jenis_bayar" class="form-select form-select-sm" required>
                                        <option value="tunai" selected>Tunai</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="giro">Giro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold text-secondary">Jumlah Bayar <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="jumlah" id="payment_jumlah"
                                            class="form-control form-control-sm text-end fw-bold text-success input-number-format"
                                            value="{{ (int) $sisaBayar }}" required>
                                    </div>
                                    <div id="payment_limit_warning" class="text-danger small mt-1 d-none">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i>Jumlah melebihi sisa piutang!
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold text-secondary">Salesman <span
                                            class="text-danger">*</span></label>
                                    <select name="kode_sales" class="form-select form-select-sm" required>
                                        <option value="">-- Pilih Salesman --</option>
                                        @foreach ($salesmen as $s)
                                            <option value="{{ $s->nik }}"
                                                {{ (old('kode_sales') ?? $item->kode_sales) === $s->nik ? 'selected' : '' }}>
                                                {{ $s->name }} ({{ $s->nik }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold text-secondary">Keterangan</label>
                                    <input type="text" name="keterangan" class="form-control form-control-sm"
                                        placeholder="Nama bank, no ref, catatan...">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold py-2 mt-3 hover-scale"
                                id="btnSubmitPembayaran">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- RIWAYAT PEMBAYARAN --}}
        <div class="col-lg-6 col-md-12">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-history me-2 text-success"></i> Riwayat Pembayaran
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light text-secondary text-uppercase fs-8">
                                <tr>
                                    <th>No Bukti / Tgl</th>
                                    <th>Metode</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allPembayarans as $bayar)
                                    <tr>
                                        <td>
                                            <span
                                                class="fw-bold text-dark d-block font-monospace small">{{ $bayar->no_bukti }}</span>
                                            <small
                                                class="text-muted font-monospace">{{ \Carbon\Carbon::parse($bayar->tanggal)->format('d-m-Y') }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1 fs-8">{{ $bayar->jenis_bayar }}</span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $bayar->keterangan ?? '-' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($bayar->status === 'pending')
                                                <span
                                                    class="badge bg-warning text-dark px-2 py-1 fs-8 mb-1 d-inline-block">Pending</span>
                                                @if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <form
                                                            action="{{ route('pembayaran.approve', [$bayar->id, 'source' => $bayar->source_table]) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-success btn-xs px-2 py-0 fw-semibold"
                                                                style="font-size: 0.65rem;" title="Setujui">
                                                                <i class="fa-solid fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form
                                                            action="{{ route('pembayaran.reject', [$bayar->id, 'source' => $bayar->source_table]) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-danger btn-xs px-2 py-0 fw-semibold"
                                                                style="font-size: 0.65rem;" title="Tolak">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @elseif($bayar->status === 'disetujui')
                                                <span class="badge bg-success px-2 py-1 fs-8">Disetujui</span>
                                                @if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                                                    <div class="d-flex justify-content-center mt-1">
                                                        <form
                                                            action="{{ route('pembayaran.cancel-approval', [$bayar->id, 'source' => $bayar->source_table]) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan persetujuan pembayaran ini?')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-warning btn-xs px-2 py-0 fw-semibold text-white d-flex align-items-center gap-1"
                                                                style="font-size: 0.65rem;" title="Batalkan Persetujuan">
                                                                <i class="fa-solid fa-arrow-rotate-left"></i> Batal
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="badge bg-danger px-2 py-1 fs-8">Ditolak</span>
                                                @if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin'))
                                                    <div class="d-flex justify-content-center mt-1">
                                                        <form
                                                            action="{{ route('pembayaran.cancel-approval', [$bayar->id, 'source' => $bayar->source_table]) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penolakan pembayaran ini?')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-warning btn-xs px-2 py-0 fw-semibold text-white d-flex align-items-center gap-1"
                                                                style="font-size: 0.65rem;" title="Batalkan Penolakan">
                                                                <i class="fa-solid fa-arrow-rotate-left"></i> Batal
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format((float) $bayar->jumlah, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-receipt d-block fs-3 mb-2 opacity-50 text-secondary"></i>
                                            Belum ada riwayat pembayaran untuk faktur ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- HISTORI RETUR POTONG FAKTUR (PF) --}}
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-arrow-rotate-left me-2 text-warning"></i> Histori Retur Potong Faktur (PF)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light text-secondary text-uppercase fs-8">
                                <tr>
                                    <th>No Retur / Tgl</th>
                                    <th>Jenis Retur</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-center" width="70">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returs as $retur)
                                    <tr>
                                        <td>
                                            <a href="{{ route('retur-penjualan.show', $retur->no_retur) }}"
                                                class="fw-bold text-primary d-block font-monospace small text-decoration-none">
                                                {{ $retur->no_retur }}
                                            </a>
                                            <small
                                                class="text-muted font-monospace">{{ \Carbon\Carbon::parse($retur->tanggal)->format('d-m-Y') }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 fs-8">{{ $retur->jenis_retur }}</span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $retur->keterangan ?? '-' }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            Rp {{ number_format((float) $retur->total, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('retur-penjualan.show', $retur->no_retur) }}"
                                                class="btn btn-sm btn-outline-secondary rounded-circle"
                                                style="width: 28px; height: 28px; padding: 2px;"
                                                title="Lihat Detail Retur">
                                                <i class="fa-solid fa-eye" style="font-size: 0.75rem;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i
                                                class="fa-solid fa-arrow-rotate-left d-block fs-3 mb-2 opacity-50 text-secondary"></i>
                                            Belum ada retur potong faktur untuk faktur ini.
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const sisaBayar = {{ (float) $sisaBayar }};

            function formatNumber(num) {
                return num.toString().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function cleanNumber(str) {
                return str.toString().replace(/\./g, "").replace(/\D/g, "") || "0";
            }

            $(document).on('input', '.input-number-format', function() {
                const start = this.selectionStart;
                const prev = this.value.length;
                const raw = cleanNumber($(this).val());
                $(this).val(formatNumber(raw));
                const diff = this.value.length - prev;
                this.setSelectionRange(start + diff, start + diff);
            });

            if ($('#payment_jumlah').val()) {
                $('#payment_jumlah').val(formatNumber(cleanNumber($('#payment_jumlah').val())));
            }

            $('#payment_jumlah').on('input', function() {
                const val = parseFloat(cleanNumber($(this).val())) || 0;
                if (val > sisaBayar) {
                    $('#payment_limit_warning').removeClass('d-none');
                    $('#btnSubmitPembayaran').attr('disabled', true);
                } else {
                    $('#payment_limit_warning').addClass('d-none');
                    $('#btnSubmitPembayaran').attr('disabled', false);
                }
            });

            $('#formPembayaran').on('submit', function() {
                $('#payment_jumlah').val(cleanNumber($('#payment_jumlah').val()));
            });
        });
    </script>
@endpush
