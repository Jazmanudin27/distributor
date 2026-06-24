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

        .table-simple th {
            background-color: #0d6efd !important;
            color: #fff !important;
            font-size: 11px !important;
            padding: 8px 6px !important;
            border: 1px solid #dee2e6 !important;
            text-align: center !important;
            font-weight: bold !important;
            white-space: nowrap !important;
        }

        .table-simple td {
            font-size: 11px !important;
            padding: 6px 6px !important;
            border: 1px solid #dee2e6 !important;
            white-space: nowrap !important;
        }
    </style>
</head>

@php
    $isExcel = isset($isExcel) ? $isExcel : false;
    $numFmt = function ($val, $decimals = 0) use ($isExcel) {
        if ($isExcel) {
            return $val;
        }
        return number_format((float) $val, $decimals, ',', '.');
    };

    $pctFmt = function ($val, $decimals = 2) use ($isExcel) {
        if ($isExcel) {
            return $val ? round((float) $val, $decimals) / 100 : '';
        }
        return $val > 0 ? number_format((float) $val, $decimals, ',', '.') : '';
    };

    if (!function_exists('formatTanggalIndo')) {
        function formatTanggalIndo($date)
        {
            if (!$date) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($date);
            $bulanIndo = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];
            return $d->day . ' ' . $bulanIndo[$d->month] . ' ' . $d->year;
        }
    }

    if (!function_exists('formatTanggalShortIndo')) {
        function formatTanggalShortIndo($date)
        {
            if (!$date) {
                return '-';
            }
            $d = \Carbon\Carbon::parse($date);
            $bulanIndo = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'Mei',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Ags',
                9 => 'Sep',
                10 => 'Okt',
                11 => 'Nov',
                12 => 'Des',
            ];
            return $d->day . '-' . $bulanIndo[$d->month] . '-' . $d->year;
        }
    }
@endphp

