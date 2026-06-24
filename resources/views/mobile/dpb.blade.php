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

                function checkStockLimit(card) {
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
                        Swal.fire({
                            title: 'Stok Gudang Tidak Cukup',
                            html: `Stok gudang barang <b>${product.nama_barang}</b> tidak mencukupi!<br><br>` +
                                `Tersedia: <b>${formattedStok}</b><br>` +
                                `Loading diminta: <b>${qty} ${selectedOpt.getAttribute('data-name')}</b> (Setara ${qtySmallest} PCS)`,
                            icon: 'error',
                            background: '#161e31',
                            color: '#f8fafc',
                            confirmButtonColor: '#6366f1'
                        });

                        const maxQtyInUnit = Math.floor(product.stok / isi);
                        qtyInput.value = maxQtyInUnit;
                        return false;
                    }
                    return true;
                }

                function addProductToCart(product) {
                    productSearchResults.classList.add('d-none');
                    productSearchInput.value = '';

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

                    barangsCache[product.kode_barang] = {
                        kode_barang: product.kode_barang,
                        nama_barang: product.nama_barang,
                        stok: product.stok,
                        satuans: product.satuans
                    };

                    const existingCard = document.querySelector(`.cart-item-card[data-code="${product.kode_barang}"]`);
                    if (existingCard) {
                        const qtyInput = existingCard.querySelector('.input-qty');
                        qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
                        checkStockLimit(existingCard);
                        return;
                    }

                    const card = document.createElement('div');
                    card.className =
                        'card-item bg-dark bg-opacity-40 border border-secondary border-opacity-30 rounded-4 p-3 mb-2 cart-item-card';
                    card.setAttribute('data-code', product.kode_barang);

                    let unitOptions = '';
                    product.satuans.forEach((sat, i) => {
                        unitOptions +=
                            `<option value="${sat.id}" data-name="${sat.satuan}" data-isi="${sat.isi}" ${i === 0 ? 'selected' : ''}>${sat.satuan} (Isi ${sat.isi})</option>`;
                    });

                    card.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start mb-2 border-bottom border-secondary border-opacity-20 pb-2">
                            <div>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem; line-height: 1.3;">${product.nama_barang}</h6>
                                <span class="badge bg-secondary mt-1" style="font-size: 0.6rem; opacity: 0.8;">${product.kode_barang}</span>
                                <input type="hidden" name="items[${rowIndex}][kode_barang]" value="${product.kode_barang}">
                            </div>
                            <button type="button" class="btn btn-sm rounded-circle d-flex align-items-center justify-content-center btn-remove-item" style="width: 28px; height: 28px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: none;">
                                <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                        <div class="row g-2 align-items-end">
                            <div class="col-6">
                                <label class="form-label text-secondary mb-1" style="font-size: 0.7rem; font-weight: 500;">Satuan</label>
                                <select name="items[${rowIndex}][satuan_id]" class="form-select form-select-sm bg-dark text-white border-secondary select-satuan" style="font-size: 0.75rem; border-radius: 8px; height: 34px;">
                                    ${unitOptions}
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary mb-1" style="font-size: 0.7rem; font-weight: 500;">Qty Muat</label>
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-qty-minus text-white px-2" style="border-radius: 8px 0 0 8px; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); height: 34px;">-</button>
                                    <input type="number" name="items[${rowIndex}][qty_ambil]" class="form-control form-control-sm bg-dark text-white border-secondary text-center input-qty px-1" min="0.01" step="any" value="1" required style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15); height: 34px;">
                                    <button type="button" class="btn btn-outline-secondary btn-qty-plus text-white px-2" style="border-radius: 0 8px 8px 0; border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); height: 34px;">+</button>
                                </div>
                            </div>
                        </div>
                    `;

                    emptyCartMessage.classList.add('d-none');
                    cartContainer.appendChild(card);

                    const selectSatuan = card.querySelector('.select-satuan');
                    const btnRemove = card.querySelector('.btn-remove-item');
                    const inputQty = card.querySelector('.input-qty');
                    const btnQtyMinus = card.querySelector('.btn-qty-minus');
                    const btnQtyPlus = card.querySelector('.btn-qty-plus');

                    selectSatuan.addEventListener('change', function() {
                        checkStockLimit(card);
                    });

                    btnRemove.addEventListener('click', function() {
                        card.remove();
                        if (cartContainer.querySelectorAll('.cart-item-card').length === 0) {
                            emptyCartMessage.classList.remove('d-none');
                        }
                        validateFormState();
                    });

                    inputQty.addEventListener('change', function() {
                        checkStockLimit(card);
                    });

                    btnQtyMinus.addEventListener('click', function() {
                        let val = parseFloat(inputQty.value) || 0;
                        if (val > 1) {
                            inputQty.value = val - 1;
                            inputQty.dispatchEvent(new Event('change'));
                        }
                    });

                    btnQtyPlus.addEventListener('click', function() {
                        let val = parseFloat(inputQty.value) || 0;
                        inputQty.value = val + 1;
                        inputQty.dispatchEvent(new Event('change'));
                    });

                    rowIndex++;
                    validateFormState();
                    checkStockLimit(card);
                }

                function validateFormState() {
                    const hasItems = cartContainer.querySelectorAll('.cart-item-card').length > 0;
                    hasItems ? btnSubmitDpb.removeAttribute('disabled') : btnSubmitDpb.setAttribute('disabled', 'true');
                }

                document.getElementById('dpb-form').addEventListener('submit', function(e) {
                    let stockOk = true;
                    cartContainer.querySelectorAll('.cart-item-card').forEach(card => {
                        if (!checkStockLimit(card)) stockOk = false;
                    });
                    if (!stockOk) {
                        e.preventDefault();
                        return false;
                    }
                });

                validateFormState();
            });
        </script>
    @endif
@endpush
