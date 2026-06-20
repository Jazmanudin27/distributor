@extends('layouts.mobile')

@section('title', 'Stok Menipis')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('mobile.owner.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Produk Stok Menipis</h5>
    </div>

    <!-- Product List -->
    @if ($lowStockItems->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fa-solid fa-square-check text-success" style="font-size: 1.8rem;"></i>
            </div>
            <h6 class="fw-bold text-white mb-1">Semua Stok Aman</h6>
            <p class="text-secondary small mb-0">Tidak ada produk yang berada di bawah stok minimum.</p>
        </div>
    @else
        <div class="alert alert-danger rounded-4 mb-3 d-flex align-items-center"
            style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171;">
            <i class="fa-solid fa-triangle-exclamation me-3" style="font-size: 1.25rem;"></i>
            <div style="font-size: 0.8rem;">
                Ditemukan <strong>{{ $lowStockItems->count() }} item</strong> produk yang memerlukan pemesanan ulang
                (restock).
            </div>
        </div>

        @foreach ($lowStockItems as $item)
            @php
                $baseSatuan = $item->satuans->sortBy('isi')->first();
                $satuanName = $baseSatuan ? $baseSatuan->satuan : 'PCS';
            @endphp
            <div class="mobile-card p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold text-white mb-0" style="font-size: 0.9rem;">{{ $item->nama_barang }}</h6>
                        <span class="text-secondary font-monospace"
                            style="font-size: 0.72rem;">{{ $item->kode_barang }}</span>
                    </div>
                    <span class="badge rounded-pill bg-danger border border-danger border-opacity-30 text-white py-1 px-2.5"
                        style="font-size: 0.7rem;">
                        Stok: {{ $item->formatStok($item->stok) }}
                    </span>
                </div>
                <div class="border-top border-secondary border-opacity-10 pt-2 mt-2" style="font-size: 0.78rem;">
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Min. Stok:</span>
                        <span class="text-white fw-semibold">{{ $item->formatStok($item->stok_min) }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary">
                        <span>Supplier:</span>
                        <span class="text-white">{{ $item->supplier->nama_supplier ?? '-' }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection
