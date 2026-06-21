@extends('layouts.app')
@section('title', $item->exists ? 'Edit Retur Pembelian' : 'Retur Pembelian Baru')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-rotate-left me-2"></i>
                {{ $item->exists ? 'Edit Transaksi Retur Pembelian' : 'Transaksi Retur Pembelian Baru' }}
            </h5>
            <small class="text-white-50">Silakan isi informasi retur barang ke supplier</small>
        </div>

        <div class="card-body p-4">
            {{-- Form Route --}}
            <form action="{{ $item->exists ? route('retur-pembelian.update', $item->no_retur) : route('retur-pembelian.store') }}"
                method="POST" id="returForm">
                @csrf
                @if ($item->exists)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    {{-- LEFT COLUMN: INVOICE METADATA --}}
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <!-- No Retur & Tanggal -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">No Retur <span class="text-danger">*</span></label>
                                <input type="text" name="no_retur" id="no_retur" class="form-control form-control-sm bg-light fw-bold"
                                    value="{{ old('no_retur', $item->no_retur) }}" readonly required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Tanggal Retur <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm"
                                    value="{{ old('tanggal', $item->tanggal ?? date('Y-m-d')) }}" required>
                            </div>

                            <!-- No Faktur Pembelian (Asal) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Pilih Faktur Pembelian (Asal)</label>
                                <select name="no_faktur" id="no_faktur" class="form-select form-select-sm select2-init">
                                    <option value="">-- Retur Tanpa Faktur (Manual) --</option>
                                    @foreach ($pembelians as $pemb)
                                        <option value="{{ $pemb->no_faktur }}" {{ old('no_faktur', $item->no_faktur) == $pemb->no_faktur ? 'selected' : '' }}>
                                            {{ $pemb->no_faktur }} ({{ \Carbon\Carbon::parse($pemb->tanggal)->format('d-m-Y') }} - {{ $pemb->supplier->nama_supplier ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Supplier -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Supplier <span class="text-danger">*</span></label>
                                <select name="kode_supplier" id="kode_supplier" class="form-select form-select-sm select2-init" required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->kode_supplier }}" {{ old('kode_supplier', $item->kode_supplier) == $sup->kode_supplier ? 'selected' : '' }}>
                                            {{ $sup->nama_supplier }} ({{ $sup->kode_supplier }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Jenis Retur & Kondisi Barang -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Jenis Retur <span class="text-danger">*</span></label>
                                <select name="jenis_retur" id="jenis_retur" class="form-select form-select-sm" required>
                                    <option value="PF" {{ old('jenis_retur', $item->jenis_retur) == 'PF' ? 'selected' : '' }}>PF (Potong Faktur)</option>
                                    <option value="Tukar Barang" {{ old('jenis_retur', $item->jenis_retur) == 'Tukar Barang' ? 'selected' : '' }}>Tukar Barang</option>
                                    <option value="Cash" {{ old('jenis_retur', $item->jenis_retur) == 'Cash' ? 'selected' : '' }}>Cash / Refund</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1">Kondisi Barang <span class="text-danger">*</span></label>
                                <select name="kondisi" id="kondisi" class="form-select form-select-sm" required>
                                    <option value="Baik" {{ old('kondisi', $item->kondisi) == 'Baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="Rusak" {{ old('kondisi', $item->kondisi) == 'Rusak' ? 'selected' : '' }}>Rusak / BS</option>
                                </select>
                            </div>

                            <!-- Keterangan -->
                            <div class="col-12">
                                <label class="form-label fw-semibold text-secondary mb-1">Keterangan / Alasan Retur</label>
                                <textarea name="keterangan" id="keterangan" rows="2" class="form-control form-control-sm"
                                    placeholder="Alasan retur barang...">{{ old('keterangan', $item->keterangan) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: DYNAMIC TOTAL CARD --}}
                    <div class="col-lg-4">
                        <div class="card bg-gradient-primary-to-secondary text-white border-0 shadow-sm h-100 p-4 rounded-3 d-flex flex-column justify-content-between"
                            style="background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%); min-height: 220px;">
                            <div>
                                <span class="text-white-50 text-uppercase fw-bold small tracking-wider d-block mb-1">TOTAL RETUR</span>
                                <h1 class="fw-extrabold mb-0 text-white display-6" id="top-grandtotal">Rp 0</h1>
                            </div>
                            <div class="border-top border-white-10 pt-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-white-50 small">Supplier:</span>
                                    <span class="fw-semibold text-white small text-truncate" id="top-supplier-name">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-white-50 small">Faktur Asal:</span>
                                    <span class="font-monospace fw-bold text-white small" id="top-invoice-no">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MANUAL QUICK ADD BAR (Only active when not selecting any Faktur Pembelian) --}}
                <div id="manual-item-adder" class="bg-light p-3 rounded my-4 border">
                    <h6 class="fw-bold text-secondary mb-2 fs-7">Input Barang Manual</h6>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label fs-8 text-secondary mb-1">Pilih Barang</label>
                            <select id="quick_barang" class="form-select form-select-sm select2-init">
                                <option value="">-- Cari Barang --</option>
                                @foreach ($barangs as $brg)
                                    <option value="{{ $brg->kode_barang }}">
                                        {{ $brg->nama_barang }} ({{ $brg->kode_barang }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fs-8 text-secondary mb-1">Satuan</label>
                            <select id="quick_satuan" class="form-select form-select-sm">
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>
                        <div class="col-md-1.5 col-sm-6">
                            <label class="form-label fs-8 text-secondary mb-1">Jumlah</label>
                            <input type="number" id="quick_qty" class="form-control form-control-sm text-end" value="1" min="0.01" step="any">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <label class="form-label fs-8 text-secondary mb-1">Harga Retur</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_harga" class="form-control form-control-sm text-end input-number-format" value="0">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" id="btn-add-item" class="btn btn-success btn-sm w-100 fw-bold hover-scale" title="Tambah Barang">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>

                {{-- DYNAMIC ITEMS TABLE --}}
                <div class="card border shadow-sm rounded mt-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-list me-1 text-primary"></i> Daftar Item Retur
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" id="table-items">
                            <thead class="table-light text-secondary text-uppercase">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th width="120">Kode</th>
                                    <th>Nama Barang</th>
                                    <th width="120">Satuan</th>
                                    <th width="100" class="text-end" id="th-qty-beli">Qty Beli</th>
                                    <th width="120" class="text-end" id="th-qty-sebelumnya">Retur Sblm</th>
                                    <th width="100" class="text-end">Qty Retur</th>
                                    <th width="150" class="text-end">Harga Retur</th>
                                    <th width="150" class="text-end">Total</th>
                                    <th width="60" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Dynamic Rows --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('retur-pembelian.index') }}" class="btn btn-light px-4 fw-semibold border hover-scale">
                        <i class="fa-solid fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold hover-scale" id="btn-submit">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Retur
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
            const existingDetails = {!! json_encode($item->details ?? []) !!};
            const isEditMode = {{ $item->exists ? 'true' : 'false' }};
            let rowIndex = 0;

            // Initialize Select2
            $('.select2-init').select2({
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

            // Supplier dropdown update
            $('#kode_supplier').on('change', function() {
                const code = $(this).val();
                const supplier = suppliers.find(s => s.kode_supplier === code);
                if (supplier) {
                    $('#top-supplier-name').text(supplier.nama_supplier);
                } else {
                    $('#top-supplier-name').text('-');
                }
            });

            // Quick Product selection (Manual Input)
            $('#quick_barang').on('change', function() {
                const code = $(this).val();
                const barang = barangs.find(b => b.kode_barang === code);
                $('#quick_satuan').empty().append('<option value="">-- Pilih Satuan --</option>');

                if (barang && barang.satuans) {
                    barang.satuans.forEach(s => {
                        $('#quick_satuan').append(
                            `<option value="${s.id}" data-name="${s.satuan}" data-price="${parseInt(s.harga_pokok || 0)}">${s.satuan}</option>`
                        );
                    });

                    if (barang.satuans.length > 0) {
                        $('#quick_satuan').val(barang.satuans[0].id).trigger('change');
                    }
                }
            });

            $('#quick_satuan').on('change', function() {
                const option = $(this).find(':selected');
                const price = option.data('price') || 0;
                $('#quick_harga').val(formatNumber(price));
            });

            // Manual Add Item button
            $('#btn-add-item').on('click', function() {
                const code = $('#quick_barang').val();
                const satuanId = $('#quick_satuan').val();
                const qty = parseFloat($('#quick_qty').val()) || 0;
                const price = parseFloat(cleanNumber($('#quick_harga').val())) || 0;

                if (!code || !satuanId || qty <= 0 || price <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data tidak lengkap',
                        text: 'Silakan pilih barang, satuan, jumlah, dan harga retur dengan benar.'
                    });
                    return;
                }

                // Check duplicate
                let duplicate = false;
                $('#table-items tbody tr').each(function() {
                    const existingCode = $(this).find('.kode-barang-val').val();
                    if (existingCode === code) {
                        duplicate = true;
                        return false;
                    }
                });

                if (duplicate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Barang sudah ada',
                        text: 'Barang tersebut sudah ada di daftar item retur.'
                    });
                    return;
                }

                const barang = barangs.find(b => b.kode_barang === code);
                const satuanName = $('#quick_satuan').find(':selected').data('name') || '';

                addItemRow({
                    kode_barang: code,
                    nama_barang: barang.nama_barang,
                    satuan_id: satuanId,
                    satuan: satuanName,
                    qty_beli: 0,
                    qty_retur_sebelumnya: 0,
                    qty_available: 999999, // manual has no original invoice limit
                    qty_retur: qty,
                    harga_retur: price
                }, false);

                // Reset quick add
                $('#quick_barang').val('').trigger('change');
                $('#quick_qty').val(1);
                $('#quick_harga').val(0);
            });

            // Dynamically load invoice details via AJAX when Faktur is selected
            $('#no_faktur').on('change', function() {
                const faktur = $(this).val();
                $('#table-items tbody').empty();
                $('#top-invoice-no').text(faktur || '-');

                if (faktur) {
                    // Hide manual adder
                    $('#manual-item-adder').slideUp();
                    $('#th-qty-beli').show();
                    $('#th-qty-sebelumnya').show();

                    // Show loader
                    Swal.fire({
                        title: 'Memuat data faktur...',
                        didOpen: () => { Swal.showLoading() },
                        allowOutsideClick: false
                    });

                    $.ajax({
                        url: `{{ url('/pembelian') }}/${faktur}/items`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            Swal.close();
                            // Set Supplier
                            if (res.kode_supplier) {
                                $('#kode_supplier').val(res.kode_supplier).trigger('change');
                                // Disable supplier select to prevent mismatch
                                $('#kode_supplier').prop('disabled', true);
                                // Add hidden field so it submits correctly
                                if (!$('#hidden_kode_supplier').length) {
                                    $('#returForm').append(`<input type="hidden" name="kode_supplier" id="hidden_kode_supplier" value="${res.kode_supplier}">`);
                                } else {
                                    $('#hidden_kode_supplier').val(res.kode_supplier);
                                }
                            }

                            // Load items into table
                            if (res.items && res.items.length > 0) {
                                res.items.forEach(function(item) {
                                    addItemRow({
                                        kode_barang: item.kode_barang,
                                        nama_barang: item.nama_barang,
                                        satuan_id: item.satuan_id,
                                        satuan: item.satuan,
                                        qty_beli: item.qty_beli,
                                        qty_retur_sebelumnya: item.qty_retur_sebelumnya,
                                        qty_available: item.qty,
                                        qty_retur: 0, // default to 0 retur so user can adjust
                                        harga_retur: item.harga
                                    }, true);
                                });
                            }
                            calculateGrandTotal();
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal memuat data item faktur.'
                            });
                        }
                    });
                } else {
                    // Manual mode
                    $('#manual-item-adder').slideDown();
                    $('#kode_supplier').prop('disabled', false);
                    $('#hidden_kode_supplier').remove();
                    $('#th-qty-beli').hide();
                    $('#th-qty-sebelumnya').hide();
                    calculateGrandTotal();
                }
            });

            // Function to add a row to the items table
            function addItemRow(data, isFromInvoice) {
                const trClass = isFromInvoice ? '' : 'manual-row';
                const hideClass = isFromInvoice ? '' : 'd-none';

                const rowHtml = `
                    <tr id="row-${rowIndex}" class="${trClass}">
                        <td class="text-center row-number font-monospace text-secondary fw-semibold"></td>
                        <td>
                            <input type="text" name="items[${rowIndex}][kode_barang]" class="form-control form-control-sm font-monospace bg-light kode-barang-val fw-semibold" value="${data.kode_barang}" readonly>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm bg-light" value="${data.nama_barang}" readonly>
                        </td>
                        <td>
                            <input type="hidden" name="items[${rowIndex}][satuan_id]" value="${data.satuan_id}">
                            <input type="text" class="form-control form-control-sm bg-light" value="${data.satuan}" readonly>
                        </td>
                        <td class="text-end ${hideClass}">
                            <span class="font-monospace text-secondary">${data.qty_beli}</span>
                        </td>
                        <td class="text-end ${hideClass}">
                            <span class="font-monospace text-secondary text-danger">${data.qty_retur_sebelumnya}</span>
                        </td>
                        <td class="text-end">
                            <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm text-end qty-input font-monospace"
                                value="${data.qty_retur}" min="0" max="${data.qty_available}" step="any" required>
                            <small class="text-muted text-xs ${hideClass}">Maks: ${data.qty_available}</small>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="items[${rowIndex}][harga_retur]" class="form-control form-control-sm text-end harga-input input-number-format font-monospace"
                                    value="${formatNumber(data.harga_retur)}">
                            </div>
                        </td>
                        <td class="text-end fw-bold text-dark font-monospace pe-3">
                            <span class="subtotal-span">Rp 0</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded btn-delete-row" title="Hapus Item">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#table-items tbody').append(rowHtml);
                rowIndex++;
                reorderRows();
                calculateRowTotals();
            }

            // Reorder the row numbers
            function reorderRows() {
                let num = 1;
                $('#table-items tbody tr').each(function() {
                    $(this).find('.row-number').text(num++);
                });
            }

            // Calculate row and grand totals
            function calculateRowTotals() {
                $('#table-items tbody tr').each(function() {
                    const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    const price = parseFloat(cleanNumber($(this).find('.harga-input').val())) || 0;
                    const subtotal = qty * price;
                    $(this).find('.subtotal-span').text('Rp ' + formatNumber(Math.round(subtotal)));
                });
                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let grandTotal = 0;
                $('#table-items tbody tr').each(function() {
                    const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    const price = parseFloat(cleanNumber($(this).find('.harga-input').val())) || 0;
                    grandTotal += qty * price;
                });

                const formatted = 'Rp ' + formatNumber(Math.round(grandTotal));
                $('#top-grandtotal').text(formatted);
            }

            // Input changes triggers total recalculation
            $(document).on('input change', '.qty-input, .harga-input', function() {
                // Ensure qty doesn't exceed max allowed if set
                const max = parseFloat($(this).attr('max'));
                if (max && parseFloat($(this).val()) > max) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Melebihi batas',
                        text: `Jumlah retur tidak boleh melebihi sisa yang dibeli (${max}).`
                    });
                    $(this).val(max);
                }
                calculateRowTotals();
            });

            // Delete Row
            $(document).on('click', '.btn-delete-row', function() {
                $(this).closest('tr').remove();
                reorderRows();
                calculateGrandTotal();
            });

            // Handle edit mode or old inputs loading
            if (isEditMode && existingDetails.length > 0) {
                // Trigger Faktur Pembelian change event
                const fVal = $('#no_faktur').val();
                if (fVal) {
                    $('#manual-item-adder').hide();
                    $('#th-qty-beli').show();
                    $('#th-qty-sebelumnya').show();
                    $('#top-invoice-no').text(fVal);
                    $('#kode_supplier').prop('disabled', true);
                    if (!$('#hidden_kode_supplier').length) {
                        $('#returForm').append(`<input type="hidden" name="kode_supplier" id="hidden_kode_supplier" value="${$('#kode_supplier').val()}">`);
                    }

                    // For each existing details, load from original invoice details
                    // In edit mode we can load them directly since we have the DB records
                    // But we need to load original qty and returned qty to set maximums correctly
                    $.ajax({
                        url: `{{ url('/pembelian') }}/${fVal}/items`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            existingDetails.forEach(function(detail) {
                                const origItem = res.items.find(i => i.kode_barang === detail.kode_barang);
                                addItemRow({
                                    kode_barang: detail.kode_barang,
                                    nama_barang: detail.barang ? detail.barang.nama_barang : '-',
                                    satuan_id: detail.satuan_id,
                                    satuan: detail.barang_satuan ? detail.barang_satuan.satuan : '-',
                                    qty_beli: origItem ? origItem.qty_beli : detail.qty,
                                    qty_retur_sebelumnya: origItem ? origItem.qty_retur_sebelumnya - detail.qty : 0, // exclude current retur quantity
                                    qty_available: origItem ? (origItem.qty_beli - (origItem.qty_retur_sebelumnya - detail.qty)) : detail.qty,
                                    qty_retur: detail.qty,
                                    harga_retur: detail.harga_retur
                                }, true);
                            });
                            calculateGrandTotal();
                        }
                    });
                } else {
                    // Manual mode edit
                    $('#manual-item-adder').show();
                    $('#th-qty-beli').hide();
                    $('#th-qty-sebelumnya').hide();
                    existingDetails.forEach(function(detail) {
                        addItemRow({
                            kode_barang: detail.kode_barang,
                            nama_barang: detail.barang ? detail.barang.nama_barang : '-',
                            satuan_id: detail.satuan_id,
                            satuan: detail.barang_satuan ? detail.barang_satuan.satuan : '-',
                            qty_beli: 0,
                            qty_retur_sebelumnya: 0,
                            qty_available: 999999,
                            qty_retur: detail.qty,
                            harga_retur: detail.harga_retur
                        }, false);
                    });
                    calculateGrandTotal();
                }
            }

            // Remove disabled attribute before submit so value goes through
            $('#returForm').on('submit', function() {
                $('#kode_supplier').prop('disabled', false);
            });
        });
    </script>
@endpush
