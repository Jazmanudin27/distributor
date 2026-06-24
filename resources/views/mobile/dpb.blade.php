@extends('layouts.mobile')

@section('title', 'Histori DPB (Barang Bawaan)')

@push('styles')
    <style>
        /* Filter Segmented Control */
        .filter-segmented-control {
            display: flex;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 3px;
            border-radius: 30px;
            width: 100%;
        }

        .filter-segmented-control .segment-item {
            flex: 1;
            text-align: center;
            padding: 7px 10px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .filter-segmented-control .segment-item:hover {
            color: #f8fafc;
        }

        .filter-segmented-control .segment-item.active {
            background: var(--accent-gradient);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
        }

        /* Modernized Accordion styling */
        .custom-accordion .accordion-item {
            background: rgba(30, 41, 59, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-radius: 14px !important;
            overflow: hidden;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .custom-accordion .accordion-item:hover {
            border-color: rgba(255, 255, 255, 0.12) !important;
            background: rgba(30, 41, 59, 0.4) !important;
        }

        .custom-accordion .accordion-button {
            background: transparent !important;
            color: #f8fafc !important;
            box-shadow: none !important;
            padding: 12px 16px !important;
            border: none !important;
        }

        .custom-accordion .accordion-button:not(.collapsed) {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            color: #f8fafc !important;
        }

        .custom-accordion .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2394a3b8'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
            background-size: 10px !important;
            width: 10px !important;
            height: 10px !important;
            transition: transform 0.2s ease !important;
        }

        .custom-accordion .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236366f1'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
        }
    </style>
@endpush

@section('content')
    <!-- Top Header Title & Actions -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
                <i class="fa-solid fa-truck text-white" style="font-size: 1.4rem;"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">DPB Anda</h4>
                <span class="text-secondary" style="font-size: 0.8rem; font-weight: 500;">
                    Barang bawaan sales canvas
                </span>
            </div>
        </div>
        <div>
            @if (!$session)
                <a href="{{ route('mobile.order.canvas.dpb.create') }}"
                    class="btn btn-sm btn-mobile-primary d-flex align-items-center gap-1 px-2.5 py-1.5 fw-semibold"
                    style="font-size: 0.75rem; border-radius: 8px;">
                    <i class="fa-solid fa-plus"></i> Input DPB
                </a>
            @endif
        </div>
    </div>

    @if ($session)
        <!-- DPB Aktif Hari Ini -->
        <h5 class="fw-bold mb-3 text-info" style="font-size: 0.95rem; letter-spacing: 0.5px;">
            <i class="fa-solid fa-circle-play me-1"></i> DPB Aktif Hari Ini
        </h5>

        <div class="mobile-card p-3 mb-4"
            style="background: rgba(30, 41, 59, 0.45); border-left: 3px solid #06b6d4; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="text-secondary font-monospace" style="font-size: 0.65rem;">{{ $session->no_canvas }}</span>
                    <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem;">Status Bawaan Hari Ini</h6>
                </div>
                <span class="badge px-2 py-1"
                    style="font-size: 0.625rem; font-weight: 600; border-radius: 6px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.35);">
                    <i class="fa-solid fa-truck-moving me-1"></i> Di Jalan
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top border-secondary border-opacity-10"
                style="font-size: 0.75rem;">
                <span class="text-secondary" style="font-size: 0.7rem;">Tanggal: <strong
                        class="text-white-50">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</strong></span>
                @if ($session->keterangan)
                    <span class="text-secondary italic" style="font-size: 0.7rem;">"{{ $session->keterangan }}"</span>
                @endif
            </div>
        </div>

        <!-- Items Listing -->
        <h5 class="fw-bold mb-3" style="font-size: 0.95rem; letter-spacing: 0.5px;">
            Daftar Barang Bawaan ({{ $session->details->count() }} Item)
        </h5>

        <div class="mobile-card p-0 overflow-hidden mb-5"
            style="background: rgba(30, 41, 59, 0.35); border: 1px solid rgba(255, 255, 255, 0.08);">
            @foreach ($session->details as $index => $detail)
                @php
                    $qtyAmbil = (float) $detail->qty_ambil;
                    $qtyTerjual = (float) $detail->qty_terjual;
                    $qtyKembali = (float) $detail->qty_kembali;
                    $sisaStok = $qtyAmbil - $qtyTerjual - $qtyKembali;
                @endphp
                <div class="p-3 {{ $index > 0 ? 'border-top border-secondary border-opacity-10' : '' }}">
                    <div class="d-flex justify-content-between align-items-start mb-1.5">
                        <div style="flex: 1; min-width: 0; padding-right: 8px;">
                            <h6 class="fw-bold text-white mb-0 text-truncate" style="font-size: 0.85rem;"
                                title="{{ $detail->barang->nama_barang }}">
                                {{ $detail->barang->nama_barang }}
                            </h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.6rem; opacity: 0.75;">
                                {{ $detail->kode_barang }}
                            </span>
                        </div>
                        <span class="badge bg-secondary bg-opacity-35 text-white-50 border-0 px-2 py-0.5"
                            style="font-size: 0.65rem; border-radius: 6px;">
                            {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1" style="font-size: 0.75rem;">
                        <div class="d-flex gap-3 text-secondary" style="font-size: 0.7rem;">
                            <div>Bawa: <strong class="text-primary font-monospace"
                                    style="font-size: 0.8rem;">{{ $detail->barang ? $detail->barang->formatStok($qtyAmbil) : $qtyAmbil }}</strong>
                            </div>
                            <div>Jual: <strong class="text-info font-monospace"
                                    style="font-size: 0.8rem;">{{ $detail->barang ? $detail->barang->formatStok($qtyTerjual) : $qtyTerjual }}</strong>
                            </div>
                        </div>
                        <div>
                            <span class="text-secondary" style="font-size: 0.65rem;">Sisa:</span>
                            <strong class="text-success font-monospace"
                                style="font-size: 0.85rem;">{{ $detail->barang ? $detail->barang->formatStok($sisaStok) : $sisaStok }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- No Active DPB Info -->
        <div class="text-center py-4 px-3 border border-secondary border-opacity-15 rounded-4 bg-dark bg-opacity-20 mb-4">
            <i class="fa-solid fa-truck-ramp-box text-secondary mb-3" style="font-size: 2.2rem; opacity: 0.35;"></i>
            <h6 class="fw-bold text-white mb-1" style="font-size: 0.85rem;">Belum Ada DPB Aktif Hari Ini</h6>
            <p class="text-secondary small mb-3" style="font-size: 0.75rem; line-height: 1.4;">
                Anda belum memuat barang bawaan hari ini. Silakan buat sesi DPB baru sebelum memulai input order penjualan
                canvas.
            </p>
            <a href="{{ route('mobile.order.canvas.dpb.create') }}"
                class="btn btn-sm btn-mobile-primary px-3 py-2 fw-semibold" style="font-size: 0.75rem; border-radius: 8px;">
                <i class="fa-solid fa-plus me-1"></i> Mulai DPB Baru
            </a>
        </div>
    @endif

    <!-- History / Past DPBs Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0" style="font-size: 0.95rem; letter-spacing: 0.5px;">
            <i class="fa-solid fa-clock-rotate-left me-1 text-secondary"></i> Riwayat DPB
        </h5>
    </div>

    <!-- Filter Buttons (Segmented Switch Style) -->
    <div class="filter-segmented-control mb-3">
        <a href="{{ route('mobile.order.canvas.dpb', ['filter' => 'all']) }}"
            class="segment-item {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}">
            Semua
        </a>
        <a href="{{ route('mobile.order.canvas.dpb', ['filter' => 'today']) }}"
            class="segment-item {{ ($filter ?? 'all') === 'today' ? 'active' : '' }}">
            Hari Ini
        </a>
        <a href="{{ route('mobile.order.canvas.dpb', ['filter' => 'yesterday']) }}"
            class="segment-item {{ ($filter ?? 'all') === 'yesterday' ? 'active' : '' }}">
            Kemarin
        </a>
    </div>

    @if ($historySessions->isEmpty())
        <div class="text-center py-4 px-3 border border-secondary border-opacity-10 rounded-4 bg-dark bg-opacity-10">
            <p class="text-secondary mb-0" style="font-size: 0.75rem;">Tidak ada riwayat DPB.</p>
        </div>
    @else
        <!-- History Accordion -->
        <div class="accordion accordion-flush custom-accordion mb-4" id="historyAccordion">
            @foreach ($historySessions as $hs)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $hs->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $hs->id }}" aria-expanded="false"
                            aria-controls="collapse-{{ $hs->id }}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div>
                                    <span class="fw-bold text-white"
                                        style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($hs->tanggal)->format('d M Y') }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary bg-opacity-25 text-white-50 border-0 px-2 py-0.5"
                                        style="font-size: 0.65rem; border-radius: 6px;">
                                        {{ $hs->details->count() }} Item
                                    </span>
                                    <span
                                        class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-20 px-2 py-0.5"
                                        style="font-size: 0.6rem; border-radius: 6px;">
                                        Selesai
                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $hs->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $hs->id }}" data-bs-parent="#historyAccordion">
                        <div class="accordion-body bg-black bg-opacity-15 p-3">
                            <div class="mb-3 d-flex flex-column gap-1.5 p-2.5 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-10" style="font-size: 0.72rem;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary">No. DPB:</span>
                                    <strong class="text-white font-monospace" style="font-size: 0.75rem;">{{ $hs->no_canvas }}</strong>
                                </div>
                                @if ($hs->keterangan)
                                    <div class="border-top border-secondary border-opacity-10 pt-1.5 mt-1">
                                        <span class="text-secondary d-block mb-0.5" style="font-size: 0.6rem; text-transform: uppercase; font-weight: 600;">Catatan:</span>
                                        <span class="text-white-50 italic">"{{ $hs->keterangan }}"</span>
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex flex-column gap-1" style="font-size: 0.75rem;">
                                @foreach ($hs->details as $detIndex => $det)
                                    @php
                                        $qtyAmbil = (float) $det->qty_ambil;
                                        $qtyTerjual = (float) $det->qty_terjual;
                                        $qtyKembali = (float) $det->qty_kembali;
                                        $sisaStok = $qtyAmbil - $qtyTerjual - $qtyKembali;
                                    @endphp
                                    <div
                                        class="py-2.5 {{ $detIndex > 0 ? 'border-top border-secondary border-opacity-10' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start mb-1.5">
                                            <div class="fw-semibold text-white text-truncate"
                                                style="font-size: 0.8rem; max-width: 70%;"
                                                title="{{ $det->barang->nama_barang ?? 'Barang Terhapus' }}">
                                                {{ $det->barang->nama_barang ?? 'Barang Terhapus' }}
                                            </div>
                                            <span
                                                class="badge bg-secondary bg-opacity-15 text-white-50 px-1.5 py-0.5 border-0 font-monospace"
                                                style="font-size: 0.625rem; border-radius: 4px;">
                                                Bawa: {{ $det->barang ? $det->barang->formatStok($qtyAmbil) : $qtyAmbil }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center text-secondary"
                                            style="font-size: 0.7rem;">
                                            <div class="d-flex gap-3">
                                                <span>Jual: <strong
                                                        class="text-info font-monospace">{{ $det->barang ? $det->barang->formatStok($qtyTerjual) : $qtyTerjual }}</strong></span>
                                                <span>Kembali: <strong
                                                        class="text-warning font-monospace">{{ $det->barang ? $det->barang->formatStok($qtyKembali) : $qtyKembali }}</strong></span>
                                            </div>
                                            <div>
                                                <span>Sisa: <strong
                                                        class="{{ $sisaStok > 0 ? 'text-success' : 'text-white-50' }} font-monospace">{{ $det->barang ? $det->barang->formatStok($sisaStok) : $sisaStok }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $historySessions->links() }}
        </div>
    @endif
@endsection
