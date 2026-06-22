@extends('layouts.app')
@section('title', $item->exists ? 'Edit Retur Penjualan' : 'Retur Penjualan Baru')
@section('content')
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 45px; height: 45px;">
                    <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">
                        {{ $item->exists ? 'Edit Transaksi Retur Penjualan' : 'Transaksi Retur Penjualan Baru' }}</h5>
                    <small
                        class="text-white-50">{{ $item->exists ? 'Perbarui detail retur penjualan' : 'Catat penerimaan retur barang dari pelanggan' }}</small>
                </div>
            </div>
            <a href="{{ route('retur-penjualan.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
            </a>
        </div>

        <div class="card-body p-4 bg-light">
            <form
                action="{{ $item->exists ? route('retur-penjualan.update', $item->no_retur) : route('retur-penjualan.store') }}"
                method="POST" id="returForm">
                @csrf
                @if ($item->exists)
                    @method('PUT')
                @endif

                {{-- TOP METADATA PANEL --}}
                <div class="row g-3 mb-4">
                    {{-- Column 1: Data Retur --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border p-3 rounded bg-white shadow-sm mb-0">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> Data Retur
                            </h6>
                            <div class="mb-2">
                                <label for="no_retur" class="form-label fs-7 fw-bold text-secondary mb-1">No Retur
                                    <span class="text-danger">*</span></label>
                                <input type="text" name="no_retur" id="no_retur"
                                    class="form-control form-control-sm font-monospace fw-bold @error('no_retur') is-invalid @enderror"
                                    value="{{ old('no_retur', $item->no_retur) }}"
                                    {{ $item->exists ? 'readonly' : 'required' }}>
                                @error('no_retur')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="tanggal" class="form-label fs-7 fw-bold text-secondary mb-1">Tanggal
                                    <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal"
                                    class="form-control form-control-sm @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $item->tanggal ? $item->tanggal->format('Y-m-d') : date('Y-m-d')) }}"
                                    required>
                            </div>
                            <div class="mb-2">
                                <label for="jenis_retur" class="form-label fs-7 fw-bold text-secondary mb-1">Jenis Retur
                                    <span class="text-danger">*</span></label>
                                <select name="jenis_retur" id="jenis_retur" class="form-select form-select-sm" required>
                                    <option value="PF"
                                        {{ old('jenis_retur', $item->jenis_retur) === 'PF' ? 'selected' : '' }}>
                                        Potong Faktur</option>
                                    <option value="GB"
                                        {{ old('jenis_retur', $item->jenis_retur) === 'GB' ? 'selected' : '' }}>
                                        Ganti Barang</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label for="no_faktur" class="form-label fs-7 fw-bold text-secondary mb-1">Faktur Penjualan
                                    (Opsional)</label>
                                <select name="no_faktur" id="no_faktur" class="form-select form-select-sm">
                                    <option value="">-- Tanpa Faktur / Umum --</option>
                                    @foreach ($penjualans as $p)
                                        <option value="{{ $p->no_faktur }}" data-total="{{ $p->grand_total }}"
                                            {{ old('no_faktur', $item->no_faktur) == $p->no_faktur ? 'selected' : '' }}>
                                            {{ $p->no_faktur }} (Rp {{ number_format($p->grand_total, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fs-7 fw-bold text-secondary mb-1">Operator</label>
                                <input type="text" class="form-control form-control-sm bg-light text-muted"
                                    value="{{ $item->user->name ?? (auth()->user()->name ?? '-') }}" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Column 2: Data Pelanggan --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border p-3 rounded bg-white shadow-sm mb-0">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-address-book text-success me-1"></i> Data Pelanggan
                            </h6>
                            <div class="mb-2">
                                <label for="kode_pelanggan" class="form-label fs-7 fw-bold text-secondary mb-1">Pelanggan
                                    <span class="text-danger">*</span></label>
                                <select name="kode_pelanggan" id="kode_pelanggan"
                                    class="form-select form-select-sm @error('kode_pelanggan') is-invalid @enderror"
                                    required>
                                    <option value="">-- Pilih Pelanggan --</option>
                                    @foreach ($pelanggans as $p)
                                        <option value="{{ $p->kode_pelanggan }}" data-kode="{{ $p->kode_pelanggan }}"
                                            data-hp="{{ $p->no_hp_pelanggan }}" data-alamat="{{ $p->alamat_pelanggan }}"
                                            {{ old('kode_pelanggan', $item->kode_pelanggan) == $p->kode_pelanggan ? 'selected' : '' }}>
                                            {{ $p->nama_pelanggan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label fs-8 fw-bold text-secondary mb-1">Kode Pelanggan</label>
                                    <input type="text" id="pelanggan_kode"
                                        class="form-control form-control-sm bg-light font-monospace" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fs-8 fw-bold text-secondary mb-1">No HP</label>
                                    <input type="text" id="pelanggan_hp" class="form-control form-control-sm bg-light"
                                        readonly>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fs-8 fw-bold text-secondary mb-1">Alamat</label>
                                <input type="text" id="pelanggan_alamat" class="form-control form-control-sm bg-light"
                                    readonly>
                            </div>
                            <div class="mb-0">
                                <label for="keterangan" class="form-label fs-8 fw-bold text-secondary mb-1">Catatan
                                    Keterangan</label>
                                <input type="text" name="keterangan" id="keterangan"
                                    class="form-control form-control-sm"
                                    value="{{ old('keterangan', $item->keterangan) }}" placeholder="Alasan retur...">
                            </div>
                        </div>
                    </div>

                    {{-- Column 3: Live Display Card --}}
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-100 border-0 text-white p-4 rounded d-flex flex-column justify-content-center position-relative overflow-hidden shadow-sm mb-0"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); min-height: 200px;">
                            <span class="text-white-50 text-uppercase fw-bold tracking-wider fs-8 mb-1 d-block">Total Nilai
                                Retur</span>
                            <h2 class="mb-0 fw-bold fs-2 font-monospace" id="grand-total-display">Rp 0</h2>
                            <div class="mt-4 text-white-50 fs-8">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Stok produk otomatis bertambah
                                setelah retur disimpan.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- QUICK INPUT BAR --}}
                <div class="card border-0 shadow-sm p-3 rounded mb-4 bg-white">
                    <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-barcode text-primary me-1"></i> Input Barang & Satuan
                    </h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Pilih Barang</label>
                            <select id="quick_barang" class="form-select form-select-sm" style="width: 100%;">
                                <option value="">-- Cari / Pilih Barang --</option>
                                @foreach ($barangs as $b)
                                    <option value="{{ $b->kode_barang }}">{{ $b->nama_barang }} ({{ $b->kode_barang }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Satuan</label>
                            <select id="quick_satuan" class="form-select form-select-sm">
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Kondisi</label>
                            <select id="quick_kondisi" class="form-select form-select-sm">
                                <option value="Bagus">Bagus</option>
                                <option value="Jelek">Jelek</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Qty</label>
                            <input type="text" id="quick_qty"
                                class="form-control form-control-sm text-end input-qty-format" value="1">
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Harga Retur</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_harga"
                                    class="form-control form-control-sm text-end input-number-format" value="0">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-2 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">D1 %</label>
                            <input type="number" id="quick_diskon1_percent"
                                class="form-control form-control-sm text-end" value="0" min="0"
                                max="100" step="any">
                        </div>
                        <div class="col-lg-1 col-md-2 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">D2 %</label>
                            <input type="number" id="quick_diskon2_percent"
                                class="form-control form-control-sm text-end" value="0" min="0"
                                max="100" step="any">
                        </div>
                        <div class="col-lg-1 col-md-2 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">D3 %</label>
                            <input type="number" id="quick_diskon3_percent"
                                class="form-control form-control-sm text-end" value="0" min="0"
                                max="100" step="any">
                        </div>
                        <div class="col-lg-1 col-md-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Potongan</label>
                            <div class="input-group input-group-sm" style="min-width: 100px;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_diskon"
                                    class="form-control form-control-sm text-end input-number-format" value="0"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-2">
                            <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" id="btn-add-quick">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ITEMS TABLE --}}
                <div class="card border-0 shadow-sm p-4 rounded mb-4 bg-white">
                    <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                        <i class="fa-solid fa-list text-primary me-1"></i> Daftar Item Barang Retur
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="itemsTable">
                            <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th width="100">Kode</th>
                                    <th>Nama Barang</th>
                                    <th width="90" class="text-center">Satuan</th>
                                    <th width="90" class="text-center">Kondisi</th>
                                    <th width="70" class="text-end">Qty</th>
                                    <th width="120" class="text-end">Harga Retur</th>
                                    <th width="65" class="text-end">D1 %</th>
                                    <th width="65" class="text-end">D2 %</th>
                                    <th width="65" class="text-end">D3 %</th>
                                    <th width="110" class="text-end">Potongan</th>
                                    <th width="130" class="text-end">Subtotal</th>
                                    <th width="40" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Rows dynamically inserted --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- BOTTOM SUMMARY --}}
                <div class="row g-4 pt-3 border-top justify-content-end">
                    <div class="col-md-5">
                        <div class="card bg-light border-0 shadow-sm p-3 rounded">
                            <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 fs-7">Ringkasan Retur</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small">Subtotal (Sebelum Diskon)</span>
                                <span class="fw-semibold text-dark" id="summary-subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">Total Potongan Diskon</span>
                                <span class="fw-semibold text-danger" id="summary-diskon-item">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-success">Total Nilai Retur</span>
                                <span class="fw-bold text-success fs-5" id="summary-grandtotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('retur-penjualan.index') }}"
                        class="btn btn-light px-4 fw-semibold border hover-scale">
                        <i class="fa-solid fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success px-4 fw-semibold hover-scale" id="btn-save-retur">
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
            const barangs = {!! json_encode($barangs) !!};
            const existingDetails = {!! json_encode($item->details ?? []) !!};
            let rowIndex = 0;

            // Initialize Select2
            $('#kode_pelanggan').select2({
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

            $('#kode_pelanggan').on('select2:select', function(e) {
                const data = e.params.data;
                const opt = $(this).find(':selected');
                opt.attr('data-kode', data.kode);
                opt.attr('data-hp', data.hp);
                opt.attr('data-alamat', data.alamat);

                updatePelangganInfo(opt);
            });

            $('#no_faktur').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            $('#quick_barang').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Number format helpers
            function formatNumber(num) {
                return num.toString().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function cleanNumber(str) {
                return str.toString().replace(/\./g, "").replace(/\D/g, "") || "0";
            }

            function formatCurrency(value) {
                return 'Rp ' + Math.max(0, Math.round(value)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function formatNumberDecimal(val) {
                let str = val.toString().replace(/[^0-9,]/g, "");
                let parts = str.split(',');
                if (parts.length > 2) {
                    parts = [parts[0], parts.slice(1).join('')];
                }
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                return parts.join(',');
            }

            function cleanNumberDecimal(val) {
                let str = val.toString().replace(/\./g, "").replace(/,/g, ".");
                return str || "0";
            }

            // Bind formatter
            $(document).on('input', '.input-number-format', function() {
                const start = this.selectionStart;
                const prev = this.value.length;
                const raw = cleanNumber($(this).val());
                $(this).val(raw === "0" && $(this).val() === "" ? "" : formatNumber(raw));
                const diff = this.value.length - prev;
                this.setSelectionRange(start + diff, start + diff);
            });

            $(document).on('input', '.input-qty-format', function() {
                const start = this.selectionStart;
                const prev = this.value.length;
                const formatted = formatNumberDecimal($(this).val());
                $(this).val(formatted);
                const diff = this.value.length - prev;
                this.setSelectionRange(start + diff, start + diff);
            });

            let isInitializing = true;

            // Pelanggan dropdown change
            function updatePelangganInfo(opt) {
                $('#pelanggan_kode').val(opt.attr('data-kode') || opt.data('kode') || '');
                $('#pelanggan_hp').val(opt.attr('data-hp') || opt.data('hp') || '-');
                $('#pelanggan_alamat').val(opt.attr('data-alamat') || opt.data('alamat') || '-');
            }

            $('#kode_pelanggan').on('change', function() {
                const opt = $(this).find(':selected');
                updatePelangganInfo(opt);

                if (isInitializing) {
                    return;
                }

                const kodePelanggan = $(this).val();
                const noFakturSelect = $('#no_faktur');

                noFakturSelect.empty().append('<option value="">-- Tanpa Faktur / Umum --</option>');

                if (kodePelanggan) {
                    $.ajax({
                        url: '{{ route('penjualan.by-pelanggan') }}',
                        type: 'GET',
                        data: {
                            kode_pelanggan: kodePelanggan
                        },
                        dataType: 'json',
                        success: function(data) {
                            data.forEach(function(p) {
                                const totalFormatted = new Intl.NumberFormat('id-ID')
                                    .format(Math.round(p.grand_total));
                                noFakturSelect.append(
                                    `<option value="${p.no_faktur}" data-total="${p.grand_total}">${p.no_faktur} (Rp ${totalFormatted})</option>`
                                );
                            });
                            noFakturSelect.trigger('change');
                        }
                    });
                } else {
                    noFakturSelect.trigger('change');
                }
            });

            if ($('#kode_pelanggan').val()) $('#kode_pelanggan').trigger('change');
            isInitializing = false;

            // Barang select change
            $('#quick_barang').on('change', function() {
                const code = $(this).val();
                const barang = barangs.find(b => b.kode_barang === code);
                $('#quick_satuan').empty().append('<option value="">-- Pilih Satuan --</option>');

                if (barang && barang.satuans) {
                    barang.satuans.forEach(s => {
                        $('#quick_satuan').append(
                            `<option value="${s.id}" data-name="${s.satuan}" data-price="${parseInt(s.harga_jual)}">${s.satuan} (Isi ${s.isi})</option>`
                        );
                    });
                    if (barang.satuans.length > 0) {
                        $('#quick_satuan').val(barang.satuans[0].id).trigger('change');
                    }
                }
            });

            // Satuan change → set harga jual
            $('#quick_satuan').on('change', function() {
                const opt = $(this).find(':selected');
                const price = opt.data('price') || 0;
                $('#quick_harga').val(formatNumber(price));
                recalcDiskon();
            });

            // Qty / harga / diskon sync
            function recalcDiskon() {
                const price = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const qty = parseFloat(cleanNumberDecimal($('#quick_qty').val())) || 0;
                const base = price * qty;
                const d1_pct = parseFloat($('#quick_diskon1_percent').val()) || 0;
                const d2_pct = parseFloat($('#quick_diskon2_percent').val()) || 0;
                const d3_pct = parseFloat($('#quick_diskon3_percent').val()) || 0;

                const d1 = base * (d1_pct / 100);
                const d2 = (base - d1) * (d2_pct / 100);
                const d3 = (base - d1 - d2) * (d3_pct / 100);
                const computed = Math.round(d1 + d2 + d3);

                $('#quick_diskon').val(formatNumber(computed));
            }

            $('#quick_qty, #quick_harga').on('input change', function() {
                recalcDiskon();
            });

            $('#quick_diskon1_percent, #quick_diskon2_percent, #quick_diskon3_percent').on('input change',
                function() {
                    recalcDiskon();
                });

            // Add item
            $('#btn-add-quick').on('click', function() {
                const barangCode = $('#quick_barang').val();
                const satuanId = $('#quick_satuan').val();
                const kondisi = $('#quick_kondisi').val() || 'Bagus';
                const qty = parseFloat(cleanNumberDecimal($('#quick_qty').val())) || 0;
                const harga = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const d1 = parseFloat($('#quick_diskon1_percent').val()) || 0;
                const d2 = parseFloat($('#quick_diskon2_percent').val()) || 0;
                const d3 = parseFloat($('#quick_diskon3_percent').val()) || 0;

                if (!barangCode) return Swal.fire('Peringatan', 'Pilih barang terlebih dahulu!', 'warning');
                if (!satuanId) return Swal.fire('Peringatan', 'Pilih satuan terlebih dahulu!', 'warning');
                if (qty <= 0) return Swal.fire('Peringatan', 'Qty harus lebih dari 0!', 'warning');

                // Cek duplikat
                let exist = false;
                $('#itemsTable tbody tr').each(function() {
                    if ($(this).find('input[name*="[kode_barang]"]').val() === barangCode &&
                        $(this).find('input[name*="[satuan_id]"]').val() === satuanId) {
                        exist = true;
                    }
                });
                if (exist) return Swal.fire('Peringatan', 'Barang dengan satuan ini sudah ada di daftar!',
                    'warning');

                const barang = barangs.find(b => b.kode_barang === barangCode);
                const satuanName = $('#quick_satuan').find(':selected').data('name');

                appendRow(barangCode, barang.nama_barang, satuanId, satuanName, kondisi, qty, harga, d1, d2,
                    d3);

                // Reset
                $('#quick_barang').val('').trigger('change');
                $('#quick_qty').val(1);
                $('#quick_harga').val(0);
                $('#quick_diskon1_percent').val(0);
                $('#quick_diskon2_percent').val(0);
                $('#quick_diskon3_percent').val(0);
                $('#quick_diskon').val(0);

                calculateTotals();
            });

            function appendRow(barangCode, barangName, satuanId, satuanName, kondisi, qty, harga, d1 = 0, d2 = 0,
                d3 = 0) {
                const trId = `row_${rowIndex}`;
                const fmtHarga = formatNumber(cleanNumber(harga));
                const html = `
                    <tr id="${trId}" class="item-row">
                        <td class="text-center row-number"></td>
                        <td class="font-monospace small text-secondary">
                            ${barangCode}
                            <input type="hidden" name="items[${rowIndex}][kode_barang]" value="${barangCode}">
                        </td>
                        <td class="fw-bold text-dark">${barangName}</td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace px-2 py-1 fs-8">${satuanName}</span>
                            <input type="hidden" name="items[${rowIndex}][satuan_id]" value="${satuanId}">
                            <input type="hidden" name="items[${rowIndex}][satuan]" value="${satuanName}">
                        </td>
                        <td class="text-center">
                            <select name="items[${rowIndex}][kondisi]" class="form-select form-select-sm" style="min-width:80px;" required>
                                <option value="Bagus" ${kondisi === 'Bagus' ? 'selected' : ''}>Bagus</option>
                                <option value="Jelek" ${kondisi === 'Jelek' ? 'selected' : ''}>Jelek</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="items[${rowIndex}][qty]" class="form-control form-control-sm text-end input-qty input-qty-format" value="${formatNumberDecimal(qty.toString().replace('.', ','))}" style="max-width: 70px; margin-left: auto;" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="max-width: 120px; margin-left: auto;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="items[${rowIndex}][harga_retur]" class="form-control form-control-sm text-end input-harga input-number-format" value="${fmtHarga}" required>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][diskon1_persen]" class="form-control form-control-sm text-end input-diskon1" min="0" max="100" step="any" value="${d1}" style="max-width: 60px; margin-left: auto;">
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][diskon2_persen]" class="form-control form-control-sm text-end input-diskon2" min="0" max="100" step="any" value="${d2}" style="max-width: 60px; margin-left: auto;">
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][diskon3_persen]" class="form-control form-control-sm text-end input-diskon3" min="0" max="100" step="any" value="${d3}" style="max-width: 60px; margin-left: auto;">
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="max-width: 110px; margin-left: auto;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="items[${rowIndex}][diskon]" class="form-control form-control-sm text-end input-diskon input-number-format" value="0" readonly>
                            </div>
                        </td>
                        <td class="text-end fw-semibold text-dark py-2 px-3 row-subtotal">Rp 0</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row rounded-circle" style="width: 30px; height: 30px; padding: 4px;">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>`;
                $('#itemsTable tbody').append(html);
                rowIndex++;
            }

            // Remove row
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });

            // Recalculate on input
            $(document).on('input change',
                '.input-qty, .input-harga, .input-diskon1, .input-diskon2, .input-diskon3',
                calculateTotals);

            function calculateTotals() {
                let subtotalSum = 0;
                let totalDiskon = 0;
                let num = 1;

                $('#itemsTable tbody tr').each(function() {
                    $(this).find('.row-number').text(num++);
                    const qty = parseFloat(cleanNumberDecimal($(this).find('.input-qty').val())) || 0;
                    const harga = parseFloat(cleanNumber($(this).find('.input-harga').val())) || 0;
                    const sub = qty * harga;

                    const d1_pct = parseFloat($(this).find('.input-diskon1').val()) || 0;
                    const d2_pct = parseFloat($(this).find('.input-diskon2').val()) || 0;
                    const d3_pct = parseFloat($(this).find('.input-diskon3').val()) || 0;

                    const d1 = sub * (d1_pct / 100);
                    const d2 = (sub - d1) * (d2_pct / 100);
                    const d3 = (sub - d1 - d2) * (d3_pct / 100);
                    const diskon = Math.round(d1 + d2 + d3);

                    $(this).find('.input-diskon').val(formatNumber(diskon));

                    const nett = sub - diskon;
                    subtotalSum += sub;
                    totalDiskon += diskon;
                    $(this).find('.row-subtotal').text(formatCurrency(nett));
                });

                const grandTotal = subtotalSum - totalDiskon;

                $('#summary-subtotal').text(formatCurrency(subtotalSum));
                $('#summary-diskon-item').text('- ' + formatCurrency(totalDiskon));
                $('#summary-grandtotal').text(formatCurrency(grandTotal));
                $('#grand-total-display').text(formatCurrency(grandTotal));
            }

            // Load existing details (edit mode)
            if (existingDetails.length > 0) {
                existingDetails.forEach(d => {
                    const barang = barangs.find(b => b.kode_barang === d.kode_barang);
                    const name = barang ? barang.nama_barang : (d.barang ? d.barang.nama_barang : 'Barang');
                    const satuan = d.barang_satuan ? d.barang_satuan.satuan : '';
                    const kondisi = d.kondisi || 'Bagus';
                    appendRow(d.kode_barang, name, d.id_satuan, satuan, kondisi, d.qty, parseInt(d
                            .harga_retur),
                        parseFloat(d.diskon1_persen || 0), parseFloat(d.diskon2_persen || 0),
                        parseFloat(d.diskon3_persen || 0));
                });
                calculateTotals();
            }

            // Format on ready
            $('.input-number-format').each(function() {
                const v = $(this).val();
                if (v) $(this).val(formatNumber(cleanNumber(v)));
            });

            // Form submit guard
            $('#returForm').on('submit', function(e) {
                if ($('#itemsTable tbody tr').length === 0) {
                    e.preventDefault();
                    return Swal.fire('Peringatan', 'Minimal harus ada 1 item barang!', 'warning');
                }

                const jenisRetur = $('#jenis_retur').val();
                if (jenisRetur === 'PF') {
                    const selectedFaktur = $('#no_faktur').find(':selected');
                    if (selectedFaktur.val()) {
                        const totalFaktur = parseFloat(selectedFaktur.attr('data-total') || selectedFaktur
                            .data('total')) || 0;
                        const grandTotal = parseFloat(cleanNumber($('#summary-grandtotal').text())) || 0;
                        if (grandTotal > totalFaktur) {
                            e.preventDefault();
                            return Swal.fire('Peringatan',
                                'Total nilai retur tidak boleh melebihi total faktur penjualan!',
                                'warning');
                        }
                    }
                }

                // Strip formatting
                $('.input-number-format').each(function() {
                    $(this).val(cleanNumber($(this).val()));
                });
                $('.input-qty-format').each(function() {
                    $(this).val(cleanNumberDecimal($(this).val()));
                });
            });
        });
    </script>
@endpush
