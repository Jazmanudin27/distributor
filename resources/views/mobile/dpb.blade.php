@extends('layouts.mobile')

@section('title', 'Histori DPB (Barang Bawaan Hari Ini)')

@push('styles')
    <style>
        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23f8fafc'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
        }

        .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236366f1'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
        }

        .accordion-button:focus {
            box-shadow: none !important;
            border-color: rgba(99, 102, 241, 0.4) !important;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(99, 102, 241, 0.12) !important;
            color: #6366f1 !important;
            border-color: rgba(99, 102, 241, 0.3) !important;
        }
    </style>
@endpush

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center me-3"
                style="width: 50px; height: 50px; background: var(--accent-gradient); box-shadow: var(--accent-glow);">
                <i class="fa-solid fa-truck text-white" style="font-size: 1.4rem;"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold" style="font-size: 1.15rem; letter-spacing: 0.5px;">DPB Anda</h4>
                <span class="text-secondary" style="font-size: 0.8rem; font-weight: 500;">
                    Barang yang dibawa sales canvas
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
            @else
                <a href="{{ route('mobile.order.canvas.create') }}"
                    class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 px-2.5 py-1.5 fw-semibold"
                    style="font-size: 0.7rem; border-radius: 8px;">
                    <i class="fa-solid fa-arrow-left"></i> Input Order
                </a>
            @endif
        </div>
    </div>

    @if ($session)
        <!-- DPB Aktif Anda -->
        <h5 class="fw-bold mb-3 text-info" style="font-size: 0.95rem; letter-spacing: 0.5px;">
            <i class="fa-solid fa-circle-play me-1"></i> DPB Aktif Hari Ini
        </h5>

        <div class="mobile-card p-3 mb-4"
            style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);">
            <div class="row g-3">
                <div class="col-6">
                    <span class="text-secondary d-block"
                        style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">No.
                        DPB</span>
                    <strong class="text-info" style="font-size: 0.9rem;">{{ $session->no_canvas }}</strong>
                </div>
                <div class="col-6 text-end">
                    <span class="text-secondary d-block"
                        style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Tanggal
                        Loading</span>
                    <strong class="text-white-50"
                        style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</strong>
                </div>
                <div
                    class="col-12 mt-2 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-secondary" style="font-size: 0.75rem;">Status:</span>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1"
                            style="font-size: 0.65rem; font-weight: 600;">
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

        <div class="dpb-items mb-5">
            @foreach ($session->details as $detail)
                @php
                    $qtyAmbil = (float) $detail->qty_ambil;
                    $qtyTerjual = (float) $detail->qty_terjual;
                    $qtyKembali = (float) $detail->qty_kembali;
                    $sisaStok = $qtyAmbil - $qtyTerjual - $qtyKembali;
                @endphp
                <div class="mobile-card p-3 mb-2"
                    style="background: rgba(30, 41, 59, 0.45); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div
                        class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.85rem;">
                                {{ $detail->barang->nama_barang }}
                            </h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.65rem;">
                                Kode: {{ $detail->kode_barang }}
                            </span>
                        </div>
                        <span class="badge bg-light text-secondary border fw-semibold"
                            style="font-size: 0.65rem; padding: 2px 8px;">
                            {{ $detail->barangSatuan->satuan ?? 'PCS' }}
                        </span>
                    </div>

                    <div class="row g-2 text-center" style="font-size: 0.75rem;">
                        <div class="col-4">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Ambil</span>
                            <strong class="text-primary font-monospace"
                                style="font-size: 0.9rem;">{{ $qtyAmbil }}</strong>
                        </div>
                        <div class="col-4 border-start border-end border-secondary border-opacity-10">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Terjual</span>
                            <strong class="text-info font-monospace"
                                style="font-size: 0.9rem;">{{ $qtyTerjual }}</strong>
                        </div>
                        <div class="col-4">
                            <span class="text-secondary d-block" style="font-size: 0.65rem;">Sisa Bawaan</span>
                            <strong class="text-success font-monospace"
                                style="font-size: 0.9rem;">{{ $sisaStok }}</strong>
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

    <!-- History / Past DPBs -->
    <h5 class="fw-bold mb-3" style="font-size: 0.95rem; letter-spacing: 0.5px;">
        <i class="fa-solid fa-clock-rotate-left me-1 text-secondary"></i> Riwayat DPB Sebelumnya
    </h5>

    @if ($historySessions->isEmpty())
        <div class="text-center py-4 px-3 border border-secondary border-opacity-10 rounded-4 bg-dark bg-opacity-10">
            <p class="text-secondary mb-0" style="font-size: 0.75rem;">Tidak ada riwayat DPB sebelumnya.</p>
        </div>
    @else
        <div class="accordion accordion-flush mb-4" id="historyAccordion">
            @foreach ($historySessions as $hs)
                <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-10 mb-2">
                    <h2 class="accordion-header" id="heading-{{ $hs->id }}">
                        <button
                            class="accordion-button collapsed bg-dark bg-opacity-40 text-white border border-secondary border-opacity-10 rounded-3 px-3 py-2.5 small"
                            type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $hs->id }}"
                            aria-expanded="false" aria-controls="collapse-{{ $hs->id }}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div>
                                    <span class="text-secondary d-block font-monospace"
                                        style="font-size: 0.65rem;">{{ $hs->no_canvas }}</span>
                                    <span class="fw-bold text-white-50"
                                        style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($hs->tanggal)->format('d M Y') }}</span>
                                </div>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"
                                    style="font-size: 0.6rem;">
                                    Selesai
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $hs->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $hs->id }}" data-bs-parent="#historyAccordion">
                        <div
                            class="accordion-body bg-dark bg-opacity-25 rounded-3 p-3 mt-1 border border-secondary border-opacity-10">
                            @if ($hs->keterangan)
                                <p class="text-secondary italic mb-3" style="font-size: 0.75rem;">
                                    <strong>Catatan:</strong> "{{ $hs->keterangan }}"
                                </p>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-sm table-dark table-borderless mb-0"
                                    style="font-size: 0.75rem;">
                                    <thead>
                                        <tr class="text-secondary border-bottom border-secondary border-opacity-15">
                                            <th>Barang</th>
                                            <th class="text-end">Ambil</th>
                                            <th class="text-end">Terjual</th>
                                            <th class="text-end">Kembali</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($hs->details as $det)
                                            <tr>
                                                <td class="text-white-50 py-2">
                                                    {{ $det->barang->nama_barang ?? 'Barang Terhapus' }}</td>
                                                <td class="text-end font-monospace text-primary py-2">
                                                    {{ (float) $det->qty_ambil }}
                                                    {{ $det->barangSatuan->satuan ?? 'PCS' }}
                                                </td>
                                                <td class="text-end font-monospace text-info py-2">
                                                    {{ (float) $det->qty_terjual }}</td>
                                                <td class="text-end font-monospace text-success py-2">
                                                    {{ (float) $det->qty_kembali }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
