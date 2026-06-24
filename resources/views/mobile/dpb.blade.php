@extends('layouts.mobile')

@section('title', 'DPB (Barang Bawaan Hari Ini)')

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
                <i class="fa-solid fa-truck text-white" style="font-size: 1.4rem;"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">DPB Aktif Anda</h4>
                <span class="text-secondary" style="font-size: 0.8rem; font-weight: 500;">
                    Barang yang dibawa hari ini
                </span>
            </div>
        </div>
        <div>
            <a href="{{ route('mobile.order.canvas.create') }}"
                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 px-2.5 py-1.5 fw-semibold"
                style="font-size: 0.7rem; border-radius: 8px;">
                <i class="fa-solid fa-arrow-left"></i> Input Order
            </a>
        </div>
    </div>

    @if (!$session)
        <!-- Formulir Input DPB Baru (Saat Tidak Ada Sesi Aktif) -->
        <div class="mobile-card">
            <div class="d-flex align-items-center mb-3">
                <i class="fa-solid fa-circle-plus text-primary me-2 fs-5"></i>
                <h5 class="fw-bold mb-0" style="font-size: 0.95rem;">Mulai DPB Baru (Loading Barang)</h5>
            </div>

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

                <!-- Product Search (Daftar Barang Gudang) -->
                <div class="position-relative mb-3" style="position: relative; z-index: 10;">
                    <label class="form-label text-secondary small mb-1">Cari & Tambah Barang</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark text-secondary border-secondary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="product-search-input"
                            class="form-control form-control-sm bg-dark text-white border-secondary"
                            placeholder="Ketik nama atau kode barang...">
                    </div>
                    <div id="product-search-results" class="list-group position-absolute w-100 shadow-lg mt-1 d-none"
                        style="z-index: 1050; max-height: 220px; overflow-y: auto; background-color: #161e31; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                </div>

                <!-- SELECTED PRODUCT DETAILS INPUT (Show when product is selected) -->
                <div id="selected-product-panel" class="mobile-card p-3 mb-3 d-none" 
                    style="background: rgba(99, 102, 241, 0.08) !important; border: 1px solid rgba(99, 102, 241, 0.25) !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-20">
                        <div>
                            <span class="badge bg-primary mb-1" style="font-size: 0.6rem;">Barang Terpilih</span>
                            <h6 class="fw-bold text-white mb-0" id="selected-product-name" style="font-size: 0.85rem; line-height: 1.3;">-</h6>
                            <span class="text-secondary font-monospace" id="selected-product-code" style="font-size: 0.65rem;">Kode: -</span>
                        </div>
                        <button type="button" id="btn-cancel-select" class="btn btn-sm text-secondary border-0 p-1" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-secondary mb-1" style="font-size: 0.7rem; font-weight: 500;">Pilih Satuan</label>
                            <select id="select-temp-satuan" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size: 0.75rem; border-radius: 8px; height: 34px;">
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary mb-1" style="font-size: 0.7rem; font-weight: 500;">Jumlah (Qty)</label>
                            <div class="input-group input-group-sm">
                                <button type="button" id="btn-temp-minus" class="btn btn-outline-secondary btn-qty-minus text-white px-2" style="border-radius: 8px 0 0 8px; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); height: 34px;">-</button>
                                <input type="number" id="input-temp-qty" class="form-control form-control-sm bg-dark text-white border-secondary text-center px-1" min="0.01" step="any" value="1" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15); height: 34px;">
                                <button type="button" id="btn-temp-plus" class="btn btn-outline-secondary btn-qty-plus text-white px-2" style="border-radius: 0 8px 8px 0; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); height: 34px;">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-dark bg-opacity-40 p-2 rounded-3 mb-3 border border-secondary border-opacity-10">
                        <div class="small">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Stok Gudang</span>
                            <span class="text-info fw-bold font-monospace" id="selected-product-stock-info" style="font-size: 0.75rem;">-</span>
                        </div>
                        <div class="small text-end">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Muat Diminta</span>
                            <span class="text-warning fw-bold font-monospace" id="selected-product-req-info" style="font-size: 0.75rem;">0 PCS</span>
                        </div>
                    </div>

                    <button type="button" id="btn-add-to-cart" class="btn btn-sm btn-primary w-100 py-2 fw-semibold" style="font-size: 0.75rem; border-radius: 8px;">
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
    @else
        <!-- DPB Info Header -->
        <div class="mobile-card p-3 mb-4"
            style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);">
            <div class="row g-3">
                <div class="col-6">
                    <span class="text-secondary d-block"
                        style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">No.
                        DPB</span>
                    <strong class="text-info" style="font-size: 0.9rem;">{{ $session->no_canvas }}</strong>
                </div>
                <div class="col-6 text-end">
                    <span class="text-secondary d-block"
                        style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal
                        Loading</span>
                    <strong class="text-white-50"
                        style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</strong>
                </div>
                <div
                    class="col-12 mt-2 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary" style="font-size: 0.75rem;">Status:</span>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1"
                            style="font-size: 0.65rem; font-weight: 600;">
                            <i class="fa-solid fa-truck-moving me-1"></i> Aktif (Di Jalan)
                        </span>
                    </div>
                    @if ($session->keterangan)
                        <small class="text-secondary italic" style="font-size: 0.7rem;">"{{ $session->keterangan }}"</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Listing -->
        <h5 class="fw-bold mb-3" style="font-size: 0.95rem; letter-spacing: 0.5px;">
            Daftar Barang Bawaan ({{ $session->details->count() }} Item)
        </h5>

        <div class="dpb-items">
            @foreach ($session->details as $detail)
                @php
                    $qtyAmbil = (float) $detail->qty_ambil;
                    $qtyTerjual = (float) $detail->qty_terjual;
                    $qtyKembali = (float) $detail->qty_kembali;
                    $sisaStok = $qtyAmbil - $qtyTerjual - $qtyKembali;
                @endphp
                <div class="mobile-card p-3 mb-2"
                    style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div
                        class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem;">
                                {{ $detail->barang->nama_barang }}
                            </h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                Kode: {{ $detail->kode_barang }}
                            </span>
                        </div>
                        <span class="badge bg-light text-secondary border fw-semibold"
                            style="font-size: 0.65rem; padding: 2px 8px;">
                            {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                        </span>
                    </div>

                    <div class="row g-2 text-center" style="font-size: 0.75rem;">
                        <div class="col-4">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Ambil</span>
                            <strong class="text-primary font-monospace"
                                style="font-size: 0.9rem;">{{ $qtyAmbil }}</strong>
                        </div>
                        <div class="col-4 border-start border-end border-secondary border-opacity-10">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Terjual</span>
                            <strong class="text-info font-monospace"
                                style="font-size: 0.9rem;">{{ $qtyTerjual }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Sisa Bawaan</span>
                            <strong class="text-success font-monospace"
                                style="font-size: 0.9rem;">{{ $sisaStok }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    @if (!$session)
        <script>
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

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            document.addEventListener('DOMContentLoaded', function() {
                const productSearchInput = document.getElementById('product-search-input');
                const productSearchResults = document.getElementById('product-search-results');
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

                // Autocomplete Search
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
                                    <span class="text-secondary mt-1" style="font-size: 0.7rem;">Kode: ${item.kode_barang} | Stok: ${formatStokJS(item.stok, item.satuans)}</span>
                                `;
                                btn.addEventListener('click', () => {
                                    selectProduct(item);
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
                    
                    // Hide search results, clear search input
                    productSearchResults.classList.add('d-none');
                    productSearchInput.value = '';
                    
                    // Show panel
                    selectedProductPanel.classList.remove('d-none');
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
                    productSearchInput.value = '';
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
                        const formattedStok = formatStokJS(currentSelectedProduct.stok, currentSelectedProduct.satuans);
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
                    const existingCard = document.querySelector(`.cart-item-card[data-code="${currentSelectedProduct.kode_barang}"]`);
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
                    cartContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });

                function validateFormState() {
                    const hasItems = cartContainer.querySelectorAll('.cart-item-card').length > 0;
                    hasItems ? btnSubmitDpb.removeAttribute('disabled') : btnSubmitDpb.setAttribute('disabled', 'true');
                }

                validateFormState();
            });
        </script>
    @endif
@endpush
