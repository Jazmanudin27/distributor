<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Stok Barang</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: "Inter", sans-serif;
            font-size: 11px;
            color: #000;
        }

        .table-sm th,
        .table-sm td {
            font-size: 11px !important;
            padding: 4px 6px !important;
            border: 1px solid #000 !important;
        }

        .table-light th {
            background-color: #f2f2f2 !important;
            color: #000 !important;
        }

        hr {
            border-top: 1px dashed #000;
            opacity: 1;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-3">

        {{-- HEADER DINAMIS SESUAI JENIS LAPORAN --}}
        <div class="text-center mb-3">
            @if ($jenis_laporan === 'rekap')
                <h4 class="fw-bold mb-1">REKAPITULASI STOK BARANG</h4>
                <div class="small">
                    Kategori: <strong>{{ $kategori ?? 'Semua' }}</strong> &nbsp;|&nbsp;
                    Merk: <strong>{{ $merk ?? 'Semua' }}</strong>
                </div>
            @elseif ($jenis_laporan === 'rekap_persediaan')
                <h4 class="fw-bold mb-1">LAPORAN PERSEDIAAN STOK BARANG</h4>
                <div class="small">
                    Periode: <strong>{{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }}</strong>
                    s/d <strong>{{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}</strong>
                    &nbsp;|&nbsp; Supplier: <strong>{{ $kode_supplier ?? 'Semua' }}</strong>
                    &nbsp;|&nbsp; Kategori: <strong>{{ $kategori ?? 'Semua' }}</strong>
                    &nbsp;|&nbsp; Merk: <strong>{{ $merk ?? 'Semua' }}</strong>
                </div>
            @else
                <h4 class="fw-bold mb-1">KARTU / RIWAYAT MUTASI STOK BARANG</h4>
                @if (isset($barang) && $barang)
                    <h5 class="mb-1">{{ $barang->nama_barang }} <span
                            class="text-muted">({{ $barang->kode_barang }})</span></h5>
                @endif
                <div class="small">
                    Periode: <strong>{{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }}</strong>
                    s/d <strong>{{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}</strong>
                </div>
            @endif
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr style="border-top: 1px dashed #000; opacity: 1;">
        </div>

        @if ($jenis_laporan === 'rekap')
            {{-- REKAP STOK --}}
            <div class="rekap-report-container"
                style="font-family: 'Inter', sans-serif; font-size: 13px; color: #000; margin: 5px;">
                <table class="table-bordered table-sm w-100"
                    style="font-size: 11px; border-collapse: collapse; border: 1px solid #ccc; width: 100%;">
                    <thead>
                        <tr style="background-color: #0d6efd; color: white;">
                            <th width="40" class="text-center text-white" style="padding: 4px;">No</th>
                            <th width="120" class="text-white" style="padding: 4px;">Kode Barang</th>
                            <th class="text-white" style="padding: 4px;">Nama Barang</th>
                            <th class="text-white" style="padding: 4px;">Kategori</th>
                            <th class="text-white" style="padding: 4px;">Merk</th>
                            <th width="120" class="text-end text-white" style="padding: 4px;">Stok Min</th>
                            <th width="200" class="text-end text-white" style="padding: 4px;">Stok Fisik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $num = 1; @endphp
                        @foreach ($items as $item)
                            @if (($tampilkan_stok_kosong ?? false) || (float) $item->stok > 0)
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td>{{ $item->kode_barang }}</td>
                                    <td class="fw-bold">{{ $item->nama_barang }}</td>
                                    <td>{{ $item->kategori ?? '-' }}</td>
                                    <td>{{ $item->merk ?? '-' }}</td>
                                    <td class="text-end text-muted font-monospace">
                                        {{ $item->formatStok($item->stok_min) }}
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace">
                                        {{ $item->formatStok($item->stok) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            {{-- DETAIL / BUKU STOK --}}
            @if (!$kode_barang || !isset($barang) || !$barang)
                <div class="text-center py-5">
                    <p class="text-danger">Barang tidak dipilih atau tidak ditemukan.</p>
                </div>
            @else
                <div class="detail-report-container"
                    style="font-family: 'Inter', sans-serif; font-size: 13px; color: #000; margin: 5px;">

                    <table class="table-bordered table-sm w-100"
                        style="font-size: 11px; border-collapse: collapse; border: 1px solid #ccc; width: 100%;">
                        <thead>
                            <tr style="background-color: #0d6efd; color: white;">
                                <th rowspan="2" class="text-center align-middle text-white">No</th>
                                <th rowspan="2" class="text-center align-middle text-white">Tanggal</th>
                                <th rowspan="2" class="text-center align-middle text-white">No Faktur</th>
                                <th rowspan="2" class="text-center align-middle text-white">Pelanggan</th>
                                <th rowspan="2" class="text-center align-middle text-white">Wilayah</th>
                                <th rowspan="2" class="text-center align-middle text-white">Kode Sales</th>
                                <th rowspan="2" class="text-center align-middle text-white">Nama Sales</th>
                                <th rowspan="2" class="text-center align-middle text-white">Keterangan</th>
                                <th colspan="4" class="text-center text-white"
                                    style="background-color: #28a745; color: white; padding: 4px;">PENERIMAAN</th>
                                <th colspan="3" class="text-center text-white"
                                    style="background-color: #dc3545; color: white; padding: 4px;">PENGELUARAN</th>
                                <th class="text-white align-middle text-center">Saldo</th>
                            </tr>
                            <tr style="background-color: #e3f0ff;">
                                <th class="text-center"
                                    style="background-color: #1e7e34; color: white; font-size: 9px; padding: 2px;">
                                    Pembelian</th>
                                <th class="text-center"
                                    style="background-color: #1e7e34; color: white; font-size: 9px; padding: 2px;">
                                    Retur Jual</th>
                                <th class="text-center"
                                    style="background-color: #1e7e34; color: white; font-size: 9px; padding: 2px;">
                                    Batal Jual</th>
                                <th class="text-center"
                                    style="background-color: #1e7e34; color: white; font-size: 9px; padding: 2px;">
                                    Penyesuaian (+)</th>
                                <th class="text-center"
                                    style="background-color: #c82333; color: white; font-size: 9px; padding: 2px;">
                                    Penjualan</th>
                                <th class="text-center"
                                    style="background-color: #c82333; color: white; font-size: 9px; padding: 2px;">
                                    Retur Beli</th>
                                <th class="text-center"
                                    style="background-color: #c82333; color: white; font-size: 9px; padding: 2px;">
                                    Penyesuaian (-)</th>
                                <th class="text-dark fw-bold text-center"
                                    style="background-color: #e3f0ff; padding: 2px;">
                                    {{ $barang->formatStok($stokAwal) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $index => $m)
                                <tr class="lihatActivityPenjualan hover-row" data-no="{{ $m['no_referensi'] }}"
                                    style="cursor:pointer;">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($m['tanggal'])->format('d M Y') }}
                                    </td>
                                    <td>{{ $m['no_referensi'] }}</td>
                                    <td>{{ $m['pelanggan'] }}</td>
                                    <td>{{ $m['wilayah'] }}</td>
                                    <td>{{ $m['kode_sales'] }}</td>
                                    <td>{{ $m['nama_sales'] }}</td>
                                    <td>{{ $m['keterangan'] }}</td>

                                    {{-- PENERIMAAN --}}
                                    <td class="text-start font-monospace text-success">
                                        {{ $m['pembelian_masuk'] > 0 ? $barang->formatStok($m['pembelian_masuk']) : '-' }}
                                    </td>
                                    <td class="text-start font-monospace text-success">
                                        {{ $m['retur_jual'] > 0 ? $barang->formatStok($m['retur_jual']) : '-' }}
                                    </td>
                                    <td class="text-start font-monospace text-success">
                                        {{ isset($m['batal_jual']) && $m['batal_jual'] > 0 ? $barang->formatStok($m['batal_jual']) : '-' }}
                                    </td>
                                    <td class="text-start font-monospace text-success">
                                        {{ $m['opname_masuk'] > 0 ? $barang->formatStok($m['opname_masuk']) : '-' }}
                                    </td>

                                    {{-- PENGELUARAN --}}
                                    <td class="text-start font-monospace text-danger">
                                        {{ $m['penjualan_keluar'] > 0 ? $barang->formatStok($m['penjualan_keluar']) : '-' }}
                                    </td>
                                    <td class="text-start font-monospace text-danger">
                                        {{ $m['retur_beli'] > 0 ? $barang->formatStok($m['retur_beli']) : '-' }}
                                    </td>
                                    <td class="text-start font-monospace text-danger">
                                        {{ $m['opname_keluar'] > 0 ? $barang->formatStok($m['opname_keluar']) : '-' }}
                                    </td>

                                    {{-- SALDO RUNNING --}}
                                    <td class="text-start fw-bold text-dark font-monospace">
                                        {{ $barang->formatStok($m['saldo']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center py-3 text-muted">
                                        Tidak ada riwayat mutasi stok dalam rentang tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Activity Log Modal Component -->
                <div class="modal fade no-print" id="modalActivityPenjualan" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Riwayat Aktivitas</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="activityContent" class="text-center text-muted">
                                    <div class="spinner-border text-primary mb-2" role="status"
                                        style="width:2rem;height:2rem"></div><br>
                                    Memuat data...
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scripts for Modal Activity Log -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                    $(document).on("click", ".lihatActivityPenjualan", function(e) {
                        e.preventDefault();
                        const noFaktur = $(this).data("no");

                        $("#activityContent").html(`
                            <div class="text-center text-muted">
                                <div class="spinner-border text-primary mb-2" role="status" style="width:2rem;height:2rem"></div><br>
                                Memuat data...
                            </div>
                        `);

                        $("#modalActivityPenjualan").modal("show");

                        fetch(`/activity-penjualan/${noFaktur}`)
                            .then((res) => res.json())
                            .then((data) => {
                                if (!data || !data.length) {
                                    $("#activityContent").html(`
                                        <div class="text-center text-muted">
                                            Belum ada aktivitas untuk faktur <b>${noFaktur}</b>.
                                        </div>
                                    `);
                                    return;
                                }

                                let html = `
                                    <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                                        <table class="table table-sm table-bordered table-hover align-middle">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width:10%">Aksi</th>
                                                    <th style="width:15%">User</th>
                                                    <th style="width:45%">Keterangan</th>
                                                    <th style="width:20%">Waktu</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

                                data.forEach((log) => {
                                    html += `
                                        <tr>
                                            <td><span class="badge bg-primary">${log.action}</span></td>
                                            <td>${log.user_name ?? "-"}</td>
                                            <td>${log.description ?? "-"}</td>
                                            <td><small class="text-muted">${new Date(log.created_at).toLocaleString("id-ID")}</small></td>
                                        </tr>`;
                                });

                                html += `</tbody></table></div>`;
                                $("#activityContent").html(html);
                            })
                            .catch(() => {
                                $("#activityContent").html(`
                                    <div class="text-danger text-center">❌ Gagal memuat data. Coba lagi nanti.</div>
                                `);
                            });
                    });
                </script>
            @endif
        @endif
    </div>
</body>

</html>
