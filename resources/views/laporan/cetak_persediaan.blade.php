<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Persediaan Stok Good Stok</title>
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
            width: 150%;
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

        tr.hover-row:hover td {
            background: #f4faff !important;
        }

        tfoot td {
            font-weight: bold;
            background: #eaf1f8 !important;
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

        {{-- HEADER --}}
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-1">LAPORAN PERSEDIAAN STOK BARANG</h4>
            <div class="">
                Periode: <strong>{{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }}</strong>
                s/d <strong>{{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}</strong>
                &nbsp;|&nbsp; Supplier:
                <strong>
                    @if ($kode_supplier)
                        @php $suppObj = $suppliers->firstWhere('kode_supplier', $kode_supplier); @endphp
                        {{ $suppObj ? $suppObj->nama_supplier : $kode_supplier }}
                    @else
                        Semua
                    @endif
                </strong>
            </div>
            <div class=" text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr style="border-top: 1px dashed #000; opacity: 1;">
        </div>

        {{-- TABEL --}}
        <div style="font-family: 'Inter', sans-serif; font-size: 13px; color: #000; margin: 5px;">
            <table class="table table-sm align-middle w-100">
                <thead>
                    <tr>
                        <th rowspan="2" width="30"
                            style="background-color: #0d6efd !important; color: white !important;">No</th>
                        <th rowspan="2" width="90"
                            style="background-color: #0d6efd !important; color: white !important;">Kode Barang</th>
                        <th rowspan="2" width="90"
                            style="background-color: #0d6efd !important; color: white !important;">Kode Item</th>
                        <th rowspan="2" style="background-color: #0d6efd !important; color: white !important;">Nama
                            Barang</th>
                        <th rowspan="2" width="60"
                            style="background-color: #0d6efd !important; color: white !important;">Jenis</th>
                        <th rowspan="2" width="100"
                            style="background-color: #0d6efd !important; color: white !important;">Merk</th>
                        <th rowspan="2" width="100"
                            style="background-color: #0d6efd !important; color: white !important;">Stok Awal</th>
                        <th colspan="3" class="text-center"
                            style="background-color: #28a745 !important; color: white !important;">PENERIMAAN</th>
                        <th colspan="3" class="text-center"
                            style="background-color: #dc3545 !important; color: white !important;">PENGELUARAN</th>
                        <th rowspan="2" width="100"
                            style="background-color: #0d6efd !important; color: white !important;">Stok Akhir</th>
                        <th rowspan="2" width="100"
                            style="background-color: #0d6efd !important; color: white !important;">Stok Akhir (Conversi)
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center" style="background-color: #1e7e34 !important; color: white !important;">
                            Pembelian</th>
                        <th class="text-center" style="background-color: #1e7e34 !important; color: white !important;">
                            Retur Jual</th>
                        <th class="text-center" style="background-color: #1e7e34 !important; color: white !important;">
                            Penyesuaian (+)</th>
                        <th class="text-center" style="background-color: #c82333 !important; color: white !important;">
                            Penjualan</th>
                        <th class="text-center" style="background-color: #c82333 !important; color: white !important;">
                            Retur Beli</th>
                        <th class="text-center" style="background-color: #c82333 !important; color: white !important;">
                            Penyesuaian (-)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        @php $b = $item['barang']; @endphp
                        <tr class="hover-row" style="cursor:pointer;"
                            onclick="submitKartuStok('{{ $item['kode_barang'] }}')">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-monospace text-secondary">{{ $item['kode_barang'] }}</td>
                            <td class="font-monospace">{{ $item['kode_item'] ?? '-' }}</td>
                            <td class="fw-bold">{{ $item['nama_barang'] }}</td>
                            <td class="text-center">{{ $item['kategori'] ?? '-' }}</td>
                            <td>{{ $item['merk'] ?? '-' }}</td>
                            <td class="text-end font-monospace">{{ $b->formatStok($item['stok_awal']) }}</td>

                            {{-- PENERIMAAN --}}
                            <td class="text-end font-monospace text-success">
                                {{ $item['pembelian'] > 0 ? $b->formatStok($item['pembelian']) : '-' }}
                            </td>
                            <td class="text-end font-monospace text-success">
                                {{ $item['retur_jual'] > 0 ? $b->formatStok($item['retur_jual']) : '-' }}
                            </td>
                            <td class="text-end font-monospace text-success">
                                {{ $item['penyesuaian_masuk'] > 0 ? $b->formatStok($item['penyesuaian_masuk']) : '-' }}
                            </td>

                            {{-- PENGELUARAN --}}
                            <td class="text-end font-monospace text-danger">
                                {{ $item['penjualan'] > 0 ? $b->formatStok($item['penjualan']) : '-' }}
                            </td>
                            <td class="text-end font-monospace text-danger">
                                {{ $item['retur_beli'] > 0 ? $b->formatStok($item['retur_beli']) : '-' }}
                            </td>
                            <td class="text-end font-monospace text-danger">
                                {{ $item['penyesuaian_keluar'] > 0 ? $b->formatStok($item['penyesuaian_keluar']) : '-' }}
                            </td>

                            <td class="text-end fw-bold font-monospace">{{ $b->formatStok($item['stok_akhir']) }}</td>
                            <td class="text-end fw-bold font-monospace">
                                {{ number_format($item['stok_akhir'], 0, ',', '.') }} {{ $item['satuan'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-4 text-muted">
                                Tidak ada data persediaan stok barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="15" class="text-center fw-bold text-muted">
                                &mdash; Akhir Laporan &mdash;
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>{{-- end container-fluid --}}

    <script>
        function submitKartuStok(kodeBarang) {
            const start = "{{ $tanggal_mulai }}";
            const end = "{{ $tanggal_akhir }}";
            const url =
                `{{ route('laporan.stok.cetak') }}?jenis_laporan=detail&kode_barang=${kodeBarang}&tanggal_mulai=${start}&tanggal_akhir=${end}`;
            window.open(url, '_blank');
        }
    </script>


</body>

</html>
