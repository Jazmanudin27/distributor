@extends('layouts.mobile')

@section('title', 'Mulai DPB Baru')

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
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
                <i class="fa-solid fa-circle-plus text-white" style="font-size: 1.4rem;"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">Input DPB Baru</h4>
                <span class="text-secondary" style="font-size: 0.8rem; font-weight: 500;">
                    Loading barang untuk dibawa hari ini
                </span>
            </div>
        </div>
        <div>
            <a href="{{ route('mobile.order.canvas.dpb') }}"
                class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-2.5 py-1.5 fw-semibold"
                style="font-size: 0.7rem; border-radius: 8px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Formulir Input DPB Baru -->
    <div class="mobile-card">
        <form action="{{ route('mobile.order.canvas.dpb.store') }}" method="POST" id="dpb-form">
            @csrf
            <div class="mb-2">
                <label class="form-label text-secondary small mb-1">Tanggal Pengambilan</label>
                <input type="date" name="tanggal" id="tanggal"
                    class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label text-secondary small mb-1">Keterangan / Catatan Gudang</label>
                <textarea name="keterangan" id="keterangan" rows="1"
                    class="form-control form-control-sm bg-dark text-white border-secondary"
                    placeholder="Catatan tambahan (opsional)..."></textarea>
            </div>

            <!-- Product List Container (Daftar Semua Barang) -->
            <div class="mb-3">
                <label class="form-label text-secondary small mb-1">Pilih Barang dari Gudang</label>
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-dark text-secondary border-secondary">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="product-filter-input"
                        class="form-control form-control-sm bg-dark text-white border-secondary"
                        placeholder="Filter nama atau kode barang...">
                </div>

                <div id="product-list-container" class="list-group shadow-sm"
                    style="max-height: 220px; overflow-y: auto; background-color: #161e31; border: 1px solid var(--border-color); border-radius: 8px;">
                </div>
            </div>

            <!-- SELECTED PRODUCT DETAILS INPUT (Show when product is selected) -->
            <div id="selected-product-panel" class="mobile-card p-3 mb-3 d-none"
                style="background: rgba(99, 102, 241, 0.08) !important; border: 1px solid rgba(99, 102, 241, 0.25) !important;">
                <div
                    class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-20">
                    <div>
                        <span class="badge bg-primary mb-1" style="font-size: 0.6rem;">Barang Terpilih</span>
                        <h6 class="fw-bold text-white mb-0" id="selected-product-name"
                            style="font-size: 0.85rem; line-height: 1.3;">-</h6>
                        <span class="text-secondary font-monospace" id="selected-product-code"
                            style="font-size: 0.65rem;">Kode: -</span>
                    </div>
                    <button type="button" id="btn-cancel-select" class="btn btn-sm text-secondary border-0 p-1"
                        style="font-size: 0.9rem;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-secondary mb-1" style="font-size: 0.7rem; font-weight: 500;">Pilih
                            Satuan</label>
                        <select id="select-temp-satuan"
                            class="form-select form-select-sm bg-dark text-white border-secondary"
                            style="font-size: 0.75rem; border-radius: 8px; height: 34px;">
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-secondary mb-1" style="font-size: 0.7rem; font-weight: 500;">Jumlah
                            (Qty)</label>
                        <div class="input-group input-group-sm">
                            <button type="button" id="btn-temp-minus"
                                class="btn btn-outline-secondary btn-qty-minus text-white px-2"
                                style="border-radius: 8px 0 0 8px; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); height: 34px;">-</button>
                            <input type="number" id="input-temp-qty"
                                class="form-control form-control-sm bg-dark text-white border-secondary text-center px-1"
                                min="0.01" step="any" value="1"
                                style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15); height: 34px;">
                            <button type="button" id="btn-temp-plus"
                                class="btn btn-outline-secondary btn-qty-plus text-white px-2"
                                style="border-radius: 0 8px 8px 0; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); height: 34px;">+</button>
                        </div>
                    </div>
                </div>

                <div
                    class="d-flex justify-content-between align-items-center bg-dark bg-opacity-40 p-2 rounded-3 mb-3 border border-secondary border-opacity-10">
                    <div class="small">
                        <span class="text-secondary d-block" style="font-size: 0.65rem;">Stok Gudang</span>
                        <span class="text-info fw-bold font-monospace" id="selected-product-stock-info"
                            style="font-size: 0.75rem;">-</span>
                    </div>
                    <div class="small text-end">
                        <span class="text-secondary d-block" style="font-size: 0.65rem;">Muat Diminta</span>
                        <span class="text-warning fw-bold font-monospace" id="selected-product-req-info"
                            style="font-size: 0.75rem;">0 PCS</span>
                    </div>
                </div>

                <button type="button" id="btn-add-to-cart" class="btn btn-sm btn-primary w-100 py-2 fw-semibold"
                    style="font-size: 0.75rem; border-radius: 8px;">
                    <i class="fa-solid fa-plus me-1"></i> Masukkan ke Keranjang
                </button>
            </div>

            <!-- Item Cart -->
            <h6 class="fw-bold mb-2 small" style="letter-spacing: 0.5px;">Daftar Barang Muat</h6>
            <div id="cart-container" class="mb-3">
                <div class="text-center py-4 border border-secondary border-opacity-20 rounded-4 bg-dark bg-opacity-10"
                    id="empty-cart-message">
                    <i class="fa-solid fa-basket-shopping text-secondary mb-2"
                        style="font-size: 1.8rem; opacity: 0.4;"></i>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Belum ada barang ditambahkan.</p>
                </div>
            </div>

            <button type="submit" class="btn btn-sm btn-mobile-primary w-100 py-2 fs-7" id="btn-submit-dpb" disabled>
                <i class="fa-solid fa-truck-moving me-1"></i> Simpan & Muat Barang
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let barangsCache = {};
        let rowIndex = 0;
        const allProducts = @json($products);

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
            let formatted = breakdowns.join(' ') || '0 PCS';
            return isNegative ? '-' + formatted : formatted;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const productFilterInput = document.getElementById('product-filter-input');
            const productListContainer = document.getElementById('product-list-container');
            const cartContainer = document.getElementById('cart-container');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const btnSubmitDpb = document.getElementById('btn-submit-dpb');

            // Elements for Selected Product Detail Panel
            const selectedProductPanel = document.getElementById('selected-product-panel');
            const selectedProductName = document.getElementById('selected-product-name');
            const selectedProductCode = document.getElementById('selected-product-code');
            const selectedProductStockInfo = document.getElementById('selected-product-stock-info');
            const selectedProductReqInfo = document.getElementById('selected-product-req-info');
            const selectTempSatuan = document.getElementById('select-temp-satuan');
            const inputTempQty = document.getElementById('input-temp-qty');
            const btnTempMinus = document.getElementById('btn-temp-minus');
            const btnTempPlus = document.getElementById('btn-temp-plus');
            const btnCancelSelect = document.getElementById('btn-cancel-select');
            const btnAddToCart = document.getElementById('btn-add-to-cart');

            let currentSelectedProduct = null;

            // Render Product List Client-Side
            function renderProductList(filterText = '') {
                productListContainer.innerHTML = '';
                const query = filterText.toLowerCase().trim();

                const filtered = allProducts.filter(item => {
                    return item.nama_barang.toLowerCase().includes(query) ||
                        item.kode_barang.toLowerCase().includes(query) ||
                        (item.kode_item && item.kode_item.toLowerCase().includes(query));
                });

                if (filtered.length === 0) {
                    productListContainer.innerHTML =
                        '<div class="p-3 text-secondary text-center" style="font-size: 0.75rem;">Barang tidak ditemukan.</div>';
                    return;
                }

                filtered.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className =
                        'list-group-item list-group-item-action text-white border-0 py-2 px-3 d-flex justify-content-between align-items-center';
                    btn.style.backgroundColor = 'transparent';
                    btn.style.borderBottom = '1px solid rgba(255,255,255,0.05) !important';

                    const formattedStock = formatStokJS(item.stok, item.satuans);
                    btn.innerHTML = `
                        <div class="d-flex flex-column text-start" style="max-width: 80%;">
                            <span class="fw-semibold text-white" style="font-size: 0.8rem;">${item.nama_barang}</span>
                            <span class="text-secondary mt-0.5" style="font-size: 0.7rem;">Kode: ${item.kode_barang} | Stok: ${formattedStock}</span>
                        </div>
                        <span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size: 0.65rem; font-weight: 600;">Pilih</span>
                    `;

                    btn.addEventListener('click', () => {
                        selectProduct(item);
                    });
                    productListContainer.appendChild(btn);
                });
            }

            // Listen to Filter Input
            productFilterInput.addEventListener('input', function() {
                renderProductList(this.value);
            });

            // Function to select product and open configuration details
            function selectProduct(product) {
                if (product.stok <= 0) {
                    Swal.fire({
                        title: 'Stok Kosong',
                        text: `Barang "${product.nama_barang}" tidak memiliki stok di gudang.`,
                        icon: 'error',
                        background: '#161e31',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                    return;
                }

                currentSelectedProduct = product;
                barangsCache[product.kode_barang] = product;

                // Show selected panel
                selectedProductName.textContent = product.nama_barang;
                selectedProductCode.textContent = `Kode: ${product.kode_barang}`;
                selectedProductStockInfo.textContent = formatStokJS(product.stok, product.satuans);

                // Populate satuan options
                selectTempSatuan.innerHTML = '';
                product.satuans.forEach((sat, i) => {
                    const opt = document.createElement('option');
                    opt.value = sat.id;
                    opt.textContent = `${sat.satuan} (Isi ${sat.isi})`;
                    opt.setAttribute('data-name', sat.satuan);
                    opt.setAttribute('data-isi', sat.isi);
                    if (i === 0) opt.selected = true;
                    selectTempSatuan.appendChild(opt);
                });

                inputTempQty.value = 1;
                updateRequestedQtyInfo();

                // Show panel
                selectedProductPanel.classList.remove('d-none');

                // Scroll selected panel into view
                selectedProductPanel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
                inputTempQty.focus();
            }

            function updateRequestedQtyInfo() {
                if (!currentSelectedProduct) return;
                const qty = parseFloat(inputTempQty.value) || 0;
                const selectedOpt = selectTempSatuan.options[selectTempSatuan.selectedIndex];
                if (!selectedOpt) return;
                const isi = parseFloat(selectedOpt.getAttribute('data-isi')) || 1;
                const name = selectedOpt.getAttribute('data-name');
                const totalPcs = qty * isi;
                selectedProductReqInfo.textContent = `${qty} ${name} (Setara ${totalPcs} PCS)`;
            }

            selectTempSatuan.addEventListener('change', updateRequestedQtyInfo);
            inputTempQty.addEventListener('input', updateRequestedQtyInfo);
            inputTempQty.addEventListener('change', updateRequestedQtyInfo);

            btnTempMinus.addEventListener('click', function() {
                let val = parseFloat(inputTempQty.value) || 0;
                if (val > 1) {
                    inputTempQty.value = val - 1;
                    updateRequestedQtyInfo();
                }
            });

            btnTempPlus.addEventListener('click', function() {
                let val = parseFloat(inputTempQty.value) || 0;
                inputTempQty.value = val + 1;
                updateRequestedQtyInfo();
            });

            btnCancelSelect.addEventListener('click', function() {
                clearSelection();
            });

            function clearSelection() {
                currentSelectedProduct = null;
                selectedProductPanel.classList.add('d-none');
                productFilterInput.value = '';
                renderProductList('');
            }

            // Add configured product to cart
            btnAddToCart.addEventListener('click', function() {
                if (!currentSelectedProduct) return;

                const qty = parseFloat(inputTempQty.value) || 0;
                if (qty <= 0) {
                    Swal.fire({
                        title: 'Jumlah Tidak Valid',
                        text: 'Masukkan jumlah muat yang valid (lebih dari 0).',
                        icon: 'warning',
                        background: '#161e31',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                    return;
                }

                const selectedOpt = selectTempSatuan.options[selectTempSatuan.selectedIndex];
                const satuanId = selectTempSatuan.value;
                const satuanName = selectedOpt.getAttribute('data-name');
                const isi = parseFloat(selectedOpt.getAttribute('data-isi')) || 1;
                const qtySmallest = qty * isi;

                // Validate Warehouse Stock
                if (qtySmallest > currentSelectedProduct.stok) {
                    const formattedStok = formatStokJS(currentSelectedProduct.stok, currentSelectedProduct
                        .satuans);
                    Swal.fire({
                        title: 'Stok Gudang Tidak Cukup',
                        html: `Stok gudang barang <b>${currentSelectedProduct.nama_barang}</b> tidak mencukupi!<br><br>` +
                            `Tersedia: <b>${formattedStok}</b><br>` +
                            `Loading diminta: <b>${qty} ${satuanName}</b> (Setara ${qtySmallest} PCS)`,
                        icon: 'error',
                        background: '#161e31',
                        color: '#f8fafc',
                        confirmButtonColor: '#6366f1'
                    });
                    return;
                }

                // Check if already in cart (duplicate check)
                const existingCard = document.querySelector(
                    `.cart-item-card[data-code="${currentSelectedProduct.kode_barang}"]`);
                if (existingCard) {
                    existingCard.remove();
                }

                // Add to cart list
                const card = document.createElement('div');
                card.className =
                    'card-item bg-dark bg-opacity-40 border border-secondary border-opacity-30 rounded-4 p-3 mb-2 cart-item-card';
                card.setAttribute('data-code', currentSelectedProduct.kode_barang);

                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem; line-height: 1.3;">${currentSelectedProduct.nama_barang}</h6>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-secondary" style="font-size: 0.6rem; opacity: 0.8;">${currentSelectedProduct.kode_barang}</span>
                                <span class="text-warning fw-semibold font-monospace" style="font-size: 0.75rem;">${qty} ${satuanName}</span>
                                <span class="text-secondary" style="font-size: 0.65rem;">(Setara ${qtySmallest} PCS)</span>
                            </div>
                            <input type="hidden" name="items[${rowIndex}][kode_barang]" value="${currentSelectedProduct.kode_barang}">
                            <input type="hidden" name="items[${rowIndex}][satuan_id]" value="${satuanId}">
                            <input type="hidden" name="items[${rowIndex}][qty_ambil]" value="${qty}">
                        </div>
                        <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center btn-remove-item" style="width: 28px; height: 28px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: none;">
                            <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>
                `;

                emptyCartMessage.classList.add('d-none');
                cartContainer.appendChild(card);

                const btnRemove = card.querySelector('.btn-remove-item');
                btnRemove.addEventListener('click', function() {
                    card.remove();
                    if (cartContainer.querySelectorAll('.cart-item-card').length === 0) {
                        emptyCartMessage.classList.remove('d-none');
                    }
                    validateFormState();
                });

                rowIndex++;
                clearSelection();
                validateFormState();

                // Scroll down to cart
                cartContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            });

            function validateFormState() {
                const hasItems = cartContainer.querySelectorAll('.cart-item-card').length > 0;
                hasItems ? btnSubmitDpb.removeAttribute('disabled') : btnSubmitDpb.setAttribute('disabled', 'true');
            }

            // Initial list render
            renderProductList('');
            validateFormState();
        });
    </script>
@endpush
