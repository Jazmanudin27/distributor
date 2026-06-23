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

    <!-- Filters (Visible directly, no collapse) -->
    <div class="mobile-card p-3 mb-3">
        <form action="{{ route('mobile.owner.order.index') }}" method="GET" id="filterForm">
            <div class="row g-2">
                <!-- Search input -->
                <div class="col-12 mb-1">
                    <label class="form-label text-secondary small fw-semibold mb-1">Cari Kata Kunci</label>
                    <div class="input-group">
                        <input type="text" name="q" value="{{ $q }}"
                            class="form-control form-control-mobile py-2" placeholder="No. Faktur / Pelanggan...">
                        <button type="submit" class="btn btn-mobile-primary px-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>

                <!-- Sales filter -->
                <div class="col-6">
                    <label class="form-label text-secondary small fw-semibold mb-1">Pilih Sales</label>
                    <select name="kode_sales" class="form-select form-control-mobile py-2 auto-submit">
                        <option value="">-- Semua Sales --</option>
                        @foreach ($salesmen as $sales)
                            <option value="{{ $sales->nik }}" {{ $selected_sales == $sales->nik ? 'selected' : '' }}>
                                {{ $sales->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Waktu filter -->
                <div class="col-6">
                    <label class="form-label text-secondary small fw-semibold mb-1">Pilih Waktu</label>
                    <select name="filter" class="form-select form-control-mobile py-2 auto-submit">
                        <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>Semua Waktu</option>
                        <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                    </select>
                </div>

                <!-- Tipe Laporan filter -->
                <div class="col-12 mt-2">
                    <label class="form-label text-secondary small fw-semibold mb-1">Model Tampilan</label>
                    <select name="jenis_laporan" class="form-select form-control-mobile py-2 auto-submit">
                        <option value="detail" {{ $jenis_laporan == 'detail' ? 'selected' : '' }}>Rincian Per Faktur (Detail)</option>
                        <option value="rekap" {{ $jenis_laporan == 'rekap' ? 'selected' : '' }}>Total Per Sales (Rekap)</option>
                    </select>
                </div>
            </div>
        </form>
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
                                <span class="text-secondary d-block" style="font-size: 0.65rem; text-transform: uppercase;">Total Penjualan</span>
                                <span class="fw-bold text-info" style="font-size: 0.95rem;">Rp {{ number_format($rs->total_sales, 0, ',', '.') }}</span>
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
                        $dueDate = \Carbon\Carbon::parse($order->tanggal)->addDays($order->pelanggan ? ($order->pelanggan->ljt ?? 30) : 30);
                        $isOverdue =
                            $sisaBayar >= 1 &&
                            in_array($order->jenis_transaksi, ['K', 'Kredit']) &&
                            $dueDate->lt(\Carbon\Carbon::today());
                    @endphp
                    <div class="mobile-card p-3 mb-3">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start mb-2 border-bottom border-secondary border-opacity-10 pb-2">
                            <div>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">
                                    {{ $order->pelanggan ? $order->pelanggan->nama_pelanggan : 'Umum / Tanpa Pelanggan' }}
                                </h6>
                                <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                    {{ $order->no_faktur }}
                                </span>
                                <div class="text-secondary mt-0.5" style="font-size: 0.65rem;">
                                    Tgl: {{ $order->tanggal ? $order->tanggal->format('d M Y') : '-' }}
                                    @if (in_array($order->jenis_transaksi, ['K', 'Kredit']))
                                        &bull; <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">JT: {{ $dueDate->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $order->jenis_transaksi === 'Tunai' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} px-2 py-0.5" style="font-size: 0.65rem;">
                                    {{ $order->jenis_transaksi }}
                                </span>
                                @if ($order->batal === 1)
                                    <span class="badge bg-danger px-2 py-0.5 d-block mt-1" style="font-size: 0.6rem;">Batal</span>
                                @else
                                    <span class="badge {{ $sisaBayar < 1 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} px-2 py-0.5 d-block mt-1" style="font-size: 0.65rem;">
                                        {{ $sisaBayar < 1 ? 'Lunas' : 'Belum Lunas' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Items List (Directly visible!) -->
                        <div class="py-1 mb-2" style="font-size: 0.75rem;">
                            <div class="text-secondary font-monospace mb-1.5" style="font-size: 0.65rem; text-transform: uppercase;">Barang Belanja:</div>
                            @foreach ($order->details as $detail)
                                <div class="d-flex justify-content-between text-white-50 py-0.5 border-bottom border-secondary border-opacity-5">
                                    <span>{{ $detail->barang ? $detail->barang->nama_barang : 'Barang Terhapus' }} ({{ $detail->qty }} {{ $detail->barangSatuan ? $detail->barangSatuan->satuan : 'PCS' }})</span>
                                    <span>Rp {{ number_format($detail->total, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Totals Summary -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-10" style="font-size: 0.75rem;">
                            <span class="text-secondary">Sales: <span class="text-white-50">{{ $order->sales ? $order->sales->name : ($order->user ? $order->user->name : '-') }}</span></span>
                            <span class="fw-bold text-info">Total: Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
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

    @if(config('app.debug') || (Auth::check() && (in_array(strtolower(Auth::user()->role ?? ''), ['owner', 'admin', 'super admin', 'superadmin']) || Auth::user()->name === 'kasir')))
        <!-- Diagnostic Box -->
        <div class="mt-4 p-3 rounded text-warning" style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.2); font-size: 0.72rem; font-family: monospace;">
            <div class="fw-bold mb-2 border-bottom border-warning border-opacity-25 pb-1"><i class="fa-solid fa-bug me-1"></i> DIAGNOSTIC INFO (Owner/Admin Only)</div>
            <div class="row g-2">
                <div class="col-6">Database Name: <span class="text-white">{{ DB::connection()->getDatabaseName() }}</span></div>
                <div class="col-6">Total Penjualan: <span class="text-white">{{ \App\Models\Penjualan::count() }}</span></div>
                <div class="col-6">Total Users: <span class="text-white">{{ \App\Models\User::count() }}</span></div>
                <div class="col-6">Total Salesmen: <span class="text-white">{{ $salesmen->count() }}</span></div>
                <div class="col-6">Current Filter: <span class="text-white">{{ $filter }}</span></div>
                <div class="col-6">Selected Sales: <span class="text-white">{{ $selected_sales ?: '-' }}</span></div>
                <div class="col-6">Search Query: <span class="text-white">{{ $q ?: '-' }}</span></div>
                <div class="col-6">Tipe Laporan: <span class="text-white">{{ $jenis_laporan }}</span></div>
                <div class="col-12 mt-1 pt-1 border-top border-warning border-opacity-10">
                    <div>PHP Time: <span class="text-white">{{ now()->toDateTimeString() }} ({{ config('app.timezone') }})</span></div>
                    <div>SQL Time: <span class="text-white">{{ DB::select("SELECT NOW() as now")[0]->now ?? '-' }}</span></div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto submit form on select input change
            $('.auto-submit').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endpush
