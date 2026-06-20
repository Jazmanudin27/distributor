@extends('layouts.app')
@section('title', 'Detail Stok Opname: ' . $item->no_opname)
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-clipboard-list me-2"></i> Detail Stok Opname
                </h5>
                <span class="font-monospace text-white-50">{{ $item->no_opname }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('stok-opname.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
                @can('edit-stok_opname')
                    <a href="{{ route('stok-opname.edit', $item->no_opname) }}" class="btn btn-warning btn-sm text-dark fw-bold hover-scale">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Opname
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body p-4">
            {{-- HEADER METADATA --}}
            <div class="row g-4 mb-4 border-bottom pb-4">
                <div class="col-md-6">
                    <h6 class="text-secondary fw-bold text-uppercase fs-8 mb-2">Informasi Transaksi</h6>
                    <table class="table table-borderless table-sm small mb-0">
                        <tr>
                            <td width="130" class="text-secondary p-0">No Opname</td>
                            <td class="fw-bold text-dark p-0">: <span class="font-monospace bg-light px-1.5 rounded">{{ $item->no_opname }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-secondary p-0">Tanggal Opname</td>
                            <td class="fw-bold text-dark p-0">: {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h6 class="text-secondary fw-bold text-uppercase fs-8 mb-2">Pencatat</h6>
                    <table class="table table-borderless table-sm small mb-0">
                        <tr>
                            <td width="130" class="text-secondary p-0">Operator Gudang</td>
                            <td class="fw-bold text-dark p-0">: {{ $item->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary p-0">Email Operator</td>
                            <td class="fw-bold text-dark p-0">: {{ $item->user->email ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- KETERANGAN --}}
            @if ($item->keterangan)
                <div class="bg-light p-3 rounded mb-4 border border-light-subtle">
                    <h6 class="fw-bold text-secondary fs-8 mb-1">Catatan / Alasan Penyesuaian:</h6>
                    <p class="text-dark small mb-0">{{ $item->keterangan }}</p>
                </div>
            @endif

            {{-- LIST DETAILS --}}
            <div class="card border shadow-sm rounded">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-list me-1 text-primary"></i> Daftar Penyesuaian Detail Barang
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light text-secondary text-uppercase">
                            <tr>
                                <th width="60" class="text-center">No</th>
                                <th width="150">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th width="120" class="text-end">Stok Sistem</th>
                                <th width="120" class="text-end">Stok Fisik</th>
                                <th width="150" class="text-center">Selisih</th>
                                <th>Keterangan Item</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->details as $index => $detail)
                                @php
                                    $diff = (float)$detail->selisih;
                                    $badge = 'bg-secondary';
                                    $sign = '';
                                    if ($diff > 0) {
                                        $badge = 'bg-success-subtle text-success border border-success-subtle';
                                        $sign = '+';
                                    } elseif ($diff < 0) {
                                        $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center text-secondary font-monospace fw-bold">{{ $index + 1 }}</td>
                                    <td><span class="badge bg-light text-secondary border font-monospace">{{ $detail->kode_barang }}</span></td>
                                    <td class="fw-semibold text-dark">{{ $detail->barang->nama_barang ?? '-' }}</td>
                                    <td class="text-end font-monospace">{{ number_format($detail->stok_sistem, 2, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bold text-dark">
                                        <div>{{ number_format($detail->stok_fisik, 2, ',', '.') }}</div>
                                        @php
                                            $satuans = $detail->barang->satuans ?? collect();
                                            $breakdowns = [];
                                            if ($satuans->count() > 0) {
                                                $sorted = $satuans->sortByDesc('isi');
                                                $remaining = (float)$detail->stok_fisik;
                                                foreach ($sorted as $sat) {
                                                    $factor = (float)($sat->isi ?: 1);
                                                    $unitQty = floor($remaining / $factor);
                                                    if ($unitQty > 0) {
                                                        $breakdowns[] = $unitQty . ' ' . $sat->satuan;
                                                        $remaining = fmod($remaining, $factor);
                                                    }
                                                }
                                                if ($remaining > 0 && $sorted->count() > 0) {
                                                    $last = $sorted->last();
                                                    $breakdowns[] = $remaining . ' ' . $last->satuan;
                                                }
                                            } else {
                                                $breakdowns[] = $detail->stok_fisik . ' PCS';
                                            }
                                            $breakdownText = implode(', ', $breakdowns) ?: '0 PCS';
                                        @endphp
                                        <div class="text-muted small fw-normal mt-0.5" style="font-size: 0.73rem;">
                                            ({{ $breakdownText }})
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badge }} px-2.5 py-1.5 fw-bold font-monospace fs-8">
                                            {{ $sign }}{{ number_format($detail->selisih, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $detail->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
