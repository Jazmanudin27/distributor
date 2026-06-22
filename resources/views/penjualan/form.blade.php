@extends('layouts.app')
@section('title', $item->exists ? 'Edit Penjualan' : 'Transaksi Penjualan Baru')
@push('styles')
    <style>
        .promo-row,
        .promo-row td {
            background-color: rgba(253, 126, 20, 0.12) !important;
            color: #d97706 !important;
        }

        .promo-row input {
            color: #d97706 !important;
        }
    </style>
@endpush
@section('content')
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header card-premium-header text-white py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2 d-flex align-items-center justify-content-center"
                    style="width: 45px; height: 45px;">
                    <i class="fa-solid {{ $item->exists ? 'fa-pen-to-square' : 'fa-plus' }} fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">{{ $item->exists ? 'Edit Transaksi Penjualan' : 'Transaksi Penjualan Baru' }}
                    </h5>
                    <small
                        class="text-white-50">{{ $item->exists ? 'Perbarui detail faktur penjualan' : 'Catat penjualan barang ke pelanggan' }}</small>
                </div>
            </div>
            <a href="{{ route('penjualan.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-arrow-left me-1 text-primary"></i> Kembali
            </a>
        </div>

        <div class="card-body p-4 bg-light">
            <form action="{{ $item->exists ? route('penjualan.update', $item->no_faktur) : route('penjualan.store') }}"
                method="POST" id="penjualanForm">
                @csrf
                @if ($item->exists)
                    @method('PUT')
                @endif

                {{-- TOP METADATA PANEL --}}
                <div class="row g-3 mb-4">
                    {{-- Column 1: Data Transaksi --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border p-3 rounded bg-white shadow-sm mb-0">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i> Data Transaksi
                            </h6>
                            <div class="mb-2">
                                <label for="no_faktur" class="form-label fs-7 fw-bold text-secondary mb-1">No Faktur
                                    <span class="text-danger">*</span></label>
                                <input type="text" name="no_faktur" id="no_faktur"
                                    class="form-control form-control-sm font-monospace fw-bold @error('no_faktur') is-invalid @enderror"
                                    value="{{ old('no_faktur', $item->no_faktur) }}"
                                    {{ $item->exists ? 'readonly' : 'required' }}>
                                @error('no_faktur')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="tanggal" class="form-label fs-7 fw-bold text-secondary mb-1">Tanggal
                                    <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal"
                                    class="form-control form-control-sm @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : date('Y-m-d')) }}"
                                    required>
                            </div>
                            <div class="mb-2" hidden>
                                <label for="tanggal_kirim" class="form-label fs-7 fw-bold text-secondary mb-1">Tanggal
                                    Kirim</label>
                                <input type="date" name="tanggal_kirim" id="tanggal_kirim"
                                    class="form-control form-control-sm"
                                    value="{{ old('tanggal_kirim', $item->tanggal_kirim ? \Carbon\Carbon::parse($item->tanggal_kirim)->format('Y-m-d') : '') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label fs-7 fw-bold text-secondary mb-1">Jenis Transaksi
                                    <span class="text-danger">*</span></label>
                                <select name="jenis_transaksi" id="jenis_transaksi" class="form-select form-select-sm"
                                    required>
                                    <option value="K"
                                        {{ old('jenis_transaksi', $item->jenis_transaksi) === 'K' ? 'selected' : '' }}>
                                        Kredit / Tempo</option>
                                    <option value="T"
                                        {{ old('jenis_transaksi', $item->jenis_transaksi) === 'T' ? 'selected' : '' }}>
                                        Tunai / Cash</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fs-7 fw-bold text-secondary mb-1">Operator</label>
                                <input type="text" class="form-control form-control-sm bg-light text-muted"
                                    value="{{ auth()->user()->name ?? '-' }}" readonly>
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
                                        @php
                                            $sisaLimit = max(0, $p->limit_pelanggan - ($p->outstanding_piutang ?? 0));
                                            $hasOverdue = $p->jenis_pelanggan !== '1' && $p->has_overdue !== null;
                                        @endphp
                                        <option value="{{ $p->kode_pelanggan }}" data-kode="{{ $p->kode_pelanggan }}"
                                            data-hp="{{ $p->no_hp_pelanggan }}" data-alamat="{{ $p->alamat_pelanggan }}"
                                            data-metode="{{ $p->metode_bayar }}" data-limit="{{ $p->limit_pelanggan }}"
                                            data-sisa-limit="{{ $sisaLimit }}"
                                            data-has-overdue="{{ $hasOverdue ? 1 : 0 }}"
                                            {{ old('kode_pelanggan', $item->kode_pelanggan) == $p->kode_pelanggan ? 'selected' : '' }}>
                                            {{ $p->nama_pelanggan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_pelanggan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
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
                            <div class="row g-2 mb-0">
                                <div class="col-6">
                                    <label class="form-label fs-8 fw-bold text-secondary mb-1">Limit Kredit</label>
                                    <input type="text" id="pelanggan_limit"
                                        class="form-control form-control-sm bg-light text-end font-monospace" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fs-8 fw-bold text-secondary mb-1">Sisa Limit Kredit</label>
                                    <input type="text" id="pelanggan_sisa_limit"
                                        class="form-control form-control-sm bg-light text-end font-monospace fw-bold"
                                        readonly>
                                </div>
                            </div>
                            <div class="mt-2 d-none" id="pelanggan_overdue_warning">
                                <div class="alert alert-danger p-2 mb-0 fs-8 fw-bold text-center">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> TAGIHAN JATUH TEMPO (OVERDUE)
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Column 3: Grand Total Display --}}
                    <div class="col-lg-4 col-md-12">
                        <div class="card h-100 border-0 text-white p-4 rounded d-flex flex-column justify-content-center position-relative overflow-hidden shadow-sm mb-0"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); min-height: 200px;">
                            <span class="text-white-50 text-uppercase fw-bold tracking-wider fs-8 mb-1 d-block">Total
                                Penjualan</span>
                            <h2 class="mb-0 fw-bold fs-2 font-monospace" id="grand-total-display">Rp 0</h2>
                            <div class="mt-3">
                                <small class="text-white-50">Diskon Global (Rp)</small>
                                <div class="input-group input-group-sm mt-1">
                                    <span class="input-group-text bg-white bg-opacity-25 border-0 text-white">Rp</span>
                                    <input type="text" name="diskon_global" id="diskon_global"
                                        class="form-control form-control-sm bg-white bg-opacity-25 border-0 text-white text-end input-number-format fw-bold"
                                        value="{{ old('diskon_global', $item->exists ? $item->diskon - $item->details->sum('total_diskon') : 0) }}"
                                        placeholder="0" style="color: white !important;">
                                </div>
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
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Pilih Barang</label>
                            <select id="quick_barang" class="form-select form-select-sm" style="width: 100%;">
                                <option value="">-- Cari / Pilih Barang --</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Satuan</label>
                            <select id="quick_satuan" class="form-select form-select-sm">
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-3">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Qty</label>
                            <input type="number" id="quick_qty" class="form-control form-control-sm text-end"
                                value="1" min="0.01" step="any">
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Harga Jual</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_harga"
                                    class="form-control form-control-sm text-end input-number-format" value="0">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-3 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Disc 1 %</label>
                            <input type="number" id="quick_diskon1_percent"
                                class="form-control form-control-sm text-end" value="0" min="0"
                                max="100" step="any">
                        </div>
                        <div class="col-lg-1 col-md-3 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Disc 2 %</label>
                            <input type="number" id="quick_diskon2_percent"
                                class="form-control form-control-sm text-end" value="0" min="0"
                                max="100" step="any">
                        </div>
                        <div class="col-lg-1 col-md-3 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Disc 3 %</label>
                            <input type="number" id="quick_diskon3_percent"
                                class="form-control form-control-sm text-end" value="0" min="0"
                                max="100" step="any">
                        </div>
                        <div class="col-lg-1 col-md-5">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">Potongan</label>
                            <div class="input-group input-group-sm" style="min-width: 100px;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" id="quick_diskon"
                                    class="form-control form-control-sm text-end input-number-format" value="0"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-2 col-6 pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="quick_is_promo" value="1">
                                <label class="form-check-label fs-8 fw-bold text-secondary mb-0"
                                    for="quick_is_promo">Promo</label>
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
                        <i class="fa-solid fa-list text-primary me-1"></i> Daftar Item Barang
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="itemsTable">
                            <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th width="100">Kode</th>
                                    <th>Nama Barang</th>
                                    <th width="90" class="text-center">Satuan</th>
                                    <th width="60" class="text-center">Promo</th>
                                    <th width="70" class="text-end">Qty</th>
                                    <th width="120" class="text-end">Harga Jual</th>
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
                            <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3 fs-7">Ringkasan Penjualan</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small">Subtotal (Sebelum Diskon)</span>
                                <span class="fw-semibold text-dark" id="summary-subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-secondary small">Total Diskon Item</span>
                                <span class="fw-semibold text-danger" id="summary-diskon-item">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">Diskon Global</span>
                                <span class="fw-semibold text-danger" id="summary-diskon-global">- Rp 0</span>
                            </div>
                            <input type="hidden" name="keterangan" id="keterangan_hidden"
                                value="{{ old('keterangan', $item->keterangan) }}">
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-success">Grand Total</span>
                                <span class="fw-bold text-success fs-5" id="summary-grandtotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('penjualan.index') }}"
                        class="btn btn-light btn-sm px-4 fw-semibold border hover-scale">
                        <i class="fa-solid fa-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold hover-scale"
                        id="btn-save-penjualan">
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
            const existingDetails = {!! json_encode($item->details ?? []) !!};
            const diskonStrata = {!! json_encode($diskonStrata ?? []) !!};
            const barangsCache = {};

            // originalInvoiceQuantities to keep track of pre-existing invoice item quantities
            const originalInvoiceQuantities = {};
            existingDetails.forEach(d => {
                if (d.barang) {
                    const isi = d.barang_satuan ? (parseFloat(d.barang_satuan.isi) || 1) : 1;
                    const qtySmallest = (parseFloat(d.qty) || 0) * isi;
                    originalInvoiceQuantities[d.kode_barang] = (originalInvoiceQuantities[d.kode_barang] ||
                        0) + qtySmallest;

                    barangsCache[d.kode_barang] = {
                        kode_barang: d.kode_barang,
                        nama_barang: d.barang.nama_barang,
                        kategori: d.barang.kategori,
                        merk: d.barang.merk,
                        kode_supplier: d.barang.kode_supplier,
                        satuans: d.barang.satuans || [],
                        stok: parseFloat(d.barang.stok) || 0
                    };
                }
            });

            function formatStokJS(stok, satuans) {
                let remaining = Math.abs(stok);
                let isNegative = stok < 0;
                let breakdowns = [];
                if (satuans && satuans.length > 0) {
                    let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                    sorted.forEach(sat => {
                        let factor = parseFloat(sat.isi) || 1;
                        let unitQty = Math.floor(remaining / factor);
                        if (unitQty > 0) {
                            breakdowns.push(`${unitQty} ${sat.satuan}`);
                            remaining = remaining % factor;
                        }
                    });
                    if (remaining > 0 && sorted.length > 0) {
                        let last = sorted[sorted.length - 1];
                        breakdowns.push(`${remaining} ${last.satuan}`);
                    }
                } else {
                    breakdowns.push(`${remaining} PCS`);
                }
                let formatted = breakdowns.join(', ') || '0 PCS';
                return isNegative ? '-' + formatted : formatted;
            }

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
                            q: params.term,
                            exclude_no_faktur: '{{ $item->no_faktur }}'
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
            $('#quick_barang').select2({
                theme: 'bootstrap-5',
                width: '100%',
                ajax: {
                    url: '{{ route('barang.search') }}',
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

            $('#quick_barang').on('select2:select', function(e) {
                const data = e.params.data;
                barangsCache[data.kode_barang] = data;
                updateSatuanDropdown(data);
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

            // Bind formatter
            $(document).on('input', '.input-number-format', function() {
                const start = this.selectionStart;
                const prev = this.value.length;
                const raw = cleanNumber($(this).val());
                $(this).val(raw === "0" && $(this).val() === "" ? "" : formatNumber(raw));
                const diff = this.value.length - prev;
                this.setSelectionRange(start + diff, start + diff);
            });

            // Pelanggan dropdown change
            $('#kode_pelanggan').on('change', function() {
                const opt = $(this).find(':selected');
                updatePelangganInfo(opt);
            });

            $('#kode_pelanggan').on('select2:select', function(e) {
                const data = e.params.data;
                const opt = $(this).find(':selected');
                opt.attr('data-kode', data.kode);
                opt.attr('data-hp', data.hp);
                opt.attr('data-alamat', data.alamat);
                opt.attr('data-metode', data.metode);
                opt.attr('data-limit', data.limit);
                opt.attr('data-sisa-limit', data.sisa_limit);
                opt.attr('data-has-overdue', data.has_overdue);

                updatePelangganInfo(opt);
            });

            function updatePelangganInfo(opt) {
                $('#pelanggan_kode').val(opt.attr('data-kode') || opt.data('kode') || '');
                $('#pelanggan_hp').val(opt.attr('data-hp') || opt.data('hp') || '-');
                $('#pelanggan_alamat').val(opt.attr('data-alamat') || opt.data('alamat') || '-');
                $('#pelanggan_metode').val(opt.attr('data-metode') || opt.data('metode') || '-');

                // Display credit limits
                const limit = parseFloat(opt.attr('data-limit') || opt.data('limit')) || 0;
                const sisaLimit = parseFloat(opt.attr('data-sisa-limit') || opt.data('sisa-limit')) || 0;
                $('#pelanggan_limit').val(formatCurrency(limit));
                $('#pelanggan_sisa_limit').val(formatCurrency(sisaLimit));

                // Color sisa limit text
                if (sisaLimit <= 0) {
                    $('#pelanggan_sisa_limit').removeClass('text-success text-dark').addClass('text-danger');
                } else {
                    $('#pelanggan_sisa_limit').removeClass('text-danger text-dark').addClass('text-success');
                }

                // Check overdue status
                const hasOverdue = parseInt(opt.attr('data-has-overdue') || opt.data('has-overdue')) === 1;
                if (hasOverdue) {
                    $('#pelanggan_overdue_warning').removeClass('d-none');
                } else {
                    $('#pelanggan_overdue_warning').addClass('d-none');
                }

                calculateTotals();
            }

            if ($('#kode_pelanggan').val()) {
                const opt = $('#kode_pelanggan').find(':selected');
                updatePelangganInfo(opt);
            }

            // Barang select change
            $('#quick_barang').on('change', function() {
                const code = $(this).val();
                if (!code) {
                    $('#quick_satuan').empty().append('<option value="">-- Pilih Satuan --</option>');
                    return;
                }
                const barang = barangsCache[code];
                if (barang) {
                    updateSatuanDropdown(barang);
                }
            });

            function updateSatuanDropdown(barang) {
                $('#quick_satuan').empty().append('<option value="">-- Pilih Satuan --</option>');

                if (barang && barang.satuans) {
                    barang.satuans.forEach(s => {
                        $('#quick_satuan').append(
                            `<option value="${s.id}" data-name="${s.satuan}" data-price="${parseInt(s.harga_jual)}" data-isi="${s.isi}">${s.satuan} (Isi ${s.isi})</option>`
                        );
                    });
                    if (barang.satuans.length > 0) {
                        $('#quick_satuan').val(barang.satuans[0].id).trigger('change');
                    }
                }
            }

            // Satuan change → set harga jual
            $('#quick_satuan').on('change', function() {
                const opt = $(this).find(':selected');
                const price = opt.data('price') || 0;
                $('#quick_harga').val(formatNumber(price));
                recalcDiskon();
            }); // Qty / harga / diskon sync
            function recalcDiskon() {
                const price = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const qty = parseFloat($('#quick_qty').val()) || 0;
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
                const qty = parseFloat($('#quick_qty').val()) || 0;
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

                const barang = barangsCache[barangCode];
                if (barang) {
                    const selectedSatuanOpt = $('#quick_satuan').find(':selected');
                    const isi = parseFloat(selectedSatuanOpt.attr('data-isi') || selectedSatuanOpt.data(
                        'isi')) || 1;
                    const newQtySmallest = qty * isi;

                    // Calculate current total in cart for this product
                    let currentCartQtySmallest = 0;
                    $('#itemsTable tbody tr').each(function() {
                        const row = $(this);
                        if (row.find('input[name*="[kode_barang]"]').val() === barangCode) {
                            const rQty = parseFloat(row.find('.input-qty').val()) || 0;
                            const rIsi = parseFloat(row.attr('data-isi')) || 1;
                            currentCartQtySmallest += rQty * rIsi;
                        }
                    });

                    const originalQty = parseFloat(originalInvoiceQuantities[barangCode]) || 0;
                    const availableStock = (parseFloat(barang.stok) || 0) + originalQty;

                    if (currentCartQtySmallest + newQtySmallest > availableStock) {
                        return Swal.fire({
                            title: 'Stok Tidak Mencukupi',
                            html: `Stok barang <b>${barang.nama_barang}</b> tidak mencukupi!<br><br>` +
                                `Stok tersedia: <b>${formatStokJS(barang.stok, barang.satuans)}</b><br>` +
                                `Jumlah diminta: <b>${qty} ${selectedSatuanOpt.data('name')}</b> (Setara ${newQtySmallest} PCS)<br>` +
                                `Sudah di keranjang: <b>${currentCartQtySmallest} PCS</b>`,
                            icon: 'error'
                        });
                    }
                }

                const satuanName = $('#quick_satuan').find(':selected').data('name');
                const isPromo = $('#quick_is_promo').is(':checked');

                appendRow(barangCode, barang.nama_barang, satuanId, satuanName, qty, harga, d1, d2, d3,
                    isPromo);

                // Reset
                $('#quick_barang').val('').trigger('change');
                $('#quick_qty').val(1);
                $('#quick_harga').val(0);
                $('#quick_diskon1_percent').val(0);
                $('#quick_diskon2_percent').val(0);
                $('#quick_diskon3_percent').val(0);
                $('#quick_diskon').val(0);
                $('#quick_is_promo').prop('checked', false).trigger('change');

                calculateTotals();
            });

            // Handle quick promo checkbox changes
            $('#quick_is_promo').on('change', function() {
                const isChecked = $(this).is(':checked');
                if (isChecked) {
                    const currentPrice = $('#quick_harga').val();
                    $('#quick_harga').data('temp-original-price', currentPrice);
                    $('#quick_harga').val('0').attr('readonly', true);
                    $('#quick_diskon1_percent').val('0').attr('readonly', true);
                    $('#quick_diskon2_percent').val('0').attr('readonly', true);
                    $('#quick_diskon3_percent').val('0').attr('readonly', true);
                } else {
                    const originalPrice = $('#quick_harga').data('temp-original-price') || '0';
                    $('#quick_harga').val(originalPrice).removeAttr('readonly');
                    $('#quick_diskon1_percent').val('0').removeAttr('readonly');
                    $('#quick_diskon2_percent').val('0').removeAttr('readonly');
                    $('#quick_diskon3_percent').val('0').removeAttr('readonly');
                }
                recalcDiskon();
            });

            // Handle row promo checkbox changes
            $(document).on('change', '.input-promo', function() {
                const row = $(this).closest('tr');
                const isChecked = $(this).is(':checked');

                const inputHarga = row.find('.input-harga');
                const inputDis1 = row.find('.input-diskon1');
                const inputDis2 = row.find('.input-diskon2');
                const inputDis3 = row.find('.input-diskon3');

                if (isChecked) {
                    row.addClass('promo-row');
                    const currentPrice = inputHarga.val();
                    inputHarga.attr('data-original-harga', currentPrice);
                    inputHarga.val('0').attr('readonly', true);
                    inputDis1.val('0').attr('readonly', true);
                    inputDis2.val('0').attr('readonly', true);
                    inputDis3.val('0').attr('readonly', true);
                } else {
                    row.removeClass('promo-row');
                    const originalPrice = inputHarga.attr('data-original-harga') || '0';
                    inputHarga.val(originalPrice).removeAttr('readonly');
                    inputDis1.removeAttr('readonly');
                    inputDis2.removeAttr('readonly');
                    inputDis3.removeAttr('readonly');
                }
                calculateTotals();
            });

            function appendRow(barangCode, barangName, satuanId, satuanName, qty, harga, d1 = 0, d2 = 0, d3 = 0,
                isPromo = false) {
                const trId = `row_${rowIndex}`;
                const fmtHarga = formatNumber(cleanNumber(harga));

                const barang = barangsCache[barangCode];
                let isi = 1;
                if (barang && barang.satuans) {
                    const sat = barang.satuans.find(s => s.id == satuanId);
                    if (sat) {
                        isi = parseFloat(sat.isi) || 1;
                    }
                }

                const html = `
                    <tr id="${trId}" class="item-row ${isPromo ? 'promo-row' : ''}" data-isi="${isi}">
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
                            <input type="checkbox" name="items[${rowIndex}][is_promo]" value="1" class="form-check-input input-promo" ${isPromo ? 'checked' : ''}>
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm text-end input-qty" step="any" min="0.01" value="${qty}" style="max-width: 70px; margin-left: auto;" required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="max-width: 120px; margin-left: auto;">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="items[${rowIndex}][harga]" class="form-control form-control-sm text-end input-harga input-number-format" value="${isPromo ? '0' : fmtHarga}" ${isPromo ? 'readonly' : ''} data-original-harga="${fmtHarga}" required>
                            </div>
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][diskon1_persen]" class="form-control form-control-sm text-end input-diskon1" min="0" max="100" step="any" value="${d1}" style="max-width: 60px; margin-left: auto;" ${isPromo ? 'readonly' : ''}>
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][diskon2_persen]" class="form-control form-control-sm text-end input-diskon2" min="0" max="100" step="any" value="${d2}" style="max-width: 60px; margin-left: auto;" ${isPromo ? 'readonly' : ''}>
                        </td>
                        <td>
                            <input type="number" name="items[${rowIndex}][diskon3_persen]" class="form-control form-control-sm text-end input-diskon3" min="0" max="100" step="any" value="${d3}" style="max-width: 60px; margin-left: auto;" ${isPromo ? 'readonly' : ''}>
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
                '.input-qty, .input-harga, .input-diskon1, .input-diskon2, .input-diskon3, #diskon_global',
                calculateTotals);

            // Trigger recalculation on jenis transaksi change
            $('#jenis_transaksi').on('change', function() {
                calculateTotals();
            });

            // Check stock when quantity changes in table row
            $(document).on('change', '#itemsTable .input-qty', function() {
                const row = $(this).closest('tr');
                const barangCode = row.find('input[name*="[kode_barang]"]').val();
                const barang = barangsCache[barangCode];
                if (!barang) return;

                const qty = parseFloat($(this).val()) || 0;
                const isi = parseFloat(row.attr('data-isi')) || 1;

                // Recalculate other rows' quantities for this product
                let otherCartQtySmallest = 0;
                $('#itemsTable tbody tr').not(row).each(function() {
                    const r = $(this);
                    if (r.find('input[name*="[kode_barang]"]').val() === barangCode) {
                        const rQty = parseFloat(r.find('.input-qty').val()) || 0;
                        const rIsi = parseFloat(r.attr('data-isi')) || 1;
                        otherCartQtySmallest += rQty * rIsi;
                    }
                });

                const originalQty = parseFloat(originalInvoiceQuantities[barangCode]) || 0;
                const availableStock = (parseFloat(barang.stok) || 0) + originalQty;
                const newQtySmallest = qty * isi;

                if (otherCartQtySmallest + newQtySmallest > availableStock) {
                    Swal.fire({
                        title: 'Stok Tidak Mencukupi',
                        html: `Stok barang <b>${barang.nama_barang}</b> tidak mencukupi!<br><br>` +
                            `Stok tersedia: <b>${formatStokJS(barang.stok, barang.satuans)}</b><br>` +
                            `Jumlah diinput: <b>${qty}</b> (Setara ${newQtySmallest} PCS)`,
                        icon: 'error'
                    });

                    // Clamp
                    const maxQtyAllowed = Math.floor((availableStock - otherCartQtySmallest) / isi);
                    $(this).val(maxQtyAllowed > 0 ? maxQtyAllowed : 0);
                    calculateTotals();
                }
            });

            function calculateStrataDiscounts() {
                const jenisTransaksi = $('#jenis_transaksi').val(); // 'Tunai' or 'Kredit'

                // 1. Group total subtotal by supplier code
                const supplierSubtotals = {};
                $('#itemsTable tbody tr').each(function() {
                    const row = $(this);
                    const isPromo = row.find('.input-promo').is(':checked');
                    if (isPromo) return;

                    const barangCode = row.find('input[name*="[kode_barang]"]').val();
                    const qty = parseFloat(row.find('.input-qty').val()) || 0;
                    const harga = parseFloat(cleanNumber(row.find('.input-harga').val())) || 0;
                    const sub = qty * harga;

                    const b = barangsCache[barangCode];
                    if (b && b.kode_supplier) {
                        supplierSubtotals[b.kode_supplier] = (supplierSubtotals[b.kode_supplier] || 0) +
                            sub;
                    }
                });

                // 2. Iterate rows and evaluate rules
                $('#itemsTable tbody tr').each(function() {
                    const row = $(this);
                    const isPromo = row.find('.input-promo').is(':checked');
                    if (isPromo) {
                        row.find('.input-diskon1').val('0').attr('readonly', true);
                        row.find('.input-diskon2').val('0').attr('readonly', true);
                        row.find('.input-diskon3').val('0').attr('readonly', true);
                        return;
                    }

                    const barangCode = row.find('input[name*="[kode_barang]"]').val();
                    const qty = parseFloat(row.find('.input-qty').val()) || 0;
                    const harga = parseFloat(cleanNumber(row.find('.input-harga').val())) || 0;
                    const sub = qty * harga;

                    const b = barangsCache[barangCode];
                    if (!b) return;

                    let bestRate = 0;
                    let bestRule = null;
                    let bestDetail = null;

                    const findRule = (tipe) => {
                        return diskonStrata.filter(r => r.tipe === tipe && r.is_active);
                    };

                    const checkRule = (r, d) => {
                        const rate = parseFloat(d.dis1) || 0;
                        if (rate >= bestRate) {
                            bestRate = rate;
                            bestRule = r;
                            bestDetail = d;
                        }
                    };

                    // --- Priority 1: Per Barang ---
                    const rulesBarang = findRule('barang');
                    rulesBarang.forEach(r => {
                        if (r.barangs && r.barangs.some(item => item.kode_barang === barangCode)) {
                            r.details.forEach(d => {
                                if (qty >= (d.min_qty || 0) && (d.max_qty === null || qty <=
                                        d.max_qty)) {
                                    checkRule(r, d);
                                }
                            });
                        }
                    });

                    // --- Priority 2: Per Beberapa Barang ---
                    if (!bestRule) {
                        const rulesBeberapa = findRule('beberapa_barang');
                        rulesBeberapa.forEach(r => {
                            if (r.barangs && r.barangs.some(item => item.kode_barang ===
                                    barangCode)) {
                                r.details.forEach(d => {
                                    if (qty >= (d.min_qty || 0) && (d.max_qty === null ||
                                            qty <= d.max_qty)) {
                                        checkRule(r, d);
                                    }
                                });
                            }
                        });
                    }

                    // --- Priority 3: Per Kategori ---
                    if (!bestRule && b.kategori) {
                        const rulesKategori = findRule('kategori');
                        rulesKategori.forEach(r => {
                            if (r.kategori && r.kategori.nama_kategori === b.kategori) {
                                r.details.forEach(d => {
                                    if (qty >= (d.min_qty || 0) && (d.max_qty === null ||
                                            qty <= d.max_qty)) {
                                        checkRule(r, d);
                                    }
                                });
                            }
                        });
                    }

                    // --- Priority 4: Per Merk ---
                    if (!bestRule && b.merk) {
                        const rulesMerk = findRule('merk');
                        rulesMerk.forEach(r => {
                            if (r.merk && r.merk.nama_merk === b.merk) {
                                r.details.forEach(d => {
                                    if (qty >= (d.min_qty || 0) && (d.max_qty === null ||
                                            qty <= d.max_qty)) {
                                        checkRule(r, d);
                                    }
                                });
                            }
                        });
                    }

                    // --- Priority 5: Per Supplier ---
                    if (!bestRule && b.kode_supplier) {
                        const rulesSupplier = findRule('supplier');
                        const totalSupplierNominal = supplierSubtotals[b.kode_supplier] || 0;
                        rulesSupplier.forEach(r => {
                            if (r.kode_supplier === b.kode_supplier) {
                                r.details.forEach(d => {
                                    const minNom = parseFloat(d.min_nominal) || 0;
                                    const maxNom = d.max_nominal ? parseFloat(d
                                        .max_nominal) : null;
                                    if (totalSupplierNominal >= minNom && (maxNom ===
                                            null || totalSupplierNominal <= maxNom)) {
                                        checkRule(r, d);
                                    }
                                });
                            }
                        });
                    }

                    // Apply discount values
                    const inputDis1 = row.find('.input-diskon1');
                    const inputDis2 = row.find('.input-diskon2');

                    if (bestRule && bestDetail) {
                        let d1_pct = 0;
                        let d2_pct = 0;

                        const rawDis1 = parseFloat(bestDetail.dis1) || 0;
                        const rawDis2 = parseFloat(bestDetail.dis2) || 0;

                        if (bestDetail.tipe_nilai === 'persen') {
                            d1_pct = rawDis1;
                            d2_pct = rawDis2;
                        } else {
                            // Convert nominal discount to percent
                            if (bestRule.tipe === 'supplier') {
                                const totalSupplierNominal = supplierSubtotals[b.kode_supplier] || 1;
                                d1_pct = (rawDis1 / totalSupplierNominal) * 100;
                                d2_pct = (rawDis2 / totalSupplierNominal) * 100;
                            } else {
                                if (sub > 0) {
                                    d1_pct = (rawDis1 / sub) * 100;
                                    d2_pct = (rawDis2 / sub) * 100;
                                }
                            }
                        }

                        inputDis1.val(d1_pct.toFixed(2)).attr('readonly', true);

                        if (jenisTransaksi === 'T') {
                            inputDis2.val(d2_pct.toFixed(2)).attr('readonly', true);
                        } else {
                            inputDis2.val('0').attr('readonly', true);
                        }
                    } else {
                        if (inputDis1.attr('readonly')) {
                            inputDis1.removeAttr('readonly').val('0');
                        }
                        if (inputDis2.attr('readonly')) {
                            inputDis2.removeAttr('readonly').val('0');
                        }
                    }
                });
            }

            function calculateTotals() {
                // Compute tiered discounts first
                calculateStrataDiscounts();

                let subtotalSum = 0;
                let totalDiskon = 0;
                let num = 1;

                $('#itemsTable tbody tr').each(function() {
                    const row = $(this);
                    const isPromo = row.find('.input-promo').is(':checked');

                    if (isPromo) {
                        row.addClass('promo-row');
                        row.find('.input-harga').val('0').attr('readonly', true);
                        row.find('.input-diskon1').val('0').attr('readonly', true);
                        row.find('.input-diskon2').val('0').attr('readonly', true);
                        row.find('.input-diskon3').val('0').attr('readonly', true);
                    } else {
                        row.removeClass('promo-row');
                        row.find('.input-harga').removeAttr('readonly');
                    }

                    row.find('.row-number').text(num++);
                    const qty = parseFloat(row.find('.input-qty').val()) || 0;
                    const harga = parseFloat(cleanNumber(row.find('.input-harga').val())) || 0;
                    const sub = qty * harga;

                    const d1_pct = parseFloat(row.find('.input-diskon1').val()) || 0;
                    const d2_pct = parseFloat(row.find('.input-diskon2').val()) || 0;
                    const d3_pct = parseFloat(row.find('.input-diskon3').val()) || 0;

                    const d1 = sub * (d1_pct / 100);
                    const d2 = (sub - d1) * (d2_pct / 100);
                    const d3 = (sub - d1 - d2) * (d3_pct / 100);
                    const diskon = Math.round(d1 + d2 + d3);

                    row.find('.input-diskon').val(formatNumber(diskon));

                    const nett = sub - diskon;
                    subtotalSum += sub;
                    totalDiskon += diskon;
                    row.find('.row-subtotal').text(formatCurrency(nett));
                });

                const diskonGlobal = parseFloat(cleanNumber($('#diskon_global').val())) || 0;
                const grandTotal = subtotalSum - totalDiskon - diskonGlobal;

                $('#summary-subtotal').text(formatCurrency(subtotalSum));
                $('#summary-diskon-item').text('- ' + formatCurrency(totalDiskon));
                $('#summary-diskon-global').text('- ' + formatCurrency(diskonGlobal));
                $('#summary-grandtotal').text(formatCurrency(grandTotal));
                $('#grand-total-display').text(formatCurrency(grandTotal));
            }

            // Load existing details (edit mode)
            if (existingDetails.length > 0) {
                existingDetails.forEach(d => {
                    const barang = barangsCache[d.kode_barang];
                    const name = barang ? barang.nama_barang : (d.barang ? d.barang.nama_barang : 'Barang');
                    const satuan = d.barang_satuan ? d.barang_satuan.satuan : '';
                    const isPromo = parseInt(d.is_promo) === 1;
                    appendRow(d.kode_barang, name, d.satuan_id, satuan, d.qty, parseInt(d.harga),
                        parseFloat(d.diskon1_persen || 0), parseFloat(d.diskon2_persen || 0),
                        parseFloat(d.diskon3_persen || 0), isPromo);
                });
                calculateTotals();
            }

            // Format on ready
            $('.input-number-format').each(function() {
                const v = $(this).val();
                if (v) $(this).val(formatNumber(cleanNumber(v)));
            });

            // Form submit guard
            $('#penjualanForm').on('submit', function(e) {
                if ($('#itemsTable tbody tr').length === 0) {
                    e.preventDefault();
                    return Swal.fire('Peringatan', 'Minimal harus ada 1 item barang!', 'warning');
                }

                // Overdue Check
                const opt = $('#kode_pelanggan').find(':selected');
                if (opt.val()) {
                    const hasOverdue = parseInt(opt.data('has-overdue')) === 1;
                    if (hasOverdue) {
                        e.preventDefault();
                        return Swal.fire({
                            title: 'Transaksi Ditolak',
                            text: 'Pelanggan ini memiliki faktur yang sudah jatuh tempo! Harap selesaikan pembayaran terlebih dahulu.',
                            icon: 'error'
                        });
                    }
                }

                // Credit Limit Check
                const jenisTransaksi = $('#jenis_transaksi').val();
                if (jenisTransaksi === 'K') {
                    const opt = $('#kode_pelanggan').find(':selected');
                    if (opt.val()) {
                        const sisaLimit = parseFloat(opt.data('sisa-limit')) || 0;

                        let subtotalSum = 0;
                        let totalDiskon = 0;
                        $('#itemsTable tbody tr').each(function() {
                            const qty = parseFloat($(this).find('.input-qty').val()) || 0;
                            const harga = parseFloat(cleanNumber($(this).find('.input-harga')
                                .val())) || 0;
                            const sub = qty * harga;

                            const d1_pct = parseFloat($(this).find('.input-diskon1').val()) || 0;
                            const d2_pct = parseFloat($(this).find('.input-diskon2').val()) || 0;
                            const d3_pct = parseFloat($(this).find('.input-diskon3').val()) || 0;

                            const d1 = sub * (d1_pct / 100);
                            const d2 = (sub - d1) * (d2_pct / 100);
                            const d3 = (sub - d1 - d2) * (d3_pct / 100);
                            const diskon = Math.round(d1 + d2 + d3);

                            subtotalSum += sub;
                            totalDiskon += diskon;
                        });
                        const diskonGlobal = parseFloat(cleanNumber($('#diskon_global').val())) || 0;
                        const grandTotal = subtotalSum - totalDiskon - diskonGlobal;

                        if (grandTotal > sisaLimit) {
                            e.preventDefault();
                            return Swal.fire({
                                title: 'Limit Kredit Terlampaui',
                                text: 'Transaksi tidak dapat disimpan karena total belanja (Rp ' +
                                    formatNumber(Math.round(grandTotal)) +
                                    ') melebihi sisa limit kredit pelanggan (Rp ' + formatNumber(
                                        Math.round(sisaLimit)) + ')!',
                                icon: 'error'
                            });
                        }
                    }
                }

                // Double check all stock limits in cart
                let stockOk = true;
                $('#itemsTable tbody tr').each(function() {
                    const row = $(this);
                    const barangCode = row.find('input[name*="[kode_barang]"]').val();
                    const barang = barangsCache[barangCode];
                    if (barang) {
                        const qty = parseFloat(row.find('.input-qty').val()) || 0;
                        const isi = parseFloat(row.attr('data-isi')) || 1;
                        const qtySmallest = qty * isi;

                        // Calculate other rows
                        let otherCartQtySmallest = 0;
                        $('#itemsTable tbody tr').not(row).each(function() {
                            const r = $(this);
                            if (r.find('input[name*="[kode_barang]"]').val() ===
                                barangCode) {
                                const rQty = parseFloat(r.find('.input-qty').val()) || 0;
                                const rIsi = parseFloat(r.attr('data-isi')) || 1;
                                otherCartQtySmallest += rQty * rIsi;
                            }
                        });

                        const originalQty = parseFloat(originalInvoiceQuantities[barangCode]) || 0;
                        const availableStock = (parseFloat(barang.stok) || 0) + originalQty;

                        if (otherCartQtySmallest + qtySmallest > availableStock) {
                            stockOk = false;
                            Swal.fire({
                                title: 'Stok Tidak Mencukupi',
                                html: `Stok barang <b>${barang.nama_barang}</b> tidak mencukupi!<br><br>` +
                                    `Stok tersedia: <b>${formatStokJS(barang.stok, barang.satuans)}</b>`,
                                icon: 'error'
                            });
                            // Clamp
                            const maxQtyAllowed = Math.floor((availableStock -
                                otherCartQtySmallest) / isi);
                            row.find('.input-qty').val(maxQtyAllowed > 0 ? maxQtyAllowed : 0);
                        }
                    }
                });

                if (!stockOk) {
                    e.preventDefault();
                    calculateTotals();
                    return false;
                }

                // Strip formatting
                $('.input-number-format').each(function() {
                    $(this).val(cleanNumber($(this).val()));
                });
            });
        });
    </script>
@endpush
