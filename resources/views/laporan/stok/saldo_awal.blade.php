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
                            <div class="d-flex gap-2">
                                <button type="button" id="btnAutoGenerate" class="btn btn-info text-white btn-sm fw-bold shadow-sm rounded-3">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Auto-Generate dari Stok Available Saat Ini
                                </button>
                                <button type="submit" class="btn btn-success btn-sm fw-bold px-4 shadow-sm rounded-3">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Saldo Awal
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle text-sm">
                                <thead class="table-dark text-center align-middle">
                                    <tr>
                                        <th width="40">No</th>
                                        <th width="120">Kode Barang</th>
                                        <th width="120">Kode Item</th>
                                        <th>Nama Barang</th>
                                        <th width="130">Kategori / Merk</th>
                                        <th width="140">Stok Available Saat Ini</th>
                                        <th width="160">Saldo Awal Terakhir</th>
                                        <th width="180">Input Saldo Awal Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($barangs as $index => $b)
                                        @php
                                            $lastSA = $lastSaldoAwals->get($b->kode_barang);
                                            // If lastSA exists and has the exact same date as the selected effective date, use lastSA->saldo_akhir; otherwise default to current available stock ($b->stok)
                                            $defaultValue = ($lastSA && $lastSA->tanggal == $tanggalSaldoAwal) ? (float)$lastSA->saldo_akhir : (float)$b->stok;

                                            $satuans = $b->satuans->sortByDesc('isi');
                                            $breakdown = [];
                                            $remaining = (float)$defaultValue;
                                            $count = $satuans->count();
                                            $i = 0;
                                            foreach ($satuans as $sat) {
                                                $i++;
                                                $factor = (float)($sat->isi ?: 1);
                                                if ($i === $count) {
                                                    $unitQty = round($remaining / $factor, 4);
                                                    $breakdown[$sat->id] = $unitQty > 0 ? $unitQty : '';
                                                } else {
                                                    $unitQty = floor(round($remaining / $factor, 8));
                                                    $breakdown[$sat->id] = $unitQty > 0 ? $unitQty : '';
                                                    $remaining = round($remaining - ($unitQty * $factor), 4);
                                                }
                                            }
                                        @endphp
                                        <tr data-stok-available="{{ (float)$b->stok }}" data-satuans="{{ json_encode($satuans->values()) }}">
                                            <td class="text-center">{{ ($barangs->currentPage() - 1) * $barangs->perPage() + $index + 1 }}</td>
                                            <td class="font-monospace text-secondary text-center">{{ $b->kode_barang }}</td>
                                            <td class="font-monospace text-center">{{ $b->kode_item ?? '-' }}</td>
                                            <td class="fw-bold">{{ $b->nama_barang }}</td>
                                            <td class="small">
                                                <div><span class="badge bg-secondary opacity-75">{{ $b->kategori ?? '-' }}</span></div>
                                                <div class="text-muted mt-1">{{ $b->merk ?? '-' }}</div>
                                            </td>
                                            <td class="text-end font-monospace fw-bold">
                                                <span class="badge bg-primary text-wrap fs-7 px-3 py-2">
                                                    {{ $b->formatStok($b->stok) }}
                                                </span>
                                            </td>
                                            <td class="text-center small">
                                                @if ($lastSA)
                                                    <span class="text-success fw-bold font-monospace">
                                                        {{ $b->formatStok($lastSA->saldo_akhir) }}
                                                    </span>
                                                    <div class="text-muted font-monospace" style="font-size: 10px;">
                                                        ({{ number_format($lastSA->saldo_akhir, 0, ',', '.') }} PCS)
                                                    </div>
                                                    <div class="text-muted" style="font-size: 10px;">
                                                        Tgl: {{ \Carbon\Carbon::parse($lastSA->tanggal)->format('d/m/Y') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted opacity-50">&mdash; Belum ada &mdash;</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <input type="hidden" name="items[{{ $index }}][kode_barang]" value="{{ $b->kode_barang }}">
                                                <input type="hidden" name="items[{{ $index }}][saldo_awal]" class="input-saldo-awal" value="{{ $defaultValue }}">

                                                <div class="d-flex flex-wrap gap-1 justify-content-end align-items-center uom-container">
                                                    @if ($satuans->count() > 0)
                                                        @foreach ($satuans as $sat)
                                                            <div class="input-group input-group-sm" style="width: 85px;">
                                                                <input type="number" step="any" min="0"
                                                                       class="form-control form-control-sm text-end fw-bold font-monospace uom-input p-1"
                                                                       data-isi="{{ $sat->isi }}"
                                                                       value="{{ $breakdown[$sat->id] ?? '' }}"
                                                                       placeholder="0">
                                                                <span class="input-group-text px-1 text-secondary fw-semibold" style="font-size: 9px; line-height: 1.2;">{{ $sat->satuan }}</span>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="input-group input-group-sm" style="width: 100px;">
                                                            <input type="number" step="any" min="0"
                                                                   class="form-control form-control-sm text-end fw-bold font-monospace uom-input"
                                                                   data-isi="1"
                                                                   value="{{ $defaultValue > 0 ? $defaultValue : '' }}"
                                                                   placeholder="0">
                                                            <span class="input-group-text bg-light small">PCS</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="text-end text-muted small mt-1 font-monospace fw-semibold konversi-label" style="font-size: 0.73rem;">
                                                    Konversi: <span class="total-qty-val fw-bold text-primary">{{ $b->formatStok($defaultValue) }}</span>
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
                let formatted = breakdowns.join(' ') || '0 PCS';
                return isNegative ? '-' + formatted : formatted;
            }

            // Input changes in UOM inputs
            $(document).on('input change', '.uom-input', function() {
                const tr = $(this).closest('tr');
                let totalQty = 0;
                let isAnyFilled = false;

                tr.find('.uom-input').each(function() {
                    const val = $(this).val();
                    if (val !== '') {
                        isAnyFilled = true;
                    }
                    const qty = parseFloat(val) || 0;
                    const factor = parseFloat($(this).data('isi')) || 1;
                    totalQty += qty * factor;
                });

                const hiddenVal = isAnyFilled ? totalQty : 0;
                tr.find('.input-saldo-awal').val(hiddenVal);

                const satuans = tr.data('satuans') || [];
                const formatted = formatStokJS(hiddenVal, satuans);
                tr.find('.total-qty-val').text(formatted);
            });

            // Auto Generate Button Logic
            $('#btnAutoGenerate').on('click', function() {
                if (confirm('Apakah Anda yakin ingin mengisi nilai Saldo Awal secara otomatis dari stok yang tersedia saat ini untuk semua barang di daftar?')) {
                    $('table tbody tr').each(function() {
                        const tr = $(this);
                        const availableStok = parseFloat(tr.data('stok-available')) || 0;
                        const satuans = tr.data('satuans') || [];

                        let remaining = availableStok;
                        if (satuans && satuans.length > 0) {
                            let sorted = [...satuans].sort((a, b) => b.isi - a.isi);
                            let count = sorted.length;
                            sorted.forEach(function(sat, idx) {
                                let factor = parseFloat(sat.isi) || 1;
                                let inputEl = tr.find(`.uom-input[data-isi="${sat.isi}"]`);
                                if (idx === count - 1) {
                                    let unitQty = Math.round((remaining / factor) * 10000) / 10000;
                                    inputEl.val(unitQty > 0 ? unitQty : '');
                                } else {
                                    let unitQty = Math.floor(Math.round((remaining / factor) * 100000000) / 100000000);
                                    inputEl.val(unitQty > 0 ? unitQty : '');
                                    remaining = Math.round((remaining - (unitQty * factor)) * 10000) / 10000;
                                }
                            });
                        } else {
                            tr.find('.uom-input').val(availableStok > 0 ? availableStok : '');
                        }

                        tr.find('.input-saldo-awal').val(availableStok);
                        tr.find('.total-qty-val').text(formatStokJS(availableStok, satuans));
                    });
                }
            });
        });
    </script>
@endpush
