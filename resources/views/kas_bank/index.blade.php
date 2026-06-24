@extends('layouts.app')
@section('title', 'Buku Kas & Bank')
@section('content')
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 bg-success bg-gradient text-white position-relative">
                    <div class="position-absolute end-0 top-50 translate-middle-y me-3 opacity-25">
                        <i class="fa-solid fa-arrow-down-long fs-1"></i>
                    </div>
                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1 small" style="font-size: 11px;">Total Debet
                        (Penerimaan)</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalDebet, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 bg-danger bg-gradient text-white position-relative">
                    <div class="position-absolute end-0 top-50 translate-middle-y me-3 opacity-25">
                        <i class="fa-solid fa-arrow-up-long fs-1"></i>
                    </div>
                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1 small" style="font-size: 11px;">Total Kredit
                        (Pengeluaran)</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalKredit, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div
                    class="card-body p-4 {{ $saldo >= 0 ? 'bg-primary' : 'bg-warning' }} bg-gradient text-white position-relative">
                    <div class="position-absolute end-0 top-50 translate-middle-y me-3 opacity-25">
                        <i class="fa-solid fa-scale-balanced fs-1"></i>
                    </div>
                    <h6 class="text-white-50 text-uppercase fw-semibold mb-1 small" style="font-size: 11px;">Saldo Akhir
                    </h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($saldo, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header card-premium-header text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="mb-0 fw-bold">
                    <i class="fa-solid fa-wallet me-2"></i> Buku Kas & Bank
                </h5>
                <small class="text-white-50">Catatan transaksi penerimaan dan pengeluaran kas / rekening bank</small>
            </div>
            <a href="{{ route('kas-bank.create') }}" class="btn btn-primary btn-sm fw-bold hover-scale">
                <i class="fa-solid fa-plus me-1 text-white"></i> Tambah Transaksi
            </a>
        </div>

        <div class="card-body p-4">
            {{-- FILTER SECTION --}}
            <div class="bg-light p-3 rounded mb-4 border border-secondary border-opacity-10">
                <form action="{{ route('kas-bank.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Pilih Rekening Kas/Bank</label>
                        <select name="kode_bank" class="form-select form-select-sm select2-init">
                            <option value="">-- Semua Rekening --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}"
                                    {{ request('kode_bank') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->nama_bank }} - {{ $bank->no_rekening }} ({{ $bank->atas_nama }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-sm"
                            value="{{ request('tanggal_mulai') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                            value="{{ request('tanggal_akhir') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Cari Keterangan</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Kata kunci keterangan..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold" style="height: 31px;"
                            title="Filter Data">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('kas-bank.index') }}"
                            class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center"
                            style="width: 40px; height: 31px;" title="Reset">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase fs-7 tracking-wider">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th width="120">Tanggal</th>
                            <th>Rekening Kas/Bank</th>
                            <th>Keterangan</th>
                            <th width="140" class="text-center">Tipe</th>
                            <th width="150" class="text-end">Jumlah</th>
                            <th width="150">Operator</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr class="hover-row">
                                <td class="text-center text-secondary small fw-bold">
                                    {{ $items->firstItem() + $index }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                <td class="fw-semibold text-dark">
                                    {{ $item->bank->nama_bank ?? '-' }}
                                    <div class="text-muted small fs-8" style="font-size: 11px;">
                                        {{ $item->bank->no_rekening ?? '-' }} - {{ $item->bank->atas_nama ?? '-' }}</div>
                                </td>
                                <td class="text-dark">{{ $item->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    @if ($item->tipe === 'debet')
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fs-8">DEBET
                                            (Masuk)
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 fs-8">KREDIT
                                            (Keluar)</span>
                                    @endif
                                </td>
                                <td
                                    class="text-end fw-bold {{ $item->tipe === 'debet' ? 'text-success' : 'text-danger' }} font-monospace">
                                    Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-secondary small">
                                    <i class="fa-solid fa-user me-1 fs-8"></i>{{ $item->user->name ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group gap-1">
                                        <a href="{{ route('kas-bank.edit', $item->id) }}"
                                            class="btn btn-sm btn-outline-primary rounded" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('kas-bank.destroy', $item->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete rounded"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-wallet d-block fs-3 mb-2 opacity-50"></i>
                                    Tidak ada catatan transaksi kas & bank.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
