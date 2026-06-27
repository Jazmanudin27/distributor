<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Penjualan - Format 2</title>
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

        .table-rowspan th,
        .table-rowspan td {
            font-size: 11px !important;
            padding: 5px 6px !important;
            border: 1px solid #999 !important;
            white-space: nowrap !important;
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

    $pctFmt = function ($val, $decimals = 2) use ($isExcel) {
        if ($isExcel) {
            return $val > 0 ? round((float) $val, $decimals) : '';
        }
        return $val > 0 ? number_format((float) $val, $decimals, ',', '.') : '';
    };
@endphp

<body>
    <div class="container-fluid py-3">
        {{-- HEADER --}}
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">LAPORAN PENJUALAN (FORMAT 2)</h4>
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
                            style="border: 1px solid #999; text-align: right; font-weight: bold; padding: 5px 6px; mso-number-format:'#,##0';">
                            Neto</th>
                        <th class="text-end"
                            style="border: 1px solid #999; text-align: right; color: #000; padding: 5px 6px; mso-number-format:'#,##0';">
                            Bayar</th>
                        <th class="text-end"
                            style="border: 1px solid #999; text-align: right; color: #000; padding: 5px 6px; mso-number-format:'#,##0';">
                            Sisa</th>
                        <th class="text-center"
                            style="border: 1px solid #999; text-align: center; font-weight: bold; padding: 5px 6px;">
                            Status</th>
                        <th class="text-center"
                            style="border: 1px solid #999; text-align: center; font-weight: bold; padding: 5px 6px;">
                            JT</th>
                        <th class="text-center" style="border: 1px solid #999; text-align: center; padding: 5px 6px;">
                            Diinput</th>
                        <th class="text-center" style="border: 1px solid #999; text-align: center; padding: 5px 6px;">
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
                                style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'#,##0.00';">
                                {{ $row->diskon1_persen > 0 ? $pctFmt($row->diskon1_persen) : '' }}
                            </td>
                            <td class="text-center"
                                style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'#,##0.00';">
                                {{ $row->diskon2_persen > 0 ? $pctFmt($row->diskon2_persen) : '' }}
                            </td>
                            <td class="text-center"
                                style="border: 1px solid #999; text-align: center; padding: 5px 6px; mso-number-format:'#,##0.00';">
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
    </div>
</body>

</html>
