@extends('layouts.mobile')

@section('title', 'DPB (Barang Bawaan Hari Ini)')

@section('content')
    <div class="d-flex align-items-center mb-4">
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

    @if (!$session)
        <div class="mobile-card text-center py-5">
            <i class="fa-solid fa-truck-ramp-box text-secondary mb-3" style="font-size: 3rem; opacity: 0.4;"></i>
            <h6 class="fw-bold text-white mb-1">Tidak Ada DPB Aktif</h6>
            <p class="text-secondary small mb-0 px-4">
                Anda belum memiliki lembar pengambilan barang (DPB) yang aktif di jalan. Silakan hubungi admin gudang untuk melakukan loading barang canvas.
            </p>
        </div>
    @else
        <!-- DPB Info Header -->
        <div class="mobile-card p-3 mb-4" style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);">
            <div class="row g-3">
                <div class="col-6">
                    <span class="text-secondary d-block" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">No. DPB</span>
                    <strong class="text-info" style="font-size: 0.9rem;">{{ $session->no_canvas }}</strong>
                </div>
                <div class="col-6 text-end">
                    <span class="text-secondary d-block" style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal Loading</span>
                    <strong class="text-white-50" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</strong>
                </div>
                <div class="col-12 mt-2 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary" style="font-size: 0.75rem;">Status:</span>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1" style="font-size: 0.65rem; font-weight: 600;">
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
                <div class="mobile-card p-3 mb-2" style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem;">
                                {{ $detail->barang->nama_barang }}
                            </h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                Kode: {{ $detail->kode_barang }}
                            </span>
                        </div>
                        <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 0.65rem; padding: 2px 8px;">
                            {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                        </span>
                    </div>

                    <div class="row g-2 text-center" style="font-size: 0.75rem;">
                        <div class="col-4">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Ambil</span>
                            <strong class="text-primary font-monospace" style="font-size: 0.9rem;">{{ $qtyAmbil }}</strong>
                        </div>
                        <div class="col-4 border-start border-end border-secondary border-opacity-10">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Terjual</span>
                            <strong class="text-info font-monospace" style="font-size: 0.9rem;">{{ $qtyTerjual }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Sisa Bawaan</span>
                            <strong class="text-success font-monospace" style="font-size: 0.9rem;">{{ $sisaStok }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
