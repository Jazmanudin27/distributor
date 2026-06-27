@extends('layouts.app')
@section('title', 'Detail Diskon Strata')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-eye me-2"></i> Detail Aturan Diskon Strata
                </h5>
                <small class="text-white-50">Lihat konfigurasi cakupan barang dan batas range diskon strata</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('diskon-strata.edit', $item->id) }}"
                    class="btn btn-warning btn-sm fw-bold text-dark hover-scale">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Aturan
                </a>
                <a href="{{ route('diskon-strata.index') }}" class="btn btn-secondary btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-arrow-left me-1 text-white"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body p-4 bg-light">
            <div class="row g-4 mb-4">
                {{-- Left panel: metadata --}}
                <div class="col-md-5">
                    <div class="card border p-3 rounded bg-white shadow-sm mb-0">
                        <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> Informasi Aturan
                        </h6>
                        <table class="table table-sm table-borderless mb-0 fs-7">
                            <tr>
                                <td class="text-secondary" width="130">Nama Diskon</td>
                                <td class="fw-bold text-dark">: {{ $item->nama_diskon }}</td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Tipe Aturan</td>
                                <td>:
                                    @php
                                        $label = [
                                            'barang' => 'Per Barang',
                                            'beberapa_barang' => 'Per Beberapa Barang',
                                            'kategori' => 'Per Kategori',
                                            'merk' => 'Per Merk',
                                            'supplier' => 'Per Supplier',
                                        ];
                                    @endphp
                                    <strong class="text-primary">{{ $label[$item->tipe] ?? $item->tipe }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Target Aturan</td>
                                <td>:
                                    @if ($item->tipe === 'barang')
                                        <strong>{{ $item->barangs->first()->nama_barang ?? '-' }}</strong>
                                        ({{ $item->barangs->first()->kode_barang ?? '-' }})
                                    @elseif ($item->tipe === 'beberapa_barang')
                                        <strong>{{ $item->barangs->count() }} Item Barang Terpilih</strong>
                                    @elseif ($item->tipe === 'kategori')
                                        <strong>{{ $item->kategori->nama_kategori ?? '-' }}</strong>
                                    @elseif ($item->tipe === 'merk')
                                        <strong>{{ $item->merk->nama_merk ?? '-' }}</strong>
                                    @elseif ($item->tipe === 'supplier')
                                        <strong>{{ $item->supplier->nama_supplier ?? '-' }}</strong>
                                        ({{ $item->kode_supplier }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Periode Berlaku</td>
                                <td>:
                                    @if ($item->berlaku_dari || $item->berlaku_sampai)
                                        <span class="font-monospace fw-bold">
                                            {{ $item->berlaku_dari ? $item->berlaku_dari->format('d M Y H:i') : '∞' }} -
                                            {{ $item->berlaku_sampai ? $item->berlaku_sampai->format('d M Y H:i') : '∞' }}
                                        </span>
                                    @else
                                        <span class="text-muted">Selamanya</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">Status Aktif</td>
                                <td>:
                                    @if ($item->is_active)
                                        <span class="badge bg-success px-2 py-1 fs-8">Aktif</span>
                                    @else
                                        <span class="badge bg-danger px-2 py-1 fs-8">Non-Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Right panel: List of target items (if several items or single item) --}}
                @if (in_array($item->tipe, ['barang', 'beberapa_barang']))
                    <div class="col-md-7">
                        <div class="card border p-3 rounded bg-white shadow-sm mb-0">
                            <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                                <i class="fa-solid fa-box text-success me-1"></i> Daftar Barang Target
                            </h6>
                            <div class="table-responsive" style="max-height: 160px; overflow-y: auto;">
                                <table class="table table-bordered table-sm fs-8 mb-0 align-middle">
                                    <thead class="table-light text-secondary">
                                        <tr>
                                            <th width="40" class="text-center">No</th>
                                            <th width="120">Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Supplier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($item->barangs as $index => $b)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="font-monospace">{{ $b->kode_barang }}</td>
                                                <td class="fw-bold text-dark">{{ $b->nama_barang }}</td>
                                                <td>{{ $b->supplier->nama_supplier ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Tidak ada barang
                                                    terhubung.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Strata Tiers Table --}}
            <div class="card border-0 shadow-sm p-4 rounded bg-white">
                <h6 class="fw-bold text-secondary text-uppercase fs-8 mb-3 border-bottom pb-2">
                    <i class="fa-solid fa-list-ol text-primary me-1"></i> Tingkatan / Strata Diskon
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle fs-7">
                        <thead class="table-light text-secondary text-uppercase tracking-wider">
                            <tr>
                                <th width="60" class="text-center">No</th>
                                @if ($item->tipe === 'supplier')
                                    <th class="text-end" width="220">Min Nominal Pembelian</th>
                                    <th class="text-end" width="220">Max Nominal Pembelian</th>
                                @else
                                    <th class="text-end" width="180">Min Qty</th>
                                    <th class="text-end" width="180">Max Qty</th>
                                @endif
                                <th class="text-center" width="120">Tipe Nilai</th>
                                <th class="text-end">Diskon Reguler / Kredit (dis1)</th>
                                <th class="text-end">Diskon Cash / Tunai (dis2)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($item->details as $index => $d)
                                <tr>
                                    <td class="text-center fw-bold text-secondary">{{ $index + 1 }}</td>
                                    @if ($item->tipe === 'supplier')
                                        <td class="text-end font-monospace fw-semibold">Rp
                                            {{ number_format($d->min_nominal ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-end font-monospace fw-semibold">
                                            {{ $d->max_nominal ? 'Rp ' . number_format($d->max_nominal, 0, ',', '.') : 'Infinity (∞)' }}
                                        </td>
                                    @else
                                        <td class="text-end font-monospace fw-semibold">
                                            {{ number_format($d->min_qty ?? 0, 0, ',', '.') }}
                                            {{ $d->satuan ? $d->satuan->satuan : '' }}
                                        </td>
                                        <td class="text-end font-monospace fw-semibold">
                                            @if($d->max_qty)
                                                {{ number_format($d->max_qty, 0, ',', '.') }}
                                                {{ $d->satuan ? $d->satuan->satuan : '' }}
                                            @else
                                                Infinity (∞)
                                            @endif
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        @if ($d->tipe_nilai === 'persen')
                                            <span class="badge bg-primary px-2 py-0.5 fs-9">Persen (%)</span>
                                        @else
                                            <span class="badge bg-success px-2 py-0.5 fs-9">Nominal (Rp)</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace">
                                        @if ($d->tipe_nilai === 'persen')
                                            {{ floatval($d->dis1) }}%
                                        @else
                                            Rp {{ number_format($d->dis1, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success font-monospace">
                                        @if ($d->tipe_nilai === 'persen')
                                            {{ floatval($d->dis2) }}%
                                        @else
                                            Rp {{ number_format($d->dis2, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">Belum ada strata didefinisikan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
