@extends('layouts.app')
@section('title', $item->exists ? 'Edit Pembelian' : 'Tambah Pembelian')
@section('content')
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 45px; height: 45px;">
                    <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Transaksi Pembelian' : 'Transaksi Pembelian Baru' }}
                    </h5>
                    <small
                        class="text-white-50">{{ $item->exists ? 'Perbarui detail faktur pembelian' : 'Catat transaksi pembelian masuk dari supplier' }}</small>
                </div>
            </div>
            <a href="{{ route('pembelian.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
            </a>
        </div>

        <div class="card-body p-4 bg-light">
            <form action="{{ $item->exists ? route('pembelian.update', $item->no_faktur) : route('pembelian.store') }}"
                method="POST" id="pembelianForm">
                @csrf
                @if ($item->exists)
                    @method('PUT')
                @endif

                {{-- TOP METADATA PANEL --}}
                <div class="row g-3 mb-4">
                    <!-- Column 1: Metadata Left -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border p-3 rounded bg-white shadow-sm mb-0">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> Data Transaksi
                            </h6>
                            <div class="mb-2">
                                <label for="no_faktur" class="form-label fs-7 fw-bold text-secondary mb-1">No Faktur
                                    Supplier
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1" style="font-size:10px; font-weight:500;">Auto</span></label>
                                <input type="text" name="no_faktur" id="no_faktur"
                                    class="form-control form-control-sm bg-light font-monospace fw-bold @error('no_faktur') is-invalid @enderror"
                                    placeholder="Auto-generated" value="{{ old('no_faktur', $item->no_faktur) }}"
                                    readonly>
                                <div class="text-muted" style="font-size:10px; margin-top:2px;"><i class="fa-solid fa-circle-info"></i> Nomor faktur digenerate otomatis saat disimpan</div>
                                @error('no_faktur')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="tanggal" class="form-label fs-7 fw-bold text-secondary mb-1">Tanggal Transaksi
                                    <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal"
                                    class="form-control form-control-sm @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $item->tanggal ?? date('Y-m-d')) }}" required>
                            </div>
                            <div class="mb-2">
                                <label for="jatuh_tempo" class="form-label fs-7 fw-bold text-secondary mb-1">Jatuh Tempo
                                    (Kredit)</label>
                                <input type="date" name="jatuh_tempo" id="jatuh_tempo"
                                    class="form-control form-control-sm @error('jatuh_tempo') is-invalid @enderror"
                                    value="{{ old('jatuh_tempo', $item->jatuh_tempo) }}">
                            </div>
                            <div class="mb-0">
                                <label class="form-label fs-7 fw-bold text-secondary mb-1">Operator / Pembuat</label>
                                <input type="text" class="form-control form-control-sm bg-light text-muted"
                                    value="{{ auth()->user()->name ?? 'Jazz' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Supplier Middle -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border p-3 rounded bg-white shadow-sm mb-0">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-truck text-success me-1"></i> Data Supplier
                            </h6>
                            <div class="mb-2">
                                <label for="kode_supplier" class="form-label fs-7 fw-bold text-secondary mb-1">Supplier
                                    <span class="text-danger">*</span></label>
                                <select name="kode_supplier" id="kode_supplier"
                                    class="form-select form-select-sm @error('kode_supplier') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach ($suppliers as $s)
                                        <option value="{{ $s->kode_supplier }}"
                                            {{ old('kode_supplier', $item->kode_supplier) == $s->kode_supplier ? 'selected' : '' }}>
                                            {{ $s->nama_supplier }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_supplier')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label for="supplier_code" class="form-label fs-8 fw-bold text-secondary mb-1">Kode
                                        Supplier</label>
                                    <input type="text" id="supplier_code" class="form-control form-control-sm bg-light"
                                        readonly>
                                </div>
                                <div class="col-6">
                                    <label for="supplier_phone" class="form-label fs-8 fw-bold text-secondary mb-1">No
                                        HP</label>
                                    <input type="text" id="supplier_phone" class="form-control form-control-sm bg-light"
                                        readonly>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="supplier_address"
                                    class="form-label fs-8 fw-bold text-secondary mb-1">Alamat</label>
                                <input type="text" id="supplier_address" class="form-control form-control-sm bg-light"
                                    readonly>
                            </div>
                            <div class="mb-0">
                                <label for="no_po" class="form-label fs-7 fw-bold text-secondary mb-1">No. PO (Purchase
                                    Order)</label>
                                <input type="text" name="no_po" id="no_po"
                                    class="form-control form-control-sm @error('no_po') is-invalid @enderror"
                                    placeholder="Contoh: PO-001" value="{{ old('no_po', $item->no_po) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Live Grand Total Card -->
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-300 border-0 text-white p-4 rounded d-flex flex-column justify-content-center position-relative overflow-hidden shadow-sm mb-0"
                            style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); min-height: 200px;">
                            <div class="d-flex align-items-center">
                                <div>
                                    <span
                                        class="text-white-50 text-uppercase fw-bold tracking-wider fs-8 mb-1 d-block">Total
                                        Pembelian</span>
                                    <h2 class="mb-0 fw-bold fs-2 font-monospace" id="grand-total-display">Rp 0</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- QUICK INPUT BAR CARD --}}
                <div class="card border-0 shadow-sm p-3 rounded mb-4 bg-white">
                    <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-barcode text-primary me-1"></i> Input Barang & Satuan
                    </h6>
                    <div class="row g-2 align-items-end">
                        <!-- Pilih Barang -->
                        <div class="col-lg-3 col-md-6">
                            <label for="quick_barang" class="form-label fs-8 fw-bold text-secondary mb-1">Pilih
                                Barang</label>
                            <select id="quick_barang" class="form-select form-select-sm" style="width: 100%;">
                                <option value="">-- Cari / Pilih Barang --</option>
                                @foreach ($barangs as $b)
                                    <option value="{{ $b->kode_barang }}">{{ $b->nama_barang }} ({{ $b->kode_barang }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Satuan -->
                        <div class="col-lg-2 col-md-6">
                            <label for="quick_satuan" class="form-label fs-8 fw-bold text-secondary mb-1">Satuan</label>
                            <select id="quick_satuan" class="form-select form-select-sm">
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>
                        <!-- Jumlah -->
                        <div class="col-lg-1 col-md-4">
                            <label for="quick_qty" class="form-label fs-8 fw-bold text-secondary mb-1">Jumlah</label>
                            <input type="number" id="quick_qty" class="form-control form-control-sm text-end"
                                value="1" min="0.01" step="any">
                        </div>
                        <!-- Harga Modal -->
                        <div class="col-lg-2 col-md-4">
                            <label for="quick_harga" class="form-label fs-8 fw-bold text-secondary mb-1">Harga
                                Modal</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_harga"
                                    class="form-control form-control-sm text-end input-number-format" value="0">
                            </div>
                        </div>
                        <!-- Diskon % -->
                        <div class="col-lg-1 col-md-4">
                            <label for="quick_diskon_percent" class="form-label fs-8 fw-bold text-secondary mb-1">Diskon
                                %</label>
                            <input type="number" id="quick_diskon_percent" class="form-control form-control-sm text-end"
                                value="0" min="0" max="100">
                        </div>
                        <!-- Potongan Rp -->
                        <div class="col-lg-2 col-md-8">
                            <label for="quick_diskon" class="form-label fs-8 fw-bold text-secondary mb-1">Potongan
                                (Rp)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_diskon"
                                    class="form-control form-control-sm text-end input-number-format" value="0">
                            </div>
                        </div>
                        <!-- Add Button -->
                        <div class="col-lg-1 col-md-4">
                            <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" id="btn-add-quick"
                                style="padding-top: 6px; padding-bottom: 6px;">
                                <i class="fa-solid fa-plus me-1"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ITEMS DETAIL TABLE --}}
                <div class="card border-0 shadow-sm p-4 rounded mb-4 bg-white">
                    <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-list text-primary me-1"></i> Daftar Item Barang
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="itemsTable">
                            <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th width="120">Kode</th>
                                    <th>Nama Barang</th>
                                    <th width="120" class="text-center">Satuan</th>
                                    <th width="100" class="text-end">Jumlah</th>
                                    <th width="150" class="text-end">Harga</th>
                                    <th width="130" class="text-end">Pot. (Rp)</th>
                                    <th width="150" class="text-end">Total</th>
                                    <th width="60" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Rows dynamically inserted here --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- BOTTOM SUMMARY PANEL --}}
                <div class="row g-4 pt-3 border-top justify-content-end">
                    <div class="col-md-5">
                        <div class="card bg-light border-0 shadow-sm p-3 rounded">
                            <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 fs-7">Ringkasan Pembayaran</h6>

                            <!-- Subtotal -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small">Subtotal (Sebelum Diskon)</span>
                                <span class="fw-semibold text-dark" id="summary-subtotal">Rp 0</span>
                            </div>

                            <!-- Potongan -->
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6">
                                    <span class="text-secondary small">Total Diskon Item</span>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="potongan" id="potongan"
                                            class="form-control form-control-sm text-end bg-light"
                                            value="{{ old('potongan', $item->potongan ?? 0) }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Potongan Claim -->
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6">
                                    <span class="text-secondary small">Potongan Claim</span>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="potongan_claim" id="potongan_claim"
                                            class="form-control form-control-sm text-end input-number-format"
                                            value="{{ old('potongan_claim', $item->potongan_claim ?? 0) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Pajak -->
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6">
                                    <span class="text-secondary small">Pajak (PPN/dll)</span>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="pajak" id="pajak"
                                            class="form-control form-control-sm text-end input-number-format"
                                            value="{{ old('pajak', $item->pajak ?? 0) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Biaya Lain -->
                            <div class="row g-2 align-items-center mb-3">
                                <div class="col-6">
                                    <span class="text-secondary small">Biaya Lain-lain</span>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="biaya_lain" id="biaya_lain"
                                            class="form-control form-control-sm text-end input-number-format"
                                            value="{{ old('biaya_lain', $item->biaya_lain ?? 0) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Jenis Transaksi (Hidden fields needed for submit) -->
                            <input type="hidden" name="jenis_transaksi" value="Kredit">
                            <input type="hidden" name="keterangan" id="keterangan_hidden">

                            <!-- Grand Total -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-primary">Grand Total</span>
                                <span class="fw-bold text-primary fs-5" id="summary-grandtotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-light px-4 fw-semibold border hover-scale">
                        <i class="fa-solid fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale" id="btn-save-pembelian">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Load base data
            const barangs = {!! json_encode($barangs) !!};
            const suppliers = {!! json_encode($suppliers) !!};
            const existingDetails = {!! json_encode($existingDetails ?? []) !!};
            const isEditMode = {{ $item->exists ? 'true' : 'false' }};
            let rowIndex = 0;

            // Initialize Search Selects
            $('#kode_supplier').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            $('#quick_barang').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

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

            // Supplier dropdown change logic
            $('#kode_supplier').on('change', function() {
                const code = $(this).val();
                const supplier = suppliers.find(s => s.kode_supplier === code);

                if (supplier) {
                    $('#supplier_code').val(supplier.kode_supplier);
                    $('#supplier_phone').val(supplier.no_hp || '-');
                    $('#supplier_address').val(supplier.alamat || '-');
                } else {
                    $('#supplier_code').val('');
                    $('#supplier_phone').val('');
                    $('#supplier_address').val('');
                }
            });

            // Trigger supplier change on edit mode
            if ($('#kode_supplier').val()) {
                $('#kode_supplier').trigger('change');
            }

            // Quick Product selection change logic
            $('#quick_barang').on('change', function() {
                const code = $(this).val();
                const barang = barangs.find(b => b.kode_barang === code);
                $('#quick_satuan').empty().append('<option value="">-- Pilih Satuan --</option>');

                if (barang && barang.satuans) {
                    barang.satuans.forEach(s => {
                        $('#quick_satuan').append(
                            `<option value="${s.id}" data-name="${s.satuan}" data-price="${parseInt(s.harga_pokok)}">${s.satuan} (Isi ${s.isi})</option>`
                        );
                    });

                    if (barang.satuans.length > 0) {
                        $('#quick_satuan').val(barang.satuans[0].id).trigger('change');
                    }
                }
                updateQuickTotals();
            });

            // Quick Satuan select change
            $('#quick_satuan').on('change', function() {
                const option = $(this).find(':selected');
                const price = option.data('price') || 0;
                $('#quick_harga').val(formatNumber(price));
                updateQuickTotals();
            });

            // Input triggers for Quick Inputs
            $('#quick_qty, #quick_harga, #quick_diskon, #quick_diskon_percent').on('input change', function() {
                const triggerId = $(this).attr('id');
                const price = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const qty = parseFloat($('#quick_qty').val()) || 0;
                const baseTotal = price * qty;

                if (triggerId === 'quick_diskon_percent') {
                    const pct = parseFloat($('#quick_diskon_percent').val()) || 0;
                    const computedDiskon = Math.round(baseTotal * (pct / 100));
                    $('#quick_diskon').val(formatNumber(computedDiskon));
                } else if (triggerId === 'quick_diskon') {
                    const diskon = parseFloat(cleanNumber($('#quick_diskon').val())) || 0;
                    if (baseTotal > 0) {
                        const computedPct = Math.round((diskon / baseTotal) * 100);
                        $('#quick_diskon_percent').val(computedPct);
                    } else {
                        $('#quick_diskon_percent').val(0);
                    }
                }
                updateQuickTotals();
            });

            function updateQuickTotals() {
                // Not displaying inline row subtotal for quick input as it is processed when added
            }

            // Add item row to table from quick input
            $('#btn-add-quick').on('click', function() {
                const barangCode = $('#quick_barang').val();
                const satuanId = $('#quick_satuan').val();
                const qty = parseFloat($('#quick_qty').val()) || 0;
                const harga = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const diskon = parseFloat(cleanNumber($('#quick_diskon').val())) || 0;

                if (!barangCode) {
                    Swal.fire('Peringatan', 'Silakan pilih barang terlebih dahulu!', 'warning');
                    return;
                }
                if (!satuanId) {
                    Swal.fire('Peringatan', 'Silakan pilih satuan terlebih dahulu!', 'warning');
                    return;
                }
                if (qty <= 0) {
                    Swal.fire('Peringatan', 'Jumlah barang harus lebih dari 0!', 'warning');
                    return;
                }

                // Check if item already exists in table
                let exist = false;
                $('#itemsTable tbody tr').each(function() {
                    const code = $(this).find('input[name*="[kode_barang]"]').val();
                    const unitId = $(this).find('input[name*="[satuan_id]"]').val();
                    if (code === barangCode && unitId === satuanId) {
                        exist = true;
                    }
                });

                if (exist) {
                    Swal.fire('Peringatan', 'Barang dengan satuan tersebut sudah ada di daftar!',
                        'warning');
                    return;
                }

                const barang = barangs.find(b => b.kode_barang === barangCode);
                const option = $('#quick_satuan').find(':selected');
                const satuanName = option.data('name');

                appendRow(barangCode, barang.nama_barang, satuanId, satuanName, qty, harga, diskon);

                // Reset quick inputs
                $('#quick_barang').val('').trigger('change');
                $('#quick_qty').val(1);
                $('#quick_harga').val(0);
                $('#quick_diskon_percent').val(0);
                $('#quick_diskon').val(0);

                calculateTotals();
                saveDraft();

                setTimeout(function() {
                    $('#quick_barang').select2('open');
                }, 50);
            });

            // Keyboard shortcut to add item on Enter press
            $('#quick_qty, #quick_harga, #quick_diskon, #quick_diskon_percent, #quick_satuan').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#btn-add-quick').click();
                }
            });

            // Append row implementation
            function appendRow(barangCode, barangName, satuanId, satuanName, qty, harga, diskon) {
                const trId = `row_${rowIndex}`;
                const formattedHarga = formatNumber(cleanNumber(harga));
                const formattedDiskon = formatNumber(cleanNumber(diskon));
                const rowHtml = `
                    <tr id="${trId}" class="item-row">
                        <td class="text-center row-number"></td>
                        <td class="font-monospace small text-secondary">
                            ${barangCode}
                            <input type="hidden" name="items[${rowIndex}][kode_barang]" value="${barangCode}">
                        </td>
                        <td class="fw-bold text-dark">
                            ${barangName}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2.5 py-1.5 fs-8">
                                ${satuanName}
                            </span>
                            <input type="hidden" name="items[${rowIndex}][satuan_id]" value="${satuanId}">
                            <input type="hidden" name="items[${rowIndex}][satuan]" value="${satuanName}">
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm text-end input-qty" step="any" min="0.01" value="${qty}" style="max-width: 100px; margin-left: auto;" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="max-width: 150px; margin-left: auto;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="items[${rowIndex}][harga]" class="form-control form-control-sm text-end input-harga input-number-format" value="${formattedHarga}" required>
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="max-width: 130px; margin-left: auto;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="items[${rowIndex}][diskon]" class="form-control form-control-sm text-end input-diskon input-number-format" value="${formattedDiskon}" required>
                            </div>
                        </td>
                        <td class="text-end fw-semibold text-dark py-2 px-3 row-subtotal">
                            Rp 0
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row rounded-circle shadow-sm" style="width: 30px; height: 30px; padding: 4px;">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#itemsTable tbody').append(rowHtml);
                rowIndex++;
            }

            // Remove row logic
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                saveDraft();
            });

            // Inputs change inside table rows
            $(document).on('input change',
                '.input-qty, .input-harga, .input-diskon, #potongan_claim, #pajak, #biaya_lain',
                function() {
                    calculateTotals();
                });

            // Helper to format currency
            function formatCurrency(value) {
                return 'Rp ' + Math.max(0, Math.round(value)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Calculation logic
            function calculateTotals() {
                let grossSubtotalSum = 0;
                let totalDiskon = 0;
                let num = 1;

                $('#itemsTable tbody tr').each(function() {
                    $(this).find('.row-number').text(num++);

                    const qty = parseFloat($(this).find('.input-qty').val()) || 0;
                    const harga = parseFloat(cleanNumber($(this).find('.input-harga').val())) || 0;
                    const diskon = parseFloat(cleanNumber($(this).find('.input-diskon').val())) || 0;

                    const rowSubtotal = qty * harga;
                    const rowNett = rowSubtotal - diskon;

                    grossSubtotalSum += rowSubtotal;
                    totalDiskon += diskon;

                    $(this).find('.row-subtotal').text(formatCurrency(rowNett));
                });

                // Set potongan input to the sum of item-level discounts
                $('#potongan').val(formatNumber(totalDiskon));

                const potonganClaim = parseFloat(cleanNumber($('#potongan_claim').val())) || 0;
                const pajak = parseFloat(cleanNumber($('#pajak').val())) || 0;
                const biayaLain = parseFloat(cleanNumber($('#biaya_lain').val())) || 0;

                const grandTotal = grossSubtotalSum - totalDiskon - potonganClaim + pajak + biayaLain;

                $('#summary-subtotal').text(formatCurrency(grossSubtotalSum));
                $('#summary-grandtotal').text(formatCurrency(grandTotal));
                $('#grand-total-display').text(formatCurrency(grandTotal));
            }

            // Sync memo / keterangan po
            $('#no_po').on('input', function() {
                $('#keterangan_hidden').val($(this).val());
            });

            // Auto update Jatuh Tempo to 15 days after Tanggal
            function updateJatuhTempo() {
                const tanggalVal = $('#tanggal').val();
                if (tanggalVal) {
                    const dateObj = new Date(tanggalVal);
                    dateObj.setDate(dateObj.getDate() + 15);

                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');

                    const formattedDate = `${year}-${month}-${day}`;
                    $('#jatuh_tempo').val(formattedDate);
                }
            }

            $('#tanggal').on('change', function() {
                updateJatuhTempo();
            });

            @if (!$item->exists)
                updateJatuhTempo();
            @endif

            // Load existing details if edit mode
            if (existingDetails.length > 0) {
                existingDetails.forEach(d => {
                    appendRow(d.kode_barang, d.nama_barang || 'Barang', d.satuan_id, d.satuan, d.qty, d
                        .harga, d.diskon);
                });
                calculateTotals();
            }

            // Format inputs on ready (in case of old inputs or edit data)
            $('.input-number-format').each(function() {
                const val = $(this).val();
                if (val) {
                    $(this).val(formatNumber(cleanNumber(val)));
                }
            });

            // Prevent form submit if empty items and clean formatted numbers
            $('#pembelianForm').on('submit', function(e) {
                if ($('#itemsTable tbody tr').length === 0) {
                    e.preventDefault();
                    Swal.fire('Peringatan', 'Minimal harus ada 1 item barang dalam transaksi!', 'warning');
                    return;
                }

                // Strip formatting before submit
                $('.input-number-format').each(function() {
                    const rawVal = cleanNumber($(this).val());
                    $(this).val(rawVal);
                });

                const rawPotongan = cleanNumber($('#potongan').val());
                $('#potongan').val(rawPotongan);

                // Clear draft since transaction is being saved
                const key = isEditMode ? `pembelian_edit_draft_${$('#no_faktur').val()}` : `pembelian_create_draft`;
                localStorage.removeItem(key);
            });

            // --- Draft Persist System ---
            function saveDraft() {
                if (window.isRestoringDraft) return;
                const key = isEditMode ? `pembelian_edit_draft_${$('#no_faktur').val()}` : `pembelian_create_draft`;

                const items = [];
                $('#itemsTable tbody tr').each(function() {
                    const row = $(this);
                    const item = {
                        kode_barang: row.find('input[name*="[kode_barang]"]').val(),
                        nama_barang: row.find('td').eq(2).text().trim(),
                        satuan_id: row.find('input[name*="[satuan_id]"]').val(),
                        satuan: row.find('input[name*="[satuan]"]').val(),
                        qty: parseFloat(row.find('.input-qty').val()) || 0,
                        harga: parseFloat(cleanNumber(row.find('.input-harga').val())) || 0,
                        diskon: parseFloat(cleanNumber(row.find('.input-diskon').val())) || 0
                    };
                    items.push(item);
                });

                const draft = {
                    tanggal: $('#tanggal').val(),
                    jatuh_tempo: $('#jatuh_tempo').val(),
                    kode_supplier: $('#kode_supplier').val(),
                    no_po: $('#no_po').val(),
                    potongan_claim: $('#potongan_claim').val(),
                    pajak: $('#pajak').val(),
                    biaya_lain: $('#biaya_lain').val(),
                    items: items,
                    timestamp: new Date().getTime()
                };

                localStorage.setItem(key, JSON.stringify(draft));
            }

            function restoreDraft(draft) {
                window.isRestoringDraft = true;

                if (draft.tanggal) $('#tanggal').val(draft.tanggal);
                if (draft.jatuh_tempo) $('#jatuh_tempo').val(draft.jatuh_tempo);
                if (draft.no_po) {
                    $('#no_po').val(draft.no_po);
                    $('#keterangan_hidden').val(draft.no_po);
                }
                if (draft.potongan_claim) $('#potongan_claim').val(formatNumber(cleanNumber(draft.potongan_claim)));
                if (draft.pajak) $('#pajak').val(formatNumber(cleanNumber(draft.pajak)));
                if (draft.biaya_lain) $('#biaya_lain').val(formatNumber(cleanNumber(draft.biaya_lain)));

                if (draft.kode_supplier) {
                    $('#kode_supplier').val(draft.kode_supplier).trigger('change');
                }

                // Restore items Table
                $('#itemsTable tbody').empty();
                if (draft.items && draft.items.length > 0) {
                    draft.items.forEach(item => {
                        appendRow(
                            item.kode_barang,
                            item.nama_barang,
                            item.satuan_id,
                            item.satuan,
                            item.qty,
                            item.harga,
                            item.diskon
                        );
                    });
                }

                calculateTotals();

                window.isRestoringDraft = false;
            }

            function initDraftSystem() {
                const key = isEditMode ? `pembelian_edit_draft_${$('#no_faktur').val()}` : `pembelian_create_draft`;
                const savedDraftStr = localStorage.getItem(key);
                if (savedDraftStr) {
                    try {
                        const savedDraft = JSON.parse(savedDraftStr);
                        if (savedDraft && savedDraft.items && savedDraft.items.length > 0) {
                            Swal.fire({
                                title: 'Draft Pembelian Ditemukan',
                                text: isEditMode 
                                    ? 'Ditemukan draf perubahan untuk faktur ini yang belum disimpan. Pulihkan?' 
                                    : 'Ditemukan draft transaksi pembelian yang belum disimpan. Apakah Anda ingin melanjutkan?',
                                icon: 'info',
                                showCancelButton: true,
                                confirmButtonText: 'Pulihkan',
                                cancelButtonText: 'Abaikan',
                                confirmButtonColor: '#0d6efd',
                                cancelButtonColor: '#6b7280'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    restoreDraft(savedDraft);
                                } else {
                                    localStorage.removeItem(key);
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing draft:', e);
                    }
                }
            }

            // Initialize draft system
            initDraftSystem();

            // Save draft on inputs change
            $(document).on('input change', '#pembelianForm input, #pembelianForm select', function() {
                saveDraft();
            });

            // Clear draft when user explicitly cancels/goes back
            $(document).on('click', 'a[href*="pembelian.index"]', function() {
                const key = isEditMode ? `pembelian_edit_draft_${$('#no_faktur').val()}` : `pembelian_create_draft`;
                localStorage.removeItem(key);
            });
        });
    </script>
@endpush
