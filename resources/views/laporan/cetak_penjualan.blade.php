<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan</title>
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
            <h4 class="fw-bold mb-1">LAPORAN PENJUALAN BARANG</h4>
            <div class="small">
                Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d
                {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
            </div>
            @if ($kode_sales)
                @php $salesName = $salesmen->firstWhere('nik', $kode_sales)->name ?? $kode_sales; @endphp
                <div class="small">Salesman: {{ $salesName }}</div>
            @endif
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
                <p>Tidak ada data transaksi penjualan yang ditemukan.</p>
            </div>
        @else
            @if ($jenis_laporan === 'rekap')
                {{-- REKAP TABLES --}}
                <table class="table table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Wilayah</th>
                            <th>Salesman</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totTotal = 0;
                            $totDiskon = 0;
                            $totGrand = 0;
                        @endphp
                        @foreach ($items as $invoice)
                            @php
                                $totTotal += $invoice->total;
                                $totDiskon += $invoice->diskon;
                                $totGrand += $invoice->grand_total;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $num++ }}</td>
                                <td>{{ $invoice->no_faktur }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $invoice->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td>{{ $invoice->pelanggan->wilayah->nama_wilayah ?? '-' }}</td>
                                <td>{{ $invoice->sales->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($invoice->total, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($invoice->diskon, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($invoice->grand_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="6" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totTotal, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totDiskon, 0, ',', '.') }}</td>
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
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Wilayah</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th class="text-end">Qty</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Diskon Item</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totQty = 0;
                            $totDiskonDetail = 0;
                            $totSubtotal = 0;
                        @endphp
                        @foreach ($items as $detail)
                            @php
                                $totQty += $detail->qty;
                                $totDiskonDetail += $detail->diskon;
                                $totSubtotal += $detail->total;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $num++ }}</td>
                                <td>{{ $detail->no_faktur }}</td>
                                <td>{{ \Carbon\Carbon::parse($detail->penjualan->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $detail->penjualan->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td>{{ $detail->penjualan->pelanggan->wilayah->nama_wilayah ?? '-' }}</td>
                                <td>{{ $detail->kode_barang }}</td>
                                <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                <td class="text-end">{{ number_format($detail->qty, 0, ',', '.') }}</td>
                                <td>{{ $detail->barangSatuan->satuan ?? 'PCS' }}</td>
                                <td class="text-end">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($detail->diskon, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($detail->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr class="table-light">
                            <td colspan="7" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end">{{ number_format($totQty, 0, ',', '.') }}</td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($totDiskonDetail, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($totSubtotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    </div>


</body>

</html>
