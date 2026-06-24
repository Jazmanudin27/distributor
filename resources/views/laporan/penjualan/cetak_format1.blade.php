<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan - Format 1</title>
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

@php
    $isExcel = isset($isExcel) ? $isExcel : false;
    $numFmt = function ($val, $decimals = 0) use ($isExcel) {
        if ($isExcel) {
            return $decimals === 0 ? (int) round((float) $val) : round((float) $val, $decimals);
        }
        return number_format((float) $val, $decimals, ',', '.');
    };
@endphp

<body>
    <div class="container-fluid py-3">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN PENJUALAN (FORMAT 1)</h4>
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
            {{-- REKAP TABLES --}}
            <table class="table table-sm align-middle w-100" style="border-collapse: collapse; width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center"
                            style="border: 1px solid #000; text-align: center; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            No</th>
                        <th
                            style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            No Faktur</th>
                        <th
                            style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Tanggal</th>
                        <th
                            style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Pelanggan</th>
                        <th
                            style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Wilayah</th>
                        <th
                            style="border: 1px solid #000; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Salesman</th>
                        <th class="text-end"
                            style="border: 1px solid #000; text-align: right; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Total</th>
                        <th class="text-end"
                            style="border: 1px solid #000; text-align: right; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Diskon</th>
                        <th class="text-end"
                            style="border: 1px solid #000; text-align: right; background-color: #f2f2f2; font-weight: bold; padding: 4px 6px;">
                            Grand Total</th>
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
                            <td class="text-center"
                                style="border: 1px solid #000; text-align: center; padding: 4px 6px;">
                                {{ $num++ }}</td>
                            <td style="border: 1px solid #000; padding: 4px 6px; mso-number-format: '\@';">
                                {{ $invoice->no_faktur }}</td>
                            <td style="border: 1px solid #000; padding: 4px 6px;">
                                {{ \Carbon\Carbon::parse($invoice->tanggal)->format('d-m-Y') }}</td>
                            <td style="border: 1px solid #000; padding: 4px 6px;">
                                {{ $invoice->pelanggan->nama_pelanggan ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 4px 6px;">
                                {{ $invoice->pelanggan->wilayah->nama_wilayah ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 4px 6px;">
                                {{ $invoice->sales->name ?? '-' }}</td>
                            <td class="text-end"
                                style="border: 1px solid #000; text-align: right; padding: 4px 6px; mso-number-format: '#,##0';">
                                {{ $numFmt($invoice->total) }}</td>
                            <td class="text-end"
                                style="border: 1px solid #000; text-align: right; padding: 4px 6px; mso-number-format: '#,##0';">
                                {{ $numFmt($invoice->diskon) }}</td>
                            <td class="text-end fw-bold"
                                style="border: 1px solid #000; text-align: right; font-weight: bold; padding: 4px 6px; mso-number-format: '#,##0';">
                                {{ $numFmt($invoice->grand_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="fw-bold">
                    <tr class="table-light">
                        <td colspan="6" class="text-end"
                            style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 4px 6px;">
                            TOTAL KESELURUHAN:</td>
                        <td class="text-end"
                            style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 4px 6px; mso-number-format: '#,##0';">
                            {{ $numFmt($totTotal) }}</td>
                        <td class="text-end"
                            style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 4px 6px; mso-number-format: '#,##0';">
                            {{ $numFmt($totDiskon) }}</td>
                        <td class="text-end"
                            style="border: 1px solid #000; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 4px 6px; mso-number-format: '#,##0';">
                            {{ $numFmt($totGrand) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</body>

</html>
