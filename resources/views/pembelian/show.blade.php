@extends('layouts.app')
@section('title', 'Detail Transaksi Pembelian')
@section('content')
    <div class="row g-4 justify-content-start">
        {{-- INVOICE MAIN CONTAINER (FULL WIDTH) --}}
        <div class="col-lg-12 col-md-12">
            <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
                <!-- Header with Premium Gradient -->
                <div
                    class="card-header card-premium-header text-white py-3.5 d-flex justify-content-between align-items-center border-0">
                    <div class="d-flex align-items-center">
                        <div class="me-3 bg-white bg-opacity-20 rounded-circle p-2.5 d-flex align-items-center justify-content-center border border-white border-opacity-10"
                            style="width: 48px; height: 48px; backdrop-filter: blur(5px);">
                            <i class="fa-solid fa-file-invoice fs-5"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Faktur Pembelian</h5>
                            <small class="text-white text-opacity-75">Detail rincian transaksi barang masuk dari
                                supplier</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if(!$item->tanggal_approve && auth()->user()->can('approve-pembelian'))
                            <form action="{{ route('pembelian.approve', $item->no_faktur) }}" method="POST" class="d-inline approve-form">
                                @csrf
                                <button type="button" class="btn btn-success btn-sm fw-bold hover-scale text-white approve-btn">
                                    <i class="fa-solid fa-check me-1"></i> Setujui Faktur
                                </button>
                            </form>
                        @endif
                        @can('edit-pembelian')
                            <a href="{{ route('pembelian.edit', $item->no_faktur) }}"
                                class="btn btn-white btn-sm fw-bold hover-scale text-primary bg-white border">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Faktur
                            </a>
                        @endcan
                        <a href="{{ route('pembelian.index') }}"
                            class="btn btn-light btn-sm fw-bold hover-scale border border-white border-opacity-10">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body p-4 position-relative">
                    @php
                        $isPaid = $sisaBayar <= 0;
                    @endphp

                    {{-- VISUAL INVOICE STATUS STAMP --}}
                    <div class="position-absolute"
                        style="right: 40px; top: 25px; z-index: 2; pointer-events: none; transform: rotate(8deg);">
                        @if ($isPaid)
                            <div class="border border-4 border-success rounded-3 px-4 py-2 text-success fw-bold tracking-widest text-uppercase shadow-sm bg-white bg-opacity-90 d-flex flex-column align-items-center"
                                style="font-family: 'Inter', sans-serif; font-size: 1.5rem; letter-spacing: 4px; border-style: double !important; border-width: 6px !important; opacity: 0.85;">
                                <span>LUNAS</span>
                                <small style="font-size: 0.62rem; letter-spacing: 1.5px;" class="fw-semibold mt-1">PAID
                                    OFF</small>
                            </div>
                        @else
                            <div class="border border-4 border-warning rounded-3 px-4 py-2 text-warning fw-bold tracking-widest text-uppercase shadow-sm bg-white bg-opacity-90 d-flex flex-column align-items-center"
                                style="font-family: 'Inter', sans-serif; font-size: 1.5rem; letter-spacing: 4px; border-style: double !important; border-width: 6px !important; opacity: 0.85;">
                                <span>TEMPO</span>
                                <small style="font-size: 0.62rem; letter-spacing: 1.5px;"
                                    class="fw-semibold mt-1">CREDIT</small>
                            </div>
                        @endif
                    </div>

                    {{-- METADATA INFO ROW --}}
                    <div class="row g-3 border-bottom pb-4 mb-4">
                        <!-- Invoice Info -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i> Data Faktur
                            </h6>
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5" width="160">No Faktur</td>
                                    <td class="py-1.5">: <span
                                            class="badge bg-secondary font-monospace px-2.5 py-1.5 ms-2">{{ $item->no_faktur }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5">No PO (Purchase Order)</td>
                                    <td class="py-1.5">: <span
                                            class="badge bg-light text-dark font-monospace px-2.5 py-1.5 ms-2 border">{{ $item->no_po ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5">Tanggal Transaksi</td>
                                    <td class="py-1.5">: <span
                                            class="text-dark ms-2 fw-semibold">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5">Jatuh Tempo</td>
                                    <td class="py-1.5">: <span
                                            class="text-dark ms-2 fw-semibold text-danger">{{ $item->jatuh_tempo ? \Carbon\Carbon::parse($item->jatuh_tempo)->format('d-m-Y') : '-' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Supplier Info -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                                <i class="fa-solid fa-truck me-1 text-success"></i> Data Supplier
                            </h6>
                            <table class="table table-sm table-borderless align-middle mb-0">
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5" width="140">Nama Supplier</td>
                                    <td class="py-1.5">: <span
                                            class="text-dark ms-2 fw-bold">{{ $item->supplier->nama_supplier ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5">Kode Supplier</td>
                                    <td class="py-1.5">: <span
                                            class="badge bg-light text-secondary border px-2.5 py-1.5 ms-2 font-monospace">{{ $item->kode_supplier }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5">Alamat</td>
                                    <td class="py-1.5">: <span
                                            class="text-dark ms-2 small">{{ $item->supplier->alamat ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary fw-semibold py-1.5">No. HP / Kontak</td>
                                    <td class="py-1.5">: <span
                                            class="text-dark ms-2">{{ $item->supplier->no_hp ?? '-' }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- ITEMS TABLE --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-uppercase text-secondary fs-7 mb-3">
                            <i class="fa-solid fa-boxes-stacked me-1 text-primary"></i> Daftar Item Pembelian
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                    <tr>
                                        <th width="50" class="text-center py-2.5">No</th>
                                        <th width="150" class="py-2.5">Kode</th>
                                        <th class="py-2.5">Nama Barang</th>
                                        <th width="120" class="text-center py-2.5">Satuan</th>
                                        <th width="100" class="text-end py-2.5">Qty</th>
                                        <th width="160" class="text-end py-2.5">Harga Pokok</th>
                                        <th width="120" class="text-end py-2.5">Potongan (Rp)</th>
                                        <th width="180" class="text-end py-2.5">Subtotal (Nett)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotalRaw = 0; @endphp
                                    @foreach ($item->details as $index => $detail)
                                        @php
                                            $rowSubtotal = $detail->qty * $detail->harga;
                                            $rowNett = $rowSubtotal - $detail->diskon;
                                            $subtotalRaw += $rowSubtotal;
                                        @endphp
                                        <tr>
                                            <td class="text-center text-secondary small fw-bold">{{ $index + 1 }}</td>
                                            <td class="font-monospace text-secondary small">{{ $detail->kode_barang }}</td>
                                            <td>
                                                <span
                                                    class="fw-bold text-dark d-block">{{ $detail->barang->nama_barang ?? 'Barang Terhapus' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2.5 py-1.5 fs-8">
                                                    {{ $detail->satuan }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">{{ $detail->qty }}</td>
                                            <td class="text-end text-dark">Rp
                                                {{ number_format((float) $detail->harga, 0, ',', '.') }}</td>
                                            <td class="text-end text-danger">-Rp
                                                {{ number_format((float) $detail->diskon, 0, ',', '.') }}</td>
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
                        <div class="col-md-6 col-lg-5">
                            <div class="card bg-light border-0 p-3 rounded-3 shadow-sm mb-0">
                                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 fs-7">Rincian Perhitungan</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary small">Subtotal (Sebelum Diskon)</span>
                                    <span class="fw-semibold text-dark">Rp
                                        {{ number_format((float) $subtotalRaw, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary small">Total Diskon Item</span>
                                    <span class="fw-semibold text-danger">-Rp
                                        {{ number_format((float) $item->potongan, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary small">Potongan Claim</span>
                                    <span class="fw-semibold text-danger">-Rp
                                        {{ number_format((float) $item->potongan_claim, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary small">Pajak (PPN/dll)</span>
                                    <span class="fw-semibold text-success">+Rp
                                        {{ number_format((float) $item->pajak, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-secondary small">Biaya Lain-lain</span>
                                    <span class="fw-semibold text-success">+Rp
                                        {{ number_format((float) $item->biaya_lain, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2 mb-2">
                                    <span class="fw-bold text-dark">Grand Total</span>
                                    <span class="fw-bold text-dark">Rp
                                        {{ number_format((float) $item->grand_total, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 text-success">
                                    <span class="small">Total Terbayar</span>
                                    <span class="fw-semibold">Rp
                                        {{ number_format((float) $totalBayar, 0, ',', '.') }}</span>
                                </div>
                                @if ($totalRetur > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2 text-warning">
                                        <span class="small">Total Retur (PF)</span>
                                        <span class="fw-semibold">-Rp
                                            {{ number_format((float) $totalRetur, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center border-top pt-2 mb-3 text-danger">
                                    <span class="fw-bold fs-6">Sisa Tagihan</span>
                                    <span class="fw-bold fs-5">Rp
                                        {{ number_format((float) max(0, $sisaBayar), 0, ',', '.') }}</span>
                                </div>

                                @php
                                    $percentPaid = $item->grand_total > 0 ? min(100, round(($totalBayar / $item->grand_total) * 100)) : 0;
                                @endphp
                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentPaid }}%"
                                        aria-valuenow="{{ $percentPaid }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                    <span>Persentase Pelunasan</span>
                                    <strong>{{ $percentPaid }}%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM SIDE-BY-SIDE PANELS (PAYMENT & HISTORY) --}}
        <!-- Left: Catat Pembayaran Baru -->
        <div class="col-lg-6 col-md-12">
            {{-- TAMBAH PEMBAYARAN FORM / LUNAS INFO --}}
            @if (!$isPaid)
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-cash-register me-2 text-primary"></i> Catat Pembayaran Baru
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('pembelian.payment', $item->no_faktur) }}" method="POST"
                            id="formPembayaran">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="payment_tanggal" class="form-label fs-7 fw-bold text-secondary">Tanggal
                                        Pembayaran <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" id="payment_tanggal"
                                        class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_no_bukti" class="form-label fs-7 fw-bold text-secondary">No Bukti
                                        Bayar / BKM</label>
                                    <input type="text" name="no_bukti" id="payment_no_bukti"
                                        class="form-control form-control-sm font-monospace"
                                        placeholder="Auto jika kosong">
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_jenis_bayar" class="form-label fs-7 fw-bold text-secondary">Jenis
                                        Pembayaran <span class="text-danger">*</span></label>
                                    <select name="jenis_bayar" id="payment_jenis_bayar"
                                        class="form-select form-select-sm" required>
                                        <option value="Cash" selected>Cash / Tunai</option>
                                        <option value="Transfer">Transfer Bank</option>
                                        <option value="Giro">Giro</option>
                                        <option value="Cek">Cek</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_jumlah" class="form-label fs-7 fw-bold text-secondary">Jumlah
                                        Bayar <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="jumlah" id="payment_jumlah"
                                            class="form-control form-control-sm text-end fw-bold text-primary input-number-format"
                                            value="{{ (int) $sisaBayar }}" required>
                                    </div>
                                    <div id="payment_limit_warning" class="text-danger small mt-1 d-none"><i
                                            class="fa-solid fa-circle-exclamation me-1"></i>Jumlah melebihi sisa tagihan!
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="payment_keterangan"
                                        class="form-label fs-7 fw-bold text-secondary">Keterangan / Catatan</label>
                                    <input type="text" name="keterangan" id="payment_keterangan"
                                        class="form-control form-control-sm"
                                        placeholder="Catatan transfer, nama bank, dll.">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2.5 mt-3 hover-scale"
                                id="btnSubmitPembayaran">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0 rounded-4 mb-4 text-center py-4">
                    <div class="card-body py-4">
                        <i class="fa-solid fa-circle-check text-success display-5 mb-3"></i>
                        <h5 class="fw-bold text-dark mb-1">Faktur Sudah Lunas</h5>
                        <p class="text-secondary small mb-0">Seluruh pembayaran untuk faktur ini telah diselesaikan.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Riwayat Pembayaran logs table -->
        <div class="col-lg-6 col-md-12">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-history me-2 text-primary"></i> Riwayat Pembayaran
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                <tr>
                                    <th>No Bukti / Tgl</th>
                                    <th>Metode</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->pembayarans as $pembayaran)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark d-block font-monospace small"
                                                style="font-size: 0.78rem;">{{ $pembayaran->no_bukti }}</span>
                                            <small class="text-muted font-monospace"
                                                style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($pembayaran->tanggal)->format('d-m-Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1 fs-8">
                                                {{ $pembayaran->jenis_bayar }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small">{{ $pembayaran->keterangan ?? '-' }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            Rp {{ number_format((float) $pembayaran->jumlah, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-receipt d-block fs-3 mb-2 opacity-50 text-secondary"></i>
                                            Belum ada data pembayaran yang dicatat untuk transaksi ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($totalRetur > 0)
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fa-solid fa-arrow-rotate-left me-2 text-warning"></i> Histori Retur Potong Faktur (PF)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                    <tr>
                                        <th>No Retur / Tgl</th>
                                        <th>Kondisi</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($returs as $retur)
                                        <tr>
                                            <td>
                                                <a href="{{ route('retur-pembelian.show', $retur->no_retur) }}"
                                                    class="fw-bold text-primary d-block font-monospace small text-decoration-none">
                                                    {{ $retur->no_retur }}
                                                </a>
                                                <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                                    {{ \Carbon\Carbon::parse($retur->tanggal)->format('d-m-Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1 fs-8">
                                                    {{ $retur->kondisi }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-secondary small">{{ $retur->keterangan ?? '-' }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-danger">
                                                Rp {{ number_format((float) $retur->total, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.approve-btn', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Setujui Pembelian?',
                    text: "Apakah Anda yakin ingin menyetujui transaksi pembelian ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    background: '#161e31',
                    color: '#f8fafc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            const sisaBayar = {{ (float) $sisaBayar }};

            // Format number to thousands separator (dot)
            function formatNumber(num) {
                return num.toString().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Clean number from thousands separator
            function cleanNumber(str) {
                return str.toString().replace(/\./g, "").replace(/\D/g, "") || "0";
            }

            // Bind formatter to inputs
            $(document).on('input', '.input-number-format', function() {
                const selectionStart = this.selectionStart;
                const prevLength = this.value.length;

                const rawVal = cleanNumber($(this).val());
                const formatted = formatNumber(rawVal);
                $(this).val(formatted === "0" && rawVal === "" ? "" : formatted);

                // Restore cursor position
                const lengthDiff = this.value.length - prevLength;
                this.setSelectionRange(selectionStart + lengthDiff, selectionStart + lengthDiff);
            });

            // Initial format for default input value
            if ($('#payment_jumlah').val()) {
                $('#payment_jumlah').val(formatNumber(cleanNumber($('#payment_jumlah').val())));
            }

            // Check payment limit input
            $('#payment_jumlah').on('input', function() {
                const inputVal = parseFloat(cleanNumber($(this).val())) || 0;
                if (inputVal > sisaBayar) {
                    $('#payment_limit_warning').removeClass('d-none');
                    $('#btnSubmitPembayaran').attr('disabled', true);
                } else {
                    $('#payment_limit_warning').addClass('d-none');
                    $('#btnSubmitPembayaran').attr('disabled', false);
                }
            });

            // Strip formatting before submit
            $('#formPembayaran').on('submit', function() {
                const rawVal = cleanNumber($('#payment_jumlah').val());
                $('#payment_jumlah').val(rawVal);
            });
        });
    </script>
@endpush
