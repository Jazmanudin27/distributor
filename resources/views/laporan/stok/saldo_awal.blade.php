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
                                <button type="submit" class="btn btn-secondary fw-semibold">
                                    <i class="fa-solid fa-search"></i>
                                </button>
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
                                            $defaultValue = $lastSA ? (float)$lastSA->saldo_akhir : (float)$b->stok;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
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
                                                    <span class="text-success fw-semibold font-monospace">
                                                        {{ number_format($lastSA->saldo_akhir, 0, ',', '.') }}
                                                    </span>
                                                    <div class="text-muted" style="font-size: 10px;">
                                                        Tgl: {{ \Carbon\Carbon::parse($lastSA->tanggal)->format('d/m/Y') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted opacity-50">&mdash; Belum ada &mdash;</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][kode_barang]" value="{{ $b->kode_barang }}">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" name="items[{{ $index }}][saldo_awal]" 
                                                           class="form-control form-control-sm text-end fw-bold font-monospace input-saldo-awal" 
                                                           data-stok-available="{{ (float)$b->stok }}"
                                                           value="{{ old('items.' . $index . '.saldo_awal', $defaultValue) }}" required min="0">
                                                    <span class="input-group-text bg-light small">{{ $b->satuans->sortBy('isi')->first()->satuan ?? 'PCS' }}</span>
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

            // Auto Generate Button Logic
            $('#btnAutoGenerate').on('click', function() {
                if (confirm('Apakah Anda yakin ingin mengisi nilai Saldo Awal secara otomatis dari stok yang tersedia saat ini untuk semua barang di daftar?')) {
                    $('.input-saldo-awal').each(function() {
                        const stokAvailable = $(this).data('stok-available');
                        $(this).val(stokAvailable);
                    });
                </div>
            });
        });
    </script>
@endpush
