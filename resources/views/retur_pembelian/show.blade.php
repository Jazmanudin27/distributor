@extends('layouts.app')
@section('title', 'Detail Retur Pembelian: ' . $item->no_retur)
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-rotate-left me-2"></i> Detail Retur Pembelian
                </h5>
                <span class="font-monospace text-white-50">{{ $item->no_retur }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('retur-pembelian.index') }}" class="btn btn-light btn-sm fw-bold hover-scale">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
                @can('edit-retur_pembelian')
                    <a href="{{ route('retur-pembelian.edit', $item->no_retur) }}" class="btn btn-warning btn-sm text-dark fw-bold hover-scale">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Retur
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body p-4">
            {{-- HEADER INFO --}}
            <div class="row g-4 mb-4 border-bottom pb-4">
                {{-- Supplier Info --}}
                <div class="col-md-4">
                    <h6 class="text-secondary fw-bold text-uppercase fs-8 mb-2">Informasi Supplier</h6>
                    <h5 class="fw-bold text-primary mb-1">{{ $item->supplier->nama_supplier ?? '-' }}</h5>
                    <p class="text-muted small mb-0">
                        Kode: <span class="font-monospace fw-bold">{{ $item->kode_supplier }}</span><br>
                        Telp: {{ $item->supplier->no_hp ?? '-' }}<br>
                        Alamat: {{ $item->supplier->alamat ?? '-' }}
                    </p>
                </div>

                {{-- Retur Info --}}
                <div class="col-md-4">
                    <h6 class="text-secondary fw-bold text-uppercase fs-8 mb-2">Metadata Retur</h6>
                    <table class="table table-borderless table-sm small mb-0">
                        <tr>
                            <td width="130" class="text-secondary p-0">No Retur</td>
                            <td class="fw-bold text-dark p-0">: <span class="font-monospace bg-light px-1.5 rounded">{{ $item->no_retur }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-secondary p-0">Tanggal Retur</td>
                            <td class="fw-bold text-dark p-0">: {{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary p-0">No Faktur Asal</td>
                            <td class="fw-bold text-dark p-0">: 
                                @if ($item->no_faktur)
                                    <a href="{{ route('pembelian.show', $item->no_faktur) }}" class="font-monospace text-primary text-decoration-none">
                                        {{ $item->no_faktur }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Status & Total --}}
                <div class="col-md-4">
                    <h6 class="text-secondary fw-bold text-uppercase fs-8 mb-2">Kondisi & Jenis</h6>
                    <table class="table table-borderless table-sm small mb-0">
                        <tr>
                            <td width="130" class="text-secondary p-0">Jenis Retur</td>
                            <td class="p-0">: 
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0.5 fw-bold">
                                    {{ $item->jenis_retur }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary p-0">Kondisi Barang</td>
                            <td class="p-0">: 
                                <span class="badge bg-{{ $item->kondisi == 'Baik' ? 'success' : 'danger' }}-subtle text-{{ $item->kondisi == 'Baik' ? 'success' : 'danger' }}-emphasis border border-{{ $item->kondisi == 'Baik' ? 'success' : 'danger' }}-subtle px-2 py-0.5 fw-bold">
                                    {{ $item->kondisi }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary p-0">Total Nilai Retur</td>
                            <td class="fw-bold text-success fs-6 p-0">: Rp {{ number_format((float) $item->total, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- KETERANGAN --}}
            @if ($item->keterangan)
                <div class="bg-light p-3 rounded mb-4 border border-light-subtle">
                    <h6 class="fw-bold text-secondary fs-8 mb-1">Catatan / Alasan Retur:</h6>
                    <p class="text-dark small mb-0">{{ $item->keterangan }}</p>
                </div>
            @endif

            {{-- ITEMS LIST --}}
            <div class="card border shadow-sm rounded">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-list me-1 text-primary"></i> Daftar Item yang Diretur
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light text-secondary text-uppercase">
                            <tr>
                                <th width="60" class="text-center">No</th>
                                <th width="150">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th width="150" class="text-center">Satuan</th>
                                <th width="120" class="text-end">Jumlah Retur</th>
                                <th width="180" class="text-end">Harga Retur</th>
                                <th width="200" class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($item->details as $index => $detail)
                                <tr>
                                    <td class="text-center text-secondary font-monospace fw-bold">{{ $index + 1 }}</td>
                                    <td><span class="badge bg-light text-secondary border font-monospace">{{ $detail->kode_barang }}</span></td>
                                    <td class="fw-semibold text-dark">{{ $detail->barang->nama_barang ?? '-' }}</td>
                                    <td class="text-center">{{ $detail->barangSatuan->satuan ?? $detail->satuan ?? '-' }}</td>
                                    <td class="text-end font-monospace fw-bold">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                                    <td class="text-end font-monospace text-secondary">Rp {{ number_format($detail->harga_retur, 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace fw-bold text-dark pe-3">Rp {{ number_format($detail->subtotal_retur, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            {{-- Table Footer for Totals --}}
                            <tr class="table-light border-top border-secondary-subtle">
                                <td colspan="6" class="text-end fw-bold text-secondary">GRAND TOTAL RETUR :</td>
                                <td class="text-end font-monospace fw-extrabold text-success fs-6 pe-3">
                                    Rp {{ number_format((float) $item->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
