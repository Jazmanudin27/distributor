@extends('layouts.mobile')

@section('title', 'Input Order Baru')

@push('styles')
    <style>
        .cart-item-card {
            background: rgba(30, 41, 59, 0.45) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15) !important;
            transition: all 0.25s ease;
        }

        .cart-item-card:hover {
            border-color: rgba(99, 102, 241, 0.25) !important;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.1) !important;
        }

        .btn-qty-minus,
        .btn-qty-plus {
            border-color: rgba(255, 255, 255, 0.1) !important;
            background-color: rgba(255, 255, 255, 0.03) !important;
            color: #f8fafc !important;
            transition: all 0.15s ease-in-out;
            width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-qty-minus:hover,
        .btn-qty-plus:hover {
            background-color: rgba(99, 102, 241, 0.15) !important;
            border-color: rgba(99, 102, 241, 0.3) !important;
        }

        .btn-qty-minus:active,
        .btn-qty-plus:active {
            transform: scale(0.9);
            background-color: rgba(99, 102, 241, 0.3) !important;
        }

        .cart-item-card .form-control,
        .cart-item-card .form-select {
            border-radius: 8px !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            background-color: rgba(15, 23, 42, 0.6) !important;
            color: #fff !important;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .cart-item-card .form-control:focus,
        .cart-item-card .form-select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
        }

        .btn-remove-item {
            transition: all 0.2s ease;
        }

        .btn-remove-item:active {
            transform: scale(0.85);
        }
    </style>
@endpush

@section('content')
    <h5 class="fw-bold mb-3" style="font-size: 1.1rem; letter-spacing: 0.5px;">Input Order Penjualan</h5>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 py-2 px-3 mb-3 small"
            style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171;">
            <strong class="d-block mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Gagal menyimpan pesanan:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mobile.order.store') }}" method="POST" id="order-form">
        @csrf

        <!-- Form Header Info -->
        <div class="mobile-card" style="position: relative; z-index: 11;">
            <div class="mb-2">
                <label class="form-label text-secondary small mb-1">Nomor Faktur</label>
                <input type="text" name="no_faktur"
                    class="form-control form-control-sm font-monospace bg-dark text-white border-secondary"
                    value="{{ $noFaktur }}" readonly style="background-color: rgba(255,255,255,0.03) !important;">
            </div>

            <div class="row g-2 mb-2">
                <div class="col-12">
                    <label class="form-label text-secondary small mb-1">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal"
                        class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}"
                        required>
                </div>
                <input type="hidden" name="tanggal_kirim" value="">
            </div>

            <!-- Customer Selection -->
            <div class="mb-2 position-relative">
                <label class="form-label text-secondary small mb-1">Pilih Pelanggan</label>
                @if ($pelanggan)
                    <!-- Locked Customer (Pre-selected) -->
                    <div class="p-2 rounded border border-primary bg-dark bg-opacity-50">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-white small" id="selected-customer-name"
                                data-kode="{{ $pelanggan->kode_pelanggan }}"
                                data-overdue="{{ $pelanggan->hasOverdueInvoices() ? 1 : 0 }}"
                                data-sisa-limit="{{ $pelanggan->getSisaLimitKredit() }}"
                                data-jenis-pelanggan="{{ $pelanggan->jenis_pelanggan ?: '0' }}">
                                {{ $pelanggan->nama_pelanggan }}
                            </span>
                            <span class="badge bg-secondary btn-sm" style="font-size: 0.65rem;">Terkunci</span>
                        </div>
                        <div class="text-secondary mt-1" style="font-size: 0.7rem;">Kode: {{ $pelanggan->kode_pelanggan }}
                        </div>
                        <input type="hidden" name="kode_pelanggan" id="kode_pelanggan"
                            value="{{ $pelanggan->kode_pelanggan }}">
                    </div>
                @else
                    <!-- Autocomplete Dropdown Search -->
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark text-secondary border-secondary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="customer-search-input"
                            class="form-control form-control-sm bg-dark text-white border-secondary"
                            placeholder="Cari nama atau kode pelanggan..." required>
                    </div>
                    <input type="hidden" name="kode_pelanggan" id="kode_pelanggan" required>

                    <div id="customer-search-results" class="list-group position-absolute w-100 shadow-lg mt-1 d-none"
                        style="z-index: 1050; max-height: 200px; overflow-y: auto; background-color: #161e31; border: 1px solid var(--border-color); border-radius: 8px;">
                        <!-- results by js -->
                    </div>
                @endif
            </div>

            <!-- Selected Customer Quick Info (e.g. Credit Limits / Overdue Warning) -->
            <div id="customer-info-box"
                class="{{ $pelanggan ? '' : 'd-none' }} p-2 rounded border border-secondary mt-2 bg-dark bg-opacity-20"
                style="font-size: 0.75rem;">

                <div class="mb-2 pb-1 border-bottom border-secondary border-opacity-20">
                    <span class="text-secondary d-block mb-1"
                        style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Detail
                        Pelanggan</span>
                    <h6 class="fw-bold text-white mb-0" id="detail-nama-display" style="font-size: 0.8rem;">
                        {{ $pelanggan ? $pelanggan->nama_pelanggan : '' }}</h6>
                    <div class="text-secondary small mt-1">
                        <span id="detail-hp-display"><i class="fa-solid fa-phone me-1 fs-8"></i>
                            {{ $pelanggan ? $pelanggan->no_hp_pelanggan : '-' }}</span>
                    </div>
                </div>

                <div class="row g-2 mb-1">
                    <div class="col-4 text-secondary">Alamat:</div>
                    <div class="col-8 text-end text-white" id="info-alamat" style="word-break: break-word;">
                        {{ $pelanggan ? $pelanggan->alamat_pelanggan : '-' }}
                    </div>
                </div>

                <div class="row g-2 mb-1">
                    <div class="col-4 text-secondary">Wilayah:</div>
                    <div class="col-8 text-end text-white" id="info-wilayah">
                        {{ $pelanggan && $pelanggan->wilayah ? $pelanggan->wilayah->nama_wilayah : '-' }}
                        @if ($pelanggan && $pelanggan->subWilayah)
                            / {{ $pelanggan->subWilayah->nama_wilayah }}
                        @endif
                    </div>
                </div>

                <div class="row g-2 mb-1">
                    <div class="col-5 text-secondary">Limit Kredit:</div>
                    <div class="col-7 text-end fw-semibold text-white" id="info-limit-kredit">
                        Rp {{ $pelanggan ? number_format($pelanggan->limit_pelanggan, 0, ',', '.') : '0' }}
                    </div>
                </div>

                <div class="row g-2 mb-1">
                    <div class="col-5 text-secondary">Sisa Limit Kredit:</div>
                    <div class="col-7 text-end fw-bold text-success" id="info-sisa-limit">
                        Rp {{ $pelanggan ? number_format($pelanggan->getSisaLimitKredit(), 0, ',', '.') : '0' }}
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-5 text-secondary">Metode Bayar:</div>
                    <div class="col-7 text-end fw-semibold text-info" id="info-metode-bayar">
                        {{ $pelanggan ? ($pelanggan->metode_bayar ?: '-') : '-' }}
                    </div>
                </div>

                <div id="overdue-warning"
                    class="{{ $pelanggan && $pelanggan->hasOverdueInvoices() ? '' : 'd-none' }} mt-2">
                    <div class="alert alert-danger p-3 mb-0 rounded-4"
                        style="font-size: 0.75rem; background-color: rgba(220, 38, 38, 0.2); border: 1.5px solid rgba(220, 38, 38, 0.4); color: #fecaca; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);">
                        <div class="d-flex align-items-center mb-2 text-danger fw-bold" style="color: #fca5a5 !important;">
                            <i class="fa-solid fa-triangle-exclamation me-1.5 fs-6"></i>
                            <span class="fw-bold" style="font-size: 0.8rem; letter-spacing: 0.3px;">TOKO DIBLOKIR
                                (OVERDUE)!</span>
                        </div>
                        <p class="text-white-50 mb-2" style="font-size: 0.7rem; line-height: 1.3;">
                            Pelanggan memiliki tagihan jatuh tempo yang belum diselesaikan. Detail faktur:
                        </p>
                        <ul class="mb-0 ps-3" id="overdue-invoices-list"
                            style="font-size: 0.7rem; list-style-type: disc; color: #f8fafc;">
                            @if ($pelanggan && $pelanggan->hasOverdueInvoices())
                                @foreach ($pelanggan->getOverdueInvoices() as $inv)
                                    @php
                                        $sisa =
                                            $inv->grand_total -
                                            $inv->getApprovedPembayaranTotal() -
                                            $inv->getTotalRetur();
                                        $dueDate = \Carbon\Carbon::parse($inv->tanggal)->addDays(
                                            $inv->pelanggan->ljt ?? 30,
                                        );
                                    @endphp
                                    <li class="mb-2">
                                        Faktur <strong class="text-white font-monospace"
                                            style="background: rgba(255,255,255,0.08); padding: 1px 4px; border-radius: 4px;">{{ $inv->no_faktur }}</strong>
                                        <div class="text-white-50 ps-1 mt-0.5"
                                            style="font-size: 0.65rem; line-height: 1.4;">
                                            Tgl: {{ \Carbon\Carbon::parse($inv->tanggal)->format('d/m/Y') }} &bull;
                                            LJT: {{ $inv->pelanggan->ljt ?? 30 }} hari <br>
                                            JT: <span class="text-danger fw-bold"
                                                style="color: #fca5a5 !important;">{{ $dueDate->format('d/m/Y') }}</span>
                                            &bull;
                                            Sisa: <strong class="text-white" style="color: #f8fafc !important;">Rp
                                                {{ number_format($sisa, 0, ',', '.') }}</strong>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Search & Add Section -->
        <div class="mobile-card" style="position: relative; z-index: 10;">
            <h6 class="fw-bold mb-2 small">Cari & Tambah Barang</h6>
            <div class="position-relative mb-1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-dark text-secondary border-secondary">
                        <i class="fa-solid fa-box-open"></i>
                    </span>
                    <input type="text" id="product-search-input"
                        class="form-control form-control-sm bg-dark text-white border-secondary"
                        placeholder="Ketik nama atau kode barang...">
                </div>
                <div id="product-search-results" class="list-group position-absolute w-100 shadow-lg mt-1 d-none"
                    style="z-index: 1050; max-height: 220px; overflow-y: auto; background-color: #161e31; border: 1px solid var(--border-color); border-radius: 8px;">
                    <!-- results by js -->
                </div>
            </div>
        </div>

        <!-- Cart Items Container -->
        <h5 class="fw-bold mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">Daftar Belanja (Keranjang)</h5>
        <div id="cart-container">
            <!-- Cart Item Cards will be appended here -->
            <div class="mobile-card text-center py-4" id="empty-cart-message">
                <i class="fa-solid fa-cart-shopping text-secondary mb-2" style="font-size: 2.2rem; opacity: 0.4;"></i>
                <p class="text-secondary mb-0" style="font-size: 0.8rem;">Keranjang kosong. Silakan tambah barang di atas.
                </p>
            </div>
        </div>

        <!-- Summary & Settings Card -->
        <div class="mobile-card mt-3">
            <h6 class="fw-bold mb-2 small">Pembayaran & Keterangan</h6>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label text-secondary small mb-1">Metode Bayar</label>
                    <select name="jenis_transaksi" id="jenis_transaksi"
                        class="form-select form-select-sm bg-dark text-white border-secondary" required>
                        @if (Auth::user()->jenis_sales != '1')
                            <option value="Kredit"
                                {{ $pelanggan && in_array($pelanggan->metode_bayar, ['K', 'Kredit']) ? 'selected' : '' }}>
                                Kredit (Tempo)
                            </option>
                        @endif
                        <option value="Tunai"
                            {{ Auth::user()->jenis_sales == '1' || ($pelanggan && in_array($pelanggan->metode_bayar, ['T', 'Tunai'])) ? 'selected' : '' }}>
                            Tunai (Cash)
                        </option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label text-secondary small mb-1">Potongan Global (Rp)</label>
                    <input type="text" name="diskon_global" id="diskon_global"
                        class="form-control form-control-sm bg-dark text-white border-secondary text-end rupiah-input"
                        value="0" min="0" readonly
                        style="background-color: rgba(255, 255, 255, 0.05) !important;">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small mb-1">Keterangan / Catatan Order</label>
                <input type="text" name="keterangan"
                    class="form-control form-control-sm bg-dark text-white border-secondary"
                    placeholder="Catatan tambahan (opsional)...">
            </div>

            <!-- Calculations Summary -->
            <div class="p-2 rounded border border-secondary mb-3 bg-dark bg-opacity-20" style="font-size: 0.75rem;">
                <div class="d-flex justify-content-between text-secondary mb-1">
                    <span>Subtotal Barang:</span>
                    <span id="summary-subtotal" class="text-white">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between text-secondary mb-1">
                    <span>Total Diskon Strata:</span>
                    <span id="summary-diskon-item" class="text-danger">- Rp 0</span>
                </div>
                <div class="d-flex justify-content-between text-secondary mb-1">
                    <span>Potongan Global:</span>
                    <span id="summary-diskon-global" class="text-danger">- Rp 0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1 border-top border-secondary mt-1">
                    <span class="fw-bold text-white">Grand Total:</span>
                    <span class="fw-bold text-info fs-6" id="summary-grandtotal">Rp 0</span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-sm btn-mobile-primary w-100 py-2 fs-7" id="btn-submit-order" disabled>
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Simpan & Kirim Pesanan
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // In-memory data passing from Laravel
        const diskonStrata = @json($diskonStrata);

        // In-memory cached products structures
        let barangsCache = {};
        let rowIndex = 0;

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
                        let unitQty = Math.floor(Math.round((remaining / factor) * 100000000) / 100000000);
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

        document.addEventListener('DOMContentLoaded', function() {
            const customerSearchInput = document.getElementById('customer-search-input');
            const customerSearchResults = document.getElementById('customer-search-results');
            const customerInfoBox = document.getElementById('customer-info-box');
            const infoSisaLimit = document.getElementById('info-sisa-limit');
            const overdueWarning = document.getElementById('overdue-warning');
            const hiddenKodePelanggan = document.getElementById('kode_pelanggan');

            const productSearchInput = document.getElementById('product-search-input');
            const productSearchResults = document.getElementById('product-search-results');

            const cartContainer = document.getElementById('cart-container');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const btnSubmitOrder = document.getElementById('btn-submit-order');

            const jenisTransaksiEl = document.getElementById('jenis_transaksi');
            const diskonGlobalEl = document.getElementById('diskon_global');

            function parseCleanNumber(val) {
                if (typeof val === 'number') return val;
                if (!val) return 0;
                let clean = val.toString().replace(/\./g, '').replace(/,/g, '.');
                return parseFloat(clean) || 0;
            }

            // Debounce search function helper
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // --- 1. Customer Autocomplete Search ---
            if (customerSearchInput) {
                customerSearchInput.addEventListener('input', debounce(function() {
                    const q = this.value.trim();
                    if (q.length < 2) {
                        customerSearchResults.classList.add('d-none');
                        return;
                    }

                    fetch(`{{ route('pelanggan.search') }}?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(data => {
                            customerSearchResults.innerHTML = '';
                            if (data.length === 0) {
                                customerSearchResults.innerHTML =
                                    '<div class="p-2 text-secondary text-center" style="font-size: 0.75rem;">Pelanggan tidak ditemukan.</div>';
                                customerSearchResults.classList.remove('d-none');
                                return;
                            }

                            data.forEach(item => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className =
                                    'list-group-item list-group-item-action text-white border-0 py-2 px-3 d-flex flex-column';
                                btn.style.backgroundColor = 'transparent';
                                btn.style.borderBottom =
                                    '1px solid rgba(255,255,255,0.05) !important';
                                btn.innerHTML = `
                                <span class="fw-semibold text-white" style="font-size: 0.8rem;">${item.text}</span>
                                <span class="text-secondary mt-1" style="font-size: 0.7rem;">Wilayah: ${item.wilayah || '-'}</span>
                                <span class="text-secondary" style="font-size: 0.7rem;">Alamat: ${item.alamat}</span>
                            `;
                                btn.addEventListener('click', () => {
                                    selectCustomer(item);
                                });
                                customerSearchResults.appendChild(btn);
                            });
                            customerSearchResults.classList.remove('d-none');
                        });
                }, 300));

                document.addEventListener('click', function(e) {
                    if (!customerSearchInput.contains(e.target) && !customerSearchResults.contains(e
                            .target)) {
                        customerSearchResults.classList.add('d-none');
                    }
                });
            }

            function selectCustomer(customer) {
                customerSearchResults.classList.add('d-none');
                customerSearchInput.value = customer.text;
                hiddenKodePelanggan.value = customer.id;

                // Populate Info box
                document.getElementById('detail-nama-display').innerText = customer.nama || '';
                document.getElementById('detail-hp-display').innerHTML =
                    '<i class="fa-solid fa-phone me-1 fs-8"></i> ' + (customer.hp || '-');
                document.getElementById('info-alamat').innerText = customer.alamat || '-';
                document.getElementById('info-wilayah').innerText = customer.wilayah || '-';
                document.getElementById('info-limit-kredit').innerText = 'Rp ' + Number(customer.limit)
                    .toLocaleString('id-ID');
                document.getElementById('info-sisa-limit').innerText = 'Rp ' + Number(customer.sisa_limit)
                    .toLocaleString('id-ID');
                document.getElementById('info-metode-bayar').innerText = customer.metode || '-';

                // Populate overdue invoices list
                const overdueListEl = document.getElementById('overdue-invoices-list');
                if (overdueListEl) {
                    overdueListEl.innerHTML = '';
                    if (customer.overdue_invoices && customer.overdue_invoices.length > 0) {
                        customer.overdue_invoices.forEach(inv => {
                            const li = document.createElement('li');
                            const formattedSisa = Number(inv.sisa).toLocaleString('id-ID');
                            li.innerHTML = `
                                Faktur <strong class="text-white font-monospace">${inv.no_faktur}</strong>
                                <div class="text-white-50 ps-1" style="font-size: 0.65rem;">
                                    Tgl: ${inv.tanggal} &bull; 
                                    LJT: ${inv.ljt} hari &bull; 
                                    JT: <span class="text-danger fw-bold">${inv.due_date}</span>
                                    <br>
                                    Sales: ${inv.sales_name} &bull; 
                                    Sisa: <strong class="text-white">Rp ${formattedSisa}</strong>
                                </div>
                            `;
                            overdueListEl.appendChild(li);
                        });
                    }
                }

                // Set data attributes on input for validation checks
                hiddenKodePelanggan.setAttribute('data-overdue', customer.has_overdue);
                const overdueInvoiceNums = (customer.overdue_invoices || []).map(inv => typeof inv === 'object' ?
                    inv.no_faktur : inv);
                hiddenKodePelanggan.setAttribute('data-overdue-invoices', JSON.stringify(overdueInvoiceNums));
                hiddenKodePelanggan.setAttribute('data-sisa-limit', customer.sisa_limit);
                hiddenKodePelanggan.setAttribute('data-jenis-pelanggan', customer.jenis_pelanggan || '0');

                if (customer.has_overdue === 1) {
                    overdueWarning.classList.remove('d-none');
                } else {
                    overdueWarning.classList.add('d-none');
                }

                // Set default payment mode if matching
                const isRestrictedSales = @json(Auth::user()->jenis_sales == '1');
                if (customer.metode === 'Tunai' || customer.metode === 'T') {
                    jenisTransaksiEl.value = 'Tunai';
                } else if ((customer.metode === 'Kredit' || customer.metode === 'K') && !isRestrictedSales) {
                    jenisTransaksiEl.value = 'Kredit';
                } else if (isRestrictedSales) {
                    jenisTransaksiEl.value = 'Tunai';
                }

                customerInfoBox.classList.remove('d-none');
                validateFormState();
                calculateTotals();
            }

            // --- 2. Product Autocomplete Search ---
            productSearchInput.addEventListener('input', debounce(function() {
                const q = this.value.trim();
                if (q.length < 2) {
                    productSearchResults.classList.add('d-none');
                    return;
                }

                fetch(`{{ route('barang.search') }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        productSearchResults.innerHTML = '';
                        if (data.length === 0) {
                            productSearchResults.innerHTML =
                                '<div class="p-2 text-secondary text-center" style="font-size: 0.75rem;">Barang tidak ditemukan.</div>';
                            productSearchResults.classList.remove('d-none');
                            return;
                        }

                        data.forEach(item => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className =
                                'list-group-item list-group-item-action text-white border-0 py-2 px-3 d-flex flex-column';
                            btn.style.backgroundColor = 'transparent';
                            btn.style.borderBottom =
                                '1px solid rgba(255,255,255,0.05) !important';
                            btn.innerHTML = `
                            <span class="fw-semibold text-white" style="font-size: 0.8rem;">${item.text}</span>
                            <span class="text-secondary mt-1" style="font-size: 0.7rem;">Kode: ${item.kode_barang} | Merk: ${item.merk || '-'}</span>
                        `;
                            btn.addEventListener('click', () => {
                                addProductToCart(item);
                            });
                            productSearchResults.appendChild(btn);
                        });
                        productSearchResults.classList.remove('d-none');
                    });
            }, 300));

            document.addEventListener('click', function(e) {
                if (!productSearchInput.contains(e.target) && !productSearchResults.contains(e.target)) {
                    productSearchResults.classList.add('d-none');
                }
            });

            // --- 3. Cart Management ---
            function checkStockLimit(card, suppressAlert = false) {
                const code = card.getAttribute('data-code');
                const product = barangsCache[code];
                if (!product) return true;

                const qtyInput = card.querySelector('.input-qty');
                const selectSatuan = card.querySelector('.select-satuan');
                const selectedOpt = selectSatuan.options[selectSatuan.selectedIndex];

                const qty = parseFloat(qtyInput.value) || 0;
                const isi = parseFloat(selectedOpt.getAttribute('data-isi')) || 1;
                const qtySmallest = qty * isi;

                if (qtySmallest > product.stok) {
                    const formattedStok = formatStokJS(product.stok, product.satuans);
                    if (!suppressAlert) {
                        Swal.fire({
                            title: 'Stok Tidak Mencukupi',
                            html: `Stok barang <b>${product.nama_barang}</b> tidak mencukupi!<br><br>` +
                                `Stok tersedia: <b>${formattedStok}</b><br>` +
                                `Jumlah diinput: <b>${qty} ${selectedOpt.getAttribute('data-name')}</b> (Setara ${qtySmallest} PCS)`,
                            icon: 'error',
                            background: '#161e31',
                            color: '#f8fafc',
                            confirmButtonColor: '#6366f1'
                        });
                    }

                    // Clamp quantity
                    const maxQtyInUnit = Math.floor(product.stok / isi);
                    qtyInput.value = maxQtyInUnit;

                    if (maxQtyInUnit === 0 && product.stok > 0 && !suppressAlert) {
                        Swal.fire({
                            title: 'Satuan Terlalu Besar',
                            html: `Stok barang <b>${product.nama_barang}</b> hanya tersisa <b>${formattedStok}</b>.<br><br>` +
                                `Unit <b>${selectedOpt.getAttribute('data-name')}</b> (Isi ${isi}) terlalu besar. Silakan pilih satuan yang lebih kecil.`,
                            icon: 'warning',
                            background: '#161e31',
                            color: '#f8fafc',
                            confirmButtonColor: '#6366f1'
                        });
                    }
                    return false;
                }
                return true;
            }

            function addProductToCart(product, savedValues = null) {
                productSearchResults.classList.add('d-none');
                productSearchInput.value = '';

                if (product.stok <= 0 && !savedValues) {
                    Swal.fire({
                        title: 'Stok Habis',
                        text: `Barang "${product.nama_barang}" tidak dapat ditambahkan karena stok habis.`,
                        icon: 'error',
                        background: '#161e31',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                    return;
                }

                // Register in Cache
                barangsCache[product.kode_barang] = {
                    kode_barang: product.kode_barang,
                    nama_barang: product.nama_barang,
                    kategori: product.kategori,
                    merk: product.merk,
                    kode_supplier: product.kode_supplier,
                    stok: product.stok,
                    satuans: product.satuans
                };

                // Check if product already exists in cart, if so, just increment qty
                const existingCard = document.querySelector(`.cart-item-card[data-code="${product.kode_barang}"]`);
                if (existingCard && !savedValues) {
                    const qtyInput = existingCard.querySelector('.input-qty');
                    const oldVal = parseFloat(qtyInput.value) || 0;
                    qtyInput.value = oldVal + 1;

                    if (!checkStockLimit(existingCard)) {
                        calculateTotals();
                        return;
                    }

                    qtyInput.dispatchEvent(new Event('change'));
                    calculateTotals();
                    return;
                }

                // Create custom card item
                const card = document.createElement('div');
                card.className =
                    'card-item bg-dark bg-opacity-40 border border-secondary border-opacity-30 rounded-4 p-3 mb-2 cart-item-card';
                card.setAttribute('data-code', product.kode_barang);
                card.setAttribute('id', `row_${rowIndex}`);

                // Build units option: sorted by capacity (already sorted descending from backend)
                let unitOptions = '';
                let selectedSatuanId = null;
                if (savedValues) {
                    selectedSatuanId = parseInt(savedValues.satuan_id);
                } else if (product.satuans && product.satuans.length > 0) {
                    // Sort unit by capacity descending to find largest and smallest
                    const sortedSatuans = [...product.satuans].sort((a, b) => b.isi - a.isi);
                    const largestUnit = sortedSatuans[0];
                    const smallestUnit = sortedSatuans[sortedSatuans.length - 1];

                    const largestIsi = parseFloat(largestUnit.isi) || 1.0;
                    if (product.stok >= largestIsi) {
                        selectedSatuanId = largestUnit.id;
                    } else {
                        selectedSatuanId = smallestUnit.id;
                    }
                }

                product.satuans.forEach((sat, i) => {
                    const isSelected = selectedSatuanId ? (sat.id === selectedSatuanId) : (i === 0);
                    unitOptions +=
                        `<option value="${sat.id}" data-name="${sat.satuan}" data-harga="${sat.harga_jual}" data-isi="${sat.isi}" ${isSelected ? 'selected' : ''}>${sat.satuan} (${sat.isi})</option>`;
                });

                // Get initial default price
                let defaultPrice = 0;
                if (savedValues) {
                    defaultPrice = savedValues.harga;
                } else if (product.satuans.length > 0) {
                    const selSat = product.satuans.find(s => s.id === selectedSatuanId) || product.satuans[0];
                    defaultPrice = selSat.harga_jual;
                }

                const initialQty = savedValues ? savedValues.qty : 1;
                const initialD1 = savedValues ? savedValues.diskon1 : 0;
                const initialD2 = savedValues ? savedValues.diskon2 : 0;
                const initialD3 = savedValues ? savedValues.diskon3 : 0;

                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div style="flex: 1; min-width: 0; padding-right: 8px;">
                            <h6 class="fw-bold text-white mb-0 text-truncate" style="font-size: 0.85rem;" title="${product.nama_barang}">${product.nama_barang}</h6>
                            <div class="d-flex gap-1.5 align-items-center mt-1 flex-wrap">
                                <span class="badge bg-secondary bg-opacity-35 text-white-50" style="font-size: 0.6rem; letter-spacing: 0.3px;">${product.kode_barang}</span>
                                ${product.merk ? `<span class="badge bg-dark text-secondary border border-secondary" style="font-size: 0.6rem; border-color: rgba(255,255,255,0.15) !important;">${product.merk}</span>` : ''}
                            </div>
                            <input type="hidden" name="items[${rowIndex}][kode_barang]" value="${product.kode_barang}">
                        </div>
                        <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center btn-remove-item" style="width: 26px; height: 26px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: none; transition: all 0.2s;">
                            <i class="fa-solid fa-trash-can" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-4">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Satuan</label>
                            <select name="items[${rowIndex}][satuan_id]" class="form-select form-select-sm bg-dark text-white border-secondary select-satuan" style="font-size: 0.75rem; border-radius: 8px; height: 32px; padding: 2px 8px;">
                                ${unitOptions}
                            </select>
                            <input type="hidden" name="items[${rowIndex}][satuan]" class="hidden-satuan-name" value="${product.satuans.length > 0 ? product.satuans[0].satuan : ''}">
                        </div>
                        <div class="col-4">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Qty</label>
                            <div class="input-group input-group-sm" style="height: 32px;">
                                <button type="button" class="btn btn-outline-secondary btn-qty-minus text-white px-2 d-flex align-items-center justify-content-center" style="border-radius: 8px 0 0 8px; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); width: 28px; height: 100%;">-</button>
                                <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm bg-dark text-white border-secondary text-center input-qty px-1" min="0.01" step="any" value="${initialQty}" required style="font-size: 0.75rem; border-color: rgba(255,255,255,0.15); height: 100%;">
                                <button type="button" class="btn btn-outline-secondary btn-qty-plus text-white px-2 d-flex align-items-center justify-content-center" style="border-radius: 0 8px 8px 0; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); width: 28px; height: 100%;">+</button>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Harga</label>
                            <div class="fw-semibold text-white-50 price-display" style="font-size: 0.75rem; line-height: 32px;">
                                Rp ${parseFloat(defaultPrice).toLocaleString('id-ID')}
                            </div>
                            <input type="hidden" name="items[${rowIndex}][harga]" class="input-harga" value="${defaultPrice}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-15">
                        <div class="diskon-tags-display d-flex align-items-center flex-wrap" style="gap: 4px;">
                            <!-- populated by JS -->
                        </div>
                        <div class="text-end">
                            <span class="text-secondary me-1" style="font-size: 0.65rem;">Nett:</span>
                            <span class="fw-bold text-info row-subtotal-display" style="font-size: 0.85rem;">Rp 0</span>
                        </div>
                    </div>

                    <input type="hidden" name="items[${rowIndex}][diskon1_persen]" class="input-diskon1" value="${initialD1}">
                    <input type="hidden" name="items[${rowIndex}][diskon2_persen]" class="input-diskon2" value="${initialD2}">
                    <input type="hidden" name="items[${rowIndex}][diskon3_persen]" class="input-diskon3" value="${initialD3}">
                `;

                // Hide empty cart msg
                emptyCartMessage.classList.add('d-none');
                if (savedValues) {
                    cartContainer.appendChild(card);
                } else {
                    cartContainer.prepend(card);
                }

                // Bind Event Listeners on inputs
                const selectSatuan = card.querySelector('.select-satuan');
                const hiddenSatuanName = card.querySelector('.hidden-satuan-name');
                const inputHarga = card.querySelector('.input-harga');
                const btnRemove = card.querySelector('.btn-remove-item');
                const inputQty = card.querySelector('.input-qty');
                const inputDis1 = card.querySelector('.input-diskon1');
                const inputDis2 = card.querySelector('.input-diskon2');
                const inputDis3 = card.querySelector('.input-diskon3');
                const btnQtyMinus = card.querySelector('.btn-qty-minus');
                const btnQtyPlus = card.querySelector('.btn-qty-plus');

                // Set initial hidden unit name
                const selectedOpt = selectSatuan.options[selectSatuan.selectedIndex];
                if (selectedOpt) {
                    hiddenSatuanName.value = selectedOpt.getAttribute('data-name');
                }

                selectSatuan.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    hiddenSatuanName.value = opt.getAttribute('data-name');
                    inputHarga.value = opt.getAttribute('data-harga');

                    const priceDisplay = card.querySelector('.price-display');
                    if (priceDisplay) {
                        priceDisplay.innerText = 'Rp ' + parseFloat(opt.getAttribute('data-harga') || 0)
                            .toLocaleString('id-ID');
                    }

                    checkStockLimit(card);
                    calculateTotals();
                });

                btnRemove.addEventListener('click', function() {
                    card.remove();
                    if (cartContainer.querySelectorAll('.cart-item-card').length === 0) {
                        emptyCartMessage.classList.remove('d-none');
                    }
                    calculateTotals();
                    validateFormState();
                });

                [inputHarga, inputDis1, inputDis2, inputDis3].forEach(input => {
                    input.addEventListener('input', calculateTotals);
                });

                inputQty.addEventListener('input', function() {
                    checkStockLimit(card);
                    calculateTotals();
                });

                inputQty.addEventListener('change', function() {
                    checkStockLimit(card);
                    calculateTotals();
                });

                btnQtyMinus.addEventListener('click', function() {
                    let val = parseFloat(inputQty.value) || 0;
                    if (val > 0.01) {
                        let newVal = val - 1;
                        if (newVal < 0.01) newVal = 0.01;
                        inputQty.value = parseFloat(newVal.toFixed(2));
                        inputQty.dispatchEvent(new Event('input'));
                        inputQty.dispatchEvent(new Event('change'));
                    }
                });

                btnQtyPlus.addEventListener('click', function() {
                    let val = parseFloat(inputQty.value) || 0;
                    let newVal = val + 1;
                    inputQty.value = parseFloat(newVal.toFixed(2));
                    inputQty.dispatchEvent(new Event('input'));
                    inputQty.dispatchEvent(new Event('change'));
                });

                rowIndex++;
                validateFormState();

                // Trigger check on addition
                checkStockLimit(card, !!savedValues);
                calculateTotals();
            }

            jenisTransaksiEl.addEventListener('change', calculateTotals);
            diskonGlobalEl.addEventListener('input', calculateTotals);

            // --- 4. Strata Discount & Totals Calculation ---
            function calculateStrataDiscounts() {
                const jenisTransaksi = jenisTransaksiEl.value; // 'Tunai' or 'Kredit'

                // Group subtotal by supplier code
                const supplierSubtotals = {};
                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    const barangCode = card.getAttribute('data-code');
                    const qty = parseFloat(card.querySelector('.input-qty').value) || 0;
                    const harga = parseCleanNumber(card.querySelector('.input-harga').value) || 0;
                    const sub = qty * harga;

                    const b = barangsCache[barangCode];
                    if (b && b.kode_supplier) {
                        supplierSubtotals[b.kode_supplier] = (supplierSubtotals[b.kode_supplier] || 0) +
                            sub;
                    }
                });

                // Evaluate rules
                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    const barangCode = card.getAttribute('data-code');
                    const qty = parseFloat(card.querySelector('.input-qty').value) || 0;
                    const harga = parseCleanNumber(card.querySelector('.input-harga').value) || 0;
                    const sub = qty * harga;
                    const selectSatuan = card.querySelector('.select-satuan');
                    const satuanId = selectSatuan ? selectSatuan.value : null;

                    const b = barangsCache[barangCode];
                    if (!b) return;

                    let bestRate = 0;
                    let bestRule = null;
                    let bestDetail = null;

                    const findRule = (tipe) => {
                        return diskonStrata.filter(r => r.tipe === tipe && r.is_active);
                    };

                    const isSatuanMatch = (d, rowSatuanId) => {
                        if (d.satuan_id === null || !d.satuan_id) return true;
                        if (d.satuan_id == rowSatuanId) return true;

                        const ruleSatuanName = d.satuan && d.satuan.satuan ? d.satuan.satuan
                            .toUpperCase().trim() : '';
                        let rowSatuanName = '';
                        if (b && b.satuans) {
                            const found = b.satuans.find(s => s.id == rowSatuanId);
                            if (found) {
                                rowSatuanName = (found.satuan || '').toUpperCase().trim();
                            }
                        }
                        return ruleSatuanName !== '' && rowSatuanName !== '' && ruleSatuanName ===
                            rowSatuanName;
                    };

                    const checkRule = (r, d) => {
                        const rate = parseFloat(d.dis1) || 0;
                        if (rate >= bestRate) {
                            bestRule = r;
                            bestDetail = d;
                            bestRate = rate;
                        }
                    };

                    // Priority 1: Per Barang
                    const rulesBarang = findRule('barang');
                    rulesBarang.forEach(r => {
                        if (r.barangs && r.barangs.some(item => item.kode_barang === barangCode)) {
                            r.details.forEach(d => {
                                if (qty >= (d.min_qty || 0) && (d.max_qty === null || qty <=
                                        d.max_qty) && isSatuanMatch(d, satuanId)) {
                                    checkRule(r, d);
                                }
                            });
                        }
                    });

                    // Priority 2: Per Beberapa Barang
                    if (!bestRule) {
                        const rulesBeberapa = findRule('beberapa_barang');
                        rulesBeberapa.forEach(r => {
                            if (r.barangs && r.barangs.some(item => item.kode_barang ===
                                    barangCode)) {
                                r.details.forEach(d => {
                                    if (qty >= (d.min_qty || 0) && (d.max_qty === null ||
                                            qty <= d.max_qty) && isSatuanMatch(d,
                                            satuanId)) {
                                        checkRule(r, d);
                                    }
                                });
                            }
                        });
                    }

                    // Priority 3: Per Kategori
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

                    // Priority 4: Per Merk
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

                    // Priority 5: Per Supplier
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
                    const inputDis1 = card.querySelector('.input-diskon1');
                    const inputDis2 = card.querySelector('.input-diskon2');
                    if (bestRule && bestDetail) {
                        let d1_pct = 0;
                        let d2_pct = 0;

                        const rawDis1 = parseFloat(bestDetail.dis1) || 0;
                        const rawDis2 = parseFloat(bestDetail.dis2) || 0;

                        if (bestDetail.tipe_nilai === 'persen') {
                            d1_pct = rawDis1;
                            d2_pct = rawDis2;
                        } else {
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

                        // Apply Diskon 1
                        inputDis1.value = d1_pct.toFixed(2);
                        inputDis1.setAttribute('readonly', 'true');
                        inputDis1.style.backgroundColor = 'rgba(255,255,255,0.05)';

                        // Apply Diskon 2 only if Tunai
                        if (jenisTransaksi === 'Tunai') {
                            inputDis2.value = d2_pct.toFixed(2);
                            inputDis2.setAttribute('readonly', 'true');
                            inputDis2.style.backgroundColor = 'rgba(255,255,255,0.05)';
                        } else {
                            inputDis2.value = '0';
                            inputDis2.setAttribute('readonly', 'true');
                            inputDis2.style.backgroundColor = 'rgba(255,255,255,0.05)';
                        }
                    } else {
                        inputDis1.value = '0';
                        inputDis2.value = '0';
                    }
                });
            }

            function calculateTotals() {
                // First run strata calculations
                calculateStrataDiscounts();

                let subtotalSum = 0;
                let totalDiskon = 0;

                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    const qty = parseFloat(card.querySelector('.input-qty').value) || 0;
                    const harga = parseCleanNumber(card.querySelector('.input-harga').value) || 0;
                    const sub = qty * harga;

                    const d1_pct = parseFloat(card.querySelector('.input-diskon1').value) || 0;
                    const d2_pct = parseFloat(card.querySelector('.input-diskon2').value) || 0;
                    const d3_pct = parseFloat(card.querySelector('.input-diskon3').value) || 0;

                    const d1 = sub * (d1_pct / 100);
                    const d2 = (sub - d1) * (d2_pct / 100);
                    const d3 = (sub - d1 - d2) * (d3_pct / 100);
                    const diskon = Math.round(d1 + d2 + d3);

                    subtotalSum += sub;
                    totalDiskon += diskon;

                    const nett = sub - diskon;
                    card.querySelector('.row-subtotal-display').innerText = 'Rp ' + nett.toLocaleString(
                        'id-ID');

                    const diskonDisplay = card.querySelector('.diskon-tags-display');
                    if (diskonDisplay) {
                        let badgesHTML = '';
                        if (d1_pct > 0) {
                            badgesHTML +=
                                `<span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30" style="font-size: 0.6rem; font-weight: 600; padding: 2px 6px; border-radius: 4px;">D1: ${d1_pct.toFixed(1)}%</span>`;
                        }
                        if (d2_pct > 0) {
                            badgesHTML +=
                                `<span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 ms-1" style="font-size: 0.6rem; font-weight: 600; padding: 2px 6px; border-radius: 4px;">D2: ${d2_pct.toFixed(1)}%</span>`;
                        }
                        if (d3_pct > 0) {
                            badgesHTML +=
                                `<span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 ms-1" style="font-size: 0.6rem; font-weight: 600; padding: 2px 6px; border-radius: 4px;">D3: ${d3_pct.toFixed(1)}%</span>`;
                        }
                        diskonDisplay.innerHTML = badgesHTML ||
                            '<span class="text-secondary" style="font-size: 0.6rem;">Tanpa Diskon</span>';
                    }
                });

                const diskonGlobal = parseCleanNumber(diskonGlobalEl.value) || 0;
                const grandTotal = subtotalSum - totalDiskon - diskonGlobal;

                document.getElementById('summary-subtotal').innerText = 'Rp ' + subtotalSum.toLocaleString('id-ID');
                document.getElementById('summary-diskon-item').innerText = '- Rp ' + totalDiskon.toLocaleString(
                    'id-ID');
                document.getElementById('summary-diskon-global').innerText = '- Rp ' + diskonGlobal.toLocaleString(
                    'id-ID');
                document.getElementById('summary-grandtotal').innerText = 'Rp ' + grandTotal.toLocaleString(
                    'id-ID');

                // Save temporary total for validation
                btnSubmitOrder.setAttribute('data-grand-total', grandTotal);

                // Save cart to storage
                saveCartToStorage();
            }

            // --- 5. Form Validation & Guards ---
            function validateFormState() {
                const hasCustomer = hiddenKodePelanggan.value !== '';
                const hasItems = cartContainer.querySelectorAll('.cart-item-card').length > 0;

                if (hasCustomer && hasItems) {
                    btnSubmitOrder.removeAttribute('disabled');
                } else {
                    btnSubmitOrder.setAttribute('disabled', 'true');
                }
            }

            document.getElementById('order-form').addEventListener('submit', function(e) {
                const overdueStatus = parseInt(hiddenKodePelanggan.getAttribute('data-overdue')) || 0;

                // 1. Double check Overdue blocker
                if (overdueStatus === 1) {
                    e.preventDefault();
                    let overdueList = [];
                    try {
                        overdueList = JSON.parse(hiddenKodePelanggan.getAttribute(
                            'data-overdue-invoices') || '[]');
                    } catch (e) {}
                    const overdueListStr = overdueList.length > 0 ? overdueList.join(', ') : '-';
                    const customerName = document.getElementById('detail-nama-display')?.innerText ||
                        'Pelanggan';

                    Swal.fire({
                        title: 'Transaksi Ditolak',
                        html: `Pelanggan <strong>${customerName}</strong> memiliki tagihan jatuh tempo (Overdue)!<br><br>Faktur overdue: <strong>${overdueListStr}</strong>.<br><br>Harap selesaikan pembayaran terlebih dahulu.`,
                        icon: 'error',
                        background: '#161e31',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                    return false;
                }

                // 2. Double check credit limit
                const paymentMode = jenisTransaksiEl.value;
                if (paymentMode === 'Kredit' || paymentMode === 'Tunai') {
                    const jenisPelanggan = hiddenKodePelanggan.getAttribute('data-jenis-pelanggan') || '0';
                    if (jenisPelanggan === '0') {
                        const sisaLimit = parseFloat(hiddenKodePelanggan.getAttribute('data-sisa-limit')) ||
                            0;
                        const grandTotal = parseFloat(btnSubmitOrder.getAttribute('data-grand-total')) || 0;

                        if (grandTotal > sisaLimit) {
                            e.preventDefault();
                            Swal.fire({
                                title: 'Limit Kredit Terlampaui',
                                text: `Total order (Rp ${grandTotal.toLocaleString('id-ID')}) melebihi sisa limit kredit pelanggan (Rp ${sisaLimit.toLocaleString('id-ID')})!`,
                                icon: 'error',
                                background: '#161e31',
                                color: '#f8fafc',
                                confirmButtonColor: '#6366f1'
                            });
                            return false;
                        }
                    }
                }

                // 3. Double check stock limits
                let stockOk = true;
                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    if (!checkStockLimit(card)) {
                        stockOk = false;
                    }
                });
                if (!stockOk) {
                    e.preventDefault();
                    return false;
                }
            });

            // Initialize state if pre-populated customer exists
            @if ($pelanggan)
                @php
                    $overdueInvoicesData = [];
                    if ($pelanggan->hasOverdueInvoices()) {
                        foreach ($pelanggan->getOverdueInvoices() as $inv) {
                            $sisa = $inv->grand_total - $inv->getApprovedPembayaranTotal() - $inv->getTotalRetur();
                            $dueDate = \Carbon\Carbon::parse($inv->tanggal)->addDays($pelanggan->ljt ?? 30);
                            $overdueInvoicesData[] = [
                                'no_faktur' => $inv->no_faktur,
                                'tanggal' => \Carbon\Carbon::parse($inv->tanggal)->format('d/m/Y'),
                                'ljt' => $pelanggan->ljt ?? 30,
                                'due_date' => $dueDate->format('d/m/Y'),
                                'sales_name' => $inv->sales->name ?? $inv->kode_sales,
                                'sisa' => $sisa,
                            ];
                        }
                    }
                @endphp
                const mockCustomer = {
                    id: "{{ $pelanggan->kode_pelanggan }}",
                    text: "{{ $pelanggan->nama_pelanggan }}",
                    has_overdue: {{ $pelanggan->hasOverdueInvoices() ? 1 : 0 }},
                    overdue_invoices: @json($overdueInvoicesData),
                    sisa_limit: {{ $pelanggan->getSisaLimitKredit() }},
                    metode: "{{ $pelanggan->metode_bayar }}",
                    jenis_pelanggan: "{{ $pelanggan->jenis_pelanggan ?: '0' }}"
                };
                hiddenKodePelanggan.value = mockCustomer.id;
                hiddenKodePelanggan.setAttribute('data-overdue', mockCustomer.has_overdue);
                const overdueInvoiceNums = (mockCustomer.overdue_invoices || []).map(inv => typeof inv ===
                    'object' ? inv.no_faktur : inv);
                hiddenKodePelanggan.setAttribute('data-overdue-invoices', JSON.stringify(overdueInvoiceNums));
                hiddenKodePelanggan.setAttribute('data-sisa-limit', mockCustomer.sisa_limit);
                hiddenKodePelanggan.setAttribute('data-jenis-pelanggan', mockCustomer.jenis_pelanggan);

                // Set default payment mode
                const isRestrictedSales = @json(Auth::user()->jenis_sales == '1');
                if (mockCustomer.metode === 'Tunai' || mockCustomer.metode === 'T') {
                    jenisTransaksiEl.value = 'Tunai';
                } else if ((mockCustomer.metode === 'Kredit' || mockCustomer.metode === 'K') && !
                    isRestrictedSales) {
                    jenisTransaksiEl.value = 'Kredit';
                } else if (isRestrictedSales) {
                    jenisTransaksiEl.value = 'Tunai';
                }
            @endif

            // --- 6. LocalStorage Cart Persistence ---
            let isRestoringCart = false;

            function saveCartToStorage() {
                if (isRestoringCart) return;

                const items = [];
                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    const code = card.getAttribute('data-code');
                    const qty = card.querySelector('.input-qty').value;
                    const satuanId = card.querySelector('.select-satuan').value;
                    const harga = card.querySelector('.input-harga').value;
                    const diskon1 = card.querySelector('.input-diskon1').value;
                    const diskon2 = card.querySelector('.input-diskon2').value;
                    const diskon3 = card.querySelector('.input-diskon3').value;
                    const product = barangsCache[code];

                    items.push({
                        code: code,
                        qty: qty,
                        satuan_id: satuanId,
                        harga: harga,
                        diskon1: diskon1,
                        diskon2: diskon2,
                        diskon3: diskon3,
                        product: product
                    });
                });

                const cartData = {
                    kode_pelanggan: hiddenKodePelanggan.value,
                    jenis_transaksi: jenisTransaksiEl.value,
                    keterangan: document.querySelector('input[name="keterangan"]')?.value || '',
                    items: items
                };

                localStorage.setItem('mobile_order_cart_' + '{{ Auth::user()->nik }}', JSON.stringify(cartData));
            }

            function loadCartFromStorage() {
                try {
                    const dataStr = localStorage.getItem('mobile_order_cart_' + '{{ Auth::user()->nik }}');
                    if (!dataStr) return;
                    const data = JSON.parse(dataStr);
                    if (!data || !data.items || data.items.length === 0) return;

                    const lockedKode = "{{ $pelanggan ? $pelanggan->kode_pelanggan : '' }}";
                    const dataKode = (data.kode_pelanggan || '').trim().toLowerCase();
                    const currentLockedKode = (lockedKode || '').trim().toLowerCase();

                    if (currentLockedKode && dataKode !== currentLockedKode) {
                        // Locked customer changed (different check-in), discard cart
                        localStorage.removeItem('mobile_order_cart_' + '{{ Auth::user()->nik }}');
                        return;
                    }

                    isRestoringCart = true;

                    data.items.forEach(item => {
                        if (item.product) {
                            addProductToCart(item.product, item);
                        }
                    });

                    if (jenisTransaksiEl) {
                        jenisTransaksiEl.value = data.jenis_transaksi || 'Tunai';
                    }
                    const keteranganEl = document.querySelector('input[name="keterangan"]');
                    if (keteranganEl) {
                        keteranganEl.value = data.keterangan || '';
                    }

                    isRestoringCart = false;

                    calculateTotals();
                    validateFormState();
                } catch (e) {
                    console.error("Failed to load cart from storage", e);
                    isRestoringCart = false;
                }
            }

            const keteranganEl = document.querySelector('input[name="keterangan"]');
            if (keteranganEl) {
                keteranganEl.addEventListener('input', saveCartToStorage);
            }

            // Load saved cart from localStorage if present
            loadCartFromStorage();

            validateFormState();
        });
    </script>
@endpush
