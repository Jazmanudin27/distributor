<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Retur Penjualan</title>
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
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN RETUR PENJUALAN BARANG</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d
                {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            @if ($kode_pelanggan)
                @php $pelangganName = $pelanggans->firstWhere('kode_pelanggan', $kode_pelanggan)->nama_pelanggan ?? $kode_pelanggan; @endphp
                <div class="small">Pelanggan: {{ $pelangganName }}</div>
            @endif
            <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
            <hr>
        </div>

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data transaksi retur penjualan yang ditemukan.</p>
            </div>
        @else
            @if ($jenis_laporan === 'rekap')
                {{-- REKAP TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Retur</th>
                            <th>No Faktur Asal</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Salesman</th>
                            <th>Jenis Retur</th>
                            <th>Keterangan</th>
                            <th class="text-end">Total Retur (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totGrand = 0;
                        @endphp
                        @foreach ($items as $retur)
                            @php
                                $totGrand += $retur->total;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $num++ }}</td>
                                <td>{{ $retur->no_retur }}</td>
                                <td>{{ $retur->no_faktur ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($retur->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $retur->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td>{{ $retur->sales->name ?? '-' }}</td>
                                <td>{{ $retur->jenis_retur ?? '-' }}</td>
                                <td>{{ $retur->keterangan ?? '-' }}</td>
                                <td class="text-end fw-bold">{{ number_format($retur->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="8" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totGrand, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                {{-- DETAIL TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Retur</th>
                            <th>No Faktur Asal</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Salesman</th>
                            <th>Supplier</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kondisi</th>
                            <th class="text-end">Qty</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga (Rp)</th>
                            <th class="text-end">Diskon (Rp)</th>
                            <th class="text-end">Net Retur (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totQty = 0;
                            $totDiskon = 0;
                            $totNet = 0;
                        @endphp
                        @foreach ($items as $detail)
                            @php
                                $diskon = $detail->total_diskon_rupiah ?? 0;
                                $net = $detail->subtotal_retur - $diskon;
                                $totQty += $detail->qty;
                                $totDiskon += $diskon;
                                $totNet += $net;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $num++ }}</td>
                                <td>{{ $detail->no_retur }}</td>
                                <td>{{ $detail->returPenjualan->no_faktur ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($detail->returPenjualan->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $detail->returPenjualan->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td>{{ $detail->returPenjualan->sales->name ?? '-' }}</td>
                                <td>{{ $detail->barang->supplier->nama_supplier ?? '-' }}</td>
                                <td>{{ $detail->kode_barang }}</td>
                                <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                <td>{{ $detail->kondisi ?? '-' }}</td>
                                <td class="text-end">{{ number_format($detail->qty, 0, ',', '.') }}</td>
                                <td>{{ $detail->barangSatuan->satuan ?? 'PCS' }}</td>
                                <td class="text-end">{{ number_format($detail->harga_retur, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($diskon, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($net, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="10" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totQty, 0, ',', '.') }}</td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($totDiskon, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totNet, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    </div>


</body>

</html>
