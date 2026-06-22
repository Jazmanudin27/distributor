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

        .table-rowspan th,
        .table-rowspan td {
            font-size: 11px !important;
            padding: 5px 6px !important;
            border: 1px solid #999 !important;
            white-space: nowrap !important;
        }

        .num-format {
            text-align: right !important;
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
                {{-- DETAIL ROWSPAN TABLES (Format 3) --}}
                <table class="table table-sm align-middle w-100 table-rowspan">
                    <thead>
                        <tr style="background:#0d6efd; color:#fff;">
                            <th class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>No. Faktur</th>
                            <th>Kode</th>
                            <th>Nama Pelanggan</th>
                            <th>Alamat</th>
                            <th>Sales</th>
                            <th>Wilayah</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Jenis</th>
                            <th>Merk</th>
                            <th class="text-center">Qty</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">D1</th>
                            <th class="text-center">D2</th>
                            <th class="text-center">D3</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Bruto</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Neto</th>
                            <th class="text-end">Bayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">JT</th>
                            <th class="text-center">Diinput</th>
                            <th class="text-center">Update</th>
                            <th>Input Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                            $totalBrutoSum = 0;
                            $totalDiskonSum = 0;
                            $totalNetoSum = 0;
                            $totalBayarSum = 0;
                            $totalSisaSum = 0;
                        @endphp
                        @foreach ($items as $invoice)
                            @php
                                $totalBrutoSum += $invoice->total;
                                $totalDiskonSum += $invoice->diskon;
                                $totalNetoSum += $invoice->grand_total;
                                $totalBayarSum += $invoice->total_bayar;
                                $totalSisaSum += $invoice->sisa_bayar;
                            @endphp
                            @foreach ($invoice->details as $detail)
                                <tr>
                                    <td class="text-center">{{ $num++ }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($invoice->tanggal)->format('d-M-Y') }}</td>
                                    <td class="text-center">{{ $invoice->no_faktur }}</td>
                                    <td class="text-center">{{ $invoice->kode_pelanggan }}</td>
                                    <td>{{ $invoice->pelanggan->nama_pelanggan ?? '-' }}</td>
                                    <td>{{ $invoice->pelanggan->alamat ?? '-' }}</td>
                                    <td>{{ $invoice->sales->name ?? '-' }}</td>
                                    <td>{{ $invoice->pelanggan->wilayah->nama_wilayah ?? '-' }}</td>

                                    <td>{{ $detail->kode_barang }}</td>
                                    <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                                    <td>{{ $detail->barang->kategori ?? '-' }}</td>
                                    <td>{{ $detail->barang->merk ?? '-' }}</td>
                                    <td class="text-center" style="mso-number-format:'#,##0.00';">
                                        {{ number_format($detail->qty, 2, ',', '.') }}</td>
                                    <td>{{ $detail->barangSatuan->satuan ?? 'PCS' }}</td>
                                    <td class="text-end" style="mso-number-format:'#,##0';">
                                        {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                    <td class="text-center" style="mso-number-format:'#,##0.00';">
                                        {{ $detail->diskon1_persen > 0 ? number_format($detail->diskon1_persen, 2, ',', '.') : '' }}
                                    </td>
                                    <td class="text-center" style="mso-number-format:'#,##0.00';">
                                        {{ $detail->diskon2_persen > 0 ? number_format($detail->diskon2_persen, 2, ',', '.') : '' }}
                                    </td>
                                    <td class="text-center" style="mso-number-format:'#,##0.00';">
                                        {{ $detail->diskon3_persen > 0 ? number_format($detail->diskon3_persen, 2, ',', '.') : '' }}
                                    </td>
                                    <td class="text-end" style="mso-number-format:'#,##0';">
                                        {{ number_format($detail->total, 0, ',', '.') }}</td>

                                    <td class="text-end" style="mso-number-format:'#,##0';">
                                        {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                    <td class="text-end" style="mso-number-format:'#,##0';">
                                        {{ number_format($invoice->diskon, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold" style="mso-number-format:'#,##0';">
                                        {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                                    <td class="text-end text-success" style="mso-number-format:'#,##0';">
                                        {{ number_format($invoice->total_bayar, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger" style="mso-number-format:'#,##0';">
                                        {{ number_format($invoice->sisa_bayar, 0, ',', '.') }}</td>
                                    <td class="text-center fw-bold">
                                        <span
                                            class="{{ $invoice->status_pembayaran === 'Lunas' ? 'text-success' : 'text-danger' }}">
                                            {{ $invoice->status_pembayaran }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold"
                                        style="color: {{ strtolower(substr($invoice->jenis_transaksi, 0, 1)) === 'k' ? 'orange' : 'green' }};">
                                        {{ strtoupper(substr($invoice->jenis_transaksi, 0, 1)) }}
                                    </td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($invoice->created_at)->format('d M Y H:i') }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($invoice->updated_at)->format('d M Y H:i') }}</td>
                                    <td class="text-start">{{ $invoice->user->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold table-light">
                        <tr>
                            <td colspan="19" class="text-end">TOTAL KESELURUHAN:</td>
                            <td class="text-end" style="mso-number-format:'#,##0';">
                                {{ number_format($totalBrutoSum, 0, ',', '.') }}</td>
                            <td class="text-end" style="mso-number-format:'#,##0';">
                                {{ number_format($totalDiskonSum, 0, ',', '.') }}</td>
                            <td class="text-end" style="mso-number-format:'#,##0';">
                                {{ number_format($totalNetoSum, 0, ',', '.') }}</td>
                            <td class="text-end text-success" style="mso-number-format:'#,##0';">
                                {{ number_format($totalBayarSum, 0, ',', '.') }}</td>
                            <td class="text-end text-danger" style="mso-number-format:'#,##0';">
                                {{ number_format($totalSisaSum, 0, ',', '.') }}</td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    </div>


</body>

</html>
