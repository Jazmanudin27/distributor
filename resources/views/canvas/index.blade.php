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
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('canvas.index') }}" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">Sales</label>
                        <select name="kode_sales" class="form-select form-select-sm">
                            <option value="">— Semua Sales —</option>
                            @foreach ($salesmen as $s)
                                <option value="{{ $s->nik }}"
                                    {{ request('kode_sales') == $s->nik ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                            value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">S/D Tanggal</label>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                            value="{{ request('tanggal_akhir') }}">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label form-label-sm mb-1 text-secondary fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">— Semua Status —</option>
                            <option value="loading" {{ request('status') == 'loading' ? 'selected' : '' }}>Aktif (Di
                                Jalan)</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                        </select>
                    </div>
                    <div class="col-sm-auto d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('canvas.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

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
                                    @if ($item->status === 'pending')
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="fa-solid fa-clock me-1"></i> Menunggu Approval
                                        </span>
                                    @elseif ($item->status === 'loading')
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
                                        @if ($item->status === 'pending')
                                            <a href="{{ route('canvas.edit', $item->id) }}"
                                                class="btn btn-sm btn-outline-warning rounded" title="Edit DPB">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <form action="{{ route('canvas.approve', $item->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menyetujui DPB ini? Stok gudang akan langsung dipotong.')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded"
                                                    title="Setujui & Potong Stok">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('canvas.show', $item->id) }}"
                                            class="btn btn-sm btn-outline-info rounded" title="Detail / Rekonsiliasi">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('canvas.print', $item->id) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary rounded" title="Cetak Laporan Penjualan">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
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
