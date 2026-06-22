@extends('layouts.mobile')

@section('title', 'Kunjungan Sales')

@section('content')
    <h5 class="fw-bold mb-3" style="font-size: 1.1rem; letter-spacing: 0.5px;">Pelanggan & Kunjungan</h5>

    @if ($activeCheckin)
        <!-- Active Check-in Screen -->
        <div class="mobile-card border-indigo pulse-active" style="border-color: rgba(99, 102, 241, 0.4);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-indigo-subtle text-indigo px-3 py-1"
                    style="background-color: rgba(99, 102, 241, 0.15); color: #818cf8; font-weight: 600; font-size: 0.75rem;">
                    <i class="fa-solid fa-location-dot me-1"></i> Sedang Berkunjung
                </span>
                <span class="text-secondary" style="font-size: 0.75rem;" id="checkin-time"
                    data-time="{{ $activeCheckin->checkin->toIso8601String() }}">
                    Check-in: {{ $activeCheckin->checkin->format('H:i') }}
                </span>
            </div>

            <h4 class="fw-bold text-white mb-1" style="font-size: 1.15rem;">{{ $activeCheckin->pelanggan->nama_pelanggan }}
            </h4>
            <p class="text-secondary mb-2" style="font-size: 0.8rem;">
                <i class="fa-solid fa-map-pin me-1 text-danger"></i> {{ $activeCheckin->pelanggan->alamat_pelanggan }}
            </p>

            <!-- Collapsible Customer Info Card -->
            <div class="mb-3">
                <button
                    class="btn btn-sm btn-link text-indigo p-0 fw-semibold text-decoration-none d-flex align-items-center"
                    type="button" data-bs-toggle="collapse" data-bs-target="#active-customer-info" aria-expanded="false"
                    style="font-size: 0.8rem; color: #818cf8 !important;">
                    <i class="fa-solid fa-circle-info me-1"></i> Detail Informasi Pelanggan
                    <i class="fa-solid fa-chevron-down ms-1" id="active-info-chevron"
                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                </button>
                <div class="collapse mt-2" id="active-customer-info">
                    <div class="p-3 rounded-4"
                        style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                        <div class="row g-2 text-secondary" style="font-size: 0.78rem; line-height: 1.5;">
                            <div class="col-6">
                                <span class="d-block text-secondary-50"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">KODE TOKO</span>
                                <span
                                    class="text-white font-monospace fw-semibold">{{ $activeCheckin->pelanggan->kode_pelanggan }}</span>
                            </div>
                            <div class="col-6">
                                <span class="d-block text-secondary-50"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">TELEPON / HP</span>
                                <span class="text-white">{{ $activeCheckin->pelanggan->no_hp_pelanggan ?: '-' }}</span>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="d-block text-secondary-50"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">METODE BAYAR</span>
                                <span
                                    class="text-white fw-semibold">{{ $activeCheckin->pelanggan->metode_bayar ?: '-' }}</span>
                            </div>
                            <div class="col-6 mt-2">
                                <span class="d-block text-secondary-50"
                                    style="font-size: 0.68rem; letter-spacing: 0.5px;">WILAYAH</span>
                                <span class="text-white">
                                    {{ $activeCheckin->pelanggan->wilayah ? $activeCheckin->pelanggan->wilayah->nama_wilayah : '-' }}
                                    @if ($activeCheckin->pelanggan->subWilayah)
                                        / {{ $activeCheckin->pelanggan->subWilayah->nama_wilayah }}
                                    @endif
                                </span>
                            </div>

                            <div class="col-12 mt-2 pt-2 border-top border-secondary border-opacity-10">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary-50" style="font-size: 0.68rem; letter-spacing: 0.5px;">LIMIT
                                        KREDIT:</span>
                                    <span class="fw-semibold text-white">Rp
                                        {{ number_format($activeCheckin->pelanggan->limit_pelanggan, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary-50"
                                        style="font-size: 0.68rem; letter-spacing: 0.5px;">OUTSTANDING PIUTANG:</span>
                                    <span class="fw-semibold text-danger">Rp
                                        {{ number_format($activeCheckin->pelanggan->getOutstandingPiutang(), 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary-50" style="font-size: 0.68rem; letter-spacing: 0.5px;">SISA
                                        LIMIT KREDIT:</span>
                                    <span class="fw-bold text-success">Rp
                                        {{ number_format($activeCheckin->pelanggan->getSisaLimitKredit(), 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if ($activeCheckin->pelanggan->hasOverdueInvoices())
                                <div class="col-12 mt-2">
                                    <div class="alert alert-danger p-2 mb-0 d-flex align-items-center rounded-3 border-0"
                                        style="font-size: 0.72rem; background-color: rgba(239, 68, 68, 0.15); color: #f87171;">
                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                        <span>Toko memiliki tagihan jatuh tempo (Overdue)!</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timer counter -->
            <div class="p-3 rounded-4 mb-4 text-center"
                style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                <div class="text-secondary mb-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px;">
                    Durasi Kunjungan</div>
                <div class="fw-bold text-indigo" id="duration-counter"
                    style="font-size: 1.5rem; color: #818cf8; font-family: monospace;">00:00:00</div>
            </div>

            <!-- Quick actions during check-in -->
            <div class="d-grid gap-2 mb-4">
                <a href="{{ route('mobile.order.create', ['kode_pelanggan' => $activeCheckin->kode_pelanggan]) }}"
                    class="btn btn-sm btn-mobile btn-mobile-primary py-2">
                    <i class="fa-solid fa-cart-plus me-2"></i> Input Penjualan Untuk Toko Ini
                </a>

                <button type="button"
                    class="btn btn-sm btn-mobile btn-outline-light py-2 text-white border-secondary border-opacity-50"
                    style="font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#modal-ajuan-limit"
                    data-customer-code="{{ $activeCheckin->kode_pelanggan }}"
                    data-customer-name="{{ $activeCheckin->pelanggan->nama_pelanggan }}"
                    data-customer-limit="{{ (float) $activeCheckin->pelanggan->limit_pelanggan }}">
                    <i class="fa-solid fa-file-invoice-dollar me-2 text-purple" style="color: #a855f7 !important;"></i>
                    Ajukan Limit Kredit Toko Ini
                </button>
            </div>

            <!-- Checkout Form -->
            <form action="{{ route('mobile.kunjungan.checkout') }}" method="POST" id="checkout-form">
                @csrf
                <div class="mb-3">
                    <label for="catatan" class="form-label text-secondary"
                        style="font-size: 0.85rem; font-weight: 500;">Catatan Hasil Kunjungan</label>
                    <textarea class="form-control form-control-mobile" id="catatan" name="catatan" rows="3"
                        placeholder="Tulis catatan (misal: order berhasil, toko tutup, stok masih penuh...)" required></textarea>
                </div>

                <button type="submit" class="btn btn-mobile w-100 py-3 text-white"
                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Selesai Kunjungan (Check-out)
                </button>
            </form>
        </div>
    @else
        <!-- Start New Visit Screen -->
        <div class="mobile-card" style="position: relative; z-index: 10;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Mulai Kunjungan Baru</h6>
                <a href="{{ route('mobile.pelanggan.create') }}"
                    class="btn btn-xs btn-mobile-primary px-2.5 py-1 text-white border-0 text-decoration-none"
                    style="font-size: 0.72rem; border-radius: 8px;">
                    <i class="fa-solid fa-plus me-1"></i> Pelanggan Baru
                </a>
            </div>

            <!-- Region Filter -->
            <div class="mb-3">
                <label class="form-label text-secondary" style="font-size: 0.85rem; font-weight: 500;">Filter Wilayah
                    (Opsional)</label>
                <select id="search-wilayah-select" class="form-select form-control-mobile text-white"
                    style="font-size: 0.85rem; background-color: #121824;">
                    <option value="">-- Semua Wilayah --</option>
                    @foreach ($wilayahs as $w)
                        <option value="{{ $w->kode_wilayah }}">{{ $w->nama_wilayah }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Live Autocomplete Customer Search -->
            <div class="mb-3 position-relative">
                <label class="form-label text-secondary" style="font-size: 0.85rem; font-weight: 500;">Pilih Toko
                    Pelanggan</label>
                <div class="input-group">
                    <span class="input-group-text"
                        style="background-color: #121824; border-color: rgba(255, 255, 255, 0.08); color: var(--text-secondary); border-top-left-radius: 12px; border-bottom-left-radius: 12px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" id="customer-search-input" class="form-control form-control-mobile"
                        placeholder="Ketik nama atau kode toko..."
                        style="border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                </div>
                <!-- Results dropdown list -->
                <div id="search-results-list" class="list-group position-absolute w-100 shadow-lg mt-1 d-none"
                    style="z-index: 1000; max-height: 220px; overflow-y: auto; background-color: #161e31; border: 1px solid var(--border-color); border-radius: 12px;">
                    <!-- list-items will be appended by JS -->
                </div>
            </div>

            <!-- Selected Customer Card Detail (initially hidden) -->
            <div id="selected-customer-detail" class="d-none p-3 rounded-4 mb-4"
                style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-color);">
                <h6 class="fw-bold text-white mb-2" id="detail-name">Nama Toko</h6>
                <div class="text-secondary mb-2" style="font-size: 0.8rem;">
                    <div><i class="fa-solid fa-barcode me-1"></i> <span id="detail-code"></span></div>
                    <div><i class="fa-solid fa-phone me-1"></i> <span id="detail-phone"></span></div>
                    <div><i class="fa-solid fa-map-pin me-1 text-danger"></i> <span id="detail-address"></span></div>
                    <div><i class="fa-solid fa-wallet me-1 text-indigo"></i> Metode: <span id="detail-metode"
                            class="fw-semibold"></span></div>
                </div>
                <div
                    class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-10 mt-2">
                    <span class="text-secondary" style="font-size: 0.75rem;">Limit Tersedia:</span>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold text-white me-2" id="detail-limit" style="font-size: 0.85rem;">Rp 0</span>
                        <button type="button" id="link-request-limit"
                            class="badge rounded-pill bg-dark border border-secondary px-2 py-1 text-purple"
                            style="font-size: 0.68rem; color: #a855f7 !important; background: transparent; cursor: pointer;"
                            data-bs-toggle="modal" data-bs-target="#modal-ajuan-limit" data-customer-code=""
                            data-customer-name="" data-customer-limit="0">
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i>Ajukan Limit
                        </button>
                    </div>
                </div>
                <div id="overdue-warning-badge" class="mt-2 d-none">
                    <div class="alert alert-danger p-2 mb-0 d-flex align-items-center rounded-3"
                        style="font-size: 0.75rem; background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <span>Toko ini memiliki faktur jatuh tempo (Overdue)!</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('mobile.kunjungan.checkin') }}" method="POST" id="checkin-form">
                @csrf
                <input type="hidden" name="kode_pelanggan" id="hidden-kode-pelanggan" required>

                <button type="submit" id="btn-checkin" class="btn btn-mobile btn-mobile-primary w-100 py-3" disabled>
                    <i class="fa-solid fa-location-dot me-2"></i> Check-in Kunjungan
                </button>
            </form>
        </div>
    @endif

    @if ($activeCheckin)
        <!-- Histori Penjualan Pelanggan Check-in (5 Orderan Terakhir) -->
        <h5 class="fw-bold mb-3 mt-4" style="font-size: 0.95rem; letter-spacing: 0.5px;">5 Orderan Terakhir Toko Ini</h5>
        @if ($lastOrders->isEmpty())
            <div class="mobile-card text-center py-4">
                <i class="fa-solid fa-box-open text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                <p class="text-secondary mb-0" style="font-size: 0.8rem;">Belum ada riwayat order penjualan untuk toko
                    ini.</p>
            </div>
        @else
            @foreach ($lastOrders as $order)
                @php
                    $allPembayarans = $order->getAllPembayarans();
                    $totalBayar = $allPembayarans->sum('jumlah');
                    $sisaBayar = $order->grand_total - $totalBayar;
                    $dueDate = \Carbon\Carbon::parse($order->tanggal)->addDays($order->pelanggan->ljt ?? 14);
                    $isOverdue =
                        $sisaBayar > 0 &&
                        in_array($order->jenis_transaksi, ['K', 'Kredit']) &&
                        $dueDate->lt(\Carbon\Carbon::today());
                @endphp
                <!-- Collapsible Card Header -->
                <div class="mobile-card p-3 mb-2"
                    style="cursor: pointer; position: relative; background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08);"
                    data-bs-toggle="collapse"
                    data-bs-target="#kunj-details-{{ str_replace('-', '_', $order->no_faktur) }}" aria-expanded="false"
                    aria-controls="kunj-details-{{ str_replace('-', '_', $order->no_faktur) }}">
                    <div
                        class="d-flex justify-content-between align-items-start mb-2 border-bottom border-secondary border-opacity-10 pb-2">
                        <div>
                            <span class="text-white-50 font-monospace" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-file-invoice me-1 text-indigo"
                                    style="color: #818cf8 !important;"></i>{{ $order->no_faktur }}
                            </span>
                            <div class="text-secondary mt-0.5" style="font-size: 0.65rem;">
                                Tgl: {{ $order->tanggal->format('d/m/Y') }}
                                @if (in_array($order->jenis_transaksi, ['K', 'Kredit']))
                                    &bull; <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">JT:
                                        {{ $dueDate->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="mb-1 d-flex justify-content-end gap-1 flex-wrap">
                                <span
                                    class="badge {{ $order->jenis_transaksi === 'Tunai' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} px-2 py-1"
                                    style="font-size: 0.65rem; font-weight: 600;">
                                    {{ $order->jenis_transaksi }}
                                </span>
                                @if ($sisaBayar <= 0)
                                    <span class="badge bg-success-subtle text-success px-2 py-1"
                                        style="font-size: 0.65rem; font-weight: 600;">
                                        Lunas
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1"
                                        style="font-size: 0.65rem; font-weight: 600;">
                                        Belum Lunas
                                    </span>
                                    @if (in_array($order->jenis_transaksi, ['K', 'Kredit']))
                                        @if ($isOverdue)
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1"
                                                style="font-size: 0.65rem; font-weight: 600;">
                                                Jatuh Tempo
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info px-2 py-1"
                                                style="font-size: 0.65rem; font-weight: 600;">
                                                Belum JT
                                            </span>
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <div class="fw-bold text-info mt-1" style="font-size: 0.8rem;">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.7rem;">
                        <span class="text-secondary"><i class="fa-solid fa-angles-down me-1"
                                style="font-size: 0.65rem;"></i> Ketuk untuk rincian ({{ $order->details->count() }}
                            item)</span>
                    </div>
                </div>

                <!-- Collapsible detail drawer -->
                <div class="collapse" id="kunj-details-{{ str_replace('-', '_', $order->no_faktur) }}">
                    <div class="p-3 rounded-4 mb-3"
                        style="background-color: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); margin-top: -8px;">
                        <span class="text-secondary d-block mb-2 font-monospace"
                            style="font-size: 0.65rem; text-transform: uppercase;">Rincian Barang</span>
                        @foreach ($order->details as $detail)
                            <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary border-opacity-10"
                                style="font-size: 0.75rem;">
                                <div style="max-width: 70%;">
                                    <div class="fw-semibold text-white">{{ $detail->barang->nama_barang }}</div>
                                    <div class="text-secondary font-monospace mt-1" style="font-size: 0.65rem;">
                                        {{ $detail->qty }} {{ $detail->barangSatuan->satuan }} x Rp
                                        {{ number_format($detail->harga, 0, ',', '.') }}
                                    </div>
                                    @if ($detail->diskon1_persen > 0 || $detail->diskon2_persen > 0 || $detail->diskon3_persen > 0)
                                        <div class="text-danger" style="font-size: 0.65rem; font-weight: 500;">
                                            Disc:
                                            {{ $detail->diskon1_persen > 0 ? $detail->diskon1_persen . '%' : '' }}
                                            {{ $detail->diskon2_persen > 0 ? ' + ' . $detail->diskon2_persen . '%' : '' }}
                                            {{ $detail->diskon3_persen > 0 ? ' + ' . $detail->diskon3_persen . '%' : '' }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end fw-semibold text-white">
                                    Rp {{ number_format($detail->total, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach

                        <div class="pt-2 mt-1" style="font-size: 0.75rem;">
                            <div class="d-flex justify-content-between text-secondary mb-1">
                                <span>Subtotal:</span>
                                <span class="text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                            @if ($order->diskon > 0)
                                <div class="d-flex justify-content-between text-secondary mb-1">
                                    <span>Total Potongan:</span>
                                    <span class="text-danger">- Rp {{ number_format($order->diskon, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div
                                class="d-flex justify-content-between text-secondary mb-1 pt-1 border-top border-secondary border-opacity-10 mt-1">
                                <span>Total Tagihan:</span>
                                <span class="text-white fw-bold">Rp
                                    {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary mb-1">
                                <span>Total Terbayar:</span>
                                <span class="text-success">Rp {{ number_format($totalBayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary mb-1">
                                <span>Sisa Piutang:</span>
                                <span class="{{ $sisaBayar > 0 ? 'text-warning fw-bold' : 'text-secondary' }}">
                                    Rp {{ number_format($sisaBayar, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary mb-1">
                                <span>Status Bayar:</span>
                                <span
                                    class="{{ $sisaBayar <= 0 ? 'text-success fw-semibold' : 'text-warning fw-semibold' }}">
                                    {{ $sisaBayar <= 0 ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </div>
                            @if (in_array($order->jenis_transaksi, ['K', 'Kredit']))
                                <div class="d-flex justify-content-between text-secondary mb-1">
                                    <span>Jatuh Tempo:</span>
                                    <span class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-info fw-semibold' }}">
                                        {{ $dueDate->format('d/m/Y') }}
                                        @if ($sisaBayar > 0)
                                            @if ($isOverdue)
                                                (Overdue {{ (int) round(\Carbon\Carbon::today()->diffInDays($dueDate)) }}
                                                Hari)
                                            @else
                                                (Sisa
                                                {{ (int) round(\Carbon\Carbon::today()->diffInDays($dueDate, false) * -1) }}
                                                Hari)
                                            @endif
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if ($order->keterangan)
                                <div class="text-secondary mt-2 pt-2 border-top border-secondary border-opacity-10 mb-2"
                                    style="font-size: 0.7rem;">
                                    <i class="fa-solid fa-comment-dots me-1"></i> Catatan Order: <span
                                        class="text-white-50 italic">"{{ $order->keterangan }}"</span>
                                </div>
                            @endif

                            <!-- Riwayat Pembayaran -->
                            @if ($allPembayarans->isNotEmpty())
                                <div class="mt-3 pt-2 border-top border-secondary border-opacity-10">
                                    <span class="text-secondary d-block mb-1-5 font-monospace"
                                        style="font-size: 0.65rem; text-transform: uppercase;">Riwayat Pembayaran</span>
                                    @foreach ($allPembayarans as $bayar)
                                        <div class="p-2 rounded-3 mb-1 d-flex justify-content-between align-items-center"
                                            style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255,255,255,0.03); font-size: 0.75rem;">
                                            <div>
                                                <span class="d-block text-white-50 font-monospace"
                                                    style="font-size: 0.65rem;">{{ $bayar->no_bukti }}</span>
                                                <span class="text-secondary" style="font-size: 0.65rem;">
                                                    {{ $bayar->tanggal->format('d/m/Y') }} &bull;
                                                    {{ $bayar->jenis_bayar }}
                                                </span>
                                                @if ($bayar->keterangan)
                                                    <small class="d-block text-muted mt-0.5"
                                                        style="font-size: 0.65rem;">Ket:
                                                        "{{ $bayar->keterangan }}"</small>
                                                @endif
                                            </div>
                                            <div class="text-success fw-semibold" style="font-size: 0.8rem;">
                                                Rp {{ number_format($bayar->jumlah, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Info Sisa Tagihan (Sales tidak bisa input pembayaran) -->
                            @if ($order->batal !== 1 && $sisaBayar > 0)
                                <div class="mt-3 pt-2 border-top border-secondary border-opacity-10">
                                    <div class="p-2 rounded-3 d-flex align-items-center gap-2"
                                        style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.2);">
                                        <i class="fa-solid fa-clock-rotate-left text-warning" style="font-size: 0.75rem;"></i>
                                        <div>
                                            <span class="text-warning fw-semibold d-block" style="font-size: 0.7rem;">Belum Lunas</span>
                                            <span class="text-secondary" style="font-size: 0.65rem;">
                                                Sisa tagihan: <strong class="text-white font-monospace">Rp {{ number_format($sisaBayar, 0, ',', '.') }}</strong>
                                                &bull; Pembayaran dilakukan oleh admin/kasir.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endif

    <!-- Today's Visits Log -->
    <h5 class="fw-bold mb-3 mt-4" style="font-size: 0.95rem; letter-spacing: 0.5px;">Kunjungan Hari Ini</h5>
    @if ($todayVisits->isEmpty())
        <div class="mobile-card text-center py-4">
            <i class="fa-solid fa-clipboard-check text-secondary mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
            <p class="text-secondary mb-0" style="font-size: 0.8rem;">Belum ada kunjungan selesai hari ini.</p>
        </div>
    @else
        @foreach ($todayVisits as $visit)
            <div class="mobile-card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-semibold mb-1" style="font-size: 0.85rem;">{{ $visit->pelanggan->nama_pelanggan }}
                        </h6>
                        <span class="text-secondary" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-clock me-1"></i> {{ $visit->checkin->format('H:i') }} -
                            {{ $visit->checkout->format('H:i') }}
                        </span>
                    </div>
                    <div>
                        <span class="badge rounded-pill bg-success-subtle text-success px-2 py-1"
                            style="font-size: 0.65rem; font-weight: 600;">Selesai</span>
                    </div>
                </div>
                @if ($visit->catatan)
                    <div class="p-2 rounded-3 mt-2 text-secondary"
                        style="background-color: rgba(255,255,255,0.02); font-size: 0.75rem; border: 1px solid rgba(255,255,255,0.05);">
                        <i class="fa-regular fa-comment-dots me-1 text-indigo"></i> "{{ $visit->catatan }}"
                    </div>
                @endif
            </div>
        @endforeach
    @endif



    <!-- Modal Ajukan Limit Kredit -->
    <div class="modal fade" id="modal-ajuan-limit" tabindex="-1" aria-labelledby="modalAjuanLimitLabel"
        aria-hidden="true" style="backdrop-filter: blur(10px);">
        <div class="modal-dialog modal-dialog-centered px-3">
            <div class="modal-content border-0 rounded-4"
                style="background-color: #161e31; border: 1px solid rgba(255, 255, 255, 0.08) !important; color: var(--text-primary);">
                <div class="modal-header border-bottom border-secondary border-opacity-10 py-3">
                    <h6 class="modal-title fw-bold text-white" id="modalAjuanLimitLabel">
                        <i class="fa-solid fa-file-invoice-dollar text-purple me-2"
                            style="color: #a855f7 !important;"></i>Ajukan Limit Kredit Baru
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form action="{{ route('mobile.limit-kredit.store') }}" method="POST" id="form-modal-ajuan-limit">
                    @csrf
                    <input type="hidden" name="kode_pelanggan" id="modal-limit-kode-pelanggan" required>

                    <div class="modal-body py-3">
                        <!-- Customer Name Display -->
                        <div class="mb-3">
                            <span class="text-secondary small d-block mb-1"
                                style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.5px;">TOKO PELANGGAN</span>
                            <h6 class="fw-bold text-white mb-0" id="modal-limit-nama-pelanggan"
                                style="font-size: 0.85rem;">-</h6>
                        </div>

                        <!-- Current Limit Display Card -->
                        <div class="p-3 rounded-4 mb-3"
                            style="background-color: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small" style="font-size: 0.7rem;">Limit Kredit Saat
                                    Ini:</span>
                                <span class="fw-bold text-white" id="modal-limit-lbl-lama" style="font-size: 0.8rem;">Rp
                                    0</span>
                            </div>
                        </div>

                        <!-- New Limit Input -->
                        <div class="mb-3">
                            <label for="modal-limit-baru" class="form-label text-secondary small fw-semibold"
                                style="letter-spacing: 0.5px; font-size: 0.65rem;">LIMIT KREDIT BARU (RP)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text text-purple"
                                    style="font-size: 0.75rem; background-color: rgba(18, 24, 36, 0.9); border: 1px solid rgba(255, 255, 255, 0.12); border-right: none; font-weight: 600; padding: 5px 8px; border-top-left-radius: 8px; border-bottom-left-radius: 8px;">Rp</span>
                                <input type="number" name="limit_baru" id="modal-limit-baru"
                                    class="form-control form-control-sm text-white rupiah-input" min="0"
                                    placeholder="Contoh: 15.000.000" required
                                    style="font-size: 0.75rem; padding: 5px 8px; border: 1px solid rgba(255, 255, 255, 0.12) !important; border-left: none !important; background-color: rgba(18, 24, 36, 0.8) !important; border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                            </div>
                            <div class="text-info small mt-1" id="modal-limit-baru-helper"
                                style="font-size: 0.72rem; min-height: 18px;"></div>
                        </div>

                        <!-- Reason / Alasan -->
                        <div class="mb-2">
                            <label for="modal-limit-alasan" class="form-label text-secondary small fw-semibold"
                                style="letter-spacing: 0.5px; font-size: 0.65rem;">ALASAN PENGAJUAN</label>
                            <textarea name="alasan" id="modal-limit-alasan" rows="3" class="form-control form-control-sm text-white"
                                placeholder="Jelaskan mengapa toko ini membutuhkan kenaikan limit kredit..." required
                                style="font-size: 0.75rem; padding: 6px 10px; background-color: rgba(18, 24, 36, 0.8) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important; border-radius: 8px;"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top border-secondary border-opacity-10 py-2.5">
                        <button type="button"
                            class="btn btn-sm btn-outline-secondary px-3 py-1.5 text-white border-secondary border-opacity-50"
                            style="font-size: 0.75rem; border-radius: 8px;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm text-white border-0"
                            style="font-size: 0.75rem; font-weight: 600; background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%); box-shadow: 0 4px 10px rgba(168, 85, 247, 0.2); border-radius: 8px;">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Active check-in Timer
            const checkinTimeEl = document.getElementById('checkin-time');
            const durationCounterEl = document.getElementById('duration-counter');

            // Active Customer Info chevron rotation
            const activeInfoCollapse = document.getElementById('active-customer-info');
            const activeInfoChevron = document.getElementById('active-info-chevron');
            if (activeInfoCollapse && activeInfoChevron) {
                activeInfoCollapse.addEventListener('show.bs.collapse', function() {
                    activeInfoChevron.style.transform = 'rotate(180deg)';
                });
                activeInfoCollapse.addEventListener('hide.bs.collapse', function() {
                    activeInfoChevron.style.transform = 'rotate(0deg)';
                });
            }

            if (checkinTimeEl && durationCounterEl) {
                const checkinTime = new Date(checkinTimeEl.getAttribute('data-time'));

                function updateTimer() {
                    const now = new Date();
                    const diffMs = now - checkinTime;

                    if (diffMs < 0) return;

                    const secs = Math.floor(diffMs / 1000) % 60;
                    const mins = Math.floor(diffMs / (1000 * 60)) % 60;
                    const hours = Math.floor(diffMs / (1000 * 60 * 60));

                    const formatted =
                        String(hours).padStart(2, '0') + ':' +
                        String(mins).padStart(2, '0') + ':' +
                        String(secs).padStart(2, '0');

                    durationCounterEl.innerText = formatted;
                }

                updateTimer();
                setInterval(updateTimer, 1000);
            }

            // Live Autocomplete Search for Check-in
            const searchInput = document.getElementById('customer-search-input');
            const resultsList = document.getElementById('search-results-list');
            const selectedDetail = document.getElementById('selected-customer-detail');
            const hiddenKode = document.getElementById('hidden-kode-pelanggan');
            const btnCheckin = document.getElementById('btn-checkin');
            const searchWilayah = document.getElementById('search-wilayah-select');

            if (searchInput) {
                let debounceTimer;

                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsList.classList.add('d-none');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        const wilayahVal = searchWilayah ? searchWilayah.value : '';
                        fetch(
                                `{{ route('pelanggan.search') }}?q=${encodeURIComponent(query)}&kode_wilayah=${encodeURIComponent(wilayahVal)}`
                                )
                            .then(response => response.json())
                            .then(data => {
                                resultsList.innerHTML = '';
                                if (data.length === 0) {
                                    resultsList.innerHTML =
                                        '<div class="p-3 text-secondary text-center" style="font-size: 0.8rem;">Toko tidak ditemukan.</div>';
                                    resultsList.classList.remove('d-none');
                                    return;
                                }

                                data.forEach(item => {
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className =
                                        'list-group-item list-group-item-action text-white border-0 py-3 px-3 d-flex flex-column';
                                    btn.style.backgroundColor = 'transparent';
                                    btn.style.borderBottom =
                                        '1px solid rgba(255,255,255,0.05) !important';
                                    btn.innerHTML = `
                                    <span class="fw-semibold text-white" style="font-size: 0.85rem;">${item.text}</span>
                                    <span class="text-secondary mt-1" style="font-size: 0.75rem;">${item.alamat}</span>
                                `;
                                    btn.addEventListener('click', () => {
                                        selectCustomer(item);
                                    });
                                    resultsList.appendChild(btn);
                                });
                                resultsList.classList.remove('d-none');
                            })
                            .catch(err => {
                                console.error('Error searching customers:', err);
                            });
                    }, 300);
                });

                if (searchWilayah) {
                    $(searchWilayah).select2({
                        placeholder: "-- Semua Wilayah --",
                        allowClear: true
                    }).on('change', function() {
                        if (searchInput.value.trim().length >= 2) {
                            searchInput.dispatchEvent(new Event('input'));
                        }
                    });
                }

                // Close search dropdown on click outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
                        resultsList.classList.add('d-none');
                    }
                });
            }

            function selectCustomer(customer) {
                resultsList.classList.add('d-none');
                searchInput.value = customer.text;
                hiddenKode.value = customer.id;

                // Populate details Card
                document.getElementById('detail-name').innerText = customer.text;
                document.getElementById('detail-code').innerText = customer.id;
                document.getElementById('detail-phone').innerText = customer.hp;
                document.getElementById('detail-address').innerText = customer.alamat;
                document.getElementById('detail-metode').innerText = customer.metode;
                document.getElementById('detail-limit').innerText = 'Rp ' + Number(customer.sisa_limit)
                    .toLocaleString('id-ID');

                // Update Ajukan Limit Modal data attributes
                const requestLimitBtn = document.getElementById('link-request-limit');
                if (requestLimitBtn) {
                    requestLimitBtn.setAttribute('data-customer-code', customer.id);
                    requestLimitBtn.setAttribute('data-customer-name', customer.text);
                    requestLimitBtn.setAttribute('data-customer-limit', customer.limit || 0);
                }

                // Show overdue warning badge
                const warningBadge = document.getElementById('overdue-warning-badge');
                if (customer.has_overdue === 1) {
                    warningBadge.classList.remove('d-none');
                } else {
                    warningBadge.classList.add('d-none');
                }

                // Show detail card and enable submit
                selectedDetail.classList.remove('d-none');
                if (btnCheckin) {
                    btnCheckin.removeAttribute('disabled');
                }
            }



            // Modal Ajukan Limit Kredit Logic
            const modalAjuanLimit = document.getElementById('modal-ajuan-limit');
            if (modalAjuanLimit) {
                modalAjuanLimit.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const code = button.getAttribute('data-customer-code') || '';
                    const name = button.getAttribute('data-customer-name') || '';
                    const limit = parseFloat(button.getAttribute('data-customer-limit')) || 0;

                    document.getElementById('modal-limit-kode-pelanggan').value = code;
                    document.getElementById('modal-limit-nama-pelanggan').innerText = name;
                    document.getElementById('modal-limit-lbl-lama').innerText = 'Rp ' + limit
                        .toLocaleString('id-ID');

                    const inputLimitBaru = document.getElementById('modal-limit-baru');
                    inputLimitBaru.value = '';
                    inputLimitBaru.dispatchEvent(new Event('input'));

                    document.getElementById('modal-limit-alasan').value = '';
                });

                const inputLimitBaru = document.getElementById('modal-limit-baru');
                const limitBaruHelper = document.getElementById('modal-limit-baru-helper');

                inputLimitBaru.addEventListener('input', function() {
                    const rawVal = this.value.replace(/\./g, '').replace(/,/g, '.').trim();
                    if (rawVal && !isNaN(rawVal)) {
                        const parsed = parseFloat(rawVal);
                        limitBaruHelper.innerText = 'Format Terbaca: Rp ' + parsed.toLocaleString('id-ID');
                    } else {
                        limitBaruHelper.innerText = '';
                    }
                });
            }
        });
    </script>
@endpush
