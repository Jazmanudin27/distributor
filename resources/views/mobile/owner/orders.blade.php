@extends('layouts.mobile')

@section('title', 'Daftar Orderan Sales')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.owner.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Daftar Orderan</h5>
    </div>

    <!-- Summary Section -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <div class="mobile-card m-0 p-3 text-center"
                style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                <div class="text-secondary mb-1" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">
                    Hari Ini</div>
                <h6 class="fw-bold text-success mb-0" style="font-size: 0.95rem;">Rp
                    {{ number_format($todaySales, 0, ',', '.') }}</h6>
            </div>
        </div>
        <div class="col-6">
            <div class="mobile-card m-0 p-3 text-center"
                style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                <div class="text-secondary mb-1" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 600;">
                    Bulan Ini</div>
                <h6 class="fw-bold text-indigo mb-0" style="font-size: 0.95rem; color: #818cf8;">Rp
                    {{ number_format($monthSales, 0, ',', '.') }}</h6>
            </div>
        </div>
    </div>

    <!-- Filter Toggle and Form -->
    <div class="mobile-card p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse"
            data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
            <h6 class="fw-bold mb-0 text-white" style="font-size: 0.85rem;"><i
                    class="fa-solid fa-sliders me-1.5 text-primary"></i> Filter Pencarian</h6>
            <i class="fa-solid fa-chevron-down text-secondary" id="filter-chevron" style="transition: transform 0.2s;"></i>
        </div>
        <div class="collapse" id="filterCollapse">
            <form action="{{ route('mobile.owner.order.index') }}" method="GET" class="mt-3">
                <input type="hidden" name="filter" value="{{ $filter }}">

                <div class="mb-2">
                    <label class="form-label text-secondary small fw-semibold mb-1">Cari No. Faktur / Sales</label>
                    <input type="text" name="q" value="{{ $q }}"
                        class="form-control form-control-mobile py-2" placeholder="Ketik kata kunci...">
                </div>

                <div class="mb-2">
                    <label class="form-label text-secondary small fw-semibold mb-1">Filter Sales</label>
                    <select name="kode_sales" class="form-select form-control-mobile py-2">
                        <option value="">-- Semua Sales --</option>
                        @foreach ($salesmen as $sales)
                            <option value="{{ $sales->nik }}" {{ $selected_sales == $sales->nik ? 'selected' : '' }}>
                                {{ $sales->name }} ({{ $sales->nik }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold mb-1">Jenis Laporan</label>
                    <select name="jenis_laporan" class="form-select form-control-mobile py-2">
                        <option value="detail" {{ $jenis_laporan == 'detail' ? 'selected' : '' }}>Detail Orderan</option>
                        <option value="rekap" {{ $jenis_laporan == 'rekap' ? 'selected' : '' }}>Rekap per Salesman
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('mobile.owner.order.index') }}"
                        class="btn btn-mobile btn-outline-secondary w-50 py-2 fw-semibold fs-8.5 border-secondary text-secondary">Reset</a>
                    <button type="submit" class="btn btn-mobile btn-mobile-primary w-50 py-2 fw-semibold fs-8.5">
                        <i class="fa-solid fa-filter me-1.5"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="d-flex gap-2 mb-3 pb-1" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <a href="{{ route('mobile.owner.order.index', ['filter' => 'all', 'q' => $q, 'kode_sales' => $selected_sales, 'jenis_laporan' => $jenis_laporan]) }}"
            class="btn btn-sm px-3 py-1.5 rounded-pill text-decoration-none fw-semibold {{ $filter === 'all' ? 'btn-mobile-primary' : 'bg-dark border-secondary text-secondary' }}"
            style="font-size: 0.75rem;">
            Semua Waktu
        </a>
        <a href="{{ route('mobile.owner.order.index', ['filter' => 'today', 'q' => $q, 'kode_sales' => $selected_sales, 'jenis_laporan' => $jenis_laporan]) }}"
            class="btn btn-sm px-3 py-1.5 rounded-pill text-decoration-none fw-semibold {{ $filter === 'today' ? 'btn-mobile-primary' : 'bg-dark border-secondary text-secondary' }}"
            style="font-size: 0.75rem;">
            Hari Ini
        </a>
        <a href="{{ route('mobile.owner.order.index', ['filter' => 'month', 'q' => $q, 'kode_sales' => $selected_sales, 'jenis_laporan' => $jenis_laporan]) }}"
            class="btn btn-sm px-3 py-1.5 rounded-pill text-decoration-none fw-semibold {{ $filter === 'month' ? 'btn-mobile-primary' : 'bg-dark border-secondary text-secondary' }}"
            style="font-size: 0.75rem;">
            Bulan Ini
        </a>
    </div>

    <!-- Orders List -->
    <div class="orders-list">
        @if ($jenis_laporan === 'rekap')
            @if ($rekapSales->isEmpty())
                <div class="mobile-card text-center py-5">
                    <i class="fa-solid fa-box-open text-secondary mb-3" style="font-size: 2.5rem; opacity: 0.4;"></i>
                    <p class="text-secondary mb-0">Tidak ada rekap penjualan ditemukan.</p>
                </div>
            @else
                @foreach ($rekapSales as $rs)
                    <div class="mobile-card p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-white mb-1" style="font-size: 0.9rem;">
                                    {{ $rs->sales_name }}
                                </h6>
                                <span class="text-secondary font-monospace" style="font-size: 0.7rem;">
                                    NIK: {{ $rs->kode_sales ?? '-' }}
                                </span>
                                <div class="text-secondary mt-1" style="font-size: 0.7rem;">
                                    Jumlah Orderan: <span class="text-white fw-semibold">{{ $rs->order_count }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-secondary d-block"
                                    style="font-size: 0.65rem; text-transform: uppercase;">Total Penjualan</span>
                                <span class="fw-bold text-info" style="font-size: 0.95rem;">Rp
                                    {{ number_format($rs->total_sales, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @else
            @if ($orders->isEmpty())
                <div class="mobile-card text-center py-5">
                    <i class="fa-solid fa-box-open text-secondary mb-3" style="font-size: 2.5rem; opacity: 0.4;"></i>
                    <p class="text-secondary mb-0">Tidak ada riwayat penjualan ditemukan.</p>
                </div>
            @else
                @foreach ($orders as $order)
                    @php
                        $totalBayar = $order->getApprovedPembayaranTotal();
                        $totalRetur = $order->getTotalRetur();
                        $sisaBayar = $order->grand_total - $totalBayar - $totalRetur;
                        $dueDate = \Carbon\Carbon::parse($order->tanggal)->addDays($order->pelanggan->ljt ?? 30);
                        $isOverdue =
                            $sisaBayar >= 1 &&
                            in_array($order->jenis_transaksi, ['K', 'Kredit']) &&
                            $dueDate->lt(\Carbon\Carbon::today());
                        $allPembayarans = $order->getAllPembayarans();
                    @endphp
                    <!-- Order Card header (tappable for collapse) -->
                    <div class="mobile-card p-3 mb-2" style="cursor: pointer; position: relative;"
                        data-bs-toggle="collapse"
                        data-bs-target="#details-{{ str_replace('-', '_', $order->no_faktur) }}" aria-expanded="false"
                        aria-controls="details-{{ str_replace('-', '_', $order->no_faktur) }}">

                        <div
                            class="d-flex justify-content-between align-items-start mb-2 border-bottom border-secondary border-opacity-10 pb-2">
                            <div>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.9rem;">
                                    {{ $order->pelanggan->nama_pelanggan }}
                                </h6>
                                <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-file-invoice me-1"></i>{{ $order->no_faktur }}
                                </span>
                                <div class="text-secondary mt-0.5" style="font-size: 0.65rem;">
                                    Tgl: {{ $order->tanggal->format('d M Y') }}
                                    @if (in_array($order->jenis_transaksi, ['K', 'Kredit']))
                                        &bull; <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">JT:
                                            {{ $dueDate->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-1 d-flex justify-content-end gap-1 flex-wrap">
                                    @if ($order->batal === 1)
                                        <span class="badge bg-danger px-2 py-1" style="font-size: 0.6rem;">Batal</span>
                                    @else
                                        <span
                                            class="badge {{ $order->jenis_transaksi === 'Tunai' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} px-2 py-1"
                                            style="font-size: 0.65rem; font-weight: 600;">
                                            {{ $order->jenis_transaksi }}
                                        </span>
                                        @if ($sisaBayar < 1)
                                            <span class="badge bg-success-subtle text-success px-2 py-1"
                                                style="font-size: 0.65rem; font-weight: 600;">
                                                Lunas
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1"
                                                style="font-size: 0.65rem; font-weight: 600;">
                                                Belum Lunas
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                <div class="text-secondary mt-1" style="font-size: 0.65rem; font-weight: 500;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $order->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Info Details: Sales, Region, Address -->
                        <div class="mb-2" style="font-size: 0.75rem; line-height: 1.5;">
                            <div class="row g-2 mb-1">
                                <div class="col-4 text-secondary">Sales/User:</div>
                                <div class="col-8 text-end text-white-50">
                                    {{ $order->sales ? $order->sales->name : ($order->user ? $order->user->name : '-') }}
                                    <span class="text-secondary font-monospace">({{ $order->kode_sales ?? '-' }})</span>
                                </div>
                            </div>
                            <div class="row g-2 mb-1">
                                <div class="col-4 text-secondary">Wilayah:</div>
                                <div class="col-8 text-end text-white-50">
                                    {{ $order->pelanggan->wilayah ? $order->pelanggan->wilayah->nama_wilayah : '-' }}
                                    @if ($order->pelanggan->subWilayah)
                                        / {{ $order->pelanggan->subWilayah->nama_wilayah }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-20 mt-2"
                            style="font-size: 0.75rem;">
                            <span class="text-secondary"><i class="fa-solid fa-angles-down me-1"
                                    style="font-size: 0.7rem;"></i>
                                Ketuk untuk detail ({{ $order->details->count() }} item)</span>
                            <span class="fw-bold text-info">Rp
                                {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Collapsible detail drawer -->
                    <div class="collapse" id="details-{{ str_replace('-', '_', $order->no_faktur) }}">
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
                                        <span class="text-danger">- Rp
                                            {{ number_format($order->diskon, 0, ',', '.') }}</span>
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
                                    <span class="{{ $sisaBayar >= 1 ? 'text-warning fw-bold' : 'text-secondary' }}">
                                        Rp {{ number_format($sisaBayar >= 1 ? $sisaBayar : 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-secondary mb-1">
                                    <span>Status Bayar:</span>
                                    <span
                                        class="{{ $sisaBayar < 1 ? 'text-success fw-semibold' : 'text-warning fw-semibold' }}">
                                        {{ $sisaBayar < 1 ? 'Lunas' : 'Belum Lunas' }}
                                    </span>
                                </div>
                                @if (in_array($order->jenis_transaksi, ['K', 'Kredit']))
                                    <div class="d-flex justify-content-between text-secondary mb-1">
                                        <span>Jatuh Tempo:</span>
                                        <span
                                            class="{{ $isOverdue ? 'text-danger fw-semibold' : 'text-info fw-semibold' }}">
                                            {{ $dueDate->format('d/m/Y') }}
                                            @if ($sisaBayar >= 1)
                                                @if ($isOverdue)
                                                    (Overdue
                                                    {{ (int) round(\Carbon\Carbon::today()->diffInDays($dueDate)) }}
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
                                        <span class="text-secondary d-block mb-1.5 font-monospace"
                                            style="font-size: 0.65rem; text-transform: uppercase;">Riwayat
                                            Pembayaran</span>
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
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Pagination Links -->
                <div class="d-flex justify-content-center mt-4 mb-2">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Rotate chevron when collapse is toggled
            $('#filterCollapse').on('show.bs.collapse', function() {
                $('#filter-chevron').css('transform', 'rotate(180deg)');
            });
            $('#filterCollapse').on('hide.bs.collapse', function() {
                $('#filter-chevron').css('transform', 'rotate(0deg)');
            });

            // Open collapse automatically if any filter is active
            @if ($q || $selected_sales || (isset($jenis_laporan) && $jenis_laporan !== 'detail'))
                $('#filterCollapse').addClass('show');
                $('#filter-chevron').css('transform', 'rotate(180deg)');
            @endif
        });
    </script>
@endpush
