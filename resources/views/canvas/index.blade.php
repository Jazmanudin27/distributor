@extends('layouts.app')
@section('title', 'DPB (Data Pengambilan Barang)')
@section('content')
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-truck me-2"></i> DPB (Data Pengambilan Barang)</h5>
                <small class="text-white-50 font-12">Kelola pengambilan barang (DPB), rekap penjualan, dan pengembalian
                    kanvas</small>
            </div>
            <a href="{{ route('canvas.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-circle-plus me-1 text-white"></i> Mulai DPB Baru
            </a>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>No. DPB</th>
                            <th>Tanggal</th>
                            <th>Salesman</th>
                            <th class="text-center">Status</th>
                            <th>Keterangan</th>
                            <th width="150" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($canvasSessions as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">
                                    {{ $canvasSessions->firstItem() + $index }}
                                </td>
                                <td class="fw-bold text-primary">{{ $item->no_canvas }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-M-Y') }}</td>
                                <td class="fw-semibold">
                                    {{ $item->sales->name ?? $item->kode_sales }}
                                    <span class="text-secondary small d-block">NIK: {{ $item->kode_sales }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($item->status === 'loading')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="fa-solid fa-truck-moving me-1"></i> Aktif (Di Jalan)
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fa-solid fa-circle-check me-1"></i> Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="text-secondary small">{{ $item->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('canvas.show', $item->id) }}"
                                            class="btn btn-sm btn-outline-info rounded" title="Detail / Rekonsiliasi">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @if ($item->status === 'loading')
                                            <a href="{{ route('canvas.edit', $item->id) }}"
                                                class="btn btn-sm btn-outline-warning rounded"
                                                title="Selesaikan / Bongkar Muatan">
                                                <i class="fa-solid fa-box-open"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('canvas.destroy', $item->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data DPB ini? Stok yang diambil akan dikembalikan ke gudang jika status belum selesai.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-truck d-block fs-3 mb-2 opacity-50"></i>
                                    Belum ada data DPB.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $canvasSessions->links() }}
            </div>
        </div>
    </div>
@endsection
