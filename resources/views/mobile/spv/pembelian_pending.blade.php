@extends('layouts.mobile')

@section('title', 'Persetujuan Pembelian')

@section('content')
    <!-- Header with Back Button -->
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('mobile.dashboard') }}" class="btn btn-sm btn-link text-white p-0 me-3"
            style="text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="font-size: 1.35rem;"></i>
        </a>
        <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;">Persetujuan Pembelian</h5>
    </div>

    <!-- Pending Pembelian List -->
    @if ($pendingPembelians->isEmpty())
        <div class="text-center py-5">
            <div class="avatar-glow rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                style="width: 65px; height: 65px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                <i class="fa-solid fa-cart-flatbed-suitcases text-secondary" style="font-size: 1.7rem;"></i>
            </div>
            <h6 class="fw-bold text-white-50">Semua Terproses</h6>
            <p class="text-secondary small">Tidak ada pengajuan pembelian baru yang perlu disetujui saat ini.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-3 pb-5">
            @foreach ($pendingPembelians as $p)
                <div class="mobile-card p-3 mb-0">
                    <div
                        class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                        <div>
                            <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem;">
                                {{ $p->supplier->nama_supplier ?? '-' }}</h6>
                            <span class="text-secondary font-monospace" style="font-size: 0.7rem;">FAKTUR:
                                {{ $p->no_faktur }}</span>
                        </div>
                        <span
                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1"
                            style="font-size: 0.68rem; font-weight: 600;">
                            Menunggu Approval
                        </span>
                    </div>

                    <!-- Details -->
                    <div style="font-size: 0.8rem;" class="text-secondary mb-3">
                        <div class="mb-1"><i class="fa-solid fa-calendar me-1.5 text-secondary"></i> Tanggal: <strong
                                class="text-white">{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}</strong></div>
                        @if ($p->jatuh_tempo)
                            <div class="mb-1"><i class="fa-solid fa-clock me-1.5 text-danger"></i> Jatuh Tempo: <strong
                                    class="text-white">{{ \Carbon\Carbon::parse($p->jatuh_tempo)->format('d-m-Y') }}</strong>
                            </div>
                        @endif
                        <div class="mb-1"><i class="fa-solid fa-money-bill-wave me-1.5 text-success"></i> Grand Total:
                            <strong class="text-info font-monospace">Rp
                                {{ number_format($p->grand_total, 0, ',', '.') }}</strong>
                        </div>
                        @if ($p->keterangan)
                            <div class="mb-1"><i class="fa-solid fa-comment me-1.5 text-secondary"></i> Keterangan:
                                <strong class="text-white">{{ $p->keterangan }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Collapsible Accordion for Items -->
                    <div class="mb-3">
                        <button
                            class="btn btn-xs btn-outline-secondary w-100 py-1.5 collapsed text-secondary border-secondary border-opacity-25"
                            type="button" data-bs-toggle="collapse" data-bs-target="#items-{{ Str::slug($p->no_faktur) }}"
                            aria-expanded="false" style="font-size: 0.75rem; border-radius: 8px;">
                            <i class="fa-solid fa-list me-1"></i> Lihat Daftar Item ({{ $p->details->count() }}) <i
                                class="fa-solid fa-chevron-down ms-1"></i>
                        </button>
                        <div class="collapse mt-2" id="items-{{ Str::slug($p->no_faktur) }}">
                            <div class="bg-dark bg-opacity-20 p-2.5 rounded-3 border border-secondary border-opacity-10"
                                style="font-size: 0.75rem;">
                                @foreach ($p->details as $d)
                                    <div
                                        class="d-flex justify-content-between mb-1.5 pb-1.5 border-bottom border-secondary border-opacity-5 last-border-none">
                                        <div class="pe-2">
                                            <span class="fw-bold text-white d-block"
                                                style="word-break: break-word;">{{ $d->barang->nama_barang ?? '-' }}</span>
                                            <span class="text-secondary small font-monospace">{{ $d->qty }}
                                                {{ $d->satuan }} &times; Rp
                                                {{ number_format($d->harga, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="text-end font-monospace text-white-50 align-self-center">
                                            Rp {{ number_format($d->total, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="d-flex gap-2 border-top border-secondary border-opacity-10 pt-3">
                        <form action="{{ route('mobile.spv.pembelian.approve', $p->no_faktur) }}" method="POST"
                            class="w-100 approve-pembelian-form">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100 py-2 fw-semibold border-0 text-white"
                                style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 8px; font-size: 0.78rem;">
                                <i class="fa-solid fa-check me-1"></i> Setujui Pembelian
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.approve-pembelian-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Setujui Pembelian?',
                    text: "Apakah Anda yakin ingin menyetujui transaksi pembelian ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#ef4444',
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal',
                    background: '#161e31',
                    color: '#f8fafc'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
