@extends('layouts.mobile')

@section('title', 'Daftar Barang & Stok')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3" style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Daftar Barang</h5>
    </div>

    <!-- Search Bar -->
    <form action="{{ route('mobile.barang.index') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control form-control-mobile" placeholder="Cari nama atau kode barang..." value="{{ request('search') }}">
            <button class="btn btn-mobile-primary" type="submit">
                <i class="fa-solid fa-search"></i>
            </button>
        </div>
    </form>

    <!-- Product List -->
    @if ($barangs->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 65px; height: 65px; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2);">
                <i class="fa-solid fa-box-open text-primary" style="font-size: 1.8rem;"></i>
            </div>
            <h6 class="fw-bold text-white mb-1">Tidak Ada Barang</h6>
            <p class="text-secondary small mb-0">Barang tidak ditemukan atau stok kosong.</p>
        </div>
    @else
        <div class="mb-3 text-secondary" style="font-size: 0.85rem;">
            Menampilkan {{ $barangs->firstItem() }} - {{ $barangs->lastItem() }} dari {{ $barangs->total() }} barang
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
                        <span class="text-secondary font-monospace" style="font-size: 0.75rem;">{{ $item->kode_barang }}</span>
                    </div>
                    @php
                        $stokLevelClass = 'bg-success border-success';
                        if($item->stok <= 0) {
                            $stokLevelClass = 'bg-danger border-danger';
                        } elseif ($item->stok <= $item->stok_min) {
                            $stokLevelClass = 'bg-warning border-warning';
                        }
                    @endphp
                    <span class="badge rounded-pill {{ $stokLevelClass }} border border-opacity-30 text-white py-1 px-2.5" style="font-size: 0.75rem;">
                        Stok: {{ $item->formatStok($item->stok) }}
                    </span>
                </div>
                <div class="border-top border-secondary border-opacity-10 pt-2 mt-2" style="font-size: 0.8rem;">
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Kategori:</span>
                        <span class="text-white">{{ $item->kategori ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Merk:</span>
                        <span class="text-white">{{ $item->merk ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary">
                        <span>Harga ({{ $satuanName }}):</span>
                        <span class="text-white fw-semibold">Rp {{ number_format($baseSatuan ? $baseSatuan->harga_jual : 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $barangs->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endsection
