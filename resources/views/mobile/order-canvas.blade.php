@extends('layouts.mobile')

@section('title', 'Order Canvas')

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

        .canvas-badge {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 8px;
            padding: 6px 12px;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Order Penjualan Canvas</h5>
            <span class="canvas-badge">
                <i class="fa-solid fa-truck-moving me-1" style="color: #6366f1; font-size: 0.7rem;"></i>
                <span style="color: #a5b4fc; font-size: 0.7rem; font-weight: 600;">KANVAS</span>
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 py-2 px-3 mb-3 small"
            style="background-color: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171;">
            <strong class="d-block mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> Gagal menyimpan
                pesanan:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mobile.order.canvas.store') }}" method="POST" id="order-form">
        @csrf

        <!-- Header Info -->
        <div class="mobile-card">
            <div class="mb-2">
                <label class="form-label text-secondary small mb-1">Nomor Faktur</label>
                <input type="text" name="no_faktur"
                    class="form-control form-control-sm font-monospace bg-dark text-white border-secondary"
                    value="{{ $noFaktur }}" readonly style="background-color: rgba(255,255,255,0.03) !important;">
            </div>

            <div class="mb-2">
                <label class="form-label text-secondary small mb-1">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal"
                    class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}"
                    required>
            </div>

            <!-- Customer Selection (Locked) -->
            <div class="mb-3">
                <label class="form-label text-secondary small mb-1">Pelanggan Aktif</label>
                <div class="p-2 rounded border border-primary bg-dark bg-opacity-50">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-white small" id="selected-customer-name">
                            {{ $pelanggan->nama_pelanggan }}
                        </span>
                        <span class="badge bg-secondary btn-sm" style="font-size: 0.65rem;">Terkunci</span>
                    </div>
                    <div class="text-secondary mt-1" style="font-size: 0.7rem;">Kode: {{ $pelanggan->kode_pelanggan }}</div>
                </div>
                <input type="hidden" name="kode_pelanggan" id="kode_pelanggan" value="{{ $pelanggan->kode_pelanggan }}"
                    data-overdue="{{ $pelanggan->hasOverdueInvoices() ? 1 : 0 }}"
                    data-sisa-limit="{{ $pelanggan->getSisaLimitKredit() }}">
                <input type="hidden" name="is_new_pelanggan" value="0">
            </div>

            <!-- Customer Info Box -->
            <div class="p-2 rounded border border-secondary mt-2 bg-dark bg-opacity-20" style="font-size: 0.75rem;">
                <div class="mb-2 pb-1 border-bottom border-secondary border-opacity-20">
                    <span class="text-secondary d-block mb-1"
                        style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Detail
                        Pelanggan</span>
                    <h6 class="fw-bold text-white mb-0" id="info-nama-pelanggan">{{ $pelanggan->nama_pelanggan }}</h6>
                    <div class="text-secondary small mt-1">
                        <i class="fa-solid fa-phone me-1 fs-8"></i><span
                            id="info-hp-pelanggan">{{ $pelanggan->no_hp_pelanggan ?? '-' }}</span>
                    </div>
                </div>
                <div class="row g-2 mb-1">
                    <div class="col-4 text-secondary">Alamat:</div>
                    <div class="col-8 text-end text-white" id="info-alamat-pelanggan" style="word-break: break-word;">
                        {{ $pelanggan->alamat_pelanggan ?? '-' }}</div>
                </div>
                <div class="row g-2">
                    <div class="col-5 text-secondary">Metode Bayar:</div>
                    <div class="col-7 text-end fw-semibold text-info" id="info-metode-bayar">
                        {{ $pelanggan->metode_bayar ?? '-' }}</div>
                </div>

                <div id="info-overdue-container"></div>
            </div>
        </div>

        <!-- Product Search -->
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
                </div>
            </div>
        </div>

        <!-- Cart -->
        <h5 class="fw-bold mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">Daftar Belanja (Keranjang)</h5>
        <div id="cart-container">
            <div class="mobile-card text-center py-4" id="empty-cart-message">
                <i class="fa-solid fa-cart-shopping text-secondary mb-2" style="font-size: 2.2rem; opacity: 0.4;"></i>
                <p class="text-secondary mb-0" style="font-size: 0.8rem;">Keranjang kosong. Tambah barang di atas.</p>
            </div>
        </div>

        <!-- Summary & Payment -->
        <div class="mobile-card mt-3">
            <h6 class="fw-bold mb-2 small">Pembayaran & Keterangan</h6>

            <div class="row g-2 mb-3">
                <div class="col-12">
                    <label class="form-label text-secondary small mb-1">Metode Bayar</label>
                    <select name="jenis_transaksi" id="jenis_transaksi"
                        class="form-select form-select-sm bg-dark text-white border-secondary" required>
                        <option value="Tunai" selected>Tunai (Cash)</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-secondary small mb-1">Keterangan / Catatan</label>
                <input type="text" name="keterangan"
                    class="form-control form-control-sm bg-dark text-white border-secondary"
                    placeholder="Catatan tambahan (opsional)...">
            </div>

            <!-- Summary -->
            <div class="p-2 rounded border border-secondary mb-3 bg-dark bg-opacity-20" style="font-size: 0.75rem;">
                <div class="d-flex justify-content-between text-secondary mb-1">
                    <span>Subtotal Barang:</span>
                    <span id="summary-subtotal" class="text-white">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between text-secondary mb-1">
                    <span>Total Diskon Strata:</span>
                    <span id="summary-diskon-item" class="text-danger">- Rp 0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1 border-top border-secondary mt-1">
                    <span class="fw-bold text-white">Grand Total:</span>
                    <span class="fw-bold text-info fs-6" id="summary-grandtotal">Rp 0</span>
                </div>
            </div>

            <button type="submit" class="btn btn-sm btn-mobile-primary w-100 py-2 fs-7" id="btn-submit-order" disabled>
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Simpan Order Canvas
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const diskonStrata = @json($diskonStrata);
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
                        if (unitQty > 0) breakdowns.push(`${unitQty} ${sat.satuan}`);
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

        function parseCleanNumber(val) {
            if (typeof val === 'number') return val;
            if (!val) return 0;
            return parseFloat(val.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const hiddenKodePelanggan = document.getElementById('kode_pelanggan');
            const infoNama = document.getElementById('info-nama-pelanggan');



            const productSearchInput = document.getElementById('product-search-input');
            const productSearchResults = document.getElementById('product-search-results');
            const cartContainer = document.getElementById('cart-container');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const btnSubmitOrder = document.getElementById('btn-submit-order');
            const jenisTransaksiEl = document.getElementById('jenis_transaksi');

            // --- Product Search ---
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
                            btn.addEventListener('click', () => addProductToCart(item));
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

            // --- Cart Management ---
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
                            html: `Stok barang <b>${product.nama_barang}</b> tidak mencukupi!<br><br>Stok tersedia: <b>${formattedStok}</b><br>Jumlah diinput: <b>${qty} ${selectedOpt.getAttribute('data-name')}</b>`,
                            icon: 'error',
                            background: '#161e31',
                            color: '#f8fafc',
                            confirmButtonColor: '#6366f1'
                        });
                    }
                    const maxQtyInUnit = Math.floor(product.stok / isi);
                    qtyInput.value = maxQtyInUnit;
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

                barangsCache[product.kode_barang] = {
                    kode_barang: product.kode_barang,
                    nama_barang: product.nama_barang,
                    kategori: product.kategori,
                    merk: product.merk,
                    kode_supplier: product.kode_supplier,
                    stok: product.stok,
                    satuans: product.satuans
                };

                const existingCard = document.querySelector(`.cart-item-card[data-code="${product.kode_barang}"]`);
                if (existingCard && !savedValues) {
                    const qtyInput = existingCard.querySelector('.input-qty');
                    qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
                    if (!checkStockLimit(existingCard)) {
                        calculateTotals();
                        return;
                    }
                    qtyInput.dispatchEvent(new Event('change'));
                    calculateTotals();
                    return;
                }

                const card = document.createElement('div');
                card.className =
                    'card-item bg-dark bg-opacity-40 border border-secondary border-opacity-30 rounded-4 p-3 mb-2 cart-item-card';
                card.setAttribute('data-code', product.kode_barang);
                card.setAttribute('id', `row_${rowIndex}`);
                card.setAttribute('data-manual-discount', '1');

                let unitOptions = '';
                const selectedSatuanId = savedValues ? parseInt(savedValues.satuan_id) : (product.satuans.length >
                    0 ? product.satuans[0].id : null);
                product.satuans.forEach((sat, i) => {
                    const isSelected = selectedSatuanId ? (sat.id === selectedSatuanId) : (i === 0);
                    unitOptions +=
                        `<option value="${sat.id}" data-name="${sat.satuan}" data-harga="${sat.harga_jual}" data-isi="${sat.isi}" ${isSelected ? 'selected' : ''}>${sat.satuan} (${sat.isi})</option>`;
                });

                let defaultPrice = product.satuans.length > 0 ? product.satuans[0].harga_jual : 0;
                if (savedValues) defaultPrice = savedValues.harga;

                const initialQty = savedValues ? savedValues.qty : 1;
                const initialD1 = savedValues ? savedValues.diskon1 : (product.diskon_persen || 0);
                const initialD2 = 0;
                const initialD3 = 0;

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
                        <div class="col-3">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Satuan</label>
                            <select name="items[${rowIndex}][satuan_id]" class="form-select form-select-sm bg-dark text-white border-secondary select-satuan" style="font-size: 0.75rem; border-radius: 8px; height: 32px; padding: 2px 4px;">
                                ${unitOptions}
                            </select>
                            <input type="hidden" name="items[${rowIndex}][satuan]" class="hidden-satuan-name" value="${product.satuans.length > 0 ? product.satuans[0].satuan : ''}">
                        </div>
                        <div class="col-3">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Qty</label>
                            <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm bg-dark text-white border-secondary text-center input-qty px-1" min="0.01" step="any" value="${initialQty}" required style="font-size: 0.75rem; border-color: rgba(255,255,255,0.15); height: 32px; border-radius: 8px !important;">
                        </div>
                        <div class="col-3">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Disc 1 (%)</label>
                            <input type="number" name="items[${rowIndex}][diskon1_persen]" class="form-control form-control-sm bg-dark text-white border-secondary text-center input-diskon1 px-1" min="0" max="100" step="any" value="${initialD1}" style="font-size: 0.75rem; border-color: rgba(255,255,255,0.15); height: 32px; border-radius: 8px !important;">
                        </div>
                        <div class="col-3 text-end">
                            <label class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; font-weight: 500;">Harga</label>
                            <div class="fw-semibold text-white-50 price-display" style="font-size: 0.75rem; line-height: 32px; text-align: right;">
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

                    <input type="hidden" name="items[${rowIndex}][diskon2_persen]" class="input-diskon2" value="${initialD2}">
                    <input type="hidden" name="items[${rowIndex}][diskon3_persen]" class="input-diskon3" value="${initialD3}">
                `;

                emptyCartMessage.classList.add('d-none');
                if (savedValues) {
                    cartContainer.appendChild(card);
                } else {
                    cartContainer.prepend(card);
                }

                if (savedValues && savedValues.isManual === 1) {
                    card.setAttribute('data-manual-discount', '1');
                }

                const selectSatuan = card.querySelector('.select-satuan');
                const hiddenSatuanName = card.querySelector('.hidden-satuan-name');
                const inputHarga = card.querySelector('.input-harga');
                const btnRemove = card.querySelector('.btn-remove-item');
                const inputQty = card.querySelector('.input-qty');
                const inputDis1 = card.querySelector('.input-diskon1');
                const inputDis2 = card.querySelector('.input-diskon2');
                const inputDis3 = card.querySelector('.input-diskon3');

                const selectedOpt = selectSatuan.options[selectSatuan.selectedIndex];
                if (selectedOpt) hiddenSatuanName.value = selectedOpt.getAttribute('data-name');

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

                inputDis1.addEventListener('input', function() {
                    card.setAttribute('data-manual-discount', '1');
                    calculateTotals();
                });

                [inputQty, inputHarga, inputDis1, inputDis2, inputDis3].forEach(input => {
                    input.addEventListener('input', calculateTotals);
                });

                inputQty.addEventListener('change', function() {
                    checkStockLimit(card);
                    calculateTotals();
                });

                rowIndex++;
                validateFormState();
                checkStockLimit(card, !!savedValues);
                calculateTotals();
            }

            jenisTransaksiEl.addEventListener('change', calculateTotals);

            // --- Strata Discount Calculation ---
            function calculateStrataDiscounts() {
                const jenisTransaksi = jenisTransaksiEl.value;
                const supplierSubtotals = {};
                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    const b = barangsCache[card.getAttribute('data-code')];
                    if (b && b.kode_supplier) {
                        const qty = parseFloat(card.querySelector('.input-qty').value) || 0;
                        const harga = parseCleanNumber(card.querySelector('.input-harga').value) || 0;
                        supplierSubtotals[b.kode_supplier] = (supplierSubtotals[b.kode_supplier] || 0) +
                            qty * harga;
                    }
                });

                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    const barangCode = card.getAttribute('data-code');
                    const qty = parseFloat(card.querySelector('.input-qty').value) || 0;
                    const harga = parseCleanNumber(card.querySelector('.input-harga').value) || 0;
                    const sub = qty * harga;
                    const b = barangsCache[barangCode];
                    if (!b) return;

                    const inputDis1 = card.querySelector('.input-diskon1');
                    const inputDis2 = card.querySelector('.input-diskon2');
                    const inputDis3 = card.querySelector('.input-diskon3');

                    if (card.getAttribute('data-manual-discount') === '1') {
                        if (inputDis2) inputDis2.value = '0';
                        if (inputDis3) inputDis3.value = '0';
                        return;
                    }

                    let bestRate = 0,
                        bestRule = null,
                        bestDetail = null;
                    const checkRule = (r, d) => {
                        const rate = parseFloat(d.dis1) || 0;
                        if (rate >= bestRate) {
                            bestRate = rate;
                            bestRule = r;
                            bestDetail = d;
                        }
                    };

                    const findRule = tipe => diskonStrata.filter(r => r.tipe === tipe && r.is_active);

                    findRule('barang').forEach(r => {
                        if (r.barangs && r.barangs.some(i => i.kode_barang === barangCode)) {
                            r.details.forEach(d => {
                                if (qty >= (d.min_qty || 0) && (d.max_qty === null || qty <=
                                        d.max_qty)) checkRule(r, d);
                            });
                        }
                    });
                    if (!bestRule) findRule('beberapa_barang').forEach(r => {
                        if (r.barangs && r.barangs.some(i => i.kode_barang === barangCode)) {
                            r.details.forEach(d => {
                                if (qty >= (d.min_qty || 0) && (d.max_qty === null || qty <=
                                        d.max_qty)) checkRule(r, d);
                            });
                        }
                    });
                    if (!bestRule && b.kategori) findRule('kategori').forEach(r => {
                        if (r.kategori && r.kategori.nama_kategori === b.kategori) {
                            r.details.forEach(d => {
                                if (qty >= (d.min_qty || 0) && (d.max_qty === null || qty <=
                                        d.max_qty)) checkRule(r, d);
                            });
                        }
                    });
                    if (!bestRule && b.merk) findRule('merk').forEach(r => {
                        if (r.merk && r.merk.nama_merk === b.merk) {
                            r.details.forEach(d => {
                                if (qty >= (d.min_qty || 0) && (d.max_qty === null || qty <=
                                        d.max_qty)) checkRule(r, d);
                            });
                        }
                    });
                    if (!bestRule && b.kode_supplier) {
                        const totalSup = supplierSubtotals[b.kode_supplier] || 0;
                        findRule('supplier').forEach(r => {
                            if (r.kode_supplier === b.kode_supplier) {
                                r.details.forEach(d => {
                                    const minN = parseFloat(d.min_nominal) || 0;
                                    const maxN = d.max_nominal ? parseFloat(d.max_nominal) :
                                        null;
                                    if (totalSup >= minN && (maxN === null || totalSup <=
                                            maxN)) checkRule(r, d);
                                });
                            }
                        });
                    }

                    if (bestRule && bestDetail) {
                        let d1_pct = 0;
                        const rawDis1 = parseFloat(bestDetail.dis1) || 0;
                        if (bestDetail.tipe_nilai === 'persen') {
                            d1_pct = rawDis1;
                        } else {
                            if (bestRule.tipe === 'supplier') {
                                const t = supplierSubtotals[b.kode_supplier] || 1;
                                d1_pct = (rawDis1 / t) * 100;
                            } else if (sub > 0) {
                                d1_pct = (rawDis1 / sub) * 100;
                            }
                        }
                        if (inputDis1) inputDis1.value = d1_pct.toFixed(2);
                        if (inputDis2) inputDis2.value = '0';
                        if (inputDis3) inputDis3.value = '0';
                    } else {
                        if (inputDis1) inputDis1.value = '0';
                        if (inputDis2) inputDis2.value = '0';
                        if (inputDis3) inputDis3.value = '0';
                    }
                });
            }

            function calculateTotals() {
                calculateStrataDiscounts();
                let subtotalSum = 0,
                    totalDiskon = 0;
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
                    card.querySelector('.row-subtotal-display').innerText = 'Rp ' + (sub - diskon)
                        .toLocaleString('id-ID');

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
                const grandTotal = subtotalSum - totalDiskon;
                document.getElementById('summary-subtotal').innerText = 'Rp ' + subtotalSum.toLocaleString('id-ID');
                document.getElementById('summary-diskon-item').innerText = '- Rp ' + totalDiskon.toLocaleString(
                    'id-ID');
                document.getElementById('summary-grandtotal').innerText = 'Rp ' + grandTotal.toLocaleString(
                    'id-ID');
                btnSubmitOrder.setAttribute('data-grand-total', grandTotal);
                saveCartToStorage();
            }

            function validateFormState() {
                const hasItems = cartContainer.querySelectorAll('.cart-item-card').length > 0;
                hasItems ? btnSubmitOrder.removeAttribute('disabled') : btnSubmitOrder.setAttribute('disabled',
                    'true');
            }

            // --- Form Submit Guards ---
            document.getElementById('order-form').addEventListener('submit', function(e) {
                // Stock check
                let stockOk = true;
                cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                    if (!checkStockLimit(card)) stockOk = false;
                });
                if (!stockOk) {
                    e.preventDefault();
                    return false;
                }
            });

            // --- LocalStorage Cart Persistence ---
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
                    const isManual = card.getAttribute('data-manual-discount') === '1' ? 1 : 0;
                    const product = barangsCache[code];

                    items.push({
                        code: code,
                        qty: qty,
                        satuan_id: satuanId,
                        harga: harga,
                        diskon1: diskon1,
                        diskon2: diskon2,
                        diskon3: diskon3,
                        isManual: isManual,
                        product: product
                    });
                });

                const cartData = {
                    kode_pelanggan: hiddenKodePelanggan.value,
                    jenis_transaksi: jenisTransaksiEl.value,
                    keterangan: document.querySelector('input[name="keterangan"]')?.value || '',
                    items: items
                };

                localStorage.setItem('mobile_order_canvas_cart_' + '{{ Auth::user()->nik }}', JSON.stringify(
                    cartData));
            }

            function loadCartFromStorage() {
                try {
                    const dataStr = localStorage.getItem('mobile_order_canvas_cart_' + '{{ Auth::user()->nik }}');
                    if (!dataStr) return;
                    const data = JSON.parse(dataStr);
                    if (!data || !data.items || data.items.length === 0) return;

                    const lockedKode = "{{ $pelanggan ? $pelanggan->kode_pelanggan : '' }}";
                    const dataKode = (data.kode_pelanggan || '').trim().toLowerCase();
                    const currentLockedKode = (lockedKode || '').trim().toLowerCase();

                    if (currentLockedKode && dataKode !== currentLockedKode) {
                        // Locked customer changed (different check-in), discard cart
                        localStorage.removeItem('mobile_order_canvas_cart_' + '{{ Auth::user()->nik }}');
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

            calculateTotals();
            validateFormState();
        });
    </script>
@endpush
