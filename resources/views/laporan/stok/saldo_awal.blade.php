@extends('layouts.app')
@section('title', 'Setting & Generate Saldo Awal Stok Barang')
@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-12">
            <div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header card-premium-header text-white py-4 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-white">
                            <i class="fa-solid fa-calculator me-2"></i> Setting & Generate Saldo Awal Stok Barang
                        </h4>
                        <p class="text-white-50 small mb-0">
                            Atur nilai saldo awal persediaan stok barang sebagai acuan awal pada Rekap Persediaan dan Kartu Stok.
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('laporan.stok') }}" class="btn btn-light btn-sm fw-semibold shadow-sm rounded-pill px-3">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Laporan Stok
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- FILTER BAR --}}
                    <form action="{{ route('laporan.stok.saldo-awal.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Tanggal Saldo Awal</label>
                            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ request('tanggal', $tanggalSaldoAwal) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Kategori</label>
                            <select name="kategori" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Kategori --</option>
                                @foreach ($kategoris as $k)
                                    <option value="{{ $k->nama_kategori }}" {{ request('kategori') == $k->nama_kategori ? 'selected' : '' }}>
                                        {{ $k->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Merk</label>
                            <select name="merk" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Merk --</option>
                                @foreach ($merks as $m)
                                    <option value="{{ $m->nama_merk }}" {{ request('merk') == $m->nama_merk ? 'selected' : '' }}>
                                        {{ $m->nama_merk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Cari Barang</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Kode / Nama barang..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary fw-semibold">
                                    <i class="fa-solid fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tampilkan_stok_kosong" id="tampilkan_stok_kosong" value="1" {{ request('tampilkan_stok_kosong') == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label fw-semibold text-secondary small" for="tampilkan_stok_kosong">
                                    Tampilkan Barang Stok 0
                                </label>
                            </div>
                        </div>
                    </form>

                    <hr class="text-muted my-3 opacity-25">

                    {{-- FORM SAVE SALDO AWAL --}}
                    <form action="{{ route('laporan.stok.saldo-awal.store') }}" method="POST" id="formSaldoAwal">
                        @csrf
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-light p-3 rounded-3 border mb-3 gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <label class="form-label fw-bold text-dark mb-0 me-2">Tanggal Efektif Saldo Awal:</label>
                                    <input type="date" name="tanggal" class="form-control form-control-sm d-inline-block w-auto fw-bold text-primary" 
                                           value="{{ request('tanggal', $tanggalSaldoAwal) }}" required>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" id="btnAutoGenerateHitungMundur" class="btn btn-info text-white btn-sm fw-bold shadow-sm rounded-3">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Fill (Stok Saat Ini - Masuk + Keluar)
                                </button>
                                <button type="submit" class="btn btn-success btn-sm fw-bold px-4 shadow-sm rounded-3">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Saldo Awal
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold shadow-sm rounded-3" 
                                        onclick="if(confirm('Apakah Anda yakin ingin memulihkan/menyinkronkan kembali stok fisik real-time barang dari riwayat transaksi & opname?')) document.getElementById('formRecalculateStok').submit();">
                                    <i class="fa-solid fa-rotate me-1"></i> Pulihkan Stok Real-Time
                                </button>
                            </div>
                        </div>

                        <form action="{{ route('laporan.stok.saldo-awal.recalculate') }}" method="POST" id="formRecalculateStok" class="d-none">
                            @csrf
                        </form>

                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-info fs-6"></i>
                            <div>
                                <strong>Rumus Hitung Mundur Saldo Awal:</strong> 
                                <span class="badge bg-white text-dark border ms-1 font-monospace">Saldo Awal = Stok Saat Ini &minus; Total Penerimaan (Masuk) &plus; Total Pengeluaran (Keluar)</span>
                                sejak tanggal {{ \Carbon\Carbon::parse($tanggalSaldoAwal)->format('d/m/Y') }}.
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle text-sm">
                                <thead class="table-dark text-center align-middle">
                                    <tr>
                                        <th width="40">No</th>
                                        <th width="120">Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th width="130">Kategori / Merk</th>
                                        <th width="150">Stok Saat Ini</th>
                                        <th width="170">Mutasi Sejak Tgl Ini (Masuk / Keluar)</th>
                                        <th width="170">Estimasi Saldo Awal</th>
                                        <th width="280">Input Saldo Awal Baru (Per Satuan / UOM)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($barangs as $index => $b)
                                        @php
                                            $m = $mutasiPeriod->get($b->kode_barang);
                                            $totalMasuk = $m ? (float)$m->total_masuk : 0;
                                            $totalKeluar = $m ? (float)$m->total_keluar : 0;
                                            $stokSaatIni = (float)$b->stok;
                                            $estimasiSaldoAwal = $stokSaatIni - $totalMasuk + $totalKeluar;

                                            $lastSA = $lastSaldoAwals->get($b->kode_barang);
                                            $defaultValue = old('items.' . $index . '.saldo_awal', $estimasiSaldoAwal);

                                            $satuans = $b->satuans->sortByDesc('isi');
                                            $breakdown = [];
                                            $remaining = round(abs((float)$defaultValue), 4);
                                            if ($satuans->count() > 0) {
                                                $count = $satuans->count();
                                                $i = 0;
                                                foreach ($satuans as $sat) {
                                                    $i++;
                                                    $factor = (float)($sat->isi ?: 1);
                                                    if ($i === $count) {
                                                        $unitQty = round($remaining / $factor, 4);
                                                        $breakdown[$sat->id] = $unitQty > 0 ? (float)$unitQty : '';
                                                    } else {
                                                        $unitQty = floor(round($remaining / $factor, 8));
                                                        $breakdown[$sat->id] = $unitQty > 0 ? (float)$unitQty : '';
                                                        $remaining = round($remaining - ($unitQty * $factor), 4);
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ ($barangs->currentPage() - 1) * $barangs->perPage() + $index + 1 }}</td>
                                            <td class="font-monospace text-secondary text-center">
                                                <div>{{ $b->kode_barang }}</div>
                                                @if ($b->kode_item)
                                                    <div class="text-muted small">({{ $b->kode_item }})</div>
                                                @endif
                                            </td>
                                            <td class="fw-bold">{{ $b->nama_barang }}</td>
                                            <td class="small">
                                                <div><span class="badge bg-secondary opacity-75">{{ $b->kategori ?? '-' }}</span></div>
                                                <div class="text-muted mt-1">{{ $b->merk ?? '-' }}</div>
                                            </td>
                                            <td class="text-end font-monospace fw-bold">
                                                <span class="badge bg-primary text-wrap fs-7 px-3 py-1.5">
                                                    {{ $b->formatStok($b->stok) }}
                                                </span>
                                            </td>
                                            <td class="text-center font-monospace small">
                                                @if ($totalMasuk > 0 || $totalKeluar > 0)
                                                    <div class="text-success"><i class="fa-solid fa-arrow-down me-1"></i> Masuk: +{{ $totalMasuk }}</div>
                                                    <div class="text-danger"><i class="fa-solid fa-arrow-up me-1"></i> Keluar: -{{ $totalKeluar }}</div>
                                                @else
                                                    <span class="text-muted opacity-50">&mdash; Tidak ada mutasi &mdash;</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-monospace fw-bold text-dark">
                                                <div class="fs-7 text-primary">{{ $b->formatStok($estimasiSaldoAwal) }}</div>
                                                <div class="text-muted opacity-75" style="font-size: 10px;" title="Rumus: {{ $stokSaatIni }} - {{ $totalMasuk }} + {{ $totalKeluar }}">
                                                    ({{ (float)$estimasiSaldoAwal }} Base Qty)
                                                </div>
                                            </td>
                                            <td class="sa-row-container">
                                                <input type="hidden" name="items[{{ $index }}][kode_barang]" value="{{ $b->kode_barang }}">
                                                <input type="hidden" name="items[{{ $index }}][saldo_awal]" 
                                                       class="saldo-awal-total-hidden" 
                                                       data-estimasi="{{ (float)$estimasiSaldoAwal }}"
                                                       value="{{ (float)$defaultValue }}">

                                                <div class="d-flex flex-wrap gap-1 justify-content-end align-items-center mb-1">
                                                    @if ($satuans->count() > 0)
                                                        @foreach ($satuans as $sat)
                                                            <div class="input-group input-group-sm" style="width: 85px;">
                                                                <input type="number" step="any" min="0" 
                                                                       class="form-control form-control-sm text-end font-monospace p-1 uom-sa-input" 
                                                                       data-isi="{{ (float)$sat->isi }}" 
                                                                       data-satuan="{{ $sat->satuan }}"
                                                                       value="{{ $breakdown[$sat->id] ?? '' }}" 
                                                                       placeholder="0">
                                                                <span class="input-group-text px-1 text-secondary fw-semibold text-xs" style="font-size: 9px !important; line-height: 1.2;">{{ $sat->satuan }}</span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="input-group input-group-sm" style="width: 100px;">
                                                            <input type="number" step="any" min="0" 
                                                                   class="form-control form-control-sm text-end font-monospace uom-sa-input" 
                                                                   data-isi="1" 
                                                                   data-satuan="PCS"
                                                                   value="{{ $defaultValue != 0 ? (float)$defaultValue : '' }}" 
                                                                   placeholder="0">
                                                            <span class="input-group-text px-1.5 text-secondary text-xs">PCS</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-end text-muted small font-monospace fw-semibold" style="font-size: 0.73rem;">
                                                    Konversi: <span class="total-sa-converted text-primary fw-bold">{{ $b->formatStok($defaultValue) }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                Tidak ada data barang ditemukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($barangs->hasPages())
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                                <div class="small text-muted">
                                    Menampilkan {{ $barangs->firstItem() }} - {{ $barangs->lastItem() }} dari {{ $barangs->total() }} barang
                                </div>
                                <div>
                                    {{ $barangs->links() }}
                                </div>
                            </div>
                        @endif

                        @if ($barangs->isNotEmpty())
                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-success fw-bold px-4 py-2 shadow-sm rounded-3">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Saldo Awal
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-init').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Realtime UOM conversion listener
            $(document).on('input change', '.uom-sa-input', function() {
                const container = $(this).closest('.sa-row-container');
                let totalBase = 0;
                let parts = [];

                container.find('.uom-sa-input').each(function() {
                    const valStr = $(this).val();
                    const val = parseFloat(valStr) || 0;
                    const factor = parseFloat($(this).data('isi')) || 1;
                    const unitName = $(this).data('satuan');

                    if (val > 0) {
                        totalBase += val * factor;
                        parts.push(`${val} ${unitName}`);
                    }
                });

                totalBase = Math.round(totalBase * 10000) / 10000;
                const convertedText = parts.length > 0 ? parts.join(' ') : '0 PCS';

                container.find('.saldo-awal-total-hidden').val(totalBase);
                container.find('.total-sa-converted').text(convertedText);
            });

            // Auto-Fill Button Logic (Hitung Mundur Mutasi per Satuan UOM)
            $('#btnAutoGenerateHitungMundur').on('click', function() {
                $('.sa-row-container').each(function() {
                    const container = $(this);
                    const hiddenInput = container.find('.saldo-awal-total-hidden');
                    const estimasi = parseFloat(hiddenInput.data('estimasi')) || 0;

                    let remaining = Math.round(Math.abs(estimasi) * 10000) / 10000;
                    const inputs = container.find('.uom-sa-input');
                    const count = inputs.length;

                    inputs.each(function(index) {
                        const factor = parseFloat($(this).data('isi')) || 1;
                        if (index === count - 1) {
                            const unitQty = Math.round((remaining / factor) * 10000) / 10000;
                            $(this).val(unitQty > 0 ? unitQty : '');
                        } else {
                            const unitQty = Math.floor(Math.round((remaining / factor) * 100000000) / 100000000);
                            $(this).val(unitQty > 0 ? unitQty : '');
                            remaining = Math.round((remaining - (unitQty * factor)) * 10000) / 10000;
                        }
                    });

                    // Trigger change to update hidden input & conversion label
                    inputs.first().trigger('change');
                });

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Saldo Awal dikonversi & diisi otomatis per satuan UOM',
                    showConfirmButton: false,
                    timer: 2000
                });
            });
        });
    </script>
@endpush