<body>
    <div class="container-fluid py-3">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            @if ($jenis_laporan === 'detail_simple')
                <h4 class="fw-bold mb-1">LAPORAN PENJUALAN</h4>
                <div class="small">
                    Periode: {{ formatTanggalIndo($tanggal_mulai) }} s/d {{ formatTanggalIndo($tanggal_akhir) }}
                </div>
            @else
                <h4 class="fw-bold mb-1">LAPORAN PENJUALAN BARANG</h4>
                <div class="small">
                    Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') }} s/d
                    {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}
                </div>
            @endif
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
            @elseif ($jenis_laporan === 'detail_simple')
                {{-- DETAIL SIMPLE TABLES (Format 3) --}}
                <table class="table table-sm align-middle w-100 table-simple"
                    style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr style="background-color:#0d6efd; color:#ffffff;">
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                No</th>
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                Tanggal</th>
                            <th
                                style="border: 1px solid #000; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px; text-align: left;">
                                Nama Pelanggan</th>
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                No. Faktur</th>
                            <th
                                style="border: 1px solid #000; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px; text-align: left;">
                                Nama Barang</th>
                            <th class="text-end"
                                style="border: 1px solid #000; text-align: right; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                Harga</th>
                            <th class="text-end"
                                style="border: 1px solid #000; text-align: right; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                Qty</th>
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                Satuan</th>
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                D1</th>
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                D2</th>
                            <th class="text-center"
                                style="border: 1px solid #000; text-align: center; background-color:#0d6efd; color:#ffffff; font-weight: bold; padding: 8px 6px;">
                                D3</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $num = 1;
                        @endphp
                        @foreach ($items as $row)
                            <tr>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px;">
                                    {{ $num++ }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px;">
                                    {{ formatTanggalShortIndo($row->tanggal) }}</td>
                                <td style="border: 1px solid #dee2e6; padding: 6px 6px;">
                                    {{ $row->nama_pelanggan ?? '-' }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format: '\@';">
                                    {{ $row->no_faktur }}</td>
                                <td style="border: 1px solid #dee2e6; padding: 6px 6px;">{{ $row->nama_barang ?? '-' }}
                                </td>
                                <td class="text-end"
                                    style="border: 1px solid #dee2e6; text-align: right; padding: 6px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->harga) }}</td>
                                <td class="text-end"
                                    style="border: 1px solid #dee2e6; text-align: right; padding: 6px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->qty) }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px;">
                                    {{ $row->satuan ?? 'PCS' }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format:'0.00%';">
                                    {{ $row->diskon1_persen > 0 ? $pctFmt($row->diskon1_persen) : '' }}
                                </td>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format:'0.00%';">
                                    {{ $row->diskon2_persen > 0 ? $pctFmt($row->diskon2_persen) : '' }}
                                </td>
                                <td class="text-center"
                                    style="border: 1px solid #dee2e6; text-align: center; padding: 6px 6px; mso-number-format:'0.00%';">
                                    {{ $row->diskon3_persen > 0 ? $pctFmt($row->diskon3_persen) : '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                {{-- DETAIL ROWSPAN TABLES (Format 2) --}}
                <table class="table table-sm align-middle w-100 table-rowspan"
                    style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr style="background:#0d6efd; color:#fff;">
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                No</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Tanggal</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: center;">
                                No. Faktur</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: center;">
                                Kode</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Nama Pelanggan</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Alamat</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Sales</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Wilayah</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: center;">
                                Kode Barang</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Nama Barang</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Jenis</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Merk</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Qty</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: center;">
                                Satuan</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Harga</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                D1</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                D2</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                D3</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Total</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Bruto</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Diskon</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Neto</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Bayar</th>
                            <th class="text-end"
                                style="border: 1px solid #999; text-align: right; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Sisa</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Status</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                JT</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Diinput</th>
                            <th class="text-center"
                                style="border: 1px solid #999; text-align: center; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px;">
                                Update</th>
                            <th
                                style="border: 1px solid #999; background-color: #0d6efd; color: #ffffff; font-weight: bold; padding: 5px 6px; text-align: left;">
                                Input Oleh</th>
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
                            $seenInvoices = [];
                        @endphp
                        @foreach ($items as $row)
                            @php
                                if (!in_array($row->no_faktur, $seenInvoices)) {
                                    $seenInvoices[] = $row->no_faktur;
                                    $totalBrutoSum += $row->invoice_total;
                                    $totalDiskonSum += $row->invoice_diskon;
                                    $totalNetoSum += $row->invoice_grand_total;
                                    $totalBayarSum += $row->total_bayar;
                                    $totalSisaSum += $row->sisa_bayar;
                                }
                            @endphp
                            <tr>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px;">
                                    {{ $num++ }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px;">
                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d-M-Y') }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format: '\@';">
                                    {{ $row->no_faktur }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format: '\@';">
                                    {{ $row->kode_pelanggan }}</td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">
                                    {{ $row->nama_pelanggan ?? '-' }}</td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">{{ $row->alamat ?? '-' }}</td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">{{ $row->sales_name ?? '-' }}
                                </td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">{{ $row->nama_wilayah ?? '-' }}
                                </td>

                                <td style="border: 1px solid #999; padding: 5px 6px; mso-number-format: '\@';">
                                    {{ $row->kode_barang }}</td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">{{ $row->nama_barang ?? '-' }}
                                </td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">{{ $row->kategori ?? '-' }}</td>
                                <td style="border: 1px solid #999; padding: 5px 6px;">{{ $row->merk ?? '-' }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'#,##0.00';">
                                    {{ $numFmt($row->qty, 2) }}</td>
                                <td style="border: 1px solid #999; padding: 5px 6px; text-align: center;">
                                    {{ $row->satuan ?? 'PCS' }}</td>
                                <td class="text-end"
                                    style="border: 1px solid #999; text-align: right; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->harga) }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'0.00%';">
                                    {{ $row->diskon1_persen > 0 ? $pctFmt($row->diskon1_persen) : '' }}
                                </td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'0.00%';">
                                    {{ $row->diskon2_persen > 0 ? $pctFmt($row->diskon2_persen) : '' }}
                                </td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'0.00%';">
                                    {{ $row->diskon3_persen > 0 ? $pctFmt($row->diskon3_persen) : '' }}
                                </td>
                                <td class="text-end"
                                    style="border: 1px solid #999; text-align: right; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->detail_total) }}</td>

                                <td class="text-end"
                                    style="border: 1px solid #999; text-align: right; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->invoice_total) }}</td>
                                <td class="text-end"
                                    style="border: 1px solid #999; text-align: right; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->invoice_diskon) }}</td>
                                <td class="text-end fw-bold"
                                    style="border: 1px solid #999; text-align: right; font-weight: bold; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->invoice_grand_total) }}</td>
                                <td class="text-end text-success"
                                    style="border: 1px solid #999; text-align: right; color: green; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->total_bayar) }}</td>
                                <td class="text-end text-danger"
                                    style="border: 1px solid #999; text-align: right; color: red; padding: 5px 6px; mso-number-format:'#,##0';">
                                    {{ $numFmt($row->sisa_bayar) }}</td>
                                <td class="text-center fw-bold"
                                    style="border: 1px solid #999; text-align: center; font-weight: bold; padding: 5px 6px;">
                                    <span
                                        class="{{ $row->status_pembayaran === 'Lunas' ? 'text-success' : 'text-danger' }}"
                                        style="color: {{ $row->status_pembayaran === 'Lunas' ? 'green' : 'red' }};">
                                        {{ $row->status_pembayaran }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold"
                                    style="border: 1px solid #999; text-align: center; font-weight: bold; padding: 5px 6px; color: {{ strtolower(substr($row->jenis_transaksi, 0, 1)) === 'k' ? 'orange' : 'green' }};">
                                    {{ strtoupper(substr(is_array($row->jenis_transaksi) ? '' : $row->jenis_transaksi, 0, 1)) }}
                                </td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px;">
                                    {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y H:i') }}</td>
                                <td class="text-center"
                                    style="border: 1px solid #999; text-align: center; padding: 5px 6px;">
                                    {{ \Carbon\Carbon::parse($row->updated_at)->format('d M Y H:i') }}</td>
                                <td class="text-start" style="border: 1px solid #999; padding: 5px 6px;">
                                    {{ $row->input_user_name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold table-light">
                        <tr>
                            <td colspan="19" class="text-end"
                                style="border: 1px solid #999; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 5px 6px;">
                                TOTAL KESELURUHAN:</td>
                            <td class="text-end"
                                style="border: 1px solid #999; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 5px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($totalBrutoSum) }}</td>
                            <td class="text-end"
                                style="border: 1px solid #999; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 5px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($totalDiskonSum) }}</td>
                            <td class="text-end"
                                style="border: 1px solid #999; text-align: right; font-weight: bold; background-color: #f2f2f2; padding: 5px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($totalNetoSum) }}</td>
                            <td class="text-end text-success"
                                style="border: 1px solid #999; text-align: right; font-weight: bold; background-color: #f2f2f2; color: green; padding: 5px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($totalBayarSum) }}</td>
                            <td class="text-end text-danger"
                                style="border: 1px solid #999; text-align: right; font-weight: bold; background-color: #f2f2f2; color: red; padding: 5px 6px; mso-number-format:'#,##0';">
                                {{ $numFmt($totalSisaSum) }}</td>
                            <td colspan="5" style="border: 1px solid #999; background-color: #f2f2f2;"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    </div>


</body>

</html>
