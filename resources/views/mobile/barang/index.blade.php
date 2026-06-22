@extends('layouts.mobile')

@section('title', 'Daftar Barang & Stok')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Daftar Barang</h5>
    </div>

    <!-- Search Bar and Filter -->
    <form action="{{ route('mobile.barang.index') }}" method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-12">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-mobile"
                        placeholder="Cari nama atau kode barang..." value="{{ request('search') }}">
                    <button class="btn btn-mobile-primary" type="submit">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-6">
                <select name="merk" class="form-select form-control-mobile" onchange="this.form.submit()"
                    style="border-radius: 12px !important; padding: 12px 14px !important; font-size: 0.9rem !important;">
                    <option value="">-- Semua Merk --</option>
                    @foreach ($merks as $m)
                        <option value="{{ $m->nama_merk }}" {{ request('merk') == $m->nama_merk ? 'selected' : '' }}>
                            {{ $m->nama_merk }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <select name="stok" class="form-select form-control-mobile" onchange="this.form.submit()"
                    style="border-radius: 12px !important; padding: 12px 14px !important; font-size: 0.9rem !important;">
                    <option value="">-- Semua Stok --</option>
                    <option value="ada" {{ request('stok') == 'ada' ? 'selected' : '' }}>Ada Stok</option>
                    <option value="kosong" {{ request('stok') == 'kosong' ? 'selected' : '' }}>Stok Kosong</option>
                </select>
            </div>
        </div>
    </form>

    <!-- Product List -->
    @if ($barangs->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                <i class="fa-solid fa-box-open text-primary" style="font-size: 1.8rem;"></i>
            </div>
            <h6 class="fw-bold text-white mb-1">Tidak Ada Barang</h6>
            <p class="text-secondary small mb-0">Barang tidak ditemukan atau stok kosong.</p>
        </div>
    @else
        <div class="mb-3 text-secondary" style="font-size: 0.85rem;">
            Menampilkan {{ $barangs->count() }} barang teratas
        </div>

        @foreach ($barangs as $item)
            @php
                $baseSatuan = $item->satuans->sortBy('isi')->first();
                $satuanName = $baseSatuan ? $baseSatuan->satuan : 'PCS';
            @endphp
            <div class="mobile-card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">{{ $item->nama_barang }}</h6>
                        <span class="text-secondary font-monospace"
                            style="font-size: 0.75rem;">{{ $item->kode_barang }}</span>
                    </div>
                    @php
                        $stokLevelClass = 'bg-success border-success';
                        if ($item->stok <= 0) {
                            $stokLevelClass = 'bg-danger border-danger';
                        } elseif ($item->stok <= $item->stok_min) {
                            $stokLevelClass = 'bg-warning border-warning';
                        }
                    @endphp
                    <span class="badge rounded-pill {{ $stokLevelClass }} border border-opacity-30 text-white py-1 px-2.5"
                        style="font-size: 0.75rem;">
                        Stok: {{ $item->formatStok($item->stok) }}
                    </span>
                </div>
                <div class="border-top border-secondary border-opacity-10 pt-2 mt-2" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Kategori:</span>
                        <span class="text-white">{{ $item->kategori ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-2">
                        <span>Merk:</span>
                        <span class="text-white">{{ $item->merk ?? '-' }}</span>
                    </div>

                    <!-- Harga per Satuan -->
                    <div class="mt-2 pt-2 border-top border-secondary border-opacity-10">
                        <div class="text-secondary mb-1" style="font-size: 0.75rem; font-weight: 500;">Daftar Harga:</div>
                        @if ($item->satuans->isEmpty())
                            <div class="text-secondary" style="font-size: 0.8rem;">Belum ada satuan / harga diatur.</div>
                        @else
                            @foreach ($item->satuans as $sat)
                                <div class="d-flex justify-content-between align-items-center mb-1"
                                    style="font-size: 0.8rem;">
                                    <span class="text-white">
                                        <i class="fa-solid fa-tag text-secondary me-1" style="font-size: 0.7rem;"></i>
                                        1 {{ $sat->satuan }}
                                        @if ($sat->isi > 1)
                                            <small class="text-secondary ms-1">(Isi {{ $sat->isi }})</small>
                                        @endif
                                    </span>
                                    <span class="text-info fw-semibold">Rp
                                        {{ number_format($sat->harga_jual, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

    @endif
@endsection
