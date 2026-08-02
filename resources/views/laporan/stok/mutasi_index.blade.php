@extends('layouts.app')
@section('title', 'Riwayat & Log Mutasi Stok Barang')
@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-12">
            <div class="card shadow border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header card-premium-header text-white py-4 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="mb-1 fw-bold text-white">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Riwayat & Log Mutasi Stok Barang
                        </h4>
                        <p class="text-white-50 small mb-0">
                            Lihat rincian riwayat transaksi barang masuk & keluar serta hapus data mutasi yang salah/tidak valid.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('laporan.stok.saldo-awal.index') }}" class="btn btn-light btn-sm fw-semibold shadow-sm rounded-pill px-3">
                            <i class="fa-solid fa-calculator me-1"></i> Setting Saldo Awal
                        </a>
                        <a href="{{ route('laporan.stok') }}" class="btn btn-outline-light btn-sm fw-semibold shadow-sm rounded-pill px-3">
                            <i class="fa-solid fa-arrow-left me-1"></i> Laporan Stok
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- FILTER BAR --}}
                    <form action="{{ route('laporan.stok.mutasi.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary mb-1">Dari Tanggal</label>
                            <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ request('tanggal_mulai') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary mb-1">Sampai Tanggal</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ request('tanggal_akhir') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Pilih Barang</label>
                            <select name="kode_barang" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Barang --</option>
                                @foreach ($barangsList as $b)
                                    <option value="{{ $b->kode_barang }}" {{ request('kode_barang') == $b->kode_barang ? 'selected' : '' }}>
                                        {{ $b->kode_barang }} - {{ $b->nama_barang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-secondary mb-1">Jenis Transaksi</label>
                            <select name="jenis_transaksi" class="form-select form-select-sm select2-init">
                                <option value="">-- Semua Jenis --</option>
                                @foreach ($jenisTransaksis as $jt)
                                    <option value="{{ $jt }}" {{ request('jenis_transaksi') == $jt ? 'selected' : '' }}>
                                        {{ $jt }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary mb-1">Cari Ref / Keterangan</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="No Ref / Keterangan..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary fw-semibold">
                                    <i class="fa-solid fa-search me-1"></i> Cari
                                </button>
                                <a href="{{ route('laporan.stok.mutasi.index') }}" class="btn btn-outline-secondary fw-semibold">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <hr class="text-muted my-3 opacity-25">

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle text-sm">
                            <thead class="table-dark text-center align-middle">
                                <tr>
                                    <th width="40">No</th>
                                    <th width="95">Tanggal</th>
                                    <th width="140">No. Referensi</th>
                                    <th>Kode & Nama Barang</th>
                                    <th width="140">Jenis Transaksi</th>
                                    <th width="130">Masuk (+)</th>
                                    <th width="130">Keluar (-)</th>
                                    <th width="120">Saldo Awal &rarr; Akhir</th>
                                    <th width="110">User</th>
                                    <th width="80" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mutasis as $index => $m)
                                    @php
                                        $barang = $barangsMap->get($m->kode_barang);
                                        $qtyMasukFloat = (float)$m->qty_masuk;
                                        $qtyKeluarFloat = (float)$m->qty_keluar;

                                        // Badge class based on transaction type
                                        $badgeClass = 'bg-secondary';
                                        if (in_array($m->jenis_transaksi, ['Pembelian', 'Pembelian (Edit)'])) {
                                            $badgeClass = 'bg-success';
                                        } elseif (in_array($m->jenis_transaksi, ['Penjualan', 'Penjualan (Edit)'])) {
                                            $badgeClass = 'bg-primary';
                                        } elseif (str_contains(strtolower($m->jenis_transaksi), 'retur')) {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif (str_contains(strtolower($m->jenis_transaksi), 'opname')) {
                                            $badgeClass = 'bg-info text-dark';
                                        } elseif ($m->jenis_transaksi === 'Saldo Awal') {
                                            $badgeClass = 'bg-purple text-white';
                                        } elseif (str_contains(strtolower($m->jenis_transaksi), 'batal')) {
                                            $badgeClass = 'bg-danger';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center font-monospace">{{ ($mutasis->currentPage() - 1) * $mutasis->perPage() + $index + 1 }}</td>
                                        <td class="text-center font-monospace small">
                                            {{ \Carbon\Carbon::parse($m->tanggal)->format('d/m/Y') }}
                                        </td>
                                        <td class="font-monospace small fw-bold">
                                            {{ $m->no_referensi }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $m->nama_barang ?? $m->kode_barang }}</div>
                                            <div class="text-muted small font-monospace">
                                                {{ $m->kode_barang }} 
                                                @if($m->kategori || $m->merk)
                                                    &bull; <span class="badge bg-light text-secondary border">{{ $m->kategori ?? '-' }}</span>
                                                @endif
                                            </div>
                                            @if($m->keterangan)
                                                <div class="text-muted fst-italic small mt-0.5" style="font-size: 11px;">
                                                    <i class="fa-regular fa-comment-dots me-1"></i>{{ $m->keterangan }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $badgeClass }} px-2 py-1 fs-8">
                                                {{ $m->jenis_transaksi }}
                                            </span>
                                        </td>
                                        <td class="text-end font-monospace">
                                            @if ($qtyMasukFloat > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold fs-7">
                                                    +{{ $qtyMasukFloat }}
                                                </span>
                                                @if ($barang)
                                                    <div class="text-muted small" style="font-size: 10px;">
                                                        {{ $barang->formatStok($qtyMasukFloat) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted opacity-50">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace">
                                            @if ($qtyKeluarFloat > 0)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold fs-7">
                                                    -{{ $qtyKeluarFloat }}
                                                </span>
                                                @if ($barang)
                                                    <div class="text-muted small" style="font-size: 10px;">
                                                        {{ $barang->formatStok($qtyKeluarFloat) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted opacity-50">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace small">
                                            <div>{{ (float)$m->saldo_awal }} &rarr; <strong class="text-primary">{{ (float)$m->saldo_akhir }}</strong></div>
                                        </td>
                                        <td class="small text-secondary text-center">
                                            {{ $m->nama_user ?? '-' }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded px-2 btn-delete-mutasi" 
                                                    data-id="{{ $m->id }}"
                                                    data-ref="{{ $m->no_referensi }}"
                                                    data-jenis="{{ $m->jenis_transaksi }}"
                                                    data-barang="{{ $m->nama_barang ?? $m->kode_barang }}"
                                                    data-masuk="{{ $qtyMasukFloat }}"
                                                    data-keluar="{{ $qtyKeluarFloat }}"
                                                    title="Hapus Mutasi">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                            Tidak ada riwayat mutasi stok ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($mutasis->hasPages())
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
                            <div class="small text-muted">
                                Menampilkan {{ $mutasis->firstItem() }} - {{ $mutasis->lastItem() }} dari {{ $mutasis->total() }} mutasi
                            </div>
                            <div>
                                {{ $mutasis->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRM DELETE MUTASI -->
    <div class="modal fade" id="modalDeleteMutasi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Konfirmasi Hapus Mutasi Stok
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formDeleteMutasi" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body p-4">
                        <p class="mb-3">Apakah Anda yakin ingin menghapus data mutasi stok berikut?</p>
                        
                        <div class="bg-light p-3 rounded-3 border mb-3 small font-monospace">
                            <div><strong>Jenis:</strong> <span id="delMutasiJenis" class="badge bg-secondary"></span></div>
                            <div><strong>No. Ref:</strong> <span id="delMutasiRef" class="fw-bold text-dark"></span></div>
                            <div><strong>Barang:</strong> <span id="delMutasiBarang" class="text-primary"></span></div>
                            <div><strong>Qty:</strong> <span id="delMutasiQty" class="fw-bold"></span></div>
                        </div>

                        <div class="form-check form-switch bg-warning-subtle p-3 rounded-3 border border-warning-subtle mb-2">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="adjust_stok" id="delAdjustStok" value="1" checked style="cursor: pointer;">
                            <label class="form-check-label fw-bold text-dark fs-7" for="delAdjustStok" style="cursor: pointer;">
                                Sesuaikan / kembalikan stok fisik real-time barang saat ini
                            </label>
                            <small class="text-muted d-block mt-1">
                                Jika di-centang, menghapus mutasi penerimaan akan mengurangi stok, dan menghapus mutasi pengeluaran akan mengembalikan stok.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-2 px-4">
                        <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-bold px-4">
                            <i class="fa-solid fa-trash me-1"></i> Ya, Hapus Mutasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2-init').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Handle Click Delete Mutasi Button
            $('.btn-delete-mutasi').on('click', function() {
                const id = $(this).data('id');
                const ref = $(this).data('ref');
                const jenis = $(this).data('jenis');
                const barang = $(this).data('barang');
                const masuk = parseFloat($(this).data('masuk')) || 0;
                const keluar = parseFloat($(this).data('keluar')) || 0;

                let qtyText = '';
                if (masuk > 0) qtyText = `+${masuk} (Masuk)`;
                else if (keluar > 0) qtyText = `-${keluar} (Keluar)`;
                else qtyText = '0';

                $('#delMutasiJenis').text(jenis);
                $('#delMutasiRef').text(ref);
                $('#delMutasiBarang').text(barang);
                $('#delMutasiQty').text(qtyText);

                // Disable adjust_stok for Saldo Awal since Saldo Awal doesn't affect real-time stock
                if (jenis === 'Saldo Awal') {
                    $('#delAdjustStok').prop('checked', false).prop('disabled', true);
                } else {
                    $('#delAdjustStok').prop('checked', true).prop('disabled', false);
                }

                // Set form action URL
                const actionUrl = "{{ route('laporan.stok.mutasi.destroy', ':id') }}".replace(':id', id);
                $('#formDeleteMutasi').attr('action', actionUrl);

                // Show modal
                $('#modalDeleteMutasi').modal('show');
            });
        });
    </script>
@endpush
