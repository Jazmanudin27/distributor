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

        .btn-show-history {
            color: #0d6efd;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 4px;
            transition: color 0.15s ease-in-out;
        }

        .btn-show-history:hover {
            color: #0a58ca;
            text-decoration: underline;
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
            <div class="d-flex gap-2">
                @if ($item->exists)
                    <a href="{{ route('penjualan.print', $item->no_faktur) }}" target="_blank"
                        class="btn btn-white btn-sm fw-bold hover-scale text-primary bg-white border btn-print-faktur"
                        data-no-faktur="{{ $item->no_faktur }}" data-cetak="{{ $item->cetak ?? 0 }}">
                        <i class="fa-solid fa-print me-1"></i> Cetak Faktur
                    </a>
                @endif
                <a href="{{ route('penjualan.index', request()->query()) }}"
                    class="btn btn-secondary btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-arrow-left me-1 text-white"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body p-4 bg-light">
            <form
                action="{{ $item->exists ? route('penjualan.update', array_merge(['penjualan' => $item->no_faktur], request()->query())) : route('penjualan.store', request()->query()) }}"
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
                                    <span
                                        class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1"
                                        style="font-size:10px; font-weight:500;">Auto</span></label>
                                <input type="text" name="no_faktur" id="no_faktur"
                                    class="form-control form-control-sm font-monospace fw-bold bg-light @error('no_faktur') is-invalid @enderror"
                                    value="{{ old('no_faktur', $item->no_faktur) }}" readonly>
                                @error('no_faktur')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="mb-2">
                                        <label for="tanggal" class="form-label fs-7 fw-bold text-secondary mb-1">Tanggal
                                            <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" id="tanggal"
                                            class="form-control form-control-sm @error('tanggal') is-invalid @enderror"
                                            value="{{ old('tanggal', $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : date('Y-m-d')) }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-2">
                                        <label class="form-label fs-7 fw-bold text-secondary mb-1">Jenis Transaksi
                                            <span class="text-danger">*</span></label>
                                        <select name="jenis_transaksi" id="jenis_transaksi"
                                            class="form-select form-select-sm" required>

                                            <option value="K"
                                                {{ in_array(old('jenis_transaksi', $item->jenis_transaksi), ['K', 'Kredit']) ? 'selected' : '' }}>
                                                Kredit / Tempo
                                            </option>

                                            <option value="T"
                                                {{ in_array(old('jenis_transaksi', $item->jenis_transaksi), ['T', 'Tunai']) ? 'selected' : '' }}>
                                                Tunai / Cash
                                            </option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-2">
                                        <label for="kode_sales" class="form-label fs-7 fw-bold text-secondary mb-1">Salesman
                                            <span class="text-danger">*</span></label>
                                        <select name="kode_sales" id="kode_sales"
                                            class="form-select form-select-sm @error('kode_sales') is-invalid @enderror"
                                            required>
                                            <option value="">-- Pilih Salesman --</option>
                                            @foreach ($salesmen as $s)
                                                <option value="{{ $s->nik }}"
                                                    data-is-kanvas="{{ $s->is_kanvas ? 1 : 0 }}"
                                                    {{ old('kode_sales', $item->kode_sales) === $s->nik ? 'selected' : '' }}>
                                                    {{ $s->name }} ({{ $s->nik }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kode_sales')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-2">
                                        <label class="form-label fs-7 fw-bold text-secondary mb-1">Operator</label>
                                        <input type="text" class="form-control form-control-sm bg-light text-muted"
                                            value="{{ auth()->user()->name ?? '-' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2" hidden>
                                <label for="tanggal_kirim" class="form-label fs-7 fw-bold text-secondary mb-1">Tanggal
                                    Kirim</label>
                                <input type="date" name="tanggal_kirim" id="tanggal_kirim"
                                    class="form-control form-control-sm"
                                    value="{{ old('tanggal_kirim', $item->tanggal_kirim ? \Carbon\Carbon::parse($item->tanggal_kirim)->format('Y-m-d') : '') }}">
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
                                            data-wilayah="{{ $p->wilayah?->nama_wilayah ?? '-' }}"
                                            data-metode="{{ $p->metode_bayar }}" data-limit="{{ $p->limit_pelanggan }}"
                                            data-sisa-limit="{{ $sisaLimit }}"
                                            data-has-overdue="{{ $hasOverdue ? 1 : 0 }}" data-ljt="{{ $p->ljt ?? 30 }}"
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
                            <div class="mb-2">
                                <label class="form-label fs-8 fw-bold text-secondary mb-1">Wilayah</label>
                                <input type="text" id="pelanggan_wilayah"
                                    class="form-control form-control-sm bg-light" readonly>
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
                                        value="{{ old('diskon_global', $item->exists ? round($item->diskon - $item->details->sum('total_diskon'), 2) : 0) }}"
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
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fs-8 fw-bold text-secondary mb-0">Pilih Barang</label>
                                <span id="quick_stock_display" class="badge bg-info-subtle text-info d-none"
                                    style="font-size: 10px; font-weight: 600;"></span>
                            </div>
                            <select id="quick_barang" class="form-select form-select-sm" style="width: 100%;">
                                <option value="">-- Cari / Pilih Barang --</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
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
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">D1 <span
                                    class="badge bg-secondary cursor-pointer toggle-quick-type" id="quick_d1_type"
                                    style="user-select: none;">%</span></label>
                            <input type="text" id="quick_diskon1_input" class="form-control form-control-sm text-end"
                                value="0">
                            <input type="hidden" id="quick_diskon1_percent" value="0">
                        </div>
                        <div class="col-lg-1 col-md-3 col-4" id="diskon2-quickadd-col">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">D2 <span
                                    class="badge bg-secondary cursor-pointer toggle-quick-type" id="quick_d2_type"
                                    style="user-select: none;">%</span></label>
                            <input type="text" id="quick_diskon2_input" class="form-control form-control-sm text-end"
                                value="0">
                            <input type="hidden" id="quick_diskon2_percent" value="0">
                        </div>
                        <div class="col-lg-1 col-md-3 col-4">
                            <label class="form-label fs-8 fw-bold text-secondary mb-1">D3 <span
                                    class="badge bg-secondary cursor-pointer toggle-quick-type" id="quick_d3_type"
                                    style="user-select: none;">%</span></label>
                            <input type="text" id="quick_diskon3_input" class="form-control form-control-sm text-end"
                                value="0">
                            <input type="hidden" id="quick_diskon3_percent" value="0">
                        </div>
                        <div class="col-lg-1 col-md-5 d-none">
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
                                    <th width="85" class="text-end">D1</th>
                                    <th width="85" class="text-end" id="diskon2-header-th">D2</th>
                                    <th width="85" class="text-end">D3</th>
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
                <div class="row g-4 pt-3 border-top">
                    <div class="col-md-7">
                        <div class="card border p-3 rounded bg-white shadow-sm h-100">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-note-sticky text-warning me-1"></i> Keterangan / Catatan
                            </h6>
                            <div class="mb-3">
                                <textarea name="keterangan" id="keterangan" class="form-control form-control-sm" rows="5"
                                    placeholder="Masukkan keterangan atau catatan transaksi di sini...">{{ old('keterangan', $item->keterangan) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card bg-light border-0 shadow-sm p-3 rounded h-100">
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
                            <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                <span class="fw-bold text-success">Grand Total</span>
                                <span class="fw-bold text-success fs-5" id="summary-grandtotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('penjualan.index', request()->query()) }}"
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

    <!-- Modal Histori Penjualan Barang -->
    <div class="modal fade" id="historyBarangModal" tabindex="-1" aria-labelledby="historyBarangModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-3 shadow">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="historyBarangModalLabel">
                        <i class="fa-solid fa-clock-rotate-left me-2"></i> Detail Penjualan Barang
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3 p-3 bg-light rounded border">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="fw-bold text-secondary">Pelanggan:</span> <span id="history-pelanggan-name"
                                    class="fw-bold text-dark">-</span>
                            </div>
                            <div class="col-md-6">
                                <span class="fw-bold text-secondary">Barang:</span> <span id="history-barang-name"
                                    class="fw-bold text-dark">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle" id="historyTable">
                            <thead class="table-light text-secondary text-uppercase fs-8 tracking-wider">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No Faktur</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">D1%</th>
                                    <th class="text-end">D2%</th>
                                    <th class="text-end">D3%</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light pt-0">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold"
                        data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const isEditMode = {{ $item->exists ? 'true' : 'false' }};
            const existingDetails = {!! json_encode($item->details ?? []) !!};

            // Sort existingDetails by brand/merk
            existingDetails.sort((a, b) => {
                const brandA = (a.barang && a.barang.merk) ? a.barang.merk.toString().toLowerCase() : '';
                const brandB = (b.barang && b.barang.merk) ? b.barang.merk.toString().toLowerCase() : '';
                return brandA.localeCompare(brandB);
            });

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
                let qtyFloat = parseFloat(stok) || 0;
                let isNegative = qtyFloat < 0;
                let remaining = Math.round(Math.abs(qtyFloat) * 10000) / 10000;
                let breakdowns = [];
                if (satuans && satuans.length > 0) {
                    let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                    let count = sorted.length;
                    sorted.forEach((sat, index) => {
                        let factor = parseFloat(sat.isi) || 1;
                        if (index === count - 1) {
                            let unitQty = Math.round((remaining / factor) * 10000) / 10000;
                            if (unitQty > 0) {
                                breakdowns.push(`${unitQty} ${sat.satuan}`);
                            }
                        } else {
                            let unitQty = Math.floor(Math.round((remaining / factor) * 100000000) /
                                100000000);
                            if (unitQty > 0) {
                                breakdowns.push(`${unitQty} ${sat.satuan}`);
                                remaining = Math.round((remaining - (unitQty * factor)) * 10000) / 10000;
                            }
                        }
                    });
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
                    url: '{{ route('pelanggan.search', [], false) }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            exclude_no_faktur: '{{ $item->no_faktur }}',
                            kode_sales: $('#kode_sales').val()
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
            $('#kode_sales').select2({
                theme: 'bootstrap-5',
                width: '100%'
            }).on('change', function() {
                // Clear customer when selected sales representative changes
                $('#kode_pelanggan').val(null).trigger('change');
            });

            function formatBarangResult(barang) {
                if (barang.loading) {
                    return barang.text;
                }
                const formattedStok = formatStokJS(barang.stok, barang.satuans);
                const $container = $(
                    `<div class="d-flex justify-content-between align-items-center py-1">
                        <div>
                            <div class="fw-bold text-dark fs-7">${barang.nama_barang}</div>
                            <div class="text-muted font-monospace fs-8">${barang.kode_barang}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size: 11px;">
                                <i class="fa-solid fa-box me-1"></i> ${formattedStok}
                            </span>
                        </div>
                    </div>`
                );
                return $container;
            }

            function formatBarangSelection(barang) {
                if (!barang.id) {
                    return barang.text;
                }
                return `${barang.nama_barang || barang.text} (${barang.kode_barang || barang.id})`;
            }

            $('#quick_barang').select2({
                theme: 'bootstrap-5',
                width: '100%',
                ajax: {
                    url: '{{ route('barang.search', [], false) }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            kode_sales: $('#kode_sales').val(),
                            tanggal: $('#tanggal').val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: formatBarangResult,
                templateSelection: formatBarangSelection
            });

            $('#quick_barang').on('select2:select', function(e) {
                const data = e.params.data;
                barangsCache[data.kode_barang] = data;
                updateSatuanDropdown(data);
                const formattedStok = formatStokJS(data.stok, data.satuans);
                $('#quick_stock_display').html(
                    `<i class="fa-solid fa-box me-1"></i> Stok: ${formattedStok}`).removeClass('d-none');

                evaluateQuickStrata();
                recalcDiskon();
            });

            // Number format helpers
            function formatNumber(num) {
                return num.toString().replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function cleanNumber(str) {
                let s = str.toString();
                if (s.includes('e') || s.includes('E')) {
                    let num = parseFloat(s);
                    return isNaN(num) ? "0" : Math.round(num).toString();
                }
                return s.replace(/\./g, "").replace(/\D/g, "") || "0";
            }

            function formatCurrency(value) {
                return 'Rp ' + Math.max(0, Math.round(value)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function formatPercentJS(val) {
                const num = parseFloat(val) || 0;
                if (num % 1 === 0) {
                    return num.toString();
                }
                return num.toFixed(2).replace('.', ',');
            }

            // Custom input handler for discount inputs
            $(document).on('input',
                '#quick_diskon1_input, #quick_diskon2_input, #quick_diskon3_input, .input-diskon1-val, .input-diskon2-val, .input-diskon3-val',
                function() {
                    let isQuick = $(this).attr('id') && $(this).attr('id').startsWith('quick_');
                    let type = '%';
                    if (isQuick) {
                        const id = $(this).attr('id');
                        const num = id.replace('quick_diskon', '').replace('_input', '');
                        type = $(`#quick_d${num}_type`).text().trim();
                    } else {
                        const td = $(this).closest('td');
                        type = td.find('.toggle-row-type').text().trim();
                    }

                    if (type === 'Rp') {
                        const start = this.selectionStart;
                        const prev = this.value.length;
                        const raw = cleanNumber($(this).val());
                        $(this).val(raw === "0" && $(this).val() === "" ? "" : formatNumber(raw));
                        const diff = this.value.length - prev;
                        this.setSelectionRange(start + diff, start + diff);
                    } else {
                        let val = $(this).val();
                        let normalizedVal = val.replace(/,/g, '.');
                        normalizedVal = normalizedVal.replace(/[^0-9.]/g, '');
                        const parts = normalizedVal.split('.');
                        if (parts.length > 2) {
                            normalizedVal = parts[0] + '.' + parts.slice(1).join('');
                        }

                        let floatVal = parseFloat(normalizedVal) || 0;
                        if (floatVal > 100) {
                            normalizedVal = '100';
                        }

                        let displayVal = val.replace(/[^0-9.,]/g, '');
                        const commaParts = displayVal.split(/[.,]/);
                        if (commaParts.length > 2) {
                            displayVal = commaParts[0] + ',' + commaParts.slice(1).join('');
                        }

                        if (parseFloat(displayVal.replace(/,/g, '.')) > 100) {
                            displayVal = '100';
                        }

                        if ($(this).val() !== displayVal) {
                            $(this).val(displayVal);
                        }
                    }
                });

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
                opt.attr('data-wilayah', data.wilayah || '-');
                opt.attr('data-metode', data.metode);
                opt.attr('data-limit', data.limit);
                opt.attr('data-sisa-limit', data.sisa_limit);
                opt.attr('data-has-overdue', data.has_overdue);
                opt.attr('data-ljt', data.ljt || 30);

                updatePelangganInfo(opt);
            });

            // Recalculate jatuh tempo on date or transaction type changes
            $('#tanggal, #jenis_transaksi').on('change', function() {
                const opt = $('#kode_pelanggan').find(':selected');
                if (opt.val()) {
                    updateJatuhTempo(opt);
                }
            });

            function updateJatuhTempo(opt) {
                const ljt = parseInt(opt.attr('data-ljt') || opt.data('ljt')) || 30;
                const tglVal = $('#tanggal').val();
                if (tglVal) {
                    const date = new Date(tglVal);
                    date.setDate(date.getDate() + ljt);
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    $('#tanggal_kirim').val(`${yyyy}-${mm}-${dd}`); // tanggal_kirim behaves as jatuh_tempo field
                }
            }

            function updatePelangganInfo(opt) {
                $('#pelanggan_kode').val(opt.attr('data-kode') || opt.data('kode') || '');
                $('#pelanggan_hp').val(opt.attr('data-hp') || opt.data('hp') || '-');
                $('#pelanggan_alamat').val(opt.attr('data-alamat') || opt.data('alamat') || '-');
                $('#pelanggan_wilayah').val(opt.attr('data-wilayah') || opt.data('wilayah') || '-');
                $('#pelanggan_metode').val(opt.attr('data-metode') || opt.data('metode') || '-');

                updateJatuhTempo(opt);

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
                if (hasOverdue && !isEditMode) {
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
                    $('#quick_stock_display').addClass('d-none').text('');
                    return;
                }
                const barang = barangsCache[code];
                if (barang) {
                    updateSatuanDropdown(barang);
                    const formattedStok = formatStokJS(barang.stok, barang.satuans);
                    $('#quick_stock_display').html(
                        `<i class="fa-solid fa-box me-1"></i> Stok: ${formattedStok}`).removeClass(
                        'd-none');
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
                evaluateQuickStrata();
                recalcDiskon();
            }); // Qty / harga / diskon sync
            // Qty / harga / diskon sync

            function evaluateQuickStrata() {
                const barangCode = $('#quick_barang').val();
                if (!barangCode) return;

                const barang = barangsCache[barangCode];
                if (!barang) return;

                // Disable automatic strata calculations to allow manual inputs
                $('#quick_diskon1_input').prop('readonly', false);
                $('#quick_diskon2_input').prop('readonly', false);

                if (barang.diskon_persen !== undefined && parseFloat(barang.diskon_persen) > 0) {
                    $('#quick_diskon1_input').val(formatPercentJS(barang.diskon_persen));
                    $('#quick_diskon1_percent').val(barang.diskon_persen);
                    $('#quick_d1_type').text('%').removeClass('bg-success').addClass('bg-secondary');
                } else {
                    $('#quick_diskon1_input').val('0');
                    $('#quick_diskon1_percent').val(0);
                }

                $('#quick_diskon2_input').val('0');
                $('#quick_diskon2_percent').val(0);
            }

            function recalcDiskon() {
                const price = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const qty = parseFloat($('#quick_qty').val()) || 0;
                const base = price * qty;

                // D1
                let d1_type = $('#quick_d1_type').text().trim();
                let d1_val_str = $('#quick_diskon1_input').val() || "0";
                let d1_val = d1_type === '%' ? parseFloat(d1_val_str.replace(/,/g, '.')) || 0 : parseFloat(
                    cleanNumber(d1_val_str)) || 0;
                let d1_pct = 0,
                    d1_rp = 0;
                if (d1_type === '%') {
                    d1_pct = d1_val;
                    d1_rp = base * (d1_pct / 100);
                } else {
                    d1_rp = d1_val;
                    if (d1_rp > base) {
                        d1_rp = base;
                        $('#quick_diskon1_input').val(formatNumber(Math.round(d1_rp)));
                    }
                    d1_pct = base > 0 ? (d1_rp / base) * 100 : 0;
                }
                $('#quick_diskon1_percent').val(d1_pct);

                // D2
                let d2_type = $('#quick_d2_type').text().trim();
                let d2_val_str = $('#quick_diskon2_input').val() || "0";
                let d2_val = d2_type === '%' ? parseFloat(d2_val_str.replace(/,/g, '.')) || 0 : parseFloat(
                    cleanNumber(d2_val_str)) || 0;
                let d2_pct = 0,
                    d2_rp = 0;
                let base2 = base - d1_rp;
                if (d2_type === '%') {
                    d2_pct = d2_val;
                    d2_rp = base2 * (d2_pct / 100);
                } else {
                    d2_rp = d2_val;
                    if (d2_rp > base2) {
                        d2_rp = base2;
                        $('#quick_diskon2_input').val(formatNumber(Math.round(d2_rp)));
                    }
                    d2_pct = base2 > 0 ? (d2_rp / base2) * 100 : 0;
                }
                $('#quick_diskon2_percent').val(d2_pct);

                // D3
                let d3_type = $('#quick_d3_type').text().trim();
                let d3_val_str = $('#quick_diskon3_input').val() || "0";
                let d3_val = d3_type === '%' ? parseFloat(d3_val_str.replace(/,/g, '.')) || 0 : parseFloat(
                    cleanNumber(d3_val_str)) || 0;
                let d3_pct = 0,
                    d3_rp = 0;
                let base3 = base2 - d2_rp;
                if (d3_type === '%') {
                    d3_pct = d3_val;
                    d3_rp = base3 * (d3_pct / 100);
                } else {
                    d3_rp = d3_val;
                    if (d3_rp > base3) {
                        d3_rp = base3;
                        $('#quick_diskon3_input').val(formatNumber(Math.round(d3_rp)));
                    }
                    d3_pct = base3 > 0 ? (d3_rp / base3) * 100 : 0;
                }
                $('#quick_diskon3_percent').val(d3_pct);

                const computed = Math.round(d1_rp + d2_rp + d3_rp);
                $('#quick_diskon').val(formatNumber(computed));
            }

            $('#quick_qty, #quick_harga').on('input change', function() {
                evaluateQuickStrata();
                recalcDiskon();
            });

            $('#quick_diskon1_input, #quick_diskon2_input, #quick_diskon3_input').on('input change',
                function() {
                    recalcDiskon();
                });

            $(document).on('click', '.toggle-quick-type', function() {
                const current = $(this).text().trim();
                const nextType = current === '%' ? 'Rp' : '%';
                $(this).text(nextType);
                if (nextType === 'Rp') {
                    $(this).removeClass('bg-secondary').addClass('bg-success');
                } else {
                    $(this).removeClass('bg-success').addClass('bg-secondary');
                }

                // Auto convert value
                const id = $(this).attr('id');
                const inputNum = id.replace('quick_d', '').replace('_type', '');
                const inputEl = $(`#quick_diskon${inputNum}_input`);

                const price = parseFloat(cleanNumber($('#quick_harga').val())) || 0;
                const qty = parseFloat($('#quick_qty').val()) || 0;
                const base = price * qty;

                let subVal = base;
                if (inputNum === '2') {
                    const d1_pct = parseFloat($('#quick_diskon1_percent').val()) || 0;
                    subVal = base - (base * d1_pct / 100);
                } else if (inputNum === '3') {
                    const d1_pct = parseFloat($('#quick_diskon1_percent').val()) || 0;
                    const d2_pct = parseFloat($('#quick_diskon2_percent').val()) || 0;
                    const base2 = base - (base * d1_pct / 100);
                    subVal = base2 - (base2 * d2_pct / 100);
                }

                if (nextType === 'Rp') {
                    const pct = parseFloat(inputEl.val().replace(/,/g, '.')) || 0;
                    const rp = Math.round(subVal * (pct / 100));
                    inputEl.val(formatNumber(rp));
                } else {
                    const rp = parseFloat(cleanNumber(inputEl.val())) || 0;
                    const pct = subVal > 0 ? (rp / subVal) * 100 : 0;
                    inputEl.val(formatPercentJS(pct));
                }

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
                // if (exist) return Swal.fire('Peringatan', 'Barang dengan satuan ini sudah ada di daftar!',
                //     'warning');

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
                                `Stok tersedia (termasuk faktur ini): <b>${formatStokJS(availableStock, barang.satuans)}</b><br>` +
                                `Jumlah diminta: <b>${qty} ${selectedSatuanOpt.data('name')}</b> (Setara ${newQtySmallest} PCS)<br>` +
                                `Sudah di keranjang: <b>${formatStokJS(currentCartQtySmallest, barang.satuans)}</b>`,
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
                $('#quick_diskon1_input').val(0);
                $('#quick_diskon2_input').val(0);
                $('#quick_diskon3_input').val(0);
                $('#quick_diskon1_percent').val(0);
                $('#quick_diskon2_percent').val(0);
                $('#quick_diskon3_percent').val(0);
                $('#quick_d1_type, #quick_d2_type, #quick_d3_type').text('%').removeClass('bg-success')
                    .addClass('bg-secondary');
                $('#quick_diskon').val(0);
                $('#quick_is_promo').prop('checked', false).trigger('change');

                calculateTotals();
                saveDraft();
                $('#quick_barang').select2('open');
            });

            // Handle quick promo checkbox changes
            $('#quick_is_promo').on('change', function() {
                const isChecked = $(this).is(':checked');
                if (isChecked) {
                    const currentPrice = $('#quick_harga').val();
                    $('#quick_harga').data('temp-original-price', currentPrice);
                    $('#quick_harga').val('0').attr('readonly', true);
                    $('#quick_diskon1_input').val('0').attr('readonly', true);
                    $('#quick_diskon2_input').val('0').attr('readonly', true);
                    $('#quick_diskon3_input').val('0').attr('readonly', true);
                } else {
                    const originalPrice = $('#quick_harga').data('temp-original-price') || '0';
                    $('#quick_harga').val(originalPrice).removeAttr('readonly');
                    $('#quick_diskon1_input').val('0').removeAttr('readonly');
                    $('#quick_diskon2_input').val('0').removeAttr('readonly');
                    $('#quick_diskon3_input').val('0').removeAttr('readonly');
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
                    row.find('.input-diskon1-val').val('0').attr('readonly', true);
                    row.find('.input-diskon2-val').val('0').attr('readonly', true);
                    row.find('.input-diskon3-val').val('0').attr('readonly', true);
                } else {
                    row.removeClass('promo-row');
                    const originalPrice = inputHarga.attr('data-original-harga') || '0';
                    inputHarga.val(originalPrice).removeAttr('readonly');
                    row.find('.input-diskon1-val').removeAttr('readonly');
                    row.find('.input-diskon2-val').removeAttr('readonly');
                    row.find('.input-diskon3-val').removeAttr('readonly');
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
                        <td class="fw-bold text-dark"><span class="btn-show-history" data-kode="${barangCode}" data-nama="${barangName}">${barangName}</span></td>
                        <td class="text-center">${satuanName}
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
                            <div class="input-group input-group-sm" style="min-width: 90px; max-width: 100px; margin-left: auto;">
                                <span class="input-group-text cursor-pointer toggle-row-type text-primary fw-bold" style="padding: 0.1rem 0.3rem; font-size: 0.65rem; user-select: none;">%</span>
                                <input type="text" class="form-control form-control-sm text-end input-diskon1-val" value="${formatPercentJS(d1)}" ${isPromo ? 'readonly' : ''}>
                                <input type="hidden" name="items[${rowIndex}][diskon1_persen]" class="input-diskon1" value="${d1}">
                            </div>
                        </td>
                        <td class="td-diskon2">
                            <div class="input-group input-group-sm" style="min-width: 90px; max-width: 100px; margin-left: auto;">
                                <span class="input-group-text cursor-pointer toggle-row-type text-primary fw-bold" style="padding: 0.1rem 0.3rem; font-size: 0.65rem; user-select: none;">%</span>
                                <input type="text" class="form-control form-control-sm text-end input-diskon2-val" value="${formatPercentJS(d2)}" ${isPromo ? 'readonly' : ''}>
                                <input type="hidden" name="items[${rowIndex}][diskon2_persen]" class="input-diskon2" value="${d2}">
                            </div>
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="min-width: 90px; max-width: 100px; margin-left: auto;">
                                <span class="input-group-text cursor-pointer toggle-row-type text-primary fw-bold" style="padding: 0.1rem 0.3rem; font-size: 0.65rem; user-select: none;">%</span>
                                <input type="text" class="form-control form-control-sm text-end input-diskon3-val" value="${formatPercentJS(d3)}" ${isPromo ? 'readonly' : ''}>
                                <input type="hidden" name="items[${rowIndex}][diskon3_persen]" class="input-diskon3" value="${d3}">
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
                // Apply D2 visibility to newly appended row
                if (typeof toggleDiskon2Visibility === 'function') toggleDiskon2Visibility();
            }

            // Remove row
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
                calculateTotals();
                saveDraft();
            });

            $(document).on('click', '.toggle-row-type', function() {
                const row = $(this).closest('tr');
                if (row.find('.input-promo').is(':checked') || $(this).closest('td').find(
                        'input[type="text"]').attr('readonly')) return;

                row.attr('data-manual-discount', '1');
                const current = $(this).text().trim();
                const nextType = current === '%' ? 'Rp' : '%';
                $(this).text(nextType);
                if (nextType === 'Rp') {
                    $(this).removeClass('text-primary').addClass('text-success');
                } else {
                    $(this).removeClass('text-success').addClass('text-primary');
                }

                const index = row.find('.toggle-row-type').index($(this));
                const inputEl = row.find('.input-diskon' + (index + 1) + '-val');

                const qty = parseFloat(row.find('.input-qty').val()) || 0;
                const harga = parseFloat(cleanNumber(row.find('.input-harga').val())) || 0;
                const base = qty * harga;

                let subVal = base;
                if (index >= 1) {
                    const d1_pct = parseFloat(row.find('.input-diskon1').val()) || 0;
                    subVal = base - (base * d1_pct / 100);
                }
                if (index >= 2) {
                    const d2_pct = parseFloat(row.find('.input-diskon2').val()) || 0;
                    subVal = subVal - (subVal * d2_pct / 100);
                }

                if (nextType === 'Rp') {
                    const pct = parseFloat(inputEl.val().replace(/,/g, '.')) || 0;
                    const rp = Math.round(subVal * (pct / 100));
                    inputEl.val(formatNumber(rp));
                } else {
                    const rp = parseFloat(cleanNumber(inputEl.val())) || 0;
                    const pct = subVal > 0 ? (rp / subVal) * 100 : 0;
                    inputEl.val(formatPercentJS(pct));
                }

                calculateTotals();
            });

            // Recalculate on input
            $(document).on('input change',
                '.input-qty, .input-harga, .input-diskon1-val, .input-diskon2-val, .input-diskon3-val, #diskon_global',
                calculateTotals);

            // Mark row as manual discount when manually editing discount values
            $(document).on('input keydown change', '.input-diskon1-val, .input-diskon2-val, .input-diskon3-val',
                function() {
                    $(this).closest('tr').attr('data-manual-discount', '1');
                });

            // Toggle Diskon 2 visibility based on jenis_transaksi
            function toggleDiskon2Visibility() {
                $('#diskon2-header-th').show();
                $('#diskon2-quickadd-col').show();
                $('.td-diskon2').show();
            }

            // Trigger recalculation on jenis transaksi change
            $('#jenis_transaksi').on('change', function() {
                // Reset manual-discount flag so strata is recalculated for all rows
                $('#itemsTable tbody tr').removeAttr('data-manual-discount');
                toggleDiskon2Visibility();
                evaluateQuickStrata();
                calculateTotals();
            });

            // Run on page load
            toggleDiskon2Visibility();

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
                    const satuanName = row.find('input[name*="[satuan]"]').val() || '';
                    Swal.fire({
                        title: 'Stok Tidak Mencukupi',
                        html: `Stok barang <b>${barang.nama_barang}</b> tidak mencukupi!<br><br>` +
                            `Stok tersedia (termasuk faktur ini): <b>${formatStokJS(availableStock, barang.satuans)}</b><br>` +
                            `Jumlah diinput: <b>${qty} ${satuanName}</b> (Setara ${newQtySmallest} PCS)<br>` +
                            `Sudah di keranjang (baris lain): <b>${formatStokJS(otherCartQtySmallest, barang.satuans)}</b>`,
                        icon: 'error'
                    });

                    // Clamp
                    const maxQtyAllowed = Math.floor((availableStock - otherCartQtySmallest) / isi);
                    $(this).val(maxQtyAllowed > 0 ? maxQtyAllowed : 0);
                    calculateTotals();
                }
            });

            function calculateStrataDiscounts() {
                // Disabled automatic calculation - discounts are entered manually
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

                    const d1_type = row.find('.toggle-row-type').eq(0).text().trim();
                    const d1_val_str = row.find('.input-diskon1-val').val() || "0";
                    const d1_val = d1_type === '%' ? parseFloat(d1_val_str.toString().replace(/,/g, '.')) ||
                        0 : parseFloat(cleanNumber(d1_val_str)) || 0;
                    let d1_pct = 0,
                        d1_rp = 0;
                    if (d1_type === '%') {
                        d1_pct = d1_val;
                        d1_rp = sub * (d1_pct / 100);
                    } else {
                        d1_rp = d1_val;
                        if (d1_rp > sub) {
                            d1_rp = sub;
                            row.find('.input-diskon1-val').val(formatNumber(Math.round(d1_rp)));
                        }
                        d1_pct = sub > 0 ? (d1_rp / sub) * 100 : 0;
                    }
                    row.find('.input-diskon1').val(d1_pct);

                    const d2_type = row.find('.toggle-row-type').eq(1).text().trim();
                    const d2_val_str = row.find('.input-diskon2-val').val() || "0";
                    const d2_val = d2_type === '%' ? parseFloat(d2_val_str.toString().replace(/,/g, '.')) ||
                        0 : parseFloat(cleanNumber(d2_val_str)) || 0;
                    let d2_pct = 0,
                        d2_rp = 0;
                    let sub2 = sub - d1_rp;
                    if (d2_type === '%') {
                        d2_pct = d2_val;
                        d2_rp = sub2 * (d2_pct / 100);
                    } else {
                        d2_rp = d2_val;
                        if (d2_rp > sub2) {
                            d2_rp = sub2;
                            row.find('.input-diskon2-val').val(formatNumber(Math.round(d2_rp)));
                        }
                        d2_pct = sub2 > 0 ? (d2_rp / sub2) * 100 : 0;
                    }
                    row.find('.input-diskon2').val(d2_pct);

                    const d3_type = row.find('.toggle-row-type').eq(2).text().trim();
                    const d3_val_str = row.find('.input-diskon3-val').val() || "0";
                    const d3_val = d3_type === '%' ? parseFloat(d3_val_str.toString().replace(/,/g, '.')) ||
                        0 : parseFloat(cleanNumber(d3_val_str)) || 0;
                    let d3_pct = 0,
                        d3_rp = 0;
                    let sub3 = sub2 - d2_rp;
                    if (d3_type === '%') {
                        d3_pct = d3_val;
                        d3_rp = sub3 * (d3_pct / 100);
                    } else {
                        d3_rp = d3_val;
                        if (d3_rp > sub3) {
                            d3_rp = sub3;
                            row.find('.input-diskon3-val').val(formatNumber(Math.round(d3_rp)));
                        }
                        d3_pct = sub3 > 0 ? (d3_rp / sub3) * 100 : 0;
                    }
                    row.find('.input-diskon3').val(d3_pct);

                    const diskon = Math.round(d1_rp + d2_rp + d3_rp);

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
                    // Mark loaded rows as manual so their loaded discounts aren't overwritten
                    $('#itemsTable tbody tr').last().attr('data-manual-discount', '1');
                });
                calculateTotals();
            }

            // Format on ready
            $('.input-number-format').each(function() {
                const v = $(this).val();
                if (v) $(this).val(formatNumber(cleanNumber(v)));
            });

            // --- Draft Persist System ---
            function saveDraft() {
                const key = isEditMode ? `penjualan_edit_draft_${$('#no_faktur').val()}` : `penjualan_create_draft`;

                const items = [];
                $('#itemsTable tbody tr').each(function() {
                    const row = $(this);
                    const item = {
                        kode_barang: row.find('input[name*="[kode_barang]"]').val(),
                        nama_barang: row.find('td').eq(2).text().trim(),
                        satuan_id: row.find('input[name*="[satuan_id]"]').val(),
                        satuan: row.find('input[name*="[satuan]"]').val(),
                        is_promo: row.find('.input-promo').is(':checked'),
                        qty: parseFloat(row.find('.input-qty').val()) || 0,
                        harga: parseFloat(cleanNumber(row.find('.input-harga').val())) || 0,
                        diskon1_persen: parseFloat(row.find('.input-diskon1').val()) || 0,
                        diskon2_persen: parseFloat(row.find('.input-diskon2').val()) || 0,
                        diskon3_persen: parseFloat(row.find('.input-diskon3').val()) || 0,
                        diskon1_val: row.find('.input-diskon1-val').val() || '0',
                        diskon2_val: row.find('.input-diskon2-val').val() || '0',
                        diskon3_val: row.find('.input-diskon3-val').val() || '0',
                        diskon1_type: row.find('.toggle-row-type').eq(0).text().trim() || '%',
                        diskon2_type: row.find('.toggle-row-type').eq(1).text().trim() || '%',
                        diskon3_type: row.find('.toggle-row-type').eq(2).text().trim() || '%'
                    };
                    items.push(item);
                });

                const opt = $('#kode_pelanggan').find(':selected');
                const pelangganInfo = opt.val() ? {
                    id: opt.val(),
                    text: opt.text().trim(),
                    kode: opt.attr('data-kode') || opt.data('kode'),
                    hp: opt.attr('data-hp') || opt.data('hp'),
                    alamat: opt.attr('data-alamat') || opt.data('alamat'),
                    metode: opt.attr('data-metode') || opt.data('metode'),
                    limit: opt.attr('data-limit') || opt.data('limit'),
                    sisa_limit: opt.attr('data-sisa-limit') || opt.data('sisa-limit'),
                    has_overdue: opt.attr('data-has-overdue') || opt.data('has-overdue')
                } : null;

                const draft = {
                    tanggal: $('#tanggal').val(),
                    jenis_transaksi: $('#jenis_transaksi').val(),
                    kode_sales: $('#kode_sales').val(),
                    kode_pelanggan: $('#kode_pelanggan').val(),
                    pelangganInfo: pelangganInfo,
                    diskon_global: $('#diskon_global').val(),
                    items: items,
                    barangsCache: barangsCache,
                    timestamp: new Date().getTime()
                };

                localStorage.setItem(key, JSON.stringify(draft));
            }

            function restoreDraft(draft) {
                // 1. Restore metadata
                if (draft.tanggal) $('#tanggal').val(draft.tanggal);
                if (draft.jenis_transaksi) $('#jenis_transaksi').val(draft.jenis_transaksi).trigger('change');

                if (draft.kode_sales) {
                    $('#kode_sales').val(draft.kode_sales).trigger('change');
                }

                if (draft.diskon_global) {
                    $('#diskon_global').val(formatNumber(cleanNumber(draft.diskon_global)));
                }

                // 2. Restore barangsCache
                if (draft.barangsCache) {
                    Object.assign(barangsCache, draft.barangsCache);
                }

                // 3. Clear existing table rows
                $('#itemsTable tbody').empty();

                // 4. Append draft rows
                if (draft.items && draft.items.length > 0) {
                    draft.items.forEach(item => {
                        appendRow(
                            item.kode_barang,
                            item.nama_barang,
                            item.satuan_id,
                            item.satuan,
                            item.qty,
                            item.harga,
                            item.diskon1_persen,
                            item.diskon2_persen,
                            item.diskon3_persen,
                            item.is_promo
                        );

                        const row = $('#itemsTable tbody tr').last();
                        row.find('.toggle-row-type').eq(0).text(item.diskon1_type || '%');
                        row.find('.toggle-row-type').eq(1).text(item.diskon2_type || '%');
                        row.find('.toggle-row-type').eq(2).text(item.diskon3_type || '%');

                        for (let i = 0; i < 3; i++) {
                            const toggle = row.find('.toggle-row-type').eq(i);
                            if (toggle.text().trim() === 'Rp') {
                                toggle.removeClass('text-primary').addClass('text-success');
                            } else {
                                toggle.removeClass('text-success').addClass('text-primary');
                            }
                        }

                        row.find('.input-diskon1-val').val(item.diskon1_val || '0');
                        row.find('.input-diskon2-val').val(item.diskon2_val || '0');
                        row.find('.input-diskon3-val').val(item.diskon3_val || '0');
                    });
                }

                // 5. Restore select2 value for pelanggan if exists
                if (draft.pelangganInfo) {
                    const p = draft.pelangganInfo;
                    const newOption = new Option(p.text, p.id, true, true);
                    $(newOption).attr('data-kode', p.kode);
                    $(newOption).attr('data-hp', p.hp);
                    $(newOption).attr('data-alamat', p.alamat);
                    $(newOption).attr('data-metode', p.metode);
                    $(newOption).attr('data-limit', p.limit);
                    $(newOption).attr('data-sisa-limit', p.sisa_limit);
                    $(newOption).attr('data-has-overdue', p.has_overdue);

                    $('#kode_pelanggan').append(newOption).trigger('change');
                    updatePelangganInfo($(newOption));
                }

                calculateTotals();
            }

            function initDraftSystem() {
                const key = isEditMode ? `penjualan_edit_draft_${$('#no_faktur').val()}` : `penjualan_create_draft`;
                const savedDraftStr = localStorage.getItem(key);
                if (savedDraftStr) {
                    try {
                        const savedDraft = JSON.parse(savedDraftStr);
                        if (savedDraft && savedDraft.items && savedDraft.items.length > 0) {
                            Swal.fire({
                                title: 'Draft Transaksi Ditemukan',
                                text: isEditMode ?
                                    'Ditemukan draf perubahan untuk faktur ini yang belum disimpan. Pulihkan?' :
                                    'Ditemukan draft transaksi yang belum disimpan. Apakah Anda ingin melanjutkan?',
                                icon: 'info',
                                showCancelButton: true,
                                confirmButtonText: 'Pulihkan',
                                cancelButtonText: 'Abaikan',
                                confirmButtonColor: '#10b981',
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

            // Save draft on form inputs change
            $(document).on('input change', '#penjualanForm input, #penjualanForm select', function() {
                saveDraft();
            });

            // Save draft when row properties toggle
            $(document).on('click', '.toggle-row-type', function() {
                setTimeout(saveDraft, 50);
            });

            // Clear draft when user explicitly cancels/goes back
            $(document).on('click', 'a[href*="penjualan.index"]', function() {
                const key = isEditMode ? `penjualan_edit_draft_${$('#no_faktur').val()}` :
                    `penjualan_create_draft`;
                localStorage.removeItem(key);
            });

            // Form submit guard
            $('#penjualanForm').on('submit', function(e) {
                if ($('#itemsTable tbody tr').length === 0) {
                    e.preventDefault();
                    return Swal.fire('Peringatan', 'Minimal harus ada 1 item barang!', 'warning');
                }

                // Overdue Check
                if (!isEditMode) {
                    const opt = $('#kode_pelanggan').find(':selected');
                    if (opt.val()) {
                        const hasOverdue = parseInt(opt.attr('data-has-overdue') || opt.data(
                            'has-overdue')) === 1;
                        if (hasOverdue) {
                            e.preventDefault();
                            return Swal.fire({
                                title: 'Transaksi Ditolak',
                                text: 'Pelanggan ini memiliki faktur yang sudah jatuh tempo! Harap selesaikan pembayaran terlebih dahulu.',
                                icon: 'error'
                            });
                        }
                    }
                }

                // Credit Limit Check
                const jenisTransaksi = $('#jenis_transaksi').val();
                const isSalesCanvas = parseInt($('#kode_sales').find(':selected').attr(
                    'data-is-kanvas')) === 1;
                if ((jenisTransaksi === 'K' || jenisTransaksi === 'T') && !isSalesCanvas) {
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
                                    `Stok tersedia (termasuk faktur ini): <b>${formatStokJS(availableStock, barang.satuans)}</b>`,
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

                // Clear draft since transaction is being saved
                const key = isEditMode ? `penjualan_edit_draft_${$('#no_faktur').val()}` :
                    `penjualan_create_draft`;
                localStorage.removeItem(key);

                // Strip formatting
                $('.input-number-format').each(function() {
                    $(this).val(cleanNumber($(this).val()));
                });
            });

            // Click on product name to show transaction detail history
            $(document).on('click', '.btn-show-history', function() {
                const kodePelanggan = $('#kode_pelanggan').val();
                if (!kodePelanggan) {
                    return Swal.fire('Peringatan', 'Pilih pelanggan terlebih dahulu!', 'warning');
                }

                const kodeBarang = $(this).attr('data-kode');
                const namaBarang = $(this).attr('data-nama');
                const namaPelanggan = $('#kode_pelanggan').find(':selected').text().trim();

                // Set metadata in modal
                $('#history-pelanggan-name').text(namaPelanggan);
                $('#history-barang-name').text(`${namaBarang} (${kodeBarang})`);

                // Clear history table tbody
                const tbody = $('#historyTable tbody');
                tbody.html(
                    '<tr><td colspan="9" class="text-center"><i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat data histori...</td></tr>'
                );

                // Open modal
                const myModal = new bootstrap.Modal(document.getElementById('historyBarangModal'));
                myModal.show();

                // Fetch history data
                $.ajax({
                    url: '{{ route('penjualan.history-barang', [], false) }}',
                    method: 'GET',
                    data: {
                        kode_pelanggan: kodePelanggan,
                        kode_barang: kodeBarang
                    },
                    dataType: 'json',
                    success: function(response) {
                        tbody.empty();
                        if (response.length === 0) {
                            tbody.append(
                                '<tr><td colspan="9" class="text-center text-muted py-3">Tidak ada riwayat transaksi untuk barang ini dengan pelanggan tersebut.</td></tr>'
                            );
                            return;
                        }

                        response.forEach(function(row) {
                            const d1 = parseFloat(row.diskon1_persen) || 0;
                            const d2 = parseFloat(row.diskon2_persen) || 0;
                            const d3 = parseFloat(row.diskon3_persen) || 0;

                            const d1Str = d1 > 0 ? d1.toFixed(2) + '%' : '';
                            const d2Str = d2 > 0 ? d2.toFixed(2) + '%' : '';
                            const d3Str = d3 > 0 ? d3.toFixed(2) + '%' : '';

                            const formattedHarga = formatCurrency(row.harga).replace(
                                'Rp ', 'Rp');
                            const formattedTotal = formatCurrency(row.total).replace(
                                'Rp ', 'Rp');

                            const tr = `
                                <tr>
                                    <td>${row.tanggal}</td>
                                    <td class="font-monospace">${row.no_faktur}</td>
                                    <td class="text-end">${parseFloat(row.qty).toFixed(2)}</td>
                                    <td class="text-center">${row.satuan}</td>
                                    <td class="text-end">${formattedHarga}</td>
                                    <td class="text-end text-danger">${d1Str}</td>
                                    <td class="text-end text-danger">${d2Str}</td>
                                    <td class="text-end text-danger">${d3Str}</td>
                                    <td class="text-end fw-bold">${formattedTotal}</td>
                                </tr>
                            `;
                            tbody.append(tr);
                        });
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="9" class="text-center text-danger py-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Gagal memuat data histori transaksi.</td></tr>'
                        );
                    }
                });
            });

            // Keyboard Shortcuts
            $(document).on('keydown', function(e) {
                // Ctrl+Enter to save transaction
                if (e.ctrlKey && e.key === 'Enter') {
                    e.preventDefault();
                    $('#btn-save-penjualan').click();
                }
            });

            // Enter to add item in quick input bar
            $('#quick_satuan, #quick_qty, #quick_harga, #quick_diskon1_percent, #quick_diskon2_percent, #quick_diskon3_percent, #quick_is_promo')
                .on('keydown', function(e) {
                    if (e.key === 'Enter' && !e.ctrlKey) {
                        e.preventDefault();
                        $('#btn-add-quick').click();
                    }
                });
        });
    </script>
@endpush
