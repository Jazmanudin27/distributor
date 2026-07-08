<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Pembayaran Piutang</title>
    <!-- Bootstrap CSS -->
    @if (!isset($isExcel) || !$isExcel)
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: "Inter", sans-serif;
            font-size: 10px;
            color: #000;
        }

        .table-sm th,
        .table-sm td {
            font-size: 10px !important;
            padding: 4px 6px !important;
            border: 1px solid #000 !important;
            white-space: nowrap !important;
        }

        .table-light th {
            background-color: #0d6efd !important;
            color: #ffffff !important;
            font-weight: bold !important;
            text-align: center !important;
        }

        .num-format {
            text-align: right !important;
        }

        hr {
            border-top: 1px dashed #000;
            opacity: 1;
        }

        .status-lunas {
            color: #198754 !important;
            font-weight: bold !important;
        }

        .status-belum-lunas {
            color: #dc3545 !important;
            font-weight: bold !important;
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
        @if (!isset($isExcel) || !$isExcel)
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">LAPORAN PEMBAYARAN PIUTANG</h4>
                <div class="small">
                    Periode Pembayaran: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d
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
                @if (isset($kode_supplier) && $kode_supplier)
                    @php $supplierName = $suppliers->firstWhere('kode_supplier', $kode_supplier)->nama_supplier ?? $kode_supplier; @endphp
                    <div class="small">Supplier: {{ $supplierName }}</div>
                @endif
                <div class="small text-muted">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
                <hr>
            </div>
        @else
            <table>
                <tr>
                    <td colspan="15" style="font-size: 14px; font-weight: bold; text-align: center;">LAPORAN
                        PEMBAYARAN PIUTANG</td>
                </tr>
                <tr>
                    <td colspan="15" style="text-align: center;">Periode Pembayaran:
                        {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d
                        {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}</td>
                </tr>
                @if ($kode_sales)
                    @php $salesName = $salesmen->firstWhere('nik', $kode_sales)->name ?? $kode_sales; @endphp
                    <tr>
                        <td colspan="15" style="text-align: center;">Salesman: {{ $salesName }}</td>
                    </tr>
                @endif
                @if ($kode_pelanggan)
                    @php $pelangganName = $pelanggans->firstWhere('kode_pelanggan', $kode_pelanggan)->nama_pelanggan ?? $kode_pelanggan; @endphp
                    <tr>
                        <td colspan="15" style="text-align: center;">Pelanggan: {{ $pelangganName }}</td>
                    </tr>
                @endif
                @if (isset($kode_supplier) && $kode_supplier)
                    @php $supplierName = $suppliers->firstWhere('kode_supplier', $kode_supplier)->nama_supplier ?? $kode_supplier; @endphp
                    <tr>
                        <td colspan="15" style="text-align: center;">Supplier: {{ $supplierName }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="15" style="text-align: center; color: #666;">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}
                    </td>
                </tr>
                <tr></tr>
            </table>
        @endif

        {{-- TABLES --}}
        @if ($items->isEmpty())
            <div class="text-center py-5">
                <p>Tidak ada data pembayaran piutang yang ditemukan.</p>
            </div>
        @else
            <table class="table table-sm align-middle w-100" style="border-collapse: collapse; border: 1px solid #000;">
                <thead class="table-light">
                    <tr style="background-color: #0d6efd; color: #ffffff;">
                        <th width="40"
                            style="border: 1px solid #000; padding: 6px; text-align: center; background-color: #0d6efd; color: #ffffff;">
                            No</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            Tgl Bayar</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            Tgl Faktur</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            No. Faktur</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            Kode Pelanggan</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            Nama Pelanggan</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            Sales</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: left; background-color: #0d6efd; color: #ffffff;">
                            Wilayah</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: right; background-color: #0d6efd; color: #ffffff;">
                            Total</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: right; background-color: #0d6efd; color: #ffffff;">
                            Diskon</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: right; background-color: #0d6efd; color: #ffffff;">
                            Subtotal</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: right; background-color: #0d6efd; color: #ffffff;">
                            Jml Bayar</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: right; background-color: #0d6efd; color: #ffffff;">
                            Total Bayar</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: right; background-color: #0d6efd; color: #ffffff;">
                            Sisa Bayar</th>
                        <th
                            style="border: 1px solid #000; padding: 6px; text-align: center; background-color: #0d6efd; color: #ffffff;">
                            Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totTotal = 0;
                        $totDiskon = 0;
                        $totSubtotal = 0;
                        $totJmlBayar = 0;
                        $totTotalBayar = 0;
                        $totSisaBayar = 0;
                    @endphp
                    @foreach ($items as $item)
                        @php
                            $totTotal += $item->total_bruto;
                            $totDiskon += $item->total_diskon;
                            $totSubtotal += $item->total_subtotal;
                            $totJmlBayar += $item->jml_bayar;
                            $totTotalBayar += $item->total_bayar;
                            $totSisaBayar += $item->sisa_bayar;
                        @endphp
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                                {{ $loop->iteration }}</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                {{ \Carbon\Carbon::parse($item->tgl_bayar)->translatedFormat('d F Y') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                {{ \Carbon\Carbon::parse($item->tgl_faktur)->translatedFormat('d F Y') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left; mso-number-format:'\@';">
                                {{ $item->no_faktur }}</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left; mso-number-format:'\@';">
                                {{ $item->kode_pelanggan }}</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                {{ $item->nama_pelanggan }}</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                {{ $item->sales_name ?? '-' }}</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                {{ $item->nama_wilayah ?? '-' }}</td>
                            <td class="num-format" style="border: 1px solid #000; padding: 4px; text-align: right;">
                                {{ number_format($item->total_bruto, 0, ',', '.') }}</td>
                            <td class="num-format" style="border: 1px solid #000; padding: 4px; text-align: right;">
                                {{ number_format($item->total_diskon, 0, ',', '.') }}</td>
                            <td class="num-format" style="border: 1px solid #000; padding: 4px; text-align: right;">
                                {{ number_format($item->total_subtotal, 0, ',', '.') }}</td>
                            <td class="num-format" style="border: 1px solid #000; padding: 4px; text-align: right;">
                                {{ number_format($item->jml_bayar, 0, ',', '.') }}</td>
                            <td class="num-format" style="border: 1px solid #000; padding: 4px; text-align: right;">
                                {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                            <td class="num-format" style="border: 1px solid #000; padding: 4px; text-align: right;">
                                {{ number_format($item->sisa_bayar, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                                @if ($item->status_lunas === 'Lunas')
                                    <span class="status-lunas">Lunas</span>
                                @else
                                    <span class="status-belum-lunas">Belum Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="fw-bold" style="font-weight: bold; background-color: #f2f2f2;">
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td colspan="8"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">TOTAL:
                        </td>
                        <td class="num-format"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">
                            {{ number_format($totTotal, 0, ',', '.') }}</td>
                        <td class="num-format"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">
                            {{ number_format($totDiskon, 0, ',', '.') }}</td>
                        <td class="num-format"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">
                            {{ number_format($totSubtotal, 0, ',', '.') }}</td>
                        <td class="num-format"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">
                            {{ number_format($totJmlBayar, 0, ',', '.') }}</td>
                        <td class="num-format"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">
                            {{ number_format($totTotalBayar, 0, ',', '.') }}</td>
                        <td class="num-format"
                            style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">
                            {{ number_format($totSisaBayar, 0, ',', '.') }}</td>
                        <td style="border: 1px solid #000; padding: 6px;"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

</body>

</html>
